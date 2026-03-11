<?php
session_start();
require_once "../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = $_POST['username'] ?? '';
    $p = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT * FROM fn_users WHERE username = ?");
    $stmt->bind_param("s", $u);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && $p === $user['password']) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        header("Location: ../admin/dashboard.php");
        exit();
    } else {
        $error = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Premium News System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #1a1a1a; /* ดำเข้ม */
            --accent-red: #cc0000; /* แดงสดเข้ม */
            --soft-white: #f8f9fa;
        }

        body {
            height: 100vh;
            margin: 0;
            display: flex;
            flex-direction: column;
            font-family: 'Sarabun', sans-serif;
            background-color: #fff;
        }

        /* Header หรูหรา */
        .login-header {
            padding: 15px 10%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #eee;
        }

        .header-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        /* โลโก้ใหม่: วงกลมดำขอบแดง */
        .logo-icon-box {
            background: var(--bg-dark);
            color: white;
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            border-bottom: 3px solid var(--accent-red);
            font-size: 1.5rem;
        }

        .header-logo span { 
            font-size: 1.4rem; 
            font-weight: 700; 
            color: var(--bg-dark);
            letter-spacing: -0.5px;
        }

        /* พื้นหลังส่วน Login Gradient ดำ-แดง */
        .login-main {
            flex-grow: 1;
            background: radial-gradient(circle at center, #333 0%, #000 100%);
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 10%;
            overflow: hidden;
        }

        /* เพิ่มลูกเล่นเส้นแสงสีแดงในพื้นหลัง */
        .login-main::before {
            content: "";
            position: absolute;
            width: 200%;
            height: 200%;
            background: repeating-linear-gradient(45deg, transparent, transparent 100px, rgba(204, 0, 0, 0.03) 100px, rgba(204, 0, 0, 0.03) 200px);
        }

        .login-container {
            width: 100%;
            max-width: 1100px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1;
        }

        /* ฝั่งซ้าย: Brand Styling */
        .brand-section {
            color: white;
            max-width: 500px;
        }

        .brand-tag {
            background: var(--accent-red);
            color: white;
            padding: 5px 15px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 20px;
            display: inline-block;
        }

        .brand-section h1 { 
            font-size: 3.5rem; 
            font-weight: 800; 
            line-height: 1.1;
            margin-bottom: 20px;
        }

        .brand-section h1 span { color: var(--accent-red); }

        .brand-section p { 
            font-size: 1.1rem; 
            opacity: 0.7; 
            border-left: 3px solid var(--accent-red);
            padding-left: 20px;
        }

        /* ฝั่งขวา: การ์ด Login สะอาดตา */
        .login-card {
            width: 420px;
            padding: 40px;
            border-radius: 16px;
            background: white;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
        }

        .login-card h3 {
            font-weight: 700;
            margin-bottom: 30px;
            color: var(--bg-dark);
            text-align: center;
        }

        .input-group-text {
            background: transparent;
            border-right: none;
            color: #888;
        }

        .form-control {
            height: 50px;
            border-radius: 8px;
            border-left: none;
        }

        .form-control:focus {
            border-color: #ced4da;
            box-shadow: none;
        }

        .btn-login {
            width: 100%;
            height: 50px;
            background: var(--bg-dark);
            border: none;
            color: white;
            font-weight: 600;
            border-radius: 8px;
            transition: 0.3s;
            margin-top: 10px;
        }

        .btn-login:hover {
            background: var(--accent-red);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(204, 0, 0, 0.3);
        }

        .contact-admin {
            text-align: center;
            margin-top: 25px;
            font-size: 0.85rem;
            color: #666;
        }

        .contact-admin a {
            color: var(--accent-red);
            text-decoration: none;
            font-weight: 700;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .brand-section { display: none; }
            .login-container { justify-content: center; }
        }
    </style>
</head>
<body>

    <div class="login-header">
        <a href="#" class="header-logo">
            <div class="logo-icon-box">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
            <span>ADMIN<span>PANEL</span></span>
        </a>
        <div class="d-none d-md-block">
            <span class="text-muted small">ระบบจัดการข้อมูลภายใน v2.0</span>
        </div>
    </div>

    <div class="login-main">
        <div class="login-container">
            
            <div class="brand-section">
                <div class="brand-tag">Premium Experience</div>
                <h1>NEWS<span>HUB</span><br>SYSTEM</h1>
                <p>ยกระดับการจัดการข่าวสารของคุณด้วยระบบหลังบ้านที่ทรงพลัง รวดเร็ว และปลอดภัยที่สุด</p>
            </div>

            <div class="login-card">
                <h3>เข้าสู่ระบบ</h3>

                <?php if(isset($error)): ?>
                    <div class="alert alert-danger py-2 border-0 mb-4" style="font-size: 0.85rem; background: #fff5f5; color: #c00;">
                        <i class="bi bi-x-circle-fill me-2"></i> <?= $error ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input name="username" class="form-control" placeholder="ชื่อผู้ใช้ของคุณ" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between">
                            <label class="form-label small fw-bold">Password</label>
                            
                        </div>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-key"></i></span>
                            <input name="password" type="password" class="form-control" placeholder="รหัสผ่าน 6 หลักขึ้นไป" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-login">
                        Login to Dashboard <i class="bi bi-arrow-right ms-2"></i>
                    </button>
                    
                    <div class="contact-admin">
                        ไม่สามารถเข้าสู่ระบบได้? <a href="#">ติดต่อฝ่ายไอที</a>
                    </div>
                </form>
            </div>

        </div>
    </div>

</body>
</html>