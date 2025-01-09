<?php
include '../connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate input data
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);
    $hospital_id = mysqli_real_escape_string($conn, $_POST['hospital_id']);

    // File uploads
    $hospital_id_proof = $_FILES['hospital_id_proof']['name'];
    $government_id_proof = $_FILES['government_id_proof']['name'];

    // File upload directories
    $upload_dir = 'uploads/';
    $hospital_id_proof_path = $upload_dir . basename($hospital_id_proof);
    $government_id_proof_path = $upload_dir . basename($government_id_proof);

    // Password validation
    if ($password !== $confirm_password) {
        showMessage("Error: Passwords do not match.", "receptionist_registration.php", 3);
        exit;
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if email exists in any table
    $check_email_sql = "
        SELECT email FROM patients WHERE email = ? 
        UNION 
        SELECT email FROM doctors WHERE email = ? 
        UNION 
        SELECT email FROM receptionist WHERE email = ?
    ";
    $stmt = $conn->prepare($check_email_sql);
    $stmt->bind_param('sss', $email, $email, $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        showMessage("Error: Email already exists. login or use different email.", "login.html", 3);
        $stmt->close();
        $conn->close();
        exit;
    }

    $stmt->close();

    // Move uploaded files
    if (!move_uploaded_file($_FILES['hospital_id_proof']['tmp_name'], $hospital_id_proof_path) ||
        !move_uploaded_file($_FILES['government_id_proof']['tmp_name'], $government_id_proof_path)) {
        showMessage("Error: File upload failed.", "receptionist_registration.php", 3);
        $conn->close();
        exit;
    }

    // Insert new receptionist
    $insert_sql = "
        INSERT INTO receptionists (first_name, last_name, email, password, hospital_id, hospital_id_proof, government_id_proof, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')
    ";
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param('ssssiss', $first_name, $last_name, $email, $hashed_password, $hospital_id, $hospital_id_proof_path, $government_id_proof_path);

    if ($stmt->execute()) {
        showMessage("Receptionist registered successfully! Application sent to admin for approval. Try to login.", "login.html", 10);
    } else {
        showMessage("Error: " . $stmt->error, "receptionist_registration.php", 5);
    }

    $stmt->close();
    $conn->close();
} else {
    showMessage("Invalid request.", "receptionist_registration.php", 3);
    exit;
}

// Function to display the styled message and handle redirection
function showMessage($message, $redirectUrl, $redirectTime) {
    echo "
    <style>
        .message-container {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            padding: 20px;
            border: 1px solid #ddd;
            background-color: #f8f8f8;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            font-family: Arial, sans-serif;
            color: #333;
        }
        .message-container p {
            font-size: 1.1em;
            margin: 5px 0;
        }
        .message-container span {
            font-weight: bold;
            color: #e74c3c;
        }
    </style>
    <div class='message-container'>
        <p>$message</p>
        <p>You will be redirected in <span id='countdown'>$redirectTime</span> seconds...</p>
    </div>
    <script>
        let countdown = $redirectTime;
        const interval = setInterval(() => {
            countdown--;
            document.getElementById('countdown').textContent = countdown;
            if (countdown === 0) {
                clearInterval(interval);
                window.location.href = '$redirectUrl';
            }
        }, 1000);
    </script>";
}
?>
