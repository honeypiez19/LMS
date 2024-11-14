<?php
date_default_timezone_set('Asia/Bangkok');
require_once '../connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // เก็บข้อมูลจาก POST
    $userName = $_POST['userName'];
    $proveName = $_POST['proveName'];
    $createDateTime = $_POST['createDateTime'];
    $depart = $_POST['depart'];
    $lateDate = $_POST['lateDate'];
    $lateStart = $_POST['lateStart'];
    $lateEnd = $_POST['lateEnd'];
    $userCode = $_POST['userCode'];
    $name = $_POST['name'];
    $leaveStatus = $_POST['leaveStatus'];
    $action = $_POST['action'];
    $comfirmName = $_POST['comfirmName'];
    $level = $_POST['level'];
    $workplace = $_POST['workplace'];
    $leaveType = $_POST['leaveType'];

    $subDepart = $_POST['subDepart'];
    $subDepart2 = $_POST['subDepart2'];
    $subDepart3 = $_POST['subDepart3'];
    $subDepart4 = $_POST['subDepart4'];
    $subDepart5 = $_POST['subDepart5'];

    $proveDate = date('Y-m-d H:i:s');

    if ($action === 'approve') {
        $status = 2;
        $message = "$proveName อนุมัติ".$leaveType."ของ";
    } elseif ($action === 'deny') {
        $status = 3;
        $message = "$proveName ไม่อนุมัติ".$leaveType."ของ";
    } elseif ($action === 'confirm') {
        $status = 2;
        $message = "$comfirmName ยืนยัน".$leaveType."";
    } else {
        echo 'เกิดข้อผิดพลาดในการร้องขอ';
        exit;
    }

    // อัปเดตสถานะในฐานข้อมูล
    $sql = "UPDATE leave_list
            SET l_approve_status = :status, l_approve_datetime = :proveDate, l_approve_name = :userName
            WHERE l_usercode = :userCode AND l_create_datetime = :createDateTime";

    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':status', $status, PDO::PARAM_INT);
    $stmt->bindValue(':proveDate', $proveDate, PDO::PARAM_STR);
    $stmt->bindValue(':userCode', $userCode, PDO::PARAM_STR);
    $stmt->bindValue(':createDateTime', $createDateTime, PDO::PARAM_STR);
    $stmt->bindValue(':userName', $userName, PDO::PARAM_STR);

    if ($stmt->execute()) {
        $sURL = 'https://lms.system-samt.com/';
                $lMessage = "$message $name\nวันที่มาสาย : $lateDate\nเวลาที่มาสาย : $lateStart ถึง $lateEnd\nสถานะรายการ : $leaveStatus\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด: $sURL";
                if ($depart == 'RD') {
                    $sqlLate = "SELECT e_token, e_username FROM employees WHERE e_workplace = :workplace AND e_level = 'manager' AND e_sub_department = 'RD'";
                } else if($depart == 'Office') {
                    if( $subDepart == 'AC'){
                        $sqlLate = "SELECT e_token, e_username FROM employees WHERE e_workplace = :workplace AND e_level = 'manager' AND e_sub_department = 'Office'";
                    } else if( $subDepart == 'Sales'){
                        $sqlLate = "SELECT e_token, e_username FROM employees WHERE e_workplace = :workplace AND e_level = 'GM'";
                    } else if( $subDepart == 'Store'){
                        $sqlLate = "SELECT e_token, e_username FROM employees WHERE e_workplace = :workplace AND e_level = 'manager' AND e_sub_department = 'Office'";
                    } else if( $subDepart == 'All'){
                        $sqlLate = "SELECT e_token, e_username FROM employees WHERE e_workplace = :workplace AND e_level = 'manager' AND e_sub_department = 'Office'";
                    } else if( $subDepart == ''){
                        $sqlLate = "SELECT e_token, e_username FROM employees WHERE e_workplace = :workplace AND e_level = 'manager' AND e_sub_department = 'Office'";
                    } 
                }    
                else if ($depart == 'CAD1') {
                    if($subDepart == 'Modeling'){
                        $sqlLate = "SELECT e_token, e_username FROM employees WHERE e_workplace = :workplace AND e_level = 'assisManager' ";
                    }
                    else if($subDepart == 'Design'){
                        $sqlLate = "SELECT e_token, e_username FROM employees WHERE e_workplace = :workplace AND e_level = 'assisManager'";
                    }
                    else{
                        $sqlLate = "SELECT e_token, e_username FROM employees WHERE e_workplace = :workplace AND e_level = 'assisManager'";
                    }
                } else if ($depart == 'CAD2') {
                    $sqlLate = "SELECT e_token, e_username FROM employees WHERE e_workplace = :workplace AND e_level = 'assisManager'";
                } else if ($depart == 'CAM') {
                    $sqlLate = "SELECT e_token, e_username FROM employees WHERE e_workplace = :workplace AND e_level = 'assisManager'";
                } 
                else if ($depart == 'Management') {
                    if( $subDepart == 'AC'){
                        $sqlLate = "SELECT e_token, e_username FROM employees WHERE e_workplace = :workplace AND e_level = 'manager' AND e_sub_department = 'Office'";
                    } else if( $subDepart == 'Sales'){
                        $sqlLate = "SELECT e_token, e_username FROM employees WHERE e_workplace = :workplace AND e_level = 'GM'";
                    } else if( $subDepart == 'Store'){
                        $sqlLate = "SELECT e_token, e_username FROM employees WHERE e_workplace = :workplace AND e_level = 'manager' AND e_sub_department = 'Office'";
                    } else if( $subDepart == 'All'){
                        $sqlLate = "SELECT e_token, e_username FROM employees WHERE e_workplace = :workplace AND e_level = 'manager' AND e_sub_department = 'Office'";
                    } else if( $subDepart == ''){
                        $sqlLate = "SELECT e_token, e_username FROM employees WHERE e_workplace = :workplace AND e_level = 'manager' AND e_sub_department = 'Office'";
                    }         
                } 
                else {
                    echo "ไม่พบเงื่อนไข";
                }   
        
        $stmt = $conn->prepare($sqlLate);
        $stmt->bindValue(':workplace', $workplace, PDO::PARAM_STR);
        $stmt->execute();
        $managers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($managers) {
            foreach ($managers as $manager) {
                $sToken = $manager['e_token'];
                $chOne = curl_init();
                curl_setopt($chOne, CURLOPT_URL, "https://notify-api.line.me/api/notify");
                curl_setopt($chOne, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($chOne, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($chOne, CURLOPT_POST, 1);
                curl_setopt($chOne, CURLOPT_POSTFIELDS, "message=" . $lMessage);
                $headers = array('Content-type: application/x-www-form-urlencoded', 'Authorization: Bearer ' . $sToken);
                curl_setopt($chOne, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($chOne, CURLOPT_RETURNTRANSFER, 1);
                $result = curl_exec($chOne);

                if (curl_error($chOne)) {
                    echo 'Error:' . curl_error($chOne);
                }
                curl_close($chOne);
            }
        } else {
            echo "No tokens found for manager";
        }
        
        
        // ส่ง LINE Notification ให้พนักงาน
        // $stmt = $conn->prepare("SELECT e_token FROM employees WHERE e_usercode = :userCode");
        // $stmt->bindValue(':userCode', $userCode, PDO::PARAM_STR);
        // $stmt->execute();
        // $employee = $stmt->fetch(PDO::FETCH_ASSOC);

        // if ($employee) {
        //     $sToken = $employee['e_token'];
        //     $lMessage = "$message $name\nวันที่มาสาย : $lateDate\nเวลาที่มาสาย : $lateStart ถึง $lateEnd\nสถานะรายการ : $leaveStatus\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด: $sURL";
        //     $chOne = curl_init();
        //     curl_setopt($chOne, CURLOPT_URL, "https://notify-api.line.me/api/notify");
        //     curl_setopt($chOne, CURLOPT_SSL_VERIFYHOST, 0);
        //     curl_setopt($chOne, CURLOPT_SSL_VERIFYPEER, 0);
        //     curl_setopt($chOne, CURLOPT_POST, 1);
        //     curl_setopt($chOne, CURLOPT_POSTFIELDS, "message=" . $lMessage);
        //     $headers = array('Content-type: application/x-www-form-urlencoded', 'Authorization: Bearer ' . $sToken);
        //     curl_setopt($chOne, CURLOPT_HTTPHEADER, $headers);
        //     curl_setopt($chOne, CURLOPT_RETURNTRANSFER, 1);
        //     $result = curl_exec($chOne);

        //     if (curl_error($chOne)) {
        //         echo 'Error:' . curl_error($chOne);
        //     }
        //     curl_close($chOne);
        // } else {
        //     echo "ไม่พบ Token ของพนักงาน";
        // }
    } else {
        echo 'เกิดข้อผิดพลาดในการอัปเดตข้อมูล';
    }
} else {
    echo 'เกิดข้อผิดพลาดในการร้องขอ';
}