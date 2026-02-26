<?php
require_once '../config/db.php';
require_once '../src/auth.php';
require_once '../includes/auth.php';

if (!isAuthenticated() || $_SESSION['role'] !== 'teacher') {
    header("Location: dashboard.php");
    exit();
}

$submissions = $pdo->query(
    "SELECT s.*, a.title AS assignment_title, u.fullname AS student_name, u.username AS student_username
     FROM submissions s
     JOIN assignments a ON s.assignment_id = a.id
     JOIN users u ON s.student_id = u.id
     ORDER BY a.title, s.created_at DESC"
)->fetchAll();

// Group by assignment
$grouped = [];
foreach ($submissions as $sub) {
    $grouped[$sub['assignment_title']][] = $sub;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ClassDoc | Submissions</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-gray-50 min-h-screen">
<?php include '../includes/nav.php'; ?>
<div class="max-w-5xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Student Submissions</h1>

    <?php if (empty($submissions)): ?>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-10 text-center text-gray-400 text-sm">No submissions yet.</div>
    <?php else: ?>
    <div class="space-y-6">
        <?php foreach ($grouped as $assignTitle => $subs): ?>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="bg-gray-50 border-b border-gray-100 px-5 py-3">
                <h2 class="font-semibold text-gray-800"><?php echo htmlspecialchars($assignTitle); ?></h2>
                <p class="text-xs text-gray-500 mt-0.5"><?php echo count($subs); ?> submission<?php echo count($subs) !== 1 ? 's' : ''; ?></p>
            </div>
            <table class="w-full text-sm">
                <thead class="border-b border-gray-100">
                    <tr>
                        <th class="text-left px-5 py-2.5 font-semibold text-gray-600">Student</th>
                        <th class="text-left px-5 py-2.5 font-semibold text-gray-600 hidden sm:table-cell">Submitted at</th>
                        <th class="text-right px-5 py-2.5 font-semibold text-gray-600">File</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php foreach ($subs as $sub): ?>
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-5 py-3">
                            <p class="font-medium text-gray-800"><?php echo htmlspecialchars($sub['student_name']); ?></p>
                            <p class="text-xs text-gray-500">@<?php echo htmlspecialchars($sub['student_username']); ?></p>
                        </td>
                        <td class="px-5 py-3 text-gray-500 hidden sm:table-cell"><?php echo htmlspecialchars($sub['created_at']); ?></td>
                        <td class="px-5 py-3 text-right">
                            <a href="uploads/submissions/<?php echo urlencode(basename($sub['file_path'])); ?>"
                               download="<?php echo htmlspecialchars($sub['filename_orig'] ?? basename($sub['file_path'])); ?>"
                               class="text-xs bg-green-50 hover:bg-green-100 text-green-700 font-semibold px-3 py-1.5 rounded-lg transition">
                                ⬇ Download
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
</body>
</html>
