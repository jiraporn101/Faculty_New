<?php
require "../config/db.php";
require "../auth/check.php";

// 1. ดึงข้อมูลจำนวนข่าวทั้งหมด
$count_news = $conn->query("SELECT COUNT(*) c FROM fn_news")->fetch_assoc()['c'];

// 2. ดึงข้อมูลข่าวที่ยอดนิยมที่สุด
$top_news = $conn->query("SELECT title, views FROM fn_news ORDER BY views DESC LIMIT 1")->fetch_assoc();

// 3. ดึงข้อมูลแยกตามหมวดหมู่
$sql_cat = "SELECT c.name, COUNT(n.id) as total 
            FROM fn_categories c 
            LEFT JOIN fn_news n ON c.id = n.category_id 
            GROUP BY c.id";
$category_stats = $conn->query($sql_cat);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | White Red Black Edition</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-light: #f4f7f6;      /* พื้นหลังขาวนวล */
            --card-white: #ffffff;    /* ขาวบริสุทธิ์ */
            --accent-red: #e63946;    /* แดงสว่าง */
            --dark-black: #1d3557;    /* ดำน้ำเงินเข้ม */
            --text-main: #333333;
        }

        body { 
            font-family: 'Sarabun', sans-serif; 
            background-color: var(--bg-light); 
            color: var(--text-main);
            margin: 0; 
            padding: 40px 20px; 
        }

        .dashboard-container { max-width: 1100px; margin: 0 auto; }

        /* Header Styling */
        header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 40px;
            border-bottom: 3px solid var(--dark-black);
            padding-bottom: 20px;
        }

        h1 { font-weight: 800; margin: 0; font-size: 2rem; color: var(--dark-black); letter-spacing: -1px; }
        h1 span { color: var(--accent-red); }

        /* Stats Grid */
        .stats-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); 
            gap: 25px; 
            margin-bottom: 40px; 
        }

        .stat-card { 
            background: var(--card-white); 
            padding: 30px; 
            border-radius: 20px; 
            box-shadow: 0 10px 20px rgba(0,0,0,0.05); 
            border: 1px solid #e0e0e0;
            position: relative;
            transition: all 0.3s ease;
        }

        .stat-card:hover { 
            transform: translateY(-8px); 
            box-shadow: 0 15px 30px rgba(230, 57, 70, 0.1);
            border-color: var(--accent-red);
        }

        .stat-icon {
            position: absolute;
            right: 25px;
            bottom: 25px;
            font-size: 3.5rem;
            color: #f1f1f1;
            z-index: 0;
        }

        .stat-content { position: relative; z-index: 1; }

        .stat-title { font-size: 0.9rem; color: #666; text-transform: uppercase; font-weight: 700; margin-bottom: 10px; display: block; }
        .stat-value { font-size: 3rem; font-weight: 800; color: var(--dark-black); line-height: 1; }
        .stat-value span { font-size: 1.2rem; color: #999; font-weight: 400; }

        .top-news-title {
            font-size: 1.15rem;
            font-weight: 600;
            margin-top: 10px;
            color: var(--text-main);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* Category Section */
        .section-title { 
            font-size: 1.3rem; 
            font-weight: 700; 
            margin-bottom: 25px; 
            color: var(--dark-black);
            display: flex; 
            align-items: center; 
            gap: 12px;
        }
        .section-title::after { content: ""; flex: 1; height: 2px; background: #e0e0e0; }

        .category-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); 
            gap: 20px; 
            margin-bottom: 50px;
        }

        .category-card { 
            background: var(--card-white); 
            padding: 20px; 
            border-radius: 15px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            border-left: 5px solid var(--dark-black);
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        }

        .category-card:hover { border-left-color: var(--accent-red); background: #fffcfc; }

        .cat-name { font-weight: 600; color: var(--dark-black); }
        .cat-count { 
            background: var(--dark-black); 
            padding: 6px 14px; 
            border-radius: 10px; 
            font-size: 0.9rem; 
            font-weight: 700; 
            color: #fff; 
        }

        .category-card:hover .cat-count { background: var(--accent-red); }

        /* Bottom Menu */
        .menu-container { 
            background: var(--dark-black);
            padding: 40px; 
            border-radius: 25px; 
            text-align: center; 
            box-shadow: 0 10px 30px rgba(29, 53, 87, 0.2);
        }

        .btn-manage { 
            display: inline-flex;
            align-items: center;
            gap: 15px;
            padding: 18px 50px; 
            text-decoration: none; 
            border-radius: 12px; 
            font-weight: 700; 
            background-color: var(--accent-red); 
            color: white; 
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            font-size: 1.1rem;
        }

        .btn-manage:hover { 
            background-color: #ffffff; 
            color: var(--accent-red);
            transform: scale(1.05);
        }

    </style>
</head>
<body>

<div class="dashboard-container">
    <header>
        <h1>DASHBOARD<span>.ADMIN</span></h1>
        <div class="user-info">
            <span style="font-weight: 600; color: var(--dark-black);">
                <i class="bi bi-person-badge-fill me-1"></i> ยินดีต้อนรับ ผู้ดูแลระบบ
            </span>
        </div>
    </header>
    
    <div class="stats-grid">
        <div class="stat-card">
            <i class="bi bi-journal-text stat-icon"></i>
            <div class="stat-content">
                <span class="stat-title">News Overview</span>
                <div class="stat-value">
                    <?=number_format($count_news)?> <span>เรื่อง</span>
                </div>
            </div>
        </div>
        
        <div class="stat-card">
            <i class="bi bi-graph-up-arrow stat-icon"></i>
            <div class="stat-content">
                <span class="stat-title">Popular Content</span>
                <div class="top-news-title">
                    <?=htmlspecialchars($top_news['title'] ?? '-')?>
                </div>
                <div style="margin-top: 12px; font-size: 1rem; color: var(--accent-red); font-weight: 800;">
                    <i class="bi bi-eye-fill me-1"></i> <?=number_format($top_news['views'] ?? 0)?> Views
                </div>
            </div>
        </div>
    </div>

    <div class="section-title">
        <i class="bi bi-tags-fill me-2" style="color: var(--accent-red);"></i> Categories Breakdown
    </div>
    
    <div class="category-grid">
        <?php while($cat = $category_stats->fetch_assoc()): ?>
            <div class="category-card">
                <span class="cat-name">
                    <?=htmlspecialchars($cat['name'])?>
                </span>
                <span class="cat-count"><?=number_format($cat['total'])?></span>
            </div>
        <?php endwhile; ?>
    </div>
    
    <div class="menu-container">
        <a href="news_list.php" class="btn-manage">
            <i class="bi bi-plus-circle-dotted"></i> จัดการเนื้อหาข่าวสาร
        </a>
    </div>
</div>

</body>
</html>