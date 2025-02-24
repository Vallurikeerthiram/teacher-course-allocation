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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HOD Dashboard</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to your CSS file -->
    <style>
        /* CSS for styling */
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #ffffff; /* White background for a clean look */
            color: #333; /* Dark text for contrast */
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            text-align: center;
        }
        .header {
            background-color: #A3113E; /* Brand Color */
            color: white;
            padding: 20px;
            border-radius: 0px 0px 8px 8px;
            width: 100%;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0px;
            height:60px;
        }
        .header h1 {
            margin: 0;
            font-size: 2.5em;
            font-weight: bold;
        }
        .menu {
            margin-top: 80px; /* Add margin to avoid overlap with the header */
            background-color: #f4f4f4; /* Light background for the menu */
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 600px; /* Limit width for better readability */
        }
        .menu h2 {
            color: #A3113E; /* Brand Color */
            margin-bottom: 15px;
            font-size: 1.8em;
        }
        .menu a {
            display: block;
            padding: 15px;
            color: #A3113E; /* Brand Color */
            text-decoration: none;
            margin: 10px 0;
            border: 2px solid #A3113E; /* Brand Color */
            border-radius: 5px;
            transition: background-color 0.3s, color 0.3s;
        }
        .menu a:hover {
            background-color: #A3113E; /* Brand Color */
            color: white; /* Change text color on hover */
        }
        .dashboard {
            
            margin: 20px 0; /* Space between menu and dashboard */
            padding: 20px;
            background-color: #ffffff; /* White background for dashboard */
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 600px; /* Limit width for better readability */
        }
        .dashboard h2 {
            color: #A3113E; /* Brand Color */
            margin-bottom: 10px;
            font-size: 1.6em;
        }
        .dashboard p {
            font-size: 1.2em;
            line-height: 1.5;
            color: #666; /* Slightly darker text for better readability */
        }
        .back-home {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #A3113E; /* Brand Color */
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            display: inline-block; /* Align properly */
        }
        .back-home:hover {
            background-color: #F44336; /* Lighter shade on hover */
        }
    </style>
</head>
<body>

<div class="header">
    <br><br><br>
</div>

<div class="menu">
<br><br><br>
    <h2>Menu</h2>
    <a href="assign_courses.php">Assign Courses</a>
    <a href="assign_labs.php">Assign Labs</a>
    <a href="hod_lab_management.php">View Lab Allocation Status</a>
    <a href="hod_courses_management.php">View Courses Allocation Status</a>
    <a href="view_available_workload.php">View Available Workload</a>
    <a href="add_courses.php">Add Courses</a>
    <a href="add_lab.php">Add Labs</a>
    <a href="publish_allocations.php">Publish [Under Development]</a>
    <a href="logout.php">Logout</a>
</div>

<div class="dashboard">
    <h2>Dashboard Overview</h2>
    <p>Manage courses, view workloads, and assignments efficiently.</p>
    <p>Use the menu to navigate through the available options.</p>
</div>


</body>
</html>

<?php
$conn->close();
?>
