<?php
include 'connection.php';

$query = mysqli_real_escape_string($conn, $_GET['query']);
$sql = "SELECT id, hospital_name FROM hospitals WHERE hospital_name LIKE ? OR zipcode LIKE ?";
$stmt = $conn->prepare($sql);
$search_query = "%" . $query . "%";
$stmt->bind_param("ss", $search_query, $search_query);
$stmt->execute();
$result = $stmt->get_result();

$output = '';
while ($row = $result->fetch_assoc()) {
    $output .= '<div class="hospital-option" data-id="' . $row['id'] . '">' . $row['hospital_name'] . '</div>';
}

echo $output;

$stmt->close();
$conn->close();
?>
