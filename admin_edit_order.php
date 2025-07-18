<?php
session_start();
header('Content-Type: text/html; charset=utf-8');

// التحقق من صلاحيات المدير
if (!isset($_SESSION['EMAIL'])) {
    header("Location: admin.php");
    exit();
}

include("../include/connection.php");

// معالجة تحديث حالة الطلب
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $order_id = intval($_POST['order_id']);
    $new_status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $update_query = "UPDATE orders1 SET status = ? WHERE order_id = ?";
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, 'si', $new_status, $order_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['message'] = "تم تحديث حالة الطلب بنجاح";
        header("Location: admin_order_details5.php?id=$order_id");
        exit();
    } else {
        $error = "حدث خطأ أثناء تحديث الطلب: " . mysqli_error($conn);
    }
}

// جلب بيانات الطلب
$order_id = intval($_GET['id']);
$query = "SELECT * FROM orders1 WHERE order_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $order_id);
mysqli_stmt_execute($stmt);
$order = mysqli_stmt_get_result($stmt)->fetch_assoc();

if (!$order) {
    die("الطلب غير موجود");
}

// دالة لتحويل الحالة إلى العربية
function getStatusArabic($status) {
    $statuses = [
        'pending' => 'قيد المعالجة',
        'processing' => 'جاري التجهيز',
        'shipped' => 'تم الشحن',
        'completed' => 'مكتمل',
        'cancelled' => 'ملغى'
    ];
    return $statuses[$status] ?? $status;
}

function playBell() {
    // استخدام صوت من موقع خارجي (مثال)
    var audio = new Audio('https://assets.mixkit.co/sfx/preview/mixkit-alarm-digital-clock-beep-989.mp3');
    audio.play().catch(e => console.error("خطأ:", e));
}




?>

<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <title>تعديل الطلب #<?= $order_id ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --success-color: #27ae60;
            --danger-color: #e74c3c;
            --warning-color: #f39c12;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
            --sidebar-width: 250px;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
            color: #333;
        }
        
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        /* القائمة الجانبية */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--secondary-color);
            color: white;
            padding: 20px 0;
            position: fixed;
            height: 100%;
        }
        
        .sidebar h3 {
            text-align: center;
            padding: 10px;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar a {
            display: block;
            color: white;
            padding: 12px 20px;
            text-decoration: none;
            border-right: 4px solid transparent;
            transition: all 0.3s;
        }
        
        .sidebar a:hover {
            background: rgba(255,255,255,0.1);
            border-right: 4px solid var(--primary-color);
        }
        
        /* المحتوى الرئيسي */
        .main-content {
            flex: 1;
            margin-right: var(--sidebar-width);
            padding: 30px;
            background: white;
            min-height: 100vh;
        }
        
        h2 {
            color: var(--secondary-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--primary-color);
        }
        
        /* نموذج التعديل */
        .form-container {
            max-width: 600px;
            margin: 20px auto;
            padding: 25px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--secondary-color);
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .form-group input:read-only {
            background-color: #f9f9f9;
            color: #666;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background: #2980b9;
        }
        
        .btn-secondary {
            background: #95a5a6;
            color: white;
            margin-right: 10px;
        }
        
        .btn-secondary:hover {
            background: #7f8c8d;
        }
        
        .error-message {
            padding: 15px;
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        /* تصميم متجاوب */
        @media (max-width: 768px) {
            .admin-container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                position: relative;
                height: auto;
            }
            
            .main-content {
                margin-right: 0;
                padding: 20px;
            }
        }
        
        @media (max-width: 480px) {
            .form-container {
                padding: 15px;
            }
            
            .btn {
                display: block;
                width: 100%;
                margin-bottom: 10px;
            }
            
            .btn-secondary {
                margin-right: 0;
            }
        }


        .sidebar-nav {
    /* إخفاء القائمة بشكل افتراضي على الجوال */
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease;
}

/* فقط للشاشات الكبيرة تظهر القائمة تلقائياً */
@media (min-width: 768px) {
    .sidebar-nav {
        max-height: none !important; /* تجاوز أي أنماط أخرى */
        display: block !important;
    }
}

.sidebar-nav.active {
    max-height: 1000px; /* ارتفاع كافي لعرض كل المحتوى */
}


    </style>
</head>
<body>
    <div class="admin-container">
        <!-- القائمة الجانبية -->

        <nav class="sidebar-nav" id="sidebarNav">
        <div class="sidebar">
            <h3>لوحة التحكم</h3>
            <a href="admianpanel.php"><i class="fas fa-home"></i> الرئيسية</a>
            <a href="admin_orders1.php"><i class="fas fa-shopping-cart"></i> الطلبات</a>
            <a href="admin_products.php"><i class="fas fa-box-open"></i> المنتجات</a>
            <a href="admin_logout.php"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a>
        </div>
</nav>



        
        <!-- المحتوى الرئيسي -->
        <div class="main-content">
            <h2>تعديل الطلب #<?= $order_id ?></h2>
            
            <?php if (isset($error)): ?>
                <div class="error-message"><?= $error ?></div>
            <?php endif; ?>
            
            <div class="form-container">
                <form method="post">
                    <input type="hidden" name="order_id" value="<?= $order_id ?>">
                    
                    <div class="form-group">
                        <label>حالة الطلب الحالية</label>
                        <input type="text" value="<?= getStatusArabic($order['status']) ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label>تغيير الحالة إلى</label>
                        <select name="status" required>
                            <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>قيد المعالجة</option>
                            <option value="processing" <?= $order['status'] == 'processing' ? 'selected' : '' ?>>جاري التجهيز</option>
                            <option value="shipped" <?= $order['status'] == 'shipped' ? 'selected' : '' ?>>تم الشحن</option>
                            <option value="completed" <?= $order['status'] == 'completed' ? 'selected' : '' ?>>مكتمل</option>
                            <option value="cancelled" <?= $order['status'] == 'cancelled' ? 'selected' : '' ?>>ملغى</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
                        <a href="admin_orders1.php?id=<?= $order_id ?>" class="btn btn-secondary">العودة</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.getElementById('menuToggle');
    const sidebarNav = document.getElementById('sidebarNav');
    
    // إغلاق القائمة بشكل افتراضي على الجوال
    if (window.innerWidth < 768) {
        sidebarNav.classList.remove('active');
    }
    
    menuToggle.addEventListener('click', function(e) {
        e.preventDefault();
        sidebarNav.classList.toggle('active');
    });
    
    // إغلاق القائمة عند النقر خارجها
    document.addEventListener('click', function(e) {
        if (!sidebarNav.contains(e.target) && e.target !== menuToggle) {
            sidebarNav.classList.remove('active');
        }
    });
});

</script>

</body>
</html>