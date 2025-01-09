<?php
session_start();
include '../connection.php';

// Get the patient ID from the session
$patient_id = $_SESSION['user_id'];

// Query to fetch the required data
$sql = "
    SELECT hospital_name, h.state, h.city, ph.registration_status, h.registration_fee
    FROM patient_hospital ph
    INNER JOIN hospitals h ON ph.hospital_id = h.id
    WHERE ph.patient_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();

$hospitals = [];
while ($row = $result->fetch_assoc()) {
    $hospitals[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Registration</title>
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
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); /* Ensures 3-4 cards per row */
            gap: 20px;
        }
        .card {
            background-color: #ffffff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
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
        .location {
            font-size: 14px;
            color: #555;
            margin-bottom: 10px;
        }
        .status {
            margin-top: 10px;
            font-size: 14px;
            font-weight: bold;
        }
        .status.completed {
            color: green;
        }
        .status.pending {
            color: red;
        }
        .button-container {
            margin-top: 10px;
        }
        .btn-payment {
            background-color: red;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-payment:hover {
            background-color: darkred;
        }
    </style>
</head>
<body>
<body>
    <div class="container">
        <?php foreach ($hospitals as $hospital): ?>
        <div class="card">
            <div class="hospital-name">Hospital Name: <?= htmlspecialchars($hospital['hospital_name']) ?></div>
            <div class="location">Location: <?= htmlspecialchars($hospital['state']) ?>, <?= htmlspecialchars($hospital['city']) ?></div>
            <?php if ($hospital['registration_status'] === 'Completed'): ?>
                <div class="status completed">Registration Status: Completed</div>
            <?php else: ?>
                <div class="status pending">Registration Status: Pending</div>
                <div class="button-container">
                    <form action="process_payment.php" method="POST">
                        <!-- Hidden fields to send data -->
                        <input type="hidden" name="hospital_name" value="<?= htmlspecialchars($hospital['hospital_name']) ?>">
                        <input type="hidden" name="hospital_fee" value="<?= htmlspecialchars($hospital['registration_fee']) ?>">
                        <input type="hidden" name="patient_id" value="<?= htmlspecialchars($patient_id) ?>">
                        <button type="submit" class="btn-payment">
                            Complete Payment (₹<?= htmlspecialchars($hospital['registration_fee']) ?>)
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
