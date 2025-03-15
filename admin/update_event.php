<?php
session_start();

// Check if the user is logged in as admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: logIn.php");
    exit();
}

// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gallery_cafe";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Handle event update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eventId = $_POST['eventId'];
    $eventName = $_POST['eventName'];
    $description = $_POST['description'];
    $eventDate = $_POST['eventDate'];
    $eventTime = $_POST['eventTime'];

    // Retrieve and process the image file if uploaded
    $image = $_FILES['image']['tmp_name'];
    if ($image) {
        $imgContent = addslashes(file_get_contents($image));
        $update_sql = "UPDATE events SET event_name='$eventName', description='$description', image='$imgContent', event_date='$eventDate', event_time='$eventTime' WHERE event_id='$eventId'";
    } else {
        $update_sql = "UPDATE events SET event_name='$eventName', description='$description', event_date='$eventDate', event_time='$eventTime' WHERE event_id='$eventId'";
    }

    // Execute the query and check if it was successful
    if (mysqli_query($conn, $update_sql)) {
        echo "<script>alert('Event updated successfully!'); window.location.href = 'view_event.php';</script>";
    } else {
        echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
    }
}

// Close the database connection
mysqli_close($conn);
?>
