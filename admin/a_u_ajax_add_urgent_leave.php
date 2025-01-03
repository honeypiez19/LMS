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

    $subDepart = $_POST['subDepart'];
    $subDepart2 = $_POST['subDepart2'];
    $subDepart3 = $_POST['subDepart3'];
    $subDepart4 = $_POST['subDepart4'];
    $subDepart5 = $_POST['subDepart5'];
    $urgentLeaveType = $_POST['urgentLeaveType'];
    $urgentLeaveReason = $_POST['urgentLeaveReason'];

    // ตรวจสอบประเภทการลาเร่งด่วน
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

    // 08:10
    if ($urgentStartTime == '08:10') {
        $urgentStartTimeLine = '08:10';
        $urgentStartTime = '08:30';
        $remark = '08:10:00';
    }
    // 08:15
    else if ($urgentStartTime == '08:15') {
        $urgentStartTimeLine = '08:15';
        $urgentStartTime = '08:30';
        $remark = '08:15:00';
    }
    // 08:45
    else if ($urgentStartTime == '08:45') {
        $urgentStartTimeLine = '08:45';
        $urgentStartTime = '09:00';
        $remark = '08:45:00';
    }
    // 09:10
    else if ($urgentStartTime == '09:10') {
        $urgentStartTimeLine = '09:10';
        $urgentStartTime = '09:30';
        $remark = '09:10:00';
    }
    // 09:15
    else if ($urgentStartTime == '09:15') {
        $urgentStartTimeLine = '09:15';
        $urgentStartTime = '09:30';
        $remark = '09:15:00';
    }
    // 09:45
    else if ($urgentStartTime == '09:45') {
        $urgentStartTimeLine = '09:45';
        $urgentStartTime = '10:00';
        $remark = '09:45:00';
    }
    // 10:10
    else if ($urgentStartTime == '10:10') {
        $urgentStartTimeLine = '10:10';
        $urgentStartTime = '10:30';
        $remark = '10:10:00';
    }
    // 10:15
    else if ($urgentStartTime == '10:15') {
        $urgentStartTimeLine = '10:15';
        $urgentStartTime = '10:30';
        $remark = '10:15:00';
    }
    // 10:45
    else if ($urgentStartTime == '10:45') {
        $urgentStartTimeLine = '10:45';
        $urgentStartTime = '11:00';
        $remark = '10:45:00';
    }
    // 11:45
    else if ($urgentStartTime == '12:00') {
        $urgentStartTimeLine = '11:45';
    }
    // 12:45
    else if ($urgentStartTime == '13:00') {
        $urgentStartTimeLine = '12:45';
    }
    // 13:10
    else if ($urgentStartTime == '13:10') {
        $urgentStartTimeLine = '13:10';
        $urgentStartTime = '13:30';
        $remark = '13:10:00';
    }
    // 13:15
    else if ($urgentStartTime == '13:15') {
        $urgentStartTimeLine = '13:15';
        $urgentStartTime = '13:30';
        $remark = '13:15:00';
    }
    // 13:40
    else if ($urgentStartTime == '13:40') {
        $urgentStartTimeLine = '13:40';
        $urgentStartTime = '14:00';
        $remark = '13:40:00';
    }
    // 13:45
    else if ($urgentStartTime == '13:45') {
        $urgentStartTimeLine = '13:45';
        $urgentStartTime = '14:00';
        $remark = '13:45:00';
    }
    // 14:10
    else if ($urgentStartTime == '14:10') {
        $urgentStartTimeLine = '14:10';
        $urgentStartTime = '14:30';
        $remark = '14:10:00';
    }
    // 14:15
    else if ($urgentStartTime == '14:15') {
        $urgentStartTimeLine = '14:15';
        $urgentStartTime = '14:30';
        $remark = '14:15:00';
    }
    // 14:40
    else if ($urgentStartTime == '14:40') {
        $urgentStartTimeLine = '14:40';
        $urgentStartTime = '15:00';
        $remark = '14:40:00';
    }
    // 14:45
    else if ($urgentStartTime == '14:45') {
        $urgentStartTimeLine = '14:45';
        $urgentStartTime = '15:00';
        $remark = '14:45:00';
    }
    // 15:10
    else if ($urgentStartTime == '15:10') {
        $urgentStartTimeLine = '15:10';
        $urgentStartTime = '15:30';
        $remark = '15:10:00';
    }
    // 15:15
    else if ($urgentStartTime == '15:15') {
        $urgentStartTimeLine = '15:15';
        $urgentStartTime = '15:30';
        $remark = '15:15:00';
    }
    // 15:40
    else if ($urgentStartTime == '15:40') {
        $urgentStartTimeLine = '15:40';
        $urgentStartTime = '16:00';
        $remark = '15:40:00';
    }
    // 15:45
    else if ($urgentStartTime == '15:45') {
        $urgentStartTimeLine = '15:45';
        $urgentStartTime = '16:00';
        $remark = '15:45:00';
    }
    // 16:10
    else if ($urgentStartTime == '16:10') {
        $urgentStartTimeLine = '16:10';
        $urgentStartTime = '16:30';
        $remark = '16:10:00';
    }
    // 16:15
    else if ($urgentStartTime == '16:15') {
        $urgentStartTimeLine = '16:15';
        $urgentStartTime = '16:30';
        $remark = '16:15:00';
    }
    // 16:40
    else if ($urgentStartTime == '17:00') {
        $urgentStartTimeLine = '16:40';
    } else {
        $urgentStartTimeLine = $urgentStartTime;
    }

    // 08:10
    if ($urgentEndTime == '08:10') {
        $urgentEndTimeLine = '08:10';
        $urgentEndTime = '08:30';
        $remark = '08:10:00';
    }
    // 08:15
    else if ($urgentEndTime == '08:15') {
        $urgentEndTimeLine = '08:15';
        $urgentEndTime = '08:30';
        $remark = '08:15:00';
    }
    // 08:45
    else if ($urgentEndTime == '08:45') {
        $urgentEndTimeLine = '08:45';
        $urgentEndTime = '09:00';
        $remark = '08:45:00';
    }
    // 09:10
    else if ($urgentEndTime == '09:10') {
        $urgentEndTimeLine = '09:10';
        $urgentEndTime = '09:30';
        $remark = '09:10:00';
    }
    // 09:15
    else if ($urgentEndTime == '09:15') {
        $urgentEndTimeLine = '09:15';
        $urgentEndTime = '09:30';
        $remark = '09:15:00';
    }
    // 09:45
    else if ($urgentEndTime == '09:45') {
        $urgentEndTimeLine = '09:45';
        $urgentEndTime = '10:00';
        $remark = '09:45:00';
    }
    // 10:10
    else if ($urgentEndTime == '10:10') {
        $urgentEndTimeLine = '10:10';
        $urgentEndTime = '10:30';
        $remark = '10:10:00';
    }
    // 10:15
    else if ($urgentEndTime == '10:15') {
        $urgentEndTimeLine = '10:15';
        $urgentEndTime = '10:30';
        $remark = '10:15:00';
    }
    // 10:45
    else if ($urgentEndTime == '10:45') {
        $urgentEndTimeLine = '10:45';
        $urgentEndTime = '11:00';
        $remark = '10:45:00';
    }
    // 11:45
    else if ($urgentEndTime == '12:00') {
        $urgentEndTimeLine = '11:45';
    }
    // 12:45
    else if ($urgentEndTime == '13:00') {
        $urgentEndTimeLine = '12:45';
    }
    // 13:10
    else if ($urgentEndTime == '13:10') {
        $urgentEndTimeLine = '13:10';
        $urgentEndTime = '13:30';
        $remark = '13:10:00';
    }
    // 13:15
    else if ($urgentEndTime == '13:15') {
        $urgentEndTimeLine = '13:15';
        $urgentEndTime = '13:30';
        $remark = '13:15:00';
    }
    // 13:40
    else if ($urgentEndTime == '13:40') {
        $urgentEndTimeLine = '13:40';
        $urgentEndTime = '14:00';
        $remark = '13:40:00';
    }
    // 13:45
    else if ($urgentEndTime == '13:45') {
        $urgentEndTimeLine = '13:45';
        $urgentEndTime = '14:00';
        $remark = '13:45:00';
    }
    // 14:10
    else if ($urgentEndTime == '14:10') {
        $urgentEndTimeLine = '14:10';
        $urgentEndTime = '14:30';
        $remark = '14:10:00';
    }
    // 14:15
    else if ($urgentEndTime == '14:15') {
        $urgentEndTimeLine = '14:15';
        $urgentEndTime = '14:30';
        $remark = '14:15:00';
    }
    // 14:40
    else if ($urgentEndTime == '14:40') {
        $urgentEndTimeLine = '14:40';
        $urgentEndTime = '15:00';
        $remark = '14:40:00';
    }
    // 14:45
    else if ($urgentEndTime == '14:45') {
        $urgentEndTimeLine = '14:45';
        $urgentEndTime = '15:00';
        $remark = '14:45:00';
    }
    // 15:10
    else if ($urgentEndTime == '15:10') {
        $urgentEndTimeLine = '15:10';
        $urgentEndTime = '15:30';
        $remark = '15:10:00';
    }
    // 15:15
    else if ($urgentEndTime == '15:15') {
        $urgentEndTimeLine = '15:15';
        $urgentEndTime = '15:30';
        $remark = '15:15:00';
    }
    // 15:40
    else if ($urgentEndTime == '15:40') {
        $urgentEndTimeLine = '15:40';
        $urgentEndTime = '16:00';
        $remark = '15:40:00';
    }
    // 15:45
    else if ($urgentEndTime == '15:45') {
        $urgentEndTimeLine = '15:45';
        $urgentEndTime = '16:00';
        $remark = '15:45:00';
    }
    // 16:10
    else if ($urgentEndTime == '16:10') {
        $urgentEndTimeLine = '16:10';
        $urgentEndTime = '16:30';
        $remark = '16:10:00';
    }
    // 16:15
    else if ($urgentEndTime == '16:15') {
        $urgentEndTimeLine = '16:15';
        $urgentEndTime = '16:30';
        $remark = '16:15:00';
    }
    // 16:40
    else if ($urgentEndTime == '17:00') {
        $urgentEndTimeLine = '16:40';
    } else {
        $urgentEndTimeLine = $urgentEndTime;
    }

    // วันที่สร้างใบลาเร่งด่วน
    $createDatetime = date('Y-m-d H:i:s');
    $remark2 = 'ลาฉุกเฉิน';

    // สถานะใบลา
    $leaveStatus = 0;
    $leaveStatusName = ($leaveStatus == 0) ? 'ปกติ' : 'ยกเลิก';

    $comfirmStatus = 0;
    $proveStatus = 6;
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

    $stmt = $conn->prepare("INSERT INTO leave_list (l_usercode, l_username, l_name, l_department, l_phone, l_leave_id, l_leave_reason, l_leave_start_date,
    l_leave_start_time, l_leave_end_date, l_leave_end_time, l_create_datetime, l_file, l_remark, l_leave_status,
    l_hr_status, l_approve_status, l_level, l_approve_status2, l_workplace, l_remark2)
    VALUES (:userCode, :userName, :name, :depart, :telPhone, :urgentLeaveType, :urgentLeaveReason, :urgentStartDate,
    :urgentStartTime, :urgentEndDate, :urgentEndTime, :createDatetime, :filename, :remark, :leaveStatus,
    :comfirmStatus, :proveStatus, :level, :proveStatus2, :workplace, :remark2)");

    // Bind Parameters
    $stmt->bindParam(':userCode', $userCode);
    $stmt->bindParam(':userName', $userName);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':depart', $depart);
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
    $stmt->bindParam(':remark2', $remark2);
    $stmt->bindParam(':leaveStatus', $leaveStatus);
    $stmt->bindParam(':comfirmStatus', $comfirmStatus);
    $stmt->bindParam(':proveStatus', $proveStatus);
    $stmt->bindParam(':level', $level);
    $stmt->bindParam(':proveStatus2', $proveStatus2);
    $stmt->bindParam(':workplace', $workplace);

    if ($stmt->execute()) {
        $sURL = 'https://lms.system-samt.com/';
        $sMessage = "มีใบลาด่วนของ $name \nประเภทการลา : $leaveName\nเหตุผลการลา : $urgentLeaveReason\nวันเวลาที่ลา : $urgentStartDate $urgentStartTime ถึง $urgentEndDate $urgentEndTime\nสถานะใบลา : $leaveStatusName\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด : $sURL";

        if ($depart == 'RD') {
            // แจ้งไลน์โฮซัง
            $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'manager' AND e_sub_department =  'RD'");
            // $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE e_department = 'Management' AND e_workplace = :workplace AND e_level = 'manager' AND e_sub_department = :depart");
            // $stmt = $conn->prepare("SELECT e_username, e_token FROM employees WHERE e_level = 'manager' AND e_workplace = 'Bang Phli' AND e_sub_department = 'RD'");
            $stmt->bindParam(':workplace', $workplace);
            // $stmt->bindParam(':depart', $depart);

        } else if ($depart == 'Office') {
            // บัญชี
            if ($subDepart == 'AC') {
                // แจ้งเตือนพี่แวว
                // $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'chief' AND e_sub_department = :subDepart");
                $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'chief' AND e_sub_department = 'AC'");
                $stmt->bindParam(':workplace', $workplace);
                // $stmt->bindParam(':subDepart', $subDepart);
            }
            // เซลล์
            else if ($subDepart == 'Sales') {
                // แจ้งเตือนพี่เจี๊ยบหรือพี่อ้อม
                // $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE e_workplace = :workplace AND e_level = 'chief' AND e_sub_department = :subDepart");
                $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE e_workplace = :workplace AND e_level = 'chief' AND e_sub_department = 'Sales'");
                $stmt->bindParam(':workplace', $workplace);
                // $stmt->bindParam(':subDepart', $subDepart);
            }
            // สโตร์
            else if ($subDepart == 'Store') {
                // แจ้งเตือนพี่เก๋
                $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'leader' AND e_sub_department = 'Store'");
                $stmt->bindParam(':workplace', $workplace);
                // $stmt->bindParam(':subDepart', $subDepart);
            }
            // HR
            else if ($subDepart == 'All') {
                $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'manager' AND e_sub_department = 'Office'");
                $stmt->bindParam(':workplace', $workplace);
                // $stmt->bindParam(':subDepart', $subDepart);
            }
            // พี่เต๋ / พี่น้อย / พี่ไว
            else if ($subDepart == '') {
                $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'manager' AND e_sub_department = 'Office'");
                $stmt->bindParam(':workplace', $workplace);
                // $stmt->bindParam(':subDepart', $subDepart);
            }
        } else {
            echo "ไม่พบเงื่อนไข";
        }

        // แจ้งเตือนไลน์หัวหน้ากับ ผจก ในแผนก
        // $stmt = $conn->prepare("SELECT e_token FROM employees WHERE e_department = :depart AND e_level IN ('chief', 'manager')");
        // $stmt->bindParam(':depart', $depart);
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
        } else {
            echo "No tokens found for chief or manager";
        }

        // แจ้งเตือนไลน์ HR
        // $stmt = $conn->prepare("SELECT e_token FROM employees WHERE e_level = 'admin'");
        // $stmt->execute();
        // $admins = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // $aMessage = "มีใบลาด่วนของ $name \nประเภทการลา : $leaveName\nเหตุผลการลา : $urgentLeaveReason\nวันเวลาที่ลา : $urgentStartDate $urgentStartTime ถึง $urgentEndDate $urgentEndTime\nสถานะใบลา : $leaveStatusName\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด : $sURL";
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
        // } else {
        //     echo "No tokens found for admin";
        // }
    } else {
        echo "Error: " . $stmt->errorInfo()[2] . "<br>";
    }

    $conn = null;
}
