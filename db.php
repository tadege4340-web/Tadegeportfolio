
<?php
/**
 * Tadege Yilkal Portfolio
 * Database connection for MAMP and Render.
 * Online database: Aiven MySQL.
 */

// =====================================================
// 1. ERROR CONFIGURATION
// =====================================================

ini_set("display_errors", "0");
ini_set("display_startup_errors", "0");
ini_set("log_errors", "1");

error_reporting(E_ALL);

// =====================================================
// 2. SESSION INITIALIZATION
// =====================================================

if (session_status() === PHP_SESSION_DISABLED) {
    error_log("SESSION ERROR: PHP sessions are disabled.");

    http_response_code(500);
    exit("PHP sessions are disabled.");
}

if (session_status() === PHP_SESSION_NONE) {

    // Make sure no output was sent before session_start().
    if (headers_sent($sentFile, $sentLine)) {

        error_log(
            "SESSION ERROR: Headers already sent in " .
            $sentFile . " on line " . $sentLine
        );

        http_response_code(500);
        exit("Session initialization failed. Check PHP error logs.");
    }

    // Use PHP's temporary directory for session files.
    $sessionPath = sys_get_temp_dir();

    if (
        !is_dir($sessionPath) ||
        !is_writable($sessionPath)
    ) {

        error_log(
            "SESSION ERROR: Temporary directory is unavailable: " .
            $sessionPath
        );

        http_response_code(500);
        exit("Session storage directory is unavailable.");
    }

    // Configure the session directory before starting.
    $previousPath = session_save_path($sessionPath);

    if (session_save_path() !== $sessionPath) {

        error_log(
            "SESSION ERROR: Could not configure session storage. " .
            "Previous path: " . $previousPath
        );

        http_response_code(500);
        exit("Session storage configuration failed.");
    }

    // Detect HTTPS, including Render reverse proxy.
    $isHttps = (
        !empty($_SERVER["HTTPS"]) &&
        strtolower((string)$_SERVER["HTTPS"]) !== "off"
    );

    if (
        isset($_SERVER["HTTP_X_FORWARDED_PROTO"]) &&
        strtolower((string)$_SERVER["HTTP_X_FORWARDED_PROTO"]) === "https"
    ) {
        $isHttps = true;
    }

    // Session security configuration.
    ini_set("session.use_only_cookies", "1");
    ini_set("session.use_strict_mode", "1");
    ini_set("session.use_trans_sid", "0");

    session_set_cookie_params([
        "lifetime" => 0,
        "path" => "/",
        "domain" => "",
        "secure" => $isHttps,
        "httponly" => true,
        "samesite" => "Lax"
    ]);

    // Start session.
    $started = session_start();

    if (!$started) {

        $lastError = error_get_last();

        error_log(
            "SESSION ERROR: session_start() failed. " .
            "Session path: " . session_save_path() .
            ". PHP error: " .
            ($lastError["message"] ?? "Unknown error")
        );

        http_response_code(500);
        exit("Session initialization failed. Check PHP error logs.");
    }

    error_log("SESSION: Successfully initialized.");
}

// Verify session is active.
if (session_status() !== PHP_SESSION_ACTIVE) {

    error_log(
        "SESSION ERROR: Session is not active. Status: " .
        session_status()
    );

    http_response_code(500);
    exit("Session initialization failed.");
}

// =====================================================
// 3. ENVIRONMENT VARIABLE HELPER
// =====================================================

function env_or(string $key, string $default): string
{
    $value = getenv($key);

    if ($value === false || trim($value) === "") {
        return $default;
    }

    return trim($value);
}

// =====================================================
// 4. DATABASE CONFIGURATION
// =====================================================

$isOnline = trim((string)getenv("DB_HOST")) !== "";

$host = env_or("DB_HOST", "127.0.0.1");
$port = env_or("DB_PORT", "3306");
$dbname = env_or("DB_NAME", "tadege_portfolio");
$username = env_or("DB_USER", "root");
$password = env_or("DB_PASSWORD", "root");

// =====================================================
// 5. MYSQL DATABASE CONNECTION
// =====================================================

try {

    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_TIMEOUT => 10
    ];

    // =================================================
    // AIVEN SSL CONFIGURATION
    // =================================================

    if ($isOnline) {

        $caFile = __DIR__ . "/ca.pem";

        if (!is_file($caFile) || !is_readable($caFile)) {

            error_log(
                "DATABASE ERROR: Aiven SSL certificate is missing or unreadable."
            );

            http_response_code(500);
            exit("Database SSL certificate is unavailable.");
        }

        if (!defined("PDO::MYSQL_ATTR_SSL_CA")) {

            error_log(
                "DATABASE ERROR: PDO MySQL SSL support is unavailable."
            );

            http_response_code(500);
            exit("Database SSL support is unavailable.");
        }

        $options[PDO::MYSQL_ATTR_SSL_CA] = $caFile;
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
    }

    // =================================================
    // CONNECT TO DATABASE
    // =================================================

    $pdo = new PDO(
        $dsn,
        $username,
        $password,
        $options
    );

    error_log("DATABASE: Connection successful.");

} catch (PDOException $e) {

    error_log(
        "DATABASE CONNECTION ERROR: " . $e->getMessage()
    );

    http_response_code(500);
    exit("Database connection failed. Please try again later.");
}
