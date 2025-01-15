<?php

include '../connect.php';
date_default_timezone_set('Asia/Bangkok');

// ตรวจสอบว่าข้อมูลถูกส่งมาด้วย POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $createDateTime = $_POST['createDateTime'] ?? '';
    $userCode = $_POST['userCode'] ?? '';
    $canDatetime = date('Y-m-d H:i:s');
    $nameCan = $_POST['nameCan'] ?? '';

    $leaveType = $_POST['leaveType'] ?? '';
    $leaveReason = $_POST['leaveReason'] ?? '';
    $leaveStartDate = $_POST['leaveStartDate'] ?? '';
    $leaveEndDate = $_POST['leaveEndDate'] ?? '';

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
                // ดึง e_token จากตาราง employees
                $tokenQuery = "SELECT e_token FROM employees WHERE e_usercode = :userCode";
                $stmtToken = $conn->prepare($tokenQuery);
                $stmtToken->bindParam(':userCode', $userCode);
                $stmtToken->execute();
                $employee = $stmtToken->fetch(PDO::FETCH_ASSOC);

                if ($employee && !empty($employee['e_token'])) {
                    $sToken = $employee['e_token'];
                    $url = 'https://lms.system-samt.com/';
                    $sMessage = "HR ได้ยกเลิกใบลาของคุณเรียบร้อย\nประเภทการลา : $leaveType \nเหตุผลการลา : $leaveReason \nวันเวลาที่ลา : $leaveStartDate ถึง $leaveEndDate กรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $url";

                    $chOne = curl_init();
                    curl_setopt($chOne, CURLOPT_URL, "https://notify-api.line.me/api/notify");
                    curl_setopt($chOne, CURLOPT_SSL_VERIFYHOST, 0);
                    curl_setopt($chOne, CURLOPT_SSL_VERIFYPEER, 0);
                    curl_setopt($chOne, CURLOPT_POST, 1);
                    curl_setopt($chOne, CURLOPT_POSTFIELDS, "message=" . urlencode($sMessage));
                    $headers = array(
                        'Content-type: application/x-www-form-urlencoded',
                        'Authorization: Bearer ' . $sToken,
                    );
                    curl_setopt($chOne, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($chOne, CURLOPT_RETURNTRANSFER, 1);
                    $result = curl_exec($chOne);

                    if (curl_error($chOne)) {
                        echo 'Error: ' . curl_error($chOne);
                    } else {
                        $result_ = json_decode($result, true);
                        if ($result_['status'] == 200) {
                            echo 'success';
                        } else {
                            echo 'LINE Notify Error: ' . $result_['message'];
                        }
                    }
                    curl_close($chOne);
                } else {
                    echo 'success (no LINE notification)';
                }
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