<?php
// เชื่อมต่อกับฐานข้อมูล
require '../connect.php';

// รับค่าจากฟอร์ม
$userCode        = $_POST['userCode'];
$urgentStartDate = $_POST['urgentStartDate'];
$urgentStartTime = $_POST['urgentStartTime'];
$endDate         = $_POST['urgentEndDate'];
$urgentEndTime   = $_POST['urgentEndTime'];
$urgentLeaveType = $_POST['urgentLeaveType'];

// รับค่า leaveCondition ถ้ามีการส่งมา (ในกรณีลาพักร้อนฉุกเฉิน)
$leaveCondition = isset($_POST['leaveCondition']) ? $_POST['leaveCondition'] : null;

// แปลงวันที่เป็นรูปแบบ yyyy-mm-dd
$startDate = DateTime::createFromFormat('d-m-Y', $urgentStartDate)->format('Y-m-d');
$endDate   = DateTime::createFromFormat('d-m-Y', $endDate)->format('Y-m-d');

// ตรวจสอบกรณีพิเศษสำหรับลาพักร้อนฉุกเฉินเงื่อนไข 4
if ($urgentLeaveType == '5' && $leaveCondition == '4') {
    // ตรวจสอบว่าเคยใช้สิทธิ์ลาพักร้อนฉุกเฉินเงื่อนไข 4 ไปแล้วหรือไม่
    $queryCheckCondition4 = "SELECT COUNT(*) as count_used
                            FROM leave_list
                            WHERE l_usercode = :userCode
                            AND l_leave_id = 5
                            AND l_leave_id2 = 4
                            AND l_leave_status = 0";

    $stmtCheck = $conn->prepare($queryCheckCondition4);
    $stmtCheck->bindParam(':userCode', $userCode);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if ($resultCheck['count_used'] > 0) {
        // ถ้าเคยใช้สิทธิ์แล้ว
        echo 'condition4_used';
        exit; // จบการทำงานเพื่อไม่ให้ตรวจสอบเงื่อนไขอื่นต่อ
    }
}

// สร้างคิวรีพื้นฐานเพื่อตรวจสอบการลาซ้ำ
$query = "SELECT * FROM leave_list
          WHERE l_usercode = :userCode
          AND l_leave_id = :urgentLeaveType
          AND (
              (l_leave_start_date = :startDate AND l_leave_end_date = :endDate AND l_leave_end_time = :urgentEndTime)
              OR
              (l_leave_start_date = :endDate AND l_leave_end_date = :endDate AND l_leave_start_time = :urgentStartTime)
          )
          AND l_leave_status = 0";

// เพิ่มเงื่อนไขตรวจสอบ l_leave_id2 ถ้าเป็นการลาพักร้อนฉุกเฉิน
if ($urgentLeaveType == '5' && $leaveCondition) {
    $query .= " AND l_leave_id2 = :leaveCondition";
}

$stmt = $conn->prepare($query);
$stmt->bindParam(':userCode', $userCode);
$stmt->bindParam(':startDate', $startDate);
$stmt->bindParam(':urgentStartTime', $urgentStartTime);
$stmt->bindParam(':endDate', $endDate);
$stmt->bindParam(':urgentEndTime', $urgentEndTime);
$stmt->bindParam(':urgentLeaveType', $urgentLeaveType);

// ผูกค่า leaveCondition ถ้ามีการเพิ่มเงื่อนไขในคิวรี
if ($urgentLeaveType == '5' && $leaveCondition) {
    $stmt->bindParam(':leaveCondition', $leaveCondition);
}

$stmt->execute();
$result = $stmt->fetchAll();

if (count($result) > 0) {
    echo 'double';
} else {
    echo 'no_double';
}
