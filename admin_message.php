
<?php
require_once __DIR__ . "/db.php";

// Check administrator login
if (empty($_SESSION["admin"])) {
    header("Location: admin_login.php");
    exit;
}

try {
    // Get all contact messages
    $stmt = $pdo->query(
        "SELECT * FROM contact_messages ORDER BY created_at DESC"
    );

    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Mark unread messages as read
    $pdo->exec(
        "UPDATE contact_messages SET is_read = 1 WHERE is_read = 0"
    );

} catch (Throwable $e) {
    error_log("Admin messages error: " . $e->getMessage());

    http_response_code(500);
    exit("Unable to load messages. Please check the server logs.");
}

function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Notifications | Admin</title>

    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-slate-950 text-slate-100">

<div class="max-w-4xl mx-auto px-6 py-10">

    <div class="flex flex-wrap justify-between items-center gap-4 mb-8">

        <div>
            <h1 class="text-2xl font-bold">
                Notifications (<?= count($messages) ?>)
            </h1>

            <p class="text-slate-400 text-sm mt-2">
                Messages received through your portfolio contact form.
            </p>
        </div>

        <div class="flex gap-4 text-sm">
            <a href="admin_dashboard.php"
               class="text-indigo-400 hover:text-indigo-300">
                Dashboard
            </a>

            <a href="admin_logout.php"
               class="text-red-400 hover:text-red-300">
                Logout
            </a>
        </div>

    </div>

    <?php if (empty($messages)): ?>

        <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 text-slate-400">
            No messages yet.
        </div>

    <?php else: ?>

        <?php foreach ($messages as $m): ?>

            <article class="rounded-2xl p-6 mb-5 border border-slate-800 bg-slate-900">

                <h2 class="font-bold text-lg mb-2">
                    <?= e($m["subject"] ?? "No subject") ?>
                </h2>

                <p class="text-sm text-slate-400 mb-4">
                    From:
                    <?= e($m["name"] ?? "Unknown") ?>

                    &lt;<?= e($m["email"] ?? "") ?>&gt;

                    · <?= e($m["created_at"] ?? "") ?>
                </p>

                <p class="text-slate-200 leading-relaxed whitespace-pre-wrap"><?= e($m["message"] ?? "") ?></p>

                <?php if (!empty($m["email"])): ?>

                    <a href="mailto:<?= e($m["email"]) ?>"
                       class="inline-block mt-5 text-indigo-400 hover:text-indigo-300">
                        Reply by Email →
                    </a>

                <?php endif; ?>

            </article>

        <?php endforeach; ?>

    <?php endif; ?>

</div>

</body>
</html>
