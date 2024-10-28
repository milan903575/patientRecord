<?php
include 'connection.php';
session_start();

// Check if the patient is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'patient') {
    header("Location: login.html");
    exit;
}

$patient_id = $_SESSION['user_id'];
$problem = isset($_POST['problem']) ? mysqli_real_escape_string($conn, $_POST['problem']) : '';
$problem_description = isset($_POST['problem_description']) ? mysqli_real_escape_string($conn, $_POST['problem_description']) : '';
$current_medication = isset($_POST['current_medication']) ? mysqli_real_escape_string($conn, $_POST['current_medication']) : '';
$hospital_id = isset($_POST['hospital_id']) ? (int)$_POST['hospital_id'] : 0;
$ai_solution = isset($_POST['ai_solution']) ? mysqli_real_escape_string($conn, $_POST['ai_solution']) : '';
$date_submitted = date('Y-m-d H:i:s');

// Handle video file upload
$video_path = null;
if (!empty($_FILES['video']['name'])) {
    $target_dir = "uploads/videos/";
    $video_file = $target_dir . basename($_FILES["video"]["name"]);
    $videoFileType = strtolower(pathinfo($video_file, PATHINFO_EXTENSION));

    // Check file size (max 50MB)
    if ($_FILES["video"]["size"] > 50000000) {
        echo "Sorry, your file is too large.";
        exit;
    }

    // Allow certain file formats
    $allowed_types = array("mp4", "avi", "mov", "wmv");
    if (!in_array($videoFileType, $allowed_types)) {
        echo "Sorry, only MP4, AVI, MOV & WMV files are allowed.";
        exit;
    }

    // Move the uploaded file to the target directory
    if (move_uploaded_file($_FILES["video"]["tmp_name"], $video_file)) {
        $video_path = $video_file;
    } else {
        echo "Sorry, there was an error uploading your file.";
        exit;
    }
}

// Check if required fields are not empty
if (empty($problem) || empty($problem_description) || empty($current_medication) || empty($hospital_id)) {
    echo "All fields are required.";
    exit;
}

// Insert new detailed problem into patient history
$sql = "INSERT INTO patient_history (patient_id, problem, problem_description, current_medication, video_path, date_submitted, hospital_id, ai_solution)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo "Error preparing statement: " . $conn->error;
    exit;
}
$stmt->bind_param("issssssss", $patient_id, $problem, $problem_description, $current_medication, $video_path, $date_submitted, $hospital_id, $ai_solution);

if ($stmt->execute()) {
    header("Location: patient_profile.php"); // Redirect to profile page after submission
    exit;
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
