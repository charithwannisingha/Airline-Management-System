<?php
require_once 'includes/auth.php';
requireLogin();
$db = getDB();
$user = currentUser();

$booking_id = (int)($_GET['id'] ?? 0);

// බුකින් විස්තර ලබා ගැනීම
$stmt = $db->prepare("
    SELECT b.*, f.departure_time, f.flight_number 
    FROM bookings b 
    JOIN flights f ON b.flight_id = f.id 
    WHERE b.id = ? AND b.user_id = ?
");
$stmt->execute([$booking_id, $user['id']]);
$booking = $stmt->fetch();

if (!$booking || $booking['status'] !== 'confirmed') {
    die("Invalid booking or already cancelled.");
}

// Refund ගණනය කිරීමේ Logic එක
$now = time();
$departure = strtotime($booking['departure_time']);
$daysLeft = ($departure - $now) / (60 * 60 * 24);

$refundPercentage = 0;
if ($daysLeft > 7) {
    $refundPercentage = 90;
} elseif ($daysLeft >= 2) {
    $refundPercentage = 50;
} else {
    $refundPercentage = 0;
}

$refundAmount = ($booking['total_price'] * $refundPercentage) / 100;

// Refund එක Confirm කිරීමේ ක්‍රියාවලිය
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_refund'])) {
    $update = $db->prepare("UPDATE bookings SET status = 'cancelled', refund_amount = ? WHERE id = ?");
    if ($update->execute([$refundAmount, $booking_id])) {
        echo "<script>alert('Refund Processed Successfully!'); window.location.href='my_bookings.php';</script>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Process Refund · AMS</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <main class="page-content">
        <div class="card" style="max-width: 600px; margin: auto;">
            <div class="card-header"><span class="card-title">Cancel Flight & Request Refund</span></div>
            <div class="card-body">
                <h3>Flight: <?= $booking['flight_number'] ?></h3>
                <p>Booking Price: <b>LKR <?= number_format($booking['total_price']) ?></b></p>
                <p>Time Until Flight: <b><?= round($daysLeft, 1) ?> Days</b></p>
                <hr>
                <div style="background: #f8fafc; padding: 20px; border-radius: 8px; text-align: center;">
                    <h2 style="color: var(--sky);"><?= $refundPercentage ?>% Refund Applicable</h2>
                    <h1 style="margin: 10px 0;">LKR <?= number_format($refundAmount) ?></h1>
                    <p class="text-muted">මුදල් ආපසු ගෙවීමේ ප්‍රතිපත්තියට අනුව ඔබ හට ලැබෙන මුදල.</p>
                </div>

                <form method="POST" style="margin-top: 20px;">
                    <input type="hidden" name="confirm_refund" value="1">
                    <button type="submit" class="btn-primary btn-full" style="background: #ef4444;">Confirm Cancellation & Refund</button>
                    <a href="my_bookings.php" class="btn-secondary btn-full" style="display: block; text-align: center; margin-top: 10px; text-decoration: none;">Go Back</a>
                </form>
            </div>
        </div>
    </main>
</body>
</html>