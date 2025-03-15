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

$hasUnreadNotifications = false;
$unreadCount = 0;

$hasUnreadNotifications = false;
$unreadCount = 0;
$notifications = [];

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT COUNT(*) as unread_count FROM notifications WHERE user_id = ? AND is_read = 0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $unreadCount = $row['unread_count'];
    $hasUnreadNotifications = $unreadCount > 0;

    $sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $notifications = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Gallery Café</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../page_header.css">
    <link rel="stylesheet" href="../styles/menu.css">
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


<div id="menu">
    <h1 id="section">OUR MENU</h1>
    <div class="sub-menu">
        <a href="#" class="tab-link active" data-tab="tab-sri-lankan">Sri Lankan Cuisine</a>
        <a href="#" class="tab-link" data-tab="tab-italian">Italian Cuisine</a>
        <a href="#" class="tab-link" data-tab="tab-chinese">Chinese Cuisine</a>
        <a href="#" class="tab-link" data-tab="tab-beverages">Beverages</a>
    </div>

    <?php
    // Query to select products
    $sql = "SELECT name, description, price, image, menu_type FROM products";
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        die("Query failed: " . mysqli_error($conn));
    }

    // Fetch products and group by menu_type
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

    // Function to display menu items
    function displayMenuItems($items) {
        foreach ($items as $item) {
            // Assuming 'image' column contains binary data
            $imageData = base64_encode($item['image']);
            $imagePath = 'data:image/jpeg;base64,' . $imageData;
    
            echo '<div class="box">';
            echo '<div class="image">';
            echo '<img src="' . htmlspecialchars($imagePath) . '" alt="' . htmlspecialchars($item['name']) . '" class="product-image"/>';
            echo '</div>';
            echo '<div>';
            echo '<h3>' . htmlspecialchars($item['name']) . '</h3>';
            echo '<h4>Rs.' . htmlspecialchars($item['price']) . '</h4>';
            echo '</div>';
            echo '</div>';
        }
    }
    ?>

    <div id="tab-sri-lankan" class="tab-content active">
        <div id="menu_column">
            <h2>Sri Lankan Cuisine</h2>
            <?php displayMenuItems($products['Sri Lankan']); ?>
        </div>
    </div>
    <div id="tab-italian" class="tab-content">
        <div id="menu_column">
            <h2>Italian Cuisine</h2>
            <?php displayMenuItems($products['Italian']); ?>
        </div>
    </div>
    <div id="tab-chinese" class="tab-content">
        <div id="menu_column">
            <h2>Chinese Cuisine</h2>
            <?php displayMenuItems($products['Chinese']); ?>
        </div>
    </div>
    <div id="tab-beverages" class="tab-content">
        <div id="menu_column">
            <h2>Beverages</h2>
            <?php displayMenuItems($products['Beverages']); ?>
        </div>
    </div>
</div>

<div class="linkpreorder">
    <a href="preorder.php">Pre-order Now</a>
</div>

<footer>
    <div class="container">
        <p>&copy; 2024 The Gallery Café. All rights reserved.</p>
    </div>
</footer>


<script>

    // Get the modal
    var modal = document.getElementById("notification-modal");

    // Get the icon that opens the modal
    var icon = document.getElementById("notification-icon");

    // Get the <span> element that closes the modal
    var span = document.getElementsByClassName("close")[0];

    // When the user clicks on the icon, open the modal
    icon.onclick = function() {
        modal.style.display = "block";
        // Mark notifications as read
        fetch('mark_notification_read.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    icon.querySelector('i').style.color = ''; // Revert to original color
                    document.querySelector('.badge').style.display = 'none'; // Hide badge
                }
            });
    }

    // When the user clicks on <span> (x), close the modal
    span.onclick = function() {
        modal.style.display = "none";
    }

    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }

document.addEventListener('DOMContentLoaded', function() {
    const tabLinks = document.querySelectorAll('.tab-link');
    const tabContents = document.querySelectorAll('.tab-content');

    tabLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();

            tabLinks.forEach(link => link.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));

            this.classList.add('active');
            const activeTab = this.getAttribute('data-tab');
            document.getElementById(activeTab).classList.add('active');
        });
    });

    // Show the first tab by default
    tabLinks[0].click();
});
</script>

</body>
</html>
