<style>
    /* تنسيقات الفوتر المدمج المعدل */
    .main-footer {
        background: #2c3e50;
        color: #ecf0f1;
        padding: 12px 0;
        font-family: 'Arial', sans-serif;
        position: fixed;
        bottom: 0;
        width: 100%;
        z-index: 1000;
    }
    
    .footer-content {
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 15px;
        flex-wrap: wrap;
    }
    
    .social-icons {
        display: flex;
        gap: 10px;
        align-items: center;
    }
    
    .social-icon {
        color: white;
        font-size: 16px;
        transition: all 0.3s ease;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.1);
        text-decoration: none;
    }
    .social-icon:hover {
        transform: scale(1.2);
    }
    .whatsapp-icon:hover { background: #25D366; }

    .facebook-icon:hover { background: #3b5998; }
    .messenger-icon:hover { background:rgb(255, 123, 0); }
    .whatsapp-icon:hover { background: #25D366; }
    .twitter-icon:hover { background: #1DA1F2; }
    .instagram-icon:hover { 
        background: radial-gradient(circle at 30% 107%, #fdf497 0%, #fdf497 5%, #fd5949 45%, #d6249f 60%, #285AEB 90%);
    }
    

    
    .phone-link {
        color: #ecf0f1;
        text-decoration: none;
        font-size: 13px;
        display: flex;
        align-items: center;
        gap: 5px;
        padding: 5px 10px;
        border-radius: 20px;
        background: rgba(255, 255, 255, 0.1);
    }
    
    .copyright {
        font-size: 10px;
        opacity: 0.8;
        margin-top: 4px;
        width: 100%;
        text-align: center;
        padding-top: 6px;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    /* إضافة هامش للمحتوى الرئيسي */
    body {
        padding-bottom: 70px !important; /* يتناسب مع ارتفاع الفوتر */
    }
    
    /* هامش إضافي لزر الإضافة للسلة */
    .add-to-cart-button {
        margin-bottom: 80px !important;
    }
</style>

<footer class="main-footer">
    <div class="footer-content">
        <div class="social-icons">
            <!-- الأيقونات الاجتماعية -->
            <?php if (!empty($settings['facebook'])): ?>
            <a href="<?php echo htmlspecialchars($settings['facebook']); ?>" target="_blank" class="social-icon facebook-icon" title="فيسبوك">
                <i class="fab fa-facebook-f"></i>
            </a>
            <?php endif; ?>
            
            <!-- باقي الأيقونات ... -->
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

            <?php if (!empty($settings['phone_number'])): ?>
            <a href="tel:<?php echo htmlspecialchars($settings['phone_number']); ?>" class="phone-link   whatsapp-icon " title="اتصل بنا">
                <i class="fas fa-phone-alt"></i>
                <span><?php echo htmlspecialchars($settings['phone_number']); ?></span>
            </a>
            <?php endif; ?>
        </div>
        
        <div class="copyright">
            &copy; <?php echo date("Y"); ?> جميع الحقوق محفوظة
        </div>
    </div>
</footer>

<script>
// حل إضافي للتأكد من ظهور العناصر
document.addEventListener('DOMContentLoaded', function() {
    const footerHeight = document.querySelector('.main-footer').offsetHeight;
    document.body.style.paddingBottom = footerHeight + 'px';
    
    // التأكد من أن زر الإضافة للسلة ظاهر
    const addToCartBtn = document.querySelector('.add-to-cart-button');
    if(addToCartBtn) {
        addToCartBtn.style.marginBottom = (footerHeight + 10) + 'px';
    }
});
</script>