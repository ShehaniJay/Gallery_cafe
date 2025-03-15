<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/logIn.html");
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

// Get user ID
$user_id = $_SESSION['user_id'];

// Check if quantity data is submitted
if (isset($_POST['quantity']) && is_array($_POST['quantity'])) {
    foreach ($_POST['quantity'] as $order_id => $quantity) {
        $order_id = intval($order_id);
        $quantity = intval($quantity);
        
        if ($quantity > 0) {
            // Update quantity
            $stmt = $conn->prepare("UPDATE orders SET quantity = ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param("iii", $quantity, $order_id, $user_id);
            $stmt->execute();
        } else {
            // Remove item if quantity is 0 or less
            $stmt = $conn->prepare("DELETE FROM orders WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $order_id, $user_id);
            $stmt->execute();
        }
    }
}

// Close connection
mysqli_close($conn);

// Redirect to cart page
header("Location: cart.php");
exit();
?>
