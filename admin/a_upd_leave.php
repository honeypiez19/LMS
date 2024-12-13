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
// $userName = $_POST['userName'];
// $name = $_POST['name'];
$workplace = $_POST['workplace'];
$depart = $_POST['depart'];
// $subDepart = $_POST['subDepart'];

// 08:45
if ($editLeaveStartTime == '08:45') {
    $editLeaveStartTimeLine = '08:45';
    $editLeaveStartTime = '09:00';
    $remark = '08:45:00';
}
// 09:45
else if ($editLeaveStartTime == '09:45') {
    $editLeaveStartTimeLine = '09:45';
    $editLeaveStartTime = '10:00';
    $remark = '09:45:00';
}
// 10:45
else if ($editLeaveStartTime == '10:45') {
    $editLeaveStartTimeLine = '10:45';
    $editLeaveStartTime = '11:00';
    $remark = '10:45:00';
}
// 11:45
else if ($editLeaveStartTime == '12:00') {
    $editLeaveStartTimeLine = '11:45';
}
// 12:45
else if ($editLeaveStartTime == '13:00') {
    $editLeaveStartTimeLine = '12:45';
}
// 13:15
else if ($editLeaveStartTime == '13:30') {
    $editLeaveStartTimeLine = '13:15';
    $editLeaveStartTime = '13:30';
    $remark = '13:15:00';
}
// 13:45
else if ($editLeaveStartTime == '14:00') {
    $editLeaveStartTimeLine = '13:45';
    $editLeaveStartTime = '14:00';
    $remark = '13:45:00';
}
// 14:15
else if ($editLeaveStartTime == '14:30') {
    $editLeaveStartTimeLine = '14:15';
    $editLeaveStartTime = '14:30';
    $remark = '14:15:00';
}
// 14:45
else if ($editLeaveStartTime == '15:00') {
    $editLeaveStartTimeLine = '14:45';
    $editLeaveStartTime = '15:00';
    $remark = '14:45:00';
}
// 15:15
else if ($editLeaveStartTime == '15:30') {
    $editLeaveStartTimeLine = '15:15';
    $editLeaveStartTime = '15:30';
    $remark = '15:15:00';
}
// 15:45
else if ($editLeaveStartTime == '16:00') {
    $editLeaveStartTimeLine = '15:45';
    $editLeaveStartTime = '16:00';
    $remark = '15:45:00';
}
// 16:15
else if ($editLeaveStartTime == '16:30') {
    $editLeaveStartTimeLine = '16:10';
    $editLeaveStartTime = '16:30';
    $remark = '16:10:00';
}
// 16:40
else if ($editLeaveStartTime == '17:00') {
    $editLeaveStartTimeLine = '16:40';
} else {
    $editLeaveStartTimeLine = $editLeaveStartTime;
}

// 08:45
if ($editLeaveEndTime == '08:45') {
    $editLeaveEndTimeLine = '08:45';
    $editLeaveEndTime = '09:00';
    $remark = '08:45:00';
}
// 09:45
else if ($editLeaveEndTime == '09:45') {
    $editLeaveEndTimeLine = '09:45';
    $editLeaveEndTime = '10:00';
    $remark = '09:45:00';
}
// 10:45
else if ($editLeaveEndTime == '10:45') {
    $editLeaveEndTimeLine = '10:45';
    $editLeaveEndTime = '11:00';
    $remark = '10:45:00';
}
// 11:45
else if ($editLeaveEndTime == '12:00') {
    $editLeaveEndTimeLine = '11:45';
}
// 12:45
else if ($editLeaveEndTime == '13:00') {
    $editLeaveEndTimeLine = '12:45';
}
// 13:15
else if ($editLeaveEndTime == '13:30') {
    $editLeaveEndTimeLine = '13:15';
    $editLeaveEndTime = '13:30';
    $remark = '13:15:00';
}
// 13:45
else if ($editLeaveEndTime == '14:00') {
    $editLeaveEndTimeLine = '13:45';
    $editLeaveEndTime = '14:00';
    $remark = '13:45:00';
}
// 14:15
else if ($editLeaveEndTime == '14:30') {
    $editLeaveEndTimeLine = '14:15';
    $editLeaveEndTime = '14:30';
    $remark = '14:15:00';
}
// 14:45
else if ($editLeaveEndTime == '15:00') {
    $editLeaveEndTimeLine = '14:45';
    $editLeaveEndTime = '15:00';
    $remark = '14:45:00';
}
// 15:15
else if ($editLeaveEndTime == '15:30') {
    $editLeaveEndTimeLine = '15:15';
    $editLeaveEndTime = '15:30';
    $remark = '15:15:00';
}
// 15:45
else if ($editLeaveEndTime == '16:00') {
    $editLeaveEndTimeLine = '15:45';
    $editLeaveEndTime = '16:00';
    $remark = '15:45:00';
}
// 16:15
else if ($editLeaveEndTime == '16:30') {
    $editLeaveEndTimeLine = '16:15';
    $editLeaveEndTime = '16:30';
    $remark = '16:15:00';
}
// 16:40
else if ($editLeaveEndTime == '17:00') {
    $editLeaveEndTimeLine = '16:40';
} else {
    $editLeaveEndTimeLine = $editLeaveEndTime;
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

$sql = "UPDATE leave_list
        SET l_leave_id = :editLeaveType,
            l_leave_reason = :editLeaveReason,
            l_leave_start_date = :editLeaveStartDate,
            l_leave_start_time = :editLeaveStartTime,
            l_leave_end_date = :editLeaveEndDate,
            l_leave_end_time = :editLeaveEndTime,
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

    $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE e_workplace = :workplace AND e_usercode = :userCode");

    $stmt->bindParam(':workplace', $workplace);
    $stmt->bindParam(':userCode', $userCode);

    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // ถ้ามีผลลัพธ์ของการค้นหาก็ส่ง Line Notify
    if ($result) {
        $token = $result['e_token'];

        // การส่ง LINE Notify
        $url = "https://notify-api.line.me/api/notify";
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