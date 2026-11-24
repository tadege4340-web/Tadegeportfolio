
<?php
/**
 * Tadege Yilkal Portfolio
 * Database connection for MAMP and Render.
 * Local database: MAMP MySQL
 * Online database: Aiven MySQL
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

    if (headers_sent($sentFile, $sentLine)) {
        error_log(
            "SESSION ERROR: Output started in {$sentFile} on line {$sentLine}."
        );

        http_response_code(500);
        exit("Session initialization failed. Check PHP error logs.");
    }

    // Use PHP's temporary directory.
    $sessionPath = sys_get_temp_dir();

    if (!is_dir($sessionPath) || !is_writable($sessionPath)) {
        error_log(
            "SESSION ERROR: Session directory is unavailable: " . $sessionPath
        );

        http_response_code(500);
        exit("Session storage directory is unavailable.");
    }

    // Configure session storage.
    if (!session_save_path($sessionPath)) {
        error_log("SESSION ERROR: Could not configure session storage.");

        http_response_code(500);
        exit("Session storage configuration failed.");
    }

    // Detect HTTPS, including Render's reverse proxy.
    $isHttps = (
        (!empty($_SERVER["HTTPS"]) &&
        strtolower((string) $_SERVER["HTTPS"]) !== "off")
        ||
        (
            isset($_SERVER["HTTP_X_FORWARDED_PROTO"]) &&
            strtolower(trim(explode(",", $_SERVER["HTTP_X_FORWARDED_PROTO"])[0])) === "https"
        )
    );

    // Secure session configuration.
    ini_set("session.use_only_cookies", "1");
    ini_set("session.use_strict_mode", "1");
    ini_set("session.use_trans_sid", "0");

    session_set_cookie_params([
        "lifetime" => 0,
        "path" => "/",
        "secure" => $isHttps,
        "httponly" => true,
        "samesite" => "Lax"
    ]);

    // Start session.
    if (!session_start()) {
        $lastError = error_get_last();

        error_log(
            "SESSION ERROR: session_start() failed. " .
            ($lastError["message"] ?? "Unknown error")
        );

        http_response_code(500);
        exit("Session initialization failed. Check PHP error logs.");
    }

    error_log("SESSION: Successfully initialized.");
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    error_log("SESSION ERROR: Session is not active.");

    http_response_code(500);
    exit("Session initialization failed.");
}

// =====================================================
// 3. ENVIRONMENT VARIABLE HELPER
// =====================================================

if (!function_exists("env_or")) {
    function env_or(string $key, string $default): string
    {
        $value = getenv($key);

        if ($value === false || trim($value) === "") {
            return $default;
        }

        return trim($value);
    }
}

// =====================================================
// 4. DATABASE CONFIGURATION
// =====================================================

$isOnline = trim((string) getenv("DB_HOST")) !== "";

$host = env_or("DB_HOST", "127.0.0.1");
$port = env_or("DB_PORT", "3306");
$dbname = env_or("DB_NAME", "tadege_portfolio");
$username = env_or("DB_USER", "root");
$password = env_or("DB_PASSWORD", "root");

// =====================================================
// 5. PDO CONNECTION
// =====================================================

try {

    if (!extension_loaded("pdo_mysql")) {
        throw new RuntimeException("PDO MySQL extension is not installed.");
    }

    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_TIMEOUT => 10
    ];

    // =================================================
    // 6. AIVEN SSL CONFIGURATION
    // =================================================

    if ($isOnline) {

        $caFile = __DIR__ . "/ca.pem";

        if (!is_file($caFile) || !is_readable($caFile)) {
            throw new RuntimeException(
                "Aiven SSL certificate is missing or unreadable."
            );
        }

        if (!defined("PDO::MYSQL_ATTR_SSL_CA")) {
            throw new RuntimeException(
                "PDO MySQL SSL support is unavailable."
            );
        }

        $options[constant("PDO::MYSQL_ATTR_SSL_CA")] = $caFile;

        if (defined("PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT")) {
            $options[constant("PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT")] = true;
        }
    }

    // =================================================
    // 7. CONNECT TO MYSQL
    // =================================================

    $pdo = new PDO(
        $dsn,
        $username,
        $password,
        $options
    );

    error_log("DATABASE: Connection successful.");

} catch (Throwable $e) {

    error_log("DATABASE ERROR: " . $e->getMessage());

    http_response_code(500);
    exit("Database connection failed. Please check your server configuration.");
}
