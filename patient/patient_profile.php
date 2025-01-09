<?php
include '../connection.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'patient') {
    header("Location: ../login.html");
    exit;
}

$patient_id = $_SESSION['user_id'];

$sql_patient = "SELECT * FROM patients WHERE id = ?";
$stmt_patient = $conn->prepare($sql_patient);
$stmt_patient->bind_param("i", $patient_id);
$stmt_patient->execute();
$result_patient = $stmt_patient->get_result();
$patient = $result_patient->fetch_assoc();
$stmt_patient->close();

if (isset($patient['date_of_birth'])) {
    $dob_date = new DateTime($patient['date_of_birth']);
    $now = new DateTime();
    $age = $now->diff($dob_date)->y;
} else {
    $age = 'N/A';
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Patient Profile</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <style>
        <?php include 'styles.css'; ?>
    </style>
</head>
<body>
    <div class="container">
        <div class="well">
            <div class="profile-heading">
                <img src="logo.png" alt="Logo" width="50" height="50">
                <h2><?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?>'s Profile</h2>
            </div>
            <div class="profile-details">
                <p><strong>Name:</strong> <?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?></p>
                <p><strong>Gender:</strong> <?php echo htmlspecialchars($patient['gender']); ?></p>
                <p><strong>Date of Birth:</strong> <?php echo htmlspecialchars($patient['date_of_birth']); ?></p>
                <p><strong>Age:</strong> <?php echo htmlspecialchars($age); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($patient['email']); ?></p>
                <p><strong>Blood Group:</strong> <?php echo htmlspecialchars($patient['blood_group']); ?></p>
            </div>
        </div>
    </div>
</body>
</html>
