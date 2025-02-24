<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'ai') {
    header('Location: login.php');
    exit();
}

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cs_courses = isset($_POST['ai_courses']) ? $_POST['ai_courses'] : [];
    $elective_courses = isset($_POST['elective_courses']) ? $_POST['elective_courses'] : [];
    
    // Store selected courses in the session
    $_SESSION['ai_courses'] = $ai_courses;
    $_SESSION['elective_courses'] = $elective_courses;
}

// Check if the courses are selected
if (empty($_SESSION['ai_courses']) || empty($_SESSION['elective_courses'])) {
    header('Location: ai_home.php'); // Redirect back if no courses were selected
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rank Selected Courses</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; }
        .sortable-list { padding: 0; list-style: none; }
        .sortable-list li { padding: 10px; margin: 5px 0; background: #f9f9f9; border: 1px solid #ccc; cursor: grab; border-radius: 3px; }
        button { background-color: #A3113E; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
    </style>
</head>
<body>
    <h1>Rank Your Selected Courses</h1>

    <h2>Rank CS Courses</h2>
    <ol id="aiCourses" class="sortable-list">
        <?php foreach ($_SESSION['ai_courses'] as $course): ?>
            <li data-course-id="<?php echo htmlspecialchars($course); ?>">
                <?php echo htmlspecialchars($course); ?>
            </li>
        <?php endforeach; ?>
    </ol>

    <h2>Rank Elective Courses</h2>
    <ol id="electiveCourses" class="sortable-list">
        <?php foreach ($_SESSION['elective_courses'] as $course): ?>
            <li data-course-id="<?php echo htmlspecialchars($course); ?>">
                <?php echo htmlspecialchars($course); ?>
            </li>
        <?php endforeach; ?>
    </ol>

    <button id="submitRanking">Finalize Ranking</button>
    <button onclick="window.location.href='ai_home.php';">Go Back</button>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>
    <script>
        const csCourses = Sortable.create(document.getElementById('aiCourses'), {
            animation: 150,
        });
        const electiveCourses = Sortable.create(document.getElementById('electiveCourses'), {
            animation: 150,
        });

        document.getElementById('submitRanking').addEventListener('click', function () {
    const csCoursesOrder = csCourses.toArray();
    const electiveCoursesOrder = electiveCourses.toArray();

    fetch('ai_submit_rankings.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ cs: csCoursesOrder, electives: electiveCoursesOrder })
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                throw new Error('Network response was not ok: ' + text);
            });
        }
        return response.json();
    })
    .then(result => {
        if (result.success) {
            alert('Ranking submitted successfully!');
            window.location.href = 'ai_home.php';
        } else {
            alert('Submission failed: ' + result.message);
        }
    })
    .catch(error => {
        alert('An error occurred: ' + error.message);
    });
});

    </script>
</body>
</html>
