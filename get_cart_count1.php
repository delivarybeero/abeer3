<?php
session_start();
header('Content-Type: application/json');

$count = 0;
if(isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $count = count($_SESSION['cart']);
    
    // أو إذا كنت تستخدم الكميات
    // $count = array_sum($_SESSION['cart']);
}

echo json_encode(['count' => $count]);
?>