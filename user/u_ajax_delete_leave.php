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
// $leaveStatus    = $_POST['leaveStatus'];

$canDatetime = date('Y-m-d H:i:s');

$workplace  = $_POST['workplace'];
$subDepart  = $_POST['subDepart'];
$subDepart2 = $_POST['subDepart2'];
$subDepart3 = $_POST['subDepart3'];
$subDepart4 = $_POST['subDepart4'];
$subDepart5 = $_POST['subDepart5'];

$proveStatus  = 0;
$proveStatus2 = 1;
$proveStatus3 = 6;

$sqlReturn = "UPDATE leave_list SET
                l_leave_status = 1,
                l_cancel_datetime = :canDatetime,
                l_approve_status = :proveStatus,
                l_approve_name = '',
                l_approve_datetime = NULL,
                l_reason = '',
                l_approve_status2 = :proveStatus2,
                l_approve_name2 = '',
                l_approve_datetime2 = NULL,
                l_reason2 = '',
                l_approve_status3 = :proveStatus3,
                l_approve_name3 = '',
                l_approve_datetime3 = NULL,
                l_reason3 = '',
                l_hr_status = 0,
                l_hr_name = '',
                l_hr_datetime = NULL,
                l_hr_reason = ''
                WHERE l_leave_id = :leaveID AND l_create_datetime = :createDatetime";

$stmtReturn = $conn->prepare($sqlReturn);
$stmtReturn->bindParam(':leaveID', $leaveID);
$stmtReturn->bindParam(':createDatetime', $createDatetime);
$stmtReturn->bindParam(':canDatetime', $canDatetime);
$stmtReturn->bindParam(':proveStatus', $proveStatus);
$stmtReturn->bindParam(':proveStatus2', $proveStatus2);
$stmtReturn->bindParam(':proveStatus3', $proveStatus3);

if ($stmtReturn->execute()) {
    $sURL     = 'https://lms.system-samt.com/';
    $sMessage = "$name ยกเลิกใบลา\nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nวันเวลาที่ลา : $startDate ถึง $endDate\nสถานะใบลา : ยกเลิก\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $sURL";

    $sql = "SELECT e_user_id
            FROM employees
            WHERE e_workplace = :workplace
            AND e_level IN ('leader', 'cheif', 'subLeader')
            AND (
                e_sub_department = :subDepart
                OR e_sub_department2 = :subDepart2
                OR e_sub_department3 = :subDepart3
                OR e_sub_department4 = :subDepart4
                OR e_sub_department5 = :subDepart5
            )";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':workplace', $workplace);
    $stmt->bindParam(':subDepart', $subDepart);
    $stmt->bindParam(':subDepart2', $subDepart2);
    $stmt->bindParam(':subDepart3', $subDepart3);
    $stmt->bindParam(':subDepart4', $subDepart4);
    $stmt->bindParam(':subDepart5', $subDepart5);

    if ($stmt->execute()) {
        $userIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if ($userIds) {
            foreach ($userIds as $userId) {
                $data = [
                    'to'       => $userId,
                    'messages' => [
                        [
                            'type' => 'text',
                            'text' => $sMessage,
                        ],
                    ],
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
                    echo 'Error:' . curl_error($ch);
                } else {
                    echo 'Response: ' . $response;
                }

                curl_close($ch);
            }
        } else {
            echo "ไม่พบ userId ของหัวหน้าหรือผู้จัดการ";
        }
    } else {
        echo "ไม่สามารถดึงข้อมูล userId ได้";
    }

} else {
    echo "Error ในการอัพเดตข้อมูลการลา";
}