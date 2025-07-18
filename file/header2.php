<?php
ob_start(); // بدء تخزين الإخراج

// تفعيل الإبلاغ عن الأخطاء
error_reporting(E_ALL);
ini_set('display_errors', 1);

// التحقق من وجود ملف الاتصال
if (!file_exists('./include/connection.php')) {
    die('خطأ: ملف الاتصال بقاعدة البيانات غير موجود!');
}

require_once('./include/connection.php');

// جلب إعدادات الموقع
$settings_query = "SELECT * FROM site_settings WHERE id = 1 LIMIT 1";
$settings_result = mysqli_query($conn, $settings_query);

if ($settings_result && mysqli_num_rows($settings_result) ){
    $settings = mysqli_fetch_assoc($settings_result);
    $site_name = htmlspecialchars($settings['site_name'] ?? 'متجر إلكتروني');
    $logo_path = htmlspecialchars($settings['logo_path'] ?? 'images/logo.png');
} else {
    $site_name = 'متجر إلكتروني';
    $logo_path = 'images/logo.png';
}

// جلب عدد العناصر في السلة
$cart_query = "SELECT COUNT(*) AS count FROM cart1";
$cart_result = mysqli_query($conn, $cart_query);
$row_count = ($cart_result && $cart_row = mysqli_fetch_assoc($cart_result)) ? $cart_row['count'] : 0;

// جلب المنتجات المضافة حديثاً
$product_query = "SELECT * FROM products ORDER BY id DESC LIMIT 5";
$product_result = mysqli_query($conn, $product_query);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $site_name; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* أنماط CSS هنا */
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --light-color: #ecf0f1;
            --dark-color: #333;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
            color: var(--dark-color);
        }
        .sidebar {
            position: fixed;
            top: 0;
            left: 0; /* تغيير من right إلى left */
            width: 250px;
            height: 100vh;
            background: var(--secondary-color);
            color: white;
            transition: all 0.3s;
            z-index: 1000;
            overflow-y: auto;
        }
        
        .sidebar-header {
            padding: 20px;
            background: rgba(0, 0, 0, 0.1);
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-menu {
            list-style: none;
        }
        
        .sidebar-menu li a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: white;
            text-decoration: none;
            border-left: 4px solid transparent; /* تغيير من right إلى left */
            transition: all 0.3s;
        }
        
        .sidebar-menu li a:hover,
        .sidebar-menu li a.active {
            background: rgba(255, 255, 255, 0.1);
            border-left: 4px solid var(--primary-color); /* تغيير من right إلى left */
        }
        
        .sidebar-menu li a i {
            margin-right: 10px; /* تغيير من left إلى right */
            width: 20px;
            text-align: center;
        }
        
        /* زر القائمة للجوال */
        .menu-toggle {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px; /* تغيير من right إلى left */
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 4px;
            padding: 10px 15px;
            z-index: 1100;
            cursor: pointer;
        }
        
        /* المحتوى الرئيسي */
        .main-content {
            margin-left: 250px; /* تغيير من right إلى left */
            padding: 20px;
            transition: all 0.3s;
        }
        
        /* تصميم متجاوب */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%); /* تغيير من 100% إلى -100% */
                width: 80%;
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .menu-toggle {
                display: block;
            }
            
            .main-content {
                margin-left: 0; /* تغيير من right إلى left */
            }
        }
        
        /* الشعار الدائري الثابت */
        .logo {

            
            display: flex;
            align-items: center;
            gap: 10px;
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
        
        .logo h1 {
            font-size: 1.2rem;
            color: var(--secondary-color);
        }
        
        .logo img {
            width: 50px;
            height: 50px;
            border-radius: 50%; /* جعل الشعار دائري */
            object-fit: cover;
            border: 2px solid var(--primary-color);
        }
        
        /* المنتجات المضافة حديثاً بشكل دائري */
        .recent-products {
            
          position: fixed;
          position: fixed;
          top:0;
          display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 80px; /* لتفادي تداخل مع الشعار الثابت */
            position: relative;
            z-index: 1;
        }
        
        .recent-products h4 {
            width: 100%;
            margin-bottom: 15px;
            color: var(--secondary-color);
        }
        
        .product-circle {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid var(--primary-color);
            transition: transform 0.3s;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .product-circle img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .product-circle:hover {
            transform: scale(1.1);
        }
        
        /* تثبيت العناصر عند التمرير */
        .header {
            position: sticky;
            top: 0;
            background: white;
            padding: 15px 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            z-index: 900;
        }

/* القائمة الجانبية المعدلة */
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 250px;
    height: 100vh;
    background: var(--secondary-color);
    color: white;
    transition: all 0.3s;
    z-index: 1000;
    overflow-y: auto;
    transform: translateX(-100%); /* مخفية بشكل افتراضي */
}

.sidebar.active {
    transform: translateX(0); /* تظهر عند التنشيط */
}

/* المحتوى الرئيسي */
.main-content {
    margin-left: 0; /* لا يوجد مسافة من اليسار */
    padding: 20px;
    transition: all 0.3s;
    width: 100%;
}

@media (min-width: 992px) {
    .sidebar {
        transform: translateX(0); /* تظهر دائمًا على الشاشات الكبيرة */
    }
    .main-content {
        margin-left: 250px; /* مسافة من اليسار */
        width: calc(100% - 250px);
    }
}

/* تعديل حجم وعرض المنتجات */
.recent-products {
    /position: fixed;
     top:60px;
     right: 0;
   display: grid;
    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    gap: 15px;
    margin-top: 20px;
    padding: 15px;
    margin-buttom:20px;
}

.product-circle {
    width: 100%;
    aspect-ratio: 1/1; /* للحفاظ على الشكل الدائري */
    border-radius: 50%;
    overflow: hidden;
    border: 2px solid var(--primary-color);
    transition: transform 0.3s;
    background: white;
}

/* تعديلات للشاشات الصغيرة */
@media (max-width: 768px) {
    .recent-products {
        grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
        gap: 10px;
    }
}
.cart-icon {
                position: relative;
                display: inline-block; /* لجعل السلة تتناسب مع المحتوى */
                font-size:30px;
                margin-top:20px;
                position: fixed;
    top: 60px;
    right: 20px;
    z-index: 900;

            }
            .cart-count {
                position: absolute;
                top: -10px; /* ضبط الموضع العمودي */
                left: -10px; /* ضبط الموضع الأفقي */
                background-color: red; /* لون الخلفية */
                color: white; /* لون النص */
                border-radius: 50%; /* لجعلها دائرية */
                padding: 5px 5px; /* تباعد داخلي */
                font-size: 4px; /* حجم الخط */
                
                
                float:left;
          }
    .cart-icon a,i{
        font-size:20px;
    }
    
    







    </style>
</head>
<body>
    <!-- زر القائمة للجوّال -->
    <button class="menu-toggle" id="menuToggle">
        <i class="fas fa-bars"></i> القائمة
    </button>

    <!-- القائمة الجانبية -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h3>لوحة التحكم</h3>
        </div>
        <ul class="sidebar-menu">
            <li><a href="index.php"><i class="fas fa-home"></i> الرئيسية</a></li>
            <li><a href="admin/admin.php"><i class="fas fa-cog"></i> لوحة التحكم</a></li>
            <?php
            $section_query = "SELECT * FROM section";
            $section_result = mysqli_query($conn, $section_query);
            while ($section_row = mysqli_fetch_assoc($section_result)) {
                echo '<li><a href="section1.php?section=' . htmlspecialchars($section_row['section_name']) . '">';
                echo '<i class="fas fa-folder"></i> ' . htmlspecialchars($section_row['section_name']);
                echo '</a></li>';
            }
            ?>
        </ul>
    </div>

    <!-- أيقونة السلة -->
    <div class="cart-icon">
        <a href="cart1.php"><i class="fas fa-shopping-cart"></i></a>
        <span class="cart-count"><?php echo $row_count; ?></span>
    </div>

    <!-- الشعار -->
    <div class="logo">
        <h1><?php echo $site_name; ?></h1>
        <img src="<?php echo $logo_path; ?>" alt="شعار الموقع">
    </div>
    <!-- المحتوى الرئيسي -->
    <div class="main-content" id="mainContent">
        <div class="header">
            <div class="container1">
                <!-- محتوى إضافي -->
            </div>
        </div>

        <!-- قسم المضافة حديثاً بشكل دائري -->
        <div class="recent-products">
            <h4>المضافة حديثاً</h4>
            <?php
            if ($product_result) {
                while ($product_row = mysqli_fetch_assoc($product_result)) {
                    echo '<a href="details.php?id=' . htmlspecialchars($product_row['id']) . '" class="product-circle">';
                    echo '<img src="uploads/img/' . htmlspecialchars($product_row['image']) . '" alt="' . htmlspecialchars($product_row['name']) . '">';
                    echo '</a>';
                }
            }
            ?>
        </div>
    </div>

    <script>
        // تفعيل وإخفاء القائمة الجانبية
        document.addEventListener('DOMContentLoaded', function () {
            const menuToggle = document.getElementById('menuToggle');
            const sidebar = document.getElementById('sidebar');

            menuToggle.addEventListener('click', function () {
                sidebar.classList.toggle('active');
            });

            document.addEventListener('click', function (event) {
                if (!sidebar.contains(event.target) && event.target !== menuToggle) {
                    sidebar.classList.remove('active');
                }
            });

            window.addEventListener('resize', function () {
                if (window.innerWidth > 768) {
                    sidebar.classList.remove('active');
                }
            });
        });
    </script>
</body>

</html>
    <!-- المحتوى الرئيسي -->
        <!-- سيتم إضافة المحتوى هنا -->
<?php
ob_end_flush(); // 
?>