<?php
ob_start();
session_start();

include("./include/connection.php");
include("file/header6.php");

// تحقق من وجود order_id في الجلسة
if (!isset($_SESSION['order_id'])) {
    header("Location: cart1.php");
    exit();
}

$order_id = $_SESSION['order_id'];
unset($_SESSION['order_id']);

// جلب بيانات الطلب
$order_sql = "SELECT * FROM orders1 WHERE order_id = ?";
$stmt = $conn->prepare($order_sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_result = $stmt->get_result();
$order = $order_result->fetch_assoc();

if (!$order) {
    echo "<div style='color:red;text-align:center;'>عذراً، لم يتم العثور على بيانات الطلب.</div>";
   // include("file/footer.php");
    exit();
}

$customer_name = $order['customer_name'];
$customer_phone = $order['phone'];
$total_amount = $order['total_amount'];
$shipping_address = $order['shipping_address'];
$order_date = date('Y-m-d H:i', strtotime($order['order_date']));
$invoice_name = isset($order['invoice_name']) ? $order['invoice_name'] : ""; // اسم صاحب الفاتورة

// جلب عناصر الطلب
$items_sql = "SELECT product_name, product_price, product_quantity FROM order_details WHERE order_id = ?";
$stmt2 = $conn->prepare($items_sql);
$stmt2->bind_param("i", $order_id);
$stmt2->execute();
$items_result = $stmt2->get_result();

$items_list = "";
$counter = 1;
while ($item = $items_result->fetch_assoc()) {
    $product_name = htmlspecialchars($item['product_name']);
    $product_quantity = (int)$item['product_quantity'];
    $product_price = (float)$item['product_price'];
    $total_line = $product_quantity * $product_price;
    $items_list .= "<li>🛒 <b>{$product_name}</b> × {$product_quantity} = <span style='color:#2196F3;font-weight:bold'>{$total_line}</span> دينار ليبي</li>";
    $counter++;
}

// جلب بيانات المحل
$settings_sql = "SELECT site_name, logo_path, whatsapp FROM site_settings LIMIT 1";
$settings_result = $conn->query($settings_sql);
$site_name = 'متجرك';
$logo_path = '';
$shop_whatsapp = '';
if ($settings_result && $settings_result->num_rows > 0) {
    $settings = $settings_result->fetch_assoc();
    $site_name = $settings['site_name'];
    $logo_path = $settings['logo_path'];
    $shop_whatsapp = $settings['whatsapp'];
}

// تجهيز رسالة الفاتورة للواتساب
$invoice_text = "🎉 مرحباً بك في $site_name! 🎉\n";
$invoice_text .= "🗓️ التاريخ: $order_date\n";
$invoice_text .= "🔖 رقم الطلب: #$order_id\n";
if (!empty($invoice_name)) {
    $invoice_text .= "🧾 اسم صاحب الفاتورة: $invoice_name\n";
}
$invoice_text .= "👤 الاسم: $customer_name\n";
$invoice_text .= "📞 الهاتف: $customer_phone\n";
$invoice_text .= "🏠 العنوان: $shipping_address\n";
$invoice_text .= "----------------------\n";
$invoice_text .= "📦 المنتجات المطلوبة:\n";

// إعادة جلب العناصر للفاتورة (لأن مؤشر result انتهى)
$stmt2->execute();
$items_result = $stmt2->get_result();
$counter = 1;
while ($item = $items_result->fetch_assoc()) {
    $product_name = $item['product_name'];
    $product_quantity = $item['product_quantity'];
    $product_price = $item['product_price'];
    $total_line = $product_quantity * $product_price;
    $invoice_text .= "🛒 {$counter}. {$product_name} × {$product_quantity} = {$total_line} دينار ليبي\n";
    $counter++;
}
$invoice_text .= "----------------------\n";
$invoice_text .= "💰 الإجمالي: $total_amount دينار ليبي\n";
$invoice_text .= "----------------------\n";
$invoice_text .= "شكراً لتسوقك معنا! 🌟";

// ترميز النص لرابط واتساب
$invoice_text_encoded = urlencode($invoice_text);

// توليد روابط الواتساب
$shop_whatsapp_link = $shop_whatsapp ? "https://wa.me/$shop_whatsapp?text=$invoice_text_encoded" : "#";
$customer_whatsapp_link = $customer_phone ? "https://wa.me/$customer_phone?text=$invoice_text_encoded" : "#";
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>طلبك تم بنجاح 🎉</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">   
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@500;700&display=swap" rel="stylesheet">
    <style>
        body {
          background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: 'Cairo', sans-serif;
            margin: 0;
            color: #222;
        }
        .container {
            background: #fff;
            border-radius: 26px;
            box-shadow: 0 8px 32px 0 rgba(31,38,135,0.2);
            max-width: 420px;
            margin: 55px auto 0;
            padding: 40px 30px 28px 30px;
            text-align: center;
            position: relative;
        }
        .circle-logo {
            width: 110px;
            height: 110px;
            object-fit: cover;
            border-radius: 50%;
            border: 5px solid #4CAF50;
            margin: -75px auto 15px auto;
            background: #fff;
            box-shadow: 0 2px 12px rgba(76, 175, 80, 0.13);
        }
        .success-check {
            font-size: 60px;
            color: #4CAF50;
            margin: 16px 0 4px 0;
        }
        h2 {
            color: #2196F3;
            font-weight: 700;
            margin-bottom: 6px;
        }
        .order-id {
            font-size: 1.1em;
            background: #e3fcef;
            color: #388e3c;
            padding: 5px 14px;
            border-radius: 7px;
            display: inline-block;
            margin-bottom: 14px;
            font-weight: bold;
        }
        ul.product-list {
            text-align: right;
            margin: 20px 0 12px 0;
            padding: 0 18px;
            list-style: none;
        }
        ul.product-list li {
            margin-bottom: 8px;
            font-size: 1.04em;
        }
        .details-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            font-size: 1.06em;
        }
        .total {
            font-size: 1.18em;
            color: #388e3c;
            font-weight: bold;
            margin-top: 8px;
        }
        .action-btn {
            display: inline-block;
            margin-top: 16px;
            padding: 11px 24px;
            background: linear-gradient(90deg, #25D366 0%, #128C7E 100%);
            color: #fff;
            border-radius: 25px;
            border: none;
            font-size: 1.09em;
            font-family: inherit;
            font-weight: 700;
            text-decoration: none;
            box-shadow: 0 5px 14px rgba(18,140,126,0.13);
            transition: background 0.18s;
            margin-left: 8px;
        }
        .action-btn:hover {
            background: linear-gradient(90deg, #128C7E 0%, #25D366 100%);
        }
        .return-btn {
            background: linear-gradient(90deg, #2196F3 0%, #0072ff 100%);
            margin-top: 14px;
        }
        @media (max-width: 560px) {
            .container {
                padding: 18px 5px 20px 5px;
                max-width: 98vw;
            }
            .circle-logo {
                width: 78px; height: 78px;
                margin-top: -55px;
            }
        }
        .details-row {
    display: flex;
    justify-content: space-between;
    margin: 10px 0;
    font-size: 1.06em;
    direction: rtl; /* للتأكد من اتجاه النص من اليمين لليسار */
    text-align: right; /* محاذاة النص لليمين */
    font-family: 'Tahoma', 'Arial', sans-serif; /* خطوط تدعم العربية */
}

.details-row span:first-child {
    font-weight: bold;
    color: #555;
}

.details-row span:last-child {
    color: #000;
}
    </style>
</head>
<body>
    <div class="container">
        <?php if($logo_path): ?>
            <img src="<?= htmlspecialchars($logo_path) ?>" alt="شعار المحل" class="circle-logo" />
        <?php endif; ?>
        <div class="success-check">✅</div>
        <h2>تم استلام طلبك بنجاح!</h2>
        <div class="order-id">رقم الطلب: #<?= $order_id ?></div>
        <?php if(!empty($invoice_name)): ?>
        <div class="details-row"><span>🧾 اسم صاحب الفاتورة:</span><span><?= htmlspecialchars($invoice_name) ?></span></div>
        <?php endif; ?>
        <div class="details-row"><span>👤 اسم العميل:</span><span><?= htmlspecialchars($customer_name) ?></span></div>
        <div class="details-row"><span>📞 رقم الهاتف:</span><span><?= htmlspecialchars($customer_phone) ?></span></div>
        <div class="details-row"><span>🏠 العنوان:</span><span><?= htmlspecialchars($shipping_address) ?></span></div>
        <div class="details-row"><span>🗓️ تاريخ الطلب:</span><span><?= htmlspecialchars($order_date) ?></span></div>
        <ul class="product-list">
            <?= $items_list ?>
        </ul>
        <div class="total">الإجمالي: <?= $total_amount ?> دينار ليبي</div>
        <?php if($shop_whatsapp): ?>
        <a href="<?= $shop_whatsapp_link ?>" target="_blank" class="action-btn">إرسال الفاتورة لصاحب المتجر</a>
        <?php endif; ?>
        <?php if($customer_phone): ?>
        <a href="<?= $customer_whatsapp_link ?>" target="_blank" class="action-btn">إرسال الفاتورة لنفسك</a>
        <?php endif; ?>
        <br>
        <a href="index.php" class="action-btn return-btn">العودة للمتجر</a>
    </div>

<br>
<br>




</body>


</html>
