<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// التحقق من تسجيل الدخول
if (!isset($_SESSION['EMAIL'])) {
    header("Location: admin.php");
    exit();
}

include("../include/connection.php");

// التحقق من رقم المنتج
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("رقم المنتج غير صحيح.");
}

$product_id = intval($_GET['id']);

// جلب بيانات المنتج الحالي
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    die("المنتج غير موجود.");
}

$error = "";
$success = "";

if (isset($_POST['update_pro'])) {
    // استلام وتنظيف البيانات
    $name = trim(htmlspecialchars($_POST['name']));
    $price = floatval($_POST['price']);
    $description = trim(htmlspecialchars($_POST['description']));
    $prosection = trim(htmlspecialchars($_POST['prosection']));
    $prosize = trim(htmlspecialchars($_POST['prosize']));
    $quantity = intval($_POST['quantity']);
    $prounv = $_POST['prounv'];
    $old_image = '';
if (isset($_POST['old_image'])) {
    $old_image = trim($_POST['old_image']);
} elseif (isset($product['image'])) {
    $old_image = $product['image'];
}
    
   // $old_image = $_POST['old_image'];

    // معالجة الصورة
    $image = $old_image;

    if (isset($_FILES['new_image']) && $_FILES['new_image']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = $_SERVER['DOCUMENT_ROOT'] . "/uploads/img/";
        
        // إنشاء المجلد إذا لم يكن موجوداً
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // التحقق من نوع الملف
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = mime_content_type($_FILES['new_image']['tmp_name']);
        
        if (!in_array($file_type, $allowed_types)) {
            $error = "نوع الصورة غير مدعوم. يرجى استخدام صورة من نوع: jpg, png, gif, webp";
        } else {
            // إنشاء اسم فريد للملف
            $file_ext = pathinfo($_FILES['new_image']['name'], PATHINFO_EXTENSION);
            $file_name = uniqid() . '_' . time() . '.' . strtolower($file_ext);
            $target_path = $upload_dir . $file_name;

            // نقل الملف المرفوع
            if (move_uploaded_file($_FILES['new_image']['tmp_name'], $target_path)) {
                // حذف الصورة القديمة إذا لم تكن افتراضية
                if (!empty($old_image) && file_exists($_SERVER['DOCUMENT_ROOT'] . "/" . $old_image) && $old_image !== "uploads/img/default.jpg") {
                    @unlink($_SERVER['DOCUMENT_ROOT'] . "/" . $old_image);
                }
                $image = "uploads/img/" . $file_name;
            } else {
                $error = "حدث خطأ أثناء رفع الصورة.";
            }
        }
    }

    // إذا لم يكن هناك خطأ، قم بالتحديث
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
        
        $update->bind_param("sdsssissi", $name, $price, $description, $prosection, $prosize, $quantity, $image, $prounv, $product_id);

        if ($update->execute()) {
            $success = "تم تحديث المنتج بنجاح.";
            // جلب البيانات المحدثة
            $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $product = $result->fetch_assoc();
        } else {
            $error = "حدث خطأ أثناء التحديث: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تحديث المنتج</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {background: #f4f4f4; font-family: Arial, sans-serif; padding: 20px;}
        .container {max-width: 800px; margin: 20px auto; background: #fff; border-radius: 8px; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1);}
        h2 {text-align: center; color: #333; margin-bottom: 20px;}
        .form-group {margin-bottom: 15px;}
        label {display: block; font-weight: bold; margin-bottom: 5px;}
        input, textarea, select {width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;}
        button {background: #28a745; color: #fff; padding: 12px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; width: 100%; margin-top: 10px;}
        button:hover {background: #218838;}
        .success {background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 15px;}
        .error {background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px;}
        .image-preview {max-width: 200px; max-height: 200px; display: block; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px;}
        .button {display: inline-block; background: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; margin-top: 10px;}
        .button:hover {background: #0069d9;}
    </style>
</head>
<body>
    <div class="container">
        <h2>تحديث المنتج</h2>
        
        <?php if (!empty($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <!-- حقول النموذج تبقى كما هي -->
            <div class="form-group">
                <label>اسم المنتج:</label>
                <input type="text" name="name" required value="<?php echo htmlspecialchars($product['name']); ?>">
            </div>



            <!-- ... -->
            
            <div class="form-group">
    <label>الصورة الحالية:</label>
    <?php
    $display_path = (!empty($product['image'])) ? "/" . $product['image'] : "/uploads/img/default.jpg";
    ?>
    <div style="display: flex; align-items: center; gap: 15px;">
        <img src="<?php echo $display_path; ?>" class="image-preview" onerror="this.src='/uploads/img/default.jpg'" style="max-width: 150px;">
        <div>
            <label style="display: block; margin-bottom: 5px;">تغيير الصورة:</label>
            <input type="file" name="new_image" accept="image/*">
            <small style="display: block; margin-top: 5px; color: #666;">الصيغ المدعومة: jpg, png, gif, webp</small>
        </div>
    </div>
</div>
            <!-- بقية حقول النموذج -->
            <div class="form-group">
                <label>السعر (د.ل):</label>
                <input type="number" step="0.01" name="price" required 
                    value="<?php echo htmlspecialchars($product['price']); ?>">
            </div>

            <div class="form-group">
                <label>الوصف:</label>
                <textarea name="description" rows="4"><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>

            <div class="form-group">
                <label>القسم:</label>
                <input type="text" name="prosection" 
                   value="<?php echo htmlspecialchars($product['prosection'] ?? ''); ?>"

            </div>

            <div class="form-group">
                <label>المقاس:</label>
                <input type="text" name="prosize" 
                    value="<?php echo htmlspecialchars($product['prosize']); ?>">
            </div>

            <div class="form-group">
                <label>الكمية:</label>
                <input type="number" name="quantity" min="0" 
                    value="<?php echo htmlspecialchars($product['quantity']); ?>">
            </div>

            <div class="form-group">
                <label>حالة المنتج:</label>
                <select name="prounv" required>
                    <option value="متوفر" <?php if($product['prounv']=='متوفر') echo 'selected'; ?>>متوفر</option>
                    <option value="غير متوفر" <?php if($product['prounv']=='غير متوفر') echo 'selected'; ?>>غير متوفر</option>
                </select>
            </div>



            <!-- ... -->
            
            <button type="submit" name="update_pro">حفظ التعديلات</button>
            <a href="admianpanel.php" class="button">العودة للوحة التحكم</a>
        </form>
    </div>
</body>
</html>