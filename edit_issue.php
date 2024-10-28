<?php
include 'connection.php';

// Get the history ID from the URL
$history_id = $_GET['id'];

// Fetch patient details and current issue based on the history ID
$history_query = "SELECT ph.*, p.first_name, p.last_name
                  FROM patient_history ph
                  JOIN patients p ON ph.patient_id = p.id
                  WHERE ph.id = ?";
$stmt = $conn->prepare($history_query);
$stmt->bind_param("i", $history_id);
$stmt->execute();
$history_result = $stmt->get_result();
$history = $history_result->fetch_assoc();

// Update the patient's issue with the solution
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $doctor_solution = $_POST['doctor_solution'];
    $treatment_type = $_POST['treatment_type'];
    $appointment_date = !empty($_POST['appointment_date']) ? $_POST['appointment_date'] : null;

    $update_query = "UPDATE patient_history
                     SET doctor_solution = ?, treatment_type = ?, appointment_date = ?, status = 'completed', date_completed = NOW()
                     WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("sssi", $doctor_solution, $treatment_type, $appointment_date, $history_id);
    $stmt->execute();

    header("Location: doctor_dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Issue</title>
    <style>
 /* General Styles */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 0;
}

.container {
    width: 90%;
    max-width: 1200px;
    margin: auto;
    overflow: hidden;
}

header {
    background: #333;
    color: #fff;
    padding: 10px 0;
    text-align: center;
}

header h1 {
    margin: 0;
}

/* Form Styles */
form {
    background: #fff;
    padding: 20px;
    margin: 20px 0;
    border-radius: 5px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

form label {
    display: block;
    margin-bottom: 8px;
}

form input[type="text"],
form input[type="email"],
form input[type="password"],
form input[type="date"],
form select {
    width: 100%;
    padding: 10px;
    margin-bottom: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
}

form input[type="submit"] {
    background: #5cb85c;
    color: #fff;
    border: 0;
    padding: 10px 20px;
    cursor: pointer;
    border-radius: 5px;
}

form input[type="submit"]:hover {
    background: #4cae4c;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    font-weight: bold;
}

/* Error and Success Messages */
.error, .success {
    padding: 10px;
    margin-bottom: 20px;
    border-radius: 5px;
}

.error {
    background: #f2dede;
    color: #a94442;
}

.success {
    background: #dff0d8;
    color: #3c763d;
}

/* Table Styles */
table {
    width: 100%;
    margin: 20px 0;
    border-collapse: collapse;
}

table th, table td {
    padding: 10px;
    border: 1px solid #ddd;
    text-align: left;
}

table th {
    background: #333;
    color: #fff;
}

table tr:nth-child(even) {
    background: #f2f2f2;
}

/* Profile Styles */
.profile-header {
    background: #fff;
    padding: 20px;
    margin: 20px 0;
    border-radius: 5px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

.profile-header h2, .profile-header h3 {
    margin: 0;
    color: #333;
}

.profile-details {
    list-style: none;
    padding: 0;
}

.profile-details li {
    padding: 5px 0;
}

.profile-details li strong {
    display: inline-block;
    width: 150px;
}

/* Dashboard Styles */
.dashboard {
    background: #fff;
    padding: 20px;
    margin: 20px 0;
    border-radius: 5px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

.dashboard h2 {
    margin-top: 0;
}

/* Button Styles */
.button {
    background: #007bff;
    color: #fff;
    padding: 10px 15px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
}

.button:hover {
    background: #0056b3;
}

button {
    background: #007bff;
    color: #fff;
    padding: 10px 15px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

button:hover {
    background: #0056b3;
}
    </style>
    <script>
        function toggleTreatmentFields() {
            const treatmentType = document.getElementById('treatment_type').value;
            const remoteFields = document.getElementById('remote-fields');
            const inPersonFields = document.getElementById('in-person-fields');

            if (treatmentType === 'remote') {
                remoteFields.style.display = 'block';
                inPersonFields.style.display = 'none';
            } else {
                remoteFields.style.display = 'none';
                inPersonFields.style.display = 'block';
            }
        }

        async function saveAsPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            const formContent = document.getElementById('form-content');

            doc.html(formContent, {
                callback: async function (doc) {
                    const pdfBlob = doc.output('blob');
                    const formData = new FormData();
                    formData.append('pdf', pdfBlob, 'medical_receipt.pdf');

                    try {
                        const response = await fetch('save_pdf.php', {
                            method: 'POST',
                            body: formData
                        });

                        if (response.ok) {
                            alert('PDF saved successfully.');
                        } else {
                            console.error('Failed to save PDF');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                    }
                },
                x: 10,
                y: 10
            });
        }

        function openMedForm() {
            const historyId = "<?php echo $history_id; ?>";
            window.location.href = `med.html?history_id=${historyId}`;
        }

        document.addEventListener('DOMContentLoaded', function() {
            toggleTreatmentFields();
        });
    </script>
</head>
<body>
    <div class="container">
        <header>
            <h1>Provide Solution for <?php echo htmlspecialchars($history['first_name'] . ' ' . $history['last_name']); ?></h1>
        </header>

        <form action="" method="POST" id="form-content">
            <label for="problem">Problem:</label>
            <input type="text" id="problem" name="problem" value="<?php echo htmlspecialchars($history['problem']); ?>" disabled><br>

            <label for="current_medication">Current Medication:</label>
            <textarea id="current_medication" name="current_medication" disabled><?php echo htmlspecialchars($history['current_medication']); ?></textarea><br>

            <label for="treatment_type">Treatment Type:</label>
            <select id="treatment_type" name="treatment_type" required onchange="toggleTreatmentFields()">
                <option value="remote">Remote</option>
                <option value="in_person">In-Person</option>
            </select><br>

            <div id="remote-fields" style="display: none;">
                <label for="doctor_solution">Solution:</label>
                <textarea id="doctor_solution" name="doctor_solution"></textarea><br>
            </div>

            <div id="in-person-fields" style="display: none;">
                <label for="appointment_date">Appointment Date (for in-person only):</label>
                <input type="date" id="appointment_date" name="appointment_date"><br>
            </div>

            <!-- Display video if available -->
            <?php if ($history['video_path']): ?>
                <label for="video">Video:</label><br>
                <video controls width="300">
                    <source src="<?php echo htmlspecialchars($history['video_path']); ?>" type="video/mp4">
                    Your browser does not support the video tag.
                </video><br>
            <?php endif; ?>

            <input type="submit" value="Submit Solution">
            <button type="button" onclick="saveAsPDF()">Save as PDF</button>

            <div class="form-group">
                <label for="list_medications">Do you want to list the medications?</label>
                <input type="checkbox" id="list_medications" name="list_medications" onclick="openMedForm()">
            </div>
        </form>
    </div>

    <!-- jsPDF library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.3.1/jspdf.umd.min.js"></script>
</body>
</html>
