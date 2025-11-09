<?php
require 'db_connect.php';
header('Content-Type: application/json');

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Faculty') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$stmt = $conn->prepare("SELECT department FROM faculty WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user']['id']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Faculty not found']);
    exit;
}
$faculty = $result->fetch_assoc();
$faculty_department = $faculty['department'];
$stmt->close();

$stmt = $conn->prepare("SELECT DISTINCT department AS id, department AS name FROM students WHERE department = ? ORDER BY department");
$stmt->bind_param("s", $faculty_department);
$stmt->execute();
$result = $stmt->get_result();
$departments = [];
while ($row = $result->fetch_assoc()) {
    $departments[] = $row;
}
$stmt->close();

echo json_encode(['success' => true, 'departments' => $departments]);
?>