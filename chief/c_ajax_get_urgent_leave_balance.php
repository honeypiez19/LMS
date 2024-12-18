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
    // จำนวนวันและเวลาในการใช้ลา
    $days_used = $result_leave['total_leave_days'] ?? 0; // วันที่ใช้ไป
    $hours_used = $result_leave['total_leave_hours'] ?? 0; // ชั่วโมงที่ใช้ไป
    $minutes_used = $result_leave['total_leave_minutes'] ?? 0; // นาทีที่ใช้ไป

    // จำนวนวันลาแบบต่างๆ
    $total_personal = $result_leave['total_personal'] ?? 0; // จำนวนวันลาโดยรวม
    $total_personal_no = $result_leave['total_personal_no'] ?? 0; // จำนวนวันลาแบบอื่น
    $total_sick = $result_leave['total_sick'] ?? 0;
    $total_sick_work = $result_leave['total_sick_work'] ?? 0;
    $total_annual = $result_leave['total_annual'] ?? 0;
    $total_other = $result_leave['total_other'] ?? 0;

    // คำนวณวันลาเต็มวันจากการใช้ชั่วโมง (1 วัน = 8 ชั่วโมง)
    $days_used += intdiv($hours_used, 8); // คำนวณวันจากชั่วโมง
    $hours_used = $hours_used % 8; // คงเหลือชั่วโมงที่ไม่ครบ 8

    // ปรับนาทีเป็นชั่วโมง ถ้านาทีมากกว่า 60 นาที
    $hours_used += intdiv($minutes_used, 60);
    $minutes_used = $minutes_used % 60;

    // ถ้าชั่วโมงเกิน 8 ชั่วโมง ให้ปรับเป็นวันเพิ่ม
    $days_used += intdiv($hours_used, 8);
    $hours_used = $hours_used % 8;

    // เช็คประเภทการลาและคำนวณวันคงเหลือ
    if ($leaveType == 1) {
        $total_days_allowed = $total_personal;
    } elseif ($leaveType == 2) {
        $total_days_allowed = $total_personal_no;
    } elseif ($leaveType == 3) {
        $total_days_allowed = $total_sick;
    } elseif ($leaveType == 4) {
        $total_days_allowed = $total_sick_work;
    } elseif ($leaveType == 5) {
        $total_days_allowed = $total_annual;
    } elseif ($leaveType == 8) {
        $total_days_allowed = $total_other;
    } else {
        $total_days_allowed = 0; // กรณีประเภทการลาไม่ตรง
    }

    $remaining_days = $total_days_allowed - $days_used;

    // คำนวณชั่วโมงและนาทีคงเหลือ
    $total_remaining_minutes = ($remaining_days * 8 * 60) - ($hours_used * 60 + $minutes_used);

    if ($total_remaining_minutes < 0) {
        $remaining_days = 0;
        $remaining_hours = 0;
        $remaining_minutes = 0;
    } else {
        $remaining_days = intdiv($total_remaining_minutes, (8 * 60)); // แปลงกลับเป็นวัน
        $remaining_hours = intdiv($total_remaining_minutes % (8 * 60), 60); // ชั่วโมงที่เหลือ
        $remaining_minutes = $total_remaining_minutes % 60; // นาทีที่เหลือ
    }

    // แสดงผลลัพธ์
    echo json_encode([
        'remaining_days' => $remaining_days,
        'remaining_hours' => $remaining_hours,
        'remaining_minutes' => $remaining_minutes,
    ]);
}
