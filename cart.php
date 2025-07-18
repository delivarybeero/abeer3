<?php
session_start();
include ("include/connection.php");
include ("file/header.php");
?>
<?php
$add=$_POST['add'];
if (isset($add)){
$ID=$_POST['id'];
$productname=$_POST['h_name'];
$productprice=$_POST['h_price'];
$productimg=$_POST['h_img'];
$productquantity=$_POST['quantity'];
$add_cart="SELECT *FROM cart WHERE name='$productname'  ";
$result=mysqli_query($conn,$add_cart);
if(mysqli_num_rows($result)>0){
 echo '<script>alert("المنتج مضاف مسبقا لايمكن اضافة ثانية ,يمكنك تعين الكمية") </script>';
}else{

$insert_cart="INSERT INTO cart (name,price,img,quantity)  VALUES ('$productname','$productprice','$productimg','$productquantity')";
if(mysqli_query($conn,$insert_cart)===TRUE){

    echo '<script>alert("تمتت اضافة المنتج الى سلتك
") </script>';

}else{

    echo '<script>alert("لم يتم اضافة المنتج حدث خطأ 
") </script>';

}

}


}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سلة المشتريات </title>
</head>


<style>
*{margin:0;
    padding:0;
}
h3{

    font-family:arial,sans-serif;
     
    color:black;

}body{
    font-family:arial,sans-serif;
background-color:#fff;
color:#333; 
}
.cart_contianer4{
    direction:rtl;
    width:80%;
    margin:50px auto;
    background-color:#fff;
    padding:20px;
    box-shadow:rgba(0,0,0,0,.2)

}
.connt_head{
    padding:5px;
    width:100%;
    height:100px;
    background-color:rgba(168,168,236);
    margin:10px auto;

}
.connt_head img{
width:70px;
height:70px;
float:left;
border-radius:20px;
}
.connt_head h1{
  float:left;
  margin:20px;  
}
.cart_table{
width:100%;
border-collapse:collapse;
margin-bottom:20px;
}

.cart_table th ,td{

    padding:2px;
    text-align:center;
    border:1px solid #ddd;
}
.cart_table th {
    background-color:#d3d8e4;
}
.cart_table img{
    width:10%;
    height:10%;
margin:0px;}
 .cart_table input{width: 50px;
    padding:5px;
    text-align:center;
    }
.remove{background-color:#0a79a5;
color:white;
border:none;
padding:10px 10px;
margin-left:10px;

}
.remove:hover{
background-color:rgb(4,59,110)

}
.cart_table h6{
    color:black;
    font-size:large;


}
.cart_total button{
    padding:10px 40px;
    transition:transform 0.3s ease;

}
.cart_total button:hover{
transform:scale(1.2);
}
</style>
<body>
   <div class="cart_contianer4">
    <div class="connt_head">
        <img src="images/a1.png">
        <h1>programmed.k</h1>
</div>
<!-----start table---->
<table class="cart_table">
    <tr>
        <th> صورة المنتج</th>
        <th> رقم المنتج</th>
        <th> اسم المنتج</th>
        <th> الكمية</th>
        <th>السعر</th>

        <th>الاجمالي</th>
        <th> حذف</th>
        <th> تعديل</th>


</tr>
<?php
$query="SELECT * FROM cart";
$result=mysqli_query($conn,$query);
$total=0;
if (mysqli_num_rows($result)>0){
while($row=mysqli_fetch_assoc($result)){





?>

<tr>
<td><img src="uploads/img//<?php echo $row['img'];?>" ></img></td>
<td><h3><?php echo $row['id'];?></h3></td>
<td><h3><?php echo $row['name'];?></h3></td>
<td><input  value="<?php echo $row['quantity'];?>"></td>

<td><h3><?php echo $row['price'];?></h3></td>
<td><h3>$<?php echo number_format($row['quantity']*$row['price'],2);?></h3></td>
<td><a href="" ><button class="remove">حذف<i class="fa-solid fa-trash-can"></i></button></td>
<td><a href="" ><button class="remove">تعديل<i class="fa-solid fa-pen-to-square"></i></button></td>
<?php 
$total +=$row['quantity']*$row['price'];


}}
?>



</tr>


</table>
<div class="cart_total">
    <h6> <?php echo  number_format($total,2)." $"." = ";?><span id="total">المجموع
</span></h6>
<button type ="submit"  class="remove"><a href="orders.php"><h2>اتمام الطلب
</h2></a></button>
</div>




</div>
</body>
</html>