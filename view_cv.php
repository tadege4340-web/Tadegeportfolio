<?php
// view_cv.php — if ?file= given, show that CV. Otherwise list all uploaded CVs.

$uploadDir = __DIR__ . '/uploads/';

// ---------- SINGLE FILE VIEW ----------
if (isset($_GET['file'])) {
    $requested = basename($_GET['file']); // prevent path traversal
    $path = $uploadDir . $requested;

    if (!is_file($path)) {
        http_response_code(404);
        echo "<h2 style='font-family:sans-serif;color:#ef4444;text-align:center;margin-top:50px'>File not found.</h2>";
        exit;
    }

    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

    // PDFs → display inline
    if ($ext === 'pdf') {
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $requested . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }

    // DOC/DOCX → force download
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $requested . '"');
    header('Content-Length: ' . filesize($path));
    readfile($path);
    exit;
}

// ---------- LIST ALL FILES ----------
$files = [];
if (is_dir($uploadDir)) {
    foreach (scandir($uploadDir) as $f) {
        if ($f === '.' || $f === '..') continue;
        $files[] = $f;
    }
}
// newest first
usort($files, function ($a, $b) use ($uploadDir) {
    return filemtime($uploadDir . $b) - filemtime($uploadDir . $a);
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uploaded CVs | Tadege Yilkal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-950 text-slate-100 font-sans min-h-screen p-6">
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-2xl font-bold">
                <i class="fa-solid fa-folder-open text-emerald-400 mr-2"></i>
                Uploaded CVs
            </h1>
            <a href="index.html" class="text-sm text-slate-400 hover:text-emerald-400 transition">
                <i class="fa-solid fa-arrow-left mr-1"></i> Back to Portfolio
            </a>
        </div>

        <?php if (empty($files)): ?>
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-10 text-center text-slate-400">
                <i class="fa-solid fa-inbox text-4xl mb-3 text-slate-600"></i>
                <p>No CVs uploaded yet.</p>
            </div>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($files as $f):
                    $size = round(filesize($uploadDir . $f) / 1024, 1);
                    $date = date('Y-m-d H:i', filemtime($uploadDir . $f));
                    $ext  = strtolower(pathinfo($f, PATHINFO_EXTENSION));
                    $icon = $ext === 'pdf' ? 'fa-file-pdf text-red-400'
                          : ($ext === 'docx' ? 'fa-file-word text-blue-400'
                                             : 'fa-file-word text-blue-300');
                ?>
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 flex items-center justify-between gap-4 hover:border-emerald-500/50 transition">
                    <div class="flex items-center gap-3 min-w-0">
                        <i class="fa-solid <?= $icon ?> text-2xl"></i>
                        <div class="min-w-0">
                            <p class="text-sm font-medium truncate"><?= htmlspecialchars($f) ?></p>
                            <p class="text-xs text-slate-500"><?= $size ?> KB · <?= $date ?></p>
                        </div>
                    </div>
                    <div class="flex gap-2 shrink-0">
                        <a href="view_cv.php?file=<?= urlencode($f) ?>" target="_blank"
                           class="text-xs bg-emerald-600 hover:bg-emerald-500 text-white font-semibold px-3 py-2 rounded-lg transition">
                            <i class="fa-solid fa-eye mr-1"></i>View
                        </a>
                        <a href="download_cv.php?file=<?= urlencode($f) ?>"
                           class="text-xs bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold px-3 py-2 rounded-lg border border-slate-700 transition">
                            <i class="fa-solid fa-download mr-1"></i>Download
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>