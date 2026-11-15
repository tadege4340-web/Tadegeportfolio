<?php
/**
 * Database connection for local MAMP and Render deployment (Aiven MySQL).
 *
 * On Render, set these in the service Environment settings:
 *   DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASSWORD
 * Never commit real credentials.
 */

// 1) Hide PHP warnings from visitors FIRST, so they never leak into pages or JSON.
ini_set("display_errors", "0");
ini_set("display_startup_errors", "0");
error_reporting(E_ALL);

// 2) Start the session only if no output was sent yet.
if (session_status() !== PHP_SESSION_ACTIVE && !headers_sent()) {
    $isHttps = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off")
        || (isset($_SERVER["HTTP_X_FORWARDED_PROTO"])
            && strtolower((string) $_SERVER["HTTP_X_FORWARDED_PROTO"]) === "https");

    session_set_cookie_params([
        "lifetime" => 0,
        "path"     => "/",
        "domain"   => "",
        "secure"   => $isHttps,
        "httponly" => true,
        "samesite" => "Lax"
    ]);

    session_start();
}

// 3) Read configuration. Local MAMP defaults are used only when variables are absent.
function env_or(string $key, string $default): string
{
    $value = getenv($key);
    return ($value === false || trim($value) === "") ? $default : trim($value);
}

$isOnline = trim((string) getenv("DB_HOST")) !== "";

$host     = env_or("DB_HOST", "127.0.0.1");
$port     = env_or("DB_PORT", "3306");
$dbname   = env_or("DB_NAME", "tadege_portfolio");
$username = env_or("DB_USER", "root");
$password = env_or("DB_PASSWORD", "root");

// 4) Connect.
try {
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_TIMEOUT            => 10
    ];

    // SSL only online (Aiven requires it). Local MAMP stays without SSL.
    if ($isOnline) {
        $caFile = __DIR__ . "/ca.pem";

        if (is_file($caFile)) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = $caFile;
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
        } else {
            error_log("ca.pem not found, connecting without certificate check.");
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }
    }

    $pdo = new PDO($dsn, $username, $password, $options);

} catch (PDOException $e) {
    // Full details go only to the server log, never to the browser.
    error_log("Portfolio database connection failed: " . $e->getMessage());
    http_response_code(500);
    exit("Database connection failed. Please try again later.");
}