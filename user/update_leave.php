<?php
// เชื่อมต่อฐานข้อมูล
include '../connect.php';

$createDatetime = $_POST['createDatetime'];
$editLeaveType = $_POST['editLeaveType'];
$editLeaveReason = $_POST['editLeaveReason'];
$editleaveStartDate = $_POST['editLeaveStartDate'];
$editleaveStartTime = $_POST['editleaveStartTime'];

// $editLeaveEndDate = $_POST['editLeaveEndDate'];
// $editLeaveEndTime = $_POST['editLeaveEndTime'];

// 08:45
if ($editleaveStartTime == '08:45') {
    $editleaveStartTimeLine = '08:45';
    $editleaveStartTime = '09:00';
    $remark = '08:45:00';
}
// 09:45
else if ($editleaveStartTime == '09:45') {
    $editleaveStartTimeLine = '09:45';
    $editleaveStartTime = '10:00';
    $remark = '09:45:00';
}
// 10:45
else if ($editleaveStartTime == '10:45') {
    $leaveTimeStartLine = '10:45';
    $editleaveStartTime = '11:00';
    $remark = '10:45:00';
}
// 11:45
else if ($editleaveStartTime == '12:00') {
    $editleaveStartTimeLine = '11:45';
}
// 12:45
else if ($editleaveStartTime == '13:00') {
    $editleaveStartTimeLine = '12:45';
}
// 13:10
else if ($editleaveStartTime == '13:10') {
    $editleaveStartTimeLine = '13:10';
    $editleaveStartTime = '13:30';
    $remark = '13:10:00';
}
// 13:40
else if ($editleaveStartTime == '13:40') {
    $editleaveStartTimeLine = '13:40';
    $editleaveStartTime = '14:00';
    $remark = '13:40:00';
}
// 13:45
else if ($editleaveStartTime == '13:45') {
    $editleaveStartTimeLine = '13:45';
    $editleaveStartTime = '14:00';
    $remark = '13:45:00';
}
// 14:10
else if ($editleaveStartTime == '14:10') {
    $editleaveStartTimeLine = '14:10';
    $editleaveStartTime = '14:30';
    $remark = '14:10:00';
}
// 14:40
else if ($editleaveStartTime == '14:40') {
    $editleaveStartTimeLine = '14:40';
    $editleaveStartTime = '15:00';
    $remark = '14:40:00';
}
// 14:45
else if ($editleaveStartTime == '14:45') {
    $editleaveStartTimeLine = '14:45';
    $editleaveStartTime = '15:00';
    $remark = '14:45:00';
}
// 15:10
else if ($editleaveStartTime == '15:10') {
    $editleaveStartTimeLine = '15:10';
    $editleaveStartTime = '15:30';
    $remark = '15:10:00';
}
// 15:40
else if ($editleaveStartTime == '15:40') {
    $editleaveStartTimeLine = '15:40';
    $editleaveStartTime = '16:00';
    $remark = '15:40:00';
}
// 15:45
else if ($editleaveStartTime == '15:45') {
    $editleaveStartTimeLine = '15:45';
    $editleaveStartTime = '16:00';
    $remark = '15:45:00';
}
// 16:10
else if ($editleaveStartTime == '16:10') {
    $editleaveStartTimeLine = '16:10';
    $editleaveStartTime = '16:30';
    $remark = '16:10:00';
}
// 16:40
else if ($editleaveStartTime == '17:00') {
    $editleaveStartTimeLine = '16:40';
} else {
    $editleaveStartTimeLine = $editleaveStartTime;
}

// เตรียม SQL query
$sql = "UPDATE leave_list
                SET l_leave_id = :editLeaveType,
                    l_leave_reason = :editLeaveReason,
                    l_leave_start_date = :editleaveStartDate,
                    l_leave_start_time = :editleaveStartTime,
                    l_approve_status = 0,
                    l_approve_status2 = 1,
                    l_remark = :remark
                WHERE l_create_datetime = :createDatetime";

// เตรียมคำสั่ง SQL
$stmt = $conn->prepare($sql);

// Binding the parameters
$stmt->bindParam(':editLeaveType', $editLeaveType);
$stmt->bindParam(':editLeaveReason', $editLeaveReason);
$stmt->bindParam(':editleaveStartDate', $editleaveStartDate);
$stmt->bindParam(':editleaveStartTime', $editleaveStartTime);
$stmt->bindParam(':createDatetime', $createDatetime);
$stmt->bindParam(':remark', $remark);

// Execute the query
if ($stmt->execute()) {
    // ตรวจสอบการอัปเดตสำเร็จ
    echo json_encode(['status' => 'success']);
} else {
    // ข้อผิดพลาดในการอัปเดตข้อมูล
    echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถอัปเดตข้อมูลได้']);
}