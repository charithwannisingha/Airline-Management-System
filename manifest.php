<?php
require_once 'includes/auth.php';
requireLogin();
requireAdmin(); // ඇඩ්මින් පමණක් බව තහවුරු කිරීම
$db = getDB();

// URL එකෙන් flight_id එක ලබා ගැනීම
$flight_id = (int)($_GET['flight_id'] ?? 0);

if (!$flight_id) {
    header('Location: admin_flights.php');
    exit();
}

// ගුවන් ගමනේ විස්තර ලබා ගැනීම (Fetch Flight Details)
$stmt = $db->prepare("
    SELECT f.*, a.registration, a.model 
    FROM flights f 
    LEFT JOIN aircraft a ON f.aircraft_id = a.id 
    WHERE f.id = ?
");
$stmt->execute([$flight_id]);
$flight = $stmt->fetch();

if (!$flight) {
    die("ගුවන් ගමන සොයාගත නොහැක (Flight not found).");
}

// මෙම ගුවන් ගමන සඳහා වෙන්කරවාගෙන ඇති සියලුම මගීන්ගේ විස්තර ලබා ගැනීම (Fetch Passengers)
$stmt = $db->prepare("
    SELECT * FROM bookings 
    WHERE flight_id = ? AND status IN ('confirmed', 'checked_in', 'completed') 
    ORDER BY class ASC, seat_number ASC
");
$stmt->execute([$flight_id]);
$passengers = $stmt->fetchAll();

// මුළු මගීන් ගණන ගණනය කිරීම
$total_passengers = count($passengers);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Passenger Manifest - <?= htmlspecialchars($flight['flight_number']) ?> · AMS</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
  <style>
      /* මුද්‍රණය කරන විට (Print) වෙනස් විය යුතු CSS */
      @media print {
          .sidebar, .topbar, .btn-print, .no-print { display: none !important; }
          .page-content { margin: 0 !important; padding: 0 !important; width: 100% !important; }
          .card { box-shadow: none !important; border: none !important; }
          body { background: white; }
          @page { margin: 1cm; }
      }
      .manifest-header { display: flex; justify-content: space-between; border-bottom: 2px solid var(--gray-200); padding-bottom: 15px; margin-bottom: 20px; }
      .manifest-header h2 { margin: 0; color: var(--navy); font-family: var(--font-head); }
      .manifest-meta { font-size: 14px; color: var(--gray-600); }
      .manifest-meta strong { color: var(--navy); }
  </style>
</head>
<body>
<?php include 'includes/sidebar.php'; ?>

<header class="topbar">
  <div class="topbar-title">Passenger Manifest (මගී ලැයිස්තුව)</div>
  <div class="topbar-right">
      <a href="admin_flights.php" class="btn-secondary btn-sm no-print">← Back</a>
      <button onclick="window.print()" class="btn-primary btn-sm btn-print">🖨 Print Manifest</button>
  </div>
</header>

<main class="page-content">
  <div class="card">
    <div class="card-body">
        
      <div class="manifest-header">
          <div>
              <h2>Flight <?= htmlspecialchars($flight['flight_number']) ?> Manifest</h2>
              <div class="manifest-meta">
                  Route: <strong><?= htmlspecialchars($flight['origin']) ?> → <?= htmlspecialchars($flight['destination']) ?></strong><br>
                  Date & Time: <strong><?= date('d M Y, H:i', strtotime($flight['departure_time'])) ?></strong>
              </div>
          </div>
          <div style="text-align: right;">
              <div class="manifest-meta">
                  Aircraft: <strong><?= htmlspecialchars($flight['registration']) ?> (<?= htmlspecialchars($flight['model']) ?>)</strong><br>
                  Total Passengers: <strong><?= $total_passengers ?> / <?= $flight['capacity'] ?></strong><br>
                  Status: <strong style="text-transform: uppercase;"><?= $flight['status'] ?></strong>
              </div>
          </div>
      </div>

      <div class="table-wrap">
        <table class="ams-table" style="width: 100%;">
          <thead>
            <tr>
              <th>Seat</th>
              <th>Class</th>
              <th>Passenger Name</th>
              <th>Document (NIC/Passport)</th>
              <th>Booking Ref</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
          <?php if (empty($passengers)): ?>
            <tr>
                <td colspan="6" style="text-align: center; padding: 20px; color: var(--gray-600);">No passengers booked yet. (තවමත් මගීන් වෙන්කරවාගෙන නොමැත)</td>
            </tr>
          <?php else: ?>
              <?php foreach ($passengers as $p): ?>
                <tr>
                  <td class="fw" style="font-size: 16px; color: var(--sky);"><?= htmlspecialchars($p['seat_number']) ?></td>
                  <td style="text-transform: capitalize;"><?= htmlspecialchars($p['class']) ?></td>
                  <td class="fw"><?= htmlspecialchars($p['passenger_first'] . ' ' . $p['passenger_last']) ?></td>
                  <td><?= strtoupper($p['doc_type']) ?>: <?= htmlspecialchars($p['doc_number']) ?></td>
                  <td style="font-family: monospace; font-size: 14px;"><?= htmlspecialchars($p['booking_ref']) ?></td>
                  <td>
                      <span class="pill <?= $p['status'] == 'checked_in' ? 'pill-info' : 'pill-success' ?>">
                          <?= ucfirst($p['status']) ?>
                      </span>
                  </td>
                </tr>
              <?php endforeach; ?>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
      
      <div style="margin-top: 30px; font-size: 12px; color: var(--gray-600); text-align: center; border-top: 1px dashed #ccc; padding-top: 10px;">
          Generated on <?= date('Y-m-d H:i:s') ?> · Nexus Airlines Management System
      </div>

    </div>
  </div>
</main>
</div></div>
</body>
</html>