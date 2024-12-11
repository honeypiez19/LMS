<?php
include '../connect.php';

// รับค่าตัวแปรจาก POST
$userCode = $_POST['userCode'] ?? '';
$leaveType = $_POST['leaveType'] ?? ''; // ประเภทการลา
$selectedYear = $_POST['selectedYear'] ?? ''; // ปีที่ต้องการดึงข้อมูล

if (empty($userCode) || empty($leaveType) || empty($selectedYear)) {
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

// SQL สำหรับดึงข้อมูลการลา
$sql_leave = "SELECT
    SUM(
        DATEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))
        - (SELECT COUNT(1)
           FROM holiday
           WHERE h_start_date BETWEEN l_leave_start_date AND l_leave_end_date
             AND h_holiday_status = 'วันหยุด'
             AND h_status = 0)
    ) AS total_leave_days,
    SUM(
        HOUR(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))) % 24
    ) -
    SUM(
        CASE
            WHEN HOUR(CONCAT(l_leave_start_date, ' ', l_leave_start_time)) < 12
                 AND HOUR(CONCAT(l_leave_end_date, ' ', l_leave_end_time)) > 12
            THEN 1
            ELSE 0
        END
    ) AS total_leave_hours,
    SUM(
        MINUTE(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time)))
    ) AS total_leave_minutes,
    (SELECT e_leave_personal FROM employees WHERE e_usercode = :userCode) AS total_personal,
    (SELECT e_leave_personal_no FROM employees WHERE e_usercode = :userCode) AS total_personal_no,
    (SELECT e_leave_sick FROM employees WHERE e_usercode = :userCode) AS total_sick,
    (SELECT e_leave_sick_work FROM employees WHERE e_usercode = :userCode) AS total_sick_work,
    (SELECT e_leave_annual FROM employees WHERE e_usercode = :userCode) AS total_annual,
    (SELECT e_other FROM employees WHERE e_usercode = :userCode) AS total_other,
    (SELECT COUNT(l_list_id) FROM leave_list WHERE l_leave_id = 7 AND l_usercode = :userCode) AS late_count
FROM leave_list
JOIN employees ON employees.e_usercode = leave_list.l_usercode
WHERE l_leave_id = :leaveType
  AND l_usercode = :userCode
  AND YEAR(l_leave_end_date) = :selectedYear
  AND l_leave_status = 0
  AND l_approve_status IN (2,6)
  AND l_approve_status2 = 4";

// เตรียม query
$stmt_leave = $conn->prepare($sql_leave);
$stmt_leave->bindParam(':leaveType', $leaveType, PDO::PARAM_INT);
$stmt_leave->bindParam(':userCode', $userCode);
$stmt_leave->bindParam(':selectedYear', $selectedYear, PDO::PARAM_INT);
$stmt_leave->execute();

// ดึงข้อมูล
$result_leave = $stmt_leave->fetch(PDO::FETCH_ASSOC);

if ($result_leave) {
    $days = $result_leave['total_leave_days'] ?? 0; // จำนวนวันที่ใช้ลาไปแล้ว
    $hours = $result_leave['total_leave_hours'] ?? 0; // จำนวนชั่วโมงที่ใช้ลาไปแล้ว
    $minutes = $result_leave['total_leave_minutes'] ?? 0; // จำนวนที่ใช้ลาไปแล้วเป็นนาที

    // Employee leave balances
    $total_personal = $result_leave['total_personal'] ?? 0; // จำนวนวันลาทั้งหมด
    $total_personal_no = $result_leave['total_personal_no'] ?? 0; // วันลาแบบไม่ระบุเหตุผล
    $total_sick = $result_leave['total_sick'] ?? 0; // วันลาป่วย
    $total_sick_work = $result_leave['total_sick_work'] ?? 0; // วันลาป่วยจากงาน
    $total_annual = $result_leave['total_annual'] ?? 0; // วันลาประจำปี
    $total_other = $result_leave['total_other'] ?? 0; // วันลาประเภทอื่น
    $total_late = $result_leave['late_count'] ?? 0; // จำนวนวันที่มาสาย

    // คำนวณหาจำนวนวันคงเหลือจากจำนวนวันลา (ทำงาน 1 วัน 8 ชั่วโมง)
    $total_hours_available = $total_personal * 8;  // แปลงจำนวนวันทั้งหมดเป็นชั่วโมง (1 วัน = 8 ชั่วโมง)
    $total_hours_used = $days * 8 + $hours + floor($minutes / 60);  // คำนวณจำนวนชั่วโมงที่ใช้ไปแล้ว
    $remaining_hours = $total_hours_available - $total_hours_used;  // คำนวณชั่วโมงที่เหลือ

    // คำนวณวันคงเหลือจากชั่วโมงที่เหลือ
    $remaining_days = floor($remaining_hours / 8);  // แปลงชั่วโมงที่เหลือเป็นวัน
    $remaining_hours = $remaining_hours % 8;  // คำนวณชั่วโมงที่เหลือหลังจากแปลงเป็นวัน

    // คำนวณนาทีคงเหลือ
    $remaining_minutes = $remaining_hours * 60 - $minutes;  // คำนวณนาทีที่เหลือจากชั่วโมงที่เหลือ

    if ($remaining_minutes < 0) {
        $remaining_minutes = 0;
    }

    // ผลลัพธ์ที่จะแสดง
    if ($leaveType == 1) {
        echo json_encode([
            'remaining_days' => $remaining_days,
            'remaining_hours' => $remaining_hours,
            'remaining_minutes' => $remaining_minutes,
        ]);
    }
}
