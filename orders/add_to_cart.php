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

// Handle selected items
if (isset($_POST['items']) && is_array($_POST['items'])) {
    foreach ($_POST['items'] as $item_id) {
        // Insert each item into the cart
        $stmt = $conn->prepare("INSERT INTO orders (user_id, product_id, quantity) VALUES (?, ?, 1)");
        $stmt->bind_param("ii", $user_id, $item_id);
        $stmt->execute();
    }
}

mysqli_close($conn);

header("Location: cart.php");
exit();
