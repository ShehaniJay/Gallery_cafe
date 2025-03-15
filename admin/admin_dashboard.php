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
            echo "<script>alert('Error: " . mysqli_error($conn) . "'); window.location.href = 'admin_dashboard.php';</script>";
        }
    }

    // Close the database connection
    mysqli_close($conn);1
    ?>