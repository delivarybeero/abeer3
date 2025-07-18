<?php
// الاتصال بقاعدة البيانات وجلب إعدادات الموقع
include("./include/connection.php");
$settings_sql = "SELECT * FROM site_settings LIMIT 1";
$settings_result = $conn->query($settings_sql);
$settings = $settings_result->fetch_assoc();
?>

<footer style="
    background: linear-gradient(135deg, #2c3e50 0%, #1a1a2e 100%);
    color: white;
    padding: 40px 0 20px;
    font-family: 'Cairo', sans-serif;
    direction: rtl;
">
    <div class="container" style="
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 15px;
    ">
        <!-- الصف العلوي -->
        <div style="
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        ">
            <!-- معلومات المتجر -->
            <div>
                <h3 style="
                    color: #4CAF50;
                    margin-bottom: 20px;
                    font-size: 1.4rem;
                    position: relative;
                    padding-bottom: 10px;
                ">
                    <span style="
                        position: absolute;
                        bottom: 0;
                        right: 0;
                        width: 50px;
                        height: 3px;
                        background: #4CAF50;
                    "></span>
                    <?= htmlspecialchars($settings['site_name'] ?? 'متجرنا') ?>
                </h3>
                <p style="line-height: 1.8; color: #ddd;">
                    نسعى لتقديم أفضل المنتجات بجودة عالية وسرعة في التوصيل. نقدم لعملائنا تجربة تسوق فريدة وسهلة.
                </p>
                <?php if($settings['phone_number'] ?? false): ?>
                <div style="margin-top: 15px; display: flex; align-items: center;">
                    <i class="fas fa-phone" style="margin-left: 10px; color: #4CAF50;"></i>
                    <a href="tel:<?= htmlspecialchars($settings['phone_number']) ?>" style="color: white; text-decoration: none;">
                        <?= htmlspecialchars($settings['phone_number']) ?>
                    </a>
                </div>
                <?php endif; ?>
            </div>

            <!-- روابط سريعة -->
            <div>
                <h3 style="
                    color: #4CAF50;
                    margin-bottom: 20px;
                    font-size: 1.4rem;
                    position: relative;
                    padding-bottom: 10px;
                ">
                    <span style="
                        position: absolute;
                        bottom: 0;
                        right: 0;
                        width: 50px;
                        height: 3px;
                        background: #4CAF50;
                    "></span>
                    روابط سريعة
                </h3>
                <ul style="list-style: none; padding: 0; line-height: 2.5;">
                    <li><a href="index.php" style="color: #ddd; text-decoration: none; transition: 0.3s;">الرئيسية</a></li>
                    <li><a href="about_page.php" style="color: #ddd; text-decoration: none; transition: 0.3s;">من نحن</a></li>
                    <li><a href="contact1.php" style="color: #ddd; text-decoration: none; transition: 0.3s;">اتصل بنا</a></li>
                    <li><a href="" style="color: #ddd; text-decoration: none; transition: 0.3s;">سياسة الخصوصية</a></li>
                </ul>
            </div>

            <!-- وسائل التواصل -->
            <div>
                <h3 style="
                    color: #4CAF50;
                    margin-bottom: 20px;
                    font-size: 1.4rem;
                    position: relative;
                    padding-bottom: 10px;
                ">
                    <span style="
                        position: absolute;
                        bottom: 0;
                        right: 0;
                        width: 50px;
                        height: 3px;
                        background: #4CAF50;
                    "></span>
                    تواصل معنا
                </h3>
                <div style="margin-bottom: 20px;">
                    <p style="color: #ddd; line-height: 1.8;">
                        تابعنا على وسائل التواصل الاجتماعي لمعرفة آخر العروض والمنتجات الجديدة.
                    </p>
                </div>
                <div style="display: flex; gap: 15px; font-size: 1.5rem;">
                    <?php if($settings['whatsapp'] ?? false): ?>
                    <a href="https://wa.me/<?= htmlspecialchars($settings['whatsapp']) ?>" target="_blank" style="color: #25D366; transition: 0.3s;">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                    <?php endif; ?>
                    
                    <?php if($settings['facebook'] ?? false): ?>
                    <a href="<?= htmlspecialchars($settings['facebook']) ?>" target="_blank" style="color: #1877F2; transition: 0.3s;">
                        <i class="fab fa-facebook"></i>
                    </a>
                    <?php endif; ?>
                    
                    <?php if($settings['instagram'] ?? false): ?>
                    <a href="<?= htmlspecialchars($settings['instagram']) ?>" target="_blank" style="color: #E4405F; transition: 0.3s;">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <?php endif; ?>
                    
                    <?php if($settings['twitter'] ?? false): ?>
                    <a href="<?= htmlspecialchars($settings['twitter']) ?>" target="_blank" style="color: #1DA1F2; transition: 0.3s;">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <?php endif; ?>
                    
                    <?php if($settings['messenger'] ?? false): ?>
                    <a href="<?= htmlspecialchars($settings['messenger']) ?>" target="_blank" style="color: #006AFF; transition: 0.3s;">
                        <i class="fab fa-facebook-messenger"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- حقوق النشر -->
        <div style="
            text-align: center;
            padding-top: 20px;
            margin-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: #aaa;
            font-size: 0.9rem;
        ">
            <p>جميع الحقوق محفوظة &copy; <?= date('Y') ?> <?= htmlspecialchars($settings['site_name'] ?? 'متجرنا') ?></p>
        </div>
    </div>
</footer>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />