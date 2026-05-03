<?php
// ============================================================
//  AMS — My Bookings (Fixed Layout & English Only)
// ============================================================
require_once 'includes/auth.php';
requireLogin();
$db = getDB();
$user = currentUser();

// පරිශීලකයාගේ බුකින් විස්තර ලබා ගැනීම
$stmt = $db->prepare("
    SELECT b.*, f.flight_number, f.origin, f.destination, f.departure_time
    FROM bookings b
    JOIN flights f ON b.flight_id = f.id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
");
$stmt->execute([$user['id']]);
$bookings = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Bookings · AMS</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'includes/sidebar.php'; ?>

<div class="main-content">

<header class="topbar">
  <div class="topbar-title">Trip History</div>
</header>

<main class="page-content">
  <div class="card">
    <div class="card-header"><span class="card-title">My Bookings</span></div>
    <div class="table-wrap">
      <table class="ams-table">
        <thead>
          <tr>
            <th>Booking Ref</th>
            <th>Flight Route</th>
            <th>Departure Date</th>
            <th>Seat</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($bookings)): ?>
            <tr><td colspan="6" style="text-align:center; padding: 20px; color: var(--gray-500);">No bookings found. Time to plan a trip! ✈️</td></tr>
          <?php else: ?>
            <?php foreach ($bookings as $b): ?>
            <tr>
              <td style="font-weight:bold; color:var(--sky)"><?= htmlspecialchars($b['booking_ref']) ?></td>
              <td>
                <strong><?= htmlspecialchars($b['flight_number']) ?></strong><br>
                <small style="color: var(--gray-500);"><?= htmlspecialchars($b['origin']) ?> → <?= htmlspecialchars($b['destination']) ?></small>
              </td>
              <td><?= date('d M Y, H:i', strtotime($b['departure_time'])) ?></td>
              <td><?= htmlspecialchars($b['seat_number']) ?> <br><small style="color: var(--gray-500);">(<?= ucfirst($b['class']) ?>)</small></td>
              <td>
                <?php
                  $statusClass = 'pill-warning';
                  if ($b['status'] == 'confirmed') $statusClass = 'pill-success';
                  if ($b['status'] == 'cancelled') $statusClass = 'pill-danger';
                ?>
                <span class="pill <?= $statusClass ?>"><?= strtoupper($b['status']) ?></span>
              </td>
              <td>
                <?php if ($b['status'] == 'confirmed'): ?>
                    <a href="e_ticket.php?booking_id=<?= $b['id'] ?>" class="btn-primary btn-sm" style="text-decoration:none; display:inline-block; margin-bottom: 5px;">View Ticket</a>
                    <a href="refund.php?id=<?= $b['id'] ?>" class="btn-danger btn-sm" style="text-decoration:none; background:#ef4444; color:white; padding:5px 12px; border-radius:5px; display:inline-block;">Cancel</a>
                <?php else: ?>
                    <span style="color:var(--gray-500); font-size: 12px; font-weight: bold;">---</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

</div> <script src="js/app.js"></script>
</body>
</html>