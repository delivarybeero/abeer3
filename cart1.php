<?php
// في بداية كل ملف
//ini_set('session.save_path', '/home/username/tmp');

session_start();

function getCartCount() {
    if(isset($_SESSION['cart'])) {
        return count($_SESSION['cart']);
    }
    return 0;
}

include("./include/connection.php");
include("file/header5.php");

// التحقق من اتصال قاعدة البيانات
if (!$conn) {
    die("فشل الاتصال بقاعدة البيانات: " . mysqli_connect_error());
}

$session_id = session_id();

// معالجة تحديث الكمية
if (isset($_POST['update_quantity'])) {
    $cart_id = intval($_POST['cart_id']);
    $new_quantity = intval($_POST['quantity']);
    
    $update_query = "UPDATE cart1 SET quantity = ? WHERE cart_id = ? AND session_id = ?";
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, 'iis', $new_quantity, $cart_id, $session_id);
    mysqli_stmt_execute($stmt);
}

// معالجة حذف المنتج
if (isset($_POST['remove_item'])) {
    $cart_id = intval($_POST['cart_id']);
    
    $delete_query = "DELETE FROM cart1 WHERE cart_id = ? AND session_id = ?";
    $stmt = mysqli_prepare($conn, $delete_query);
    mysqli_stmt_bind_param($stmt, 'is', $cart_id, $session_id);
    mysqli_stmt_execute($stmt);
    
    $_SESSION['cart_count'] = getCartCount($conn);
}

// معالجة إفراغ السلة
if (isset($_POST['empty_cart'])) {
    $delete_all_query = "DELETE FROM cart1 WHERE session_id = ?";
    $stmt = mysqli_prepare($conn, $delete_all_query);
    mysqli_stmt_bind_param($stmt, 's', $session_id);
    mysqli_stmt_execute($stmt);
    
    $_SESSION['cart_count'] = 0;
    
    echo '<br><br><div class="empty-cart">
        <center><h3>سلة التسوق فارغة</h3>
        <p>لم تقم بإضافة أي منتجات إلى سلة التسوق بعد</p>
        <a href="index.php" style="padding: 10px 20px; background: #4CAF50; color: white; 
        text-decoration: none; border-radius: 4px; display: inline-block; margin-top: 15px;">
        العودة إلى المتجر
        </a></center>
        </div>';
    exit();
}

// استعلام محتويات السلة
$cart_query = "SELECT * FROM cart1 WHERE session_id = ?";
$stmt = mysqli_prepare($conn, $cart_query);
mysqli_stmt_bind_param($stmt, 's', $session_id);
mysqli_stmt_execute($stmt);
$cart_items = mysqli_stmt_get_result($stmt);

$total_price = 0;
?>

<!DOCTYPE html>
<html dir="rtl">
<head>
    <title>سلة التسوق</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* جميع أنماط CSS الحالية تبقى كما هي */
        :root {
            --primary-color: #3498db;
            --secondary-color:#2c3e50;
            --danger-color: #f44336;
            --light-gray: #f5f5f5;
            --dark-gray: #333;
        }
        * {
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
            color: #333;
        }
        
        .cart-container {
            max-width: 1000px;
            margin: 20px auto;
            padding: 15px;
            font-family: Arial, sans-serif;
        }
        
        .cart-title {
            text-align: center;
            color: var(--primary-color);
            margin-bottom: 25px;
            font-size: 28px;
            font-weight: 600;
        }
        
        .cart-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .cart-table th, .cart-table td {
            padding: 12px 8px;
            border: 1px solid #eee;
            text-align: center;
        }
        
        .cart-table th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 500;
        }
        
        .cart-table tr:nth-child(even) {
            background-color: var(--light-gray);
        }
        
        .cart-table tr:hover {
            background-color: #f0f0f0;
        }
        
        .product-image {
            max-width: 60px;
            max-height: 60px;
            border-radius: 4px;
            object-fit: cover;
        }
        
        .quantity-input {
            width: 50px;
            text-align: center;
            padding: 6px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .btn {
            padding: 8px 16px;
            margin: 4px;
            cursor: pointer;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn i {
            margin-left: 5px;
        }
        
        .btn-update {
            background-color: var(--secondary-color);
            color: white;
        }
        
        .btn-update:hover {
            background-color: #0d8bf2;
            transform: translateY(-1px);
        }
        
        .btn-remove {
            background-color: var(--danger-color);
            color: white;
        }
        
        .btn-remove:hover {
            background-color: #e53935;
            transform: translateY(-1px);
        }
        
        .btn-checkout {
            background-color: var(--primary-color);
            color: white;
            padding: 12px 24px;
            font-size: 16px;
            font-weight: 600;
        }
        
        .btn-checkout:hover {
            background-color: #3d8b40;
            transform: translateY(-1px);
        }
        
        .btn-continue {
            background-color: var(--secondary-color);
            color: white;
            padding: 10px 20px;
        }
        
        .btn-continue:hover {
            background-color: #0d8bf2;
            transform: translateY(-1px);
        }
        
        .btn-empty {
            background-color: var(--danger-color);
            color: white;
            padding: 10px 20px;
        }
        
        .btn-empty:hover {
            background-color: #e53935;
            transform: translateY(-1px);
        }
        
        .empty-cart {
            text-align: center;
            padding: 40px 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin: 20px 0;
        }
        
        .empty-cart h3 {
            color: var(--dark-gray);
            font-size: 22px;
            margin-bottom: 15px;
        }
        
        .empty-cart p {
            color: #666;
            font-size: 16px;
            margin-bottom: 20px;
        }
        
        .cart-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 20px;
            justify-content: flex-start;
        }
        
        .cart-summary {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-top: 20px;
            text-align: left;
            font-size: 18px;
            font-weight: 500;
        }
        
        /* تنسيق للهواتف */
        @media (max-width: 768px) {
            .cart-container {
                padding: 10px;
            }
            
            .cart-table {
                display: block;
                overflow-x: auto;
            }
            
            .cart-table th, .cart-table td {
                padding: 8px 5px;
                font-size: 14px;
            }
            
            .product-image {
                max-width: 50px;
                max-height: 50px;
            }
            
            .quantity-input {
                width: 40px;
                padding: 4px;
            }
            
            .btn {
                padding: 6px 12px;
                font-size: 13px;
            }
            
            .btn-checkout {
                padding: 10px 18px;
                font-size: 15px;
            }
            
            .cart-actions {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .cart-actions a, .cart-actions form {
                width: 100%;
            }
            
            .cart-actions .btn {
                width: 100%;
                margin: 5px 0;
            }
        }
        
        /* تأثيرات للتفاعل */
        .btn:active {
            transform: translateY(1px);
        }
        
        /* رسائل التأكيد */
        .confirmation-message {
            background-color: #dff0d8;
            color: #3c763d;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            display: none;
        }

       /* باقي الأنماط تبقى نفسها */
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="cart-container">
        <h2 class="cart-title">سلة التسوق</h2>
        <div class="confirmation-message" id="confirmationMessage"></div>

        <?php if (mysqli_num_rows($cart_items) > 0): ?>
            <table class="cart-table">
                <tr>
                    <th>الصورة</th>
                    <th>المنتج</th>
                    <th>السعر</th>
                    <th>الكمية</th>
                    <th>الإجمالي</th>
                    <th>إجراءات</th>
                </tr>
                
                <?php while ($item = mysqli_fetch_assoc($cart_items)): ?>
                    <?php 
                    $item_total = $item['price'] * $item['quantity'];
                    $total_price += $item_total;
                    
                    // تصحيح مسار الصورة
                    $image_path = $item['img'];
                    if (strpos($image_path, '../') === 0) {
                        $image_path = substr($image_path, 3); // إزالة ../
                    }
                    ?>
                    
                    <tr>
                        <td>
                            <img src="<?php echo htmlspecialchars($image_path); ?>" 
                                 class="product-image" 
                                 onerror="this.src='images/default-product.png'">
                        </td>
                        <td><?php echo htmlspecialchars($item['name'] ?? ''); ?></td>
                        <td><?php echo isset($item['price']) ? number_format((float)$item['price'], 2) . ' د.ل' : '0.00 د.ل'; ?></td>
                        <td>
                            <form method="post" action="cart1.php" style="display: inline;">
                                <input type="hidden" name="cart_id" value="<?php echo htmlspecialchars($item['cart_id'] ?? ''); ?>">
                                <input type="number" name="quantity" value="<?php echo htmlspecialchars($item['quantity'] ?? 1); ?>" 
                                       min="1" max="100" class="quantity-input">
                                <button type="submit" name="update_quantity" class="btn btn-update">
                                    <i class="fas fa-sync-alt"></i> تحديث
                                </button>
                            </form>
                        </td>
                        <td>
                            <?php echo isset($item['price'], $item['quantity']) ? 
                                number_format($item['price'] * $item['quantity'], 2) . ' د.ل' : 
                                '0.00 د.ل'; ?>
                        </td>
                        <td>
                            <form method="post" action="cart1.php" style="display: inline;">
                                <input type="hidden" name="cart_id" value="<?php echo htmlspecialchars($item['cart_id'] ?? ''); ?>">
                                <button type="submit" name="remove_item" class="btn btn-remove">
                                    <i class="fas fa-trash-alt"></i> حذف
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>

            <div class="cart-summary">
                <span>المجموع الكلي: <?php echo number_format($total_price, 2); ?> د.ل</span>
            </div>

            <div class="cart-actions">
                <form method="post" action="cart1.php" style="display: inline;">
                    <button type="submit" name="empty_cart" class="btn btn-empty"
                            onclick="return confirm('هل أنت متأكد من إفراغ السلة بالكامل؟')">
                        <i class="fas fa-broom"></i> إفراغ السلة
                    </button>
                </form>
                <a href="index.php" class="btn btn-continue">
                    <i class="fas fa-arrow-left"></i> استمر بالتسوق
                </a>
                <a href="checkout.php" class="btn btn-checkout">
                    <i class="fas fa-credit-card"></i> إتمام الشراء
                </a>
            </div>
        <?php else: ?>
            <div class="empty-cart">
                <h3>سلة التسوق فارغة</h3>
                <p>لم تقم بإضافة أي منتجات إلى سلة التسوق بعد</p>
                <a href="index.php" class="btn btn-checkout">
                    <i class="fas fa-store"></i> العودة إلى المتجر
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // جميع نصوص JavaScript الحالية تبقى كما هي
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('emptied')) {
                const message = document.getElementById('confirmationMessage');
                message.textContent = 'تم إفراغ سلة التسوق بنجاح';
                message.style.display = 'block';
                setTimeout(() => {
                    message.style.display = 'none';
                }, 3000);
            }

            document.querySelector('form[action="cart1.php"]').addEventListener('submit', function(e) {
                if (e.submitter.name === 'empty_cart') {
                    document.getElementById('cart-counter').textContent = '0';
                }
            });
        });

        window.addEventListener('scroll', function() {
            const title = document.querySelector('.cart-title');
            if (window.scrollY > 50) {
                title.style.fontSize = '24px';
                title.style.padding = '10px 0';
            } else {
                title.style.fontSize = '28px';
                title.style.padding = '0';
            }
        });
    </script>
</body>
</html>

<?php include "footer.php"; ?>