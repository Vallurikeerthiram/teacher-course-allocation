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
$courses = [];
$teachers = [];
$selected_course_code = "";

// Handle AJAX requests

// Check if the request method is POST and an action is set
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'fetch_courses') {
        $selected_course_type = $_POST['course_type'];
        $sql = "";

        // Fetch courses based on selected course type
        if ($selected_course_type == "cs") {
            $sql = "SELECT * FROM cs_courses";
        } elseif ($selected_course_type == "ai") {
            $sql = "SELECT * FROM ai_courses";
        } elseif ($selected_course_type == "elective") {
            $sql = "SELECT * FROM electives_courses";
        } else {
            echo json_encode(["error" => "Invalid course type selected."]);
            exit();
        }

        $result = $conn->query($sql);
        $courses = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $courses[] = $row;
            }
        }

        echo json_encode($courses);
        exit();
    }

    // Handle fetching teachers and sections
    if ($_POST['action'] == 'fetch_teachers_and_sections') {
        $course_code = $_POST['course_code'];
        $response = [];

        // Fetch teachers who are interested in the selected course
        $sql = "SELECT t.username, t.available_hours, ts.priority 
                FROM teacher_selections ts 
                JOIN teachers t ON ts.teacher_username = t.username 
                WHERE ts.course_code = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $course_code);
        $stmt->execute();
        $result_teachers = $stmt->get_result();
        $teachers = [];

        while ($row = $result_teachers->fetch_assoc()) {
            $teachers[] = $row;
        }
        $stmt->close();

        // Fetch available sections for the course
        $query_sections = "
            SELECT section FROM sections 
            WHERE course_code = ? 
            AND section NOT IN (
                SELECT section FROM assigned_courses WHERE course_code = ?
            )";
        $stmt_sections = $conn->prepare($query_sections);
        $stmt_sections->bind_param("ss", $course_code, $course_code);
        $stmt_sections->execute();
        $result_sections = $stmt_sections->get_result();
        $sections = [];

        while ($row = $result_sections->fetch_assoc()) {
            $sections[] = $row['section'];
        }
        $stmt_sections->close();

        // Return both teachers and sections as JSON
        $response['teachers'] = $teachers;
        $response['sections'] = $sections;

        echo json_encode($response);
        exit();
    }
}

// Handle form submission to assign courses to teachers
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['teacher_username'])) {
    $course_name = $_POST['course_name'] ?? '';
    $course_code = $_POST['course_code'] ?? '';
    $teacher_username = $_POST['teacher_username'] ?? '';
    $selected_section = $_POST['selected_section'] ?? '';

    if (!empty($course_name) && !empty($course_code) && !empty($teacher_username) && !empty($selected_section)) {
        // Fetch credits and year from the respective course table
        $course_type = $_POST['course_type'];
        $credits = 0;
        $year = 0;

        if ($course_type == "cs") {
            $sql = "SELECT credits, year FROM cs_courses WHERE course_code = ?";
        } elseif ($course_type == "ai") {
            $sql = "SELECT credits, year FROM ai_courses WHERE course_code = ?";
        } else {
            $sql = "SELECT credits, year FROM electives_courses WHERE course_code = ?";
        }

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $course_code);
        $stmt->execute();
        $stmt->bind_result($credits, $year);
        $stmt->fetch();
        $stmt->close();

        // Split sections by comma and insert each into `assigned_courses`
        $sections = explode(',', $selected_section);

        foreach ($sections as $section) {
            $section = trim($section); // Remove any extra spaces
            if (!empty($section)) {
                $insert_sql = "INSERT INTO assigned_courses (teacher_username, course_code, course_name, credits, year, section)
                               VALUES (?, ?, ?, ?, ?, ?)";
                $insert_stmt = $conn->prepare($insert_sql);
                $insert_stmt->bind_param("sssiis", $teacher_username, $course_code, $course_name, $credits, $year, $section);
                $insert_stmt->execute();
                $insert_stmt->close();
            }
        }

        // Update available hours in the teachers table based on assigned sections and credits
        $total_hours_reduction = count($sections) * $credits;
        $update_hours_sql = "UPDATE teachers SET available_hours = available_hours - ? WHERE username = ?";
        $update_stmt = $conn->prepare($update_hours_sql);
        $update_stmt->bind_param("is", $total_hours_reduction, $teacher_username);
        $update_stmt->execute();
        $update_stmt->close();

        // Remove the teacher's entry from teacher_selections
        $delete_sql = "DELETE FROM teacher_selections WHERE teacher_username = ? AND course_code = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("ss", $teacher_username, $course_code);
        $delete_stmt->execute();
        $delete_stmt->close();

            echo "<script>alert('Courses assigned successfully to teacher: $teacher_username for sections: $selected_section.');</script>";
            
        } else {
            echo "<script>alert('Error: All fields must be filled. Please go back and enter all required details.');</script>";
            
        }
        
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Courses to Teachers</title>
    <link rel="stylesheet" href="styles.css">
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
    </style>
</head>
<body>
    <div class="navbar">
        <img src="amrita logo light mode.png" alt="Logo" class="navbar-logo">
        <div class="navbar-links">
            <a href="hod_home.php" class="navbar-link">Home</a>
            <a href="assign_labs.php" class="navbar-link">Assign Labs</a>
            <a href="hod_lab_management.php" class="navbar-link">Lab Allocation Status</a>
            <a href="hod_courses_management.php" class="navbar-link">Course Allocation Status</a>
            <a href="view_available_workload.php" class="navbar-link">Available Workload</a>
            <a href="add_courses.php" class="navbar-link">Add Courses</a>
            <a href="add_lab.php" class="navbar-link">Add Labs</a>
        </div>
    </div>
    <br><br><br><br><br><br>
    <h1>Assign Courses to Teachers</h1>
    <form method="POST" id="course_form">
        <label for="course_type">Select Course Type:</label>
        <select id="course_type" name="course_type" onchange="fetchCourses()">
            <option value="">--Select--</option>
            <option value="cs">CS</option>
            <option value="ai">AI</option>
            <option value="elective">Elective</option>
        </select>

        <label for="course_name">Select Course:</label>
        <select id="course_name" name="course_name" onchange="fetchTeachersAndSections()" disabled>
            <option value="">--Select--</option>
        </select>

        <div id="teachers_container"></div>
        <div id="sections_container"></div>

        <div class="suggestion-box">
    <label for="teacher_username">Enter Teacher's Username:</label>
    <select name="teacher_username" id="teacher_username" required>
        <option value="" disabled selected>Select a Teacher</option> <!-- Placeholder option -->
    </select>
</div>

        <label for="manual_section">Enter Section(s), separated by commas:</label>
        <input list="sections_list" name="selected_section" id="manual_section" required>
        <datalist id="sections_list"></datalist>

        <input type="hidden" name="course_code" id="hidden_course_code">
        <input type="hidden" name="course_name" id="hidden_course_name">
        
        <input type="submit" value="Assign">
    </form>

    <script>
        function fetchCourses() {
            var courseType = document.getElementById('course_type').value;

            var xhr = new XMLHttpRequest();
            xhr.open("POST", "assign_courses.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    var courses = JSON.parse(xhr.responseText);
                    var courseSelect = document.getElementById('course_name');
                    courseSelect.innerHTML = "";
                    courseSelect.disabled = false;

                    courses.forEach(function(course) {
                        var option = document.createElement('option');
                        option.value = course.course_code;
                        option.text = course.course_name;
                        courseSelect.add(option);
                    });
                }
            };
            xhr.send("action=fetch_courses&course_type=" + courseType);
        }

        function fetchTeachersAndSections() {
    var courseCode = document.getElementById('course_name').value;

    if (courseCode === "") return;

    var xhr = new XMLHttpRequest();
    xhr.open("POST", "assign_courses.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            var data = JSON.parse(xhr.responseText);
            var teachersContainer = document.getElementById('teachers_container');
            var sectionsContainer = document.getElementById('sections_container');
            var teacherSelect = document.getElementById('teacher_username');

            teachersContainer.innerHTML = "";
            sectionsContainer.innerHTML = "";
            teacherSelect.innerHTML = "<option value='' disabled selected>Select a Teacher</option>"; // Reset select

            // Populate teachers and their available hours
            data.teachers.forEach(function(teacher) {
                var option = document.createElement('option');
                option.value = teacher.username; // Set value to teacher's username
                option.text = teacher.username; // Display teacher's username
                teacherSelect.add(option); // Add to the select dropdown

                var card = document.createElement('div');
                card.className = 'teacher-card';
                card.innerHTML = `
                    <div class="teacher-info">
                        <strong>${teacher.username}</strong><br>
                        Available Hours: ${teacher.available_hours}<br>
                        Priority: ${teacher.priority}
                    </div>
                `;
                teachersContainer.appendChild(card);
            });

            // Populate available sections
            data.sections.forEach(function(section) {
                var card = document.createElement('div');
                card.className = 'section-card';
                card.innerHTML = `
                    <div class="section-info">
                        <strong>Section ${section} is available to assign </strong>
                    </div>
                `;
                sectionsContainer.appendChild(card);
            });

            document.getElementById('hidden_course_code').value = courseCode;
            document.getElementById('hidden_course_name').value = document.getElementById('course_name').options[document.getElementById('course_name').selectedIndex].text;
        }
    };
        xhr.send("action=fetch_teachers_and_sections&course_code=" + courseCode);
    }</script>
</body>
</html>
