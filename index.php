<?php
session_start();
include("./include/connection.php");
include("file/header6.php");
$shop_whatsapp='';
$query = "SELECT * FROM products";
$result = mysqli_query($conn, $query);
$products_count = mysqli_num_rows($result);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>منتجاتنا</title>
    <!-- رابط مكتبة Font Awesome للأيقونات -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* تنسيقات عامة */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f9f9f9;
            color: #333;
        }
        
        main {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .products-header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px 0;
        }
        
        .products-header h2 {
            color: #2c3e50;
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        /* شبكة المنتجات */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
            padding: 10px;
        }
        
        /* بطاقة المنتج */
        .product-card {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        /* صورة المنتج */
        .product-image {
            position: relative;
            height: 200px;
            overflow: hidden;
        }
        
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }
        
        .product-card:hover .product-image img {
            transform: scale(1.05);
        }
        
        .availability-label {
            position: absolute;
            top: 10px;
            left: 10px;
            background: #e74c3c;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
        }
        
        /* معلومات المنتج */
        .product-info {
            padding: 15px;
        }
        
        .product-category {
            color: #7f8c8d;
            font-size: 13px;
            margin-bottom: 5px;
        }
        
        .product-name {
            margin: 10px 0;
            font-size: 16px;
            height: 40px;
            overflow: hidden;
        }
        
        .product-name a {
            color: #2c3e50;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .product-name a:hover {
            color: #e74c3c;
        }
        
        .product-price {
            color: #e74c3c;
            font-weight: bold;
            font-size: 18px;
            margin: 10px 0;
        }
        
        /* زر إضافة إلى السلة */
        .add-to-cart-btn {
            width: 100px;
            padding: 8 12px;
            background:rgb(143, 220, 243);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 10px;
        }
        
        .add-to-cart-btn:hover {
            background: #c0392b;
            transform: translateY(-2px);
        }
        
        .add-to-cart-btn:disabled {
            background: #95a5a6;
            cursor: not-allowed;
            transform: none;
        }
        
        /* عناصر التحكم بالكمية */
        .quantity-control {
            display: flex;
            margin: 15px 0;
            align-items: center;
            justify-content: center;
        }
        
        .quantity-control button {
            width: 28px;
            height: 28px;
            background: #f1f1f1;
            border: 1px solid #ddd;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            transition: all 0.2s;
        }
        
        .quantity-control button:hover {
            background: #e0e0e0;
        }
        
        .quantity-input {
            width: 45px;
            height: 28px;
            text-align: center;
            border: 1px solid #ddd;
            margin: 0 5px;
            font-size: 14px;
        }
        
        /* زر العودة لأعلى الصفحة */
        .back-to-top {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 50%;
    font-size: 24px;
    cursor: pointer;
    opacity: 0;
    transform: translateY(20px);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    z-index: 9999;
    display: flex;
    justify-content: center;
    align-items: center;
    text-decoration: none;
    pointer-events: none;
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
    border: none;
    outline: none;
}

.back-to-top.active {
    opacity: 1;
    transform: translateY(0);
    pointer-events: auto;
}

.back-to-top:hover {
    transform: translateY(-5px) scale(1.05);
    box-shadow: 0 8px 25px rgba(0,0,0,0.2);
}
/* تأكد أن الفوتر لديه z-index أقل */
footer {
    background: linear-gradient(135deg, #2c3e50 0%, #1a1a2e 100%) !important;

    position: relative;
    z-index: 1; /* أي قيمة أقل من 9999 */
} </style>
</head>
<body>

<main>
    <div class="products-header">
        <h2>منتجاتنا</h2>
        <p>عرض <?php echo $products_count; ?> منتج</p>
    </div>

    <div class="products-grid">
        <?php while ($row = mysqli_fetch_assoc($result)): 
            // تعيين القيم الافتراضية
            $row['name'] = $row['name'] ?? '';
            $row['prosection'] = $row['prosection'] ?? '';
            $row['image'] = $row['image'] ?? 'default-product.jpg';
            $row['price'] = $row['price'] ?? 0;
            $is_available = ($row['prounv'] ?? '') !== 'غير متوفر';
        ?>
        <div class="product-card">
            <div class="product-image">
                <a href="details1.php?id=<?php echo (int)$row['id']; ?>">
                    <img src="<?php echo htmlspecialchars($row['image']); ?>" 
                         alt="<?php echo htmlspecialchars($row['name']); ?>"
                         onerror="this.src='images/default-product.jpg'">
                    <?php if(!$is_available): ?>
                    <span class="availability-label">غير متوفر</span>
                    <?php endif; ?>
                </a>
            </div>

            <div class="product-info">
                <div class="product-category">
                    <?php echo htmlspecialchars($row['prosection']); ?>
                </div>

                <h3 class="product-name">
                    <a href="details.php?id=<?php echo (int)$row['id']; ?>">
                        <?php echo htmlspecialchars($row['name']); ?>
                    </a>
                </h3>

                <div class="product-price">
                    <?php echo number_format((float)$row['price'], 2); ?> د.ل
                </div>

                <form action="add_to_cart.php" method="post" class="add-to-cart-form">
                    <input type="hidden" name="product_id" value="<?php echo (int)$row['id']; ?>">
                    <input type="hidden" name="name" value="<?php echo htmlspecialchars($row['name']); ?>">
                    <input type="hidden" name="price" value="<?php echo (float)$row['price']; ?>">
                    <input type="hidden" name="image" value="<?php echo htmlspecialchars($row['image']); ?>">
                    
                    <div class="quantity-control">
                        <button type="button" class="quantity-minus">-</button>
                        <input type="number" name="quantity" value="1" min="1" max="100" class="quantity-input">
                        <button type="button" class="quantity-plus">+</button>
                    </div>

                    <button type="submit" name="add_to_cart" class="add-to-cart-btn" 
                        <?php echo $is_available ? '' : 'disabled'; ?>>
                        <i class="fas fa-cart-plus"></i>
                        <span>أضف  للسلة</span>
                    </button>
                </form>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</main>

<!-- زر العودة لأعلى الصفحة -->
<!-- أيقونة السهم التقليدي -->
<!-- أو أي من هذه البدائل -->
<a href="#" class="back-to-top">
    <i class="fas fa-rocket"></i> <!-- أيقونة صاروخ -->
</a>

<a href="#" class="back-to-top">
    <i class="fas fa-chevron-up"></i> <!-- شكل شيفرون -->
</a>




<a href="#" class="back-to-top">
    <i class="fas fa-angle-double-up"></i> <!-- سهم مزدوج -->
</a>


<a href="#" class="back-to-top">
    <i class="fas fa-long-arrow-alt-up"></i> <!-- سهم طويل -->
</a>














<script>
// التحكم في الكمية
document.querySelectorAll('.quantity-plus').forEach(button => {
    button.addEventListener('click', function() {
        const input = this.parentNode.querySelector('.quantity-input');
        if (parseInt(input.value) < 100) {
            input.value = parseInt(input.value) + 1;
        }
    });
});

document.querySelectorAll('.quantity-minus').forEach(button => {
    button.addEventListener('click', function() {
        const input = this.parentNode.querySelector('.quantity-input');
        if (parseInt(input.value) > 1) {
            input.value = parseInt(input.value) - 1;
        }
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const backToTop = document.querySelector('.back-to-top');
    const footer = document.querySelector('footer');
    
    function updateButtonPosition() {
        const footerRect = footer.getBoundingClientRect();
        const windowHeight = window.innerHeight;
        
        // إذا كان الفوتر ظاهراً على الشاشة
        if (footerRect.top < windowHeight) {
            backToTop.style.bottom = (windowHeight - footerRect.top + 20) + 'px';
        } else {
            backToTop.style.bottom = '30px';
        }
    }

    window.addEventListener('scroll', function() {
        // التحكم في ظهور/اختفاء الزر
        if (window.pageYOffset > 300) {
            backToTop.classList.add('active');
        } else {
            backToTop.classList.remove('active');
        }
        
        // تحديث موقع الزر بالنسبة للفوتر
        updateButtonPosition();
    });

    backToTop.addEventListener('click', function(e) {
        e.preventDefault();
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    // تحديث الموقع عند تحميل الصفحة
    updateButtonPosition();
});
</script>

<br>
<br>
<?php include "footer.php"; ?>


</body>
</html>
</body>
</html>