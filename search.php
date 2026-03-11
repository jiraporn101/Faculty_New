<?php
// 1. เชื่อมต่อฐานข้อมูล
require "config/db.php";

// 2. รับค่า keyword จาก URL และจัดการช่องว่าง
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

// --- ส่วนจัดการ Pagination (เลือกหน้า) ---
$limit = 6; // จำนวนข่าวต่อหน้า
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// หาจำนวนข่าวที่ค้นหาเจอทั้งหมด
$total_rows = 0;
if (!empty($keyword)) {
    $total_sql = "SELECT COUNT(*) FROM fn_news n WHERE n.status = 'publish' AND (n.title LIKE ? OR n.detail LIKE ?)";
    $stmt_total = $conn->prepare($total_sql);
    $search_param = "%" . $keyword . "%";
    $stmt_total->bind_param("ss", $search_param, $search_param);
    $stmt_total->execute();
    $total_rows = $stmt_total->get_result()->fetch_row()[0];
}
$total_pages = ceil($total_rows / $limit);
// --------------------------------------

// 3. ดึงข้อมูลหมวดหมู่ทั้งหมดเพื่อทำเมนู
$categories = $conn->query("SELECT * FROM fn_categories");

// 4. ค้นหาข่าว (ถ้ามี keyword) พร้อม Pagination
$news_result = null;
if (!empty($keyword)) {
    $search_sql = "SELECT n.id, n.title, n.views, n.created_at, c.name as cat_name 
                   FROM fn_news n 
                   JOIN fn_categories c ON n.category_id = c.id 
                   WHERE n.status = 'publish' 
                   AND (n.title LIKE ? OR n.detail LIKE ?)
                   ORDER BY n.id DESC LIMIT $limit OFFSET $offset";
    
    $stmt = $conn->prepare($search_sql);
    $search_param = "%" . $keyword . "%";
    $stmt->bind_param("ss", $search_param, $search_param);
    $stmt->execute();
    $news_result = $stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ผลการค้นหา: <?=htmlspecialchars($keyword)?> - Faculty News</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Sarabun', sans-serif; background-color: #f8f9fa; margin: 0; padding: 0; color: #333; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        
        /* Header & Search เหมือน index */
        header { background: white; padding: 20px 0; border-bottom: 3px solid #000; position: sticky; top: 0; z-index: 1000; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .header-content { display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 0 auto; padding: 0 20px; }
        
        .logo-area { display: flex; align-items: center; gap: 15px; }
        .logo-area img { height: 60px; width: auto; }
        .site-title { display: flex; flex-direction: column; }
        h1 { margin: 0; font-size: 1.8rem; color: #000; font-weight: bold; }
        .sub-title { font-size: 1rem; color: #555; font-weight: 500; }
        
        .search-box { display: flex; gap: 5px; }
        .search-box input { padding: 10px 15px; border: 1px solid #ddd; border-radius: 20px; outline: none; transition: 0.3s; }
        .search-box input:focus { border-color: #cc0000; }
        .search-box button { padding: 10px 20px; background-color: #000; color: white; border: none; border-radius: 20px; cursor: pointer; font-weight: bold; transition: 0.3s; }
        .search-box button:hover { background-color: #333; }

        /* Navigation เหมือน index */
        nav { background: #000; padding: 0; }
        .nav-content { max-width: 1200px; margin: 0 auto; padding: 0 20px; display: flex; overflow-x: auto; }
        .nav-content a { color: white; text-decoration: none; padding: 18px 20px; font-size: 1rem; font-weight: 600; white-space: nowrap; transition: 0.3s; }
        .nav-content a:hover, .nav-content a.active { background-color: #cc0000; }

        /* News Cards ปรับปรุงให้เรียง 3 คอลัมน์เหมือน index */
        .page-title { font-size: 1.6rem; font-weight: bold; color: #000; border-left: 5px solid #cc0000; padding-left: 15px; margin: 30px 0; }
        .news-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 20px; }
        .news-card { background: white; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.03); overflow: hidden; transition: all 0.3s; display: flex; flex-direction: column; border: 1px solid #eee; }
        .news-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .news-img-wrapper { width: 100%; height: 180px; overflow: hidden; }
        .news-img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s; }
        .news-card:hover .news-img { transform: scale(1.1); }
        .news-content { padding: 15px; flex-grow: 1; display: flex; flex-direction: column; }
        .news-title { margin: 0 0 10px 0; font-size: 1rem; line-height: 1.4; font-weight: bold; }
        .news-title a { text-decoration: none; color: #000; transition: 0.3s; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .news-title a:hover { color: #cc0000; }
        .news-meta { color: #777; font-size: 0.8rem; margin-top: auto; padding-top: 10px; border-top: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        
        .no-result { text-align: center; padding: 60px 20px; background: white; border-radius: 8px; grid-column: 1/-1; border: 1px solid #eee; }
        .no-result h3 { font-size: 1.5rem; color: #cc0000; }

        /* Pagination */
        .pagination { display: flex; justify-content: center; gap: 5px; margin-top: 40px; margin-bottom: 30px; }
        .pagination a, .pagination span { padding: 10px 16px; border: 1px solid #ddd; text-decoration: none; color: #333; border-radius: 5px; font-weight: bold; }
        .pagination a:hover { background-color: #cc0000; color: white; border-color: #cc0000; }
        .pagination .active { background-color: #000; color: white; border-color: #000; }
        
        /* Footer เหมือน index */
        footer { background-color: #222; color: #fff; padding: 40px 0; margin-top: 50px; font-size: 0.9rem; }
        .footer-content { max-width: 1200px; margin: 0 auto; padding: 0 20px; display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 30px; }
        .footer-logo img { height: 60px; margin-bottom: 15px; }
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
        }
    </style>
</head>
<body>

<header>
    <div class="header-content">
        <div class="logo-area">
            <img src="logo.png" alt="Faculty Logo">                
            <div class="site-title">
                <h1>Faculty News</h1>
                <div class="sub-title">ข่าวประชาสัมพันธ์</div>
            </div>
        </div>
        <form action="search.php" method="GET" class="search-box">
            <input type="text" name="keyword" placeholder="ค้นหาข่าว..." value="<?=htmlspecialchars($keyword)?>" required>
            <button type="submit">ค้นหา</button>
        </form>
    </div>
</header>

<nav>
    <div class="nav-content">
        <a href="index.php">หน้าแรก</a>
        <?php 
        if($categories->num_rows > 0) {
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
    <div class="page-title">
        ผลการค้นหาสำหรับ: "<?=htmlspecialchars($keyword)?>"
    </div>
    
    <div class="news-grid">
        <?php if(!empty($keyword) && $news_result && $news_result->num_rows > 0): ?>
            <?php while($row = $news_result->fetch_assoc()): 
                // ดึงรูปภาพ 1 รูป
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
            <div class="no-result">
                <h3>ไม่พบผลลัพธ์ที่ตรงกับคำค้นหาของคุณ</h3>
                <p>ลองใช้คำค้นหาอื่น เช่น "มหาวิทยาลัย", "กิจกรรม"</p>
            </div>
        <?php endif; ?>
    </div>

    <?php if(!empty($keyword) && $news_result && $news_result->num_rows > 0): ?>
    <div class="pagination">
        <?php if($page > 1): ?>
            <a href="?keyword=<?=urlencode($keyword)?>&page=<?=$page-1?>">&laquo; ก่อนหน้า</a>
        <?php endif; ?>
        
        <?php for($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?keyword=<?=urlencode($keyword)?>&page=<?=$i?>" class="<?=($page == $i) ? 'active' : ''?>"><?=$i?></a>
        <?php endfor; ?>
        
        <?php if($page < $total_pages): ?>
            <a href="?keyword=<?=urlencode($keyword)?>&page=<?=$page+1?>">ถัดไป &raquo;</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
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
                <li><a href="admin/login.php">เข้าสู่ระบบจัดการข่าว</a></li>
                <li><a href="#">คู่มือการใช้งาน</a></li>
            </ul>
        </div>
    </div>
    <div class="copyright">
        &copy; 2026 Faculty News. All Rights Reserved.
    </div>
</footer>

</body>
</html>