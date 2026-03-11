<?php
// เปิดการแสดง Error เพื่อเช็คปัญหา
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require "config/db.php";

// --- ส่วนดึงข้อมูลผู้จัดทำ (About Info) ---
$about_res = $conn->query("SELECT * FROM fn_about LIMIT 1");
$about = $about_res->fetch_assoc();

// --- ส่วนจัดการ Pagination ---
$limit = 6; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// หาจำนวนข่าวทั้งหมด
$total_news_query = "SELECT COUNT(*) FROM fn_news WHERE status = 'publish'";
$total_result = $conn->query($total_news_query);
$total_rows = $total_result->fetch_row()[0];
$total_pages = ceil($total_rows / $limit);

// 1. ดึงข้อมูลหมวดหมู่ทั้งหมด
$categories = $conn->query("SELECT * FROM fn_categories");

// 2. ดึงข่าวล่าสุด 5 รายการ ทำ Slider
$slider_query = "SELECT n.id, n.title, i.image_name FROM fn_news n
                  LEFT JOIN fn_news_images i ON n.id = i.news_id
                  WHERE n.status = 'publish' 
                  GROUP BY n.id
                  ORDER BY n.id DESC LIMIT 5";
$slider_result = $conn->query($slider_query);

// 3. ดึงข่าวเด่นวันนี้ (Sidebar)
$hot_news_query = "SELECT id, title, views FROM fn_news WHERE status = 'publish' ORDER BY views DESC LIMIT 5";
$hot_news_result = $conn->query($hot_news_query);

// 4. ดึงข่าวล่าสุดรายการอื่นๆ พร้อม Pagination
$news_query = "SELECT n.id, n.title, c.name as cat_name, n.views, n.created_at FROM fn_news n 
                JOIN fn_categories c ON n.category_id = c.id 
                WHERE n.status = 'publish' 
                ORDER BY n.id DESC LIMIT $limit OFFSET $offset";
$news_result = $conn->query($news_query);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty News - ข่าวประชาสัมพันธ์คณะ</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { font-family: 'Sarabun', sans-serif; background-color: #f8f9fa; margin: 0; padding: 0; color: #333; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        
        header { 
            background: white; padding: 20px 0; border-bottom: 3px solid #000; 
            position: sticky; top: 0; z-index: 1000; box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
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
        
        nav { background: #000; padding: 0; }
        .nav-content { max-width: 1200px; margin: 0 auto; padding: 0 20px; display: flex; overflow-x: auto; }
        .nav-content a { color: white; text-decoration: none; padding: 18px 20px; font-size: 1rem; font-weight: 600; white-space: nowrap; transition: 0.3s; }
        .nav-content a:hover, .nav-content a.active { background-color: #cc0000; }
        
        .wrapper { display: flex; gap: 25px; margin-top: 30px; align-items: flex-start; }
        .sidebar { width: 320px; flex-shrink: 0; }
        .main-content { flex-grow: 1; }
        
        .hot-news-box { background: white; padding: 25px; border-radius: 8px; border: 1px solid #eee; position: sticky; top: 140px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
        .box-title { color: #cc0000; font-size: 1.4rem; font-weight: bold; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; display: flex; align-items: center; gap: 10px; }
        .hot-news-item { display: flex; gap: 15px; padding: 15px 0; border-bottom: 1px solid #eee; text-decoration: none; color: #333; transition: all 0.2s; }
        .hot-news-item:hover { color: #cc0000; transform: translateX(5px); }
        .hot-news-number { font-size: 1.8rem; font-weight: bold; color: #cc0000; min-width: 30px; text-align: center; }
        .hot-news-text { font-size: 1rem; line-height: 1.5; font-weight: 600; }
        
        .slider-wrapper { position: relative; width: 100%; height: 450px; overflow: hidden; border-radius: 10px; margin-bottom: 30px; box-shadow: 0 6px 15px rgba(0,0,0,0.1); background-color: #eee; }
        .slider-slide { display: none; width: 100%; height: 100%; }
        .slider-slide img { width: 100%; height: 100%; object-fit: cover; }
        .slider-caption { position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(to top, rgba(0,0,0,0.9), rgba(0,0,0,0)); color: white; padding: 40px 20px 20px 20px; text-decoration: none; }
        .slider-caption h2 { margin: 0; font-size: 1.8rem; text-shadow: 1px 1px 3px rgba(0,0,0,0.5); }
        
        .prev, .next { cursor: pointer; position: absolute; top: 50%; width: auto; padding: 16px; margin-top: -22px; color: white; font-weight: bold; font-size: 20px; transition: 0.3s; border-radius: 0 3px 3px 0; user-select: none; background-color: rgba(0,0,0,0.3); }
        .next { right: 0; border-radius: 3px 0 0 3px; }
        .prev:hover, .next:hover { background-color: #cc0000; }
        
        .section-title { font-size: 1.6rem; font-weight: bold; color: #000; border-left: 5px solid #cc0000; padding-left: 15px; margin-bottom: 25px; }
        .news-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .news-card { background: white; border-radius: 8px; overflow: hidden; transition: all 0.3s; display: flex; flex-direction: column; border: 1px solid #eee; box-shadow: 0 2px 5px rgba(0,0,0,0.03); }
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

        .modal-overlay {
            display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%;
            background-color: rgba(0,0,0,0.7); align-items: center; justify-content: center;
        }
        .modal-card {
            background-color: white; padding: 30px; border-radius: 15px; width: 90%; max-width: 500px;
            position: relative; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.3); animation: fadeIn 0.3s;
        }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
        .close-btn { position: absolute; top: 10px; right: 20px; font-size: 28px; cursor: pointer; color: #999; }
        .close-btn:hover { color: #cc0000; }
        
        footer { background-color: #222; color: #fff; padding: 40px 0; margin-top: 50px; font-size: 0.9rem; }
        .footer-content { max-width: 1200px; margin: 0 auto; padding: 0 20px; display: grid; grid-template-columns: 1.5fr 1fr; gap: 40px; }
        .footer-section h3 { margin-top: 0; color: #cc0000; border-bottom: 1px solid #444; padding-bottom: 10px; }
        .footer-section ul { list-style: none; padding: 0; }
        .footer-section ul li { margin-bottom: 10px; }
        .footer-section a { color: #ccc; text-decoration: none; }
        .footer-section a:hover { color: white; }
        .copyright { text-align: center; padding: 20px; border-top: 1px solid #444; margin-top: 30px; color: #888; }
        
        @media (max-width: 900px) { 
            .wrapper { flex-direction: column; } 
            .sidebar { width: 100%; } 
            .hot-news-box { position: relative; top: 0; }
            .slider-wrapper { height: 300px; }
            .news-grid { grid-template-columns: repeat(2, 1fr); }
            .footer-content { grid-template-columns: 1fr; }
        }
        @media (max-width: 600px) {
            .news-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<header>
    <div class="header-content">
        <div class="logo-area">              
            <a href="index.php" class="site-title-link">
                <h1>Faculty News</h1>
                <div class="sub-title">ข่าวประชาสัมพันธ์คณะ</div>
            </a>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <form action="search.php" method="GET" class="search-box">
                <input type="text" name="keyword" placeholder="ค้นหาข่าว..." required>
                <button type="submit">ค้นหา</button>
            </form>
            <a href="javascript:void(0)" onclick="openAdminModal()" style="text-decoration: none; padding: 10px 15px; background-color: #6c757d; color: white; border-radius: 20px; font-weight: bold; font-size: 0.9rem; transition: 0.3s;">
                <i class="bi bi-person-circle"></i> ข้อมูลแอดมิน
            </a>
        </div>
    </div>
</header>

<nav>
    <div class="nav-content">
        <a href="index.php" class="<?= !isset($_GET['cat_id']) ? 'active' : '' ?>">หน้าแรก</a>
        <?php 
        if ($categories) {
            $categories->data_seek(0);
            while($cat = $categories->fetch_assoc()): 
        ?>
            <a href="category.php?cat_id=<?=$cat['id']?>"><?=htmlspecialchars($cat['name'])?></a>
        <?php 
            endwhile;
        }
        ?>
    </div>
</nav>

<div class="container">
    <div class="wrapper">
        <aside class="sidebar">
            <div class="hot-news-box">
                <div class="box-title"> ข่าวเด่นวันนี้ !! </div>
                <?php if ($hot_news_result): ?>
                    <?php $i = 1; while($hot = $hot_news_result->fetch_assoc()): ?>
                    <a href="news.php?id=<?=$hot['id']?>" class="hot-news-item">
                        <span class="hot-news-number"><?=str_pad($i, 2, '0', STR_PAD_LEFT)?></span>
                        <span class="hot-news-text"><?=htmlspecialchars($hot['title'])?></span>
                    </a>
                    <?php $i++; endwhile; ?>
                <?php endif; ?>
            </div>
        </aside>

        <section class="main-content">
            <div class="slider-wrapper">
                <?php if ($slider_result && $slider_result->num_rows > 0): ?>
                    <?php $i = 0; while($slider = $slider_result->fetch_assoc()): ?>
                    <div class="slider-slide" style="display: <?= ($i==0) ? 'block' : 'none' ?>;">
                        <img src="uploads/<?=$slider['image_name']?>" alt="Slider Image">
                        <a href="news.php?id=<?=$slider['id']?>" class="slider-caption">
                            <h2><?=htmlspecialchars($slider['title'])?></h2>
                        </a>
                    </div>
                    <?php $i++; endwhile; ?>
                    <a class="prev" onclick="plusSlides(-1)">&#10094;</a>
                    <a class="next" onclick="plusSlides(1)">&#10095;</a>
                <?php else: ?>
                    <div style="text-align:center; padding: 100px; color: #888;">ไม่มีรูปภาพข่าว</div>
                <?php endif; ?>
            </div>

            <div class="section-title">ข่าวสารล่าสุด</div>
            <div class="news-grid">
                <?php 
                if ($news_result):
                while($row = $news_result->fetch_assoc()): 
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
                <?php endwhile; 
                endif;
                ?>
            </div>
            
            <div class="pagination">
                <?php if($page > 1): ?>
                    <a href="?page=<?=$page-1?>">&laquo; ก่อนหน้า</a>
                <?php endif; ?>
                
                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?=$i?>" class="<?=($page == $i) ? 'active' : ''?>"><?=$i?></a>
                <?php endfor; ?>
                
                <?php if($page < $total_pages): ?>
                    <a href="?page=<?=$page+1?>">ถัดไป &raquo;</a>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>

<div id="adminModal" class="modal-overlay">
    <div class="modal-card">
        <span class="close-btn" onclick="closeAdminModal()">&times;</span>
        <h2 style="color:#cc0000; border-bottom:2px solid #eee; padding-bottom:10px;">ข้อมูลผู้จัดทำ</h2>
        <div style="text-align:left; margin-top:20px; line-height:2;">
            <p><strong>คณะ:</strong> <?=htmlspecialchars($about['faculty_name'] ?? 'ไม่ระบุ')?></p>
            <p><strong>รายชื่อผู้จัดทำ:</strong></p>
            <div style="padding-left:10px; margin-bottom:10px;">
                <?=nl2br(htmlspecialchars($about['admin_names'] ?? 'ไม่มีข้อมูล'))?>
            </div>
            <p style="margin-top:10px;"><strong>สถาบัน:</strong> <?=htmlspecialchars($about['university_name'] ?? 'ไม่ระบุ')?></p>
            <hr>
        </div>
    </div>
</div>

<footer>
    <div class="footer-content">
        <div class="footer-section">
            <p><strong>Faculty News</strong><br>
               ข่าวประชาสัมพันธ์คณะ<br><br>
               จัดทำโดย:<br>
               <?=nl2br(htmlspecialchars($about['admin_names'] ?? 'ไม่มีข้อมูล'))?>
            </p>
        </div>
        <div class="footer-section">
            <h3>ติดต่อเรา</h3>
            <ul>
                <li>โทรศัพท์: <?=htmlspecialchars($about['phone'] ?? '-')?></li>
                <li>อีเมล: <?=htmlspecialchars($about['email'] ?? '-')?></li>
            </ul>
        </div>
    </div>
    <div class="copyright">
        &copy; <?=date('Y')?> Faculty News. All Rights Reserved.
    </div>
</footer>

<script>
    // ส่วน Slider
    let slideIndex = 1;
    let slides = document.getElementsByClassName("slider-slide");
    
    if(slides.length > 0) {
        showSlides(slideIndex);
        setInterval(() => { plusSlides(1); }, 5000);
    }

    function plusSlides(n) { showSlides(slideIndex += n); }
    
    function showSlides(n) {
        let i;
        if (n > slides.length) {slideIndex = 1}    
        if (n < 1) {slideIndex = slides.length}
        for (i = 0; i < slides.length; i++) { 
            slides[i].style.display = "none";  
        }
        if(slides[slideIndex-1]) slides[slideIndex-1].style.display = "block";  
    }

    // ส่วน Modal ข้อมูลผู้จัดทำ
    function openAdminModal() {
        document.getElementById('adminModal').style.display = 'flex';
    }
    function closeAdminModal() {
        document.getElementById('adminModal').style.display = 'none';
    }
    window.onclick = function(event) {
        let modal = document.getElementById('adminModal');
        if (event.target == modal) {
            closeAdminModal();
        }
    }
</script>
</body>
</html>