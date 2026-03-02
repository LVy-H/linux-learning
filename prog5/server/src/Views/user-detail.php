<?php

declare(strict_types=1);

$title = 'User Detail';
ob_start();
?>
<h1 class="text-2xl font-bold mb-4">User Detail</h1>

<?php if (!$user): ?>
    <p class="text-red-500">User not found.</p>
<?php else: ?>
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-1">
        <div class="mb-3 flex items-center gap-4">
            <img src="/avatar/<?= (int)$user['id'] ?>" alt="avatar" class="w-24 h-24 rounded-full object-cover border border-slate-300" onerror="this.style.display='none'">
            <div>
                <p class="text-xl font-semibold"><?= htmlspecialchars($user['fullname']) ?></p>
                <p class="text-slate-500">@<?= htmlspecialchars($user['username']) ?></p>
            </div>
        </div>
        <p><strong>ID:</strong> <?= (int) $user['id'] ?></p>
        <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
        <p><strong>Full Name:</strong> <?= htmlspecialchars($user['fullname']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars((string) ($user['email'] ?? '')) ?></p>
        <p><strong>Phone:</strong> <?= htmlspecialchars((string) ($user['phone'] ?? '')) ?></p>
        <p><strong>Role:</strong> <?= htmlspecialchars($user['role']) ?></p>
        <p><strong>Created At:</strong> <?= htmlspecialchars($user['created_at']) ?></p>
    </div>

    <?php if (!empty($currentUser)): ?>
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm mt-4">
            <h2 class="text-lg font-semibold mb-2">Leave a message</h2>
            <form action="/users/<?= (int)$user['id'] ?>/messages" method="POST" class="space-y-2">
                <textarea name="content" class="w-full border border-slate-300 rounded-lg p-2" rows="3" required></textarea>
                <button type="submit" class="px-3 py-2 bg-slate-900 hover:bg-slate-700 text-white rounded-lg">Send</button>
            </form>
        </div>
    <?php endif; ?>

    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm mt-4">
        <h2 class="text-lg font-semibold mb-3">Messages</h2>
        <?php if (empty($messages)): ?>
            <p class="text-slate-500">No messages yet.</p>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($messages as $m): ?>
                    <div class="border border-slate-200 rounded-lg p-3 bg-slate-50/50">
                        <p class="text-sm text-slate-500">From: <?= htmlspecialchars($m['sender_username']) ?> | <?= htmlspecialchars($m['created_at']) ?></p>
                        <p class="my-2"><?= nl2br(htmlspecialchars($m['content'])) ?></p>
                        <?php if (($currentUser['id'] ?? 0) === (int)$m['sender_id']): ?>
                            <form action="/messages/<?= (int)$m['id'] ?>/update" method="POST" class="space-y-2 mb-2">
                                <input type="hidden" name="receiver_id" value="<?= (int)$user['id'] ?>">
                                <textarea name="content" class="w-full border border-slate-300 rounded-lg p-2" rows="2" required><?= htmlspecialchars($m['content']) ?></textarea>
                                <button class="text-blue-700 underline" type="submit">Update</button>
                            </form>
                            <form action="/messages/<?= (int)$m['id'] ?>/delete" method="POST">
                                <input type="hidden" name="receiver_id" value="<?= (int)$user['id'] ?>">
                                <button class="text-red-600 underline" type="submit">Delete</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>
<?php
$content = (string) ob_get_clean();
require __DIR__ . '/layout.php';
