
<?php
/**
 * Database connection for MAMP and Render.
 * Uses Aiven MySQL online.
 */

ini_set("display_errors", "0");
ini_set("display_startup_errors", "0");
ini_set("log_errors", "1");
error_reporting(E_ALL);

// --------------------------------------------------
// 1. SESSION INITIALIZATION
// --------------------------------------------------

if (session_status() === PHP_SESSION_NONE) {

    if (headers_sent($sentFile, $sentLine)) {
        error_log(
            "SESSION ERROR: Headers already sent in {$sentFile} on line {$sentLine}."
        );

        http_response_code(500);
        exit("Session initialization failed.");
    }

    // Configure session storage.
    $sessionPath = sys_get_temp_dir();

    if (!is_dir($sessionPath) || !is_writable($sessionPath)) {
        error_log(
            "SESSION ERROR: Session directory is not writable: " . $sessionPath
        );

        http_response_code(500);
        exit("Session storage is unavailable.");
    }

    // session_save_path() returns the PREVIOUS path.
    // Verify the current path instead of checking its return value.
    session_save_path($sessionPath);

    if (session_save_path() !== $sessionPath) {
        error_log(
            "SESSION ERROR: Failed to configure session path: " . $sessionPath
        );

        http_response_code(500);
        exit("Session storage configuration failed.");
    }

    // Detect HTTPS behind Render's reverse proxy.
    $isHttps =
        (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") ||
        (
            isset($_SERVER["HTTP_X_FORWARDED_PROTO"]) &&
            strtolower((string) $_SERVER["HTTP_X_FORWARDED_PROTO"]) === "https"
        );

    session_set_cookie_params([
        "lifetime" => 0,
        "path" => "/",
        "domain" => "",
        "secure" => $isHttps,
        "httponly" => true,
        "samesite" => "Lax"
    ]);

    if (!session_start()) {
        error_log(
            "SESSION ERROR: session_start() failed. Save path: " .
            session_save_path()
        );

        http_response_code(500);
        exit("Session initialization failed.");
    }
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    error_log("SESSION ERROR: Session is not active.");

    http_response_code(500);
    exit("Session initialization failed.");
}

// --------------------------------------------------
// 2. ENVIRONMENT VARIABLE HELPER
// --------------------------------------------------

function env_or(string $key, string $default): string
{
    $value = getenv($key);

    if ($value === false || trim($value) === "") {
        return $default;
    }

    return trim($value);
}

// --------------------------------------------------
// 3. DATABASE CONFIGURATION
// --------------------------------------------------

$isOnline = trim((string) getenv("DB_HOST")) !== "";

$host = env_or("DB_HOST", "127.0.0.1");
$port = env_or("DB_PORT", "3306");
$dbname = env_or("DB_NAME", "tadege_portfolio");
$username = env_or("DB_USER", "root");
$password = env_or("DB_PASSWORD", "root");

// --------------------------------------------------
// 4. MYSQL CONNECTION
// --------------------------------------------------

try {

    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_TIMEOUT => 10
    ];

    if ($isOnline) {

        $caFile = __DIR__ . "/ca.pem";

        if (is_file($caFile)) {

            $options[PDO::MYSQL_ATTR_SSL_CA] = $caFile;
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;

        } else {

            error_log(
                "DATABASE WARNING: ca.pem not found. " .
                "Certificate verification is disabled."
            );

            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }
    }

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
