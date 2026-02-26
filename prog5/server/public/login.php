<?php
require_once '../config/db.php';
require_once '../src/auth.php';
require_once '../includes/auth.php';

$error_message = "";

if (isAuthenticated()) {
    header("Location: dashboard.php");
    exit();
}

$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if (login($username, $password)) {
        session_regenerate_id(true);
        header("Location: dashboard.php");
        exit();
    } else {
        $error_message = "Invalid credentials. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ClassDoc | Secure Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .bg-mesh {
            background-color: #f8fafc;
            background-image: radial-gradient(at 0% 0%, rgba(59, 130, 246, 0.1) 0, transparent 50%),
                radial-gradient(at 100% 100%, rgba(37, 99, 235, 0.1) 0, transparent 50%);
        }
    </style>
</head>

<body class="bg-mesh flex items-center justify-center min-h-screen p-4">

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
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Welcome Back</h1>
            <p class="text-slate-500 text-sm mt-1">Access your class documents and materials</p>
        </div>

        <div class="bg-white/80 backdrop-blur-xl p-8 rounded-3xl shadow-2xl shadow-slate-200/60 border border-white">

            <?php if (!empty($error_message)): ?>
                <div
                    class="flex items-center gap-3 bg-red-50 text-red-600 p-4 rounded-xl text-sm mb-6 border border-red-100 italic">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="C18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd" />
                    </svg>
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="space-y-6">
                <div>
                    <label
                        class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2 ml-1">Username</label>
                    <input type="text" name="username" required
                        class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:bg-white focus:ring-4 focus:ring-blue-100 focus:border-blue-500 focus:outline-none transition-all duration-200 placeholder:text-slate-300"
                        placeholder="yourname123">
                </div>

                <div>
                    <label
                        class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2 ml-1">Password</label>
                    <input type="password" name="password" required
                        class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:bg-white focus:ring-4 focus:ring-blue-100 focus:border-blue-500 focus:outline-none transition-all duration-200 placeholder:text-slate-300"
                        placeholder="••••••••">
                </div>

                <div class="flex items-center justify-between px-1">
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <input type="checkbox"
                            class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-slate-500 group-hover:text-slate-700 transition-colors">Keep me signed
                            in</span>
                    </label>
                </div>

                <button type="submit"
                    class="w-full bg-slate-900 hover:bg-blue-600 text-white font-semibold py-4 rounded-2xl shadow-lg shadow-slate-200 hover:shadow-blue-200 transition-all duration-300 active:scale-[0.98]">
                    Sign In
                </button>
            </form>
        </div>

        <div class="mt-8 text-center">
            <p class="text-sm text-slate-400">
                Don't have an account? <a href="#" class="text-blue-600 font-semibold hover:text-blue-700">Contact
                    Admin</a>
            </p>
        </div>
    </div>

</body>

</html>