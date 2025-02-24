<?php
session_start();
include('db_connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $email = $_POST['email'];
    $new_password = $_POST['new_password'];

    // Query to check if the user ID and email match
    $sql = "SELECT * FROM teachers WHERE id = '$user_id' AND email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Update the password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT); // Hash the new password
        $update_sql = "UPDATE teachers SET password = '$hashed_password' WHERE id = '$user_id'";
        if ($conn->query($update_sql) === TRUE) {
            $success_message = "Password has been reset successfully. You can now log in.";
        } else {
            $error_message = "Error updating password. Please try again.";
        }
    } else {
        $error_message = "User ID and email do not match.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
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
            padding: 30px; /* Increased padding for better spacing */
            border-radius: 10px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 400px;
            text-align: center;
            transition: box-shadow 0.3s ease, transform 0.3s ease; /* Animation for scaling */
            animation: fadeIn 0.5s; /* Fade-in animation */
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
            animation: slideIn 0.5s; /* Text slide-in animation */
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
        input[type="password"],
        input[type="email"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box; /* Prevent overflow */
            transition: border-color 0.3s ease, box-shadow 0.3s ease; /* Animation for border and shadow */
        }

        input:focus {
            border-color: #A3113E; /* Highlight border on focus */
            outline: none;
            box-shadow: 0 0 5px rgba(163, 17, 62, 0.5); /* Glow effect on focus */
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
            transition: background-color 0.3s ease, transform 0.3s ease; /* Animation for scaling */
        }

        button:hover {
            background-color: #9A0F2D; /* Darker shade on hover */
            transform: scale(1.02); /* Slight scaling on hover */
        }

        .error {
            color: red;
            margin-top: 10px;
        }

        .success {
            color: green;
            margin-top: 10px;
        }

        .reset-password {
            margin-top: 15px;
            cursor: pointer;
            color: #A3113E;
            text-decoration: underline;
            transition: color 0.3s ease; /* Color change on hover */
        }

        .reset-password:hover {
            color: #9A0F2D; /* Darker shade on hover */
        }

        /* Media Queries for Mobile Devices */
        @media (max-width: 600px) {
            body {
                padding: 10px;
            }
            .container {
                padding: 20px; /* Adjust padding for mobile */
            }
            button {
                font-size: 14px; /* Smaller button text */
            }
        }

    </style>
</head>
<body>
    <div class="container">
        <h2>Reset Password</h2>
        <form method="POST" action="">
            <input type="text" name="user_id" placeholder="User ID" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="new_password" placeholder="New Password" required>
            <button type="submit">Reset Password</button>
            <?php if (isset($error_message)) echo "<p class='error'>$error_message</p>"; ?>
            <?php if (isset($success_message)) echo "<p class='success'>$success_message</p>"; ?>
        </form>
    </div>
</body>
</html>
