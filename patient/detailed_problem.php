<?php
include '../connection.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'patient') {
    header("Location: ../login.html");
    exit;
}

$patient_id = $_SESSION['user_id'];

// Hospital search logic (AJAX handled in the same file)
if (isset($_GET['query'])) {
    $query = mysqli_real_escape_string($conn, $_GET['query']);
    $sql = "SELECT id, hospital_name, registration_fee FROM hospitals WHERE hospital_name LIKE ? OR zipcode LIKE ?";
    $stmt = $conn->prepare($sql);
    $search_query = "%" . $query . "%";
    $stmt->bind_param("ss", $search_query, $search_query);
    $stmt->execute();
    $result = $stmt->get_result();

    $output = '';
    while ($row = $result->fetch_assoc()) {
        $output .= '<div class="hospital-option" data-id="' . $row['id'] . '" data-fee="' . $row['registration_fee'] . '">' . htmlspecialchars($row['hospital_name']) . '</div>';
    }

    echo $output;
    $stmt->close();
    $conn->close();
    exit;
}

// Check registration status
if (isset($_POST['hospital_id'])) {
    $hospital_id = $_POST['hospital_id'];
    $sql = "SELECT ph.registration_status, h.registration_fee
            FROM patient_hospital ph
            LEFT JOIN hospitals h ON ph.hospital_id = h.id
            WHERE ph.hospital_id = ? AND ph.patient_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $hospital_id, $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $response = '';

    if ($row = $result->fetch_assoc()) {
        if ($row['registration_status'] == 'Pending') {
            $response = "This hospital requires a registration fee of " . $row['registration_fee'] . ". Please complete the registration fee in the hospital list in your profile.";
        } else {
            $response = "Registration is completed. You can submit the form.";
        }
    } else {
        $response = "<p style='color: red; font-weight: bold;'>You have not registered to this hospital, please register in the login page.</p>";
    }

    echo $response;
    $stmt->close();
    exit;
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Submit Problem</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
<style>
    /* General body styles */
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
    }

    /* Container styling */
    .container {
        max-width: 800px;
        margin: auto;
        padding: 20px;
    }

    /* Form and well container styling */
    .well {
        background: #fff;
        padding: 20px;
        border-radius: 5px;
    }

    /* Form input, textarea, and select styles */
    form input,
    form textarea,
    form select {
        width: 100%;
        padding: 10px;
        margin-bottom: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 16px;
    }

    /* Submit button styling */
    form input[type="submit"] {
        background: #007bff;
        color: #fff;
        border: none;
        padding: 10px 15px;
        cursor: pointer;
        border-radius: 5px;
        font-size: 16px;
    }

    /* Dropdown styling */
    select {
        background-color: #f9f9f9;
    }

    /* AI solution box styling */
    .ai-solution {
        margin-top: 20px;
        padding: 15px;
        background: #e7f3fe;
        border: 1px solid #b3d7ff;
        border-radius: 5px;
        color: #31708f;
    }

    /* Caution text styling */
    .caution {
        color: #d9534f;
        font-weight: bold;
    }
</style>
</head>
<body>
<div class="container">
    <div class="well">
        <h2>Submit Your Problem</h2>
        <form action="submit_detailed_problem.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="pdf_path" value="">
            
            <!-- Specialization Dropdown -->
            <div class="form-group">
                <label for="specialization">Select Specialization</label>
                <select name="specialization" id="specialization" required>
                    <option value="" disabled selected>Select a specialization</option>
                    <option value="Pulmonologist">Pulmonologist: Lung, respiratory issues, asthma, bronchitis, breathing problems</option>
                    <option value="General Physician">General Physician: Fever, headache, weakness, general checkup</option>
                    <option value="Cardiologist">Cardiologist: Heart, chest pain, blood pressure, palpitations</option>
                    <option value="Dermatologist">Dermatologist: Skin, rash, eczema, acne, psoriasis</option>
                    <option value="Neurologist">Neurologist: Brain, nerves, seizures, stroke, paralysis</option>
                    <option value="Pediatrician">Pediatrician: Child health, vaccination, development issues</option>
                    <option value="Orthopedist">Orthopedist: Bones, fractures, arthritis, joint pain</option>
                    <option value="Gastroenterologist">Gastroenterologist: Stomach, digestion, ulcers, IBS, liver issues</option>
                    <option value="Endocrinologist">Endocrinologist: Hormonal imbalance, thyroid, diabetes</option>
                    <option value="Urologist">Urologist: Urinary tract, kidney stones, bladder issues</option>
                    <option value="Oncologist">Oncologist: Cancer, tumors, chemotherapy</option>
                    <option value="Psychiatrist">Psychiatrist: Mental health, depression, anxiety, PTSD</option>
                    <option value="Rheumatologist">Rheumatologist: Arthritis, autoimmune disorders, chronic pain</option>
                    <option value="Ophthalmologist">Ophthalmologist: Eyes, vision problems, cataracts</option>
                    <option value="ENT Specialist">ENT Specialist: Ear, nose, throat, sinusitis, hearing loss</option>
                    <option value="Nephrologist">Nephrologist: Kidney, dialysis, nephritis</option>
                    <option value="Surgeon">Surgeon: Surgeries, wounds, hernia</option>
                    <option value="Gynecologist">Gynecologist: Women's health, pregnancy, menstrual disorders</option>
                </select>
            </div>
            

            <div class="form-group">
                <label for="problem_description">Problem Description</label>
                <textarea name="problem_description" id="problem_description" required></textarea>
            </div>
            <div class="form-group">
                <label for="current_medication">Current Medication</label>
                <textarea name="current_medication" id="current_medication" required></textarea>
            </div>
            <div class="form-group">
                <label for="hospital_search">Search Hospital</label>
                <div class="hospital-search-container">
                    <input type="text" name="hospital_search" id="hospital_search" placeholder="Search by name or zip code" onkeyup="searchHospital()">
                    <input type="hidden" name="hospital_id" id="hospital_id">
                    <div id="hospital_list" style="display: none;"></div>
                </div>
                <p id="registration_message"></p> <!-- Message will be displayed here -->
            </div>
            <div class="form-group">
                <label for="video">Upload Video</label>
                <input type="file" name="video" id="video">
            </div>
            <div class="form-group">
                <label for="ai_solution">AI Generated Solution</label>
                <p class="caution">Caution: Use for minor problems. AI can make mistakes.</p>
                <button type="button" id="generate_solution">Get AI Generated Solution</button>
                <div class="ai-solution" id="ai_solution_display"></div>
            </div>
            <input type="submit" value="Submit">
        </form>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
    function searchHospital() {
        let query = $('#hospital_search').val();
        if (query.length > 2) {
            $.ajax({
                url: '', // Current file for hospital search
                type: 'GET',
                data: { query: query },
                success: function(response) {
                    $('#hospital_list').html(response).show();
                    $('.hospital-option').click(function() {
                        let hospital_id = $(this).data('id');
                        let registration_fee = $(this).data('fee');
                        $('#hospital_search').val($(this).text());
                        $('#hospital_id').val(hospital_id);
                        $('#hospital_list').hide();
                        // Check registration status when a hospital is selected
                        checkRegistrationStatus(hospital_id, registration_fee);
                    });
                },
                error: function() {
                    alert("Error: Could not retrieve hospital list.");
                }
            });
        } else {
            $('#hospital_list').hide();
        }
    }

    // Function to check registration status
    function checkRegistrationStatus(hospital_id, registration_fee) {
        $.ajax({
            url: '', // Same PHP file
            type: 'POST',
            data: { hospital_id: hospital_id },
            success: function(response) {
                if (response.indexOf("registration fee") !== -1) {
                    $('#registration_message').html('<strong>' + response + '</strong>').css('color', 'red');
                    $('form input[type="submit"]').prop('disabled', true); // Disable form submission
                } else {
                    $('#registration_message').html('<strong>' + response + '</strong>').css('color', 'green');
                    $('form input[type="submit"]').prop('disabled', false); // Enable form submission
                }
            },
            error: function() {
                alert("Error: Could not check registration status.");
            }
        });
    }

    // Generate AI solution
    $('#generate_solution').on('click', function() {
        var problem_description = $('#problem_description').val();
        if (problem_description.length > 0) {
            $.ajax({
                url: 'get_ai_solution.php',
                method: 'POST',
                data: { problem_description: problem_description },
                success: function(data) {
                    $('#ai_solution_display').text(data);
                }
            });
        } else {
            alert('Please enter a problem description.');
        }
    });
</script>
</body>
</html>
