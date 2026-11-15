
<?php
// Start output buffering to prevent accidental output issues.
ob_start();

// Load database and session configuration.
require_once __DIR__ . "/db.php";

// Make sure the session is active before destroying it.
if (session_status() !== PHP_SESSION_ACTIVE) {
    error_log("Logout error: PHP session is not active.");
    http_response_code(500);
    exit("Logout failed. Session could not be initialized.");
}

// Clear all session variables.
$_SESSION = [];

// Remove the session cookie.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        "",
        [
            "expires" => time() - 42000,
            "path" => $params["path"],
            "domain" => $params["domain"],
            "secure" => $params["secure"],
            "httponly" => $params["httponly"],
            "samesite" => $params["samesite"] ?? "Lax"
        ]
    );
}

// Destroy the session.
if (!session_destroy()) {
    error_log("Logout error: session_destroy() failed.");
}

// Redirect to admin login.
header("Location: admin_login.php", true, 303);
exit;
