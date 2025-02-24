<?php
session_start();

// Check if user is logged in as HOD
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'hod') {
    header('Location: login.php');
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = ""; // Your MySQL password
$dbname = "dbms_project"; // Replace with your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch available workloads
$teachers = [];
$sql = "SELECT username, available_hours, working_hours FROM teachers";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $teachers[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Workload of Teachers</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link your external CSS file -->
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        h1 {
            color: #A3113E; /* Your brand color */
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #A3113E; /* Your brand color */
            color: white;
        }

        tr:hover {
            background-color: #f1f1f1; /* Highlight on hover */
        }

        .container {
            max-width: 800px;
            margin: auto;
            background: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 8px;
        }

        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #A3113E; /* Your brand color */
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
            transition: background-color 0.3s;
        }

        .button:hover {
            background-color: #A52B4C; /* Darker shade on hover */
        }

        @media (max-width: 600px) {
            table, th, td {
                display: block;
                width: 100%;
            }

            th {
                position: absolute;
                top: -9999px;
                left: -9999px;
            }

            tr {
                margin-bottom: 15px;
            }
        }
        .navbar {
            position: fixed;
            font-family: 'Poppins', sans-serif;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(240, 240, 240, 0.95); /* Slight blackish white */
            backdrop-filter: blur(8px); /* Glass effect */
            display: flex;
            align-items: center; /* Center items vertically */
            padding: 10px 40px; /* Padding for sides */
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); /* Soft shadow */
            width: 100%;
            z-index: 100;
        }

        .navbar-logo {
            height: 70px; /* Logo height */
            margin-right: 40px; /* Margin to the right of the logo */
        }

        .navbar-links {
            flex-grow: 1;
            display: flex;
            justify-content: flex-end; /* Center links */
            font-family: 'Poppins', sans-serif;
        }

        .navbar-link {
            color: #333; /* Dark gray for contrast */
            text-align: center;
            padding: 10px 20px;
            font-family: 'Poppins', sans-serif;
            text-decoration: none;
            font-size: 16px;
            font-weight: 500;
            position: relative;
            transition: all 0.3s ease;
            margin: 0 5px; /* Spacing between links */
        }

        /* Hover Effect */
        .navbar-link:hover {
            color: #A3113E; /* Brand color on hover */
            background: rgba(163, 17, 62, 0.1); /* Light overlay on hover */
            transform: translateY(-3px); /* Slight lift effect */
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2); /* Enhanced shadow on hover */
        }

        /* Underline Slide-In Effect */
        .navbar-link::after {
            content: '';
            display: block;
            position: absolute;
            width: 0;
            height: 2px;
            background: #fff; /* White underline */
            bottom: -4px;
            left: 50%;
            transform: translateX(-50%);
            transition: width 0.4s ease;
        }

        .navbar-link:hover::after {
            width: 100%; /* Full width underline on hover */
        }

        /* Content Styling */
        .content {
            padding: 100px 20px; /* Padding below navbar */
            font-size: 18px;
        }
    </style>
</head>
<body>
    
<div class="navbar">
        <img src="amrita logo light mode.png" alt="Logo" class="navbar-logo">
        <div class="navbar-links">
            <a href="hod_home.php" class="navbar-link">Home</a>
            <a href="assign_courses" class="navbar-link">Assign Courses</a>
            <a href="assign_labs.php" class="navbar-link">Assign Labs</a>
            <a href="hod_lab_management.php" class="navbar-link">Lab Allocation Status</a>
            <a href="hod_courses_management.php" class="navbar-link">Course Allocation Status</a>
            <a href="add_courses.php" class="navbar-link">Add Courses</a>
            <a href="add_lab.php" class="navbar-link">Add Labs</a>
        </div>
    </div><br><br><br><br><br><br>
        <h1>Available Workload of Teachers</h1>
        <table>
            <tr>
                <th>Username</th>
                <th>Available Hours</th>
                <th>Working Hours</th>
            </tr>
            <?php foreach ($teachers as $teacher): ?>
                <tr>
                    <td><?php echo htmlspecialchars($teacher['username']); ?></td>
                    <td><?php echo htmlspecialchars($teacher['available_hours']); ?></td>
                    <td><?php echo htmlspecialchars($teacher['working_hours']); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <a href="hod_home.php" class="button">Back to HOD Home</a>
    </div>
</body>
</html>
