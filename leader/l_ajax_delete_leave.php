<?php
include '../connect.php';
include '../access_token_channel.php';

date_default_timezone_set('Asia/Bangkok');

$leaveID        = $_POST['leaveId'];
$createDatetime = $_POST['createDatetime'];
$usercode       = $_POST['usercode'];
$name           = $_POST['name'];
$leaveType      = $_POST['leaveType'];
$leaveReason    = $_POST['leaveReason'];
$startDate      = $_POST['startDate'];
$endDate        = $_POST['endDate'];
$depart         = $_POST['depart'];
$workplace      = $_POST['workplace'];
$subDepart      = $_POST['subDepart'];
$subDepart2     = $_POST['subDepart2'];
$subDepart3     = $_POST['subDepart3'];
$subDepart4     = $_POST['subDepart4'];
$subDepart5     = $_POST['subDepart5'];

$canDatetime = date('Y-m-d H:i:s');

$sqlCheck = "SELECT l_approve_status, l_approve_status2, l_approve_status3 FROM leave_list
WHERE l_leave_id = :leaveID AND l_create_datetime = :createDatetime AND l_usercode = :usercode";

$stmtCheck = $conn->prepare($sqlCheck);
$stmtCheck->bindParam(':leaveID', $leaveID);
$stmtCheck->bindParam(':createDatetime', $createDatetime);
$stmtCheck->bindParam(':usercode', $usercode);
$stmtCheck->execute();
$approveStatuses = $stmtCheck->fetch(PDO::FETCH_ASSOC);

$updates = [];
if ($approveStatuses) {
    if (! is_null($approveStatuses['l_approve_status'])) {
        if ($approveStatuses['l_approve_status'] == 0 || $approveStatuses['l_approve_status'] == 2) {
            $updates[] = "l_approve_status = 2, l_approve_name = '', l_approve_datetime = NULL, l_reason = ''";
        } else {
            $updates[] = "l_approve_status = 6, l_approve_name = '', l_approve_datetime = NULL, l_reason = ''";
        }
    }
    if (! is_null($approveStatuses['l_approve_status2'])) {
        if ($approveStatuses['l_approve_status2'] == 1 || $approveStatuses['l_approve_status2'] == 4) {
            $updates[] = "l_approve_status2 = 1, l_approve_name2 = '', l_approve_datetime2 = NULL, l_reason2 = ''";
        } else {
            $updates[] = "l_approve_status2 = 6, l_approve_name2 = '', l_approve_datetime2 = NULL, l_reason2 = ''";
        }
    }
    if (! is_null($approveStatuses['l_approve_status3'])) {
        if ($approveStatuses['l_approve_status3'] == 7 || $approveStatuses['l_approve_status3'] == 8) {
            $updates[] = "l_approve_status3 = 7, l_approve_name3 = '', l_approve_datetime3 = NULL, l_reason3 = ''";
        } else {
            $updates[] = "l_approve_status3 = 6, l_approve_name3 = '', l_approve_datetime3 = NULL, l_reason3 = ''";
        }
    }
}

$updateQuery = "UPDATE leave_list SET l_leave_status = 1, l_cancel_datetime = :canDatetime, l_hr_status = 0, l_hr_name = '', l_hr_datetime = NULL, l_hr_reason = ''";
if (! empty($updates)) {
    $updateQuery .= ", " . implode(", ", $updates);
}
$updateQuery .= " WHERE l_leave_id = :leaveID AND l_create_datetime = :createDatetime";

$stmtReturn = $conn->prepare($updateQuery);
$stmtReturn->bindParam(':leaveID', $leaveID);
$stmtReturn->bindParam(':createDatetime', $createDatetime);
$stmtReturn->bindParam(':canDatetime', $canDatetime);

if ($stmtReturn->execute()) {
    $sURL     = 'https://lms.system-samt.com/';
    $sMessage = "$name ยกเลิกใบลา\nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nวันเวลาที่ลา : $startDate ถึง $endDate\nสถานะใบลา : ยกเลิก\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $sURL";

    $sql  = "SELECT e_user_id FROM employees WHERE e_workplace = :workplace AND e_level IN ('chief','manager','manger2','assisManager') AND (e_sub_department = :subDepart OR e_sub_department2 = :subDepart2 OR e_sub_department3 = :subDepart3 OR e_sub_department4 = :subDepart4 OR e_sub_department5 = :subDepart5)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':workplace', $workplace);
    $stmt->bindParam(':subDepart', $subDepart);
    $stmt->bindParam(':subDepart2', $subDepart2);
    $stmt->bindParam(':subDepart3', $subDepart3);
    $stmt->bindParam(':subDepart4', $subDepart4);
    $stmt->bindParam(':subDepart5', $subDepart5);

    if ($stmt->execute()) {
        $userIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } else {
        echo "ไม่สามารถดึงข้อมูล userId ของหัวหน้าหรือผู้จัดการได้";
        $userIds = [];
    }

    if ($approveStatuses['l_approve_status3'] == 7 || $approveStatuses['l_approve_status3'] == 8) {
        // ดึง userId ของ GM
        $sqlGM  = "SELECT e_user_id FROM employees WHERE e_level = 'GM' AND e_workplace = :workplace";
        $stmtGM = $conn->prepare($sqlGM);
        $stmtGM->bindParam(':workplace', $workplace);

        if ($stmtGM->execute()) {
            $gmUserIds = $stmtGM->fetchAll(PDO::FETCH_COLUMN);
            if ($gmUserIds) {
                $userIds = array_merge($userIds, $gmUserIds);
            }
        } else {
            echo "ไม่สามารถดึงข้อมูล userId ของ GM ได้";
        }
    }

    foreach ($userIds as $userId) {
        $data = [
            'to'       => $userId,
            'messages' => [['type' => 'text', 'text' => $sMessage]],
        ];

        $ch = curl_init('https://api.line.me/v2/bot/message/push');
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Bearer ' . $access_token]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);
        if (curl_error($ch)) {
            echo 'Error:' . curl_error($ch);
        } else {
            echo 'Response: ' . $response;
        }
        curl_close($ch);
    }

} else {
    echo "Error ในการอัพเดตข้อมูลการลา";
}