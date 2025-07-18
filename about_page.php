<?php
require_once("include/connection.php");

$query = "SELECT about_page FROM site_settings WHERE id=1 LIMIT 1";
$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

$about_content = [];
if (!empty($data['about_page'])) {
    $about_content = json_decode($data['about_page'], true);
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>من نحن</title>
    <style>
        body {
            font-family: 'Tahoma', Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            color: #3498db;
            text-align: center;
            margin-bottom: 30px;
        }
        h2 {
            color: #2980b9;
            border-bottom: 2px solid #3498db;
            padding-bottom: 5px;
            margin-top: 30px;
        }
        p {
            margin-bottom: 15px;
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
        <?php if (!empty($about_content)): ?>
            <h1><?php echo htmlspecialchars($about_content['title'] ?? 'من نحن'); ?></h1>
            
            <?php if (!empty($about_content['mission'])): ?>
                <h2>رسالتنا</h2>
                <p><?php echo nl2br(htmlspecialchars($about_content['mission'])); ?></p>
            <?php endif; ?>
            
            <?php if (!empty($about_content['vision'])): ?>
                <h2>رؤيتنا</h2>
                <p><?php echo nl2br(htmlspecialchars($about_content['vision'])); ?></p>
            <?php endif; ?>
            
            <?php if (!empty($about_content['values'])): ?>
                <h2>قيمنا</h2>
                <p><?php echo nl2br(htmlspecialchars($about_content['values'])); ?></p>
            <?php endif; ?>
            
            <?php if (!empty($about_content['history'])): ?>
                <h2>تاريخنا</h2>
                <p><?php echo nl2br(htmlspecialchars($about_content['history'])); ?></p>
            <?php endif; ?>
            
            <?php if (!empty($about_content['team'])): ?>
                <h2>فريق العمل</h2>
                <p><?php echo nl2br(htmlspecialchars($about_content['team'])); ?></p>
            <?php endif; ?>
            
        <?php else: ?>
            <h1>من نحن</h1>
            <p>الصفحة قيد التطوير...</p>
        <?php endif; ?>

        <div class="action-buttons">
    <a href="index.php" class="action-btn back-btn">
        <i class="fas fa-arrow-right"></i> العودة إلى المتجر
    </a>
</div>
    </div>
</body>
</html>