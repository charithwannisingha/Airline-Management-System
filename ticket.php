<?php
require_once 'includes/auth.php';
requireLogin(); // ලොග් වී ඇති බව තහවුරු කිරීම (මෙය මගීන්ට පමණක් සීමා නොවේ, Admin ටත් තමන්ගේ Bookings බැලිය හැක)
$db = getDB();
$user = currentUser();

$msg = '';
$error = '';

// මගියා විසින් බුකින් එකක් අවලංගු කිරීම (Cancel Booking)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel') {
    $booking_id = (int)$_POST['booking_id'];
    
    // අදාළ බුකින් එක මේ මගියාගේමද යන්න පරීක්ෂා කිරීම (Security Check)
    $stmt = $db->prepare("SELECT id, status FROM bookings WHERE id = ? AND user_id = ?");
    $stmt->execute([$booking_id, $user['id']]);
    $booking = $stmt->fetch();
    
    if ($booking) {
        if ($booking['status'] === 'confirmed') {
            $cancelStmt = $db->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
            if ($cancelStmt->execute([$booking_id])) {
                $msg = "ඔබගේ ගුවන් ගමන් වෙන්කිරීම සාර්ථකව අවලංගු කරන ලදී.";
            }
        } else {
            $error = "මෙම වෙන්කිරීම අවලංගු කළ නොහැක (දැනටමත් අවලංගු කර හෝ ගමන අවසන් කර ඇත).";
        }
    } else {
        $error = "අවලංගු කිරීම ප්‍රතික්ෂේප විය.";
    }
}

// ලොග් වී සිටින මගියාගේ සියලුම ගුවන් ගමන් ලබා ගැනීම (Fetch User's Trip History)
$stmt = $db->prepare("
    SELECT b.*, f.flight_number, f.origin, f.destination, f.departure_time, f.arrival_time
    FROM bookings b
    JOIN flights f ON b.flight_id = f.id
    WHERE b.user_id = ?
    ORDER BY f.departure_time DESC
");
$stmt->execute([$user['id']]);
$my_bookings = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Bookings · AMS</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
  <style>
      .booking-card { border: 1px solid var(--gray-200); border-radius: 12px; padding: 20px; margin-bottom: 20px; background: white; transition: 0.3s; }
      .booking-card:hover { border-color: var(--sky-light); box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
      .booking-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px dashed var(--gray-200); padding-bottom: 15px; margin-bottom: 15px; }
      .route-info { display: flex; align-items: center; gap: 15px; }
      .city { font-family: var(--font-head); font-size: 24px; font-weight: 800; color: var(--navy); }
      .flight-arrow { color: var(--sky-light); font-size: 18px; }
      .booking-details { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; }
      .detail-box { display: flex; flex-direction: column; }
      .detail-lbl { font-size: 12px; color: var(--gray-600); text-transform: uppercase; letter-spacing: 0.5px; }
      .detail-val { font-size: 15px; font-weight: 500; color: var(--navy); margin-top: 4px; }
      .booking-actions { margin-top: 20px; display: flex; gap: 10px; justify-content: flex-end; }
  </style>
</head>
<body>
<?php include 'includes/sidebar.php'; ?>

<header class="topbar">
  <div class="topbar-title">My Bookings (මගේ ගුවන් ගමන් ඉතිහාසය)</div>
  <div class="topbar-right">
      <a href="flights.php" class="btn-primary btn-sm">Book New Flight</a>
  </div>
</header>

<main class="page-content">
  <?php if(!empty($msg)): ?>
      <div class="alert alert-success"><?= $msg ?></div>
  <?php endif; ?>
  <?php if(!empty($error)): ?>
      <div class="alert alert-danger"><?= $error ?></div>
  <?php endif; ?>

  <div class="page-header">
      <p>ඔබගේ අතීත සහ අනාගත ගුවන් ගමන් විස්තර පහතින් දැක්වේ.</p>
  </div>

  <?php if (empty($my_bookings)): ?>
      <div class="card">
        <div class="card-body text-center" style="padding:4rem 2rem">
          <div style="font-size:4rem; margin-bottom:1rem; opacity:0.5;">🎫</div>
          <h2 style="font-family:var(--font-head); color:var(--navy); margin-bottom:.5rem;">ඔබ තවමත් ගුවන් ගමන් වෙන්කර නොමැත</h2>
          <p class="text-muted">නව ගුවන් ගමනක් වෙන්කර ගැනීම සඳහා 'Book New Flight' බොත්තම ඔබන්න.</p>
        </div>
      </div>
  <?php else: ?>
      
      <?php foreach ($my_bookings as $b): 
          $isFuture = strtotime($b['departure_time']) > time();
          $statusClass = $b['status'] === 'confirmed' ? 'pill-success' : ($b['status'] === 'cancelled' ? 'pill-danger' : 'pill-info');
      ?>
          <div class="booking-card">
              <div class="booking-header">
                  <div class="route-info">
                      <div class="city"><?= htmlspecialchars($b['origin']) ?></div>
                      <div class="flight-arrow">✈</div>
                      <div class="city"><?= htmlspecialchars($b['destination']) ?></div>
                      <span class="pill <?= $statusClass ?>" style="margin-left: 15px;"><?= ucfirst($b['status']) ?></span>
                  </div>
                  <div style="text-align: right;">
                      <div class="detail-lbl">Booking Reference</div>
                      <div class="detail-val" style="font-family: monospace; font-size: 18px; color: var(--sky);"><?= htmlspecialchars($b['booking_ref']) ?></div>
                  </div>
              </div>
              
              <div class="booking-details">
                  <div class="detail-box">
                      <span class="detail-lbl">Flight</span>
                      <span class="detail-val"><?= htmlspecialchars($b['flight_number']) ?></span>
                  </div>
                  <div class="detail-box">
                      <span class="detail-lbl">Departure</span>
                      <span class="detail-val"><?= date('d M Y, H:i', strtotime($b['departure_time'])) ?></span>
                  </div>
                  <div class="detail-box">
                      <span class="detail-lbl">Passenger</span>
                      <span class="detail-val"><?= htmlspecialchars($b['passenger_first'] . ' ' . $b['passenger_last']) ?></span>
                  </div>
                  <div class="detail-box">
                      <span class="detail-lbl">Seat & Class</span>
                      <span class="detail-val"><?= htmlspecialchars($b['seat_number']) ?> (<?= ucfirst($b['class']) ?>)</span>
                  </div>
                  <div class="detail-box">
                      <span class="detail-lbl">Total Paid</span>
                      <span class="detail-val">LKR <?= number_format($b['total_price']) ?></span>
                  </div>
              </div>

              <div class="booking-actions">
                  <?php if ($b['status'] !== 'cancelled'): ?>
                      <a href="e_ticket.php?booking_id=<?= $b['id'] ?>" class="btn-primary btn-sm">View E-Ticket</a>
                  <?php endif; ?>

                  <?php if ($isFuture && $b['status'] === 'confirmed'): ?>
                      <form method="POST" onsubmit="return confirm('මෙම ගුවන් ගමන අවලංගු කිරීමට ඔබට සහතිකද? මුදල් ආපසු ගෙවීමේ ප්‍රතිපත්ති (Refund Policy) අදාළ විය හැක.');">
                          <input type="hidden" name="action" value="cancel">
                          <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                          <button type="submit" class="btn-secondary btn-sm" style="color: #ef4444; border-color: #ef4444;">Cancel Flight</button>
                      </form>
                  <?php endif; ?>
              </div>
          </div>
      <?php endforeach; ?>

  <?php endif; ?>
</main>
</div></div>
</body>
</html>