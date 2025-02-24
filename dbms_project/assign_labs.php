<?php
// Database connection
$host = 'localhost';
$dbname = 'dbms_project'; // Change as per your database name
$user = 'root'; // Your database username
$pass = ''; // Your database password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database $dbname :" . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'fetch_teachers') {
        $lab_id = $_POST['lab_id'];

        // Prepare and execute the SQL statement to fetch teachers applying for the lab
        $stmt = $pdo->prepare("
            SELECT tls.teacher_username, tls.course_code, tls.priority, t.available_hours
            FROM teacher_lab_selections tls
            JOIN teachers t ON tls.teacher_username = t.username
            WHERE tls.course_code IN (
                SELECT course_code FROM labs WHERE id = :lab_id
            )
        ");
        $stmt->execute(['lab_id' => $lab_id]);
        
        // Fetch all matching records
        $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($teachers);
        exit;
    } elseif ($_POST['action'] === 'fetch_vacancies') {
        $lab_id = $_POST['lab_id'];

        // Fetch sections and their current vacancies for the selected lab
        $stmt = $pdo->prepare("
            SELECT ls.section_name, l.teachers_required, 
                   COUNT(al.teacher_username) AS allocated_count,
                   l.credits
            FROM lab_sections ls
            JOIN labs l ON ls.lab_id = l.id
            LEFT JOIN allocated_labs al ON al.section = ls.section_name AND al.course_code = l.course_code
            WHERE l.id = :lab_id
            GROUP BY ls.section_name, l.teachers_required, l.credits
        ");
        $stmt->execute(['lab_id' => $lab_id]);
        $vacancies = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($vacancies);
        exit;
    } elseif ($_POST['action'] === 'allocate_labs') {
        $teacher_username = $_POST['teacher_username'];
        $course_code = $_POST['course_code'];
        $course_name = $_POST['course_name'];
        $credits = $_POST['credits'];
        $year = $_POST['year'];
        $sections = $_POST['sections'];

        // Insert into allocated_labs table
        $stmt = $pdo->prepare("
            INSERT INTO allocated_labs (teacher_username, course_code, course_name, credits, year, section)
            VALUES (:teacher_username, :course_code, :course_name, :credits, :year, :section)
        ");

        // Handle multiple sections
        $section_array = explode(',', $sections);
        $vacancy_check = true;

        // Check for vacancies before allocation
        foreach ($section_array as $section) {
            $section = trim($section);
            $vacancyStmt = $pdo->prepare("
                SELECT teachers_required, 
                       (teachers_required - COUNT(al.teacher_username)) AS available_vacancies
                FROM lab_sections ls
                JOIN labs l ON ls.lab_id = l.id
                LEFT JOIN allocated_labs al ON al.section = ls.section_name AND al.course_code = l.course_code
                WHERE ls.section_name = :section AND l.course_code = :course_code
                GROUP BY ls.section_name, l.teachers_required
            ");
            $vacancyStmt->execute(['section' => $section, 'course_code' => $course_code]);
            $vacancy = $vacancyStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($vacancy['available_vacancies'] <= 0) {
                $vacancy_check = false; // No vacancies available for this section
                break;
            }
        }

        if ($vacancy_check) {
            foreach ($section_array as $section) {
                $stmt->execute([
                    'teacher_username' => $teacher_username,
                    'course_code' => $course_code,
                    'course_name' => $course_name,
                    'credits' => $credits,
                    'year' => $year,
                    'section' => trim($section)
                ]);
            }

            // Update available hours in the teachers table
            $stmt = $pdo->prepare("
                UPDATE teachers SET available_hours = available_hours - :credits
                WHERE username = :teacher_username
            ");
            $stmt->execute(['credits' => $credits, 'teacher_username' => $teacher_username]);

            // Delete the application from teacher_lab_selections
            $stmt = $pdo->prepare("
                DELETE FROM teacher_lab_selections 
                WHERE teacher_username = :teacher_username AND course_code = :course_code
            ");
            $stmt->execute(['teacher_username' => $teacher_username, 'course_code' => $course_code]);

            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'One or more sections have no vacancies available. Please recheck and submit again.']);
        }
        exit;
    } elseif ($_POST['action'] === 'reject_application') {
        $teacher_username = $_POST['teacher_username'];
        $course_code = $_POST['course_code'];

        // Delete the application from teacher_lab_selections
        $stmt = $pdo->prepare("
            DELETE FROM teacher_lab_selections 
            WHERE teacher_username = :teacher_username AND course_code = :course_code
        ");
        $stmt->execute(['teacher_username' => $teacher_username, 'course_code' => $course_code]);

        echo json_encode(['status' => 'success']);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Labs</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        h1 {
            color: #A3113E; /* Your brand color */
            text-align: center;
        }

        .container {
            max-width: 800px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        select, input[type="text"], input[type="number"], input[type="submit"] {
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
            border: none;
        }

        input[type="submit"]:hover {
            background-color: #8A0E31; /* Darker shade for hover effect */
        }

        .card {
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 15px;
            margin: 10px 0;
            background-color: white;
            transition: box-shadow 0.3s;
        }

        .card h4 {
            margin: 0 0 10px;
        }

        .return-home {
            text-align: center;
            margin-top: 20px;
        }

        .return-home a {
            padding: 10px 15px;
            background-color: #A3113E; /* Your brand color */
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .return-home a:hover {
            background-color: #8A0E31; /* Darker shade for hover effect */
        }

         .teacher-container {
            margin: 20px 0;
        }

        .vacancy-container {
            display: flex; /* Use flexbox layout */
            flex-wrap: wrap; /* Allow wrapping of cards if needed */
            gap: 20px; /* Space between cards */
            justify-content: center; /* Align items to the start */
        }

        .vacancy-card {
            background-color: #f9f9f9; /* Card background color */
            border: 1px solid #ccc; /* Card border */
            border-radius: 8px; /* Rounded corners */
            padding: 16px; /* Inner spacing */
            width: 200px; /* Set a width for the cards */
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Subtle shadow */
        }


        .vacancy-card h5 {
            margin: 0;
            color: #007bff;
        }

        .instructions {
            font-style: italic;
            color: #555;
            margin-bottom: 15px;
        }

        .error-message, .success-message {
            font-weight: bold;
            margin-top: 10px;
        }

        .error-message {
            color: red;
        }

        .success-message {
            color: green;
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
    <div class="navbar">
        <img src="amrita logo light mode.png" alt="Logo" class="navbar-logo">
        <div class="navbar-links">
            <a href="hod_home.php" class="navbar-link">Home</a>
            <a href="assign_courses.php" class="navbar-link">Assign Courses</a>
            <a href="hod_lab_management.php" class="navbar-link">Lab Allocation Status</a>
            <a href="hod_courses_management.php" class="navbar-link">Course Allocation Status</a>
            <a href="view_available_workload.php" class="navbar-link">Available Workload</a>
            <a href="add_courses.php" class="navbar-link">Add Courses</a>
            <a href="add_lab.php" class="navbar-link">Add Labs</a>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;;&nbsp;&nbsp;&nbsp;
        </div>
    </div>
    <br><br><br><br><br><br>
<div class="container">
        <h2>Assign Labs</h2>
        <label for="lab_select">Select Lab:</label>
        <select id="lab_select">
            <option value="">--Select Lab--</option>
            <?php
            // Fetching labs from the database
            $labs_stmt = $pdo->query("SELECT * FROM labs");
            while ($lab = $labs_stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<option value='{$lab['id']}'>{$lab['lab_name']}</option>";
            }
            ?>
        </select>
        <div id="vacancy-container" class="vacancy-container"></div>
        <div id="teachers-container" class="teacher-container"></div>
        

        <div class="return-home">
            <a href="home.php">Return Home</a>
        </div>
    </div>


<script>
$(document).ready(function() {
    $('#lab_select').change(function() {
        var lab_id = $(this).val();
        if (lab_id) {
            // Fetch lab vacancies and teachers
            $.ajax({
                url: 'assign_labs.php',
                type: 'POST',
                data: {
                    action: 'fetch_teachers',
                    lab_id: lab_id
                },
                success: function(response) {
                    const teachers = JSON.parse(response);
                    $('#teachers-container').empty(); // Clear previous teacher cards

                    teachers.forEach(teacher => {
                        $('#teachers-container').append(`
                            <div class="card">
                                <h4>${teacher.teacher_username}</h4>
                                <p>Course Code: ${teacher.course_code}</p>
                                <p>Priority: ${teacher.priority}</p>
                                <p>Available Hours: ${teacher.available_hours}</p>
                                <label for="sections-${teacher.teacher_username}">Sections:</label>
                                <input type="text" id="sections-${teacher.teacher_username}" placeholder="Enter sections (comma separated)">
                                <button class="allocate-lab" data-username="${teacher.teacher_username}" data-course-code="${teacher.course_code}" data-course-name="Lab Name" data-credits="3" data-year="2024">Allocate</button>
                                <button class="reject-application" data-username="${teacher.teacher_username}" data-course-code="${teacher.course_code}">Reject</button>
                            </div>
                        `);
                    });
                },
                error: function() {
                    alert('Error fetching teacher data');
                }
            });

            // Fetch and display vacancies for each section
            $.ajax({
                url: 'assign_labs.php',
                type: 'POST',
                data: {
                    action: 'fetch_vacancies',
                    lab_id: lab_id
                },
                success: function(vacancyResponse) {
                    const vacancies = JSON.parse(vacancyResponse);
                    $('#vacancy-container').empty(); // Clear previous vacancy cards

                    vacancies.forEach(vacancy => {
                        $('#vacancy-container').append(`
                            <div class="card">
                                <h4>Section: ${vacancy.section_name}</h4>
                                <p>Teachers Required: ${vacancy.teachers_required}</p>
                                <p>Allocated: ${vacancy.allocated_count}</p>
                                <p>Credits: ${vacancy.credits}</p>
                                <p>Available Vacancies: ${vacancy.teachers_required - vacancy.allocated_count}</p>
                            </div>
                        `);
                    });
                },
                error: function() {
                    alert('Error fetching vacancy data');
                }
            });
        }
    });

    $(document).on('click', '.allocate-lab', function() {
        const username = $(this).data('username');
        const course_code = $(this).data('course-code');
        const course_name = $(this).data('course-name');
        const credits = $(this).data('credits');
        const year = $(this).data('year');
        const sections = $(`#sections-${username}`).val();

        if (sections) {
            $.ajax({
                url: 'assign_labs.php',
                type: 'POST',
                data: {
                    action: 'allocate_labs',
                    teacher_username: username,
                    course_code: course_code,
                    course_name: course_name,
                    credits: credits,
                    year: year,
                    sections: sections
                },
                success: function(response) {
                    const result = JSON.parse(response);
                    if (result.status === 'success') {
                        alert('Lab allocated successfully!');
                        location.reload(); // Refresh the page to update vacancies
                    } else {
                        alert(result.message); // Show error message if any section has no vacancies
                    }
                },
                error: function() {
                    alert('Error allocating lab');
                }
            });
        } else {
            alert('Please enter sections to allocate.');
        }
    });

    $(document).on('click', '.reject-application', function() {
        const username = $(this).data('username');
        const course_code = $(this).data('course-code');

        $.ajax({
            url: 'assign_labs.php',
            type: 'POST',
            data: {
                action: 'reject_application',
                teacher_username: username,
                course_code: course_code
            },
            success: function(response) {
                const result = JSON.parse(response);
                if (result.status === 'success') {
                    alert('Application rejected successfully!');
                    location.reload(); // Refresh the page to update vacancies
                } else {
                    alert('Failed to reject application');
                }
            },
            error: function() {
                alert('Error rejecting application');
            }
        });
    });
});
</script>
</body>
</html>
