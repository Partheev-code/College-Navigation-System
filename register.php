<?php
session_start();
$host = 'localhost';
$db = 'college_nav';
$user = 'root'; // Default XAMPP MySQL user
$pass = ''; // Default XAMPP MySQL password
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm-password']);
    $role = trim($_POST['role']);

    // Server-side validation
    $errors = [];
    if (empty($username)) {
        $errors[] = 'Username is required';
    }
    if (empty($email)) {
        $errors[] = 'Email is required';
    }
    if (empty($password)) {
        $errors[] = 'Password is required';
    }
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match';
    }
    if (!in_array($role, ['Student', 'Faculty', 'Admin'])) {
        $errors[] = 'Invalid role';
    }

    // Check for duplicate username
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $errors[] = 'Username already exists';
    }
    $stmt->close();

    // Check for duplicate email
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $errors[] = 'Email already exists';
    }
    $stmt->close();

    if (empty($errors)) {
        try {
            // Begin transaction
            $conn->begin_transaction();

            // Insert into users table
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $email, $hashed_password, $role);
            $stmt->execute();
            $user_id = $conn->insert_id; // Get new user_id

            // Insert into students or faculty table
            if ($role === 'Student') {
                $roll_number = 'ROLL_' . $user_id; // Placeholder
                $name = $username; // Use username as name
                $stmt = $conn->prepare("INSERT INTO students (student_id, user_id, roll_number, name, email) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("iisss", $user_id, $user_id, $roll_number, $name, $email);
                $stmt->execute();
            } elseif ($role === 'Faculty') {
                $name = $username; // Use username as name
                $stmt = $conn->prepare("INSERT INTO faculty (faculty_id, user_id, name, email) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiss", $user_id, $user_id, $name, $email);
                $stmt->execute();
            }
            // No additional table for Admin

            // Commit transaction
            $conn->commit();
            $_SESSION['message'] = 'Registration successful! Please login.';
            header('Location: index.html');
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = 'Registration failed: ' . $e->getMessage();
        }
    }

    // Return errors as JSON for client-side display
    if (!empty($errors)) {
        header('Content-Type: application/json');
        echo json_encode(['errors' => $errors]);
        exit();
    }
}

$conn->close();
?>