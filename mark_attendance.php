<?php
session_start();
require 'db_connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Faculty') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get faculty_id and department
$stmt = $conn->prepare("SELECT faculty_id, department FROM faculty WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user']['id']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    error_log("No faculty found for user_id: " . $_SESSION['user']['id']);
    echo json_encode(['success' => false, 'message' => 'Faculty not found']);
    exit;
}
$faculty = $result->fetch_assoc();
$faculty_id = $faculty['faculty_id'];
$faculty_department = $faculty['department'];
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle attendance saving
    $input = json_decode(file_get_contents('php://input'), true);
    $department = trim($input['department'] ?? '');
    $semester = isset($input['semester']) ? (int)$input['semester'] : null;
    $date = trim($input['date'] ?? '');
    $attendance = $input['attendance'] ?? [];

    if (empty($department) || $semester === null || empty($date) || empty($attendance)) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }

    if ($department !== $faculty_department) {
        echo json_encode(['success' => false, 'message' => 'Invalid department']);
        exit;
    }

    if ($semester < 1 || $semester > 8) {
        echo json_encode(['success' => false, 'message' => 'Invalid semester']);
        exit;
    }

    $course_id = "ATT_{$department}_{$semester}";

    // Begin transaction
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("INSERT INTO attendance (student_id, faculty_id, course_id, date, status) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE status = ?");
        foreach ($attendance as $record) {
            $student_id = (int)$record['student_id'];
            $status = $record['status'];
            if (!in_array($status, ['P', 'A', 'L', 'E'])) {
                continue;
            }
            $stmt->bind_param("iissss", $student_id, $faculty_id, $course_id, $date, $status, $status);
            $stmt->execute();
        }
        $stmt->close();
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Attendance saved successfully']);
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error saving attendance: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to save attendance']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Handle student fetching
    $department = trim($_GET['department'] ?? '');
    $semester = isset($_GET['semester']) ? (int)$_GET['semester'] : null;
    $date = trim($_GET['date'] ?? '');

    if (empty($department) || $semester === null || empty($date)) {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        exit;
    }

    if ($department !== $faculty_department) {
        echo json_encode(['success' => false, 'message' => 'Invalid department']);
        exit;
    }

    if ($semester < 1 || $semester > 8) {
        echo json_encode(['success' => false, 'message' => 'Invalid semester']);
        exit;
    }

    $stmt = $conn->prepare("SELECT student_id, name, roll_number FROM students WHERE department = ? AND semester = ? ORDER BY roll_number");
    $stmt->bind_param("si", $department, $semester);
    $stmt->execute();
    $result = $stmt->get_result();
    $students = [];
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
    $stmt->close();

    echo json_encode(['success' => true, 'students' => $students]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>