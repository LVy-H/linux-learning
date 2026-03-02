<?php

declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ClassDoc | Secure Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-50 flex items-center justify-center min-h-screen p-4">
    <div class="w-full max-w-[400px]">
        <h1 class="text-2xl font-bold text-slate-900 mb-4">Welcome Back</h1>

        <?php if (!empty($error_message)): ?>
            <div class="bg-red-50 text-red-600 p-3 rounded text-sm mb-4 border border-red-100">
                <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>

        <form action="/login" method="POST" class="space-y-4 bg-white p-6 rounded-xl border">
            <div>
                <label class="block text-sm mb-1">Username</label>
                <input type="text" name="username" required class="w-full px-3 py-2 border rounded" placeholder="yourname123">
            </div>
            <div>
                <label class="block text-sm mb-1">Password</label>
                <input type="password" name="password" required class="w-full px-3 py-2 border rounded" placeholder="••••••••">
            </div>
            <button type="submit" class="w-full bg-slate-900 text-white py-2 rounded">Sign In</button>
        </form>
    </div>
</body>

</html>
