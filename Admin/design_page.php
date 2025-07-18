<?php
session_start();
include("../include/connection.php");

// التحقق من صلاحيات المدير
if(!isset($_SESSION['EMAIL'])){
    header("Location: admin.php");
    exit();
}

// معالجة رفع الصورة
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['background_image'])) {
    $target_dir = "../images/";
    $target_file = $target_dir . "a5.jpg"; // نفس اسم الملف الحالي
    
    // التحقق من أن الملف صورة
    $imageFileType = strtolower(pathinfo($_FILES['background_image']['name'], PATHINFO_EXTENSION));
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    
    if(in_array($imageFileType, $allowed_types)) {
        // حذف الصورة القديمة إذا كانت موجودة
        if(file_exists($target_file)) {
            unlink($target_file);
        }
        
        // رفع الصورة الجديدة
        if(move_uploaded_file($_FILES['background_image']['tmp_name'], $target_file)) {
            $success = "تم تغيير خلفية الهيدر بنجاح!";
        } else {
            $error = "حدث خطأ أثناء رفع الصورة.";
        }
    } else {
        $error = "نوع الملف غير مسموح به. يرجى رفع صورة (JPG, JPEG, PNG, GIF).";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تغيير خلفية الهيدر</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .preview {
            margin-top: 20px;
            text-align: center;
        }
        .preview img {
            max-width: 100%;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 5px;
        }
        .btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover {
            background-color: #45a049;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .alert-success {
            color: #3c763d;
            background-color: #dff0d8;
            border-color: #d6e9c6;
        }
        .alert-danger {
            color: #a94442;
            background-color: #f2dede;
            border-color: #ebccd1;
        }
        .button-container {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        .button {
            background-color: #2196F3;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 4px;
            display: inline-block;
        }
        .button:hover {
            background-color: #0b7dda;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-image"></i> تغيير خلفية المتجر الالكتروني
        </h1>
        
        <?php if(isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form action="" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="background_image">اختر صورة جديدة للخلفية:</label>
                <input type="file" name="background_image" id="background_image" required>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn"><i class="fas fa-upload"></i> رفع الصورة</button>
            </div>
        </form>
        
        <div class="preview">
            <h3>معاينة الخلفية الحالية:</h3>
            <img src="../images/a5.jpg?<?php echo time(); ?>" alt="الخلفية الحالية">
        </div>
    </div>
    
    <script>
        // عرض معاينة للصورة قبل الرفع
        document.getElementById('background_image').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.querySelector('.preview img').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    </script>

<div class="button-container">
        <a href="../index.php" class="button"><i class="fas fa-store"></i> العودة للمتجر</a>
        <a href="../Admin/admianpanel.php" class="button"><i class="fas fa-cog"></i> لوحة التحكم</a>
    </div>



</body>
</html>