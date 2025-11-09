<?php
header('Content-Type: application/json');
$conn = new mysqli('localhost', 'root', '', 'college_nav');

if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
}

$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($userId <= 0) {
    echo json_encode(['error' => 'Invalid user ID']);
    exit;
}

$query = "
    SELECT 
        u.id, u.username, u.role, u.email,
        s.roll_number, s.semester, s.name AS student_name, s.department AS student_dept, s.phone_no AS student_phone, s.address,
        f.faculty_id, f.department AS faculty_dept, f.office_location, f.name AS faculty_name, f.email AS faculty_email, f.course, f.phone_no AS faculty_phone
    FROM users u
    LEFT JOIN students s ON u.id = s.user_id AND u.role = 'Student'
    LEFT JOIN faculty f ON u.id = f.user_id AND u.role = 'Faculty'
    WHERE u.id = ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo json_encode(['error' => 'User not found']);
    exit;
}

$response = [];
if ($user['role'] === 'Student' && $user['student_name']) {
    $response = [
        'roll_number' => $user['roll_number'] ?: '-',
        'name' => $user['student_name'] ?: '-',
        'semester' => $user['semester'] ?: '-',
        'department' => $user['student_dept'] ?: '-',
        'phone_no' => $user['student_phone'] ?: '-',
        'address' => $user['address'] ?: '-',
        'email' => $user['email'] ?: '-'
    ];
} elseif ($user['role'] === 'Faculty' && $user['faculty_name']) {
    $response = [
        'faculty_id' => $user['faculty_id'] ?: '-',
        'name' => $user['faculty_name'] ?: '-',
        'department' => $user['faculty_dept'] ?: '-',
        'office_location' => $user['office_location'] ?: '-',
        'email' => $user['faculty_email'] ?: '-',
        'course' => $user['course'] ?: '-',
        'phone_no' => $user['faculty_phone'] ?: '-'
    ];
} else {
    $response = [
        'id' => $user['id'] ?: '-',
        'username' => $user['username'] ?: '-',
        'role' => $user['role'] ?: '-',
        'email' => $user['email'] ?: '-'
    ];
}

echo json_encode($response);
$stmt->close();
$conn->close();
?>