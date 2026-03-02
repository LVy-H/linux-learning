<?php

declare(strict_types=1);

use App\Core\Auth;

$title = 'Home';
ob_start();
?>
<h1 class="text-2xl font-bold">Welcome to the Home Page</h1>
<?php if (Auth::isLoggedIn()): ?>
	<p class="mt-2"><a class="text-blue-600 underline" href="/users">View users</a></p>
<?php else: ?>
	<p class="mt-2"><a class="text-blue-600 underline" href="/login">Sign in to continue</a></p>
<?php endif; ?>
<?php
$content = (string) ob_get_clean();
require __DIR__ . '/layout.php';
