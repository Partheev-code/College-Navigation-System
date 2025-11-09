<?php
header('Content-Type: application/json');
$conn = new mysqli('localhost', 'root', '', 'college_nav');

if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed']));
}

$data = json_decode(file_get_contents('php://input'), true);
$id = (int)$data['id'];

$query = "DELETE FROM events WHERE id=$id";
if ($conn->query($query)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Failed to delete event']);
}
$conn->close();
?>