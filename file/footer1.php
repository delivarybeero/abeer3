<style>
    /* تنسيقات الفوتر الجديدة المدمجة */
    .main-footer {
        background: #2c3e50;
        color: #ecf0f1;
        padding: 20px 0 10px;
        font-family: 'Arial', sans-serif;
        position: fixed;
        bottom: 0;
        width: 100%;
        z-index: 1000;
    }
    
    .footer-content {
        max-width: 1200px;
        margin: 0 auto;
        text-align: center;
    }
    
    .social-icons {
        display: flex;
        justify-content: center;
        gap: 15px;
        margin-bottom: 10px;
    }
    
    .social-icon {
        color: white;
        font-size: 18px;
        transition: all 0.3s ease;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.1);
    }
    
    .social-icon:hover {
        transform: scale(1.2);
    }
    
    .facebook-icon:hover { background: #3b5998; }
    .messenger-icon:hover { background: #006AFF; }
    .whatsapp-icon:hover { background: #25D366; }
    .twitter-icon:hover { background: #1DA1F2; }
    .instagram-icon:hover { 
        background: radial-gradient(circle at 30% 107%, #fdf497 0%, #fdf497 5%, #fd5949 45%, #d6249f 60%, #285AEB 90%);
    }
    
    .contact-info {
        margin: 8px 0;
    }
    
    .phone-link {
        color: #ecf0f1;
        text-decoration: none;
        font-size: 14px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    
    .copyright {
        font-size: 12px;
        opacity: 0.8;
        margin-top: 8px;
        padding-top: 8px;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }
</style>

<footer class="main-footer">
    <div class="footer-content">
        <div class="social-icons">
            <?php if (!empty($settings['facebook'])): ?>
            <a href="<?php echo htmlspecialchars($settings['facebook']); ?>" target="_blank" class="social-icon facebook-icon" title="فيسبوك">
                <i class="fab fa-facebook-f"></i>
            </a>
            <?php endif; ?>
            
            <?php if (!empty($settings['messenger'])): ?>
            <a href="<?php echo htmlspecialchars($settings['messenger']); ?>" target="_blank" class="social-icon messenger-icon" title="ماسنجر">
                <i class="fab fa-facebook-messenger"></i>
            </a>
            <?php endif; ?>
            
            <?php if (!empty($settings['whatsapp'])): ?>
            <a href="https://wa.me/<?php echo htmlspecialchars($settings['whatsapp']); ?>" target="_blank" class="social-icon whatsapp-icon" title="واتساب">
                <i class="fab fa-whatsapp"></i>
            </a>
            <?php endif; ?>
            
            <?php if (!empty($settings['twitter'])): ?>
            <a href="<?php echo htmlspecialchars($settings['twitter']); ?>" target="_blank" class="social-icon twitter-icon" title="تويتر">
                <i class="fab fa-twitter"></i>
            </a>
            <?php endif; ?>
            
            <?php if (!empty($settings['instagram'])): ?>
            <a href="<?php echo htmlspecialchars($settings['instagram']); ?>" target="_blank" class="social-icon instagram-icon" title="إنستجرام">
                <i class="fab fa-instagram"></i>
            </a>
            <?php endif; ?>
        </div>
        
        <div class="contact-info">
            <?php if (!empty($settings['phone_number'])): ?>
            <a href="tel:<?php echo htmlspecialchars($settings['phone_number']); ?>" class="phone-link">
                <i class="fas fa-phone-alt"></i>
                <?php echo htmlspecialchars($settings['phone_number']); ?>
            </a>
            <?php endif; ?>
        </div>
        
        <div class="copyright">
            &copy; <?php echo date("Y"); ?> جميع الحقوق محفوظة
        </div>
    </div>
</footer>