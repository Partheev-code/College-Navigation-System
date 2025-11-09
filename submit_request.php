<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['Student', 'Faculty'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'college_nav');
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$message = $input['message'] ?? '';

if (empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Request message cannot be empty']);
    exit;
}

$username = $_SESSION['user']['username'];
$stmt = $conn->prepare('INSERT INTO requests (username, message, created_at) VALUES (?, ?, NOW())');
$stmt->bind_param('ss', $username, $message);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to submit request']);
}

$stmt->close();
$conn->close();
?>