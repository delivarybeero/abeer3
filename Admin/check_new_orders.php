<?php
session_start();
include("../include/connection.php");

// الحصول على آخر معرف طلب من الطلب
$last_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;

// استعلام للطلبات الجديدة
$query = "SELECT COUNT(*) as new_orders, MAX(order_id) as last_id FROM orders1 WHERE order_id > ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $last_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

header('Content-Type: application/json');
echo json_encode([
    'new_orders' => $data['new_orders'],
    'last_id' => $data['last_id'] ?: $last_id
]);
?>