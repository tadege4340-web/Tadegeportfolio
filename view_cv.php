
<?php

// ==========================================================
// SECURE FILE VIEWER AND DOWNLOAD SYSTEM
// Supports the file formats accepted by admin_upload_cv.php
// ==========================================================

require_once __DIR__ . "/db.php";

// ==========================================================
// GET FILE ID
// ==========================================================

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    http_response_code(400);
    exit("Invalid file ID.");
}

// ==========================================================
// CHECK ADMIN STATUS
// ==========================================================

$isAdmin = !empty($_SESSION["admin"]);

// ==========================================================
// GET FILE INFORMATION
// ==========================================================

try {

    if ($isAdmin) {

        // Admin can access public and private files
        $stmt = $pdo->prepare(
            "SELECT
                id,
                file_name,
                original_name,
                file_size,
                mime_type,
                is_public
             FROM cv_uploads
             WHERE id = ?
             LIMIT 1"
        );

        $stmt->execute([$id]);

    } else {

        // Visitors can access only published files
        $stmt = $pdo->prepare(
            "SELECT
                id,
                file_name,
                original_name,
                file_size,
                mime_type,
                is_public
             FROM cv_uploads
             WHERE id = ?
             AND is_public = 1
             LIMIT 1"
        );

        $stmt->execute([$id]);
    }

    $file = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    error_log("CV file database error: " . $e->getMessage());

    http_response_code(500);
    exit("Could not retrieve the file.");
}

// ==========================================================
// CHECK FILE EXISTS IN DATABASE
// ==========================================================

if (!$file) {
    http_response_code(404);
    exit("File not found or not publicly available.");
}

// ==========================================================
// VALIDATE STORED FILE NAME
// ==========================================================

$storedName = (string)$file["file_name"];

// Upload code creates 40 hexadecimal characters plus extension
if (!preg_match('/^[a-f0-9]{40}\.[a-z0-9]{1,10}$/i', $storedName)) {

    http_response_code(400);
    exit("Invalid stored filename.");
}

// ==========================================================
// FILE LOCATION
// ==========================================================

$uploadDirectory = __DIR__ . "/uploads/cvs/";

$realDirectory = realpath($uploadDirectory);

if ($realDirectory === false) {
    http_response_code(404);
    exit("Upload directory not found.");
}

$filePath = realpath($uploadDirectory . $storedName);

if (
    $filePath === false ||
    dirname($filePath) !== $realDirectory ||
    !is_file($filePath) ||
    !is_readable($filePath)
) {

    http_response_code(404);
    exit("The requested file does not exist.");
}

// ==========================================================
// GET ORIGINAL FILE INFORMATION
// ==========================================================

$originalName = basename((string)$file["original_name"]);

$extension = strtolower(
    pathinfo($originalName, PATHINFO_EXTENSION)
);

// Use MIME type saved when the administrator uploaded the file
$mimeType = trim((string)$file["mime_type"]);

// Fallback MIME type if database value is empty or invalid
if (
    $mimeType === "" ||
    !preg_match('/^[a-zA-Z0-9.+-]+\/[a-zA-Z0-9.+-]+$/', $mimeType)
) {

    $mimeType = "application/octet-stream";
}

// ==========================================================
// DETERMINE VIEW OR DOWNLOAD
// ==========================================================

$forceDownload = isset($_GET["download"]) &&
    $_GET["download"] === "1";

// Only formats generally safe and suitable for browser display
$inlineTypes = [

    "application/pdf",

    "image/jpeg",
    "image/png",
    "image/gif",
    "image/webp",
    "image/bmp",
    "image/tiff",
    "image/x-tiff",
    "image/x-icon",
    "image/vnd.microsoft.icon",
    "image/avif",
    "image/heic",
    "image/heif",

    "text/plain",

    "audio/mpeg",
    "audio/wav",
    "audio/x-wav",
    "audio/ogg",
    "audio/mp4",
    "audio/aac",
    "audio/flac",

    "video/mp4",
    "video/quicktime",
    "video/x-msvideo",
    "video/x-matroska",
    "video/webm",
    "video/mpeg"
];

// SVG and other active or unsupported document types are downloaded
$canViewInline = in_array($mimeType, $inlineTypes, true);

// Determine Content-Disposition
$disposition = (!$forceDownload && $canViewInline)
    ? "inline"
    : "attachment";

// ==========================================================
// SECURITY HEADERS
// ==========================================================

header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
header("Referrer-Policy: no-referrer");

header("Content-Type: " . $mimeType);

// Original filename for download
$safeAsciiName = preg_replace(
    '/[^A-Za-z0-9._-]/',
    '_',
    $originalName
);

header(
    "Content-Disposition: " . $disposition .
    '; filename="' . $safeAsciiName . '"' .
    "; filename*=UTF-8''" . rawurlencode($originalName)
);

// File size
$fileSize = filesize($filePath);

if ($fileSize === false) {
    http_response_code(500);
    exit("Could not read file size.");
}

header("Content-Length: " . $fileSize);

// Do not cache private files
header("Cache-Control: private, no-store, no-cache, must-revalidate");
header("Pragma: no-cache");

// ==========================================================
// SEND FILE
// ==========================================================

if ($_SERVER["REQUEST_METHOD"] === "HEAD") {
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    header("Allow: GET, HEAD");
    exit("Method not allowed.");
}

// Prevent previously buffered output from corrupting the file
while (ob_get_level() > 0) {
    ob_end_clean();
}

readfile($filePath);
exit;

