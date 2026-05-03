<?php
// ============================================================
//  AMS — Book Flight & Interactive Seat Map
// ============================================================
require_once 'includes/auth.php';
requireLogin();
$db   = getDB();
$user = currentUser();

$flightId = (int)($_GET['flight_id'] ?? 0);
$class    = htmlspecialchars($_GET['class'] ?? 'economy');

if (!$flightId) {
    header('Location: flights.php');
    exit();
}

$flight = $db->prepare("
    SELECT f.*, a.model as aircraft_model, a.capacity
    FROM flights f
    LEFT JOIN aircraft a ON f.aircraft_id = a.id
    WHERE f.id = ?
");
$flight->execute([$flightId]);
$flight = $flight->fetch();

if (!$flight) {
    header('Location: flights.php?error=Flight+not+found');
    exit();
}

// වෙන්කර ඇති ආසන ලබා ගැනීම
$takenSeats = $db->prepare("SELECT seat_number FROM bookings WHERE flight_id = ? AND status = 'confirmed'");
$takenSeats->execute([$flightId]);
$takenSeats = array_column($takenSeats->fetchAll(), 'seat_number');

$basePrice = match($class) {
    'business' => $flight['price_business'],
    'first'    => $flight['price_first'],
    default    => $flight['price_economy'],
};

// Dynamic Pricing
$seatsLeft = $flight['capacity'] - count($takenSeats);
$dynamicData = getDynamicPrice($basePrice, $flight['departure_time'], $seatsLeft, $flight['capacity']);
$price = $dynamicData['final_price']; 
$badges = $dynamicData['badges'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Book Flight · AMS</title>
  <link rel="stylesheet" href="css/style.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
      .payment-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
      .secure-badge { background: #e0f2fe; color: #0284c7; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; }
      .pay-box { background: #f8fafc; border: 1px solid var(--gray-200); border-radius: 8px; padding: 20px; margin-bottom: 20px; }
  </style>
</head>
<body>
<?php include 'includes/sidebar.php'; ?>

<div class="main-content">

<header class="topbar">
  <div class="topbar-title">Book Flight — <?= htmlspecialchars($flight['flight_number']) ?></div>
  <div class="topbar-right">
    <a href="flights.php" class="btn-secondary btn-sm">← Back</a>
  </div>
</header>

<main class="page-content">
  <div class="card mb-3" style="border-left:4px solid var(--sky-light)">
    <div class="card-body" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem">
      <div style="display:flex;align-items:center;gap:2rem">
        <div style="text-align:center">
          <div style="font-size:28px;font-weight:800;color:var(--navy)"><?= htmlspecialchars($flight['origin']) ?></div>
        </div>
        <div style="text-align:center; color:var(--sky-light);font-size:20px">✈ ─────────── ✈</div>
        <div style="text-align:center">
          <div style="font-size:28px;font-weight:800;color:var(--navy)"><?= htmlspecialchars($flight['destination']) ?></div>
        </div>
      </div>
      <div style="text-align:right">
        <div style="font-size:22px;font-weight:800;color:var(--sky)">LKR <?= number_format($price) ?></div>
        <div style="margin-top: 5px;"><?= $badges ?></div>
      </div>
    </div>
  </div>

  <form id="bookingForm">
    <input type="hidden" name="flight_id" value="<?= $flightId ?>">
    <input type="hidden" name="class" value="<?= $class ?>">
    <input type="hidden" name="seat_number" id="selectedSeat" value="">
    <input type="hidden" name="price" value="<?= $price ?>">

    <div class="grid-2">
      <div class="card">
        <div class="card-header"><span class="card-title">Passenger & Payment</span></div>
        <div class="card-body">
          <div class="grid-2">
            <div class="form-group"><label>First Name</label><input type="text" name="first_name" required></div>
            <div class="form-group"><label>Last Name</label><input type="text" name="last_name" required></div>
          </div>
          <div class="form-group"><label>Email</label><input type="email" name="email" value="<?= htmlspecialchars($user['username'] ?? '') ?>" required></div>
          <div class="grid-2">
            <div class="form-group">
              <label>Document Type</label>
              <select name="doc_type">
                <option value="nic">NIC</option>
                <option value="passport">Passport</option>
              </select>
            </div>
            <div class="form-group"><label>Doc Number</label><input type="text" name="doc_number" required></div>
          </div>

          <hr class="divider">
          
          <div class="payment-header">
            <h3>Payment Method</h3>
            <div class="secure-badge">🔒 Secure</div>
          </div>
          <div class="pay-box">
              <div class="form-group"><label>Card Number</label><input type="text" placeholder="0000 0000 0000 0000" maxlength="19"></div>
              <div class="grid-2">
                <div class="form-group"><label>Expiry</label><input type="text" placeholder="MM/YY"></div>
                <div class="form-group"><label>CVV</label><input type="password" placeholder="•••"></div>
              </div>
          </div>
          <button type="submit" class="btn-primary btn-full">Pay LKR <?= number_format($price) ?> & Confirm</button>
        </div>
      </div>

      <div class="card">
        <div class="card-header"><span class="card-title">Select Your Seat</span></div>
        <div class="card-body" style="background: #f1f5f9; border-radius: 0 0 10px 10px; padding-bottom: 30px;">
          
          <div class="plane-fuselage">
              <div class="seat-map-grid" id="seatMap"></div>
          </div>
          
          <div style="text-align:center; margin-top:20px;">
              <div class="text-sm text-muted">Selected Seat</div>
              <strong id="seatDisplay" style="font-size: 24px; color: var(--sky);">None</strong>
          </div>

        </div>
      </div>
    </div>
  </form>
</main>

</div></div> <script>
// Seat Map Generator
(function() {
  const map = document.getElementById('seatMap');
  const input = document.getElementById('selectedSeat');
  const display = document.getElementById('seatDisplay');
  const taken = <?= json_encode($takenSeats) ?>;
  const cols = ['A','B','C','','D','E','F']; // Middle aisle
  
  for (let r = 1; r <= 8; r++) { // 8 Rows
    cols.forEach(c => {
      const div = document.createElement('div');
      if (c === '') { 
          div.className = 'seat aisle'; 
          map.appendChild(div); 
          return; 
      }
      const id = r + c;
      div.className = 'seat' + (taken.includes(id) ? ' taken' : '');
      div.textContent = id;
      
      if (!taken.includes(id)) {
        div.onclick = () => {
          document.querySelectorAll('.seat.selected').forEach(s => s.classList.remove('selected'));
          div.classList.add('selected'); 
          input.value = id; 
          display.textContent = id;
        };
      }
      map.appendChild(div);
    });
  }
})();

// AJAX Submit with SweetAlert2
document.getElementById('bookingForm').onsubmit = function(e) {
    e.preventDefault();
    
    // Check if seat is selected
    if (!document.getElementById('selectedSeat').value) {
        Swal.fire({
            icon: 'warning', 
            title: 'No Seat Selected', 
            text: 'Please select a seat from the airplane map!',
            confirmButtonColor: '#0ea5e9'
        });
        return;
    }
    
    // Show Loading Screen
    Swal.fire({ 
        title: 'Processing Payment...', 
        html: 'Securely authenticating your payment.<br><b>Please do not close this window.</b>',
        allowOutsideClick: false, 
        didOpen: () => { Swal.showLoading(); } 
    });

    // Send data to PHP backend
    fetch('php/book.php', { method: 'POST', body: new FormData(this) })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Swal.fire({ 
                icon: 'success', 
                title: 'Success!', 
                text: data.message, 
                showConfirmButton: false, 
                timer: 2500 
            }).then(() => { 
                window.location.href = 'e_ticket.php?booking_id=' + data.booking_id; 
            });
        } else {
            Swal.fire({ 
                icon: 'error', 
                title: 'Payment Failed', 
                text: data.message,
                confirmButtonColor: '#e05252'
            });
        }
    })
    .catch(() => {
        Swal.fire({ icon: 'error', title: 'Network Error', text: 'Something went wrong.'});
    });
};
</script>
</body>
</html>