<?php
// Include connection
include '../connection.php';

// Sanitize POST data
$first_name = mysqli_real_escape_string($conn, trim($_POST['first_name']));
$last_name = mysqli_real_escape_string($conn, trim($_POST['last_name']));
$specialization = mysqli_real_escape_string($conn, trim($_POST['specialization']));
$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL); // Email sanitization
$password = mysqli_real_escape_string($conn, trim($_POST['password']));
$confirm_password = mysqli_real_escape_string($conn, trim($_POST['confirm_password']));
$hospital_id = intval($_POST['hospital_id']);
$location = mysqli_real_escape_string($conn, trim($_POST['location']));
$dob = mysqli_real_escape_string($conn, trim($_POST['dob'])); // Validate date separately if required
$gender = mysqli_real_escape_string($conn, trim($_POST['gender']));
$terms = isset($_POST['terms']) ? 1 : 0;
$consent = isset($_POST['consent']) ? 1 : 0;

// File upload handling remains the same
$gov_id_proof = null;
$hospital_id_proof = null;
$profile_picture = null;

// Check file uploads and validate
$allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
$max_file_size = 5 * 1024 * 1024; // 5MB in bytes

$files = [
    'gov_id_proof' => &$gov_id_proof,
    'hospital_id_proof' => &$hospital_id_proof,
    'profile_file' => &$profile_picture
];

foreach ($files as $key => &$file) {
    if (!empty($_FILES[$key]['tmp_name'])) {
        // Validate file type
        if (!in_array($_FILES[$key]['type'], $allowed_types)) {
            die("Invalid file type for $key. Only JPEG, PNG, and PDF files are allowed.");
        }

        // Validate file size
        if ($_FILES[$key]['size'] > $max_file_size) {
            die("File size for $key exceeds the maximum limit of 5MB.");
        }

        // Read file content for BLOB storage
        $file = file_get_contents($_FILES[$key]['tmp_name']);
    }
}

// Validate data
$errors = [];

// Validate password match
if ($password !== $confirm_password) {
    $errors[] = "Passwords do not match.";
}

// Check if email exists in doctors, patients, or receptionists
$email_check = $conn->prepare("SELECT id FROM doctors WHERE email = ? UNION SELECT id FROM patients WHERE email = ? UNION SELECT id FROM receptionist WHERE email = ?");
$email_check->bind_param("sss", $email, $email, $email);
$email_check->execute();
$email_check_result = $email_check->get_result();
if ($email_check_result->num_rows > 0) {
    $errors[] = "This email is already registered in the system.";
}
$email_check->close();

// Validate hospital ID
if (empty($hospital_id)) {
    $errors[] = "Hospital not selected.";
}

// Check if hospital ID exists in the database
$hospital_check = $conn->prepare("SELECT id FROM hospitals WHERE id = ?");
$hospital_check->bind_param("i", $hospital_id);
$hospital_check->execute();
$hospital_check_result = $hospital_check->get_result();
if ($hospital_check_result->num_rows === 0) {
    $errors[] = "Hospital not found.";
}
$hospital_check->close();

// If there are errors, display them with a redirect
if (!empty($errors)) {
    echo "<div class='message-container'>
            <h2 class='error-heading'>Error</h2>";
    foreach ($errors as $error) {
        echo "<p class='error-message'>$error</p>";
    }
    echo "<p>You will be redirected in <span id='countdown'>3</span> seconds...</p>
          <script>
            var countdown = 3;
            setInterval(function() {
                countdown--;
                document.getElementById('countdown').innerText = countdown;
                if (countdown == 0) {
                    window.location.href = 'doctor_registration.php'; // Specify your redirection page here
                }
            }, 1000);
          </script>
          </div>";
} else {
    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Prepare registration status
    $registration_status = 'pending';

    // Prepare and execute the SQL statement to insert the data into the database
    $stmt = $conn->prepare("INSERT INTO doctors (first_name, last_name, dob, gender, email, password, specialization, hospital_id, location, terms, consent, registration_status, gov_id_proof, hospital_id_proof, profile_picture) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssissssssbb", $first_name, $last_name, $dob, $gender, $email, $hashed_password, $specialization, $hospital_id, $location, $terms, $consent, $registration_status, $gov_id_proof, $hospital_id_proof, $profile_picture);

    // Execute the query
    if ($stmt->execute()) {
        echo "<div class='message-container'>
                <h2 class='success-heading'>Registration Successful! Your application has been sent to the admin. Please try to login.</h2>
                <p class='success-message'>You will be redirected in <span id='countdown'>9</span> seconds...</p>
                <script>
                    var countdown = 9;
                    setInterval(function() {
                        countdown--;
                        document.getElementById('countdown').innerText = countdown;
                        if (countdown == 0) {
                            window.location.href = '../login.html'; // Specify your redirection page here
                        }
                    }, 1000);
                </script>
              </div>";
    } else {
        echo "<div class='message-container'>
                <h2 class='error-heading'>Error</h2>
                <p class='error-message'>Registration failed: " . $stmt->error . "</p>
                <p>You will be redirected in <span id='countdown'>3</span> seconds...</p>
                <script>
                    var countdown = 3;
                    setInterval(function() {
                        countdown--;
                        document.getElementById('countdown').innerText = countdown;
                        if (countdown == 0) {
                            window.location.href = 'doctor_registration.php'; // Specify your redirection page here
                        }
                    }, 1000);
                </script>
              </div>";
    }

    $stmt->close();
}

$conn->close();
?>


<!-- CSS and JS included for message styling and countdown -->
<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        padding: 20px;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }

    .message-container {
        text-align: center;
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        width: 400px;
    }

    .error-heading {
        color: red;
        font-size: 24px;
    }

    .success-heading {
        color: green;
        font-size: 24px;
    }

    .error-message, .success-message {
        color: #ff0000;
        font-size: 18px;
        margin: 10px 0;
    }

    #countdown {
        font-weight: bold;
        color: #333;
    }
</style>

