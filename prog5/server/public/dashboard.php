<?php
require_once '../config/db.php';
require_once '../src/auth.php';
require_once '../includes/auth.php';

if (!isAuthenticated()) {
    header("Location: login.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ClassDoc | Dashboard</title>
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
<body class="bg-mesh min-h-screen p-4">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">Welcome, <?php echo htmlspecialchars($_SESSION['fullname']); ?>!</h1>
        <p class="mb-4">This is your dashboard. Here you can manage your account and view your information.</p>
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <h2 class="text-2xl font-semibold mb-4">Your Information</h2>
            <ul class="mb-6">
                <li><strong>Username:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?></li>
                <li><strong>Full Name:</strong> <?php echo htmlspecialchars($_SESSION['fullname']); ?></li>
                <li><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['email']); ?></li>
                <li><strong>Phone:</strong> <?php echo htmlspecialchars($_SESSION['phone']); ?></li>
                <li><strong>Role:</strong> <?php echo htmlspecialchars($_SESSION['role']); ?></li>
            </ul>
            <a href="logout.php" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Logout</a>
        </div>
    </div>
</body>