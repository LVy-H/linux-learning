<?php 
$pageTitle = 'Login - Muhaha';
require BASE_PATH . '/views/partials/header.php';
?>
<div class="bg-mesh flex items-center justify-center min-h-screen p-4">
    <div class="w-full max-w-[400px] animate-in fade-in zoom-in duration-500">
        <div class="text-center mb-10">
            <div
                class="inline-flex items-center justify-center w-12 h-12 bg-blue-600 rounded-xl mb-4 shadow-lg shadow-blue-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="C9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold mb-2">Welcome Back</h1>
            <p class="text-gray-600">Please enter your credentials to log in.</p>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="bg-red-100 text-red-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>
        <form action="" method="POST" class="space-y-6">
            <div>
                <label class="block text-sm font-medium mb-1">Username</label>
                <input type="text" name="username" required
                    class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Password</label>
                <input type="password" name="password" required
                    class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <button type="submit"
                class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition">Log In</button>
        </form>
    </div>
</div>

<?php
require BASE_PATH . '/views/partials/footer.php';
?>