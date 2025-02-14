<?php
include '../connect.php';
include '../access_token_channel.php';

date_default_timezone_set('Asia/Bangkok');

$leaveID        = $_POST['leaveId'];
$createDatetime = $_POST['createDatetime'];
$usercode       = $_POST['usercode'];
$userName       = $_POST['userName'];
$name           = $_POST['name'];
$leaveType      = $_POST['leaveType'];
$leaveReason    = $_POST['leaveReason'];
$startDate      = $_POST['startDate'];
$endDate        = $_POST['endDate'];
$depart         = $_POST['depart'];
$level          = $_POST['level'];
$workplace      = $_POST['workplace'];
$subDepart      = $_POST['subDepart'];
$subDepart2     = $_POST['subDepart2'];
$subDepart3     = $_POST['subDepart3'];
$subDepart4     = $_POST['subDepart4'];
$subDepart5     = $_POST['subDepart5'];

$canDatetime = date('Y-m-d H:i:s');
$proveDate3  = date('Y-m-d H:i:s');
$proveName3  = $userName;

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
            $updates[] = "l_approve_status = 6, l_approve_name = '', l_approve_datetime = NULL, l_reason = ''";
        } else {
            $updates[] = "l_approve_status = 6, l_approve_name = '', l_approve_datetime = NULL, l_reason = ''";
        }
    }
    if (! is_null($approveStatuses['l_approve_status2'])) {
        if ($approveStatuses['l_approve_status2'] == 1 || $approveStatuses['l_approve_status2'] == 4) {
            $updates[] = "l_approve_status2 = 6, l_approve_name2 = '', l_approve_datetime2 = NULL, l_reason2 = ''";
        } else {
            $updates[] = "l_approve_status2 = 6, l_approve_name2 = '', l_approve_datetime2 = NULL, l_reason2 = ''";
        }
    }
    if (! is_null($approveStatuses['l_approve_status3'])) {
        if ($approveStatuses['l_approve_status3'] == 7 || $approveStatuses['l_approve_status3'] == 8) {
            $updates[] = "l_approve_status3 = 8, l_approve_name3 = :proveName3, l_approve_datetime3 = :proveDate3, l_reason3 = ''";
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
$stmtReturn->bindParam(':proveName3', $proveName3);
$stmtReturn->bindParam(':proveDate3', $proveDate3);

if ($stmtReturn->execute()) {
    $sURL = 'https://lms.system-samt.com/';
    // $sMessage = "$name ยกเลิกใบลา\nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nวันเวลาที่ลา : $startDate ถึง $endDate\nสถานะใบลา : ยกเลิก\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $sURL";

    $sql =
        "SELECT e_user_id, e_username
            FROM employees
            WHERE e_level = 'admin'
            AND e_level <> :level
            AND e_workplace = :workplace";

    $stmt = $conn->prepare($sql);

    $stmt->bindParam(':workplace', $workplace);
    $stmt->bindParam(':level', $level);

    if ($stmt->execute()) {
        $managers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        error_log("ไม่สามารถดึงข้อมูล userId ของหัวหน้าหรือผู้จัดการได้");
        $managers = [];
    }

    if (! empty($managers)) {
        foreach ($managers as $manager) {
            $sMessageToManager = "$name ยกเลิกใบลา\nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nวันเวลาที่ลา : $startDate ถึง $endDate\nสถานะใบลา : ยกเลิก\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $sURL";

            $data = [
                'to'       => $manager['e_user_id'],
                'messages' => [['type' => 'text', 'text' => $sMessageToManager]],
            ];

            $ch = curl_init('https://api.line.me/v2/bot/message/push');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $access_token,
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

            $response = curl_exec($ch);
            if (curl_error($ch)) {
                error_log('LINE API Error: ' . curl_error($ch));
            } else {
                // Check if the response contains an error
                $responseData = json_decode($response, true);
                if (isset($responseData['error'])) {
                    error_log('LINE API Error: ' . $responseData['error']['message']);
                } else {
                    error_log('LINE API Response: ' . $response);
                }
            }
            curl_close($ch);
        }
    } else {
        error_log("ไม่พบหัวหน้าที่ตรงกับเงื่อนไข");
    }
} else {
    error_log("Error ในการอัพเดตข้อมูลการลา");
    echo "เกิดข้อผิดพลาดในการอัพเดตข้อมูลการลา";
}