<?php
include("./include/connection.php");
include "file/header1.php";
?>

<?php

if (isset($_GET['btn-searsh'])) {
    $search = $_GET['search'];
    $query = "SELECT * FROM products WHERE description LIKE '%$search%' OR name LIKE '%$search%' OR id LIKE '%$search%' OR price LIKE '%$search%'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {

            echo '
            <div class="contianer_card">
            <div class="product_card">
                <!-- الصورة -->
                <div class="card-img">

     
                    <img src="uploads/img/' . $row['image'] . '">


                    <span class="unvailable">' . $row['prounv'] . '</span>
                    <a href=""></a>
                </div>
                <div class="product_section">
                    <a href="">' . $row['prosection'] . '</a>
                </div>
                <div class="product_name">
                    <a href="">' . $row['name'] . '</a>
                </div>
                <div class="product_price">
                    <a href="">' . $row['price'] . ' &nbsp; price</a>
                </div>
                <div class="product_discription"><a href=""><i class="fa-solid fa-eye"> ' . $row['description'] . '</i></a> اضغط هنا للمزيد من التفاصيل</div>
                <div class="qy_input">
                    <button class="qy_count-mins">-</button>
                    <input type="number" value="1" id="quantity" min="1" max="7" style="width: 40px;">
                    <button class="qy_count-add">+</button>
                </div><!-- نهاية دف الكمية -->
                <!-- دف اضف للسلة -->
                <div><a href="">
                    <button class="add-to-cart" type="submit" style="margin: 20px;">اضف الي السلة <i class="fa solid fa-cart-plus"></i></button>
                </a></div>
            </div>
            ';

        }


    } else {
        echo '<p>No products found.</p>';
    }
}
?>
        </div>

<?php


include "file/footer.php";
?>