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
session_start();

// التحقق من أن المستخدم مسجل دخوله وهو مدير
if (!isset($_SESSION['EMAIL'])) {
    header("Location: admin.php");
    exit();
}

include("../include/connection.php");

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




?>



<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المنتجات</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
<!-- بقية الهيد كما هي -->
<style>
    /* إضافة أنماط حالات الطلبات */

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
</style></head>
<body>
    <div class="admin-container">
        <!-- القائمة الجانبية -->
        <div class="sidebar">
        
            <div class="sidebar-header">
                <div class="sidebar-brand">
                    <img src="../imaesg/a1.png" alt="Logo">
                    <h3>لوحة التحكم</h3>
                </div>
                <button class="menu-toggle" id="menuToggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            
            <div class="user-panel">
                <img src="../assets/user.png" alt="User">
                <div class="user-info">
                    <div class="name"><?php echo $_SESSION['NAME']; ?></div>
                    <div class="role">مدير النظام</div>
                </div>
            </div>
            
            <nav class="sidebar-nav" id="sidebarNav">
                <ul class="sidebar-menu">
                    <li>
                        <a href="admianpanel.php">
                            <i class="fas fa-home"></i>
                            <span>الرئيسية</span>
                        </a>
                    </li>
                    <li>
                        <a href="product.php">
                            <i class="fas fa-box-open"></i>
                           <span>إدارة المنتجات</span>
                            <span class="badge">15</span>
                        </a>
                    </li>
                    <li>
                        <a href="admin_orders1.php" class="active">
                            <i class="fas fa-shopping-cart"></i>
                         <span>إدارة الطلبات</span>
                            <span class="badge">3 جديد</span>
                        </a>
                    </li>
                    <li>
                        <a href="">
                            <i class="fas fa-users"></i>
                            <span>إدارة العملاء</span>
                        </a>
                    </li>
                    <li>
                        <a href="">
                            <i class="fas fa-chart-bar"></i>
                            <span>التقارير</span>
                        </a>
                    </li>
                    <li>
                        <a href="settings.php">
                            <i class="fas fa-cog"></i>
                            <span>الإعدادات</span>
                        </a>
                    </li>
                </ul>
                
                <div class="sidebar-footer">
                    <a href="logout.php" style="color: #f39c12;">
                        <i class="fas fa-sign-out-alt"></i> تسجيل الخروج
                    </a>
                </div>
            </nav>
        </div>
        

        <!-- المحتوى الرئيسي -->
        <div class="main-content">

        <div class="filters">
            <h4> فلترة الطلبات</h4>

            <form method="get">
                <select name="status" onchange="this.form.submit()">
                    <option value="">كل الطلبات</option>
                    <option value="pending" <?= isset($_GET['status']) && $_GET['status'] == 'pending' ? 'selected' : '' ?>>قيد المعالجة</option>
                    <option value="processing" <?= isset($_GET['status']) && $_GET['status'] == 'processing' ? 'selected' : '' ?>>جاري التنفيذ</option>
                    <option value="shipped" <?= isset($_GET['status']) && $_GET['status'] == 'shipped' ? 'selected' : '' ?>>تم الشحن</option>
                    <option value="delivered" <?= isset($_GET['status']) && $_GET['status'] == 'delivered' ? 'selected' : '' ?>>تم التسليم</option>
                    <option value="completed" <?= isset($_GET['status']) && $_GET['status'] == 'completed' ? 'selected' : '' ?>>مكتمل</option>
                    <option value="cancelled" <?= isset($_GET['status']) && $_GET['status'] == 'cancelled' ? 'selected' : '' ?>>ملغى</option>
                    <option value="returned" <?= isset($_GET['status']) && $_GET['status'] == 'returned' ? 'selected' : '' ?>>مرتجع</option>
                </select>
            </form>
        </div>

          <center>  <h1>إدارة الطلبات</h1></center>

          <!-- جدول الطلبات -->
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

                            <td data-label="رقم الطلب">#<?= $order['order_id'] ?></td>
<td data-label="التاريخ"><?= date('Y-m-d H:i', strtotime($order['order_date'])) ?></td>
<td data-label="العميل">
    <?= htmlspecialchars($order['shipping_address']) ?><br>
    <?= $order['phone'] ?>
</td>
<td data-label="المبلغ"><?= number_format($order['total_amount'], 2) ?> د.ل</td>
<td data-label="الحالة">
    <span class="order-status status-<?= $order['status'] ?>">
        <i class="<?= $status_style['icon'] ?>"></i>
        <?= $translated_status ?>
    </span>
<td data-label="الإجراءات">
    <!-- الأزرار هنا -->
    <a href="admin_order_details3.php?id=<?= $order['order_id'] ?>" class="action-btn view-btn">
                                        <i class="fas fa-eye"></i> عرض
                                    </a>
                                    <a href="admin_edit_order.php?id=<?= $order['order_id'] ?>" class="action-btn edit-btn">
                                        <i class="fas fa-edit"></i> تعديل
                                    </a>
                                    <a href="admin_orders1.php?delete_id=<?= $order['order_id'] ?>" 
                                       class="action-btn delete-btn" 
                                       onclick="return confirm('سيتم حذف الطلب وجميع منتجاته المرتبطة به. هل أنت متأكد؟')">
                                        <i class="fas fa-trash"></i> حذف
                                    </a>
</td>

</td>
 </tr>
                            <?php
                        }
                    } else {
                        echo "<tr><td colspan='6' style='text-align:center;'>لا توجد طلبات</td></tr>";
                    }
                    ?>
                </tbody>
            </table>

<!-- أضف هذا في واجهة المستخدم -->
<button id="enableSound" style="display: none;">تفعيل الإشعار الصوتي</button>

<script>
document.getElementById('enableSound').addEventListener('click', function() {
    playBell(); // اختبار التشغيل
    this.style.display = 'none'; // إخفاء الزر بعد التفغيل
});

// عند وجود طلب جديد، عرض الزر إذا كان الصوت غير مفعل
function notifyNewOrder() {
    const enableBtn = document.getElementById('enableSound');
    enableBtn.style.display = 'block';
    enableBtn.textContent = 'لديك طلب جديد! انقر لتشغيل الجرس';
}
</script>


        </div>
    </div>
    <div class="actions-bar">
    <a href="admin_orders1.php?delete_all=1" 
       class="action-btn delete-all-btn"
       onclick="return confirm('هل أنت متأكد من حذف جميع الطلبات؟ هذا الإجراء لا يمكن التراجع عنه!')">
       <i class="fas fa-trash"></i> حذف جميع الطلبات
    </a>
</div>





    <script>
        // تأكيد الحذف مع رسالة توضيحية
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                if (!confirm('سيتم حذف هذا الطلب بشكل نهائي مع جميع المنتجات المرتبطة به. هذا الإجراء لا يمكن التراجع عنه. هل أنت متأكد؟')) {
                    e.preventDefault();
                }
            });
        });
    </script>

<script>
// تأكيد حذف جميع الطلبات
document.querySelector('.delete-all-btn').addEventListener('click', function(e) {
    if (!confirm('تحذير: سيتم حذف جميع الطلبات بشكل نهائي مع جميع العناصر المرتبطة بها. هذا الإجراء لا يمكن التراجع عنه. هل أنت متأكد؟')) {
        e.preventDefault();
    } else {
        if (!confirm('هل أنت متأكد تماماً؟ هذه عملية خطيرة قد تؤثر على التقارير والإحصائيات.')) {
            e.preventDefault();
        }
    }
});
</script>
<script>
        // تبديل القائمة الجانبية على الأجهزة المحمولة
        document.getElementById('menuToggle').addEventListener('click', function() {
            document.getElementById('sidebarNav').classList.toggle('active');
        });
        
        // إغلاق القائمة عند النقر خارجها
        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.sidebar');
            const menuToggle = document.getElementById('menuToggle');
            
            if (window.innerWidth < 768 && 
                !sidebar.contains(event.target) && 
                event.target !== menuToggle) {
                document.getElementById('sidebarNav').classList.remove('active');
            }
        });
        
        // إضافة تأثيرات للقائمة الجانبية
        const menuItems = document.querySelectorAll('.sidebar-menu li a');
        menuItems.forEach(item => {
            item.addEventListener('click', function() {
                menuItems.forEach(i => i.classList.remove('active'));
                this.classList.add('active');
                
                // إغلاق القائمة على الأجهزة المحمولة بعد الاختيار
                if (window.innerWidth < 768) {
                    document.getElementById('sidebarNav').classList.remove('active');
                }
            });
        });
    </script>

<script>
// صوت التنبيه
function playBell() {
    // إنشاء عنصر صوت مع مسار مطلق
    var audio = new Audio('/path/to/bell.mp3');
    
    // محاولة التشغيل مع معالجة الأخطاء
    audio.play().then(() => {
        console.log("تم تشغيل الجرس بنجاح");
    }).catch(e => {
        console.error("خطأ في تشغيل الصوت:", e);
        // يمكنك عرض رسالة للمستخدم هنا
        alert("تعذر تشغيل صوت الإشعار. يرجى تفعيل الصوت في المتصفح.");
    });
}
// متغير لتخزين آخر طلب تم عرضه
let lastOrderId = <?php 
    $q = "SELECT MAX(order_id) as max_id FROM orders1";
    $res = mysqli_query($conn, $q);
    $row = mysqli_fetch_assoc($res);
    echo $row['max_id'] ?? 0;
?>;

// التحقق من الطلبات الجديدة كل 5 ثواني
setInterval(function() {
    fetch('check_new_orders.php?last_id=' + lastOrderId)
    .then(response => response.json())
    .then(data => {
        if(data.new_orders > 0) {
            playBell();
            lastOrderId = data.last_id;
            
            // عرض تنبيه مرئي
            const alertDiv = document.createElement('div');
            alertDiv.style.position = 'fixed';
            alertDiv.style.top = '20px';
            alertDiv.style.right = '20px';
            alertDiv.style.backgroundColor = '#27ae60';
            alertDiv.style.color = 'white';
            alertDiv.style.padding = '15px';
            alertDiv.style.borderRadius = '5px';
            alertDiv.style.zIndex = '10000';
            alertDiv.innerHTML = `<i class="fas fa-bell"></i> لديك ${data.new_orders} طلب جديد!`;
            document.body.appendChild(alertDiv);
            
            setTimeout(() => alertDiv.remove(), 5000);
            
            // تحديث الصفحة بعد 5 ثواني
            setTimeout(() => location.reload(), 5000);
        }
    });
}, 5000); // كل 5 ثواني

// إضافة هذا السكريبت في نهاية الصفحة
document.addEventListener('DOMContentLoaded', function() {
  if (window.innerWidth < 768) {
    document.querySelectorAll('.orders-table tr').forEach(row => {
      row.addEventListener('click', function(e) {
        if (!e.target.closest('.action-btn')) {
          this.classList.toggle('expanded');
        }
      });
    });
  }
});




</script>


</body>
</html>