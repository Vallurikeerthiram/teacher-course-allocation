<?php
session_start();
header('Content-Type: application/json'); // Ensure the content type is JSON

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'cs') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

// Include your database connection file
include 'db_connection.php';

// Get the data from the POST request
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid data received.']);
    exit();
}

$cs_courses = isset($data['cs']) ? $data['cs'] : [];
$elective_courses = isset($data['electives']) ? $data['electives'] : [];

if (empty($cs_courses) && empty($elective_courses)) {
    echo json_encode(['success' => false, 'message' => 'No courses selected.']);
    exit();
}

$teacher_id = $_SESSION['user_id'];
$teacher_username = $_SESSION['user_name'];  // Assuming teacher username is stored in session

// Function to insert course data
function insert_course($conn, $table, $course_code, $course_name, $priority, $teacher_id, $teacher_username) {
    $stmt = $conn->prepare("INSERT INTO $table (teacher_id, course_name, priority, teacher_username, course_code) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param("isiss", $teacher_id, $course_name, $priority, $teacher_username, $course_code);
    return $stmt->execute();
}

try {
    // Insert CS courses
    $priority = 1;
    foreach ($cs_courses as $course_code) {
        $course_query = "SELECT course_name FROM cs_courses WHERE course_code = ?";
        $stmt = $conn->prepare($course_query);
        $stmt->bind_param("s", $course_code);
        $stmt->execute();
        $result = $stmt->get_result();
        $course_data = $result->fetch_assoc();

        if ($course_data) {
            $course_name = $course_data['course_name'];
            if (!insert_course($conn, 'cs', $course_code, $course_name, $priority, $teacher_id, $teacher_username)) {
                throw new Exception('Failed to insert CS course.');
            }
            $priority++;
        }
    }

    // Insert Elective courses
    $priority = 1;
    foreach ($elective_courses as $course_code) {
        $course_query = "SELECT course_name FROM electives_courses WHERE course_code = ?";
        $stmt = $conn->prepare($course_query);
        $stmt->bind_param("s", $course_code);
        $stmt->execute();
        $result = $stmt->get_result();
        $course_data = $result->fetch_assoc();

        if ($course_data) {
            $course_name = $course_data['course_name'];
            if (!insert_course($conn, 'electives', $course_code, $course_name, $priority, $teacher_id, $teacher_username)) {
                throw new Exception('Failed to insert elective course.');
            }
            $priority++;
        }
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
