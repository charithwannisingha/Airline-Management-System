<?php
// ============================================================
//  Nexus Airlines — Loyalty Club
// ============================================================
require_once 'includes/auth.php';
requireLogin();
$db = getDB();
$user = currentUser();

// මගියාගේ Points ප්‍රමාණය ලබා ගැනීම
$stmt = $db->prepare("SELECT loyalty_points FROM users WHERE id = ?");
$stmt->execute([$user['id']]);
$points = (int)$stmt->fetchColumn();

// Progress Calculation (Target: 5000 points)
$target = 5000;
$progress = ($points / $target) * 100;
if ($progress > 100) $progress = 100;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loyalty Club · Nexus Airlines</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .loyalty-hero {
            background: linear-gradient(135deg, #0f172a 0%, #1e4d8c 100%);
            color: white;
            border-radius: 16px;
            padding: 50px 20px;
            text-align: center;
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
            position: relative;
            overflow: hidden;
            margin-bottom: 30px;
        }
        .loyalty-hero::before {
            content: '👑';
            position: absolute;
            font-size: 200px;
            opacity: 0.05;
            top: -30px;
            left: -20px;
            transform: rotate(-15deg);
        }
        .points-circle {
            margin: 30px auto;
            width: 180px;
            height: 180px;
            border: 8px solid rgba(56, 189, 248, 0.2);
            border-top-color: #38bdf8;
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            box-shadow: 0 0 30px rgba(56, 189, 248, 0.15);
            animation: spinBorder 2s linear infinite;
        }
        /* Custom animation for the circle border */
        @keyframes spinBorder {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .points-content {
            /* Counters the rotation so text stays straight */
            animation: spinBorderReverse 2s linear infinite;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        @keyframes spinBorderReverse {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(-360deg); }
        }
        .points-number {
            font-size: 48px;
            font-weight: 800;
            color: #38bdf8;
            font-family: var(--font-head);
            line-height: 1;
        }
        .points-label {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #cbd5e1;
            margin-top: 5px;
        }
        .progress-container {
            width: 100%;
            max-width: 400px;
            margin: 25px auto 10px;
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            height: 8px;
            overflow: hidden;
        }
        .progress-bar {
            height: 100%;
            background: #38bdf8;
            border-radius: 10px;
            transition: width 1s ease-in-out;
        }
        .perk-box {
            background: #ffffff;
            border: 1px solid var(--gray-200);
            border-radius: 16px;
            padding: 30px;
            text-align: left;
            transition: 0.3s;
        }
        .perk-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
            border-color: #bae6fd;
        }
        .perk-icon {
            font-size: 28px;
            margin-bottom: 15px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #f0f9ff;
            color: #0284c7;
            width: 50px;
            height: 50px;
            border-radius: 12px;
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        
        <header class="topbar">
            <div class="topbar-title">Loyalty Club</div>
        </header>

        <main class="page-content">
            
            <div class="loyalty-hero">
                <h2 style="font-family: var(--font-head); font-size: 28px; margin-bottom: 10px;">Nexus Frequent Flyer Club</h2>
                <p style="color: #cbd5e1; font-size: 15px;">Enjoy exclusive benefits, upgrades, and lounge access with every flight you take.</p>
                
                <div class="points-circle">
                    <div class="points-content">
                        <span class="points-number"><?= number_format($points) ?></span>
                        <span class="points-label">Points</span>
                    </div>
                </div>

                <div class="progress-container">
                    <div class="progress-bar" style="width: <?= $progress ?>%;"></div>
                </div>
                <p style="color: #94a3b8; font-size: 13px; margin-top: 10px; font-weight: 500;">
                    <?= number_format($target - $points > 0 ? $target - $points : 0) ?> points away from Gold Tier
                </p>
            </div>

            <div class="grid-2" style="gap: 20px;">
                <div class="perk-box">
                    <div class="perk-icon">💳</div>
                    <h4 style="color: var(--navy); margin-bottom: 10px; font-family: var(--font-head); font-size: 18px;">How to earn points?</h4>
                    <p style="color: var(--gray-500); font-size: 14px; line-height: 1.6;">
                        Earn <strong>1 Loyalty Point</strong> for every LKR 1,000 you spend on Nexus Airlines flight bookings. Points are automatically credited to your account after you complete a flight.
                    </p>
                </div>

                <div class="perk-box">
                    <div class="perk-icon">🎁</div>
                    <h4 style="color: var(--navy); margin-bottom: 10px; font-family: var(--font-head); font-size: 18px;">How to use points?</h4>
                    <p style="color: var(--gray-500); font-size: 14px; line-height: 1.6;">
                        Once you reach <strong>5,000 points</strong>, you unlock the Gold Tier! Enjoy exclusive discounts on future flights, priority boarding, and free access to Nexus Airport Lounges.
                    </p>
                </div>
            </div>

        </main>
    </div> <script src="js/app.js"></script>
</body>
</html>