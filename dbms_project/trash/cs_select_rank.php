<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['user_role'] !== 'cs') {
    header('Location: login.php');
    exit();
}

include 'db_connection.php';

// Fetch CS courses
$cs_courses_query = "SELECT * FROM cs_courses";
$cs_courses_result = mysqli_query($conn, $cs_courses_query);
$cs_courses = mysqli_fetch_all($cs_courses_result, MYSQLI_ASSOC);

// Fetch Elective courses
$electives_query = "SELECT * FROM electives_courses";
$electives_result = mysqli_query($conn, $electives_query);
$electives_courses = mysqli_fetch_all($electives_result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CS Select & Rank Courses</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="styles.css"> <!-- Include your CSS file -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        h1, h2 { color: #A3113E; }
        .container { max-width: 1000px; margin: 0 auto; padding: 20px; }
        .course-list, .selected-course-list {
            background: white; border: 1px solid #ccc;
            padding: 15px; border-radius: 10px;
            margin-bottom: 20px; min-height: 200px;
        }
        .course-list h2, .selected-course-list h2 {
            margin-bottom: 10px;
        }
        .course-item {
            background: #f9f9f9;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 10px;
            border: 1px solid #e0e0e0;
            cursor: pointer;
        }
        .course-item.dragging {
            background-color: #A3113E;
            color: white;
            opacity: 0.7;
        }
        .drop-zone {
            border: 2px dashed #ccc;
            min-height: 150px;
            padding: 10px;
            border-radius: 10px;
        }
        button {
            background-color: #A3113E;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            transition: background-color 0.3s ease;
            cursor: pointer;
        }
        button:hover {
            background-color: #8b0e35;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Select and Rank CS and Elective Courses</h1>

    <!-- CS Courses Section -->
    <div class="course-list">
        <h2>CS Courses</h2>
        <div id="csCourses" class="drop-zone">
            <?php foreach ($cs_courses as $course): ?>
                <div class="course-item" draggable="true" data-course-code="<?php echo $course['course_code']; ?>">
                    <?php echo htmlspecialchars($course['course_name']); ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Selected CS Courses -->
    <div class="selected-course-list">
        <h2>Selected CS Courses (Drag to rank)</h2>
        <div id="selectedCsCourses" class="drop-zone"></div>
    </div>

    <!-- Elective Courses Section -->
    <div class="course-list">
        <h2>Elective Courses</h2>
        <div id="electiveCourses" class="drop-zone">
            <?php foreach ($electives_courses as $course): ?>
                <div class="course-item" draggable="true" data-course-code="<?php echo $course['course_code']; ?>">
                    <?php echo htmlspecialchars($course['course_name']); ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Selected Elective Courses -->
    <div class="selected-course-list">
        <h2>Selected Elective Courses (Drag to rank)</h2>
        <div id="selectedElectiveCourses" class="drop-zone"></div>
    </div>

    <button id="submitBtn">Finalize and Submit</button>
</div>

<script>
    // Drag and Drop Functionality
    const draggables = document.querySelectorAll('.course-item');
    const dropZones = document.querySelectorAll('.drop-zone');

    let draggedItem = null;

    draggables.forEach(item => {
        item.addEventListener('dragstart', () => {
            draggedItem = item;
            item.classList.add('dragging');
        });

        item.addEventListener('dragend', () => {
            draggedItem = null;
            item.classList.remove('dragging');
        });
    });

    dropZones.forEach(zone => {
        zone.addEventListener('dragover', (e) => {
            e.preventDefault();
        });

        zone.addEventListener('drop', () => {
            if (draggedItem && zone !== draggedItem.parentElement) {
                zone.appendChild(draggedItem);
            }
        });
    });

    // Handle Form Submission
    document.getElementById('submitBtn').addEventListener('click', () => {
        const selectedCsCourses = Array.from(document.getElementById('selectedCsCourses').children)
            .map((item, index) => ({
                course_code: item.getAttribute('data-course-code'),
                priority: index + 1,
                department: 'CS'
            }));

        const selectedElectiveCourses = Array.from(document.getElementById('selectedElectiveCourses').children)
            .map((item, index) => ({
                course_code: item.getAttribute('data-course-code'),
                priority: index + 1,
                department: 'Electives'
            }));

        const allSelections = [...selectedCsCourses, ...selectedElectiveCourses];

        fetch('cs_submit_rankings.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ selections: allSelections })
        })
        .then(response => response.json())
        .then(data => {
            alert('Courses submitted successfully');
            window.location.href = 'cs_home.php';
        })
        .catch(error => {
            console.error('Error:', error);
        });
    });
</script>
</body>
</html>
