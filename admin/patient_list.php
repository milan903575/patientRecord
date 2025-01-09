<?php
include '../connection.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient List</title>
    <style>
        /* Add table styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        button {
            margin: 5px;
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            background-color: #1abc9c;
            color: white;
        }

        button:hover {
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <h3>Patient List</h3>
    <table>
        <thead>
            <tr>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Problem</th>
                <th>Created At</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
<?php
$query = "SELECT patients.first_name, patients.last_name, 
                 patients.created_at, patient_history.status, patient_history.problem 
          FROM patients 
          JOIN patient_history ON patients.id = patient_history.patient_id 
          WHERE patient_history.status = 'pending'";

$result = mysqli_query($conn, $query);

while ($row = mysqli_fetch_assoc($result)) {
    // Sanitize the data before displaying
    $first_name = htmlspecialchars($row['first_name']);
    $last_name = htmlspecialchars($row['last_name']);
    $problem = htmlspecialchars($row['problem']);
    $created_at = htmlspecialchars($row['created_at']);
    $status = htmlspecialchars($row['status']);
    
    echo "<tr>
            <td>{$first_name}</td>
            <td>{$last_name}</td>
            <td>{$problem}</td>
            <td>{$created_at}</td>
            <td>{$status}</td>
          </tr>";
}
?>

        </tbody>
    </table>
</body>
</html>
