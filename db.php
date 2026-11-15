
<?php
// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    if (!session_start()) {
        error_log("Session initialization failed.");
        http_response_code(500);
        exit("Session initialization failed. Please try again later.");
    }
}

// Database configuration
$host = getenv("DB_HOST") ?: "localhost";
$dbname = getenv("DB_NAME") ?: "your_database_name";
$username = getenv("DB_USER") ?: "your_database_user";
$password = getenv("DB_PASSWORD") ?: "your_database_password";
$port = getenv("DB_PORT") ?: "3306";

// Connect to database
try {
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);

} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    http_response_code(500);
    exit("Database connection failed. Please check the server configuration.");
}
