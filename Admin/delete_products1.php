<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "../include/connection.php";
session_start();

// التحقق من صلاحيات المدير
if (!isset($_SESSION['EMAIL'])) {
    header("Location: admin.php");
    exit();
}

// تعريف المسارات
define('UPLOADS_DIR', '../uploads/img/');
define('CART_FILE', '../cart1');

// معالجة الحذف
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    if (strtolower($_POST['confirm']) === 'نعم') {
        $results = [
            'products' => ['success' => false, 'message' => '', 'count' => 0],
            'images' => ['success' => false, 'message' => '', 'count' => 0],
            'cart' => ['success' => false, 'message' => '']
        ];

        try {
            // بدء المعاملة
            $conn->autocommit(false); // تعطيل الإرسال التلقائي لبدء المعاملة

            // 1. حذف المنتجات
            if (isset($_POST['delete_products'])) {
                $stmt = $conn->prepare("DELETE FROM products");
                $stmt->execute();
                $results['products']['count'] = $stmt->rowCount();
                $results['products']['success'] = true;
            }

            // 2. حذف الصور
            if (isset($_POST['delete_images'])) {
                $deleted_files = 0;
                if (is_dir(UPLOADS_DIR) && is_writable(UPLOADS_DIR)) {
                    $files = glob(UPLOADS_DIR . '*');
                    foreach ($files as $file) {
                        if (is_file($file) && is_writable($file)) {
                            if (unlink($file)) {
                                $deleted_files++;
                            }
                        }
                    }
                    $results['images']['count'] = $deleted_files;
                    $results['images']['success'] = true;
                } else {
                    $results['images']['message'] = "المجلد غير قابل للكتابة أو غير موجود";
                }
            }

            // 3. تفريغ السلة (ملف cart1)
            if (isset($_POST['delete_cart'])) {
                if (file_exists(CART_FILE) && is_writable(CART_FILE)) {
                    if (file_put_contents(CART_FILE, '') !== false) {
                        $results['cart']['success'] = true;
                    } else {
                        $results['cart']['message'] = "فشل في كتابة الملف";
                    }
                } elseif (!file_exists(CART_FILE)) {
                    $results['cart']['success'] = true;
                    $results['cart']['message'] = "الملف غير موجود (تم اعتبار العملية ناجحة)";
                } else {
                    $results['cart']['message'] = "الملف غير قابل للكتابة";
                }
            }

            // تأكيد جميع العمليات
            $conn->commit();

            // تحضير رسالة النجاح
            $success_message = "نتائج العمليات:<br><ul>";
            
            if ($results['products']['success']) {
                $success_message .= "<li>تم حذف {$results['products']['count']} منتج</li>";
            }
            
            if ($results['images']['success']) {
                $success_message .= "<li>تم حذف {$results['images']['count']} صورة</li>";
            } elseif (!empty($results['images']['message'])) {
                $success_message .= "<li>حذف الصور: {$results['images']['message']}</li>";
            }
            
            if ($results['cart']['success']) {
                $success_message .= "<li>تم تفريغ سلة المشتريات بنجاح</li>";
                if (!empty($results['cart']['message'])) {
                    $success_message .= " ({$results['cart']['message']})";
                }
            } elseif (!empty($results['cart']['message'])) {
                $success_message .= "<li>تفريغ السلة: {$results['cart']['message']}</li>";
            }
            
            $success_message .= "</ul>";

            $_SESSION['success_message'] = $success_message;
            header("Location: admianpanel.php?success=1");
            exit();

        } catch (Exception $e) {
            $conn->rollBack();
            $_SESSION['error_message'] = "حدث خطأ: " . $e->getMessage();
            header("Location: delete_products.php?error=1");
            exit();
        }
    } else {
        $_SESSION['warning_message'] = "تم إلغاء العملية";
        header("Location: delete_products.php?warning=1");
        exit();
    }
}
?>
<!DOCTYPE html>
<html dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>حذف جميع المنتجات</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #f8f9fa;
        }
        .card {
            margin-top: 50px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .alert {
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h4 class="mb-0">حذف جميع المنتجات</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger">
                                <?= $_SESSION['error_message'] ?? 'حدث خطأ غير معروف' ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($_GET['warning'])): ?>
                            <div class="alert alert-warning">
                                <?= $_SESSION['warning_message'] ?? 'تحذير' ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="alert alert-danger text-center">
                            <h5><i class="fas fa-exclamation-triangle"></i> تحذير هام!</h5>
                            <p>هذه العملية لا يمكن التراجع عنها وسيتم حذف:</p>
                            <ul class="text-right">
                                <li>جميع المنتجات من قاعدة البيانات</li>
                                <li>جميع الصور من مجلد uploads/img</li>
                                <li>جميع البيانات المرتبطة بالمنتجات</li>
                            </ul>
                        </div>
                        
                        <form method="post" class="mt-4">
                            <div class="form-group text-center">
                                <label for="confirm" class="h5">لتأكيد الحذف، اكتب "<span class="text-danger">نعم</span>" في المربع التالي:</label>
                                <input type="text" id="confirm" name="confirm" class="form-control form-control-lg text-center mt-3" required>
                            </div>
                            
                            <div class="d-flex justify-content-center mt-4">
                                <button type="submit" class="btn btn-danger btn-lg mx-2">
                                    <i class="fas fa-trash-alt"></i> حذف الكل
                                </button>
                                <a href="admianpanel.php" class="btn btn-secondary btn-lg mx-2">
                                    <i class="fas fa-times"></i> إلغاء
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>