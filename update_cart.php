<?php
session_start();
include("./include/connection.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'], $_POST['quantity'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);
    $session_id = session_id();
    
    if ($quantity <= 0) {
        // إذا كانت الكمية صفر أو أقل، احذف المنتج من السلة
        $query = "DELETE FROM cart1 WHERE session_id = '$session_id' AND product_id = $product_id";
    } else {
        // غير الكمية
        $query = "UPDATE cart1 SET quantity = $quantity WHERE session_id = '$session_id' AND product_id = $product_id";
    }
    
    mysqli_query($conn, $query);
    
    // تحديث عدد العناصر في الجلسة
    $count_query = "SELECT SUM(quantity) as total FROM cart1 WHERE session_id = '$session_id'";
    $count_result = mysqli_query($conn, $count_query);
    $_SESSION['cart_items_count'] = mysqli_fetch_assoc($count_result)['total'] ?? 0;
    
    echo json_encode(['success' => true]);
    exit();
}

echo json_encode(['success' => false, 'message' => 'طلب غير صالح']);
?>