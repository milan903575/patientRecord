<!-- get_hospitals.php -->
<?php
include 'connection.php';

$state = $_GET['state'];
$city = $_GET['city'];

$sql = "SELECT id, hospital_name FROM hospitals WHERE state = ? AND city = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $state, $city);
$stmt->execute();
$result = $stmt->get_result();

$options = "<option value=''>Select hospital</option>";
while ($row = $result->fetch_assoc()) {
    $options .= "<option value='" . $row['id'] . "'>" . $row['hospital_name'] . "</option>";
}

echo $options;

$stmt->close();
$conn->close();
?>
