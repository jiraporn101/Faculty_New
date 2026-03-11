<?php
// 1. เชื่อมต่อฐานข้อมูล
require "config/db.php";

// 2. รับค่า cat_id จาก URL และตรวจสอบความถูกต้อง (Security)
$cat_id = isset($_GET['cat_id']) ? intval($_GET['cat_id']) : 0;

// ถ้าไม่มี cat_id หรือค่าไม่ใช่ตัวเลข ให้กลับไปหน้าหลัก
if ($cat_id <= 0) {
    header("Location: index.php");
    exit();
}

// --- เพิ่มส่วนนี้: ดึงข้อมูลผู้จัดทำจากตาราง fn_about ---
$about_res = $conn->query("SELECT * FROM fn_about LIMIT 1");
$about_data = $about_res->fetch_assoc();
// ------------------------------------------------

// --- ส่วนจัดการ Pagination (เลือกหน้า) ---
$limit = 6; // จำนวนข่าวต่อหน้า
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// หาจำนวนข่าวทั้งหมดในหมวดหมู่นี้
$total_news_query = "SELECT COUNT(*) FROM fn_news WHERE category_id = ? AND status = 'publish'";
$stmt_total = $conn->prepare($total_news_query);
$stmt_total->bind_param("i", $cat_id);
$stmt_total->execute();
$total_result = $stmt_total->get_result();
$total_rows = $total_result->fetch_row()[0];
$total_pages = ceil($total_rows / $limit);
// --------------------------------------

// 3. ดึงข้อมูลหมวดหมู่ปัจจุบัน
$stmt = $conn->prepare("SELECT name FROM fn_categories WHERE id = ?");
$stmt->bind_param("i", $cat_id);
$stmt->execute();
$cat_result = $stmt->get_result();
$current_cat = $cat_result->fetch_assoc();

if (!$current_cat) {
    die("ไม่พบหมวดหมู่นี้ในระบบ");
}

// 4. ดึงข้อมูลหมวดหมู่ทั้งหมด เพื่อทำเมนู
$menu_categories = $conn->query("SELECT * FROM fn_categories");

// 5. ดึงข้อมูลข่าวเฉพาะหมวดที่เลือก
$news_sql = "SELECT n.id, n.title, n.views, n.created_at, c.name as cat_name 
             FROM fn_news n 
             JOIN fn_categories c ON n.category_id = c.id 
             WHERE n.category_id = ? AND n.status = 'publish' 
             ORDER BY n.id DESC LIMIT $limit OFFSET $offset";
$news_stmt = $conn->prepare($news_sql);
$news_stmt->bind_param("i", $cat_id);
$news_stmt->execute();
$news_result = $news_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=htmlspecialchars($current_cat['name'])?> - Faculty News</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        body { font-family: 'Sarabun', sans-serif; background-color: #f8f9fa; margin: 0; padding: 0; color: #333; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        
        header { background: white; padding: 20px 0; border-bottom: 3px solid #000; position: sticky; top: 0; z-index: 1000; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .header-content { display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 0 auto; padding: 0 20px; }
        
        .logo-area { display: flex; align-items: center; gap: 15px; }
        
        .site-title-link { text-decoration: none; color: inherit; display: flex; flex-direction: column; transition: 0.3s; }
        .site-title-link:hover h1 { color: #cc0000; }
        .site-title-link h1 { margin: 0; font-size: 1.8rem; color: #000; font-weight: bold; }
        .site-title-link .sub-title { font-size: 1rem; color: #555; font-weight: 500; }

        .search-box { display: flex; gap: 5px; }
        .search-box input { padding: 10px 15px; border: 1px solid #ddd; border-radius: 20px; outline: none; transition: 0.3s; }
        .search-box input:focus { border-color: #cc0000; }
        .search-box button { padding: 10px 20px; background-color: #000; color: white; border: none; border-radius: 20px; cursor: pointer; font-weight: bold; transition: 0.3s; }
        .search-box button:hover { background-color: #333; }

        .btn-admin-info { display: flex; align-items: center; gap: 8px; background-color: #6c757d; color: white; padding: 8px 18px; border-radius: 20px; text-decoration: none; font-size: 0.9rem; font-weight: 600; transition: 0.3s; border: none; cursor: pointer; }
        .btn-admin-info:hover { background-color: #5a6268; transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }

        nav { background: #000; padding: 0; }
        .nav-content { max-width: 1200px; margin: 0 auto; padding: 0 20px; display: flex; overflow-x: auto; }
        .nav-content a { color: white; text-decoration: none; padding: 18px 20px; font-size: 1rem; font-weight: 600; white-space: nowrap; transition: 0.3s; }
        .nav-content a:hover, .nav-content a.active { background-color: #cc0000; }

        .page-title { font-size: 1.6rem; font-weight: bold; color: #000; border-left: 5px solid #cc0000; padding-left: 15px; margin: 30px 0; }
        .news-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .news-card { background: white; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.03); overflow: hidden; transition: all 0.3s; display: flex; flex-direction: column; border: 1px solid #eee; }
        .news-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .news-img-wrapper { width: 100%; height: 180px; overflow: hidden; background-color: #eee; }
        .news-img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s; }
        .news-card:hover .news-img { transform: scale(1.1); }
        .news-content { padding: 15px; flex-grow: 1; display: flex; flex-direction: column; }
        .news-title { margin: 0 0 10px 0; font-size: 1rem; line-height: 1.4; font-weight: bold; }
        .news-title a { text-decoration: none; color: #000; transition: 0.3s; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .news-title a:hover { color: #cc0000; }
        .news-meta { color: #777; font-size: 0.8rem; margin-top: auto; padding-top: 10px; border-top: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        
        .pagination { display: flex; justify-content: center; gap: 5px; margin-top: 40px; margin-bottom: 30px; }
        .pagination a, .pagination span { padding: 10px 16px; border: 1px solid #ddd; text-decoration: none; color: #333; border-radius: 5px; font-weight: bold; }
        .pagination a:hover { background-color: #cc0000; color: white; border-color: #cc0000; }
        .pagination .active { background-color: #000; color: white; border-color: #000; }
        
        .modal { display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.6); animation: fadeIn 0.3s; }
        .modal-content { background-color: #fff; margin: 10% auto; padding: 0; border-radius: 15px; width: 90%; max-width: 500px; position: relative; box-shadow: 0 5px 30px rgba(0,0,0,0.3); overflow: hidden; }
        .modal-header { background: #fff; padding: 20px; text-align: center; border-bottom: 1px solid #eee; }
        .modal-header h2 { color: #cc0000; margin: 0; font-size: 1.5rem; }
        .modal-body { padding: 25px; line-height: 1.6; }
        .close-modal { position: absolute; right: 20px; top: 15px; font-size: 28px; cursor: pointer; color: #aaa; transition: 0.2s; }
        .close-modal:hover { color: #000; }
        .btn-go-admin { display: block; text-align: center; background: #000; color: #fff; padding: 12px; border-radius: 8px; text-decoration: none; font-weight: bold; margin-top: 20px; transition: 0.3s; }
        .btn-go-admin:hover { background: #333; }

        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

        footer { background-color: #222; color: #fff; padding: 40px 0; margin-top: 50px; font-size: 0.9rem; }
        .footer-content { max-width: 1200px; margin: 0 auto; padding: 0 20px; display: grid; grid-template-columns: 1.5fr 1fr; gap: 40px; }
        .footer-section h3 { margin-top: 0; color: #cc0000; border-bottom: 1px solid #444; padding-bottom: 10px; }
        .footer-section ul { list-style: none; padding: 0; }
        .footer-section ul li { margin-bottom: 10px; }
        .footer-section a { color: #ccc; text-decoration: none; }
        .footer-section a:hover { color: white; }
        .copyright { text-align: center; padding: 20px; border-top: 1px solid #444; margin-top: 30px; color: #888; }
        
        @media (max-width: 900px) { 
            .news-grid { grid-template-columns: repeat(2, 1fr); }
            .footer-content { grid-template-columns: 1fr; }
        }
        @media (max-width: 600px) {
            .news-grid { grid-template-columns: 1fr; }
            .header-content { flex-direction: column; gap: 15px; }
        }
    </style>
</head>
<body>

<header>
    <div class="header-content">
        <div class="logo-area">                
            <a href="index.php" class="site-title-link">
                <h1>Faculty News</h1>
                <div class="sub-title">ข่าวประชาสัมพันธ์</div>
            </a>
        </div>
        
        <div style="display: flex; align-items: center; gap: 10px;">
            <form action="search.php" method="GET" class="search-box">
                <input type="text" name="keyword" placeholder="ค้นหาข่าว..." required>
                <button type="submit">ค้นหา</button>
            </form>
            
            <button class="btn-admin-info" onclick="openAdminModal()">
                <i class="bi bi-person-circle"></i> ข้อมูลแอดมิน
            </button>
        </div>
    </div>
</header>

<nav>
    <div class="nav-content">
        <a href="index.php">หน้าแรก</a>
        <?php 
        if($menu_categories->num_rows > 0) {
            while($cat = $menu_categories->fetch_assoc()): 
                $isActive = ($cat['id'] == $cat_id) ? 'active' : '';
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
    <div class="page-title">
        หมวดหมู่: <?=htmlspecialchars($current_cat['name'])?>
    </div>
    
    <div class="news-grid">
        <?php if($news_result->num_rows > 0): ?>
            <?php while($row = $news_result->fetch_assoc()): 
                $img_res = $conn->query("SELECT image_name FROM fn_news_images WHERE news_id = ".$row['id']." LIMIT 1");
                $img_row = $img_res->fetch_assoc();
            ?>
                <div class="news-card">
                    <div class="news-img-wrapper">
                        <?php if($img_row): ?>
                            <img src="uploads/<?=$img_row['image_name']?>" alt="News Image" class="news-img">
                        <?php else: ?>
                            <img src="assets/no-image.jpg" alt="No Image" class="news-img">
                        <?php endif; ?>
                    </div>
                    <div class="news-content">
                        <h3 class="news-title">
                            <a href="news.php?id=<?=$row['id']?>"><?=htmlspecialchars($row['title'])?></a>
                        </h3>
                        <div class="news-meta">
                            <span>📅 <?=date('d/m/Y', strtotime($row['created_at']))?></span>
                            <span>👁️ <?=number_format($row['views'])?></span>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="grid-column: 1/-1; text-align: center; padding: 50px; background:white; border-radius:8px;">ยังไม่มีข่าวในหมวดหมู่นี้</p>
        <?php endif; ?>
    </div>
    
    <div class="pagination">
        <?php if($page > 1): ?>
            <a href="?cat_id=<?=$cat_id?>&page=<?=$page-1?>">&laquo; ก่อนหน้า</a>
        <?php endif; ?>
        
        <?php for($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?cat_id=<?=$cat_id?>&page=<?=$i?>" class="<?=($page == $i) ? 'active' : ''?>"><?=$i?></a>
        <?php endfor; ?>
        
        <?php if($page < $total_pages): ?>
            <a href="?cat_id=<?=$cat_id?>&page=<?=$page+1?>">ถัดไป &raquo;</a>
        <?php endif; ?>
    </div>
</div>

<div id="adminModal" class="modal">
    <div class="modal-content">
        <span class="close-modal" onclick="closeAdminModal()">&times;</span>
        <div class="modal-header">
            <h2>ข้อมูลผู้จัดทำ</h2>
        </div>
        <div class="modal-body">
            <p><strong>คณะ:</strong> <?= htmlspecialchars($about_data['faculty_name'] ?? 'วิทยาศาสตร์') ?></p>
            <p><strong>รายชื่อผู้จัดทำ:</strong></p>
            <div style="margin-bottom: 12px; white-space: pre-line;">
                <?= nl2br(htmlspecialchars($about_data['admin_names'] ?? "")) ?>
            </div>
            <p><strong>สถาบัน:</strong> <?= htmlspecialchars($about_data['university_name'] ?? 'มหาวิทยาลัยราชภัฏอุดรธานี') ?></p>
        </div>
    </div>
</div>

<footer>
    <div class="footer-content">
        <div class="footer-section">
            <p><strong><?= htmlspecialchars($about_data['faculty_name'] ?? 'Faculty News') ?></strong><br>
                ข่าวประชาสัมพันธ์คณะ<br><br>
                จัดทำโดย:<br>
                <?= nl2br(htmlspecialchars($about_data['admin_names'] ?? "")) ?>
            </p>
        </div>
        <div class="footer-section">
            <h3>ติดต่อเรา</h3>
            <ul>
                <li>โทรศัพท์: <?= htmlspecialchars($about_data['phone'] ?? '02-999-9999') ?></li>
                <li>อีเมล: <?= htmlspecialchars($about_data['email'] ?? 'admin@example.com') ?></li>
            </ul>
        </div>
    </div>
    <div class="copyright">
        &copy; 2026 Faculty News. All Rights Reserved.
    </div>
</footer>

<script>
    function openAdminModal() {
        document.getElementById("adminModal").style.display = "block";
    }

    function closeAdminModal() {
        document.getElementById("adminModal").style.display = "none";
    }

    window.onclick = function(event) {
        let modal = document.getElementById("adminModal");
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
</script>

</body>
</html>