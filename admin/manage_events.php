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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $eventName = $_POST['eventName'];
    $description = $_POST['description'];
    $eventDate = $_POST['eventDate'];
    $eventTime = $_POST['eventTime'];

    // Retrieve and process the image file
    $image = $_FILES['image']['tmp_name'];
    $imgContent = addslashes(file_get_contents($image));

    // Insert new event into the database
    $sql = "INSERT INTO events (event_name, description, image, event_date, event_time) VALUES ('$eventName', '$description', '$imgContent', '$eventDate', '$eventTime')";

    // Execute the query and check if it was successful
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Event added successfully!'); window.location.href = 'view_event.php';</script>";
    } else {
        echo "<script>alert('Error: " . mysqli_error($conn) . "'); window.location.href = 'view_event.php';</script>";
    }
}

// Close the database connection
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrator Dashboard - The Gallery Café</title>
    <link rel="stylesheet" href="../styles/admin_staff.css">
    <link rel="stylesheet" href="../styles/admin_manage_events.css">
    <script>
        function validateEventForm() {
            var eventName = document.getElementById('eventName').value;
            var description = document.getElementById('description').value;
            var eventDate = document.getElementById('eventDate').value;
            var eventTime = document.getElementById('eventTime').value;
            var image = document.getElementById('image').value;

            if (eventName == "") {
                alert("Event name must be filled out");
                return false;
            }
            if (description == "") {
                alert("Event description must be filled out");
                return false;
            }
            if (eventDate == "") {
                alert("Event date must be selected");
                return false;
            }
            if (eventTime == "") {
                alert("Event time must be selected");
                return false;
            }
            if (image == "") {
                alert("Event image must be selected");
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
        <a href="admin_dashboard.html"><img src="../logo.png" alt="The Gallery Café Logo" class="sidebar-logo"></a>
            <h2 class="title_admin1">Admin Dashboard</h2>
        </div>
        <a href="manage_menu.php">Manage Menu</a>
        <a href="manage_preorders.php">Manage Pre-orders</a>
        <a href="manage_reservation.php">Manage Reservations</a>
        <a href="manage_events.php">Manage Events</a>
        <a href="manage_user.php">Manage Users</a>
        <a href="../php/logout.php" class="logout-button">Logout</a>
    </div>

    <div class="main-content">
        <h3 class="section-title">Add New Event</h3>
        <div class="form-container">
            <form action="manage_events.php" method="post" enctype="multipart/form-data" onsubmit="return validateEventForm()">
                <label for="eventName">Event Name:</label>
                <input type="text" id="eventName" name="eventName" class="form-input" required>

                <label for="description">Event Description:</label>
                <textarea id="description" name="description" class="form-textarea" required></textarea>

                <label for="eventDate">Event Date:</label>
                <input type="date" id="eventDate" name="eventDate" class="form-input" required>

                <label for="eventTime">Event Time:</label>
                <input type="time" id="eventTime" name="eventTime" class="form-input" required>

                <label for="image">Event Image:</label>
                <input type="file" id="image" name="image" class="form-input" accept="image/*" required>

                <button type="submit" class="submit-button">Add Event</button>
            </form>
        </div>
       

        <!-- View Events Button -->
        <form action="view_event.php" method="get">
            <button type="submit" class="view-events-button">View Events</button>
        </form>
    </div>

    <footer>
        <p>&copy; 2024 The Gallery Café. All rights reserved.</p>
    </footer>
</body>
</html>
