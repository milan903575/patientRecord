<?php
include 'connection.php'; // Include the database connection file
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if email and password fields are set
    if (isset($_POST['email']) && isset($_POST['password'])) {
        // Form data sanitization
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $password = $_POST['password'];

        // Query to check if email exists in patients table
        $sql_patient = "SELECT * FROM patients WHERE email = ?";
        $stmt_patient = $conn->prepare($sql_patient);
        $stmt_patient->bind_param("s", $email);
        $stmt_patient->execute();
        $result_patient = $stmt_patient->get_result();

        if ($result_patient->num_rows > 0) {
            // Check password for patient
            $patient = $result_patient->fetch_assoc();
            if (password_verify($password, $patient['password'])) {
                $_SESSION['user_id'] = $patient['id'];
                $_SESSION['user_type'] = 'patient';
                header("Location: patient_homepage.php"); // Redirect to patient profile page
                exit();
            } else {
                echo "Invalid email or password.";
            }
        } else {
            // Query to check if email exists in doctors table
            $sql_doctor = "SELECT * FROM doctors WHERE email = ?";
            $stmt_doctor = $conn->prepare($sql_doctor);
            $stmt_doctor->bind_param("s", $email);
            $stmt_doctor->execute();
            $result_doctor = $stmt_doctor->get_result();

            if ($result_doctor->num_rows > 0) {
                // Check password for doctor
                $doctor = $result_doctor->fetch_assoc();
                if (password_verify($password, $doctor['password'])) {
                    $_SESSION['user_id'] = $doctor['id'];
                    $_SESSION['user_type'] = 'doctor';
                    header("Location: doctor_profile.php"); // Redirect to doctor dashboard
                    exit();
                } else {
                    echo "Invalid email or password.";
                }
            } else {
                echo "Invalid email or password.";
            }
        }

        $stmt_patient->close();
        $stmt_doctor->close();
    } else {
        echo "Email and password fields are required.";
    }
}
$conn->close();
?>
