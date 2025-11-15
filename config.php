<?php
// config.php - edit with your DB credentials and site root
$BASE_DIR = __DIR__ . '/data';
$CACHE_FILE = __DIR__ . '/cache.json';
$SITE_ROOT = '/slovnik'; // change if you deploy under different path

// Session & security
session_start([
    'cookie_lifetime' => 60*60*24*60, // 2 months
    'cookie_httponly' => true,
    'use_strict_mode' => true,
    'cookie_samesite' => 'Strict'
]);

// Cookie settings (legacy)
$COOKIE_NAME = 'slovnik_user';
$COOKIE_TTL = 60*60*24*30*2; // 2 months

// MySQLi connection - replace with your hosting credentials
$dbHost = 'DB_HOST';
$dbUser = 'DB_USER';
$dbPass = 'DB_PASS';
$dbName = 'DB_NAME';

$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($mysqli->connect_errno) {
    // On production consider hiding details and logging instead
    die("DB connection failed: " . $mysqli->connect_error);
}
$mysqli->set_charset('utf8mb4');
?>
