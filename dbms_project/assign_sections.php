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

// Initialize variables
$sections = [];
$course_code = $_SESSION['course_code'] ?? '';
$course_name = $_SESSION['course_name'] ?? '';
$year = null;
$teachers_info = [];

// Fetch sections based on course code
if (!empty($course_code)) {
    // Get available sections from the sections table
    $sql = "SELECT id, section FROM sections WHERE course_code = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $course_code);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $sections[] = $row;
    }
    $stmt->close();

    // Get the course year from the relevant courses table
    $sql = "SELECT year FROM cs_courses WHERE course_code = ? LIMIT 1"; // Change this if needed for AI or electives
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $course_code);
    $stmt->execute();
    $stmt->bind_result($year);
    $stmt->fetch();
    $stmt->close();

    // Fetch teachers and their available workloads based on course code from teacher_selections
    $sql = "SELECT ts.teacher_username, t.available_hours 
            FROM teacher_selections ts 
            JOIN teachers t ON ts.teacher_username = t.username 
            WHERE ts.course_code = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $course_code);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $teachers_info[] = $row;
    }
    $stmt->close();
}

// Handle form submission for assigning teachers to sections
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['assign'])) {
    $entered_sections = $_POST['sections'] ?? '';

    // Split the entered sections by commas and trim whitespace
    $sections_array = array_map('trim', explode(',', $entered_sections));

    // Get the course credits to calculate total hours for the assignment
    $sql = "SELECT credits FROM cs_courses WHERE course_code = ? LIMIT 1"; // Adjust if needed for AI or electives
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $course_code);
    $stmt->execute();
    $stmt->bind_result($credits);
    $stmt->fetch();
    $stmt->close();

    // Calculate total hours based on number of sections and credits
    $total_hours = count($sections_array) * $credits;

    // Loop through each entered section and assign teachers
    foreach ($sections_array as $section) {
        foreach ($teachers_info as $teacher) {
            // Insert assignment into the assigned_courses table
            $sql = "INSERT INTO assigned_courses (teacher_username, course_code, course_name, year, section)
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                echo "Error preparing statement: " . $conn->error;
                continue; // Skip to next iteration
            }
            $stmt->bind_param("sssss", $teacher['teacher_username'], $course_code, $course_name, $year, $section);
            if (!$stmt->execute()) {
                echo "Error executing statement: " . $stmt->error;
            }
            $stmt->close();

            // Update the available hours of the teacher
            $new_available_hours = $teacher['available_hours'] - $total_hours;
            if ($new_available_hours < 0) {
                echo "Not enough available hours for teacher: " . htmlspecialchars($teacher['teacher_username']);
                continue; // Skip if not enough available hours
            }
            $update_sql = "UPDATE teachers SET available_hours = ? WHERE username = ?";
            $update_stmt = $conn->prepare($update_sql);
            if (!$update_stmt) {
                echo "Error preparing update statement: " . $conn->error;
                continue; // Skip to next iteration
            }
            $update_stmt->bind_param("is", $new_available_hours, $teacher['teacher_username']);
            if (!$update_stmt->execute()) {
                echo "Error updating available hours for teacher: " . $teacher['teacher_username'] . " - " . $update_stmt->error;
            }
            $update_stmt->close();
        }
    }

    // Clear session data
    unset($_SESSION['course_code'], $_SESSION['course_name']);

    // Redirect to a confirmation page or back to the HOD home page
    header('Location: hod_home.php?success=1');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Sections to Teachers</title>
    <link rel="stylesheet" href="styles.css"> <!-- Include a CSS file for styling -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4; /* Light background for contrast */
            margin: 0;
            padding: 20px;
        }

        h1 {
            color: #A3113E; /* Your brand color */
            text-align: center;
        }

        form, .info {
            background: #fff; /* White background for the form */
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 600px; /* Limit the form width */
            margin: 20px auto; /* Center the form */
        }

        label {
            display: block; /* Make labels block elements */
            margin-bottom: 8px; /* Space below labels */
            font-weight: bold; /* Bold labels */
        }

        input[type="text"] {
            width: calc(100% - 20px); /* Full width minus padding */
            padding: 10px; /* Padding inside text box */
            margin-bottom: 15px; /* Space below text box */
            border: 1px solid #ccc; /* Light border */
            border-radius: 4px; /* Rounded corners */
        }

        input[type="submit"], .return-home a {
            background-color: #A3113E; /* Your brand color */
            color: #fff; /* White text for buttons */
            padding: 10px 15px; /* Padding for buttons */
            border: none; /* No border */
            border-radius: 10px; /* Rounded corners */
            text-decoration: none; /* Remove underline */
            transition: background-color 0.3s; /* Transition for hover effect */
            cursor: pointer; /* Pointer cursor on hover */
        }

        input[type="submit"]:hover, .return-home a:hover {
            background-color: #8A0E31; /* Darker shade for hover effect */
        }

        .return-home {
            text-align: center; /* Center the return link/button */
            margin-top: 20px; /* Space above return section */
        }
    </style>
</head>
<body>
    <h1>Assign Sections to Teachers for <?php echo htmlspecialchars($course_name); ?></h1>
    <div class="info">
        <h2>Available Sections for <?php echo htmlspecialchars($course_name); ?> (Year: <?php echo htmlspecialchars($year); ?>):</h2>
        <ul>
            <?php if (empty($sections)): ?>
                <li>No sections available for this course.</li>
            <?php else: ?>
                <?php foreach ($sections as $section): ?>
                    <li><?php echo htmlspecialchars($section['section']); ?></li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>

        <h2>Selected Teachers and Their Workloads:</h2>
        <ul>
            <?php if (empty($teachers_info)): ?>
                <li>No teachers available for assignment.</li>
            <?php else: ?>
                <?php foreach ($teachers_info as $teacher): ?>
                    <li><?php echo htmlspecialchars($teacher['teacher_username']); ?> - Available Hours: <?php echo htmlspecialchars($teacher['available_hours']); ?></li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>

    <form method="POST" action="">
        <label for="sections">Enter Sections (comma separated):</label>
        <input type="text" id="sections" name="sections" placeholder="e.g., Section A, Section B" required>
        <input type="submit" name="assign" value="Assign Sections">
    </form>

    <div class="return-home">
        <a href="hod_home.php">Return to HOD Home</a>
    </div>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
