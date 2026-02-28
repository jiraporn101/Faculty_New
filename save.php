<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name_th = $_POST['name_th'] ?? '';
    $name_en = $_POST['name_en'] ?? '';
    $description = $_POST['description'] ?? ''; 
    $characteristics = $_POST['characteristics'] ?? '';
    $care_instructions = $_POST['care_instructions'] ?? '';
    $is_visible = isset($_POST['is_visible']) ? 1 : 0;

    // เตรียมตัวแปรเก็บชื่อไฟล์สำหรับตารางหลัก
    $first_image_name = null;
    $uploaded_images = [];

    /* =========================
       1️⃣ จัดการอัปโหลดไฟล์
    ========================= */
    if (!empty($_FILES['images']['name'][0])) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (!is_dir("uploads")) {
            mkdir("uploads", 0777, true);
        }

        foreach ($_FILES['images']['tmp_name'] as $key => $tmp) {
            if ($_FILES['images']['error'][$key] !== 0) continue;
            
            $fileName = $_FILES['images']['name'][$key];
            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if (in_array($ext, $allowed)) {
                $newName = time() . "_" . uniqid() . "." . $ext;
                
                if (move_uploaded_file($tmp, "uploads/" . $newName)) {
                    // เก็บชื่อไฟล์แรกไว้ใส่ catbreeds
                    if ($first_image_name === null) {
                        $first_image_name = $newName;
                    }
                    // เก็บทุกชื่อไฟล์ไว้ใส่ cat_images
                    $uploaded_images[] = $newName; 
                }
            }
        }
    }

    /* =========================
       2️⃣ บันทึกลงตาราง catbreeds (รวมชื่อไฟล์รูปแรก)
    ========================= */
    $sql = "INSERT INTO catbreeds 
            (name_th, name_en, description, characteristics, care_instructions, is_visible, image_url) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param(
        $stmt,
        "sssssis", // s=string, i=integer
        $name_th,
        $name_en,
        $description,
        $characteristics,
        $care_instructions,
        $is_visible,
        $first_image_name // บันทึกชื่อรูปลงคอลัมน์ image_url
    );
    
    if (mysqli_stmt_execute($stmt)) {
        $cat_id = mysqli_insert_id($conn);

        /* =========================
           3️⃣ บันทึกทุกรูปลงตาราง cat_images
        ========================= */
        if (!empty($uploaded_images)) {
            foreach ($uploaded_images as $imgName) {
                $imgSql = "INSERT INTO cat_images (cat_id, image) VALUES (?, ?)";
                $imgStmt = mysqli_prepare($conn, $imgSql);
                mysqli_stmt_bind_param($imgStmt, "is", $cat_id, $imgName);
                mysqli_stmt_execute($imgStmt);
            }
        }

        header("Location: index.php");
        exit;
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>