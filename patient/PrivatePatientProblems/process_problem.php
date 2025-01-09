<?php
require_once '../../connection.php';
session_start();

// Check if the patient is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.html");
    exit;
}

$patient_id = $_SESSION['user_id'];

// Retrieve the encryption key
$key_path = 'C:/secure_keys/encryption_key.key';
$encryption_key = trim(file_get_contents($key_path));

if (!$encryption_key) {
    displayMessageAndRedirect("Encryption key is missing!", "submit_problem.php", false);
    exit;
}

// Process the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $problem_description = $_POST['problem_description'];
    $doctor_id = $_POST['doctor_id'];

    if (!$doctor_id) {
        displayMessageAndRedirect("Invalid doctor selected. Please try again.", "submit_problem.php", false);
        exit;
    }

    $sql = "SELECT hospital_id FROM patient_hospital WHERE patient_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $hospital_id = $row['hospital_id'] ?? null;

    if (!$hospital_id) {
        displayMessageAndRedirect("Hospital not found for this patient.", "submit_problem.php", false);
        exit;
    }

    $sql = "SELECT id FROM doctors WHERE id = ? AND hospital_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $doctor_id, $hospital_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        displayMessageAndRedirect("Selected doctor is not associated with the patient's hospital.", "submit_problem.php", false);
        exit;
    }

    $iv_desc = random_bytes(openssl_cipher_iv_length('aes-256-gcm'));
    $tag_desc = '';
    $encrypted_description = openssl_encrypt(
        $problem_description,
        'aes-256-gcm',
        $encryption_key,
        0,
        $iv_desc,
        $tag_desc
    );

    if ($encrypted_description === false) {
        displayMessageAndRedirect("Encryption failed for problem description!", "submit_problem.php", false);
        exit;
    }

    $encrypted_video = null;
    $iv_video = null;
    $tag_video = null;

    if (isset($_FILES['video_upload']) && $_FILES['video_upload']['error'] === UPLOAD_ERR_OK) {
        if ($_FILES['video_upload']['size'] > 200 * 1024 * 1024) {
            displayMessageAndRedirect("File size exceeds the 200 MB limit.", "submit_problem.php", false);
            exit;
        }

        $video_tmp_path = $_FILES['video_upload']['tmp_name'];
        $video_data = file_get_contents($video_tmp_path);

        $iv_video = random_bytes(openssl_cipher_iv_length('aes-256-gcm'));
        $tag_video = '';
        $encrypted_video = openssl_encrypt(
            $video_data,
            'aes-256-gcm',
            $encryption_key,
            0,
            $iv_video,
            $tag_video
        );

        if ($encrypted_video === false) {
            displayMessageAndRedirect("Encryption failed for video file!", "submit_problem.php", false);
            exit;
        }
    }

    $query = "INSERT INTO private_problems 
              (patient_id, doctor_id, hospital_id, problem_description, iv, auth_tag, video_file, video_iv, video_auth_tag) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);

    $stmt->bind_param(
        'iiissssss',
        $patient_id,
        $doctor_id,
        $hospital_id,
        $encrypted_description,
        $iv_desc,
        $tag_desc,
        $encrypted_video,
        $iv_video,
        $tag_video
    );

    if ($encrypted_video !== null) {
        $stmt->send_long_data(6, $encrypted_video);
    }

    if ($stmt->execute()) {
        displayMessageAndRedirect("Problem submitted securely.", "../patient_homepage.php", true);
    } else {
        displayMessageAndRedirect("Database error: " . $stmt->error, "submit_problem.php", false);
    }

    $stmt->close();
}

// Function to display a message and redirect with a countdown
function displayMessageAndRedirect($message, $redirect_url, $success) {
    echo "
        <div style='
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            text-align: center; 
            font-family: Arial, sans-serif;'>
            <div>
                <h2>" . htmlspecialchars($message) . "</h2>
                <p>Redirecting in <span id='countdown'>3</span> seconds...</p>
            </div>
        </div>
        <script>
            let countdown = 3;
            const interval = setInterval(() => {
                countdown--;
                document.getElementById('countdown').textContent = countdown;
                if (countdown <= 0) {
                    clearInterval(interval);
                    window.location.href = '" . $redirect_url . "';
                }
            }, 1000);
        </script>
    ";
}
?>
