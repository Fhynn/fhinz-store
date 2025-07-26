<?php
// Database configuration
$host = 'localhost';
$dbname = 'fhinz_store';
$username = 'root';
$password = '';

try {
    // Create connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create settings table
    $sql = "CREATE TABLE IF NOT EXISTS `settings` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `site_name` varchar(255) NOT NULL DEFAULT 'Fhinz Store',
        `site_description` text,
        `site_logo` varchar(255) DEFAULT NULL,
        `contact_email` varchar(255) NOT NULL,
        `contact_phone` varchar(50) DEFAULT NULL,
        `address` text,
        `facebook` varchar(255) DEFAULT NULL,
        `instagram` varchar(255) DEFAULT NULL,
        `whatsapp` varchar(50) DEFAULT NULL,
        `theme_color` varchar(20) DEFAULT 'blue',
        `language` varchar(5) DEFAULT 'id',
        `maintenance_mode` tinyint(1) DEFAULT 0,
        `enable_registration` tinyint(1) DEFAULT 1,
        `admin_password_hash` varchar(255) DEFAULT NULL,
        `session_timeout` int(11) DEFAULT 30,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    $pdo->exec($sql);
    echo "✅ Settings table created successfully!<br>";
    
    // Insert default settings
    $check = $pdo->query("SELECT COUNT(*) FROM settings WHERE id = 1")->fetchColumn();
    if ($check == 0) {
        $insert = "INSERT INTO `settings` (`id`, `site_name`, `site_description`, `contact_email`, 
                    `contact_phone`, `address`, `facebook`, `instagram`, `whatsapp`, `theme_color`, 
                    `language`, `maintenance_mode`, `enable_registration`, `session_timeout`) 
                    VALUES (1, 'Fhinz Store', 'Toko aplikasi premium terpercaya', 
                    'alfhinhidayat98@gmail.com', '+62 896-3556-0147', 'Pekanbaru, Indonesia', 
                    'https://facebook.com/fhinzstore', 'https://instagram.com/fhinz_anxiety', 
                    '+62 896-3556-0147', 'blue', 'id', 0, 1, 30)";
        
        $pdo->exec($insert);
        echo "✅ Default settings inserted successfully!<br>";
    } else {
        echo "ℹ️ Settings already exist.<br>";
    }
    
    echo "<br>🎉 Installation complete! You can now use the settings page.";
    
} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Install Settings</title>
    <meta http-equiv="refresh" content="3;url=admin/settings.php">
</head>
<body>
    <p>Redirecting to settings page in 3 seconds...</p>
</body>
</html>
