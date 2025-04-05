<?php
// เชื่อมต่อกับฐานข้อมูล
require '../connect.php';

// รับค่าจากฟอร์ม
$userCode = $_POST['userCode'];

$urgentStartDate = $_POST['urgentStartDate'];

$urgentStartTime = $_POST['urgentStartTime'];
$endDate         = $_POST['urgentEndDate'];
$urgentEndTime   = $_POST['urgentEndTime'];
$urgentLeaveType = $_POST['urgentLeaveType'];

// แปลงวันที่เป็นรูปแบบ yyyy-mm-dd
$startDate = DateTime::createFromFormat('d-m-Y', $urgentStartDate)->format('Y-m-d');
$endDate   = DateTime::createFromFormat('d-m-Y', $endDate)->format('Y-m-d');

// คิวรีตรวจสอบการลาซ้ำ
$query = "SELECT * FROM leave_list
          WHERE l_usercode = :userCode
          AND l_leave_id = :urgentLeaveType
          AND (
              (l_leave_start_date = :startDate AND l_leave_end_date = :endDate AND l_leave_end_time = :urgentEndTime)
              OR
              (l_leave_start_date = :endDate AND l_leave_end_date = :endDate AND l_leave_start_time = :urgentStartTime)
          )
        AND l_leave_status = 0";

$stmt = $conn->prepare($query);
$stmt->bindParam(':userCode', $userCode);
$stmt->bindParam(':startDate', $startDate);
$stmt->bindParam(':urgentStartTime', $urgentStartTime);
$stmt->bindParam(':endDate', $endDate);
$stmt->bindParam(':urgentEndTime', $urgentEndTime);
$stmt->bindParam(':urgentLeaveType', $urgentLeaveType); // ผูกค่าประเภทการลา

$stmt->execute();
$result = $stmt->fetchAll();

if (count($result) > 0) {
    echo 'double';
} else {
    echo 'no_double';
}