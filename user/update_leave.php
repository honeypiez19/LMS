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

$workplace = $_POST['workplace'];

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
    $updateEmployeeSql = "UPDATE employees SET e_phone = :editTelPhone WHERE e_phone = :oldTelPhone";

    $updateEmployeeStmt = $conn->prepare($updateEmployeeSql);
    $updateEmployeeStmt->bindParam(':editTelPhone', $editTelPhone);
    $updateEmployeeStmt->bindParam(':oldTelPhone', $oldTelPhone); // ค่าของเบอร์โทรศัพท์เดิม (อาจจะรับมาจาก POST หรือ Query ก่อนหน้า)

    // Execute the update
    if (!$updateEmployeeStmt->execute()) {
        echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถอัปเดตเบอร์โทรศัพท์ในตาราง employees']);
        exit;
    }
}

// เตรียม SQL query
$sql = "UPDATE leave_list
        SET l_leave_id = :editLeaveType,
            l_leave_reason = :editLeaveReason,
            l_leave_start_date = :editLeaveStartDate,
            l_leave_start_time = :editLeaveStartTime,
            l_leave_end_date = :editLeaveEndDate,
            l_leave_end_time = :editLeaveEndTime,
            l_approve_status = 0,
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

// ตรวจสอบว่ามีไฟล์หรือไม่แล้ว bind ค่า filename
if ($filename) {
    $stmt->bindParam(':filename', $filename);
}

$stmt->bindParam(':createDatetime', $createDatetime);

// Execute the query
if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'อัปเดตข้อมูลสำเร็จ']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถอัปเดตข้อมูลได้']);
}