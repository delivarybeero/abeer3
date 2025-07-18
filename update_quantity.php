<?php
session_start();
include("include/connection.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cart_id = intval($_POST['cart_id']);
    $quantity = intval($_POST['quantity']);
    
    $sql = "UPDATE cart1 SET quantity = ? WHERE cart_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'ii', $quantity, $cart_id);
    mysqli_stmt_execute($stmt);
}

header("Location: cart1.php");
exit();
?>