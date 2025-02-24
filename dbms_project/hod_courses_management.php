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

// Handle department selection
$selected_department = isset($_POST['department']) ? $_POST['department'] : null;

// Fetch assigned courses data
$assigned_courses = [];
if ($selected_department) {
    $query = "SELECT ac.course_code, ac.course_name, ac.credits, GROUP_CONCAT(ac.teacher_username) AS teachers, GROUP_CONCAT(ac.section) AS sections
              FROM assigned_courses ac
              JOIN {$selected_department}_courses c ON ac.course_code = c.course_code
              WHERE c.course_code = ac.course_code
              GROUP BY ac.course_code";
    $result = mysqli_query($conn, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        $assigned_courses[] = $row;
    }
}

// Fetch courses to be assigned
$courses_to_be_assigned = [];
if ($selected_department) {
    $all_courses_query = "SELECT course_code, course_name, credits, year FROM {$selected_department}_courses";
    $all_courses_result = mysqli_query($conn, $all_courses_query);
    
    while ($course = mysqli_fetch_assoc($all_courses_result)) {
        // Check if all sections are assigned for this course
        $sections_query = "SELECT section FROM sections WHERE course_code = '{$course['course_code']}'";
        $sections_result = mysqli_query($conn, $sections_query);
        
        $all_sections = [];
        while ($section_row = mysqli_fetch_assoc($sections_result)) {
            $all_sections[] = $section_row['section'];
        }

        // Get assigned sections for this course
        $assigned_sections_query = "SELECT section FROM assigned_courses WHERE course_code = '{$course['course_code']}'";
        $assigned_sections_result = mysqli_query($conn, $assigned_sections_query);
        
        $assigned_sections = [];
        while ($assigned_section = mysqli_fetch_assoc($assigned_sections_result)) {
            $assigned_sections[] = $assigned_section['section'];
        }

        // Check for unassigned sections
        $vacant_sections = array_diff($all_sections, $assigned_sections);
        if (!empty($vacant_sections)) {
            $course['vacancy'] = implode(', ', $vacant_sections);
            $courses_to_be_assigned[] = $course;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HOD Course Management</title>
    <style>
        /* Add your CSS here */
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 20px;
        }
        h1 {
            color: #A3113E;
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
        select, input[type="submit"], .btn-home {
            width: calc(100% - 20px);
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="submit"], .btn-home {
            background-color: #A3113E; /* Brand color */
            color: #ffffff;
            border: none;
            transition: background-color 0.3s, transform 0.2s;
        }
        input[type="submit"]:hover, .btn-home:hover {
            background-color: #900c2e; /* Darker shade */
            transform: scale(1.05);
        }
        .course-container {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            max-width: 1200px;
            margin: 20px auto;
        }
        .course-section {
            flex: 1;
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-right: 20px;
        }
        .course-section:last-child {
            margin-right: 0; /* Remove margin for the last section */
        }
        h2 {
            color: #A3113E;
            margin-top: 0;
        }
        .course-card {
            border: 1px solid #e1e1e1;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background-color: #ffffff;
            transition: transform 0.2s;
        }
        .course-card:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }
        .course-card h3 {
            margin: 0 0 10px;
            color: #A3113E;
        }
        .course-card p {
            margin: 5px 0;
            color: #333;
        }
        .no-courses {
            text-align: center;
            font-style: italic;
            color: #888;
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
            <a href="view_available_workload.php" class="navbar-link">Available Workload</a>
            <a href="add_courses.php" class="navbar-link">Add Courses</a>
            <a href="add_lab.php" class="navbar-link">Add Labs</a>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;;&nbsp;&nbsp;&nbsp;
        </div>
    </div><br><br><br><br>
    <h1>HOD Course Management</h1>

    <form method="POST">
        <label for="department">Select Department:</label>
        <select name="department" id="department" required>
            <option value="">--Select Department--</option>
            <option value="cs" <?= $selected_department == 'cs' ? 'selected' : '' ?>>CS Department</option>
            <option value="ai" <?= $selected_department == 'ai' ? 'selected' : '' ?>>AI Department</option>
            <option value="electives" <?= $selected_department == 'electives' ? 'selected' : '' ?>>Electives</option>
        </select>
        <input type="submit" value="View Courses">
        <button class="btn-home" onclick="window.location.href='hod_home.php'; return false;">Go Back to Home</button>
    </form>

    <?php if ($selected_department): ?>
        <div class="course-container">
            <div class="course-section">
                <h2>Assigned Courses</h2>
                <?php if (!empty($assigned_courses)): ?>
                    <?php foreach ($assigned_courses as $course): ?>
                        <div class="course-card">
                            <h3><?= htmlspecialchars($course['course_name']) ?> (<?= htmlspecialchars($course['course_code']) ?>)</h3>
                            <p><strong>Credits:</strong> <?= htmlspecialchars($course['credits']) ?></p>
                            <p><strong>Teachers:</strong> <?= htmlspecialchars($course['teachers']) ?></p>
                            <p><strong>Sections:</strong> <?= htmlspecialchars($course['sections']) ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-courses">No assigned courses found.</p>
                <?php endif; ?>
            </div>

            <div class="course-section">
                <h2>Courses to be Assigned</h2>
                <?php if (!empty($courses_to_be_assigned)): ?>
                    <?php foreach ($courses_to_be_assigned as $course): ?>
                        <div class="course-card">
                            <h3><?= htmlspecialchars($course['course_name']) ?> (<?= htmlspecialchars($course['course_code']) ?>)</h3>
                            <p><strong>Credits:</strong> <?= htmlspecialchars($course['credits']) ?></p>
                            <p><strong>Year:</strong> <?= htmlspecialchars($course['year']) ?></p>
                            <p><strong>Vacancy:</strong> <?= htmlspecialchars($course['vacancy']) ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-courses">All courses are fully assigned.</p>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</body>
</html>
