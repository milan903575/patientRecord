<?php
session_start();
require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';
include '../connection.php'; // Include the database connection file

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if the user is logged in and has a session user_id
if (!isset($_SESSION['user_id'])) {
    header("Location: logout.php");
    exit();
}

$user_id = $_SESSION['user_id']; // Retrieve patient ID from session
$message = "";

// Handle OTP generation and sending
if (!isset($_SESSION['otp'])) {
    // Fetch email from the patients table
    $sql = "SELECT email FROM patients WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $patient = $result->fetch_assoc();

    if ($patient) {
        $email = $patient['email'];

        // Generate a unique OTP (6 digits)
        $otp = rand(100000, 999999);
        $_SESSION['otp'] = $otp;
        $_SESSION['patient_email'] = $email;

        // Send OTP via email
        $message = sendOtpEmail($email, $otp);
    } else {
        $message = "No patient found with the given ID.";
        $stmt->close();
        $conn->close();
        exit();
    }

    $stmt->close();
}

// Handle OTP verification
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['otp'])) {
    if ($_POST['otp'] == $_SESSION['otp']) {
        header("Location: patient_homepage.php"); // Redirect on successful OTP verification
        exit();
    } else {
        $message = "Invalid OTP. Please try again.";
    }
}

// Function to send OTP email
function sendOtpEmail($email, $otp) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'milansooraj93@gmail.com'; // Replace with your email
        $mail->Password = 'ifag urwx cjry fsst';   // Replace with your App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('milansooraj93@gmail.com', 'Login OTP');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Your OTP for Login';
        $mail->Body = 'Your OTP is: <b>' . $otp . '</b>';

        $mail->send();
        return "An OTP has been sent to your email.";
    } catch (Exception $e) {
        error_log("PHPMailer error: " . $mail->ErrorInfo);
        return "An error occurred while sending the OTP.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #87CEEB;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            max-width: 400px;
            width: 90%;
            text-align: center;
        }
        h2 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
        }
        p {
            font-size: 16px;
            color: #555;
            margin-bottom: 15px;
        }
        input[type="number"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }
        .countdown {
            font-size: 20px;
            color: #FF0000;
            margin: 10px 0;
        }
        button {
            width: 100%;
            padding: 12px;
            margin: 5px 0;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .btn-submit {
            background-color: #4CAF50;
            color: white;
        }
        .btn-submit:hover {
            background-color: #45a049;
        }
        .btn-resend {
            background-color: #007bff;
            color: white;
            display: none;
        }
        .btn-resend:hover {
            background-color: #0056b3;
        }
        .btn-verify {
            background-color: #ff5722;
            color: white;
            display: none;
        }
        .btn-verify:hover {
            background-color: #e64a19;
        }
        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }
            h2 {
                font-size: 20px;
            }
            button {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>OTP Verification</h2>
        <?php if (!empty($message)) : ?>
            <p><?= htmlspecialchars($message); ?></p>
        <?php endif; ?>
        <form method="POST">
            <p>Enter the OTP sent to your email:</p>
            <input type="number" name="otp" placeholder="Enter OTP" required>
            <div class="countdown" id="countdown">30</div>
            <button type="submit" class="btn-submit">Verify OTP</button>
        </form>
        <button class="btn-resend" onclick="location.reload()">Resend OTP</button>
        <button class="btn-verify" onclick="window.location.href='verify_face.php'">Continue with Face Verification</button>
    </div>

    <script>
        let countdownElement = document.getElementById('countdown');
        let resendButton = document.querySelector('.btn-resend');
        let verifyButton = document.querySelector('.btn-verify');
        let timer = 30;

        let interval = setInterval(() => {
            timer--;
            countdownElement.textContent = timer;

            if (timer <= 0) {
                clearInterval(interval);
                countdownElement.textContent = "Time expired.";
                resendButton.style.display = "block";
                verifyButton.style.display = "block";
            }
        }, 1000);
    </script>
</body>
</html>
