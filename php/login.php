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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $usernameOrEmail = mysqli_real_escape_string($conn, $_POST['usernameOrEmail']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'ss', $usernameOrEmail, $usernameOrEmail);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
       
        if (password_verify($password, $row['password_hash'])) {
           
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];

            
            if ($_SESSION['role'] === 'admin') {
                header("Location: ../admin/admin_dashboard.html");
            } elseif ($_SESSION['role'] === 'customer') {
                header("Location: ../index.php");
            } elseif ($_SESSION['role'] === 'staff') {
                header("Location: ../staff/staff_dashboard.html");
            } else {
              
                echo "<script>
                    alert('Unexpected user role. Please contact support.');
                    window.location.href = '../pages/logIn.html';
                </script>";
            }
            exit();
        } else {
           
            echo "<script>
                alert('Invalid password. Please try again.');
                window.location.href = '../pages/logIn.html';
            </script>";
        }
    } else {
       
        echo "<script>
            alert('Username or email not found. Please try again.');
            window.location.href = '../pages/logIn.html';
        </script>";
    }

   
    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
?>
