<?php
require_once __DIR__ . "/db.php";
require_once __DIR__ . "/vendor/autoload.php";

use Aws\S3\S3Client;

if (session_status() !== PHP_SESSION_ACTIVE) {
    http_response_code(500);
    exit("Session initialization failed.");
}

if (empty($_SESSION["admin"])) {
    header("Location: admin_login.php");
    exit;
}

function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

function storageEnv(string $name): string
{
    $value = getenv($name);
    return $value === false ? "" : trim($value);
}

$maxFileSize = 20 * 1024 * 1024;
$maxFiles = 10;

$successMessage = "";
$errorMessages = [];

if (empty($_SESSION["upload_csrf"])) {
    $_SESSION["upload_csrf"] = bin2hex(random_bytes(32));
}

$allowedMimeTypes = [
    "pdf" => ["application/pdf"],
    "doc" => ["application/msword", "application/octet-stream"],
    "docx" => [
        "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
        "application/zip",
        "application/x-zip-compressed"
    ],
    "ppt" => ["application/vnd.ms-powerpoint", "application/octet-stream"],
    "pptx" => [
        "application/vnd.openxmlformats-officedocument.presentationml.presentation",
        "application/zip",
        "application/x-zip-compressed"
    ],
    "xls" => ["application/vnd.ms-excel", "application/octet-stream"],
    "xlsx" => [
        "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        "application/zip",
        "application/x-zip-compressed"
    ],
    "txt" => ["text/plain"],
    "rtf" => ["application/rtf", "text/rtf", "text/plain"],
    "csv" => ["text/plain", "text/csv", "application/csv", "application/vnd.ms-excel"],
    "jpg" => ["image/jpeg"],
    "jpeg" => ["image/jpeg"],
    "png" => ["image/png"],
    "gif" => ["image/gif"],
    "webp" => ["image/webp"],
    "bmp" => ["image/bmp", "image/x-ms-bmp"],
    "tif" => ["image/tiff"],
    "tiff" => ["image/tiff"],
    "zip" => ["application/zip", "application/x-zip-compressed"],
    "rar" => ["application/vnd.rar", "application/x-rar-compressed"],
    "7z" => ["application/x-7z-compressed"]
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $submittedToken = $_POST["csrf_token"] ?? "";

    if (
        !is_string($submittedToken) ||
        !hash_equals($_SESSION["upload_csrf"], $submittedToken)
    ) {
        $errorMessages[] = "Invalid form request. Refresh the page and try again.";

    } elseif (!class_exists("finfo")) {
        $errorMessages[] = "PHP Fileinfo extension is unavailable.";

    } elseif (!class_exists(S3Client::class)) {
        $errorMessages[] = "AWS SDK is not installed correctly.";

    } elseif (
        !isset($_FILES["documents"]["name"]) ||
        !is_array($_FILES["documents"]["name"])
    ) {
        $errorMessages[] = "Please select at least one file.";

    } else {

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
            $errorMessages[] = "Supabase Storage configuration is incomplete.";

        } elseif (!filter_var($endpoint, FILTER_VALIDATE_URL)) {
            $errorMessages[] = "The Supabase S3 endpoint is invalid.";

        } else {

            $fileCount = count($_FILES["documents"]["name"]);

            if ($fileCount < 1 || $fileCount > $maxFiles) {
                $errorMessages[] = "Select between 1 and {$maxFiles} files.";

            } else {

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

                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $uploadedCount = 0;
                    $isPublic = isset($_POST["is_public"]) ? 1 : 0;

                    for ($i = 0; $i < $fileCount; $i++) {

                        $originalName = basename(
                            str_replace(
                                "\\",
                                "/",
                                (string)$_FILES["documents"]["name"][$i]
                            )
                        );

                        $tmpName = $_FILES["documents"]["tmp_name"][$i];
                        $fileSize = (int)$_FILES["documents"]["size"][$i];
                        $uploadError = (int)$_FILES["documents"]["error"][$i];

                        if ($uploadError !== UPLOAD_ERR_OK) {
                            $errorMessages[] = $originalName . ": Upload failed. Error code " . $uploadError . ".";
                            continue;
                        }

                        if (!is_uploaded_file($tmpName)) {
                            $errorMessages[] = $originalName . ": Invalid uploaded file.";
                            continue;
                        }

                        if ($fileSize <= 0 || $fileSize > $maxFileSize) {
                            $errorMessages[] = $originalName . ": File must be 20 MB or smaller.";
                            continue;
                        }

                        $extension = strtolower(
                            pathinfo($originalName, PATHINFO_EXTENSION)
                        );

                        if (!isset($allowedMimeTypes[$extension])) {
                            $errorMessages[] = $originalName . ": File extension is not allowed.";
                            continue;
                        }

                        $detectedMime = $finfo->file($tmpName);

                        if (
                            $detectedMime === false ||
                            !in_array(
                                $detectedMime,
                                $allowedMimeTypes[$extension],
                                true
                            )
                        ) {
                            $errorMessages[] = $originalName . ": File type is invalid.";
                            continue;
                        }

                        // Generate one unique key for BOTH Supabase and MySQL.
                        $storedName = bin2hex(random_bytes(20)) . "." . $extension;

                        $objectUploaded = false;

                        try {

                            // 1. Upload the actual file to Supabase.
                            $s3->putObject([
                                "Bucket" => $bucket,
                                "Key" => $storedName,
                                "SourceFile" => $tmpName,
                                "ContentType" => $detectedMime,
                                "ContentLength" => $fileSize
                            ]);

                            $objectUploaded = true;

                            // 2. Verify that the object exists in Supabase.
                            $s3->headObject([
                                "Bucket" => $bucket,
                                "Key" => $storedName
                            ]);

                            // 3. Save exactly the same key in Aiven MySQL.
                            $sql = "
                                INSERT INTO cv_uploads (
                                    file_name,
                                    original_name,
                                    file_size,
                                    mime_type,
                                    uploader_name,
                                    uploader_email,
                                    uploaded_at,
                                    ip_address,
                                    is_public,
                                    storage_key
                                ) VALUES (
                                    :file_name,
                                    :original_name,
                                    :file_size,
                                    :mime_type,
                                    :uploader_name,
                                    :uploader_email,
                                    NOW(),
                                    :ip_address,
                                    :is_public,
                                    :storage_key
                                )
                            ";

                            $stmt = $pdo->prepare($sql);

                            $stmt->execute([
                                ":file_name" => $storedName,
                                ":original_name" => $originalName,
                                ":file_size" => $fileSize,
                                ":mime_type" => $detectedMime,
                                ":uploader_name" => "Admin",
                                ":uploader_email" => $_SESSION["admin_email"] ?? "",
                                ":ip_address" => $_SERVER["REMOTE_ADDR"] ?? "",
                                ":is_public" => $isPublic,
                                ":storage_key" => $storedName
                            ]);

                            $uploadedCount++;

                        } catch (Throwable $exception) {

                            // Remove the object if its database record was not saved.
                            if ($objectUploaded) {
                                try {
                                    $s3->deleteObject([
                                        "Bucket" => $bucket,
                                        "Key" => $storedName
                                    ]);
                                } catch (Throwable $deleteError) {
                                    error_log(
                                        "Supabase cleanup error: " .
                                        $deleteError->getMessage()
                                    );
                                }
                            }

                            error_log(
                                "CV upload error for " . $originalName . ": " .
                                $exception->getMessage()
                            );

                            $errorMessages[] =
                                $originalName . ": Upload or database saving failed. Check Render logs.";
                        }
                    }

                    if ($uploadedCount > 0) {
                        $successMessage =
                            $uploadedCount . " file(s) uploaded and saved successfully.";

                        $_SESSION["upload_csrf"] = bin2hex(random_bytes(32));
                    }

                } catch (Throwable $exception) {

                    error_log(
                        "Supabase connection error: " .
                        $exception->getMessage()
                    );

                    $errorMessages[] =
                        "Could not connect to Supabase Storage. Check your storage settings and Render logs.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Upload Files | Admin Dashboard</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .upload-area {
            border: 2px dashed #475569;
            transition: 0.3s ease;
        }

        .upload-area:hover {
            border-color: #10b981;
            background: rgba(16, 185, 129, 0.05);
        }

        .main-button {
            background: #059669;
            transition: 0.3s ease;
        }

        .main-button:hover {
            background: #047857;
            transform: translateY(-2px);
        }
    </style>
</head>

<body class="bg-slate-950 text-slate-100 min-h-screen p-4 sm:p-8">

<div class="max-w-3xl mx-auto">

    <div class="flex flex-wrap items-center justify-between gap-4 mb-8">

        <div>
            <h1 class="text-3xl font-bold">
                <i class="fa-solid fa-cloud-arrow-up text-emerald-400 mr-2"></i>
                Upload New Files
            </h1>

            <p class="text-slate-400 mt-2">
                Upload and manage your portfolio documents.
            </p>
        </div>

        <a href="admin_uploaded_cvs.php"
           class="bg-slate-800 hover:bg-slate-700 px-4 py-3 rounded-xl text-sm">
            <i class="fa-solid fa-arrow-left mr-2"></i>
            Back to Files
        </a>

    </div>

    <?php if ($successMessage): ?>
        <div class="bg-emerald-900/30 border border-emerald-700 text-emerald-300 rounded-xl p-4 mb-6">
            <i class="fa-solid fa-circle-check mr-2"></i>
            <?= e($successMessage) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($errorMessages)): ?>
        <div class="bg-red-900/30 border border-red-700 text-red-300 rounded-xl p-4 mb-6">
            <h3 class="font-bold mb-2">Upload problems</h3>

            <ul class="list-disc pl-6 space-y-1 text-sm">
                <?php foreach ($errorMessages as $message): ?>
                    <li><?= e($message) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 shadow-xl">

        <form method="POST"
              action="admin_upload_cv.php"
              enctype="multipart/form-data">

            <input type="hidden"
                   name="csrf_token"
                   value="<?= e($_SESSION["upload_csrf"]) ?>">

            <label class="block font-semibold mb-3">
                Select Your Documents
            </label>

            <label for="documents"
                   class="upload-area flex flex-col items-center justify-center rounded-2xl p-10 text-center cursor-pointer">

                <i class="fa-solid fa-cloud-arrow-up text-5xl text-emerald-400 mb-4"></i>

                <span class="text-lg font-bold">
                    Click here to choose files
                </span>

                <span class="text-sm text-slate-400 mt-2">
                    You can select multiple files.
                </span>

                <span class="text-xs text-slate-500 mt-3">
                    Maximum 20 MB per file · Up to 10 files
                </span>

                <input type="file"
                       name="documents[]"
                       id="documents"
                       multiple
                       required
                       class="hidden">
            </label>

            <div id="selectedFiles" class="mt-5 text-sm text-slate-400">
                No files selected.
            </div>

            <div class="mt-7 bg-slate-800/70 rounded-xl p-4">

                <label class="flex items-center gap-3 cursor-pointer">

                    <input type="checkbox"
                           name="is_public"
                           value="1"
                           checked
                           class="w-5 h-5 accent-emerald-500">

                    <span>
                        <strong class="text-white">Make files public</strong>

                        <span class="block text-xs text-slate-400 mt-1">
                            Allows visitors to access these files through your website.
                        </span>
                    </span>

                </label>

            </div>

            <button type="submit"
                    class="main-button w-full mt-7 py-4 rounded-xl text-white font-bold">

                <i class="fa-solid fa-upload mr-2"></i>
                Upload Selected Files

            </button>

        </form>

    </div>

    <div class="mt-6 bg-slate-900 border border-slate-800 rounded-xl p-5">

        <h3 class="font-bold text-white mb-3">
            <i class="fa-solid fa-circle-info text-blue-400 mr-2"></i>
            Supported Formats
        </h3>

        <p class="text-sm text-slate-400 leading-7">
            PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, TXT, RTF, CSV,
            JPG, JPEG, PNG, GIF, WEBP, BMP, TIFF, ZIP, RAR and 7Z.
        </p>

    </div>

</div>

<script>
const fileInput = document.getElementById("documents");
const selectedFiles = document.getElementById("selectedFiles");

fileInput.addEventListener("change", function () {
    const files = Array.from(this.files);

    if (files.length === 0) {
        selectedFiles.textContent = "No files selected.";
        return;
    }

    if (files.length > 10) {
        alert("You can select a maximum of 10 files.");
        this.value = "";
        selectedFiles.textContent = "No files selected.";
        return;
    }

    selectedFiles.innerHTML = "";

    files.forEach(function(file) {
        const item = document.createElement("div");

        item.className =
            "flex justify-between gap-3 bg-slate-800 rounded-lg p-3 mb-2";

        const name = document.createElement("span");
        name.textContent = file.name;
        name.className = "break-all";

        const size = document.createElement("span");
        size.textContent = (file.size / 1024 / 1024).toFixed(2) + " MB";
        size.className = "text-emerald-400 shrink-0";

        item.appendChild(name);
        item.appendChild(size);

        selectedFiles.appendChild(item);
    });
});
</script>

</body>
</html>