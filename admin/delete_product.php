<?php
// Start the session
session_start();

// Check if the user is logged in as admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: logIn.html");
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

// Check if the 'id' GET variable was set
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // SQL query to delete the product
    $sql = "DELETE FROM products WHERE id='$id'";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Product deleted successfully!'); window.location.href = 'view_products.php';</script>";
    } else {
        echo "<script>alert('Error deleting product: " . mysqli_error($conn) . "'); window.location.href = 'view_products.php';</script>";
    }
} else {
    echo "<script>alert('Invalid product ID.'); window.location.href = 'view_products.php';</script>";
}

// Close the database connection
mysqli_close($conn);
?>
