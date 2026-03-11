<?php
require "../config/db.php"; 
require "../auth/check.php";

$id = $_GET['id'];
$res = $conn->query("SELECT * FROM fn_news WHERE id = $id");
$news = $res->fetch_assoc();

if (!$news) { echo "ไม่พบข้อมูลข่าว"; exit; }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $detail = mysqli_real_escape_string($conn, $_POST['detail']);
    $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $views = isset($_POST['views']) ? mysqli_real_escape_string($conn, $_POST['views']) : $news['views'];
    
    $image_name = $news['image']; 

    if (!empty($_FILES['image']['name'])) {
        // ปรับเป็น PATHINFO_EXTENSION เพื่อความถูกต้องตามมาตรฐาน PHP
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $new_name = uniqid() . "." . $ext;
        $target_dir = "../uploads/";
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $new_name)) {
            if (!empty($news['image']) && file_exists($target_dir . $news['image'])) {
                unlink($target_dir . $news['image']);
            }
            $image_name = $new_name;
        }
    }

    $sql = "UPDATE fn_news SET 
            title='$title', 
            detail='$detail', 
            category_id='$category_id', 
            image='$image_name', 
            status='$status',
            views='$views' 
            WHERE id=$id";
            
    if (mysqli_query($conn, $sql)) {
        header("Location: news_list.php"); 
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แก้ไขข้อมูลข่าวสาร</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .container-box { 
            background: white; 
            padding: 40px; 
            border-radius: 5px; 
            box-shadow: 0 0 10px rgba(0,0,0,0.05); 
            margin-top: 30px;
            max-width: 900px;
        }
        .form-label { font-weight: 500; color: #333; margin-bottom: 8px; }
        .btn-save { background-color: #28a745; color: white; border: none; padding: 12px; width: 100%; font-size: 18px; border-radius: 5px; text-decoration: none; display: block; text-align: center; cursor: pointer; }
        .btn-save:hover { background-color: #218838; color: white; }
        .header-title { font-size: 24px; font-weight: bold; border-bottom: 1px solid #dee2e6; padding-bottom: 15px; margin-bottom: 25px; }
        .current-img { max-width: 200px; border: 1px solid #ddd; padding: 5px; border-radius: 5px; margin-bottom: 10px; }
    </style>
</head>
<body>

<div class="container container-box shadow">
    <div class="header-title">แก้ไขข่าวสาร (ID: #<?=$id?>)</div>
    
    <form method="POST" enctype="multipart/form-data">
        
        <div class="mb-4">
            <label class="form-label">หัวข้อข่าว</label>
            <input type="text" name="title" class="form-control" placeholder="ใส่หัวข้อข่าวที่นี่" value="<?=$news['title']?>" required>
        </div>

        <div class="mb-4">
            <label class="form-label">เนื้อหาข่าว</label>
            <textarea name="detail" class="form-control" rows="8" placeholder="ใส่รายละเอียดข่าวที่นี่"><?=$news['detail']?></textarea>
        </div>

        <div class="row">
            <div class="col-md-6 mb-4">
                <label class="form-label">หมวดหมู่</label>
                <select name="category_id" class="form-select">
                    <option value="1" <?=($news['category_id'] == 1) ? 'selected' : ''?>>ข่าวประกาศ</option>
                    <option value="2" <?=($news['category_id'] == 2) ? 'selected' : ''?>>ข่าวกิจกรรม</option>
                    <option value="3" <?=($news['category_id'] == 3) ? 'selected' : ''?>>ข่าวสมัคร</option>
                </select>
            </div>
           
        </div>

        <div class="mb-4">
            <label class="form-label">รูปภาพประกอบ (ปัจจุบัน)</label><br>
            <?php if(!empty($news['image'])): ?>
                <div class="mb-2">
                    <img src="../uploads/<?=$news['image']?>" class="current-img shadow-sm">
                </div>
            <?php endif; ?>
            <input type="file" name="image" class="form-control mt-2">
            <small class="text-muted">อนุญาตไฟล์ JPG, PNG, GIF ขนาดไม่เกิน 5MB</small>
        </div>

        <div class="mb-4">
            <label class="form-label">สถานะ</label>
            <select name="status" class="form-select">
                <option value="publish" <?=($news['status'] == 'publish') ? 'selected' : ''?>>เผยแพร่</option>
                <option value="draft" <?=($news['status'] == 'draft') ? 'selected' : ''?>>แบบร่าง</option>
               
            </select>
        </div>

        <div class="mt-5 row border-top pt-4">
            <div class="col-md-9">
                <button type="submit" class="btn-save shadow-sm">บันทึกการแก้ไขข้อมูล</button>
            </div>
            <div class="col-md-3">
                <a href="news_list.php" class="btn btn-outline-secondary w-100 p-2" style="font-size: 18px;">ยกเลิก</a>
            </div>
        </div>

    </form>
</div>

</body>
</html>