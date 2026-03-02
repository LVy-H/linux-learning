<?php

declare(strict_types=1);

$title = 'Submissions';
ob_start();
?>
<h1 class="text-2xl font-bold mb-4">All Student Submissions</h1>

<div class="overflow-x-auto bg-white rounded-xl border border-slate-200 shadow-sm">
<table class="min-w-full">
    <thead>
        <tr class="bg-slate-50">
            <th class="py-3 px-4 border-b text-left">ID</th>
            <th class="py-3 px-4 border-b text-left">Assignment</th>
            <th class="py-3 px-4 border-b text-left">Student</th>
            <th class="py-3 px-4 border-b text-left">Submitted At</th>
            <th class="py-3 px-4 border-b text-left">File</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($submissions as $s): ?>
            <tr class="hover:bg-slate-50">
                <td class="py-3 px-4 border-b"><?= (int)$s['id'] ?></td>
                <td class="py-3 px-4 border-b"><?= htmlspecialchars($s['assignment_title']) ?></td>
                <td class="py-3 px-4 border-b"><?= htmlspecialchars($s['student_name']) ?> (<?= htmlspecialchars($s['student_username']) ?>)</td>
                <td class="py-3 px-4 border-b text-sm text-slate-500"><?= htmlspecialchars($s['created_at']) ?></td>
                <td class="py-3 px-4 border-b">
                    <a class="text-blue-700 underline" href="/teacher/submissions/<?= (int)$s['id'] ?>/download">Download</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php
$content = (string) ob_get_clean();
require __DIR__ . '/layout.php';
