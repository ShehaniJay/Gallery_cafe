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


$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;


$hasUnreadNotifications = false;
$unreadCount = 0;
if ($user_id) {
    $sql = "SELECT COUNT(*) as unread_count FROM notifications WHERE user_id = ? AND is_read = FALSE";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $unreadCount = $row['unread_count'];
        $hasUnreadNotifications = $unreadCount > 0;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>The Gallery Café</title>
    <link rel="stylesheet" href="index.css"> 
    <style>
   

    </style>
</head>
<body>
    <header class="fixed-header">
        <div class="container">
            <div class="logo">
                <a href="index.php"><img src="logo.png" alt="The Gallery Café Logo"></a>
            </div>
            <nav>
                <ul>
                    <li class="menu_item"><a href="index.php">Home</a></li>
                    <li class="menu_item"><a href="php/menu.php">Menu</a></li>
                    <li class="menu_item"><a href="php/preorder.php">Pre-order</a></li>
                    <li class="menu_item"><a href="php/reservations.php">Reservations</a></li>
                    <li class="menu_item"><a href="php/events.php">Events</a></li>
                    <li class="menu_item"><a href="php/contact.php">Contact</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="menu_item_welcome">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</li>
                        <li><button class="logout-button"><a href="php/logout.php">Logout</a></button></li>
                    <?php else: ?>
                        <li><button class="login-button"><a href="pages/logIn.html">Login</a></button></li>
                    <?php endif; ?>
                    <li class="headmenu_item">
                        <a href="orders/cart.php">
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
    </header>

   
      <!-- The Notification Modal -->
      <div id="notification-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Notifications</h2>
            <?php
            if ($user_id) {
                $sql = "SELECT id, message, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $class = $row['is_read'] ? '' : 'notification';
                        echo '<div class="' . $class . '">';
                        echo '<p>' . htmlspecialchars($row['message']) . '</p>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>No notifications.</p>';
                }
                $stmt->close();
            }
            ?>
        </div>
    </div>

    <section id="index" class="hero">
        <div class="container">
            <h1>Welcome to The Gallery Café</h1>
            <p>Experience the finest dining with a touch of elegance.</p><br><br>
            <button onclick="document.getElementById('about').scrollIntoView({ behavior: 'smooth' });">About us</button>
        </div>
    </section>
    <section id="about">
        <div class="container">
            <h1>About The Gallery Café</h1>
            <p>
                Welcome to The Gallery Café, a unique dining experience where culinary art meets visual art. Nestled in the heart of the city Colombo, our café offers a serene ambiance perfect for any occasion.
            </p>
            <h2>Our Story</h2>
            <p>
                Established in 2018, The Gallery Café was founded with the vision of creating a space where food lovers and art enthusiasts can come together. Our café is adorned with beautiful artwork from local artists, providing a feast for the eyes while you enjoy our diverse menu.
            </p>
            <img src="image/gallerycafe.jpg" alt="Our Story" class="about-image">
            <h2>Our Cuisine</h2>
            <p>
                At The Gallery Café, we take pride in offering a wide variety of cuisines to cater to all tastes. Whether you're in the mood for authentic Sri Lankan dishes, classic Italian pasta, flavorful Chinese cuisine, or refreshing beverages, we have something for everyone. Each dish is prepared with the finest ingredients and utmost care to ensure a memorable dining experience.
            </p>
            <div class="cuisine-images">
                <div class="cuisine-image-container">
                    <img src="image/Srilankan Cuisine.jpg" alt="Sri Lankan Cuisine" class="cuisine-image">
                    <p>Sri Lankan Cuisine</p>
                </div>
                <div class="cuisine-image-container">
                    <img src="image/italiancuisine.jpg" alt="Italian Cuisine" class="cuisine-image">
                    <p>Italian Cuisine</p>
                </div>
                <div class="cuisine-image-container">
                    <img src="image/Chinese Cuisine.webp" alt="Chinese Cuisine" class="cuisine-image">
                    <p>Chinese Cuisine</p>
                </div>
            </div>
            
            <h2>Our Commitment</h2>
            <p>
                We are committed to providing excellent service and a warm, welcoming atmosphere. Our team is dedicated to making your visit enjoyable and ensuring that you leave with a smile. We believe in supporting local artists and regularly feature their work in our café, creating a vibrant and dynamic environment for our guests.
            </p>
            <img src="image/commitment.jpg" alt="Our Commitment" class="about-image">
            <h2>Current Promotions</h2>
        <p>
            We are excited to offer the following promotions to our valued customers:
        </p>
        <div class="promotion-box">
        <div class="promotion">
            <h3>Happy Hour Special</h3>
            <p>Enjoy 30% off on Sri Lankan cuisine every weekday from 4 PM to 6 PM.</p>
            <img src="image/offer.jpg" alt="Happy Hour" class="promotion-image">
        </div>
        <div class="promotion">
            <h3>Weekend Brunch</h3>
            <p>Join us for a delightful weekend brunch with a free beverage.</p>
            <img src="image/freebev.jpeg" alt="Weekend Brunch" class="promotion-image">
        </div>
        <div class="promotion">
            <h3>Gallery Lover's Discount</h3>
            <p>Show your gallery coupon and get a 10% discount on your meal.</p>
            <img src="image/discout.jpg" alt="Art Lover's Discount" class="promotion-image">
        </div>
        </div>
        <h2>Join Us</h2>
            <p>
                We invite you to join us at The Gallery Café and experience the perfect blend of great food and beautiful art. Whether you're here for a casual meal, a special celebration, or just to enjoy a cup of coffee, we look forward to serving you.
            </p>
        </div>
        </div>
    </section>


    <footer>
        <div class="container">
            <p>&copy; 2024 The Gallery Café. All rights reserved.</p>
        </div>
    </footer>
    <script>
        // Get modal element
        var modal = document.getElementById("notification-modal");
        var modalClose = document.querySelector(".modal .close");

        // Get the notification icon
        var notificationIcon = document.getElementById("notification-icon");

        // When the user clicks on the notification icon, open the modal
        notificationIcon.onclick = function() {
            modal.style.display = "block";
        }

        // When the user clicks on <span> (x), close the modal
        modalClose.onclick = function() {
            modal.style.display = "none";
        }

        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        // Mark notifications as read when the modal is opened
        if (modal.style.display === "block" && <?php echo $user_id ? 'true' : 'false'; ?>) {
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "php/mark_notifications_read.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.send("user_id=<?php echo $user_id; ?>");
        }
    </script>
</body>
</html>
