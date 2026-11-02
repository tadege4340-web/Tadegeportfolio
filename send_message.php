<?php
// send_message.php
header('Content-Type: application/json');
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
    exit;
}

$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

if (!$name || !$email || !$subject || !$message || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Please fill all fields correctly.']);
    exit;
}

try {
    // Save to database
    $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $email, $subject, $message]);

    // Email notification to you
    $to      = 'taadegeyilkal@gmail.com';
    $mailSub = "📩 New Portfolio Message: $subject";
    $headers = "From: Portfolio Contact <no-reply@yourdomain.com>\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $body    = "New message from your portfolio website.\n\n"
             . "Name: $name\nEmail: $email\nSubject: $subject\n\n"
             . "Message:\n$message";
    @mail($to, $mailSub, $body, $headers);

    echo json_encode(['status' => 'success', 'message' => 'Message sent!']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Something went wrong.']);
}