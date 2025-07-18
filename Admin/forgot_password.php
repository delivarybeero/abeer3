<?php
session_start();
include("../include/connection.php");

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    
    // التحقق من وجود البريد الإلكتروني
    $sql = "SELECT * FROM admins WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $reset_token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", time() + 3600);
        
        $update_sql = "UPDATE admins SET reset_token = ?, reset_expires = ? WHERE email = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("sss", $reset_token, $expires, $email);
        $update_stmt->execute();
        
        // إرسال البريد باستخدام mail()
        $reset_link = "http://yourdomain.com/reset_password.php?token=$reset_token";
        $to = $email;
        $subject = "إعادة تعيين كلمة المرور";
        $message = "
            <html>
            <head>
                <title>إعادة تعيين كلمة المرور</title>
            </head>
            <body>
                <h2>إعادة تعيين كلمة المرور</h2>
                <p>الرجاء النقر على الرابط التالي:</p>
                <a href='$reset_link'>إعادة تعيين كلمة المرور</a>
                <p>الرابط صالح لمدة ساعة واحدة</p>
            </body>
            </html>
        ";
        
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: System Admin <admin@yourdomain.com>" . "\r\n";
        
        if(mail($to, $subject, $message, $headers)) {
            $message = "تم إرسال رابط إعادة التعيين إلى بريدك الإلكتروني";
        } else {
            $message = "حدث خطأ أثناء إرسال البريد";
        }
    } else {
        $message = "البريد الإلكتروني غير مسجل";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>استعادة كلمة المرور</title>
    <style>
        /* نفس التنسيقات السابقة */
        body {
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            font-family: Arial, sans-serif;
        }
        .container {
            width: 400px;
            margin: 80px auto;
            padding: 30px;
            background-color: #fff;
            box-shadow: 0 6px 17px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        /* ... باقي التنسيقات مثل النسخة السابقة ... */
    </style>
</head>
<body>
    <div class="container">
        <h1>استعادة كلمة المرور</h1>
        
        <?php if (!empty($message)): ?>
            <div class="message <?= strpos($message, 'خطأ') !== false ? 'error' : 'success' ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>
        
        <form action="forgot_password.php" method="post">
            <label for="email">البريد الإلكتروني:</label>
            <input type="email" id="email" name="email" required>
            
            <button type="submit">إرسال رابط الاستعادة</button>
            
            <div class="back-to-login">
                <a href="admin.php">العودة إلى تسجيل الدخول</a>
            </div>
        </form>
    </div>
</body>
</html>