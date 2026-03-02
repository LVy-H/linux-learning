<?php

declare(strict_types=1);

$title = $mode === 'create' ? 'Add Student' : 'Edit Student';
ob_start();
?>
<h1 class="text-2xl font-bold mb-4"><?= htmlspecialchars($title) ?></h1>

<?php if (!empty($error)): ?>
    <div class="bg-red-50 text-red-700 p-2 border border-red-200 rounded mb-3"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST" action="<?= $mode === 'create' ? '/teacher/students' : '/teacher/students/' . (int)$student['id'] . '/update' ?>" class="bg-white border border-slate-200 rounded-xl p-5 space-y-3 max-w-2xl shadow-sm">
    <div>
        <label class="block text-sm mb-1">Username</label>
        <input class="w-full border border-slate-300 rounded-lg p-2.5" name="username" required value="<?= htmlspecialchars((string)($student['username'] ?? '')) ?>">
    </div>
    <div>
        <label class="block text-sm mb-1">Password <?= $mode === 'edit' ? '(leave blank to keep)' : '' ?></label>
        <input class="w-full border border-slate-300 rounded-lg p-2.5" type="password" name="password" <?= $mode === 'create' ? 'required' : '' ?>>
    </div>
    <div>
        <label class="block text-sm mb-1">Full name</label>
        <input class="w-full border border-slate-300 rounded-lg p-2.5" name="fullname" required value="<?= htmlspecialchars((string)($student['fullname'] ?? '')) ?>">
    </div>
    <div>
        <label class="block text-sm mb-1">Email</label>
        <input class="w-full border border-slate-300 rounded-lg p-2.5" type="email" name="email" value="<?= htmlspecialchars((string)($student['email'] ?? '')) ?>">
    </div>
    <div>
        <label class="block text-sm mb-1">Phone</label>
        <input class="w-full border border-slate-300 rounded-lg p-2.5" name="phone" value="<?= htmlspecialchars((string)($student['phone'] ?? '')) ?>">
    </div>
    <button class="px-4 py-2 bg-slate-900 hover:bg-slate-700 text-white rounded-lg" type="submit">Save</button>
</form>
<?php
$content = (string) ob_get_clean();
require __DIR__ . '/layout.php';
