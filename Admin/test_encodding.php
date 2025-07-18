<?php
header('Content-Type: text/html; charset=utf-8');
include 'connection.php';

$test = mysqli_query($conn, "SHOW VARIABLES LIKE 'char%'");
echo "<h2>إعدادات الترميز:</h2>";
while($row = mysqli_fetch_assoc($test)) {
    echo $row['Variable_name'].": ".$row['Value']."<br>";
}

$test_query = "SELECT 'العربية' AS test, product_name FROM orders1_items LIMIT 1";
$result = mysqli_query($conn, $test_query);
$data = mysqli_fetch_assoc($result);

echo "<h2>اختبار العربية:</h2>";
echo "<p>".htmlspecialchars($data['test'], ENT_QUOTES, 'UTF-8')."</p>";
echo "<p>".htmlspecialchars($data['product_name'], ENT_QUOTES, 'UTF-8')."</p>";
?>