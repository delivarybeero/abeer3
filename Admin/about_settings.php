<?php
session_start();
require_once("../include/connection.php");

if (!isset($_SESSION['EMAIL'])) {
    header('Location: ../index.php');
    exit();
}

// جلب البيانات الحالية
$settings_query = "SELECT about_page FROM site_settings WHERE id=1 LIMIT 1";
$settings_result = mysqli_query($conn, $settings_query);
$current_data = mysqli_fetch_assoc($settings_result);

// معالجة إرسال النموذج
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $about_data = [
        'title' => mysqli_real_escape_string($conn, $_POST['title']),
        'mission' => mysqli_real_escape_string($conn, $_POST['mission']),
        'vision' => mysqli_real_escape_string($conn, $_POST['vision']),
        'values' => mysqli_real_escape_string($conn, $_POST['values']),
        'history' => mysqli_real_escape_string($conn, $_POST['history']),
        'team' => mysqli_real_escape_string($conn, $_POST['team'])
    ];
    
    $json_data = json_encode($about_data, JSON_UNESCAPED_UNICODE);
    
    $update_query = "UPDATE site_settings SET about_page='$json_data' WHERE id=1";
    
    if (mysqli_query($conn, $update_query)) {
        $success_message = "تم حفظ بيانات 'من نحن' بنجاح!";
        $current_data['about_page'] = $json_data;
    } else {
        $error_message = "حدث خطأ أثناء الحفظ: " . mysqli_error($conn);
    }
}

// تحويل البيانات الحالية إذا كانت موجودة
$about_content = [];
if (!empty($current_data['about_page'])) {
    $about_content = json_decode($current_data['about_page'], true);
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إعدادات صفحة من نحن</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
    <style>
        body {
            font-family: 'Tahoma', Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #3498db;
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
        textarea, input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: inherit;
        }
        textarea {
            min-height: 120px;
        }
        .btn {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            display: block;
            margin: 30px auto 0;
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

        .action-buttons {
    margin: 30px 0;
    text-align: center;
}

.action-btn {
    display: inline-flex;
    align-items: center;
    padding: 12px 25px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: bold;
    transition: all 0.3s;
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

.action-btn i {
    margin-right: 8px;
    font-size: 18px;
}




    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-info-circle"></i> إعدادات صفحة من نحن</h1>
        
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
        
        <form action="about_settings.php" method="POST">
            <div class="form-group">
                <label for="title">عنوان الصفحة</label>
                <input type="text" id="title" name="title" 
                       value="<?php echo $about_content['title'] ?? ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="mission">رسالتنا</label>
                <textarea id="mission" name="mission"><?php echo $about_content['mission'] ?? ''; ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="vision">رؤيتنا</label>
                <textarea id="vision" name="vision"><?php echo $about_content['vision'] ?? ''; ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="values">قيمنا</label>
                <textarea id="values" name="values"><?php echo $about_content['values'] ?? ''; ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="history">تاريخنا</label>
                <textarea id="history" name="history"><?php echo $about_content['history'] ?? ''; ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="team">فريق العمل</label>
                <textarea id="team" name="team"><?php echo $about_content['team'] ?? ''; ?></textarea>
            </div>
            
            <button type="submit" class="btn">
                <i class="fas fa-save"></i> حفظ البيانات
            </button>
        </form>

        <div class="action-buttons">
    <a href="settings.php" class="action-btn back-btn">
        <i class="fas fa-arrow-right"></i> العودة إلى إعدادات الموقع
    </a>
</div>



    </div>
</body>
</html>