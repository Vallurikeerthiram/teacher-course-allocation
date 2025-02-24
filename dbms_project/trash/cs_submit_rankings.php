<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

include 'db_connection.php';

// Get the JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!empty($data['selections'])) {
    $username = $_SESSION['username'];

    foreach ($data['selections'] as $selection) {
        $course_code = $selection['course_code'];
        $priority = $selection['priority'];
        $department = $selection['department'];

        // Insert into teacher_selections table
        $stmt = $conn->prepare("INSERT INTO teacher_selections (teacher_username, course_code, priority, department) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssis", $username, $course_code, $priority, $department);
        $stmt->execute();
    }

    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => "No selections made"]);
}
?>
