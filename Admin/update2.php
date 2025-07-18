<?php
session_start();
include("../include/connection.php");

// التحقق من صلاحيات المستخدم
if (!isset($_SESSION['EMAIL'])) {
    header("Location: admin.php");
    exit();
}

if(!isset($_GET['id'])) {
    die("خطأ: لم يتم تحديد المنتج");
}

$product_id = intval($_GET['id']);

// جلب بيانات المنتج الحالية
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 0) {
    die("المنتج غير موجود");
}

$product = $result->fetch_assoc();

// معالجة تحديث البيانات
if(isset($_POST['update_pro'])) {
    // تنظيف المدخلات
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $price = floatval($_POST['price']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $prosection = mysqli_real_escape_string($conn, $_POST['prosection']);
    $prosize = mysqli_real_escape_string($conn, $_POST['prosize']);
    $quantity = intval($_POST['quantity']);

    // أخذ القيمة من القائمة المنسدلة
    $prounv = $_POST['prounv'];

    // معالجة صورة المنتج
    $image = $product['image'];
    if(!empty($_FILES['image']['name'])) {
        $upload_dir = "../uploads/img/";
        $file_name = uniqid() . '_' . basename($_FILES['image']['name']);
        $target_path = $upload_dir . $file_name;

        if(move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
            // حذف الصورة القديمة
            if(!empty($product['image'])) {
                @unlink($upload_dir . $product['image']);
            }
            $image = $file_name;
        } else {
            die("خطأ في رفع الصورة");
        }
    }

    // تحديث البيانات في قاعدة البيانات
    $update_stmt = $conn->prepare("
        UPDATE products SET
        name = ?,
        price = ?,
        description = ?,
        prosection = ?,
        prosize = ?,
        quantity = ?,
        image = ?,
        prounv = ?
        WHERE id = ?
    ");

    $update_stmt->bind_param("sdsssissi",
        $name,
        $price,
        $description,
        $prosection,
        $prosize,
        $quantity,
        $image,
        $prounv,
        $product_id
    );

    if($update_stmt->execute()) {
        $_SESSION['success'] = "تم تحديث المنتج بنجاح";
        header("Location: admianpanel.php");
        exit();
    } else {
        $error = "خطأ في التحديث: " . $update_stmt->error;
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
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        input, textarea, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button {
            background: #27ae60;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
        }
        .error {
            color: #b22222;
            background: #ffdada;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>تحديث المنتج - <?php echo htmlspecialchars($product['name']); ?></h1>

        <?php if(isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>اسم المنتج:</label>
                <input type="text" name="name" required value="<?php echo htmlspecialchars($product['name']); ?>">
            </div>

            <div class="form-group">
                <label>الصورة الحالية:</label>
                <?php
                $image_path = $product['image'];
                if (!empty($image_path)) {
                    if (strpos($image_path, 'uploads/img/') === false) {
                        $image_path = 'uploads/img/' . $image_path;
                    }
                    echo '<img src="../' . htmlspecialchars($image_path) . '" 
                               style="max-width: 100px; display: block; margin: 10px 0;"
                               onerror="this.src=\'../images/default-product.jpg\';">';
                }
                ?>
                <input type="file" name="image">
            </div>

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
                    value="<?php echo htmlspecialchars($product['prosection']); ?>">
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

            <button type="submit" name="update_pro">حفظ التغييرات</button>
        </form>
    </div>
</body>
</html>