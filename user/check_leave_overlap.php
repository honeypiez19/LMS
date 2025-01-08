<?php
// เชื่อมต่อกับฐานข้อมูล
require '../connect.php';

// รับค่าจากฟอร์ม
// $userCode = $_POST['userCode'];
// $startDate = '08-01-2025';
// $startTime = $_POST['startTime'];
// $endDate = $_POST['endDate'];
// $endTime = $_POST['endTime'];

$userCode = '6608418';
$startDate = '08-01-2025';
$startTime = '08:00';
$endDate = '08-01-2025';
$endTime = '17:00';

// แปลงวันที่เป็นรูปแบบ yyyy-mm-dd
$startDate = DateTime::createFromFormat('d-m-Y', $startDate)->format('Y-m-d');
$endDate = DateTime::createFromFormat('d-m-Y', $endDate)->format('Y-m-d');

// echo $startDate;

// คิวรีตรวจสอบการลาซ้ำ
$query = "SELECT * FROM leave_list WHERE l_usercode = :userCode AND (
    (l_leave_start_date = :startDate AND l_leave_end_date = :endDate AND l_leave_end_time = :endTime)
    OR
    (l_leave_start_date = :startDate AND l_leave_end_date = :endDate AND l_leave_start_time = :startTime)
)";

$stmt = $conn->prepare($query);
$stmt->bindParam(':userCode', $userCode);
$stmt->bindParam(':startDate', $startDate);
$stmt->bindParam(':startTime', $startTime);
$stmt->bindParam(':endDate', $endDate);
$stmt->bindParam(':endTime', $endTime);

$stmt->execute();
$result = $stmt->fetchAll();

if (count($result) > 0) {
    echo 'overlap';
} else {
    echo 'no_overlap';
}