<?php
include '../connection.php';

// Get the history ID from the URL
$history_id = $_GET['id'] ?? null;
// Fetch patient details
$history_query = "SELECT ph.*, p.first_name, p.last_name
                  FROM patient_history ph
                  JOIN patients p ON ph.patient_id = p.id
                  WHERE ph.id = ?";
$stmt = $conn->prepare($history_query);
$stmt->bind_param("i", $history_id);
$stmt->execute();
$history_result = $stmt->get_result();
$history = $history_result->fetch_assoc();

// Redirect if the issue is already completed
if ($history['status'] === 'completed') {
    header("Location: doctor_dashboard.php?message=This patient issue is already completed.");
    exit;
}

// Handle errors (optional)
$error = $_GET['error'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Issue and Medication Instructions</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
/* General Reset */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* Body Styling */
body {
    font-family: 'Roboto', sans-serif;
    background-color: #f4f4f9;
    color: #333;
    margin: 0;
    padding: 0;
}

/* Container */
.container {
    width: 90%;
    max-width: 900px;
    margin: 30px auto;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    padding: 20px;
}

/* Header */
header {
    background: #28a745;
    color: #fff;
    text-align: center;
    padding: 20px 0;
    border-top-left-radius: 12px;
    border-top-right-radius: 12px;
}

header h1 {
    font-size: 1.8rem;
    font-weight: 500;
}

/* Form Styling */
form {
    margin: 20px 0;
}

label {
    font-weight: bold;
    margin-bottom: 8px;
    display: block;
}

input[type="text"],
input[type="date"],
input[type="time"],
select,
textarea {
    width: 100%;
    padding: 12px 15px;
    margin-bottom: 20px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 1rem;
    background-color: #f9f9f9;
    transition: border-color 0.3s ease;
}

input[type="text"]:focus,
input[type="date"]:focus,
input[type="time"]:focus,
select:focus,
textarea:focus {
    border-color: #28a745;
    outline: none;
}

textarea {
    resize: vertical;
    height: 100px;
}

/* Buttons */
button, input[type="submit"] {
    background: #28a745;
    color: #fff;
    border: none;
    padding: 12px 20px;
    cursor: pointer;
    border-radius: 8px;
    font-size: 1rem;
    margin: 10px 0;
    transition: background-color 0.3s ease, transform 0.2s ease;
}

button:hover, input[type="submit"]:hover {
    background: #218838;
    transform: translateY(-2px);
}

button:active, input[type="submit"]:active {
    transform: translateY(0);
}

/* Responsive Design */
@media (max-width: 768px) {
    .container {
        padding: 15px;
    }

    input[type="text"],
    input[type="date"],
    input[type="time"],
    select,
    textarea {
        font-size: 0.9rem;
    }

    button, input[type="submit"] {
        font-size: 0.9rem;
    }
}

/* Medication Component */
.medication-container {
    margin-top: 30px;
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 12px;
    padding: 20px;
}

.medication-entry {
    margin-bottom: 20px;
    border: 1px solid #ddd;
    border-radius: 12px;
    padding: 20px;
    background: #fff;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.medication-entry h3 {
    font-size: 1.2rem;
    color: #555;
    margin-bottom: 15px;
    border-bottom: 2px solid #28a745;
    padding-bottom: 5px;
}

.medication-entry label {
    margin-top: 10px;
    font-size: 0.95rem;
}

/* Row alignment for inputs */
.medication-row {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 20px;
}

.medication-row > div {
    flex: 1 1 calc(25% - 15px); /* Four columns per row */
}

.medication-row > div label {
    font-size: 0.9rem;
    margin-bottom: 5px;
    display: block;
}

.medication-row > div input {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 8px;
    background: #f9f9f9;
}

.date-row {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
}

.date-row > div {
    flex: 1;
}

/* "Add Medication" Button */
#add-medication-button {
    background: #007bff;
    border: none;
    color: #fff;
    padding: 12px 20px;
    border-radius: 8px;
    font-size: 1rem;
    margin-top: 15px;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.2s ease;
}

#add-medication-button:hover {
    background: #0056b3;
    transform: translateY(-2px);
}

#add-medication-button:active {
    transform: translateY(0);
}

.history-button {
    display: inline-block;
    background-color: #007bff;
    color: #fff;
    padding: 12px 20px;
    border-radius: 8px;
    font-size: 1rem;
    text-decoration: none;
    transition: background-color 0.3s ease, transform 0.2s ease;
}

.history-button:hover {
    background-color: #0056b3;
    transform: translateY(-2px);
}

.history-button:active {
    transform: translateY(0);
}
.hidden {
    display: none;
}



</style>
</head>
<body>
<div class="container">
    <header>
        <h1>Provide Solution for <?= htmlspecialchars($history['first_name'] . ' ' . $history['last_name']); ?></h1>
    </header>

    <form action="process_problem.php" method="POST">
        <input type="hidden" name="form_action" value="solution_form">

        <!-- Problem Section -->
        <label for="problem" class="essential">Problem:</label>
        <input type="text" id="problem" class="essential" value="<?= htmlspecialchars($history['problem']); ?>" disabled>

        <label for="problem_description" class="essential">Description:</label>
        <textarea id="problem_description" class="essential" disabled><?= htmlspecialchars($history['problem_description']); ?></textarea>

        <label for="current_medication" class="essential">Current Medication:</label>
        <textarea id="current_medication" class="essential" disabled><?= htmlspecialchars($history['current_medication']); ?></textarea>

        <div class="video-container essential">
            <?php if (!empty($history['video_file'])): ?>
                <?php 
                $video_base64 = base64_encode($history['video_file']);
                $video_path = "data:video/mp4;base64,$video_base64";
                ?>
                <video controls style="width: 100%; max-width: 600px; margin: 20px auto; display: block;">
                    <source src="<?= $video_path ?>" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            <?php else: ?>
                <p><center>No video available.</center></p><br/>
            <?php endif; ?>
        </div>

        <div style="text-align: center; margin-top: 20px;">
            <a href="patient_list.php?id=<?= urlencode($history['id']); ?>" class="history-button">
                History Record of <?= htmlspecialchars($history['first_name']); ?>
            </a>
        </div>

        <!-- Solution Section -->
        <div class="form-field essential">
            <label for="doctor_solution">Doctor Solution:</label>
            <textarea id="doctor_solution" name="doctor_solution" required></textarea>
        </div>

        <div class="form-field essential">
            <label for="treatment_type">Treatment Type:</label>
            <select id="treatment_type" name="treatment_type" required>
                <option value="remote">Remote</option>
                <option value="in_person">In-Person</option>
            </select>
        </div>

        <div id="appointment_date" class="hidden essencial">
            <label for="appointment_date">Appointment Date:</label>
            <input type="date" id="appointment_date" name="appointment_date">
        </div>

        <!-- Medication Section -->
        <div class="medication-container">


            <div class="medication-entry">
  
                <h3>Medication #1</h3>

                <div class="medication-row">
                  <div class="form-field">
                        <label for="medication_name_1">Medication Name:</label>
                        <input type="text" id="medication_name_1" name="medication_name_1">
                    </div>
                    <div class="form-field">
                        <label for="medication_type_1">Medication Type:</label>
                        <select id="medication_type_1" name="medication_type_1">
                            <option value="tablet">Tablet</option>
                            <option value="syrup">Syrup</option>
                            <option value="eye_drop">Eye Drop</option>
                            <option value="cream">Cream</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label for="dosage_quantity_1">Dosage & Quantity:</label>
                        <input type="text" id="dosage_quantity_1" name="dosage_quantity_1">
                    </div>
                </div>

                <div class="medication-row">
                    <div class="form-field">
                        <label for="morning_time_1">Morning Time:</label>
                        <input type="time" id="morning_time_1" name="morning_time_1">
                    </div>
                    <div class="form-field">
                        <label for="afternoon_time_1">Afternoon Time:</label>
                        <input type="time" id="afternoon_time_1" name="afternoon_time_1">
                    </div>
                    <div class="form-field">
                        <label for="evening_time_1">Evening Time:</label>
                        <input type="time" id="evening_time_1" name="evening_time_1">
                    </div>
                    <div class="form-field">
                        <label for="night_time_1">Night Time:</label>
                        <input type="time" id="night_time_1" name="night_time_1">
                    </div>
                </div>

                <div class="date-row">
                    <div class="form-field">
                        <label for="start_date_1">Start Date:</label>
                        <input type="date" id="start_date_1" name="start_date_1">
                    </div>
                    <div class="form-field">
                        <label for="end_date_1">End Date:</label>
                        <input type="date" id="end_date_1" name="end_date_1">
                    </div>
                </div>

                <div class="form-field">
                    <label for="additional_instructions_1">Additional Instructions:</label>
                    <textarea id="additional_instructions_1" name="additional_instructions_1"></textarea>
                </div>
            </div>
        </div>

        <div id="medication-entries"></div>
        <input type="hidden" id="medication_count" name="medication_count" value="1">
        <button type="button" onclick="addMedication()">Add Medication</button>

        <input type="hidden" name="history_id" value="<?= htmlspecialchars($history_id); ?>">
        <input type="submit" value="Submit Solution">
    </form>
</div>

<script>
let medicationCount = 1;

// Function to add a medication entry dynamically
function addMedication() {
    medicationCount++;
    const medicationContainer = document.getElementById('medication-entries');
    const medicationEntry = document.createElement('div');
    medicationEntry.className = 'medication-entry';
    medicationEntry.innerHTML = `
        <h3>Medication #${medicationCount}</h3>
        <label for="medication_name_${medicationCount}">Medication Name:</label>
        <input type="text" id="medication_name_${medicationCount}" name="medication_name_${medicationCount}">

        <label for="medication_type_${medicationCount}">Medication Type:</label>
        <select id="medication_type_${medicationCount}" name="medication_type_${medicationCount}">
            <option value="tablet">Tablet</option>
            <option value="syrup">Syrup</option>
            <option value="eye_drop">Eye Drop</option>
            <option value="cream">Cream</option>
            <option value="other">Other</option>
        </select>

        <label for="dosage_quantity_${medicationCount}">Dosage & Quantity:</label>
        <input type="text" id="dosage_quantity_${medicationCount}" name="dosage_quantity_${medicationCount}">

        <div class="medication-row">
            <div>
                <label for="morning_time_${medicationCount}">Morning Time:</label>
                <input type="time" id="morning_time_${medicationCount}" name="morning_time_${medicationCount}">
            </div>
            <div>
                <label for="afternoon_time_${medicationCount}">Afternoon Time:</label>
                <input type="time" id="afternoon_time_${medicationCount}" name="afternoon_time_${medicationCount}">
            </div>
            <div>
                <label for="evening_time_${medicationCount}">Evening Time:</label>
                <input type="time" id="evening_time_${medicationCount}" name="evening_time_${medicationCount}">
            </div>
            <div>
                <label for="night_time_${medicationCount}">Night Time:</label>
                <input type="time" id="night_time_${medicationCount}" name="night_time_${medicationCount}">
            </div>
        </div>

        <div class="date-row">
            <div>
                <label for="start_date_${medicationCount}">Start Date:</label>
                <input type="date" id="start_date_${medicationCount}" name="start_date_${medicationCount}">
            </div>
            <div>
                <label for="end_date_${medicationCount}">End Date:</label>
                <input type="date" id="end_date_${medicationCount}" name="end_date_${medicationCount}">
            </div>
        </div>

        <label for="additional_instructions_${medicationCount}">Additional Instructions:</label>
        <textarea id="additional_instructions_${medicationCount}" name="additional_instructions_${medicationCount}"></textarea>
    `;
    medicationContainer.appendChild(medicationEntry);
    document.getElementById('medication_count').value = medicationCount; // Update the hidden field with the new count
}

// Add event listener to the treatment type dropdown
document.getElementById('treatment_type').addEventListener('change', function () {
    const treatmentType = this.value; // Get selected value
    const appointmentDateDiv = document.getElementById('appointment_date');
    const allFields = document.querySelectorAll('.form-field'); // Select all toggleable fields
    const essentialFields = document.querySelectorAll('.essential'); // Select essential fields

    if (treatmentType === 'in_person') {
        appointmentDateDiv.classList.remove('hidden'); // Show appointment date
        allFields.forEach(field => field.classList.add('hidden')); // Hide all fields
        essentialFields.forEach(field => field.classList.remove('hidden')); // Show essential fields
    } else {
        appointmentDateDiv.classList.add('hidden'); // Hide appointment date
        allFields.forEach(field => field.classList.remove('hidden')); // Show all fields
    }
});

// Initialize visibility on page load
window.onload = function () {
    const treatmentType = document.getElementById('treatment_type').value;
    const appointmentDateDiv = document.getElementById('appointment_date');
    const allFields = document.querySelectorAll('.form-field');
    const essentialFields = document.querySelectorAll('.essential');

    if (treatmentType === 'in_person') {
        appointmentDateDiv.classList.remove('hidden'); // Show appointment date
        allFields.forEach(field => field.classList.add('hidden')); // Hide all fields
        essentialFields.forEach(field => field.classList.remove('hidden')); // Show essential fields
    } else {
        appointmentDateDiv.classList.add('hidden'); // Hide appointment date
        allFields.forEach(field => field.classList.remove('hidden')); // Show all fields
    }
};
</script>





</body>
</html>