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
$level          = $_POST['level'];
$workplace      = $_POST['workplace'];
$subDepart      = $_POST['subDepart'];
$subDepart2     = $_POST['subDepart2'];
$subDepart3     = $_POST['subDepart3'];
$subDepart4     = $_POST['subDepart4'];
$subDepart5     = $_POST['subDepart5'];
// $leaveStatus    = $_POST['leaveStatus'];

$canDatetime = date('Y-m-d H:i:s');

// Check leave approval statuses
$sqlCheck = "SELECT l_approve_status, l_approve_status2, l_approve_status3 FROM leave_list
WHERE l_leave_id = :leaveID AND l_create_datetime = :createDatetime AND l_usercode = :usercode";

$stmtCheck = $conn->prepare($sqlCheck);
$stmtCheck->bindParam(':leaveID', $leaveID);
$stmtCheck->bindParam(':createDatetime', $createDatetime);
$stmtCheck->bindParam(':usercode', $usercode);
$stmtCheck->execute();
$approveStatuses = $stmtCheck->fetch(PDO::FETCH_ASSOC);

if ($approveStatuses) {
    // Update leave status to canceled
    $updateQuery = "UPDATE leave_list SET l_leave_status = 1, l_cancel_datetime = :canDatetime WHERE l_leave_id = :leaveID AND l_create_datetime = :createDatetime";
    $stmtReturn  = $conn->prepare($updateQuery);
    $stmtReturn->bindParam(':leaveID', $leaveID);
    $stmtReturn->bindParam(':createDatetime', $createDatetime);
    $stmtReturn->bindParam(':canDatetime', $canDatetime);

    if ($stmtReturn->execute()) {
        $sURL = 'https://lms.system-samt.com/';

        // Check for specific approval status
        if ($approveStatuses['l_approve_status'] == 0 || $approveStatuses['l_approve_status2'] == 1 || $approveStatuses['l_approve_status3'] == 7) {
            // Fetch relevant managers
            $sql = "SELECT e_user_id, e_username FROM employees WHERE e_level IN ('leader', 'chief', 'assisManager', 'manager', 'manager2', 'GM', 'subLeader')
                AND e_level <> :level
                AND e_workplace = :workplace
                AND (
                   (e_sub_department IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND e_sub_department <> '')
                OR (e_sub_department2 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND e_sub_department2 <> '')
                OR (e_sub_department3 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND e_sub_department3 <> '')
                OR (e_sub_department4 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND e_sub_department4 <> '')
                OR (e_sub_department5 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND e_sub_department5 <> '')
                )";

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':depart', $depart);
            $stmt->bindParam(':subDepart', $subDepart);
            $stmt->bindParam(':subDepart2', $subDepart2);
            $stmt->bindParam(':subDepart3', $subDepart3);
            $stmt->bindParam(':subDepart4', $subDepart4);
            $stmt->bindParam(':subDepart5', $subDepart5);
            $stmt->bindParam(':workplace', $workplace);
            $stmt->bindParam(':level', $level);

            if ($stmt->execute()) {
                $managers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($managers as $manager) {
                    $proveNamee        = $manager['e_username'];
                    $sMessageToManager = "K." . $proveNamee . "\n\n$name ยกเลิกใบลา\nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nวันเวลาที่ลา : $startDate ถึง $endDate\nสถานะใบลา : ยกเลิก\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $sURL";

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
                        error_log('LINE API Response: ' . $response);
                    }
                    curl_close($ch);
                }
            } else {
                error_log("ไม่สามารถดึงข้อมูล userId ของหัวหน้าหรือผู้จัดการได้");
            }
        }
    } else {
        echo "Error ในการอัพเดตข้อมูลการลา";
    }
}