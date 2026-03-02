<?php

function login($username, $password) {
    $pdo = db();

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['is_admin'] = ($user['role'] === 'teacher');
        return true;
    }
    return false;
}

function register($username, $password, $fullname, $email, $phone, $role) {
    $pdo = db();

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, fullname, email, phone, role) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$username, $hashed_password, $fullname, $email, $phone, $role]);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

function logout() {
    session_unset();
    session_destroy();
}

