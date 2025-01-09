<?php
session_start();

// Clear session data for a fresh start
if (!isset($_POST['email']) && !isset($_POST['otp'])) {
    session_unset();
    session_destroy();
    session_start(); // Restart the session to avoid issues with subsequent requests
}

include '../connection.php';
require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Function to send OTP
function sendOtp($email, $otp) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'milansooraj93@gmail.com'; // Replace with your email
        $mail->Password = 'ifag urwx cjry fsst'; // Replace with your App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('your-email@gmail.com', 'OTP Verification');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Your OTP Code';
        $mail->Body    = 'Your OTP is: <b>' . $otp . '</b>';

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("PHPMailer error: " . $mail->ErrorInfo);
        return false;
    }
}

// Handle form submission
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_SESSION['otp']) && isset($_POST['email'])) { // Email submission
        $email = mysqli_real_escape_string($conn, $_POST['email']);

        // Check email in patients and doctors tables
        $query = "SELECT email FROM patients WHERE email = ? UNION SELECT email FROM doctors WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $email, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Generate OTP and store in session
            $otp = rand(100000, 999999);
            $_SESSION['otp'] = $otp;
            $_SESSION['email'] = $email;

            // Send OTP to user's email
            if (sendOtp($email, $otp)) {
                $message = "OTP sent to your email.";
            } else {
                $message = "Failed to send OTP. Try again.";
            }
        } else {
            $message = "Email not found in our records.";
        }
    } elseif (isset($_SESSION['otp']) && isset($_POST['otp'])) { // OTP verification
        $otp = $_POST['otp'];

        if ($otp == $_SESSION['otp']) {
            // OTP is correct; proceed to next page
            header("Location: update_password.php");
            exit();
        } else {
            $message = "Invalid OTP. Try again.";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email & OTP Verification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .form-container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        .form-container h2 {
            margin-bottom: 20px;
        }
        .form-container input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .form-container button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .form-container button:hover {
            background-color: #0056b3;
        }
        .message {
            color: red;
            font-size: 14px;
        }
        .hidden {
            display: none;
        }
    </style>
    <script>
        function toggleVisibility() {
            const emailForm = document.getElementById("email-form");
            const otpForm = document.getElementById("otp-form");

            <?php if (isset($_SESSION['otp'])): ?>
            emailForm.classList.add("hidden");
            otpForm.classList.remove("hidden");
            <?php else: ?>
            emailForm.classList.remove("hidden");
            otpForm.classList.add("hidden");
            <?php endif; ?>
        }
        window.onload = toggleVisibility;
    </script>
</head>
<body>
<div class="form-container">
    <!-- Email Form -->
    <form id="email-form" method="POST" <?php echo isset($_SESSION['otp']) ? 'class="hidden"' : ''; ?>>
        <h2>Enter Email</h2>
        <input type="email" id="email" name="email" placeholder="Enter your email" required>
        <button type="submit">Send OTP</button>
    </form>

    <!-- OTP Form -->
    <form id="otp-form" method="POST" <?php echo !isset($_SESSION['otp']) ? 'class="hidden"' : ''; ?>>
        <h2>Enter OTP</h2>
        <input type="number" id="otp" name="otp" placeholder="Enter the OTP" required>
        <button type="submit">Verify OTP</button>
    </form>

    <!-- Message Section -->
    <?php if ($message): ?>
        <p class="message"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
</div>

</body>
</html>

