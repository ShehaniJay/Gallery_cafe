<?php
// Start the session
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

// Query to select events
$sql = "SELECT event_id, event_name, description, image, event_date, event_time FROM events";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Events</title>
    <link rel="stylesheet" href="../styles/view_products.css">
    
    <script>
        function searchEvents() {
            var input = document.getElementById('searchInput');
            var filter = input.value.toLowerCase();
            var table = document.getElementById('eventsTable');
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

        function editRow(row) {
            var cells = row.getElementsByTagName('td');
            for (var i = 1; i < cells.length - 2; i++) {
                var cell = cells[i];
                var content = cell.innerText;
                cell.setAttribute('data-original-content', content);
                cell.innerHTML = '<input type="text" value="' + content + '">';
            }
            var imageCell = cells[cells.length - 2];
            imageCell.setAttribute('data-original-content', imageCell.innerHTML);
            imageCell.innerHTML = '<input type="file" accept="image/*">';

            cells[cells.length - 1].innerHTML = '<button class="save-button" onclick="saveRow(this)">Save</button> | <button class="cancel-button" onclick="cancelEdit(this)">Cancel</button>';
        }

        function saveRow(button) {
            var row = button.parentNode.parentNode;
            var cells = row.getElementsByTagName('td');

            var id = cells[0].innerText;
            var name = cells[1].getElementsByTagName('input')[0].value;
            var description = cells[2].getElementsByTagName('input')[0].value;
            var date = cells[3].getElementsByTagName('input')[0].value;
            var time = cells[4].getElementsByTagName('input')[0].value;
            var image = cells[5].getElementsByTagName('input')[0].files[0];

            var formData = new FormData();
            formData.append('eventId', id);
            formData.append('eventName', name);
            formData.append('description', description);
            formData.append('eventDate', date);
            formData.append('eventTime', time);
            formData.append('image', image);

            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'update_event.php', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    alert('Event updated successfully!');
                    location.reload();
                } else {
                    alert('Error updating event.');
                }
            };
            xhr.send(formData);
        }

        function cancelEdit(button) {
            var row = button.parentNode.parentNode;
            var cells = row.getElementsByTagName('td');

            for (var i = 1; i < cells.length - 2; i++) {
                var cell = cells[i];
                var originalContent = cell.getAttribute('data-original-content');
                cell.innerHTML = originalContent;
            }

            var imageCell = cells[cells.length - 2];
            var originalImage = imageCell.getAttribute('data-original-content');
            imageCell.innerHTML = '<img src="' + originalImage + '" alt="Event Image" class="product-image"/>';

            cells[cells.length - 1].innerHTML = '<button class="edit-button" onclick="editRow(this.parentNode.parentNode)">Edit</button> | <a class="delete-link" href="view_event.php?delete=' + cells[0].innerText + '" onclick="return confirm(\'Are you sure you want to delete this event?\')">Delete</a>';
        }
    </script>
</head>
<body>
    <div class="view-products-container">
        <h2>Available Events</h2>
        <input type="text" id="searchInput" onkeyup="searchEvents()" placeholder="Search for events..">
        <table id="eventsTable">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Date</th>
                <th>Time</th>
                <th>Image</th>
                <th>Actions</th>
            </tr>
            <?php
            // Check if there are any events in the result
            if (mysqli_num_rows($result) > 0) {
                // Fetch and display each event
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td>" . $row['event_id'] . "</td>";
                    echo "<td>" . $row['event_name'] . "</td>";
                    echo "<td>" . $row['description'] . "</td>";
                    echo "<td>" . $row['event_date'] . "</td>";
                    echo "<td>" . $row['event_time'] . "</td>";
                    echo '<td><img src="data:image/jpeg;base64,' . base64_encode($row['image']) . '" alt="Event Image" class="product-image"/></td>';
                    echo '<td class="actions">';
                    echo '<button onclick="editRow(this.parentNode.parentNode)">Edit</button> | ';
                    echo '<a href="delete_event.php?delete=' . $row['event_id'] . '" onclick="return confirm(\'Are you sure you want to delete this event?\')">Delete</a>';
                    echo '</td>';
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='7'>No events found</td></tr>";
            }
            // Close the database connection
            mysqli_close($conn);
            ?>
        </table>
        <a href="admin_dashboard.html" class="back-link">Back to Admin Dashboard</a>
    </div>
</body>
</html>
