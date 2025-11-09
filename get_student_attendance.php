<?php
session_start();
require 'db_connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Student') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get student_id
$stmt = $conn->prepare("SELECT student_id, department, semester FROM students WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user']['id']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Student not found']);
    exit;
}
$student = $result->fetch_assoc();
$student_id = $student['student_id'];
$department = $student['department'];
$semester = $student['semester'];
$stmt->close();

$course_id = "ATT_{$department}_{$semester}";

// Fetch attendance data
$stmt = $conn->prepare("SELECT date, status FROM attendance WHERE student_id = ? AND course_id = ? ORDER BY date DESC");
$stmt->bind_param("is", $student_id, $course_id);
$stmt->execute();
$result = $stmt->get_result();
$attendance = [];
while ($row = $result->fetch_assoc()) {
    $attendance[] = $row;
}
$stmt->close();

// Calculate metrics
$present_days = 0;
$absent_days = 0;
$absent_dates = [];
foreach ($attendance as $record) {
    if ($record['status'] === 'P' || $record['status'] === 'L') {
        $present_days++;
    } elseif ($record['status'] === 'A') {
        $absent_days++;
        $absent_dates[] = $record['date'];
    }
}

// Assume 100 academic days per semester
$total_days = 100;
$required_percentage = 0.75;
$required_days = ceil($total_days * $required_percentage);
$days_to_attend = max(0, $required_days - $present_days);

echo json_encode([
    'success' => true,
    'present_days' => $present_days,
    'absent_days' => $absent_days,
    'days_to_attend_for_75' => $days_to_attend,
    'absent_dates' => $absent_dates
]);
?>