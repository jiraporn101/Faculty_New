<?php
// ===== Start Session ปลอดภัย =====
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ===== Base URL (แก้ตรงนี้ถ้าอยู่ใน subfolder) =====
// ตัวอย่าง:
// localhost: /catbreeds
// hosting: /~it67040233101
$base = "";
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>CatBreeds</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet">

<style>
body { font-family: 'Sarabun', sans-serif; background:#f5f7f9; }
.navbar-brand { font-weight:700; }
.footer { background:#5f9ea0; color:white; padding:20px; margin-top:50px; }
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark" style="background:#5f9ea0">
<div class="container">

<a class="navbar-brand" href="<?= $base ?>../index.php">🐾 CatBreeds</a>

<div class="ms-auto">
<?php if(isset($_SESSION['admin_id'])): ?>
    <div class="dropdown">
        <button class="btn btn-warning dropdown-toggle shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            👑 Admin
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow">
            <li>
                <a class="dropdown-item" href="../index.php">
                    <i class="bi bi-house-door me-2"></i> กลับหน้าหลัก
                </a>
            </li>
            <li>
                <a class="dropdown-item" href="profile.php">
                    <i class="bi bi-person-circle me-2"></i> ข้อมูลส่วนตัว
                </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <a class="dropdown-item text-danger" href="logout.php">
                    <i class="bi bi-box-arrow-right me-2"></i> ออกจากระบบ
                </a>
            </li>
        </ul>
    </div>
<?php else: ?>
    <a href="login.php" class="btn btn-light shadow-sm">Login</a>
<?php endif; ?>
</div>

</div>
</nav>

<div class="container mt-4">
