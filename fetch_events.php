<?php
header('Content-Type: application/json');
$conn = new mysqli('localhost', 'root', '', 'college_nav');

if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
}

$result = $conn->query('SELECT id, title, DATE_FORMAT(event_date, "%Y-%m-%d") as event_date, DAY(event_date) as event_day, MONTHNAME(event_date) as event_month, event_time, location FROM events ORDER BY event_date ASC');
$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}

echo json_encode($events);
$conn->close();
?>