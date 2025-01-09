<?php
session_start();
include '../connection.php';

// Check if hospital_id is set in the session
if (!isset($_SESSION['hospital_id'])) {
    // Redirect to login page if not set
    header("Location: ../login.html");
    exit();
}

// Retrieve hospital_id from session
$hospital_id = $_SESSION['hospital_id'];

// Fetch the total number of doctors for the hospital
$doctor_query = "
    SELECT COUNT(d.id) AS doctor_count 
    FROM doctors d
    INNER JOIN hospitals h ON d.hospital_id = h.id
    WHERE h.id = $hospital_id";
$doctor_result = mysqli_query($conn, $doctor_query);
$doctor_count_display = mysqli_fetch_assoc($doctor_result)['doctor_count'];

// Fetch the total number of patients for the hospital
$patient_query = "
    SELECT COUNT(p.id) AS patient_count 
    FROM patients p
    INNER JOIN patient_hospital ph ON p.id = ph.patient_id
    INNER JOIN hospitals h ON ph.hospital_id = h.id
    WHERE h.id = $hospital_id";
$patient_result = mysqli_query($conn, $patient_query);
$patient_count = mysqli_fetch_assoc($patient_result)['patient_count'];

// Fetch patient status data for the hospital
$status_query = "
    SELECT 
        (SELECT COUNT(ph.id) 
         FROM patient_history ph
         INNER JOIN patient_hospital phosp ON ph.patient_id = phosp.patient_id
         WHERE phosp.hospital_id = $hospital_id AND ph.status = 'pending') AS pending_count,
        (SELECT COUNT(ph.id) 
         FROM patient_history ph
         INNER JOIN patient_hospital phosp ON ph.patient_id = phosp.patient_id
         WHERE phosp.hospital_id = $hospital_id AND ph.status = 'completed') AS completed_count";
$status_result = mysqli_query($conn, $status_query);
$status_data = mysqli_fetch_assoc($status_result);
$pending_count = $status_data['pending_count'];
$completed_count = $status_data['completed_count'];
$total_status_count = $pending_count + $completed_count;
$pending_percentage = $total_status_count > 0 ? round(($pending_count / $total_status_count) * 100) : 0;

$pending_count_display = htmlspecialchars($pending_count);
$completed_count_display = htmlspecialchars($completed_count);
$total_status_count_display = htmlspecialchars($total_status_count);
$pending_percentage_display = htmlspecialchars($pending_percentage);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        /* Include your CSS here */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .container {
            display: flex;
            flex: 1;
        }

        .sidebar {
            background-color: #2c3e50;
            color: #ecf0f1;
            width: 20%;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }

        .sidebar h2 {
            margin-bottom: 20px;
            text-align: center;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar ul li {
            padding: 10px;
            margin: 10px 0;
            background-color: #34495e;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
        }

        .sidebar ul li a {
            color: #ecf0f1;
            text-decoration: none;
        }

        .sidebar ul li:hover {
            background-color: #1abc9c;
        }

        .content {
            flex: 1;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .stats-row {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .stat-box {
            flex: 1;
            min-width: 250px;
            background-color: #f4f4f4;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
        }

        .stat-box h3 {
            font-size: 24px;
            margin: 0;
            color: #2c3e50;
        }

        .stat-box span {
            font-size: 40px;
            color: #1abc9c;
        }

        .patient-box {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .circle {
            position: relative;
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: conic-gradient(
                red <?php echo $pending_percentage; ?>%, 
                green <?php echo $pending_percentage; ?>%
            );
        }

        .circle-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 20px;
            color: #2c3e50;
        }

        @media (max-width: 768px) {
            .stats-row {
                flex-direction: column;
                align-items: center;
            }

            .stat-box {
                max-width: 100%;
            }

            .circle {
                width: 120px;
                height: 120px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <nav class="sidebar">
            <h2>Admin Dashboard</h2>
            <ul>
                <li><a href="patient_list.php">Patient List</a></li>
                <li><a href="doctor_list.php">Doctor Applications</a></li>
                <li><a href="receptionist_list.php">Receptionist Applications</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="content">
            <!-- Statistics Row -->
            <div class="stats-row">
                <div class="stat-box">
                    <h3>Total Doctors</h3>
                    <span><?php echo $doctor_count_display; ?></span>
                </div>
                <div class="stat-box">
                    <h3>Total Patients</h3>
                    <span><?php echo $patient_count_display; ?></span>
                </div>
            </div>

            <!-- Patient Status -->
            <div class="patient-box">
                <h3>Total Patient Status</h3>
                <div class="circle">
                    <div class="circle-text"><?php echo $pending_percentage_display; ?>%</div>
                </div>
                <p>Pending: <?php echo $pending_count; ?> | Completed: <?php echo $completed_count_display; ?></p>
            </div>
        </div>
    </div>
</body>
</html>
