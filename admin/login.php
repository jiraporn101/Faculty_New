<?php
session_start();
require_once "../db_connect.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $user = trim($_POST['username']);
    $pass = trim($_POST['password']);

    $stmt = mysqli_prepare($conn,"SELECT * FROM admins WHERE username=? LIMIT 1");
    mysqli_stmt_bind_param($stmt,"s",$user);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);

    // ✅ เทียบรหัสแบบธรรมดา
    if ($row && $pass === $row['password']) {

        $_SESSION['admin_id'] = $row['id'];

        // 🔥 เด้งไป index
        header("Location: ../index.php");
        exit;

    } else {
        $error = "ชื่อผู้ใช้หรือรหัสผ่านผิด";
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>Admin Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
<div class="card col-md-4 mx-auto shadow">
<div class="card-body">

<h4 class="text-center mb-3">🔐 Admin Login</h4>

<?php if($error): ?>
<div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<form method="post">
<input name="username" class="form-control mb-2" placeholder="Username" required>
<input type="password" name="password" class="form-control mb-3" placeholder="Password" required>
<button class="btn btn-primary w-100">Login</button>
</form>



</div>
</div>
</div>

</body>
</html>
