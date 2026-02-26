<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/auth.php';

error_reporting(E_ALL);
ini_set('display_errors', '1');

// initialize schema
if (function_exists('init_db')) {
  init_db();
  echo "Database schema ensured.\n";
} else {
  echo "init_db() not found; ensure server/config/db.php provides it.\n";
}

$users = [
  ['username'=>'teacher1','password'=>'123456a@A','fullname'=>'Teacher One','role'=>'teacher','email'=> 'teacher1@example.com','phone'=>''],
  ['username'=>'teacher2','password'=>'123456a@A','fullname'=>'Teacher Two','role'=>'teacher','email'=> 'teacher2@example.com','phone'=>''],
  ['username'=>'student1','password'=>'123456a@A','fullname'=>'Student One','role'=>'student','email'=> 'student1@example.com','phone'=>''],
  ['username'=>'student2','password'=>'123456a@A','fullname'=>'Student Two','role'=>'student','email'=> 'student2@example.com','phone'=>''],
];

// Insert users using register() from auth.php when they don't exist
$check = $pdo->prepare('SELECT id FROM users WHERE username = ?');
foreach ($users as $u) {
  $check->execute([$u['username']]);
  if ($check->fetch()) {
    echo "User {$u['username']} already exists, skipping.\n";
    continue;
  }
  $ok = register($u['username'], $u['password'], $u['fullname'], $u['email'], $u['phone'], $u['role']);
  if ($ok) {
    echo "Inserted user {$u['username']}\n";
  } else {
    echo "Failed to insert {$u['username']}\n";
  }
}

echo "Seeding completed.\n";
exit(0);
