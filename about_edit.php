<?php
require_once "../config/db.php";
require_once "../auth/check.php";

// ดึงข้อมูลเดิมมาแสดง
$res = $conn->query("SELECT * FROM fn_about LIMIT 1");
$data = $res->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $faculty = $_POST['faculty_name'];
    $admins = $_POST['admin_names'];
    $uni = $_POST['university_name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];

    $sql = "UPDATE fn_about SET faculty_name=?, admin_names=?, university_name=?, phone=?, email=? WHERE id=1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $faculty, $admins, $uni, $phone, $email);
    
    if ($stmt->execute()) {
        echo "<script>
                alert('อัปเดตข้อมูลผู้จัดทำเรียบร้อยแล้ว!');
                window.location='news_list.php';
              </script>";
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Edit Admin Info</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600&display=swap" rel="stylesheet">
    <style>body { font-family: 'Sarabun', sans-serif; background: #f8f9fa; padding: 40px; }</style>
</head>
<body>
<div class="container bg-white p-5 rounded shadow-sm" style="max-width: 700px;">
    <h3><i class="bi bi-person-gear"></i> แก้ไขข้อมูลผู้จัดทำ</h3>
    <hr>
    <form method="POST">
        <div class="mb-3">
            <label class="form-label fw-bold">ชื่อคณะ</label>
            <input type="text" name="faculty_name" class="form-control" value="<?=$data['faculty_name']?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold">รายชื่อผู้จัดทำ (ใส่เลขลำดับและขึ้นบรรทัดใหม่ได้เลย)</label>
            <textarea name="admin_names" class="form-control" rows="5" required><?=$data['admin_names']?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold">ชื่อมหาวิทยาลัย / สถาบัน</label>
            <input type="text" name="university_name" class="form-control" value="<?=$data['university_name']?>" required>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">เบอร์โทรศัพท์</label>
                <input type="text" name="phone" class="form-control" value="<?=$data['phone']?>">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">อีเมล</label>
                <input type="email" name="email" class="form-control" value="<?=$data['email']?>">
            </div>
        </div>
        <div class="mt-4">
            <button type="submit" class="btn btn-primary px-4">บันทึกข้อมูล</button>
            <a href="news_list.php" class="btn btn-light px-4">กลับหน้าหลัก</a>
        </div>
    </form>
</div>
</body>
</html>