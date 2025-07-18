<?php
session_start();
include("./include/connection.php");

header('Content-Type: application/json');

$session_id = session_id();
$query = "SELECT * FROM cart1 WHERE session_id = '$session_id'";
$result = mysqli_query($conn, $query);

$items = [];
$total = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $items[] = [
        'product_id' => $row['product_id'],
        'name' => $row['name'],
        'price' => (float)$row['price'],
        'img' => $row['img'],
        'quantity' => (int)$row['quantity']
    ];
    $total += $row['price'] * $row['quantity'];
}

echo json_encode([
    'success' => true,
    'items' => $items,
    'total' => $total,
    'count' => count($items)
]);
?>