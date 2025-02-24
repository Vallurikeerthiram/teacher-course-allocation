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
$message = '';
$isPublished = false;
function verifyHodPassword($password) {
    $hodPassword = 'amma'; // Change this as necessary
    return $password === $hodPassword;
}
// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hodPassword = $_POST['hod_password'];

    if (verifyHodPassword($hodPassword)) {
        // Update published status in the database
        $sql = "UPDATE allocation_status SET published = 1 WHERE id = 1"; // Assuming a single row for status
        if (mysqli_query($conn, $sql)) {
            $message = 'Allocations published successfully!';
            $isPublished = true;
        } else {
            $message = 'Error publishing allocations: ' . mysqli_error($conn);
        }
    } else {
        $message = 'Invalid password. Please try again.';
    }
}

// Check published status
$sql = "SELECT published FROM allocation_status WHERE id = 1"; // Change as per your table structure
$result = mysqli_query($conn, $sql);
if ($row = mysqli_fetch_assoc($result)) {
    $isPublished = $row['published'] == 1;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publish Allocations</title>
</head>
<body>
    <h1>Publish Allocations</h1>
    <form method="POST" action="">
        <label for="hod_password">HOD Password:</label>
        <input type="password" id="hod_password" name="hod_password" required>
        <button type="submit">Publish Allocations</button>
    </form>
    <p><?php echo $message; ?></p>
    
    <h2>Status:</h2>
    <p><?php echo $isPublished ? 'Allocations are published.' : 'Allocations are not published.'; ?></p>

    <script>
        // Disable view_allocated.php based on published status
        if (<?php echo json_encode($isPublished); ?>) {
            // This can be modified to disable buttons in your other modules
            document.getElementById('submit_rankings_ai').disabled = true; // AI module button ID
            document.getElementById('submit_rankings_cs').disabled = true; // CS module button ID
        }
    </script>
</body>
</html>
