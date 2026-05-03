<?php
require_once 'includes/db.php';
$db = getDB();

// ඔයාගේ සැබෑ ඊමේල් ලිපිනය මෙතනට දෙන්න (උදා: charith@gmail.com)
$email = "charitherandabs@gmail.com"; 

try {
    $stmt = $db->prepare("UPDATE users SET role = 'admin' WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->rowCount() > 0) {
        echo "<h2 style='color:green; font-family:sans-serif;'>සාර්ථකයි! ඔබගේ ගිණුම දැන් Admin ගිණුමක් බවට පත් විය.</h2>";
        echo "<p style='font-family:sans-serif;'>දැන් පද්ධතියෙන් <b>Sign Out වී නැවත Log In වන්න</b>.</p>";
    } else {
        echo "<h2 style='color:red; font-family:sans-serif;'>මෙම ඊමේල් ලිපිනයෙන් ගිණුමක් සොයාගත නොහැක! ඊමේල් එක නිවැරදිදැයි බලන්න.</h2>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>