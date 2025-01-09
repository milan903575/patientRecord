<?php
// Database connection
include '../connection.php';
session_start();

$patient_id = $_SESSION['user_id']; // Assuming this is set when the user logs in.

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['question'])) {
        $content = $conn->real_escape_string($_POST['question']);
        $conn->query("INSERT INTO faq (patient_id, content) VALUES ($patient_id, '$content')");
    } elseif (isset($_POST['reply'], $_POST['question_id'])) {
        $content = $conn->real_escape_string($_POST['reply']);
        $question_id = intval($_POST['question_id']);
        $parent_id = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : null;
        $conn->query("INSERT INTO faq (parent_id, patient_id, content) VALUES ($parent_id, $patient_id, '$content')");
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Fetch questions
$questions = $conn->query("SELECT f.id, f.content, f.created_at, CONCAT(p.first_name, ' ', p.last_name) AS patient_name
                           FROM faq f
                           JOIN patients p ON f.patient_id = p.id
                           WHERE f.parent_id IS NULL
                           ORDER BY f.created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dynamic FAQ</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f7f8fa;
            margin: 0;
            padding: 20px;
        }
        .faq-container {
            max-width: 800px;
            margin: auto;
        }
        .card {
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            padding: 15px;
        }
        .card h3 {
            margin: 0;
            color: #333;
        }
        .reply-list {
            margin-top: 10px;
            border-top: 1px solid #e0e0e0;
            padding-top: 10px;
        }
        .reply-item {
            margin-bottom: 10px;
            padding: 5px;
            border-left: 4px solid #007BFF;
            background: #f0f4ff;
            border-radius: 5px;
            padding-left: 10px;
        }
        .nested-reply {
            margin-left: 20px;
            background: #f9f9f9;
        }
        textarea {
            width: calc(100% - 20px);
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 10px;
            resize: none;
        }
        button {
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            background: #28a745;
            color: white;
            cursor: pointer;
        }
        button:hover {
            background: #218838;
        }
        .reply-btn {
            margin-top: 5px;
        }
        .hidden-reply {
            display: none;
            margin-top: 10px;
        }
        .show-hide-btn {
            background-color: #007BFF;
            color: white;
            border-radius: 5px;
            border: none;
            padding: 8px 15px;
            cursor: pointer;
        }
        .show-hide-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
<div class="faq-container">
    <h1>Frequently Asked Questions</h1>
    <form method="POST">
        <textarea name="question" rows="2" placeholder="Ask a question..." required></textarea><br>
        <button type="submit">Submit Question</button>
    </form>
    <br>
    <?php while ($question = $questions->fetch_assoc()): ?>
        <div class="card">
            <h3><?= htmlspecialchars($question['content']) ?></h3>
            <p>Asked by: <?= htmlspecialchars($question['patient_name']) ?> on <?= $question['created_at'] ?></p>
            <button onclick="toggleReplyInput('reply-box-<?= $question['id'] ?>')" class="reply-btn">Replying to Question</button>
            <div class="hidden-reply" id="reply-box-<?= $question['id'] ?>">
                <form method="POST">
                    <textarea 
                        name="reply" 
                        rows="2" 
                        placeholder="Replying to: <?= htmlspecialchars($question['content']) ?>" 
                        required
                    ></textarea>
                    <input type="hidden" name="question_id" value="<?= $question['id'] ?>">
                    <input type="hidden" name="parent_id" value="<?= $question['id'] ?>">
                    <button type="submit">Submit Reply</button>
                </form>
            </div>

            <!-- Show/Hide Button for Replies -->
            <button class="show-hide-btn" onclick="toggleReplies(<?= $question['id'] ?>)" id="toggle-btn-<?= $question['id'] ?>">Show All Replies</button>
            
            <div class="reply-list" id="replies-<?= $question['id'] ?>" style="display: none;">
                <?php 
                $replies = $conn->query("SELECT f.id, f.content, CONCAT(p.first_name, ' ', p.last_name) AS patient_name, f.parent_id
                    FROM faq f
                    JOIN patients p ON f.patient_id = p.id
                    WHERE f.parent_id = {$question['id']}
                    ORDER BY f.created_at ASC");
                while ($reply = $replies->fetch_assoc()):
                    displayReply($conn, $reply, 1); // Recursive function to display nested replies
                endwhile;
                ?>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<script>
    // Toggle reply input box visibility
    function toggleReplyInput(id) {
        const replyBox = document.getElementById(id);
        replyBox.style.display = replyBox.style.display === 'block' ? 'none' : 'block';
    }

    // Toggle visibility of all replies and change button text
    function toggleReplies(questionId) {
        const replyList = document.getElementById(`replies-${questionId}`);
        const toggleBtn = document.getElementById(`toggle-btn-${questionId}`);
        if (replyList.style.display === 'block') {
            replyList.style.display = 'none';
            toggleBtn.textContent = 'Show All Replies';
        } else {
            replyList.style.display = 'block';
            toggleBtn.textContent = 'Hide All Replies';
        }
    }
    
    // Function to display nested replies
    function displayReply(conn, reply, depth) {
        const nestedReplies = conn.query(`SELECT * FROM faq WHERE parent_id = ${reply['id']}`);
        
        let replyHtml = `
            <div class="reply-item" style="margin-left: ${depth * 20}px">
                <p><strong>${reply['patient_name']}:</strong> ${reply['content']}</p>
                <button onclick="toggleReplyInput('reply-box-${reply['id']}')" class="reply-btn">Replying to This Reply</button>
                <div class="hidden-reply" id="reply-box-${reply['id']}">
                    <form method="POST">
                        <textarea name="reply" rows="2" placeholder="Replying to: ${reply['content']}" required></textarea>
                        <input type="hidden" name="question_id" value="${reply['id']}">
                        <input type="hidden" name="parent_id" value="${reply['id']}">
                        <button type="submit">Submit Reply</button>
                    </form>
                </div>
            </div>
        `;

        if (nestedReplies && nestedReplies.length > 0) {
            nestedReplies.forEach(nestedReply => {
                displayReply(conn, nestedReply, depth + 1);
            });
        }

        document.getElementById(`replies-${reply['parent_id']}`).innerHTML += replyHtml;
    }
</script>

</body>
</html>
