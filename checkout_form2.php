<?php
session_start();

// إعدادات عرض الأخطاء (يجب تعطيلها في الإنتاج)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// الاتصال بقاعدة البيانات
require_once("./include/connection.php");

// التحقق من اتصال قاعدة البيانات
if (!$conn) {
    die("<div class='error'>فشل الاتصال بقاعدة البيانات. يرجى المحاولة لاحقاً.</div>");
}

// دالة لمعالجة مسار الصورة
function process_image_path($path) {
    if (strpos($path, '../') === 0) {
        return substr($path, 3);
    }
    return $path;
}

try {
    // جلب بيانات السلة
    $session_id = session_id();
    $cart_query = "SELECT 
                    c.cart_id,
                    c.product_id,
                    c.quantity,
                    c.price AS cart_price,
                    p.name,
                    p.price AS product_price,
                    p.image,
                    p.description
                  FROM cart1 c
                  JOIN products p ON c.product_id = p.id
                  WHERE c.session_id = ?";
    
    $stmt = mysqli_prepare($conn, $cart_query);
    if (!$stmt) {
        throw new Exception("خطأ في تحضير استعلام سلة التسوق");
    }

    mysqli_stmt_bind_param($stmt, 's', $session_id);
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("خطأ في تنفيذ استعلام سلة التسوق");
    }

    $cart_items = mysqli_stmt_get_result($stmt);
    if (!$cart_items) {
        throw new Exception("خطأ في جلب بيانات سلة التسوق");
    }

    if (mysqli_num_rows($cart_items) == 0) {
        header("Location: cart1.php");
        exit();
    }

    // حساب المجموع وجمع بيانات السلة
    $total_cart = 0;
    $cart_data = [];
    while ($item = mysqli_fetch_assoc($cart_items)) {
        // استخدام سعر المنتج إذا لم يكن هناك سعر في السلة
        $price = !empty($item['cart_price']) ? $item['cart_price'] : $item['product_price'];
        $item_total = $price * $item['quantity'];
        $total_cart += $item_total;
        
        $item['image'] = process_image_path($item['image']);
        $cart_data[] = $item;
    }

} catch (Exception $e) {
    die("<div class='error'>" . $e->getMessage() . "</div>");
}

// تضمين الهيدر
include("file/header6.php");
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إتمام عملية الشراء</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #28a745;
            --secondary: #343a40;
            --light: #f8f9fa;
            --dark: #343a40;
            --border: #dee2e6;
        }
        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .checkout-header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .checkout-steps {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }
        .step {
            padding: 10px 20px;
            position: relative;
        }
        .step.active {
            font-weight: bold;
            color: var(--primary);
        }
        .step.active:after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 100%;
            height: 3px;
            background: var(--primary);
        }
        .checkout-content {
            display: flex;
            gap: 20px;
        }
        .order-summary {
            flex: 1;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .checkout-form {
            flex: 1;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .product-item {
            display: flex;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border);
        }
        .product-image {
            width: 100px;
            height: 100px;
            object-fit: contain;
            border: 1px solid var(--border);
            border-radius: 4px;
            margin-left: 15px;
        }
        .product-info {
            flex: 1;
        }
        .product-name {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .product-meta {
            display: flex;
            justify-content: space-between;
            color: #666;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        input, textarea, select {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border);
            border-radius: 4px;
            font-family: 'Tajawal', sans-serif;
        }
        .btn-submit {
            background: var(--primary);
            color: white;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
            font-size: 16px;
        }
        .total-summary {
            font-size: 18px;
            font-weight: bold;
            text-align: left;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 2px solid var(--primary);
        }
        @media (max-width: 768px) {
            .checkout-content {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="checkout-header">
            <h1>إتمام عملية الشراء</h1>
        </div>
        
        <div class="checkout-steps">
            <div class="step active">سلة التسوق</div>
            <div class="step active">معلومات الشحن</div>
            <div class="step">تأكيد الطلب</div>
        </div>
        
        <div class="checkout-content">
            <div class="order-summary">
                <h2>ملخص الطلب</h2>
                <?php foreach ($cart_data as $item): ?>
                    <div class="product-item">
                        <img src="<?= htmlspecialchars($item['image']) ?>" 
                             class="product-image"
                             alt="<?= htmlspecialchars($item['name']) ?>"
                             onerror="this.src='images/default-product.jpg'">
                        <div class="product-info">
                            <div class="product-name"><?= htmlspecialchars($item['name']) ?></div>
                            <?php if (!empty($item['description'])): ?>
                                <div class="product-description"><?= substr(htmlspecialchars($item['description']), 0, 50) ?>...</div>
                            <?php endif; ?>
                            <div class="product-meta">
                                <span>الكمية: <?= $item['quantity'] ?></span>
                                <span>السعر: <?= number_format($item['cart_price'] ?? $item['product_price'], 2) ?> د.ل</span>
                                <span>المجموع: <?= number_format(($item['cart_price'] ?? $item['product_price']) * $item['quantity'], 2) ?> د.ل</span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div class="total-summary">
                    المجموع الكلي: <?= number_format($total_cart, 2) ?> د.ل
                </div>
            </div>
            
            <div class="checkout-form">
                <h2>معلومات التوصيل</h2>
                <form action="complete_order.php" method="post">
                    <div class="form-group">
                        <label for="name">الاسم الكامل *</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">رقم الهاتف *</label>
                        <input type="tel" id="phone" name="phone" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="address">عنوان التوصيل *</label>
                        <textarea id="address" name="address" rows="3" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="notes">ملاحظات إضافية</label>
                        <textarea id="notes" name="notes" rows="2"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="payment">طريقة الدفع *</label>
                        <select id="payment" name="payment_method" required>
                            <option value="">-- اختر طريقة الدفع --</option>
                            <option value="cash">نقداً عند الاستلام</option>
                            <option value="bank">تحويل بنكي</option>
                        </select>
                    </div>
                    
                    <input type="hidden" name="total_amount" value="<?= $total_cart ?>">
                    
                    <button type="submit" class="btn-submit">تأكيد الطلب</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            const phone = document.getElementById('phone').value;
            if (!/^(\+?218|0)?\d{9}$/.test(phone)) {
                alert('يرجى إدخال رقم هاتف صحيح');
                e.preventDefault();
            }
        });
    </script>
</body>
</html>

<?php
// إغلاق الاتصالات
if (isset($stmt)) {
    mysqli_stmt_close($stmt);
}
if (isset($conn)) {
    mysqli_close($conn);
}

// تضمين الفوتر
include("file/footer2.php");
?>