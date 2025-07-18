<?php
session_start();
include("./include/connection.php");

if(isset($_POST['add_to_cart'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $price = floatval($_POST['price']);
    $image = mysqli_real_escape_string($conn, $_POST['image']);
    $session_id = session_id();

    // التحقق من وجود المنتج في السلة
    $check_query = "SELECT * FROM cart1 WHERE session_id = ? AND product_id = ?";
    $stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($stmt, 'si', $session_id, $product_id);
    mysqli_stmt_execute($stmt);
    $existing_item = mysqli_stmt_get_result($stmt);

    if(mysqli_num_rows($existing_item) > 0) {
        // تحديث الكمية إن وجد
        $update_query = "UPDATE cart1 SET quantity = quantity + ? WHERE session_id = ? AND product_id = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, 'isi', $quantity, $session_id, $product_id);
    } else {
        // إضافة جديد
        $insert_query = "INSERT INTO cart1 (session_id, product_id, name, price, img, quantity) 
                        VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($stmt, 'sisdsi', $session_id, $product_id, $name, $price, $image, $quantity);
    
    // بعد إضافة المنتج
//$_SESSION['cart_count'] = getCartCount($conn);
    
    
    }
    
    mysqli_stmt_execute($stmt);
    header("Location: cart1.php");
    exit();
}
?>