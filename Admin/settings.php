<?php
// إعدادات الجلسة والأخطاء
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// التحقق من تسجيل الدخول
if (!isset($_SESSION['EMAIL'])) {
    header('Location: ../index.php');
    exit();
}

// الاتصال بقاعدة البيانات
require_once("../include/connection.php");

if (!$conn) {
    die("<div class='alert alert-error'>فشل الاتصال بقاعدة البيانات: " . mysqli_connect_error() . "</div>");
}

// التحقق من صلاحيات مجلد الصور
$target_dir = "../images/";
if (!is_writable($target_dir)) {
    $error_message = "المجلد 'images' ليس لديه صلاحيات الكتابة. الرجاء تعديل الصلاحيات إلى 755 أو 777";
}

// القيم الافتراضية للإعدادات
$default_settings = [
    'site_name' => 'shopping_online',
    'logo_path' => 'images/a1.png',
    'site_email' => 'info@example.com', // إضافة قيمة افتراضية للبريد
    'phone_number' => '',
    'whatsapp' => '',
    'facebook' => '',
    'twitter' => '',
    'instagram' => '',
    'messenger' => ''
];

// جلب الإعدادات الحالية
$settings_query = "SELECT * FROM site_settings WHERE id=1 LIMIT 1";
$settings_result = mysqli_query($conn, $settings_query);

if (!$settings_result) {
    die("<div class='alert alert-error'>خطأ في استعلام قاعدة البيانات: " . mysqli_error($conn) . "</div>");
}

if (mysqli_num_rows($settings_result) > 0) {
    $settings = mysqli_fetch_assoc($settings_result);
    // تعيين قيمة افتراضية إذا كان الحقل فارغاً
    if (empty($settings['site_email'])) {
        $settings['site_email'] = $default_settings['site_email'];
    }
} else {
    // إنشاء سجل جديد بالقيم الافتراضية
    $insert_query = "INSERT INTO site_settings (site_name, logo_path, site_email) 
                    VALUES ('{$default_settings['site_name']}', 
                            '{$default_settings['logo_path']}', 
                            '{$default_settings['site_email']}')";
    
    if (mysqli_query($conn, $insert_query)) {
        $settings = $default_settings;
    } else {
        die("<div class='alert alert-error'>فشل في إنشاء إعدادات الموقع: " . mysqli_error($conn) . "</div>");
    }
}
// القيم الافتراضية للإعدادات
$default_settings = [
    'site_name' => 'shopping_online',
    'logo_path' => 'images/a1.png',
    'site_email' => 'info@example.com',
    // باقي الحقول...
];
// معالجة تحديث الإعدادات
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $update_fields = [];
    $error_messages = [];

    // التحقق من اسم الموقع
    $new_site_name = trim($_POST['site_name']);
    if (empty($new_site_name)) {
        $error_messages[] = "اسم الموقع مطلوب";
    } else {
        $new_site_name = mysqli_real_escape_string($conn, $new_site_name);
        $update_fields[] = "site_name='$new_site_name'";
    }

    // التحقق من بريد الموقع
// التحقق من بريد الموقع
$new_site_email = trim($_POST['site_email']);
if (empty($new_site_email)) {
    $error_messages[] = "بريد الموقع مطلوب";
} elseif (!filter_var($new_site_email, FILTER_VALIDATE_EMAIL)) {
    $error_messages[] = "صيغة البريد الإلكتروني غير صحيحة";
} else {
    $new_site_email = mysqli_real_escape_string($conn, $new_site_email);
    $update_fields[] = "site_email='$new_site_email'";
}

    // معالجة الحقول الاختيارية
    $optional_fields = ['phone_number', 'whatsapp', 'facebook', 'twitter', 'instagram', 'messenger'];
    foreach ($optional_fields as $field) {
        if (!empty($_POST[$field])) {
            $value = mysqli_real_escape_string($conn, trim($_POST[$field]));
            $update_fields[] = "$field='$value'";
        }
    }

    // معالجة صورة الشعار
    if (!empty($_FILES['logo']['name'])) {
        $image_info = getimagesize($_FILES['logo']['tmp_name']);
        if ($image_info === false) {
            $error_messages[] = "الملف المرفوع ليس صورة صالحة";
        } else {
            $imageFileType = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (!in_array($imageFileType, $allowed_types)) {
                $error_messages[] = "نوع الملف غير مسموح به. يسمح فقط بـ JPG, JPEG, PNG, GIF";
            } elseif ($_FILES['logo']['size'] > 2000000) {
                $error_messages[] = "حجم الملف كبير جداً! الحد الأقصى 2MB";
            } else {
                $random_name = uniqid() . '.' . $imageFileType;
                $target_file = $target_dir . $random_name;
                
                if (move_uploaded_file($_FILES['logo']['tmp_name'], $target_file)) {
                    // حذف الصورة القديمة إذا كانت موجودة
                    if (!empty($settings['logo_path']) && $settings['logo_path'] != 'images/a1.png') {
                        $old_file = "../" . $settings['logo_path'];
                        if (file_exists($old_file)) {
                            unlink($old_file);
                        }
                    }
                    
                    $logo_path = 'images/' . $random_name;
                    $update_fields[] = "logo_path='$logo_path'";
                } else {
                    $error_messages[] = "فشل في رفع الملف";
                }
            }
        }
    }

    // تنفيذ التحديث إذا لم تكن هناك أخطاء
    if (empty($error_messages)) {
        $update_query = "UPDATE site_settings SET " . implode(', ', $update_fields) . " WHERE id=1";
        
        if (mysqli_query($conn, $update_query)) {
            $success_message = "تم تحديث الإعدادات بنجاح!";
            // تحديث الإعدادات المحملة
            $settings_result = mysqli_query($conn, "SELECT * FROM site_settings WHERE id=1");
            $settings = mysqli_fetch_assoc($settings_result);
        } else {
            $error_messages[] = "حدث خطأ أثناء تحديث الإعدادات: " . mysqli_error($conn);
        }
    } else {
        $error_message = implode("<br>", $error_messages);
    }
}




?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إعدادات الموقع</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2980b9;
            --success-color: #2ecc71;
            --error-color: #e74c3c;
            --text-color: #333;
            --light-gray: #f5f5f5;
            --white: #fff;
            --border-radius: 8px;
            --box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        body {
            font-family: 'Tahoma', Arial, sans-serif;
            background-color: var(--light-gray);
            margin: 0;
            padding: 0;
            color: var(--text-color);
            line-height: 1.6;
        }
        
        .container {
            max-width: 900px;
            margin: 30px auto;
            background: var(--white);
            padding: 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }
        
        h1 {
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 30px;
            font-size: 28px;
            position: relative;
            padding-bottom: 15px;
        }
        
        h1::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 3px;
            background: var(--primary-color);
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: var(--text-color);
        }
        
        .required-field::after {
            content: ' *';
            color: var(--error-color);
        }
        
        input[type="text"],
        input[type="email"],
        input[type="file"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            transition: border 0.3s;
        }
        
        input[type="text"]:focus,
        input[type="email"]:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 5px rgba(52, 152, 219, 0.5);
        }
        
        .logo-container {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-top: 15px;
        }
        
        .logo-preview {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--primary-color);
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn:hover {
            background-color: var(--secondary-color);
        }
        
        .btn i {
            font-size: 18px;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .alert-success {
            background-color: rgba(46, 204, 113, 0.2);
            color: #155724;
            border-left: 4px solid var(--success-color);
        }
        
        .alert-error {
            background-color: rgba(231, 76, 60, 0.2);
            color: #721c24;
            border-left: 4px solid var(--error-color);
        }
        
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
        }
        
        .action-btn {
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .back-btn {
            background-color: #2ecc71;
            color: white;
        }
        
        .back-btn:hover {
            background-color: #27ae60;
        }
        
        .admin-btn {
            background-color: #9b59b6;
            color: white;
        }
        
        .admin-btn:hover {
            background-color: #8e44ad;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 20px;
                margin: 15px;
            }
            
            .logo-container {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-cog"></i> إعدادات الموقع</h1>
        
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <form action="settings.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="site_name" class="required-field">اسم الموقع</label>
                <input type="text" id="site_name" name="site_name" 
                       value="<?php echo htmlspecialchars($settings['site_name']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="site_email" class="required-field">بريد الموقع الرسمي</label>
                <input type="email" id="site_email" name="site_email" 
                       value="<?php echo htmlspecialchars($settings['site_email']); ?>" required
                       pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                       title="يجب إدخال بريد إلكتروني صحيح (مثال: info@example.com)">
                <small class="form-text">سيستخدم هذا البريد في المراسلات الرسمية</small>
            </div>
            
            <div class="form-group">
                <label for="phone_number">رقم الهاتف</label>
                <input type="text" id="phone_number" name="phone_number" 
                       value="<?php echo htmlspecialchars($settings['phone_number'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="whatsapp">رقم واتساب</label>
                <input type="text" id="whatsapp" name="whatsapp" 
                       value="<?php echo htmlspecialchars($settings['whatsapp'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="facebook">رابط فيسبوك</label>
                <input type="text" id="facebook" name="facebook" 
                       value="<?php echo htmlspecialchars($settings['facebook'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="twitter">رابط تويتر</label>
                <input type="text" id="twitter" name="twitter" 
                       value="<?php echo htmlspecialchars($settings['twitter'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="instagram">رابط انستغرام</label>
                <input type="text" id="instagram" name="instagram" 
                       value="<?php echo htmlspecialchars($settings['instagram'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="messenger">رابط ماسنجر</label>
                <input type="text" id="messenger" name="messenger" 
                       value="<?php echo htmlspecialchars($settings['messenger'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="logo">شعار الموقع</label>
                <input type="file" id="logo" name="logo" accept="image/*">
                <div class="logo-container">
                    <?php if (!empty($settings['logo_path'])): ?>
                        <img src="../<?php echo htmlspecialchars($settings['logo_path']); ?>" 
                             alt="الشعار الحالي" class="logo-preview" id="logoPreview">
                    <?php else: ?>
                        <img src="../images/a1.png" alt="الشعار الافتراضي" 
                             class="logo-preview" id="logoPreview">
                    <?php endif; ?>
                    <div>
                        <p><strong>ملاحظات:</strong></p>
                        <ul>
                            <li>يفضل استخدام صورة مربعة</li>
                            <li>الحجم الأمثل: 300×300 بكسل</li>
                            <li>الحد الأقصى للحجم: 2MB</li>
                            <li>الصيغ المسموحة: JPG, PNG, GIF</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label>إعدادات صفحة من نحن</label>
                <a href="about_settings.php" class="action-btn back-btn">
                    <i class="fas fa-info-circle"></i> إعدادات صفحة من نحن
                </a>
            </div>
            
            <button type="submit" class="btn">
                <i class="fas fa-save"></i> حفظ التغييرات
            </button>
        </form>
        
        <div class="action-buttons">
            <a href="../index.php" class="action-btn back-btn">
                <i class="fas fa-store"></i> العودة إلى المتجر
            </a>
            <a href="../Admin/admianpanel.php" class="action-btn admin-btn">
                <i class="fas fa-tachometer-alt"></i> لوحة تحكم الإدارة
            </a>
        </div>
    </div>

    <script>
        // عرض معاينة الصورة قبل الرفع
        document.getElementById('logo').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('logoPreview').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>