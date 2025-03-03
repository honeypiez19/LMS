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

    $leaveType   = $_POST['leaveType'];
    $leaveReason = $_POST['leaveReason'];
    $approver    = $_POST['approver'];

    $leaveTypes = [
        1 => 'ลากิจได้รับค่าจ้าง',
        2 => 'ลากิจไม่ได้รับค่าจ้าง',
        3 => 'ลาป่วย',
        4 => 'ลาป่วยจากงาน',
        5 => 'ลาพักร้อน',
        8 => 'อื่น ๆ',
    ];
    $leaveName = $leaveTypes[$leaveType] ?? 'ไม่พบประเภทการลา';

    // วันที่ + เวลาเริ่มต้นที่ลา
    $leaveDateStart = date('Y-m-d', strtotime($_POST['startDate']));
    $leaveTimeStart = $_POST['startTime'];

    // วันที่ + เวลาสิ้นสุดที่ลา
    $leaveDateEnd = date('Y-m-d', strtotime($_POST['endDate']));
    $leaveTimeEnd = $_POST['endTime'];

    $proveDate2 = date('Y-m-d H:i:s');
    $proveName2 = $userName;

    $timeMappings = [
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
        '11:45' => ['11:45', '12:00', null],
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

    if (isset($timeMappings[$leaveTimeStart])) {
        [$leaveTimeStartLine, $leaveTimeStart, $timeRemark] = $timeMappings[$leaveTimeStart];
    } else {
        $leaveTimeStartLine = $leaveTimeStart;
    }

    $timeMappings2 = [
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
        '11:45' => ['11:45', '12:00', null],
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

    if (isset($leaveTimeEnd, $timeMappings2)) {
        list($leaveTimeEndLine, $leaveTimeEnd, $timeRemark2) = $timeMappings2[$leaveTimeEnd];
    } else {
        $leaveTimeEndLine = $leaveTimeEnd;
    }

    $formattedDate = $_POST['formattedDate'];
    // $formattedDate = date('Y-m-d', strtotime($_POST['formattedDate']));

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

    $chkApprover = "SELECT * FROM employees WHERE e_name = :approver";
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

        $departments  = ['RD', 'CAD1', 'CAD2', 'CAM', 'Modeling', 'Design', 'Office', 'AC', 'Sales', 'Store', 'QC'];
        $specialDepts = ['MC', 'FN', 'PC'];
        $leaders      = ['leader', 'subLeader', 'chief'];
        $managers     = ['manager', 'manager2', 'assisManager'];

        if (in_array($subDepartment, $specialDepts)) {
            $proveStatus  = 6;
            $proveStatus2 = 4;
            $proveStatus3 = 7;
        } else if (in_array($levelApprover, $leaders) && in_array($subDepartment, $departments)) {
            $proveStatus  = 6;
            $proveStatus2 = 4;
            $proveStatus3 = 6;
        } elseif (in_array($levelApprover, $managers) && in_array($subDepartment, $departments)) {
            $proveStatus  = 6;
            $proveStatus2 = 4;
            $proveStatus3 = 6;
        } elseif ($levelApprover == 'GM') {
            $proveStatus  = 6;
            $proveStatus2 = 4;
            $proveStatus3 = 7;
        } elseif ($levelApprover == 'admin') {
            $proveStatus  = 6;
            $proveStatus2 = 4;
            $proveStatus3 = 6;
        } else {
            echo "ไม่พบแผนก";
        }

    }

    $stmt = $conn->prepare("INSERT INTO leave_list (l_usercode, l_username, l_name, l_department, l_phone, l_leave_id, l_leave_reason,
        l_leave_start_date, l_leave_start_time, l_leave_end_date, l_leave_end_time, l_create_datetime, l_file, l_leave_status,
        l_hr_status, l_approve_status, l_level, l_approve_status2, l_workplace, l_time_remark, l_approve_status3, l_time_remark2,
        l_approve_name2, l_approve_datetime2, l_file2, l_file3)
        VALUES (:userCode, :userName, :name, :depart, :telPhone, :leaveType, :leaveReason, :leaveDateStart, :leaveTimeStart,
        :leaveDateEnd, :leaveTimeEnd, :formattedDate, :filename, :leaveStatus, :comfirmStatus,
        :proveStatus, :level, :proveStatus2, :workplace, :timeRemark, :proveStatus3, :timeRemark2, :proveName2, :proveDate2,
        :filename2, :filename3)");

    $stmt->bindParam(':userCode', $userCode);
    $stmt->bindParam(':userName', $userName);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':depart', $depart);
    $stmt->bindParam(':telPhone', $telPhone);
    $stmt->bindParam(':leaveType', $leaveType);
    $stmt->bindParam(':leaveReason', $leaveReason);
    $stmt->bindParam(':leaveDateStart', $leaveDateStart);
    $stmt->bindParam(':leaveTimeStart', $leaveTimeStart);
    $stmt->bindParam(':leaveDateEnd', $leaveDateEnd);
    $stmt->bindParam(':leaveTimeEnd', $leaveTimeEnd);
    $stmt->bindParam(':formattedDate', $formattedDate);
    $stmt->bindParam(':filename', $filename);
    $stmt->bindParam(':filename2', $filename2); // เพิ่มพารามิเตอร์ใหม่
    $stmt->bindParam(':filename3', $filename3); // เพิ่มพารามิเตอร์ใหม่
    $stmt->bindParam(':leaveStatus', $leaveStatus);
    $stmt->bindParam(':comfirmStatus', $comfirmStatus);
    $stmt->bindParam(':proveStatus', $proveStatus);
    $stmt->bindParam(':proveStatus2', $proveStatus2);
    $stmt->bindParam(':level', $level);
    $stmt->bindParam(':workplace', $workplace);
    $stmt->bindParam(':timeRemark', $timeRemark);
    $stmt->bindParam(':timeRemark2', $timeRemark2);
    $stmt->bindParam(':proveStatus3', $proveStatus3);
    $stmt->bindParam(':proveName2', $proveName2);
    $stmt->bindParam(':proveDate2', $proveDate2);

    if ($stmt->execute()) {
        $sql  = "SELECT e_user_id FROM employees WHERE e_name = :approver AND e_workplace = :workplace";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':approver', $approver);
        $stmt->bindParam(':workplace', $workplace);
        $stmt->execute();
        $userIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if ($userIds) {
            $sURL     = 'https://lms.system-samt.com/';
            $sMessage = "มีใบลาของ $name \nประเภทการลา : $leaveName\nเหตุผลการลา : $leaveReason\n" .
                "วันเวลาที่ลา : $leaveDateStart $leaveTimeStartLine ถึง $leaveDateEnd $leaveTimeEndLine\n" .
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