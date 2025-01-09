<?php
include '../connection.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'patient') {
    header("Location: login.php"); // Redirect to login if not authenticated
    exit;
}

// Fetch the profile picture path of the logged-in patient
$userId = $_SESSION['user_id'];
$sql = "SELECT profile_picture FROM patients WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $profilePicturePath = $row['profile_picture'];

    if (!empty($profilePicturePath)) {
        $profilePictureUrl = $profilePicturePath;
    } else {
        $profilePictureUrl = "path/to/default/profile_picture.png";
    }
} else {
    $profilePictureUrl = "path/to/default/profile_picture.png";
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Face Verification</title>
  <script defer src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
  <style>
/* General Body Styles */
body {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  height: 100vh;
  margin: 0;
  font-family: 'Arial', sans-serif;
  background: linear-gradient(to bottom, #87ceeb, #b3e5fc); /* Sky blue gradient */
  color: #333;
  padding: 20px;
  box-sizing: border-box;
}

/* Video Container Styles */
#video-container {
  display: none;
  text-align: center;
  margin-top: 20px;
}

/* Responsive Video */
video {
  border: 5px solid #fff;
  border-radius: 15px;
  width: 100%;
  max-width: 400px;
  height: auto;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
}

/* Timer Styling */
#timer {
  font-size: 1.5em;
  margin-top: 10px;
  color: #333;
  font-weight: bold;
}

/* Buttons and Links */
button, a {
  margin-top: 20px;
  padding: 10px 20px;
  font-size: 16px;
  background-color: #ff5722;
  color: #fff;
  border: none;
  border-radius: 5px;
  text-decoration: none;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

button:hover, a:hover {
  background-color: #e64a19;
}

.hidden {
  display: none;
}

/* Headings and Text */
h2 {
  margin: 0 0 10px;
  font-size: 24px;
  text-align: center;
  color: #004d75;
}

p {
  font-size: 14px;
  text-align: center;
  margin-bottom: 15px;
  color: #004d75;
}

/* Responsive Design */
@media screen and (max-width: 768px) {
  h2 {
    font-size: 20px;
  }
  
  button, a {
    font-size: 14px;
    padding: 8px 16px;
  }

  #timer {
    font-size: 1.2em;
  }
}

  </style>
</head>
<body>
  <div id="button-container">
    <h2>Face Verification</h2>
    <p>Please ensure your face is fully visible and in good lighting conditions.</p>
    <center><button id="start-verification">Start Face Verification</button></center>
  </div>
  <div id="video-container">
    <h2>Face Verification in Progress...</h2>
    <video id="video" autoplay muted></video>
    <div id="timer-container">
      <p id="timer">30</p>
    </div>
    <div id="retry-options" class="hidden">
      <p>Sorry, face not detected. Please try again or verify via OTP.</p>
      <button id="retry">Try Again</button>
      <a href="login_otp_verification.php">Get OTP via Email</a>
    </div>
  </div>

  <script>
    const startVerificationButton = document.getElementById("start-verification");
    const videoContainer = document.getElementById("video-container");
    const video = document.getElementById("video");
    const timerElement = document.getElementById("timer");
    const retryOptions = document.getElementById("retry-options");
    const retryButton = document.getElementById("retry");

    const profilePictureUrl = "<?php echo $profilePictureUrl ? $profilePictureUrl : ''; ?>";

    const startFaceVerification = async () => {
      retryOptions.classList.add("hidden");
      timerElement.textContent = "30";

      if (!profilePictureUrl) {
        alert("No profile picture found. Please upload your profile picture.");
        return;
      }

      if (typeof faceapi === 'undefined') {
        alert("face-api.js is not loaded. Please check your script inclusion.");
        return;
      }

      try {
        await faceapi.nets.tinyFaceDetector.loadFromUri('./face_models');
        await faceapi.nets.faceRecognitionNet.loadFromUri('./face_models');
        await faceapi.nets.ssdMobilenetv1.loadFromUri('./face_models');
        await faceapi.nets.faceLandmark68Net.loadFromUri('./face_models');
      } catch (error) {
        alert("Error loading face detection models. Please check your setup.");
        return;
      }

      try {
        const stream = await navigator.mediaDevices.getUserMedia({ video: true });
        video.srcObject = stream;
        videoContainer.style.display = "block";
        document.getElementById("button-container").style.display = "none";

        const storedImage = await faceapi.fetchImage(profilePictureUrl);
        let countdown = 30;

        const timer = setInterval(() => {
          countdown -= 1;
          timerElement.textContent = countdown;

          if (countdown <= 0) {
            clearInterval(timer);
            stream.getTracks().forEach((track) => track.stop());
            retryOptions.classList.remove("hidden");
          }
        }, 1000);

        video.addEventListener("play", async () => {
          const canvas = faceapi.createCanvasFromMedia(video);
          document.body.append(canvas);

          const displaySize = { width: video.width, height: video.height };
          faceapi.matchDimensions(canvas, displaySize);

          const interval = setInterval(async () => {
            try {
              const detections = await faceapi
                .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
                .withFaceLandmarks()
                .withFaceDescriptor();

              if (detections) {
                const storedFaceDescriptor = await faceapi.computeFaceDescriptor(storedImage);
                const faceMatcher = new faceapi.FaceMatcher([storedFaceDescriptor]);
                const bestMatch = faceMatcher.findBestMatch(detections.descriptor);

                if (bestMatch.distance < 0.65) {
                  clearInterval(timer);
                  clearInterval(interval);
                  stream.getTracks().forEach((track) => track.stop());
                  window.location.href = "patient_homepage.php";
                }
              }
            } catch (error) {
              console.error("Error during face detection and comparison:", error);
            }
          }, 1000);
        });
      } catch (error) {
        alert("Unable to access the camera. Please allow camera access.");
      }
    };

    startVerificationButton.addEventListener("click", startFaceVerification);
    retryButton.addEventListener("click", startFaceVerification);
  </script>
</body>
</html>
