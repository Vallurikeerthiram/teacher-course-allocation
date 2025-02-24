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

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $dept = $_POST['dept'];
    $course_code = $_POST['course_code'];
    $course_name = $_POST['course_name'];
    $credits = $_POST['credits'];
    $year = $_POST['year'];
    $sections = $_POST['section']; // Multivariable input for sections (comma-separated string)

    // Determine which table to insert into
    if ($dept === 'cs') {
        $sql = "INSERT INTO cs_courses (course_code, course_name, credits, year) VALUES (?, ?, ?, ?)";
    } elseif ($dept === 'ai') {
        $sql = "INSERT INTO ai_courses (course_code, course_name, credits, year) VALUES (?, ?, ?, ?)";
    } elseif ($dept === 'elective') {
        $sql = "INSERT INTO electives_courses (course_code, course_name, credits, year) VALUES (?, ?, ?, ?)";
    } else {
        echo "Invalid department selected.";
        exit();
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssis", $course_code, $course_name, $credits, $year);
    
    if ($stmt->execute()) {
        // Split sections and insert into sections table
        $sectionArray = explode(',', $sections); // Split sections by comma
        foreach ($sectionArray as $section) {
            $section = trim($section); // Trim whitespace around sections
            $sql_section = "INSERT INTO sections (course_code, section) VALUES (?, ?)";
            $stmt_section = $conn->prepare($sql_section);
            $stmt_section->bind_param("ss", $course_code, $section);
            $stmt_section->execute();
        }
        
        echo "<script>alert('Course and sections added successfully!');</script>";
    } else {
        echo "<script>alert('Error adding course: " . $stmt->error . "');</script>";
    }
    
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Course</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to your main CSS file -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        h1 {
            color: #A3113E; /* Your brand color */
            text-align: center;
        }
        form {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            margin: 20px auto;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        input[type="text"], input[type="number"], select {
            width: calc(100% - 20px);
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        input[type="submit"] {
            background-color: #A3113E; /* Your brand color */
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        input[type="submit"]:hover {
            background-color: #8A0E31; /* Darker shade for hover effect */
        }
        .return-home {
            text-align: center;
            margin-top: 20px;
        }
        .return-home a {
            padding: 10px 15px;
            background-color: #A3113E; /* Your brand color */
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .return-home a:hover {
            background-color: #8A0E31; /* Darker shade for hover effect */
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
            <a href="view_available_workload.php" class="navbar-link">Available Workload</a>
            <a href="add_lab.php" class="navbar-link">Add Labs</a>
        </div>
    </div><br><br><br><br>
    <h1>Add Course</h1>
    <form method="post" action="add_courses.php">
        <label for="dept">Department:</label>
        <select name="dept" required>
            <option value="">Select Department</option>
            <option value="cs">Computer Science</option>
            <option value="ai">Artificial Intelligence</option>
            <option value="elective">Elective</option>
        </select>
        <label for="course_code">Course Code:</label>
        <input type="text" name="course_code" required>
        <label for="course_name">Course Name:</label>
        <input type="text" name="course_name" required>
        <label for="credits">Credits:</label>
        <input type="number" name="credits" required min="1">
        <label for="year">Year:</label>
        <input type="number" name="year" required min="1" max="4">
        <label for="section">Section(s):</label>
        <input type="text" name="section" placeholder="Enter sections separated by commas (e.g., A,B,C)" required>
        <input type="submit" value="Add Course">
    </form>

    <div class="return-home">
        <a href="hod_home.php">Return to Home</a>
    </div>
</body>
</html>
