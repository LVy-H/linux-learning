<?php

declare(strict_types=1);

$title = 'User Detail';
ob_start();
?>
<h1 class="text-2xl font-bold mb-4">User Detail</h1>

<?php if (!$user): ?>
    <p class="text-red-500">User not found.</p>
<?php else: ?>
    <div class="bg-white p-4 rounded shadow space-y-1">
        <p><strong>ID:</strong> <?= (int) $user['id'] ?></p>
        <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
        <p><strong>Full Name:</strong> <?= htmlspecialchars($user['fullname']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars((string) ($user['email'] ?? '')) ?></p>
        <p><strong>Phone:</strong> <?= htmlspecialchars((string) ($user['phone'] ?? '')) ?></p>
        <p><strong>Role:</strong> <?= htmlspecialchars($user['role']) ?></p>
        <p><strong>Created At:</strong> <?= htmlspecialchars($user['created_at']) ?></p>
    </div>
<?php endif; ?>
<?php
$content = (string) ob_get_clean();
require __DIR__ . '/layout.php';
