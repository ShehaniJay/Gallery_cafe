<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: logIn.php");
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = intval($_POST['order_id']);
    $quantity = intval($_POST['quantity']);
    $deliverStatus = $_POST['deliver_status'];
    $status = $_POST['status'];

    
    $update_sql = "UPDATE orders 
                   SET quantity = ?, 
                       deliver_status = ?, 
                       status = ? 
                   WHERE id = ?";

    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("issi", $quantity, $deliverStatus, $status, $orderId);

   
    if ($stmt->execute()) {
        echo "<script>alert('Preorder updated successfully!'); window.location.href = 'manage_preorders.php';</script>";
    } else {
        echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
    }

    $stmt->close();
}

mysqli_close($conn);
?>
