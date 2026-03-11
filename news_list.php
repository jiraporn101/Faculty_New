<?php
require_once "../config/db.php";
require_once "../auth/check.php";

$cat_id = isset($_GET['cat_id']) ? (int)$_GET['cat_id'] : 0;

// ปรับ SQL ให้ดึง views และ created_at มาด้วย
$sql = "SELECT n.*, c.name as cat FROM fn_news n LEFT JOIN fn_categories c ON n.category_id = c.id";
if ($cat_id > 0) { $sql .= " WHERE n.category_id = $cat_id"; }
$sql .= " ORDER BY n.id DESC";

$news_list = $conn->query($sql);
$categories = $conn->query("SELECT * FROM fn_categories");
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { background-color: #f8f9fa; padding: 20px; font-family: 'Sarabun', sans-serif; }
        .container-box { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .btn-logout { background-color: #dc3545; color: white; border: none; padding: 8px 15px; border-radius: 5px; text-decoration: none; transition: 0.3s; cursor: pointer; }
        .btn-logout:hover { background-color: #a71d2a; color: white; }
        .header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .news-img-thumb { width: 80px; height: 55px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd; }
        .nav-pills .nav-link { color: #555; font-weight: bold; margin-right: 5px; border: 1px solid #dee2e6; }
        .nav-pills .nav-link.active { background-color: #0d6efd; border-color: #0d6efd; }
        .view-badge { background-color: #e9ecef; color: #495057; padding: 4px 8px; border-radius: 5px; font-size: 0.85rem; }
        
        /* เพิ่มเติม: กำหนดให้ลิงก์และตัวหนังสือในส่วนชื่อข่าวเป็นสีดำ */
        .news-title-link { color: #000000 !important; text-decoration: none; }
        .news-title-link:hover { text-decoration: underline; }
        .text-black-custom { color: #000000 !important; }
    </style>
</head>
<body>

<div class="container container-box">
    <div class="header-flex">
        <h1><i class="bi bi-newspaper"></i> การจัดการข่าวสาร</h1>
        <button class="btn-logout" onclick="confirmLogout()">
            <i class="bi bi-box-arrow-right"></i> ออกจากระบบ
        </button>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="news_form.php" class="btn btn-success"><i class="bi bi-plus-circle"></i> เพิ่มข่าวใหม่</a>
        <a href="../" target="_blank" class="btn btn-info text-white"><i class="bi bi-eye"></i> ดูเว็บไซต์</a>
        <a href="dashboard.php" class="btn btn-secondary"><i class="bi bi-speedometer2"></i> แดชบอร์ด</a>
        <a href="about_edit.php" class="btn btn-warning"><i class="bi bi-person-gear"></i> แก้ไขข้อมูลผู้จัดทำ</a>
    </div>
</div>

    <ul class="nav nav-pills mb-4">
        <li class="nav-item"><a class="nav-link <?= ($cat_id == 0) ? 'active' : '' ?>" href="news_list.php">ทั้งหมด</a></li>
        <?php while($c = $categories->fetch_assoc()): ?>
            <li class="nav-item"><a class="nav-link <?= ($cat_id == $c['id']) ? 'active' : '' ?>" href="news_list.php?cat_id=<?=$c['id']?>"><?=$c['name']?></a></li>
        <?php endwhile; ?>
    </ul>

    <table class="table table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>รูปภาพ</th>
                <th>ชื่อข่าว / วันที่โพสต์</th>
                <th>หมวดหมู่</th>
                <th>ยอดวิว</th>
                <th>สถานะ</th>
                <th class="text-center">เครื่องมือ</th>
            </tr>
        </thead>
        <tbody>
            <?php if($news_list->num_rows > 0): ?>
                <?php while($row = $news_list->fetch_assoc()): ?>
                <tr>
                    <td>#<?=$row['id']?></td>
                    <td>
                        <?php 
                        $news_id = $row['id'];
                        $img_res = $conn->query("SELECT image_name FROM fn_news_images WHERE news_id = $news_id LIMIT 1");
                        $img_data = $img_res->fetch_assoc();
                        if($img_data): 
                        ?>
                            <img src="../uploads/<?=$img_data['image_name']?>" class="news-img-thumb">
                        <?php else: ?>
                            <span class="text-muted small">ไม่มีรูป</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="fw-bold"><a href="news_form.php?id=<?=$row['id']?>" class="news-title-link"><?=$row['title']?></a></div>
                        <small class="text-black-custom"><i class="bi bi-calendar3"></i> <?=date('d/m/Y H:i', strtotime($row['created_at']))?></small>
                    </td>
                    <td><span class="badge bg-secondary"><?=$row['cat']?></span></td>
                    <td>
                        <span class="view-badge"><i class="bi bi-eye-fill"></i> <?=number_format($row['views'])?></span>
                    </td>
                    <td>
                        <span class="badge bg-<?=($row['status'] == 'publish' ? 'success' : 'warning text-dark')?>">
                            <?=($row['status'] == 'publish' ? 'เผยแพร่' : 'แบบร่าง')?>
                        </span>
                    </td>
                    <td class="text-center">
                        <a href="news_form.php?id=<?=$row['id']?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i> แก้ไข</a>
                        <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?=$row['id']?>)"><i class="bi bi-trash"></i> ลบ</button>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="7" class="text-center py-4 text-muted">ไม่พบข้อมูลข่าวสาร</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
function confirmDelete(id) {
    Swal.fire({
        title: 'ยืนยันการลบข่าว?',
        text: "หากลบแล้วข้อมูลจะหายไปถาวร!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'ใช่, ลบเลย!',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'news_delete.php?id=' + id;
        }
    })
}

function confirmLogout() {
    Swal.fire({
        title: 'ออกจากระบบ?',
        text: "คุณต้องการออกจากระบบใช่หรือไม่",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'ยืนยัน',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '../auth/logout.php';
        }
    })
}
</script>
</body>
</html>