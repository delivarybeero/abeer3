<?php
session_start();

// 1. التحقق من تسجيل دخول المدير
if (!isset($_SESSION['EMAIL'])) {
    header("Location: admin.php");
    exit();
}

// 2. الاتصال بقاعدة البيانات
require_once("../include/connection.php");

// 3. تعريب حالات الطلب
$status_labels = [
    'pending' => 'قيد المعالجة',
    'processing' => 'جاري التجهيز',
    'completed' => 'مكتمل',
    'cancelled' => 'ملغى',
    'shipped' => 'تم الشحن'
];

// 4. التحقق من معرف الطلب
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($order_id <= 0) {
    die("معرف الطلب غير صالح");
}

// 5. جلب بيانات الطلب
$order_query = "SELECT * FROM orders1 WHERE order_id = ?";
$stmt = mysqli_prepare($conn, $order_query);
mysqli_stmt_bind_param($stmt, 'i', $order_id);
mysqli_stmt_execute($stmt);
$order = mysqli_stmt_get_result($stmt)->fetch_assoc();

if (!$order) {
    die("الطلب غير موجود");
}

// 6. جلب عناصر الطلب
$items_query = "SELECT oi.*, p.name, p.image 
               FROM orders1_items oi
               LEFT JOIN products p ON oi.product_id = p.id
               WHERE oi.order_id = ?";
$stmt_items = mysqli_prepare($conn, $items_query);
mysqli_stmt_bind_param($stmt_items, 'i', $order_id);
mysqli_stmt_execute($stmt_items);
$items = mysqli_stmt_get_result($stmt_items);
?>

<!DOCTYPE html>
<html dir="rtl">
<head>
    <title>تفاصيل الطلب #<?= $order_id ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* التنسيقات العامة */
        body {
            font-family: 'Tahoma', Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        
        /* تصميم بطاقة الطلب */
        .order-container {
            max-width: 1000px;
            margin: 20px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        /* رأس الصفحة */
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #3498db;
            padding-bottom: 15px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }
        
        /* حالة الطلب */
        .order-status {
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-processing { background: #cce5ff; color: #004085; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .status-shipped { background: #e2e3e5; color: #383d41; }
        
        /* معلومات العميل */
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
        .info-item {
            margin-bottom: 10px;
        }
        .info-label {
            font-weight: bold;
            color: #3498db;
            display: inline-block;
            width: 120px;
        }
        
        /* جدول المنتجات */
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
        
        /* المجموع الكلي */
        .total-row {
            font-weight: bold;
            background-color: #f2f2f2 !important;
            font-size: 16px;
        }
        
        /* الأزرار */
        .action-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .btn {
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            font-weight: bold;
        }
        .btn-back {
            background: #6c757d;
            color: white;
        }
        .btn-print {
            background: #17a2b8;
            color: white;
        }
        .btn-edit {
            background: #ffc107;
            color: #212529;
        }
        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        
        /* للطباعة */
        @media print {
            body { background: none; padding: 0; }
            .order-container { box-shadow: none; }
            .action-buttons { display: none; }
        }


        <style>
/* التعديلات العامة للشاشات الصغيرة */
@media (max-width: 768px) {
    /* تعديل حجم الخطوط */
    body {
        font-size: 14px;
        padding: 10px;
    }
    
    /* تعديل حاوية الطلب */
    .order-container {
        padding: 15px;
        margin: 10px auto;
    }
    
    /* تعديل رأس الصفحة */
    .order-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    /* تعديل جدول المعلومات */
    .info-grid {
        grid-template-columns: 1fr;
    }
    
    /* تعديل جدول المنتجات */
    .order-table {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
    }
    
    .order-table th, 
    .order-table td {
        padding: 8px 10px;
    }
    
    /* تعديل حجم الصور */
    .product-image {
        width: 50px;
        height: 50px;
    }
    
    /* تعديل الأزرار */
    .action-buttons {
        flex-direction: column;
        gap: 10px;
    }
    
    .btn {
        width: 100%;
        justify-content: center;
        padding: 8px 15px;
    }
    
    /* إخفاء بعض العناصر في الجوال */
    .order-table th:nth-child(2),
    .order-table td:nth-child(2) {
        display: none;
    }
}

/* تعديلات إضافية للشاشات الصغيرة جداً */
@media (max-width: 480px) {
    /* تصغير حجم الخط أكثر */
    body {
        font-size: 13px;
    }
    
    /* تعديل حجم الصور */
    .product-image {
        width: 40px;
        height: 40px;
    }
    
    /* تعديل الجدول ليكون أكثر ملائمة */
    .order-table th, 
    .order-table td {
        padding: 6px 8px;
    }
    
    /* إخفاء المزيد من الأعمدة غير الضرورية */
    .order-table th:nth-child(5),
    .order-table td:nth-child(5) {
        display: none;
    }
    
    /* تعديل تخطيط معلومات العميل */
    .info-item {
        display: flex;
        flex-direction: column;
    }
    
    .info-label {
        width: 100%;
        margin-bottom: 3px;
    }
}
</style>




    </style>
</head>
<body>
    <div class="order-container">
        <!-- رأس الصفحة -->
        <div class="order-header">
            <h1>تفاصيل الطلب #<?= $order_id ?></h1>
            <div class="order-status status-<?= $order['status'] ?>">
                <?= $status_labels[strtolower($order['status'])] ?? $order['status'] ?>
            </div>
        </div>
        
        <!-- معلومات العميل -->
        <div class="customer-info">
            <h3><i class="fas fa-user"></i> معلومات العميل</h3>
            <div class="info-grid">







                <div class="info-item">
                    <span class="info-label"><i class="fas fa-user-tag"></i> العنوان:</span>
                    <?= htmlspecialchars($order['shipping_address']) ?>
                </div>
                <div class="info-item">
                    <span class="info-label"><i class="fas fa-phone"></i> الهاتف:</span>
                    <?= $order['phone'] ?>
                </div>
                <div class="info-item">
                    <span class="info-label"><i class="fas fa-calendar"></i> التاريخ:</span>
                    <?= date('Y-m-d H:i', strtotime($order['order_date'])) ?>
                </div>
                <div class="info-item">
                    <span class="info-label"><i class="fas fa-money-bill-wave"></i> المبلغ:</span>
                    <?= number_format($order['total_amount'], 2) ?> د.ل
                </div>
                <?php if (!empty($order['payment_method'])): ?>
                <div class="info-item">
                    <span class="info-label"><i class="fas fa-credit-card"></i> الدفع:</span>
                    <?= $order['payment_method'] == 'cash' ? 'نقدي عند الاستلام' : 'بطاقة ائتمان' ?>
                </div>
                <?php endif; ?>
                <?php if (!empty($order['notes'])): ?>
                <div class="info-item">
                    <span class="info-label"><i class="fas fa-sticky-note"></i> ملاحظات:</span>
                    <?= htmlspecialchars($order['notes']) ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- قائمة المنتجات -->
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
                <?php
                $counter = 1;
                $total = 0;
                mysqli_data_seek($items, 0);
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
                <tr class="total-row">
                    <td colspan="5" style="text-align: left;">المجموع الكلي</td>
                    <td><?= number_format($total, 2) ?> د.ل</td>
                </tr>
            </tbody>
        </table>
        
        <!-- أزرار التحكم -->
        <div class="action-buttons">
            <a href="admin_orders1.php" class="btn btn-back">
                <i class="fas fa-arrow-right"></i> العودة للقائمة
            </a>
            <div>
                <a href="admin_edit_order.php?id=<?= $order_id ?>" class="btn btn-edit">
                    <i class="fas fa-edit"></i> تعديل الطلب
                </a>
                <button onclick="window.print()" class="btn btn-print">
                    <i class="fas fa-print"></i> طباعة الفاتورة
                </button>
            </div>
        </div>
    </div>

    <!-- السكريبتات -->
    <script>
        // تأكيد قبل حذف الطلب
        function confirmDelete() {
            return confirm('هل أنت متأكد من حذف هذا الطلب؟ لا يمكن التراجع عن هذه العملية');
        }
    </script>
</body>
</html>