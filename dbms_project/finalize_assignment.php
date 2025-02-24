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
    $teacher_id = $_POST['teacher_id'];
    $course_code = $_POST['course_code'];
    $action = $_POST['action']; // Determine if it's an approve or reject action
    $selected_sections = isset($_POST['sections']) ? $_POST['sections'] : [];
    $selected_years = isset($_POST['years']) ? $_POST['years'] : [];

    // Fetch course name and credits based on the course code
    $course_name = "";
    $credits = 0;

    // Determine which table to query for course name and credits
    $sql = "SELECT course_name, credits FROM cs_courses WHERE course_code = ?
            UNION ALL
            SELECT course_name, credits FROM ai_courses WHERE course_code = ?
            UNION ALL
            SELECT course_name, credits FROM electives_courses WHERE course_code = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $course_code, $course_code, $course_code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $course_name = $row['course_name'];
        $credits = $row['credits'];
    } else {
        echo "Course not found.";
        exit();
    }
    $stmt->close();

    // Handle assignment or rejection of course
    if ($action === 'approve') {
        // Convert sections and years arrays into a comma-separated string
        $sections_str = implode(',', $selected_sections);
        $years_str = implode(',', $selected_years);

        // Insert into assigned_courses table
        $sql = "INSERT INTO assigned_courses (teacher_username, course_code, course_name, credits, year, section)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssiis", $teacher_id, $course_code, $course_name, $credits, $years_str, $sections_str);

        if (!$stmt->execute()) {
            echo "Error inserting into assigned_courses: " . $stmt->error;
            exit();
        }
        $stmt->close();

        // Remove from teacher_selections
        $sql = "DELETE FROM teacher_selections WHERE teacher_username = ? AND course_code = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $teacher_id, $course_code);
        if (!$stmt->execute()) {
            echo "Error deleting from teacher_selections: " . $stmt->error;
            exit();
        }

        // Check if any rows were affected (deleted)
        if ($stmt->affected_rows > 0) {
            // Update teacher's workload
            $sql = "UPDATE teachers SET available_hours = available_hours - ? WHERE username = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $credits, $teacher_id);
            if (!$stmt->execute()) {
                echo "Error updating available_hours: " . $stmt->error;
                exit();
            }
            echo "Course assigned successfully!";
        } else {
            echo "No matching selection found to delete.";
        }
        $stmt->close();

    } elseif ($action === 'reject') {
        // Remove from teacher_selections
        $sql = "DELETE FROM teacher_selections WHERE teacher_username = ? AND course_code = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $teacher_id, $course_code);
        if (!$stmt->execute()) {
            echo "Error deleting from teacher_selections: " . $stmt->error;
            exit();
        }

        // Check if any rows were affected (deleted)
        if ($stmt->affected_rows > 0) {
            echo "Teacher's selection rejected successfully.";
        } else {
            echo "No matching selection found to delete.";
        }
        $stmt->close();
    }
}
$conn->close();
?>
