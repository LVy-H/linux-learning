<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../src/auth.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="p-4">
    <h1 class="text-2xl font-bold mb-4">Users</h1>
    <table class="min-w-full bg-white border border-gray-200">
        <thead>
            <tr>
                <th class="py-2 px-4 border-b">ID</th>
                <th class="py-2 px-4 border-b">Username</th>
                <th class="py-2 px-4 border-b">Full Name</th>
                <th class="py-2 px-4 border-b">Email</th>
                <th class="py-2 px-4 border-b">Phone</th>
                <th class="py-2 px-4 border-b">Role</th>
                <th class="py-2 px-4 border-b">Created At</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $stmt = $pdo->query("SELECT id, username, fullname, email, phone, role, created_at FROM users");
            while ($row = $stmt->fetch()) {
                echo "<tr>";
                echo "";
                echo "<td class='py-2 px-4 border-b'><a href='user-detail.php?id={$row['id']}' class='block'>{$row['id']}</a></td>";
                echo "<td class='py-2 px-4 border-b'>{$row['username']}</td>";
                echo "<td class='py-2 px-4 border-b'>{$row['fullname']}</td>";
                echo "<td class='py-2 px-4 border-b'>{$row['email']}</td>";
                echo "<td class='py-2 px-4 border-b'>{$row['phone']}</td>";
                echo "<td class='py-2 px-4 border-b'>{$row['role']}</td>";
                echo "<td class='py-2 px-4 border-b'>{$row['created_at']}</td>";
    
                echo "</tr>";
            
            }
            ?>
        </tbody>
    </table>

</html>
