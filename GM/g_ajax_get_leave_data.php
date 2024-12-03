<?php
// Include the database connection file
include '../connect.php';

$status = $_GET['status'];
$month = $_GET['month'];
$year = $_GET['year'];

// Prepare a SQL query to select leave data based on the status
$sql = "SELECT
    li.*
FROM
    leave_list li
WHERE
    li.l_department <> 'RD'
    AND li.l_leave_status = 0
    AND li.l_leave_id NOT IN (6, 7)
    AND li.l_level IN ('user', 'chief', 'leader', 'admin')
    AND Year(li.l_leave_end_date) = :year";

if ($status == 'all') {
    // No additional filters for 'all' status
} else if ($status == 1) {
    $sql .= " AND li.l_approve_status2 = 1";
} else if ($status == 4) {
    $sql .= " AND li.l_approve_status2 = 4";
} else if ($status == 5) {
    $sql .= " AND li.l_approve_status2 = 5";
} else {
    echo json_encode(['error' => 'ไม่พบสถานะ']);
    exit;
}

if ($month != "All") {
    $sql .= " AND Month(li.l_leave_end_date) = :month";
}

$sql .= " ORDER BY li.l_create_datetime DESC";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':year', $year, PDO::PARAM_INT);

if ($month != "All") {
    $stmt->bindParam(':month', $month, PDO::PARAM_INT);
}

$stmt->execute();

// Fetch the results as an associative array
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate holidays within leave period
foreach ($results as &$row) {
    $holiday_query = "SELECT COUNT(*) as holiday_count
                      FROM holiday
                      WHERE h_start_date BETWEEN :start_date AND :end_date
                        AND h_holiday_status = 'วันหยุด'
                        AND h_status = 0";

    $holiday_stmt = $conn->prepare($holiday_query);
    $holiday_stmt->bindParam(':start_date', $row['l_leave_start_date']);
    $holiday_stmt->bindParam(':end_date', $row['l_leave_end_date']);
    $holiday_stmt->execute();

    $holiday_data = $holiday_stmt->fetch(PDO::FETCH_ASSOC);
    $holiday_count = $holiday_data['holiday_count'] ?? 0;

    // Calculate leave duration
    $start_date = new DateTime($row['l_leave_start_date'] . ' ' . $row['l_leave_start_time']);
    $end_date = new DateTime($row['l_leave_end_date'] . ' ' . $row['l_leave_end_time']);
    $interval = $start_date->diff($end_date);

    $leave_days = $interval->days - $holiday_count;
    $leave_hours = $interval->h;
    $leave_minutes = $interval->i;

    // Adjust hours for out-of-range times
    $start_hour = (int) $start_date->format('H');
    $end_hour = (int) $end_date->format('H');

    if (!((($start_hour >= 8 && $start_hour < 12) && ($end_hour <= 12)) ||
        (($start_hour >= 13 && $start_hour < 17) && ($end_hour <= 17)))) {
        $leave_hours -= 1;
    }

    if ($leave_hours >= 8) {
        $leave_days += floor($leave_hours / 8);
        $leave_hours %= 8;
    }

    if ($leave_minutes >= 30) {
        $leave_minutes = 30;
    }

    $row['calculated_leave'] = [
        'days' => $leave_days,
        'hours' => $leave_hours,
        'minutes' => $leave_minutes,
    ];
}
// Send the results as JSON
header('Content-Type: application/json');
echo json_encode($results);