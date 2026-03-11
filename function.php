<?php
function uploadImage($file) {
    if (empty($file['name'])) return null;
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allow = ['jpg', 'jpeg', 'png'];
    
    if (!in_array($ext, $allow)) return null;
    
    $new_name = time() . "_" . rand(1000, 9999) . "." . $ext;
    
    // --- แก้ไขจุดนี้ ---
    // ลองใช้ __DIR__ เพื่ออ้างอิง path จากตำแหน่งไฟล์ function.php เอง
    $path = __DIR__ . "/uploads/" . $new_name; 
    
    // ตรวจสอบว่าฟังก์ชันทำงานหรือไม่
    if (move_uploaded_file($file['tmp_name'], $path)) {
        return $new_name;
    }
    else {
    // ถ้าไม่สำเร็จ ให้แสดง error
    echo "Error: ไม่สามารถย้ายไฟล์ได้. ตรวจสอบ Permission และ Path.";
    return null;
    }
}
?>