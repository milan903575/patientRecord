<?php
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
include 'connection.php'; // Include the database connection file

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    // Check if the email exists in the patients table
    $sql_patient = "SELECT * FROM patients WHERE email = ?";
    $stmt_patient = $conn->prepare($sql_patient);
    $stmt_patient->bind_param("s", $email);
    $stmt_patient->execute();
    $result_patient = $stmt_patient->get_result();

    if ($result_patient->num_rows > 0) {
        // Generate a unique reset token
        $reset_token = bin2hex(random_bytes(50));

        // Store the reset token in the patients table
        $sql_update = "UPDATE patients SET reset_token = ? WHERE email = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("ss", $reset_token, $email);
        $stmt_update->execute();

        // Send the reset link to the user's email
        $message = sendPasswordResetEmail($email, $reset_token);
        $redirect_url = "forgot_password.php"; // Assuming the reset link is sent from this page
    } else {
        // Check if the email exists in the doctors table
        $sql_doctor = "SELECT * FROM doctors WHERE email = ?";
        $stmt_doctor = $conn->prepare($sql_doctor);
        $stmt_doctor->bind_param("s", $email);
        $stmt_doctor->execute();
        $result_doctor = $stmt_doctor->get_result();

        if ($result_doctor->num_rows > 0) {
            // Generate a unique reset token
            $reset_token = bin2hex(random_bytes(50));

            // Store the reset token in the doctors table
            $sql_update = "UPDATE doctors SET reset_token = ? WHERE email = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("ss", $reset_token, $email);
            $stmt_update->execute();

            // Send the reset link to the user's email
            $message = sendPasswordResetEmail($email, $reset_token);
            $redirect_url = "login.html"; // Assuming the reset link is sent from this page
        } else {
            $message = "No account found with that email address.";
            $redirect_url = "forgot_password.php";
        }
    }

    $stmt_patient->close();
    $stmt_doctor->close();
    $conn->close();

    // Redirect with message
    echo "<!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <meta http-equiv='refresh' content='3;url=$redirect_url'>
        <title>Redirecting...</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                text-align: center;
                margin-top: 20%;
            }
        </style>
    </head>
    <body>
        <p>$message</p>
        <p>You will be redirected in 3 seconds...</p>
    </body>
    </html>";
}
function sendPasswordResetEmail($email, $reset_token) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'milansooraj93@gmail.com'; // Replace with your email
        $mail->Password = 'tsha euxj brhw ooet'; // Replace with your App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Secure connection
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('your-email@gmail.com', 'Password Reset'); // Replace with your email
        $mail->addAddress($email);

        // Content
        $reset_link = "http://localhost/patientRecord/reset_password.php?token=" . $reset_token;
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request';
        $mail->Body = 'Click the link to reset your password: <a href="' . $reset_link . '">' . $reset_link . '</a>';

        $mail->send();
        return "A password reset link has been sent to your email.";
    } catch (Exception $e) {
        return "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

?>
