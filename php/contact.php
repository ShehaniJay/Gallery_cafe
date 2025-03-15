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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    $sql = "INSERT INTO contacts (name, email, subject, message) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $name, $email, $subject, $message);

    if ($stmt->execute()) {
        echo "<script>alert('Message sent successfully!');</script>";
    } else {
        echo "<script>alert('Error: " . $stmt->error . "');</script>";
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
    <title>The Gallery Café - Contact Us</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../page_header.css">
    <link rel="stylesheet" href="../styles/contact.css"> 
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
    
    <section id="contact" class="contact">
        <div class="contact-container">
            <h2>Contact Us</h2>
            <div class="contact-info">
                <p>Email: <a href="mailto:thegallerycafe@gmail.com">thegallerycafe@gmail.com</a></p>
                <p>Phone: <a href="tel:+94123456789">+94 123 456 789</a></p>
                <p>Address: No. 123, Main Street, Colombo, Sri Lanka</p>
                <img src="../image/cafe.jpg" alt="The Gallery Café" />
            </div>
            <h3>Send Us a Message</h3>
            <form action="contact.php" method="post">
                <div>
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div>
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div>
                    <label for="subject">Subject:</label>
                    <input type="text" id="subject" name="subject" required>
                </div>
                <div>
                    <label for="message">Message:</label>
                    <textarea id="message" name="message" rows="5" required></textarea>
                </div>
                <div>
                    <button type="submit">Send Message</button>
                </div>
            </form>
        </div>
    </section>

    <div id="notification-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Notifications</h2>
            <?php
            if (isset($_SESSION['user_id'])) {
                foreach ($notifications as $notification) {
                    $class = $notification['is_read'] ? '' : 'notification';
                    echo '<div class="' . $class . '">';
                    echo '<p>' . htmlspecialchars($notification['message']) . '</p>';
                    echo '</div>';
                }
            }
            ?>
        </div>
    </div>

    <footer>
        <p>&copy; 2024 The Gallery Café. All rights reserved.</p>
    </footer>

    <script>
        var modal = document.getElementById("notification-modal");
        var icon = document.getElementById("notification-icon");
        var span = document.getElementsByClassName("close")[0];
        icon.onclick = function() {
            modal.style.display = "block";
            fetch('mark_notification_read.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        icon.querySelector('i').style.color = ''; 
                        document.querySelector('.badge').style.display = 'none'; 
                    }
                });
        }

        
        span.onclick = function() {
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
