<?php
// ตรวจสอบ session ก่อนเป็นอันดับแรก
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "auth.php"; 
require_once "../db_connect.php"; 

ini_set('display_errors', 1);
error_reporting(E_ALL);

$id = $_SESSION['admin_id'] ?? null;
if (!$id) { 
    header("Location: login.php");
    exit; 
}

$msg = "";

// ====== ส่วนบันทึกข้อมูล (POST) ======
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username']);
    $pass = trim($_POST['password']);
    $full_name = trim($_POST['full_name']);
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $education = trim($_POST['education']);

    if (!empty($pass)) {
        $sql = "UPDATE admins SET username=?, password=?, full_name=?, address=?, phone=?, email=?, education=? WHERE id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssssssi", $user, $pass, $full_name, $address, $phone, $email, $education, $id);
    } else {
        $sql = "UPDATE admins SET username=?, full_name=?, address=?, phone=?, email=?, education=? WHERE id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssssi", $user, $full_name, $address, $phone, $email, $education, $id);
    }
    
    if (mysqli_stmt_execute($stmt)) {
        $msg = "<div class='alert alert-success'>✅ บันทึกข้อมูลเรียบร้อยแล้ว</div>";
        $res = $conn->query("SELECT * FROM admins WHERE id = $id");
        $admin = $res->fetch_assoc();
    } else {
        $msg = "<div class='alert alert-danger'>❌ เกิดข้อผิดพลาด: " . mysqli_error($conn) . "</div>";
    }
} else {
    $res = $conn->query("SELECT * FROM admins WHERE id = $id");
    $admin = $res->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>โปรไฟล์ผู้ดูแลระบบ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { background-color: #f4f7f6; font-family: 'Sarabun', sans-serif; }
        .card { border-radius: 15px; border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold">👤 ข้อมูลส่วนตัว</h2>
                <a href="../index.php" class="btn btn-outline-secondary btn-sm">กลับหน้าหลัก</a>
            </div>

            <?= $msg ?>

            <div class="card p-4">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">ชื่อผู้ใช้งาน (Username)</label>
                            <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($admin['username'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">รหัสผ่านใหม่ (ว่างไว้ถ้าไม่เปลี่ยน)</label>
                            <input type="password" name="password" class="form-control">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">ชื่อ-นามสกุล</label>
                        <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($admin['full_name'] ?? '') ?>" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">อีเมล</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($admin['email'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">เบอร์โทรศัพท์</label>
                            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($admin['phone'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">ที่อยู่</label>
                        <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($admin['address'] ?? '') ?></textarea>
                    </div>

                   <div class="mb-4">
                        <label class="form-label">ประวัติการศึกษา</label>
                        <textarea name="education" class="form-control" rows="4"><?= htmlspecialchars($admin['education'] ?? '') ?></textarea>
                        </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-dark py-2 shadow-sm">บันทึกการเปลี่ยนแปลง</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>