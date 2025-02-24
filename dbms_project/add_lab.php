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

$message = "";
$course_data = [];
$sections = [];
$show_lab_details_form = false; // Track whether to show lab details form
$is_existing_course = false; // Track if it's an existing course
$course_code = ''; // Initialize course_code
$lab_name = ''; // Initialize lab_name

// Handle form submission for course code
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['course_code'])) {
    $course_code = $_POST['course_code'];

    // Check if course code matches existing courses
    $course_check_query = "
        SELECT 'CS' AS department, course_name, credits, year, course_code FROM cs_courses WHERE course_code = ?
        UNION ALL
        SELECT 'AI' AS department, course_name, credits, year, course_code FROM ai_courses WHERE course_code = ?
        UNION ALL
        SELECT 'Elective' AS department, course_name, credits, year, course_code FROM electives_courses WHERE course_code = ?
    ";

    $stmt = $conn->prepare($course_check_query);
    $stmt->bind_param("sss", $course_code, $course_code, $course_code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Fetch the course details
        $course_data = $result->fetch_assoc();
        $is_existing_course = true; // Mark as an existing course

        // Automatically set the lab name to the course name
        $lab_name = $course_data['course_name'];

        // Fetch sections associated with the course code
        $section_query = "SELECT section FROM sections WHERE course_code = ?";
        $stmt_sections = $conn->prepare($section_query);
        $stmt_sections->bind_param("s", $course_code);
        $stmt_sections->execute();
        $section_result = $stmt_sections->get_result();

        while ($row = $section_result->fetch_assoc()) {
            $sections[] = $row['section'];
        }

        $stmt_sections->close();
    } else {
        $is_existing_course = false; // It's a fully lab-oriented course
        $lab_name = ''; // Reset lab_name for new lab-oriented course
    }
}

// Handle submission of lab details
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['lab_name'])) {
    // Check if it's an existing course or a new lab-oriented course
    if ($is_existing_course) {
        // Existing course logic
        $lab_name = $_POST['lab_name']; // This will be set automatically
        $teachers_required = $_POST['teachers_required'];
        $lab_component = isset($_POST['lab_component']) ? $_POST['lab_component'] : ''; // Default to empty string
        $year = $course_data['year']; // Use the year from course data
        $department = $course_data['department']; // Default department
        $credits = $course_data['credits']; // Handle credits from existing course
    } else {
        // New lab-oriented course logic
        $lab_name = $_POST['lab_name'];
        $teachers_required = $_POST['teachers_required'];
        $lab_component = $_POST['lab_component'];
        $year = $_POST['year'];
        $department = 'Fully Lab-Oriented'; // Set department for new lab-oriented course
        $credits = $_POST['credits']; // User input for credits
    }

    // Insert lab into labs table
    $insert_lab_query = "
        INSERT INTO labs (lab_name, teachers_required, course_code, credits, lab_component, year, department) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ";

    $stmt = $conn->prepare($insert_lab_query);

    // Ensure that the parameters are always set before binding
    if (!$lab_component) {
        $lab_component = ''; // Set a default value if not provided
    }

    // Ensure the course_code is valid for the lab
    $course_code_to_insert = $is_existing_course ? $course_data['course_code'] : $course_code; // Use the course code entered by user for new lab

    // Bind parameters, ensuring all are set
    if ($stmt) {
        // Make sure to convert credits to string to avoid null
        $credits = $credits !== null ? $credits : ''; // Default to an empty string if credits are null
        $stmt->bind_param("sssssss", $lab_name, $teachers_required, $course_code_to_insert, $credits, $lab_component, $year, $department);

        if ($stmt->execute()) {
            $lab_id = $stmt->insert_id; // Get the inserted lab ID

            // Insert sections into lab_sections table
            // Make sure to handle section_names as a string
            $section_names = $_POST['section_names']; // Get the input directly
            if (is_string($section_names)) { // Ensure it's a string
                $section_names_array = explode(',', $section_names); // Split the sections input by commas
                foreach ($section_names_array as $section) {
                    $section = trim($section); // Trim whitespace
                    if (!empty($section)) { // Check if section is not empty
                        $insert_section_query = "INSERT INTO lab_sections (lab_id, section_name) VALUES (?, ?)"; // Correctly using 'section_name'
                        $stmt_section = $conn->prepare($insert_section_query);
                        $stmt_section->bind_param("is", $lab_id, $section);
                        $stmt_section->execute();
                        $stmt_section->close();
                    }
                }
            }

            $message = "Lab and sections added successfully!";
        } else {
            $message = "Error adding lab: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $message = "Error preparing statement: " . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Lab</title>
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
        max-width: 600px;
        margin: 20px auto;
    }

    label {
        display: block;
        margin-bottom: 8px;
        font-weight: bold;
    }

    select, input[type="submit"], input[type="text"], input[type="number"], input[type="password"] {
        width: calc(100% - 20px);
        padding: 10px;
        margin-bottom: 15px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    input[type="submit"] {
        background-color: #A3113E; /* Your brand color */
        color: #fff;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    input[type="submit"]:hover {
        background-color: #8A0E31; /* Darker shade for hover effect */
    }

    .teacher-card {
        border: 1px solid #ccc;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        background-color: white;
        transition: box-shadow 0.3s;
    }

    .teacher-card.selected {
        border-color: #A3113E; /* Highlight color */
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    }

    .teacher-info {
        flex: 1;
        margin-right: 10px;
    }

    .return-home {
        text-align: center;
        margin-top: 20px;
    }

    .return-home a, .return-home input {
        padding: 10px 15px;
        background-color: #A3113E; /* Your brand color */
        color: white;
        text-decoration: none;
        border-radius: 4px;
        transition: background-color 0.3s;
        border: none;
    }

    .return-home a:hover, .return-home input:hover {
        background-color: #8A0E31; /* Darker shade for hover effect */
    }

    .section-list {
        background: #fff;
        padding: 15px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
    }

    .section-item {
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
        margin-bottom: 5px;
        background-color: white;
        transition: background-color 0.3s, box-shadow 0.3s;
    }

    .section-item:hover {
        background-color: #f9f9f9;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .teacher-selection-list {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 20px;
    }

    .teacher-selection-item {
        background: #fff;
        border: 1px solid #ccc;
        border-radius: 8px;
        padding: 10px;
        cursor: pointer;
        transition: box-shadow 0.3s, transform 0.2s;
    }

    .teacher-selection-item.selected {
        border-color: #A3113E;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        transform: translateY(-2px);
    }

    .teacher-selection-item:hover {
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
    }

    .instructions {
        font-style: italic;
        color: #555;
        margin-bottom: 15px;
    }

    .error-message {
        color: red;
        font-weight: bold;
        margin-top: 10px;
    }

    .success-message {
        color: green;
        font-weight: bold;
        margin-top: 10px;
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
        .message {
            margin: 20px 0;
            color: #A3113E;
        }
    </style>
</head>
<body>
<div class="navbar">
        <img src="amrita logo light mode.png" alt="Logo" class="navbar-logo">
        <div class="navbar-links">
            <a href="hod_home.php" class="navbar-link">Home</a>
            <a href="assign_courses.php" class="navbar-link">Assign Courses</a>
            <a href="assign_labs.php" class="navbar-link">Assign Labs</a>
            <a href="hod_lab_management.php" class="navbar-link">Lab Allocation Status</a>
            <a href="hod_courses_management.php" class="navbar-link">Course Allocation Status</a>
            <a href="view_available_workload.php" class="navbar-link">Available Workload</a>
            <a href="add_courses.php" class="navbar-link">Add Courses</a>
            
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;;&nbsp;&nbsp;&nbsp;
        </div>

    </div>
    <br><br><br><br><br><br>
    <h1>Add Lab</h1>
    <form method="post" action="">
        <input type="text" name="course_code" placeholder="Course Code" required>
        <input type="submit" value="Check Course">
    </form>

    <?php if ($is_existing_course): ?>
        <form method="post" action="">
            <input type="hidden" name="course_code" value="<?= htmlspecialchars($course_code); ?>">
            <h2>Course Details</h2>
            <p>Course Name: <?= htmlspecialchars($lab_name); ?></p>
            <p>Credits: <?= htmlspecialchars($course_data['credits'] ?? 'N/A'); ?></p>
            <p>Year: <?= htmlspecialchars($course_data['year'] ?? 'N/A'); ?></p>
            <p>Department: <?= htmlspecialchars($course_data['department'] ?? 'N/A'); ?></p>
            <input type="hidden" name="lab_name" value="<?= htmlspecialchars($lab_name); ?>"> <!-- Automatically set lab name -->
            <input type="number" name="teachers_required" placeholder="Number of Teachers Required" required>
            <p>Sections Available: <?= implode(', ', $sections); ?></p>
            <input type="text" name="lab_component" placeholder="Lab Component (hours per week)" required>
            <input type="number" name="year" value="<?= htmlspecialchars($course_data['year'] ?? ''); ?>" required>
            <input type="number" name="credits" value="<?= htmlspecialchars($course_data['credits'] ?? ''); ?>" required>
            <input type="text" name="section_names" placeholder="Enter Sections (comma-separated)" required>
            <input type="submit" value="Add Lab">
        </form>
    <?php elseif ($_SERVER["REQUEST_METHOD"] == "POST" && !$is_existing_course): ?>
        <form method="post" action="">
            <h2>New Lab-Oriented Course (<?= htmlspecialchars($course_code); ?>)</h2>
            <input type="hidden" name="course_code" value="<?= htmlspecialchars($course_code); ?>">
            <input type="text" name="lab_name" placeholder="Lab Name" required>
            <input type="number" name="teachers_required" placeholder="Number of Teachers Required" required>
            <input type="text" name="lab_component" placeholder="Lab Component (hours per week)" required>
            <input type="number" name="credits" placeholder="Credits" required>
            <input type="number" name="year" placeholder="Year" required>
            <input type="text" name="section_names" placeholder="Enter Sections (comma-separated)" required>
            <input type="submit" value="Add Lab">
        </form>
    <?php endif; ?>

    <div class="message"><?= $message; ?></div>
</body>
</html>
