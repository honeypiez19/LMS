<?php
session_start();
date_default_timezone_set('Asia/Bangkok');

include '../connect.php';

$page          = isset($_GET['page']) ? intval($_GET['page']) : 1;
$per_page      = isset($_GET['per_page']) ? intval($_GET['per_page']) : 10;
$status        = isset($_GET['status']) ? $_GET['status'] : 'all';
$selectedYear  = isset($_GET['year']) ? $_GET['year'] : date('Y');
$selectedMonth = isset($_GET['month']) ? $_GET['month'] : 'All';
$codeSearch    = isset($_GET['codeSearch']) ? $_GET['codeSearch'] : '';
$nameSearch    = isset($_GET['nameSearch']) ? $_GET['nameSearch'] : '';
$leaveSearch   = isset($_GET['leaveSearch']) ? $_GET['leaveSearch'] : '';
$workplace     = isset($_GET['workplace']) ? $_GET['workplace'] : '';

// Pagination
$offset = ($page - 1) * $per_page;

// Build WHERE conditions
$where = "li.l_hr_status <> 3
    AND li.l_leave_id NOT IN (6, 7)
    AND (
        YEAR(li.l_leave_end_date) = :selectedYear
    )
    AND li.l_workplace = :workplace
";

$params = [
    ':selectedYear' => $selectedYear,
    ':workplace'    => $workplace,
];

if ($status !== 'all') {
    if ($status == '4') {
        // For canceled leaves use l_leave_status = 1 instead of l_hr_status
        $where .= " AND li.l_leave_status = 1 AND li.l_hr_status <> 3";
    } else {
        $where .= " AND li.l_hr_status = :status";
        $params[':status'] = $status;
    }
}

if ($selectedMonth !== 'All') {
    $where .= " AND (
        MONTH(li.l_leave_end_date) = :selectedMonth
    )";
    $params[':selectedMonth'] = $selectedMonth;
}

if (! empty($codeSearch)) {
    $where .= " AND li.l_usercode LIKE :codeSearch";
    $params[':codeSearch'] = "%$codeSearch%";
}

if (! empty($nameSearch)) {
    $where .= " AND li.l_name LIKE :nameSearch";
    $params[':nameSearch'] = "%$nameSearch%";
}

if (! empty($leaveSearch)) {
    $where .= " AND (
        (li.l_leave_id = 1 AND :leaveSearch LIKE '%ลากิจได้รับค่าจ้าง%') OR
        (li.l_leave_id = 2 AND :leaveSearch LIKE '%ลากิจไม่ได้รับค่าจ้าง%') OR
        (li.l_leave_id = 3 AND :leaveSearch LIKE '%ลาป่วย%') OR
        (li.l_leave_id = 4 AND :leaveSearch LIKE '%ลาป่วยจากงาน%') OR
        (li.l_leave_id = 5 AND :leaveSearch LIKE '%ลาพักร้อน%') OR
        (li.l_leave_id = 8 AND :leaveSearch LIKE '%อื่น ๆ%')
    )";
    $params[':leaveSearch'] = $leaveSearch;
}

// Count query
$count_sql = "SELECT COUNT(*) FROM leave_list li
              INNER JOIN employees em ON li.l_usercode = em.e_usercode
              WHERE $where";
$count_stmt = $conn->prepare($count_sql);
foreach ($params as $key => &$val) {
    $count_stmt->bindParam($key, $val);
}
$count_stmt->execute();
$total_rows = $count_stmt->fetchColumn();

// Main data query
$data_sql = "SELECT li.*, em.*
             FROM leave_list li
             INNER JOIN employees em ON li.l_usercode = em.e_usercode
             WHERE $where
             ORDER BY li.l_create_datetime DESC
             LIMIT :limit OFFSET :offset";

$data_stmt = $conn->prepare($data_sql);
foreach ($params as $key => &$val) {
    $data_stmt->bindParam($key, $val);
}
$data_stmt->bindParam(':limit', $per_page, PDO::PARAM_INT);
$data_stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$data_stmt->execute();
$data = $data_stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate pagination info
$total_pages = ceil($total_rows / $per_page);
$from        = ($total_rows == 0) ? 0 : (($page - 1) * $per_page) + 1;
$to          = min($from + $per_page - 1, $total_rows);

// Calculate leave time
foreach ($data as &$row) {
    $holiday_query = "SELECT COUNT(*) as holiday_count
                      FROM holiday
                      WHERE h_start_date BETWEEN :start_date AND :end_date
                      AND h_holiday_status = 'วันหยุด' AND h_status = 0";
    $holiday_stmt = $conn->prepare($holiday_query);
    $holiday_stmt->bindParam(':start_date', $row['l_leave_start_date']);
    $holiday_stmt->bindParam(':end_date', $row['l_leave_end_date']);
    $holiday_stmt->execute();
    $holiday_count = $holiday_stmt->fetchColumn();

    $start    = new DateTime($row['l_leave_start_date'] . ' ' . $row['l_leave_start_time']);
    $end      = new DateTime($row['l_leave_end_date'] . ' ' . $row['l_leave_end_time']);
    $interval = $start->diff($end);

    $leave_days    = $interval->days - $holiday_count;
    $leave_hours   = $interval->h;
    $leave_minutes = $interval->i;

    $start_hour = (int) $start->format('H');
    $end_hour   = (int) $end->format('H');

    if (! (($start_hour >= 8 && $start_hour < 12 && $end_hour <= 12) ||
        ($start_hour >= 13 && $start_hour < 17 && $end_hour <= 17))) {
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

// ดึงสถานะทั้งหมดที่ต้องการ
$statuses      = ['all', '0', '1', '2', '4'];
$status_counts = [];

foreach ($statuses as $stat) {
    $status_where = "li.l_hr_status <> 3 AND li.l_leave_id NOT IN (6,7)
        AND YEAR(li.l_leave_end_date) = :selectedYear
        AND li.l_workplace = :workplace
    ";

    $status_params = [
        ':selectedYear' => $selectedYear,
        ':workplace'    => $workplace,
    ];

    if ($selectedMonth !== 'All') {
        $status_where .= " AND MONTH(li.l_leave_end_date) = :selectedMonth";
        $status_params[':selectedMonth'] = $selectedMonth;
    }

    // เพิ่มเงื่อนไขสำหรับการค้นหา
    if (! empty($codeSearch)) {
        $status_where .= " AND li.l_usercode LIKE :codeSearch";
        $status_params[':codeSearch'] = "%$codeSearch%";
    }

    if (! empty($nameSearch)) {
        $status_where .= " AND li.l_name LIKE :nameSearch";
        $status_params[':nameSearch'] = "%$nameSearch%";
    }

    if (! empty($leaveSearch)) {
        $status_where .= " AND (
        (li.l_leave_id = 1 AND :leaveSearch LIKE '%ลากิจได้รับค่าจ้าง%') OR
        (li.l_leave_id = 2 AND :leaveSearch LIKE '%ลากิจไม่ได้รับค่าจ้าง%') OR
        (li.l_leave_id = 3 AND :leaveSearch LIKE '%ลาป่วย%') OR
        (li.l_leave_id = 4 AND :leaveSearch LIKE '%ลาป่วยจากงาน%') OR
        (li.l_leave_id = 5 AND :leaveSearch LIKE '%ลาพักร้อน%') OR
        (li.l_leave_id = 8 AND :leaveSearch LIKE '%อื่น ๆ%')
    )";
        $status_params[':leaveSearch'] = $leaveSearch;
    }

    if ($stat !== 'all') {
        if ($stat == '4') {
            // For canceled leaves in status counts
            $status_where .= " AND li.l_leave_status = 1 AND li.l_hr_status <> 3";
        } else {
            $status_where .= " AND li.l_hr_status = :status";
            $status_params[':status'] = $stat;
        }
    }

    $status_sql  = "SELECT COUNT(*) FROM leave_list li INNER JOIN employees em ON li.l_usercode = em.e_usercode WHERE $status_where";
    $status_stmt = $conn->prepare($status_sql);
    $status_stmt->execute($status_params);
    $status_counts[$stat] = $status_stmt->fetchColumn();
}

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
    'status_counts' => $status_counts, // ส่งค่ากลับด้วย
];

header('Content-Type: application/json');
echo json_encode($response);
