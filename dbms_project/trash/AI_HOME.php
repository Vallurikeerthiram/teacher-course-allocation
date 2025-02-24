<?php 
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'ai') {
    header('Location: login.php');
    exit();
}

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

// Handle form submission for course ranking and verification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if it's a verification attempt
    if (isset($_POST['verify'])) {
        // Get username and password from POST
        $input_username = $_POST['username'];
        $input_password = $_POST['password'];

        // Verify credentials
        $check_user_query = "SELECT * FROM teachers WHERE username = ? AND password = ?";
        $stmt = $conn->prepare($check_user_query);
        $stmt->bind_param("ss", $input_username, $input_password);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if user exists and credentials are correct
        if ($result->num_rows === 1) {
            // Prepare statement for inserting selections
            $insert_query = "INSERT INTO teacher_selections (teacher_username, course_code, priority, department) VALUES (?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_query);

            // Check selected AI courses
            if (!empty($_POST['ai_courses'])) {
                foreach ($_POST['ai_courses'] as $course_code) {
                    $priority = $_POST['priority'][$course_code] ?? null; 
                    
                    if ($priority !== null && is_numeric($priority)) {
                        $department = 'AI';
                        $insert_stmt->bind_param("ssis", $input_username, $course_code, $priority, $department);
                        $insert_stmt->execute();
                    }
                }
            }

            // Check selected Elective courses
            if (!empty($_POST['elective_courses'])) {
                foreach ($_POST['elective_courses'] as $course_code) {
                    $priority = $_POST['priority'][$course_code] ?? null; 
                    
                    if ($priority !== null && is_numeric($priority)) {
                        $department = 'Elective';
                        $insert_stmt->bind_param("ssis", $input_username, $course_code, $priority, $department);
                        $insert_stmt->execute();
                    }
                }
            }

            // Close insert statement
            $insert_stmt->close();
            echo "<script>alert('Course rankings updated successfully!');</script>";
        } else {
            echo "<script>alert('Invalid username or password!');</script>";
        }

        // Close check user statement
        $stmt->close();
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
    </style>
    <script>
        function togglePriorityInputs() {
            const courseItems = document.querySelectorAll('.course-item');
            courseItems.forEach(item => {
                const checkbox = item.querySelector('input[type="checkbox"]');
                const priorityInput = item.querySelector('.priority-input');
                if (checkbox.checked) {
                    priorityInput.style.display = 'block';
                } else {
                    priorityInput.style.display = 'none';
                }
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
            showCredentialsSection(); // Show the credentials section only if validation passes
        }
    }
        function showCredentialsSection() {
            document.getElementById('credentials-section').style.display = 'block';
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Welcome, AI Faculty!</h1>
        
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

            <button type="button" onclick="finalizeRankings()">Finalize Rankings</button>
            <div id="credentials-section" style="display:none;">
                <h2>Confirm Your Credentials</h2>
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
                <button type="submit" name="verify">Verify</button>
            </div>
        </form>
        <br>
        <button class="logout-button" onclick="window.location.href='logout.php'">Logout</button>
    </div>
</body>
</html>
