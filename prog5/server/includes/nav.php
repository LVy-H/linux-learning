<nav class="bg-white border-b border-gray-200 shadow-sm">
    <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
        <a href="dashboard.php" class="flex items-center gap-2 text-blue-600 font-bold text-lg">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            ClassDoc
        </a>
        <div class="flex items-center gap-1 text-sm">
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'teacher'): ?>
                <a href="students-manage.php" class="px-3 py-2 rounded-lg hover:bg-gray-100 text-gray-700 font-medium">Students</a>
                <a href="submissions.php" class="px-3 py-2 rounded-lg hover:bg-gray-100 text-gray-700 font-medium">Submissions</a>
            <?php endif; ?>
            <a href="assignments.php" class="px-3 py-2 rounded-lg hover:bg-gray-100 text-gray-700 font-medium">Assignments</a>
            <a href="users.php" class="px-3 py-2 rounded-lg hover:bg-gray-100 text-gray-700 font-medium">Users</a>
            <a href="profile-edit.php" class="px-3 py-2 rounded-lg hover:bg-gray-100 text-gray-700 font-medium">My Profile</a>
            <a href="logout.php" class="ml-2 px-3 py-2 rounded-lg bg-red-50 hover:bg-red-100 text-red-600 font-medium">Logout</a>
        </div>
    </div>
</nav>
