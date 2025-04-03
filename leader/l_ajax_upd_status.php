<?php
// Start session
session_start();
date_default_timezone_set('Asia/Bangkok'); // Set timezone to Thailand

include '../connect.php';
include '../access_token_channel.php';

$appDate = date("Y-m-d H:i:s");

// รับค่าที่ส่งมาจาก AJAX
$userCode       = isset($_POST['userCode']) ? $_POST['userCode'] : '';
$createDate     = isset($_POST['createDate']) ? $_POST['createDate'] : '';
$status         = isset($_POST['status']) ? $_POST['status'] : '';
$empName        = isset($_POST['empName']) ? $_POST['empName'] : '';
$userName       = isset($_POST['userName']) ? $_POST['userName'] : '';
$proveName      = isset($_POST['proveName']) ? $_POST['proveName'] : '';
$leaveType      = isset($_POST['leaveType']) ? $_POST['leaveType'] : '';
$leaveReason    = isset($_POST['leaveReason']) ? $_POST['leaveReason'] : '';
$leaveStartDate = isset($_POST['leaveStartDate']) ? $_POST['leaveStartDate'] : '';
$leaveEndDate   = isset($_POST['leaveEndDate']) ? $_POST['leaveEndDate'] : '';
$depart         = isset($_POST['depart']) ? $_POST['depart'] : '';
$leaveStatus    = isset($_POST['leaveStatus']) ? $_POST['leaveStatus'] : '';
$reasonNoProve  = isset($_POST['reasonNoProve']) ? $_POST['reasonNoProve'] : '';
$workplace      = isset($_POST['workplace']) ? $_POST['workplace'] : '';
$level          = isset($_POST['level']) ? $_POST['level'] : '';
$subDepart      = isset($_POST['subDepart']) ? $_POST['subDepart'] : '';
$subDepart2     = isset($_POST['subDepart2']) ? $_POST['subDepart2'] : '';
$subDepart3     = isset($_POST['subDepart3']) ? $_POST['subDepart3'] : '';
$subDepart4     = isset($_POST['subDepart4']) ? $_POST['subDepart4'] : '';
$subDepart5     = isset($_POST['subDepart5']) ? $_POST['subDepart5'] : '';

// URL ของระบบ
$sURL = 'https://lms.system-samt.com/';

try {
    // เริ่ม transaction
    $conn->beginTransaction();

    // อัปเดตสถานะการลาในฐานข้อมูล
    $sqlUpdateLeave = "UPDATE leave_list SET l_approve_status = :status, l_approve_datetime = :appDate, l_approve_name = :userName, l_reason = :reasonNoProve
                      WHERE l_usercode = :userCode AND l_create_datetime = :createDate";
    $stmt   = $conn->prepare($sqlUpdateLeave);
    $result = $stmt->execute([
        ':status'        => $status,
        ':appDate'       => $appDate,
        ':userName'      => $userName,
        ':userCode'      => $userCode,
        ':createDate'    => $createDate,
        ':reasonNoProve' => $reasonNoProve,
    ]);

    $notifiedList = [];

    // กรณีไม่อนุมัติ (status = 3) ให้แจ้งเตือนกลับไปยังพนักงานเจ้าของใบลา
    if ($status == '3') {
        $sqlUpdateLeaveStatus = "UPDATE leave_list
                          SET l_approve_status2 = 6,
                              l_approve_status3 = 6,
                              l_hr_status = 3
                          WHERE l_usercode = :userCode
                          AND l_create_datetime = :createDate";

        $stmt = $conn->prepare($sqlUpdateLeaveStatus);
        $stmt->execute([
            ':userCode'   => $userCode,
            ':createDate' => $createDate,
        ]);

        // ค้นหา user_id ของพนักงานเจ้าของใบลา
        $sqlFindEmployee = "SELECT e_user_id, e_username, e_name
                            FROM employees
                            WHERE e_usercode = :userCode";

        $stmt = $conn->prepare($sqlFindEmployee);
        $stmt->execute([
            ':userCode' => $userCode,
        ]);
        $employee = $stmt->fetch(PDO::FETCH_ASSOC);

        if (! empty($employee) && ! empty($employee['e_user_id'])) {
            // สร้างข้อความแจ้งเตือน
            $message = "K." . $employee['e_username'] . "\n\n$proveName ไม่อนุมัติใบลาของคุณ\nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nสถานะใบลา : $leaveStatus\nวันเวลาที่ลา: $leaveStartDate ถึง $leaveEndDate";

            if (! empty($reasonNoProve)) {
                $message .= "\nเหตุผลที่ไม่อนุมัติ : $reasonNoProve";
            }

            $message .= "\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $sURL";

            if ($leaveStatus == 'ยกเลิก') {
                $message = "K." . $employee['e_username'] . "\n\n$proveName ไม่อนุมัติยกเลิกใบลาของคุณ\nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nสถานะใบลา : $leaveStatus\nวันเวลาที่ลา: $leaveStartDate ถึง $leaveEndDate";

                if (! empty($reasonNoProve)) {
                    $message .= "\nเหตุผลที่ไม่อนุมัติ : $reasonNoProve";
                }

                $message .= "\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $sURL";
            }

            // ส่งข้อความแจ้งเตือน
            $response = sendLineMessage($employee['e_user_id'], $message, $access_token);
            error_log("ผลการส่งแจ้งเตือนไปยังพนักงาน " . $employee['e_username'] . ": " . print_r($response, true));

            if (isset($response['message'])) {
                error_log("แจ้งเตือนล้มเหลวสำหรับ " . $employee['e_username'] . " - " . $response['message']);
            } else {
                error_log("ส่งข้อความแจ้งเตือนสำเร็จถึง " . $employee['e_username']);
                $notifiedList[] = [
                    'name'     => $employee['e_name'],
                    'username' => $employee['e_username'],
                    'level'    => 'employee',
                ];
            }
        } else {
            error_log("ไม่พบข้อมูลพนักงานหรือ user_id สำหรับ userCode: " . $userCode);
        }
    }
    // กรณีอนุมัติ (status = 2) ให้ดำเนินการตามเงื่อนไขเดิม
    else if ($status == '2') {
        // เพิ่มเงื่อนไขใหม่: ถ้าเป็นการอนุมัติใบลาที่ถูกยกเลิกแล้ว ให้อัปเดตสถานะแล้วจบ
        if ($leaveStatus == 'ยกเลิก') {
            // อัปเดตสถานะตามที่ต้องการโดยไม่ต้องแจ้งเตือน GM หรือ admin
            $sqlUpdateCancelStatus = "UPDATE leave_list
                                     SET l_approve_status2 = 6,
                                         l_approve_status3 = 6,
                                         l_hr_status = 3
                                     WHERE l_usercode = :userCode
                                     AND l_create_datetime = :createDate";

            $stmt = $conn->prepare($sqlUpdateCancelStatus);
            $stmt->execute([
                ':userCode'   => $userCode,
                ':createDate' => $createDate,
            ]);

            error_log("อัปเดตสถานะสำหรับใบลาที่ถูกยกเลิก: userCode=" . $userCode . ", createDate=" . $createDate);
        } else {
            // ดึงข้อมูลสถานะการอนุมัติปัจจุบัน
            $sqlGetApproveStatus = "SELECT l_approve_status, l_approve_status2, l_approve_status3
                                   FROM leave_list
                                   WHERE l_usercode = :userCode AND l_create_datetime = :createDate";
            $stmt = $conn->prepare($sqlGetApproveStatus);
            $stmt->execute([
                ':userCode'   => $userCode,
                ':createDate' => $createDate,
            ]);
            $approveStatus = $stmt->fetch(PDO::FETCH_ASSOC);

            $managers = [];

            // เงื่อนไขที่ 1: ถ้า l_approve_status2 = 1 และไม่ใช่ใบลายกเลิก (l_leave_status != 1) ให้ส่งแจ้งเตือนหาระดับผู้จัดการ
            if (isset($approveStatus['l_approve_status2']) && $approveStatus['l_approve_status2'] == 1) {
                // ค้นหาผู้จัดการที่ต้องแจ้งเตือน
                $sqlFindManagers = "SELECT e_user_id, e_username, e_name, e_level
                            FROM employees
                            WHERE e_level IN ('manager', 'manager2', 'assisManager')
                            AND (
                                (e_sub_department IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND e_sub_department <> '')
                                OR (e_sub_department2 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND e_sub_department2 <> '')
                                OR (e_sub_department3 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND e_sub_department3 <> '')
                                OR (e_sub_department4 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND e_sub_department4 <> '')
                                OR (e_sub_department5 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND e_sub_department5 <> '')
                            )
                            AND e_workplace = :workplace";

                $stmt = $conn->prepare($sqlFindManagers);
                $stmt->execute([
                    ':workplace'  => $workplace,
                    ':depart'     => $depart,
                    ':subDepart'  => $subDepart,
                    ':subDepart2' => $subDepart2,
                    ':subDepart3' => $subDepart3,
                    ':subDepart4' => $subDepart4,
                    ':subDepart5' => $subDepart5,
                ]);
                $managers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            // เงื่อนไขที่ 2: ถ้า l_approve_status3 = 7 ให้ส่งแจ้งเตือนหาระดับ GM
            else if (isset($approveStatus['l_approve_status3']) && $approveStatus['l_approve_status3'] == 7) {
                // ค้นหา GM ที่ต้องแจ้งเตือน
                $sqlFindGM = "SELECT e_user_id, e_username, e_name, e_level
                              FROM employees
                              WHERE e_level = 'GM'
                              AND e_workplace = :workplace";

                $stmt = $conn->prepare($sqlFindGM);
                $stmt->execute([
                    ':workplace' => $workplace,
                ]);
                $gms = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // รวม GM เข้ากับรายชื่อผู้จัดการ (ถ้ามี)
                foreach ($gms as $gm) {
                    $managers[] = $gm;
                }
                error_log("พบ GM: " . count($gms) . " คน");
            }
            // ถ้าไม่เข้าเงื่อนไขใดเลย ให้แจ้งเตือนไป admin
            else if (empty($managers)) {
                $sqlFindAdmins = "SELECT e_user_id, e_username, e_name, e_level
                             FROM employees
                             WHERE e_level = 'admin'
                             AND e_workplace = :workplace";

                $stmt = $conn->prepare($sqlFindAdmins);
                $stmt->execute([
                    ':workplace' => $workplace,
                ]);
                $managers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                error_log("พบ admin: " . count($managers) . " คน");
            }

            // ส่งข้อความแจ้งเตือนไปยังผู้จัดการ/GM/admin
            foreach ($managers as $manager) {
                if (! empty($manager['e_user_id'])) {
                    // สร้างข้อความแจ้งเตือน
                    $message = "K." . $manager['e_username'] . "\n\n$proveName อนุมัติใบลาของ $empName\nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nสถานะใบลา : $leaveStatus\nวันเวลาที่ลา: $leaveStartDate ถึง $leaveEndDate\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $sURL";

                    $response = sendLineMessage($manager['e_user_id'], $message, $access_token);
                    error_log("ผลการส่งแจ้งเตือนไปยัง " . $manager['e_username'] . ": " . print_r($response, true));

                    if (isset($response['message'])) {
                        error_log("แจ้งเตือนล้มเหลวสำหรับ " . $manager['e_username'] . " - " . $response['message']);
                    } else {
                        error_log("ส่งข้อความแจ้งเตือนสำเร็จถึง " . $manager['e_username']);
                        // เพิ่มรายชื่อผู้ที่ได้รับแจ้งเตือนสำเร็จ
                        $notifiedList[] = [
                            'name'     => $manager['e_name'],
                            'username' => $manager['e_username'],
                            'level'    => $manager['e_level'],
                        ];
                    }
                } else {
                    error_log("ไม่พบ user_id สำหรับ " . $manager['e_username']);
                }
            }
        }
    }

    $conn->commit();
    echo json_encode(['status' => 'success', 'notified' => $notifiedList]);

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log("เกิดข้อผิดพลาด: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

function sendLineMessage($userId, $message, $access_token)
{
    if (empty($userId) || empty($message) || empty($access_token)) {
        error_log("ข้อมูลสำหรับส่งแจ้งเตือนไม่ครบถ้วน: userId=" . (empty($userId) ? "ไม่มี" : "มี") .
            ", message=" . (empty($message) ? "ไม่มี" : "มี") .
            ", access_token=" . (empty($access_token) ? "ไม่มี" : "มี"));
        return ['status' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วน'];
    }

    $messageData = [
        'to'       => $userId,
        'messages' => [['type' => 'text', 'text' => $message]],
    ];

    $jsonData = json_encode($messageData);
    $ch       = curl_init('https://api.line.me/v2/bot/message/push');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $access_token,
    ]);

    $result    = curl_exec($ch);
    $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);

    if ($curlError) {
        error_log("cURL Error: " . $curlError);
    }

    error_log("LINE API Response Code: " . $httpCode);
    error_log("LINE API Response: " . $result);

    curl_close($ch);

    return json_decode($result, true);
}

// ปิดการเชื่อมต่อกับฐานข้อมูล
$conn = null;
