<?php
require_once '../config/db.php';
require_once '../src/auth.php';
require_once '../includes/auth.php';

if (!isAuthenticated()) {
    header("Location: login.php");
    exit();
}

$users = $pdo->query("SELECT id, username, fullname, email, phone, avatar, role FROM users ORDER BY role, fullname")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ClassDoc | User Directory</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-gray-50 min-h-screen">
<?php include '../includes/nav.php'; ?>
<div class="max-w-6xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">User Directory</h1>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php foreach ($users as $u): ?>
        <?php
            $av = $u['avatar'] ?? '';
            $avHtml = '';
            if ($av) {
                $avHtml = filter_var($av, FILTER_VALIDATE_URL) ? htmlspecialchars($av) : htmlspecialchars('uploads/avatars/' . basename($av));
            }
        ?>
        <a href="user-profile.php?id=<?php echo (int)$u['id']; ?>" class="bg-white rounded-2xl p-5 shadow-sm hover:shadow-md transition border border-gray-100 flex items-center gap-4">
            <?php if ($avHtml): ?>
                <img src="<?php echo $avHtml; ?>" alt="avatar" class="w-14 h-14 rounded-full object-cover border-2 border-gray-100 shrink-0">
            <?php else: ?>
                <div class="w-14 h-14 rounded-full <?php echo $u['role'] === 'teacher' ? 'bg-orange-100 text-orange-600' : 'bg-blue-100 text-blue-600'; ?> flex items-center justify-center text-xl font-bold shrink-0">
                    <?php echo htmlspecialchars(mb_substr($u['fullname'], 0, 1)); ?>
                </div>
            <?php endif; ?>
            <div class="min-w-0">
                <p class="font-semibold text-gray-900 truncate"><?php echo htmlspecialchars($u['fullname']); ?></p>
                <p class="text-sm text-gray-500">@<?php echo htmlspecialchars($u['username']); ?></p>
                <span class="inline-block mt-1 text-xs px-2 py-0.5 rounded-full <?php echo $u['role'] === 'teacher' ? 'bg-orange-100 text-orange-700' : 'bg-blue-100 text-blue-700'; ?>">
                    <?php echo $u['role'] === 'teacher' ? 'Teacher' : 'Student'; ?>
                </span>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>
