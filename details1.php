<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


ob_start();
session_start();
require_once('./include/connection.php');

// التحقق من وجود معرف المنتج
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}
$id = intval($_GET['id']);

// جلب بيانات المنتج
$query = $conn->prepare("SELECT * FROM products WHERE id = ?");
$query->bind_param("i", $id);
$query->execute();
$result = $query->get_result();

if ($result->num_rows === 0) {
    header("Location: index.php");
    exit;
}
$product = $result->fetch_assoc();

// إضافة تعليق
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_comment'])) {
    $comment = trim($_POST['comment']);
    if (!empty($comment)) {
        $insert_query = $conn->prepare("INSERT INTO commint (comment, product_id) VALUES (?, ?)");
        $insert_query->bind_param("si", $comment, $id);
        $insert_query->execute();
        header("Location: details.php?id=$id");
        exit;
    } else {
        echo '<script>alert("الرجاء كتابة التعليق لأن الحقل فارغ");</script>';
    }
}

// جلب التعليقات الخاصة بالمنتج فقط
$comments_query = $conn->prepare("SELECT * FROM commint WHERE product_id = ? ORDER BY id DESC");
$comments_query->bind_param("i", $id);
$comments_query->execute();
$comments_result = $comments_query->get_result();

// جلب المنتجات الحديثة باستثناء المنتج الحالي
$recent_products_query = $conn->prepare("SELECT * FROM products WHERE id != ? ORDER BY RAND() LIMIT 5");
$recent_products_query->bind_param("i", $id);
$recent_products_query->execute();
$recent_products_result = $recent_products_query->get_result();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>تفاصيل المنتج</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f9f9f9; margin: 0; padding: 0; }
        main { display: flex; flex-wrap: wrap; direction: rtl; }
        .container { width: 90%; margin: 20px auto; background: #fff; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); padding: 20px; }
        .product_img img { width: 400px; height: 400px; margin: 20px auto; display: block; border-radius: 8px; box-shadow: 0 5px 10px rgba(0,0,0,0.1);}
        .product_info { text-align: center; margin-top: 20px; }
        .product_info h1, .product_info h2, .product_info h3, .product_info p { margin: 10px 0; }
        .recently_added { margin-top: 30px; }
        .recently_added h4 { text-align: center; margin-bottom: 20px; }
        .recently_added .product { display: inline-block; width: 30%; margin: 10px; text-align: center; }
        .recently_added .product img { width: 100px; height: 100px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); margin-bottom: 10px; }
        .comment_info { margin-top: 30px; padding: 20px; background-color: #f1f1f1; border-radius: 10px;}
        .comment_info h5 { text-align: center; margin-bottom: 10px;}
        textarea { width: 100%; height: 70px; margin: 10px 0; padding: 10px; border-radius: 8px; border: 1px solid #ddd; }
        .add_comment { display: block; width: 100%; padding: 10px; background-color: #4CAF50; color: white; border: none; border-radius: 8px; cursor: pointer; }
        .add_comment:hover { background-color: #45a049; }
        .comments .comment { margin: 10px 0; padding: 10px; background-color: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);}
    </style>
</head>
<body>
<main>
    <div class="container">
        <div class="product_img">
        <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">        </div>
        <div class="product_info">
            <h1><?php echo htmlspecialchars($product['name']); ?></h1>
            <h2><?php echo htmlspecialchars($product['price']); ?>$</h2>
            <h3>الحجم المتوفر: <?php echo htmlspecialchars($product['prosize']); ?></h3>
            <p><?php echo htmlspecialchars($product['description']); ?></p>
        </div>
    </div>
    <div class="container recently_added">
        <h4>منتجات مضافة حديثاً</h4>




        <?php while ($recent_product = $recent_products_result->fetch_assoc()) { ?>
    <div class="product">
        <a href="details1.php?id=<?php echo $recent_product['id']; ?>">
            <img src="<?php echo htmlspecialchars(str_replace('../', '', $recent_product['image'])); ?>" alt="<?php echo htmlspecialchars($recent_product['name']); ?>">
            <p><?php echo htmlspecialchars($recent_product['name']); ?></p>
        </a>
    </div>
<?php } ?>


    </div>
    <div class="container comment_info">
        <h5>هل تريد تقييم هذا المنتج؟</h5>
        <form action="" method="POST">
            <textarea name="comment" placeholder="قيّم هذا المنتج من فضلك"></textarea>
            <button type="submit" class="add_comment" name="add_comment">إرسال</button>
        </form>
        <h5>تقييم العملاء</h5>
        <div class="comments">
            <?php if ($comments_result->num_rows > 0) {
                while ($comment = $comments_result->fetch_assoc()) { ?>
                    <div class="comment"><?php echo htmlspecialchars($comment['comment']); ?></div>
                <?php }
            } else { ?>
                <p>لا توجد تعليقات بعد.</p>
            <?php } ?>
        </div>
    </div>
</main>
<a href="index.php" style="padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 4px; display: inline-block; margin: 20px;">
    العودة إلى المتجر
</a>
</body>
</html>
<?php ob_end_flush(); ?>