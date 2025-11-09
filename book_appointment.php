<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id']) || !isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'Student') {
    header('Location: index.html');
    exit;
}

$student_id = $_SESSION['user']['id'];
$faculty_id = isset($_GET['faculty_id']) ? (int)$_GET['faculty_id'] : 0;
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointment_date = trim($_POST['appointment_date']);
    $appointment_time = trim($_POST['appointment_time']);
    $purpose = trim($_POST['purpose']);

    // Validate inputs
    if (empty($appointment_date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $appointment_date)) {
        $errors[] = 'Please enter a valid date (YYYY-MM-DD).';
    } else {
        $date = new DateTime($appointment_date);
        $today = new DateTime();
        if ($date < $today) {
            $errors[] = 'Appointment date must be in the future.';
        }
    }

    if (empty($appointment_time) || !preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $appointment_time)) {
        $errors[] = 'Please enter a valid time (HH:MM in 24-hour format).';
    }

    if (empty($faculty_id) || $faculty_id <= 0) {
        $errors[] = 'Invalid faculty selected.';
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO appointments (student_id, faculty_id, appointment_date, appointment_time, purpose) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iisss", $student_id, $faculty_id, $appointment_date, $appointment_time, $purpose);

        if ($stmt->execute()) {
            $success = 'Appointment booked successfully!';
        } else {
            $errors[] = 'Error booking appointment. Please try again.';
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment - College Navigation</title>
    <link rel="stylesheet" href="book_appointment.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="main-content">
        <div class="content-section centered">
            <h2>Book Appointment</h2>
            <a href="student_dashboard.html" class="back-btn">Back to Dashboard</a>
            <?php if (!empty($errors)): ?>
                <div class="faculty-error">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="faculty-success">
                    <p><?php echo htmlspecialchars($success); ?></p>
                </div>
            <?php endif; ?>
            <form method="POST" action="" class="appointment-form">
                <div class="form-group">
                    <label for="appointment_date">Date (YYYY-MM-DD):</label>
                    <input type="date" id="appointment_date" name="appointment_date" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="form-group">
                    <label for="appointment_time">Time (HH:MM):</label>
                    <input type="time" id="appointment_time" name="appointment_time" required>
                </div>
                <div class="form-group">
                    <label for="purpose">Purpose :</label>
                    <textarea id="purpose" name="purpose" rows="3" placeholder="e.g., Discuss project or assignment"></textarea>
                </div>
                <button type="submit" class="book-btn">Submit Appointment</button>
            </form>
        </div>
    </div>
</body>
</html>