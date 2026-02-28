<?php
require_once "admin/auth.php"; // เพิ่มบรรทัดนี้ไว้บนสุด
require_once "db_connect.php";

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = intval($_GET['id']);

// 1. ดึงข้อมูลสายพันธุ์แมว
$sql = "SELECT * FROM catbreeds WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    header("Location: index.php");
    exit;
}

// 2. ดึงรูปภาพทั้งหมดของแมวตัวนี้มาแสดง
$sql_img = "SELECT * FROM cat_images WHERE cat_id = ?";
$stmt_img = mysqli_prepare($conn, $sql_img);
mysqli_stmt_bind_param($stmt_img, "i", $id);
mysqli_stmt_execute($stmt_img);
$res_img = mysqli_stmt_get_result($stmt_img);
$current_images = mysqli_fetch_all($res_img, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แก้ไขข้อมูลสายพันธุ์แมว</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { background:#f4f7f6; font-family:'Sarabun',sans-serif; }
        .card { border-radius:20px; }
        .img-container { position: relative; display: inline-block; }
        .btn-delete-img { 
            position: absolute; top: -5px; right: -5px; 
            padding: 0px 6px; font-size: 12px; border-radius: 50%; 
        }
    </style>
</head>

<body>
<div class="container mt-5 mb-5">
    <div class="card shadow col-md-8 mx-auto border-0">
        <div class="card-header bg-warning text-dark text-center py-3">
            <h4 class="mb-0 fw-bold">✏️ แก้ไขข้อมูลสายพันธุ์แมว</h4>
        </div>

        <div class="card-body p-4">
            <form action="update.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $data['id'] ?>">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">ชื่อ (ไทย)</label>
                        <input type="text" name="name_th" class="form-control" value="<?= htmlspecialchars($data['name_th']) ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">ชื่อ (อังกฤษ)</label>
                        <input type="text" name="name_en" class="form-control" value="<?= htmlspecialchars($data['name_en']) ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">คำอธิบาย</label>
                    <textarea name="description" class="form-control" rows="3" required><?= htmlspecialchars($data['description']) ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">ลักษณะ</label>
                    <textarea name="characteristics" class="form-control" rows="3"><?= htmlspecialchars($data['characteristics']) ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">การดูแล</label>
                    <textarea name="care_instructions" class="form-control" rows="3"><?= htmlspecialchars($data['care_instructions']) ?></textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold d-block">รูปภาพปัจจุบัน:</label>
                    <div class="row g-2">
                        <?php foreach ($current_images as $img): ?>
                            <div class="col-4 col-md-3 text-center" id="img-box-<?= $img['id'] ?>">
                                <div class="img-container border rounded p-1 shadow-sm bg-white">
                                    <img src="uploads/<?= htmlspecialchars($img['image']) ?>" class="img-fluid rounded" style="height: 100px; width: 100%; object-fit: cover;">
                                    <button type="button" class="btn btn-danger btn-delete-img" onclick="deleteImg(<?= $img['id'] ?>)">×</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($current_images)): ?>
                            <div class="col-12"><small class="text-muted italic">ยังไม่มีรูปภาพ</small></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="form-label fw-bold">เพิ่มรูปภาพใหม่</label>
                        <input type="file" name="images[]" class="form-control" multiple>
                        <small class="text-muted">เลือกได้หลายรูป (ระบบจะบันทึกเพิ่มจากรูปเดิม)</small>
                    </div>
                    <div class="col-md-4 mb-4">
                        <label class="form-label fw-bold">สถานะการแสดงผล</label>
                        <select name="is_visible" class="form-select border-warning">
                            <option value="1" <?= $data['is_visible'] ? 'selected' : '' ?>>แสดง</option>
                            <option value="0" <?= !$data['is_visible'] ? 'selected' : '' ?>>ซ่อน</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <a href="index.php" class="btn btn-outline-secondary w-25">ยกเลิก</a>
                    <button type="submit" class="btn btn-warning w-75 fw-bold">บันทึกการแก้ไข</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function deleteImg(imgId) {
    if (confirm('ยืนยันว่าจะลบรูปภาพนี้หรือไม่?')) {
        // ส่งคำสั่งลบไปที่ไฟล์ delete_image_api.php (ต้องสร้างไฟล์นี้ด้วยครับ)
        fetch('delete_image_api.php?id=' + imgId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // ถ้าลบใน Database สำเร็จ ให้ลบรูปออกจากหน้าจอทันที
                const element = document.getElementById('img-box-' + imgId);
                element.style.transition = "0.3s";
                element.style.opacity = "0";
                setTimeout(() => element.remove(), 300);
            } else {
                alert('เกิดข้อผิดพลาด: ' + data.message);
            }
        });
    }
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>