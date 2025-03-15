<?php
session_start();

// Check if the user is logged in as admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: logIn.html");
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

// Handle reservation deletion
if (isset($_GET['delete'])) {
    $reservation_id = mysqli_real_escape_string($conn, $_GET['delete']);

    $delete_sql = "DELETE FROM reservations WHERE reservation_id = '$reservation_id'";
    if (mysqli_query($conn, $delete_sql)) {
        echo "<script>alert('Reservation deleted successfully!'); window.location.href = 'manage_reservation.php';</script>";
    } else {
        echo "<script>alert('Error deleting reservation: " . mysqli_error($conn) . "'); window.location.href = 'manage_reservation.php';</script>";
    }
}

// Fetch all reservations
$sql = "SELECT * FROM reservations";
$result = mysqli_query($conn, $sql);

// Close the database connection
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reservations</title>
    <link rel="stylesheet" href="../styles/admin_staff.css">
    <link rel="stylesheet" href="../styles/manage_reservation.css">
    <script>
        function searchReservations() {
            var input = document.getElementById('searchInput');
            var filter = input.value.toLowerCase();
            var table = document.getElementById('reservationsTable');
            var tr = table.getElementsByTagName('tr');

            for (var i = 1; i < tr.length; i++) {
                tr[i].style.display = 'none';
                var td = tr[i].getElementsByTagName('td');
                for (var j = 0; j < td.length; j++) {
                    if (td[j]) {
                        if (td[j].innerHTML.toLowerCase().indexOf(filter) > -1) {
                            tr[i].style.display = '';
                            break;
                        }
                    }
                }
            }
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
        <a href="manage_users.php">Manage Users</a>
        <a href="../php/logout.php" class="logout-button">Logout</a>
    </div>

    <div class="main-content">
        <h2>View Reservations</h2>
        <input type="text" id="searchInput" onkeyup="searchReservations()" placeholder="Search for reservations..">
        <table id="reservationsTable">
            <thead>
                <tr>
                    <th>Reservation ID</th>
                    <th>User ID</th>
                    <th>Reservation Date</th>
                    <th>Reservation Time</th>
                    <th>Number of Guests</th>
                    <th>Special Requests</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td>" . $row['reservation_id'] . "</td>";
                        echo "<td>" . $row['user_id'] . "</td>";
                        echo "<td>" . $row['reservation_date'] . "</td>";
                        echo "<td>" . $row['reservation_time'] . "</td>";
                        echo "<td>" . $row['number_of_guests'] . "</td>";
                        echo "<td>" . $row['special_requests'] . "</td>";
                        echo "<td><a href='manage_reservation.php?delete=" . $row['reservation_id'] . "' onclick=\"return confirm('Are you sure you want to delete this reservation?');\">Delete</a></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>No reservations found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <footer>
        <p>&copy; 2024 The Gallery Café. All rights reserved.</p>
    </footer>
</body>
</html>
