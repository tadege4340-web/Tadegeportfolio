
<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/db.php";

// Load manually installed PHPMailer files
require_once __DIR__ . "/PHPMailer/Exception.php";
require_once __DIR__ . "/PHPMailer/PHPMailer.php";
require_once __DIR__ . "/PHPMailer/SMTP.php";

// Return JSON response
function respond($success, $message)
{
    echo json_encode([
        "ok" => $success,
        "error" => $success ? null : $message
    ]);
    exit;
}

// Accept POST requests only
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    respond(false, "Invalid request method.");
}

// Get and validate form data
$name = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");
$subject = trim($_POST["subject"] ?? "");
$message = trim($_POST["message"] ?? "");

if ($name === "" || $email === "" || $subject === "" || $message === "") {
    http_response_code(400);
    respond(false, "Please complete all fields.");
}

if (strlen($name) > 100 ||
    strlen($email) > 190 ||
    strlen($subject) > 200 ||
    strlen($message) > 5000) {
    http_response_code(400);
    respond(false, "One or more fields are too long.");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    respond(false, "Please enter a valid email address.");
}

// Save message in MySQL first
try {
    $stmt = $pdo->prepare(
        "INSERT INTO contact_messages
        (name, email, subject, message, is_read, created_at)
        VALUES
        (:name, :email, :subject, :message, 0, NOW())"
    );

    $stmt->execute([
        ":name" => $name,
        ":email" => $email,
        ":subject" => $subject,
        ":message" => $message
    ]);

} catch (PDOException $e) {
    error_log("Contact database error: " . $e->getMessage());

    http_response_code(500);
    respond(false, "Your message could not be saved. Please try again later.");
}

// Database save succeeded. Now attempt email notification.
try {

    $config = require __DIR__ . "/mail_config.php";

    $smtpUsername = trim($config["smtp_username"] ?? "");
    $smtpPassword = trim($config["smtp_password"] ?? "");
    $recipients = $config["recipients"] ?? [];

    // Do not fail the visitor's submission if SMTP is not configured.
    if ($smtpUsername === "" ||
        $smtpPassword === "" ||
        empty($recipients)) {

        error_log("Contact email notification is not configured.");

        respond(true, "Message saved successfully.");
    }

    $mail = new PHPMailer(true);

    // SMTP configuration
    $mail->isSMTP();
    $mail->Host = $config["smtp_host"] ?? "smtp.gmail.com";
    $mail->SMTPAuth = true;
    $mail->Username = $smtpUsername;
    $mail->Password = $smtpPassword;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = (int)($config["smtp_port"] ?? 587);

    $mail->CharSet = "UTF-8";

    // Sender must be the authenticated Gmail account
    $mail->setFrom(
        $smtpUsername,
        $config["from_name"] ?? "Portfolio Contact Form"
    );

    // Send to both notification addresses
    foreach ($recipients as $recipient) {
        if (filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            $mail->addAddress($recipient);
        }
    }

    if (count($mail->getToAddresses()) === 0) {
        throw new Exception("No valid notification recipients configured.");
    }

    // Reply directly to the visitor
    $mail->addReplyTo($email, $name);

    $safeName = htmlspecialchars($name, ENT_QUOTES, "UTF-8");
    $safeEmail = htmlspecialchars($email, ENT_QUOTES, "UTF-8");
    $safeSubject = htmlspecialchars($subject, ENT_QUOTES, "UTF-8");
    $safeMessage = nl2br(
        htmlspecialchars($message, ENT_QUOTES, "UTF-8")
    );

    $mail->isHTML(true);
    $mail->Subject = "New Portfolio Contact: " . $subject;

    $mail->Body = "
        <h2>New Portfolio Contact Message</h2>

        <p><strong>Visitor Name:</strong> {$safeName}</p>

        <p><strong>Visitor Email:</strong> {$safeEmail}</p>

        <p><strong>Subject:</strong> {$safeSubject}</p>

        <hr>

        <h3>Message</h3>

        <p>{$safeMessage}</p>

        <hr>

        <p>This notification was sent from your portfolio contact form.</p>
    ";

    $mail->AltBody =
        "New Portfolio Contact Message\n\n" .
        "Name: {$name}\n" .
        "Email: {$email}\n" .
        "Subject: {$subject}\n\n" .
        "Message:\n{$message}";

    $mail->send();

} catch (Exception $e) {

    // The visitor's message is already safely stored in MySQL.
    error_log("Contact email error: " . $e->getMessage());

} catch (Throwable $e) {

    error_log("Contact notification error: " . $e->getMessage());
}

// Message was saved, even if email delivery failed.
respond(true, "Message saved successfully.");
