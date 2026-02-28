<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "admin/auth.php";
require_once "db_connect.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php"); // ปรับ Path ให้ตรงกับหน้า Login ของคุณ
    exit();
}
ini_set('display_errors', 1);
error_reporting(E_ALL);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>คลังข้อมูลสายพันธุ์แมว</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        body {
            background-color: #f4f7f6;
            font-family: 'Sarabun', sans-serif;
        }
        .cat-card {
            border-radius: 16px;
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .cat-img {
            height: 350px;
            object-fit: cover;
            cursor: pointer; /* เปลี่ยนเมาส์เป็นรูปมือ */
            transition: 0.3s;
        }
        .cat-img:hover {
            opacity: 0.8;
        }
        .card-body {
            padding: 1rem;
            display: flex;
            flex-direction: column;
        }
        .card-content {
            flex-grow: 1;
        }
        .text-limit {
            max-height: 70px;
            overflow: hidden;
        }
        .badge-visible {
            background-color: #d1e7dd;
            color: #0f5132;
        }
        .badge-hidden {
            background-color: #f8d7da;
            color: #842029;
        }

        /* ลูกศร slider */
        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            background-color: rgba(0,0,0,0.4);
            border-radius: 50%;
        }
        
        /* สไตล์สำหรับรูปที่กดขยาย */
        .zoom-img { 
            width: 100%; 
            border-radius: 12px; 
        }
    </style>
</head>

<body>
<div class="container my-5">

    <div class="d-flex justify-content-between mb-4">
        <h2 class="fw-bold">🐾 คลังข้อมูลสายพันธุ์แมว</h2>
        <div>
            <a href="viewer.php" class="btn btn-outline-primary me-2">
                <i class="bi bi-eye"></i> Viewer
            </a>

            <?php if(isset($_SESSION['admin_id'])): ?>
                <div class="dropdown d-inline">
                    <button class="btn btn-dark dropdown-toggle" data-bs-toggle="dropdown">
                        👑 Admin
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li class="dropdown-item text-muted">
                            ID: <?= $_SESSION['admin_id'] ?>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="admin/profile.php">
                                <i class="bi bi-person-circle"></i> ข้อมูลส่วนตัว
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="admin/logout.php">Logout</a>
                        </li>
                    </ul>
                </div>
            <?php else: ?>
                <a href="admin/login.php" class="btn btn-outline-dark">Admin Login</a>
            <?php endif; ?>

            <a href="form.php" class="btn btn-success rounded-pill ms-2">
                <i class="bi bi-plus-circle"></i> เพิ่มสายพันธุ์
            </a>
        </div>
    </div>

    <div class="row g-3">
        <?php
        $res = $conn->query("SELECT * FROM catbreeds ORDER BY id DESC");

        if ($res && $res->num_rows > 0):
            while ($row = $res->fetch_assoc()):
                $id = $row['id'];
                $imgs = [];
                $q = $conn->query("SELECT image FROM cat_images WHERE cat_id = $id");
                while ($im = $q->fetch_assoc()) {
                    $imgs[] = basename($im['image']);
                }
        ?>
        <div class="col-md-6 col-lg-4">
            <div class="card cat-card">
                <?php if (!empty($imgs)): ?>
                <div id="carousel<?= $id ?>" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        <?php $active = true; ?>
                        <?php foreach ($imgs as $img_name): ?>
                            <div class="carousel-item <?= $active ? 'active' : '' ?>">
                                <img src="uploads/<?= rawurlencode($img_name) ?>" 
                                     class="cat-img w-100" 
                                     onclick="zoomImg(this.src)">
                            </div>
                        <?php $active = false; endforeach; ?>
                    </div>

                    <?php if (count($imgs) > 1): ?>
                    <button class="carousel-control-prev" type="button" data-bs-target="#carousel<?= $id ?>" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon"></span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#carousel<?= $id ?>" data-bs-slide="next">
                        <span class="carousel-control-next-icon"></span>
                    </button>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                    <div class="bg-light d-flex justify-content-center align-items-center" style="height:220px;">
                        ไม่มีรูป
                    </div>
                <?php endif; ?>

                <div class="card-body">
                    <div class="card-content">
                        <h5 class="fw-bold"><?= htmlspecialchars($row['name_th']) ?></h5>
                        <small class="text-muted"><em><?= htmlspecialchars($row['name_en']) ?></em></small><br>
                        <span class="badge rounded-pill <?= $row['is_visible'] ? 'badge-visible' : 'badge-hidden' ?>">
                            <?= $row['is_visible'] ? 'แสดงผล' : 'ซ่อนไว้' ?>
                        </span>
                        <hr>
                        <strong>คำอธิบาย:</strong>
                        <div class="text-limit"><?= nl2br(htmlspecialchars($row['description'] ?? '-')) ?></div>
                        <strong class="mt-2 d-block">ลักษณะ:</strong>
                        <div class="text-limit"><?= nl2br(htmlspecialchars($row['characteristics'] ?? '-')) ?></div>
                        <strong class="mt-2 d-block">การดูแล:</strong>
                        <div class="collapse" id="detail<?= $id ?>">
                            <?= nl2br(htmlspecialchars($row['care_instructions'] ?? '-')) ?>
                        </div>
                        <button class="btn btn-link p-0 mt-1" onclick="toggleDetail(<?= $id ?>, this)">ดูเพิ่มเติม</button>
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <a href="edit.php?id=<?= $id ?>" class="btn btn-warning btn-sm flex-fill">
                            <i class="bi bi-pencil"></i> แก้ไข
                        </a>
                        <button class="btn btn-danger btn-sm flex-fill" onclick="openDeleteModal(<?= $id ?>)">
                            <i class="bi bi-trash"></i> ลบ
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; else: ?>
            <div class="col-12 text-center text-muted">ยังไม่มีข้อมูล</div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="deleteModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-4 text-center">
            <h5 class="text-danger fw-bold">ยืนยันการลบ</h5>
            <p>เมื่อลบข้อมูลสายพันธุ์นี้แล้ว จะไม่สามารถกู้คืนได้</p>
            <div class="d-flex gap-2">
                <a id="confirmDeleteBtn" class="btn btn-danger flex-fill">ลบข้อมูล</a>
                <button class="btn btn-secondary flex-fill" data-bs-dismiss="modal">ยกเลิก</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="zoomModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 p-1 bg-transparent">
            <div class="text-end">
                <button type="button" class="btn-close btn-close-white mb-2" data-bs-dismiss="modal"></button>
            </div>
            <img id="zoomImage" class="zoom-img shadow-lg mx-auto d-block" src="">
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
function openDeleteModal(id) {
    // ต้องมี admin/ นำหน้า เพราะไฟล์ delete.php อยู่ในโฟลเดอร์ admin
    document.getElementById('confirmDeleteBtn').href = "admin/delete.php?id=" + id;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

function toggleDetail(id, btn) {
    const detail = document.getElementById('detail' + id);
    if (!detail) return;
    const collapse = new bootstrap.Collapse(detail, { toggle: false });
    if (detail.classList.contains('show')) {
        collapse.hide();
        btn.innerText = 'ดูเพิ่มเติม';
    } else {
        collapse.show();
        btn.innerText = 'ย่อกลับ';
    }
}

function zoomImg(src) {
    document.getElementById('zoomImage').src = src;
    var myModal = new bootstrap.Modal(document.getElementById('zoomModal'));
    myModal.show();
}
</script>

</body>
</html>