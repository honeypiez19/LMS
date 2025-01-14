<?php

include '../connect.php';
date_default_timezone_set('Asia/Bangkok');

// ตรวจสอบว่าข้อมูลถูกส่งมาด้วย POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $createDateTime = $_POST['createDateTime'] ?? '';
    $userCode = $_POST['userCode'] ?? '';
    $canDatetime = date('Y-m-d H:i:s');
    $nameCan = $_POST['nameCan'] ?? '';

    // ตรวจสอบข้อมูล
    if (!empty($createDateTime) && !empty($userCode)) {
        try {

            // อัปเดตสถานะเป็น "canceled"
            $sql = "UPDATE leave_list
                    SET l_leave_status = 1,
                    l_cancel_datetime = :canDatetime,
                    l_hr_cancel_name = :nameCan,
                    l_hr_cancel_datetime = :canDatetime
                    WHERE l_create_datetime = :createDateTime
                    AND l_usercode = :userCode";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':createDateTime', $createDateTime);
            $stmt->bindParam(':canDatetime', $canDatetime);
            $stmt->bindParam(':userCode', $userCode);
            $stmt->bindParam(':nameCan', $nameCan);

            if ($stmt->execute()) {
                echo 'success'; // ส่งกลับไปยัง AJAX
            } else {
                echo 'error';
            }
        } catch (PDOException $e) {
            // แสดงข้อความข้อผิดพลาด
            echo 'error: ' . $e->getMessage();
        }
    } else {
        echo 'invalid'; // กรณีข้อมูลไม่ครบ
    }
} else {
    echo 'invalid'; // กรณีไม่ได้ใช้ POST
}