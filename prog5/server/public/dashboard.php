<?php
require_once '../config/db.php';
require_once '../src/auth.php';
require_once '../includes/auth.php';

if (!isAuthenticated()) {
    header("Location: login.php");
    exit();
}

$isTeacher = ($_SESSION['role'] === 'teacher');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ClassDoc | Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-gray-50 min-h-screen">
<?php include '../includes/nav.php'; ?>
<div class="max-w-6xl mx-auto px-4 py-8">
    <div class="flex items-center gap-4 mb-8">
        <?php
        $av = $_SESSION['avatar'] ?? '';
        if ($av):
            $avHtml = (filter_var($av, FILTER_VALIDATE_URL)) ? htmlspecialchars($av) : htmlspecialchars('uploads/avatars/' . basename($av));
        ?>
            <img src="<?php echo $avHtml; ?>" alt="avatar" class="w-16 h-16 rounded-full object-cover border-2 border-blue-200">
        <?php else: ?>
            <div class="w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 text-2xl font-bold">
                <?php echo htmlspecialchars(mb_substr($_SESSION['fullname'], 0, 1)); ?>
            </div>
        <?php endif; ?>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Welcome, <?php echo htmlspecialchars($_SESSION['fullname']); ?>!</h1>
            <p class="text-gray-500 text-sm"><?php echo $isTeacher ? '👩‍🏫 Teacher' : '🎓 Student'; ?> · <?php echo htmlspecialchars($_SESSION['username']); ?></p>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <a href="users.php" class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-md transition border border-gray-100 flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-500 shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <div>
                <p class="font-semibold text-gray-800">User Directory</p>
                <p class="text-sm text-gray-500 mt-1">Browse and message all users</p>
            </div>
        </a>

        <a href="assignments.php" class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-md transition border border-gray-100 flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-green-50 flex items-center justify-center text-green-500 shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <div>
                <p class="font-semibold text-gray-800">Assignments</p>
                <p class="text-sm text-gray-500 mt-1"><?php echo $isTeacher ? 'Upload & manage assignments' : 'View & download assignments'; ?></p>
            </div>
        </a>

        <a href="profile-edit.php" class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-md transition border border-gray-100 flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-50 flex items-center justify-center text-purple-500 shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <div>
                <p class="font-semibold text-gray-800">My Profile</p>
                <p class="text-sm text-gray-500 mt-1">Edit info & upload avatar</p>
            </div>
        </a>

        <?php if ($isTeacher): ?>
        <a href="students-manage.php" class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-md transition border border-gray-100 flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-orange-50 flex items-center justify-center text-orange-500 shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
            <div>
                <p class="font-semibold text-gray-800">Manage Students</p>
                <p class="text-sm text-gray-500 mt-1">Add, edit, delete student accounts</p>
            </div>
        </a>

        <a href="submissions.php" class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-md transition border border-gray-100 flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-teal-50 flex items-center justify-center text-teal-500 shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
            </div>
            <div>
                <p class="font-semibold text-gray-800">Submissions</p>
                <p class="text-sm text-gray-500 mt-1">Review student submissions</p>
            </div>
        </a>
        <?php endif; ?>
    </div>
</div>
</body>
</html>