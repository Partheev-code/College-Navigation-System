<?php
header('Content-Type: application/json');

// Database connection
$host = 'localhost';
$dbname = 'college_nav';
$username = 'root'; // Replace with your database username
$password = ''; // Replace with your database password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get input data
    $input = json_decode(file_get_contents('php://input'), true);
    $message = $input['message'] ?? null;
    $created_by = $input['created_by'] ?? null;

    if (!$message || !$created_by) {
        echo json_encode(['success' => false, 'error' => 'Message and created_by are required']);
        exit;
    }

    // Insert message into college_messages table
    $stmt = $pdo->prepare("INSERT INTO college_messages (message, created_by) VALUES (?, ?)");
    $stmt->execute([$message, $created_by]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>