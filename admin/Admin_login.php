<?php
require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';
include '../connection.php'; // Include the database connection file

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['email']) && isset($_POST['password'])) {
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $password = mysqli_real_escape_string($conn, $_POST['password']);

        // Query to check if the admin exists in the database
        $sql_admin = "SELECT * FROM hospitals WHERE email = ?";
        $stmt_admin = $conn->prepare($sql_admin);
        $stmt_admin->bind_param("s", $email);
        $stmt_admin->execute();
        $result_admin = $stmt_admin->get_result();
        $admin = $result_admin->fetch_assoc();

        // Verify if admin exists and password matches
 if ($admin && $admin['password'] === $password) { // Secure password verification
            // Generate a unique OTP (6 digits)
            $otp = rand(100000, 999999);
            $_SESSION['otp'] = $otp;             // Store OTP in session
            $_SESSION['admin_id'] = $email;     // Store admin email in session

            // Send OTP via email
            $message = sendOtpEmail($admin['email'], $otp);

            // Redirect to OTP verification page
            header("Location: otp_verification.php");
            exit();
        } else {
            $message = "Invalid Email or Password.";
        }

        $stmt_admin->close();
        $conn->close();
    } else {
        $message = "Please enter both email and password.";
    }
}

// Function to send OTP email
function sendOtpEmail($email, $otp) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'milansooraj93@gmail.com'; // Replace with your email
        $mail->Password = 'ifag urwx cjry fsst'; // Replace with your App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('milansooraj93@gmail.com', 'Admin Login OTP');  // Replace with your email
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your OTP for Admin Login';
        $mail->Body    = 'Your OTP is: <b>' . $otp . '</b>';

        $mail->send();
        return "An OTP has been sent to your email.";
    } catch (Exception $e) {
        error_log("PHPMailer error: " . $mail->ErrorInfo); // Log error for debugging
        return "An error occurred while sending the OTP.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <style>
        /* Styles for login form */
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f4f4f4;
        }
        .login-container {
            background-color: #fff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .login-container h2 {
            margin-bottom: 1rem;
        }
        .login-container label {
            display: block;
            margin-bottom: 0.5rem;
        }
        .login-container input {
            width: 100%;
            padding: 0.5rem;
            margin-bottom: 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .login-container button {
            width: 100%;
            padding: 0.5rem;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .login-container button:hover {
            background-color: #0056b3;
        }
        .error-message {
            color: red;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Admin Login</h2>
        <form action="" method="POST">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <button type="submit">Login</button>
        </form>

        <?php if (isset($message)): ?>
            <p class="error-message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
    </div>
</body>
</html>