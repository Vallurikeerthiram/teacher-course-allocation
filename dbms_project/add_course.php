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

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_code = $_POST['course_code'];
    $course_name = $_POST['course_name'];
    $credits = $_POST['credits'];
    $course_type = $_POST['course_type']; // Either 'cs', 'ai', or 'elective'
    $year = $_POST['year'];
    $sections = explode(',', $_POST['section']); // Split sections into an array

    // Assign the department based on course_type
    $dept = ($course_type == 'ai') ? 'AI' : (($course_type == 'cs') ? 'CS' : 'Elective');

    // Prepare SQL query to insert into the appropriate table
    if ($course_type == "ai") {
        $sql = "INSERT INTO ai_courses (course_code, course_name, credits, dept, year, section) VALUES (?, ?, ?, ?, ?, ?)";
    } elseif ($course_type == "cs") {
        $sql = "INSERT INTO cs_courses (course_code, course_name, credits, dept, year, section) VALUES (?, ?, ?, ?, ?, ?)";
    } elseif ($course_type == "elective") {
        $sql = "INSERT INTO electives_courses (course_code, course_name, credits, dept, year, section) VALUES (?, ?, ?, ?, ?, ?)";
    } else {
        echo "Invalid course type selected.";
        exit();
    }

    // Prepare and bind the query
    $stmt = $conn->prepare($sql);

    // Loop through each section and execute the insert statement
    foreach ($sections as $section) {
        $section = trim($section); // Trim whitespace from each section
        $stmt->bind_param("ssissi", $course_code, $course_name, $credits, $dept, $year, $section);

        // Execute the query for each section
        if (!$stmt->execute()) {
            echo "Error: " . $stmt->error;
        }
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();

    // Redirect to HOD home page after successful insertion
    echo "New course added successfully!";
    header('Location: hod_home.php');
    exit();
} else {
    echo "Invalid request method.";
}
?>
