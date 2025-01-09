<?php
include '../connection.php';
session_start();

// Check if the patient is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'patient') {
    echo "You are not logged in. Please log in to continue.";
    exit;
}

// Securely retrieve session and POST data
$patient_id = $_SESSION['user_id'];
$problem = !empty($_POST['problem']) ? trim(mysqli_real_escape_string($conn, $_POST['problem'])) : null;
$problem_description = !empty($_POST['problem_description']) ? trim(mysqli_real_escape_string($conn, $_POST['problem_description'])) : null;
$current_medication = !empty($_POST['current_medication']) ? trim(mysqli_real_escape_string($conn, $_POST['current_medication'])) : null;
$hospital_id = isset($_POST['hospital_id']) ? (int)$_POST['hospital_id'] : 0;
$ai_solution = !empty($_POST['ai_solution']) ? trim(mysqli_real_escape_string($conn, $_POST['ai_solution'])) : null;
$date_submitted = date('Y-m-d H:i:s');

// Validate uploaded video file
$video_path = null;
if (!empty($_FILES['video']['name'])) {
    $video_name = basename($_FILES["video"]["name"]);
    $videoFileType = strtolower(pathinfo($video_name, PATHINFO_EXTENSION));
    $allowed_types = ["mp4", "avi", "mov", "wmv"];

    if ($_FILES["video"]["size"] > 50000000) {
        echo "Sorry, your file is too large.";
        exit;
    }

    if (!in_array($videoFileType, $allowed_types)) {
        echo "Only MP4, AVI, MOV & WMV files are allowed.";
        exit;
    }

    if (!is_uploaded_file($_FILES['video']['tmp_name']) || !is_readable($_FILES['video']['tmp_name'])) {
        echo "Invalid file upload. Please try again.";
        exit;
    }

    $video_path = file_get_contents($_FILES["video"]["tmp_name"]);
}

// Validate required fields
if (empty($problem) || empty($problem_description) || $hospital_id <= 0) {
    echo "All fields are required.";
    exit;
}

// Retrieve all available doctors and their pending counts
$query_doctors = "
    SELECT 
        d.id, 
        d.first_name, 
        d.last_name, 
        d.specialization, 
        COUNT(ph.id) AS pending_count
    FROM doctors d
    LEFT JOIN patient_history ph ON d.id = ph.doctor_id AND ph.status = 'pending'
    WHERE d.status = 'available'
    GROUP BY d.id, d.specialization
";
$doctor_result = mysqli_query($conn, $query_doctors);
if (!$doctor_result) {
    echo "Error fetching doctor data: " . mysqli_error($conn);
    exit;
}

$doctors_data = [];
while ($doctor = mysqli_fetch_assoc($doctor_result)) {
    // Match specialization with the patient's problem
    if (stripos($problem, $doctor['specialization']) !== false) {
        $doctors_data[] = [
            'id' => $doctor['id'],
            'first_name' => $doctor['first_name'],
            'last_name' => $doctor['last_name'],
            'specialization' => $doctor['specialization'],
            'pending_count' => $doctor['pending_count'],
        ];
    }
}

// If no doctors match the specialization, return an error
if (empty($doctors_data)) {
    echo "No doctors available matching the problem's specialization.";
    exit;
}

// Find the doctor with the least pending count
usort($doctors_data, function ($a, $b) {
    return $a['pending_count'] - $b['pending_count'];
});

$least_pending_count = $doctors_data[0]['pending_count'];
$least_loaded_doctors = array_filter($doctors_data, function ($doctor) use ($least_pending_count) {
    return $doctor['pending_count'] === $least_pending_count;
});

// Select any doctor from the least loaded doctors (random selection if there's a tie)
$selected_doctor = $least_loaded_doctors[array_rand($least_loaded_doctors)];
$doctor_id = $selected_doctor['id'];
$doctor_name = $selected_doctor['first_name'] . ' ' . $selected_doctor['last_name'];

// Insert data into the database
$sql = "INSERT INTO patient_history 
        (patient_id, problem, problem_description, current_medication, date_submitted, hospital_id, doctor_id, ai_solution, video)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo "Error preparing statement: " . $conn->error;
    exit;
}
$stmt->bind_param(
    "isssssisb", 
    $patient_id, 
    $problem, 
    $problem_description, 
    $current_medication, 
    $date_submitted, 
    $hospital_id, 
    $doctor_id, 
    $ai_solution, 
    $video_path
);

if ($stmt->execute()) {
    echo "<div id='message' style='text-align: center; font-size: 20px;'>Your appointment is set with Dr. $doctor_name. Redirecting in <span id='countdown'>3</span> seconds...</div>";
} else {
    echo "<div id='message' style='text-align: center; font-size: 20px;'>Error: " . $stmt->error . ". Redirecting in <span id='countdown'>3</span> seconds...</div>";
}

$stmt->close();
$conn->close();
?>

<script>
    let countdown = 3;
    const countdownElement = document.getElementById('countdown');

    const interval = setInterval(() => {
        countdown--;
        countdownElement.textContent = countdown;

        if (countdown <= 0) {
            clearInterval(interval);
            window.location.href = 'your_redirect_page.php';
        }
    }, 1000);
</script>
