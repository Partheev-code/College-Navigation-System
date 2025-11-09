<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'db_connect.php';

// Log session data
error_log("Faculty Profile Session: " . print_r($_SESSION, true));

// Check session
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id']) || !isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'Faculty') {
    error_log("Session check failed: id=" . ($_SESSION['user']['id'] ?? 'unset') . ", role=" . ($_SESSION['user']['role'] ?? 'unset'));
    header('Location: index.html');
    exit;
}

// Fetch faculty_id and profile data
$user_id = $_SESSION['user']['id'];
$stmt = $conn->prepare("SELECT faculty_id, name, department, office_location, phone_no, course, available_day FROM faculty WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    error_log("No faculty found for user_id: " . $user_id);
    header('Location: index.html');
    exit;
}
$profile = $result->fetch_assoc();
$faculty_id = $profile['faculty_id'];
$stmt->close();

// Handle form submission
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $department = trim($_POST['department']) ?: NULL;
    $office_location = trim($_POST['office_location']) ?: NULL;
    $phone_no = trim($_POST['phone_no']) ?: NULL;
    $course = trim($_POST['course']) ?: NULL;
    $available_day = trim($_POST['available_day']) ?: NULL;

    // Validation
    if (empty($name)) {
        $error = 'Name is required';
    } elseif (empty($course)) {
        $error = 'Course is required';
    } elseif (empty($available_day)) {
        $error = 'Available Day is required';
    } elseif ($phone_no !== NULL && !preg_match('/^[\+]?[0-9\s]{10,15}$/', $phone_no)) {
        $error = 'Phone number must be 10–15 digits, may include + or spaces';
    } else {
        $stmt = $conn->prepare("UPDATE faculty SET name = ?, department = ?, office_location = ?, phone_no = ?, course = ?, available_day = ? WHERE faculty_id = ?");
        $stmt->bind_param("ssssssi", $name, $department, $office_location, $phone_no, $course, $available_day, $faculty_id);
        if ($stmt->execute()) {
            $success = 'Profile updated successfully!';
            header('Refresh: 2; URL=faculty_dashboard.html');
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
    <title>Edit Faculty Profile</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="faculty.css">
</head>
<body>
    <div class="main-content">
        <div class="header">
            <h1 class="page-title">Edit Profile</h1>
            <a href="faculty_dashboard.html" class="action-btn secondary">Back to Dashboard</a>
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
                    <label for="office_location">Office Location</label>
                    <input type="text" id="office_location" name="office_location" value="<?php echo htmlspecialchars($profile['office_location'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="phone_no">Phone Number</label>
                    <input type="text" id="phone_no" name="phone_no" value="<?php echo htmlspecialchars($profile['phone_no'] ?? ''); ?>" placeholder="+91 1234567890">
                </div>
                <div class="form-group">
                    <label for="course">Course <span class="required">*</span></label>
                    <input type="text" id="course" name="course" value="<?php echo htmlspecialchars($profile['course'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="available_day">Available Day <span class="required">*</span></label>
                    <textarea id="available_day" name="available_day" rows="3"><?php echo htmlspecialchars($profile['available_day'] ?? ''); ?></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="action-btn">Save Changes</button>
                    <a href="faculty_dashboard.html" class="action-btn secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>