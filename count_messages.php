<?php
require "db.php";
header("Content-Type: application/json");

// Visitors get nothing, only the logged-in admin gets the count
if (empty($_SESSION["admin"])) {
    echo json_encode(["count" => 0, "latest" => null]);
    exit;
}

try {
    $count = (int)$pdo->query(
        "SELECT COUNT(*) FROM contact_messages WHERE is_read = 0"
    )->fetchColumn();

    $latest = $pdo->query(
        "SELECT id, name, subject FROM contact_messages ORDER BY id DESC LIMIT 1"
    )->fetch(PDO::FETCH_ASSOC);

    echo json_encode(["count" => $count, "latest" => $latest ?: null]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["count" => 0, "latest" => null]);
}