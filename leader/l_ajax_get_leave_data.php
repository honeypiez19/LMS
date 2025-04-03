<?php
session_start();
date_default_timezone_set('Asia/Bangkok');

include '../connect.php';

// รับค่าพารามิเตอร์จาก AJAX
$page          = isset($_GET['page']) ? intval($_GET['page']) : 1;
$per_page      = isset($_GET['per_page']) ? intval($_GET['per_page']) : 10;
$status        = isset($_GET['status']) ? $_GET['status'] : 'all';
$selectedYear  = isset($_GET['year']) ? $_GET['year'] : date('Y');
$selectedMonth = isset($_GET['month']) ? $_GET['month'] : 'All';
$codeSearch    = isset($_GET['codeSearch']) ? $_GET['codeSearch'] : '';
$nameSearch    = isset($_GET['nameSearch']) ? $_GET['nameSearch'] : '';
$leaveSearch   = isset($_GET['leaveSearch']) ? $_GET['leaveSearch'] : '';

// รับค่าแผนกและหน่วยงาน
$depart     = isset($_GET['depart']) ? $_GET['depart'] : '';
$subDepart  = isset($_GET['subDepart']) ? $_GET['subDepart'] : '';
$subDepart2 = isset($_GET['subDepart2']) ? $_GET['subDepart2'] : '';
$subDepart3 = isset($_GET['subDepart3']) ? $_GET['subDepart3'] : '';
$subDepart4 = isset($_GET['subDepart4']) ? $_GET['subDepart4'] : '';
$subDepart5 = isset($_GET['subDepart5']) ? $_GET['subDepart5'] : '';

// คำนวณ offset สำหรับ pagination
$offset = ($page - 1) * $per_page;

// สร้าง SQL query พื้นฐาน
$sql = "SELECT
    li.*,
    em.*
FROM leave_list li
INNER JOIN employees em ON li.l_usercode = em.e_usercode
WHERE
    li.l_level IN ('user')
    AND li.l_approve_status <> 6
    AND li.l_leave_id NOT IN (6, 7)
    AND (
        YEAR(li.l_create_datetime) = :selectedYear
        OR YEAR(li.l_leave_end_date) = :selectedYear
    )";

// เพิ่มเงื่อนไขสำหรับการกรองตามสถานะการอนุมัติ
if ($status !== 'all') {
    $sql .= " AND li.l_approve_status = :status";
}

// เพิ่มเงื่อนไขสำหรับเดือน
if ($selectedMonth != "All") {
    $sql .= " AND (
        MONTH(li.l_create_datetime) = :selectedMonth
        OR MONTH(li.l_leave_end_date) = :selectedMonth
    )";
}

// เพิ่มเงื่อนไขสำหรับการค้นหาตามรหัสพนักงาน
if (! empty($codeSearch)) {
    $sql .= " AND (li.l_usercode LIKE :codeSearch)";
}

// เพิ่มเงื่อนไขสำหรับการค้นหาตามชื่อพนักงาน
if (! empty($nameSearch)) {
    $sql .= " AND (li.l_name LIKE :nameSearch)";
}

// เพิ่มเงื่อนไขสำหรับการค้นหาตามประเภทการลา
if (! empty($leaveSearch)) {
    $sql .= " AND (
        (li.l_leave_id = 1 AND :leaveSearch LIKE '%ลากิจได้รับค่าจ้าง%') OR
        (li.l_leave_id = 2 AND :leaveSearch LIKE '%ลากิจไม่ได้รับค่าจ้าง%') OR
        (li.l_leave_id = 3 AND :leaveSearch LIKE '%ลาป่วย%') OR
        (li.l_leave_id = 4 AND :leaveSearch LIKE '%ลาป่วยจากงาน%') OR
        (li.l_leave_id = 5 AND :leaveSearch LIKE '%ลาพักร้อน%') OR
        (li.l_leave_id = 8 AND :leaveSearch LIKE '%อื่น ๆ%')
    )";
}

// เพิ่มเงื่อนไขสำหรับการกรองตามแผนก
$sql .= " AND (
    (em.e_sub_department IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND em.e_sub_department <> '')
    OR
    (em.e_sub_department2 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND em.e_sub_department2 <> '')
    OR
    (em.e_sub_department3 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND em.e_sub_department3 <> '')
    OR
    (em.e_sub_department4 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND em.e_sub_department4 <> '')
    OR
    (em.e_sub_department5 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND em.e_sub_department5 <> '')
)";

// เพิ่มการเรียงลำดับ
$sql .= " ORDER BY li.l_create_datetime DESC";

// ใช้ SQL เดียวกันสำหรับนับจำนวนรายการทั้งหมดเพื่อทำ pagination
$count_sql = $sql;

// เพิ่ม LIMIT และ OFFSET สำหรับแบ่งหน้า
$sql .= " LIMIT :limit OFFSET :offset";

// เตรียม statement สำหรับนับจำนวนรายการทั้งหมด
$count_stmt = $conn->prepare($count_sql);

// Bind parameters สำหรับ count_stmt
$count_stmt->bindParam(':selectedYear', $selectedYear, PDO::PARAM_INT);
$count_stmt->bindParam(':depart', $depart);
$count_stmt->bindParam(':subDepart', $subDepart);
$count_stmt->bindParam(':subDepart2', $subDepart2);
$count_stmt->bindParam(':subDepart3', $subDepart3);
$count_stmt->bindParam(':subDepart4', $subDepart4);
$count_stmt->bindParam(':subDepart5', $subDepart5);

if ($status !== 'all') {
    $count_stmt->bindParam(':status', $status);
}

if ($selectedMonth != "All") {
    $count_stmt->bindParam(':selectedMonth', $selectedMonth);
}

if (! empty($codeSearch)) {
    $codeSearchParam = '%' . $codeSearch . '%';
    $count_stmt->bindParam(':codeSearch', $codeSearchParam);
}

if (! empty($nameSearch)) {
    $nameSearchParam = '%' . $nameSearch . '%';
    $count_stmt->bindParam(':nameSearch', $nameSearchParam);
}

if (! empty($leaveSearch)) {
    $count_stmt->bindParam(':leaveSearch', $leaveSearch);
}

// Execute count query
$count_stmt->execute();
$total_rows = $count_stmt->rowCount();

// เตรียม statement สำหรับดึงข้อมูล
$stmt = $conn->prepare($sql);

// Bind parameters
$stmt->bindParam(':selectedYear', $selectedYear, PDO::PARAM_INT);
$stmt->bindParam(':depart', $depart);
$stmt->bindParam(':subDepart', $subDepart);
$stmt->bindParam(':subDepart2', $subDepart2);
$stmt->bindParam(':subDepart3', $subDepart3);
$stmt->bindParam(':subDepart4', $subDepart4);
$stmt->bindParam(':subDepart5', $subDepart5);
$stmt->bindParam(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

if ($status !== 'all') {
    $stmt->bindParam(':status', $status);
}

if ($selectedMonth != "All") {
    $stmt->bindParam(':selectedMonth', $selectedMonth);
}

if (! empty($codeSearch)) {
    $codeSearchParam = '%' . $codeSearch . '%';
    $stmt->bindParam(':codeSearch', $codeSearchParam);
}

if (! empty($nameSearch)) {
    $nameSearchParam = '%' . $nameSearch . '%';
    $stmt->bindParam(':nameSearch', $nameSearchParam);
}

if (! empty($leaveSearch)) {
    $stmt->bindParam(':leaveSearch', $leaveSearch);
}

// Execute query
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// คำนวณข้อมูลสำหรับ pagination
$total_pages = ceil($total_rows / $per_page);
$from        = ($total_rows == 0) ? 0 : (($page - 1) * $per_page) + 1;
$to          = min($from + $per_page - 1, $total_rows);

// เตรียมข้อมูลสำหรับคำนวณจำนวนวันลา
foreach ($data as &$row) {
    // คำนวณวันลาและชั่วโมง (โค้ดเดิมจาก PHP หลัก)
    $holiday_query = "SELECT COUNT(*) as holiday_count
        FROM holiday
        WHERE h_start_date BETWEEN :start_date AND :end_date
        AND h_holiday_status = 'วันหยุด'
        AND h_status = 0";

    $holiday_stmt = $conn->prepare($holiday_query);
    $holiday_stmt->bindParam(':start_date', $row['l_leave_start_date']);
    $holiday_stmt->bindParam(':end_date', $row['l_leave_end_date']);
    $holiday_stmt->execute();

    $holiday_data  = $holiday_stmt->fetch(PDO::FETCH_ASSOC);
    $holiday_count = $holiday_data['holiday_count'];

    $l_leave_start_date = new DateTime($row['l_leave_start_date'] . ' ' . $row['l_leave_start_time']);
    $l_leave_end_date   = new DateTime($row['l_leave_end_date'] . ' ' . $row['l_leave_end_time']);
    $interval           = $l_leave_start_date->diff($l_leave_end_date);

    $leave_days    = $interval->days - $holiday_count;
    $leave_hours   = $interval->h;
    $leave_minutes = $interval->i;

    $start_hour = (int) $l_leave_start_date->format('H');
    $end_hour   = (int) $l_leave_end_date->format('H');

    if (! ((($start_hour >= 8 && $start_hour < 12) && ($end_hour <= 12)) ||
        (($start_hour >= 13 && $start_hour < 17) && ($end_hour <= 17)))) {
        $leave_hours -= 1;
    }

    if ($leave_hours >= 8) {
        $leave_days += floor($leave_hours / 8);
        $leave_hours = $leave_hours % 8;
    }

    if ($leave_minutes >= 30) {
        $leave_minutes = 30;
    }

    $row['calculated_leave'] = [
        'days'    => $leave_days,
        'hours'   => $leave_hours,
        'minutes' => $leave_minutes,
    ];
}

// เพิ่มการคำนวณจำนวนรายการตามสถานะ
$statuses      = ['all', '0', '2', '3'];
$status_counts = [];

foreach ($statuses as $stat) {
    // สร้าง SQL base สำหรับนับจำนวนตามสถานะ
    $status_sql = "SELECT COUNT(*) FROM leave_list li
                  INNER JOIN employees em ON li.l_usercode = em.e_usercode
                  WHERE
                    li.l_level IN ('user')
                    AND li.l_approve_status <> 6
                    AND li.l_leave_id NOT IN (6, 7)
                    AND (
                        YEAR(li.l_create_datetime) = :selectedYear
                        OR YEAR(li.l_leave_end_date) = :selectedYear
                    )";

    // เพิ่มเงื่อนไขสำหรับการกรองตามแผนก (เหมือนกับ query หลัก)
    $status_sql .= " AND (
        (em.e_sub_department IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND em.e_sub_department <> '')
        OR
        (em.e_sub_department2 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND em.e_sub_department2 <> '')
        OR
        (em.e_sub_department3 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND em.e_sub_department3 <> '')
        OR
        (em.e_sub_department4 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND em.e_sub_department4 <> '')
        OR
        (em.e_sub_department5 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND em.e_sub_department5 <> '')
    )";

    // เพิ่มเงื่อนไขสำหรับเดือนที่เลือก
    if ($selectedMonth != "All") {
        $status_sql .= " AND (
            MONTH(li.l_create_datetime) = :selectedMonth
            OR MONTH(li.l_leave_end_date) = :selectedMonth
        )";
    }

    // เพิ่มเงื่อนไขสำหรับการค้นหา
    if (! empty($codeSearch)) {
        $status_sql .= " AND (li.l_usercode LIKE :codeSearch)";
    }

    if (! empty($nameSearch)) {
        $status_sql .= " AND (li.l_name LIKE :nameSearch)";
    }

    if (! empty($leaveSearch)) {
        $status_sql .= " AND (
            (li.l_leave_id = 1 AND :leaveSearch LIKE '%ลากิจได้รับค่าจ้าง%') OR
            (li.l_leave_id = 2 AND :leaveSearch LIKE '%ลากิจไม่ได้รับค่าจ้าง%') OR
            (li.l_leave_id = 3 AND :leaveSearch LIKE '%ลาป่วย%') OR
            (li.l_leave_id = 4 AND :leaveSearch LIKE '%ลาป่วยจากงาน%') OR
            (li.l_leave_id = 5 AND :leaveSearch LIKE '%ลาพักร้อน%') OR
            (li.l_leave_id = 8 AND :leaveSearch LIKE '%อื่น ๆ%')
        )";
    }

    // เพิ่มเงื่อนไขตามสถานะ
    if ($stat !== 'all') {
        $status_sql .= " AND li.l_approve_status = :status";
    }

    $status_stmt = $conn->prepare($status_sql);

    // Bind parameters
    $status_stmt->bindParam(':selectedYear', $selectedYear, PDO::PARAM_INT);
    $status_stmt->bindParam(':depart', $depart);
    $status_stmt->bindParam(':subDepart', $subDepart);
    $status_stmt->bindParam(':subDepart2', $subDepart2);
    $status_stmt->bindParam(':subDepart3', $subDepart3);
    $status_stmt->bindParam(':subDepart4', $subDepart4);
    $status_stmt->bindParam(':subDepart5', $subDepart5);

    if ($selectedMonth != "All") {
        $status_stmt->bindParam(':selectedMonth', $selectedMonth);
    }

    if (! empty($codeSearch)) {
        $status_stmt->bindParam(':codeSearch', $codeSearchParam);
    }

    if (! empty($nameSearch)) {
        $status_stmt->bindParam(':nameSearch', $nameSearchParam);
    }

    if (! empty($leaveSearch)) {
        $status_stmt->bindParam(':leaveSearch', $leaveSearch);
    }

    if ($stat !== 'all') {
        $status_stmt->bindParam(':status', $stat);
    }

    $status_stmt->execute();
    $status_counts[$stat] = $status_stmt->fetchColumn();
}

// ส่งกลับข้อมูลในรูปแบบ JSON
$response = [
    'data'          => $data,
    'pagination'    => [
        'current_page' => $page,
        'total_pages'  => $total_pages,
        'per_page'     => $per_page,
        'total_rows'   => $total_rows,
        'from'         => $from,
        'to'           => $to,
    ],
    'status_counts' => $status_counts,
];

header('Content-Type: application/json');
echo json_encode($response);
