<?php
include '../../connection.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'patient') {
    header("Location: ../../login.html");
    exit;
}

$patient_id = $_SESSION['user_id'];
$order_by = isset($_GET['order_by']) && $_GET['order_by'] === 'oldest' ? 'ASC' : 'DESC';

// Retrieve the encryption key
$key_path = 'C:/secure_keys/encryption_key.key';
$encryption_key = trim(file_get_contents($key_path));

if (!$encryption_key) {
    die("Encryption key is missing!");
}

$sql_history = "
    SELECT pp.id AS history_id, pp.problem_description, pp.iv AS problem_iv, pp.auth_tag AS problem_auth_tag, 
           pp.doctor_solution, pp.solution_iv, pp.solution_auth_tag, pp.status,
           CASE 
               WHEN pp.doctor_id IS NULL THEN 'Not Selected' 
               ELSE CONCAT('Dr. ', COALESCE(d.first_name, ''), ' ', COALESCE(d.last_name, '')) 
           END AS doctor_name, 
           pp.updated_at AS date, 
           h.hospital_name AS hospital
    FROM private_problems pp
    LEFT JOIN hospitals h ON pp.hospital_id = h.id
    LEFT JOIN doctors d ON pp.doctor_id = d.id
    WHERE pp.patient_id = ?
    ORDER BY pp.created_at $order_by
";

$stmt_history = $conn->prepare($sql_history);
$stmt_history->bind_param("i", $patient_id);
$stmt_history->execute();
$result_history = $stmt_history->get_result();
$stmt_history->close();

// Decrypt the data
$decrypted_history = [];
while ($row = $result_history->fetch_assoc()) {
    // Decrypt the problem description
    $decrypted_problem = openssl_decrypt(
        $row['problem_description'],
        'aes-256-gcm',
        $encryption_key,
        0,
        $row['problem_iv'],
        $row['problem_auth_tag']
    );

    // Decrypt the doctor solution if it exists
    $decrypted_solution = null;
    if ($row['doctor_solution']) {
        $decrypted_solution = openssl_decrypt(
            $row['doctor_solution'],
            'aes-256-gcm',
            $encryption_key,
            0,
            $row['solution_iv'],
            $row['solution_auth_tag']
        );
    }

    // Store the decrypted data for use in display
    $row['problem_description'] = $decrypted_problem;
    $row['doctor_solution'] = $decrypted_solution ? $decrypted_solution : 'Pending';
    $row['status'] = $row['status'] === 'completed' ? 'Completed' : 'Pending';
    $decrypted_history[] = $row;
}

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
        .status {
            font-size: 14px;
            color: #888;
            font-weight: bold;
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
            <span>Sort by:</span>
            <a href="?order_by=recent">Recent</a>
            <a href="?order_by=oldest">Oldest</a>
        </div>
        <div class="grid">
            <?php foreach ($decrypted_history as $row): ?>
            <a class="card-link" href="history_detail.php?history_id=<?= $row['history_id'] ?>">
                <div class="card">
                    <div class="hospital-name">Hospital: <?= htmlspecialchars($row['hospital']) ?></div>
                    <div class="details"><strong>Problem:</strong> <?= htmlspecialchars($row['problem_description']) ?></div>
                    
                    <?php if ($row['doctor_solution'] === 'Pending'): ?>
                        <div class="details"><strong>Doctor's Solution:</strong> Pending</div>
                    <?php else: ?>
                        <div class="details"><strong>Doctor's Solution:</strong> <?= htmlspecialchars($row['doctor_solution']) ?></div>
                    <?php endif; ?>
                    
                    <div class="doctor"><strong>Doctor:</strong> <?= htmlspecialchars($row['doctor_name']) ?></div>
                    <div class="date">Updated on: <?= htmlspecialchars($row['date']) ?></div>
                    <div class="status"><?= htmlspecialchars($row['status']) ?></div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
