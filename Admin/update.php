<?php
// بداية الجلسة يجب أن تكون أول شيء في الملف
session_start();

// تفعيل عرض الأخطاء للتصحيح
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// التحقق من تسجيل الدخول
if (!isset($_SESSION['EMAIL'])) {
    header("Location: admin.php");
    exit();
}

// الاتصال بقاعدة البيانات
require_once("../include/connection.php");

// التحقق من وجود ID المنتج
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("رقم المنتج غير صحيح.");
}

$product_id = intval($_GET['id']);

// جلب بيانات المنتج
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);

if (!$stmt->execute()) {
    die("خطأ في جلب بيانات المنتج: " . $conn->error);
}

$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    die("المنتج غير موجود.");
}

// متغيرات للرسائل
$error = "";
$success = "";

// معالجة تحديث المنتج
if (isset($_POST['update_pro'])) {
    // تنظيف المدخلات
    $name = trim(htmlspecialchars($_POST['name'] ?? ''));
    $price = floatval($_POST['price'] ?? 0);
    $description = trim(htmlspecialchars($_POST['description'] ?? ''));
    $prosection = trim(htmlspecialchars($_POST['prosection'] ?? ''));
    $prosize = trim(htmlspecialchars($_POST['prosize'] ?? ''));
    $quantity = intval($_POST['quantity'] ?? 0);
    $prounv = $_POST['prounv'] ?? 'متوفر';
    
    // معالجة الصورة
    $old_image = $product['image'] ?? 'uploads/img/default.jpg';
    $image = $old_image;

    if (isset($_FILES['new_image']['error']) && $_FILES['new_image']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = $_SERVER['DOCUMENT_ROOT'] . "/uploads/img/";
        
        // إنشاء المجلد إذا لم يكن موجوداً
        if (!file_exists($upload_dir)) {
            if (!mkdir($upload_dir, 0755, true)) {
                $error = "لا يمكن إنشاء مجلد الصور";
            }
        }

        if (empty($error)) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $file_info = finfo_open(FILEINFO_MIME_TYPE);
            $file_type = finfo_file($file_info, $_FILES['new_image']['tmp_name']);
            finfo_close($file_info);

            if (in_array($file_type, $allowed_types)) {
                $file_ext = pathinfo($_FILES['new_image']['name'], PATHINFO_EXTENSION);
                $file_name = uniqid() . '.' . strtolower($file_ext);
                $target_path = $upload_dir . $file_name;

                if (move_uploaded_file($_FILES['new_image']['tmp_name'], $target_path)) {
                    // حذف الصورة القديمة إذا كانت ليست الصورة الافتراضية
                    if ($old_image != 'uploads/img/default.jpg' && file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $old_image)) {
                        @unlink($_SERVER['DOCUMENT_ROOT'] . '/' . $old_image);
                    }
                    $image = "uploads/img/" . $file_name;
                } else {
                    $error = "فشل في رفع الصورة";
                }
            } else {
                $error = "نوع الملف غير مدعوم. يرجى استخدام صورة من نوع JPG, PNG, GIF أو WEBP";
            }
        }
    }

    // إذا لم يكن هناك أخطاء، قم بالتحديث
    if (empty($error)) {
        $update = $conn->prepare("UPDATE products SET 
            name = ?, 
            price = ?, 
            description = ?, 
            prosection = ?, 
            prosize = ?, 
            quantity = ?, 
            image = ?, 
            prounv = ? 
            WHERE id = ?");
        
        if (!$update) {
            $error = "تحضير الاستعلام فشل: " . $conn->error;
        } else {
            $update->bind_param("sdsssissi", $name, $price, $description, $prosection, $prosize, $quantity, $image, $prounv, $product_id);
            
            if ($update->execute()) {
                $success = "تم تحديث المنتج بنجاح";
                // تحديث بيانات المنتج
                $stmt->execute();
                $result = $stmt->get_result();
                $product = $result->fetch_assoc();
            } else {
                $error = "فشل في تحديث المنتج: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تحديث المنتج</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { background: #fff; max-width: 800px; margin: 0 auto; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, textarea, select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #4CAF50; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #45a049; }
        .error { color: #f00; margin-bottom: 15px; }
        .success { color: #090; margin-bottom: 15px; }
        .image-preview { max-width: 200px; max-height: 200px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h2>تحديث المنتج</h2>
        
        <?php if (!empty($success)): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>اسم المنتج:</label>
                <input type="text" name="name" required value="<?= htmlspecialchars($product['name'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label>السعر:</label>
                <input type="number" step="0.01" name="price" required value="<?= htmlspecialchars($product['price'] ?? 0) ?>">
            </div>
            
            <div class="form-group">
                <label>الوصف:</label>
                <textarea name="description" rows="4"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
            </div>
            
            <div class="form-group">
                <label>الصورة الحالية:</label>
                <?php
                $image_path = (!empty($product['image']) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $product['image']))
                    ? '/' . $product['image']
                    : '/uploads/img/default.jpg';
                ?>
                <img src="<?= htmlspecialchars($image_path) ?>" class="image-preview" onerror="this.src='/uploads/img/default.jpg'">
                <input type="file" name="new_image" accept="image/*">
            </div>
            
            <div class="form-group">
                <label>القسم:</label>
                <input type="text" name="prosection" value="<?= htmlspecialchars($product['prosection'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label>المقاس:</label>
                <input type="text" name="prosize" value="<?= htmlspecialchars($product['prosize'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label>الكمية:</label>
                <input type="number" name="quantity" min="0" value="<?= htmlspecialchars($product['quantity'] ?? 0) ?>">
            </div>
            
            <div class="form-group">
                <label>حالة التوفر:</label>
                <select name="prounv" required>
                    <option value="متوفر" <?= ($product['prounv'] ?? '') === 'متوفر' ? 'selected' : '' ?>>متوفر</option>
                    <option value="غير متوفر" <?= ($product['prounv'] ?? '') === 'غير متوفر' ? 'selected' : '' ?>>غير متوفر</option>
                </select>
            </div>
            
            <button type="submit" name="update_pro">حفظ التغييرات</button>
            <a href="admianpanel.php" style="display: inline-block; margin-top: 10px;">العودة للوحة التحكم</a>
        </form>
    </div>
</body>
</html>