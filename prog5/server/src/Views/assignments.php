<?php

declare(strict_types=1);

$title = 'Assignments';
ob_start();
?>
<h1 class="text-2xl font-bold mb-4">Assignments</h1>

<?php if (!empty($error)): ?>
    <div class="bg-red-50 text-red-700 p-2 border border-red-200 rounded mb-3"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if (($currentUser['role'] ?? '') === 'teacher'): ?>
    <form method="POST" action="/teacher/assignments" enctype="multipart/form-data" class="bg-white border border-slate-200 rounded-xl p-5 space-y-3 mb-4 max-w-2xl shadow-sm">
        <h2 class="text-lg font-semibold">Upload assignment</h2>
        <input class="w-full border border-slate-300 rounded-lg p-2.5" name="title" placeholder="Title" required>
        <textarea class="w-full border border-slate-300 rounded-lg p-2.5" name="description" placeholder="Description" rows="3"></textarea>
        <input class="w-full border border-slate-300 rounded-lg p-2" type="file" name="file" required>
        <button class="px-4 py-2 bg-slate-900 hover:bg-slate-700 text-white rounded-lg" type="submit">Upload</button>
    </form>
<?php endif; ?>

<div class="space-y-3">
    <?php foreach ($assignments as $a): ?>
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <p class="font-semibold"><?= htmlspecialchars($a['title']) ?></p>
            <p class="text-sm text-slate-600">By <?= htmlspecialchars($a['teacher_name']) ?> | <?= htmlspecialchars($a['created_at']) ?></p>
            <p class="my-2"><?= nl2br(htmlspecialchars((string)$a['description'])) ?></p>
            <a class="text-blue-700 underline" href="/assignments/<?= (int)$a['id'] ?>/download">Download file</a>

            <?php if (($currentUser['role'] ?? '') === 'student'): ?>
                <form method="POST" action="/assignments/<?= (int)$a['id'] ?>/submit" enctype="multipart/form-data" class="mt-3 flex flex-wrap items-center gap-2">
                    <input class="border border-slate-300 rounded-lg p-2" type="file" name="submission_file" required>
                    <button class="px-3 py-2 bg-slate-900 hover:bg-slate-700 text-white rounded-lg" type="submit">Submit work</button>
                </form>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
<?php
$content = (string) ob_get_clean();
require __DIR__ . '/layout.php';
