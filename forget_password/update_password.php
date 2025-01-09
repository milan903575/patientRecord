<?php
require '../connection.php'; // Include database connection

session_start();

// Ensure OTP verification is complete
if (!isset($_SESSION['email'])) {
    header("Location: ../login.html"); // Redirect to the start if no session
    exit();
}

$message = "";
$countdown_message = ""; // For countdown message after form submission

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['new_password']) && isset($_POST['confirm_password'])) {
        $new_password = mysqli_real_escape_string($conn, $_POST['new_password']);
        $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);

        // Check if passwords match
        if ($new_password === $confirm_password) {
            $email = $_SESSION['email'];

            // Hash the password for security
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Update password in patients table
            $update_patients_query = "UPDATE patients SET password = ? WHERE email = ?";
            $stmt1 = $conn->prepare($update_patients_query);
            $stmt1->bind_param("ss", $hashed_password, $email);
            $patients_updated = $stmt1->execute();

            // Update password in doctors table
            $update_doctors_query = "UPDATE doctors SET password = ? WHERE email = ?";
            $stmt2 = $conn->prepare($update_doctors_query);
            $stmt2->bind_param("ss", $hashed_password, $email);
            $doctors_updated = $stmt2->execute();

            // Check if the updates succeeded
            if ($patients_updated || $doctors_updated) {
                $message = "Password updated successfully.";

                // Show countdown message after successful update
                $countdown_message = "<script>
                    let seconds = 3;
                    const countdownEl = document.getElementById('countdown');
                    const interval = setInterval(() => {
                        if (seconds > 0) {
                            countdownEl.innerText = 'Redirecting in ' + seconds + ' seconds...';
                            seconds--;
                        } else {
                            clearInterval(interval);
                            window.location.href = '../login.html'; // Redirect after countdown
                        }
                    }, 1000);
                </script>";
            } else {
                $message = "Failed to update password. Please try again.";
            }
        } else {
            $message = "Passwords do not match.";
        }
    } else {
        $message = "All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
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
            font-size: 14px;
            color: red;
        }
        .countdown {
            font-size: 14px;
            color: green;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Reset Password</h2>

        <!-- Form for submitting the new password -->
        <form method="POST">
            <input type="password" name="new_password" placeholder="New Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <button type="submit">Update Password</button>
        </form>

        <!-- Message that appears after form submission -->
        <?php if ($message): ?>
            <p class="message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <!-- Countdown message that appears after the update -->
        <p id="countdown" class="countdown">
            <?php echo $countdown_message ? "Redirecting in 3 seconds..." : ""; ?>
        </p>

        <!-- Inject the countdown script after form submission -->
        <?php echo $countdown_message; ?>
    </div>
</body>
</html>
