<?php
require "../config/db.php";
require "../auth/check.php";

// ตรวจสอบว่ามีการส่ง ID ของรูปภาพมาหรือไม่
if (isset($_GET['id'])) {
    $image_id = (int)$_GET['id'];

    // 1. ดึงชื่อไฟล์รูปมาก่อนเพื่อลบไฟล์จริงออกจาก Folder
    $stmt = $conn->prepare("SELECT image_name, news_id FROM fn_news_images WHERE id = ?");
    $stmt->bind_param("i", $image_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $image = $result->fetch_assoc();

    if ($image) {
        // ลบไฟล์รูปจริงออกจากโฟลเดอร์ uploads
        $file_path = __DIR__ . "/../uploads/" . $image['image_name'];
        if (file_exists($file_path)) {
            unlink($file_path); // ลบไฟล์
        }

        // 2. ลบข้อมูลชื่อไฟล์ออกจากฐานข้อมูล
        $stmt = $conn->prepare("DELETE FROM fn_news_images WHERE id = ?");
        $stmt->bind_param("i", $image_id);
        $stmt->execute();
        
        // ส่งกลับไปยังหน้าแก้ไขข่าวเดิม
        header("Location: news_form.php?id=" . $image['news_id']);
        exit();
    }
}

// หากไม่มี ID หรือเกิดข้อผิดพลาด ให้กลับไปหน้าหลัก
header("Location: news_list.php");
exit();
?>