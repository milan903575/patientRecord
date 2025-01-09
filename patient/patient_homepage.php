<?php
include '../connection.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'patient') {
    header("Location: ../login.html");
    exit;
}

$patient_id = $_SESSION['user_id'];

// Fetch patient information
$sql_patient = "SELECT first_name, last_name, date_of_birth FROM patients WHERE id = ?";
$stmt_patient = $conn->prepare($sql_patient);
$stmt_patient->bind_param("i", $patient_id);
$stmt_patient->execute();
$result_patient = $stmt_patient->get_result();
$patient = $result_patient->fetch_assoc();
$stmt_patient->close();

// Calculate age if date of birth is available
if (isset($patient['date_of_birth'])) {
    $dob_date = new DateTime($patient['date_of_birth']);
    $now = new DateTime();
    $age = $now->diff($dob_date)->y;
} else {
    $age = 'N/A';
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dynamic Landing Page</title>
    <style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
}

body {
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    align-items: center;
    min-height: 100vh;
    background: #111;
    color: #fff;
    overflow: hidden;
}

header {
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 40px;
    position: fixed;
    top: 0;
    z-index: 1000;
    background: rgba(0, 0, 0, 0.8);
}

.logo {
    font-size: 24px;
    font-weight: 600;
    color: #fff;
}

nav ul {
    list-style: none;
    display: flex;
    gap: 20px;
    margin-right: 100px;
}

nav ul li a {
    color: #fff;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.3s;
}

nav ul li a:hover {
    color: #03a9f4;
}

.logout-button {
    position: absolute;
    right: 40px;
    top: 50%;
    transform: translateY(-50%);
    background: #dc3545;
    color: #fff;
    padding: 10px 15px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    text-decoration: none;
}

.logout-button:hover {
    background: #c82333;
}

.content {
    text-align: center;
    z-index: 10;
    margin-top: 100px;
    padding: 20px;
}

.content h2 {
    font-size: 48px;
    margin-bottom: 10px;
    text-transform: uppercase;
    font-weight: 700;
}

.content h3 {
    font-size: 32px;
    font-weight: 500;
    margin-bottom: 20px;
}

.content p {
    font-size: 18px;
    max-width: 600px;
    margin: 0 auto 20px auto;
    line-height: 1.6;
}

.buttons-container {
    display: flex;
    gap: 20px;
    justify-content: center;
    flex-wrap: wrap;
}

.submit-button {
    font-size: 18px;
    background: #03a9f4;
    padding: 10px 30px;
    color: #111;
    text-transform: uppercase;
    text-decoration: none;
    font-weight: 600;
    border-radius: 5px;
    transition: background 0.3s ease;
}

.submit-button:hover {
    background: #0288d1;
}

.submit-private-button {
    background: #4caf50;
    color: #fff;
}

.submit-private-button:hover {
    background: #45a049;
}

.how-it-works {
    font-size: 14px;
    color: #03a9f4;
    cursor: pointer;
    margin-top: 5px;
    transition: color 0.3s;
}

.how-it-works:hover {
    color: #0288d1;
}

.hidden-info {
    display: none;
    margin-top: 20px;
    font-size: 16px;
    line-height: 1.6;
}

video {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    opacity: 0.75;
}

.overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 5;
}

footer {
    position: fixed;
    bottom: 0;
    left: 0;
    width: 100%;
    text-align: center;
    padding: 10px;
    background: transparent;
    color: #fff;
    font-size: 14px;
}

.chat-bot-box {
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 300px;
    height: 400px;
    background: #333;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    border-radius: 10px;
    display: flex;
    flex-direction: column;
    z-index: 10;
    overflow: hidden;
    transition: transform 0.3s ease, opacity 0.3s ease;
}

.chat-bot-box.minimized {
    height: 50px;
    transform: translateY(350px);
    opacity: 0.8;
}

.chat-bot-header {
    background: #03a9f4;
    color: #fff;
    padding: 10px;
    font-weight: bold;
    cursor: pointer;
    text-align: center;
}

.chat-bot-content {
    flex: 1;
    padding: 10px;
    text-align: left;
    overflow-y: auto;
    color: #fff;
    font-size: 14px;
}

.chat-bot-input {
    display: flex;
    padding: 10px;
    background: #444;
}

.chat-bot-input input {
    flex: 1;
    padding: 10px;
    border: none;
    border-radius: 5px;
    margin-right: 5px;
    font-size: 14px;
    color: #333;
}

.chat-bot-input button {
    background: #03a9f4;
    color: #fff;
    border: none;
    padding: 10px 15px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
}

.chat-bot-input button:hover {
    background: #0288d1;
}

.chat-bot-content div {
    margin-bottom: 10px;
}

.chat-bot-content .user-message {
    color: #03a9f4;
}

.chat-bot-content .bot-message {
    color: #fff;
}

.chat-bot-content .error-message {
    color: #f00;
}

/* Responsive Design */
@media (max-width: 768px) {
    /* Header and navigation adjustments */
    header {
        flex-direction: column;
        align-items: flex-start;
        padding: 10px 20px;
    }

    nav ul {
        flex-direction: column;
        gap: 10px;
        margin-right: 0;
    }

    .logout-button {
        position: static;
        margin-top: 10px;
    }

    /* Content adjustments */
    .content h2 {
        font-size: 32px;
    }

    .content h3 {
        font-size: 24px;
    }

    .content p {
        font-size: 16px;
        padding: 0 10px;
    }

    /* Buttons-container moved to the bottom-left */
    .buttons-container {
        position: fixed; /* Fix the position to the viewport */
        bottom: 10px; /* Place it at the bottom */
        left: 10px; /* Align to the left */
        flex-direction: column; /* Stack buttons vertically */
        gap: 10px; /* Space between buttons */
        z-index: 1000; /* Ensure it stays on top */
        background: rgba(0, 0, 0, 0.8); /* Optional background for visibility */
        padding: 10px; /* Optional padding for spacing */
        border-radius: 5px; /* Rounded corners */
    }

    footer {
        font-size: 12px;
    }

    /* Chatbot adjustments for mobile */
    .chat-bot-box {
        width: 200px;
        height: 300px;
        right: 10px;
        bottom: 10px;
    }

    .chat-bot-box.minimized {
        height: 40px;
        transform: translateY(260px);
    }

    .chat-bot-header {
        font-size: 14px;
        padding: 8px;
    }

    .chat-bot-content {
        font-size: 12px;
        padding: 8px;
    }

    .chat-bot-input input {
        font-size: 12px;
        padding: 5px;
    }

    .chat-bot-input button {
        font-size: 12px;
        padding: 8px 10px;
    }
}





    </style>
    <script>
        function toggleInfo(id) {
            const info = document.getElementById(id);
            info.style.display = info.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</head>
<body>
    <header>
        <div class="logo">
            <?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?>
        </div>
        <nav>
            <ul>
                <li><a href="patient_profile.php">Profile</a></li>
                <li><a href="patient_history.php">History</a></li>
                <li><a href="hospital_list.php">Hospital List</a></li>
                <li><a href="FAQ.php">FAQ</a></li>
                <li><a href="../contactus.php">Contact Us</a></li>
            </ul>
        </nav>
        <a href="../logout.php" class="logout-button">Logout</a>
    </header>
    
    <video src="../uploads/videos/bgv.mp4" muted loop autoplay></video>
    <div class="overlay"></div>

    <div class="content">
        <h2>Welcome to Healthcare Safety</h2>
        <h3>Your Gateway to a Healthier Tomorrow</h3>
        <p>Empowering you with solutions for better health and safety. Explore personalized healthcare guidance and ensure your well-being with our dedicated services.</p>
        <div class="buttons-container">
            <div>
                <a href="detailed_problem.php" class="submit-button">Submit Public Problem</a>
                <div class="how-it-works" onclick="toggleInfo('problem-info')">How It Works</div>
            </div>
            <div>
                <a href="PrivatePatientProblems/submit_problem.php" class="submit-private-button submit-button">Submit Private Problem</a>
                <div class="how-it-works" onclick="toggleInfo('private-problem-info')">How It Works</div>
            </div>
        </div>
        <div class="hidden-info" id="problem-info">
            Submitting a public problem allows every doctor to view in.
        </div>
        <div class="hidden-info" id="private-problem-info">
            Submitting a private problem ensures that your details remain confidential.
        </div>
    </div>

    <footer>
        <p>© 2024 Healthcare Safety. All rights reserved.</p>
    </footer>
<div class="chat-bot-box">
    <div class="chat-bot-header">Chat Bot</div>
    <div class="chat-bot-content" id="chat-content">
        <!-- Chat messages will appear here -->
    </div>
    <div class="chat-bot-input">
        <input type="text" id="chat-input" placeholder="Type your message here..." />
        <button onclick="sendMessage()">Send</button>
    </div>
</div>

<script>
// Chatbot Toggle Minimize/Maximize
document.querySelector('.chat-bot-header').addEventListener('click', () => {
    const chatBox = document.querySelector('.chat-bot-box');
    chatBox.classList.toggle('minimized');
});

// Chatbot Functionality
function sendMessage() {
    const input = document.getElementById("chat-input");
    const chatContent = document.getElementById("chat-content");
    const userMessage = input.value.trim();

    if (userMessage) {
        // Append user message
        appendMessage(chatContent, `You: ${userMessage}`, "user-message");

        // Clear input field
        input.value = "";

        // Simulate bot response or call backend
        setTimeout(() => {
            // Fetch bot response from massage.php
            fetchBotResponse(userMessage).then(botResponse => {
                appendMessage(chatContent, `Bot: ${botResponse}`, "bot-message");
            });
        }, 500);
    }
}

// Append messages to chat content
function appendMessage(container, message, className) {
    const messageDiv = document.createElement("div");
    messageDiv.textContent = message;
    messageDiv.className = className;
    container.appendChild(messageDiv);

    // Scroll to the latest message
    container.scrollTop = container.scrollHeight;
}

// Fetch bot response from massage.php
async function fetchBotResponse(userMessage) {
    const response = await fetch('chatbotmassage.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ message: userMessage })
    });

    const data = await response.json();
    return data.response;
}

// Initialize the chat bot with a default message
window.onload = () => {
    const chatContent = document.getElementById("chat-content");
    appendMessage(chatContent, "Bot: I'm here to assist you today!", "bot-message");
};

// Enable sending message on Enter key press
document.getElementById("chat-input").addEventListener("keydown", (event) => {
    if (event.key === "Enter") {
        sendMessage();
    }
});

</script>


</body>
</html>
