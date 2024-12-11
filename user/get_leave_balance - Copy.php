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
    $total_sick = $result_leave['total_sick'] ?? 0;
    $total_annual = $result_leave['total_annual'] ?? 0;
    $total_other = $result_leave['total_other'] ?? 0;

    // คำนวณวันลาเต็มวันจากการใช้ชั่วโมง
    // 1 วัน = 8 ชั่วโมง
    $days_used += floor($hours_used / 8); // คำนวณวันจากชั่วโมง
    $hours_used = $hours_used % 8; // คงเหลือชั่วโมงที่ไม่ครบ 8

    // ปรับนาทีเป็นชั่วโมง ถ้านาทีมากกว่า 60 นาที
    if ($minutes_used >= 60) {
        $hours_used += floor($minutes_used / 60);
        $minutes_used = $minutes_used % 60;
    }

    // คำนวณวันคงเหลือ
    $remaining_days = $total_personal - $days_used;

    // คำนวณชั่วโมงคงเหลือ
    $remaining_hours = ($total_personal * 8) - ($days_used * 8 + $hours_used);

    // คำนวณนาทีคงเหลือ
    $remaining_minutes = $remaining_hours * 60 - $minutes_used;

    // ถ้านาทีคงเหลือเป็นค่าลบ จะตั้งให้เป็น 0
    if ($remaining_minutes < 0) {
        $remaining_minutes = 0;
    }    

    // แสดงผลลัพธ์
    if ($leaveType == 1) {
        echo json_encode([
            'remaining_days' => $remaining_days,
            'remaining_hours' => $remaining_hours,
            'remaining_minutes' => $remaining_minutes,
        ]);
    }
}

