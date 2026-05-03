<?php
// ============================================================
//  AMS — Booking Processor (AJAX & Loyalty Points)
// ============================================================
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$db        = getDB();
$userId    = (int)$_SESSION['user_id'];
$flightId  = (int)($_POST['flight_id'] ?? 0);
$class     = trim($_POST['class']       ?? 'economy');
$seat      = strtoupper(trim($_POST['seat_number'] ?? ''));
$price     = (float)($_POST['price']   ?? 0);

if (!$flightId || !$seat || !$price) {
    echo json_encode(['success' => false, 'message' => 'Missing information.']);
    exit();
}

// ආසනය වෙන කෙනෙක් අරගෙනද කියා බැලීම
$check = $db->prepare("SELECT id FROM bookings WHERE flight_id=? AND seat_number=? AND status='confirmed'");
$check->execute([$flightId, $seat]);
if ($check->fetch()) {
    echo json_encode(['success' => false, 'message' => "Seat {$seat} was just taken by someone else."]);
    exit();
}

// Booking Reference (PNR) එක හැදීම
$ref = 'HA' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));

$firstName  = sanitize($_POST['first_name']   ?? '');
$lastName   = sanitize($_POST['last_name']    ?? '');
$email      = sanitize($_POST['email']        ?? '');
$docType    = sanitize($_POST['doc_type']     ?? 'nic');
$docNumber  = sanitize($_POST['doc_number']   ?? '');

try {
    // Database Transaction එකක් ආරම්භ කිරීම (Booking එකයි Points දාන එකයි දෙකම එකට වීමට)
    $db->beginTransaction();

    // 1. Booking එක Database එකට ඇතුළත් කිරීම
    $stmt = $db->prepare("
        INSERT INTO bookings
          (user_id, flight_id, booking_ref, seat_number, class, total_price,
           passenger_first, passenger_last, passenger_email, doc_type, doc_number, status, created_at)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,'confirmed', NOW())
    ");
    $stmt->execute([
        $userId, $flightId, $ref, $seat, $class, $price,
        $firstName, $lastName, $email, $docType, $docNumber
    ]);

    $bookingId = $db->lastInsertId();

    // 2. Loyalty Points එකතු කිරීම (සෑම රුපියල් 1000 කටම 1 Point)
    $pointsToAdd = floor($price / 1000);
    $updatePoints = $db->prepare("UPDATE users SET loyalty_points = loyalty_points + ? WHERE id = ?");
    $updatePoints->execute([$pointsToAdd, $userId]);

    // සියල්ල සාර්ථක නම් Save කිරීම තහවුරු කිරීම (Commit)
    $db->commit();

    // සාර්ථක වූ බව සහ ලැබුණු ලකුණු ගණන දැනුම් දීම
    echo json_encode([ 
        'success' => true, 
        'message' => 'Payment Successful! You earned ' . $pointsToAdd . ' Loyalty Points.', 
        'booking_id' => $bookingId 
    ]);

} catch (Exception $e) {
    // දෝෂයක් වුවහොත් කිසිවක් Save නොකර සිටීම
    $db->rollBack();
    echo json_encode(['success' => false, 'message' => 'System error occurred. Please try again.']);
}
?>