<?php
session_start();

// التحقق من أن المستخدم مسجل دخوله وهو مدير
if (!isset($_SESSION['EMAIL'])) {
    header("Location: admin.php");
    exit();
}

include("../include/connection.php");

// دالة حذف الطلب
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    
    // التحقق من وجود الطلب أولاً
    $check_query = "SELECT * FROM orders1 WHERE order_id = $delete_id";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        // تسجيل عملية الحذف في السجل
        $log_query = "INSERT INTO deleted_orders 
                      SELECT *, NOW(), '" . $_SESSION['EMAIL'] . "' 
                      FROM orders1 WHERE order_id = $delete_id";
        mysqli_query($conn, $log_query);
        
        // حذف الطلب
        $delete_query = "DELETE FROM orders1 WHERE order_id = $delete_id";
        if (mysqli_query($conn, $delete_query)) {
            echo "<script>
                alert('تم حذف الطلب بنجاح');
                window.location.href = 'admin_orders1.php';
            </script>";
        } else {
            echo "<script>alert('خطأ في حذف الطلب: " . mysqli_error($conn) . "');</script>";
        }
    } else {
        echo "<script>alert('الطلب غير موجود');</script>";
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
    <title>لوحة التحكم - إدارة الطلبات</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
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
            --delete-color: #e74c3c;
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

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        /* أنماط القائمة الجانبية */
        .sidebar {
            width: 250px;
            background: var(--sidebar-bg);
            color: white;
            padding: 20px 0;
        }

        /* أنماط الجدول */
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
            background-color: var(--table-header);
            color: white;
        }

        .orders-table tr:nth-child(even) {
            background-color: var(--table-even-row);
        }

        /* أنماط أزرار الإجراءات */
        .action-btn {
            padding: 6px 10px;
            margin: 0 3px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .view-btn {
            background: var(--primary-color);
            color: white;
        }

        .edit-btn {
            background: #f39c12;
            color: white;
        }

        .delete-btn {
            background: var(--delete-color);
            color: white;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        /* أنماط حالات الطلب */
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
            color: var(--pending-color);
        }

        /* باقي الأنماط... */
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- القائمة الجانبية -->
        <div class="sidebar">
            <!-- محتوى القائمة الجانبية -->
        </div>

        <!-- المحتوى الرئيسي -->
        <div class="main-content">
            <h1>إدارة الطلبات</h1>

            <!-- فلترة الطلبات حسب الحالة -->
            <div class="filters">
                <form method="get">
                    <select name="status" onchange="this.form.submit()">
                        <option value="">كل الطلبات</option>
                        <option value="pending" <?= isset($_GET['status']) && $_GET['status'] == 'pending' ? 'selected' : '' ?>>قيد المعالجة</option>
                        <!-- باقي الخيارات... -->
                    </select>
                </form>
            </div>

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
                                <td>#<?= $order['order_id'] ?></td>
                                <td><?= date('Y-m-d H:i', strtotime($order['order_date'])) ?></td>
                                <td>
                                    <?= htmlspecialchars($order['shipping_address']) ?><br>
                                    <?= $order['phone'] ?>
                                </td>
                                <td><?= number_format($order['total_amount'], 2) ?> د.ل</td>
                                <td>
                                    <span class="order-status status-<?= $order['status'] ?>">
                                        <i class="<?= $status_style['icon'] ?>"></i>
                                        <?= $translated_status ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="admin_order_details.php?id=<?= $order['order_id'] ?>" class="action-btn view-btn">
                                        <i class="fas fa-eye"></i> عرض
                                    </a>
                                    <a href="admin_edit_order.php?id=<?= $order['order_id'] ?>" class="action-btn edit-btn">
                                        <i class="fas fa-edit"></i> تعديل
                                    </a>
                                    <a href="admin_orders1.php?delete_id=<?= $order['order_id'] ?>" 
                                       class="action-btn delete-btn" 
                                       onclick="return confirm('هل أنت متأكد من حذف هذا الطلب؟')">
                                        <i class="fas fa-trash"></i> حذف
                                    </a>
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
        </div>
    </div>

    <script>
        // تأكيد الحذف
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                if (!confirm('هل أنت متأكد من حذف هذا الطلب؟')) {
                    e.preventDefault();
                }
            });
        });

        // باقي السكريبتات...
    </script>
</body>
</html>