<?php
session_start();
date_default_timezone_set('Asia/Bangkok'); // ตั้งโซนเวลาเป็น Asia/Bangkok

include '../connect.php'; // รวมไฟล์เชื่อมต่อฐานข้อมูล

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userCode = $_SESSION['s_usercode'];
    $newPhone = $_POST['newPhone'] ?? '';

    $sql  = "UPDATE users SET phone = :phone WHERE user_id = :user_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':phone', $newPhone, PDO::PARAM_STR);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "เปลี่ยนเบอร์โทรศัพท์สำเร็จ"]);
    } else {
        echo json_encode(["status" => "error", "message" => "เกิดข้อผิดพลาดในการอัปเดต"]);
    }

} else {
    echo "<div class='alert alert-danger'>ข้อมูลไม่ถูกต้อง</div>";
}