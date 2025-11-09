<?php
header('Content-Type: application/json');
$conn = new mysqli('localhost', 'root', '', 'college_nav');

if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
}

$result = $conn->query("
    SELECT DISTINCT department 
    FROM (
        SELECT department FROM students
        UNION
        SELECT department FROM faculty
    ) AS departments
    WHERE department IS NOT NULL
    ORDER BY department
");

$departments = [];
while ($row = $result->fetch_assoc()) {
    $departments[] = $row['department'];
}

echo json_encode($departments);
$conn->close();
?>