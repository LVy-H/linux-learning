<?php

declare(strict_types=1);

$title = 'Users';
ob_start();
?>
<h1 class="text-2xl font-bold mb-4">Users</h1>
<table class="min-w-full bg-white border border-gray-200">
    <thead>
        <tr>
            <th class="py-2 px-4 border-b">ID</th>
            <th class="py-2 px-4 border-b">Username</th>
            <th class="py-2 px-4 border-b">Full Name</th>
            <th class="py-2 px-4 border-b">Email</th>
            <th class="py-2 px-4 border-b">Phone</th>
            <th class="py-2 px-4 border-b">Role</th>
            <th class="py-2 px-4 border-b">Created At</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $row): ?>
            <tr>
                <td class="py-2 px-4 border-b">
                    <a class="text-blue-600 underline" href="/users/<?= (int) $row['id'] ?>"><?= (int) $row['id'] ?></a>
                </td>
                <td class="py-2 px-4 border-b"><?= htmlspecialchars($row['username']) ?></td>
                <td class="py-2 px-4 border-b"><?= htmlspecialchars($row['fullname']) ?></td>
                <td class="py-2 px-4 border-b"><?= htmlspecialchars((string) ($row['email'] ?? '')) ?></td>
                <td class="py-2 px-4 border-b"><?= htmlspecialchars((string) ($row['phone'] ?? '')) ?></td>
                <td class="py-2 px-4 border-b"><?= htmlspecialchars($row['role']) ?></td>
                <td class="py-2 px-4 border-b"><?= htmlspecialchars($row['created_at']) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php
$content = (string) ob_get_clean();
require __DIR__ . '/layout.php';
