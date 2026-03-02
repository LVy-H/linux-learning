<?php
$pageTitle = $pageTitle ?? 'Home';
$isLoggedIn = isset($_SESSION['user']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
	<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 text-gray-900 min-h-screen">
	<header class="bg-white border-b border-gray-200">
		<div class="max-w-5xl mx-auto px-6 py-4 flex items-center justify-between">
			<a href="/" class="text-xl font-semibold">Ha</a>
			<nav class="flex items-center gap-5 text-sm">
				<a href="/" class="hover:text-blue-600">Home</a>
				<a href="/exercise" class="hover:text-blue-600">Exercise</a>
				<?php if ($isLoggedIn): ?>
					<a href="/dashboard" class="hover:text-blue-600">Dashboard</a>
				<?php else: ?>
					<a href="/login" class="hover:text-blue-600">Login</a>
					<a href="/register" class="hover:text-blue-600">Register</a>
				<?php endif; ?>
			</nav>
		</div>
	</header>

	<main class="max-w-5xl mx-auto px-6 py-10">
