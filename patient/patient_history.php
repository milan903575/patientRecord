<?php
include '../connection.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'patient') {
    header("Location: ../login.html");
    exit;
}

$patient_id = $_SESSION['user_id'];
$order_by = isset($_GET['order_by']) && $_GET['order_by'] === 'oldest' ? 'ASC' : 'DESC';

$sql_history = "
    SELECT ph.id AS history_id, ph.problem,
           COALESCE(ph.doctor_solution, 'Not Provided') AS doctor_solution, 
           CASE 
               WHEN ph.doctor_id IS NULL THEN 'Not Selected' 
               ELSE CONCAT('Dr. ', COALESCE(d.first_name, ''), ' ', COALESCE(d.last_name, '')) 
           END AS doctor_name, 
           COALESCE(ph.date_completed, 'Pending') AS date, 
           ph.treatment_type, ph.appointment_date, 
           h.hospital_name AS hospital
    FROM patient_history ph
    LEFT JOIN hospitals h ON ph.hospital_id = h.id
    LEFT JOIN doctors d ON ph.doctor_id = d.id
    WHERE ph.patient_id = ?
    ORDER BY ph.date_submitted $order_by
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient History</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
        }
        .container {
            max-width: 1200px;
            margin: 20px auto;
        }
        .filter-container {
            margin-bottom: 20px;
            display: flex;
            justify-content: flex-end;
        }
        .filter-container a {
            text-decoration: none;
            color: #007bff;
            font-size: 14px;
            margin-left: 10px;
            font-weight: bold;
        }
        .filter-container a:hover {
            text-decoration: underline;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        .card {
            background-color: #ffffff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 12px rgba(0, 0, 0, 0.15);
        }
        .hospital-name {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        .details {
            font-size: 14px;
            color: #555;
            margin-bottom: 10px;
        }
        .doctor {
            font-size: 14px;
            color: #666;
            font-style: italic;
        }
        .date {
            font-size: 14px;
            color: #888;
            margin-top: 10px;
            font-style: italic;
        }
        .card-link {
            text-decoration: none;
            color: inherit;
        }
    </style>
</head>
<body>
    <div class="container">
<div class="filter-container">
    <a href="PrivatePatientProblems/private_history.php" class="private-history-button">Private History</a>
    <span>Sort by:</span>
    <a href="?order_by=recent">Recent</a>
    <a href="?order_by=oldest">Oldest</a>
</div>

<div class="grid">
    <?php while ($row = $result_history->fetch_assoc()): ?>
    <a class="card-link" href="history_detail.php?history_id=<?= $row['history_id'] ?>">
        <div class="card">
            <div class="hospital-name">Hospital: <?= htmlspecialchars($row['hospital']) ?></div>
            <div class="details"><strong>Problem:</strong> <?= htmlspecialchars($row['problem']) ?></div>
            
            <?php if ($row['treatment_type'] === 'in-person'): ?>
                <div class="details"><strong>Appointment Date:</strong> <?= htmlspecialchars($row['appointment_date']) ?></div>
            <?php else: ?>
                <div class="details"><strong>Doctor's Solution:</strong> <?= htmlspecialchars($row['doctor_solution']) ?></div>
            <?php endif; ?>
            
            <div class="doctor"><strong>Doctor:</strong> <?= htmlspecialchars($row['doctor_name']) ?></div>
            <div class="date">Completed on: <?= htmlspecialchars($row['date']) ?></div>
        </div>
    </a>
    <?php endwhile; ?>
</div>


    </div>
</body>
</html>
