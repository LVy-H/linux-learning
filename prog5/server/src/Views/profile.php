<?php

declare(strict_types=1);

use App\Core\Csrf;

$title = 'My Profile';
ob_start();
?>
<h1 class="text-2xl font-bold mb-4">My Profile</h1>

<?php if (!empty($error)): ?>
    <div class="bg-red-50 text-red-700 p-2 border border-red-200 rounded mb-3"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="bg-green-50 text-green-700 p-2 border border-green-200 rounded mb-3"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<form method="POST" action="/me/update" enctype="multipart/form-data" class="bg-white border border-slate-200 rounded-xl p-5 space-y-3 max-w-2xl shadow-sm">
    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
    <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?> (cannot change)</p>
    <p><strong>Full Name:</strong> <?= htmlspecialchars($user['fullname']) ?> (cannot change)</p>

    <div>
        <label class="block text-sm mb-1">New password (optional)</label>
        <input class="w-full border border-slate-300 rounded-lg p-2.5" type="password" name="password">
    </div>
    <div>
        <label class="block text-sm mb-1">Email</label>
        <input class="w-full border border-slate-300 rounded-lg p-2.5" type="email" name="email" value="<?= htmlspecialchars((string)$user['email']) ?>">
    </div>
    <div>
        <label class="block text-sm mb-1">Phone</label>
        <input class="w-full border border-slate-300 rounded-lg p-2.5" name="phone" value="<?= htmlspecialchars((string)$user['phone']) ?>">
    </div>

    <div>
        <label class="block text-sm mb-1">Avatar file upload</label>
        <input class="w-full border border-slate-300 rounded-lg p-2" type="file" name="avatar_file" accept="image/*">
    </div>

    <div>
        <label class="block text-sm mb-1">Or avatar URL (will be downloaded and stored)</label>
        <input class="w-full border border-slate-300 rounded-lg p-2.5" type="url" name="avatar_url" placeholder="https://example.com/avatar.jpg">
    </div>

    <button class="px-4 py-2 bg-slate-900 hover:bg-slate-700 text-white rounded-lg" type="submit">Update profile</button>
</form>
<?php
$content = (string) ob_get_clean();
require __DIR__ . '/layout.php';
