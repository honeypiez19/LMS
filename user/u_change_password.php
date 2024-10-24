<?php
session_start();
date_default_timezone_set('Asia/Bangkok'); // ตั้งโซนเวลาเป็น Asia/Bangkok

include '../connect.php'; // รวมไฟล์เชื่อมต่อฐานข้อมูล

if (isset($_POST['newPassword']) && isset($_POST['confirmNewPassword'])) {
    $userCode = $_SESSION['s_usercode'];
    $newPassword = $_POST['newPassword'];
    $confirmNewPassword = $_POST['confirmNewPassword'];

    // ตรวจสอบให้แน่ใจว่ารหัสผ่านใหม่และการยืนยันรหัสผ่านตรงกัน
    if ($newPassword !== $confirmNewPassword) {
        echo "<div class='alert alert-danger'>รหัสผ่านใหม่ไม่ตรงกัน</div>";
    } else {

        // อัปเดตรหัสผ่านใหม่ในฐานข้อมูล
        $sqlNewPass = "UPDATE session SET s_password = :newPassword WHERE s_usercode = :userCode";
        $newPassStmt = $conn->prepare($sqlNewPass);
        $newPassStmt->bindParam(':newPassword', $newPassword, PDO::PARAM_STR);
        $newPassStmt->bindParam(':userCode', $userCode, PDO::PARAM_STR);
        $newPassStmt->execute();

         // อัปเดตรหัสผ่านในตาราง employees
        $sqlNewPassEmp = "UPDATE employees SET e_password = :newPassword WHERE e_usercode = :userCode"; // แก้ไขให้ใช้ e_usercode
        $newPassEmpStmt = $conn->prepare($sqlNewPassEmp);
        $newPassEmpStmt->bindParam(':newPassword', $newPassword, PDO::PARAM_STR);
        $newPassEmpStmt->bindParam(':userCode', $userCode, PDO::PARAM_STR); // ตรวจสอบให้แน่ใจว่าใช้ user code ที่ถูกต้อง
        $newPassEmpStmt->execute();
        // echo "<div class='alert alert-success'>เปลี่ยนรหัสผ่านเรียบร้อยแล้ว</div>";
        echo "เปลี่ยนรหัสผ่านใหม่สำเร็จ";
    }
} else {
    echo "<div class='alert alert-danger'>ข้อมูลไม่ถูกต้อง</div>";
}
?>