<?php

declare(strict_types=1);

use App\Core\Csrf;

$title = 'Users';
ob_start();
?>
<div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-bold">Users</h1>
    <span class="text-sm text-slate-500">Total: <?= count($users) ?></span>
</div>
<?php if (($currentUser['role'] ?? '') === 'teacher'): ?>
    <p class="mb-3"><a class="inline-flex px-3 py-2 rounded-lg bg-slate-900 text-white hover:bg-slate-700" href="/teacher/students/new">+ Add new student</a></p>
<?php endif; ?>
<div class="overflow-x-auto bg-white rounded-xl border border-slate-200 shadow-sm">
<table class="min-w-full">
    <thead>
        <tr class="bg-slate-50">
            <th class="py-3 px-4 border-b text-left">ID</th>
            <th class="py-3 px-4 border-b text-left">Username</th>
            <th class="py-3 px-4 border-b text-left">Full Name</th>
            <th class="py-3 px-4 border-b text-left">Email</th>
            <th class="py-3 px-4 border-b text-left">Phone</th>
            <th class="py-3 px-4 border-b text-left">Role</th>
            <th class="py-3 px-4 border-b text-left">Created At</th>
            <?php if (($currentUser['role'] ?? '') === 'teacher'): ?>
                <th class="py-3 px-4 border-b text-left">Actions</th>
            <?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $row): ?>
            <tr class="hover:bg-slate-50">
                <td class="py-3 px-4 border-b">
                    <a class="text-blue-600 underline" href="/users/<?= (int) $row['id'] ?>"><?= (int) $row['id'] ?></a>
                </td>
                <td class="py-3 px-4 border-b"><?= htmlspecialchars($row['username']) ?></td>
                <td class="py-3 px-4 border-b"><?= htmlspecialchars($row['fullname']) ?></td>
                <td class="py-3 px-4 border-b"><?= htmlspecialchars((string) ($row['email'] ?? '')) ?></td>
                <td class="py-3 px-4 border-b"><?= htmlspecialchars((string) ($row['phone'] ?? '')) ?></td>
                <td class="py-3 px-4 border-b"><span class="px-2 py-1 rounded text-xs bg-slate-100"><?= htmlspecialchars($row['role']) ?></span></td>
                <td class="py-3 px-4 border-b text-sm text-slate-500"><?= htmlspecialchars($row['created_at']) ?></td>
                <?php if (($currentUser['role'] ?? '') === 'teacher'): ?>
                    <td class="py-3 px-4 border-b space-x-2">
                        <?php if (($row['role'] ?? '') === 'student'): ?>
                            <a class="text-blue-700 underline" href="/teacher/students/<?= (int) $row['id'] ?>/edit">Edit</a>
                            <form action="/teacher/students/<?= (int) $row['id'] ?>/delete" method="POST" class="inline">
                                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
                                <button class="text-red-600 underline" type="submit" onclick="return confirm('Delete this student?')">Delete</button>
                            </form>
                        <?php endif; ?>
                    </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php
$content = (string) ob_get_clean();
require __DIR__ . '/layout.php';
