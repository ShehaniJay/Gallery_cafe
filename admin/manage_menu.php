<?php
session_start();

// Check if the user is logged in as admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: logIn.php");
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $menuType = $_POST['menuType'];

    // Retrieve and process the image file
    $image = $_FILES['image']['tmp_name'];
    $imgContent = addslashes(file_get_contents($image));

    // Insert new product into the database
    $sql = "INSERT INTO products (name, description, price, stock, image, menu_type) VALUES ('$name', '$description', '$price', '$stock', '$imgContent', '$menuType')";

    // Execute the query and check if it was successful
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Product added successfully!'); window.location.href = 'view_products.php';</script>";
    } else {
        echo "<script>alert('Error: " . mysqli_error($conn) . "'); window.location.href = 'manage_menu.php';</script>";
    }
}

// Close the database connection
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrator Dashboard - The Gallery Café</title>
    <link rel="stylesheet" href="../styles/admin_staff.css">
    <link rel="stylesheet" href="../styles/admin_menu_manage.css">
    <script>
        function validateProductForm() {
            var name = document.getElementById('name').value;
            var description = document.getElementById('description').value;
            var price = document.getElementById('price').value;
            var stock = document.getElementById('stock').value;
            var image = document.getElementById('image').value;
            var menuType = document.getElementById('menuType').value;

            if (name == "") {
                alert("Product name must be filled out");
                return false;
            }
            if (description == "") {
                alert("Product description must be filled out");
                return false;
            }
            if (price == "" || isNaN(price) || price <= 0) {
                alert("Valid product price must be filled out");
                return false;
            }
            if (stock == "" || isNaN(stock) || stock < 0) {
                alert("Valid product stock must be filled out");
                return false;
            }
            if (image == "") {
                alert("Product image must be selected");
                return false;
            }
            if (menuType == "") {
                alert("Menu type must be selected");
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
        <a href="admin_dashboard.html"><img src="../logo.png" alt="The Gallery Café Logo" class="sidebar-logo"></a>
            <h2 class="title_admin1">Admin Dashboard</h2>
        </div>
        <a href="manage_menu.php">Manage Menu</a>
        <a href="manage_preorders.php">Manage Pre-orders</a>
        <a href="manage_reservation.php">Manage Reservations</a>
        <a href="manage_events.php">Manage Events</a>
        <a href="manage_user">Manage Users</a>
        <a href="../php/logout.php" class="logout-button">Logout</a>
    </div>

    <div class="main-content">
        <h3 class="section-title">Add New Product</h3>
        <div class="form-container">
            <form action="manage_menu.php" method="post" enctype="multipart/form-data" onsubmit="return validateProductForm()">
                <label for="name">Product Name:</label>
                <input type="text" id="name" name="name" class="form-input" required>

                <label for="description">Product Description:</label>
                <textarea id="description" name="description" class="form-textarea" required></textarea>

                <label for="price">Product Price:</label>
                <input type="number" step="0.01" id="price" name="price" class="form-input" required>

                <label for="stock">Product Stock:</label>
                <input type="number" id="stock" name="stock" class="form-input" required>

                <label for="image">Product Image:</label>
                <input type="file" id="image" name="image" class="form-input" accept="image/*" required>

                 <label for="menuType">Menu Type:</label>
                <select id="menuType" name="menuType" class="form-select" required>
                    <option value="">Select Menu Type</option>
                    <option value="Sri Lankan">Sri Lankan Cuisine</option>
                    <option value="Italian">Italian Cuisine</option>
                    <option value="Chinese">Chinese Cuisine</option>
                    <option value="Beverages">Beverages</option>
                </select>

                <button type="submit" class="submit-button">Add Product</button>
            </form>
        </div>
        <br>
        <a href="view_products.php" class="view-products-link">View Available Products</a>
    </div>

    <footer>
        <p>&copy; 2024 The Gallery Café. All rights reserved.</p>
    </footer>
</body>
</html>
