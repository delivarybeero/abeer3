<?php
session_start();
include("../include/connection.php");

// تحقق من جلسة الدخول
if(!isset($_SESSION['EMAIL'])){
    header("Location: admin.php");
    exit();
}

// متغيرات فارغة افتراضية
$prosection = '';
$prounv = '';
$prosize = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['proadd'])) {
    // تنظيف المدخلات
    $proname = mysqli_real_escape_string($conn, $_POST['name']);
    $prodescrip = mysqli_real_escape_string($conn, $_POST['description']);
    $proprice = floatval($_POST['price']);
    $quantity = intval($_POST['quantity']);
    $prosection = mysqli_real_escape_string($conn, $_POST['prosection'] ?? '');
    $prosize = mysqli_real_escape_string($conn, $_POST['prosize'] ?? '');
    $prounv = mysqli_real_escape_string($conn, $_POST['prounv'] ?? '');

    // معالجة الصورة
    $uploadOk = 1;
    $imageName = basename($_FILES['image']['name']);
    $target_dir = "../uploads/img/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    $uniqueName = uniqid() . '_' . $imageName;
    $target_file = $target_dir . $uniqueName;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // التحقق من نوع الملف
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    if(!in_array($imageFileType, $allowed_types)) {
        echo '<script>alert("فقط الصور مسموح بها: JPG, JPEG, PNG, GIF");</script>';
        $uploadOk = 0;
    }
    // التحقق من أخطاء رفع الملف
    if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        echo '<script>alert("خطأ في رفع الملف: ' . $_FILES['image']['error'] . '");</script>';
        $uploadOk = 0;
    }

    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            // تسجيل في قاعدة البيانات
            $stmt = $conn->prepare("INSERT INTO products 
                (name, description, price, quantity, image, prosection, prosize, prounv)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssdissss", 
                $proname, 
                $prodescrip, 
                $proprice,
                $quantity,
                $target_file,
                $prosection,
                $prosize,
                $prounv
            );
            if($stmt->execute()){
                echo '<script>alert("تمت الإضافة بنجاح");</script>';
            } else {
                echo '<script>alert("خطأ في قاعدة البيانات: ' . $stmt->error . '");</script>';
            }
            $stmt->close();
        } else {
            echo '<script>alert("خطأ في تحميل الصورة إلى السيرفر");</script>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>إضافة منتج جديد</title>
    <style>
        body {font-family: Arial, sans-serif; background: #f0f0f0;}
        .container {max-width: 800px; margin: 20px auto; padding: 20px; background: white; border-radius: 8px;}
        .form-group {margin-bottom: 15px;}
        label {display: block; margin-bottom: 5px; font-weight: bold;}
        input, select, textarea {width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;}
        .button {background: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;}
        .button:hover {background: #45a049;}
        @media (max-width: 600px) {
  body {
    font-size: 18px;
  }
  h1, h2, h3 {
    font-size: 22px;
  }
}
    </style>
</head>
<body>
    <div class="container">
        <h1>إضافة منتج جديد</h1>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>اسم المنتج:</label>
                <input type="text" name="name" required>
            </div>

            <div class="form-group">
                <label>الوصف:</label>
                <textarea name="description" required></textarea>
            </div>

            <div class="form-group">
                <label>السعر:</label>
                <input type="number" name="price" step="0.01" required>
            </div>

            <div class="form-group">
                <label>الكمية:</label>
                <input type="number" name="quantity" required>
            </div>

            <div class="form-group">
                <label>قسم المنتج (اختياري):</label>
                <input type="text" name="prosection">
            </div>

            <div class="form-group">
                <label>الحجم/الوزن (اختياري):</label>
                <input type="text" name="prosize">
            </div>
<!--
            <div class="form-group">
                <label>وحدة المنتج (اختياري):</label>
                <input type="text" name="prounv">
            </div>
-->
            <div class="form-group">
                <label>صورة المنتج:</label>
                <input type="file" name="image" accept="image/*" required>
            </div>

            <button type="submit" name="proadd" class="button">إضافة المنتج</button>
            <a href="admianpanel.php" class="button">العودة للوحة التحكم</a>
        </form>
    </div>
</body>
</html>