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

try {
    $conn->beginTransaction();

    // อัปเดตสถานะการลาในฐานข้อมูล
    $sqlUpdateLeave = "UPDATE leave_list SET l_approve_status2 = :status, l_approve_datetime2 = :appDate, l_approve_name2 = :userName, l_reason2 = :reasonNoProve
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

    // ตรวจสอบว่าการอัปเดตสำเร็จหรือไม่
    if (! $result) {
        error_log("ไม่สามารถอัปเดตสถานะการลาได้: " . print_r($stmt->errorInfo(), true));
        throw new Exception("ไม่สามารถอัปเดตสถานะการลาได้");
    }

    $rowsAffected = $stmt->rowCount();
    error_log("จำนวนแถวที่ถูกอัปเดต: $rowsAffected");

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

    error_log("สถานะการอนุมัติ: " . print_r($approveStatus, true));

    // สร้างข้อความแจ้งเตือน
    $sURL = 'https://lms.system-samt.com/';
    if ($status == '4') {
        $message = "$proveName อนุมัติใบลาของ $empName\nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nวันเวลาที่ลา : $leaveStartDate ถึง $leaveEndDate\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $sURL";
        if ($leaveStatus == 'ยกเลิกใบลา') {
            $message = "$proveName อนุมัติยกเลิกใบลาของ $empName\nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nวันเวลาที่ลา : $leaveStartDate ถึง $leaveEndDate\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $sURL";
        }
    } elseif ($status == '5') {
        $message = "$proveName ไม่อนุมัติใบลาของ $empName\nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nวันเวลาที่ลา : $leaveStartDate ถึง $leaveEndDate\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $sURL";
        if ($leaveStatus == 'ยกเลิกใบลา') {
            $message = "$proveName ไม่อนุมัติยกเลิกใบลาของ $empName\nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nวันเวลาที่ลา : $leaveStartDate ถึง $leaveEndDate\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $sURL";
        }
    }

    // ตัวแปรเก็บผู้ที่ได้รับแจ้งเตือน
    $notifiedList = [];
    $managers     = [];

    // เงื่อนไขที่ 1: ถ้า l_approve_status2 = 1 ให้ส่งแจ้งเตือนหาระดับผู้จัดการ
    if (isset($approveStatus['l_approve_status2']) && $approveStatus['l_approve_status2'] == 1) {
        error_log("เงื่อนไข: l_approve_status2 = 1 - ส่งแจ้งเตือนหาระดับผู้จัดการ");
        echo "<div class='alert alert-info'>เงื่อนไข: ส่งแจ้งเตือนหาระดับผู้จัดการ เนื่องจาก l_approve_status2 = 1</div>";

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
        error_log("พบผู้จัดการ: " . count($managers) . " คน");
    }

    // เงื่อนไขที่ 2: ถ้า l_approve_status3 = 7 ให้ส่งแจ้งเตือนหาระดับ GM
    else if (isset($approveStatus['l_approve_status3']) && $approveStatus['l_approve_status3'] == 7) {
        error_log("เงื่อนไข: l_approve_status3 = 7 - ส่งแจ้งเตือนหาระดับ GM");
        echo "<div class='alert alert-info'>เงื่อนไข: ส่งแจ้งเตือนหาระดับ GM เนื่องจาก l_approve_status3 = 7</div>";

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

    // ถ้าไม่เข้าเงื่อนไขใดเลย ให้ค้นหาผู้รับในระดับถัดไป
    else if (empty($managers)) {
        error_log("ไม่เข้าเงื่อนไขพิเศษ: ค้นหาผู้รับในระดับถัดไป");
        echo "<div class='alert alert-info'>เงื่อนไข: ค้นหาผู้รับในระดับถัดไป</div>";

        $sqlFindManagers = "SELECT e_user_id, e_username, e_name, e_level
                          FROM employees
                          WHERE e_level IN ('leader', 'chief', 'assisManager', 'manager', 'manager2', 'GM', 'subLeader')
                          AND e_level <> :level
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
            ':level'      => $level,
            ':workplace'  => $workplace,
            ':depart'     => $depart,
            ':subDepart'  => $subDepart,
            ':subDepart2' => $subDepart2,
            ':subDepart3' => $subDepart3,
            ':subDepart4' => $subDepart4,
            ':subDepart5' => $subDepart5,
        ]);
        $managers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("พบผู้รับในระดับถัดไป: " . count($managers) . " คน");
    }

    // ตรวจสอบว่าพบผู้รับแจ้งเตือนหรือไม่
    if (empty($managers)) {
        error_log("ไม่พบผู้รับแจ้งเตือน");
        echo "<div class='alert alert-warning'>ไม่พบผู้รับแจ้งเตือน</div>";
        $conn->commit();
        echo "<div class='alert alert-success'>อัปเดตสถานะการลาเรียบร้อยแล้ว</div>";
        exit;
    }

    // แสดงรายชื่อผู้รับแจ้งเตือน
    echo "<div class='alert alert-info'>";
    echo "<h4>ผู้รับแจ้งเตือน:</h4>";
    echo "<ul class='list-group'>";
    foreach ($managers as $manager) {
        echo "<li class='list-group-item'>{$manager['e_name']} ({$manager['e_username']}) - ตำแหน่ง: {$manager['e_level']}</li>";
    }
    echo "</ul>";
    echo "</div>";

    // ส่งข้อความแจ้งเตือน
    foreach ($managers as $manager) {
        if (! empty($manager['e_user_id'])) {
            $personalMessage = "$proveName " . ($status == '4' ? 'อนุมัติ' : 'ไม่อนุมัติ') . "ใบลาของ $empName\nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nวันเวลาที่ลา : $leaveStartDate ถึง $leaveEndDate\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $sURL\n\nข้อความนี้ส่งถึง: " . $manager['e_username'];
            // $personalMessage = "$proveName " . ($status == '2' ? 'อนุมัติ' : 'ไม่อนุมัติ') . "ใบลาของ $empName\nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nวันเวลาที่ลา : $leaveStartDate ถึง $leaveEndDate\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $sURL";

            $response = sendLineMessage($manager['e_user_id'], $personalMessage, $access_token);
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

    $conn->commit();

    // แสดงผลการส่งแจ้งเตือน
    echo "<div class='alert alert-success mt-3'>";
    echo "<h4>ผลการส่งแจ้งเตือน:</h4>";
    if (! empty($notifiedList)) {
        echo "<ul class='list-group'>";
        foreach ($notifiedList as $notified) {
            echo "<li class='list-group-item'><i class='fas fa-check-circle text-success'></i> {$notified['name']} ({$notified['username']}) - ตำแหน่ง: {$notified['level']}</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>ไม่มีผู้ได้รับแจ้งเตือนสำเร็จ</p>";
    }
    echo "</div>";

    echo "<div class='alert alert-success'>อัปเดตสถานะการลาเรียบร้อยแล้ว</div>";

} catch (Exception $e) {
    error_log("เกิดข้อผิดพลาด: " . $e->getMessage());
    $conn->rollBack();
    echo "<div class='alert alert-danger'>เกิดข้อผิดพลาด: " . $e->getMessage() . "</div>";
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