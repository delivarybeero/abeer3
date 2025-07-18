<?php
session_start();
include("../include/connection.php");
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المنتجات</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            padding: 20px;
            overflow-x: auto;
        }
        
        h1 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background-color: #3498db;
            color: white;
            font-weight: bold;
        }
        
        tr:hover {
            background-color: #f9f9f9;
        }
        
        img {
            max-width: 80px;
            max-height: 80px;
            border-radius: 4px;
            object-fit: cover;
        }
        
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        
        .delet {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .delet:hover {
            background-color: #c0392b;
        }
        
        .UPDATE {
            background-color: #2ecc71;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .UPDATE:hover {
            background-color: #27ae60;
        }
        
        .navigation {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        
        .nav-btn {
            padding: 12px 25px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            font-weight: bold;
        }
        
        .nav-btn:hover {
            background: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
        }
        
        .success-message {
            background-color: #2ecc71;
            color: white;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
            display: none;
        }
        
        @media (max-width: 768px) {
            th, td {
                padding: 8px 10px;
                font-size: 14px;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-box-open"></i> إدارة المنتجات</h1>
        
        <?php
        // التحقق من وجود معرّف المنتج وحذفه
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
            $id = $_POST['id'];
            $query = "DELETE FROM products WHERE id='".$id."'";
            $delete = mysqli_query($conn, $query);
            
            if ($delete) {
                echo '<div class="success-message" id="successMsg">
                        <i class="fas fa-check-circle"></i> تم حذف المنتج بنجاح
                      </div>
                      <script>
                        setTimeout(() => {
                            document.getElementById("successMsg").style.display = "none";
                        }, 3000);
                      </script>';
            } else {
                echo '<div class="success-message" style="background:#e74c3c">
                        <i class="fas fa-exclamation-circle"></i> خطأ: ' . mysqli_error($conn) . '
                      </div>';
            }
        }
        ?>
        
        <table>
            <thead>
                <tr>
                    <th>الرقم</th>
                    <th>الصورة</th>
                    <th>العنوان</th>
                    <th>السعر</th>
                    <th>الحجم</th>
                    <th>الكمية</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
    <?php
    $query = "SELECT * FROM products";
    $result = mysqli_query($conn, $query);
    if (!$result) {
        echo '<script>alert("Error: ' . mysqli_error($conn) . '");</script>';
        die();
    }
    while ($row = mysqli_fetch_assoc($result)) {
    ?>
    <tr>
        <td><?php echo $row['id']; ?></td>
        <td>
            <img 
                src="../<?php echo htmlspecialchars($row['image']); ?>" 
                alt="صورة المنتج"
                onerror="this.src='../images/default-product.jpg';">
        </td>
        <td><?php echo $row['name']; ?></td>
        <td><?php echo $row['price']; ?> د.ل</td>
        <td><?php echo $row['prosize']; ?></td>
        <td><?php echo $row['quantity']; ?></td>
        <td class="action-buttons">
            <form method="POST" action="product.php" onsubmit="return confirm('هل أنت متأكد من حذف هذا المنتج؟');">
                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                <button type="submit" class="delet"><i class="fas fa-trash"></i> حذف</button>
            </form>
            <a href="update.php?id=<?php echo $row['id'];?>">
                <button type="button" class="UPDATE"><i class="fas fa-edit"></i> تعديل</button>
            </a>
        </td>
    </tr>
    <?php } ?>
</tbody>        </table>
        
        <div class="navigation">
            <a href="../index.php" class="nav-btn">
                <i class="fas fa-store"></i> العودة إلى المتجر
            </a>
            <a href="admianpanel.php" class="nav-btn">
                <i class="fas fa-tachometer-alt"></i> لوحة التحكم
            </a>
            <a href="add_product.php" class="nav-btn" style="background:#2ecc71">
                <i class="fas fa-plus"></i> إضافة منتج جديد
            </a>
        </div>
    </div>
    
    <script>
        // إظهار رسالة النجاح لمدة 3 ثواني
        if (document.querySelector('.success-message')) {
            setTimeout(() => {
                document.querySelector('.success-message').style.display = 'none';
            }, 3000);
        }
    </script>
</body>
</html>