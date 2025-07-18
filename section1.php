<?php
include("./include/connection.php");
include("file/header5.php");
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
   <div class="contianer_card" >

<?php
$section = $_GET['section'];
$query = "SELECT * FROM products WHERE prosection='$section'";
$result = mysqli_query($conn, $query);
if(mysqli_num_rows($result) > 0){
  while($row = mysqli_fetch_assoc($result)){
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
   <input type="number" value="1" name="" id="quantity" min="1" max="7" style="width:40px"></input>
     <button  class="qy_count-add" >+</button>   
  </div><!-- نهاية دف الكمية -->
  
  <!-- دف اضف للسلة  --> 
  
  <div  class=""><a href="">
  <button  class="add-to-cart " type="submit" name="" style="margin:20px;">اضف الي السلة<i class="fa solid fa-cart-plus"></i></button>
  </a>
  </div>
  
 </div>

<?php
  }
} else {
    echo '<br><br><div class="card1">no result </div>';
}
?>
 </div>

</main> 
</body>
</html>
 <?php
include "file/footer.php"
?>
