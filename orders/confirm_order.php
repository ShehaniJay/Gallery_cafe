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

$sql = "UPDATE orders SET deliver_status = 'confirmed' WHERE user_id = ? AND deliver_status = 'pending'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();

header("Location: view_order_bill.php");
exit();
?>
