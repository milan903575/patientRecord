<?php
include 'connection.php';

// Define variables and initialize with empty values
$hospital_name = $state = $city = $zip_code = $reg_fee = $duration = $patient_id = "";
$hospital_name_err = $state_err = $city_err = $zip_code_err = $reg_fee_err = $duration_err = $licence_file_err = $hospital_seal_err = "";

// Assume the patient_id is retrieved from session or a previous step
$patient_id = 1; // Example patient ID

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate hospital name
    if (empty(trim($_POST["hospital_name"]))) {
        $hospital_name_err = "Please enter a hospital name.";
    } else {
        $hospital_name = trim($_POST["hospital_name"]);
    }

    // Validate state
    if (empty(trim($_POST["state"]))) {
        $state_err = "Please enter a state.";
    } else {
        $state = trim($_POST["state"]);
    }

    // Validate city
    if (empty(trim($_POST["city"]))) {
        $city_err = "Please enter a city.";
    } else {
        $city = trim($_POST["city"]);
    }

    // Validate zip code
    if (empty(trim($_POST["zip_code"]))) {
        $zip_code_err = "Please enter a zip code.";
    } elseif (!preg_match("/^[0-9]{6}$/", trim($_POST["zip_code"]))) {
        $zip_code_err = "Please enter a valid 6-digit zip code.";
    } else {
        $zip_code = trim($_POST["zip_code"]);
    }

    // Validate licence file
    if (empty($_FILES["licence_file"]["name"])) {
        $licence_file_err = "Please upload a licence file.";
    } else {
        $allowed_types = ['pdf', 'jpg', 'jpeg', 'png'];
        $file_type = pathinfo($_FILES["licence_file"]["name"], PATHINFO_EXTENSION);
        if (!in_array($file_type, $allowed_types)) {
            $licence_file_err = "Only PDF, JPG, JPEG, and PNG files are allowed.";
        } else {
            $licence_file = $_FILES["licence_file"]["name"];
            $target_dir = "uploads/";
            $target_file = $target_dir . basename($_FILES["licence_file"]["name"]);
            if (!move_uploaded_file($_FILES["licence_file"]["tmp_name"], $target_file)) {
                $licence_file_err = "There was an error uploading your file.";
            }
        }
    }

    // Validate hospital seal
    if (empty($_FILES["hospital_seal"]["name"])) {
        $hospital_seal_err = "Please upload a hospital seal.";
    } else {
        $allowed_seal_types = ['jpg', 'jpeg', 'png'];
        $seal_file_type = pathinfo($_FILES["hospital_seal"]["name"], PATHINFO_EXTENSION);
        if (!in_array($seal_file_type, $allowed_seal_types)) {
            $hospital_seal_err = "Only JPG, JPEG, and PNG files are allowed for the hospital seal.";
        } else {
            $hospital_seal = $_FILES["hospital_seal"]["name"];
            $target_seal_dir = "uploads/";
            $target_seal_file = $target_seal_dir . basename($_FILES["hospital_seal"]["name"]);
            if (!move_uploaded_file($_FILES["hospital_seal"]["tmp_name"], $target_seal_file)) {
                $hospital_seal_err = "There was an error uploading your hospital seal.";
            }
        }
    }

    // Validate registration fee and duration if checkbox is checked
    if (isset($_POST["collect_fee"])) {
        if (empty(trim($_POST["reg_fee"]))) {
            $reg_fee_err = "Please enter the registration fee.";
        } elseif (!is_numeric(trim($_POST["reg_fee"]))) {
            $reg_fee_err = "Please enter a valid registration fee.";
        } else {
            $reg_fee = trim($_POST["reg_fee"]);
        }

        if (empty(trim($_POST["duration"]))) {
            $duration_err = "Please enter the duration.";
        } elseif (!is_numeric(trim($_POST["duration"]))) {
            $duration_err = "Please enter a valid duration.";
        } else {
            $duration = trim($_POST["duration"]);
        }
    } else {
        $reg_fee = 0;
        $duration = 0;
    }

    // Check input errors before inserting in database
    if (empty($hospital_name_err) && empty($state_err) && empty($city_err) && empty($zip_code_err) && empty($licence_file_err) && empty($hospital_seal_err) && empty($reg_fee_err) && empty($duration_err)) {
        // Prepare an insert statement
        $sql = "INSERT INTO hospitals (hospital_name, state, city, zip_code, licence_file, hospital_seal, reg_fee, duration, patient_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = mysqli_prepare($conn, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "ssssssiii", $param_hospital_name, $param_state, $param_city, $param_zip_code, $param_licence_file, $param_hospital_seal, $param_reg_fee, $param_duration, $param_patient_id);

            // Set parameters
            $param_hospital_name = $hospital_name;
            $param_state = $state;
            $param_city = $city;
            $param_zip_code = $zip_code;
            $param_licence_file = $licence_file;
            $param_hospital_seal = $hospital_seal;
            $param_reg_fee = $reg_fee;
            $param_duration = $duration;
            $param_patient_id = $patient_id;

            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                // Redirect to success page
                header("location: success.php");
                exit();
            } else {
                echo "Something went wrong. Please try again later.";
            }
        }

        // Close statement
        mysqli_stmt_close($stmt);
    }

    // Close connection
    mysqli_close($conn);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hospital Registration</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 90%;
            max-width: 800px;
            margin: auto;
            overflow: hidden;
        }
        .well {
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
        form input[type="number"],
        form input[type="checkbox"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        form input[type="file"] {
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        form input[type="submit"] {
            background: #007bff;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        form input[type="submit"]:hover {
            background: #0056b3;
        }
        .form-group {
            position: relative;
        }
        .collect-fee-container {
            display: flex;
            align-items: center;
        }
        .collect-fee-container label {
            margin-left: 10px;
        }
        .fee-fields {
            display: none;
        }
    </style>
    <script>
        function toggleFeeFields() {
            var feeCheckbox = document.getElementById("collect_fee");
            var feeFields = document.getElementById("fee_fields");
            feeFields.style.display = feeCheckbox.checked ? "block" : "none";
        }
    </script>
</head>
<body>
<div class="container">
    <div class="well">
        <h2>Register Hospital</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
            <div class="form-group <?php echo (!empty($hospital_name_err)) ? 'has-error' : ''; ?>">
                <label for="hospital_name">Hospital Name</label>
                <input type="text" name="hospital_name" id="hospital_name" value="<?php echo $hospital_name; ?>" required>
                <span class="help-block"><?php echo $hospital_name_err; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($state_err)) ? 'has-error' : ''; ?>">
                <label for="state">State</label>
                <input type="text" name="state" id="state" value="<?php echo $state; ?>" required>
                <span class="help-block"><?php echo $state_err; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($city_err)) ? 'has-error' : ''; ?>">
                <label for="city">City</label>
                <input type="text" name="city" id="city" value="<?php echo $city; ?>" required>
                <span class="help-block"><?php echo $city_err; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($zip_code_err)) ? 'has-error' : ''; ?>">
                <label for="zip_code">Zip Code</label>
                <input type="text" name="zip_code" id="zip_code" value="<?php echo $zip_code; ?>" required>
                <span class="help-block"><?php echo $zip_code_err; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($licence_file_err)) ? 'has-error' : ''; ?>">
                <label for="licence_file">Upload Licence File</label>
                <input type="file" name="licence_file" id="licence_file" required>
                <span class="help-block"><?php echo $licence_file_err; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($hospital_seal_err)) ? 'has-error' : ''; ?>">
                <label for="hospital_seal">Upload Hospital Seal</label>
                <input type="file" name="hospital_seal" id="hospital_seal" required>
                <span class="help-block"><?php echo $hospital_seal_err; ?></span>
            </div>
            <div class="form-group collect-fee-container <?php echo (!empty($reg_fee_err) || !empty($duration_err)) ? 'has-error' : ''; ?>">
                <input type="checkbox" name="collect_fee" id="collect_fee" onclick="toggleFeeFields()" <?php echo isset($_POST['collect_fee']) ? 'checked' : ''; ?>>
                <label for="collect_fee">Collect Registration Fee?</label>
            </div>
            <div id="fee_fields" class="fee-fields">
                <div class="form-group <?php echo (!empty($reg_fee_err)) ? 'has-error' : ''; ?>">
                    <label for="reg_fee">Registration Fee</label>
                    <input type="number" name="reg_fee" id="reg_fee" value="<?php echo $reg_fee; ?>">
                    <span class="help-block"><?php echo $reg_fee_err; ?></span>
                </div>
                <div class="form-group <?php echo (!empty($duration_err)) ? 'has-error' : ''; ?>">
                    <label for="duration">Duration</label>
                    <input type="number" name="duration" id="duration" value="<?php echo $duration; ?>">
                    <span class="help-block"><?php echo $duration_err; ?></span>
                </div>
            </div>
            <div class="form-group">
                <input type="submit" value="Register Hospital">
            </div>
        </form>
    </div>
</div>
</body>
</html>
