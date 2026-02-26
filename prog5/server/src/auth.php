<?php

function login($username, $password) {
    global $pdo;

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['is_admin'] = ($user['role'] === 'teacher');
        $_SESSION['username'] = $user['username'];
        $_SESSION['fullname'] = $user['fullname'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['phone'] = $user['phone'];
        $_SESSION['avatar'] = $user['avatar'] ?? '';
        $_SESSION['role'] = $user['role'];
        return true;
    }
    return false;
}

function register($username, $password, $fullname, $email, $phone, $role) {
    global $pdo;

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

