<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../pages/logIn.html");
    exit();
}


$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gallery_cafe";


$conn = mysqli_connect($servername, $username, $password, $dbname);


if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}


$sql = "SELECT id, name, email, subject, message, created_at FROM contacts";
$result = $conn->query($sql);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $contact_id = intval($_POST['contact_id']);
    $stmt = $conn->prepare("DELETE FROM contacts WHERE id = ?");
    $stmt->bind_param("i", $contact_id);
    $stmt->execute();
    header("Location: manage_contact.php");
    exit();
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Contact Form Entries</title>
    <link rel="stylesheet" href="../styles/admin_staff.css">
    <link rel="stylesheet" href="../styles/manage_contact.css">
    <script>
        function searchContacts() {
            var input = document.getElementById('searchInput');
            var filter = input.value.toLowerCase();
            var table = document.getElementById('contactsTable');
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
            <a href="staff_dashboard.html"><img src="../logo.png" alt="The Gallery Café Logo" class="sidebar-logo"></a>
            <h2 class="title_admin1">Staff Dashboard</h2>
        </div>
        <a href="manage_preorders.php">Manage Pre-orders</a>
        <a href="staff_manage_reservation.php">Manage Reservations</a>
        <a href="manage_contact.php">Manage Contact</a>
        <a href="../php/logout.php" class="logout-button">Logout</a>
    </div>

    <div class="staff_manage-contact">
        <div id="manage-contact">
            <h1>Manage Contact Form Entries</h1>
            <input type="text" id="searchInput" onkeyup="searchContacts()" placeholder="Search for contacts..">
            <table id="contactsTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Subject</th>
                        <th>Message</th>
                        <th>Date Submitted</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0) : ?>
                        <?php while($row = $result->fetch_assoc()) : ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo $row['name']; ?></td>
                                <td><?php echo $row['email']; ?></td>
                                <td><?php echo $row['subject']; ?></td>
                                <td><?php echo $row['message']; ?></td>
                                <td><?php echo $row['created_at']; ?></td>
                                <td>
                                    <form action="manage_contact.php" method="post" style="display:inline;">
                                        <input type="hidden" name="contact_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="delete">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="7">No contact entries found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
