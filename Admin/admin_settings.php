<?php
// تأكد من وجود اتصال بقاعدة البيانات وتحقق من صلاحيات المدير
session_start();
if(!isset($_SESSION['EMAIL'])){
    header('location:../index.php');
    exit();
}

include("../include/connection.php");

// معالجة تحديث الإعدادات
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_site_name = mysqli_real_escape_string($conn, $_POST['site_name']);
    
    // معالجة تحميل صورة اللوجو
    $logo_path = '../images/a1.png'; // المسار الافتراضي
    
    if (!empty($_FILES['logo']['name'])) {
        $target_dir = "../images/";
        $target_file = $target_dir . basename($_FILES["logo"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        // التحقق من أن الملف صورة
        $check = getimagesize($_FILES["logo"]["tmp_name"]);
        if ($check !== false) {
            // السماح بأنواع معينة من الصور
            if ($imageFileType == "jpg" || $imageFileType == "png" || $imageFileType == "jpeg") {
                if (move_uploaded_file($_FILES["logo"]["tmp_name"], $target_file)) {
                    $logo_path = 'images/' . basename($_FILES["logo"]["name"]);
                    
                    // تحديث مسار الصورة في قاعدة البيانات إذا كنت تخزنه هناك
                    $update_logo = "UPDATE site_settings SET logo_path='$logo_path' WHERE id=1";
                    mysqli_query($conn, $update_logo);
                }
            }
        }
    }
    
    // تحديث اسم الموقع في قاعدة البيانات
    $update_name = "UPDATE site_settings SET site_name='$new_site_name' WHERE id=1";
    if (mysqli_query($conn, $update_name)) {
        $success_message = "تم تحديث الإعدادات بنجاح!";
    } else {
        $error_message = "حدث خطأ أثناء تحديث الإعدادات: " . mysqli_error($conn);
    }
}

// جلب الإعدادات الحالية
$settings_query = "SELECT * FROM site_settings WHERE id=1";
$settings_result = mysqli_query($conn, $settings_query);
$settings = mysqli_fetch_assoc($settings_result);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إعدادات الموقع</title>
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
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        input[type="text"], input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        .logo-preview {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #3498db;
            margin-top: 10px;
        }
        .btn {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover {
            background-color: #2980b9;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    
    <div class="container">
        <h1><i class="fas fa-cog"></i> إعدادات الموقع</h1>
        
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <form action="admin_settings.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="site_name">اسم الموقع:</label>
                <input type="text" id="site_name" name="site_name" value="<?php echo htmlspecialchars($settings['site_name'] ?? 'shopping_online'); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="logo">شعار الموقع:</label>
                <input type="file" id="logo" name="logo" accept="image/*">
                <img src="../<?php echo $settings['logo_path'] ?? 'images/a1.png'; ?>" alt="الشعار الحالي" class="logo-preview">
            </div>
            
            <button type="submit" class="btn"><i class="fas fa-save"></i> حفظ التغييرات</button>
        </form>
    </div>
    <a href="../index.php" style="padding: 10px 20px; background: #4CAF50; color: white; 
           text-decoration: none; border-radius: 4px; display: inline-block; margin-top: 15px;margin-right:200px;">
            العودة إلى المتجر
        </a>
        <a href="../Admin/admianpanel.php" style="padding: 10px 20px; background: #4CAF50; color: white; 
           text-decoration: none; border-radius: 4px; display: inline-block; margin-top: 15px;margin-right:50px;">
    لوحة تحكم الإدارة
    </a>



</body>
</html>