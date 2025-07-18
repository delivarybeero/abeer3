<?php
// بداية الملف
session_start();

// تحقق من تسجيل الدخول
$_SESSION['test'] = 'session_works';
if(!isset($_SESSION['test'])) {
    die("الجلسات لا تعمل! تحقق من إعدادات PHP.ini");
}
include("./include/connection.php");

// التحقق من اتصال قاعدة البيانات
if (!$conn) {
    die("فشل الاتصال بقاعدة البيانات: " . mysqli_connect_error());
}

$session_id = session_id();

// استعلام لاستعادة محتويات السلة
$cart_query = "SELECT c.*, p.image as product_image, p.name as product_name 
               FROM cart1 c 
               JOIN products p ON c.product_id = p.id 
               WHERE c.session_id = ?";
$stmt = mysqli_prepare($conn, $cart_query);
mysqli_stmt_bind_param($stmt, 's', $session_id);
mysqli_stmt_execute($stmt);
$cart_items = mysqli_stmt_get_result($stmt);

// التحقق من وجود عناصر في السلة
if (mysqli_num_rows($cart_items) == 0) {
    header("Location: cart1.php");
    exit();
}

// معالجة إرسال الطلب
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['complete_order'])) {
    // تنظيف المدخلات
    $customer_name = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $total_amount = floatval($_POST['total_amount']);
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);
    $user_id = $_SESSION['user_id'];

    // بدء المعاملة
    mysqli_begin_transaction($conn);

    // ... [الكود السابق يبقى كما هو حتى جزء معالجة الطلب] ...

try {
    // 1. إضافة الطلب إلى جدول orders1
    $order_query = "INSERT INTO orders1 (
        customer_name, 
        phone, 
        shipping_address, 
        total_amount, 
        status, 
        session_id, 
        invoice_name
    ) VALUES (?, ?, ?, ?, 'جديد', ?, ?)";

    $stmt = mysqli_prepare($conn, $order_query);
    mysqli_stmt_bind_param($stmt, "sssdss", 
        $customer_name, 
        $phone, 
        $address, 
        $total_amount, 
        $session_id, 
        $invoice_name
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("خطأ في إضافة الطلب: " . mysqli_error($conn));
    }

    $order_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);

    // 2. معالجة عناصر السلة
    mysqli_data_seek($cart_items, 0);
    while ($item = mysqli_fetch_assoc($cart_items)) {
        // إضافة إلى جدول order_details
        $detail_query = "INSERT INTO order_details (
            order_id, 
            product_id, 
            product_name, 
            product_price, 
            product_quantity
        ) VALUES (?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($conn, $detail_query);
        mysqli_stmt_bind_param($stmt, "iisdi", 
            $order_id, 
            $item['product_id'], 
            $item['name'], 
            $item['price'], 
            $item['quantity']
        );
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // تحديث المخزون
        $update_query = "UPDATE products SET quantity = quantity - ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, "ii", $item['quantity'], $item['product_id']);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    // 3. إضافة إلى جدول orders1_items
    // داخل كتلة try بعد إدراج order_details وقبل تفريغ السلة
// 3. إضافة العناصر إلى جدول orders1_items
mysqli_data_seek($cart_items, 0);
while ($item = mysqli_fetch_assoc($cart_items)) {
    $items_query = "INSERT INTO orders1_items (
        order_id,
        customer_name,
        product_id,
        quantity,
        price,
        product_image,
        product_name
    ) VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $items_query);
    
    // استخدام product_image من الاستعلام الأساسي بدلاً من img
    $image_path = $item['product_image'];
    if (strpos($image_path, '../') === 0) {
        $image_path = substr($image_path, 3);
    } elseif (strpos($image_path, 'uploads/') !== 0) {
        $image_path = 'uploads/img/' . $image_path;
    }
    
    mysqli_stmt_bind_param($stmt, "isidsss", 
        $order_id,
        $customer_name,
        $item['product_id'],
        $item['quantity'],
        $item['price'],
        $image_path,
        $item['product_name']  // استخدام product_name من الاستعلام الأساسي
    );
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("خطأ في إضافة عناصر الطلب إلى orders1_items: " . mysqli_error($conn));
    }
    mysqli_stmt_close($stmt);
}

    // 4. تفريغ السلة
    $delete_query = "DELETE FROM cart1 WHERE session_id = ?";
    $stmt = mysqli_prepare($conn, $delete_query);
    mysqli_stmt_bind_param($stmt, "s", $session_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // ... [باقي الكود يبقى كما هو] ...
        // تأكيد المعاملة
        mysqli_commit($conn);

        // تخزين بيانات الطلب في الجلسة
        $_SESSION['order_id'] = $order_id;
        $_SESSION['order_completed'] = true;

        // إرسال إشعار بالبريد الإلكتروني (اختياري)
        // send_order_confirmation_email($order_id, $customer_name, $email);

        header("Location: order_success.php");
        exit();

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['error'] = "حدث خطأ في معالجة طلبك: " . $e->getMessage();
        header("Location: checkout.php");
        exit();
    }
}

include("file/header5.php");
?>

<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إتمام عملية الشراء</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Tahoma', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        
        .checkout-container {
            max-width: 1000px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .checkout-steps {
            margin-top: 30px;
        }
        
        .step {
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 20px;
            background: #fff;
        }
        
        .step.active {
            border-color: #4CAF50;
        }
        
        .order-summary {
            margin: 15px 0;
        }
        
        .order-item {
            display: flex;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .order-item img {
            margin-right: 15px;
            max-width: 60px;
            max-height: 60px;
            border-radius: 4px;
            object-fit: cover;
        }
        
        .order-item-details {
            flex: 1;
        }
        
        .order-total {
            padding: 15px;
            text-align: left;
            font-size: 1.2em;
            border-top: 2px solid #4CAF50;
            background: #f9f9f9;
            margin-top: 10px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: 'Tahoma', sans-serif;
        }
        
        .checkout-btn {
            background: #4CAF50;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
            width: 100%;
            transition: background 0.3s;
        }
        
        .checkout-btn:hover {
            background: #45a049;
        }
        
        .error-message {
            color: #d9534f;
            background: #f2dede;
            border: 1px solid #ebccd1;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        
        @media (max-width: 768px) {
            .checkout-container {
                padding: 10px;
            }
            
            .order-item {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .order-item img {
                margin-bottom: 10px;
                margin-right: 0;
            }
        }
    </style>
</head>
<body>
    <div class="checkout-container">
        <h1>إتمام عملية الشراء</h1>
        
        <?php if(isset($_SESSION['error'])): ?>
            <div class="error-message">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <div class="checkout-steps">
            <!-- الخطوة 1: تأكيد الطلب -->
            <div class="step active">
                <h2>1. تأكيد الطلب</h2>
                <div class="order-summary">
                    <?php
                    $total = 0;
                    mysqli_data_seek($cart_items, 0);
                    while ($item = mysqli_fetch_assoc($cart_items)) {
                        $item_total = $item['price'] * $item['quantity'];
                        $total += $item_total;
                        echo '<div class="order-item">';
                        
                        // تحديد مسار الصورة
                        $image_path = $item['product_image'];
                        if (strpos($image_path, '../') === 0) {
                            $image_path = substr($image_path, 3);
                        } elseif (!file_exists($image_path)) {
                            $image_path = 'images/default-product.jpg';
                        }
                        
                        echo '<img src="'.$image_path.'" alt="'.$item['product_name'].'">';
                        echo '<div class="order-item-details">';
                        echo '<h3>'.$item['product_name'].'</h3>';
                        echo '<p>الكمية: '.$item['quantity'].' × '.number_format($item['price'], 2).' د.ل</p>';
                        echo '</div>';
                        echo '<span>'.number_format($item_total, 2).' د.ل</span>';
                        echo '</div>';
                    }
                    ?>
                    
                    <div class="order-total">
                        <strong>المجموع الكلي: <?= number_format($total, 2) ?> د.ل</strong>
                    </div>
                </div>
            </div>
            
            <!-- الخطوة 2: معلومات الشحن -->
            <div class="step">
                <h2>2. معلومات الشحن والدفع</h2>
                <form method="post" action="checkout.php">
                    <div class="form-group">
                        <label>الاسم الكامل:</label>
                        <input type="text" name="customer_name" required 
                               value="<?= isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>عنوان التوصيل:</label>
                        <textarea name="address" rows="3" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>رقم الهاتف:</label>
                        <input type="tel" name="phone" required 
                               value="<?= isset($_SESSION['user_phone']) ? $_SESSION['user_phone'] : '' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>طريقة الدفع:</label>
                        <select name="payment_method" required>
                            <option value="cash">نقداً عند الاستلام</option>
                            <option value="bank">تحويل بنكي</option>
                            <option value="card">بطاقة ائتمان</option>
                        </select>
                    </div>
                    
                    <input type="hidden" name="total_amount" value="<?= $total ?>">
                    
                    <button type="submit" name="complete_order" class="checkout-btn">
                        <i class="fas fa-check-circle"></i> تأكيد الطلب
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>


<?php include "footer.php"; ?>
