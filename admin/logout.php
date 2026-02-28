<?php
session_start();
session_destroy();

// เด้งไปหน้า login
header("Location: login.php");
exit;
