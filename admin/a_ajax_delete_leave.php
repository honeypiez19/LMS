<?php

include '../connect.php';
include '../access_token_channel.php';

date_default_timezone_set('Asia/Bangkok');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $createDateTime = $_POST['createDateTime'] ?? '';
    $userCode       = $_POST['userCode'] ?? '';
    $canDatetime    = date('Y-m-d H:i:s');
    $nameCan        = $_POST['nameCan'] ?? '';

    $leaveType      = $_POST['leaveType'] ?? '';
    $leaveReason    = $_POST['leaveReason'] ?? '';
    $leaveStartDate = $_POST['leaveStartDate'] ?? '';
    $leaveEndDate   = $_POST['leaveEndDate'] ?? '';

    // ตรวจสอบข้อมูล
    if (! empty($createDateTime) && ! empty($userCode)) {
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
                // ดึง e_user_id (LINE userId) จากตาราง employees
                $userIdQuery = "SELECT e_user_id FROM employees WHERE e_usercode = :userCode";
                $stmtUserId  = $conn->prepare($userIdQuery);
                $stmtUserId->bindParam(':userCode', $userCode);
                $stmtUserId->execute();
                $employee = $stmtUserId->fetch(PDO::FETCH_ASSOC);

                if ($employee && ! empty($employee['e_user_id'])) {
                    $userId  = $employee['e_user_id'];
                    $url     = 'https://lms.system-samt.com/';
                    $message = "HR ได้ยกเลิกใบลาของคุณเรียบร้อย\nประเภทการลา : $leaveType \nเหตุผลการลา : $leaveReason \nวันเวลาที่ลา : $leaveStartDate ถึง $leaveEndDate กรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $url";

                    // สร้าง payload สำหรับส่งข้อความ
                    $data = [
                        'to'       => $userId,
                        'messages' => [
                            [
                                'type' => 'text',
                                'text' => $message,
                            ],
                        ],
                    ];

                    $post = json_encode($data);

                    $ch = curl_init("https://api.line.me/v2/bot/message/push");
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        'Content-Type: application/json',
                        'Authorization: Bearer ' . $access_token,
                    ]);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    $result = curl_exec($ch);

                    if (curl_error($ch)) {
                        echo 'Error: ' . curl_error($ch);
                    } else {
                        $response = json_decode($result, true);
                        if (isset($response['message'])) {
                            echo 'LINE Messaging API Error: ' . $response['message'];
                        } else {
                            echo 'success';
                        }
                    }
                    curl_close($ch);
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
