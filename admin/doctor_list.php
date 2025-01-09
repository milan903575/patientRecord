<?php
include '../connection.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Applications</title>
    <style>
        /* Add table styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        img {
            max-width: 100px;
            border-radius: 5px;
        }

        button {
            margin: 5px;
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            background-color: #1abc9c;
            color: white;
        }

        button:hover {
            opacity: 0.8;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            max-width: 400px;
            width: 80%;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .modal-content h3 {
            margin-bottom: 20px;
        }

        .modal-content button {
            margin: 0 10px;
        }
    </style>
</head>
<body>
    <h3>Doctor Applications</h3>
    <table>
        <thead>
            <tr>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Specialization</th>
                <th>Created At</th>
                <th>Hospital ID</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
 $query = "SELECT id, first_name, last_name, specialization, created_at, hospital_id_proof, hospital_id_proof_type 
          FROM doctors WHERE registration_status = 'pending'";
$result = mysqli_query($conn, $query);
while ($row = mysqli_fetch_assoc($result)) {
    // Sanitize text data
    $first_name = htmlspecialchars($row['first_name']);
    $last_name = htmlspecialchars($row['last_name']);
    $specialization = htmlspecialchars($row['specialization']);
    $created_at = htmlspecialchars($row['created_at']);

    // Fetch the BLOB data and its MIME type (assumed `hospital_id_proof_type` stores the file type)
    $hospital_id_proof_data = $row['hospital_id_proof'];
    $hospital_id_proof_type = $row['hospital_id_proof_type']; // e.g., 'image/jpeg', 'application/pdf'

    if ($hospital_id_proof_type === 'application/pdf') {
        // If it's a PDF, create a link to the PDF file
        $pdf_src = 'data:application/pdf;base64,' . base64_encode($hospital_id_proof_data);
        $hospital_id_proof_display = "<a href='{$pdf_src}' target='_blank'>View PDF</a>";
    } else {
        // If it's an image (jpeg/png), create an <img> tag
        $hospital_id_proof_base64 = base64_encode($hospital_id_proof_data);
        $hospital_id_proof_display = "<img src='data:{$hospital_id_proof_type};base64,{$hospital_id_proof_base64}' alt='ID Image' style='max-width: 100px; max-height: 100px;'>";
    }

    // Display the row with sanitized data
    echo "<tr>
            <td>{$first_name}</td>
            <td>{$last_name}</td>
            <td>{$specialization}</td>
            <td>{$created_at}</td>
            <td>{$hospital_id_proof_display}</td>
          </tr>";

                            <button onclick=\"showModal('doctor', {$row['id']}, 'approved')\">Approve</button>
                            <button onclick=\"showModal('doctor', {$row['id']}, 'rejected')\">Reject</button>
                        </td>
                      </tr>";
            }
            ?>
        </tbody>
    </table>

    <!-- Modal HTML -->
    <div class="modal" id="confirmationModal">
        <div class="modal-content">
            <h3>Are you sure?</h3>
            <p id="modalMessage"></p>
            <button id="confirmYes">Yes</button>
            <button id="confirmNo">No</button>
        </div>
    </div>

    <script>
        let actionType = "";
        let recordId = "";
        let statusType = "";

        function showModal(type, id, status) {
            actionType = type;
            recordId = id;
            statusType = status;

            const modal = document.getElementById("confirmationModal");
            const message = document.getElementById("modalMessage");
            message.innerText = `Do you want to ${status === 'approved' ? 'approve' : 'reject'} this application?`;
            modal.style.display = "flex";
        }

        document.getElementById("confirmYes").onclick = function () {
            const modal = document.getElementById("confirmationModal");
            modal.style.display = "none";

            // Proceed with the AJAX request
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "update_status.php", true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.onload = function () {
                if (this.status === 200) {
                    alert(this.responseText);
                    location.reload();
                }
            };
            xhr.send(`type=${actionType}&id=${recordId}&status=${statusType}`);
        };

        document.getElementById("confirmNo").onclick = function () {
            const modal = document.getElementById("confirmationModal");
            modal.style.display = "none";
        };

        // Close the modal when clicking outside
        window.onclick = function (event) {
            const modal = document.getElementById("confirmationModal");
            if (event.target === modal) {
                modal.style.display = "none";
            }
        };
    </script>
</body>
</html>
