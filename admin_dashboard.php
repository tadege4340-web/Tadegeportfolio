
<?php
require_once __DIR__ . "/db.php";

if (empty($_SESSION["admin"])) {
    header("Location: admin_login.php");
    exit;
}

$totalMessages = (int) $pdo->query(
    "SELECT COUNT(*) FROM contact_messages"
)->fetchColumn();

$unreadMessages = (int) $pdo->query(
    "SELECT COUNT(*) FROM contact_messages WHERE is_read = 0"
)->fetchColumn();

$totalCVs = (int) $pdo->query(
    "SELECT COUNT(*) FROM cv_uploads"
)->fetchColumn();

$publishedCVs = (int) $pdo->query(
    "SELECT COUNT(*) FROM cv_uploads WHERE is_public = 1"
)->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-950 text-slate-100">

<header class="border-b border-slate-800 bg-slate-900">
    <div class="max-w-6xl mx-auto px-6 py-5 flex justify-between items-center">
        <div>
            <h1 class="text-xl font-bold">Portfolio Admin</h1>
            <p class="text-sm text-slate-400">Administration Dashboard</p>
        </div>
        <a href="admin_logout.php"
           class="bg-red-600 hover:bg-red-500 px-4 py-2 rounded-lg text-sm font-semibold">
            Logout
        </a>
    </div>
</header>

<main class="max-w-6xl mx-auto px-6 py-10">

    <h2 class="text-2xl font-bold mb-2">Welcome, Admin</h2>
    <p class="text-slate-400 mb-8">Manage your portfolio messages and CV documents.</p>

    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-10">

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
            <p class="text-slate-400 text-sm">Total Messages</p>
            <p class="text-3xl font-bold mt-2"><?= $totalMessages ?></p>
        </div>

        <div class="bg-slate-900 border border-indigo-500/40 rounded-2xl p-6">
            <p class="text-slate-400 text-sm">Unread Notifications</p>
            <p class="text-3xl font-bold text-indigo-400 mt-2"><?= $unreadMessages ?></p>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
            <p class="text-slate-400 text-sm">Total CVs</p>
            <p class="text-3xl font-bold mt-2"><?= $totalCVs ?></p>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
            <p class="text-slate-400 text-sm">Published CVs</p>
            <p class="text-3xl font-bold text-green-400 mt-2"><?= $publishedCVs ?></p>
        </div>

    </div>

    <div class="grid md:grid-cols-2 gap-6">

        <a href="admin_message.php"
           class="block bg-slate-900 border border-slate-800 hover:border-indigo-500 rounded-2xl p-7 transition">
            <div class="flex justify-between items-center">
                <h3 class="text-xl font-bold">Notifications</h3>
                <span class="bg-indigo-600 text-white rounded-full px-3 py-1 text-sm">
                    <?= $unreadMessages ?> New
                </span>
            </div>
            <p class="text-slate-400 mt-3">
                View messages sent by visitors through your portfolio contact form.
            </p>
            <p class="text-indigo-400 mt-5 font-semibold">View Messages →</p>
        </a>

        <a href="admin_upload_cv.php"
           class="block bg-slate-900 border border-slate-800 hover:border-indigo-500 rounded-2xl p-7 transition">
            <h3 class="text-xl font-bold">Upload CV</h3>
            <p class="text-slate-400 mt-3">
                Upload a new CV and choose whether it should appear publicly on your portfolio.
            </p>
            <p class="text-indigo-400 mt-5 font-semibold">Upload Document →</p>
        </a>

    </div>

    <div class="mt-8">
        <a href="index.php" class="text-indigo-400 hover:text-indigo-300">
            ← View Public Portfolio
        </a>
    </div>

</main>
</body>
</html>
