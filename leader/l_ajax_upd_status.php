<?php
// Start session
session_start();
date_default_timezone_set('Asia/Bangkok'); // Set timezone to Thailand

include '../connect.php';
include '../access_token_channel.php';
// Include database connection file

$appDate = date("Y-m-d H:i:s");

// รับค่าที่ส่งมาจาก AJAX
$userCode       = $_POST['userCode'];
$createDate     = $_POST['createDate'];
$status         = $_POST['status'];
$empName        = $_POST['empName'];
$userName       = $_POST['userName'];
$proveName      = $_POST['proveName'];
$leaveType      = $_POST['leaveType'];
$leaveReason    = $_POST['leaveReason'];
$leaveStartDate = $_POST['leaveStartDate'];
$leaveEndDate   = $_POST['leaveEndDate'];
$depart         = $_POST['depart'];
$leaveStatus    = $_POST['leaveStatus'];
$reasonNoProve  = $_POST['reasonNoProve'];
// $workplace = $_POST['workplace'];

$level = $_POST['level'];

// อนุมัติ
if ($status == '2') {
    // อัปเดตสถานะการลาในฐานข้อมูล
    $sql = "UPDATE leave_list SET l_approve_status = :status, l_approve_datetime = :appDate, l_approve_name = :userName
            WHERE l_usercode = :userCode AND l_create_datetime = :createDate";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':status'     => $status,
        ':appDate'    => $appDate,
        ':userName'   => $userName,
        ':userCode'   => $userCode,
        ':createDate' => $createDate,
    ]);

    // แจ้งเตือน พนง
    $stmt = $conn->prepare("SELECT e_user_id FROM employees WHERE e_usercode = :userCode");
    $stmt->execute([':userCode' => $userCode]);
    $userId = $stmt->fetchColumn(); // This should be the LINE user ID, not a token
    $sURL   = 'https://lms.system-samt.com/';

    // ข้อความแจ้งเตือน
    $message = "$proveName อนุมัติใบลา \nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nวันเวลาที่ลา : $leaveStartDate ถึง $leaveEndDate\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $sURL";
    if ($leaveStatus == 'ยกเลิกใบลา') {
        $message = "$proveName อนุมัติยกเลิกใบลาของ $empName\nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nวันเวลาที่ลา : $leaveStartDate ถึง $leaveEndDate\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $sURL";
    }

    // ส่งข้อความผ่าน LINE Messaging API ไปยังพนักงาน
    if ($userId) {

        // สร้าง message data
        $messageData = [
            'to'       => $userId,
            'messages' => [
                [
                    'type' => 'text',
                    'text' => $message,
                ],
            ],
        ];

        // แปลงข้อมูลเป็น JSON
        $jsonData = json_encode($messageData);

        // ส่งข้อความด้วย cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.line.me/v2/bot/message/push');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $access_token,
        ]);

        $result = curl_exec($ch);
        if (curl_error($ch)) {
            echo 'Error: ' . curl_error($ch);
        } else {
            $responseData = json_decode($result, true);
            if (isset($responseData['message'])) {
                echo "Error message: " . $responseData['message'] . "\n";
            } else {
                echo "Message sent successfully\n";
            }
        }
        curl_close($ch);
    }

    // แจ้งเตือนผู้จัดการตามแผนก
    if ($level == 'leader') {
        if ($depart == 'Office') {
            $stmt = $conn->prepare("SELECT e_user_id, e_username FROM employees WHERE e_level = 'manager' AND e_sub_department = 'Office'");
        } else if ($depart == 'CAD1') {
            $stmt = $conn->prepare("SELECT e_user_id, e_username FROM employees WHERE e_level = 'assisManager' AND e_sub_department = 'CAD1'");
        } else if ($depart == 'CAD2') {
            $stmt = $conn->prepare("SELECT e_user_id, e_username FROM employees WHERE e_level = 'assisManager' AND e_sub_department2 = 'CAD2'");
        } else if ($depart == 'CAM') {
            $stmt = $conn->prepare("SELECT e_user_id, e_username FROM employees WHERE e_level = 'assisManager' AND e_sub_department3 = 'CAM'");
        } else if ($depart == 'RD') {
            $stmt = $conn->prepare("SELECT e_user_id, e_username FROM employees WHERE e_level = 'manager' AND e_sub_department = 'RD'");
        }
    } else if ($level == 'chief' && $depart == 'Management') {
        $stmt = $conn->prepare("SELECT e_user_id, e_username FROM employees WHERE e_level = 'manager' AND e_sub_department = 'Office'");
    } else {
        echo "ไม่พบเงื่อนไข";
        exit; // เพิ่ม exit เพื่อหยุดการทำงานหากไม่พบเงื่อนไข
    }

    $stmt->execute();
    $managers       = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $managerMessage = "มีใบลาของ $empName\n$proveName อนุมัติใบลาเรียบร้อย \nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nวันเวลาที่ลา : $leaveStartDate ถึง $leaveEndDate\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $sURL";

    if ($leaveStatus == 'ยกเลิกใบลา') {
        $managerMessage = "$empName ยกเลิกใบลา\n$proveName อนุมัติยกเลิกใบลา\nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nวันเวลาที่ลา : $leaveStartDate ถึง $leaveEndDate\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $sURL";
    }

    if ($managers) {
        foreach ($managers as $manager) {
            $userId = $manager['e_user_id'];

            if (! $userId) {
                echo "ไม่พบ User ID สำหรับผู้จัดการ: " . $manager['e_username'] . "<br>";
                continue;
            }

            $messageData = [
                'to'       => $userId,
                'messages' => [
                    [
                        'type' => 'text',
                        'text' => $managerMessage,
                    ],
                ],
            ];

            // แปลงข้อมูลเป็น JSON
            $jsonData = json_encode($messageData);

            // ส่งข้อความด้วย cURL
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.line.me/v2/bot/message/push');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $access_token,
            ]);

            $result = curl_exec($ch);

            if (curl_error($ch)) {
                echo 'Error: ' . curl_error($ch) . "<br>";
            } else {
                $responseData = json_decode($result, true);
                if (isset($responseData['message'])) {
                    echo "ผู้จัดการ: " . $manager['e_username'] . " - Error: " . $responseData['message'] . "<br>";
                } else {
                    echo "ผู้จัดการ: " . $manager['e_username'] . " - ส่งข้อความสำเร็จ<br>";
                }
            }
            curl_close($ch);
        }
    } else {
        echo "ไม่พบข้อมูลผู้จัดการ";
    }

}
// ไม่อนุมัติ ------------------------------------------------------------------------------------------------------
else if ($status == '3') {
    // อัปเดตสถานะการลาในฐานข้อมูล
    $sql = "UPDATE leave_list SET l_approve_status = :status, l_approve_datetime = :appDate, l_approve_name = :userName, l_reason = :reasonNoProve
            WHERE l_usercode = :userCode AND l_create_datetime = :createDate";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':status'        => $status,
        ':appDate'       => $appDate,
        ':userName'      => $userName,
        ':userCode'      => $userCode,
        ':createDate'    => $createDate,
        ':reasonNoProve' => $reasonNoProve,
    ]);

    // แจ้งเตือน พนง
    $stmt = $conn->prepare("SELECT e_user_id FROM employees WHERE e_usercode = :userCode");
    $stmt->execute([':userCode' => $userCode]);
    $userId = $stmt->fetchColumn(); // This should be the LINE user ID, not a token
    $sURL   = 'https://lms.system-samt.com/';

// ข้อความแจ้งเตือน
    $message = "$proveName ไม่อนุมัติใบลา \nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nวันเวลาที่ลา : $leaveStartDate ถึง $leaveEndDate\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $sURL";
    if ($leaveStatus == 'ยกเลิกใบลา') {
        $message = "$proveName ไม่อนุมัติยกเลิกใบลาของ $empName\nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nวันเวลาที่ลา : $leaveStartDate ถึง $leaveEndDate\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $sURL";
    }

// ส่งข้อความผ่าน LINE Messaging API ไปยังพนักงาน
    if ($userId) {

        // สร้าง message data
        $messageData = [
            'to'       => $userId,
            'messages' => [
                [
                    'type' => 'text',
                    'text' => $message,
                ],
            ],
        ];

        // แปลงข้อมูลเป็น JSON
        $jsonData = json_encode($messageData);

        // ส่งข้อความด้วย cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.line.me/v2/bot/message/push');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $access_token,
        ]);

        $result = curl_exec($ch);
        if (curl_error($ch)) {
            echo 'Error: ' . curl_error($ch);
        } else {
            $responseData = json_decode($result, true);
            if (isset($responseData['message'])) {
                echo "Error message: " . $responseData['message'] . "\n";
            } else {
                echo "Message sent successfully\n";
            }
        }
        curl_close($ch);
    }

    // แจ้งเตือนผู้จัดการตามแผนก
    if ($depart == 'RD') {
        // แจ้งไลน์โฮซัง
        $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'manager' AND e_sub_department =  :depart");
        $stmt->bindParam(':workplace', $workplace);
        $stmt->bindParam(':depart', $depart);

    } else if ($level == 'leader') {
        if ($depart == 'Office') {
            // แจ้งเตือนไปที่พี่ตุ๊ก
            $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'manager' AND e_sub_department = 'Office'");
            $stmt->bindParam(':workplace', $workplace);
        } else if ($depart == 'CAD1') {
            $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'assisManager' AND e_sub_department = 'CAD1'");
            $stmt->bindParam(':workplace', $workplace);
        } else if ($depart == 'CAD2') {
            $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'assisManager' AND e_sub_department2 = 'CAD2'");
            $stmt->bindParam(':workplace', $workplace);

        } else if ($depart == 'CAM') {
            $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'assisManager' AND e_sub_department3 = 'CAM'");
            $stmt->bindParam(':workplace', $workplace);
        }
    } else if ($level == 'chief') {
        if ($depart == 'Management') {
            // แจ้งเตือนไปที่พี่ตุ๊ก
            $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'manager' AND e_sub_department = 'Office'");
            $stmt->bindParam(':workplace', $workplace);
        }
    } else {
        echo "ไม่พบเงื่อนไข";
    }

    $stmt->execute();
    $managers       = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $managerMessage = "มีใบลาของ $empName\n$proveName อนุมัติใบลาเรียบร้อย \nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nวันเวลาที่ลา : $leaveStartDate ถึง $leaveEndDate\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $sURL";

    if ($leaveStatus == 'ยกเลิกใบลา') {
        $managerMessage = "$empName ยกเลิกใบลา\n$proveName อนุมัติยกเลิกใบลา\nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nวันเวลาที่ลา : $leaveStartDate ถึง $leaveEndDate\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $sURL";
    }

    if ($managers) {
        foreach ($managers as $manager) {
            $userId = $manager['e_user_id'];

            if (! $userId) {
                echo "ไม่พบ User ID สำหรับผู้จัดการ: " . $manager['e_username'] . "<br>";
                continue;
            }

            $messageData = [
                'to'       => $userId,
                'messages' => [
                    [
                        'type' => 'text',
                        'text' => $managerMessage,
                    ],
                ],
            ];

            // แปลงข้อมูลเป็น JSON
            $jsonData = json_encode($messageData);

            // ส่งข้อความด้วย cURL
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.line.me/v2/bot/message/push');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $access_token,
            ]);

            $result = curl_exec($ch);

            if (curl_error($ch)) {
                echo 'Error: ' . curl_error($ch) . "<br>";
            } else {
                $responseData = json_decode($result, true);
                if (isset($responseData['message'])) {
                    echo "ผู้จัดการ: " . $manager['e_username'] . " - Error: " . $responseData['message'] . "<br>";
                } else {
                    echo "ผู้จัดการ: " . $manager['e_username'] . " - ส่งข้อความสำเร็จ<br>";
                }
            }
            curl_close($ch);
        }
    } else {
        echo "ไม่พบข้อมูลผู้จัดการ";
    }

}
// ปิดการเชื่อมต่อกับฐานข้อมูล
$conn = null;