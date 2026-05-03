<?php
session_start();

// දැනටමත් ලොග් වී ඇත්නම් (Session එකක් තිබේ නම්) කෙලින්ම Dashboard එකට යවන්න
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Database සම්බන්ධතාවය ලබා ගැනීම
require_once 'includes/db.php';

$error = '';
$success = '';

// Form එක Submit කළ පසු දත්ත අල්ලා ගැනීම
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = getDB();
    
    // Form එකෙන් එන දත්ත වල හිස් ඉඩ (spaces) ඉවත් කර ලබා ගැනීම
    $fullName = trim($_POST['full_name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // සියලුම තොරතුරු (Fields) පුරවා ඇත්දැයි පරීක්ෂා කිරීම
    if (empty($fullName) || empty($email) || empty($username) || empty($password)) {
        $error = 'Please fill in all the required fields.';
    } else {
        // ලබා දී ඇති Username එක හෝ Email එක දැනටමත් Database එකේ තිබේදැයි සෙවීම
        $checkStmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $checkStmt->execute([$username, $email]);
        
        // එසේ තිබේ නම් දෝෂ පණිවිඩයක් පෙන්වීම
        if ($checkStmt->fetch()) {
            $error = 'This Username or Email is already registered!';
        } else {
            // ආරක්ෂාව සඳහා මුරපදය (Password) Hash කිරීම
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // අලුත් මගියාව (Passenger) Database එකේ users table එකට ඇතුළත් කිරීම
            $insertStmt = $db->prepare("INSERT INTO users (username, password, full_name, email, role) VALUES (?, ?, ?, ?, 'passenger')");
            
            // දත්ත සාර්ථකව ඇතුළත් වුවහොත් Success පණිවිඩය පෙන්වීම
            if ($insertStmt->execute([$username, $hashedPassword, $fullName, $email])) {
                $success = 'Account created successfully! You can now log in.';
            } else {
                // දත්ත ඇතුළත් කිරීමේදී දෝෂයක් වුවහොත් පෙන්වීම
                $error = 'An error occurred while creating the account. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register · AMS</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>
<body class="auth-page">

  <div class="sky-bg">
    <div class="cloud c1"></div>
    <div class="cloud c2"></div>
    <div class="cloud c3"></div>
    <div class="stars"></div>
  </div>

  <div class="auth-container" style="justify-content: center;">
    <div class="auth-right" style="max-width: 500px; width: 100%;">
      
      <div class="auth-card">
        <h3>Create an Account</h3>
        <p class="auth-sub">Join Nexus Airlines today</p>

        <?php if ($error): ?>
          <div class="alert alert-danger" style="background: #ffe3e3; color: #dc3545; padding: 10px; border-radius: 8px; margin-bottom: 15px;">
            <?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
          <div class="alert alert-success" style="background: #d1e7dd; color: #0f5132; padding: 10px; border-radius: 8px; margin-bottom: 15px;">
            <?= htmlspecialchars($success) ?> <br>
            <a href="index.php" style="color: #0f5132; font-weight: bold; text-decoration: underline;">Click here to Sign In</a>
          </div>
        <?php else: ?>

        <form action="register.php" method="POST">
          <div class="form-group">
            <label for="full_name">Full Name</label>
            <input type="text" id="full_name" name="full_name" placeholder="John Doe" required>
          </div>
          <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" placeholder="john@email.com" required>
          </div>
          <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="Choose a username" required autocomplete="off">
          </div>
          <div class="form-group">
            <label for="password">Password</label>
            <div class="pass-wrap">
              <input type="password" id="password" name="password" placeholder="Create a strong password" required>
            </div>
          </div>
          
          <button type="submit" class="btn-primary btn-full" style="margin-top: 15px;">
            <span>Sign Up</span>
          </button>
        </form>
        
        <?php endif; ?>

        <div class="auth-divider" style="margin-top: 20px;"><span>Already have an account?</span></div>
        <div style="text-align: center; margin-top: 10px;">
          <a href="index.php" class="btn-secondary" style="display: inline-block; padding: 8px 20px; text-decoration: none;">Sign In Here</a>
        </div>

      </div>
    </div>
  </div>

</body>
</html>