<?php

declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'App') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="p-4">
    <?= $content ?? '' ?>
</body>

</html>
