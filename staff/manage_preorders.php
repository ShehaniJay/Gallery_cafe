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

$sql = "SELECT o.id, o.user_id, u.username AS customer_name, p.name AS product_name, o.quantity, o.status, o.deliver_status
        FROM orders o
        JOIN users u ON o.user_id = u.id
        JOIN products p ON o.product_id = p.id";
$result = $conn->query($sql);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm'])) {
        $order_id = intval($_POST['order_id']);
        $stmt = $conn->prepare("UPDATE orders SET status = 'confirmed' WHERE id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        sendNotification($order_id, 'confirmed');
    } elseif (isset($_POST['delete'])) {
        $order_id = intval($_POST['order_id']);
        $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
    } elseif (isset($_POST['edit_save'])) {
        $order_id = intval($_POST['order_id']);
        $quantity = intval($_POST['quantity']);
        $deliver_status = $_POST['deliver_status'];
        $status = $_POST['status'];

        $stmt = $conn->prepare("UPDATE orders SET quantity = ?, deliver_status = ?, status = ? WHERE id = ?");
        $stmt->bind_param("issi", $quantity, $deliver_status, $status, $order_id);
        $stmt->execute();

      
        if ($status === 'confirmed') {
            sendNotification($order_id, 'confirmed');
        }
    } elseif (isset($_POST['edit_quantity'])) {
        $order_id = intval($_POST['order_id']);
        $quantity = intval($_POST['quantity']);
        $stmt = $conn->prepare("UPDATE orders SET quantity = ? WHERE id = ?");
        $stmt->bind_param("ii", $quantity, $order_id);
        $stmt->execute();
    } elseif (isset($_POST['update_delivery_status'])) {
        $order_id = intval($_POST['order_id']);
        $deliver_status = $_POST['deliver_status'];
        $stmt = $conn->prepare("UPDATE orders SET deliver_status = ? WHERE id = ?");
        $stmt->bind_param("si", $deliver_status, $order_id);
        $stmt->execute();
    }
    header("Location: manage_preorders.php");
    exit();
}


function sendNotification($order_id, $status) {
    global $conn;
    $stmt = $conn->prepare("SELECT u.id, u.email FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $user_id = $user['id'];
    $email = $user['email'];

    $message = $status === 'confirmed' 
        ? "Your order with ID $order_id has been confirmed." 
        : "Your order with ID $order_id has not been confirmed.";

    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $message);
    $stmt->execute();

    
    mail($email, 'Order Status Update', $message);
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Preorders</title>
    <link rel="stylesheet" href="../styles/admin_staff.css">
    <link rel="stylesheet" href="../styles/manage_staff_preorders.css">
    <script>
        function searchPreorders() {
            var input = document.getElementById('searchInput');
            var filter = input.value.toLowerCase();
            var table = document.getElementById('preordersTable');
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

    
    var quantityCell = cells[3];
    var quantityContent = quantityCell.innerText;
    quantityCell.setAttribute('data-original-content', quantityContent);
    quantityCell.innerHTML = '<input type="number" value="' + quantityContent + '">';

    var statusCell = cells[4];
    var statusContent = statusCell.innerText;
    statusCell.setAttribute('data-original-content', statusContent);
    statusCell.innerHTML = '<select>' +
        '<option value="Pending"' + (statusContent === 'Pending' ? ' selected' : '') + '>Pending</option>' +
        '<option value="Confirmed"' + (statusContent === 'Confirmed' ? ' selected' : '') + '>Confirmed</option>' +
        '<option value="Cancelled"' + (statusContent === 'Cancelled' ? ' selected' : '') + '>Cancelled</option>' +
        '</select>';

    var deliverStatusCell = cells[5];
    var deliverStatusContent = deliverStatusCell.innerText;
    deliverStatusCell.setAttribute('data-original-content', deliverStatusContent);
    deliverStatusCell.innerHTML = '<select>' +
        '<option value="Not Delivered"' + (deliverStatusContent === 'Not Delivered' ? ' selected' : '') + '>Not Delivered</option>' +
        '<option value="Delivered"' + (deliverStatusContent === 'Delivered' ? ' selected' : '') + '>Delivered</option>' +
        '</select>';

    cells[cells.length - 1].innerHTML = '<button class="save-button" onclick="saveRow(this)">Save</button> | <button class="cancel-button" onclick="cancelEdit(this)">Cancel</button>';
}


function saveRow(button) {
    var row = button.parentNode.parentNode;
    var cells = row.getElementsByTagName('td');

    
    console.log(cells);

    var orderId = cells[0] ? cells[0].innerText : null;
    var quantityInput = cells[3] ? cells[3].getElementsByTagName('input')[0] : null;
    var deliverStatusSelect = cells[5] ? cells[5].getElementsByTagName('select')[0] : null;
    var statusSelect = cells[4] ? cells[4].getElementsByTagName('select')[0] : null;

    if (!quantityInput || !deliverStatusSelect || !statusSelect) {
        console.error("Could not find the required inputs or selects for the row.");
        alert("Error: Could not find the required inputs or selects for the row.");
        return;
    }

    var quantity = quantityInput.value;
    var deliverStatus = deliverStatusSelect.value;
    var status = statusSelect.value;

    console.log('Saving Order:', {orderId, quantity, deliverStatus, status}); 

    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'manage_preorders.php', true);
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            console.log('Response:', xhr.responseText); 
            alert('Order updated successfully!');
            location.reload();
        } else {
            console.error('Error:', xhr.status, xhr.statusText); 
            alert('Error updating order.');
        }
    };

    var data = 'order_id=' + encodeURIComponent(orderId) + 
               '&quantity=' + encodeURIComponent(quantity) + 
               '&deliver_status=' + encodeURIComponent(deliverStatus) + 
               '&status=' + encodeURIComponent(status) +
               '&edit_save=1';

    console.log('Sending Data:', data); 
    xhr.send(data);
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
    cells[cells.length - 1].innerHTML = '<button onclick="editRow(this)">Edit</button> | <form action="manage_preorders.php" method="post" style="display:inline;">' +
        '<input type="hidden" name="order_id" value="' + cells[0].innerText + '">' +
        '<button type="submit" name="confirm">Confirm</button>' +
        '</form> | <form action="manage_preorders.php" method="post" style="display:inline;">' +
        '<input type="hidden" name="order_id" value="' + cells[0].innerText + '">' +
        '<button type="submit" name="delete">Delete</button>' +
        '</form>';
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

    <div class="staff_manage-preorders">
        <div id="manage-preorders">
            <h1>Manage Preorders</h1>
            <input type="text" id="searchInput" onkeyup="searchPreorders()" placeholder="Search for preorders..">
            <table id="preordersTable">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer Name</th>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Status</th>
                        <th>Delivery Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0) : ?>
                        <?php while($row = $result->fetch_assoc()) : ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo $row['customer_name']; ?></td>
                                <td><?php echo $row['product_name']; ?></td>
                                <td><?php echo $row['quantity']; ?></td>
                                <td><?php echo $row['status']; ?></td>
                                <td><?php echo $row['deliver_status']; ?></td>
                                <td>
                                    <button onclick="editRow(this)">Edit</button> |
                                    <form action="manage_preorders.php" method="post" style="display:inline;">
                                        <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                                        
                                    </form> |
                                    <form action="manage_preorders.php" method="post" style="display:inline;">
                                        <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="delete">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="7">No preorders found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
