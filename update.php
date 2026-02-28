<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id = intval($_POST['id']);

    $name_th = $_POST['name_th'] ?? '';
    $name_en = $_POST['name_en'] ?? '';
    $description = $_POST['description'] ?? '';
    $characteristics = $_POST['characteristics'] ?? '';
    $care_instructions = $_POST['care_instructions'] ?? '';
    $is_visible = $_POST['is_visible'] ?? 1;

    $upload_dir = "uploads/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // =========================
    // 1️⃣ UPDATE ข้อมูลแมว
    // =========================
    $sql = "UPDATE catbreeds SET
        name_th = ?,
        name_en = ?,
        description = ?,
        characteristics = ?,
        care_instructions = ?,
        is_visible = ?
        WHERE id = ?";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param(
        $stmt,
        "sssssii",
        $name_th,
        $name_en,
        $description,
        $characteristics,
        $care_instructions,
        $is_visible,
        $id
    );
    mysqli_stmt_execute($stmt);

    // =========================
    // 2️⃣ ถ้ามีอัปโหลดรูปใหม่
    // =========================
    // =========================
// 2️⃣ อัปโหลดหลายรูป (ไม่ลบของเก่า)
// =========================
if (!empty($_FILES['images']['name'][0])) {

    $allowed_ext = ['jpg','jpeg','png','gif','webp'];

    foreach ($_FILES['images']['tmp_name'] as $key => $tmp) {

        if ($_FILES['images']['error'][$key] !== 0) continue;

        $fileName = $_FILES['images']['name'][$key];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed_ext)) continue;

        $newName = time() . "_" . uniqid() . "." . $ext;

        if (move_uploaded_file($tmp, $upload_dir . $newName)) {

            $stmt = $conn->prepare("INSERT INTO cat_images (cat_id, image) VALUES (?, ?)");
            $stmt->bind_param("is", $id, $newName);
            $stmt->execute();
        }
    }
}


    header("Location: index.php");
    exit;
}
