<?php
require "../config/db.php";
require "../auth/check.php";
require "../function.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$news = null;
$title = "";
$detail = "";
$cat_id = 0;
$status = "draft";
$error_message = ""; 

if ($id > 0) {
    $stmt = $conn->prepare("SELECT * FROM fn_news WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $news = $stmt->get_result()->fetch_assoc();
    if ($news) {
        $title = $news['title'];
        $detail = $news['detail'];
        $cat_id = $news['category_id'];
        $status = $news['status'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $detail = trim($_POST['detail']);
    $cat_id = (int)$_POST['category_id'];
    $status = $_POST['status'];

    if (empty($title) || empty($detail)) {
        $error_message = "กรุณากรอกหัวข้อและเนื้อหาข่าวให้ครบถ้วน";
    } else {
        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE fn_news SET title=?, detail=?, category_id=?, status=? WHERE id=?");
            $stmt->bind_param("ssisi", $title, $detail, $cat_id, $status, $id);
            $stmt->execute();
            $news_id = $id;
        } else {
            $stmt = $conn->prepare("INSERT INTO fn_news (title, detail, category_id, status) VALUES (?,?,?,?)");
            $stmt->bind_param("ssis", $title, $detail, $cat_id, $status);
            $stmt->execute();
            $news_id = $conn->insert_id;
        }

        if (isset($_FILES['image'])) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_size = 5 * 1024 * 1024; 

            foreach ($_FILES['image']['name'] as $key => $name) {
                if ($_FILES['image']['error'][$key] == 0) {
                    $file_type = $_FILES['image']['type'][$key];
                    if (!in_array($file_type, $allowed_types)) continue;
                    if ($_FILES['image']['size'][$key] > $max_size) continue;

                    $file_ext = pathinfo($name, PATHINFO_EXTENSION);
                    $new_name = uniqid('img_', true) . "_" . time() . "." . $file_ext;
                    
                    $tmp_name = $_FILES['image']['tmp_name'][$key];
                    if (move_uploaded_file($tmp_name, "../uploads/" . $new_name)) {
                        $stmt_img = $conn->prepare("INSERT INTO fn_news_images (news_id, image_name) VALUES (?, ?)");
                        $stmt_img->bind_param("is", $news_id, $new_name);
                        $stmt_img->execute();
                    }
                }
            }
        }
        header("Location: news_list.php");
        exit();
    }
}
$cats = $conn->query("SELECT * FROM fn_categories");
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=($id > 0) ? "แก้ไขข่าว" : "เพิ่มข่าวใหม่"?></title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #eef2f5; margin: 0; padding: 20px; }
        .form-container { max-width: 700px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        h2 { color: #333; margin-top: 0; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: bold; color: #555; }
        input[type="text"], textarea, select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; font-size: 1rem; }
        textarea { height: 200px; resize: vertical; }
        .current-images { margin-top: 10px; display: flex; flex-wrap: wrap; gap: 10px; }
        .image-item { border-radius: 5px; border: 1px solid #ddd; padding: 5px; position: relative; }
        .image-item img { display: block; border-radius: 4px; }
        /* ปรับแต่งปุ่มลบรูป */
        .delete-img-btn { position: absolute; top: -5px; right: -5px; background: #dc3545; color: white; border: none; border-radius: 50%; width: 22px; height: 22px; font-size: 14px; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(0,0,0,0.2); transition: 0.2s; }
        .delete-img-btn:hover { background: #a71d2a; transform: scale(1.1); }
        .btn-submit { background-color: #28a745; color: white; padding: 12px 20px; border: none; border-radius: 6px; font-size: 1.1rem; cursor: pointer; width: 100%; transition: background 0.2s; }
        .btn-submit:hover { background-color: #218838; }
        .back-link { display: inline-block; margin-top: 15px; color: #666; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
        .alert-danger { color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; border-radius: 5px; margin-bottom: 15px; }
    </style>
</head>
<body>

<div class="form-container">
    <h2><i class="bi bi-pencil-square"></i> <?=($id > 0) ? "แก้ไขข่าวสาร" : "เพิ่มข่าวสารใหม่"?></h2>
    
    <?php if(!empty($error_message)): ?>
        <div class="alert-danger"><?= $error_message ?></div>
    <?php endif; ?>
    
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>หัวข้อข่าว</label>
            <input type="text" name="title" value="<?=htmlspecialchars($title)?>" placeholder="ใส่หัวข้อข่าวที่นี่">
        </div>
        
        <div class="form-group">
            <label>เนื้อหาข่าว</label>
            <textarea name="detail" placeholder="ใส่รายละเอียดข่าวที่นี่"><?=htmlspecialchars($detail)?></textarea>
        </div>
        
        <div class="form-group">
            <label>หมวดหมู่</label>
            <select name="category_id">
                <?php while($c = $cats->fetch_assoc()): ?>
                    <option value="<?=$c['id']?>" <?=($c['id'] == $cat_id) ? 'selected' : ''?>><?=$c['name']?></option>
                <?php endwhile; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label>รูปภาพประกอบ (เลือกได้หลายรูป)</label>
            <?php if($id > 0): ?>
                <div class="current-images">
                    <?php 
                    $img_res = $conn->query("SELECT * FROM fn_news_images WHERE news_id = $id");
                    while($img_row = $img_res->fetch_assoc()):
                    ?>
                    <div class="image-item">
                        <img src="../uploads/<?=$img_row['image_name']?>" width="100" height="70" style="object-fit: cover;">
                        <button type="button" class="delete-img-btn" onclick="confirmDeleteImg(<?=$img_row['id']?>)">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>
            <input type="file" name="image[]" accept="image/jpeg, image/png, image/gif" multiple style="margin-top:10px;">
            <small style="color: #666; display:block; margin-top:5px;">อนุญาตไฟล์ JPG, PNG, GIF ขนาดไม่เกิน 5MB</small>
        </div>
        
        <div class="form-group">
            <label>สถานะ</label>
            <select name="status">
                <option value="publish" <?=($status == 'publish') ? 'selected' : ''?>>เผยแพร่</option>
                <option value="draft" <?=($status == 'draft') ? 'selected' : ''?>>แบบร่าง</option>
            </select>
        </div>
        
        <button type="submit" class="btn-submit">บันทึกข้อมูล</button>
    </form>
    
    <a href="news_list.php" class="back-link">&larr; กลับหน้ารายการข่าว</a>
</div>

<script>
// ฟังก์ชันเด้งแจ้งเตือนการลบรูป
function confirmDeleteImg(imgId) {
    Swal.fire({
        title: 'ลบรูปภาพนี้?',
        text: "คุณจะไม่สามารถกู้คืนรูปภาพนี้ได้!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'ใช่, ลบเลย!',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            // ส่งไปที่ไฟล์ลบรูปภาพ
            window.location.href = 'delete_image.php?id=' + imgId;
        }
    })
}
</script>

</body>
</html>