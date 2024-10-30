<?php
session_start();
date_default_timezone_set('Asia/Bangkok');

require '../connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userCode = $_POST['userCode'];
    $userName = $_POST['userName'];
    $name = $_POST['name'];
    $telPhone = $_POST['telPhone'];
    $depart = $_POST['depart'];
    $level = $_POST['level'];
    $workplace = $_POST['workplace'];

    $urgentLeaveType = $_POST['urgentLeaveType'];
    $urgentLeaveReason = $_POST['urgentLeaveReason'];

    // ประเภทการลาเร่งด่วน
    $leaveTypes = [
        1 => 'ลากิจได้รับค่าจ้าง',
        2 => 'ลากิจไม่ได้รับค่าจ้าง',
        5 => 'ลาพักร้อนฉุกเฉิน',
    ];
    $leaveName = $leaveTypes[$urgentLeaveType] ?? 'ไม่พบประเภทการลา';

    // วันที่ + เวลาเริ่มต้นที่ลาเร่งด่วน
    $urgentStartDate = date('Y-m-d', strtotime($_POST['urgentStartDate']));
    $urgentStartTime = $_POST['urgentStartTime'];

    // วันที่ + เวลาสิ้นสุดที่ลาเร่งด่วน
    $urgentEndDate = date('Y-m-d', strtotime($_POST['urgentEndDate']));
    $urgentEndTime = $_POST['urgentEndTime'];

    // วันที่สร้างใบลาเร่งด่วน
    $createDatetime = date('Y-m-d H:i:s');
    $remark = 'ลาฉุกเฉิน';

    $leaveStatus = 0;

    $comfirmStatus = 0;

    $proveStatus = 2;
    $proveName = $userName;
    $proveDate = date('Y-m-d H:i:s');

    $proveStatus2 = 1;

    $filename = null;
    if (isset($_FILES['urgentFile']) && $_FILES['urgentFile']['error'] === UPLOAD_ERR_OK) {
        $filename = $_FILES['urgentFile']['name'];
        $location = "../upload/" . $filename;
        $imageFileType = strtolower(pathinfo($location, PATHINFO_EXTENSION));

        $valid_extensions = ["jpg", "jpeg", "png"];
        if (in_array($imageFileType, $valid_extensions)) {
            if (move_uploaded_file($_FILES['urgentFile']['tmp_name'], $location)) {
                $response = $location;
            }
        }
    }

    $stmt = $conn->prepare("INSERT INTO leave_list (l_usercode, l_username, l_name, l_department, l_phone, l_leave_id, l_leave_reason,
    l_leave_start_date, l_leave_start_time, l_leave_end_date, l_leave_end_time, l_create_datetime, l_file, l_remark, l_leave_status, l_hr_status, l_approve_status,
    l_approve_name,
    l_approve_datetime,
    l_level, l_approve_status2, l_workplace)
    VALUES (:userCode, :userName, :name, :depart, :telPhone, :urgentLeaveType, :urgentLeaveReason, :urgentStartDate, :urgentStartTime,
    :urgentEndDate, :urgentEndTime, :createDatetime, :filename, :remark, :leaveStatus, :comfirmStatus, :proveStatus,   :proveName,
    :proveDate, :level, :proveStatus2, :workplace)");

    $stmt->bindParam(':userCode', $userCode);
    $stmt->bindParam(':userName', $userName);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':depart', $depart);
    $stmt->bindParam(':workplace', $workplace);
    $stmt->bindParam(':telPhone', $telPhone);
    $stmt->bindParam(':urgentLeaveType', $urgentLeaveType);
    $stmt->bindParam(':urgentLeaveReason', $urgentLeaveReason);
    $stmt->bindParam(':urgentStartDate', $urgentStartDate);
    $stmt->bindParam(':urgentStartTime', $urgentStartTime);
    $stmt->bindParam(':urgentEndDate', $urgentEndDate);
    $stmt->bindParam(':urgentEndTime', $urgentEndTime);
    $stmt->bindParam(':createDatetime', $createDatetime);
    $stmt->bindParam(':filename', $filename);
    $stmt->bindParam(':remark', $remark);
    $stmt->bindParam(':leaveStatus', $leaveStatus);
    $stmt->bindParam(':comfirmStatus', $comfirmStatus);
    $stmt->bindParam(':proveStatus', $proveStatus);
    $stmt->bindParam(':proveName', $proveName);
    $stmt->bindParam(':proveDate', $proveDate);
    $stmt->bindParam(':proveStatus2', $proveStatus2);
    $stmt->bindParam(':level', $level);
    $stmt->bindParam(':workplace', $workplace);

    if ($stmt->execute()) {
        // แจ้งเตือนไลน์ ผจก ในแผนก
        // $stmt = $conn->prepare("SELECT e_username, e_token FROM employees WHERE e_department = :depart AND e_level = 'manager'");

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

        // $stmt->bindParam(':depart', $depart);
        $stmt->execute();
        $managers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $sURL = 'https://lms.system-samt.com/';

        if ($managers) {
            foreach ($managers as $manager) {
                $sToken = $manager['e_token'];
                $sMessage = "มีใบลาด่วนของ $name \nประเภทการลา : $leaveName\nเหตุผลการลา : $urgentLeaveReason\nวันเวลาที่ลา : $urgentStartDate $urgentStartTime ถึง $urgentEndDate $urgentEndTime\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด : $sURL";

                $chOne = curl_init();
                curl_setopt($chOne, CURLOPT_URL, "https://notify-api.line.me/api/notify");
                curl_setopt($chOne, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($chOne, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($chOne, CURLOPT_POST, 1);
                curl_setopt($chOne, CURLOPT_POSTFIELDS, "message=" . $sMessage);
                $headers = array('Content-type: application/x-www-form-urlencoded', 'Authorization: Bearer ' . $sToken . '');
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
            echo "No tokens found for manager";
        }
    } else {
        echo "Error: " . $stmt->errorInfo()[2] . "<br>";
    }

    $conn = null;
}