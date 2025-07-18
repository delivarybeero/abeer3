<?php
include "../include/connection.php";
session_start();

if(!isset($_SESSION['EMAIL'])) {
    header('location:../index.php');
    exit;
}

if(isset($_GET['confirm']) && $_GET['confirm'] == 'true') {
    $sql = "TRUNCATE TABLE commint";
    
    if(mysqli_query($conn, $sql)) {
        echo "<script>
            alert('تم حذف جميع التعليقات بنجاح');
            window.location.href='admianpanel.php';
        </script>";
    } else {
        echo "<script>
            alert('حدث خطأ أثناء حذف التعليقات: " . mysqli_error($conn) . "');
            window.location.href='admianpanel.php';
        </script>";
    }
    exit;
}
?>

<script>
function confirmDelete() {
    if(confirm('هل أنت متأكد أنك تريد حذف جميع التعليقات؟ هذا الإجراء لا يمكن التراجع عنه!')) {
        window.location.href = 'delete_comments.php?confirm=true';
    }
}
</script>

<div style="text-align: center; margin-top: 50px;">
    <button onclick="confirmDelete()" style="padding: 10px 20px; background: #e74c3c; color: white; border: none; border-radius: 4px; cursor: pointer;">
        حذف جميع التعليقات
    </button>
    <a href="admianpanel.php" style="padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 4px; margin-right: 10px;">
        العودة للوحة التحكم
    </a>
</div>