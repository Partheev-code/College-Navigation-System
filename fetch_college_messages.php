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

    // Fetch messages from college_messages table
    $stmt = $pdo->prepare("SELECT message, created_by, created_at FROM college_messages ORDER BY created_at DESC");
    $stmt->execute();
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format messages for display
    $formattedMessages = array_map(function($msg) {
        $date = new DateTime($msg['created_at']);
        return [
            'message' => htmlspecialchars($msg['message']),
            'created_by' => htmlspecialchars($msg['created_by']),
            'day' => $date->format('d'),
            'month' => $date->format('F'),
            'time' => $date->format('h:i A')
        ];
    }, $messages);

    echo json_encode($formattedMessages);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>