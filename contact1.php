<?php
session_start();
require_once("include/connection.php");

// جلب إعدادات الموقع
$settings_query = "SELECT site_name, logo_path, site_email, phone_number, whatsapp FROM site_settings WHERE id=1";
$settings = mysqli_fetch_assoc(mysqli_query($conn, $settings_query));

// معالجة إرسال النموذج
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    
    // إدخال الرسالة في قاعدة البيانات
    $insert_query = "INSERT INTO contact_messages (name, email, message) VALUES ('$name', '$email', '$message')";
    
    if (mysqli_query($conn, $insert_query)) {
        $success = "شكراً لك! سنتواصل معك قريباً.";
        
        // إرسال إشعار بالبريد (بدائي)
        $to = $settings['site_email'];
        $subject = "رسالة جديدة من $name";
        $headers = "From: $email";
        mail($to, $subject, $message, $headers);
    } else {
        $error = "حدث خطأ أثناء إرسال الرسالة!";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>اتصل بنا - <?= $settings['site_name'] ?></title>
    <style>
        :root {
            --primary: #3498db;
            --secondary: #2980b9;
            --light: #f8f9fa;
            --dark: #343a40;
        }
        
        body {
            font-family: 'Tahoma', sans-serif;
            background-color: #f5f5f5;
        }
        
        .contact-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        
        .contact-header {
            text-align: center;
            margin-bottom: 30px;
            color: var(--primary);
        }
        
        .contact-form .form-group {
            margin-bottom: 20px;
        }
        
        .contact-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        
        .contact-form input,
        .contact-form textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .contact-form textarea {
            height: 150px;
        }
        
        .submit-btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 4px;
            cursor: pointer;
            transition: 0.3s;
        }
        
        .submit-btn:hover {
            background: var(--secondary);
        }
        
        .contact-info {
            margin-top: 40px;
            background: var(--light);
            padding: 20px;
            border-radius: 8px;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
        }
        
        .action-btn {
            padding: 10px 20px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 3px 6px rgba(0,0,0,0.1);
        }
        
        .back-btn {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
        }
        
        .back-btn:hover {
            background: linear-gradient(135deg, #2980b9, #3498db);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        



    </style>
</head>
<body>
    <div class="contact-container">
        <div class="contact-header">
            <h1><i class="fas fa-envelope"></i> تواصل معنا</h1>
            <p>يسعدنا تواصلك معنا في أي وقت</p>
        </div>
        
        <?php if(isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php elseif(isset($error)): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>
        
        <form class="contact-form" method="POST">
            <div class="form-group">
                <label for="name">الاسم الكامل</label>
                <input type="text" id="name" name="name" required>
            </div>
            
            <div class="form-group">
                <label for="email">البريد الإلكتروني</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="message">الرسالة</label>
                <textarea id="message" name="message" required></textarea>
            </div>
            
            <button type="submit" class="submit-btn">إرسال الرسالة</button>
        </form>
        
        <div class="contact-info">
            <h3>معلومات التواصل</h3>
            <p><i class="fas fa-phone"></i> <?= $settings['phone_number'] ?? 'غير متوفر' ?></p>
            <p><i class="fas fa-envelope"></i> <?= $settings['site_email'] ?? 'غير متوفر' ?></p>
            <?php if(!empty($settings['whatsapp'])): ?>
                <p>
                    <i class="fab fa-whatsapp"></i> 
                    <a href="https://wa.me/<?= $settings['whatsapp'] ?>">تواصل عبر واتساب</a>
                </p>
            <?php endif; ?>
        </div>

        <div class="action-buttons">
            <a href="index.php" class="action-btn back-btn">
                <i class="fas fa-arrow-right"></i> العودة إلى الصفحة الرئيسية
            </a>
        </div>

    </div>
</body>
</html>