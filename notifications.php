<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id']) || !isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'Student') {
    header('Location: index.html');
    exit;
}

$student_id = $_SESSION['user']['id'];
$appointments = [];
$messages = [];

if ($conn) {
    // Fetch appointments
    $stmt = $conn->prepare("SELECT a.appointment_id, f.name AS faculty_name, a.appointment_date, a.appointment_time, a.purpose, a.status FROM appointments a JOIN faculty f ON a.faculty_id = f.faculty_id WHERE a.student_id = ? AND a.status IN ('Approved', 'Rejected') ORDER BY a.appointment_date, a.appointment_time");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
    $stmt->close();

    // Fetch college messages
    $stmt = $conn->prepare("SELECT message, created_by, created_at FROM college_messages ORDER BY created_at DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
    $stmt->close();

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - College Navigation System</title>
    <link rel="stylesheet" href="notifications.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="main-content">
        <div class="content-section centered">
            <h2>Notifications</h2><br><br>
            <a href="student_dashboard.html" class="back-btn">Back to Dashboard</a><br><br>
            <?php if (empty($appointments)): ?>
                <p class="no-notifications">No appointment updates available.</p>
            <?php else: ?>
                <div class="appointments-section">
                    <h3>Appointment Updates</h3>
                    <div class="appointments-table-wrapper">
                        <table class="appointments-table">
                            <thead>
                                <tr>
                                    <th>Faculty Name</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Purpose</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($appointments as $appointment): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($appointment['faculty_name']); ?></td>
                                        <td><?php echo htmlspecialchars(date('d M Y', strtotime($appointment['appointment_date']))); ?></td>
                                        <td><?php echo htmlspecialchars(date('h:i A', strtotime($appointment['appointment_time']))); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['purpose'] ?: 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['status']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
            <div class="messages-section">
                <h3>College Messages</h3>
                <?php if (empty($messages)): ?>
                    <p class="no-messages">No college messages available.</p>
                <?php else: ?>
                    <div class="messages-table-wrapper">
                        <table class="messages-table">
                            <thead>
                                <tr>
                                    <th>Message</th>
                                    <th>Created By</th>
                                    <th>Created At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($messages as $message): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($message['message']); ?></td>
                                        <td><?php echo htmlspecialchars($message['created_by']); ?></td>
                                        <td><?php echo htmlspecialchars(date('d M Y, h:i A', strtotime($message['created_at']))); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>