<?php
require "config/db.php";

// 1. รับค่า id จาก URL
$news_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($news_id == 0) {
    header("Location: index.php");
    exit();
}

// 2. ดึงข้อมูลข่าวและหมวดหมู่
$news_query = "SELECT n.*, c.name as cat_name FROM fn_news n 
               JOIN fn_categories c ON n.category_id = c.id 
               WHERE n.id = $news_id AND n.status = 'publish'";
$news_result = $conn->query($news_query);
$row = $news_result->fetch_assoc();

if (!$row) {
    die("ไม่พบข่าวนี้หรือข่าวอาจถูกซ่อนไว้");
}

// 3. ฟังก์ชันนับวิว
$conn->query("UPDATE fn_news SET views = views + 1 WHERE id = $news_id");

// 4. ดึงข้อมูลหมวดหมู่ทั้งหมดเพื่อทำเมนู
$categories = $conn->query("SELECT * FROM fn_categories");

// --- ดึงรูปภาพทั้งหมดจากตาราง fn_news_images ---
$images_result = $conn->query("SELECT image_name FROM fn_news_images WHERE news_id = $news_id");
$images = [];
while($img = $images_result->fetch_assoc()) {
    $images[] = $img['image_name'];
}

// --- ฟังก์ชันสำหรับเปลี่ยนข้อความ URL ให้เป็นลิงก์ที่คลิกได้ ---
function makeLinksClickable($text) {
    // ใช้ Regular Expression ค้นหา URL และแปลงเป็น tag <a>
    return preg_replace('@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)@', '<a href="$1" target="_blank" style="color: #007bff; text-decoration: underline;">$1</a>', $text);
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=htmlspecialchars($row['title'])?> - ข่าวสารคณะ</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* จัดการพื้นฐาน */
        body { font-family: 'Sarabun', sans-serif; background-color: #f8f9fa; margin: 0; padding: 0; color: #333; }
        .container { max-width: 900px; margin: 0 auto; padding: 20px; }
        
        /* Card ข่าว */
        .news-detail-card { background: white; padding: 30px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        
        /* สไตล์รูปภาพ Gallery */
        .featured-image-wrapper { width: 100%; height: 500px; overflow: hidden; border-radius: 5px; margin-bottom: 15px; }
        .featured-image { width: 100%; height: 100%; object-fit: cover; cursor: pointer; }
        
        .image-gallery { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 30px; }
        .gallery-item { width: 100%; height: 100px; overflow: hidden; border-radius: 3px; cursor: pointer; border: 2px solid transparent; transition: all 0.2s; }
        .gallery-item img { width: 100%; height: 100%; object-fit: cover; }
        .gallery-item:hover { border-color: #cc0000; }
        
        /* หัวข้อและ Metadata */
        .news-title { 
            margin: 0 0 15px 0; 
            color: #000; 
            font-size: 2.2rem;
            font-weight: bold;
            line-height: 1.2; 
        }
        .news-meta { color: #888; font-size: 0.95rem; margin-bottom: 30px; padding-bottom: 15px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        .meta-left { display: flex; gap: 15px; align-items: center; }
        .meta-tag { background: #eee; padding: 5px 12px; border-radius: 15px; color: #333; font-weight: 600; text-decoration: none; font-size: 0.85rem; text-transform: uppercase; }
        .back-btn { text-decoration: none; color: #cc0000; font-weight: bold; }
        .back-btn:hover { text-decoration: underline; }
        
        /* เนื้อหา */
        .news-content { line-height: 1.9; font-size: 1.15rem; color: #444; }
        .news-content img { max-width: 100%; height: auto; border-radius: 5px; }
        .news-content a { color: #007bff; text-decoration: underline; } /* สไตล์ลิงก์ในเนื้อหา */
        
        /* Header/Nav */
        header { background: white; padding: 15px 0; border-bottom: 3px solid #000; }
        .header-content { display: flex; justify-content: space-between; align-items: center; max-width: 1100px; margin: 0 auto; padding: 0 20px; }
        h1 { margin: 0; font-size: 1.5rem; color: #000; }
        nav { background: #1a1a1a; padding: 0; }
        .nav-content { max-width: 1100px; margin: 0 auto; padding: 0 20px; display: flex; gap: 5px; overflow-x: auto; }
        .nav-content a { color: white; text-decoration: none; padding: 15px 20px; font-size: 0.95rem; font-weight: bold; white-space: nowrap; }
        .nav-content a:hover, .nav-content a.active { background-color: #333; }
        
        /* สไตล์ Modal (Lightbox) */
        .modal { display: none; position: fixed; z-index: 1000; padding-top: 50px; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.9); }
        .modal-content { margin: auto; display: block; max-width: 80%; max-height: 80%; }
        .close { position: absolute; top: 15px; right: 35px; color: #f1f1f1; font-size: 40px; font-weight: bold; cursor: pointer; }
        
        /* Footer ใหม่ */
        footer { background-color: #222; color: #fff; padding: 40px 0; margin-top: 50px; font-size: 0.9rem; }
        .footer-content { max-width: 1200px; margin: 0 auto; padding: 0 20px; display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 30px; }
        .footer-logo img { height: 60px; margin-bottom: 15px; }
        .footer-section h3 { margin-top: 0; color: #cc0000; border-bottom: 1px solid #444; padding-bottom: 10px; }
        .footer-section ul { list-style: none; padding: 0; }
        .footer-section ul li { margin-bottom: 10px; }
        .footer-section a { color: #ccc; text-decoration: none; }
        .footer-section a:hover { color: white; }
        .copyright { text-align: center; padding: 20px; border-top: 1px solid #444; margin-top: 30px; color: #888; }
        
        /* Responsive */
        @media (max-width: 768px) {
            .featured-image-wrapper { height: 300px; }
            .image-gallery { grid-template-columns: repeat(3, 1fr); }
            .news-title { font-size: 1.7rem; }
            .footer-content { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<header>
    <div class="header-content">
        <h1>Faculty News</h1>
    </div>
</header>

<nav>
    <div class="nav-content">
        <a href="index.php">หน้าแรก</a>
        <?php 
        if($categories->num_rows > 0) {
            while($cat = $categories->fetch_assoc()): 
                $isActive = ($cat['id'] == $row['category_id']) ? 'active' : '';
        ?>
            <a href="category.php?cat_id=<?=$cat['id']?>" class="<?= $isActive ?>">
                <?=htmlspecialchars($cat['name'])?>
            </a>
        <?php 
            endwhile;
        }
        ?>
    </div>
</nav>

<div class="container">
    <div class="news-detail-card">
        
        <?php if(!empty($images)): ?>
            <div class="featured-image-wrapper">
                <img src="uploads/<?=$images[0]?>" alt="Featured Image" class="featured-image" id="featured" onclick="openModal(this.src)">
            </div>
            
            <?php if(count($images) > 1): ?>
                <div class="image-gallery">
                    <?php foreach($images as $img): ?>
                        <div class="gallery-item" onclick="changeFeaturedImage('uploads/<?=$img?>')">
                            <img src="uploads/<?=$img?>" alt="Gallery Image">
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <h1 class="news-title"><?=htmlspecialchars($row['title'])?></h1>
        
        <div class="news-meta">
            <div class="meta-left">
                <span class="meta-tag"><?=$row['cat_name']?></span>
                <span>📅 <?=date('d/m/Y', strtotime($row['created_at']))?></span>
                <span>👁️ <?=number_format($row['views'])?></span>
            </div>
            <a href="javascript:history.back()" class="back-btn">⬅️ ย้อนกลับ</a>
        </div>
        
        <div class="news-content">
            <?php 
                // แสดงรายละเอียดข่าวโดยใช้ฟังก์ชันแปลง URL เป็นลิงก์
                echo makeLinksClickable(nl2br($row['detail']));
            ?>
        </div>
    </div>
</div>

<footer>
    <div class="footer-content">
        <div class="footer-section">
            <div class="footer-logo">
                <img src="logo.png" alt="Faculty Logo">
            </div>
            <p>คณะ..... มหาวิทยาลัย.....<br>
            เลขที่ 123 ถนน.... แขวง.... เขต.... กรุงเทพฯ 10900</p>
        </div>
        <div class="footer-section">
            <h3>ติดต่อเรา</h3>
            <ul>
                <li>โทรศัพท์: 02-xxx-xxxx</li>
                <li>อีเมล: info@university.ac.th</li>
            </ul>
        </div>
        <div class="footer-section">
            <h3>สำหรับ Admin</h3>
            <ul>
                <li><a href="admin/login.php" target="_blank">เข้าสู่ระบบจัดการข่าว</a></li>
                <li><a href="#">คู่มือการใช้งาน</a></li>
            </ul>
        </div>
    </div>
    <div class="copyright">
        &copy; 2026 Faculty News. All Rights Reserved.
    </div>
</footer>

<div id="myModal" class="modal">
  <span class="close" onclick="closeModal()">&times;</span>
  <img class="modal-content" id="img01">
</div>

<script>
    function changeFeaturedImage(imagePath) {
        document.getElementById('featured').src = imagePath;
    }

    function openModal(imageSrc) {
        document.getElementById("myModal").style.display = "block";
        document.getElementById("img01").src = imageSrc;
    }

    function closeModal() {
        document.getElementById("myModal").style.display = "none";
    }
</script>

</body>
</html>