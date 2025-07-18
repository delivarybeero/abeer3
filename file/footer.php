<?php
// الاتصال بقاعدة البيانات
include ("include/connection.php");

// جلب البيانات من جدول site_settings
$sql = "SELECT facebook, twitter, instagram, phone_number FROM site_settings LIMIT 1";
$result = $conn->query($sql);

$settings = [];
if ($result && $result->num_rows > 0) {
    $settings = $result->fetch_assoc();

    // تنظيف القيم من المسافات أو النصوص غير المرغوبة
    $settings['facebook'] = isset($settings['facebook']) ? trim($settings['facebook']) : '';
    $settings['twitter'] = isset($settings['twitter']) ? trim($settings['twitter']) : '';
    $settings['instagram'] = isset($settings['instagram']) ? trim($settings['instagram']) : '';
    $settings['whatsapp'] = isset($settings['whatsapp']) ? trim($settings['whatsapp']) : '';
    $settings['messenger'] = isset($settings['messenger']) ? trim($settings['messenger']) : '';

    $settings['phone_number'] = isset($settings['phone_number']) ? trim($settings['phone_number']) : '';

}

$conn->close();
?>

<!-- تضمين مكتبة Font Awesome -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

<footer style="background: #f1f1f1; padding: 20px; text-align: center; margin-top: 20px;">
    <div style="margin-bottom: 10px;">
        
        <?php if (!empty($settings['facebook'])): ?>
            <a href="<?php echo htmlspecialchars($settings['facebook']); ?>" target="_blank" style="margin: 0 10px; color: #3b5998; font-size: 24px;">
                <i class="fab fa-facebook"></i>
            </a>
        <?php endif; ?>

        <?php if (!empty($settings['mesenger'])): ?>
            <a href="<?php echo htmlspecialchars($settings['messenger']); ?>" target="_blank" style="margin: 0 10px; color: #3b5998; font-size: 24px;">
                <i class="fab fa-massenger"></i>
            </a>
        <?php endif; ?>








        <?php if (!empty($settings['twitter'])): ?>
            <a href="<?php echo htmlspecialchars($settings['twitter']); ?>" target="_blank" style="margin: 0 10px; color: #1DA1F2; font-size: 24px;">
                <i class="fab fa-twitter"></i>
            </a>
        <?php endif; ?>

        <?php if (!empty($settings['instagram'])): ?>
            <a href="<?php echo htmlspecialchars($settings['instagram']); ?>" target="_blank" style="margin: 0 10px; color: #C13584; font-size: 24px;">
                <i class="fab fa-instagram"></i>
            </a>
        <?php endif; ?>
    </div>
    <div style="margin-top: 10px;">
        <?php if (!empty($settings['phone_number'])): ?>
            <a href="tel:<?php echo htmlspecialchars($settings['phone_number']); ?>" style="color: #4CAF50; text-decoration: none; font-size: 18px;">
                <i class="fas fa-phone"></i> <?php echo htmlspecialchars($settings['phone_number']); ?>
            </a>
        <?php endif; ?>
    </div>
    <p style="margin-top: 15px; color: #555;">&copy; <?php echo date("Y"); ?> جميع الحقوق محفوظة</p>
</footer>