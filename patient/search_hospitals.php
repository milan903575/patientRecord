<?php
include '../connection.php';

if (isset($_GET['query'])) {
    $query = $_GET['query'];

    // Prepare a search query for hospital name or zip code
    $stmt = $conn->prepare("SELECT id, hospital_name FROM hospitals WHERE hospital_name LIKE ? OR zipcode LIKE ?");
    $searchTerm = "%" . $query . "%";
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    $hospitals = [];
    while ($row = $result->fetch_assoc()) {
        $hospitals[] = $row;
    }

    $stmt->close();
    $conn->close();

    // Send JSON response with hospital list
    header('Content-Type: application/json');
    echo json_encode($hospitals);
}
?>
