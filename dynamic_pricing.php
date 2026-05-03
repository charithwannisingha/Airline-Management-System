<?php
// ============================================================
//  AMS — Dynamic Pricing (Nexus Airlines) - Logic Intact
// ============================================================
require_once 'includes/auth.php';
requireLogin();
requireAdmin(); // ඇඩ්මින් පමණක් බව තහවුරු කිරීම
$db = getDB();

$msg = '';

// මිල ගණන් යාවත්කාලීන කිරීම (Update Pricing)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_price'])) {
    $flight_id = (int)$_POST['flight_id'];
    $eco = (float)$_POST['price_economy'];
    $bus = (float)$_POST['price_business'];
    $fir = (float)$_POST['price_first'];

    $stmt = $db->prepare("UPDATE flights SET price_economy = ?, price_business = ?, price_first = ? WHERE id = ?");
    if($stmt->execute([$eco, $bus, $fir, $flight_id])) {
        $msg = "Flight fares updated successfully.";
    }
}

// අවලංගු නොකළ ගුවන් ගමන් ලැයිස්තුව ගැනීම
$flights = $db->query("
    SELECT id, flight_number, origin, destination, departure_time, price_economy, price_business, price_first 
    FROM flights 
    WHERE status != 'cancelled' 
    ORDER BY departure_time ASC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pricing Management · Nexus Airlines</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'includes/sidebar.php'; ?>

<div class="main-content">

<header class="topbar">
  <div class="topbar-title">Pricing Management</div>
</header>

<main class="page-content">
  <?php if(!empty($msg)): ?>
      <div class="alert alert-success" style="margin-bottom: 20px; padding: 15px; background: #d1e7dd; color: #0f5132; border-radius: 8px; font-weight: bold;"><?= $msg ?></div>
  <?php endif; ?>

  <div class="card mb-3" style="background: #f0f9ff; border: 1px solid #bae6fd;">
      <div class="card-body">
          <h4 style="color: #0284c7; margin-bottom: 5px;">Base Fare Management</h4>
          <p style="color: #0f172a; font-size: 14px;">Update the base fares for each class. The Nexus Airlines dynamic pricing engine will automatically apply surcharges based on real-time demand and time left before departure.</p>
      </div>
  </div>

  <div class="card">
    <div class="card-header"><span class="card-title">Adjust Flight Fares (LKR)</span></div>
    <div class="table-wrap">
      <table class="ams-table">
        <thead>
          <tr>
            <th>FLIGHT</th>
            <th>ROUTE / DATE</th>
            <th>ECONOMY</th>
            <th>BUSINESS</th>
            <th>FIRST CLASS</th>
            <th>ACTION</th>
          </tr>
        </thead>
        <tbody>
        <?php if(empty($flights)): ?>
            <tr><td colspan="6" style="text-align:center; padding:20px; color:var(--gray-500);">No active flights available for pricing update.</td></tr>
        <?php else: ?>
            <?php foreach ($flights as $f): ?>
              <tr>
                <form method="POST" style="margin: 0;">
                    <input type="hidden" name="update_price" value="1">
                    <input type="hidden" name="flight_id" value="<?= $f['id'] ?>">
                    
                    <td class="fw" style="color:var(--sky)"><?= htmlspecialchars($f['flight_number']) ?></td>
                    <td>
                        <strong><?= htmlspecialchars($f['origin']) ?> → <?= htmlspecialchars($f['destination']) ?></strong><br>
                        <small style="color:var(--gray-500);"><?= date('d M Y, H:i', strtotime($f['departure_time'])) ?></small>
                    </td>
                    
                    <td><input type="number" name="price_economy" value="<?= $f['price_economy'] ?>" style="width:100px; padding:6px; border-radius: 5px; border: 1px solid #ccc;" required></td>
                    <td><input type="number" name="price_business" value="<?= $f['price_business'] ?>" style="width:100px; padding:6px; border-radius: 5px; border: 1px solid #ccc;" required></td>
                    <td><input type="number" name="price_first" value="<?= $f['price_first'] ?>" style="width:100px; padding:6px; border-radius: 5px; border: 1px solid #ccc;" required></td>
                    
                    <td><button type="submit" class="btn-primary btn-sm">Save</button></td>
                </form>
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