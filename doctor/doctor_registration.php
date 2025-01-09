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
    <title>Doctor Registration</title>
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
    <form class="well form-horizontal" action="register_doctor.php" method="POST" id="doctor_form" enctype="multipart/form-data">
        <fieldset>
            <legend>Doctor Registration</legend>

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
                        <input name="first_name" class="form-control" type="text" placeholder="First Name" required>
                    </div>
                </div>
            </div>

            <!-- Last Name -->
            <div class="form-group">
                <label class="col-md-4 control-label">Last Name</label>
                <div class="col-md-8 inputGroupContainer">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                        <input name="last_name" class="form-control" type="text" placeholder="Last Name" required>
                    </div>
                </div>
            </div>

            <!-- Specialization -->
            <div class="form-group">
                <label class="col-md-4 control-label">Specialization</label>
                <div class="col-md-8 inputGroupContainer">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-briefcase"></i></span>
                        <input name="specialization" class="form-control" type="text" placeholder="Specialization" required>
                    </div>
                </div>
            </div>

            <!-- Date of Birth -->
            <div class="form-group">
                <label class="col-md-4 control-label">Date of Birth</label>
                <div class="col-md-8 inputGroupContainer">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                        <input name="dob" class="form-control" type="date" required>
                    </div>
                </div>
            </div>

            <!-- Gender -->
            <div class="form-group">
                <label class="col-md-4 control-label">Gender</label>
                <div class="col-md-8 inputGroupContainer">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                        <select name="gender" class="form-control" required>
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Photo Upload -->
            <div class="form-group">
                <label class="col-md-4 control-label">Your Photo</label>
                <div class="col-md-8 inputGroupContainer">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-camera"></i></span>
                        <input type="file" name="photo_file" class="form-control">
                    </div>
                </div>
            </div>

            <!-- Hospital ID Upload -->
            <div class="form-group">
                <label class="col-md-4 control-label">Hospital ID</label>
                <div class="col-md-8 inputGroupContainer">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-file"></i></span>
                        <input type="file" name="hospital_id_proof" class="form-control" required>
                    </div>
                </div>
            </div>

            <!-- Government ID Upload -->
            <div class="form-group">
                <label class="col-md-4 control-label">Government ID</label>
                <div class="col-md-8 inputGroupContainer">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-credit-card"></i></span>
                        <input type="file" name="gov_id_proof" class="form-control" required>
                    </div>
                </div>
            </div>

            <!-- Location -->
            <div class="form-group">
                <label class="col-md-4 control-label">Location</label>
                <div class="col-md-8 inputGroupContainer">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-map-marker"></i></span>
                        <input id="location" name="location" class="form-control" type="text" placeholder="Start typing location">
                    </div>
                    <div id="location_results"></div>
                </div>
            </div>

            <!-- Email -->
            <div class="form-group">
                <label class="col-md-4 control-label">Email</label>
                <div class="col-md-8 inputGroupContainer">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-envelope"></i></span>
                        <input name="email" class="form-control" type="email" placeholder="Email" required>
                    </div>
                </div>
            </div>

            <!-- Password -->
            <div class="form-group">
                <label class="col-md-4 control-label">Password</label>
                <div class="col-md-8 inputGroupContainer">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                        <input name="password" class="form-control" type="password" placeholder="Password" required>
                    </div>
                </div>
            </div>

            <!-- Confirm Password -->
            <div class="form-group">
                <label class="col-md-4 control-label">Confirm Password</label>
                <div class="col-md-8 inputGroupContainer">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                        <input name="confirm_password" class="form-control" type="password" placeholder="Confirm Password" required>
                    </div>
                </div>
            </div>

            <!-- Terms and Conditions -->
            <div class="form-group">
                <label class="col-md-4 control-label"></label>
                <div class="col-md-8">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="terms" required> I agree to the terms and conditions
                        </label>
                    </div>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="consent" required> I consent to data processing
                        </label>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="form-group">
                <label class="col-md-4 control-label"></label>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary">Register <span class="glyphicon glyphicon-send"></span></button>
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
    // Set the minimum date of birth to 21 years ago
    var today = new Date();
    var minAgeDate = new Date(today.setFullYear(today.getFullYear() - 21));
    var minDate = minAgeDate.toISOString().split('T')[0];  // Format as YYYY-MM-DD
    $('input[name="dob"]').attr('max', minDate);

    // Initialize Bootstrap Validator for the form
    $('#doctor_form').bootstrapValidator({
        feedbackIcons: {
            valid: 'glyphicon glyphicon-ok',
            invalid: 'glyphicon glyphicon-remove',
            validating: 'glyphicon glyphicon-refresh'
        },
        fields: {
            first_name: {
                validators: {
                    notEmpty: {
                        message: 'The first name is required'
                    }
                }
            },
            last_name: {
                validators: {
                    notEmpty: {
                        message: 'The last name is required'
                    }
                }
            },
            specialization: {
                validators: {
                    notEmpty: {
                        message: 'The specialization is required'
                    }
                }
            },
            dob: {
                validators: {
                    notEmpty: {
                        message: 'The date of birth is required'
                    }
                }
            },
            gender: {
                validators: {
                    notEmpty: {
                        message: 'The gender is required'
                    }
                }
            },
            photo_file: {
                validators: {
                    notEmpty: {
                        message: 'The photo file is required'
                    }
                }
            },
            hospital_id_file: {
                validators: {
                    notEmpty: {
                        message: 'The hospital ID file is required'
                    }
                }
            },
            gov_id_file: {
                validators: {
                    notEmpty: {
                        message: 'The government ID file is required'
                    }
                }
            },
            location: {
                validators: {
                    notEmpty: {
                        message: 'The location is required'
                    }
                }
            },
            email: {
                validators: {
                    notEmpty: {
                        message: 'The email address is required'
                    },
                    emailAddress: {
                        message: 'The email address is not valid'
                    }
                }
            },
            password: {
                validators: {
                    notEmpty: {
                        message: 'The password is required'
                    }
                }
            },
            confirm_password: {
                validators: {
                    notEmpty: {
                        message: 'The password confirmation is required'
                    },
                    identical: {
                        field: 'password',
                        message: 'The password and its confirmation do not match'
                    }
                }
            },
            terms: {
                validators: {
                    notEmpty: {
                        message: 'You must agree to the terms and conditions'
                    }
                }
            },
            consent: {
                validators: {
                    notEmpty: {
                        message: 'You must consent to data processing'
                    }
                }
            }
        }
    });

    // Hospital search functionality
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

    // Location search functionality
    window.searchLocation = function() {
        let query = $('#location_search').val();
        if (query.length > 0) {
            $.ajax({
                url: '', // Adjust if necessary
                type: 'GET',
                data: { location_query: query },
                success: function(response) {
                    $('#location_list').html(response).show();
                    $('.location-option').click(function() {
                        $('#location_search').val($(this).text());
                        $('#location_list').hide();
                    });
                },
                error: function() {
                    alert("Error: Could not retrieve location list.");
                }
            });
        } else {
            $('#location_list').hide();
        }
    };

    // Attach search function to hospital input field
    $('#hospital_search').on('input', searchHospital);
});
</script>
</body>
</html>
