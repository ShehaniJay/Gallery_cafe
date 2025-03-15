<?php
session_start();


$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gallery_cafe";

$conn = mysqli_connect($servername, $username, $password, $dbname);


if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$sql_events = "SELECT event_name, description, image, event_date, event_time FROM events";
$result_events = mysqli_query($conn, $sql_events);

if (!$result_events) {
    die("Query failed: " . mysqli_error($conn));
}

$hasUnreadNotifications = false;
$unreadCount = 0;
$notifications = [];

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

   
    $sql_notifications = "SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id = ? AND is_read = FALSE";
    $stmt = mysqli_prepare($conn, $sql_notifications);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        $unreadCount = $row['unread_count'];
        $hasUnreadNotifications = $unreadCount > 0;
    }

    mysqli_stmt_close($stmt);

    $sql_notifications_details = "SELECT id, message, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
    $stmt = mysqli_prepare($conn, $sql_notifications_details);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $notifications[] = $row;
    }

    mysqli_stmt_close($stmt);
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
    <link rel="stylesheet" href="../styles/events.css">
  
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

<div id="event_page">
<section id="events" class="events">
<h2>Upcoming Events</h2>
    <div class="container_event">

        <?php if (mysqli_num_rows($result_events) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result_events)): ?>
                <div class="event">
                    <h3 class="event_name"><?php echo htmlspecialchars($row['event_name']); ?></h3>
                    <img src="data:image/jpeg;base64,<?php echo base64_encode($row['image']); ?>" alt="<?php echo htmlspecialchars($row['event_name']); ?> Image">
                    <p class="description"><?php echo htmlspecialchars($row['description']); ?></p>
                    <p class="date_time">Date: <?php echo htmlspecialchars($row['event_date']); ?>, Time: <?php echo htmlspecialchars($row['event_time']); ?>.</p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No upcoming events found.</p>
        <?php endif; ?>
    </div>
</section>
</div>

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
    document.addEventListener('DOMContentLoaded', function() {
        const notificationIcon = document.getElementById('notification-icon');
        const notificationModal = document.getElementById('notification-modal');
        const closeModal = document.querySelector('.modal .close');

        if (notificationIcon) {
            notificationIcon.addEventListener('click', function(event) {
                event.preventDefault();
                notificationModal.style.display = 'block';

                // Mark notifications as read
                fetch('mark_notification_read.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        'user_id': '<?php echo $_SESSION['user_id']; ?>'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.querySelector('.notification-icon .badge').style.display = 'none';
                        document.querySelector('.notification-icon i').style.color = ''; 
                    }
                })
                .catch(error => console.error('Error:', error));
            });
        }

        if (closeModal) {
            closeModal.addEventListener('click', function() {
                notificationModal.style.display = 'none';
            });
        }

        window.addEventListener('click', function(event) {
            if (event.target === notificationModal) {
                notificationModal.style.display = 'none';
            }
        });
    });
</script>
</body>
</html>