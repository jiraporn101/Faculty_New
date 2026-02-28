<?php
$host = "localhost";

/* ======================
   ตรวจว่าเป็น localhost ไหม
====================== */
$isLocal = in_array($_SERVER['HTTP_HOST'], [
    'localhost',
    '127.0.0.1',
    '::1'
]);

if ($isLocal) {
    // ===== LOCALHOST =====
    $user = "root";
    $pass = "";
    $db   = "db_67";
    $port = 3307;   // XAMPP ของเธอใช้ 3307
} else {
    // ===== HOSTING =====
    $user = "it67040233101";
    $pass = "S9Y0Z6Z2";
    $db   = "it67040233101";
    $port = 3306;
}

/* ======================
   CONNECT
====================== */
$conn = mysqli_connect($host, $user, $pass, $db, $port);

if (!$conn) {
    die("เชื่อมต่อฐานข้อมูลไม่สำเร็จ: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");
?>
