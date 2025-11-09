<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'db_connect.php';

// Log session data
error_log("Student Profile Session: " . print_r($_SESSION, true));

// Check session
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id']) || !isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'Student') {
    error_log("Session check failed: id=" . ($_SESSION['user']['id'] ?? 'unset') . ", role=" . ($_SESSION['user']['role'] ?? 'unset'));
    header('Location: index.html');
    exit;
}

// Fetch student_id and profile data
$user_id = $_SESSION['user']['id'];
$stmt = $conn->prepare("SELECT student_id, name, roll_number, department, semester, phone_no, address FROM students WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    error_log("No student found for user_id: " . $user_id);
    header('Location: index.html');
    exit;
}
$profile = $result->fetch_assoc();
$student_id = $profile['student_id'];
$stmt->close();

// Handle form submission
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $department = trim($_POST['department']) ?: NULL;
    $semester = !empty($_POST['semester']) ? (int)$_POST['semester'] : NULL;
    $phone_no = trim($_POST['phone_no']) ?: NULL;
    $address = trim($_POST['address']) ?: NULL;

    // Validation
    if (empty($name)) {
        $error = 'Name is required';
    } elseif ($semester !== NULL && ($semester < 1 || $semester > 8)) {
        $error = 'Semester must be between 1 and 8';
    } elseif ($phone_no !== NULL && !preg_match('/^[\+]?[0-9\s]{10,15}$/', $phone_no)) {
        $error = 'Phone number must be 10–15 digits, may include + or spaces';
    } elseif ($address !== NULL && strlen($address) > 500) {
        $error = 'Address cannot exceed 500 characters';
    } else {
        $stmt = $conn->prepare("UPDATE students SET name = ?, department = ?, semester = ?, phone_no = ?, address = ? WHERE student_id = ?");
        $stmt->bind_param("ssissi", $name, $department, $semester, $phone_no, $address, $student_id);
        if ($stmt->execute()) {
            $success = 'Profile updated successfully!';
            header('Refresh: 2; URL=student_dashboard.html');
        } else {
            $error = 'Update failed: ' . $conn->error;
            error_log("Update failed: " . $conn->error);
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student Profile</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="student.css">
</head>
<body>
    <div class="main-content">
        <div class="header">
            <h1 class="page-title">Edit Profile</h1>
            <a href="student_dashboard.html" class="action-btn secondary">Back to Dashboard</a>
        </div>
        <div class="profile-form">
            <?php if ($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label for="name">Name <span class="required">*</span></label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($profile['name'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="department">Department</label>
                    <select id="department" name="department">
                        <option value="">Select Department</option>
                        <option value="BCA" <?php echo ($profile['department'] === 'BCA') ? 'selected' : ''; ?>>BCA</option>
                        <option value="B.Com" <?php echo ($profile['department'] === 'B.Com') ? 'selected' : ''; ?>>B.Com</option>
                        <option value="Maths" <?php echo ($profile['department'] === 'Maths') ? 'selected' : ''; ?>>Maths</option>
                        <option value="Physics" <?php echo ($profile['department'] === 'Physics') ? 'selected' : ''; ?>>Physics</option>
                        <option value="Bio.Tech" <?php echo ($profile['department'] === 'Bio.Tech') ? 'selected' : ''; ?>>Bio.Tech</option>
                        <option value="English" <?php echo ($profile['department'] === 'English') ? 'selected' : ''; ?>>English</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="semester">Semester</label>
                    <input type="number" id="semester" name="semester" value="<?php echo htmlspecialchars($profile['semester'] ?? ''); ?>" min="1" max="8">
                </div>
                <div class="form-group">
                    <label for="phone_no">Phone Number</label>
                    <input type="text" id="phone_no" name="phone_no" value="<?php echo htmlspecialchars($profile['phone_no'] ?? ''); ?>" placeholder="+91 1234567890">
                </div>
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" rows="4"><?php echo htmlspecialchars($profile['address'] ?? ''); ?></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="action-btn">Save Changes</button>
                    <a href="student_dashboard.html" class="action-btn secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>