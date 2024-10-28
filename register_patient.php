<?php
include 'connection.php';

function redirect_to_payment($amount, $patient_id, $hospital_id) {
    header("Location: payment_page.php?amount=$amount&patient_id=$patient_id&hospital_id=$hospital_id");
    exit();
}

// Sanitize input data
$first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
$last_name = !empty($_POST['last_name']) ? mysqli_real_escape_string($conn, $_POST['last_name']) : null;
$dob = mysqli_real_escape_string($conn, $_POST['dob']);
$gender = mysqli_real_escape_string($conn, $_POST['gender']);
$blood_group = mysqli_real_escape_string($conn, $_POST['blood_group']);
$email = mysqli_real_escape_string($conn, $_POST['email']);
$password = password_hash($_POST['password'], PASSWORD_BCRYPT);
$hospital_id = intval($_POST['hospital_id']);

// Check if email is already registered
$sql_check = "SELECT id FROM patients WHERE email = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("s", $email);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    $patient = $result_check->fetch_assoc();
    $patient_id = $patient['id'];
} else {
    // Calculate age from dob
    $dob_date = new DateTime($dob);
    $now = new DateTime();
    $age = $now->diff($dob_date)->y;

    // Insert new patient
    $sql_patient = "INSERT INTO patients (first_name, last_name, date_of_birth, age, gender, blood_group, email, password)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_patient = $conn->prepare($sql_patient);
    $stmt_patient->bind_param("ssssssss", $first_name, $last_name, $dob, $age, $gender, $blood_group, $email, $password);
    
    if ($stmt_patient->execute()) {
        $patient_id = $stmt_patient->insert_id;
    } else {
        die("Error: " . $stmt_patient->error);
    }
    $stmt_patient->close();
}

// Link patient to hospital
$sql_hospital = "SELECT registration_fee, registration_duration FROM hospitals WHERE id = ?";
$stmt_hospital = $conn->prepare($sql_hospital);
$stmt_hospital->bind_param("i", $hospital_id);
$stmt_hospital->execute();
$result_hospital = $stmt_hospital->get_result();
$hospital = $result_hospital->fetch_assoc();

if ($hospital) {
    $sql_link = "INSERT INTO patient_hospitals (patient_id, hospital_id) VALUES (?, ?)";
    $stmt_link = $conn->prepare($sql_link);
    $stmt_link->bind_param("ii", $patient_id, $hospital_id);
    
    if ($stmt_link->execute()) {
        if ($hospital['registration_fee'] > 0) {
            // Redirect to payment page if fee is applicable
            redirect_to_payment($hospital['registration_fee'], $patient_id, $hospital_id);
        } else {
            echo "Registration successful!";
        }
    } else {
        die("Error: " . $stmt_link->error);
    }
    $stmt_link->close();
}

$stmt_hospital->close();
$conn->close();
?>
