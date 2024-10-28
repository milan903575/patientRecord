<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <style>
        @import url('https://fonts.googleapis.com/css?family=Raleway:400,700');

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Raleway, sans-serif;
        }

        body {
            background: linear-gradient(90deg, #a0c4ff, #83aefd);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            background: linear-gradient(90deg, #83aefd, #6fa1ff);
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0px 0px 24px #5c8cf1;
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        h2 {
            margin-bottom: 20px;
            color: #fff;
            font-weight: 700;
        }

        form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        label {
            margin-bottom: 10px;
            color: #fff;
            font-weight: 700;
            text-align: left;
            width: 100%;
        }

        input[type="email"] {
            padding: 10px;
            margin-bottom: 20px;
            border: none;
            border-radius: 5px;
            width: calc(100% - 20px);
            max-width: 300px;
            outline: none;
            font-size: 16px;
        }

        button {
            padding: 12px 24px;
            background-color: #fff;
            border: none;
            border-radius: 5px;
            color: #4C489D;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.2s;
            font-size: 16px;
            outline: none;
        }

        button:hover {
            background-color: #D4D3E8;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Forgot Password</h2>
        <form action="send_reset_link.php" method="POST">
            <label for="email">Enter your email address:</label>
            <input type="email" id="email" name="email" required>
            <button type="submit">Send Reset Link</button>
        </form>
    </div>
</body>
</html>
