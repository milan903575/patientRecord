<?php
include '../connection.php';
session_start();

if (!isset($_GET['history_id'])) {
    die("Invalid request.");
}

$history_id = $_GET['history_id'];

// Fetch history details
$sql_detail = "
    SELECT ph.problem, ph.problem_description, ph.doctor_solution, ph.date_submitted AS date,
           d.id AS doctor_id, -- Added this line to include doctor_id
           d.first_name AS doctor_first_name, d.last_name AS doctor_last_name, d.profile_picture, d.specialization,
           h.hospital_name AS hospital
    FROM patient_history ph
    LEFT JOIN doctors d ON ph.doctor_id = d.id
    LEFT JOIN hospitals h ON ph.hospital_id = h.id
    WHERE ph.id = ?
";

$stmt_detail = $conn->prepare($sql_detail);
$stmt_detail->bind_param("i", $history_id);
$stmt_detail->execute();
$result_detail = $stmt_detail->get_result();

if ($result_detail->num_rows === 0) {
    die("No details found.");
}

$details = $result_detail->fetch_assoc();
$stmt_detail->close();

// Store doctor_id in session
$_SESSION['doctor_id'] = $details['doctor_id']; 

// Fetch medication details
$sql_medications = "
    SELECT medication_name, dosage, start_date, end_date, medication_type,
           morning_time, afternoon_time, evening_time, night_time, additional_instructions
    FROM medication_alerts
    WHERE patient_history_id = ?
";
$stmt_medications = $conn->prepare($sql_medications);
$stmt_medications->bind_param("i", $history_id);
$stmt_medications->execute();
$result_medications = $stmt_medications->get_result();

$medications = [];
while ($row = $result_medications->fetch_assoc()) {
    $medications[] = $row;
}

$stmt_medications->close();
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History Detail</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
        }
        .container {
            max-width: 1000px;
            margin: 30px auto;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        .main-card {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .left-card {
            flex: 20%;
            background-color: #f9f9f9;
            border-radius: 8px;
            text-align: center;
            padding: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .left-card img {
            border-radius: 50%;
            width: 100px;
            height: 100px;
            margin-bottom: 10px;
        }
        .left-card .doctor-name {
            font-size: 16px;
            font-weight: bold;
        }
        .left-card .specialization {
            font-size: 14px;
            color: #666;
        }
        .right-card {
            flex: 80%;
            padding: 15px;
        }
        .hospital-name {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        .problem {
            margin-bottom: 15px;
        }
        .problem .title {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .sub-cards {
            margin-top: 20px;
        }
        .sub-card {
            background-color: #f9f9f9;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .sub-card .medication-name {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .sub-card .detail {
            font-size: 14px;
            color: #555;
        }
        .feedback {
            text-align: center;
            margin-top: 30px;
        }
        .feedback a {
            text-decoration: none;
            background-color: #007BFF;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 16px;
        }
        .feedback a:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="main-card">
            <!-- Left Card -->
            <div class="left-card">
                <img src="<?= htmlspecialchars($details['profile_picture']) ?>" alt="Doctor">
                <div class="doctor-name">Dr. <?= htmlspecialchars($details['doctor_first_name'] . ' ' . $details['doctor_last_name']) ?></div>
                <div class="specialization"><?= htmlspecialchars($details['specialization']) ?></div>
            </div>

            <!-- Right Card -->
            <div class="right-card">
                <div class="hospital-name">Hospital: <?= htmlspecialchars($details['hospital']) ?></div>
                <div class="problem">
                    <div class="title">Problem:</div>
                    <div><?= htmlspecialchars($details['problem']) ?></div>
                </div>
                <div class="problem">
                    <div class="title">Problem Description:</div>
                    <div><?= htmlspecialchars($details['problem_description']) ?></div>
                </div>
                <div class="problem">
                    <div class="title">Doctor's Solution:</div>
                    <div><?= htmlspecialchars($details['doctor_solution']) ?></div>
                </div>

                <!-- Sub Cards for Medications -->
                <div class="sub-cards">
                    <h3>Medications</h3>
                    <?php if (!empty($medications)): ?>
                        <?php foreach ($medications as $medication): ?>
                            <div class="sub-card">
                                <div class="medication-name"><?= htmlspecialchars($medication['medication_name']) ?></div>
                                <div class="detail"><strong>Dosage:</strong> <?= htmlspecialchars($medication['dosage']) ?></div>
                                <div class="detail"><strong>Start Date:</strong> <?= htmlspecialchars($medication['start_date']) ?></div>
                                <div class="detail"><strong>End Date:</strong> <?= htmlspecialchars($medication['end_date']) ?></div>
                                <div class="detail"><strong>Type:</strong> <?= htmlspecialchars($medication['medication_type']) ?></div>
                                <div class="detail"><strong>Timing:</strong> 
                                    Morning: <?= htmlspecialchars($medication['morning_time']) ?>, 
                                    Afternoon: <?= htmlspecialchars($medication['afternoon_time']) ?>, 
                                    Evening: <?= htmlspecialchars($medication['evening_time']) ?>, 
                                    Night: <?= htmlspecialchars($medication['night_time']) ?>
                                </div>
                                <div class="detail"><strong>Additional Instructions:</strong> <?= htmlspecialchars($medication['additional_instructions']) ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div>No medications prescribed for this history.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Feedback Section -->
        <div class="feedback">
            <h3>Do you like this response? Please give feedback.</h3>
            <a href="../feedback/doctor_feedback.php?doctor_id=<?= urlencode($details['doctor_id']) ?>">Give Feedback for this Doctor</a>
        </div>
    </div>
</body>
</html>


