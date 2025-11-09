<?php
session_start();

// Database connection
$conn = new mysqli('localhost', 'root', '', 'college_nav');
if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role = trim($_POST['role'] ?? '');

    if (empty($username) || empty($password) || empty($role)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }

    if (!in_array($role, ['Student', 'Faculty', 'Admin'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid role']);
        exit;
    }

    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ? AND role = ?");
    $stmt->bind_param("ss", $username, $role);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'role' => $user['role']
            ];
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'role' => $user['role']]);
            exit;
        }
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid credentials or role']);
    $stmt->close();
    exit;
}

$conn->close();
?>