<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../src/auth.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Detail</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-4">
    <h1 class="text-2xl font-bold mb-4">User Detail</h1>
    <?php
    if (!isset($_GET['id'])) {
        echo "<p class='text-red-500'>User ID is required.</p>";
        exit;
    }

    $id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT id, username, fullname, email, phone, role, created_at FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();

    if (!$user) {
        echo "<p class='text-red-500'>User not found.</p>";
        exit;
    }
    ?>

    <div class="bg-white p-4 rounded shadow">
        <p><strong>ID:</strong> <?php echo $user['id']; ?></p>
        <p><strong>Username:</strong> <?php echo $user['username']; ?></p>
        <p><strong>Full Name:</strong> <?php echo $user['fullname']; ?></p>
        <p><strong>Email:</strong> <?php echo $user['email']; ?></p>
        <p><strong>Phone:</strong> <?php echo $user['phone']; ?></p>
        <p><strong>Role:</strong> <?php echo $user['role']; ?></p>
        <p><strong>Created At:</strong> <?php echo $user['created_at']; ?></p>
    </div>
</body>
</html>