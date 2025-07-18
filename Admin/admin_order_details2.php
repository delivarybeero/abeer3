<?php
ini_set('default_charset', 'UTF-8');
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
// تفعيل عرض الأخطاء للتصحيح
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/html; charset=utf-8');
session_start();

// التحقق من صلاحيات الدخول
if (!isset($_SESSION['EMAIL'])) {
    header("Location: admin.php");
    exit();
}

require_once("../include/connection.php");

// استعلام لضبط الترميز

mysqli_set_charset($conn, "utf8mb4");
// التحقق من وجود معرف الطلب
$order_id = (int)($_GET['id'] ?? 0);
if ($order_id <= 0) {
    die("معرف الطلب غير صالح");
}

// استعلام بيانات الطلب مع التحقق من الأخطاء
$order_query = "SELECT * FROM orders1 WHERE order_id = ?";
$stmt = mysqli_prepare($conn, $order_query);
if (!$stmt) {
    die("خطأ في إعداد استعلام الطلب: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $order_id);
if (!mysqli_stmt_execute($stmt)) {
    die("خطأ في تنفيذ استعلام الطلب: " . mysqli_error($conn));
}

$order_result = mysqli_stmt_get_result($stmt);
if (!$order_result || mysqli_num_rows($order_result) === 0) {
    die("الطلب غير موجود");
}

$order = mysqli_fetch_assoc($order_result);

// استعلام عناصر الطلب مع بيانات المنتج الاحتياطية
$items_query = "SELECT 
                oi.order_item_id,
                oi.product_id,
                oi.quantity,
                oi.price,
                oi.product_image,
                oi.product_name,
                IFNULL(oi.product_name, p.name) AS final_product_name,
                IFNULL(oi.product_image, p.image) AS final_product_image
                FROM orders1_items oi
                LEFT JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = ?";

$stmt = mysqli_prepare($conn, $items_query);
if (!$stmt) {
    die("خطأ في إعداد استعلام العناصر: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $order_id);
if (!mysqli_stmt_execute($stmt)) {
    die("خطأ في تنفيذ استعلام العناصر: " . mysqli_error($conn));
}

$items_result = mysqli_stmt_get_result($stmt);
if (!$items_result) {
    die("خطأ في جلب بيانات المنتجات: " . mysqli_error($conn));
}

$items = mysqli_fetch_all($items_result, MYSQLI_ASSOC);

// استعلام إعدادات الموقع
$settings_query = "SELECT * FROM site_settings LIMIT 1";
$settings_result = mysqli_query($conn, $settings_query);
$settings = mysqli_fetch_assoc($settings_result) ?? [];

// معالجة إرسال الفاتورة عبر واتساب
if (isset($_POST['send_invoice'])) {
    $site_name = $settings['site_name'] ?? 'متجر إلكتروني';
    $current_time = date('Y-m-d H:i');
    
    $products = "";
    foreach ($items as $item) {
        $products .= "• " . ($item['product_name'] ?? $item['final_product_name']) . " - " 
                    . $item['quantity'] . "x" 
                    . number_format($item['price'], 2) . " د.ل\n";
    }

    $customer_phone = preg_replace('/^0+/', '', $order['phone']);
    $whatsapp_number = "218" . $customer_phone;
    
    $message = "🛒 *فاتورة شراء من {$site_name}*\n"
             . "📅 التاريخ: " . $current_time . "\n"
             . "📌 رقم الفاتورة: #$order_id\n\n"
             . "📦 المنتجات:\n$products\n"
             . "💰 الإجمالي: " . number_format($order['total_amount'], 2) . " د.ل\n\n"
             . "شكرًا لثقتك! ❤️\n"
             . "سيصل طلبك خلال ٢-٣ ساعات ⏳";

    $encoded_message = urlencode($message);
    header("Location: https://wa.me/$whatsapp_number?text=$encoded_message");
    exit();
}
?>
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فاتورة الطلب #<?= htmlspecialchars($order_id) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
    <style>
        body { font-family: 'Tahoma', sans-serif; background: #f5f5f5; margin: 0; padding: 20px; }
        .order-container { max-width: 1000px; margin: 20px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        .order-status { padding: 8px 15px; border-radius: 20px; font-weight: bold; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-new { background: #d4edda; color: #155724; }
        .order-table { width: 100%; border-collapse: collapse; margin: 25px 0; }
        .order-table th, .order-table td { padding: 12px; text-align: center; border: 1px solid #ddd; }
        .order-table th { background-color: #3498db; color: white; }
        .total-row { background-color: #f8f9fa; font-weight: bold; }
        .product-image { width: 60px; height: 60px; object-fit: contain; border-radius: 5px; }
        .customer-info { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .action-buttons { display: flex; justify-content: space-between; margin-top: 30px; }
        .btn { padding: 10px 20px; border-radius: 5px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }
        .btn-back { background: #6c757d; color: white; }
        .btn-print { background: #17a2b8; color: white; }
        .btn-whatsapp { background: #25D366; color: white; }
        @media print {
            .no-print { display: none; }
            body { padding: 0; background: white; }
        }
        @media (max-width: 768px) {
            .order-container { padding: 15px; }
            .product-image { width: 40px; height: 40px; }
            .order-table th, .order-table td { padding: 8px; font-size: 14px; }
        }
    </style>
</head>
<body>
    <div class="order-container">
        <div style="text-align:center; margin-bottom:30px;">
            <?php if (!empty($settings['logo_path'])): ?>
                <img src="../<?= htmlspecialchars($settings['logo_path']) ?>" style="height:80px; margin-bottom:15px;">
            <?php endif; ?>
            <h1 style="margin:0;"><?= htmlspecialchars($settings['site_name'] ?? 'متجر إلكتروني') ?></h1>
            <h2 style="color:#3498db; margin:10px 0;">فاتورة الطلب #<?= htmlspecialchars($order_id) ?></h2>
            <div>تاريخ الفاتورة: <?= date('Y-m-d H:i') ?></div>
        </div>

        <div class="customer-info">
            <h3 style="margin-top:0; border-bottom:1px solid #ddd; padding-bottom:10px;">معلومات العميل</h3>
            <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(250px, 1fr)); gap:15px;">
                <div><strong>اسم العميل:</strong> <?= htmlspecialchars($order['customer_name'] ?? 'غير محدد') ?></div>
                <div><strong>رقم الهاتف:</strong> <?= htmlspecialchars($order['phone'] ?? 'غير محدد') ?></div>
                <div><strong>العنوان:</strong> <?= htmlspecialchars($order['shipping_address'] ?? 'غير محدد') ?></div>
                <div><strong>حالة الطلب:</strong> 
                    <span class="order-status <?= strtolower($order['status']) === 'مكتمل' ? 'status-completed' : 'status-pending' ?>">
                        <?= htmlspecialchars($order['status']) ?>
                    </span>
                </div>
                <?php if (!empty($order['invoice_name'])): ?>
                    <div><strong>اسم الفاتورة:</strong> <?= htmlspecialchars($order['invoice_name']) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <h3 style="margin-top:30px;"><i class="fas fa-boxes"></i> المنتجات المطلوبة</h3>

        <table class="order-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الصورة</th>
                    <th>المنتج</th>
                    <th>السعر</th>
                    <th>الكمية</th>
                    <th>المجموع</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $total = 0;
                foreach ($items as $index => $item): 
                    $item_total = $item['price'] * $item['quantity'];
                    $total += $item_total;
                    
                    // تحديد مسار الصورة
                    $image_path = !empty($item['product_image']) ? $item['product_image'] : $item['final_product_image'];
                    $final_image_path = "../" . ltrim($image_path, '/');
                    
                    if (!file_exists($final_image_path)) {
                        $final_image_path = "../images/default-product.jpg";
                    }
                ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td>
                        <img src="<?= htmlspecialchars($final_image_path) ?>" 
                             class="product-image"
                             alt="<?= htmlspecialchars($item['product_name'] ?? $item['final_product_name']) ?>"
                             onerror="this.src='../images/default-product.jpg'">
                    </td>
                    <td><?= htmlspecialchars($item['product_name'] ?? $item['final_product_name']) ?></td>
                    <td><?= number_format($item['price'], 2) ?> د.ل</td>
                    <td><?= $item['quantity'] ?></td>
                    <td><?= number_format($item_total, 2) ?> د.ل</td>
                </tr>
                <?php endforeach; ?>
                <tr class="total-row">
                    <td colspan="5" style="text-align:left;"><strong>الإجمالي النهائي</strong></td>
                    <td><strong><?= number_format($order['total_amount'], 2) ?> د.ل</strong></td>
                </tr>
            </tbody>
        </table>

        <div class="action-buttons no-print">
            <a href="admin_orders1.php" class="btn btn-back">
                <i class="fas fa-arrow-right"></i> العودة للقائمة
            </a>
            <div style="display:flex; gap:15px;">
                <button onclick="window.print()" class="btn btn-print">
                    <i class="fas fa-print"></i> طباعة الفاتورة
                </button>
                <form method="post" style="margin:0;">
                    <button type="submit" name="send_invoice" class="btn btn-whatsapp">
                        <i class="fab fa-whatsapp"></i> إرسال عبر واتساب
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>