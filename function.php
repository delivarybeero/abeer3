<?php
function getCartCount($conn) {
    session_start();
    
    // إذا كانت الجلسة تحتوي على العدد واستضافتك تدعم الجلسات
    if(isset($_SESSION['cart_count'])) {
        return $_SESSION['cart_count'];
    }
    
    // حساب من قاعدة البيانات كحل بديل
    $stmt = $conn->prepare("SELECT SUM(quantity) FROM cart1 WHERE session_id = ?");
    $stmt->bind_param("s", session_id());
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_row()[0] ?? 0;
    
    $_SESSION['cart_count'] = $count;
    return $count;
}?>