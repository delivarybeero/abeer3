<?php
session_start();
require_once("include/connection.php");

// جلب إعدادات الموقع للعرض
$settings_query = "SELECT * FROM site_settings WHERE id=1 LIMIT 1";
$settings_result = mysqli_query($conn, $settings_query);
$settings = mysqli_fetch_assoc($settings_result);

// معالجة إرسال النموذج
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    // إدخال البيانات في قاعدة البيانات
    $insert_query = "INSERT INTO contact_messages (name, email, phone, subject, message) 
                    VALUES ('$name', '$email', '$phone', '$subject', '$message')";
    
    if (mysqli_query($conn, $insert_query)) {
        $success_message = "شكراً لك! تم استلام رسالتك وسنقوم بالرد في أقرب وقت.";
        
        // هنا يمكنك إضافة إرسال إيميل إشعار إذا لزم الأمر
    } else {
        $error_message = "حدث خطأ أثناء إرسال الرسالة. يرجى المحاولة مرة أخرى.";
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>اتصل بنا - <?php echo htmlspecialchars($settings['site_name']); ?></title>
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
        
        .contact-container {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
        }
        
        .contact-form {
            flex: 1;
            min-width: 300px;
        }
        
        .contact-info {
            flex: 1;
            min-width: 300px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        
        .required-field::after {
            content: ' *';
            color: var(--error-color);
        }
        
        input[type="text"],
        input[type="email"],
        input[type="tel"],
        textarea,
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            transition: border 0.3s;
            font-family: inherit;
        }
        
        textarea {
            min-height: 150px;
        }
        
        input:focus,
        textarea:focus,
        select:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 5px rgba(52, 152, 219, 0.5);
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
            width: 100%;
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
        
        .info-box {
            background: var(--light-gray);
            padding: 20px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .info-icon {
            font-size: 24px;
            color: var(--primary-color);
            min-width: 40px;
            text-align: center;
        }
        
        .info-content h3 {
            margin: 0 0 5px 0;
            color: var(--primary-color);
        }
        
        .info-content p {
            margin: 0;
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
        
        @media (max-width: 768px) {
            .container {
                padding: 20px;
                margin: 15px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-headset"></i> اتصل بنا</h1>
        
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
        
        <div class="contact-container">
            <div class="contact-form">
                <form action="contact.php" method="POST">
                    <div class="form-group">
                        <label for="name" class="required-field">الاسم الكامل</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="required-field">البريد الإلكتروني</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">رقم الهاتف</label>
                        <input type="tel" id="phone" name="phone">
                    </div>
                    
                    <div class="form-group">
                        <label for="subject" class="required-field">موضوع الرسالة</label>
                        <select id="subject" name="subject" required>
                            <option value="">اختر موضوع الرسالة</option>
                            <option value="استفسار عام">استفسار عام</option>
                            <option value="مشكلة تقنية">مشكلة تقنية</option>
                            <option value="اقتراح أو شكوى">اقتراح أو شكوى</option>
                            <option value="الشراكة والتعاون">الشراكة والتعاون</option>
                            <option value="أخرى">أخرى</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="message" class="required-field">نص الرسالة</label>
                        <textarea id="message" name="message" required></textarea>
                    </div>
                    
                    <button type="submit" class="btn">
                        <i class="fas fa-paper-plane"></i> إرسال الرسالة
                    </button>
                </form>
            </div>
            
            <div class="contact-info">
                <div class="info-box">
                    <div class="info-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="info-content">
                        <h3>عنواننا</h3>
                        <p><?php echo htmlspecialchars($settings['address'] ?? ' البيضاء:الجبل الاخضر_ليبيا
  '); ?></p>
                    </div>
                </div>
                <div class="info-box">
    <div class="info-icon">
        <i class="fas fa-envelope"></i>
    </div>
    <div class="info-content">
        <h3>البريد الإلكتروني الرسمي</h3>
        <p><?php echo htmlspecialchars($settings['site_email'] ?? 'info@example.com'); ?></p>
    </div>
</div>
                <div class="info-box">
                    <div class="info-icon">
                        <i class="fas fa-phone-alt"></i>
                    </div>
                    <div class="info-content">
                        <h3>اتصل بنا</h3>
                        <p><?php echo htmlspecialchars($settings['phone_number'] ?? '+966 12 345 6789'); ?></p>
                        <?php if (!empty($settings['whatsapp'])): ?>
                            <p>واتساب: <?php echo htmlspecialchars($settings['whatsapp']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="info-box">
                    <div class="info-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="info-content">
                        <h3>البريد الإلكتروني</h3>
                        <p><?php echo htmlspecialchars($settings['contact_email'] ?? 'info@example.com'); ?></p>
                    </div>
                </div>
                
                <div class="info-box">
                    <div class="info-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="info-content">
                        <h3>ساعات العمل</h3>
                        <p>الأحد - الخميس: 8 صباحاً - 5 مساءً</p>
                        <p>الجمعة - السبت: إجازة</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="action-buttons">
            <a href="index.php" class="action-btn back-btn">
                <i class="fas fa-arrow-right"></i> العودة إلى الصفحة الرئيسية
            </a>
        </div>
    </div>
</body>
</html>