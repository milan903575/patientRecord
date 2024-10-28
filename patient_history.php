<?php
include 'connection.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'patient') {
    header("Location: login.html");
    exit;
}

$patient_id = $_SESSION['user_id'];

$sql_history = "
    SELECT ph.problem, ph.current_medication, ph.doctor_solution, ph.date_submitted AS date, h.hospital_name AS hospital
    FROM patient_history ph
    LEFT JOIN hospitals h ON ph.hospital_id = h.id
    WHERE ph.patient_id = ?
";
$stmt_history = $conn->prepare($sql_history);
$stmt_history->bind_param("i", $patient_id);
$stmt_history->execute();
$result_history = $stmt_history->get_result();
$stmt_history->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Patient History</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <style>
        <?php include 'styles.css'; ?>
    </style>
</head>
<body>
    <div class="container">
        <div class="well">
            <div class="history-table">
                <h3>Patient History</h3>
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>Problem</th>
                        <th>Current Medication</th>
                        <th>Doctor's Solution</th>
                        <th>Hospital</th>
                        <th>Date</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php while ($row = $result_history->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['problem']); ?></td>
                            <td><?php echo htmlspecialchars($row['current_medication']); ?></td>
                            <td><?php echo htmlspecialchars($row['doctor_solution']); ?></td>
                            <td><?php echo htmlspecialchars($row['hospital']); ?></td>
                            <td><?php echo htmlspecialchars($row['date']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
