<?php
// เชื่อมต่อฐานข้อมูล (เพื่อใช้ session_start() ที่อยู่ใน db.php)
require_once __DIR__ . "/../config/db.php";

// เช็คว่ามี session ของ user_id หรือไม่
if (!isset($_SESSION['user_id'])) {
    // ถ้าไม่มีให้ส่งกลับไปหน้าล็อกอิน
    header("Location: ../auth/login.php");
    exit();
}
?>