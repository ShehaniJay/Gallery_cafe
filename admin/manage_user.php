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

// Handle user deletion
if (isset($_GET['delete'])) {
    $user_id = $_GET['delete'];

    $delete_sql = "DELETE FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $delete_sql);
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<script>alert('User deleted successfully!'); window.location.href = 'manage_user.php';</script>";
    } else {
        echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
    }
    mysqli_stmt_close($stmt);
}

// Fetch all users
$sql = "SELECT id, name, email, phone, username, role FROM users";
$result = mysqli_query($conn, $sql);

// Close the database connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link rel="stylesheet" href="../styles/admin_staff.css">
    <link rel="stylesheet" href="../styles/manage_user.css">
    
    <script>
        function searchUsers() {
            var input = document.getElementById('searchInput');
            var filter = input.value.toLowerCase();
            var table = document.getElementById('usersTable');
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

        function cancelEdit(button) {
            var row = button.parentNode.parentNode;
            var cells = row.getElementsByTagName('td');

            for (var i = 1; i < cells.length - 1; i++) {
                var cell = cells[i];
                var originalContent = cell.getAttribute('data-original-content');
                cell.innerHTML = originalContent;
            }

            cells[cells.length - 1].innerHTML = '<button class="edit-button" onclick="editRow(this.parentNode.parentNode)">Edit</button> | <button class="delete-button" onclick="confirmDelete(this.parentNode.parentNode)">Delete</button>';
        }

        function confirmDelete(row) {
            var cells = row.getElementsByTagName('td');
            var userId = cells[0].innerText;

            if (confirm('Are you sure you want to delete this user?')) {
                window.location.href = 'manage_user.php?delete=' + userId;
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
        <a href="manage_user.php">Manage Users</a>
        <a href="../php/logout.php" class="logout-button">Logout</a>
    </div>

    <div class="main-content">
        <h2>Manage Users</h2>
        <input type="text" id="searchInput" onkeyup="searchUsers()" placeholder="Search for users..">
        <table id="usersTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td>" . $row['id'] . "</td>";
                        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['phone']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['role']) . "</td>";
                        echo '<td><button class="delete-button" onclick="confirmDelete(this.parentNode.parentNode)">Delete</button></td>';
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>No users found.</td></tr>";
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
