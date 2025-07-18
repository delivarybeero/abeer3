<?php
session_start();

// التحقق من صحة الجلسة
if (!isset($_SESSION['EMAIL'])) {
    header("Location: admin.php");
    exit();
}

require_once("../include/connection.php");

// جلب إعدادات الموقع
$settings = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT site_name, logo_path, phone_number, whatsapp 
    FROM site_settings LIMIT 1"
));

// جلب بيانات الطلب
$order_id = (int)($_GET['id'] ?? 0);
$order_result = mysqli_query($conn, 
    "SELECT * FROM orders1 WHERE order_id = $order_id"
);

if (!$order_result || mysqli_num_rows($order_result) === 0) {
    die("الطلب غير موجود");
}

$order = mysqli_fetch_assoc($order_result);

// جلب عناصر الطلب
$items_result = mysqli_query($conn, 
    "SELECT oi.*, p.name, p.image 
    FROM orders1_items oi
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = $order_id"
);

if (!$items_result) {
    die("خطأ في جلب بيانات المنتجات: " . mysqli_error($conn));
}

$items = mysqli_fetch_all($items_result, MYSQLI_ASSOC);

// معالجة إرسال الواتساب
if (isset($_POST['send_invoice'])) {
    $site_name = $settings['site_name'];
    $products = "";
    
    foreach ($items as $item) {
        $products .= "â–ªï¸� " . $item['name'] . " - " 
        . $item['quantity'] . "x" 
        . number_format($item['price'], 2) . " Ø¯.Ù„\n";
    }

    $libya_time = new DateTime('now', new DateTimeZone('Africa/Tripoli'));
    $current_time = $libya_time->format('Y-m-d H:i');

    $customer_phone = preg_replace('/^0+/', '', $order['phone']);
    $whatsapp_number = "218" . $customer_phone;
    $message = "🛍️ *فاتورة شراء من {$site_name}*\n"
    . "📅 التاريخ: " . $current_time . "\n"
    . "📋 رقم الفاتورة: #$order_id\n\n"
    . "📦 المنتجات:\n$products\n"
    . "💰 الإجمالي: " . number_format($order['total_amount'], 2) . " د.ل\n\n"
    . "شكرًا لثقتك! ❤️\n"
    . "سيصلك طلبك خلال ٢-٣ ساعات ⏳";

    
    $encoded_message = urlencode($message);
    header("Location: https://wa.me/$whatsapp_number?text=$encoded_message");
    exit();
}

// دالة مساعدة لتنسيق رقم الواتساب
function format_whatsapp_number($phone) {
    $phone = preg_replace('/^0+/', '', $phone);
    return '218' . $phone;
}
?>

<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>فاتورة الطلب</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
    <style>
body { 
    font-family: 'Tahoma', sans-serif; 
    background: #f5f5f5; 
    margin: 0; 
    padding: 20px; 
}

.order-container { 
    max-width: 1000px; 
    margin: 20px auto; 
    background: white; 
    padding: 30px; 
    border-radius: 10px; 
    box-shadow: 0 0 20px rgba(0,0,0,0.1); 
}

.order-status { 
    padding: 8px 15px; 
    border-radius: 20px; 
    font-weight: bold; 
}
.status-pending { background: #fff3cd; color: #856404; }
.status-processing { background: #cce5ff; color: #004085; }
.status-completed { background: #d4edda; color: #155724; }
.status-cancelled { background: #f8d7da; color: #721c24; }
.status-shipped { background: #e2e3e5; color: #383d41; }

.order-table { 
    width: 100%; 
    border-collapse: separate; 
    border-spacing: 0; 
    margin: 25px 0; 
    border: 2px solid #3498db; 
    border-radius: 10px; 
    overflow: hidden; 
    box-shadow: 0 0 20px rgba(0,0,0,0.1); 
}
.order-table th, .order-table td { 
    padding: 15px; 
    text-align: center; 
    border: 1px solid #dcdcdc; 
}
.order-table th { 
    background-color: #3498db; 
    color: white; 
    border-bottom: 3px solid #2c3e50; 
}
.order-table tr:nth-child(even) { background-color: #f9f9f9; }
.order-table tr:hover { background-color: #f1f1f1; }
.total-row { 
    background-color: #e8f4ff !important; 
    font-weight: bold; 
    border-top: 2px solid #3498db; 
}
.product-image { 
    width: 70px; 
    height: 70px; 
    object-fit: contain; 
    border-radius: 10px; 
    border: 1px solid #eee; 
    background: #fafafa; 
}

@media (max-width: 768px) {
    .order-container { padding: 8px; }
    .product-image { width: 50px; height: 50px; }
    .order-table td { padding: 10px; font-size: 14px; }
    .order-table th { padding: 12px; font-size: 15px; }
}

.order-header { 
    flex-direction: column; 
    align-items: flex-start; 
    gap: 10px; 
}
.customer-info { 
    background: #f8f9fa; 
    padding: 20px; 
    border-radius: 8px; 
    margin-bottom: 30px; 
}
.customer-info h3 { 
    margin-top: 0; 
    color: #2c3e50; 
    border-bottom: 1px dashed #ccc; 
    padding-bottom: 10px; 
}
.info-grid { 
    display: grid; 
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); 
    gap: 15px; 
}
.info-item { margin-bottom: 10px; }
.info-label { 
    font-weight: bold; 
    color: #3498db; 
    display: inline-block; 
    width: 120px; 
}

.action-buttons { 
    display: flex; 
    justify-content: space-between; 
    align-items: center; 
    margin: 30px 0; 
    gap: 20px; 
}
.button-group { 
    display: flex; 
    gap: 15px; 
    align-items: center; 
}
.btn, .btn-back, .btn-print, .btn-edit {
    max-width: 300px;
    min-width: 120px;
    padding: 10px 20px; 
    font-size: 14px; 
    border-radius: 6px; 
    text-decoration: none; 
    display: inline-flex; 
    align-items: center; 
    gap: 10px; 
    transition: all 0.3s ease; 
    font-weight: 600; 
    border: 2px solid transparent; 
}
.btn-back { 
    background: #6c757d; 
    color: white; 
    box-shadow: 0 3px 6px rgba(108, 117, 125, 0.2); 
}
.btn-print { 
    background: #17a2b8; 
    color: white; 
    box-shadow: 0 3px 6px rgba(23, 162, 184, 0.2); 
}
.btn-edit { 
    background: #ffc107; 
    color: #212529; 
    box-shadow: 0 3px 6px rgba(255, 193, 7, 0.2); 
}
.btn:hover { 
    transform: translateY(-2px); 
    box-shadow: 0 5px 15px rgba(0,0,0,0.2); 
    opacity: 0.9; 
}

@media (max-width: 768px) {
    .action-buttons {
        flex-direction: column;
        gap: 15px;
        align-items: stretch;
    }
    .button-group { 
        flex-direction: column; 
        width: 100%;
        gap: 10px;
    }
    .btn, .btn-back, .btn-print, .btn-edit {
        width: 100%;
        justify-content: center;
        padding: 15px 20px;
        font-size: 16px;
        max-width: 100%;
        min-width: 0;
        margin-bottom: 0;
    }
    .btn-back { 
        width: 100%;
        margin-bottom: 10px;
    }
}

.fas, .fab { font-size: 16px; }
.logo { 
    width: 50px; 
    height: 50px; 
    border-radius: 50%; 
    object-fit: cover; 
    border: 2px solid #3498db; 
}
@media print {
    .no-print, .action-buttons, .contact-info { display: none !important; }
    .order-container { box-shadow: none; border: 1px solid #ddd; }
}
.contact-info { 
    border: 1px solid #eee; 
    padding: 15px; 
    border-radius: 8px; 
    margin: 20px 0; 
}
.contact-item { 
    display: flex; 
    align-items: center; 
    gap: 15px; 
    margin-bottom: 15px; 
    padding: 10px; 
    background: #f8f9fa; 
    border-radius: 5px; 
}
.whatsapp-btn { 
    background: #25D366; 
    color: white; 
    padding: 12px 20px; 
    border-radius: 8px; 
    text-decoration: none; 
    display: inline-flex; 
    align-items: center; 
    gap: 10px; 
}
@media (max-width: 600px) {
  body {
    font-size: 18px;
  }
  h1, h2, h3 {
    font-size: 22px;
  }
}  

@media (max-width: 768px) {
    .action-buttons {
        flex-direction: column;
        gap: 15px;
        align-items: stretch;
    }
    .btn-back,
    .btn-print,
    .btn-edit,
    .btn {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        box-sizing: border-box;
        display: block !important;
    }
    .button-group {
        width: 100%;
        flex-direction: column;
        gap: 10px;
    }
}




</style>
</head>
<body>
    <div class="order-container">
        <div class="invoice-header" style="text-align:center;">
            <?php if (!empty($settings['logo_path'])): ?>
                <img src="../<?= htmlspecialchars($settings['logo_path']) ?>" class="logo" alt="شعار المتجر">
            <?php endif; ?>
            <h1><?= htmlspecialchars($settings['site_name']) ?></h1>
            <div class="invoice-info">
                <div>رقم الفاتورة: <?= $order_id ?></div>
                <div>تاريخ الإصدار: <?= date('Y-m-d H:i') ?></div>
            </div>
        </div>
        <div class="customer-info">
            <h3>معلومات العميل:</h3>
            <p>الاسم: <?= htmlspecialchars($order['customer_name']) ?></p>
            <p>الهاتف: <?= htmlspecialchars($order['phone']) ?></p>
            <p>العنوان: <?= htmlspecialchars($order['shipping_address']) ?></p>
        </div>

        <h3><i class="fas fa-boxes"></i> المنتجات المطلوبة</h3>
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
                <?php $total = 0; ?>
                <?php foreach ($items as $index => $item): ?>
                    <?php $item_total = $item['price'] * $item['quantity']; ?>
                    <?php $total += $item_total; ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td>
                            <img src="../<?= htmlspecialchars($item['image'] ?? 'images/default-product.jpg') ?>"
                                 class="product-image"
                                 alt="<?= htmlspecialchars($item['name'] ?? 'منتج') ?>"
                                 onerror="this.src='../images/default-product.jpg'">
                        </td>
                        <td><?= htmlspecialchars($item['name'] ?? 'منتج محذوف') ?></td>
                        <td><?= number_format($item['price'], 2) ?> د.ل</td>
                        <td><?= $item['quantity'] ?></td>
                        <td><?= number_format($item_total, 2) ?> د.ل</td>
                    </tr>
                <?php endforeach; ?>
                <tr class="total-row">
                    <td colspan="5">المجموع الكلي</td>
                    <td><?= number_format($total, 2) ?> د.ل</td>
                </tr>
            </tbody>
        </table>

        <div class="contact-info no-print">
            <form method="post">
                <button type="submit" name="send_invoice" class="whatsapp-btn">
                    <i class="fab fa-whatsapp"></i> إرسال الفاتورة عبر واتساب
                </button>
            </form>
        </div>

        <?php if(!empty($order['phone'])): ?>
        <div class="contact-info no-print">
            <div class="contact-item">
                <i class="fas fa-phone"></i>
                <span>اتصال هاتفي: <?= $order['phone'] ?></span>
                <a href="tel:<?= $order['phone'] ?>" class="btn">اتصال</a>
            </div>
            <div class="contact-item">
                <i class="fab fa-whatsapp"></i>
                <span>مراسلة عبر واتساب:</span>
                <a href="https://wa.me/<?= format_whatsapp_number($order['phone']) ?>" 
                    class="whatsapp-btn"
                    target="_blank">
                    <i class="fab fa-whatsapp"></i> إرسال رسالة
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="action-buttons">
        <a href="admin_orders1.php" class="btn btn-back">
            <i class="fas fa-arrow-right"></i> العودة للقائمة
        </a>
        <div class="button-group">
            <button onclick="window.print()" class="btn btn-print">
                <i class="fas fa-print"></i> طباعة الفاتورة
            </button>
            <a href="admin_edit_order.php?id=<?= $order_id ?>" class="btn btn-edit">
                <i class="fas fa-edit"></i> تعديل الطلب
            </a>
        </div>
    </div>
</body>
</html>
