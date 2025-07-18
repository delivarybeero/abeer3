<?php
// ملف include/connection.php
$host = 'sql307.infinityfree.com';
$dbname = 'if0_38246983_mydatabase';
$username = 'if0_38246983';
$password = 'S2bU5A0b7bZo';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("فشل الاتصال بقاعدة البيانات: " . $e->getMessage());
}
?>