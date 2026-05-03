<?php
require_once __DIR__ . '/auth.php';
$user    = currentUser();
$current = basename($_SERVER['PHP_SELF'], '.php');
$role    = $user['role'] ?? 'passenger';
$name    = $user['name'] ?: ($user['username'] ?? 'User');
?>

<div class="ticker">
  <div class="ticker-inner">
    <span class="ticker-item">✈ NX 101 CMB→DXB On-Time</span>
    <span class="ticker-item">✈ NX 204 CMB→SIN Delayed 20m</span>
    <span class="ticker-item">✈ NX 312 CMB→KUL On-Time</span>
    <span class="ticker-item">✈ NX 520 CMB→LHR Boarding</span>
  </div>
</div>

<div class="app-layout">
<aside class="sidebar" id="sidebar">
  
  <div class="sidebar-brand" style="display: flex; align-items: center; gap: 15px; padding: 25px 20px; border-bottom: 1px solid rgba(255,255,255,0.05);">
    <div style="width: 45px; height: 45px; background: rgba(56, 189, 248, 0.15); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #38bdf8; flex-shrink: 0; border: 1px solid rgba(56, 189, 248, 0.3);">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M17.8 19.2 16 11l3.5-3.5C21 6 21.5 4 21 3c-1-.5-3 0-4.5 1.5L13 8 4.8 6.2c-.5-.1-.9.2-1.1.6L3 8l6 4-3 3-3.5-1-1.5 1.5L6 18l1.5 5 1.5-1.5-1-3.5 3-3 4 6l1.2-.7c.4-.2.7-.6.6-1.1z"/>
        </svg>
    </div>
    <div class="sidebar-brand-text" style="display: flex; flex-direction: column;">
        <span style="color: #ffffff; font-size: 18px; font-weight: 800; letter-spacing: 0.5px; line-height: 1.2;">Nexus Airlines</span>
        <span style="color: #94a3b8; font-size: 11px; font-weight: 500;">Management System</span>
    </div>
  </div>

  <div class="sidebar-user" style="display: flex; align-items: center; gap: 15px; padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.05);">
    <div style="width: 45px; height: 45px; background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #ffffff; box-shadow: 0 4px 10px rgba(2, 132, 199, 0.4); flex-shrink: 0; border: 2px solid #38bdf8;">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
            <circle cx="12" cy="7" r="4"></circle>
        </svg>
    </div>
    <div class="user-info">
      <div class="user-name" style="color: #ffffff; font-weight: 700; font-size: 14px; margin-bottom: 2px;"><?= htmlspecialchars($name) ?></div>
      <div class="user-role" style="color: #38bdf8; font-size: 12px; text-transform: capitalize; font-weight: 600;"><?= htmlspecialchars($role) ?></div>
    </div>
  </div>

  <nav class="sidebar-nav">
    <?php if ($role === 'admin'): ?>
    <div class="nav-section" style="padding: 15px 25px 5px; color: #64748b; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px;">Main</div>
    
    <a href="dashboard.php" class="nav-item <?= $current==='dashboard'?'active':'' ?>">
      <span class="nav-icon">📊</span> Dashboard
    </a>
    <a href="flights.php" class="nav-item <?= $current==='flights'?'active':'' ?>">
      <span class="nav-icon">✈️</span> Flight Search
    </a>
    <a href="bookings.php" class="nav-item <?= $current==='bookings'?'active':'' ?>">
      <span class="nav-icon">🎫</span> All Bookings
    </a>
    <a href="all_passengers.php" class="nav-item <?= $current==='all_passengers'?'active':'' ?>">
      <span class="nav-icon">👥</span> All Passengers
    </a>
    
    <div class="nav-section" style="padding: 20px 25px 5px; color: #64748b; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px;">Management</div>
    
    <a href="manage_flights.php" class="nav-item <?= $current==='manage_flights'?'active':'' ?>">
      <span class="nav-icon">📅</span> Manage Flights
    </a>
    <a href="manage_aircrafts.php" class="nav-item <?= $current==='manage_aircrafts'?'active':'' ?>">
      <span class="nav-icon">🛩️</span> Manage Aircrafts
    </a>
    <a href="manage_crew.php" class="nav-item <?= $current==='manage_crew'?'active':'' ?>">
      <span class="nav-icon">👨‍✈️</span> Manage Crew
    </a>
    <a href="dynamic_pricing.php" class="nav-item <?= $current==='dynamic_pricing'?'active':'' ?>">
      <span class="nav-icon">💰</span> Dynamic Pricing
    </a>
    
    <?php else: ?>
    <div class="nav-section" style="padding: 15px 25px 5px; color: #64748b; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px;">Passenger</div>
    
    <a href="flights.php" class="nav-item <?= $current==='flights'?'active':'' ?>">
      <span class="nav-icon">✈️</span> Search Flights
    </a>
    <a href="my_bookings.php" class="nav-item <?= $current==='my_bookings'?'active':'' ?>">
      <span class="nav-icon">🎫</span> My Bookings
    </a>
    <a href="loyalty.php" class="nav-item <?= $current==='loyalty'?'active':'' ?>">
      <span class="nav-icon">👑</span> Loyalty Club
    </a>
    <a href="profile.php" class="nav-item <?= $current==='profile'?'active':'' ?>">
      <span class="nav-icon">👤</span> My Profile
    </a>
    <?php endif; ?>
  </nav>

  <div class="sidebar-footer" style="position: absolute; bottom: 0; width: 100%; padding: 20px; border-top: 1px solid rgba(255,255,255,0.05); background: #0f172a;">
    <a href="php/logout.php" style="color: #ef4444; text-decoration: none; font-size: 14px; font-weight: 700; display: flex; align-items: center; gap: 10px; transition: 0.3s;">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
            <polyline points="16 17 21 12 16 7"></polyline>
            <line x1="21" y1="12" x2="9" y2="12"></line>
        </svg>
        Sign Out
    </a>
  </div>
</aside>