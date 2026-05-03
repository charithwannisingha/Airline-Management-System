<?php
// ============================================================
//  AMS — All Bookings Management (Admin) - English Only & Fixed
// ============================================================
require_once 'includes/auth.php';
requireLogin();
requireAdmin(); // Admin පමණක් ඇතුළු වීමට
$db = getDB();

$success_msg = '';

// Status එක Update කිරීමේ ක්‍රියාවලිය (Update Status Button එක එබූ විට)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $booking_id = (int)$_POST['booking_id'];
    $new_status = $_POST['status'];
    
    $update = $db->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    if ($update->execute([$new_status, $booking_id])) {
        // සිංහල පණිවිඩය වෙනුවට ඉංග්‍රීසි පණිවිඩය යෙදුවා
        $success_msg = "Booking status updated successfully!";
    }
}

// සියලුම බුකින්ස් Database එකෙන් ලබා ගැනීම
$stmt = $db->query("
    SELECT b.*, f.flight_number, f.departure_time, u.full_name
    FROM bookings b
    JOIN flights f ON b.flight_id = f.id
    JOIN users u ON b.user_id = u.id
    ORDER BY b.created_at DESC
");
$all_bookings = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>All Bookings · Nexus Airlines</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'includes/sidebar.php'; ?>

<div class="main-content">

<header class="topbar">
  <div class="topbar-title">All Bookings Management</div>
</header>

<main class="page-content">
  
  <?php if ($success_msg): ?>
      <div class="alert alert-success" style="margin-bottom: 20px; padding: 15px; background: #d1e7dd; color: #0f5132; border-radius: 8px; font-weight: bold;">
          <?= $success_msg ?>
      </div>
  <?php endif; ?>

  <div class="card">
    <div class="card-body">
      <div class="table-wrap">
        <table class="ams-table">
          <thead>
            <tr>
              <th>REF NO</th>
              <th>PASSENGER</th>
              <th>FLIGHT</th>
              <th>SEAT</th>
              <th>STATUS</th>
              <th>UPDATE STATUS</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($all_bookings)): ?>
              <tr><td colspan="6" style="text-align:center; padding:20px; color:var(--gray-500);">No bookings found in the system.</td></tr>
            <?php else: ?>
              <?php foreach ($all_bookings as $b): ?>
              <tr>
                <td style="font-weight:bold; color:var(--sky)"><?= htmlspecialchars($b['booking_ref']) ?></td>
                <td><?= htmlspecialchars($b['full_name']) ?></td>
                <td>
                  <strong><?= htmlspecialchars($b['flight_number']) ?></strong><br>
                  <small style="color:var(--gray-500);"><?= date('d M Y, H:i', strtotime($b['departure_time'])) ?></small>
                </td>
                <td><?= htmlspecialchars($b['seat_number']) ?> <br><small style="color:var(--gray-500);">(<?= ucfirst($b['class']) ?>)</small></td>
                <td>
                  <?php
                    $statusClass = 'pill-warning';
                    if ($b['status'] == 'confirmed') $statusClass = 'pill-success';
                    if ($b['status'] == 'cancelled') $statusClass = 'pill-danger';
                  ?>
                  <span class="pill <?= $statusClass ?>"><?= strtoupper($b['status']) ?></span>
                </td>
                <td>
                    <form method="POST" style="display: flex; gap: 5px; align-items: center;">
                        <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                        <select name="status" style="padding: 5px; border-radius: 5px; border: 1px solid #ccc; font-size: 13px;">
                            <option value="confirmed" <?= $b['status'] == 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                            <option value="pending" <?= $b['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="cancelled" <?= $b['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                        <button type="submit" name="update_status" class="btn-secondary btn-sm" style="padding: 5px 10px; font-size: 12px;">Update</button>
                    </form>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</main>

</div> <script src="js/app.js"></script>
</body>
</html>