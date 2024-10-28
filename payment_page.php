<?php
include 'connection.php';

// Assume payment processing is successful for simplicity
if (isset($_GET['amount']) && isset($_GET['patient_id'])) {
    $amount = floatval($_GET['amount']);
    $patient_id = intval($_GET['patient_id']);

    // Update the patient's registration status or record the payment
    $sql = "UPDATE patients SET registration_status = 'Completed' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "Error preparing statement: " . $conn->error;
    } else {
        $stmt->bind_param("i", $patient_id);

        if ($stmt->execute()) {
            echo "Payment of $$amount was successful! Registration completed.";
        } else {
            echo "Error executing statement: " . $stmt->error;
        }

        $stmt->close();
    }
} else {
    echo "Invalid payment request.";
}

$conn->close();
?>
