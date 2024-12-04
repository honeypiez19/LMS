<?php
// เชื่อมต่อฐานข้อมูล
include '../connect.php';

$createDatetime = $_POST['createDatetime'];
$editLeaveType = $_POST['editLeaveType'];
$editLeaveReason = $_POST['editLeaveReason'];
$editleaveStartDate = $_POST['editleaveStartDate'];
$editleaveStartTime = $_POST['editleaveStartTime'];

// เตรียม SQL query
$sql = "UPDATE leave_list
                SET l_leave_id = :editLeaveType,
                    l_leave_reason = :editLeaveReason,
                    l_leave_start_date = :editleaveStartDate,
                    l_leave_start_time = :editleaveStartTime,
                    l_approve_status = 0,
                    l_approve_status2 = 1
                WHERE l_create_datetime = :createDatetime";

// เตรียมคำสั่ง SQL
$stmt = $conn->prepare($sql);

// Execute the query with the parameters
if ($stmt->execute([
    ':editLeaveType' => $editLeaveType,
    ':editLeaveReason' => $editLeaveReason,
    ':editleaveStartDate' => $editleaveStartDate,
    ':editleaveStartTime' => $editleaveStartTime,

    ':createDatetime' => $createDatetime,
])) {
    // ตรวจสอบการอัปเดตสำเร็จ
    echo json_encode(['status' => 'success']);
} else {
    // ข้อผิดพลาดในการอัปเดตข้อมูล
    echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถอัปเดตข้อมูลได้']);
}