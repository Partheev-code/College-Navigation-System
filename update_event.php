<?php
header('Content-Type: application/json');
$conn = new mysqli('localhost', 'root', '', 'college_nav');

if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed']));
}

$data = json_decode(file_get_contents('php://input'), true);
$id = (int)$data['id'];
$title = $conn->real_escape_string($data['title']);
$event_date = $conn->real_escape_string($data['event_date']);
$event_time = $conn->real_escape_string($data['event_time']);
$location = $conn->real_escape_string($data['location']);

$query = "UPDATE events SET title='$title', event_date='$event_date', event_time='$event_time', location='$location' WHERE id=$id";
if ($conn->query($query)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Failed to update event']);
}
$conn->close();
?>