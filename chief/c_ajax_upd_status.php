<?php
// Start session
session_start();

require '../connect.php'; // Include database connection file
date_default_timezone_set('Asia/Bangkok'); // Set timezone to Thailand

$appDate = date("Y-m-d H:i:s");

// รับค่าที่ส่งมาจาก AJAX
$userCode = $_POST['userCode'];
$createDate = $_POST['createDate'];
$status = $_POST['status'];
$empName = $_POST['empName'];
$userName = $_POST['userName'];
$proveName = $_POST['proveName'];
$leaveType = $_POST['leaveType'];
$leaveReason = $_POST['leaveReason'];
$leaveStartDate = $_POST['leaveStartDate'];
$leaveEndDate = $_POST['leaveEndDate'];
$depart = $_POST['depart'];
$leaveStatus = $_POST['leaveStatus'];
$reasonNoProve = $_POST['reasonNoProve'];
$level = $_POST['level'];

// อนุมัติ
if ($status == '2') {
    // อัปเดตสถานะการลาในฐานข้อมูล
    $sql = "UPDATE leave_list SET l_approve_status = :status, l_approve_datetime = :appDate, l_approve_name = :userName
            WHERE l_usercode = :userCode AND l_create_datetime = :createDate";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':status' => $status,
        ':appDate' => $appDate,
        ':userName' => $userName,
        ':userCode' => $userCode,
        ':createDate' => $createDate
    ]);

    // แจ้งเตือน พนง
    $stmt = $conn->prepare("SELECT e_token FROM employees WHERE e_usercode = :usercode");
    $stmt->execute([':usercode' => $userCode]);
    $sToken = $stmt->fetchColumn();
    $sURL = 'https://lms.system-samt.com/';

    // ข้อความแจ้งเตือน
    $message = "$proveName อนุมัติใบลา \nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nวันเวลาที่ลา : $leaveStartDate ถึง $leaveEndDate\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $sURL";

    if ($leaveStatus == 'ยกเลิกใบลา') {
        $message = " $proveName อนุมัติยกเลิกใบลาของ $empName\nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nวันเวลาที่ลา : $leaveStartDate ถึง $leaveEndDate\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $sURL";
    }

    // ส่ง LINE Notify ไปยังพนักงาน
    if ($sToken) {
        $chOne = curl_init();
        curl_setopt($chOne, CURLOPT_URL, "https://notify-api.line.me/api/notify");
        curl_setopt($chOne, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($chOne, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($chOne, CURLOPT_POST, 1);
        curl_setopt($chOne, CURLOPT_POSTFIELDS, "message=" . urlencode($message));
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
            echo "status : " . $result_['status'] . "\n";
            echo "message : " . $result_['message'] . "\n";
        }
        curl_close($chOne);
    }

    if ($level == 'leader') {
        if ($depart == 'Office') {
            $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE e_level = 'manager' AND e_sub_department = 'Office'");
        } else if ($depart == 'CAD1') {
            $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE e_level = 'assisManager' AND e_sub_department = 'CAD1'");
        } else if ($depart == 'CAD2') {
            $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE e_level = 'assisManager' AND e_sub_department2 = 'CAD2'");
        } else if ($depart == 'CAM') {
            $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE e_level = 'assisManager' AND e_sub_department3 = 'CAM'");
        } else if ($depart == 'RD') {
            $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE e_level = 'manager' AND e_sub_department = 'RD'");
        }
    } else if ($level == 'chief' && $depart == 'Management') {
        $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE e_level = 'manager' AND e_sub_department = 'Office'");
    } else {
        echo "ไม่พบเงื่อนไข";
    }
    
    $stmt->execute();
    $managers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $managerMessage = "มีใบลาของ $empName\n$proveName อนุมัติใบลาเรียบร้อย \nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nวันเวลาที่ลา : $leaveStartDate ถึง $leaveEndDate\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $sURL";

    if ($leaveStatus == 'ยกเลิกใบลา') {
        $managerMessage = "$empName ยกเลิกใบลา\n$proveName อนุมัติยกเลิกใบลา\nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nวันเวลาที่ลา : $leaveStartDate ถึง $leaveEndDate\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $sURL";
    }
    if ($managers) {
        foreach ($managers as $manager) {
            $sToken = $manager['e_token'];

            $chOne = curl_init();
            curl_setopt($chOne, CURLOPT_URL, "https://notify-api.line.me/api/notify");
            curl_setopt($chOne, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($chOne, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($chOne, CURLOPT_POST, 1);
            curl_setopt($chOne, CURLOPT_POSTFIELDS, "message=" . urlencode($managerMessage));
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
                echo "status : " . $result_['status'] . "<br>";
                echo "message : " . $result_['message'] . "<br>";
            }
            curl_close($chOne);
        }
    } else {
        echo "No tokens found for managers";
    }
}
else if ($status == '3') {
    // อัปเดตสถานะการลาในฐานข้อมูล
    $sql = "UPDATE leave_list SET l_approve_status = :status, l_approve_datetime = :appDate, l_approve_name = :userName
            WHERE l_usercode = :userCode AND l_create_datetime = :createDate";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':status' => $status,
        ':appDate' => $appDate,
        ':userName' => $userName,
        ':userCode' => $userCode,
        ':createDate' => $createDate
    ]);

    // แจ้งเตือน พนง
    $stmt = $conn->prepare("SELECT e_token FROM employees WHERE e_usercode = :usercode");
    $stmt->execute([':usercode' => $userCode]);
    $sToken = $stmt->fetchColumn();
    $sURL = 'https://lms.system-samt.com/';

    // ข้อความแจ้งเตือน
    $message = "$proveName ไม่อนุมัติใบลา \nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nวันเวลาที่ลา : $leaveStartDate ถึง $leaveEndDate\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $sURL";

    if ($leaveStatus == 'ยกเลิกใบลา') {
        $message = " $proveName ไม่อนุมัติยกเลิกใบลาของ $empName\nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nวันเวลาที่ลา : $leaveStartDate ถึง $leaveEndDate\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $sURL";
    }

    // ส่ง LINE Notify ไปยังพนักงาน
    if ($sToken) {
        $chOne = curl_init();
        curl_setopt($chOne, CURLOPT_URL, "https://notify-api.line.me/api/notify");
        curl_setopt($chOne, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($chOne, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($chOne, CURLOPT_POST, 1);
        curl_setopt($chOne, CURLOPT_POSTFIELDS, "message=" . urlencode($message));
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
            echo "status : " . $result_['status'] . "\n";
            echo "message : " . $result_['message'] . "\n";
        }
        curl_close($chOne);
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
    }
    $stmt->execute();
    $managers = $stmt->fetchAll(PDO::FETCH_ASSOC);
   
    $managerMessage = "มีใบลาของ $empName\n$proveName ไม่อนุมัติใบลาเรียบร้อย \nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nวันเวลาที่ลา : $leaveStartDate ถึง $leaveEndDate\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $sURL";

    if ($leaveStatus == 'ยกเลิกใบลา') {
        $managerMessage = "$empName ยกเลิกใบลา\n$proveName ไม่อนุมัติยกเลิกใบลา\nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nวันเวลาที่ลา : $leaveStartDate ถึง $leaveEndDate\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $sURL";
    }
    
    if ($managers) {
        foreach ($managers as $manager) {
            $sToken = $manager['e_token'];

            $chOne = curl_init();
            curl_setopt($chOne, CURLOPT_URL, "https://notify-api.line.me/api/notify");
            curl_setopt($chOne, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($chOne, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($chOne, CURLOPT_POST, 1);
            curl_setopt($chOne, CURLOPT_POSTFIELDS, "message=" . urlencode($managerMessage));
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
                echo "status : " . $result_['status'] . "<br>";
                echo "message : " . $result_['message'] . "<br>";
            }
            curl_close($chOne);
        }
    } else {
        echo "No tokens found for managers";
    }
}
// ปิดการเชื่อมต่อกับฐานข้อมูล
$conn = null;