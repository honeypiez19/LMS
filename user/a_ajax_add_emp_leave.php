<?php
session_start();
date_default_timezone_set('Asia/Bangkok');

require '../connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // รับค่าจากฟอร์ม (ใช้รูปแบบเดิม)
    $userCode    = $_POST['userCode'];
    $userName    = $_POST['userName'];
    $name        = $_POST['name'];
    $telPhone    = $_POST['telPhone'];
    $depart      = $_POST['depart'];
    $level       = $_POST['level'];
    $workplace   = $_POST['workplace'];
    $subDepart   = $_POST['subDepart'];
    $addUserName = $_POST['addUserName'];

    $leaveType   = $_POST['leaveType'];
    $leaveReason = $_POST['leaveReason'];
    $remark      = 'ลาย้อนหลัง';

    $createDateByHR = date('Y-m-d H:i:s');
    $createDate     = date('Y-m-d H:i:s');

    // ตรวจสอบประเภทการลา
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

    // กำหนดค่า mapping สำหรับเวลา
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

    $timeRemark         = $leaveTimeStart . ':00'; // เพิ่ม :00 ต่อท้ายเวลาเริ่มต้น
    $timeRemark2        = $leaveTimeEnd . ':00';   // เพิ่ม :00 ต่อท้ายเวลาสิ้นสุด
    $leaveTimeStartLine = $leaveTimeStart;
    $leaveTimeEndLine   = $leaveTimeEnd;

    // ตรวจสอบและกำหนดค่าเวลาเริ่มต้น
    if (isset($timeMappings[$leaveTimeStart])) {
        $leaveTimeStartLine = $timeMappings[$leaveTimeStart][0];
        $leaveTimeStart     = $timeMappings[$leaveTimeStart][1] . ':00';
        // กำหนดค่า timeRemark เฉพาะเมื่อค่าในตารางไม่ใช่ null
        if ($timeMappings[$leaveTimeStart][2] !== null) {
            $timeRemark = $timeMappings[$leaveTimeStart][2];
        }
    } else {
        $leaveTimeStart = $leaveTimeStart . ':00';
    }

    // ตรวจสอบและกำหนดค่าเวลาสิ้นสุด
    if (isset($timeMappings[$leaveTimeEnd])) {
        $leaveTimeEndLine = $timeMappings[$leaveTimeEnd][0];
        $leaveTimeEnd     = $timeMappings[$leaveTimeEnd][1] . ':00';

        // กรณีพิเศษสำหรับเวลา 17:00 ให้ remark เป็น 16:40:00 เสมอ
        if ($leaveTimeEnd === '17:00:00') {
            $timeRemark2 = '16:40:00';
        }
        // กำหนดค่า timeRemark2 เฉพาะเมื่อค่าในตารางไม่ใช่ null
        else if ($timeMappings[$leaveTimeEnd][2] !== null) {
            $timeRemark2 = $timeMappings[$leaveTimeEnd][2];
        }
    } else {
        $leaveTimeEnd = $leaveTimeEnd . ':00';
    }

    // กำหนดค่าเริ่มต้น
    $proveStatus   = 0;
    $proveStatus2  = 0;
    $proveStatus3  = 0;
    $comfirmStatus = 1;

    // ค้นหาว่าพนักงานอยู่แผนกไหนและระดับอะไร
    $chkEmployee = "SELECT
                        e_usercode,
                        e_department,
                        e_sub_department,
                        e_sub_department2,
                        e_sub_department3,
                        e_sub_department4,
                        e_sub_department5,
                        e_level
                    FROM employees
                    WHERE e_usercode = :userCode";
    $stmt = $conn->prepare($chkEmployee);
    $stmt->bindParam(':userCode', $userCode, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $departments = [
            $result['e_sub_department'],
            $result['e_sub_department2'],
            $result['e_sub_department3'],
            $result['e_sub_department4'],
            $result['e_sub_department5'],
        ];

        $levelApprover = $result['e_level'];

        // แผนกที่มีลำดับขั้นการอนุมัติแบบต่างๆ
        $leaderChiefDepartments = ['Store', 'AC', 'Office', 'CAD1 Design Modeling', 'CAD2', 'CAM', 'Sales', 'MC', 'FN', 'PC', 'QC', 'RD'];
        $managerDepartments     = ['Store', 'AC', 'Office', 'CAD1 Design Modeling', 'CAD2', 'CAM', 'Sales', 'MC', 'FN', 'PC', 'QC', 'RD'];

        // กำหนดค่า proveStatus ตามระดับของพนักงานและแผนก
        if (in_array($levelApprover, ['leader', 'chief', 'subLeader'])) {
            if (in_array('Office', $departments)) {
                $proveStatus = 6;
            } else {
                $proveStatus = 2; // ค่าเดิมจากโค้ดเก่า
            }

            if (in_array('QC', $departments) || in_array('Sales', $departments)) {
                $proveStatus2 = 6;
            } else {
                $proveStatus2 = 4;
            }
            $proveStatus3 = 8;
        } elseif (in_array($levelApprover, ['manager', 'manager2', 'assisManager'])) {
            $proveStatus = 6;
            if (in_array('Sales', $departments) || in_array('QC', $departments)) {
                $proveStatus2 = 6;
            } else {
                $proveStatus2 = 4;
            }
            $proveStatus3 = 8;
        } elseif ($levelApprover === 'GM') {
            $proveStatus  = 6;
            $proveStatus2 = 6;
            $proveStatus3 = 8;
        } elseif ($levelApprover === 'admin') {
            $proveStatus  = 6;
            $proveStatus2 = 4;
            $proveStatus3 = 8;
        } else {
            // ค่าเริ่มต้นจากโค้ดเดิม ถ้าไม่ตรงกับเงื่อนไขข้างบน
            $proveStatus  = 2;
            $proveStatus2 = 4;
            $proveStatus3 = 8;
        }

        // แสดงข้อมูลการอนุมัติที่กำหนด
        echo "ระดับผู้ใช้: " . $levelApprover . "<br>";
        echo "แผนกที่สังกัด: " . implode(", ", array_filter($departments)) . "<br>";
        echo "สถานะการอนุมัติ: proveStatus=" . $proveStatus . ", proveStatus2=" . $proveStatus2 . ", proveStatus3=" . $proveStatus3 . "<br>";
    } else {
        echo "ไม่พบข้อมูลพนักงาน<br>";
        // ถ้าไม่พบข้อมูลพนักงาน ใช้ค่าเริ่มต้น
        $proveStatus  = 2;
        $proveStatus2 = 4;
        $proveStatus3 = 8;
    }

    // สถานะใบลา
    $leaveStatus     = 0;
    $leaveStatusName = ($leaveStatus == 0) ? 'ปกติ' : 'ยกเลิก';

    $filename = null;
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $filename      = $_FILES['file']['name'];
        $location      = "../upload/" . $filename;
        $imageFileType = strtolower(pathinfo($location, PATHINFO_EXTENSION));

        $valid_extensions = ["jpg", "jpeg", "png"];
        if (in_array($imageFileType, $valid_extensions)) {
            if (move_uploaded_file($_FILES['file']['tmp_name'], $location)) {
                $response = $location;
            } else {
                echo "ไม่สามารถบันทึกไฟล์ได้<br>";
            }
        } else {
            echo "ประเภทไฟล์ไม่ถูกต้อง<br>";
        }
    }

    try {
        $stmt = $conn->prepare("INSERT INTO leave_list (l_usercode, l_username, l_name, l_department, l_phone, l_leave_id, l_leave_reason,
        l_leave_start_date, l_leave_start_time, l_leave_end_date, l_leave_end_time,
        l_hr_create_datetime, l_file, l_leave_status, l_hr_status, l_approve_status,
        l_level, l_approve_status2, l_approve_status3, l_workplace, l_hr_create_name, l_time_remark, l_time_remark2, l_remark, l_create_datetime)
        VALUES (:userCode, :userName, :name, :depart, :telPhone, :leaveType, :leaveReason, :leaveDateStart, :leaveTimeStart,
        :leaveDateEnd, :leaveTimeEnd, :createDateByHR, :filename, :leaveStatus,
        :comfirmStatus, :proveStatus, :level, :proveStatus2, :proveStatus3, :workplace, :addUserName, :timeRemark, :timeRemark2, :remark, :createDate)");

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
        $stmt->bindParam(':createDateByHR', $createDateByHR);
        $stmt->bindParam(':filename', $filename);
        $stmt->bindParam(':leaveStatus', $leaveStatus);
        $stmt->bindParam(':comfirmStatus', $comfirmStatus);
        $stmt->bindParam(':proveStatus', $proveStatus);
        $stmt->bindParam(':proveStatus2', $proveStatus2);
        $stmt->bindParam(':proveStatus3', $proveStatus3);
        $stmt->bindParam(':level', $level);
        $stmt->bindParam(':workplace', $workplace);
        $stmt->bindParam(':addUserName', $addUserName);
        $stmt->bindParam(':timeRemark', $timeRemark);
        $stmt->bindParam(':timeRemark2', $timeRemark2);
        $stmt->bindParam(':remark', $remark);
        $stmt->bindParam(':createDate', $createDate);

        if ($stmt->execute()) {
            echo 'บันทึกข้อมูลสำเร็จ';
        } else {
            echo 'เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' . print_r($stmt->errorInfo(), true);
        }
    } catch (PDOException $e) {
        echo 'เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' . $e->getMessage();
    }

    $conn = null;
}