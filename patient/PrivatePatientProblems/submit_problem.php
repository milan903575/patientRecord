<?php
include '../../connection.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'patient') {
    header("Location: ../../login.html");
    exit;
}

$patient_id = $_SESSION['user_id'];

// Doctor search logic with patient registration and status check
if (isset($_GET['query'])) {
    $query = mysqli_real_escape_string($conn, $_GET['query']);
    $sql = "
        SELECT 
            d.id AS doctor_id,
            d.first_name,
            d.last_name,
            d.specialization,
            h.id AS hospital_id,
            h.hospital_name,
            h.city,
            h.registration_fee,
            ph.registration_status
        FROM doctors d
        INNER JOIN hospitals h ON d.hospital_id = h.id
        LEFT JOIN patient_hospital ph ON h.id = ph.hospital_id AND ph.patient_id = ?
        WHERE 
            CONCAT(d.first_name, ' ', d.last_name) LIKE ? 
            OR d.specialization LIKE ?";

    $stmt = $conn->prepare($sql);
    $search_query = "%" . $query . "%";
    $stmt->bind_param("iss", $patient_id, $search_query, $search_query);
    $stmt->execute();
    $result = $stmt->get_result();

    $output = '';
    while ($row = $result->fetch_assoc()) {
        $output .= '
            <div class="doctor-option" 
                 data-id="' . $row['doctor_id'] . '" 
                 data-hospital-id="' . $row['hospital_id'] . '" 
                 data-registration-fee="' . $row['registration_fee'] . '" 
                 data-registration-status="' . htmlspecialchars($row['registration_status']) . '">
                Dr. ' . htmlspecialchars($row['first_name']) . ' ' . htmlspecialchars($row['last_name']) . ' 
                (' . htmlspecialchars($row['specialization']) . ', ' . htmlspecialchars($row['hospital_name']) . ', ' . htmlspecialchars($row['city']) . ')
            </div>';
    }

    echo $output;
    $stmt->close();
    $conn->close();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Problem</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 800px;
            margin: auto;
            padding: 20px;
        }
        .well {
            background: #fff;
            padding: 20px;
            border-radius: 5px;
        }
        form input, form textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        form input[type="submit"] {
            background: #007bff;
            color: #fff;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
        }
        .autocomplete-suggestions {
            position: absolute;
            background: #fff;
            border: 1px solid #ccc;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            top: 100%;
            left: 0;
            width: 100%;
            border-radius: 5px;
        }
        .doctor-option {
            padding: 10px;
            cursor: pointer;
        }
        .doctor-option:hover {
            background: #f4f4f4;
        }
        #registration_message {
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="well">
        <h2>Submit Your Problem</h2>
        <form action="process_problem.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="doctor_name">Search Doctor</label>
                <div class="input-wrapper" style="position: relative;">
                    <input type="text" id="doctor_name" name="doctor_name" placeholder="Type a doctor's name" autocomplete="off" required>
                    <input type="hidden" id="doctor_id" name="doctor_id">
                    <input type="hidden" id="hospital_id" name="hospital_id">
                    <div id="doctor_suggestions" class="autocomplete-suggestions"></div>
                </div>
            </div>
            <div class="form-group">
                <label for="problem_description">Problem Description</label>
                <textarea name="problem_description" id="problem_description" rows="5" required></textarea>
            </div>
            <div class="form-group">
                <label for="video_upload">Upload Video</label>
                <input type="file" name="video_upload" id="video_upload" accept="video/*">
            </div>
            <p id="registration_message"></p>
            <button type="submit" id="submit_button" class="btn btn-primary">Submit</button>
        </form>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
$(document).ready(function () {
    $('#doctor_name').on('input', function () {
        const query = $(this).val();
        
        if (query.length > 2) {
            $.ajax({
                url: '', // Current PHP file
                type: 'GET',
                data: { query: query },
                success: function (response) {
                    $('#doctor_suggestions').html(response).show();
                    $('.doctor-option').click(function () {
                        const doctor_id = $(this).data('id');
                        const hospital_id = $(this).data('hospital-id');
                        const registration_fee = $(this).data('registration-fee');
                        const registration_status = $(this).data('registration-status');

                        $('#doctor_name').val($(this).text());
                        $('#doctor_id').val(doctor_id);
                        $('#hospital_id').val(hospital_id);
                        $('#doctor_suggestions').hide();

if (!registration_status) {
    $('#registration_message')
        .text("You are not registered with this hospital. Please register on the login page.")
        .css('color', 'red');
    $('#submit_button').prop('disabled', true);
} else if (registration_fee > 0) {
    $('#registration_message')
        .text("This hospital requires a registration fee: " + registration_fee + ". Please complete your payment in the hospital list on your home page.")
        .css('color', 'red');
    $('#submit_button').prop('disabled', true);
} else {
    $('#registration_message')
        .text("Your registration is completed with this hospital. You can submit the form.")
        .css('color', 'green');
    $('#submit_button').prop('disabled', false);
}

                    });
                }
            });
        } else {
            $('#doctor_suggestions').hide();
        }
    });
});
</script>
</body>
</html>
