<?php
session_start();
if (!isset($_SESSION['logged_in_user'])) {
    header('Location: login.php'); // Redirect to login if not logged in
    exit();
}
$loggedInUser = $_SESSION['logged_in_user'];
$userRole = $_SESSION['user_role'] ?? '';

// Determine the target page based on the user's role
$targetPage = 'lab_selection.php'; // Default page
if ($userRole === 'ai') {
    $targetPage = 'ai_home.php';
} elseif ($userRole === 'cs') {
    $targetPage = 'cs_home.php';
}

// Include your database connection file
include 'db_connection.php';

// Fetch the department of the logged-in user
$department_query = "SELECT role FROM teachers WHERE username = ?";
$department_stmt = $conn->prepare($department_query);
$department_stmt->bind_param("s", $loggedInUser);
$department_stmt->execute();
$department_stmt->bind_result($department);
$department_stmt->fetch();
$department_stmt->close();

// Fetch labs from the database
$labs_query = "SELECT * FROM labs"; 
$labs_result = mysqli_query($conn, $labs_query);
$labs = mysqli_fetch_all($labs_result, MYSQLI_ASSOC);

// Handle form submission for lab ranking
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Prepare statement for inserting selections
    $insert_query = "INSERT INTO teacher_lab_selections (teacher_username, course_code, priority, department) VALUES (?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_query);

    $priorities = []; // Array to track priorities and detect duplicates
    $hasDuplicate = false; // Flag for duplicate priorities

    // Check selected labs
    if (!empty($_POST['labs'])) {
        foreach ($_POST['labs'] as $lab_code) {
            $priority = $_POST['priority'][$lab_code] ?? null; 
            
            if ($priority !== null && is_numeric($priority)) {
                if (in_array($priority, $priorities)) {
                    $hasDuplicate = true; // Duplicate priority found
                    break; // Stop checking further
                }
                $priorities[] = $priority; // Add to the priorities array

                // Insert data into the database
                $insert_stmt->bind_param("ssis", $loggedInUser, $lab_code, $priority, $department);
                $insert_stmt->execute();
            }
        }
    }

    // Close insert statement
    $insert_stmt->close();

    if ($hasDuplicate) {
        echo "<script>alert('Duplicate priorities found! Please ensure each priority is unique.');</script>";
    } else {
        echo "<script>alert('Lab rankings updated successfully!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Selection</title>
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
        }        .navbar {
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

        h1, h2 { 
            color: #A3113E; 
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        .lab-list { 
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
            gap: 15px;
            background: white; 
            padding: 15px; 
            border-radius: 10px; 
            border: 1px solid #ccc;
        }
        .lab-item {
            position: relative;
            background: #f9f9f9;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            text-align: center;
            transition: background-color 0.3s ease;
        }
        .lab-item:hover {
            background-color: #f0f0f0;
            cursor: pointer;
        }
        .lab-item input[type="checkbox"] {
            display: none;
        }
        .lab-item input[type="checkbox"]:checked + label {
            background-color: #A3113E;
            color: white;
            border-color: #A3113E;
        }
        .lab-item label {
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
            const labItems = document.querySelectorAll('.lab-item');
            labItems.forEach(item => {
                const checkbox = item.querySelector('input[type="checkbox"]');
                const priorityInput = item.querySelector('.priority-input');
                if (checkbox.checked) {
                    priorityInput.style.display = 'block';
                } else {
                    priorityInput.style.display = 'none';
                }
            });
        }

        function finalizeRankings() {
            const selectedLabs = document.querySelectorAll('input[name="labs[]"]:checked');
            const priorities = [];
            let hasDuplicate = false;

            selectedLabs.forEach(lab => {
                const priorityInput = lab.closest('.lab-item').querySelector('.priority-input input');
                const priority = priorityInput.value;

                if (priorities.includes(priority)) {
                    hasDuplicate = true;
                } else {
                    priorities.push(priority);
                }
            });

            if (hasDuplicate) {
                alert('Duplicate priorities found! Please ensure each priority is unique.');
            } else {
                document.querySelector('form').submit();
            }
        }
    </script>
</head>
<body>
<div class="navbar">
        <img src="amrita logo light mode.png" alt="Logo" class="navbar-logo">
        <div class="navbar-links">
        <a href="<?php echo $targetPage; ?>" class="navbar-link">Select Courses</a>
            <a href="add_lab.php" class="navbar-link">View Assigned</a>
            <a href="logout.php" class="navbar-link">Log Out</a>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;;&nbsp;&nbsp;&nbsp;
        </div>
    </div><br><br><br><br><br><br><br>
<div class="container">
    <h1>Welcome, <?php echo htmlspecialchars($loggedInUser); ?>!</h1> 
    <h2>Select Your Labs</h2>
    <form method="POST" action="">
        <div class="lab-list">
            <?php foreach ($labs as $lab): ?>
                <div class="lab-item">
                    <input type="checkbox" id="lab_<?php echo htmlspecialchars($lab['course_code']); ?>" name="labs[]" value="<?php echo htmlspecialchars($lab['course_code']); ?>" onchange="togglePriorityInputs()">
                    <label for="lab_<?php echo htmlspecialchars($lab['course_code']); ?>">
                        <?php echo htmlspecialchars($lab['lab_name']); ?><br>
                        <small>Department: <?php echo htmlspecialchars($lab['department']); ?></small>
                    </label>
                    <div class="priority-input" style="display: none;">
                        <label for="priority_<?php echo htmlspecialchars($lab['course_code']); ?>">Priority:</label>
                        <input type="number" id="priority_<?php echo htmlspecialchars($lab['course_code']); ?>" name="priority[<?php echo htmlspecialchars($lab['course_code']); ?>]" min="1">
                    </div>
                </div>
            <?php endforeach; ?>
        </div><br><br>
        <button type="button" onclick="finalizeRankings()">Finalize Rankings</button>
    </form>
</div>
</body>
</html>
