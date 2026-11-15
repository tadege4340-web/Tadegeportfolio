<?php
require_once __DIR__ . "/db.php";

if (session_status() !== PHP_SESSION_ACTIVE) {
    http_response_code(500);
    exit("Session initialization failed.");
}

if (empty($_SESSION["admin"])) {
    header("Location: admin_login.php");
    exit;
}

$cvs = [];
$error = "";

function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

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

try {
    $stmt = $pdo->query("
        SELECT
            id,
            file_name,
            original_name,
            storage_key,
            file_size,
            mime_type,
            uploaded_at,
            is_public
        FROM cv_uploads
        ORDER BY id DESC
    ");

    $cvs = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Throwable $exception) {
    error_log("CV listing error: " . $exception->getMessage());
    $error = "Could not load uploaded files. Please check the database connection.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Uploaded Files | Admin Dashboard</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-slate-950 text-slate-100 min-h-screen p-4 sm:p-6">

<div class="max-w-5xl mx-auto">

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

        <div class="flex flex-wrap gap-3">
            <a href="admin_upload_cv.php"
               class="bg-emerald-600 hover:bg-emerald-500 px-5 py-3 rounded-xl font-bold text-sm">
                <i class="fa-solid fa-cloud-arrow-up mr-2"></i>
                Upload New
            </a>

            <a href="admin_dashboard.php"
               class="text-sm text-slate-300 hover:text-white py-3">
                Dashboard
            </a>

            <a href="index.php"
               class="text-sm text-slate-300 hover:text-white py-3">
                Website
            </a>
        </div>
    </div>

    <?php if ($error !== ""): ?>
        <div class="bg-red-900/40 border border-red-700 text-red-300 p-4 rounded-xl mb-6">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 mb-6">
        <p class="text-sm text-slate-400">Total database records</p>
        <h2 class="text-3xl font-bold mt-1"><?= count($cvs) ?></h2>
    </div>

    <?php if (empty($cvs)): ?>

        <div class="bg-slate-900 border border-slate-800 rounded-xl p-10 text-center">
            <i class="fa-solid fa-inbox text-5xl text-slate-600 mb-4"></i>
            <h2 class="text-lg font-bold">No Files Uploaded</h2>
        </div>

    <?php else: ?>

        <div class="space-y-4">

            <?php foreach ($cvs as $c): ?>

                <?php
                    $id = (int)$c["id"];
                    $originalName = $c["original_name"] ?: "Unnamed file";

                    $storageKey = trim((string)($c["storage_key"] ?? ""));

                    if ($storageKey === "") {
                        $storageKey = trim((string)($c["file_name"] ?? ""));
                    }

                    $hasKey = ($storageKey !== "");
                    $extension = strtoupper(
                        pathinfo($originalName, PATHINFO_EXTENSION)
                    );
                ?>

                <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">

                    <div class="flex flex-wrap items-center justify-between gap-4">

                        <div class="flex items-center gap-4 min-w-0 flex-1">

                            <div class="w-12 h-12 bg-indigo-500/10 rounded-xl flex items-center justify-center">
                                <i class="fa-solid fa-file text-indigo-400 text-2xl"></i>
                            </div>

                            <div class="min-w-0">
                                <p class="font-semibold break-words">
                                    <?= e($originalName) ?>
                                </p>

                                <p class="text-xs text-slate-500 mt-2">
                                    ID: <?= $id ?>
                                    · <?= e($extension ?: "FILE") ?>
                                    · <?= e(formatFileSize($c["file_size"] ?? 0)) ?>
                                </p>

                                <p class="text-xs text-slate-500 mt-1">
                                    <?= e($c["uploaded_at"] ?? "") ?>
                                </p>

                                <?php if ($hasKey): ?>
                                    <p class="text-xs text-emerald-400 mt-2">
                                        <i class="fa-solid fa-key mr-1"></i>
                                        Storage key: <?= e($storageKey) ?>
                                    </p>
                                <?php else: ?>
                                    <p class="text-xs text-red-400 mt-2">
                                        <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                                        Missing storage key
                                    </p>
                                <?php endif; ?>

                                <p class="text-xs mt-2">
                                    <?php if ((int)$c["is_public"] === 1): ?>
                                        <span class="text-emerald-400">Public</span>
                                    <?php else: ?>
                                        <span class="text-yellow-400">Private</span>
                                    <?php endif; ?>
                                </p>
                            </div>

                        </div>

                        <div class="flex items-center gap-2">

                            <a href="cv_file.php?id=<?= $id ?>"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="bg-indigo-600 hover:bg-indigo-500 px-4 py-3 rounded-lg text-xs font-bold">
                                <i class="fa-solid fa-eye mr-1"></i>
                                View
                            </a>

                            <a href="cv_file.php?id=<?= $id ?>&download=1"
                               class="bg-slate-800 hover:bg-slate-700 border border-slate-700 px-4 py-3 rounded-lg text-xs font-bold">
                                <i class="fa-solid fa-download mr-1"></i>
                                Download
                            </a>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

    <div class="text-center text-xs text-slate-600 mt-10 pb-5">
        Admin File Management | All Rights Reserved
    </div>

</div>

</body>
</html>