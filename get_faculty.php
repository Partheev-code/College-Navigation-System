<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'db_connect.php';

// Debug: Log session data
error_log("Session data: " . print_r($_SESSION, true));

// Check session
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id']) || !isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'Student') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.', 'debug' => 'Invalid session']);
    exit;
}

try {
    $user_id = $_SESSION['user']['id'];
    $mode = isset($_GET['mode']) ? $_GET['mode'] : 'default';
    error_log("Mode: $mode, User ID: $user_id");

    if ($mode === 'all') {
        // Fetch all faculty, grouped by department
        $stmt = $conn->prepare("SELECT faculty_id, name, department, office_location, email, course, phone_no, available_day FROM faculty WHERE name IS NOT NULL AND department IS NOT NULL ORDER BY department, name");
        $stmt->execute();
        $result = $stmt->get_result();
        
        $faculty_by_dept = [];
        while ($row = $result->fetch_assoc()) {
            $dept = $row['department'];
            if (!isset($faculty_by_dept[$dept])) {
                $faculty_by_dept[$dept] = [];
            }
            $faculty_by_dept[$dept][] = [
                'faculty_id' => $row['faculty_id'],
                'name' => $row['name'],
                'department' => $dept,
                'office_location' => $row['office_location'] ?: 'Not specified',
                'email' => $row['email'] ?: 'Not specified',
                'course' => $row['course'] ?: 'Not specified',
                'phone_no' => $row['phone_no'] ?: 'Not specified',
                'available_day' => $row['available_day'] ?: 'Not specified'
            ];
        }
        
        $stmt->close();
        error_log("All faculty count: " . array_sum(array_map('count', $faculty_by_dept)));
        
        if (empty($faculty_by_dept)) {
            echo json_encode(['success' => false, 'message' => 'No faculty found.', 'debug' => 'No faculty data']);
        } else {
            echo json_encode(['success' => true, 'faculty_by_dept' => $faculty_by_dept]);
        }
    } else {
        // Default: Fetch up to 4 faculty from student's department
        $stmt = $conn->prepare("SELECT department FROM students WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $student = $result->fetch_assoc();
        $student_dept = $student['department'] ?? null;
        $stmt->close();
        error_log("Student department: " . ($student_dept ?: 'None'));

        $faculty = [];
        if ($student_dept) {
            $stmt = $conn->prepare("SELECT faculty_id, name, department, office_location, email, course, phone_no, available_day FROM faculty WHERE department = ? AND name IS NOT NULL LIMIT 4");
            $stmt->bind_param("s", $student_dept);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                $faculty[] = [
                    'faculty_id' => $row['faculty_id'],
                    'name' => $row['name'],
                    'department' => $row['department'],
                    'office_location' => $row['office_location'] ?: 'Not specified',
                    'email' => $row['email'] ?: 'Not specified',
                    'course' => $row['course'] ?: 'Not specified',
                    'phone_no' => $row['phone_no'] ?: 'Not specified',
                    'available_day' => $row['available_day'] ?: 'Not specified'
                ];
            }
            $stmt->close();
            error_log("Faculty found for $student_dept: " . count($faculty));
        }

        // Fallback to random faculty if none found
        if (empty($faculty)) {
            error_log("Falling back to random faculty");
            $stmt = $conn->prepare("SELECT faculty_id, name, department, office_location, email, course, phone_no, available_day FROM faculty WHERE name IS NOT NULL AND department IS NOT NULL ORDER BY RAND() LIMIT 4");
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                $faculty[] = [
                    'faculty_id' => $row['faculty_id'],
                    'name' => $row['name'],
                    'department' => $row['department'],
                    'office_location' => $row['office_location'] ?: 'Not specified',
                    'email' => $row['email'] ?: 'Not specified',
                    'course' => $row['course'] ?: 'Not specified',
                    'phone_no' => $row['phone_no'] ?: 'Not specified',
                    'available_day' => $row['available_day'] ?: 'Not specified'
                ];
            }
            $stmt->close();
            error_log("Random faculty count: " . count($faculty));
        }
        
        if (empty($faculty)) {
            echo json_encode(['success' => false, 'message' => 'No faculty found.', 'debug' => 'No faculty in default or fallback']);
        } else {
            echo json_encode(['success' => true, 'faculty' => $faculty, 'debug' => "Department: $student_dept, Faculty count: " . count($faculty)]);
        }
    }
} catch (Exception $e) {
    error_log("Error in get_faculty.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error fetching faculty data.', 'debug' => $e->getMessage()]);
}
$conn->close();
?>