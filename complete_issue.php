<?php
include 'connection.php';

$id = $_GET['id'];

$sql = "UPDATE patient_history SET status = 'completed' WHERE id = '$id'";

if ($conn->query($sql) === TRUE) {
    echo "Issue marked as completed.";
} else {
    echo "Error updating record: " . $conn->error;
}

$conn->close();

header("Location: doctor_dashboard.php");
exit;
?>
