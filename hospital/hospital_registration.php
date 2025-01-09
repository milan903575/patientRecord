<?php
include '../connection.php';

// Initialize variables
$hospital_name = $country = $state = $city = $zip_code = $email = $password = $confirm_password = "";
$hospital_name_err = $country_err = $state_err = $city_err = $zip_code_err = $reg_fee_err = $duration_err = "";
$licence_file_err = $hospital_seal_err = $gov_id_proof_err = $director_approve_err = "";
$errors = [];

// Handle POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate inputs
    $hospital_name = mysqli_real_escape_string($conn, trim($_POST["hospital_name"]));
    $country = isset($_POST["country"]) ? mysqli_real_escape_string($conn, trim($_POST["country"])) : "";
    $state = mysqli_real_escape_string($conn, trim($_POST["state"]));
    $city = mysqli_real_escape_string($conn, trim($_POST["city"]));
    $zip_code = mysqli_real_escape_string($conn, trim($_POST["zip_code"]));
    $email = mysqli_real_escape_string($conn, filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL));
    $password = mysqli_real_escape_string($conn, trim($_POST["password"]));
    $confirm_password = mysqli_real_escape_string($conn, trim($_POST["confirm_password"]));
    $terms = isset($_POST["terms"]) ? 1 : 0;
    $consent = isset($_POST["consent"]) ? 1 : 0;

    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    // Password validation
    if (empty($password) || empty($confirm_password)) {
        $errors[] = "Password and Confirm Password are required.";
    } elseif ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    // File upload configuration
    $max_file_size = 5 * 1024 * 1024; // 5MB limit
    $allowed_file_types = ['image/jpeg', 'image/png', 'application/pdf'];

    // Function to validate file type
    function is_valid_file_type($file_type) {
        global $allowed_file_types;
        return in_array($file_type, $allowed_file_types);
    }

    // Validate and process file uploads
    $files = ['licence_file', 'hospital_seal', 'gov_id_proof', 'director_approve'];
    foreach ($files as $file) {
        if (isset($_FILES[$file]['tmp_name']) && is_uploaded_file($_FILES[$file]['tmp_name'])) {
            $file_type = $_FILES[$file]['type'];
            $file_size = $_FILES[$file]['size'];

            // File size check
            if ($file_size > $max_file_size) {
                $errors[] = ucfirst(str_replace('_', ' ', $file)) . " exceeds the maximum size of 5MB.";
            } elseif (!is_valid_file_type($file_type)) {
                $errors[] = "Invalid file type for " . ucfirst(str_replace('_', ' ', $file)) . ". Only JPEG, PNG, and PDF files are allowed.";
            } else {
                $$file = file_get_contents($_FILES[$file]['tmp_name']);
            }
        } else {
            $errors[] = ucfirst(str_replace('_', ' ', $file)) . " is required.";
        }
    }

    // Password hashing
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    if (empty($errors)) {
        // Default values
        $registration_fee = 0.00;
        $registration_duration = 0;
  

        // SQL Query
// Corrected SQL Query
$sql = "INSERT INTO hospitals 
        (hospital_name, country, state, city, zipcode, registration_fee, registration_duration, license_file, hospital_seal, email, password, gov_id_proof, terms, consent, director_approve) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

// Prepare statement
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "sssssdsssssiiss", 
        $hospital_name, 
        $country, 
        $state, 
        $city, 
        $zip_code, 
        $registration_fee, 
        $registration_duration, 
        $licence_file, 
        $hospital_seal, 
        $email, 
        $hashed_password, 
        $gov_id_proof, 
        $terms, 
        $consent, 
        $director_approve
    );

    // Execute statement
    if (mysqli_stmt_execute($stmt)) {
        header("Location: hospital_success.php");
        exit();
    } else {
        $errors[] = "Database error: Unable to register hospital.";
    }
} else {
    $errors[] = "Database error: Failed to prepare statement.";
}

    }
}

// Debugging: Display errors (optional for production)
if (!empty($errors)) {
    foreach ($errors as $error) {
        echo "<p>Error: $error</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Hospital</title>
    <style>
/* Basic Reset */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
}

body {
    background-color: #f0f4f8;
    padding: 20px;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    color: #333;
}

.form-container {
    background-color: #ffffff;
    padding: 25px 30px;
    border-radius: 12px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    width: 100%;
    max-width: 600px;
    overflow-y: auto;
    animation: fadeIn 0.5s ease-in-out;
}

h1 {
    text-align: center;
    color: #2d3436;
    margin-bottom: 25px;
    font-size: 1.8em;
    font-weight: bold;
    letter-spacing: 0.5px;
}

.form-group {
    margin-bottom: 20px;
    position: relative;
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-size: 0.95em;
    color: #444;
    margin-bottom: 8px;
    font-weight: 500;
}

.form-group input[type="text"],
.form-group input[type="email"],
.form-group input[type="password"],
.form-group input[type="file"],
.form-group input[type="number"],
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 10px;
    font-size: 1em;
    color: #333;
    background-color: #f8f9fa;
    transition: all 0.3s ease-in-out;
}

.form-group textarea {
    resize: vertical;
    height: 100px;
}

.form-group input[type="text"]:focus,
.form-group input[type="email"]:focus,
.form-group input[type="password"]:focus,
.form-group input[type="number"]:focus,
.form-group select:focus,
.form-group textarea:focus {
    border-color: #3498db;
    box-shadow: 0 0 5px rgba(52, 152, 219, 0.4);
    outline: none;
}

.form-group input[type="checkbox"] {
    width: auto;
    margin-right: 10px;
}

.form-group input[type="file"] {
    padding: 5px;
    border: none;
    font-size: 0.9em;
}

.help-block {
    color: #e74c3c;
    font-size: 0.85em;
    margin-top: 5px;
}

.form-group.has-error input[type="text"],
.form-group.has-error input[type="email"],
.form-group.has-error input[type="password"],
.form-group.has-error input[type="number"],
.form-group select {
    border-color: #e74c3c;
}

.form-group input[type="submit"] {
    background-color: #3498db;
    color: white;
    font-size: 1em;
    border: none;
    padding: 12px;
    border-radius: 10px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.form-group input[type="submit"]:hover {
    background-color: #2980b9;
}

.fee-fields {
    display: none;
    margin-top: 15px;
}

.fee-fields .form-group {
    margin-bottom: 15px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .form-container {
        padding: 20px;
    }

    h1 {
        font-size: 1.5em;
    }

    .form-group input[type="text"],
    .form-group input[type="email"],
    .form-group input[type="password"],
    .form-group input[type="file"],
    .form-group input[type="number"],
    .form-group select {
        padding: 10px;
    }
}

/* Animations */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
   </style>
</head>
<body>
    <div class="form-container">
        <h1>Register Hospital</h1>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
            <!-- Hospital Details -->
            <div class="form-group <?php echo (!empty($hospital_name_err)) ? 'has-error' : ''; ?>">
                <label for="hospital_name">Hospital Name</label>
                <input type="text" name="hospital_name" id="hospital_name" value="<?php echo $hospital_name; ?>" required>
                <span class="help-block"><?php echo $hospital_name_err; ?></span>
            </div>
<div class="form-group <?php echo (!empty($country_err)) ? 'has-error' : ''; ?>">
    <label for="country">Country</label>
    <input type="text" name="country" id="country" value="<?php echo $country; ?>" required>
    <span class="help-block"><?php echo $country_err; ?></span>
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

            <!-- File Uploads -->
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

            <!-- Conditional Registration Fee -->
            <div class="form-group">
                <input type="checkbox" name="collect_fee" id="collect_fee" onclick="toggleFeeFields()">
                <label for="collect_fee">Collect Registration Fee?</label>
            </div>
            <div id="fee_fields" class="fee-fields">
                <div class="form-group">
                    <label for="reg_fee">Registration Fee</label>
                    <input type="number" name="reg_fee" id="reg_fee">
                </div>
                <div class="form-group">
                    <label for="duration">Duration (in days)</label>
                    <input type="number" name="duration" id="duration">
                </div>
            </div>

            <!-- Additional Fields -->
            <div class="form-group">
                <label for="gov_id_proof">Government ID Proof</label>
                <input type="file" name="gov_id_proof" id="gov_id_proof" required>
            </div>
            <div class="form-group">
                <label for="director_approve">Director Approval Letter</label>
                <input type="file" name="director_approve" id="director_approve" required>
            </div>

            <!-- Email and Password -->
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" name="confirm_password" id="confirm_password" required>
            </div>

            <!-- Terms and Consent -->
            <div class="form-group">
                <input type="checkbox" name="terms" id="terms" required>
                <label for="terms">I agree to the Terms and Conditions</label>
            </div>

            <div class="form-group">
                <input type="checkbox" name="terms" id="terms" required>
                <label for="terms">I agree to the Consent</label>
            </div>


            <!-- Submit Button -->
            <div class="form-group">
                <input type="submit" value="Register">
            </div>
        </form>
    </div>

    <script>
        // Toggle Fee Fields
        function toggleFeeFields() {
            const feeFields = document.getElementById('fee_fields');
            feeFields.style.display = document.getElementById('collect_fee').checked ? 'block' : 'none';
        }
    </script>
</body>
</html>

