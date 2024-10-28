<?php
// Include connection
include 'connection.php';

// Capture POST data
$first_name = $_POST['first_name'];
$last_name = $_POST['last_name'];
$specialization = $_POST['specialization'];
$email = $_POST['email'];
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];
$hospital_id = $_POST['hospital_id'];
$zip_code = $_POST['zip_code'];

// Validate data
$errors = [];

// Validate password match
if ($password !== $confirm_password) {
    $errors[] = "Passwords do not match.";
}

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

// Validate and upload signature file
$signature_file = $_FILES['signature_file'];
if ($signature_file['error'] === UPLOAD_ERR_OK) {
    $file_name = basename($signature_file['name']);
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $allowed_ext = ['jpeg', 'jpg', 'png'];
    if (!in_array($file_ext, $allowed_ext)) {
        $errors[] = "Invalid file type for signature.";
    } else {
        $upload_dir = 'uploads/signatures/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $target_file = $upload_dir . uniqid() . '.' . $file_ext;
        if (!move_uploaded_file($signature_file['tmp_name'], $target_file)) {
            $errors[] = "Failed to upload signature file.";
        }
    }
} else {
    $errors[] = "Signature file upload error.";
}

if (empty($errors)) {
    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Prepare and execute the SQL statement
    $stmt = $conn->prepare("INSERT INTO doctors (first_name, last_name, specialization, email, password, hospital_id, zip_code, doctor_signature) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssis", $first_name, $last_name, $specialization, $email, $hashed_password, $hospital_id, $zip_code, $target_file);

    if ($stmt->execute()) {
        echo "Doctor registered successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
} else {
    foreach ($errors as $error) {
        echo "<p>$error</p>";
    }
}

$conn->close();
?>
