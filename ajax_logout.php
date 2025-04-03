<?php
// เชื่อมต่อฐานข้อมูล
include 'connect.php';
session_start();

date_default_timezone_set('Asia/Bangkok'); // เวลาไทย

$dateLogout = date("Y-m-d H:i:s");

// รับค่าชื่อผู้ใช้จาก Ajax request
$username = $_POST['Username'];

// ทำการ insert dateLogout ลง table users คอลัมน์ Date_logout
$updDateLogout = "UPDATE session SET s_logout_datetime = '$dateLogout' WHERE s_username = '$username'";
$conn->query($updDateLogout);

// Clear session
$_SESSION = []; // ล้างตัวแปร session ทั้งหมด

// ลบคุกกี้ session ถ้ามีการใช้
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// ทำลาย session
session_destroy();

echo "success";

// ปิดการเชื่อมต่อกับฐานข้อมูล
$conn = null;