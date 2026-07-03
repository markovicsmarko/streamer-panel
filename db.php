<?php
// Security protection: disable direct browser access
if (basename($_SERVER['SCRIPT_FILENAME']) === 'db.php') {
    header('HTTP/1.0 403 Forbidden');
    exit('Access Denied');
}

// Start session, this is required for login
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_lifetime', 60 * 60 * 24 * 30);
    ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 30);
    session_start();
}

// Load configuration
require_once __DIR__ . '/config.php';

// Compatibility variables for older code
$steam_api_key = STEAM_API_KEY;
$admin_steam_id = ADMIN_STEAM_ID;
$host = DB_HOST;
$dbname = DB_NAME;
$username = DB_USER;
$password = DB_PASS;

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Adatbázis kapcsolódási hiba: " . $e->getMessage());
    die("Hiba az adatbázis csatlakozásakor! Kérjük, próbálkozzon később.");
}

// Load language initialization
require_once __DIR__ . '/localization.php';
?>
