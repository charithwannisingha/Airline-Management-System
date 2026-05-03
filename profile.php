<?php
// ============================================================
//  AMS — My Profile (English Only & Layout Fixed)
// ============================================================
require_once 'includes/auth.php';
requireLogin();
$db = getDB();
$currentUser = currentUser();
$user_id = $currentUser['id'];

$success = '';
$error = '';

// පෝරමය (Form) Submit කළ විට දත්ත යාවත්කාලීන කිරීම
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $address  = trim($_POST['address'] ?? '');
    $password = $_POST['new_password'] ?? '';

    try {
        if (!empty($password)) {
            // මුරපදයත් වෙනස් කරනවා නම්
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET full_name=?, email=?, phone=?, address=?, password=? WHERE id=?");
            $stmt->execute([$fullName, $email, $phone, $address, $hashed, $user_id]);
        } else {
            // මුරපදය වෙනස් කරන්නේ නැත්නම්
            $stmt = $db->prepare("UPDATE users SET full_name=?, email=?, phone=?, address=? WHERE id=?");
            $stmt->execute([$fullName, $email, $phone, $address, $user_id]);
        }
        $success = "Profile updated successfully!";
    } catch (PDOException $e) {
        $error = "Error updating profile. Email might already be in use.";
    }
}

// Database එකෙන් පරිශීලකයාගේ නිවැරදි දත්ත ලබා ගැනීම (Warnings නැති කිරීමට)
$stmt = $db->prepare("SELECT username, full_name, email, phone, address FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$userData) {
    // දත්ත නොමැති නම් හිස් අගයන් ලබා දීම
    $userData = ['username' => '', 'full_name' => '', 'email' => '', 'phone' => '', 'address' => ''];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Profile · AMS</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<?php include 'includes/sidebar.php'; ?>

<div class="main-content"> 

<header class="topbar">
  <div class="topbar-title">My Profile</div>
</header>

<main class="page-content">
  <div class="card" style="max-width: 700px; margin: 0 auto;">
    <div class="card-header"><span class="card-title">Update Personal Information</span></div>
    <div class="card-body">
        
      <?php if ($success): ?>
          <div class="alert alert-success" style="margin-bottom:15px; padding:10px; background:#d1e7dd; color:#0f5132; border-radius:8px;"><?= $success ?></div>
      <?php endif; ?>
      <?php if ($error): ?>
          <div class="alert alert-danger" style="margin-bottom:15px; padding:10px; background:#ffe3e3; color:#dc3545; border-radius:8px;"><?= $error ?></div>
      <?php endif; ?>

      <form method="POST" action="profile.php">
        <div class="form-group">
          <label style="font-size: 11px; color: var(--gray-500); font-weight: bold; text-transform: uppercase;">Username (Cannot be changed)</label>
          <input type="text" value="<?= htmlspecialchars($userData['username']) ?>" readonly style="background: var(--gray-100); color: var(--gray-500); cursor: not-allowed;">
        </div>

        <div class="form-group">
          <label style="font-size: 11px; color: var(--gray-500); font-weight: bold; text-transform: uppercase;">Full Name</label>
          <input type="text" name="full_name" value="<?= htmlspecialchars($userData['full_name']) ?>" required>
        </div>

        <div class="form-group">
          <label style="font-size: 11px; color: var(--gray-500); font-weight: bold; text-transform: uppercase;">Email Address</label>
          <input type="email" name="email" value="<?= htmlspecialchars($userData['email']) ?>" required>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label style="font-size: 11px; color: var(--gray-500); font-weight: bold; text-transform: uppercase;">Phone Number</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($userData['phone'] ?? '') ?>" placeholder="+94 7X XXX XXXX">
            </div>
            <div class="form-group">
                <label style="font-size: 11px; color: var(--gray-500); font-weight: bold; text-transform: uppercase;">Address</label>
                <input type="text" name="address" value="<?= htmlspecialchars($userData['address'] ?? '') ?>" placeholder="City, Country">
            </div>
        </div>

        <hr style="border: 0; border-top: 1px solid var(--gray-200); margin: 20px 0;">

        <h4 style="margin-bottom: 15px; color: var(--navy);">Change Password</h4>
        <div class="form-group">
          <label style="font-size: 11px; color: var(--gray-500); font-weight: bold; text-transform: uppercase;">New Password (Leave blank to keep current password)</label>
          <input type="password" name="new_password" placeholder="Enter new password">
        </div>

        <button type="submit" class="btn-primary btn-full" style="margin-top: 10px;">Save Changes</button>
      </form>
      
    </div>
  </div>
</main>

</div> <script src="js/app.js"></script>
</body>
</html>