<?php
session_start();
include("include/connection.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cart_id = intval($_POST['cart_id']);
    
    $sql = "DELETE FROM cart1 WHERE cart_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $cart_id);
    mysqli_stmt_execute($stmt);
    $_SESSION['cart_count'] -= 1; // تقليل العداد بواحد

}

header("Location: cart1.php");
exit();
?>