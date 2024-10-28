<?php
include 'connection.php'; // Include the database connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST['token'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Check if the token exists in the patients table
    $sql_patient = "SELECT * FROM patients WHERE reset_token = ?";
    $stmt_patient = $conn->prepare($sql_patient);
    $stmt_patient->bind_param("s", $token);
    $stmt_patient->execute();
    $result_patient = $stmt_patient->get_result();

    if ($result_patient->num_rows > 0) {
        // Update the password for the patient
        $sql_update = "UPDATE patients SET password = ?, reset_token = NULL WHERE reset_token = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("ss", $password, $token);
        $stmt_update->execute();

        echo "Your password has been reset successfully.";
    } else {
        // Check if the token exists in the doctors table
        $sql_doctor = "SELECT * FROM doctors WHERE reset_token = ?";
        $stmt_doctor = $conn->prepare($sql_doctor);
        $stmt_doctor->bind_param("s", $token);
        $stmt_doctor->execute();
        $result_doctor = $stmt_doctor->get_result();

        if ($result_doctor->num_rows > 0) {
            // Update the password for the doctor
            $sql_update = "UPDATE doctors SET password = ?, reset_token = NULL WHERE reset_token = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("ss", $password, $token);
            $stmt_update->execute();

            echo "Your password has been reset successfully.";
        } else {
            echo "Invalid token.";
        }
    }

    $stmt_patient->close();
    $stmt_doctor->close();
    $conn->close();
}
?>
