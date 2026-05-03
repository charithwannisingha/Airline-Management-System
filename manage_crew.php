<?php
// ============================================================
//  AMS — Manage Crew (Nexus Airlines) - Logic Intact
// ============================================================
require_once 'includes/auth.php';
requireLogin();
requireAdmin(); // Admin access only
$db = getDB();

$msg = '';

// 1. Crew Member කෙනෙක් මකා දැමීම (Delete Crew)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = (int)$_POST['id'];
    $stmt = $db->prepare("DELETE FROM crew WHERE id = ?");
    if($stmt->execute([$id])) {
        $msg = "Crew member deleted successfully.";
    }
}

// 2. Crew Member ගේ විස්තර යාවත්කාලීන කිරීම (Update Crew)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $id = (int)$_POST['id'];
    $emp_id = trim($_POST['employee_id']);
    $name = trim($_POST['full_name']);
    $role = $_POST['role'];
    $license = trim($_POST['license_no']);
    $availability = $_POST['availability'];

    $stmt = $db->prepare("UPDATE crew SET employee_id=?, full_name=?, role=?, license_no=?, availability=? WHERE id=?");
    if($stmt->execute([$emp_id, $name, $role, $license, $availability, $id])) {
        $msg = "Crew member details updated successfully.";
    }
}

// 3. අලුත් Crew කෙනෙක් එකතු කිරීම (Add Crew)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $emp_id = trim($_POST['employee_id']);
    $name = trim($_POST['full_name']);
    $role = $_POST['role'];
    $license = trim($_POST['license_no']);

    $stmt = $db->prepare("INSERT INTO crew (employee_id, full_name, role, license_no) VALUES (?, ?, ?, ?)");
    if($stmt->execute([$emp_id, $name, $role, $license])) {
        $msg = "New crew member added successfully.";
    }
}

// සියලුම කාර්ය මණ්ඩල ලැයිස්තුව
$crew = $db->query("SELECT * FROM crew ORDER BY role ASC, full_name ASC")->fetchAll();

// Edit බොත්තම එබූ විට දත්ත ලබා ගැනීම
$editData = null;
if (isset($_GET['edit_id'])) {
    $stmt = $db->prepare("SELECT * FROM crew WHERE id = ?");
    $stmt->execute([(int)$_GET['edit_id']]);
    $editData = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Crew · Nexus Airlines</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'includes/sidebar.php'; ?>

<div class="main-content">

<header class="topbar">
  <div class="topbar-title">Crew Management</div>
</header>

<main class="page-content">
  <?php if(!empty($msg)): ?>
      <div class="alert alert-success" style="margin-bottom: 20px; padding: 15px; background: #d1e7dd; color: #0f5132; border-radius: 8px; font-weight: bold;"><?= $msg ?></div>
  <?php endif; ?>

  <div class="grid-2">
    <div class="card">
      <div class="card-header">
          <span class="card-title">
              <?= $editData ? 'Edit Crew Member Details' : 'Add New Crew Member' ?>
          </span>
      </div>
      <div class="card-body">
        <?php if($editData): ?>
            <form method="POST" action="manage_crew.php">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?= $editData['id'] ?>">
                <div class="form-group">
                    <label>Employee ID</label>
                    <input type="text" name="employee_id" value="<?= htmlspecialchars($editData['employee_id']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" value="<?= htmlspecialchars($editData['full_name']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select name="role" required>
                        <option value="captain" <?= $editData['role']=='captain'?'selected':'' ?>>Captain</option>
                        <option value="first_officer" <?= $editData['role']=='first_officer'?'selected':'' ?>>First Officer</option>
                        <option value="purser" <?= $editData['role']=='purser'?'selected':'' ?>>Purser</option>
                        <option value="cabin_crew" <?= $editData['role']=='cabin_crew'?'selected':'' ?>>Cabin Crew</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>License No (Optional)</label>
                    <input type="text" name="license_no" value="<?= htmlspecialchars($editData['license_no']) ?>">
                </div>
                <div class="form-group">
                    <label>Availability Status</label>
                    <select name="availability" required>
                        <option value="available" <?= $editData['availability']=='available'?'selected':'' ?>>Available</option>
                        <option value="on_duty" <?= $editData['availability']=='on_duty'?'selected':'' ?>>On Duty</option>
                        <option value="rest" <?= $editData['availability']=='rest'?'selected':'' ?>>Rest</option>
                        <option value="leave" <?= $editData['availability']=='leave'?'selected':'' ?>>On Leave</option>
                    </select>
                </div>
                <button type="submit" class="btn-primary mt-2">Update Member</button>
                <a href="manage_crew.php" class="btn-secondary" style="display: inline-block; padding: 10px 15px; margin-top: 10px; margin-left: 10px; text-decoration: none; border-radius: 8px;">Cancel</a>
            </form>
        <?php else: ?>
            <form method="POST" action="manage_crew.php">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label>Employee ID</label>
                    <input type="text" name="employee_id" placeholder="e.g. CPT005" required>
                </div>
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" placeholder="Enter full name" required>
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select name="role" required>
                        <option value="captain">Captain</option>
                        <option value="first_officer">First Officer</option>
                        <option value="purser">Purser</option>
                        <option value="cabin_crew">Cabin Crew</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>License No (Optional)</label>
                    <input type="text" name="license_no" placeholder="Enter license number">
                </div>
                <button type="submit" class="btn-primary mt-2">Add Member</button>
            </form>
        <?php endif; ?>
      </div>
    </div>

    <div class="card">
      <div class="card-header"><span class="card-title">Crew Roster</span></div>
      <div class="table-wrap">
        <table class="ams-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>NAME</th>
              <th>ROLE</th>
              <th>STATUS</th>
              <th>ACTION</th> 
            </tr>
          </thead>
          <tbody>
          <?php if(empty($crew)): ?>
              <tr><td colspan="5" style="text-align:center; padding:20px; color:var(--gray-500);">No crew members found.</td></tr>
          <?php else: ?>
              <?php foreach ($crew as $c): ?>
                <tr>
                  <td class="fw" style="color:var(--sky)"><?= htmlspecialchars($c['employee_id']) ?></td>
                  <td style="font-weight:500;"><?= htmlspecialchars($c['full_name']) ?></td>
                  <td><?= ucwords(str_replace('_', ' ', $c['role'])) ?></td>
                  <td>
                      <?php 
                        $statusClass = $c['availability']=='available' ? 'pill-success' : ($c['availability']=='on_duty' ? 'pill-info' : 'pill-warning');
                      ?>
                      <span class="pill <?= $statusClass ?>">
                          <?= ucfirst(str_replace('_', ' ', $c['availability'])) ?>
                      </span>
                  </td>
                  <td style="display: flex; gap: 5px;">
                      <a href="?edit_id=<?= $c['id'] ?>" class="btn-secondary btn-sm" style="text-decoration:none;">Edit</a>
                      <form method="POST" style="margin: 0;" onsubmit="return confirm('Are you sure you want to delete this crew member?');">
                          <input type="hidden" name="action" value="delete">
                          <input type="hidden" name="id" value="<?= $c['id'] ?>">
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