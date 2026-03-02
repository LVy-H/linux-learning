<?php

declare(strict_types=1);

use App\Core\Auth;
use App\Core\Csrf;

$me = Auth::user();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'App') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-gradient-to-b from-slate-50 to-slate-100 text-slate-800">
    <div class="max-w-6xl mx-auto p-4 md:p-6">
    <nav class="bg-white/95 backdrop-blur border border-slate-200 shadow-sm rounded-xl px-4 py-3 mb-6 flex flex-wrap gap-3 items-center">
        <a class="text-slate-900 font-bold" href="/">ClassDoc</a>
        <?php if ($me): ?>
            <a class="text-slate-700 hover:text-slate-900" href="/users">Users</a>
            <a class="text-slate-700 hover:text-slate-900" href="/assignments">Assignments</a>
            <?php if (($me['role'] ?? '') === 'student'): ?>
                <a class="text-slate-700 hover:text-slate-900" href="/me">My Profile</a>
            <?php endif; ?>
            <?php if (($me['role'] ?? '') === 'teacher'): ?>
                <a class="text-slate-700 hover:text-slate-900" href="/teacher/students/new">Add Student</a>
                <a class="text-slate-700 hover:text-slate-900" href="/teacher/submissions">Submissions</a>
            <?php endif; ?>
            <span class="ml-auto text-sm text-slate-500">
                <?= htmlspecialchars($me['fullname'] ?? $me['username']) ?> · <?= htmlspecialchars($me['role'] ?? '') ?>
            </span>
            <form method="POST" action="/logout">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
                <button class="px-3 py-2 bg-slate-900 hover:bg-slate-700 text-white rounded-lg text-sm" type="submit">Logout</button>
            </form>
        <?php else: ?>
            <a class="ml-auto px-3 py-2 bg-slate-900 hover:bg-slate-700 text-white rounded-lg text-sm" href="/login">Login</a>
        <?php endif; ?>
    </nav>

    <?= $content ?? '' ?>
    </div>
</body>

</html>
