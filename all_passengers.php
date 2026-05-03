<?php
// ============================================================
//  AMS — All Passengers (Nexus Airlines)
// ============================================================
require_once 'includes/auth.php';
requireLogin();
requireAdmin();
$db = getDB();

$stmt = $db->query("SELECT id, username, full_name, email, role, created_at FROM users ORDER BY created_at DESC");
$passengers = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>All Passengers · Nexus Airlines</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'includes/sidebar.php'; ?>

<div class="main-content">

<header class="topbar">
  <div class="topbar-title">Passenger Management</div>
</header>

<main class="page-content">
  <div class="card">
    <div class="card-header"><span class="card-title">Registered Passengers</span></div>
    <div class="table-wrap">
      <table class="ams-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>FULL NAME</th>
            <th>EMAIL</th>
            <th>USERNAME</th>
            <th>ROLE</th>
            <th>JOINED DATE</th>
          </tr>
        </thead>
        <tbody>
          <?php if(empty($passengers)): ?>
            <tr><td colspan="6" style="text-align:center; padding:20px; color:var(--gray-500);">No passengers registered yet.</td></tr>
          <?php else: ?>
            <?php foreach ($passengers as $p): ?>
            <tr>
              <td style="font-weight:bold; color:var(--sky)">#<?= $p['id'] ?></td>
              <td style="font-weight:600;"><?= htmlspecialchars($p['full_name']) ?></td>
              <td><?= htmlspecialchars($p['email']) ?></td>
              <td><?= htmlspecialchars($p['username']) ?></td>
              <td><span class="pill <?= $p['role'] == 'admin' ? 'pill-success' : 'pill-warning' ?>"><?= strtoupper($p['role']) ?></span></td>
              <td class="text-muted"><?= date('d M Y', strtotime($p['created_at'])) ?></td>
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