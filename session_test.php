<?php
require_once __DIR__ . "/db.php";

$_SESSION["test"] = "Session is working";

echo "Session status: " . session_status() . "<br>";
echo "Session ID: " . htmlspecialchars(session_id()) . "<br>";
echo "Session value: " . htmlspecialchars($_SESSION["test"]) . "<br>";
echo "Database: Connected";