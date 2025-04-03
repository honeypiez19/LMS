<?php
include '../connect.php';
// รับค่าตัวแปรจากการส่งมาทาง POST
$userCode       = $_POST['userCode'];
$leaveType      = $_POST['leaveType'];                                               // ประเภทการลา
$selectedYear   = $_POST['selectedYear'];                                            // ปีที่ต้องการดึงข้อมูล
$createDatetime = isset($_POST['createDatetime']) ? $_POST['createDatetime'] : null; // รับค่าตัวแปรเพื่อระบุใบลาที่กำลังแก้ไข

// SQL สำหรับดึงข้อมูลการลาทั้งหมด (ยกเว้นใบลาที่กำลังแก้ไข)
$sql_leave = "SELECT
    SUM(
        DATEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))
        - (
            SELECT COUNT(1)
            FROM holiday
            WHERE h_start_date BETWEEN l_leave_start_date AND l_leave_end_date
              AND h_holiday_status = 'วันหยุด'
              AND h_status = 0
        )
    ) AS total_leave_days,
    SUM(
        HOUR(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))) % 24
    ) AS total_leave_hours,
    SUM(
        MINUTE(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time)))
    ) AS total_leave_minutes,
    (SELECT e_leave_personal FROM employees WHERE e_usercode = :userCode) AS total_personal,
    (SELECT e_leave_personal_no FROM employees WHERE e_usercode = :userCode) AS total_personal_no,
    (SELECT e_leave_sick FROM employees WHERE e_usercode = :userCode) AS total_sick,
    (SELECT e_leave_sick_work FROM employees WHERE e_usercode = :userCode) AS total_sick_work,
    (SELECT e_leave_annual FROM employees WHERE e_usercode = :userCode) AS total_annual,
    (SELECT e_other FROM employees WHERE e_usercode = :userCode) AS total_other
    FROM leave_list
    JOIN employees ON employees.e_usercode = leave_list.l_usercode
    WHERE l_leave_id = :leaveType
    AND l_usercode = :userCode
    AND YEAR(l_leave_end_date) = :selectedYear
    AND l_leave_status = 0
    AND l_approve_status IN (2,6)
    AND l_approve_status2 = 4";

// เพิ่มเงื่อนไขยกเว้นใบลาที่กำลังแก้ไข
if (! empty($createDatetime)) {
    $sql_leave .= " AND l_create_datetime != :createDatetime";
}

// เตรียมการ query
$stmt_leave = $conn->prepare($sql_leave);
$stmt_leave->bindParam(':leaveType', $leaveType, PDO::PARAM_INT);
$stmt_leave->bindParam(':userCode', $userCode);
$stmt_leave->bindParam(':selectedYear', $selectedYear, PDO::PARAM_INT);
if (! empty($createDatetime)) {
    $stmt_leave->bindParam(':createDatetime', $createDatetime);
}
$stmt_leave->execute();

// ดึงข้อมูลจาก query
$result_leave = $stmt_leave->fetch(PDO::FETCH_ASSOC);

if ($result_leave) {
    $days    = $result_leave['total_leave_days'] ?? 0;
    $hours   = $result_leave['total_leave_hours'] ?? 0;
    $minutes = $result_leave['total_leave_minutes'] ?? 0;

    // Employee leave balances
    $total_personal    = $result_leave['total_personal'] ?? 0;
    $total_personal_no = $result_leave['total_personal_no'] ?? 0;
    $total_sick        = $result_leave['total_sick'] ?? 0;
    $total_sick_work   = $result_leave['total_sick_work'] ?? 0;
    $total_annual      = $result_leave['total_annual'] ?? 0;
    $total_other       = $result_leave['total_other'] ?? 0;

    $days += floor($hours / 8);
    $hours = $hours % 8;

// Adjust minutes if necessary
    if ($minutes >= 60) {
        $hours += floor($minutes / 60);
        $minutes = $minutes % 60;
    }

// Correct minute logic for leave type 1
    if ($minutes > 0 && $minutes <= 30) {
        $minutes = 30;
    } elseif ($minutes > 30) {
        $minutes = 0;
        $hours += 1;
    }

    if ($leaveType == 1) { // ลาพักร้อน
                               // Calculate remaining leave balance
        $remaining_days = $total_personal - $days;

        if ($minutes > 0) {
            $remaining_hours   = $hours - 1; // Subtract an hour for 30 minutes or more
            $remaining_minutes = 30;         // The remaining minutes should be 30
        } else {
            $remaining_hours   = $hours;
            $remaining_minutes = $minutes;
        }
    } elseif ($leaveType == 2) { // ลาป่วย
        $remaining_days    = $total_personal_no - $days;
        $remaining_hours   = ($total_personal_no * 8) - ($days * 8 + $hours);
        $remaining_minutes = ($total_personal_no * 8 * 60) - (($days * 8 + $hours) * 60 + $minutes);
    } elseif ($leaveType == 3) { // ลากิจ
        $remaining_days    = $total_sick - $days;
        $remaining_hours   = ($total_sick * 8) - ($days * 8 + $hours);
        $remaining_minutes = ($total_sick * 8 * 60) - (($days * 8 + $hours) * 60 + $minutes);
    } elseif ($leaveType == 4) { // ลาอื่นๆ
        $remaining_days    = $total_sick_work - $days;
        $remaining_hours   = ($total_sick_work * 8) - ($days * 8 + $hours);
        $remaining_minutes = ($total_sick_work * 8 * 60) - (($days * 8 + $hours) * 60 + $minutes);
    } elseif ($leaveType == 5) { // ลาพักผ่อน
        $remaining_days    = $total_annual - $days;
        $remaining_hours   = ($total_annual * 8) - ($days * 8 + $hours);
        $remaining_minutes = ($total_annual * 8 * 60) - (($days * 8 + $hours) * 60 + $minutes);
    }

    // ปรับค่าให้ถูกต้อง (คงเหลือต้องไม่ติดลบ)
    if ($remaining_minutes < 0) {
        $remaining_hours -= 1;
        $remaining_minutes += 60;
    }
    if ($remaining_hours < 0) {
        $remaining_days -= 1;
        $remaining_hours += 8;
    }
    if ($remaining_days < 0) {
        $remaining_days    = 0;
        $remaining_hours   = 0;
        $remaining_minutes = 0;
    }

    // ส่งผลลัพธ์ออกมาในรูปแบบ JSON
    echo json_encode([
        'remaining_days'    => $remaining_days,
        'remaining_hours'   => $remaining_hours,
        'remaining_minutes' => $remaining_minutes,
    ]);
} else {
    // หากไม่พบข้อมูลการลา ให้ส่งข้อมูลจำนวนวันลาตามสิทธิ์ทั้งหมด
    $sql_employee = "SELECT
        e_leave_personal, e_leave_personal_no, e_leave_sick,
        e_leave_sick_work, e_leave_annual, e_other
        FROM employees WHERE e_usercode = :userCode";

    $stmt_employee = $conn->prepare($sql_employee);
    $stmt_employee->bindParam(':userCode', $userCode);
    $stmt_employee->execute();
    $employee_data = $stmt_employee->fetch(PDO::FETCH_ASSOC);

    if ($employee_data) {
        if ($leaveType == 1) { // ลาพักร้อน
            $remaining_days = $employee_data['e_leave_personal'] ?? 0;
        } elseif ($leaveType == 2) { // ลาป่วย
            $remaining_days = $employee_data['e_leave_personal_no'] ?? 0;
        } elseif ($leaveType == 3) { // ลากิจ 
            $remaining_days = $employee_data['e_leave_sick'] ?? 0;
        } elseif ($leaveType == 4) { // ลาอื่นๆ
            $remaining_days = $employee_data['e_leave_sick_work'] ?? 0;
        } elseif ($leaveType == 5) { // ลาพักผ่อน
            $remaining_days = $employee_data['e_leave_annual'] ?? 0;
        } else {
            $remaining_days = 0;
        }

        echo json_encode([
            'remaining_days'    => $remaining_days,
            'remaining_hours'   => 0,
            'remaining_minutes' => 0,
        ]);
    } else {
        // หากไม่พบข้อมูลพนักงาน
        echo json_encode(['error' => 'No data found']);
    }
}
