<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'cs') {
    header('Location: login.php');
    exit();
}

include 'db_connection.php';

// Get selected CS courses and priorities
$selected_cs_courses = $_POST['cs_courses'] ?? [];
$cs_priorities = $_POST['cs_priorities'] ?? [];

$priorities = [];
$errors = [];

// Validate priorities
foreach ($selected_cs_courses as $course_code) {
    $priority = $cs_priorities[$course_code] ?? null;
    
    // Check if priority is valid
    if ($priority !== null) {
        if (in_array($priority, $priorities)) {
            $errors[] = "Duplicate priority '{$priority}' for course '{$course_code}' is not allowed.";
        } else {
            $priorities[] = $priority;
        }
    }
}

// Proceed if there are no errors
if (empty($errors)) {
    // Save the rankings to the database
    foreach ($selected_cs_courses as $course_code) {
        $priority = $cs_priorities[$course_code] ?? null;
        if ($priority) {
            // Insert into database logic here
            // For example: save to a 'course_rankings' table
        }
    }
    // Redirect or display success message
} else {
    // Handle errors (e.g., display them on the page)
    foreach ($errors as $error) {
        echo "<p style='color:red;'>$error</p>";
    }
}
?>
