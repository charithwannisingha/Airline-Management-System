<?php
// ============================================================
//  AMS — Login Handler
// ============================================================
session_start();
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit();
}

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

if (empty($username) || empty($password)) {
    header('Location: ../index.php?error=Please+fill+in+all+fields');
    exit();
}

$db = getDB();
$stmt = $db->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
$stmt->execute([$username]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    session_regenerate_id(true);
    $_SESSION['user_id']  = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['name']     = $user['full_name'];
    $_SESSION['role']     = $user['role'];

    // Update last login
    $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);

    if ($user['role'] === 'admin') {
        header('Location: ../dashboard.php');
    } else {
        header('Location: ../flights.php');
    }
} else {
    header('Location: ../index.php?error=Invalid+username+or+password');
}
exit();
?>
