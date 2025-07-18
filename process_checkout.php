<?php
// تمكين عرض الأخطاء
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// تسجيل بدء التنفيذ
error_log("بدء عملية الدفع - جلسة: " . session_id());

include("./include/connection.php");

// التحقق من اتصال قاعدة البيانات
if ($conn->connect_error) {
    $_SESSION['error'] = "لا يمكن الاتصال بقاعدة البيانات";
    error_log("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
    header("Location: checkout.php");
    exit();
}

// التحقق من طريقة الطلب
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "طريقة الطلب غير صحيحة";
    error_log("طريقة طلب غير صحيحة: " . $_SERVER['REQUEST_METHOD']);
    header("Location: checkout.php");
    exit();
}

// جمع بيانات الطلب مع التحقق
$customer_name = isset($_POST['customer_name']) ? trim($_POST['customer_name']) : '';
$address = isset($_POST['address']) ? trim($_POST['address']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$total_amount = isset($_POST['total_amount']) ? floatval($_POST['total_amount']) : 0;
$invoice_name = isset($_POST['invoice_name']) ? trim($_POST['invoice_name']) : '';
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$session_id = session_id();

// تسجيل البيانات المستلمة
error_log("بيانات الطلب المستلمة: " . json_encode($_POST));

// التحقق من البيانات الأساسية
if (empty($customer_name) || empty($address) || empty($phone)) {
    $_SESSION['error'] = "الرجاء تعبئة جميع الحقول المطلوبة";
    error_log("بيانات ناقصة: customer_name=$customer_name, address=$address, phone=$phone");
    header("Location: checkout.php");
    exit();
}

// بدء المعاملة
$conn->begin_transaction();

try {
    // 1. إضافة الطلب إلى جدول orders1
    $order_query = "INSERT INTO orders1 (
        customer_name, 
        phone, 
        shipping_address, 
        total_amount, 
        status, 
        session_id, 
        invoice_name,
        user_id
    ) VALUES (?, ?, ?, ?, 'جديد', ?, ?, ?)";
    
    error_log("استعداد لتنفيذ استعلام: " . $order_query);
    
    $stmt = $conn->prepare($order_query);
    if (!$stmt) {
        throw new Exception("خطأ في إعداد استعلام الطلب: " . $conn->error);
    }
    
    $stmt->bind_param("sssdssi", 
        $customer_name, 
        $phone, 
        $address, 
        $total_amount, 
        $session_id, 
        $invoice_name, 
        $user_id
    );
    
    if (!$stmt->execute()) {
        throw new Exception("خطأ في إضافة الطلب: " . $stmt->error);
    }
    
    $order_id = $conn->insert_id;
    $stmt->close();
    
    error_log("تم إنشاء الطلب بنجاح - رقم الطلب: $order_id");

    // 2. جلب محتويات السلة
    $cart_query = "SELECT c.*, p.quantity as stock 
                  FROM cart1 c 
                  JOIN products p ON c.product_id = p.id 
                  WHERE c.session_id = ?";
    
    $stmt = $conn->prepare($cart_query);
    $stmt->bind_param("s", $session_id);
    $stmt->execute();
    $cart_items = $stmt->get_result();
    
    if ($cart_items->num_rows == 0) {
        throw new Exception("سلة التسوق فارغة");
    }

    // 3. معالجة كل عنصر في السلة
    while ($item = $cart_items->fetch_assoc()) {
        // إضافة إلى جدول order_details
        $detail_query = "INSERT INTO order_details (
            order_id, 
            product_id, 
            product_name, 
            product_price, 
            product_quantity
        ) VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($detail_query);
        $stmt->bind_param("iisdi", 
            $order_id, 
            $item['product_id'], 
            $item['name'], 
            $item['price'], 
            $item['quantity']
        );
        $stmt->execute();
        $stmt->close();

        // إضافة إلى جدول orders1_items
        $item_query = "INSERT INTO orders1_items (
            order_id, 
            customer_name, 
            product_id, 
            quantity, 
            price
        ) VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($item_query);
        $stmt->bind_param("isiid", 
            $order_id, 
            $customer_name, 
            $item['product_id'], 
            $item['quantity'], 
            $item['price']
        );
        $stmt->execute();
        $stmt->close();

        // تحديث المخزون
        $new_stock = $item['stock'] - $item['quantity'];
        $update_query = "UPDATE products SET quantity = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ii", $new_stock, $item['product_id']);
        $stmt->execute();
        $stmt->close();
    }

    // 4. تفريغ سلة التسوق
    $delete_query = "DELETE FROM cart1 WHERE session_id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("s", $session_id);
    $stmt->execute();
    $stmt->close();

    // 5. تحديث جدول session_users
    $session_query = "INSERT IGNORE INTO session_users (session_id) VALUES (?)";
    $stmt = $conn->prepare($session_query);
    $stmt->bind_param("s", $session_id);
    $stmt->execute();
    $stmt->close();

    // تأكيد المعاملة
    $conn->commit();
    
    error_log("تمت عملية الدفع بنجاح للطلب رقم: $order_id");

    // توجيه إلى صفحة النجاح
    $_SESSION['order_id'] = $order_id;

// أضف هذه الأسطر قبل أي إخراج HTML
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
header("Location: order_success.php", true, 303);
exit();


} catch (Exception $e) {
    // التراجع في حالة الخطأ
    $conn->rollback();
    
    // تسجيل الخطأ وإعادة التوجيه
    $error_message = "حدث خطأ: " . $e->getMessage();
    $_SESSION['error'] = $error_message;
    error_log("Checkout Error: " . $error_message);
    header("Location: checkout.php");
    exit();
}
?>