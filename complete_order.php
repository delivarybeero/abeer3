<?php
session_start();
include("include/connection.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $total = $_POST['total'];

    // إدخال الطلب في جدول الطلبات
    $insert_order = "INSERT INTO orders1 (user_id, order_date, total_amount, status) VALUES ('1', NOW(), '$total', 'Pending')";
    if (mysqli_query($conn, $insert_order)) {
        $order_id = mysqli_insert_id($conn);

        // إدخال تفاصيل الطلب
        $cart_query = "SELECT * FROM cart";
        $cart_result = mysqli_query($conn, $cart_query);
        while ($cart_row = mysqli_fetch_assoc($cart_result)) {
            $product_id = $cart_row['id'];
            $quantity = $cart_row['quantity'];
            $price = $cart_row['price'];
            $insert_order_item = "INSERT INTO orders1_items (order_id, product_id, quantity, price) VALUES ('$order_id', '$product_id', '$quantity', '$price')";
            mysqli_query($conn, $insert_order_item);
        }

        // مسح السلة بعد اتمام الطلب
        $clear_cart = "DELETE FROM cart";
        mysqli_query($conn, $clear_cart);

        echo '<script>alert("تم اتمام الطلب بنجاح")</script>';
        echo '<script>window.location.href="orders.php";</script>';
    } else {
        echo '<script>alert("حدث خطأ أثناء اتمام الطلب")</script>';
    }
}
?>