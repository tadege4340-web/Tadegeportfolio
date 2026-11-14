<?php
require "db.php";
header("Content-Type: application/json");

$name    = trim($_POST["name"] ?? "");
$email   = trim($_POST["email"] ?? "");
$subject = trim($_POST["subject"] ?? "");
$message = trim($_POST["message"] ?? "");

if ($name === "" || !filter_var($email, FILTER_VALIDATE_EMAIL) || $subject === "" || $message === "") {
    http_response_code(400);
    echo json_encode(["ok" => false, "error" => "Please fill all fields correctly."]);
    exit;
}

$stmt = $pdo->prepare(
    "INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)"
);
$stmt->execute([$name, $email, $subject, $message]);

echo json_encode(["ok" => true]);