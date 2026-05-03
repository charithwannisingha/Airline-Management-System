<?php
// ============================================================
//  AMS — Manage Aircrafts (Nexus Airlines) - Logic Intact
// ============================================================
require_once 'includes/auth.php';
requireLogin();
requireAdmin(); // ඇඩ්මින් පමණක් බව තහවුරු කිරීම
$db = getDB();

$msg = '';

// 1. ගුවන් යානයක් මකා දැමීම (Delete Aircraft)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = (int)$_POST['id'];
    $stmt = $db->prepare("DELETE FROM aircraft WHERE id = ?");
    if($stmt->execute([$id])) {
        $msg = "Aircraft deleted successfully.";
    }
}

// 2. ගුවන් යානයක විස්තර යාවත්කාලීන කිරීම (Update Aircraft)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $id = (int)$_POST['id'];
    $reg = trim($_POST['registration']);
    $model = trim($_POST['model']);
    $cap = (int)$_POST['capacity'];
    $status = $_POST['maintenance_status'];
    
    $stmt = $db->prepare("UPDATE aircraft SET registration = ?, model = ?, capacity = ?, maintenance_status = ? WHERE id = ?");
    if($stmt->execute([$reg, $model, $cap, $status, $id])) {
        $msg = "Aircraft details updated successfully.";
    }
}

// 3. අලුත් ගුවන් යානයක් ඇතුළත් කිරීම (Add New Aircraft)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $reg = trim($_POST['registration']);
    $model = trim($_POST['model']);
    $cap = (int)$_POST['capacity'];
    
    $stmt = $db->prepare("INSERT INTO aircraft (registration, model, capacity) VALUES (?, ?, ?)");
    if($stmt->execute([$reg, $model, $cap])) {
        $msg = "New aircraft added successfully.";
    }
}

// දත්ත ගබඩාවේ ඇති යානා සියල්ල ලබා ගැනීම (Fetch All Aircraft)
$aircrafts = $db->query("SELECT * FROM aircraft ORDER BY id DESC")->fetchAll();

// Edit බොත්තම එබූ විට, අදාළ යානයේ විස්තර ලබා ගැනීම (Fetch Single Aircraft for Editing)
$editData = null;
if (isset($_GET['edit_id'])) {
    $stmt = $db->prepare("SELECT * FROM aircraft WHERE id = ?");
    $stmt->execute([(int)$_GET['edit_id']]);
    $editData = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Aircrafts · Nexus Airlines</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'includes/sidebar.php'; ?>

<div class="main-content">

<header class="topbar">
  <div class="topbar-title">Aircraft Management</div>
</header>

<main class="page-content">
  <?php if(!empty($msg)): ?>
      <div class="alert alert-success" style="margin-bottom: 20px; padding: 15px; background: #d1e7dd; color: #0f5132; border-radius: 8px; font-weight: bold;"><?= $msg ?></div>
  <?php endif; ?>

  <div class="grid-2">
    <div class="card">
      <div class="card-header">
          <span class="card-title">
              <?= $editData ? 'Edit Aircraft Details' : 'Add New Aircraft' ?>
          </span>
      </div>
      <div class="card-body">
        
        <?php if($editData): ?>
            <form method="POST" action="manage_aircrafts.php">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?= $editData['id'] ?>">
                
                <div class="form-group">
                    <label>Registration No</label>
                    <input type="text" name="registration" value="<?= htmlspecialchars($editData['registration']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Aircraft Model</label>
                    <input type="text" name="model" value="<?= htmlspecialchars($editData['model']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Total Capacity (Seats)</label>
                    <input type="number" name="capacity" value="<?= $editData['capacity'] ?>" required>
                </div>
                <div class="form-group">
                    <label>Maintenance Status</label>
                    <select name="maintenance_status">
                        <option value="operational" <?= $editData['maintenance_status'] === 'operational' ? 'selected' : '' ?>>Operational</option>
                        <option value="maintenance" <?= $editData['maintenance_status'] === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                        <option value="retired" <?= $editData['maintenance_status'] === 'retired' ? 'selected' : '' ?>>Retired</option>
                    </select>
                </div>
                <button type="submit" class="btn-primary">Update Aircraft</button>
                <a href="manage_aircrafts.php" class="btn-secondary" style="display: inline-block; text-align: center; text-decoration: none; padding: 10px; margin-top: 10px; margin-left: 10px; border-radius: 8px;">Cancel</a>
            </form>
        <?php else: ?>
            <form method="POST" action="manage_aircrafts.php">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label>Registration No (e.g. 4R-ABX)</label>
                    <input type="text" name="registration" placeholder="Enter Registration No" required>
                </div>
                <div class="form-group">
                    <label>Aircraft Model</label>
                    <input type="text" name="model" placeholder="e.g. Boeing 737" required>
                </div>
                <div class="form-group">
                    <label>Total Capacity (Seats)</label>
                    <input type="number" name="capacity" placeholder="e.g. 180" required>
                </div>
                <button type="submit" class="btn-primary mt-2">Add Aircraft</button>
            </form>
        <?php endif; ?>

      </div>
    </div>

    <div class="card">
      <div class="card-header"><span class="card-title">Fleet List</span></div>
      <div class="table-wrap">
        <table class="ams-table">
          <thead>
            <tr>
              <th>REGISTRATION</th>
              <th>MODEL</th>
              <th>CAPACITY</th>
              <th>STATUS</th>
              <th>ACTION</th> 
            </tr>
          </thead>
          <tbody>
          <?php if(empty($aircrafts)): ?>
              <tr><td colspan="5" style="text-align:center; padding:20px; color:var(--gray-500);">No aircraft found in the fleet.</td></tr>
          <?php else: ?>
              <?php foreach ($aircrafts as $a): ?>
                <tr>
                  <td class="fw" style="color:var(--sky)"><?= htmlspecialchars($a['registration']) ?></td>
                  <td style="font-weight: 500;"><?= htmlspecialchars($a['model']) ?></td>
                  <td><?= $a['capacity'] ?> Seats</td>
                  <td>
                      <?php 
                        $statusClass = $a['maintenance_status'] === 'operational' ? 'pill-success' : ($a['maintenance_status'] === 'maintenance' ? 'pill-warning' : 'pill-danger');
                      ?>
                      <span class="pill <?= $statusClass ?>"><?= ucfirst($a['maintenance_status']) ?></span>
                  </td>
                  <td style="display: flex; gap: 5px;">
                      <a href="?edit_id=<?= $a['id'] ?>" class="btn-secondary btn-sm" style="text-decoration: none;">Edit</a>
                      
                      <form method="POST" style="margin: 0;" onsubmit="return confirm('Are you sure you want to delete this aircraft?');">
                          <input type="hidden" name="action" value="delete">
                          <input type="hidden" name="id" value="<?= $a['id'] ?>">
                          <button type="submit" class="btn-secondary btn-sm" style="background-color: #ef4444; color: white; border: none; cursor: pointer;">Delete</button>
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