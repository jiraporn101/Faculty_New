<?php
require_once "../config/db.php";
require_once "../auth/check.php";

// ดึงข้อมูลแอดมิน (สมมติว่าใช้ session เก็บ id ไว้)
$admin_id = $_SESSION['admin_id'] ?? 1; 
$res = $conn->query("SELECT * FROM fn_admins WHERE id = $admin_id");
$admin = $res->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = $_POST['fullname'];
    $username = $_POST['username'];
    $sql = "UPDATE fn_admins SET fullname='$fullname', username='$username' WHERE id=$admin_id";
    if($conn->query($sql)) {
        echo "<script>alert('อัปเดตข้อมูลสำเร็จ!'); window.location.href='news_list.php';</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แก้ไขข้อมูลแอดมิน</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light p-5">
    <div class="card mx-auto shadow" style="max-width: 500px;">
        <div class="card-header bg-warning text-dark"><strong>แก้ไขข้อมูลแอดมิน</strong></div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">ชื่อ-นามสกุล</label>
                    <input type="text" name="fullname" class="form-control" value="<?=$admin['fullname']?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">ชื่อผู้ใช้งาน (Username)</label>
                    <input type="text" name="username" class="form-control" value="<?=$admin['username']?>" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">บันทึกข้อมูล</button>
                <a href="news_list.php" class="btn btn-secondary w-100 mt-2">ยกเลิก</a>
            </form>
        </div>
    </div>
</body>
</html>