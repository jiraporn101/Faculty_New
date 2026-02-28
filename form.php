<?php
require_once "admin/auth.php"; 
// ตรวจสอบว่าได้ Login หรือยัง
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เพิ่มข้อมูลแมว</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { background-color: #f4f7f6; font-family: 'Sarabun', sans-serif; }
        .card { border: none; border-radius: 20px; }
        .card-header { background: #5f9ea0 !important; color: white; border-radius: 20px 20px 0 0 !important; padding: 20px; }
        .form-label { font-weight: 600; color: #555; }
        .form-control, .form-select { border-radius: 10px; border: 1px solid #ddd; padding: 12px; }

        /* ✅ FIX CSS */
        .form-control:focus {
            border-color: #5f9ea0;
            box-shadow: 0 0 0 0.25rem rgba(95, 158, 160, 0.25);
        }

        .btn-save { background-color: #5f9ea0; border: none; padding: 12px; border-radius: 10px; color: white; font-weight: 600; }
        .btn-save:hover { background-color: #4a7c7d; color: white; }
    </style>
</head>
<body>

<div class="container mt-5 mb-5">
    <div class="card shadow-sm col-md-8 mx-auto">
        <div class="card-header text-center">
            <h4 class="mb-0">✨ เพิ่มข้อมูลสายพันธุ์แมวใหม่</h4>
        </div>
        <div class="card-body p-4">
            <form action="save.php" method="post" enctype="multipart/form-data">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">ชื่อสายพันธุ์ (ไทย)</label>
                        <input type="text" name="name_th" class="form-control" placeholder="เช่น แมวสีสวาด" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">ชื่อสายพันธุ์ (อังกฤษ)</label>
                        <input type="text" name="name_en" class="form-control" placeholder="เช่น Korat Cat" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">คำอธิบาย</label>
                    <textarea name="description" class="form-control" rows="3" required></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">ลักษณะและพฤติกรรม</label>
                    <textarea name="characteristics" class="form-control" rows="3"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">คำแนะนำการเลี้ยงดู</label>
                    <textarea name="care_instructions" class="form-control" rows="3"></textarea>
                </div>

                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="form-label">รูปภาพสายพันธุ์ (หลายรูปได้)</label>

                        <!-- ✅ FIX: รองรับหลายรูป -->
                        <input type="file" name="images[]" class="form-control" accept="image/*" multiple>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">สถานะการแสดงผล</label>
                        <select name="is_visible" class="form-select">
                            <option value="1">✅ แสดง</option>
                            <option value="0">❌ ไม่แสดง</option>
                        </select>
                    </div>
                </div>

                <hr class="my-4" style="opacity: 0.1;">

                <div class="d-flex gap-2">
                    <a href="index.php" class="btn btn-light w-25 border">ยกเลิก</a>
                    <button type="submit" class="btn btn-save w-75">บันทึกข้อมูลแมว</button>
                </div>

            </form>
        </div>
    </div>
</div>

</body>
</html>
