<?php

use PHPMailer\PHPMailer\PHPMailer;

ob_start();

require_once __DIR__ . "/db.php";

header("Content-Type: application/json; charset=UTF-8");

// Always return clean JSON: {"ok": true} or {"ok": false, "error": "..."}
function respond(bool $success, string $message = "", int $code = 200): void
{
    if (ob_get_length()) {
        ob_clean();
    }
    http_response_code($code);
    echo json_encode([
        "ok"    => $success,
        "error" => $success ? null : $message
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Turn any unexpected PHP error into a JSON reply instead of an empty page
set_exception_handler(function (Throwable $e) {
    error_log("Contact fatal: " . $e->getMessage());
    respond(false, "Server error. Please try again later.", 500);
});

if (($_SERVER["REQUEST_METHOD"] ?? "") !== "POST") {
    respond(false, "Invalid request method.", 405);
}

// Get and validate form data
$name    = trim((string)($_POST["name"] ?? ""));
$email   = trim((string)($_POST["email"] ?? ""));
$subject = trim((string)($_POST["subject"] ?? ""));
$message = trim((string)($_POST["message"] ?? ""));

if ($name === "" || $email === "" || $subject === "" || $message === "") {
    respond(false, "Please complete all fields.", 400);
}

if (mb_strlen($name) > 100 ||
    mb_strlen($email) > 150 ||
    mb_strlen($subject) > 200 ||
    mb_strlen($message) > 5000) {
    respond(false, "One or more fields are too long.", 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(false, "Please enter a valid email address.", 400);
}

// 1) Save the message in MySQL first
try {
    $stmt = $pdo->prepare(
        "INSERT INTO contact_messages (name, email, subject, message, is_read)
         VALUES (:name, :email, :subject, :message, 0)"
    );
    $stmt->execute([
        ":name"    => $name,
        ":email"   => $email,
        ":subject" => $subject,
        ":message" => $message
    ]);
} catch (PDOException $e) {
    error_log("Contact database error: " . $e->getMessage());
    respond(false, "Your message could not be saved. Please try again later.", 500);
}

// 2) Optional email notification. It must never break the reply.
try {
    $config = [];

    // Local file (MAMP) if it exists
    $configFile = __DIR__ . "/mail_config.php";
    if (is_file($configFile)) {
        $loaded = require $configFile;
        if (is_array($loaded)) {
            $config = $loaded;
        }
    }

    // Render environment variables override the file
    $envUser = getenv("SMTP_USERNAME");
    $envPass = getenv("SMTP_PASSWORD");
    $envTo   = getenv("NOTIFY_EMAILS"); // comma separated

    if ($envUser) { $config["smtp_username"] = $envUser; }
    if ($envPass) { $config["smtp_password"] = $envPass; }
    if ($envTo)   { $config["recipients"] = array_map("trim", explode(",", $envTo)); }

    $smtpUsername = trim((string)($config["smtp_username"] ?? ""));
    $smtpPassword = trim((string)($config["smtp_password"] ?? ""));
    $recipients   = $config["recipients"] ?? [];

    $phpmailerReady =
        is_file(__DIR__ . "/PHPMailer/PHPMailer.php") &&
        is_file(__DIR__ . "/PHPMailer/SMTP.php") &&
        is_file(__DIR__ . "/PHPMailer/Exception.php");

    if ($smtpUsername === "" || $smtpPassword === "" || empty($recipients) || !$phpmailerReady) {
        error_log("Contact email notification is not configured.");
        respond(true);
    }

    require_once __DIR__ . "/PHPMailer/Exception.php";
    require_once __DIR__ . "/PHPMailer/PHPMailer.php";
    require_once __DIR__ . "/PHPMailer/SMTP.php";

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = $config["smtp_host"] ?? "smtp.gmail.com";
    $mail->SMTPAuth   = true;
    $mail->Username   = $smtpUsername;
    $mail->Password   = $smtpPassword;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = (int)($config["smtp_port"] ?? 587);
    $mail->Timeout    = 8;      // do not hang if the port is blocked
    $mail->CharSet    = "UTF-8";

    $mail->setFrom($smtpUsername, $config["from_name"] ?? "Portfolio Contact Form");

    foreach ($recipients as $recipient) {
        if (filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            $mail->addAddress($recipient);
        }
    }

    if (count($mail->getToAddresses()) === 0) {
        throw new RuntimeException("No valid notification recipients configured.");
    }

    $mail->addReplyTo($email, $name);

    $safeName    = htmlspecialchars($name, ENT_QUOTES, "UTF-8");
    $safeEmail   = htmlspecialchars($email, ENT_QUOTES, "UTF-8");
    $safeSubject = htmlspecialchars($subject, ENT_QUOTES, "UTF-8");
    $safeMessage = nl2br(htmlspecialchars($message, ENT_QUOTES, "UTF-8"));

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
        "Name: {$name}\nEmail: {$email}\nSubject: {$subject}\n\nMessage:\n{$message}";

    $mail->send();

} catch (Throwable $e) {
    // The message is already saved in MySQL, so only log the email problem.
    error_log("Contact email error: " . $e->getMessage());
}

respond(true);