<?php
session_start();

if (!isset($_SESSION['user_id'])) {
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

$user_id = $_SESSION['user_id'];


$sql = "SELECT o.id AS order_id, p.name, p.price, o.quantity, o.deliver_status 
        FROM orders o 
        JOIN products p ON o.product_id = p.id 
        WHERE o.user_id = ? AND o.deliver_status != 'delivered'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$items = $result->fetch_all(MYSQLI_ASSOC);


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['order_id']) && isset($_POST['deliver_status'])) {
    $order_id = $_POST['order_id'];
    $deliver_status = $_POST['deliver_status'];

    $sql_update_status = "UPDATE orders SET deliver_status = ? WHERE id = ?";
    $stmt_update_status = $conn->prepare($sql_update_status);
    $stmt_update_status->bind_param("si", $deliver_status, $order_id);
    $stmt_update_status->execute();

   
    $sql_check_status = "SELECT COUNT(*) AS not_delivered FROM orders WHERE user_id = ? AND deliver_status != 'delivered'";
    $stmt_check_status = $conn->prepare($sql_check_status);
    $stmt_check_status->bind_param("i", $user_id);
    $stmt_check_status->execute();
    $result_check = $stmt_check_status->get_result();
    $status_row = $result_check->fetch_assoc();

    if ($status_row['not_delivered'] == 0) {
        $sql_clear_cart = "DELETE FROM orders WHERE user_id = ?";
        $stmt_clear_cart = $conn->prepare($sql_clear_cart);
        $stmt_clear_cart->bind_param("i", $user_id);
        $stmt_clear_cart->execute();
    }
}


$notifications = [];
$hasUnreadNotifications = false;
$unreadCount = 0;


if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    
    $sql = "SELECT id, message, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
        if (!$row['is_read']) {
            $hasUnreadNotifications = true;
            $unreadCount++;
        }
    }
    $stmt->close();
}
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../page_header.css">
    <link rel="stylesheet" href="../styles/order_bill.css"> 
    <style>
       
        </style>
</head>
<body>
<header class="fixed-header">
    <div class="container">
        <div class="header-content">
            <div class="logo">
                <a href="../index.php"><img src="../logo.png" alt="The Gallery Café Logo"></a>
            </div>
            <nav>
                <ul>
                    <li class="headmenu_item"><a href="../index.php">Home</a></li>
                    <li class="headmenu_item"><a href="../php/menu.php">Menu</a></li>
                    <li class="headmenu_item"><a href="../php/preorder.php">Pre-order</a></li>
                    <li class="headmenu_item"><a href="../php/reservations.php">Reservations</a></li>
                    <li class="headmenu_item"><a href="../php/events.php">Events</a></li>
                    <li class="headmenu_item"><a href="../php/contact.php">Contact</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="headmenu_item_welcome">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</li>
                        <li><button class="logout-button"><a href="../php/logout.php">Logout</a></button></li>
                    <?php else: ?>
                        <li><button class="login-button"><a href="../pages/logIn.html">Login</a></button></li>
                    <?php endif; ?>
                    <li class="headmenu_item">
                        <a href="cart.php">
                            <i class="fas fa-shopping-cart"></i>
                        </a>
                    </li>
                    <li class="headmenu_item notification-icon">
                        <a id="notification-icon" href="#">
                            <i class="fas fa-bell" style="<?php echo $hasUnreadNotifications ? 'color: red;' : ''; ?>"></i>
                            <?php if ($hasUnreadNotifications): ?>
                                <span class="badge"><?php echo $unreadCount; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</header>

<h1>Your Order Details</h1>
<div class="order-details">
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Total</th>
                
            </tr>
        </thead>
        <tbody>
            <?php
            $total = 0;
            foreach ($items as $item) {
                $itemTotal = $item['price'] * $item['quantity'];
                $total += $itemTotal;
                echo '<tr>';
                echo '<td>' . htmlspecialchars($item['name']) . '</td>';
                echo '<td>Rs.' . htmlspecialchars($item['price']) . '</td>';
                echo '<td>' . htmlspecialchars($item['quantity']) . '</td>';
                echo '<td>Rs.' . htmlspecialchars($itemTotal) . '</td>';
               
                echo '</tr>';
            }
            ?>
            <tr>
                <td colspan="3"><strong>Total:</strong></td>
                <td>Rs.<?php echo $total; ?></td>
                <td></td>
            </tr>
        </tbody>
    </table>
</div>

<div class="linkmenu">
    <a href="cart.php">Go back to the cart</a>
</div>

<div id="notification-modal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Notifications</h2>
        <?php if (!empty($notifications)): ?>
            <?php foreach ($notifications as $notification): ?>
                <div class="<?php echo !$notification['is_read'] ? 'notification' : ''; ?>">
                    <p><?php echo htmlspecialchars($notification['message']); ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No notifications.</p>
        <?php endif; ?>
    </div>
</div>

<footer>
    <p>&copy; 2024 The Gallery Café. All rights reserved.</p>
</footer>

<script>
    var modal = document.getElementById("notification-modal");
    var modalClose = document.querySelector("#notification-modal .close");
    var notificationIcon = document.getElementById("notification-icon");

    notificationIcon.onclick = function() {
        modal.style.display = "block";
        if (<?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>) {
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "mark_notification_read.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onload = function() {
                if (xhr.status === 200) {
                    console.log("Notifications marked as read");
                } else {
                    console.error("Error marking notifications as read");
                }
            };
            xhr.send("user_id=<?php echo $_SESSION['user_id']; ?>");
        }
    }

    modalClose.onclick = function() {
        modal.style.display = "none";
    }

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
</script>
</body>
</html>
