<?php
require_once "db_connect.php";
header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ไม่พบ ID']);
    exit;
}

$id = intval($_GET['id']);

// 1. ดึงชื่อไฟล์มาเพื่อลบไฟล์จริงในเครื่อง
$res = $conn->query("SELECT image FROM cat_images WHERE id = $id");
$img = $res->fetch_assoc();

if ($img) {
    $path = "uploads/" . $img['image'];
    if (file_exists($path)) { unlink($path); } // ลบไฟล์ในโฟลเดอร์ uploads

    // 2. ลบข้อมูลใน Database
    $sql = "DELETE FROM cat_images WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'ลบข้อมูลในฐานข้อมูลไม่สำเร็จ']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูลรูปภาพ']);
}