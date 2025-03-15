<?php
session_start();


if (!isset($_SESSION['user_id'])) {
    $message = urlencode("Please log in to pre-order your meal.");
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
$sql = "SELECT COUNT(*) as unread_count FROM notifications WHERE user_id = ? AND is_read = 0";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$hasUnreadNotifications = $row['unread_count'] > 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Gallery Café</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../page_header.css">
    <link rel="stylesheet" href="../styles/preorder.css">
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
                                <span class="badge"><?php echo $row['unread_count']; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</header>

<!-- The Notification Modal -->
<div id="notification-modal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Notifications</h2>
        <?php
     
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
        ?>
    </div>
</div>

<div id="preorder">
    <h1>Pre-order Your Meal</h1>
    <form action="../orders/add_to_cart.php" method="post">
        <?php
        $sql = "SELECT id, name, description, price, image, menu_type FROM products";
        $result = mysqli_query($conn, $sql);

        if (!$result) {
            die("Query failed: " . mysqli_error($conn));
        }

      
        $products = [
            'Sri Lankan' => [],
            'Italian' => [],
            'Chinese' => [],
            'Beverages' => []
        ];

        while ($row = mysqli_fetch_assoc($result)) {
            $menuType = $row['menu_type'];
            if (array_key_exists($menuType, $products)) {
                $products[$menuType][] = $row;
            }
        }

        mysqli_close($conn);

        
        function displayMenuItems($items) {
            foreach ($items as $item) {
                $imageData = base64_encode($item['image']);
                $imagePath = 'data:image/jpeg;base64,' . $imageData;
                echo '<div class="box">';
                echo '<input type="checkbox" id="item_' . htmlspecialchars($item['id']) . '" name="items[]" value="' . htmlspecialchars($item['id']) . '">';
                echo '<label for="item_' . htmlspecialchars($item['id']) . '">';
                echo '<div id="image">';
                echo '<img src="' . $imagePath . '" alt="' . htmlspecialchars($item['name']) . '" class="product-image">';
                echo '</div>';
                echo '<div>';
                echo '<h3>' . htmlspecialchars($item['name']) . '</h3>';
                echo '<h4>Rs.' . htmlspecialchars($item['price']) . '</h4>';
                echo '</div>';
                echo '</label>';
                echo '</div>';
            }
        }

        foreach ($products as $menuType => $items) {
            echo '<div class="sub-menu">';
            echo '<h2>' . htmlspecialchars($menuType) . '</h2>';
            displayMenuItems($items);
            echo '</div>';
        }
        ?>
        <div class="preorder">
            <button type="submit">Add to Cart</button>
        </div>
    </form>

    <div class="linkmenu">
        <a href="menu.php">Go back to Menu Page</a>
    </div>
</div>

<footer>
    <div class="container">
        <p>&copy; 2024 The Gallery Café. All rights reserved.</p>
    </div>
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
                    icon.style.color = ''; 
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
