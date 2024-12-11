<?php
// เชื่อมต่อฐานข้อมูล
include '../connect.php';

// รับข้อมูลจาก POST
$createDatetime = $_POST['createDatetime'] ?? null;
$editLeaveType = $_POST['editLeaveType'] ?? null;
$editLeaveReason = $_POST['editLeaveReason'] ?? null;
$editLeaveStartDate = $_POST['editLeaveStartDate'] ?? null;
$editLeaveStartTime = $_POST['editLeaveStartTime'] ?? null;
$editLeaveEndDate = $_POST['editLeaveEndDate'] ?? null;
$editLeaveEndTime = $_POST['editLeaveEndTime'] ?? null;
$editTelPhone = $_POST['editTelPhone'] ?? null;

$userCode = $_POST['userCode'];
$userName = $_POST['userName'];
$name = $_POST['name'];
$workplace = $_POST['workplace'];
$depart = $_POST['depart'];
$subDepart = $_POST['subDepart'];

if ($editLeaveStartTime == '08:45') {
    $editLeaveStartTimeLine = '08:45';
    $editLeaveStartTime = '09:00';
    $remark = '08:45:00';
} else if ($editLeaveStartTime == '09:45') {
    $editLeaveStartTimeLine = '09:45';
    $editLeaveStartTime = '10:00';
    $remark = '09:45:00';
} else if ($editLeaveStartTime == '10:45') {
    $editLeaveStartTimeLine = '10:45';
    $editLeaveStartTime = '11:00';
    $remark = '10:45:00';
} else if ($editLeaveStartTime == '12:00') {
    $editLeaveStartTimeLine = '11:45';
    $remark = '11:45:00';
} else if ($editLeaveStartTime == '13:00') {
    $editLeaveStartTimeLine = '12:45';
    $remark = '12:45:00';
} else if ($editLeaveStartTime == '13:10') {
    $editLeaveStartTimeLine = '13:10';
    $editLeaveStartTime = '13:30';
    $remark = '13:10:00';
} else if ($editLeaveStartTime == '13:40') {
    $editLeaveStartTimeLine = '13:40';
    $editLeaveStartTime = '14:00';
    $remark = '13:40:00';
} else if ($editLeaveStartTime == '13:45') {
    $editLeaveStartTimeLine = '13:45';
    $editLeaveStartTime = '14:00';
    $remark = '13:45:00';
} else if ($editLeaveStartTime == '14:10') {
    $editLeaveStartTimeLine = '14:10';
    $editLeaveStartTime = '14:30';
    $remark = '14:10:00';
} else if ($editLeaveStartTime == '14:40') {
    $editLeaveStartTimeLine = '14:40';
    $editLeaveStartTime = '15:00';
    $remark = '14:40:00';
} else if ($editLeaveStartTime == '14:45') {
    $editLeaveStartTimeLine = '14:45';
    $editLeaveStartTime = '15:00';
    $remark = '14:45:00';
} else if ($editLeaveStartTime == '15:10') {
    $editLeaveStartTimeLine = '15:10';
    $editLeaveStartTime = '15:30';
    $remark = '15:10:00';
} else if ($editLeaveStartTime == '15:40') {
    $editLeaveStartTimeLine = '15:40';
    $editLeaveStartTime = '16:00';
    $remark = '15:40:00';
} else if ($editLeaveStartTime == '15:45') {
    $editLeaveStartTimeLine = '15:45';
    $editLeaveStartTime = '16:00';
    $remark = '15:45:00';
} else if ($editLeaveStartTime == '16:10') {
    $editLeaveStartTimeLine = '16:10';
    $editLeaveStartTime = '16:30';
    $remark = '16:10:00';
} else if ($editLeaveStartTime == '17:00') {
    $editLeaveStartTimeLine = '16:40';
} else {
    $editLeaveStartTimeLine = $editLeaveStartTime;
    $remark = isset($remark) ? $remark : ''; // ตรวจสอบค่า remark หากไม่มีค่าให้เป็นค่าว่าง
}

// เวลาที่สิ้นสุด
if ($editLeaveEndTime == '08:45') {
    $editLeaveEndTimeLine = '08:45';
    $editLeaveEndTime = '09:00';
    $remark = '08:45:00';
} else if ($editLeaveEndTime == '09:45') {
    $editLeaveEndTimeLine = '09:45';
    $editLeaveEndTime = '10:00';
    $remark = '09:45:00';
} else if ($editLeaveEndTime == '10:45') {
    $editLeaveEndTimeLine = '10:45';
    $editLeaveEndTime = '11:00';
    $remark = '10:45:00';
} else if ($editLeaveEndTime == '12:00') {
    $editLeaveEndTimeLine = '11:45';
    $remark = '11:45:00';
} else if ($editLeaveEndTime == '13:00') {
    $editLeaveEndTimeLine = '12:45';
    $remark = '12:45:00';
} else if ($editLeaveEndTime == '13:10') {
    $editLeaveEndTimeLine = '13:10';
    $editLeaveEndTime = '13:30';
    $remark = '13:10:00';
} else if ($editLeaveEndTime == '13:40') {
    $editLeaveEndTimeLine = '13:40';
    $editLeaveEndTime = '14:00';
    $remark = '13:40:00';
} else if ($editLeaveEndTime == '13:45') {
    $editLeaveEndTimeLine = '13:45';
    $editLeaveEndTime = '14:00';
    $remark = '13:45:00';
} else if ($editLeaveEndTime == '14:10') {
    $editLeaveEndTimeLine = '14:10';
    $editLeaveEndTime = '14:30';
    $remark = '14:10:00';
} else if ($editLeaveEndTime == '14:40') {
    $editLeaveEndTimeLine = '14:40';
    $editLeaveEndTime = '15:00';
    $remark = '14:40:00';
} else if ($editLeaveEndTime == '14:45') {
    $editLeaveEndTimeLine = '14:45';
    $editLeaveEndTime = '15:00';
    $remark = '14:45:00';
} else if ($editLeaveEndTime == '15:10') {
    $editLeaveEndTimeLine = '15:10';
    $editLeaveEndTime = '15:30';
    $remark = '15:10:00';
} else if ($editLeaveEndTime == '15:40') {
    $editLeaveEndTimeLine = '15:40';
    $editLeaveEndTime = '16:00';
    $remark = '15:40:00';
} else if ($editLeaveEndTime == '15:45') {
    $editLeaveEndTimeLine = '15:45';
    $editLeaveEndTime = '16:00';
    $remark = '15:45:00';
} else if ($editLeaveEndTime == '16:10') {
    $editLeaveEndTimeLine = '16:10';
    $editLeaveEndTime = '16:30';
    $remark = '16:10:00';
} else if ($editLeaveEndTime == '17:00') {
    $editLeaveEndTimeLine = '16:40';
    $remark = '16:40:00';
} else {
    $editLeaveStartTimeLine = $editLeaveEndTime;
    $remark = isset($remark) ? $remark : ''; // ตรวจสอบค่า remark หากไม่มีค่าให้เป็นค่าว่าง
}

// จัดการอัปโหลดไฟล์
$filename = null;
if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    // ถ้ามีการอัปโหลดไฟล์ใหม่
    $tempName = $_FILES['file']['tmp_name'];
    $extension = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

    $validExtensions = ["jpg", "jpeg", "png"];
    if (in_array($extension, $validExtensions)) {
        $filename = uniqid() . '.' . $extension;
        $uploadPath = "../upload/" . $filename;

        if (!move_uploaded_file($tempName, $uploadPath)) {
            echo json_encode(['status' => 'error', 'message' => 'อัปโหลดไฟล์ไม่สำเร็จ']);
            exit;
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'ชนิดไฟล์ไม่รองรับ']);
        exit;
    }
} else {
    // ถ้าไม่มีการอัปโหลดไฟล์ใหม่ ใช้ไฟล์เดิม
    $filename = isset($_POST['currentFile']) ? $_POST['currentFile'] : null;
}

if ($editTelPhone) {
    // อัปเดตเบอร์โทรศัพท์ในตาราง employees
    $updateEmployeeSql = "UPDATE employees SET e_phone = :editTelPhone WHERE e_usercode = :userCode";

    $updateEmployeeStmt = $conn->prepare($updateEmployeeSql);
    $updateEmployeeStmt->bindParam(':editTelPhone', $editTelPhone);
    $updateEmployeeStmt->bindParam(':userCode', $userCode);

    // Execute the update
    if (!$updateEmployeeStmt->execute()) {
        echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถอัปเดตเบอร์โทรศัพท์ในตาราง employees']);
        exit;
    }
}
// Office
if ($depart == 'Office') {
    if ($subDepart == 'Store' || $subDepart == 'AC') {
        $proveStatus = 2;
        $proveStatus2 = 1;
        $proveStatus3 = 7;
    } else if ($subDepart == '' || $subDepart == 'All') {
        $proveStatus = 6;
        $proveStatus2 = 1;
        $proveStatus3 = 7;
    } else if ($subDepart == 'Sales') {
        $proveStatus = 2;
        $proveStatus2 = 6;
        $proveStatus3 = 7;
    } else {
        echo 'ไม่พบแผนก';
    }
}
// CAD1
else if ($depart == 'CAD1') {
    if ($subDepart == 'Design' || $subDepart == 'Modeling') {
        $proveStatus = 2;
        $proveStatus2 = 1;
        $proveStatus3 = 7;
    } else if ($subDepart == 'CAD1') {
        $proveStatus = 2;
        $proveStatus2 = 1;
        $proveStatus3 = 7;
    } else {
        echo 'ไม่พบแผนก';
    }
}
// CAD2
else if ($depart == 'CAD2') {
    if ($subDepart == 'CAD2') {
        $proveStatus = 2;
        $proveStatus2 = 1;
        $proveStatus3 = 7;
    } else {
        echo 'ไม่พบแผนก';
    }
}
// CAM
else if ($depart == 'CAM') {
    if ($subDepart == 'CAM') {
        $proveStatus = 2;
        $proveStatus2 = 1;
        $proveStatus3 = 7;
    } else {
        echo 'ไม่พบแผนก';
    }
} else {
    $proveStatus = 2;
    $proveStatus2 = 1;
    $proveStatus3 = 7;
}

$sql = "UPDATE leave_list
        SET l_leave_id = :editLeaveType,
            l_leave_reason = :editLeaveReason,
            l_leave_start_date = :editLeaveStartDate,
            l_leave_start_time = :editLeaveStartTime,
            l_leave_end_date = :editLeaveEndDate,
            l_leave_end_time = :editLeaveEndTime,
            l_approve_status = :proveStatus,
            l_approve_status2 = 1,
            l_phone = :editTelPhone,
            l_remark = :remark";

// ตรวจสอบว่า $filename มีค่า (หมายความว่าไฟล์ใหม่ถูกอัปโหลด) แล้วอัปเดตข้อมูลไฟล์
if ($filename) {
    $sql .= ", l_file = :filename";
}

$sql .= " WHERE l_create_datetime = :createDatetime";

$stmt = $conn->prepare($sql);

$stmt->bindParam(':editLeaveType', $editLeaveType);
$stmt->bindParam(':editLeaveReason', $editLeaveReason);
$stmt->bindParam(':editLeaveStartDate', $editLeaveStartDate);
$stmt->bindParam(':editLeaveStartTime', $editLeaveStartTime);
$stmt->bindParam(':editLeaveEndDate', $editLeaveEndDate);
$stmt->bindParam(':editLeaveEndTime', $editLeaveEndTime);
$stmt->bindParam(':remark', $remark);
$stmt->bindParam(':editTelPhone', $editTelPhone);
$stmt->bindParam(':proveStatus', $proveStatus);
$stmt->bindParam(':createDatetime', $createDatetime);

// ตรวจสอบว่าไฟล์ถูกอัปโหลดก่อนที่จะ bind ค่า $filename
if ($filename) {
    $stmt->bindParam(':filename', $filename);
}

$stmt->bindParam(':createDatetime', $createDatetime);

// Execute the query
if ($stmt->execute()) {
    // ส่งการแจ้งเตือน LINE
    $URL = 'https://lms.system-samt.com/';
    $message = "มีการแก้ไขใบลา $name\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด : $URL";

    // ตรวจสอบเงื่อนไขตามแผนก
    if ($depart == 'RD') {
        $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE e_workplace = :workplace AND e_level = 'leader' AND e_sub_department = 'RD'");
    } elseif ($depart == 'Office' || $depart == 'Management') {
        $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE e_workplace = :workplace AND e_level = 'manager' AND e_sub_department = 'Office'");
    } elseif ($depart == 'CAD1') {
        $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE e_workplace = :workplace AND e_level = 'assisManager' AND e_sub_department = 'CAD1'");
    } elseif ($depart == 'CAD2') {
        $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE e_workplace = :workplace AND e_level = 'assisManager' AND e_sub_department2 = 'CAD2'");
    } elseif ($depart == 'CAM') {
        $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE e_workplace = :workplace AND e_level = 'assisManager' AND e_sub_department3 = 'CAM'");
    } else {
        echo "ไม่พบเงื่อนไข";
        exit;
    }

    // Bind และ Execute
    $stmt->bindParam(':workplace', $workplace);
    $stmt->execute();

    // ตรวจสอบผลลัพธ์
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        $token = $result['e_token'];

        // การส่ง LINE Notify
        $url = "https://notify-api.line.me/api/notify";
        $data = ['message' => $message];
        $headers = ['Authorization: Bearer ' . $token];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // ส่งคำขอ
        $notifyResult = curl_exec($ch);
        curl_close($ch);

        // ส่งผลลัพธ์การอัปเดตข้อมูล
        echo json_encode(['status' => 'success', 'message' => 'อัปเดตข้อมูลสำเร็จ']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'ไม่พบผู้รับการแจ้งเตือน']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาดในการอัปเดตข้อมูล']);
}