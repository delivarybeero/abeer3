<?php
session_start();

// التحقق من أن المستخدم مسجل دخوله وهو مدير
if (!isset($_SESSION['EMAIL'])) {
    header("Location: admin.php");
    exit();
}

include("../include/connection.php");

// دالة حذف الطلب وعناصره
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    
    // بدء المعاملة
    mysqli_begin_transaction($conn);
    
    try {
        // 1. حذف العناصر المرتبطة بالطلب أولاً
        $delete_items_query = "DELETE FROM orders1_items WHERE order_id = $delete_id";
        if (!mysqli_query($conn, $delete_items_query)) {
            throw new Exception("خطأ في حذف عناصر الطلب: " . mysqli_error($conn));
        }
        
        // 2. حذف الطلب نفسه
        $delete_query = "DELETE FROM orders1 WHERE order_id = $delete_id";
        if (!mysqli_query($conn, $delete_query)) {
            throw new Exception("خطأ في حذف الطلب: " . mysqli_error($conn));
        }
        
        // إذا نجحت جميع العمليات
        mysqli_commit($conn);
        echo "<script>
            alert('تم حذف الطلب وعناصره بنجاح');
            window.location.href = 'admin_orders1.php';
        </script>";
    } catch (Exception $e) {
        // في حالة حدوث خطأ، التراجع عن جميع العمليات
        mysqli_rollback($conn);
        echo "<script>alert('" . $e->getMessage() . "');</script>";
    }
}

// دالة حذف جميع الطلبات
if (isset($_GET['delete_all']) ){
    // بدء المعاملة
    mysqli_begin_transaction($conn);
    
    try {
        // 1. حذف جميع عناصر الطلبات
        $delete_all_items = "DELETE FROM orders1_items";
        if (!mysqli_query($conn, $delete_all_items)) {
            throw new Exception("خطأ في حذف عناصر الطلبات: " . mysqli_error($conn));
        }
        
        // 2. حذف جميع الطلبات
        $delete_all_orders = "DELETE FROM orders1";
        if (!mysqli_query($conn, $delete_all_orders)) {
            throw new Exception("خطأ في حذف الطلبات: " . mysqli_error($conn));
        }
        
        // إذا نجحت جميع العمليات
        mysqli_commit($conn);
        echo "<script>
            alert('تم حذف جميع الطلبات بنجاح');
            window.location.href = 'admin_orders1.php';
        </script>";
    } catch (Exception $e) {
        // في حالة حدوث خطأ، التراجع عن جميع العمليات
        mysqli_rollback($conn);
        echo "<script>alert('" . $e->getMessage() . "');</script>";
    }
}

// دالة لتعريب حالة الطلب
function translateOrderStatus($status) {
    $statuses = [
        'pending' => 'قيد المعالجة',
        'processing' => 'جاري التنفيذ',
        'shipped' => 'تم الشحن',
        'delivered' => 'تم التسليم',
        'cancelled' => 'ملغى',
        'returned' => 'مرتجع',
        'completed' => 'مكتمل'
    ];
    
    return $statuses[$status] ?? $status;
}

// دالة لإرجاع لون وأيقونة الحالة
function getStatusStyle($status) {
    $styles = [
        'pending' => ['color' => '#e67e22', 'icon' => 'fas fa-clock'],
        'processing' => ['color' => '#3498db', 'icon' => 'fas fa-cog fa-spin'],
        'shipped' => ['color' => '#9b59b6', 'icon' => 'fas fa-truck'],
        'delivered' => ['color' => '#2ecc71', 'icon' => 'fas fa-check-circle'],
        'cancelled' => ['color' => '#e74c3c', 'icon' => 'fas fa-times-circle'],
        'returned' => ['color' => '#f39c12', 'icon' => 'fas fa-undo'],
        'completed' => ['color' => '#27ae60', 'icon' => 'fas fa-check-double']
    ];
    
    return $styles[$status] ?? ['color' => '#7f8c8d', 'icon' => 'fas fa-question-circle'];
}

?>
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الطلبات</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* هنا يتم تضمين جميع أنماط CSS الواردة في الكود السابق */
        :root {
            --primary-color: #3498db;
            --sidebar-bg: #2c3e50;
            --sidebar-active: #34495e;
            --sidebar-hover: #3d5166;
            --sidebar-text: #ecf0f1;
            --sidebar-width: 280px;
            --table-header: #3498db;
            --table-even-row: #f9f9f9;
            --pending-color: #e67e22;
            --completed-color: #27ae60;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        






        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            overflow-x: hidden;
            color: #333;
        }


        sbody {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 250px;
            background: #2c3e50;
            color: white;
            padding: 20px 0;
        }
        .sidebar a {
            display: block;
            color: white;
            padding: 15px 20px;
            text-decoration: none;
            border-left: 4px solid transparent;
            transition: all 0.3s;
        }
        .sidebar a:hover {
            background: #34495e;
            border-left: 4px solid #3498db;
        }
        .main-content {
            flex: 1;
            padding: 20px;
        }
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .orders-table th, .orders-table td {
            padding: 12px 15px;
            text-align: center;
            border: 1px solid #ddd;
        }
        .orders-table th {
            background-color: #3498db;
            color: white;
        }
        .orders-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .status-pending {
            color: #e67e22;
            font-weight: bold;
        }
        .status-completed {
            color: #27ae60;
            font-weight: bold;
        }
        .action-btn {
            padding: 5px 10px;
            margin: 0 3px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        .view-btn {
            background: #3498db;
            color: white;
        }
        .edit-btn {
            background: #f39c12;
            color: white;
        }
        .delete-btn {
            background: #e74c3c;
            color: white;
        }
        .page-title {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }




        
        .admin-container {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        /* تصميم القائمة الجانبية */
        .sidebar {
            width: 100%;
            background: var(--sidebar-bg);
            color: var(--sidebar-text);
            position: relative;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background-color: rgba(0,0,0,0.1);
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .sidebar-brand img {
            width: 30px;
            height: 30px;
            border-radius: 50%;
        }
        
        .sidebar-brand h3 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 600;
        }
        
        .menu-toggle {
            display: block;
            background: none;
            border: none;
            color: var(--sidebar-text);
            font-size: 1.5rem;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .menu-toggle:hover {
            color: var(--primary-color);
        }
        
        .sidebar-nav {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }
        
        .sidebar-nav.active {
            max-height: 1000px;
        }
        
        .sidebar-menu {
            list-style: none;
        }
        
        .sidebar-menu li {
            position: relative;
        }
        
        .sidebar-menu li a {
            display: flex;
            align-items: center;
            color: var(--sidebar-text);
            padding: 15px 20px;
            text-decoration: none;
            border-right: 4px solid transparent;
            transition: all 0.3s;
            gap: 10px;
        }
        
        .sidebar-menu li a i {
            width: 20px;
            text-align: center;
        }
        
        .sidebar-menu li a:hover {
            background: var(--sidebar-hover);
            border-right: 4px solid var(--primary-color);
            padding-right: 25px;
        }
        
        .sidebar-menu li a.active {
            background: var(--sidebar-active);
            border-right: 4px solid var(--primary-color);
            font-weight: 600;
        }
        
        .sidebar-menu li a .badge {
            margin-right: auto;
            background: var(--primary-color);
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.7rem;
        }
        
        .sidebar-footer {
            padding: 15px;
            border-top: 1px solid rgba(255,255,255,0.1);
            margin-top: 10px;
            text-align: center;
        }
        
        .user-panel {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .user-panel img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .user-info {
            line-height: 1.3;
        }
        
        .user-info .name {
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .user-info .role {
            font-size: 0.8rem;
            opacity: 0.8;
        }
        
        /* المحتوى الرئيسي */
        .main-content {
            flex: 1;
            padding: 20px;
            background: white;
            transition: margin 0.3s;
        }
        
        /* باقي الأنماط... (يمكن إضافة الأنماط السابقة هنا) */
        
        /* للشاشات المتوسطة والكبيرة */
        @media (min-width: 768px) {
            .admin-container {
                flex-direction: row;
            }
            
            .sidebar {
                width: var(--sidebar-width);
                min-height: 100vh;
                position: fixed;
                padding: 0;
            }
            
            .sidebar-header {
                padding: 20px;
            }
            
            .menu-toggle {
                display: none;
            }
            
            .sidebar-nav {
                max-height: none;
                height: calc(100vh - 120px);
                overflow-y: auto;
            }
            
            .main-content {
                margin-right: var(--sidebar-width);
                padding: 30px;
            }
        }
        
        /* تحسينات للشاشات الصغيرة جدًا */
        @media (max-width: 480px) {
            .sidebar-brand h3 {
                font-size: 1rem;
            }
            
            .sidebar-menu li a {
                padding: 12px 15px;
                font-size: 0.9rem;
            }
        }


        .order-status {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .status-pending {
            background-color: rgba(230, 126, 34, 0.1);
            color: #e67e22;
        }
        
        .status-processing {
            background-color: rgba(52, 152, 219, 0.1);
            color: #3498db;
        }
        
        .status-shipped {
            background-color: rgba(155, 89, 182, 0.1);
            color: #9b59b6;
        }
        
        .status-delivered {
            background-color: rgba(46, 204, 113, 0.1);
            color: #2ecc71;
        }
        
        .status-cancelled {
            background-color: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
        }
        
        .status-returned {
            background-color: rgba(243, 156, 18, 0.1);
            color: #f39c12;
        }
        
        .status-completed {
            background-color: rgba(39, 174, 96, 0.1);
            color: #27ae60;
        }        .navigation {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        .fa-trash {
  color: inherit !important;
}/* أيقونة السلة باللون الأحمر */
.delete-btn .fas.fa-trash {
  color: #e74c3c !important; /* أحمر داكن */
  transition: all 0.3s ease; /* تأثير حركي */
}

.delete-btn:hover .fas.fa-trash {
  color: #c0392b !important; /* أحمر داكن عند التحويم */
}
  /* تحسين شريط الإجراءات السفلي */
.actions-bar {
    margin: 20px 0;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 5px;
    text-align: center;
}

.delete-all-btn {
    background: #e74c3c;
    color: white;
    padding: 10px 20px;
    border-radius: 4px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
}

.delete-all-btn:hover {
    background: #c0392b;
    transform: translateY(-2px);
}

/* تحسين عنوان الصفحة */
.page-header {
    text-align: center;
    margin-bottom: 30px;
    padding-bottom: 15px;
    border-bottom: 2px solid #3498db;
    position: relative;
}

.page-header h1 {
    color: #2c3e50;
    font-size: 2rem;
    margin-bottom: 10px;
}

/* تحسين قائمة الفلاتر */
.filters {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.filters h4 {
    margin-bottom: 15px;
    color: #2c3e50;
    font-size: 1.2rem;
}

.filters select {
    width: 100%;
    max-width: 300px;
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background-color: #f8f9fa;
    font-size: 1rem;
    transition: all 0.3s;
}

.filters select:focus {
    border-color: #3498db;
    outline: none;
    box-shadow: 0 0 0 3px rgba(52,152,219,0.2);
}

/* تحسين الرسائل المنبثقة */
.alert-message {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 15px 25px;
    border-radius: 5px;
    background: #27ae60;
    color: white;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 1000;
    display: flex;
    align-items: center;
    gap: 10px;
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from { transform: translateX(100%); }
    to { transform: translateX(0); }
}

.alert-message.error {
    background: #e74c3c;
}

.alert-message.warning {
    background: #f39c12;
}@media (max-width: 768px) {
  .orders-table {
    width: 100%;
    border-collapse: collapse;
  }
  
  .orders-table thead {
    display: none;
  }
  
  .orders-table tr {
    display: block;
    margin-bottom: 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    background: #fff;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
  }
  
  .orders-table td {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 15px;
    border: none;
    border-bottom: 1px solid #eee;
    text-align: right !important;
  }
  
  .orders-table td:last-child {
    border-bottom: none;
  }
  
  .orders-table td::before {
    content: attr(data-label);
    font-weight: bold;
    margin-left: 10px;
    color: #555;
  }
  
  /* تحسين عرض أزرار الإجراءات */
  .orders-table td[data-label="الإجراءات"] {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    justify-content: center;
  }
  
  .orders-table td[data-label="الإجراءات"] .action-btn {
    flex: 1;
    min-width: 80px;
    text-align: center;
    margin: 2px;
  }
  
  /* تحسين عرض حالة الطلب */
  .order-status {
    justify-content: space-between;
    width: 100%;
  }
}
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="sidebar">
            <!-- هنا القائمة الجانبية -->
        </div>
        <div class="main-content">
            <div class="filters">
                <h4> فلترة الطلبات</h4>
                <form method="get">
                    <select name="status" onchange="this.form.submit()">
                        <option value="">كل الطلبات</option>
                        <option value="pending">قيد المعالجة</option>
                        <option value="processing">جاري التنفيذ</option>
                        <option value="shipped">تم الشحن</option>
                        <option value="delivered">تم التسليم</option>
                        <option value="completed">مكتمل</option>
                        <option value="cancelled">ملغى</option>
                        <option value="returned">مرتجع</option>
                    </select>
                </form>
            </div>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>رقم الطلب</th>
                        <th>التاريخ</th>
                        <th>العميل</th>
                        <th>المبلغ</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = "SELECT * FROM orders1";
                    if (isset($_GET['status']) && !empty($_GET['status'])) {
                        $status = mysqli_real_escape_string($conn, $_GET['status']);
                        $query .= " WHERE status = '$status'";
                    }
                    $query .= " ORDER BY order_date DESC";
                    $result = mysqli_query($conn, $query);

                    if (mysqli_num_rows($result) > 0) {
                        while ($order = mysqli_fetch_assoc($result)) {
                            $status_style = getStatusStyle($order['status']);
                            $translated_status = translateOrderStatus($order['status']);
                            ?>
                            <tr>
                                <td>#<?= $order['order_id'] ?></td>
                                <td><?= date('Y-m-d H:i', strtotime($order['order_date'])) ?></td>
                                <td><?= htmlspecialchars($order['shipping_address']) ?><br><?= $order['phone'] ?></td>
                                <td><?= number_format($order['total_amount'], 2) ?> د.ل</td>
                                <td><?= $translated_status ?></td>
                                <td>
                                    <a href="admin_order_details3.php?id=<?= $order['order_id'] ?>" class="action-btn view-btn">عرض</a>
                                    <a href="admin_edit_order.php?id=<?= $order['order_id'] ?>" class="action-btn edit-btn">تعديل</a>
                                    <a href="?delete_id=<?= $order['order_id'] ?>" class="action-btn delete-btn" onclick="return confirm('هل أنت متأكد؟')">حذف</a>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        echo "<tr><td colspan='6'>لا توجد طلبات</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>