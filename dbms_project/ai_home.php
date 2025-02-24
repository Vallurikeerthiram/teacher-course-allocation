<?php 
session_start();
if (!isset($_SESSION['logged_in_user'])) {
    header('Location: login.php'); // Redirect to login if not logged in
    exit();
}
$loggedInUser = $_SESSION['logged_in_user'];

// Include your database connection file
include 'db_connection.php';

// Fetch AI courses from the database
$ai_courses_query = "SELECT course_code, course_name FROM ai_courses"; 
$ai_courses_result = mysqli_query($conn, $ai_courses_query);
$ai_courses = mysqli_fetch_all($ai_courses_result, MYSQLI_ASSOC);

// Fetch Elective courses from the database
$electives_query = "SELECT course_code, course_name FROM electives_courses"; 
$electives_result = mysqli_query($conn, $electives_query);
$electives_courses = mysqli_fetch_all($electives_result, MYSQLI_ASSOC);

// Handle form submission for course ranking
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ai_priorities = [];
    $elective_priorities = [];
    $hasDuplicate = false;

    // Validate priorities for AI courses
    if (!empty($_POST['ai_courses'])) {
        foreach ($_POST['ai_courses'] as $course_code) {
            $priority = $_POST['priority'][$course_code] ?? null; 
            if ($priority !== null && is_numeric($priority) && $priority > 0) {
                // Check for duplicate priorities
                if (in_array($priority, $ai_priorities)) {
                    $hasDuplicate = true; // Set flag if a duplicate is found
                    break; // Exit the loop on first duplicate
                }
                $ai_priorities[] = $priority; // Add priority to the array for checking
            }
        }
    }

    // Validate priorities for Elective courses
    if (!empty($_POST['elective_courses'])) {
        foreach ($_POST['elective_courses'] as $course_code) {
            $priority = $_POST['priority'][$course_code] ?? null; 
            if ($priority !== null && is_numeric($priority) && $priority > 0) {
                // Check for duplicate priorities
                if (in_array($priority, $elective_priorities)) {
                    $hasDuplicate = true; // Set flag if a duplicate is found
                    break; // Exit the loop on first duplicate
                }
                $elective_priorities[] = $priority; // Add priority to the array for checking
            }
        }
    }

    // Check if any duplicates were found
    if ($hasDuplicate) {
        echo "<script>alert('Duplicate priority number detected. Please correct your selection.');</script>";
    } else {
        // If no duplicates, proceed to insert into the database
        if (!empty($_POST['ai_courses'])) {
            foreach ($_POST['ai_courses'] as $course_code) {
                $priority = $_POST['priority'][$course_code] ?? null; 
                if ($priority !== null) {
                    $department = 'AI';
                    $insert_course_stmt = $conn->prepare("INSERT INTO teacher_selections (teacher_username, course_code, priority, department) VALUES (?, ?, ?, ?)");
                    $insert_course_stmt->bind_param("ssis", $loggedInUser, $course_code, $priority, $department);
                    $insert_course_stmt->execute();
                }
            }
        }

        if (!empty($_POST['elective_courses'])) {
            foreach ($_POST['elective_courses'] as $course_code) {
                $priority = $_POST['priority'][$course_code] ?? null; 
                if ($priority !== null) {
                    $department = 'Elective';
                    $insert_course_stmt = $conn->prepare("INSERT INTO teacher_selections (teacher_username, course_code, priority, department) VALUES (?, ?, ?, ?)");
                    $insert_course_stmt->bind_param("ssis", $loggedInUser, $course_code, $priority, $department);
                    $insert_course_stmt->execute();
                }
            }
        }

        echo "<script>alert('Selections updated successfully!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Home</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> 
    <link rel="stylesheet" href="styles.css"> 
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background-color: #f4f4f4; 
            margin: 0; 
            padding: 20px;
            color: #333;
        }
        h1, h2 { 
            color: #A3113E; 
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        .course-list { 
            background: white; 
            border: 1px solid #ccc; 
            padding: 15px; 
            border-radius: 10px; 
            margin-bottom: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
            gap: 15px;
        }
        .course-item {
            position: relative;
            background: #f9f9f9;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            text-align: center;
            transition: background-color 0.3s ease;
        }
        .course-item:hover {
            background-color: #f0f0f0;
            cursor: pointer;
        }
        .course-item input[type="checkbox"] {
            display: none;
        }
        .course-item input[type="checkbox"]:checked + label {
            background-color: #A3113E;
            color: white;
            border-color: #A3113E;
        }
        .course-item label {
            display: block;
            cursor: pointer;
            padding: 10px;
            border-radius: 8px;
            transition: all 0.3s;
        }
        .priority-input {
            margin-top: 10px;
        }
        button { 
            background-color: #A3113E; 
            color: white; 
            padding: 12px 24px; 
            border: none; 
            border-radius: 5px; 
            font-size: 16px;
            transition: background-color 0.3s ease;
        }
        button:hover { 
            background-color: #8b0e35; 
        }
        .logout-button {
            background-color: #ff3b30; 
        }
        .logout-button:hover { 
            background-color: #d32f2f; 
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
    margin: 0 15px; /* Spacing between links */
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
    </style>
    <script>
        function togglePriorityInputs() {
            const courseItems = document.querySelectorAll('.course-item');
            courseItems.forEach(item => {
                const checkbox = item.querySelector('input[type="checkbox"]');
                const priorityInput = item.querySelector('.priority-input');
                priorityInput.style.display = checkbox.checked ? 'block' : 'none';
            });
        }

        function validateElectiveSelection() {
            const selectedElectives = document.querySelectorAll('input[name="elective_courses[]"]:checked');
            if (selectedElectives.length < 2) {
                alert('Please select at least two elective courses.');
                return false; // Prevent form submission
            }
            return true; // Allow form submission
        }

        function finalizeRankings() {
            if (validateElectiveSelection()) {
                // Directly submit the form since verification is no longer needed
                document.querySelector('form').submit();
            }
        }
    </script>
</head>
<body>
<div class="navbar">
        <img src="amrita logo light mode.png" alt="Logo" class="navbar-logo">
        <div class="navbar-links">
            <a href="lab_selection.php" class="navbar-link">Select Labs</a>
            <a href="view_allocated.php" class="navbar-link">View Assigned</a>
            <a href="logout.php" class="navbar-link">Log Out</a>
        </div>
    </div><br><br><br><br><br><br><br>
<div class="container">

    <h1>Welcome, <?php echo htmlspecialchars($loggedInUser); ?>!</h1>
    
    <h2>Select Your AI Courses</h2>
    <form method="POST" action="">
        <div class="course-list">
            <?php foreach ($ai_courses as $course): ?>
                <div class="course-item">
                    <input type="checkbox" id="ai_<?php echo htmlspecialchars($course['course_code']); ?>" name="ai_courses[]" value="<?php echo htmlspecialchars($course['course_code']); ?>" onchange="togglePriorityInputs()">
                    <label for="ai_<?php echo htmlspecialchars($course['course_code']); ?>">
                        <?php echo htmlspecialchars($course['course_name']); ?>
                    </label>
                    <div class="priority-input" style="display: none;">
                        <label for="priority_<?php echo htmlspecialchars($course['course_code']); ?>">Priority:</label>
                        <input type="number" id="priority_<?php echo htmlspecialchars($course['course_code']); ?>" name="priority[<?php echo htmlspecialchars($course['course_code']); ?>]" min="1" >
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <h2>Select Elective Courses</h2>
        <div class="course-list">
            <?php foreach ($electives_courses as $course): ?>
                <div class="course-item">
                    <input type="checkbox" id="elective_<?php echo htmlspecialchars($course['course_code']); ?>" name="elective_courses[]" value="<?php echo htmlspecialchars($course['course_code']); ?>" onchange="togglePriorityInputs()">
                    <label for="elective_<?php echo htmlspecialchars($course['course_code']); ?>">
                        <?php echo htmlspecialchars($course['course_name']); ?>
                    </label>
                    <div class="priority-input" style="display: none;">
                        <label for="priority_<?php echo htmlspecialchars($course['course_code']); ?>">Priority:</label>
                        <input type="number" id="priority_<?php echo htmlspecialchars($course['course_code']); ?>" name="priority[<?php echo htmlspecialchars($course['course_code']); ?>]" min="1" >
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <button type="button" onclick="finalizeRankings()">Submit Rankings</button>
    </form>
</div>
</body>
</html>
