
<?php
require_once __DIR__ . "/db.php";

// Only log out the administrator
unset($_SESSION["admin"]);

// Regenerate the session ID
session_regenerate_id(true);

// Redirect to portfolio
header("Location: index.php");
exit;
