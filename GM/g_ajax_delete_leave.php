<?php

require '../connect.php';
date_default_timezone_set('Asia/Bangkok');

$usercode = $_POST['usercode'];
$userName = $_POST['userName'];
$name = $_POST['name'];
$depart = $_POST['depart'];
$leaveID = $_POST['leaveId'];
$leaveType = $_POST['leaveType'];
$leaveReason = $_POST['leaveReason'];
$startDate = $_POST['startDate'];
$endDate = $_POST['endDate'];
$leaveStatus = $_POST['leaveStatus'];
$createDatetime = $_POST['createDatetime'];
$canDatetime = date('Y-m-d H:i:s');
$proveDate = date('Y-m-d H:i:s');

$workplace = $_POST['workplace'];
$subDepart = $_POST['subDepart'];
$subDepart2 = $_POST['subDepart2'];
$subDepart3 = $_POST['subDepart3'];
$subDepart4 = $_POST['subDepart4'];
$subDepart5 = $_POST['subDepart5'];

$sqlReturn = "UPDATE leave_list SET
                l_leave_status = 1,
                l_cancel_datetime = :canDatetime,
                l_approve_status = 6,
                l_approve_name = '',
                l_approve_datetime = NULL,
                l_reason = '',
                l_approve_status2 = 4,
                l_approve_name2 = :userName,
                l_approve_datetime2 = :proveDate,
                l_reason2 = '',
                l_hr_status = 0,
                l_hr_name = '',
                l_hr_datetime = NULL
              WHERE l_leave_id = :leaveID AND l_create_datetime = :createDatetime";
$stmtReturn = $conn->prepare($sqlReturn);
$stmtReturn->bindParam(':leaveID', $leaveID);
$stmtReturn->bindParam(':userName', $userName);
$stmtReturn->bindParam(':createDatetime', $createDatetime);
$stmtReturn->bindParam(':canDatetime', $canDatetime);
$stmtReturn->bindParam(':proveDate', $proveDate);

if ($stmtReturn->execute()) {
    $sURL = 'https://lms.system-samt.com/';

    // แจ้งเตือนไลน์ HR
    $stmt = $conn->prepare("SELECT e_token FROM employees WHERE e_level = 'admin'");
    $stmt->execute();
    $admins = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $aMessage = "$name ยกเลิกใบลา\nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nวันเวลาที่ลา : $startDate ถึง $endDate\nสถานะใบลา : $leaveStatus\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $sURL";
    if ($admins) {
        foreach ($admins as $sToken) {
            $chOne = curl_init();
            curl_setopt($chOne, CURLOPT_URL, "https://notify-api.line.me/api/notify");
            curl_setopt($chOne, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($chOne, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($chOne, CURLOPT_POST, 1);
            curl_setopt($chOne, CURLOPT_POSTFIELDS, "message=" . $aMessage);
            $headers = [
                'Content-type: application/x-www-form-urlencoded',
                'Authorization: Bearer ' . $sToken,
            ];
            curl_setopt($chOne, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($chOne, CURLOPT_RETURNTRANSFER, 1);
            $result = curl_exec($chOne);

            if (curl_error($chOne)) {
                echo 'Error:' . curl_error($chOne);
            } else {
                $result_ = json_decode($result, true);
                echo "status : " . $result_['status'];
                echo "message : " . $result_['message'];
            }

            curl_close($chOne);
        }
        echo "ยกเลิกใบลาสำเร็จ";
    } else {
        echo "ไม่พบ Token ของ admin";
    }
} else {
    echo "Error";
}