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

// Handle event deletion
if (isset($_GET['delete'])) {
    $event_id = $_GET['delete'];

    // Delete the event from the database
    $delete_sql = "DELETE FROM events WHERE event_id = '$event_id'";
    if (mysqli_query($conn, $delete_sql)) {
        echo "<script>alert('Event deleted successfully!'); window.location.href = 'view_event.php';</script>";
    } else {
        echo "<script>alert('Error deleting event: " . mysqli_error($conn) . "'); window.location.href = 'view_event.php';</script>";
    }
} else {
    echo "<script>alert('No event ID provided.'); window.location.href = 'view_event.php';</script>";
}

// Close the database connection
mysqli_close($conn);
?>
