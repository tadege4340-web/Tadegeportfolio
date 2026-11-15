
<?php
// IMPORTANT: There must be absolutely nothing before this opening PHP tag.
// No spaces, blank lines, or HTML.

// Start output buffering to prevent accidental early output.
if (!ob_get_level()) {
    ob_start();
}

// Start session before sending any output.
if (session_status() === PHP_SESSION_NONE) {
    if (headers_sent($file, $line)) {
        error_log("Cannot start session. Output already sent in $file on line $line");
        http_response_code(500);
        exit("Session initialization failed. Check PHP output before db.php.");
    }

    if (!session_start()) {
        error_log("Unable to start PHP session.");
        http_response_code(500);
        exit("Session initialization failed.");
    }
}

// Database configuration
$host = getenv("DB_HOST") ?: "localhost";
$dbname = getenv("DB_NAME") ?: "your_database_name";
$username = getenv("DB_USER") ?: "your_database_user";
$password = getenv("DB_PASSWORD") ?: "your_database_password";
$port = getenv("DB_PORT") ?: "3306";

// Database connection
try {
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);

} catch (PDOException $e) {
    error_log("Database connection error: " . $e->getMessage());
    http_response_code(500);
    exit("Database connection failed. Please check your configuration.");
}
