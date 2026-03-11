<?php
// config.php - Database Configuration File

// 1. Connection Details
$host = 'localhost';
$db   = 'ecommerce_admin';
$user = 'root';
$pass = ''; // XAMPP default is empty
$charset = 'utf8mb4';

// 2. Data Source Name (The "Address")
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// 3. Security Options
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     // 4. Create the connection
     $pdo = new PDO($dsn, $user, $pass, $options);
     // If you see nothing, it worked!
} catch (\PDOException $e) {
     // 5. If it fails, show the error
     die("Database connection failed: " . $e->getMessage());
}

// Database credentials
define('DB_HOST', 'localhost');     // Change if your host is different
define('DB_USER', 'root');          // Change to your database username
define('DB_PASS', '');              // Change to your database password
define('DB_NAME', 'ecommerce_admin'); // Database name

// Create connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset to utf8mb4 for proper character encoding
mysqli_set_charset($conn, "utf8mb4");

// Function to sanitize input
function sanitize_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = mysqli_real_escape_string($conn, $data);
    return $data;
}

// Function to check if user is logged in
function check_login() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header("Location: login.php");
        exit();
    }
}
?>