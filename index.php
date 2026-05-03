<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nexus Airlines</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=DM+Sans:ital,wght@0,400;0,500;1,400&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>
<body class="auth-page">

  <div class="sky-bg">
    <div class="cloud c1"></div>
    <div class="cloud c2"></div>
    <div class="cloud c3"></div>
    <div class="stars"></div>
  </div>

  <div class="auth-container">
    <div class="auth-left">
      <div class="brand-logo">
        <span class="plane-spin">✈</span>
        <div>
          <h1>Nexus Airlines</h1>
          <p>Airline Management System</p>
        </div>
      </div>
      <div class="tagline">
        <h2>The sky is not<br>the limit.</h2>
        <p>Managing every flight, booking,<br>and passenger — in one place.</p>
      </div>
      <div class="auth-stats">
        <div class="stat-item"><span class="stat-num">47</span><span class="stat-lbl">Daily Flights</span></div>
        <div class="stat-item"><span class="stat-num">6K+</span><span class="stat-lbl">Passengers</span></div>
        <div class="stat-item"><span class="stat-num">99%</span><span class="stat-lbl">Uptime</span></div>
      </div>
    </div>

    <div class="auth-right">
      <div class="auth-card">
        <h3>Welcome back</h3>
        <p class="auth-sub">Sign in to your AMS account</p>

        <?php if (isset($_GET['error'])): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['msg'])): ?>
          <div class="alert alert-success"><?= htmlspecialchars($_GET['msg']) ?></div>
        <?php endif; ?>

        <form action="php/login.php" method="POST" id="loginForm">
          <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="Enter your username" required autocomplete="off">
          </div>
          <div class="form-group">
            <label for="password">Password</label>
            <div class="pass-wrap">
              <input type="password" id="password" name="password" placeholder="Enter your password" required>
              <button type="button" class="toggle-pass" onclick="togglePass()">👁</button>
            </div>
          </div>
          <div class="form-row">
            <label class="checkbox-label">
              <input type="checkbox" name="remember"> Remember me
            </label>
            <a href="forgot_password.php" class="forgot-link">Forgot password?</a>
          </div>
          <button type="submit" class="btn-primary btn-full" id="loginBtn">
            <span>Sign In</span>
          </button>
        </form>

        <div style="text-align: center; margin-top: 15px;">
          <span style="color: var(--gray-600); font-size: 14px;">Don't have an account?</span>
          <a href="register.php" style="color: var(--sky); font-weight: 700; text-decoration: none; font-size: 14px;">Sign Up</a>
        </div>

  <script src="js/app.js"></script>
</body>
</html>