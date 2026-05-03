<?php
session_start();
// කෙනෙක් දැනටමත් ලොග් වෙලා නම් Dashboard එකට යවනවා
if (isset($_SESSION['user_id'])) { 
    header("Location: dashboard.php"); 
    exit(); 
}
require_once 'includes/db.php';

$error = ''; $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = getDB();
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $new_password = $_POST['new_password'];

    // Username එකයි Email එකයි දත්ත ගබඩාවේ තියෙනවද කියලා බලනවා
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ? AND email = ?");
    $stmt->execute([$username, $email]);
    $user = $stmt->fetch();

    if ($user) {
        // මැච් වෙනවා නම් අලුත් පාස්වර්ඩ් එක Hash කරලා සේව් කරනවා
        $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
        $updateStmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
        $updateStmt->execute([$hashedPassword, $user['id']]);
        
        $success = "ඔබගේ මුරපදය සාර්ථකව වෙනස් කරන ලදී! දැන් Log In වන්න.";
    } else {
        $error = "Username සහ Email ලිපිනය නොගැලපේ!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Forgot Password · AMS</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body class="auth-page">
  <div class="sky-bg">
      <div class="cloud c1"></div>
      <div class="cloud c2"></div>
      <div class="stars"></div>
  </div>

  <div class="auth-container" style="justify-content: center;">
    <div class="auth-right" style="max-width: 450px; width: 100%;">
      <div class="auth-card">
        <h3>Reset Password</h3>
        <p class="auth-sub">මුරපදය යළි සකසන්න</p>

        <?php if ($error): ?>
            <div class="alert alert-danger" style="margin-bottom:15px; padding:10px; background:#ffe3e3; color:red; border-radius:8px;">
                <?= $error ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success" style="margin-bottom:15px; padding:10px; background:#d1e7dd; color:green; border-radius:8px;">
                <?= $success ?><br><br>
                <a href="index.php" class="btn-primary btn-sm" style="display:inline-block; text-align:center; padding:8px 15px; text-decoration:none;">Go to Login</a>
            </div>
        <?php else: ?>
        
        <form method="POST">
          <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" required placeholder="ඔබගේ Username එක">
          </div>
          <div class="form-group">
            <label>Registered Email Address</label>
            <input type="email" name="email" required placeholder="ඔබ ලියාපදිංචි වූ Email එක">
          </div>
          <div class="form-group">
            <label>New Password</label>
            <input type="password" name="new_password" required placeholder="අලුත් මුරපදයක් දෙන්න">
          </div>
          <button type="submit" class="btn-primary btn-full mt-2">Reset Password</button>
        </form>
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 15px;">
          <a href="index.php" style="color: var(--sky); font-weight: bold; text-decoration: none;">← Back to Sign In</a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>