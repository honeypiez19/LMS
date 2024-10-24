<?php

require '../connect.php';
date_default_timezone_set('Asia/Bangkok');

$usercode = $_POST['usercode'];
$userName = $_POST['userName'];
$name = $_POST['name'];
$level = $_POST['level'];
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
                l_approve_status = 2,
                l_approve_name = :userName,
                l_approve_datetime = :proveDate,
                l_reason = '',
                l_approve_status2 = 1,
                l_approve_name2 = '',
                l_approve_datetime2 = NULL,
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
    // $sMessage = "$name ยกเลิกใบลา\nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nวันเวลาที่ลา : $startDate ถึง $endDate\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $sURL";
    $sMessage = "$name ยกเลิกใบลา\nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nวันเวลาที่ลา : $startDate ถึง $endDate\nสถานะใบลา : $leaveStatus\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $sURL";

    if ($depart == 'RD') {
        // แจ้งไลน์โฮซัง
        $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'manager' AND e_sub_department =  :depart");
        // $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE e_department = 'Management' AND e_workplace = :workplace AND e_level = 'manager' AND e_sub_department = :depart");
        // $stmt = $conn->prepare("SELECT e_username, e_token FROM employees WHERE e_level = 'manager' AND e_workplace = 'Bang Phli' AND e_sub_department = 'RD'");
        $stmt->bindParam(':workplace', $workplace);
        $stmt->bindParam(':depart', $depart);

    } else if ($level == 'leader') {
        if ($depart == 'Office') {
            // แจ้งเตือนไปที่พี่ตุ๊ก
            $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'manager' AND e_sub_department = 'Office'");
            $stmt->bindParam(':workplace', $workplace);
        }
        else if ($depart == 'CAD1') {
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
        // $stmt = $conn->prepare("SELECT e_token FROM employees WHERE e_department = :depart AND e_workplace = :workplace AND e_level IN ('chief', 'manager')");
        // $stmt->bindParam(':depart', $depart);
        // $stmt->bindParam(':workplace', $workplace);
    }

    $stmt->execute();
    $tokens = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if ($tokens) {
        foreach ($tokens as $sToken) {
            $chOne = curl_init();
            curl_setopt($chOne, CURLOPT_URL, "https://notify-api.line.me/api/notify");
            curl_setopt($chOne, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($chOne, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($chOne, CURLOPT_POST, 1);
            curl_setopt($chOne, CURLOPT_POSTFIELDS, "message=" . $sMessage);
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
        echo "ไม่พบ Token ของหัวหน้าหรือผู้จัดการ";
    }
    // แจ้งเตือนไลน์ HR
    // $stmt = $conn->prepare("SELECT e_token FROM employees WHERE e_level = 'admin'");
    // $stmt->execute();
    // $admins = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // $aMessage = "$name ยกเลิกใบลา\nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nวันเวลาที่ลา : $startDate ถึง $endDate\nสถานะใบลา : $leaveStatus\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $sURL";
    // if ($admins) {
    //     foreach ($admins as $sToken) {
    //         $chOne = curl_init();
    //         curl_setopt($chOne, CURLOPT_URL, "https://notify-api.line.me/api/notify");
    //         curl_setopt($chOne, CURLOPT_SSL_VERIFYHOST, 0);
    //         curl_setopt($chOne, CURLOPT_SSL_VERIFYPEER, 0);
    //         curl_setopt($chOne, CURLOPT_POST, 1);
    //         curl_setopt($chOne, CURLOPT_POSTFIELDS, "message=" . $aMessage);
    //         $headers = [
    //             'Content-type: application/x-www-form-urlencoded',
    //             'Authorization: Bearer ' . $sToken,
    //         ];
    //         curl_setopt($chOne, CURLOPT_HTTPHEADER, $headers);
    //         curl_setopt($chOne, CURLOPT_RETURNTRANSFER, 1);
    //         $result = curl_exec($chOne);

    //         if (curl_error($chOne)) {
    //             echo 'Error:' . curl_error($chOne);
    //         } else {
    //             $result_ = json_decode($result, true);
    //             echo "status : " . $result_['status'];
    //             echo "message : " . $result_['message'];
    //         }

    //         curl_close($chOne);
    //     }
    //     echo "ยกเลิกใบลาสำเร็จ";
    // } else {
    //     echo "ไม่พบ Token ของ admin";
    // }
} else {
    echo "Error";
}