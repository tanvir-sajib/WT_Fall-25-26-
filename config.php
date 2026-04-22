<?php
// ============================================================
// Admin config.php - SECURED
// ============================================================

define('DEV_MODE', true);
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ecommerce');

if (DEV_MODE) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$conn) {
    die(DEV_MODE ? "Connection failed: " . mysqli_connect_error() : "Service unavailable.");
}
mysqli_set_charset($conn, "utf8mb4");

// Hardened session
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 3600,
        'path'     => '/',
        'secure'   => !DEV_MODE,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

// CSRF token for admin
if (empty($_SESSION['admin_csrf'])) {
    $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
}

function verify_admin_csrf($token) {
    if (!isset($_SESSION['admin_csrf']) || !hash_equals($_SESSION['admin_csrf'], $token)) {
        http_response_code(403);
        die("Invalid request.");
    }
}

function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function check_login() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header("Location: LogPage.php");
        exit();
    }
    // Auto-logout after 2 hours of inactivity
    if (isset($_SESSION['admin_last_active']) && (time() - $_SESSION['admin_last_active']) > 7200) {
        session_destroy();
        header("Location: LogPage.php?timeout=1");
        exit();
    }
    $_SESSION['admin_last_active'] = time();
}