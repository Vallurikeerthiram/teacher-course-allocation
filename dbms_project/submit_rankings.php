<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'cs') {
    header('Location: login.php');
    exit();
}

// Check if the request method is POST and contains the necessary data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the POST data from the fetch request
    $data = json_decode(file_get_contents('php://input'), true);

    // Check if the required data is present
    if (isset($data['cs']) && isset($data['electives'])) {
        $cs_courses = $data['cs'];
        $elective_courses = $data['electives'];

        // Connect to the database
        $conn = new mysqli('localhost', 'root', '', 'dbms_project');
        if ($conn->connect_error) {
            die('Connection failed: ' . $conn->connect_error);
        }

        // Get the logged-in user's username
        $username = $_SESSION['username'];  // Assuming you have stored the username in the session

        // Prepare and execute the queries to insert/update course rankings in teacher_selections
        $stmt = $conn->prepare('INSERT INTO teacher_selections (teacher_username, course_code, priority, department) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE priority = VALUES(priority)');

        // Rank CS courses
        foreach ($cs_courses as $priority => $course_code) {
            $department = 'CS';  // Assuming these are CS department courses
            $stmt->bind_param('ssis', $username, $course_code, $priority + 1, $department);
            $stmt->execute();
        }

        // Rank Elective courses
        foreach ($elective_courses as $priority => $course_code) {
            $department = 'Elective';  // Assuming these are Elective department courses
            $stmt->bind_param('ssis', $username, $course_code, $priority + 1, $department);
            $stmt->execute();
        }

        $stmt->close();
        $conn->close();

        // Return a success response
        echo json_encode(['success' => true]);
    } else {
        // Return an error if required data is missing
        echo json_encode(['success' => false, 'message' => 'Invalid course data']);
    }
} else {
    // Return an error for invalid request method
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
