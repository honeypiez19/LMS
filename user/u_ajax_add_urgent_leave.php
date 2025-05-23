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

    $urgentApprover = $_POST['urgentApprover'];

    // ประเภทการลา
    $leaveTypes = [
        1 => 'ลากิจได้รับค่าจ้าง',
        2 => 'ลากิจไม่ได้รับค่าจ้าง',
        5 => 'ลาพักร้อนฉุกเฉิน',
    ];
    $leaveName = $leaveTypes[$urgentLeaveType] ?? 'ไม่พบประเภทการลา';

    // เงื่อนไขลาพักร้อนฉุกเฉิน
    $leaveCondition = isset($_POST['leaveCondition']) ? $_POST['leaveCondition'] : null;

    // วันที่ + เวลาเริ่มต้น
    $urgentStartDate = date('Y-m-d', strtotime($_POST['urgentStartDate']));
    $urgentStartTime = $_POST['urgentStartTime'];

    // วันที่ + เวลาสิ้นสุด
    $urgentEndDate = date('Y-m-d', strtotime($_POST['urgentEndDate']));
    $urgentEndTime = $_POST['urgentEndTime'];

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
        '11:10' => ['11:10', '11:30', '11:10:00'],
        '11:15' => ['11:15', '11:30', '11:15:00'],
        '11:45' => ['11:45', '12:00', '11:45:00'],
        '12:45' => ['12:45', '13:00', '12:45:00'],
        '13:00' => ['12:45', '13:00', '12:45:00'],
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
        '16:40' => ['16:40', '17:00', '16:40:00'],
        '17:00' => ['16:40', '17:00', '16:40:00'],
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
        '11:10' => ['11:10', '11:30', '11:10:00'],
        '11:15' => ['11:15', '11:30', '11:15:00'],
        '11:45' => ['11:45', '12:00', '11:45:00'],
        '12:45' => ['12:45', '13:00', '12:45:00'],
        '13:00' => ['12:45', '13:00', '12:45:00'],
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
        '16:40' => ['16:40', '17:00', '16:40:00'],
        '17:00' => ['16:40', '17:00', '16:40:00'],
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

    $filename  = null;
    $filename2 = null;
    $filename3 = null;

// จัดการอัปโหลดไฟล์ที่ 1
    if (isset($_FILES['file1']) && $_FILES['file1']['error'] === UPLOAD_ERR_OK) {
        $filename      = time() . '_1_' . $_FILES['file1']['name']; // เพิ่ม timestamp และลำดับไฟล์เพื่อป้องกันชื่อซ้ำ
        $location      = "../upload/" . $filename;
        $imageFileType = strtolower(pathinfo($location, PATHINFO_EXTENSION));

        $valid_extensions = ["jpg", "jpeg", "png"];
        if (in_array($imageFileType, $valid_extensions)) {
            if (move_uploaded_file($_FILES['file1']['tmp_name'], $location)) {
                // อัปโหลดสำเร็จ
            } else {
                $filename = null; // กรณีอัปโหลดไม่สำเร็จ
            }
        } else {
            $filename = null; // กรณีไฟล์ไม่ใช่รูปภาพที่รองรับ
        }
    }

// จัดการอัปโหลดไฟล์ที่ 2
    if (isset($_FILES['file2']) && $_FILES['file2']['error'] === UPLOAD_ERR_OK) {
        $filename2     = time() . '_2_' . $_FILES['file2']['name'];
        $location      = "../upload/" . $filename2;
        $imageFileType = strtolower(pathinfo($location, PATHINFO_EXTENSION));

        $valid_extensions = ["jpg", "jpeg", "png"];
        if (in_array($imageFileType, $valid_extensions)) {
            if (move_uploaded_file($_FILES['file2']['tmp_name'], $location)) {
                // อัปโหลดสำเร็จ
            } else {
                $filename2 = null;
            }
        } else {
            $filename2 = null;
        }
    }

// จัดการอัปโหลดไฟล์ที่ 3
    if (isset($_FILES['file3']) && $_FILES['file3']['error'] === UPLOAD_ERR_OK) {
        $filename3     = time() . '_3_' . $_FILES['file3']['name'];
        $location      = "../upload/" . $filename3;
        $imageFileType = strtolower(pathinfo($location, PATHINFO_EXTENSION));

        $valid_extensions = ["jpg", "jpeg", "png"];
        if (in_array($imageFileType, $valid_extensions)) {
            if (move_uploaded_file($_FILES['file3']['tmp_name'], $location)) {
                // อัปโหลดสำเร็จ
            } else {
                $filename3 = null;
            }
        } else {
            $filename3 = null;
        }
    }

    $chkApprover = "SELECT e_sub_department, e_sub_department2, e_sub_department3, e_sub_department4, e_sub_department5, e_level FROM employees WHERE e_name = :urgentApprover";
    $stmt        = $conn->prepare($chkApprover);
    $stmt->bindParam(':urgentApprover', $urgentApprover, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

// กำหนดค่าเริ่มต้น
    $proveStatus  = 0;
    $proveStatus2 = 0;
    $proveStatus3 = 0;

    if ($result) {
        $departments = [
            $result['e_sub_department'],
            $result['e_sub_department2'],
            $result['e_sub_department3'],
            $result['e_sub_department4'],
            $result['e_sub_department5'],
        ];

        $levelApprover = $result['e_level'];

        $leaderChiefDepartments = ['Store', 'AC', 'Office', 'CAD1 Design Modeling', 'CAD2', 'CAM', 'Sales', 'MC', 'FN', 'PC', 'QC', 'RD'];
        $managerDepartments     = ['Store', 'AC', 'Office', 'CAD1 Design Modeling', 'CAD2', 'CAM', 'Sales', 'MC', 'FN', 'PC', 'QC', 'RD'];

        if (in_array($levelApprover, ['leader', 'chief', 'subLeader'])) {
            if (in_array('Office', $departments)) {
                $proveStatus = 6;
            }
            if (in_array('QC', $departments) || in_array('Sales', $departments)) {
                $proveStatus2 = 6;
            } else {
                $proveStatus2 = 1;
            }
            $proveStatus3 = 7;
        } elseif (in_array($levelApprover, ['manager', 'manager2', 'assisManager'])) {
            $proveStatus = 6;
            if (in_array('Sales', $departments) || in_array('QC', $departments)) {
                $proveStatus2 = 6;
            } else {
                $proveStatus2 = 1;
            }
            $proveStatus3 = 7;
        } elseif ($levelApprover === 'GM') {
            $proveStatus  = 6;
            $proveStatus2 = 6;
            $proveStatus3 = 7;
        } elseif ($levelApprover === 'admin') {
            $proveStatus  = 6;
            $proveStatus2 = 6;
            $proveStatus3 = 6;
        }
    }

    if ($urgentLeaveType == 5 && $leaveCondition) {
        // เพิ่ม field lt2_id ในการ INSERT
        $stmt = $conn->prepare("INSERT INTO leave_list (l_usercode, l_username, l_name, l_department, l_phone, l_leave_id, l_leave_reason,
        l_leave_start_date, l_leave_start_time, l_leave_end_date, l_leave_end_time, l_create_datetime, l_file, l_file2, l_file3, l_leave_status,
        l_hr_status, l_approve_status, l_level, l_approve_status2, l_workplace, l_remark, l_approve_status3, l_time_remark, l_time_remark2, l_leave_id2)
        VALUES (:userCode, :userName, :name, :depart, :telPhone, :urgentLeaveType, :urgentLeaveReason, :urgentStartDate, :urgentStartTime,
        :urgentEndDate, :urgentEndTime, :formattedDate, :filename, :filename2, :filename3, :leaveStatus, :comfirmStatus,
        :proveStatus, :level, :proveStatus2, :workplace, :remark, :proveStatus3, :timeRemark, :timeRemark2, :leaveCondition)");

        // Bind เพิ่ม parameter leaveCondition
        $stmt->bindParam(':leaveCondition', $leaveCondition);
    } else {
        // ใช้ query เดิมถ้าไม่ใช่ลาพักร้อนฉุกเฉินหรือไม่มีเงื่อนไข
        $stmt = $conn->prepare("INSERT INTO leave_list (l_usercode, l_username, l_name, l_department, l_phone, l_leave_id, l_leave_reason,
        l_leave_start_date, l_leave_start_time, l_leave_end_date, l_leave_end_time, l_create_datetime, l_file, l_file2, l_file3, l_leave_status,
        l_hr_status, l_approve_status, l_level, l_approve_status2, l_workplace, l_remark, l_approve_status3, l_time_remark, l_time_remark2)
        VALUES (:userCode, :userName, :name, :depart, :telPhone, :urgentLeaveType, :urgentLeaveReason, :urgentStartDate, :urgentStartTime,
        :urgentEndDate, :urgentEndTime, :formattedDate, :filename, :filename2, :filename3, :leaveStatus, :comfirmStatus,
        :proveStatus, :level, :proveStatus2, :workplace, :remark, :proveStatus3, :timeRemark, :timeRemark2)");
    }

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
    $stmt->bindParam(':filename2', $filename2);
    $stmt->bindParam(':filename3', $filename3);
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

    if ($stmt->execute()) {
        // Change the SQL query to fetch both user_id AND username
        $sql  = "SELECT e_user_id, e_username FROM employees WHERE e_name = :urgentApprover AND e_workplace = :workplace";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':urgentApprover', $urgentApprover);
        $stmt->bindParam(':workplace', $workplace);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch as associative array

        if ($users) {
            $sURL = 'https://lms.system-samt.com/';

            foreach ($users as $user) {
                $userId    = $user['e_user_id'];
                $proveName = $user['e_username'];

                $sMessage = "K." . $proveName . "\n\nมีใบลาฉุกเฉินของ $name \nประเภทการลา : $leaveName\nเหตุผลการลา : $urgentLeaveReason\n" .
                    "วันเวลาที่ลา : $urgentStartDate $urgentStartTimeLine ถึง $urgentEndDate $urgentEndTimeLine\n" .
                    "สถานะใบลา : $leaveStatusName\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด : $sURL";

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
                    echo "Warning: ไม่สามารถส่งข้อความ Line ได้ " . curl_error($ch);
                }
            }
        }
    } else {
        echo "Error: " . $stmt->errorInfo()[2] . "<br>";
    }

    $conn = null;
}