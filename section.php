<?php
include("./include/connection.php");
include("file/header1.php");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الاقسام</title>
</head>
<body>
   <main>
<?php
$section=$_GET['section'];
$query="SELECT * FROM products WHERE prosection='$section'";
$result=mysqli_query($conn,$query);
if(mysqli_num_rows($result)>o){
  while($row=mysqli_fetch_assoc($result)){
?>
 <div class="product_card">
       <!-- الصورة -->
       <div class="card-img" ><a href="detalis.php?id=<?php echo $row['id']?>">
           
           <img src="uploads/img//<?php echo $row['image'];?>">
           <span class="unvailable"><?php echo $row['prounv'];?></span>

           <a href="" ></a>
       </div>
         <div class="product_section">
           
           <a href="detalis.php?id=<?php echo $row['id']?>"><?php echo $row['prosection']?></a>
       </div>
       
  <div class="product_name">
      <a href="detalis.php?id=<?php echo $row['id']?>"><?php echo $row['name'];?>
      </a>
  </div>
  
  <div class="product_price">
      <a href="detalis.php?id=<?php echo $row['id']?>">   <?php echo $row['price'];?>  &nbsp;  price             </a>
  </div>
  
  <div  class="product_discription"><a href="detalis.php?id=<?php echo $row['id']?>"><i class="fa-solid fa-eye"> </i></a>اضغط هنا للمزيد من التفاصيل</div>
  
  <div class="qy_input">
      
   <button  class="qy_count-mins" >-</button>   
   <input type="numbet" value="1" name="" id="quantity" min="1" max="7" style="width    :40px"></input>
     <button  class="qy_count-add" >+</button>   
  </div><!-- نهاية دف الكمية -->
  
  <!-- دف اضف للسلة  --> 
  <form action="add_to_cart.php" method="post" class="add-to-cart-form">
                    <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                    <input type="hidden" name="name" value="<?php echo htmlspecialchars($row['name']); ?>">
                    <input type="hidden" name="price" value="<?php echo $row['price']; ?>">
                    <input type="hidden" name="image" value="<?php echo $row['image']; ?>">
                    
                    <div class="quantity-control">
                        <button type="button" class="quantity-minus">-</button>
                        <input type="number" name="quantity" value="1" min="1" max="10" class="quantity-input">
                        <button type="button" class="quantity-plus">+</button>
                    </div>
                    
                    <button type="submit" name="add_to_cart" class="add-to-cart-btn" 
                            <?php echo $row['prounv'] ? 'disabled' : ''; ?>>
                        <i class="fas fa-cart-plus"></i> أضف للسلة
                    </button>
                </form>
  
          </div>
  </div>
 </div>

<?php

  }


}else{

    echo'<br><br><div class="card1">no result </div>';
}
?>

</main> 
</body>
</html>
<?php

       
include "file/footer.php"
?>

<style>
    /* تنسيقات عامة */
    main {
        padding: 20px;
        max-width: 1200px;
        margin: 0 auto;
    }
    
    .products-header {
        text-align: center;
        margin-bottom: 30px;
    }
    
    .products-header h2 {
        color: #2c3e50;
        font-size: 28px;
    }
    
    /* شبكة المنتجات */
    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 25px;
    }
    
    /* بطاقة المنتج */
    .product-card {
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        overflow: hidden;
        transition: transform 0.3s, box-shadow 0.3s;
    }
    
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    /* صورة المنتج */
    .product-image {
        position: relative;
        height: 200px;
        overflow: hidden;
    }
    
    .product-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s;
    }
    
    .product-card:hover .product-image img {
        transform: scale(1.05);
    }
    
    .availability-label {
        position: absolute;
        top: 10px;
        right: 10px;
        background: #e74c3c;
        color: white;
        padding: 3px 8px;
        border-radius: 3px;
        font-size: 12px;
    }
    
    /* معلومات المنتج */
    .product-info {
        padding: 15px;
    }
    
    .product-category {
        color: #7f8c8d;
        font-size: 14px;
        margin-bottom: 5px;
    }
    
    .product-name {
        margin: 10px 0;
        font-size: 18px;
    }
    
    .product-name a {
        color: #2c3e50;
        text-decoration: none;
    }
    
    .product-price {
        color: #e74c3c;
        font-weight: bold;
        font-size: 18px;
        margin: 10px 0;
    }
    
    /* عناصر التحكم بالكمية */
    .quantity-control {
        display: flex;
        margin: 15px 0;
    }
    
    .quantity-control button {
        width: 30px;
        background: #f1f1f1;
        border: none;
        cursor: pointer;
        font-size: 16px;
    }
    
    .quantity-input {
        width: 50px;
        text-align: center;
        border: 1px solid #ddd;
    }
    
    /* زر الإضافة للسلة */
    .add-to-cart-btn {
        width: 100%;
        padding: 10px;
        background: #27ae60;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: background 0.3s;
    }
    
    .add-to-cart-btn:hover {
        background: #2ecc71;
    }
    
    .add-to-cart-btn:disabled {
        background: #95a5a6;
        cursor: not-allowed;
    }
    
    .add-to-cart-btn i {
        margin-left: 5px;
    }
</style>

<script>
    // التحكم في الكمية
    document.querySelectorAll('.quantity-plus').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.parentNode.querySelector('.quantity-input');
            if (parseInt(input.value) < 10) {
                input.value = parseInt(input.value) + 1;
            }
        });
    });
    
    document.querySelectorAll('.quantity-minus').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.parentNode.querySelector('.quantity-input');
            if (parseInt(input.value) > 1) {
                input.value = parseInt(input.value) - 1;
            }
        });
    });
</script>

