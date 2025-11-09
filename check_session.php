<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user']) || !isset($_SESSION['user']['username']) || !isset($_SESSION['user']['role'])) {
    echo json_encode(['logged_in' => false]);
    exit;
}

echo json_encode([
    'logged_in' => true,
    'user' => [
        'username' => $_SESSION['user']['username'],
        'role' => $_SESSION['user']['role']
    ]
]);
?>