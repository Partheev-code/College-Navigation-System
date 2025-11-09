<?php
session_start();
require 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id']) || !isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'Faculty') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

$faculty_id = $_SESSION['user']['id'];
$all = isset($_GET['all']) && $_GET['all'] === 'true';

try {
    $stmt = $conn->prepare("SELECT department FROM faculty WHERE faculty_id = ?");
    $stmt->bind_param("i", $faculty_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $faculty = $result->fetch_assoc();
    $stmt->close();

    if (!$faculty) {
        echo json_encode(['success' => false, 'message' => 'Faculty not found.']);
        exit;
    }

    $department = $faculty['department'];
    $query = "SELECT student_id, roll_number, semester, name, department, phone_no, address, email FROM students WHERE department = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $department);
    $stmt->execute();
    $result = $stmt->get_result();
    $students = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $conn->close();

    echo json_encode(['success' => true, 'students' => $students]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error fetching students.', 'debug' => $e->getMessage()]);
}
?>