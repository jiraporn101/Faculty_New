<?php
// เช็คก่อนว่ามี session เปิดอยู่หรือยัง
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ถ้าไม่มีการล็อกอิน (ไม่มี admin_id ใน session)
if (!isset($_SESSION['admin_id'])) {
    // ให้ดีดไปที่หน้า login โดยใช้ URL เต็มของระบบคุณ
    header("Location: https://hosting.udru.ac.th/~it67040233101/Show_cat2/admin/login.php");
    exit;
}
?>