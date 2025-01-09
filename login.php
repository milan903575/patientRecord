<?php
include 'connection.php'; // Include the database connection file
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['email']) && isset($_POST['password'])) {
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $password = $_POST['password'];

        $redirect_url = '';
        $message = '';

        $sql_patient = "SELECT * FROM patients WHERE email = ?";
        $stmt_patient = $conn->prepare($sql_patient);
        $stmt_patient->bind_param("s", $email);
        $stmt_patient->execute();
        $result_patient = $stmt_patient->get_result();

        if ($result_patient->num_rows > 0) {
            $patient = $result_patient->fetch_assoc();
            if (password_verify($password, $patient['password'])) {
                $_SESSION['user_id'] = $patient['id'];
                $_SESSION['user_type'] = 'patient';
                $_SESSION['profile_picture'] = $patient['profile_picture']; // Store the profile picture for comparison

                // Redirect to face verification page
                $redirect_url = "patient/verify_face.php";
                $message = "Credentials verified. Proceeding to face verification.";
            } else {
                $message = "The password entered is incorrect.";
            }
        } else {
            // Handle doctor and receptionist login logic (unchanged)
            $sql_doctor = "SELECT * FROM doctors WHERE email = ?";
            $stmt_doctor = $conn->prepare($sql_doctor);
            $stmt_doctor->bind_param("s", $email);
            $stmt_doctor->execute();
            $result_doctor = $stmt_doctor->get_result();

            if ($result_doctor->num_rows > 0) {
                $doctor = $result_doctor->fetch_assoc();
                if (password_verify($password, $doctor['password'])) {
                    if ($doctor['registration_status'] === 'pending') {
                        $message = "Your application is under review.";
                    } elseif ($doctor['registration_status'] === 'rejected') {
                        $message = "Your application has been rejected. Please contact support.";
                    } elseif ($doctor['registration_status'] === 'approved') {
                        $_SESSION['user_id'] = $doctor['id'];
                        $_SESSION['user_type'] = 'doctor';
                        $redirect_url = "doctor/doctor_profile.php";
                        $message = "Login successful! Welcome to your dashboard.";
                    }
                } else {
                    $message = "The password entered is incorrect.";
                }
            } else {
                $sql_receptionist = "SELECT * FROM receptionist WHERE email = ?";
                $stmt_receptionist = $conn->prepare($sql_receptionist);
                $stmt_receptionist->bind_param("s", $email);
                $stmt_receptionist->execute();
                $result_receptionist = $stmt_receptionist->get_result();

                if ($result_receptionist->num_rows > 0) {
                    $receptionist = $result_receptionist->fetch_assoc();
                    if (password_verify($password, $receptionist['password'])) {
                        if ($receptionist['status'] === 'pending') {
                            $message = "Your application is under review.";
                        } elseif ($receptionist['status'] === 'rejected') {
                            $message = "Your application has been rejected. Please contact support.";
                        } elseif ($receptionist['status'] === 'approved') {
                            $_SESSION['user_id'] = $receptionist['id'];
                            $_SESSION['user_type'] = 'receptionist';
                            $redirect_url = "receptionist/receptionist_dashboard.php";
                            $message = "Login successful! Welcome to your dashboard.";
                        }
                    } else {
                        $message = "The password entered is incorrect.";
                    }
                } else {
                    $message = "Email address not found. Please try again.";
                }
            }
        }

        $stmt_patient->close();
        if (isset($stmt_doctor)) $stmt_doctor->close();
        if (isset($stmt_receptionist)) $stmt_receptionist->close();

        echo "<!DOCTYPE html>
              <html lang='en'>
              <head>
                  <meta charset='UTF-8'>
                  <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                  <title>Redirecting...</title>
                  <style>
                      body {
                          font-family: 'Arial', sans-serif;
                          margin: 0;
                          padding: 0;
                          background-color: #f4f4f9;
                          display: flex;
                          justify-content: center;
                          align-items: center;
                          height: 100vh;
                          color: #333;
                      }
                      .message-container {
                          text-align: center;
                          background: #ffffff;
                          padding: 40px;
                          border-radius: 10px;
                          box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
                          width: 90%;
                          max-width: 400px;
                      }
                      .message-container h2 {
                          font-size: 22px;
                          margin-bottom: 10px;
                      }
                      .message-container p {
                          font-size: 16px;
                          margin: 10px 0;
                      }
                      .success {
                          color: #4CAF50;
                      }
                      .error {
                          color: #FF5733;
                      }
                      .countdown {
                          font-weight: bold;
                          color: #007BFF;
                          font-size: 18px;
                      }
                  </style>
              </head>
              <body>
                  <div class='message-container'>
                      <h2 class='" . (!empty($redirect_url) ? "success" : "error") . "'>" . $message . "</h2>
                      <p>Redirecting in <span class='countdown' id='countdown'>3</span> seconds...</p>
                  </div>
                  <script>
                      let countdown = 3;
                      const countdownElement = document.getElementById('countdown');
                      const interval = setInterval(() => {
                          countdown--;
                          countdownElement.innerText = countdown;
                          if (countdown === 0) {
                              clearInterval(interval);
                              " . (!empty($redirect_url) ? "window.location.href = '$redirect_url';" : "window.history.back();") . "
                          }
                      }, 1000);
                  </script>
              </body>
              </html>";
    } else {
        echo "<div style='text-align:center; margin-top:20%; font-family:Arial, sans-serif;'>
                <h2 style='color:#FF5733;'>Email and password fields are required.</h2>
                <p>You will be redirected back in <span id='countdown'>5</span> seconds...</p>
              </div>
              <script>
                let countdown = 5;
                const countdownElement = document.getElementById('countdown');
                const interval = setInterval(() => {
                    countdown--;
                    countdownElement.innerText = countdown;
                    if (countdown === 0) {
                        clearInterval(interval);
                        window.history.back();
                    }
                }, 1000);
              </script>";
    }
}
$conn->close();
?>
