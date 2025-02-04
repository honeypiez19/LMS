<?php
// เชื่อมต่อฐานข้อมูล
include '../connect.php';

// รับข้อมูลจาก POST
$createDatetime     = $_POST['createDatetime'] ?? null;
$editLeaveType      = $_POST['editLeaveType'] ?? null;
$editLeaveReason    = $_POST['editLeaveReason'] ?? null;
$editLeaveStartDate = $_POST['editLeaveStartDate'] ?? null;
$editLeaveStartTime = $_POST['editLeaveStartTime'] ?? null;
$editLeaveEndDate   = $_POST['editLeaveEndDate'] ?? null;
$editLeaveEndTime   = $_POST['editLeaveEndTime'] ?? null;
$editTelPhone       = $_POST['editTelPhone'] ?? null;

$userCode  = $_POST['userCode'];
$userName  = $_POST['userName'];
$name      = $_POST['name'];
$workplace = $_POST['workplace'];
$depart    = $_POST['depart'];
$subDepart = $_POST['subDepart'];

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

if (isset($timeMappings[$editLeaveStartTime])) {
    [$editLeaveStartTimeLine, $editLeaveStartTime, $timeRemark] = $timeMappings[$editLeaveStartTime];
} else {
    $editLeaveStartTimeLine = $editLeaveStartTime;
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

if (isset($editLeaveEndTime, $timeMappings2)) {
    list($editLeaveEndTimeLine, $editLeaveEndTime, $timeRemark2) = $timeMappings2[$editLeaveEndTime];
} else {
    $editLeaveEndTimeLine = $editLeaveEndTime;
}

// จัดการอัปโหลดไฟล์
$filename = null;
if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    // ถ้ามีการอัปโหลดไฟล์ใหม่
    $tempName  = $_FILES['file']['tmp_name'];
    $extension = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

    $validExtensions = ["jpg", "jpeg", "png"];
    if (in_array($extension, $validExtensions)) {
        $filename   = uniqid() . '.' . $extension;
        $uploadPath = "../upload/" . $filename;

        if (! move_uploaded_file($tempName, $uploadPath)) {
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
    if (! $updateEmployeeStmt->execute()) {
        echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถอัปเดตเบอร์โทรศัพท์ในตาราง employees']);
        exit;
    }
}

$sql = "UPDATE leave_list
        SET l_leave_id = :editLeaveType,
            l_leave_reason = :editLeaveReason,
            l_leave_start_date = :editLeaveStartDate,
            l_leave_start_time = :editLeaveStartTime,
            l_leave_end_date = :editLeaveEndDate,
            l_leave_end_time = :editLeaveEndTime,
            l_phone = :editTelPhone,
            l_hr_status = 0,
            l_time_remark = :timeRemark,
            l_time_remark2 = :timeRemark2";

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
// $stmt->bindParam(':remark', $remark);
$stmt->bindParam(':editTelPhone', $editTelPhone);
// $stmt->bindParam(':proveStatus', $proveStatus);
$stmt->bindParam(':createDatetime', $createDatetime);
$stmt->bindParam(':timeRemark', $timeRemark);
$stmt->bindParam(':timeRemark2', $timeRemark2);

if ($filename) {
    $stmt->bindParam(':filename', $filename);
}

$stmt->bindParam(':createDatetime', $createDatetime);

// Execute the query
if ($stmt->execute()) {
    // ส่งการแจ้งเตือน LINE
    $URL     = 'https://lms.system-samt.com/';
    $message = "มีการแก้ไขใบลา $name\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด : $URL";

    if ($depart == 'RD') {
        $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE e_workplace = :workplace AND e_level = 'leader' AND e_sub_department = 'RD'");

    } else if ($depart == 'Office') {
        // บัญชี
        if ($subDepart == 'AC') {
            // แจ้งเตือนพี่แวว
            $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'chief' AND e_sub_department = 'AC'");
        }
        // เซลล์
        else if ($subDepart == 'Sales') {
            // แจ้งเตือนพี่เจี๊ยบหรือพี่อ้อม
            $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE e_workplace = :workplace AND e_level = 'chief' AND e_sub_department = 'Sales'");
        }
        // สโตร์
        else if ($subDepart == 'Store') {
            // แจ้งเตือนพี่เก๋
            $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'leader' AND e_sub_department = 'Store'");
        }
        // HR
        else if ($subDepart == 'All') {
            $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'manager' AND e_sub_department = 'Office'");
        }
        // พี่เต๋ / พี่น้อย / พี่ไว
        else if ($subDepart == '') {
            $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'manager' AND e_sub_department = 'Office'");
        }
    } else if ($depart == 'CAD1') {
        if ($subDepart == 'Modeling') {
            $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'leader' AND e_sub_department = 'Modeling'");
        } else if ($subDepart == 'Design') {
            $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'leader' AND e_sub_department = 'Design'");
        } else {
            $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'leader' AND e_sub_department = 'Design'");
        }
    } else if ($depart == 'CAD2') {
        $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'leader' AND e_sub_department = 'CAD2'");
    } else if ($depart == 'CAM') {
        $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'leader' AND e_sub_department = 'CAM'");
    } else {
        echo "ไม่พบเงื่อนไข";
    }

    $stmt->bindParam(':workplace', $workplace);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // ถ้ามีผลลัพธ์ของการค้นหาก็ส่ง Line Notify
    if ($result) {
        $token = $result['e_token'];

        // การส่ง LINE Notify
        $url  = "https://notify-api.line.me/api/notify";
        $data = [
            'message' => $message,
        ];
        $headers = [
            'Authorization: Bearer ' . $token,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); // เปลี่ยนเป็น http_build_query
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // ส่งคำขอ
        $result = curl_exec($ch);
        curl_close($ch);
    }

    // ส่งผลลัพธ์การอัปเดตข้อมูล
    echo json_encode(['status' => 'success', 'message' => 'อัปเดตข้อมูลสำเร็จ']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาดในการอัปเดตข้อมูล']);
}