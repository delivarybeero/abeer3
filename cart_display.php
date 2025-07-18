<?php
session_start();
include("include/connection.php");
include("file/header.php");

// استعلام للحصول على محتويات السلة من قاعدة البيانات
$query = "SELECT * FROM cart1";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    echo "<table border='1'>
    <tr>
    <th>اسم المنتج</th>
    <th>السعر</th>
    <th>الصورة</th>
    <th>الكمية</th>
    </tr>";

    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['name'] . "</td>";
        echo "<td>" . $row['price'] . "</td>";
        echo "<td><img src='uploads/img/" . $row['img'] . "' alt='" . $row['name'] . "' width='100'></td>";
        echo "<td>" . $row['quantity'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "سلتك فارغة.";
}

include("file/footer.php");
?>