<?php
session_start();

// التحقق من تسجيل دخول المدير
if (!isset($_SESSION['EMAIL'])) {
    header("Location: admin.php");
    exit();
}

// الاتصال بقاعدة البيانات
require_once("../include/connection.php");

// جلب إعدادات الموقع
$settings_query = "SELECT * FROM site_settings LIMIT 1";
$settings_result = mysqli_query($conn, $settings_query);
$site_settings = mysqli_fetch_assoc($settings_result);

// تعرّف حالات الطلب
$status_labels = [
    'pending' => 'قيد المعالجة',
    'processing' => 'جاري التجهيز',
    'completed' => 'مكتمل',
    'cancelled' => 'ملغي',
    'shipped' => 'تم الشحن'
];

// التحقق من معرّف الطلب
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($order_id <= 0) {
    die("معرّف الطلب غير صالح");
}

// جلب بيانات الطلب
$order_query = "SELECT * FROM orders1 WHERE order_id = ?";
$stmt = mysqli_prepare($conn, $order_query);
mysqli_stmt_bind_param($stmt, 'i', $order_id);
mysqli_stmt_execute($stmt);
$order = mysqli_stmt_get_result($stmt)->fetch_assoc();

if (!$order) {
    die("الطلب غير موجود");
}

// جلب عناصر الطلب
$items_query = "SELECT oi.*, p.name, p.image 
               FROM orders1_items oi
               LEFT JOIN products p ON oi.product_id = p.id
               WHERE oi.order_id = ?";
$stmt_items = mysqli_prepare($conn, $items_query);
mysqli_stmt_bind_param($stmt_items, 'i', $order_id);
mysqli_stmt_execute($stmt_items);
$items = mysqli_stmt_get_result($stmt_items);

// معالجة إرسال الفاتورة
if (isset($_POST['send_invoice'])) {
    $customer_phone = $order['phone'];
    $message = urlencode(
        "فاتورة الطلبية #$order_id\n" .
        "المجموع: " . number_format($order['total_amount'], 2) . " د.ل\n\n" .
        "شكراً لثقتك! الرجاء تأكيد الاستلام بالرد على هذه الرسالة."
    );
    header("Location: https://wa.me/$customer_phone?text=$message");
    exit();
}
?>

<!DOCTYPE html>
<html dir="rtl">
<head>
    <title>تفاصيل الطلبية #<?= $order_id ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Tahoma', Arial, sans-serif;
            background-color: #f5f5f5;
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
        .order-table {
            width: 100%;
            border-collapse: collapse;
            margin: 25px 0;
        }
        .order-table th {
            background-color: #3498db;
            color: white;
            padding: 12px 15px;
            text-align: right;
        }
        .order-table td {
            padding: 12px 15px;
            border: 1px solid #e0e0e0;
        }
        .order-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .product-image {
            width: 70px;
            height: 70px;
            object-fit: contain;
            border-radius: 5px;
            border: 1px solid #eee;
        }
        .contact-btn {
            padding: 12px 20px;
            border-radius: 8px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: 0.3s;
        }
        .whatsapp-btn {
            background: #25D366;
            color: white;
        }
        .contact-btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
<div class="order-container">
    <h1>تفاصيل الطلب #<?= $order_id ?></h1>
    <h3>معلومات العميل</h3>
    <p><strong>الهاتف:</strong> <?= $order['phone'] ?></p>
    <p><strong>العنوان:</strong> <?= htmlspecialchars($order['shipping_address']) ?></p>

    <h3>المنتجات</h3>
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
            $counter = 1;
            $total = 0;
            while ($item = mysqli_fetch_assoc($items)):
                $item_total = $item['price'] * $item['quantity'];
                $total += $item_total;
            ?>
            <tr>
                <td><?= $counter++ ?></td>
                <td>
                    <img src="../uploads/img/<?= $item['image'] ?? 'default.jpg' ?>" 
                         class="product-image"
                         onerror="this.src='../images/default-product.jpg'">
                </td>
                <td><?= htmlspecialchars($item['name'] ?? 'منتج محذوف') ?></td>
                <td><?= number_format($item['price'], 2) ?> د.ل</td>
                <td><?= $item['quantity'] ?></td>
                <td><?= number_format($item_total, 2) ?> د.ل</td>
            </tr>
            <?php endwhile; ?>
            <tr>
                <td colspan="5" style="text-align: left; font-weight: bold;">المجموع الكلي</td>
                <td><?= number_format($total, 2) ?> د.ل</td>
            </tr>
        </tbody>
    </table>

    <form method="post">
        <button type="submit" name="send_invoice" class="contact-btn whatsapp-btn">
            <i class="fab fa-whatsapp"></i> إرسال الفاتورة عبر واتساب
        </button>
    </form>
</div>
</body>
</html>