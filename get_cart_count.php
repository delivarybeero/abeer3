<?php
   session_start();
   include("./include/connection.php");

   $total = 0;
   if (isset($_SESSION['cart_items_count'])) {
       $total = $_SESSION['cart_items_count'];
   } elseif ($conn) {
       $stmt = $conn->prepare("SELECT SUM(quantity) FROM cart1 WHERE session_id = ?");
       $stmt->bind_param("s", session_id());
       $stmt->execute();
       $result = $stmt->get_result();
       $total = $result->fetch_row()[0] ?? 0;
   }

   header('Content-Type: application/json');
   echo json_encode(['count' => $total]);
   ?>