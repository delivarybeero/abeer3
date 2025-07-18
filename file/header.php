<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">  
    
    
    <link rel="stylesheet" href="style.css">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>header</title>
</head>

<body>
<div  class="contianer">

<div class="section">

<nav>

          <ul>
       <li> <a href="../index.php">الرئيسية</a></li>
          
       <li> <a href="../Admin/admin.php">admin panal</a></li>

<?php
$query="SELECT *FROM section ";
$result=mysqli_query($conn,$query);
while ($row=mysqli_fetch_assoc($result)){

?>           <li><a href="section1.php?section=<?php echo $row['section_name'];?>">
<?php echo $row['section_name'];

?>

</a></li>

<?php


}?>
      </ul>
</nav>
</div><!-- نهاية سكشن -->
<!--شريط السوشيال ميدا -->  
</div><!-- نهاية كونتينر-->

   <div class="contianer2"> 
 <div class="last-bost"><!--المضاف اخيرا  -->
  <h4>المضاف اخيرا</h4>      
  <?php
$query="SELECT * FROM products  ORDER BY ID DESC  LIMIT 5";
$result=mysqli_query($conn,$query);
while ($row=mysqli_fetch_assoc($result)){
//print_r($row);





?>
  <a href="details.php?id=<?php echo $row['id'];?>">
   <span class="span-img">
    <img src="uploads/img//<?php echo $row['image'];?>" width="30">
  </span> </a>
   
<?php
}
?>    
     
</div><!--المضاف اخيرا  نهاية-->


    </div>

    <div class="header">
        <div class="container1">
       <div class="logo">
      <h1>  shopping_online</h1>   
      <img src="../images/a1.png"></img>  
     </div>
     <div    class="cart-icon">

   <i  class="fas fa-shopping-cart" style="font-size:20px" ></i>
    <span class="cart-count">3</span> <!-- عدد المشتريات -->
   
  
          <a href="sinup.php"><i class="fa-solid fa-user" style="font-size:20px;color:#080808;"></i></a>
   </div>
    </div>

     <!---searsh 
       <div class="searsh">
           <div class="searsh_bar">  
           <form action="search.php"    method="get">
               <input type="text"  class="searsh_input" name="search" placeholder="ادخل كلملة البحث"></input>
               <button class="button_searsh" name="btn-searsh">بحث</button>
           </form>
       </div>
      </div>  


      
      -end  searsh-->
    </div> 
    
    
    
    
    

</body>
</html>