<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function isAuthenticated() {
    return isset($_SESSION['user_id']);
}

function requireAdmin() {
    if (!isAuthenticated() || !$_SESSION['is_admin']) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden']);
        exit;
    }
}