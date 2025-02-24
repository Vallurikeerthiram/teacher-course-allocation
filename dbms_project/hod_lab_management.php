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

// Fetch assigned labs data
$assigned_labs = [];
$assigned_query = "SELECT al.course_code, l.lab_name, l.credits, l.year, l.department, GROUP_CONCAT(al.teacher_username) AS teachers, GROUP_CONCAT(al.section) AS sections
                   FROM allocated_labs al
                   JOIN labs l ON al.course_code = l.course_code
                   GROUP BY al.course_code";
$assigned_result = mysqli_query($conn, $assigned_query);
while ($row = mysqli_fetch_assoc($assigned_result)) {
    $assigned_labs[] = $row;
}

// Fetch labs to be assigned
$labs_to_be_assigned = [];
$labs_query = "SELECT l.id, l.course_code, l.lab_name, l.credits, l.year, l.department, l.teachers_required
               FROM labs l";
$labs_result = mysqli_query($conn, $labs_query);

while ($lab = mysqli_fetch_assoc($labs_result)) {
    $lab_id = $lab['id'];

    // Get all sections for this lab
    $sections_query = "SELECT section_name FROM lab_sections WHERE lab_id = $lab_id";
    $sections_result = mysqli_query($conn, $sections_query);

    $all_sections = [];
    while ($section_row = mysqli_fetch_assoc($sections_result)) {
        $all_sections[] = $section_row['section_name'];
    }

    // Get assigned teachers per section for this lab
    $assigned_sections_query = "SELECT section, COUNT(*) as teacher_count FROM allocated_labs WHERE course_code = '{$lab['course_code']}' GROUP BY section";
    $assigned_sections_result = mysqli_query($conn, $assigned_sections_query);

    $assigned_sections = [];
    while ($assigned_row = mysqli_fetch_assoc($assigned_sections_result)) {
        $assigned_sections[$assigned_row['section']] = $assigned_row['teacher_count'];
    }

    // Calculate vacancy for each section
    $vacancy_per_section = [];
    foreach ($all_sections as $section) {
        $teachers_needed = $lab['teachers_required'];
        $teachers_allocated = isset($assigned_sections[$section]) ? $assigned_sections[$section] : 0;
        $vacancy_per_section[$section] = $teachers_needed - $teachers_allocated;
    }

    // Add vacancy info to the lab if there are unfilled sections
    $unfilled_sections = array_filter($vacancy_per_section, function($vacancy) {
        return $vacancy > 0;
    });

    if (!empty($unfilled_sections)) {
        $lab['vacancy'] = $unfilled_sections;
        $labs_to_be_assigned[] = $lab;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HOD Lab Management</title>
    <style>
        /* Add your CSS styles here */
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
        .lab-container {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            max-width: 1200px;
            margin: 20px auto;
        }
        .lab-section {
            flex: 1;
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-right: 20px;
        }
        .lab-section:last-child {
            margin-right: 0; /* Remove margin for the last section */
        }
        .lab-card {
            border: 1px solid #e1e1e1;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background-color: #ffffff;
            transition: transform 0.2s;
        }
        .lab-card:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }
        .lab-card h3 {
            margin: 0 0 10px;
            color: #A3113E;
        }
        .lab-card p {
            margin: 5px 0;
            color: #333;
        }
        .no-labs {
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
        <a href="assign_courses.php" class="navbar-link">Assign Courses</a>
        <a href="assign_labs.php" class="navbar-link">Assign Labs</a>
        <a href="hod_courses_management.php" class="navbar-link">Course Allocation Status</a>
        <a href="view_available_workload.php" class="navbar-link">Available Workload</a>
        <a href="add_courses.php" class="navbar-link">Add Courses</a>
        <a href="add_lab.php" class="navbar-link">Add Labs</a>
    </div>
</div><br><br><br><br><br>
    <h1>HOD Lab Management</h1>

    <div class="lab-container">
        <div class="lab-section">
            <h2>Assigned Labs</h2>
            <?php if (!empty($assigned_labs)): ?>
                <?php foreach ($assigned_labs as $lab): ?>
                    <div class="lab-card">
                        <h3><?= htmlspecialchars($lab['lab_name']) ?> (<?= htmlspecialchars($lab['course_code']) ?>)</h3>
                        <p><strong>Credits:</strong> <?= htmlspecialchars($lab['credits']) ?></p>
                        <p><strong>Year:</strong> <?= htmlspecialchars($lab['year']) ?></p>
                        <p><strong>Department:</strong> <?= htmlspecialchars($lab['department']) ?></p>
                        <p><strong>Teachers:</strong> <?= htmlspecialchars($lab['teachers']) ?></p>
                        <p><strong>Sections:</strong> <?= htmlspecialchars($lab['sections']) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-labs">No assigned labs found.</p>
            <?php endif; ?>
        </div>

        <div class="lab-section">
            <h2>Labs to be Assigned</h2>
            <?php if (!empty($labs_to_be_assigned)): ?>
                <?php foreach ($labs_to_be_assigned as $lab): ?>
                    <div class="lab-card">
                        <h3><?= htmlspecialchars($lab['lab_name']) ?> (<?= htmlspecialchars($lab['course_code']) ?>)</h3>
                        <p><strong>Credits:</strong> <?= htmlspecialchars($lab['credits']) ?></p>
                        <p><strong>Year:</strong> <?= htmlspecialchars($lab['year']) ?></p>
                        <p><strong>Department:</strong> <?= htmlspecialchars($lab['department']) ?></p>
                        <p><strong>Vacancies per Section:</strong></p>
                        <ul>
                            <?php foreach ($lab['vacancy'] as $section => $vacancy): ?>
                                <li>Section <?= htmlspecialchars($section) ?>: <?= htmlspecialchars($vacancy) ?> vacancies</li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-labs">All labs are fully assigned.</p>
            <?php endif; ?>
        </div>
        
    </div>
    
</body>
</html>
