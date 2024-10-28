<!-- process_hospital_registration.php -->
<?php
include 'connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $hospital_name = mysqli_real_escape_string($conn, $_POST['hospital_name']);
    $state = mysqli_real_escape_string($conn, $_POST['state']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $zipcode = mysqli_real_escape_string($conn, $_POST['zipcode']);

    $sql = "INSERT INTO hospitals (hospital_name, state, city, zipcode) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $hospital_name, $state, $city, $zipcode);

    if ($stmt->execute()) {
        echo "Hospital registered successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
