<?php
session_start();
include '../connection.php';


if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // Redirect to login page if user_id is not set in the session
    header("Location: ../login.html");
    exit();
}
$user_id = $_SESSION['user_id'];

// Fetch receptionist's hospital ID
$query = "SELECT hospital_id FROM receptionist WHERE id = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    error_log("Hospital ID query preparation failed: " . $conn->error);
    exit;
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    error_log("Receptionist not found for user_id: " . $user_id);
    echo 'Receptionist not found.';
    exit;
}

$hospital_id = $result->fetch_assoc()['hospital_id'];
error_log("Hospital ID: " . $hospital_id);

// Handle search request when form is submitted via AJAX
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['email'])) {
    $email = trim($_POST['email']);
    error_log("Searching for email: " . $email);

    $query = "
        SELECT 
            p.first_name, 
            p.last_name, 
            p.email, 
            p.gender, 
            p.age,
            ph.registration_status
        FROM patients p
        JOIN patient_hospital ph ON p.id = ph.patient_id
        WHERE LOWER(p.email) = LOWER(?) AND ph.hospital_id = ?
    ";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Patient search query preparation failed: " . $conn->error);
        exit;
    }
    $stmt->bind_param("si", $email, $hospital_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $patient_data = $result->fetch_assoc();
        error_log("Patient found: " . json_encode($patient_data));

        echo json_encode([
            'first_name' => $patient_data['first_name'],
            'last_name' => $patient_data['last_name'],
            'email' => $patient_data['email'],
            'gender' => $patient_data['gender'],
            'age' => $patient_data['age'],
            'registration_status' => $patient_data['registration_status'],
            'statusColor' => ($patient_data['registration_status'] === 'active') ? 'green' : 'red'
        ]);
    } else {
        error_log("No patient found for email: $email and hospital ID: $hospital_id");
        echo json_encode(['error' => 'Patient not found in this hospital.']);
    }

    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receptionist Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }

        .dashboard-container {
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .search-bar {
            margin-bottom: 20px;
        }

        .patient-details {
            margin-top: 20px;
            display: none;
        }

        .status {
            font-weight: bold;
            padding: 5px 10px;
            border-radius: 5px;
            color: #fff;
            display: inline-block;
        }

        .status.green {
            background-color: green;
        }

        .status.red {
            background-color: red;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h2 class="text-center mb-4">Receptionist Dashboard</h2>
        <form id="searchForm" class="search-bar">
            <div class="input-group">
                <input 
                    type="text" 
                    class="form-control" 
                    id="emailInput" 
                    placeholder="Enter patient email..." 
                    required 
                />
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
        </form>
        <div id="patientDetails" class="patient-details">
            <h4>Patient Details:</h4>
            <p><strong>First Name:</strong> <span id="firstName"></span></p>
            <p><strong>Last Name:</strong> <span id="lastName"></span></p>
            <p><strong>Email:</strong> <span id="email"></span></p>
            <p><strong>Gender:</strong> <span id="gender"></span></p>
            <p><strong>Age:</strong> <span id="age"></span></p>
            <p>
                <strong>Registration Status:</strong> 
                <span id="registrationStatus" class="status"></span>
            </p>
        </div>
        <div id="noPatientFound" class="alert alert-warning mt-4" style="display: none;">
            No patient found with the provided email for your hospital.
        </div>
    </div>

    <script>
        const searchForm = document.getElementById('searchForm');
        const patientDetails = document.getElementById('patientDetails');
        const noPatientFound = document.getElementById('noPatientFound');

        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const email = document.getElementById('emailInput').value;

            fetch('receptionist_dashboard.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `email=${encodeURIComponent(email)}`
            })
            .then(response => response.json())
            .then(data => {
                console.log(data); // Log the data to check if JSON is returned
                if (data.error) {
                    noPatientFound.style.display = 'block';
                    patientDetails.style.display = 'none';
                } else {
                    noPatientFound.style.display = 'none';
                    patientDetails.style.display = 'block';

                    document.getElementById('firstName').textContent = data.first_name;
                    document.getElementById('lastName').textContent = data.last_name;
                    document.getElementById('email').textContent = data.email;
                    document.getElementById('gender').textContent = data.gender;
                    document.getElementById('age').textContent = data.age;
                    const statusElement = document.getElementById('registrationStatus');
                    statusElement.textContent = data.registration_status;
                    statusElement.className = `status ${data.statusColor}`;
                }
            })
            .catch(err => {
                console.error('Error:', err);
                noPatientFound.style.display = 'block';
                patientDetails.style.display = 'none';
            });
        });
    </script>
</body>
</html>
