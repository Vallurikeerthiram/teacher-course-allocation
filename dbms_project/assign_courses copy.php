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

// Fetch all labs for the dropdown
$labs = [];
$labs_sql = "SELECT id, lab_name FROM labs";
$labs_result = $conn->query($labs_sql);
if ($labs_result->num_rows > 0) {
    while ($row = $labs_result->fetch_assoc()) {
        $labs[] = $row;
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'fetch_lab_details') {
        $lab_id = $_POST['lab_id'];

        // Fetch lab details
        $lab_sql = "SELECT * FROM labs WHERE id = ?";
        $lab_stmt = $conn->prepare($lab_sql);
        $lab_stmt->bind_param("i", $lab_id);
        $lab_stmt->execute();
        $lab_result = $lab_stmt->get_result();

        if ($lab_result->num_rows > 0) {
            $lab_details = $lab_result->fetch_assoc();

            // Fetch sections related to the lab
            $sections_sql = "SELECT section_name FROM lab_sections WHERE lab_id = ?";
            $sections_stmt = $conn->prepare($sections_sql);
            $sections_stmt->bind_param("i", $lab_id);
            $sections_stmt->execute();
            $sections_result = $sections_stmt->get_result();
            $sections = [];
            while ($row = $sections_result->fetch_assoc()) {
                $sections[] = $row['section_name'];
            }

            // Fetch teachers who applied for this lab
            $teachers_sql = "SELECT * FROM teacher_lab_selections WHERE course_code = ?";
            $teachers_stmt = $conn->prepare($teachers_sql);
            $teachers_stmt->bind_param("s", $lab_details['course_code']);
            $teachers_stmt->execute();
            $teachers_result = $teachers_stmt->get_result();
            $teachers = [];
            while ($row = $teachers_result->fetch_assoc()) {
                $teachers[] = $row;
            }

            // Prepare the response
            echo json_encode([
                'lab_name' => $lab_details['lab_name'],
                'teachers_required' => $lab_details['teachers_required'],
                'allocated_teachers' => count($teachers), // Count of teachers already allocated
                'sections' => $sections,
                'teachers' => $teachers
            ]);
        } else {
            echo json_encode(['error' => 'Lab not found.']);
        }

        $lab_stmt->close();
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Labs</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Add your styles here */
        .teacher-card {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            margin: 10px;
            display: inline-block;
            width: 200px;
        }
    </style>
</head>
<body>
    <h1>Assign Labs to Teachers</h1>
    <select id="lab-dropdown">
        <option value="">Select a lab</option>
        <?php foreach ($labs as $lab): ?>
            <option value="<?php echo $lab['id']; ?>"><?php echo $lab['lab_name']; ?></option>
        <?php endforeach; ?>
    </select>
    
    <div id="lab-details"></div>

    <script>
        document.getElementById('lab-dropdown').addEventListener('change', function() {
            var labId = this.value;
            if (labId) {
                fetchLabDetails(labId);
            }
        });

        function fetchLabDetails(labId) {
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "assign_labs.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    var data = JSON.parse(xhr.responseText);
                    displayLabDetails(data);
                }
            };
            xhr.send("action=fetch_lab_details&lab_id=" + labId);
        }

        function displayLabDetails(data) {
            var labDetailsDiv = document.getElementById('lab-details');
            labDetailsDiv.innerHTML = '';

            if (data.error) {
                labDetailsDiv.innerHTML = '<p>' + data.error + '</p>';
                return;
            }

            var totalTeachersRequired = data.teachers_required;
            var allocatedTeachers = data.allocated_teachers;
            var vacancies = totalTeachersRequired - allocatedTeachers;

            labDetailsDiv.innerHTML += `<h2>Lab: ${data.lab_name}</h2>`;
            labDetailsDiv.innerHTML += `<p>Teachers Required: ${totalTeachersRequired}</p>`;
            labDetailsDiv.innerHTML += `<p>Allocated Teachers: ${allocatedTeachers}</p>`;
            labDetailsDiv.innerHTML += `<p>Vacancies: ${vacancies}</p>`;
            labDetailsDiv.innerHTML += '<h3>Teachers who applied:</h3>';
            data.teachers.forEach(function(teacher) {
                var card = document.createElement('div');
                card.className = 'teacher-card';
                card.innerHTML = `
                    <strong>${teacher.teacher_username}</strong><br>
                    Course Code: ${teacher.course_code}<br>
                    Priority: ${teacher.priority}<br>
                    Department: ${teacher.department}
                `;
                labDetailsDiv.appendChild(card);
            });

            // Add a dropdown to select teachers from those who applied
            var selectTeacher = document.createElement('select');
            selectTeacher.innerHTML = '<option value="">Select a Teacher</option>';
            data.teachers.forEach(function(teacher) {
                var option = document.createElement('option');
                option.value = teacher.teacher_username;
                option.textContent = teacher.teacher_username;
                selectTeacher.appendChild(option);
            });
            labDetailsDiv.appendChild(selectTeacher);

            // Add input for sections
            var sectionInput = document.createElement('input');
            sectionInput.placeholder = 'Enter sections (comma separated)';
            labDetailsDiv.appendChild(sectionInput);
        }
    </script>
</body>
</html>
