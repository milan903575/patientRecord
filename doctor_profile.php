<?php
include 'connection.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'doctor') {
    header("Location: login.html");
    exit;
}

$doctor_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['status'];

    $sql_update = "UPDATE doctors SET status = ? WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("si", $status, $doctor_id);

    if ($stmt_update->execute()) {
        $message = "Status updated successfully!";
    } else {
        $message = "Error: " . $stmt_update->error;
    }

    $stmt_update->close();
} else {
    $message = "";
}

// Get doctor details
$sql_doctor = "SELECT * FROM doctors WHERE id = ?";
$stmt_doctor = $conn->prepare($sql_doctor);  // Fixed the misplaced statement
$stmt_doctor->bind_param("i", $doctor_id);
$stmt_doctor->execute();
$result_doctor = $stmt_doctor->get_result();
$doctor = $result_doctor->fetch_assoc();
$stmt_doctor->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Doctor Profile</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h2>Welcome, Dr. <?php echo htmlspecialchars($doctor['first_name'] . ' ' . $doctor['last_name']); ?></h2>
        <h3>Set Your Availability</h3>
        <?php if ($message): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <form action="doctor_profile.php" method="POST">
            <div class="form-group">
                <label for="status">Status:</label>
                <select id="status" name="status" class="form-control">
                    <option value="available" <?php echo ($doctor['status'] == 'available') ? 'selected' : ''; ?>>Available</option>
                    <option value="busy" <?php echo ($doctor['status'] == 'busy') ? 'selected' : ''; ?>>Busy</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update Status</button>
            <a href="doctor_dashboard.php" class="btn btn-secondary">Dashboard</a>
            <a href="chat/recent_chats_doctor.php" class="btn btn-info">Chats from Patients</a>
        </form>
    </div>
</body>
</html>