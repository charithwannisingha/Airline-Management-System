<?php
// ============================================================
//  AMS — Manage Flights (Nexus Airlines) - Logic Intact
// ============================================================
require_once 'includes/auth.php';
requireLogin();
requireAdmin(); // ඇඩ්මින් පමණක් බව තහවුරු කිරීම
$db = getDB();

$msg = '';

// 1. ගුවන් ගමනක් මකා දැමීම (Delete Flight)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = (int)$_POST['id'];
    $stmt = $db->prepare("DELETE FROM flights WHERE id = ?");
    if($stmt->execute([$id])) {
        $msg = "Flight deleted successfully.";
    }
}

// 2. ගුවන් ගමනක් යාවත්කාලීන කිරීම (Update Flight)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $id = (int)$_POST['id'];
    $flight_no = trim($_POST['flight_number']);
    $aircraft_id = (int)$_POST['aircraft_id'];
    $origin = strtoupper(trim($_POST['origin']));
    $destination = strtoupper(trim($_POST['destination']));
    $dep_time = $_POST['departure_time'];
    $arr_time = $_POST['arrival_time'];
    $price_eco = (float)$_POST['price_economy'];
    $status = $_POST['status'];

    $stmt = $db->prepare("UPDATE flights SET flight_number=?, aircraft_id=?, origin=?, destination=?, departure_time=?, arrival_time=?, price_economy=?, status=? WHERE id=?");
    if($stmt->execute([$flight_no, $aircraft_id, $origin, $destination, $dep_time, $arr_time, $price_eco, $status, $id])) {
        $msg = "Flight details updated successfully.";
    }
}

// 3. අලුත් Flight එකක් Database එකට ඇතුළත් කිරීම (Add Flight)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $flight_no = trim($_POST['flight_number']);
    $aircraft_id = (int)$_POST['aircraft_id'];
    $origin = strtoupper(trim($_POST['origin']));
    $destination = strtoupper(trim($_POST['destination']));
    $dep_time = $_POST['departure_time'];
    $arr_time = $_POST['arrival_time'];
    $price_eco = (float)$_POST['price_economy'];

    $stmt = $db->prepare("INSERT INTO flights (flight_number, aircraft_id, origin, destination, departure_time, arrival_time, price_economy, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'scheduled')");
    
    if($stmt->execute([$flight_no, $aircraft_id, $origin, $destination, $dep_time, $arr_time, $price_eco])) {
        $msg = "New flight scheduled successfully.";
    }
}

// Dropdown එක සඳහා ගුවන් යානා ලැයිස්තුව ගැනීම
$aircrafts = $db->query("SELECT id, registration, model FROM aircraft")->fetchAll();

// ෂෙඩියුල් කර ඇති ගුවන් ගමන් ලැයිස්තුව ගැනීම
$flights = $db->query("
    SELECT f.*, a.registration 
    FROM flights f 
    LEFT JOIN aircraft a ON f.aircraft_id = a.id 
    ORDER BY f.departure_time DESC
")->fetchAll();

// Edit බොත්තම එබූ විට, අදාළ ගුවන් ගමනේ විස්තර ලබා ගැනීම
$editData = null;
if (isset($_GET['edit_id'])) {
    $stmt = $db->prepare("SELECT * FROM flights WHERE id = ?");
    $stmt->execute([(int)$_GET['edit_id']]);
    $editData = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Flights · Nexus Airlines</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'includes/sidebar.php'; ?>

<div class="main-content">

<header class="topbar">
  <div class="topbar-title">Manage Flights</div>
</header>

<main class="page-content">
  <?php if(!empty($msg)): ?>
      <div class="alert alert-success" style="margin-bottom: 20px; padding: 15px; background: #d1e7dd; color: #0f5132; border-radius: 8px; font-weight: bold;"><?= $msg ?></div>
  <?php endif; ?>

  <div class="card mb-3">
    <div class="card-header">
        <span class="card-title">
            <?= $editData ? 'Edit Flight Details' : 'Schedule New Flight' ?>
        </span>
    </div>
    <div class="card-body">
      <?php if($editData): ?>
          <form method="POST">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?= $editData['id'] ?>">
            <div class="grid-2">
                <div class="form-group">
                    <label>Flight Number</label>
                    <input type="text" name="flight_number" value="<?= htmlspecialchars($editData['flight_number']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Assign Aircraft</label>
                    <select name="aircraft_id" required>
                        <?php foreach($aircrafts as $a): ?>
                            <option value="<?= $a['id'] ?>" <?= $editData['aircraft_id'] == $a['id'] ? 'selected' : '' ?>><?= htmlspecialchars($a['registration']) ?> (<?= htmlspecialchars($a['model']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Origin (IATA)</label>
                    <input type="text" name="origin" maxlength="3" value="<?= htmlspecialchars($editData['origin']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Destination (IATA)</label>
                    <input type="text" name="destination" maxlength="3" value="<?= htmlspecialchars($editData['destination']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Departure Time</label>
                    <input type="datetime-local" name="departure_time" value="<?= date('Y-m-d\TH:i', strtotime($editData['departure_time'])) ?>" required>
                </div>
                <div class="form-group">
                    <label>Arrival Time</label>
                    <input type="datetime-local" name="arrival_time" value="<?= date('Y-m-d\TH:i', strtotime($editData['arrival_time'])) ?>" required>
                </div>
                <div class="form-group">
                    <label>Economy Price (LKR)</label>
                    <input type="number" name="price_economy" value="<?= $editData['price_economy'] ?>" required>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="scheduled" <?= $editData['status'] == 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                        <option value="boarding" <?= $editData['status'] == 'boarding' ? 'selected' : '' ?>>Boarding</option>
                        <option value="on_time" <?= $editData['status'] == 'on_time' ? 'selected' : '' ?>>On Time</option>
                        <option value="delayed" <?= $editData['status'] == 'delayed' ? 'selected' : '' ?>>Delayed</option>
                        <option value="cancelled" <?= $editData['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        <option value="completed" <?= $editData['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn-primary mt-2">Update Flight</button>
            <a href="manage_flights.php" class="btn-secondary" style="display:inline-block; padding:9px 15px; margin-top:10px; margin-left: 10px; text-decoration:none; border-radius: 8px;">Cancel</a>
          </form>
      <?php else: ?>
          <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="grid-2">
                <div class="form-group">
                    <label>Flight Number (e.g., NX105)</label>
                    <input type="text" name="flight_number" required>
                </div>
                <div class="form-group">
                    <label>Assign Aircraft</label>
                    <select name="aircraft_id" required>
                        <?php foreach($aircrafts as $a): ?>
                            <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['registration']) ?> (<?= htmlspecialchars($a['model']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Origin (IATA)</label>
                    <input type="text" name="origin" maxlength="3" required style="text-transform: uppercase;">
                </div>
                <div class="form-group">
                    <label>Destination (IATA)</label>
                    <input type="text" name="destination" maxlength="3" required style="text-transform: uppercase;">
                </div>
                <div class="form-group">
                    <label>Departure Time</label>
                    <input type="datetime-local" name="departure_time" required>
                </div>
                <div class="form-group">
                    <label>Arrival Time</label>
                    <input type="datetime-local" name="arrival_time" required>
                </div>
                <div class="form-group">
                    <label>Economy Price (LKR)</label>
                    <input type="number" name="price_economy" required>
                </div>
            </div>
            <button type="submit" class="btn-primary mt-2">Schedule Flight</button>
          </form>
      <?php endif; ?>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><span class="card-title">All Scheduled Flights</span></div>
    <div class="table-wrap">
      <table class="ams-table">
        <thead>
          <tr>
            <th>FLIGHT</th>
            <th>ROUTE</th>
            <th>DEPARTURE</th>
            <th>AIRCRAFT</th>
            <th>STATUS</th>
            <th>ACTION</th> 
          </tr>
        </thead>
        <tbody>
        <?php if(empty($flights)): ?>
            <tr><td colspan="6" style="text-align:center; padding:20px; color:var(--gray-500);">No scheduled flights found.</td></tr>
        <?php else: ?>
            <?php foreach ($flights as $f): ?>
            <tr>
                <td class="fw" style="color:var(--sky)"><?= htmlspecialchars($f['flight_number']) ?></td>
                <td><strong><?= htmlspecialchars($f['origin']) ?> → <?= htmlspecialchars($f['destination']) ?></strong></td>
                <td><?= date('d M Y, H:i', strtotime($f['departure_time'])) ?></td>
                <td><?= htmlspecialchars($f['registration'] ?? 'N/A') ?></td>
                <td>
                    <?php
                        $statusClass = 'pill-info';
                        if ($f['status'] == 'completed') $statusClass = 'pill-success';
                        if ($f['status'] == 'delayed') $statusClass = 'pill-warning';
                        if ($f['status'] == 'cancelled') $statusClass = 'pill-danger';
                    ?>
                    <span class="pill <?= $statusClass ?>"><?= ucfirst(str_replace('_', ' ', $f['status'])) ?></span>
                </td>
                <td style="display: flex; gap: 5px;">
                    <a href="manifest.php?flight_id=<?= $f['id'] ?>" class="btn-secondary btn-sm" style="text-decoration: none;">Manifest</a>
                    <a href="?edit_id=<?= $f['id'] ?>" class="btn-secondary btn-sm" style="text-decoration: none;">Edit</a>
                    <form method="POST" style="margin: 0;" onsubmit="return confirm('Are you sure you want to delete this flight?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $f['id'] ?>">
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
</main>

</div> <script src="js/app.js"></script>
</body>
</html>