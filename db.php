<?php
/**
 * Database connection for local MAMP and Render deployment.
 * IMPORTANT: On Render, set DB_HOST, DB_PORT, DB_NAME, DB_USER and DB_PASSWORD
 * in the service Environment settings. Never commit real credentials.
 */

// Start the session before any HTML, whitespace, or other output.
if (session_status() !== PHP_SESSION_ACTIVE) {
    $isHttps = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off")
        || (isset($_SERVER["HTTP_X_FORWARDED_PROTO"])
            && strtolower((string) $_SERVER["HTTP_X_FORWARDED_PROTO"]) === "https");

    session_set_cookie_params([
        "lifetime" => 0,
        "path" => "/",
        "domain" => "",
        "secure" => $isHttps,
        "httponly" => true,
        "samesite" => "Lax"
    ]);

    session_start();
}

// Do not display PHP warnings or database details to public visitors.
ini_set("display_errors", "0");
ini_set("display_startup_errors", "0");
error_reporting(E_ALL);

// Read deployment credentials from environment variables.
// Local MAMP defaults are used only when environment variables are absent.
$host = getenv("DB_HOST");
$port = getenv("DB_PORT");
$dbname = getenv("DB_NAME");
$username = getenv("DB_USER");
$password = getenv("DB_PASSWORD");

if ($host === false || $host === "") $host = "127.0.0.1";
if ($port === false || $port === "") $port = "3306";
if ($dbname === false || $dbname === "") $dbname = "tadege_portfolio";
if ($username === false || $username === "") $username = "root";
if ($password === false || $password === "") $password = "root";

try {
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_TIMEOUT => 10
    ]);

} catch (PDOException $e) {
    // Full details go only to the server log, never to the browser.
    error_log("Portfolio database connection failed: " . $e->getMessage());
    http_response_code(500);
    exit("Database connection failed. Please check the database service and its environment variables.");
}
