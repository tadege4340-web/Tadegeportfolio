<?php
require_once __DIR__ . "/db.php";
require_once __DIR__ . "/vendor/autoload.php";

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

if (session_status() !== PHP_SESSION_ACTIVE) {
    http_response_code(500);
    exit("Session initialization failed.");
}

// 1. Validate file ID.
$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    http_response_code(400);
    exit("Invalid file ID.");
}

// 2. Retrieve file record.
try {
    $stmt = $pdo->prepare("
        SELECT *
        FROM cv_uploads
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$id]);
    $cv = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (Throwable $exception) {
    error_log("CV database error: " . $exception->getMessage());

    http_response_code(500);
    exit("Could not retrieve the requested file.");
}

if (!$cv) {
    http_response_code(404);
    exit("File not found in database.");
}

// 3. Check permissions.
$isAdmin = !empty($_SESSION["admin"]);

if (!$isAdmin && (int)$cv["is_public"] !== 1) {
    http_response_code(403);
    exit("You do not have permission to access this file.");
}

// 4. Get storage key.
// New records should have storage_key.
// Older records can fall back to file_name.
$storedName = trim((string)($cv["storage_key"] ?? ""));

if ($storedName === "") {
    $storedName = trim((string)($cv["file_name"] ?? ""));
}

if (
    $storedName === "" ||
    strlen($storedName) > 255 ||
    preg_match('//u', $storedName) !== 1 ||
    str_contains($storedName, "/") ||
    str_contains($storedName, "\\") ||
    preg_match('/[\x00-\x1F\x7F]/', $storedName)
) {
    http_response_code(404);
    exit("This file does not have a valid storage key.");
}

// 5. Validate MIME type.
$allowedMimeTypes = [
    "application/pdf",
    "application/msword",
    "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
    "application/vnd.ms-powerpoint",
    "application/vnd.openxmlformats-officedocument.presentationml.presentation",
    "application/vnd.ms-excel",
    "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
    "text/plain",
    "text/csv",
    "application/csv",
    "application/rtf",
    "text/rtf",
    "image/jpeg",
    "image/png",
    "image/gif",
    "image/webp",
    "image/bmp",
    "image/x-ms-bmp",
    "image/tiff",
    "application/zip",
    "application/x-zip-compressed",
    "application/vnd.rar",
    "application/x-rar-compressed",
    "application/x-7z-compressed"
];

$mimeType = (string)($cv["mime_type"] ?? "");

if (!in_array($mimeType, $allowedMimeTypes, true)) {
    http_response_code(415);
    exit("Unsupported file type.");
}

// 6. Get original filename.
$originalName = basename(
    str_replace("\\", "/", (string)($cv["original_name"] ?? "download"))
);

$originalName = preg_replace(
    '/[\x00-\x1F\x7F]/',
    "_",
    $originalName
);

if ($originalName === "") {
    $originalName = "download";
}

// 7. View or download.
$download = isset($_GET["download"]) && $_GET["download"] === "1";

$previewTypes = [
    "application/pdf",
    "image/jpeg",
    "image/png",
    "image/gif",
    "image/webp",
    "image/bmp"
];

$canPreview = in_array($mimeType, $previewTypes, true);

// 8. Show download-only message for unsupported preview formats.
if (!$download && !$canPreview) {

    $downloadUrl = "cv_file.php?id=" .
                   (int)$cv["id"] . "&download=1";

    $backUrl = $isAdmin
        ? "admin_uploaded_cvs.php"
        : "index.php#cv";

    $backText = $isAdmin
        ? "Back to Uploaded Files"
        : "Back to Portfolio";

    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>Preview Not Available</title>

        <script src="https://cdn.tailwindcss.com"></script>

        <link rel="stylesheet"
              href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    </head>

    <body class="bg-slate-950 text-white min-h-screen flex items-center justify-center p-5">

        <div class="max-w-lg w-full bg-slate-900 border border-slate-800 rounded-2xl p-8 text-center">

            <i class="fa-solid fa-file-circle-exclamation text-6xl text-yellow-400 mb-6"></i>

            <h1 class="text-2xl font-bold mb-3">
                Preview Not Available
            </h1>

            <p class="text-slate-400 mb-3">
                This file format cannot be displayed directly in your browser.
                Please download it to view it.
            </p>

            <p class="text-white font-semibold break-words mb-6">
                <?= htmlspecialchars($originalName, ENT_QUOTES, "UTF-8") ?>
            </p>

            <a href="<?= htmlspecialchars($downloadUrl, ENT_QUOTES, "UTF-8") ?>"
               class="inline-block bg-emerald-600 hover:bg-emerald-500 px-6 py-3 rounded-xl font-bold">

                <i class="fa-solid fa-download mr-2"></i>
                Download File

            </a>

            <div class="mt-5">

                <a href="<?= htmlspecialchars($backUrl, ENT_QUOTES, "UTF-8") ?>"
                   class="text-slate-400 hover:text-white text-sm">

                    <i class="fa-solid fa-arrow-left mr-1"></i>
                    <?= htmlspecialchars($backText, ENT_QUOTES, "UTF-8") ?>

                </a>

            </div>

        </div>

    </body>
    </html>
    <?php
    exit;
}

// 9. Read Supabase configuration.
function storageEnv(string $name): string
{
    $value = getenv($name);
    return $value === false ? "" : trim($value);
}

$endpoint = storageEnv("SUPABASE_S3_ENDPOINT");
$region = storageEnv("SUPABASE_S3_REGION");
$accessKey = storageEnv("SUPABASE_S3_ACCESS_KEY");
$secretKey = storageEnv("SUPABASE_S3_SECRET_KEY");
$bucket = storageEnv("SUPABASE_S3_BUCKET");

if (
    $endpoint === "" ||
    $region === "" ||
    $accessKey === "" ||
    $secretKey === "" ||
    $bucket === ""
) {
    error_log("Supabase Storage configuration is incomplete.");

    http_response_code(500);
    exit("File storage is not configured correctly.");
}

// 10. Connect and retrieve file.
try {

    $s3 = new S3Client([
        "version" => "latest",
        "region" => $region,
        "endpoint" => rtrim($endpoint, "/"),
        "use_path_style_endpoint" => true,
        "signature_version" => "v4",
        "credentials" => [
            "key" => $accessKey,
            "secret" => $secretKey
        ]
    ]);

    $result = $s3->getObject([
        "Bucket" => $bucket,
        "Key" => $storedName
    ]);

    $body = $result["Body"];
    $fileSize = $result["ContentLength"] ?? null;

    // 11. Clear output buffers before sending file headers.
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    // 12. Send response headers.
    header("X-Content-Type-Options: nosniff");
    header("Cache-Control: private, no-store");
    header("Content-Type: " . $mimeType);

    if ($fileSize !== null) {
        header("Content-Length: " . (int)$fileSize);
    }

    $disposition = ($download || !$canPreview)
        ? "attachment"
        : "inline";

    $safeFilename = preg_replace(
        '/[^A-Za-z0-9._-]/',
        "_",
        $originalName
    );

    header(
        "Content-Disposition: " .
        $disposition .
        '; filename="' . $safeFilename . '"' .
        "; filename*=UTF-8''" . rawurlencode($originalName)
    );

    // 13. Send the actual file.
    echo $body->getContents();
    exit;

} catch (AwsException $exception) {

    $statusCode = $exception->getStatusCode();

    error_log(
        "Supabase file retrieval error. CV ID: " . $id .
        ", Storage key: " . $storedName .
        ", HTTP status: " . ($statusCode ?? "unknown") .
        ", Error: " . $exception->getAwsErrorMessage()
    );

    if ($statusCode === 404) {
        http_response_code(404);
        exit("File not found in Supabase Storage. Check the storage key.");
    }

    http_response_code(500);
    exit("Could not retrieve the file from storage.");

} catch (Throwable $exception) {

    error_log(
        "CV file error: " . $exception->getMessage()
    );

    http_response_code(500);
    exit("An unexpected error occurred while opening the file.");
}