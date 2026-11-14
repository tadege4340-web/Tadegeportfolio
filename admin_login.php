<?php
require "db.php";

// Password: tadege
$ADMIN_HASH = '$2b$12$WCZFVYjgCiP36ovI9BWQRujghnZARCLTTzi9/I0qx1DzqF8SZ7Do.';
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (password_verify($_POST["password"] ?? "", $ADMIN_HASH)) {
        session_regenerate_id(true);
        $_SESSION["admin"] = true;
        header("Location: index.php");
        exit;
    }
    $error = "Wrong password";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex items-center justify-center">
  <form method="post" class="bg-slate-900 border border-slate-700 rounded-2xl p-8 w-full max-w-sm space-y-4">
    <h1 class="text-xl font-bold">Admin Login</h1>
    <input type="password" name="password" placeholder="Password" required
           class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-indigo-500">
    <?php if ($error): ?>
      <p class="text-red-400 text-sm"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <button class="w-full bg-indigo-600 hover:bg-indigo-500 py-3 rounded-xl font-semibold">Login</button>
  </form>
</body>
</html>