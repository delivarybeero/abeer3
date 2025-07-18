<?php
$servername = "localhost";
$username = "abeer";
$password = "abeer_zakut"; // استبدل بكلمة المرور الفعلية
$dbname = "ecommerce_db";

// إنشاء الاتصال
$conn = mysqli_connect($servername, $username, $password, $dbname);

// التحقق من الاتصال
if (!$conn) {
    die("فشل الاتصال: " . mysqli_connect_error());
}
//echo "تم الاتصال بنجاح";
?>