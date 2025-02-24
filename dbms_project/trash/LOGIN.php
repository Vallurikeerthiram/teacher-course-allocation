<?php
session_start();
include('db_connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Query to check if the username and password are valid
    $sql = "SELECT * FROM teachers WHERE username = ?"; // Using prepared statement for security
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Verify password (for production use password_hash and password_verify)
        if ($password === $row['password']) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_role'] = $row['role'];

            // Check if it's the first login
            if ($password === 'amma') {
                $_SESSION['first_login'] = true;
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $row['email'];
                header('Location: change_password.php'); // Redirect to change password page
                exit();
            } else {
                // Redirect based on role
                if ($row['role'] === 'hod') {
                    header('Location: hod_home.php');
                } elseif ($row['role'] === 'cs') {
                    header('Location: cs_home.php');
                } elseif ($row['role'] === 'ai') {
                    header('Location: ai_home.php');
                }
                exit();
            }
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "Invalid username.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            transition: background-color 0.3s ease;
        }

        .container {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 400px;
            text-align: center;
            transition: box-shadow 0.3s ease, transform 0.3s ease;
            animation: fadeIn 0.5s;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        h2 {
            color: #A3113E;
            margin-bottom: 20px;
            animation: slideIn 0.5s;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        input:focus {
            border-color: #A3113E;
            outline: none;
            box-shadow: 0 0 5px rgba(163, 17, 62, 0.5);
        }

        button {
            background-color: #A3113E;
            color: white;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        button:hover {
            background-color: #9A0F2D;
            transform: scale(1.02);
        }

        .error {
            color: red;
            margin-top: 10px;
        }

        .reset-password {
            margin-top: 15px;
            cursor: pointer;
            color: #A3113E;
            text-decoration: underline;
            transition: color 0.3s ease;
        }

        .reset-password:hover {
            color: #9A0F2D;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <form method="POST" action="">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
            <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        </form>
        <a class="reset-password" href="reset_password.php">Forgot Password?</a>
    </div>
</body>
</html>
