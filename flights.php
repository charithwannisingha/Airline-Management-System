<?php
// ============================================================
//  AMS — Search Flights (Nexus Airlines - Ultra Premium UI)
// ============================================================
require_once 'includes/auth.php';
requireLogin();
$db   = getDB();
$user = currentUser();

$results = [];
$searched = false;

// ප්‍රසිද්ධ ගුවන් තොටුපළවල් ලැයිස්තුව
$airports = [
    'CMB' => 'Colombo, Sri Lanka',
    'DXB' => 'Dubai, UAE',
    'LHR' => 'London, UK',
    'SIN' => 'Singapore',
    'KUL' => 'Kuala Lumpur, Malaysia',
    'JFK' => 'New York, USA',
    'SYD' => 'Sydney, Australia',
    'BKK' => 'Bangkok, Thailand',
    'NRT' => 'Tokyo, Japan',
    'MLE' => 'Male, Maldives',
    'DOH' => 'Doha, Qatar',
    'CDG' => 'Paris, France'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' || !empty($_GET['origin'])) {
    $searched = true;
    $origin      = strtoupper(trim($_POST['origin']      ?? $_GET['origin']      ?? ''));
    $destination = strtoupper(trim($_POST['destination'] ?? $_GET['destination'] ?? ''));
    $date        = trim($_POST['date']        ?? $_GET['date']        ?? '');
    $class       = trim($_POST['class']       ?? $_GET['class']       ?? 'economy');

    $sql = "
        SELECT f.*, a.model as aircraft_model, a.capacity,
               CASE '$class'
                 WHEN 'economy'  THEN f.price_economy
                 WHEN 'business' THEN f.price_business
                 WHEN 'first'    THEN f.price_first
                 ELSE f.price_economy
               END as display_price,
               (f.capacity - COALESCE(
                  (SELECT COUNT(*) FROM bookings WHERE flight_id=f.id AND status='confirmed'),0
               )) as seats_left
        FROM flights f
        LEFT JOIN aircraft a ON f.aircraft_id = a.id
        WHERE f.status != 'cancelled'
    ";
    $params = [];
    
    if ($origin && $origin !== 'ALL') { $sql .= " AND f.origin = ?"; $params[] = $origin; }
    if ($destination && $destination !== 'ALL') { $sql .= " AND f.destination = ?"; $params[] = $destination; }
    if ($date) { $sql .= " AND DATE(f.departure_time) = ?"; $params[] = $date; }
    
    $sql .= " ORDER BY f.departure_time ASC";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Search Flights · Nexus Airlines</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
  <style>
      /* --- Premium Hero Search Section --- */
      .hero-search {
          background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
          border-radius: 16px;
          padding: 30px;
          margin-bottom: 30px;
          box-shadow: 0 20px 40px rgba(0,0,0,0.15);
          color: white;
          position: relative;
          overflow: hidden;
      }
      .hero-search::before {
          content: '✈';
          position: absolute;
          right: -20px;
          top: -40px;
          font-size: 250px;
          color: rgba(255,255,255,0.03);
          transform: rotate(-15deg);
          pointer-events: none;
      }
      .hero-search-title {
          font-family: var(--font-head);
          font-size: 24px;
          font-weight: 700;
          margin-bottom: 20px;
          display: flex;
          align-items: center;
          gap: 10px;
      }
      .search-form-grid {
          display: grid;
          grid-template-columns: 1fr 1fr 1fr 1fr auto;
          gap: 15px;
          align-items: end;
          position: relative;
          z-index: 2;
      }
      .search-form-grid label {
          color: #94a3b8;
          font-weight: 700;
          font-size: 11px;
          text-transform: uppercase;
          margin-bottom: 8px;
          display: block;
          letter-spacing: 0.5px;
      }
      .search-form-grid select, .search-form-grid input {
          width: 100%;
          padding: 12px 15px;
          border-radius: 8px;
          border: 1px solid #334155;
          background-color: #1e293b !important;
          color: #ffffff !important;
          font-family: var(--font-body);
          font-size: 14px;
          font-weight: 500;
          transition: all 0.3s ease;
      }
      .search-form-grid select {
          appearance: none;
          background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
          background-repeat: no-repeat;
          background-position: right 15px center;
          background-size: 16px;
          cursor: pointer;
      }
      .search-form-grid select:focus, .search-form-grid input:focus {
          outline: none;
          border-color: #38bdf8;
          background-color: #0f172a !important;
          box-shadow: 0 0 0 4px rgba(56, 189, 248, 0.15);
      }
      .search-form-grid option { background-color: #0f172a; color: white; padding: 10px; }

      /* --- Destination Cards --- */
      .dest-grid {
          display: grid;
          grid-template-columns: repeat(3, 1fr);
          gap: 20px;
          margin-top: 15px;
      }
      .dest-card {
          border-radius: 16px;
          overflow: hidden;
          background: #fff;
          box-shadow: 0 10px 20px rgba(0,0,0,0.05);
          transition: all 0.4s ease;
          cursor: pointer;
          border: 1px solid var(--gray-200);
      }
      .dest-card:hover { transform: translateY(-8px); box-shadow: 0 20px 30px rgba(14, 165, 233, 0.15); border-color: #bae6fd; }
      .dest-bg { height: 140px; display: flex; align-items: center; justify-content: center; font-size: 50px; color: white; }
      .bg-dubai { background: linear-gradient(135deg, #f59e0b 0%, #ea580c 100%); }
      .bg-london { background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); }
      .bg-singapore { background: linear-gradient(135deg, #10b981 0%, #047857 100%); }
      .dest-content { padding: 20px; }
      .dest-city { font-family: var(--font-head); font-size: 20px; font-weight: 700; color: var(--navy); margin-bottom: 5px; }
      .dest-country { color: var(--gray-500); font-size: 13px; font-weight: 500; }
      .dest-price { margin-top: 15px; font-size: 14px; color: var(--sky); font-weight: 700; }

      /* --- NEW: Loyalty Banner --- */
      .promo-banner {
          margin-top: 40px;
          background: linear-gradient(135deg, #1e4d8c 0%, #0ea5e9 100%);
          border-radius: 16px;
          padding: 30px;
          color: white;
          display: flex;
          align-items: center;
          justify-content: space-between;
          box-shadow: 0 15px 30px rgba(14, 165, 233, 0.2);
      }
      .promo-text h3 { font-family: var(--font-head); font-size: 24px; margin-bottom: 8px; }
      .promo-text p { opacity: 0.9; font-size: 15px; font-weight: 500; margin: 0; }
      .btn-promo { background: white; color: #1e4d8c; padding: 12px 24px; border-radius: 8px; font-weight: 800; text-decoration: none; transition: 0.3s; }
      .btn-promo:hover { background: #f8fafc; box-shadow: 0 5px 15px rgba(0,0,0,0.2); transform: translateY(-2px); }

      /* --- NEW: Perks Section --- */
      .perks-section { margin-top: 40px; padding-top: 30px; border-top: 1px solid var(--gray-200); }
      .perks-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 25px; }
      .perk-card { text-align: center; padding: 30px 20px; background: #f8fafc; border-radius: 16px; transition: 0.3s; border: 1px solid transparent; }
      .perk-card:hover { transform: translateY(-5px); background: #ffffff; border-color: #e2e8f0; box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
      .perk-icon { font-size: 32px; margin-bottom: 15px; display: inline-block; width: 70px; height: 70px; line-height: 70px; background: #e0f2fe; color: #0284c7; border-radius: 50%; }
  </style>
</head>
<body>
<?php include 'includes/sidebar.php'; ?>

<div class="main-content">

<div id="pageLoader" class="loader-overlay">
    <div style="text-align: center;">
        <div class="spinner" style="margin: 0 auto;"></div>
        <p style="margin-top: 15px; font-weight: bold; color: var(--navy);">Searching Flights...</p>
    </div>
</div>

<header class="topbar">
  <div class="topbar-title">Flight Search</div>
  <div class="topbar-right">
    <div class="live-badge"><span class="pulse"></span> Live Availability</div>
  </div>
</header>

<main class="page-content">

  <div class="hero-search">
    <div class="hero-search-title">
        <span style="color: #38bdf8;">✈</span> Find Your Next Destination
    </div>
    <form method="POST" action="flights.php" id="searchForm">
      <div class="search-form-grid">
        <div class="form-group" style="margin:0">
          <label>Flying From</label>
          <select name="origin" required>
            <option value="ALL">Anywhere</option>
            <?php foreach($airports as $code => $name): ?>
                <option value="<?= $code ?>" <?= ($_POST['origin'] ?? '') === $code ? 'selected' : '' ?>><?= $name ?> (<?= $code ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group" style="margin:0">
          <label>Flying To</label>
          <select name="destination">
            <option value="ALL">Anywhere</option>
            <?php foreach($airports as $code => $name): ?>
                <option value="<?= $code ?>" <?= ($_POST['destination'] ?? '') === $code ? 'selected' : '' ?>><?= $name ?> (<?= $code ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group" style="margin:0">
          <label>Departure Date</label>
          <input type="date" name="date" value="<?= htmlspecialchars($_POST['date'] ?? $_GET['date'] ?? date('Y-m-d')) ?>">
        </div>
        <div class="form-group" style="margin:0">
          <label>Cabin Class</label>
          <select name="class">
            <option value="economy"  <?= ($_POST['class'] ?? '') === 'economy'  ? 'selected' : '' ?>>Economy Class</option>
            <option value="business" <?= ($_POST['class'] ?? '') === 'business' ? 'selected' : '' ?>>Business Class</option>
            <option value="first"    <?= ($_POST['class'] ?? '') === 'first'    ? 'selected' : '' ?>>First Class</option>
          </select>
        </div>
        <button type="submit" class="btn-primary" style="height:46px; padding:0 2rem; white-space:nowrap; background:#38bdf8; color:#0f172a; font-weight:800; border-radius:8px; transition: 0.3s;">Search ✈</button>
      </div>
    </form>
  </div>

  <?php if ($searched): ?>
    
    <div class="page-header" style="margin-bottom: 15px;">
      <p style="color: var(--gray-500); font-weight: bold;"><?= count($results) ?> flight<?= count($results) !== 1 ? 's' : '' ?> found for your search</p>
    </div>

    <?php if (empty($results)): ?>
      <div class="card" style="border: 2px dashed var(--gray-200); box-shadow: none;">
        <div class="card-body text-center" style="padding:4rem">
          <div style="font-size:3rem; margin-bottom:1rem; opacity: 0.5;">🛬</div>
          <h3 style="color:var(--navy)">No flights available</h3>
          <p class="text-muted mt-1">Try selecting different dates or destinations.</p>
        </div>
      </div>
    <?php else: ?>
      <?php foreach ($results as $i => $f):
        $dur = round((strtotime($f['arrival_time']) - strtotime($f['departure_time'])) / 3600, 1);
        $durH = floor($dur); $durM = round(($dur - $durH) * 60);
        $classLabel = ucfirst($_POST['class'] ?? 'economy');
        
        // Dynamic Pricing Logic
        if (function_exists('getDynamicPrice')) {
            $dynamicData = getDynamicPrice($f['display_price'], $f['departure_time'], $f['seats_left'], $f['capacity']);
            $finalPrice = $dynamicData['final_price'];
            $badges = $dynamicData['badges'];
        } else {
            $finalPrice = $f['display_price'];
            $badges = '';
        }
        
        $originCity = $airports[$f['origin']] ?? $f['origin'];
        $destCity = $airports[$f['destination']] ?? $f['destination'];
      ?>
      <div class="flight-result" style="animation-delay:<?= $i * .06 ?>s; border: 1px solid var(--gray-200);">
        <div class="route-display">
          <div style="text-align: center;">
            <div class="airport-big" style="color: var(--navy);"><?= htmlspecialchars($f['origin']) ?></div>
            <div style="font-size: 18px; font-weight: 800;"><?= date('H:i', strtotime($f['departure_time'])) ?></div>
            <div style="font-size: 11px; color: var(--gray-500); font-weight: bold; margin-top: 5px; text-transform: uppercase;"><?= htmlspecialchars(explode(',', $originCity)[0]) ?></div>
          </div>
          <div class="flight-connector" style="flex: 1; padding: 0 20px;">
            <div class="connector-plane" style="color: var(--sky);">✈</div>
            <div class="connector-line"></div>
            <div class="flight-meta" style="color: var(--gray-500);"><?= $durH ?>h <?= $durM ?>m · Direct</div>
            <div class="flight-meta" style="font-weight: bold;"><?= htmlspecialchars($f['flight_number']) ?></div>
          </div>
          <div style="text-align: center;">
            <div class="airport-big" style="color: var(--navy);"><?= htmlspecialchars($f['destination']) ?></div>
            <div style="font-size: 18px; font-weight: 800;"><?= date('H:i', strtotime($f['arrival_time'])) ?></div>
            <div style="font-size: 11px; color: var(--gray-500); font-weight: bold; margin-top: 5px; text-transform: uppercase;"><?= htmlspecialchars(explode(',', $destCity)[0]) ?></div>
          </div>
          <div style="margin-left:2rem; border-left: 1px solid var(--gray-200); padding-left: 1.5rem;">
            <div class="text-sm text-muted" style="font-weight:bold;"><?= htmlspecialchars($f['aircraft_model'] ?? 'Aircraft') ?></div>
            <div class="text-sm" style="color:<?= $f['seats_left'] < 5 ? 'var(--danger)' : 'var(--success)' ?>;font-weight:700;margin-top:4px">
              <?= $f['seats_left'] > 0 ? $f['seats_left'] . ' seats left' : 'Fully Booked' ?>
            </div>
            <div style="margin-top: 5px; font-size: 12px;"><?= $badges ?></div>
          </div>
        </div>
        
        <div class="flight-price-block" style="background: #f8fafc; padding: 20px; border-radius: 0 10px 10px 0;">
          <?php if($finalPrice > $f['display_price']): ?>
              <div style="text-decoration: line-through; color: var(--gray-400); font-size: 14px; font-weight:bold;">LKR <?= number_format($f['display_price']) ?></div>
          <?php endif; ?>
          <div class="price-big" style="color: var(--sky);">LKR <?= number_format($finalPrice) ?></div>
          <div class="price-class" style="color: var(--gray-500); font-weight:bold;"><?= $classLabel ?></div>
          
          <?php if ($f['seats_left'] > 0): ?>
          <a href="booking.php?flight_id=<?= $f['id'] ?>&class=<?= htmlspecialchars($_POST['class'] ?? 'economy') ?>"
             class="btn-accent" style="display:inline-block; margin-top:1rem; padding:12px 25px; border-radius:8px; font-size:14px; font-weight:800; color:white; text-decoration:none; box-shadow: 0 4px 10px rgba(14, 165, 233, 0.3); transition: 0.3s;">
            Book Now
          </a>
          <?php else: ?>
          <button class="btn-secondary btn-sm" style="margin-top:1rem" disabled>Fully Booked</button>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>

  <?php else: ?>
    <h3 style="color: var(--navy); font-family: var(--font-head); margin-top: 10px;">🌟 Featured Destinations</h3>
    <p style="color: var(--gray-500); font-size: 14px; margin-bottom: 20px;">Explore our most popular routes and start your next adventure.</p>
    
    <div class="dest-grid">
        <div class="dest-card" onclick="document.querySelector('select[name=destination]').value='DXB'; document.getElementById('searchForm').submit();">
            <div class="dest-bg bg-dubai">🏙️</div>
            <div class="dest-content">
                <div class="dest-city">Dubai (DXB)</div>
                <div class="dest-country">United Arab Emirates</div>
                <div class="dest-price">Flights from LKR 65,000 →</div>
            </div>
        </div>
        <div class="dest-card" onclick="document.querySelector('select[name=destination]').value='LHR'; document.getElementById('searchForm').submit();">
            <div class="dest-bg bg-london">🎡</div>
            <div class="dest-content">
                <div class="dest-city">London (LHR)</div>
                <div class="dest-country">United Kingdom</div>
                <div class="dest-price">Flights from LKR 185,000 →</div>
            </div>
        </div>
        <div class="dest-card" onclick="document.querySelector('select[name=destination]').value='SIN'; document.getElementById('searchForm').submit();">
            <div class="dest-bg bg-singapore">🌳</div>
            <div class="dest-content">
                <div class="dest-city">Singapore (SIN)</div>
                <div class="dest-country">Singapore</div>
                <div class="dest-price">Flights from LKR 75,000 →</div>
            </div>
        </div>
    </div>

    <div class="promo-banner">
        <div class="promo-text">
            <h3>👑 Join Nexus Loyalty Club</h3>
            <p>Earn points on every flight and unlock exclusive upgrades, lounge access, and free tickets.</p>
        </div>
        <a href="loyalty.php" class="btn-promo">Explore Benefits</a>
    </div>

    <div class="perks-section">
        <h3 style="color: var(--navy); font-family: var(--font-head); text-align: center; margin-bottom: 5px;">Why Fly Nexus Airlines?</h3>
        <p style="color: var(--gray-500); text-align: center; font-size: 14px;">Experience world-class hospitality in the skies.</p>
        
        <div class="perks-grid">
            <div class="perk-card">
                <div class="perk-icon">🧳</div>
                <h4 style="color: var(--navy); margin-bottom: 8px;">Generous Baggage</h4>
                <p style="color: var(--gray-500); font-size: 13px; line-height: 1.5;">Enjoy up to 30kg of checked baggage allowance on all Economy class flights.</p>
            </div>
            <div class="perk-card">
                <div class="perk-icon">🍽️</div>
                <h4 style="color: var(--navy); margin-bottom: 8px;">Gourmet Dining</h4>
                <p style="color: var(--gray-500); font-size: 13px; line-height: 1.5;">Savor complimentary world-class meals and beverages crafted by top chefs.</p>
            </div>
            <div class="perk-card">
                <div class="perk-icon">💺</div>
                <h4 style="color: var(--navy); margin-bottom: 8px;">Superior Comfort</h4>
                <p style="color: var(--gray-500); font-size: 13px; line-height: 1.5;">Relax with extra legroom and award-winning cabin service on every journey.</p>
            </div>
        </div>
    </div>

  <?php endif; ?>

</main>
</div>

<script src="js/app.js"></script>
<script>
    document.getElementById('searchForm').onsubmit = function() {
        document.getElementById('pageLoader').style.display = 'flex';
    };
</script>
</body>
</html>