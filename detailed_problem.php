<?php
include 'connection.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'patient') {
    header("Location: login.html");
    exit;
}

$patient_id = $_SESSION['user_id'];
$pdf_path = isset($_SESSION['pdf_path']) ? $_SESSION['pdf_path'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Submit Problem</title>
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
        form input[type="email"],
        form input[type="password"],
        form input[type="date"],
        form select,
        form textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        form input[type="file"] {
            margin-bottom: 10px;
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
        #hospital_results {
            position: absolute;
            background: #fff;
            border: 1px solid #ccc;
            width: calc(100% - 20px);
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
        }
        .hospital-option {
            padding: 10px;
            cursor: pointer;
        }
        .hospital-option:hover {
            background: #f4f4f4;
        }
        .ai-solution {
            margin-top: 20px;
            padding: 15px;
            background: #e7f3fe;
            border: 1px solid #b3d7ff;
            border-radius: 5px;
            color: #31708f;
        }
        .caution {
            color: #d9534f;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="well">
        <h2>Submit Your Problem</h2>
        <form action="submit_detailed_problem.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="pdf_path" value="<?php echo htmlspecialchars($pdf_path); ?>">
            <div class="form-group">
                <label for="problem">Problem</label>
                <input type="text" name="problem" id="problem" required>
            </div>
            <div class="form-group">
                <label for="problem_description">Problem Description</label>
                <textarea name="problem_description" id="problem_description" required></textarea>
            </div>
            <div class="form-group">
                <label for="current_medication">Current Medication</label>
                <textarea name="current_medication" id="current_medication" required></textarea>
            </div>
            <div class="form-group">
                <label for="hospital_search">Search Hospital</label>
                <input type="text" name="hospital_search" id="hospital_search" placeholder="Search by name or zip code">
                <input type="hidden" name="hospital_id" id="hospital_id">
                <div id="hospital_results"></div>
            </div>
            <div class="form-group">
                <label for="video">Upload Video</label>
                <input type="file" name="video" id="video">
            </div>
            <div class="form-group">
                <label for="ai_solution">AI Generated Solution</label>
                <p class="caution">Caution: Use for minor problems. AI can make mistakes.</p>
                <button type="button" id="generate_solution">Get AI Generated Solution</button>
                <div class="ai-solution" id="ai_solution_display"></div>
            </div>
            <input type="submit" value="Submit">
        </form>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
    $(document).ready(function() {
        $('#hospital_search').on('input', function() {
            var query = $(this).val();
            if (query.length > 2) {
                $.ajax({
                    url: 'search_hospitals.php',
                    method: 'GET',
                    data: { query: query },
                    success: function(data) {
                        $('#hospital_results').html(data);
                    }
                });
            } else {
                $('#hospital_results').empty();
            }
        });

        $(document).on('click', '.hospital-option', function() {
            var hospital_id = $(this).data('id');
            var hospital_name = $(this).text();
            $('#hospital_search').val(hospital_name);
            $('#hospital_id').val(hospital_id);
            $('#hospital_results').empty();
        });

        $('#generate_solution').on('click', function() {
            var problem_description = $('#problem_description').val();
            if (problem_description.length > 0) {
                $.ajax({
                    url: 'get_ai_solution.php',
                    method: 'POST',
                    data: { problem_description: problem_description },
                    success: function(data) {
                        $('#ai_solution_display').text(data);
                    }
                });
            } else {
                alert('Please enter a problem description.');
            }
        });
    });

    async function saveAsPDF() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        const formContent = document.getElementById('form-content');

        // Hide buttons before saving
        document.querySelector('.form-footer').style.display = 'none';
        document.querySelectorAll('input').forEach(input => {
            const div = document.createElement('div');
            div.textContent = input.value;
            input.parentNode.replaceChild(div, input);
        });
        document.querySelector('button[onclick="addMedicineField()"]').style.display = 'none';

        doc.html(formContent, {
            callback: async function (doc) {
                const pdfBlob = doc.output('blob');
                const formData = new FormData();
                formData.append('pdf', pdfBlob, 'medical_receipt.pdf');

                try {
                    const response = await fetch('save_pdf.php', {
                        method: 'POST',
                        body: formData
                    });

                    if (response.ok) {
                        window.location.href = 'submit_problem.php';
                    } else {
                        console.error('Failed to save PDF');
                    }
                } catch (error) {
                    console.error('Error:', error);
                }
            },
            x: 10,
            y: 10
        });
    }
</script>
</body>
</html>
