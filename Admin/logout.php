<?php
//start session
session_start();
//delete for all session
session_unset();
//kill session
session_destroy();
header('location:admin.php')
?>