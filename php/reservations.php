<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    $message = urlencode("Please log in to make reservations.");
    header("Location: ../pages/logIn.html?message=$message");
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

$notifications = [];
$hasUnreadNotifications = false;
$unreadCount = 0;

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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $reservation_date = $_POST['reservation_date'];
    $reservation_time = $_POST['reservation_time'];
    $number_of_guests = $_POST['number_of_guests'];
    $special_requests = $_POST['special_requests'];

    $sql = "INSERT INTO reservations (user_id, reservation_date, reservation_time, number_of_guests, special_requests) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issii", $user_id, $reservation_date, $reservation_time, $number_of_guests, $special_requests);

    if ($stmt->execute()) {
        $reservation_id = $stmt->insert_id; 
        echo "<script>
                alert('Reservation successful! Your Reservation ID is: $reservation_id');
                window.location.href = 'reservations.php'; // Refresh the page
              </script>";
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Gallery Café</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../page_header.css">
    <link rel="stylesheet" href="../styles/reservations.css">
    
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
                    <li class="headmenu_item"><a href="menu.php">Menu</a></li>
                    <li class="headmenu_item"><a href="preorder.php">Pre-order</a></li>
                    <li class="headmenu_item"><a href="reservations.php">Reservations</a></li>
                    <li class="headmenu_item"><a href="events.php">Events</a></li>
                    <li class="headmenu_item"><a href="contact.php">Contact</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="headmenu_item_welcome">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</li>
                        <li><button class="logout-button"><a href="logout.php">Logout</a></button></li>
                    <?php else: ?>
                    <li><button class="login-button"><a href="../pages/logIn.html">Login</a></button></li>
                    <?php endif; ?>
                    <li class="headmenu_item">
                        <a href="../orders/cart.php">
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

<section id="reservations" class="reservations">
    <div class="container">
        <h2>Table Reservations</h2>
        <div class="reservation-images">
            <img src="../image/reservetable.jpeg" alt="Table Setting" class="reservation-image">
            <img src="../image/tale.jpg" alt="Dining Table" class="reservation-image">
            <img src="../image/reserve.jpg" alt="Dining Table" class="reservation-image">
        </div>
        
        <form id="reservationForm" action="reservations.php" method="post">
            <label for="date">Date:</label>
            <input type="date" id="date" name="reservation_date" required>
            <label for="time">Time:</label>
            <input type="time" id="time" name="reservation_time" required>
            <label for="guests">Number of Guests:</label>
            <input type="number" id="guests" name="number_of_guests" required>
            <label for="special_requests">Special Requests:</label>
            <textarea id="special_requests" name="special_requests"></textarea><br>
            <button type="submit">Reserve</button>
        </form>
        
    </div>
</section>

<!-- The Notification Modal -->
<div id="notification-modal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Notifications</h2>
        <?php if (!empty($notifications)): ?>
            <?php foreach ($notifications as $notification): ?>
                <div class="<?php echo $notification['is_read'] ? '' : 'notification'; ?>">
                    <p><?php echo htmlspecialchars($notification['message']); ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No notifications.</p>
        <?php endif; ?>
    </div>
</div>

<footer>
    <div class="container">
        <p>&copy; 2024 The Gallery Café. All rights reserved.</p>
    </div>
</footer>

<script>
    var modal = document.getElementById("notification-modal");
    var modalClose = document.querySelector(".modal .close");
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
            xhr.send("user_id=<?php echo $user_id; ?>");
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
