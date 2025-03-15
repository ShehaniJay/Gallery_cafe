<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gallery_cafe";


$conn = mysqli_connect($servername, $username, $password, $dbname);


if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $user = $_POST['username'];
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

   
    $checkEmailSql = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($conn, $checkEmailSql);

    if (mysqli_num_rows($result) > 0) {
        
        echo "<script>alert('Error: This email is already registered.'); window.location.href = '../pages/register.html';</script>";
    } else {
        
        $sql = "INSERT INTO users (name, email, phone, username, password_hash, role) VALUES ('$name', '$email', '$phone', '$user', '$pass', '$role')";

        if (mysqli_query($conn, $sql)) {
            
            echo "<script>alert('Registration successful!'); window.location.href = '../pages/logIn.html';</script>";
        } else {
            
            echo "<script>alert('Error: " . mysqli_error($conn) . "'); window.location.href = '../pages/register.html';</script>";
        }
    }
}

mysqli_close($conn);
?>
