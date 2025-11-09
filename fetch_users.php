<?php
header('Content-Type: application/json');
$conn = new mysqli('localhost', 'root', '', 'college_nav');

if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
}

$role = isset($_GET['role']) ? $conn->real_escape_string($_GET['role']) : '';
$department = isset($_GET['department']) ? $conn->real_escape_string($_GET['department']) : '';
$semester = isset($_GET['semester']) ? (int)$_GET['semester'] : '';

$query = "
    SELECT 
        u.id, 
        u.username, 
        u.role, 
        u.email, 
        COALESCE(s.department, f.department, '') AS department, 
        s.semester, 
        COALESCE(s.name, f.name, u.username) AS name, 
        COALESCE(s.phone_no, f.phone_no, '') AS phone_no
    FROM users u
    LEFT JOIN students s ON u.id = s.user_id AND u.role = 'Student'
    LEFT JOIN faculty f ON u.id = f.user_id AND u.role = 'Faculty'
    WHERE 1=1
";

if ($role) {
    $query .= " AND u.role = '$role'";
}
if ($department) {
    $query .= " AND (s.department = '$department' OR f.department = '$department')";
}
if ($semester && $role === 'Student') {
    $query .= " AND s.semester = $semester";
}

$result = $conn->query($query);
$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = [
        'id' => $row['id'],
        'username' => $row['username'],
        'role' => $row['role'],
        'department' => $row['department'],
        'semester' => $row['semester'] ?: '-',
        'name' => $row['name'],
        'email' => $row['email'],
        'phone_no' => $row['phone_no'] ?: '-'
    ];
}

$totalUsers = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];

echo json_encode(['users' => $users, 'total' => $totalUsers]);
$conn->close();
?>