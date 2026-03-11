<?php
// admin/news_delete.php
require "../config/db.php"; // ปรับ path ให้ตรงกับที่เชื่อมต่อ DB ของคุณ
require "../auth/check.php"; // ตรวจสอบสิทธิ์ Admin

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    // 1. ดึงข้อมูลรูปภาพเก่าออกมาเพื่อลบไฟล์จริงออกจาก Folder
    $stmt = $conn->prepare("SELECT image_name FROM fn_news_images WHERE news_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // ลบไฟล์รูปภาพออกจากโฟลเดอร์ uploads/
    while ($row = $result->fetch_assoc()) {
        $file_path = "../uploads/" . $row['image_name'];
        if (file_exists($file_path)) {
            unlink($file_path); // ลบไฟล์จริง
        }
    }

    // 2. ลบข้อมูลรูปภาพออกจากฐานข้อมูล (ตาราง fn_news_images)
    $stmt = $conn->prepare("DELETE FROM fn_news_images WHERE news_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // 3. ลบข้อมูลข่าวหลักออกจากฐานข้อมูล (ตาราง fn_news)
    $stmt = $conn->prepare("DELETE FROM fn_news WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // ส่งกลับไปหน้าแสดงรายการข่าว
    header("Location: news_list.php?msg=deleted");
    exit();
} else {
    // ถ้าไม่มี ID ให้ส่งกลับไปหน้าแสดงรายการข่าว
    header("Location: news_list.php");
    exit();
}
?>