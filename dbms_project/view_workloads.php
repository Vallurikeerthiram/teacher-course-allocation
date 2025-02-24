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
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $dept = $_GET['dept'];

    // Fetch workloads based on department
    $sql = "SELECT * FROM assigned_courses ac
            JOIN teachers t ON ac.teacher_id = t.id
            JOIN cs_courses cc ON ac.course_id = cc.id
            WHERE cc.dept = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $dept);
    $stmt->execute();
    $result = $stmt->get_result();

    // Display the workloads
    echo "<h1>Workloads for $dept Department</h1>";
    echo "<table border='1'><tr><th>Teacher Name</th><th>Course Name</th><th>Priority</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['username']}</td>
                <td>{$row['course_name']}</td>
                <td>{$row['priority']}</td>
              </tr>";
    }
    
    echo "</table>";

    // Close the statement and connection
    $stmt->close();
    $conn->close();
}
?>
