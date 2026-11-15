
<?php
require_once __DIR__ . "/db.php";
require_once __DIR__ . "/vendor/autoload.php";

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

// Validate file ID
$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    http_response_code(400);
    exit("Invalid file ID.");
}

// Find file in database
try {
    $stmt = $pdo->prepare(
        "SELECT * FROM cv_uploads WHERE id = ? LIMIT 1"
    );
    $stmt->execute([$id]);
    $cv = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("CV database error: " . $e->getMessage());
    http_response_code(500);
    exit("Could not retrieve the requested file.");
}

if (!$cv) {
    http_response_code(404);
    exit("File not found in database.");
}

// Check permissions
$isAdmin = !empty($_SESSION["admin"]);

if (!$isAdmin && (int)$cv["is_public"] !== 1) {
    http_response_code(403);
    exit("You do not have permission to access this file.");
}

// Validate stored filename
$storedName = $cv["file_name"];

if (
    !is_string($storedName) ||
    !preg_match('/\A[a-f0-9]{40}\.[a-z0-9]{1,10}\z/i', $storedName)
) {
    http_response_code(400);
    exit("Invalid stored filename.");
}

// Allowed MIME types
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

$mimeType = $cv["mime_type"];

if (!in_array($mimeType, $allowedMimeTypes, true)) {
    http_response_code(415);
    exit("Unsupported file type.");
}

// Original filename
$originalName = basename(
    str_replace("\\", "/", $cv["original_name"])
);

$originalName = preg_replace(
    '/[\x00-\x1F\x7F]/',
    '_',
    $originalName
);

// Download option
$download = isset($_GET["download"]) && $_GET["download"] === "1";

// Browser preview formats
$previewTypes = [
    "application/pdf",
    "image/jpeg",
    "image/png",
    "image/gif",
    "image/webp",
    "image/bmp"
];

$canPreview = in_array($mimeType, $previewTypes, true);

// Preview unavailable page
if (!$download && !$canPreview) {

    $downloadUrl = "cv_file.php?id=" . (int)$cv["id"] . "&download=1";
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
                This file format cannot be displayed in your browser.
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
                <a href="<?= $isAdmin ? 'admin_uploaded_cvs.php' : 'index.php#cv' ?>"
                   class="text-slate-400 hover:text-white text-sm">

                    <i class="fa-solid fa-arrow-left mr-1"></i>
                    <?= $isAdmin ? 'Back to Uploaded Files' : 'Back to Portfolio' ?>
                </a>
            </div>

        </div>

    </body>
    </html>
    <?php
    exit;
}

// Supabase Storage configuration
$endpoint = getenv("SUPABASE_S3_ENDPOINT");
$region = getenv("SUPABASE_S3_REGION");
$accessKey = getenv("SUPABASE_S3_ACCESS_KEY");
$secretKey = getenv("SUPABASE_S3_SECRET_KEY");
$bucket = getenv("SUPABASE_S3_BUCKET");

if (!$endpoint || !$region || !$accessKey || !$secretKey || !$bucket) {
    error_log("Supabase Storage environment variables are missing.");
    http_response_code(500);
    exit("File storage is not configured correctly.");
}

// Connect to Supabase Storage
try {

    $s3 = new S3Client([
        "version" => "latest",
        "region" => $region,
        "endpoint" => $endpoint,
        "use_path_style_endpoint" => true,
        "credentials" => [
            "key" => $accessKey,
            "secret" => $secretKey
        ],
        "signature_version" => "v4"
    ]);

    // Retrieve file from Supabase
    $result = $s3->getObject([
        "Bucket" => $bucket,
        "Key" => $storedName
    ]);

    $body = $result["Body"];
    $fileSize = $result["ContentLength"];

    // Clear output buffers
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    // Prepare response headers
    header("X-Content-Type-Options: nosniff");
    header("Cache-Control: private, no-store");
    header("Content-Type: " . $mimeType);

    if ($fileSize !== null) {
        header("Content-Length: " . $fileSize);
    }

    $disposition = ($download || !$canPreview)
        ? "attachment"
        : "inline";

    $safeFilename = preg_replace(
        '/[^A-Za-z0-9._-]/',
        '_',
        $originalName
    );

    header(
        "Content-Disposition: " . $disposition .
        '; filename="' . $safeFilename . '"' .
        "; filename*=UTF-8''" . rawurlencode($originalName)
    );

    // Send file contents to browser
    echo $body->getContents();
    exit;

} catch (AwsException $e) {

    error_log(
        "Supabase file retrieval error: " .
        $e->getAwsErrorMessage()
    );

    $statusCode = $e->getStatusCode();

    if ($statusCode === 404) {
        http_response_code(404);
        exit("File not found in Supabase Storage.");
    }

    http_response_code(500);
    exit("Could not retrieve the file from storage.");

} catch (Throwable $e) {

    error_log("CV file error: " . $e->getMessage());

    http_response_code(500);
    exit("An unexpected error occurred while opening the file.");
}
