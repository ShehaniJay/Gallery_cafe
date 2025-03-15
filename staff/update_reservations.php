<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
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
    $reservationId = $_POST['reservationId'];
    $reservationDate = $_POST['reservationDate'];
    $reservationTime = $_POST['reservationTime'];
    $numberOfGuests = $_POST['numberOfGuests'];
    $specialRequests = $_POST['specialRequests'];
    $status = $_POST['status'];

    $update_sql = "UPDATE reservations 
                   SET reservation_date='$reservationDate', 
                       reservation_time='$reservationTime', 
                       number_of_guests='$numberOfGuests', 
                       special_requests='$specialRequests', 
                       status='$status' 
                   WHERE reservation_id='$reservationId'";

  
    if (mysqli_query($conn, $update_sql)) {
        echo "<script>alert('Reservation updated successfully!'); window.location.href = 'staff_manage_reservation.php';</script>";
    } else {
        echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
    }
}

mysqli_close($conn);
?>
