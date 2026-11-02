<?php
// db.php
$host = 'localhost';
$dbname = 'your_database_name';   // ← CHANGE
$user = 'root';                    // ← CHANGE
$pass = '';                        // ← CHANGE

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Database connection failed.');
}