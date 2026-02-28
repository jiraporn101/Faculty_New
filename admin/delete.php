<?php
require_once "auth.php"; 
require_once "../db_connect.php"; 

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "../db_connect.php";

// ตรวจสอบ id
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = intval($_GET['id']);

$sql = "DELETE FROM catbreeds WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

// กลับหน้า index
header("Location: ../index.php");
exit;
