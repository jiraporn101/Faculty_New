<?php
// 1. เชื่อมต่อฐานข้อมูล
require "../config/db.php"; // ปรับ path ให้ถูกต้อง

// 2. จัดการฟังก์ชัน เพิ่ม / แก้ไข / ลบ (CRUD)
$message = "";

// --- เพิ่มหมวดหมู่ใหม่ ---
if (isset($_POST['add_category'])) {
    $name = $_POST['name'];
    if (!empty($name)) {
        $stmt = $conn->prepare("INSERT INTO fn_categories (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        if ($stmt->execute()) {
            $message = "<div style='color:green;'>เพิ่มหมวดหมู่สำเร็จ</div>";
        } else {
            $message = "<div style='color:red;'>ข้อผิดพลาด: " . $stmt->error . "</div>";
        }
    }
}

// --- ลบหมวดหมู่ ---
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM fn_categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = "<div style='color:green;'>ลบหมวดหมู่สำเร็จ</div>";
    }
}

// 3. ดึงข้อมูลหมวดหมู่ทั้งหมดมาแสดง
$categories = $conn->query("SELECT * FROM fn_categories ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการหมวดหมู่ - Admin</title>
    <style>
        body { font-family: sans-serif; background-color: #f4f4f4; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f8f8f8; }
        .form-group { margin-bottom: 15px; }
        input[type="text"] { padding: 8px; width: 250px; }
        button { padding: 8px 12px; cursor: pointer; }
        .btn-add { background-color: #28a745; color: white; border: none; }
        .btn-delete { background-color: #dc3545; color: white; text-decoration: none; padding: 5px 10px; border-radius: 3px; }
    </style>
</head>
<body>

<div class="container">
    <h2>จัดการหมวดหมู่ข่าว</h2>
    <?= $message ?>

    <form method="POST" action="">
        <div class="form-group">
            <input type="text" name="name" placeholder="ชื่อหมวดหมู่ใหม่" required>
            <button type="submit" name="add_category" class="btn-add">เพิ่มหมวดหมู่</button>
        </div>
    </form>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>ชื่อหมวดหมู่</th>
                <th>จัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $categories->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td>
                    <a href="categories.php?delete=<?= $row['id'] ?>" class="btn-delete" onclick="return confirm('ยืนยันการลบ?')">ลบ</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <br>
    <a href="index.php">กลับหน้าหลัก Admin</a>
</div>

</body>
</html>