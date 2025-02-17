<?php
session_start();
date_default_timezone_set('Asia/Bangkok');

include '../connect.php';
include '../access_token_channel.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userCode  = $_POST['userCode'];
    $userName  = $_POST['userName'];
    $name      = $_POST['name'];
    $telPhone  = $_POST['telPhone'];
    $depart    = $_POST['depart'];
    $level     = $_POST['level'];
    $workplace = $_POST['workplace'];

    $urgentLeaveType   = $_POST['urgentLeaveType'];
    $urgentLeaveReason = $_POST['urgentLeaveReason'];

    $approver = $_POST['urgentApprover'];

    // ตรวจสอบประเภทการลา
    $leaveTypes = [
        1 => 'ลากิจได้รับค่าจ้าง',
        2 => 'ลากิจไม่ได้รับค่าจ้าง',
        5 => 'ลาพักร้อนฉุกเฉิน',
    ];
    $leaveName = $leaveTypes[$urgentLeaveType] ?? 'ไม่พบประเภทการลา';

// วันที่ + เวลาเริ่มต้น
    $urgentStartDate = date('Y-m-d', strtotime($_POST['urgentStartDate']));
    $urgentStartTime = $_POST['urgentStartTime'];

// วันที่ + เวลาสิ้นสุด
    $urgentEndDate = date('Y-m-d', strtotime($_POST['urgentEndDate']));
    $urgentEndTime = $_POST['urgentEndTime'];

    $proveDate3 = date('Y-m-d H:i:s');
    $proveName3 = $userName;

    $timeMapping = [
        '08:10' => ['08:10', '08:30', '08:10:00'],
        '08:15' => ['08:15', '08:30', '08:15:00'],
        '08:45' => ['08:45', '09:00', '08:45:00'],
        '09:10' => ['09:10', '09:30', '09:10:00'],
        '09:15' => ['09:15', '09:30', '09:15:00'],
        '09:45' => ['09:45', '10:00', '09:45:00'],
        '10:10' => ['10:10', '10:30', '10:10:00'],
        '10:15' => ['10:15', '10:30', '10:15:00'],
        '10:45' => ['10:45', '11:00', '10:45:00'],
        '12:00' => ['11:45', '12:00', null],
        '13:00' => ['12:45', '13:00', null],
        '13:10' => ['13:10', '13:30', '13:10:00'],
        '13:15' => ['13:15', '13:30', '13:15:00'],
        '13:40' => ['13:40', '14:00', '13:40:00'],
        '13:45' => ['13:45', '14:00', '13:45:00'],
        '14:10' => ['14:10', '14:30', '14:10:00'],
        '14:15' => ['14:15', '14:30', '14:15:00'],
        '14:40' => ['14:40', '15:00', '14:40:00'],
        '14:45' => ['14:45', '15:00', '14:45:00'],
        '15:10' => ['15:10', '15:30', '15:10:00'],
        '15:15' => ['15:15', '15:30', '15:15:00'],
        '15:40' => ['15:40', '16:00', '15:40:00'],
        '15:45' => ['15:45', '16:00', '15:45:00'],
        '16:10' => ['16:10', '16:30', '16:10:00'],
        '16:15' => ['16:15', '16:30', '16:15:00'],
        '17:00' => ['16:40', '17:00', null],
    ];

    if (isset($timeMapping[$urgentStartTime])) {
        [$urgentStartTimeLine, $urgentStartTime, $timeRemark] = $timeMapping[$urgentStartTime];
    } else {
        $urgentStartTimeLine = $urgentStartTime;
    }

    $timeMapping2 = [
        '08:10' => ['08:10', '08:30', '08:10:00'],
        '08:15' => ['08:15', '08:30', '08:15:00'],
        '08:45' => ['08:45', '09:00', '08:45:00'],
        '09:10' => ['09:10', '09:30', '09:10:00'],
        '09:15' => ['09:15', '09:30', '09:15:00'],
        '09:45' => ['09:45', '10:00', '09:45:00'],
        '10:10' => ['10:10', '10:30', '10:10:00'],
        '10:15' => ['10:15', '10:30', '10:15:00'],
        '10:45' => ['10:45', '11:00', '10:45:00'],
        '12:00' => ['11:45', '12:00', null],
        '13:00' => ['12:45', '13:00', null],
        '13:10' => ['13:10', '13:30', '13:10:00'],
        '13:15' => ['13:15', '13:30', '13:15:00'],
        '13:40' => ['13:40', '14:00', '13:40:00'],
        '13:45' => ['13:45', '14:00', '13:45:00'],
        '14:10' => ['14:10', '14:30', '14:10:00'],
        '14:15' => ['14:15', '14:30', '14:15:00'],
        '14:40' => ['14:40', '15:00', '14:40:00'],
        '14:45' => ['14:45', '15:00', '14:45:00'],
        '15:10' => ['15:10', '15:30', '15:10:00'],
        '15:15' => ['15:15', '15:30', '15:15:00'],
        '15:40' => ['15:40', '16:00', '15:40:00'],
        '15:45' => ['15:45', '16:00', '15:45:00'],
        '16:10' => ['16:10', '16:30', '16:10:00'],
        '16:15' => ['16:15', '16:30', '16:15:00'],
        '17:00' => ['16:40', '17:00', null],
    ];

    if (isset($timeMapping2[$urgentEndTime])) {
        [$urgentEndTimeLine, $urgentEndTime, $timeRemark2] = $timeMapping2[$urgentEndTime];
    } else {
        $urgentEndTimeLine = $urgentEndTime;
    }

    $formattedDate = $_POST['formattedDate'];
    // $formattedDate = date('Y-m-d', strtotime($_POST['formattedDate']));

    $remark = 'ลาฉุกเฉิน';

    // สถานะใบลา
    $leaveStatus     = 0;
    $leaveStatusName = ($leaveStatus == 0) ? 'ปกติ' : 'ยกเลิก';

    $comfirmStatus = 0;

    $subDepart  = $_POST['subDepart'];
    $subDepart2 = $_POST['subDepart2'];
    $subDepart3 = $_POST['subDepart3'];
    $subDepart4 = $_POST['subDepart4'];
    $subDepart5 = $_POST['subDepart5'];

    $filename = null;
    if (isset($_FILES['urgentFile']) && $_FILES['urgentFile']['error'] === UPLOAD_ERR_OK) {
        $filename      = $_FILES['urgentFile']['name'];
        $location      = "../upload/" . $filename;
        $imageFileType = strtolower(pathinfo($location, PATHINFO_EXTENSION));

        $valid_extensions = ["jpg", "jpeg", "png"];
        if (in_array($imageFileType, $valid_extensions)) {
            if (move_uploaded_file($_FILES['urgentFile']['tmp_name'], $location)) {
                $response = $location;
            }
        }
    }

    $chkApprover = "SELECT e_sub_department, e_level FROM employees WHERE e_username = :approver";
    $stmt        = $conn->prepare($chkApprover);
    $stmt->bindParam(':approver', $approver, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $proveStatus  = null;
    $proveStatus2 = null;
    $proveStatus3 = null;

    if ($result) {
        $subDepartment = $result['e_sub_department'];
        $levelApprover = $result['e_level'];

        $departments = ['RD', 'CAD1', 'CAD2', 'CAM', 'Modeling', 'Design', 'Office', 'AC', 'Sales', 'Store', 'MC', 'FN', 'PC', 'QC'];
        $leaders     = ['leader', 'subLeader', 'chief'];
        $managers    = ['manager', 'manager2', 'assisManager'];

        if (in_array($levelApprover, $leaders) && in_array($subDepartment, $departments)) {
            $proveStatus  = 6;
            $proveStatus2 = 6;
            $proveStatus3 = 6;
        } elseif (in_array($levelApprover, $managers) && in_array($subDepartment, $departments)) {
            $proveStatus  = 6;
            $proveStatus2 = 6;
            $proveStatus3 = 6;
        } elseif ($levelApprover == 'GM') {
            $proveStatus  = 6;
            $proveStatus2 = 6;
            $proveStatus3 = 8;
        } elseif ($levelApprover == 'admin') {
            $proveStatus  = 6;
            $proveStatus2 = 6;
            $proveStatus3 = 8;
        } else {
            echo "ไม่พบแผนก";
        }
    }

    $stmt = $conn->prepare("INSERT INTO leave_list (l_usercode, l_username, l_name, l_department, l_phone, l_leave_id, l_leave_reason,
        l_leave_start_date, l_leave_start_time, l_leave_end_date, l_leave_end_time, l_create_datetime, l_file, l_leave_status,
        l_hr_status, l_approve_status, l_level, l_approve_status2, l_workplace, l_remark, l_approve_status3, l_time_remark, l_time_remark2
        , l_approve_name3, l_approve_datetime3)
        VALUES (:userCode, :userName, :name, :depart, :telPhone, :urgentLeaveType, :urgentLeaveReason, :urgentStartDate, :urgentStartTime,
        :urgentEndDate, :urgentEndTime, :formattedDate, :filename, :leaveStatus, :comfirmStatus,
        :proveStatus, :level, :proveStatus2, :workplace, :remark, :proveStatus3, :timeRemark, :timeRemark2, :proveName3, :proveDate3)");

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
    $stmt->bindParam(':formattedDate', $formattedDate);
    $stmt->bindParam(':filename', $filename);
    $stmt->bindParam(':leaveStatus', $leaveStatus);
    $stmt->bindParam(':comfirmStatus', $comfirmStatus);
    $stmt->bindParam(':proveStatus', $proveStatus);
    $stmt->bindParam(':proveStatus2', $proveStatus2);
    $stmt->bindParam(':level', $level);
    $stmt->bindParam(':workplace', $workplace);
    $stmt->bindParam(':remark', $remark);
    $stmt->bindParam(':proveStatus3', $proveStatus3);
    $stmt->bindParam(':timeRemark', $timeRemark);
    $stmt->bindParam(':timeRemark2', $timeRemark2);
    $stmt->bindParam(':proveName3', $proveName3);
    $stmt->bindParam(':proveDate3', $proveDate3);

    if ($stmt->execute()) {
        $sql  = "SELECT e_user_id FROM employees WHERE e_username = :approver AND e_workplace = :workplace";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':approver', $approver);
        $stmt->bindParam(':workplace', $workplace);
        $stmt->execute();
        $userIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if ($userIds) {
            $sURL     = 'https://lms.system-samt.com/';
            $sMessage = "มีใบลาฉุกเฉินของ $name \nประเภทการลา : $leaveName\nเหตุผลการลา : $urgentLeaveReason\n" .
                "วันเวลาที่ลา : $urgentStartDate $urgentStartTimeLine ถึง $urgentEndDate $urgentEndTimeLine\n" .
                "สถานะใบลา : $leaveStatusName\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด : $sURL";

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
                curl_setopt($ch, CURLOPT_URL, 'https://api.line.me/v2/bot/message/push');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $access_token,
                ]);

                $response = curl_exec($ch);
                curl_close($ch);

                if ($response === false) {
                    echo "Error: " . curl_error($ch);
                }
            }
        } else {
            echo "ไม่พบผู้รับข้อความ";
        }

    } else {
        echo "Error: " . $stmt->errorInfo()[2] . "<br>";
    }

    $conn = null;
}