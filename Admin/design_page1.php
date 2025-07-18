<?php
// تفعيل عرض الأخطاء
error_reporting(E_ALL);
ini_set('display_errors', 1);

// بدء الجلسة
session_start();

// التحقق من تسجيل الدخول
if(!isset($_SESSION['EMAIL'])) {
    header("Location: admin.php");
    exit();
}

// الاتصال بقاعدة البيانات
require_once("../include/connection.php");

// إنشاء مجلدات التخزين إذا لم تكن موجودة
if (!file_exists('../images')) {
    mkdir('../images', 0755, true);
}
if (!file_exists('../config')) {
    mkdir('../config', 0755, true);
}

// معالجة الإرسال
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // ... (نفس كود معالجة البيانات الذي لديك)
}

// ==============================================
// بداية HTML
// ==============================================
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المتجر</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
    <style>
        /* ... (أنماط CSS الخاصة بك) ... */
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-store"></i> إدارة المتجر</h1>
        
        <?php if(isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form action="" method="post" enctype="multipart/form-data">
            <!-- ... (بقية نموذج الإدارة) ... -->
        </form>
    </div>
</body>
</html>