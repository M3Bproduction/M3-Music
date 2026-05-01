<?php
// config.php - Configuration de la base de données

// CONFIGURATION INFINITYFREE
$servername = "sql100.infinityfree.com";
$username = "if0_41622441";
$password = "rAqJW5hD6c";
$dbname = "if0_41622441_m3music";

// Connexion à la base de données
try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    // Vérifier la connexion
    if ($conn->connect_error) {
        die("Erreur de connexion: " . $conn->connect_error);
    }
    
    // Définir l'encodage UTF-8
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    die("Erreur de connexion: " . $e->getMessage());
}

// URL de base du site
define('BASE_URL', 'https://m3music.kesug.com/');
define('ADMIN_URL', BASE_URL . 'admin/');

// Autres configurations
define('ITEMS_PER_PAGE', 10);
define('MAX_UPLOAD_SIZE', 5242880); // 5MB
define('ADMIN_PASSWORD_HASH', password_hash('admin123', PASSWORD_BCRYPT)); // À changer!

?>
