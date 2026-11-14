<?php
require "db.php";
if (empty($_SESSION["admin"])) { header("Location: admin_login.php"); exit; }

// 1. Load first, so we can highlight the unread ones
$messages = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC")
                ->fetchAll(PDO::FETCH_ASSOC);

// 2. Then mark everything as read (the bell badge goes away)
$pdo->exec("UPDATE contact_messages SET is_read = 1 WHERE is_read = 0");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Messages | Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen">
  <div class="max-w-3xl mx-auto px-6 py-10">

    <div class="flex justify-between items-center mb-8">
      <h1 class="text-2xl font-bold">Messages (<?= count($messages) ?>)</h1>
      <div class="space-x-4 text-sm">
        <a href="index.php" class="text-indigo-400 hover:text-indigo-300">← Back to site</a>
        <a href="admin_logout.php" class="text-red-400 hover:text-red-300">Logout</a>
      </div>
    </div>

    <?php if (!$messages): ?>
      <p class="text-slate-400">No messages yet.</p>
    <?php endif; ?>

    <?php foreach ($messages as $m): ?>
      <div class="rounded-2xl p-6 mb-4 border <?= $m["is_read"] ? 'bg-slate-900 border-slate-800' : 'bg-indigo-950/60 border-indigo-500/60' ?>">
        <div class="flex justify-between items-start mb-2">
          <h2 class="font-bold text-lg"><?= htmlspecialchars($m["subject"]) ?></h2>
          <?php if (!$m["is_read"]): ?>
            <span class="text-xs bg-red-500 text-white font-bold px-2 py-0.5 rounded-full">NEW</span>
          <?php endif; ?>
        </div>
        <p class="text-sm text-slate-400 mb-3">
          <?= htmlspecialchars($m["name"]) ?>
          &lt;<a class="text-indigo-400" href="mailto:<?= htmlspecialchars($m["email"]) ?>"><?= htmlspecialchars($m["email"]) ?></a>&gt;
          · <?= htmlspecialchars($m["created_at"]) ?>
        </p>
        <p class="text-slate-200 leading-relaxed"><?= nl2br(htmlspecialchars($m["message"])) ?></p>
      </div>
    <?php endforeach; ?>

  </div>
</body>
</html>