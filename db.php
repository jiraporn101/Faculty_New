<?php
$host = "localhost";
$user = "it67040233101"; // ปรับตาม hosting
$pass = "S9Y0Z6Z2";     // ปรับตาม hosting
$db   = "it67040233101";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
$conn->set_charset("utf8mb4");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>