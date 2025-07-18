<?php
function getCartCount($conn) {
    if(!isset($_SESSION)) {
        session_start();
    }
    
    // إذا كان العداد محفوظاً في الجلسة
    if(isset($_SESSION['cart_count'])) {
        return $_SESSION['cart_count'];
    }
    
    // حساب العدد من قاعدة البيانات
    $stmt = $conn->prepare("SELECT COALESCE(SUM(quantity), 0) FROM cart1 WHERE session_id = ?");
    $stmt->bind_param("s", session_id());
    $stmt->execute();
    $count = $stmt->get_result()->fetch_row()[0];
    
    $_SESSION['cart_count'] = $count;
    return $count;
}

function updateCartCount($conn, $change) {
    if(!isset($_SESSION)) {
        session_start();
    }
    
    $_SESSION['cart_count'] = getCartCount($conn) + $change;
}
?>