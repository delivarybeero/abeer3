<?php
session_start();
include 'connection.php';

// جلب جميع الطلبات من قاعدة البيانات
$sql = "SELECT * FROM orders1";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>الطلبات</title>
</head>
<body>
    <h1>الطلبات</h1>
    <table border="1">
        <tr>
            <th>معرف الطلب</th>
            <th>معرف المستخدم</th>
            <th>تاريخ الطلب</th>
            <th>حالة الطلب</th>
            <th>المجموع الكلي</th>
        </tr>
        <?php while($row = mysqli_fetch_assoc($result)) { ?>
        <tr>
            <td><?php echo $row['order_id']; ?></td>
            <td><?php echo $row['user_id']; ?></td>
            <td><?php echo $row['order_date']; ?></td>
            <td><?php echo $row['order_status']; ?></td>
            <td><?php echo $row['total_amount']; ?></td>
        </tr>
        <?php } ?>
    </table>
</body>
</html>
<form method="POST" action="complete_order.php">
    <div class="cart_total">
        <h6> <?php echo  number_format($total,2)." $"." = ";?><span id="total">المجموع</span></h6>
        <button type="submit" name="complete_order" class="remove">اتمام الطلب</button>
    </div>
</form>