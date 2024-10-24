<?php
session_start();
date_default_timezone_set('Asia/Bangkok'); // ตั้งโซนเวลาเป็น Asia/Bangkok

include 'connect.php'; // รวมไฟล์เชื่อมต่อฐานข้อมูล

if (isset($_POST['newPassword']) && isset($_POST['confirmNewPassword'])) {
    $userCode = $_POST['userCode']; // รับ user code จาก session
    $newPassword = $_POST['newPassword'];
    $confirmNewPassword = $_POST['confirmNewPassword'];

    // ตรวจสอบให้แน่ใจว่ารหัสผ่านใหม่และการยืนยันรหัสผ่านตรงกัน
    if ($newPassword !== $confirmNewPassword) {
        echo "<div class='alert alert-danger'>รหัสผ่านใหม่ไม่ตรงกัน</div>";
        exit; // ออกจากสคริปต์เมื่อรหัสผ่านไม่ตรงกัน
    }

    // อัปเดตรหัสผ่านใหม่ในตาราง session
    $sqlNewPass = "UPDATE session SET s_password = :newPassword WHERE s_usercode = :userCode";
    $newPassStmt = $conn->prepare($sqlNewPass);
    $newPassStmt->bindParam(':newPassword', $newPassword, PDO::PARAM_STR);
    $newPassStmt->bindParam(':userCode', $userCode, PDO::PARAM_STR);
    
    if ($newPassStmt->execute()) {
        // อัปเดตรหัสผ่านในตาราง employees ด้วย
        $sqlUpdateEmployees = "UPDATE employees SET e_password = :newPassword WHERE e_usercode = :userCode";
        $updateStmt = $conn->prepare($sqlUpdateEmployees);
        $updateStmt->bindParam(':newPassword', $newPassword, PDO::PARAM_STR);
        $updateStmt->bindParam(':userCode', $userCode, PDO::PARAM_STR);
        
        if ($updateStmt->execute()) {
            echo "เปลี่ยนรหัสผ่านใหม่สำเร็จ";
        } else {
            echo "<div class='alert alert-danger'>เกิดข้อผิดพลาดในการอัปเดตรหัสผ่านในตาราง employees</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>เกิดข้อผิดพลาดในการอัปเดตรหัสผ่านในตาราง session</div>";
    }
} else {
    echo "<div class='alert alert-danger'>ข้อมูลไม่ถูกต้อง</div>";
}
?>