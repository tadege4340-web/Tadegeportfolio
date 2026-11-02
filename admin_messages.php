<?php
// admin_messages.php
session_start();
require 'db.php';

// ===== CHANGE THIS PASSWORD =====
$ADMIN_PASSWORD = 'YourSecretPassword123!';
// ================================

if (isset($_POST['password'])) {
    if ($_POST['password'] === $ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        $loginError = 'Wrong password.';
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin_messages.php');
    exit;
}

if (isset($_SESSION['admin_logged_in'])) {
    if (isset($_GET['mark_read'])) {
        $pdo->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?")->execute([(int)$_GET['mark_read']]);
        header('Location: admin_messages.php');
        exit;
    }
    if (isset($_GET['delete'])) {
        $pdo->prepare("DELETE FROM contact_messages WHERE id = ?")->execute([(int)$_GET['delete']]);
        header('Location: admin_messages.php');
        exit;
    }
}

// LOGIN SCREEN
if (!isset($_SESSION['admin_logged_in'])) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Admin Login</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-slate-950 text-slate-100 flex items-center justify-center min-h-screen">
        <form method="POST" class="bg-slate-900 p-8 rounded-2xl border border-slate-800 w-full max-w-sm">
            <h1 class="text-2xl font-bold mb-6 text-center">🔒 Admin Login</h1>
            <?php if (!empty($loginError)): ?>
                <p class="text-red-400 text-sm mb-4 text-center"><?= $loginError ?></p>
            <?php endif; ?>
            <input type="password" name="password" required placeholder="Enter admin password"
                   class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 mb-4 text-slate-100 focus:outline-none focus:border-indigo-500">
            <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-500 rounded-xl py-3 font-semibold transition">
                Login
            </button>
        </form>
    </body>
    </html>
    <?php
    exit;
}

$messages = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
$unread   = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — Messages</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen">

    <!-- Real-time toast container -->
    <div id="toastContainer" class="fixed top-24 right-6 z-[100] space-y-3"></div>

    <header class="bg-slate-900 border-b border-slate-800 px-6 py-4 flex justify-between items-center sticky top-0 z-50">
        <div class="flex items-center gap-3">
            <i class="fa-solid fa-inbox text-indigo-400 text-xl"></i>
            <h1 class="text-xl font-bold">Admin — Messages</h1>
            <span id="unreadBadge" class="bg-red-500 text-white text-xs font-bold px-2.5 py-1 rounded-full <?= $unread > 0 ? '' : 'hidden' ?>">
                <?= $unread ?> new
            </span>
        </div>
        <div class="flex items-center gap-3">
            <button id="enableNotifBtn" class="text-xs bg-indigo-600 hover:bg-indigo-500 px-3 py-2 rounded-lg transition hidden">
                <i class="fa-solid fa-bell mr-1"></i> Enable Desktop Alerts
            </button>
            <a href="?logout=1" class="text-sm bg-slate-800 hover:bg-slate-700 px-4 py-2 rounded-lg transition">
                <i class="fa-solid fa-right-from-bracket mr-1"></i> Logout
            </a>
        </div>
    </header>

    <main class="max-w-5xl mx-auto p-6 space-y-4">
        <?php if (empty($messages)): ?>
            <p class="text-center text-slate-500 mt-20">No messages yet.</p>
        <?php endif; ?>

        <?php foreach ($messages as $m): ?>
            <div class="bg-slate-900 border <?= $m['is_read'] ? 'border-slate-800' : 'border-indigo-500/60' ?> rounded-2xl p-6">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h2 class="text-lg font-bold text-slate-100">
                            <?= htmlspecialchars($m['subject']) ?>
                            <?php if (!$m['is_read']): ?>
                                <span class="ml-2 text-xs bg-indigo-600 text-white px-2 py-0.5 rounded-full">NEW</span>
                            <?php endif; ?>
                        </h2>
                        <p class="text-sm text-slate-400 mt-1">
                            From: <strong class="text-slate-200"><?= htmlspecialchars($m['name']) ?></strong>
                            &lt;<?= htmlspecialchars($m['email']) ?>&gt;
                            &middot; <?= date('M d, Y H:i', strtotime($m['created_at'])) ?>
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <?php if (!$m['is_read']): ?>
                            <a href="?mark_read=<?= $m['id'] ?>" class="text-xs bg-slate-800 hover:bg-slate-700 px-3 py-1.5 rounded-lg transition" title="Mark as read">
                                <i class="fa-solid fa-check"></i>
                            </a>
                        <?php endif; ?>
                        <a href="?delete=<?= $m['id'] ?>" onclick="return confirm('Delete this message?')" class="text-xs bg-red-950 hover:bg-red-900 text-red-300 px-3 py-1.5 rounded-lg transition" title="Delete">
                            <i class="fa-solid fa-trash"></i>
                        </a>
                    </div>
                </div>
                <p class="text-slate-300 whitespace-pre-wrap text-sm bg-slate-950/50 p-4 rounded-xl border border-slate-800">
                    <?= htmlspecialchars($m['message']) ?>
                </p>
                <a href="mailto:<?= htmlspecialchars($m['email']) ?>?subject=Re: <?= urlencode($m['subject']) ?>"
                   class="inline-flex items-center gap-2 mt-3 text-sm text-indigo-400 hover:text-indigo-300 transition">
                    <i class="fa-solid fa-reply"></i> Reply by email
                </a>
            </div>
        <?php endforeach; ?>
    </main>

    <script>
        // ===== Toast Notification =====
        function showToast(message) {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = 'flex items-start gap-3 bg-emerald-950/95 border border-emerald-500/60 text-emerald-100 backdrop-blur-md rounded-xl px-5 py-4 shadow-2xl max-w-sm transition-all duration-500 translate-x-full opacity-0';
            toast.innerHTML = `
                <i class="fa-solid fa-bell text-lg mt-0.5 shrink-0 text-emerald-400"></i>
                <div class="text-sm font-medium leading-relaxed">${message}</div>
                <button class="ml-auto text-current/60 hover:text-current" onclick="this.parentElement.remove()">
                    <i class="fa-solid fa-xmark text-xs"></i>
                </button>
            `;
            container.appendChild(toast);
            requestAnimationFrame(() => toast.classList.remove('translate-x-full', 'opacity-0'));
            setTimeout(() => {
                toast.classList.add('translate-x-full', 'opacity-0');
                setTimeout(() => toast.remove(), 500);
            }, 8000);
        }

        // ===== Ask for desktop notification permission =====
        const enableBtn = document.getElementById('enableNotifBtn');
        if ('Notification' in window && Notification.permission === 'default') {
            enableBtn.classList.remove('hidden');
        }
        enableBtn.addEventListener('click', async () => {
            await Notification.requestPermission();
            enableBtn.classList.add('hidden');
        });

        // ===== Poll server for new messages every 10 seconds =====
        let lastCount = -1;
        async function checkNew() {
            try {
                const r = await fetch('check_new.php');
                const d = await r.json();

                // Update badge
                const badge = document.getElementById('unreadBadge');
                if (d.unread > 0) {
                    badge.textContent = d.unread + ' new';
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }

                // If new message arrived since last check, show toast + desktop notification
                if (lastCount !== -1 && d.unread > lastCount) {
                    showToast('📩 New message received! Refresh to view.');
                    if ('Notification' in window && Notification.permission === 'granted') {
                        new Notification('📩 New Portfolio Message', {
                            body: d.latest
                                ? `From ${d.latest.name}: ${d.latest.subject}`
                                : 'You have a new message.',
                            icon: '/download.jpg'
                        });
                    }
                }
                lastCount = d.unread;
            } catch (e) { /* ignore */ }
        }
        checkNew();
        setInterval(checkNew, 10000);
    </script>
</body>
</html>