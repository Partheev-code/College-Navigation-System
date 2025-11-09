<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id']) || !isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'Faculty') {
    header('Location: index.html');
    exit;
}

$faculty_id = $_SESSION['user']['id'];
$appointments = [];
$success = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointment_id = isset($_POST['appointment_id']) ? (int)$_POST['appointment_id'] : 0;
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($appointment_id > 0 && in_array($action, ['approve', 'reject'])) {
        $new_status = ($action === 'approve') ? 'Approved' : 'Rejected';
        $stmt = $conn->prepare("UPDATE appointments SET status = ? WHERE appointment_id = ? AND faculty_id = ?");
        $stmt->bind_param("sii", $new_status, $appointment_id, $faculty_id);

        if ($stmt->execute()) {
            $success = "Appointment $appointment_id $action successfully!";
        } else {
            $errors[] = "Error updating appointment $appointment_id. Please try again.";
        }
        $stmt->close();
    } else {
        $errors[] = "Invalid action or appointment ID.";
    }
}

if ($conn) {
    $stmt = $conn->prepare("SELECT a.appointment_id, s.name AS student_name, a.appointment_date, a.appointment_time, a.purpose, a.status FROM appointments a JOIN students s ON a.student_id = s.student_id WHERE a.faculty_id = ? ORDER BY a.appointment_date, a.appointment_time");
    $stmt->bind_param("i", $faculty_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
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
    <title>Manage Appointments - College Navigation System</title>
    <link rel="stylesheet" href="manage_appointments.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="main-content">
        <div class="content-section centered">
            <h2>Manage Appointments</h2>
            <div class="back-btn-container">
        <a href="faculty_dashboard.html" class="back-btn">Back to Dashboard</a>
    </div>
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
            <?php if (empty($appointments)): ?>
                <p class="no-appointments">No appointments scheduled.</p>
            <?php else: ?>
                <div class="appointments-table-wrapper">
                    <table class="appointments-table">
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Purpose</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($appointments as $appointment): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($appointment['student_name']); ?></td>
                                    <td><?php echo htmlspecialchars(date('d M Y', strtotime($appointment['appointment_date']))); ?></td>
                                    <td><?php echo htmlspecialchars(date('h:i A', strtotime($appointment['appointment_time']))); ?></td>
                                    <td><?php echo htmlspecialchars($appointment['purpose'] ?: 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($appointment['status']); ?></td>
                                    <td>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to <?php echo $appointment['status'] === 'Approved' ? 'reject' : 'approve'; ?> this appointment?');">
                                            <input type="hidden" name="appointment_id" value="<?php echo $appointment['appointment_id']; ?>">
                                            <?php if ($appointment['status'] === 'Pending'): ?>
                                                <button type="submit" name="action" value="approve" class="action-btn approve-btn">Approve</button>
                                                <button type="submit" name="action" value="reject" class="action-btn reject-btn">Reject</button>
                                            <?php elseif ($appointment['status'] === 'Approved'): ?>
                                                <button type="submit" name="action" value="reject" class="action-btn reject-btn">Reject</button>
                                            <?php elseif ($appointment['status'] === 'Rejected'): ?>
                                                <button type="submit" name="action" value="approve" class="action-btn approve-btn">Approve</button>
                                            <?php endif; ?>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>