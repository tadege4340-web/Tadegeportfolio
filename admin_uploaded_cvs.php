
<?php
require_once __DIR__ . "/db.php";

// Ensure db.php successfully initialized the session.
if (session_status() !== PHP_SESSION_ACTIVE) {
    error_log("Admin uploaded CVs: Session is not active after db.php.");
    http_response_code(500);
    exit("Session initialization failed. Please check db.php.");
}

// Check administrator login.
if (empty($_SESSION["admin"])) {
    header("Location: admin_login.php");
    exit;
}

// Initialize variables.
$cvs = [];
$error = "";

// Escape output safely.
function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

// Format file size.
function formatFileSize($bytes): string
{
    $bytes = (float)$bytes;

    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . " GB";
    }

    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . " MB";
    }

    if ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . " KB";
    }

    return number_format($bytes, 0) . " Bytes";
}

// Load uploaded CV files.
try {
    $stmt = $pdo->query("
        SELECT
            id,
            original_name,
            file_size,
            mime_type,
            uploaded_at,
            is_public
        FROM cv_uploads
        ORDER BY id DESC
    ");

    $cvs = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Throwable $exception) {
    error_log(
        "CV listing error: " . $exception->getMessage()
    );

    $error = "Could not load uploaded files. Please check the database connection.";
}

// Check upload page.
$uploadPageExists = is_file(__DIR__ . "/admin_upload_cv.php");

// Use relative links so local MAMP and Render both work.
$uploadUrl = "admin_upload_cv.php";
$dashboardUrl = "admin_dashboard.php";
$websiteUrl = "index.php";

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Uploaded Files | Admin Dashboard</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    >

    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .upload-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 13px 22px;
            background: #059669;
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-size: 14px;
            font-weight: bold;
            transition: 0.3s ease;
        }

        .upload-button:hover {
            background: #047857;
            transform: translateY(-2px);
        }

        .file-card {
            transition: 0.25s ease;
        }

        .file-card:hover {
            border-color: #6366f1;
            transform: translateY(-2px);
        }
    </style>
</head>

<body class="bg-slate-950 text-slate-100 min-h-screen p-4 sm:p-6">

<div class="max-w-5xl mx-auto">

    <!-- HEADER -->
    <div class="flex flex-wrap items-center justify-between gap-4 mb-8">

        <div>
            <h1 class="text-2xl sm:text-3xl font-bold">
                <i class="fa-solid fa-folder-open text-emerald-400 mr-2"></i>
                Uploaded Files
            </h1>

            <p class="text-sm text-slate-400 mt-2">
                Manage, view and download your uploaded documents.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">

            <?php if ($uploadPageExists): ?>

                <a href="<?= e($uploadUrl) ?>" class="upload-button">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    Upload New
                </a>

            <?php else: ?>

                <span class="bg-red-900/40 text-red-300 px-4 py-3 rounded-xl text-sm">
                    <i class="fa-solid fa-triangle-exclamation mr-2"></i>
                    Upload page missing
                </span>

            <?php endif; ?>

            <a href="<?= e($dashboardUrl) ?>"
               class="text-sm text-slate-300 hover:text-white">
                <i class="fa-solid fa-arrow-left mr-1"></i>
                Dashboard
            </a>

            <a href="<?= e($websiteUrl) ?>"
               class="text-sm text-slate-300 hover:text-white">
                <i class="fa-solid fa-globe mr-1"></i>
                Website
            </a>

        </div>
    </div>

    <!-- ERROR MESSAGE -->
    <?php if ($error !== ""): ?>

        <div class="bg-red-900/40 border border-red-700 text-red-300 p-4 rounded-xl mb-6">
            <i class="fa-solid fa-circle-exclamation mr-2"></i>
            <?= e($error) ?>
        </div>

    <?php endif; ?>

    <!-- MISSING UPLOAD PAGE -->
    <?php if (!$uploadPageExists): ?>

        <div class="bg-yellow-900/30 border border-yellow-700 text-yellow-300 p-4 rounded-xl mb-6">

            <strong>Upload page not found!</strong>

            <p class="text-sm mt-2">
                Create admin_upload_cv.php inside the project folder.
            </p>

        </div>

    <?php endif; ?>

    <!-- TOTAL FILES -->
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 mb-6 flex items-center justify-between">

        <div>
            <p class="text-sm text-slate-400">
                Total uploaded files
            </p>

            <h2 class="text-3xl font-bold text-white mt-1">
                <?= count($cvs) ?>
            </h2>
        </div>

        <div class="bg-indigo-500/10 p-4 rounded-xl">
            <i class="fa-solid fa-file-lines text-indigo-400 text-2xl"></i>
        </div>

    </div>

    <!-- FILE LIST -->
    <?php if (empty($cvs)): ?>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-10 text-center text-slate-400">

            <i class="fa-solid fa-inbox text-5xl mb-4 text-slate-600"></i>

            <h2 class="text-lg font-bold text-white mb-2">
                No Files Uploaded
            </h2>

            <p class="text-sm">
                Click Upload New to add your first document.
            </p>

        </div>

    <?php else: ?>

        <div class="space-y-4">

            <?php foreach ($cvs as $c): ?>

                <?php
                    $originalName = $c["original_name"] ?? "Unnamed file";

                    $extension = strtolower(
                        pathinfo($originalName, PATHINFO_EXTENSION)
                    );

                    $fileId = (int)$c["id"];

                    $viewUrl = "cv_file.php?id=" . $fileId;

                    $downloadUrl = "cv_file.php?id=" . $fileId . "&download=1";
                ?>

                <div class="file-card bg-slate-900 border border-slate-800 rounded-xl p-4 sm:p-5 flex flex-wrap items-center justify-between gap-4">

                    <!-- FILE INFORMATION -->
                    <div class="flex items-center gap-4 min-w-0 flex-1">

                        <div class="w-12 h-12 bg-indigo-500/10 rounded-xl flex items-center justify-center shrink-0">

                            <i class="fa-solid fa-file text-indigo-400 text-2xl"></i>

                        </div>

                        <div class="min-w-0 flex-1">

                            <p class="text-sm font-semibold text-white break-words">
                                <?= e($originalName) ?>
                            </p>

                            <p class="text-xs text-slate-500 mt-2">

                                <?= e(strtoupper($extension ?: "FILE")) ?>

                                <span class="mx-1">·</span>

                                <?= e(formatFileSize($c["file_size"] ?? 0)) ?>

                                <span class="mx-1">·</span>

                                <?= e($c["uploaded_at"] ?? "") ?>

                            </p>

                            <p class="text-xs mt-2">

                                <?php if ((int)($c["is_public"] ?? 0) === 1): ?>

                                    <span class="text-emerald-400">
                                        <i class="fa-solid fa-globe mr-1"></i>
                                        Public
                                    </span>

                                <?php else: ?>

                                    <span class="text-yellow-400">
                                        <i class="fa-solid fa-lock mr-1"></i>
                                        Private
                                    </span>

                                <?php endif; ?>

                            </p>

                        </div>

                    </div>

                    <!-- VIEW AND DOWNLOAD -->
                    <div class="flex items-center gap-2 shrink-0">

                        <a
                            href="<?= e($viewUrl) ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-2 text-xs bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-4 py-3 rounded-lg transition"
                        >
                            <i class="fa-solid fa-eye"></i>
                            View
                        </a>

                        <a
                            href="<?= e($downloadUrl) ?>"
                            class="inline-flex items-center gap-2 text-xs bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold px-4 py-3 rounded-lg border border-slate-700 transition"
                        >
                            <i class="fa-solid fa-download"></i>
                            Download
                        </a>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

    <!-- FOOTER -->
    <div class="text-center text-xs text-slate-600 mt-10 pb-5">
        Admin File Management | All Rights Reserved
    </div>

</div>

</body>
</html>
