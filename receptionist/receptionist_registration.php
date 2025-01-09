<?php
include '../connection.php';

// Handle hospital search AJAX request
if (isset($_GET['query'])) {
    $query = mysqli_real_escape_string($conn, $_GET['query']);
    $sql = "SELECT id, hospital_name FROM hospitals WHERE hospital_name LIKE ? OR zipcode LIKE ?";
    $stmt = $conn->prepare($sql);
    $search_query = "%" . $query . "%";
    $stmt->bind_param("ss", $search_query, $search_query);
    $stmt->execute();
    $result = $stmt->get_result();

    $output = '';
    while ($row = $result->fetch_assoc()) {
        $output .= '<div class="hospital-option" data-id="' . $row['id'] . '">' . htmlspecialchars($row['hospital_name']) . '</div>';
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
    <title>Receptionist Registration</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-validator/0.5.3/css/bootstrapValidator.min.css">
    <style>
        body {
            background-color: #f2f2f2;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .well {
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        .form-group img {
            max-width: 100%;
            height: auto;
        }
        #hospital_results {
            position: absolute;
            background: #fff;
            border: 1px solid #ccc;
            width: calc(100% - 20px);
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
        }
        .hospital-option {
            padding: 10px;
            cursor: pointer;
        }
        .hospital-option:hover {
            background: #f4f4f4;
        }
    </style>
</head>
<body>
<div class="container">
    <form class="well form-horizontal" action="register_receptionist.php" method="POST" id="patient_form" enctype="multipart/form-data">
        <fieldset>
            <legend>Receptionist Registration</legend>

            <!-- Hospital Search -->
            <div class="form-group">
                <label class="col-md-4 control-label">Hospital</label>
                <div class="col-md-8 inputGroupContainer">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-plus"></i></span>
                        <input type="text" id="hospital_search" placeholder="Search by name or zip code" class="form-control" oninput="searchHospital()">
                        <input type="hidden" name="hospital_id" id="hospital_id">
                        <div id="hospital_list" class="list-group"></div>
                    </div>
                </div>
            </div>

            <!-- First Name -->
            <div class="form-group">
                <label class="col-md-4 control-label">First Name</label>
                <div class="col-md-8 inputGroupContainer">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                        <input name="first_name" placeholder="First Name" class="form-control" type="text" required>
                    </div>
                </div>
            </div>

            <!-- Last Name -->
            <div class="form-group">
                <label class="col-md-4 control-label">Last Name</label>
                <div class="col-md-8 inputGroupContainer">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                        <input name="last_name" placeholder="Last Name" class="form-control" type="text">
                    </div>
                </div>
            </div>

            <!-- Hospital ID Proof -->
            <div class="form-group">
                <label class="col-md-4 control-label">Hospital ID Proof</label>
                <div class="col-md-8 inputGroupContainer">
                    <input name="hospital_id_proof" type="file" accept="image/*" class="form-control" required>
                </div>
            </div>

            <!-- Government ID Proof -->
            <div class="form-group">
                <label class="col-md-4 control-label">Government ID Proof</label>
                <div class="col-md-8 inputGroupContainer">
                    <input name="government_id_proof" type="file" accept="image/*" class="form-control" required>
                </div>
            </div>

            <!-- Email -->
            <div class="form-group">
                <label class="col-md-4 control-label">Email</label>
                <div class="col-md-8 inputGroupContainer">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-envelope"></i></span>
                        <input name="email" placeholder="Email" class="form-control" type="email" required>
                    </div>
                </div>
            </div>

            <!-- Password -->
            <div class="form-group">
                <label class="col-md-4 control-label">Password</label>
                <div class="col-md-8 inputGroupContainer">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                        <input name="password" placeholder="Password" class="form-control" type="password" required>
                    </div>
                </div>
            </div>

            <!-- Confirm Password -->
            <div class="form-group">
                <label class="col-md-4 control-label">Confirm Password</label>
                <div class="col-md-8 inputGroupContainer">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                        <input name="confirm_password" placeholder="Confirm Password" class="form-control" type="password" required>
                    </div>
                </div>
            </div>

            <!-- Terms and Conditions Checkbox -->
            <div class="form-group">
                <label class="col-md-4 control-label">Terms</label>
                <div class="col-md-8">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="terms" required> I agree to the terms and conditions.
                        </label>
                    </div>
                </div>
            </div>

            <!-- Hospital Belonging Consent -->
            <div class="form-group">
                <label class="col-md-4 control-label">Consent</label>
                <div class="col-md-8">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="hospital_consent" required> I confirm that I am associated with this hospital.
                        </label>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="form-group">
                <label class="col-md-4 control-label"></label>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-warning">Register <span class="glyphicon glyphicon-send"></span></button>
                </div>
            </div>
        </fieldset>
    </form>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-validator/0.5.3/js/bootstrapValidator.min.js"></script>
<script>
$(document).ready(function() {
    $('#patient_form').bootstrapValidator({
        feedbackIcons: {
            valid: 'glyphicon glyphicon-ok',
            invalid: 'glyphicon glyphicon-remove',
            validating: 'glyphicon glyphicon-refresh'
        },
        fields: {
            first_name: { validators: { notEmpty: { message: 'The first name is required' } } },
            last_name: { validators: { notEmpty: { message: 'The last name is required' } } },
            email: {
                validators: {
                    notEmpty: { message: 'The email address is required' },
                    emailAddress: { message: 'The email address is not valid' }
                }
            },
            password: { validators: { notEmpty: { message: 'The password is required' } } },
            confirm_password: {
                validators: {
                    notEmpty: { message: 'The password confirmation is required' },
                    identical: { field: 'password', message: 'The password and its confirmation do not match' }
                }
            },
            terms: { validators: { notEmpty: { message: 'You must agree to the terms' } } },
            hospital_consent: { validators: { notEmpty: { message: 'You must confirm your association' } } }
        }
    });
});

function searchHospital() {
    let query = $('#hospital_search').val();
    if (query.length > 0) {
        $.ajax({
            url: '', // Current file for hospital search
            type: 'GET',
            data: { query: query },
            success: function(response) {
                $('#hospital_list').html(response).show();
                $('.hospital-option').click(function() {
                    let hospital_id = $(this).data('id');
                    $('#hospital_search').val($(this).text());
                    $('#hospital_id').val(hospital_id);
                    $('#hospital_list').hide();
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
</script>
</body>
</html>
