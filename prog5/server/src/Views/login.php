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

<body class="bg-gradient-to-b from-slate-50 to-slate-100 flex items-center justify-center min-h-screen p-4">
    <div class="w-full max-w-[420px]">
        <div class="text-center mb-6">
            <h1 class="text-3xl font-bold text-slate-900">Welcome Back</h1>
            <p class="text-slate-500 mt-1">Sign in to continue to ClassDoc</p>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="bg-red-50 text-red-600 p-3 rounded text-sm mb-4 border border-red-100">
                <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>

        <form action="/login" method="POST" class="space-y-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <div>
                <label class="block text-sm font-medium mb-1">Username</label>
                <input type="text" name="username" required class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-300" placeholder="yourname123">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Password</label>
                <input type="password" name="password" required class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-300" placeholder="••••••••">
            </div>
            <button type="submit" class="w-full bg-slate-900 hover:bg-slate-700 text-white py-2.5 rounded-lg font-medium">Sign In</button>
        </form>
    </div>
</body>

</html>
