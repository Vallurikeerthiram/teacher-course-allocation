<?php
session_start();
include 'database.php'; // Include your database connection

// Check published status
$sql = "SELECT published FROM allocation_status WHERE id = 1"; // Change as per your table structure
$result = mysqli_query($conn, $sql);
$isPublished = false;
if ($row = mysqli_fetch_assoc($result)) {
    $isPublished = $row['published'] == 1;
}

// If not published, redirect or show a message
if (!$isPublished) {
    header("Location: some_error_page.php"); // Redirect if allocations are not published
    exit();
}

// Fetch allocations from database (modify according to your structure)
$allocations = []; // Fetch your allocation data here
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Allocated</title>
</head>
<body>
    <h1>Allocated Courses</h1>
    <?php if (empty($allocations)): ?>
        <p>No allocations found.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($allocations as $allocation): ?>
                <li><?php echo htmlspecialchars($allocation['course_name']); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</body>
</html>
