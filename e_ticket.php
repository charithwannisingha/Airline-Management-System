<?php
require_once 'includes/auth.php';
requireLogin();
$db = getDB();
$user = currentUser();

// URL එකෙන් booking_id එක ලබා ගැනීම
$booking_id = (int)($_GET['booking_id'] ?? 0);

if (!$booking_id) {
    header('Location: my_bookings.php');
    exit();
}

// Booking එකට අදාළ සියලුම විස්තර ලබා ගැනීම
$stmt = $db->prepare("
    SELECT b.*, f.flight_number, f.origin, f.destination, f.departure_time, f.arrival_time, 
           a.model as aircraft_model, a.registration
    FROM bookings b
    JOIN flights f ON b.flight_id = f.id
    LEFT JOIN aircraft a ON f.aircraft_id = a.id
    WHERE b.id = ? AND b.user_id = ?
");
$stmt->execute([$booking_id, $user['id']]);
$ticket = $stmt->fetch();

if (!$ticket) {
    die("ප්‍රවේශපත්‍රය සොයාගත නොහැක හෝ ඔබට මෙය බැලීමට අවසර නැත.");
}

// ගුවන් ගමන් කාලය (Duration) ගණනය කිරීම
$dep = new DateTime($ticket['departure_time']);
$arr = new DateTime($ticket['arrival_time']);
$interval = $dep->diff($arr);
$duration = $interval->format('%hH %IM');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>E-Ticket - <?= htmlspecialchars($ticket['booking_ref']) ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
  <style>
    :root {
        --sky: #0ea5e9; --sky-dark: #0284c7; --navy: #0f172a; 
        --gray: #f1f5f9; --text: #334155; --muted: #64748b;
    }
    body { font-family: 'DM Sans', sans-serif; background: #e2e8f0; margin: 0; padding: 40px; color: var(--text); }
    
    /* Toolbar for Print/Back buttons */
    .toolbar { max-width: 800px; margin: 0 auto 20px; display: flex; justify-content: space-between; }
    .btn { padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: bold; cursor: pointer; border: none; font-family: inherit; }
    .btn-back { background: white; color: var(--navy); border: 1px solid #cbd5e1; }
    .btn-print { background: var(--sky); color: white; }
    
    /* Main Ticket Container */
    .ticket-wrapper { max-width: 800px; margin: 0 auto; display: flex; background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.1); position: relative; }
    
    /* Left Side - Main Details */
    .ticket-main { flex: 3; padding: 30px; border-right: 2px dashed #cbd5e1; position: relative; background: #ffffff; }
    .ticket-main::after, .ticket-main::before { content: ''; position: absolute; right: -15px; width: 30px; height: 30px; background: #e2e8f0; border-radius: 50%; z-index: 10; }
    .ticket-main::before { top: -15px; }
    .ticket-main::after { bottom: -15px; }

    /* Right Side - Stub */
    .ticket-stub { flex: 1; padding: 30px; background: #f8fafc; display: flex; flex-direction: column; justify-content: space-between; align-items: center; text-align: center; }

    /* Header */
    .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid var(--gray); padding-bottom: 15px; margin-bottom: 20px; }
    .airline-name { font-family: 'Syne', sans-serif; font-size: 24px; font-weight: 800; color: var(--navy); margin: 0; }
    .boarding-pass-title { color: var(--sky); font-weight: bold; font-size: 14px; text-transform: uppercase; letter-spacing: 2px; }

    /* Route Display */
    .route { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    .city { text-align: center; }
    .city h1 { margin: 0; font-family: 'Syne', sans-serif; font-size: 48px; color: var(--navy); line-height: 1; }
    .city p { margin: 5px 0 0; color: var(--muted); font-size: 14px; }
    .flight-path { flex: 1; text-align: center; position: relative; margin: 0 20px; }
    .flight-path::before { content: ''; position: absolute; top: 50%; left: 0; right: 0; border-top: 2px dashed #cbd5e1; transform: translateY(-50%); z-index: 1; }
    .plane-icon { position: relative; z-index: 2; background: white; padding: 0 10px; font-size: 24px; color: var(--sky); }

    /* Info Grid */
    .info-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 20px; }
    .info-item label { display: block; font-size: 11px; text-transform: uppercase; color: var(--muted); font-weight: bold; letter-spacing: 1px; margin-bottom: 4px; }
    .info-item span { display: block; font-size: 16px; font-weight: bold; color: var(--navy); }

    /* Barcode/QR Mockup */
    .barcode { font-family: 'Libre Barcode 39', cursive, sans-serif; font-size: 40px; letter-spacing: 2px; margin-top: 10px; opacity: 0.8; }
    .qr-mock { width: 100px; height: 100px; background: #cbd5e1; margin: 10px auto; display: grid; place-items: center; font-size: 40px; border-radius: 8px; }

    /* Warning */
    .ticket-footer { background: #eff6ff; padding: 15px; border-radius: 8px; font-size: 12px; color: var(--sky-dark); margin-top: 20px; }

    /* Print Styles */
    @media print {
        body { background: white; padding: 0; margin: 0; }
        .toolbar { display: none; }
        .ticket-wrapper { box-shadow: none; border: 1px solid #cbd5e1; margin: 20px auto; }
        .ticket-main::after, .ticket-main::before { background: white; border: 1px solid #cbd5e1; border-left: none; }
    }
  </style>
</head>
<body>

  <div class="toolbar">
      <a href="my_bookings.php" class="btn btn-back">← Back to Bookings</a>
      <button onclick="window.print()" class="btn btn-print">🖨 Print / PDF E-Ticket</button>
  </div>

  <div class="ticket-wrapper">
      
      <div class="ticket-main">
          <div class="header">
              <h1 class="airline-name">✈ Nexus Airlines</h1>
              <div class="boarding-pass-title">Boarding Pass</div>
          </div>

          <div class="route">
              <div class="city">
                  <h1><?= htmlspecialchars($ticket['origin']) ?></h1>
                  <p>Departure</p>
              </div>
              <div class="flight-path">
                  <span class="plane-icon">✈</span>
                  <div style="font-size: 12px; color: var(--muted); margin-top: 5px; font-weight: bold; background: white; position: relative; z-index: 2; display: inline-block; padding: 0 5px;"><?= $duration ?></div>
              </div>
              <div class="city">
                  <h1><?= htmlspecialchars($ticket['destination']) ?></h1>
                  <p>Arrival</p>
              </div>
          </div>

          <div class="info-grid">
              <div class="info-item">
                  <label>Passenger Name</label>
                  <span><?= htmlspecialchars(strtoupper($ticket['passenger_first'] . ' ' . $ticket['passenger_last'])) ?></span>
              </div>
              <div class="info-item">
                  <label>Flight No</label>
                  <span><?= htmlspecialchars($ticket['flight_number']) ?></span>
              </div>
              <div class="info-item">
                  <label>Date</label>
                  <span><?= date('d M Y', strtotime($ticket['departure_time'])) ?></span>
              </div>
              
              <div class="info-item">
                  <label>Departure Time</label>
                  <span><?= date('H:i', strtotime($ticket['departure_time'])) ?></span>
              </div>
              <div class="info-item">
                  <label>Class</label>
                  <span style="text-transform: capitalize;"><?= htmlspecialchars($ticket['class']) ?></span>
              </div>
              <div class="info-item">
                  <label>Seat</label>
                  <span style="font-size: 22px; color: var(--sky);"><?= htmlspecialchars($ticket['seat_number']) ?></span>
              </div>
          </div>

          <div class="ticket-footer">
              <strong>Important:</strong> Gates close 30 minutes before departure. Please bring your <?= strtoupper($ticket['doc_type']) ?> (<?= htmlspecialchars($ticket['doc_number']) ?>) for verification.
          </div>
      </div>

      <div class="ticket-stub">
          <div>
              <h2 style="font-family: 'Syne', sans-serif; font-size: 18px; margin: 0; color: var(--navy);">Nexus Airlines</h2>
              <div style="font-size: 10px; font-weight: bold; color: var(--muted); text-transform: uppercase;">Economy Class</div>
          </div>
          
          <div style="text-align: left; width: 100%; margin: 20px 0;">
              <div class="info-item" style="margin-bottom: 15px;">
                  <label>Passenger</label>
                  <span><?= htmlspecialchars(strtoupper($ticket['passenger_last'])) ?></span>
              </div>
              <div class="info-item" style="margin-bottom: 15px;">
                  <label>Flight</label>
                  <span><?= htmlspecialchars($ticket['flight_number']) ?></span>
              </div>
              <div class="info-item">
                  <label>Seat</label>
                  <span style="font-size: 24px; color: var(--sky);"><?= htmlspecialchars($ticket['seat_number']) ?></span>
              </div>
          </div>

          <div>
              <div class="qr-mock">▣</div>
              <div style="font-family: monospace; font-size: 12px; font-weight: bold; margin-top: 5px;"><?= htmlspecialchars($ticket['booking_ref']) ?></div>
          </div>
      </div>

  </div>

</body>
</html>