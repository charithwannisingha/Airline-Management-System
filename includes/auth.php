<?php
// ============================================================
//  AMS — Auth Helper Functions
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';

function requireLogin(string $redirect = '../index.php'): void {
    if (!isset($_SESSION['user_id'])) {
        header("Location: $redirect");
        exit();
    }
}

function requireAdmin(string $redirect = '../dashboard.php'): void {
    requireLogin();
    if ($_SESSION['role'] !== 'admin') {
        header("Location: $redirect?error=Access+denied");
        exit();
    }
}

function isAdmin(): bool {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function currentUser(): array {
    return [
        'id'       => $_SESSION['user_id']   ?? null,
        'username' => $_SESSION['username']  ?? '',
        'name'     => $_SESSION['name']      ?? '',
        'role'     => $_SESSION['role']       ?? 'passenger',
    ];
}

function sanitize(string $val): string {
    return htmlspecialchars(trim($val), ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void {
    header("Location: $url");
    exit();
}

function flashMsg(string $type, string $msg): void {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function showFlash(): string {
    if (!isset($_SESSION['flash'])) return '';
    $f   = $_SESSION['flash'];
    unset($_SESSION['flash']);
    $cls = $f['type'] === 'success' ? 'alert-success' : 'alert-danger';
    return "<div class='alert $cls'>" . sanitize($f['msg']) . "</div>";
}

// ============================================================
//  Dynamic Pricing Algorithm (AMS)
// ============================================================
function getDynamicPrice($basePrice, $departureTime, $seatsLeft, $totalCapacity) {
    $price = $basePrice;
    $now = time();
    $dep = strtotime($departureTime);
    $daysLeft = ($dep - $now) / (60 * 60 * 24);
    
    // Total Capacity එක 0 වීම වැළැක්වීමට
    $totalCapacity = $totalCapacity > 0 ? $totalCapacity : 180; 
    $bookedPercent = (($totalCapacity - $seatsLeft) / $totalCapacity) * 100;

    $badges = []; 

    // 1. Demand-Based Pricing (ඉල්ලුම අනුව මිල වැඩි වීම)
    if ($bookedPercent >= 85) {
        $price *= 1.25; // 85% ක් බුක් වෙලා නම් මිල 25% කින් වැඩි වේ
        $badges[] = "<span class='pill' style='background:#fee2e2; color:#ef4444; font-size:10px;'>🔥 High Demand</span>";
    } elseif ($bookedPercent >= 60) {
        $price *= 1.10; // 60% ක් බුක් වෙලා නම් මිල 10% කින් වැඩි වේ
    }

    // 2. Time-Based Pricing (පිටත්වෙන දිනය ළං වීම අනුව)
    if ($daysLeft > 0 && $daysLeft <= 3) {
        $price *= 1.20; // දවස් 3කට වඩා අඩු නම් 20% කින් වැඩි වේ
        $badges[] = "<span class='pill' style='background:#fef3c7; color:#d97706; font-size:10px;'>⏳ Last Minute</span>";
    } elseif ($daysLeft > 3 && $daysLeft <= 7) {
        $price *= 1.05; // දවස් 7කට වඩා අඩු නම් 5% කින් වැඩි වේ
    }

    return [
        'final_price' => round($price),
        'badges' => implode(' ', $badges)
    ];
}
?>