<?php

declare(strict_types=1);

require_once __DIR__ . '/../autoload.php';

use App\Core\Database;

error_reporting(E_ALL);
ini_set('display_errors', '1');

Database::initSchema();
echo "Database schema ensured.\n";

$users = [
  ['username'=>'teacher1','password'=>'123456a@A','fullname'=>'Teacher One','role'=>'teacher','email'=> 'teacher1@example.com','phone'=>''],
  ['username'=>'teacher2','password'=>'123456a@A','fullname'=>'Teacher Two','role'=>'teacher','email'=> 'teacher2@example.com','phone'=>''],
  ['username'=>'student1','password'=>'123456a@A','fullname'=>'Student One','role'=>'student','email'=> 'student1@example.com','phone'=>''],
  ['username'=>'student2','password'=>'123456a@A','fullname'=>'Student Two','role'=>'student','email'=> 'student2@example.com','phone'=>''],
];

$pdo = Database::connection();
$check = $pdo->prepare('SELECT id FROM users WHERE username = ?');
$insert = $pdo->prepare('INSERT INTO users (username, password, fullname, email, phone, role) VALUES (?, ?, ?, ?, ?, ?)');
foreach ($users as $u) {
  $check->execute([$u['username']]);
  if ($check->fetch()) {
    echo "User {$u['username']} already exists, skipping.\n";
    continue;
  }
  $ok = $insert->execute([
    $u['username'],
    password_hash($u['password'], PASSWORD_DEFAULT),
    $u['fullname'],
    $u['email'],
    $u['phone'],
    $u['role'],
  ]);
  if ($ok) {
    echo "Inserted user {$u['username']}\n";
  } else {
    echo "Failed to insert {$u['username']}\n";
  }
}

echo "Seeding completed.\n";
exit(0);
