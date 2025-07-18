<?php
session_start();
include("include/connection.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // استقبال البيانات من النموذج
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $total = $_POST['total_amount'];
    $customer_name = $_POST['customer_name']; // الحقل الجديد
    $full_name = $_POST['full_name'];
    $payment_method = $_POST['payment_method'];

    // إدراج الطلب في جدول orders1 مع الحقل الجديد
    $insert_order = "INSERT INTO orders1 
                    (user_id, order_date, total_amount, status, phone, shipping_address, customer_name) 
                    VALUES 
                    ('1', NOW(), ?, 'Pending', ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $insert_order);
    mysqli_stmt_bind_param($stmt, 'dsss', $total, $phone, $address, $customer_name);
    mysqli_stmt_execute($stmt);

    if (mysqli_stmt_affected_rows($stmt) > 0) {
        $order_id = mysqli_insert_id($conn);

        // إدراج تفاصيل الطلب
        $session_id = session_id();
        $cart_query = "SELECT * FROM cart1 WHERE session_id = ?";
        $stmt_cart = mysqli_prepare($conn, $cart_query);
        mysqli_stmt_bind_param($stmt_cart, 's', $session_id);
        mysqli_stmt_execute($stmt_cart);
        $cart_result = mysqli_stmt_get_result($stmt_cart);

        while ($cart_row = mysqli_fetch_assoc($cart_result)) {
            $insert_item = "INSERT INTO orders1_items 
                          (order_id, product_id, quantity, price, customer_name) 
                          VALUES 
                          (?, ?, ?, ?, ?)";
            
            $stmt_item = mysqli_prepare($conn, $insert_item);
            mysqli_stmt_bind_param($stmt_item, 'iiids', 
                                 $order_id, 
                                 $cart_row['product_id'], 
                                 $cart_row['quantity'], 
                                 $cart_row['price'],
                                 $customer_name);
            mysqli_stmt_execute($stmt_item);
        }

        // مسح السلة
        $delete_cart = "DELETE FROM cart1 WHERE session_id = ?";
        $stmt_delete = mysqli_prepare($conn, $delete_cart);
        mysqli_stmt_bind_param($stmt_delete, 's', $session_id);
        mysqli_stmt_execute($stmt_delete);

        // إرسال بريد إلكتروني أو إشعار
        // ... يمكنك إضافة هذا الجزء لاحقاً
        
        header("Location: order_success.php?order_id=".$order_id);
        exit();
    } else {
        die("حدث خطأ في إتمام الطلب: " . mysqli_error($conn));
    }
}
?>