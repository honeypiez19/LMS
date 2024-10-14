<?php
// Start session
session_start();

require '../connect.php'; // Include database connection file
date_default_timezone_set('Asia/Bangkok'); // เวลาไทย

$appDate = date("Y-m-d H:i:s");

// รับค่าที่ส่งมาจาก AJAX
$userCode = $_POST['userCode'];
$createDate = $_POST['createDate'];
$status = $_POST['status'];
$userName = $_POST['userName'];
$proveName = $_POST['proveName'];
$leaveType = $_POST['leaveType'];
$leaveReason = $_POST['leaveReason'];
$leaveStartDate = $_POST['leaveStartDate'];
$leaveEndDate = $_POST['leaveEndDate'];
$depart = $_POST['depart'];
$leaveStatus = $_POST['leaveStatus'];
$empName = $_POST['empName'];
$reasonNoProve = $_POST['reasonNoProve'];

// อนุมัติ
if ($status == '2') {
    $sql = "UPDATE leave_list SET l_approve_status = :status, l_approve_datetime = :appDate, l_approve_name = :userName
            WHERE l_usercode = :userCode AND l_create_datetime = :createDate";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':appDate', $appDate);
    $stmt->bindParam(':userName', $userName);
    $stmt->bindParam(':userCode', $userCode);
    $stmt->bindParam(':createDate', $createDate);

    if ($stmt->execute()) {
        // แจ้งเตือนพนักงาน
        $stmt = $conn->prepare("SELECT e_token FROM employees WHERE e_usercode = :userCode");
        $stmt->bindParam(':userCode', $userCode);
        $stmt->execute();
        $sToken = $stmt->fetchColumn();
        $sURL = 'https://lms.system-samt.com/';
        $sMessage = "$proveName อนุมัติใบลา \nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nวันเวลาที่ลา : $leaveStartDate ถึง $leaveEndDate\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $sURL";

        if ($leaveStatus == 'ยกเลิกใบลา') {
            $message = " $proveName อนุมัติยกเลิกใบลาของ $empName\nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nวันเวลาที่ลา : $leaveStartDate ถึง $leaveEndDate\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $sURL";
        }

        if ($sToken) {
            $chOne = curl_init();
            curl_setopt($chOne, CURLOPT_URL, "https://notify-api.line.me/api/notify");
            curl_setopt($chOne, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($chOne, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($chOne, CURLOPT_POST, 1);
            curl_setopt($chOne, CURLOPT_POSTFIELDS, "message=" . urlencode($sMessage));
            $headers = array(
                'Content-type: application/x-www-form-urlencoded',
                'Authorization: Bearer ' . $sToken,
            );
            curl_setopt($chOne, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($chOne, CURLOPT_RETURNTRANSFER, 1);
            $result = curl_exec($chOne);

            if (curl_error($chOne)) {
                echo 'Error:' . curl_error($chOne);
            } else {
                $result_ = json_decode($result, true);
                echo "status : " . $result_['status'] . "\n";
                echo "message : " . $result_['message'] . "\n";
            }
            curl_close($chOne);
        }

        // แจ้งเตือนพี่ตุ๊ก
        $stmt = $conn->prepare("SELECT e_token FROM employees WHERE e_usercode = '3505004' AND e_level = 'manager'");
        // $stmt->bindParam(':depart', $depart);
        $stmt->execute();
        $managerTokens = $stmt->fetchAll(PDO::FETCH_COLUMN);
        // $mMessage = "ผจก";
        $managerMessage = "มีใบลาของ $empName\n$proveName อนุมัติใบลาเรียบร้อย \nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nวันเวลาที่ลา : $leaveStartDate ถึง $leaveEndDate\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $sURL";

        if ($leaveStatus == 'ยกเลิกใบลา') {
            $managerMessage = "$empName ยกเลิกใบลา\n$proveName อนุมัติยกเลิกใบลา\nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nวันเวลาที่ลา : $leaveStartDate ถึง $leaveEndDate\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $sURL";
        }
        foreach ($managerTokens as $sToken) {
            $chOne = curl_init();
            curl_setopt($chOne, CURLOPT_URL, "https://notify-api.line.me/api/notify");
            curl_setopt($chOne, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($chOne, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($chOne, CURLOPT_POST, 1);
            curl_setopt($chOne, CURLOPT_POSTFIELDS, "message=" . urlencode($managerMessage));
            $headers = array(
                'Content-type: application/x-www-form-urlencoded',
                'Authorization: Bearer ' . $sToken,
            );
            curl_setopt($chOne, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($chOne, CURLOPT_RETURNTRANSFER, 1);
            $result = curl_exec($chOne);

            if (curl_error($chOne)) {
                echo 'Error:' . curl_error($chOne);
            } else {
                $result_ = json_decode($result, true);
                echo "status : " . $result_['status'] . "\n";
                echo "message : " . $result_['message'] . "\n";
            }
            curl_close($chOne);
        }

        // แจ้งเตือน HR
        // $stmt = $conn->prepare("SELECT e_token FROM employees WHERE e_level = 'admin'");
        // $stmt->execute();
        // $adminTokens = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // foreach ($adminTokens as $sToken) {
        //     $chOne = curl_init();
        //     curl_setopt($chOne, CURLOPT_URL, "https://notify-api.line.me/api/notify");
        //     curl_setopt($chOne, CURLOPT_SSL_VERIFYHOST, 0);
        //     curl_setopt($chOne, CURLOPT_SSL_VERIFYPEER, 0);
        //     curl_setopt($chOne, CURLOPT_POST, 1);
        //     curl_setopt($chOne, CURLOPT_POSTFIELDS, "message=" . urlencode($sMessage));
        //     $headers = array(
        //         'Content-type: application/x-www-form-urlencoded',
        //         'Authorization: Bearer ' . $sToken,
        //     );
        //     curl_setopt($chOne, CURLOPT_HTTPHEADER, $headers);
        //     curl_setopt($chOne, CURLOPT_RETURNTRANSFER, 1);
        //     $result = curl_exec($chOne);

        //     if (curl_error($chOne)) {
        //         echo 'Error:' . curl_error($chOne);
        //     } else {
        //         $result_ = json_decode($result, true);
        //         echo "status : " . $result_['status'] . "\n";
        //         echo "message : " . $result_['message'] . "\n";
        //     }
        //     curl_close($chOne);
        // }

        echo "อัปเดตสถานะผ่านสำเร็จ";
    } else {
        echo 'อัปเดตสถานะผ่านไม่สำเร็จ';
    }
}
// ไม่อนุมัติ
else if ($status == '3') {
        // SQL statement สำหรับอัปเดตข้อมูล
        $sql = "UPDATE leave_list 
        SET l_approve_status = :status, 
            l_approve_datetime = :appDate, 
            l_approve_name = :userName, 
            l_reason = :reasonNoProve  -- เพิ่ม l_reason
        WHERE l_usercode = :userCode 
        AND l_create_datetime = :createDate";

    $stmt = $conn->prepare($sql);

    // Binding parameters
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':appDate', $appDate);
    $stmt->bindParam(':userName', $userName);
    $stmt->bindParam(':userCode', $userCode);
    $stmt->bindParam(':createDate', $createDate);
    $stmt->bindParam(':reasonNoProve', $reasonNoProve); // Binding l_reason
    if ($stmt->execute()) {
        // แจ้งเตือนไลน์พนักงาน
        $stmt = $conn->prepare("SELECT e_token FROM employees WHERE e_usercode = :userCode");
        $stmt->bindParam(':userCode', $userCode);
        $stmt->execute();
        $sToken = $stmt->fetchColumn();
        $sURL = 'https://lms.system-samt.com/';

        // ข้อความแจ้งเตือน
        $message = "$proveName ไม่อนุมัติใบลา \nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nวันเวลาที่ลา : $leaveStartDate ถึง $leaveEndDate\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $sURL";

        if ($leaveStatus == 'ยกเลิกใบลา') {
            $message = "$proveName ไม่อนุมัติยกเลิกใบลา\nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nวันเวลาที่ลา : $leaveStartDate ถึง $leaveEndDate\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $sURL";
        }

        if ($sToken) {
            $chOne = curl_init();
            curl_setopt($chOne, CURLOPT_URL, "https://notify-api.line.me/api/notify");
            curl_setopt($chOne, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($chOne, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($chOne, CURLOPT_POST, 1);
            curl_setopt($chOne, CURLOPT_POSTFIELDS, "message=" . urlencode($message));
            $headers = array(
                'Content-type: application/x-www-form-urlencoded',
                'Authorization: Bearer ' . $sToken,
            );
            curl_setopt($chOne, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($chOne, CURLOPT_RETURNTRANSFER, 1);
            $result = curl_exec($chOne);

            if (curl_error($chOne)) {
                echo 'Error:' . curl_error($chOne);
            } else {
                $result_ = json_decode($result, true);
                echo "status : " . $result_['status'] . "\n";
                echo "message : " . $result_['message'] . "\n";
            }
            curl_close($chOne);
        }

        // แจ้งเตือนพี่ตุ๊ก
        $stmt = $conn->prepare("SELECT e_token FROM employees WHERE e_usercode = '3505004' AND e_level = 'manager'");
        // $stmt->bindParam(':depart', $depart);
        $stmt->execute();
        $managerTokens = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $managerMessage = "มีใบลาของ $empName\n$proveName ไม่อนุมัติใบลาเรียบร้อย \nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nวันเวลาที่ลา : $leaveStartDate ถึง $leaveEndDate\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $sURL";

        if ($leaveStatus == 'ยกเลิกใบลา') {
            $managerMessage = "$empName ยกเลิกใบลา\n$proveName ไม่อนุมัติยกเลิกใบลาของ $empName\nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nวันเวลาที่ลา : $leaveStartDate ถึง $leaveEndDate\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $sURL";
        }

        foreach ($managerTokens as $sToken) {
            $chOne = curl_init();
            curl_setopt($chOne, CURLOPT_URL, "https://notify-api.line.me/api/notify");
            curl_setopt($chOne, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($chOne, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($chOne, CURLOPT_POST, 1);
            curl_setopt($chOne, CURLOPT_POSTFIELDS, "message=" . urlencode($managerMessage));
            $headers = array(
                'Content-type: application/x-www-form-urlencoded',
                'Authorization: Bearer ' . $sToken,
            );
            curl_setopt($chOne, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($chOne, CURLOPT_RETURNTRANSFER, 1);
            $result = curl_exec($chOne);

            if (curl_error($chOne)) {
                echo 'Error:' . curl_error($chOne);
            } else {
                $result_ = json_decode($result, true);
                echo "status : " . $result_['status'] . "\n";
                echo "message : " . $result_['message'] . "\n";
            }
            curl_close($chOne);
        }

        // แจ้งเตือนไลน์ HR
        // $stmt = $conn->prepare("SELECT e_token FROM employees WHERE e_level = 'admin'");
        // $stmt->execute();
        // $adminTokens = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // foreach ($adminTokens as $sToken) {
        //     $chOne = curl_init();
        //     curl_setopt($chOne, CURLOPT_URL, "https://notify-api.line.me/api/notify");
        //     curl_setopt($chOne, CURLOPT_SSL_VERIFYHOST, 0);
        //     curl_setopt($chOne, CURLOPT_SSL_VERIFYPEER, 0);
        //     curl_setopt($chOne, CURLOPT_POST, 1);
        //     curl_setopt($chOne, CURLOPT_POSTFIELDS, "message=" . urlencode($sMessage));
        //     $headers = array(
        //         'Content-type: application/x-www-form-urlencoded',
        //         'Authorization: Bearer ' . $sToken,
        //     );
        //     curl_setopt($chOne, CURLOPT_HTTPHEADER, $headers);
        //     curl_setopt($chOne, CURLOPT_RETURNTRANSFER, 1);
        //     $result = curl_exec($chOne);

        //     if (curl_error($chOne)) {
        //         echo 'Error:' . curl_error($chOne);
        //     } else {
        //         $result_ = json_decode($result, true);
        //         echo "status : " . $result_['status'] . "\n";
        //         echo "message : " . $result_['message'] . "\n";
        //     }
        //     curl_close($chOne);
        // }

        echo "อัปเดตสถานะผ่านสำเร็จ";
    } else {
        echo 'อัปเดตสถานะผ่านไม่สำเร็จ';
    }
} else {
    echo "ไม่พบสถานะ";
}

// ปิดการเชื่อมต่อกับฐานข้อมูล
$conn = null;