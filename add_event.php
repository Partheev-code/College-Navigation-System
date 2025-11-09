<?php
header('Content-Type: application/json');
$conn = new mysqli('localhost', 'root', '', 'college_nav');

if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed']));
}

$data = json_decode(file_get_contents('php://input'), true);
$title = $conn->real_escape_string($data['title']);
$event_date = $conn->real_escape_string($data['event_date']);
$event_time = $conn->real_escape_string($data['event_time']);
$location = $conn->real_escape_string($data['location']);

$query = "INSERT INTO events (title, event_date, event_time, location) VALUES ('$title', '$event_date', '$event_time', '$location')";
if ($conn->query($query)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Failed to add event']);
}
$conn->close();
?>