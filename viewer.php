<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "db_connect.php";

// 1. ดึงข้อมูล Admin แถวแรกมาเตรียมไว้
$admin_res = $conn->query("SELECT full_name, email, phone, address, education FROM admins LIMIT 1");
$admin = $admin_res->fetch_assoc();

// 2. ดึงข้อมูลสายพันธุ์แมว
$cats = $conn->query("
    SELECT * FROM catbreeds 
    WHERE is_visible = 1
    ORDER BY id DESC
");
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>คลังสายพันธุ์แมว</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        body { background:#f5f7f9; font-family:Sarabun,sans-serif;}
        .cat-img{ height:350px; object-fit:cover; cursor:pointer; transition:.2s; }
        .cat-img:hover{ transform:scale(1.02); }
        .zoom-img{ width:100%; border-radius:12px; }
        .carousel-control-prev-icon, .carousel-control-next-icon{ background-color: rgba(0,0,0,0.5); border-radius:50%; }
    </style>
</head>

<body>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">🐾 คลังสายพันธุ์แมว</h2>
        <div>
            <button class="btn btn-info text-white shadow-sm me-2" data-bs-toggle="modal" data-bs-target="#adminInfoModal">
                <i class="bi bi-person-circle"></i> ข้อมูลผู้จัดทำ
            </button>
        </div>
    </div>

    <div class="row g-4">
    <?php while ($cat = $cats->fetch_assoc()): ?>
        <?php
        $cat_id = (int)$cat['id'];
        $imgs = [];
        $q = $conn->query("SELECT image FROM cat_images WHERE cat_id = $cat_id");
        while ($r = $q->fetch_assoc()) {
            $file = basename(trim($r['image']));
            if ($file !== '') $imgs[] = $file;
        }
        ?>
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <?php if (!empty($imgs)): ?>
                    <div id="carousel<?= $cat_id ?>" class="carousel slide">
                        <div class="carousel-inner">
                            <?php $active = true; foreach ($imgs as $img): ?>
                                <div class="carousel-item <?= $active ? 'active' : '' ?>">
                                    <img src="uploads/<?= rawurlencode($img) ?>" class="cat-img w-100" onclick="zoomImg(this.src)">
                                </div>
                            <?php $active = false; endforeach; ?>
                        </div>
                        <?php if (count($imgs) > 1): ?>
                            <button class="carousel-control-prev" data-bs-target="#carousel<?= $cat_id ?>" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
                            <button class="carousel-control-next" data-bs-target="#carousel<?= $cat_id ?>" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="bg-light text-center p-5 text-muted small">ไม่มีรูปภาพ</div>
                <?php endif; ?>

                <div class="card-body">
                    <h5 class="fw-bold mb-0"><?= htmlspecialchars($cat['name_th']) ?></h5>
                    <small class="text-muted d-block mb-2"><?= htmlspecialchars($cat['name_en']) ?></small>
                    <p class="text-muted small"><?= mb_substr(strip_tags($cat['description']),0,80) ?>...</p>
                    
                    <div class="collapse mt-2" id="detail<?= $cat_id ?>">
                        <hr>
                        <p class="small"><b>ลักษณะ:</b> <?= nl2br(htmlspecialchars($cat['characteristics'])) ?></p>
                        <p class="small"><b>การดูแล:</b> <?= nl2br(htmlspecialchars($cat['care_instructions'])) ?></p>
                    </div>
                    <button class="btn btn-outline-primary btn-sm w-100" data-bs-toggle="collapse" data-bs-target="#detail<?= $cat_id ?>">ดูข้อมูลเพิ่มเติม</button>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
    </div>
</div>

<div class="modal fade" id="adminInfoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="bi bi-person-vcard"></i> ข้อมูลติดต่อผู้ดูแลระบบ</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-3">
                    <h4 class="fw-bold text-dark mb-1"><?= htmlspecialchars($admin['full_name'] ?? 'ไม่พบข้อมูล') ?></h4>
                    <span class="badge bg-light text-info border border-info">Administrator</span>
                    <hr>
                </div>
                <div class="mb-3">
                    <label class="text-muted small d-block"><i class="bi bi-envelope"></i> อีเมล</label>
                    <span class="fw-bold text-dark"><?= htmlspecialchars($admin['email'] ?? '-') ?></span>
                </div>
                <div class="mb-3">
                    <label class="text-muted small d-block"><i class="bi bi-telephone"></i> เบอร์โทรศัพท์</label>
                    <span class="fw-bold text-dark"><?= htmlspecialchars($admin['phone'] ?? '-') ?></span>
                </div>
                <div class="mb-3">
                    <label class="text-muted small d-block"><i class="bi bi-geo-alt"></i> ที่อยู่</label>
                    <p class="mb-0 text-dark small"><?= nl2br(htmlspecialchars($admin['address'] ?? '-')) ?></p>
                </div>
                <div class="mb-0">
                    <label class="text-muted small d-block"><i class="bi bi-mortarboard"></i> ประวัติการศึกษา</label>
                    <p class="mb-0 text-dark small"><?= nl2br(htmlspecialchars($admin['education'] ?? '-')) ?></p>
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">ปิดหน้าต่าง</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="zoomModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 p-1 bg-transparent">
            <div class="text-end"><button type="button" class="btn-close btn-close-white mb-2" data-bs-dismiss="modal"></button></div>
            <img id="zoomImage" class="zoom-img shadow-lg mx-auto d-block" src="">
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// คำสั่งเดิมที่มีอยู่แล้ว
function zoomImg(src){
    document.getElementById('zoomImage').src = src;
    var myModal = new bootstrap.Modal(document.getElementById('zoomModal'));
    myModal.show();
}

// เพิ่มส่วนนี้เข้าไปเพื่อให้ Carousel เลื่อนอัตโนมัติ
document.addEventListener('DOMContentLoaded', function () {
    var carousels = document.querySelectorAll('.carousel');
    carousels.forEach(function (carousel) {
        new bootstrap.Carousel(carousel, {
            interval: 3000, // ความเร็วในการเลื่อน (3000 = 3 วินาที)
            ride: 'carousel'
        });
    });
});
</script>

</body>
</html>