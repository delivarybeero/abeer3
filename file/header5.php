<?php
// تفعيل الإبلاغ عن الأخطاء
error_reporting(E_ALL);
ini_set('display_errors', 1);

// التحقق من وجود ملف connection.php
if (!file_exists('./include/connection.php')) {
    die('خطأ: ملف الاتصال بقاعدة البيانات غير موجود!');
}

require_once('./include/connection.php');

// جلب إعدادات الموقع
$settings_query = "SELECT * FROM site_settings WHERE id = 1 LIMIT 1";
$settings_result = mysqli_query($conn, $settings_query);

if ($settings_result && mysqli_num_rows($settings_result) > 0) {
    $settings = mysqli_fetch_assoc($settings_result);
    $site_name = htmlspecialchars($settings['site_name'] ?? 'shopping_online');
    $logo_path = htmlspecialchars($settings['logo_path'] ?? 'images/a1.png');
} else {
    $site_name = 'shopping_online';
    $logo_path = 'images/a1.png';
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
    <title> توصيل دليفري في مدينة البيضاء | القوج للغذائية - 0916939745</title>
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="Algooj For Market - موقعك للتسوق الذكي" />
    <meta property="og:description" content="اكتشف أفضل العروض والمنتجات على Algooj For Market!" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="https://algooj-for-market.infy.uk/" />
    <meta property="og:image" content="https://algooj-for-market.infy.uk/include/algooj.jpg" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
    <meta property="og:site_name" content="Algooj For Market" />
    
    <meta name="keywords" content="غذائية مول ديليفري">
    <meta name="author" content=" الغوج ماركت ">
    
    <!-- Twitter Meta Tags -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://algooj-for-market.infy.uk/">
    <meta property="twitter:title" content="Algooj For Market - موقعك للتسوق الذكي">
    <meta property="twitter:description" content="اكتشف أفضل العروض والمنتجات على Algooj For Market!">
    <meta property="twitter:image" content="https://algooj-for-market.infy.uk/include/algooj.jpg">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Favicon -->
    <link rel="icon" href="https://algooj-for-market.infy.uk/include/algooj.jpg" type="image/jpeg"> 
    
    <style>
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
            margin: 0;
            padding: 0;
        }
        
        /* القائمة الجانبية */
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
            transform: translateX(-100%);
        }
        
        .sidebar.active {
            transform: translateX(0);
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
            border-left: 4px solid transparent;
            transition: all 0.3s;
        }
        
        .sidebar-menu li a:hover,
        .sidebar-menu li a.active {
            background: rgba(255, 255, 255, 0.1);
            border-left: 4px solid var(--primary-color);
        }
        
        .sidebar-menu li a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        /* زر القائمة للجوال */
        .menu-toggle {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
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
            margin-left: 0;
            padding: 20px;
            transition: all 0.3s;
            width: 100%;
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
            border-radius: 50%;
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
              border-radius: 40%;
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
          
        /* أيقونة السلة */
        .cart-icon {
            position: relative;
            display: inline-block;
            font-size: 30px;
            margin-top: 20px;
            position: fixed;
            top: 60px;
            right: 20px;
            z-index: 900;
        }
        
        .cart-count {
            position: absolute;
            top: -10px;
            left: -10px;
            background-color: red;
            color: white;
            border-radius: 50%;
            padding: 5px;
            font-size: 12px;
        }
        
        .cart-icon a, .cart-icon i {
            font-size: 20px;
            color: var(--secondary-color);
        }
        
        /* الخلفية الجميلة مع نص الترحيب */
        .header-background {
            width: 100%;
            height: 300px;
            background-image: url('https://algooj-for-market.infy.uk/include/gooj1.jpg');
            background-size: cover;
            background-position: center;
            margin: 80px 0 30px;
            border-radius: 10px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;

           // background-attachment: fixed; /* تأثير بارالاكس */
   // transition: all 0.5s ease;

        }
        
        .header-background::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.3);
            border-radius: 10px;
        }
        
        .header-content {
            position: relative;
            z-index: 2;
            color: white;
            padding: 20px;
            max-width: 800px;
        }
        
        .header-content h2 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            text-shadow: 2px 2px 5px rgba(0,0,0,0.5);
        }
        
        .header-content p {
            font-size: 1.2rem;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.5);
        }
        
        /* أزرار التواصل */
        .contact-buttons {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin: 20px auto;
            width: 90%;
            max-width: 500px;
        }
        
        .order-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 12px 20px;
            border-radius: 30px;
            color: white;
            text-decoration: none;
            font-size: 16px;
            font-weight: bold;
            transition: all 0.3s ease;
            width: 100%;
            box-sizing: border-box;
        }
        
        /* زر الهاتف */
        .phone-btn {
            background: #3498db;
            box-shadow: 0 4px 8px rgba(52, 152, 219, 0.3);
        }
        
        /* زر واتساب */
        .whatsapp-btn {
            background: #25D366;
            box-shadow: 0 4px 8px rgba(37, 211, 102, 0.3);
        }
        
        /* تأثيرات hover */
        .order-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        
        /* الأيقونات داخل الأزرار */
        .order-btn i {
            margin-left: 8px;
            font-size: 18px;
        }
        
        /* تعديلات للشاشات الكبيرة */
        @media (min-width: 992px) {
            .sidebar {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 250px;
                width: calc(100% - 250px);
            }
            
            .contact-buttons {
                flex-direction: row;
                justify-content: center;
            }
            
            .order-btn {
                width: auto;
                min-width: 200px;
            }
        }
        
        /* تعديلات للشاشات الصغيرة */
        @media (max-width: 768px) {
            .menu-toggle {
                display: block;
            }
            
            .header-background {
                height: 250px;
                margin-top: 60px;
            }
            
            .header-content h2 {
                font-size: 1.8rem;
            }
            
            .header-content p {
                font-size: 1rem;
            }
            
            .recent-products {
                grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
                gap: 10px;
            }
        }
    </style>
</head>

<body>
    <!-- زر القائمة للجوال -->
    <button class="menu-toggle" id="menuToggle">
        <i class="fas fa-bars"></i> القائمة
    </button>

    <!-- القائمة الجانبية -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h3>لوحة التحكم</h3>
        </div>
        <ul class="sidebar-menu">
            <li><a href="../index.php"><i class="fas fa-home"></i> الرئيسية</a></li>
            <li><a href="../Admin/admin.php"><i class="fas fa-cog"></i> لوحة التحكم</a></li>
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
        <i class="fas fa-shopping-cart"></i>
        <span class="cart-count"><?php echo $row_count; ?></span>
    </div>

    <!-- الشعار -->
    <div class="logo">
        <h1><?php echo $site_name; ?></h1>
        <img src="../<?php echo $logo_path; ?>" alt="Logo">
    </div>

    <!-- المحتوى الرئيسي -->
    <div class="main-content" id="mainContent">
        <!-- الخلفية الجميلة مع نص الترحيب -->
        <div class="header-background">
            <div class="header-content">
                <h2>مرحباً بكم في <?php echo $site_name; ?></h2>
                <p>استمتع بالتسوق السريع والمريح من خلال موقع الغوج ماركت<br>لتصلك طلباتك لباب المنزل</p>
            </div>
        </div>

        <!-- أزرار التواصل -->
        <div class="contact-buttons">
            <?php
            // استرجاع رقم الهاتف من الإعدادات
            $phone_query = "SELECT phone_number FROM site_settings WHERE id = 1 LIMIT 1";
            $phone_result = mysqli_query($conn, $phone_query);
            $phone_data = mysqli_fetch_assoc($phone_result);
            $phone_number = $phone_data['phone_number'] ?? '0926969264';
            
            // استرجاع رقم واتساب من الإعدادات
            $whatsapp_query = "SELECT whatsapp FROM site_settings WHERE id = 1 LIMIT 1";
            $whatsapp_result = mysqli_query($conn, $whatsapp_query);
            $whatsapp_data = mysqli_fetch_assoc($whatsapp_result);
            $whatsapp_number = $whatsapp_data['whatsapp'] ?? $phone_number;
            ?>
            
            <!-- زر الهاتف -->
            <a href="tel:<?php echo htmlspecialchars($phone_number); ?>" class="order-btn phone-btn">
                <i class="fas fa-phone-alt"></i>
                <span>اتصل للطلب الآن</span>
            </a>
            
            <!-- زر واتساب -->
            <a href="https://wa.me/<?php echo htmlspecialchars($whatsapp_number); ?>" class="order-btn whatsapp-btn">
                <i class="fab fa-whatsapp"></i>
                <span>اطلب عبر واتساب</span>
            </a>
        </div>

        <!-- قسم المنتجات المضافة حديثاً -->
        <div class="recent-products">
            <h4>المضافة حديثاً</h4>
            <?php
            if ($product_result) {
                while ($product_row = mysqli_fetch_assoc($product_result)) {
                    echo '<a href="details1.php?id=' . htmlspecialchars($product_row['id']) . '" class="product-circle">';
                    echo '<img src="../' . htmlspecialchars($product_row['image']) . '" alt="' . htmlspecialchars($product_row['name']) . '">';
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