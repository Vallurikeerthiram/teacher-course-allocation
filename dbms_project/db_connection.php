<?php
$servername = "localhost";
$username = "root"; // default WAMP username
$password = ""; // default WAMP password (usually empty)
$dbname = "dbms_project"; // your new database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
