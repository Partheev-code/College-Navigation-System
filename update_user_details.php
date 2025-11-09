<?php
header('Content-Type: application/json');

// Database connection
$host = 'localhost';
$dbname = 'college_nav';
$username = 'root'; // Replace with your database username
$password = ''; // Replace with your database password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get input data
    $input = json_decode(file_get_contents('php://input'), true);
    $user_id = $input['user_id'] ?? null;
    $role = $input['role'] ?? null;

    if (!$user_id || !$role) {
        echo json_encode(['success' => false, 'error' => 'Missing user_id or role']);
        exit;
    }

    if ($role === 'Student') {
        $name = $input['name'] ?? null;
        $semester = $input['semester'] ?? null;
        $department = $input['department'] ?? null;
        $email = $input['email'] ?? null;
        $phone_no = $input['phone_no'] ?? null;
        $address = $input['address'] ?? null;

        if (!$name || !$email) {
            echo json_encode(['success' => false, 'error' => 'Name and email are required']);
            exit;
        }

        // Validate phone number
        if ($phone_no && !preg_match('/^[0-9]{10}$/', $phone_no)) {
            echo json_encode(['success' => false, 'error' => 'Invalid phone number']);
            exit;
        }

        // Validate semester
        if ($semester && !in_array($semester, ['1', '2', '3', '4', '5', '6', '7', '8'])) {
            echo json_encode(['success' => false, 'error' => 'Invalid semester']);
            exit;
        }

        // Validate department
        $valid_departments = ['BCA', 'Bio.Tech', 'English', 'B.com', 'Maths', 'Physics'];
        if ($department && !in_array($department, $valid_departments)) {
            echo json_encode(['success' => false, 'error' => 'Invalid department']);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE students SET name = ?, semester = ?, department = ?, email = ?, phone_no = ?, address = ? WHERE user_id = ?");
        $stmt->execute([$name, $semester ?: null, $department ?: null, $email, $phone_no ?: null, $address ?: null, $user_id]);
    } elseif ($role === 'Faculty') {
        $name = $input['name'] ?? null;
        $department = $input['department'] ?? null;
        $office_location = $input['office_location'] ?? null;
        $email = $input['email'] ?? null;
        $course = $input['course'] ?? null;
        $phone_no = $input['phone_no'] ?? null;
        $available_day = $input['available_day'] ?? null;

        if (!$name || !$email) {
            echo json_encode(['success' => false, 'error' => 'Name and email are required']);
            exit;
        }

        // Validate phone number
        if ($phone_no && !preg_match('/^[0-9]{10}$/', $phone_no)) {
            echo json_encode(['success' => false, 'error' => 'Invalid phone number']);
            exit;
        }

        // Validate department
        $valid_departments = ['BCA', 'Bio.Tech', 'English', 'B.com', 'Maths', 'Physics'];
        if ($department && !in_array($department, $valid_departments)) {
            echo json_encode(['success' => false, 'error' => 'Invalid department']);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE faculty SET name = ?, department = ?, office_location = ?, email = ?, course = ?, phone_no = ?, available_day = ? WHERE user_id = ?");
        $stmt->execute([$name, $department ?: null, $office_location ?: null, $email, $course ?: null, $phone_no ?: null, $available_day ?: null, $user_id]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid role']);
        exit;
    }

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>