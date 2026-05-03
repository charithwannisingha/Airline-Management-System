<?php
// ============================================================
//  Nexus Airlines — Admin Dashboard with Chart.js Analytics
// ============================================================
require_once 'includes/auth.php';
requireLogin();
requireAdmin();
$db   = getDB();
$user = currentUser();

// Fetch KPI Data
$totalFlights   = $db->query("SELECT COUNT(*) FROM flights WHERE DATE(departure_time) = CURDATE()")->fetchColumn();
$totalBookings  = $db->query("SELECT COUNT(*) FROM bookings WHERE DATE(created_at) = CURDATE()")->fetchColumn();
$totalRevenue   = $db->query("SELECT COALESCE(SUM(total_price),0) FROM bookings WHERE status='confirmed'")->fetchColumn();
$onTimeRate     = 91; // Example static value for demonstration

// Fetch last 7 days data for charts
$chartQuery = $db->query("
    SELECT DATE(created_at) as date, COUNT(*) as total_bookings, COALESCE(SUM(total_price), 0) as total_revenue
    FROM bookings
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    GROUP BY DATE(created_at)
    ORDER BY DATE(created_at) ASC
")->fetchAll();

$dates = [];
$bookingsData = [];
$revenueData = [];

// Create arrays for the past 7 days
for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $dates[] = date('M d', strtotime($d)); // e.g., 'Aug 15'
    
    $found = false;
    foreach ($chartQuery as $row) {
        if ($row['date'] === $d) {
            $bookingsData[] = (int)$row['total_bookings'];
            $revenueData[] = (float)$row['total_revenue'];
            $found = true;
            break;
        }
    }
    if (!$found) {
        $bookingsData[] = 0;
        $revenueData[] = 0;
    }
}

// Fetch recent bookings
$recentBookings = $db->query("
    SELECT b.id, b.booking_ref, u.full_name, f.flight_number,
           f.origin, f.destination, b.total_price, b.status, b.created_at
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN flights f ON b.flight_id = f.id
    ORDER BY b.created_at DESC LIMIT 5
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard · Nexus Airlines</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<?php include 'includes/sidebar.php'; ?>

<div class="main-content">

<header class="topbar">
  <div class="topbar-title">Dashboard Overview</div>
  <div class="topbar-right">
    <span class="user-badge" style="background:#e0f2fe; color:#0284c7; padding:5px 15px; border-radius:20px; font-weight:bold;">
        👋 Welcome back, <?= htmlspecialchars($user['name']) ?>
    </span>
  </div>
</header>

<main class="page-content">
  <div class="grid-4" style="margin-bottom: 20px;">
    <div class="card kpi-card delay-1">
      <div class="kpi-title">Today's Flights</div>
      <div class="kpi-value"><?= $totalFlights ?></div>
      <div class="kpi-trend trend-up">↗ +2 from yesterday</div>
    </div>
    <div class="card kpi-card delay-2">
      <div class="kpi-title">Today's Bookings</div>
      <div class="kpi-value"><?= $totalBookings ?></div>
      <div class="kpi-trend trend-up">↗ +15% vs last week</div>
    </div>
    <div class="card kpi-card delay-3">
      <div class="kpi-title">Total Revenue (LKR)</div>
      <div class="kpi-value"><?= number_format($totalRevenue) ?></div>
      <div class="kpi-trend trend-up">↗ Steady growth</div>
    </div>
    <div class="card kpi-card delay-4">
      <div class="kpi-title">On-Time Performance</div>
      <div class="kpi-value"><?= $onTimeRate ?>%</div>
      <div class="kpi-trend trend-down">↘ -2% due to weather</div>
    </div>
  </div>

  <div class="grid-2" style="margin-bottom: 20px;">
    <div class="card">
        <div class="card-header"><span class="card-title">Weekly Revenue (LKR)</span></div>
        <div class="card-body">
            <canvas id="revenueChart" height="250"></canvas>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><span class="card-title">Weekly Ticket Bookings</span></div>
        <div class="card-body">
            <canvas id="bookingsChart" height="250"></canvas>
        </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header" style="display:flex;justify-content:space-between">
      <span class="card-title">Recent Bookings</span>
      <a href="bookings.php" class="btn-secondary btn-sm">View All</a>
    </div>
    <div class="table-wrap">
      <table class="ams-table">
        <thead>
          <tr><th>REF</th><th>PASSENGER</th><th>FLIGHT</th><th>ROUTE</th><th>PRICE (LKR)</th><th>STATUS</th><th>DATE</th></tr>
        </thead>
        <tbody>
        <?php if (empty($recentBookings)): ?>
          <tr><td colspan="7" class="text-center text-muted" style="padding:2rem">No bookings yet</td></tr>
        <?php else: ?>
          <?php foreach ($recentBookings as $b):
            $pc = $b['status'] === 'confirmed' ? 'pill-success' : ($b['status'] === 'cancelled' ? 'pill-danger' : 'pill-warning');
          ?>
          <tr>
            <td class="fw" style="color:var(--sky)"><?= htmlspecialchars($b['booking_ref']) ?></td>
            <td style="font-weight:500;"><?= htmlspecialchars($b['full_name']) ?></td>
            <td class="fw"><?= htmlspecialchars($b['flight_number']) ?></td>
            <td><?= htmlspecialchars($b['origin']) ?> → <?= htmlspecialchars($b['destination']) ?></td>
            <td class="fw"><?= number_format($b['total_price']) ?></td>
            <td><span class="pill <?= $pc ?>"><?= ucfirst($b['status']) ?></span></td>
            <td class="text-muted text-sm"><?= date('d M Y, H:i', strtotime($b['created_at'])) ?></td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

</div> <script>
    // Pass PHP data to JavaScript
    const dates = <?= json_encode($dates) ?>;
    const revenueData = <?= json_encode($revenueData) ?>;
    const bookingsData = <?= json_encode($bookingsData) ?>;

    // 1. Revenue Line Chart
    const ctxRev = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctxRev, {
        type: 'line',
        data: {
            labels: dates,
            datasets: [{
                label: 'Daily Revenue (LKR)',
                data: revenueData,
                borderColor: '#1e4d8c', // var(--sky)
                backgroundColor: 'rgba(30, 77, 140, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4, // Smooth curve
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#1e4d8c',
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, grid: { borderDash: [5, 5] } }, x: { grid: { display: false } } }
        }
    });

    // 2. Bookings Bar Chart
    const ctxBook = document.getElementById('bookingsChart').getContext('2d');
    new Chart(ctxBook, {
        type: 'bar',
        data: {
            labels: dates,
            datasets: [{
                label: 'Tickets Booked',
                data: bookingsData,
                backgroundColor: '#38b899', // var(--teal)
                borderRadius: 6, // Rounded corners for bars
                barPercentage: 0.6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } }, x: { grid: { display: false } } }
        }
    });
</script>
</body>
</html>