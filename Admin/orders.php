<?php
include "../include/connection.php";
session_start();

if (!isset($_SESSION['EMAIL'])) {
    header("Location: admin.php");
    exit();
}

echo "<center><h3>صفحة الطلبات</h3></center>";
?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">  
    <link rel="stylesheet" href="style1.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>صفحة الطلبات</title>
</head>
<body>
    <div class="content_sec">
        <table dir="rtl">
            <tr>
                <th>رقم الطلب</th>
                <th>العنوان</th>
                <th>رقم الهاتف</th>
                <th>تفاصيل الطلب</th>
            </tr>
            <?php
            $query = "SELECT o.order_id, o.shipping_address, o.phone, GROUP_CONCAT(oi.product_id, ': ', oi.quantity, ' x ', oi.price SEPARATOR '<br>') AS order_details
                      FROM orders1 o
                      JOIN orders1_items oi ON o.order_id = oi.order_id
                      GROUP BY o.order_id";
            $result = mysqli_query($conn, $query);

            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['order_id']) . "</td>";
                echo "<td>" . htmlspecialchars($row['shipping_address']) . "</td>";
                echo "<td>" . htmlspecialchars($row['phone']) . "</td>";
                echo "<td>" . $row['order_details'] . "</td>";
                echo "</tr>";
            }
            ?>
        </table>
    </div>
</body>
</html>