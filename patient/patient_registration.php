<?php
include '../connection.php';

// Handle email check AJAX request
if (isset($_POST['email_check'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email_check']);
    $query = "SELECT * FROM patients WHERE email = '$email'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        echo "exists";
    } else {
        echo "not_exists";
    }
    exit;
}

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
    <title>Patient Registration</title>
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
        border-radius: 10px;
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

    .text-danger {
        font-size: 14px;
        margin-top: 5px;
        text-align: center;
        display: block;
    }

    /* Camera Container Styling */
    #camera-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-top: 10px;
    }

    #video-container {
        border: 1px solid #ddd;
        padding: 10px;
        border-radius: 5px;
        background-color: #f9f9f9;
        text-align: center;
        max-width: 100%;
        margin-bottom: 15px;
    }

    #video, #canvas {
        width: 100%;
        max-width: 300px; /* Adjust size to fit nicely */
        height: auto;
        border-radius: 5px;
    }

    #capture-btn {
        margin-top: 10px;
        background-color: #337ab7;
        color: #fff;
        border: none;
        padding: 8px 15px;
        border-radius: 5px;
        cursor: pointer;
    }

    #capture-btn:hover {
        background-color: #286090;
    }
</style>

</head>
<body>
<div class="container">
    <form class="well form-horizontal" action="register_patient.php" method="POST" id="patient_form" enctype="multipart/form-data">
        <fieldset>
            <legend>Patient Registration</legend>
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

            <!-- Date of Birth -->
            <div class="form-group">
                <label class="col-md-4 control-label">Date of Birth</label>
                <div class="col-md-8 inputGroupContainer">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                        <input id="dob" name="dob" placeholder="Date of Birth" class="form-control" type="date" required>
                    </div>
                </div>
            </div>

            <!-- Gender -->
            <div class="form-group">
                <label class="col-md-4 control-label">Gender</label>
                <div class="col-md-8">
                    <div class="radio">
                        <label><input type="radio" name="gender" value="Male" required> Male</label>
                    </div>
                    <div class="radio">
                        <label><input type="radio" name="gender" value="Female" required> Female</label>
                    </div>
                    <div class="radio">
                        <label><input type="radio" name="gender" value="Other" required> Other</label>
                    </div>
                </div>
            </div>

            <!-- Blood Group -->
            <div class="form-group">
                <label class="col-md-4 control-label">Blood Group</label>
                <div class="col-md-8 inputGroupContainer">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-tint"></i></span>
                        <select name="blood_group" class="form-control" required>
                            <option value="">Select Blood Group</option>
                            <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="AB+">AB+</option>
                            <option value="AB-">AB-</option>
                            <option value="O+">O+</option>
                            <option value="O-">O-</option>
                        </select>
                    </div>
                </div>
            </div>


<!-- Profile Picture Upload -->
<div class="form-group">
    <label class="col-md-4 control-label">Profile Picture</label>
    <div class="col-md-8 inputGroupContainer">
        <div class="input-group" id="camera-container">
            <span class="input-group-addon"><i class="glyphicon glyphicon-picture"></i></span>

            <!-- Button to Request Camera Access -->
            <button id="request-camera-btn" type="button" class="btn btn-primary btn-sm">Request Camera Access</button>
            
            <!-- Video Preview -->
            <div id="video-container" style="display:none;">
                <video id="video" width="240" height="180" autoplay></video>
                <button id="capture-btn" type="button" class="btn btn-primary btn-sm">Capture Photo</button>
            </div>

            <!-- Canvas for Captured Image -->
            <canvas id="canvas" width="240" height="180" style="display:none;"></canvas>
            
            <!-- Hidden Input to Store Captured Image -->
            <input name="profile_picture" id="profile_picture" type="hidden" required>
        </div>
    </div>
</div>


            <!-- Location -->
            <div class="form-group">
                <label class="col-md-4 control-label">Location</label>
                <div class="col-md-8 inputGroupContainer">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-map-marker"></i></span>
                        <input name="location" placeholder="Enter Location" class="form-control" type="text">
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
    // Set the min date for Date of Birth to be 10 years ago
    let today = new Date();
    let minDate = new Date(today.setFullYear(today.getFullYear() - 1)).toISOString().split('T')[0];
    $('#dob').attr('max', minDate);

    // Initialize form validation
    $('#patient_form').bootstrapValidator({
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
            dob: {
                validators: {
                    notEmpty: {
                        message: 'The date of birth is required'
                    },
                    date: {
                        format: 'DD-MM-YYYY',
                        message: 'The date of birth is not valid'
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
            blood_group: {
                validators: {
                    notEmpty: {
                        message: 'The blood group is required'
                    }
                }
            },
            location: {
                validators: {
                    notEmpty: {
                        message: 'Location is required'
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
                    },
                    stringLength: {
                        min: 6,
                        message: 'The password must be at least 6 characters long'
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
            }
        }
    });

    // Hospital search functionality
    $('#hospital_search').on('input', function() {
        let query = $(this).val();
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
    });

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
});
$(document).ready(function () {
    // Email input validation for dynamic field visibility
    $('input[name="email"]').on('input', function () {
        const email = $(this).val();

        if (email) {
            $.ajax({
                url: '', // Current file
                type: 'POST',
                data: { email_check: email },
                success: function (response) {
                    if (response.trim() === "exists") {
                        // Hide all fields except Email, Hospital, and Register button
                        $('fieldset > div').hide(); // Hide all form groups
                        $('input[name="email"]').closest('.form-group').show(); // Show email field
                        $('#hospital_search').closest('.form-group').show(); // Show hospital field
                        $('button[type="submit"]').closest('.form-group').show(); // Show Register button

                        // Add a message for the user
                        if ($('#email-message').length === 0) {
                            // Only append the message if it doesn't already exist
                            $('form').prepend('<div id="email-message" class="alert alert-warning text-center">Your email is already registered. Please select a hospital and submit.</div>');
                        }
                    } else {
                        // Show all fields and remove the message
                        $('fieldset > div').show();
                        $('#email-message').remove();
                    }
                },
                error: function () {
                    alert("Error checking email.");
                }
            });
        } else {
            // Show all fields and remove the message if email input is cleared
            $('fieldset > div').show();
            $('#email-message').remove();
        }
    });

    // Validate hospital selection on form submit
    $('form').on('submit', function (e) {
        const hospitalValue = $('#hospital_search').val().trim();
        if (!hospitalValue) {
            e.preventDefault(); // Prevent form submission
            if ($('#hospital-message').length === 0) {
                // Add the message only if it doesn't already exist
                $('#hospital_search')
                    .closest('.form-group')
                    .append('<div id="hospital-message" class="text-danger">Please select a hospital.</div>');
            }
        } else {
            // Remove the message if hospital is selected
            $('#hospital-message').remove();
        }
    });

    // Remove error message when user starts typing in the hospital search field
    $('#hospital_search').on('input', function () {
        $('#hospital-message').remove();
    });
});
</script>

<!-- JavaScript for Camera and Image Capture -->
<script>
    const requestCameraButton = document.getElementById('request-camera-btn');
    const videoContainer = document.getElementById('video-container');
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const captureButton = document.getElementById('capture-btn');
    const profilePictureInput = document.getElementById('profile_picture');

    // Request access to the user's webcam
    requestCameraButton.addEventListener('click', () => {
        navigator.mediaDevices.getUserMedia({ video: true })
            .then((stream) => {
                video.srcObject = stream;
                videoContainer.style.display = 'block'; // Show the video and capture button
                requestCameraButton.style.display = 'none'; // Hide the request button
            })
            .catch((err) => {
                console.error("Error accessing the webcam: ", err);
                alert("Webcam access is required to capture your profile picture.");
            });
    });

    // Capture the image from the video stream
    captureButton.addEventListener('click', () => {
        const context = canvas.getContext('2d');
        canvas.style.display = 'block';
        context.drawImage(video, 0, 0, canvas.width, canvas.height);

        // Convert the canvas image to a base64 string
        const imageData = canvas.toDataURL('image/png');

        // Set the base64 string as the value of the hidden input
        profilePictureInput.value = imageData;

        alert("Photo captured successfully!");
    });
</script>

</body>
</html>
