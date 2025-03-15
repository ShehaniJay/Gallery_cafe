<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'staff') {
    header("Location: logIn.php");
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

if (isset($_GET['delete'])) {
    $reservation_id = mysqli_real_escape_string($conn, $_GET['delete']);
    $delete_query = "DELETE FROM reservations WHERE reservation_id='$reservation_id'";
    mysqli_query($conn, $delete_query);
    header("Location: staff_manage_reservation.php");
    exit();
}

if (isset($_POST['update_reservation'])) {
    $reservation_id = mysqli_real_escape_string($conn, $_POST['reservation_id']);
    $reservation_date = mysqli_real_escape_string($conn, $_POST['reservation_date']);
    $reservation_time = mysqli_real_escape_string($conn, $_POST['reservation_time']);
    $number_of_guests = mysqli_real_escape_string($conn, $_POST['number_of_guests']);
    $special_requests = mysqli_real_escape_string($conn, $_POST['special_requests']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    $update_query = "UPDATE reservations SET reservation_date='$reservation_date', reservation_time='$reservation_time', number_of_guests='$number_of_guests', special_requests='$special_requests', status='$status' WHERE reservation_id='$reservation_id'";
    mysqli_query($conn, $update_query);
    echo "success";
    exit();
}

if (isset($_POST['update_status'])) {
    $reservation_id = mysqli_real_escape_string($conn, $_POST['reservation_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    $status_query = "UPDATE reservations SET status='$status' WHERE reservation_id='$reservation_id'";
    mysqli_query($conn, $status_query);
    echo "success";
    exit();
}

$query = "SELECT * FROM reservations";
$result = mysqli_query($conn, $query);

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reservations</title>
    <link rel="stylesheet" href="../styles/admin_staff.css">
    <link rel="stylesheet" href="../styles/manage_staff_reservation.css">
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

        function editRow(button) {
            var row = button.parentNode.parentNode;
            var cells = row.getElementsByTagName('td');

            for (var i = 2; i < cells.length - 2; i++) {
                var cell = cells[i];
                var content = cell.innerText;
                cell.setAttribute('data-original-content', content);
                cell.innerHTML = '<input type="text" value="' + content + '">';
            }

            var statusContent = cells[cells.length - 2].innerText;
            cells[cells.length - 2].innerHTML = '<select>' +
                '<option value="Pending"' + (statusContent === 'Pending' ? ' selected' : '') + '>Pending</option>' +
                '<option value="Confirmed"' + (statusContent === 'Confirmed' ? ' selected' : '') + '>Confirmed</option>' +
                '<option value="Cancelled"' + (statusContent === 'Cancelled' ? ' selected' : '') + '>Cancelled</option>' +
                '</select>';

            cells[cells.length - 1].innerHTML = '<button class="save-button" onclick="saveRow(this)">Save</button> | <button class="cancel-button" onclick="cancelEdit(this)">Cancel</button>';
        }

        function saveRow(button) {
            var row = button.parentNode.parentNode;
            var cells = row.getElementsByTagName('td');

            var reservationId = cells[0].innerText;
            var reservationDate = cells[2].getElementsByTagName('input')[0].value;
            var reservationTime = cells[3].getElementsByTagName('input')[0].value;
            var numberOfGuests = cells[4].getElementsByTagName('input')[0].value;
            var specialRequests = cells[5].getElementsByTagName('input')[0].value;
            var status = cells[6].getElementsByTagName('select')[0].value;

            var formData = new FormData();
            formData.append('reservation_id', reservationId);
            formData.append('reservation_date', reservationDate);
            formData.append('reservation_time', reservationTime);
            formData.append('number_of_guests', numberOfGuests);
            formData.append('special_requests', specialRequests);
            formData.append('status', status);
            formData.append('update_reservation', 'true');

            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'staff_manage_reservation.php', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    alert('Reservation updated successfully!');
                    location.reload();
                } else {
                    alert('Error updating reservation.');
                }
            };
            xhr.send(formData);
        }

        function cancelEdit(button) {
            var row = button.parentNode.parentNode;
            var cells = row.getElementsByTagName('td');

            for (var i = 2; i < cells.length - 2; i++) {
                var cell = cells[i];
                var originalContent = cell.getAttribute('data-original-content');
                cell.innerHTML = originalContent;
            }

            var originalStatus = cells[cells.length - 2].getAttribute('data-original-content');
            cells[cells.length - 2].innerHTML = originalStatus;
            cells[cells.length - 1].innerHTML = '<button onclick="editRow(this)">Edit</button> | <a href="staff_manage_reservation.php?delete=' + cells[0].innerText + '" onclick="return confirm(\'Are you sure you want to delete this reservation?\')">Delete</a>';
        }

        function updateStatus(select, reservationId) {
            var status = select.value;

            var formData = new FormData();
            formData.append('reservation_id', reservationId);
            formData.append('status', status);
            formData.append('update_status', 'true');

            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'staff_manage_reservation.php', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    alert('Reservation status updated successfully!');
                } else {
                    alert('Error updating status.');
                }
            };
            xhr.send(formData);
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
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $reservation_id = $row['reservation_id'];
                        echo "<tr>";
                        echo "<td>" . $row['reservation_id'] . "</td>";
                        echo "<td>" . $row['user_id'] . "</td>";
                        echo "<td>" . $row['reservation_date'] . "</td>";
                        echo "<td>" . $row['reservation_time'] . "</td>";
                        echo "<td>" . $row['number_of_guests'] . "</td>";
                        echo "<td>" . $row['special_requests'] . "</td>";
                        echo "<td data-original-content='" . $row['status'] . "'>";
                        echo "<select onchange=\"updateStatus(this, '$reservation_id')\">";
                        echo "<option value='Pending'" . ($row['status'] == 'Pending' ? ' selected' : '') . ">Pending</option>";
                        echo "<option value='Confirmed'" . ($row['status'] == 'Confirmed' ? ' selected' : '') . ">Confirmed</option>";
                        echo "<option value='Cancelled'" . ($row['status'] == 'Cancelled' ? ' selected' : '') . ">Cancelled</option>";
                        echo "</select>";
                        echo "</td>";
                        echo "<td>";
                        echo "<button onclick=\"editRow(this)\">Edit</button> | ";
                        echo "<a href='staff_manage_reservation.php?delete=" . $row['reservation_id'] . "' onclick=\"return confirm('Are you sure you want to delete this reservation?')\">Delete</a>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='8'>No reservations found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
