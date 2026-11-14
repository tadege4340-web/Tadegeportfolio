<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host   = 'localhost';
$port   = 3306;              // use 8889 if MAMP shows 8889
$dbname = 'tadege_portfolio';
$user   = 'root';
$pass   = 'root';            // MAMP default. Try '' if "root" fails

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['ok' => false, 'error' => 'Database connection failed.']);
    exit;
}