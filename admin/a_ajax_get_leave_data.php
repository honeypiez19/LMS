<?php
// Include the database connection file
include '../connect.php';

$status = $_GET['status'];
$month = $_GET['month'];
$year = $_GET['year'];

// Prepare a SQL query to select leave data based on the status
if ($status == 'all') {
    // $sql = "SELECT * FROM leave_list WHERE Month(l_leave_end_date) = '$month' AND l_leave_id <> 7 ORDER BY l_create_datetime DESC ";

    $sql = "SELECT * FROM leave_list WHERE l_leave_id NOT IN (6,7) ";

    if ($month != "All") {
        $sql .= " AND Month(l_leave_start_date) = '$month'";
    }

    $sql .= " AND Year(l_leave_start_date) = '$year' ORDER BY l_create_datetime DESC";

} else if ($status == 0) {
    // $sql = "SELECT * FROM leave_list WHERE Month(l_leave_end_date) = '$month' AND l_hr_status = 0 AND l_leave_id <> 7 ORDER BY l_create_datetime DESC";
    $sql = "SELECT * FROM leave_list WHERE l_leave_id NOT IN (6,7)
    AND l_hr_status = 0";
    if ($month != "All") {
        $sql .= " AND Month(l_leave_start_date) = '$month'";
    }
    $sql .= " AND Year(l_leave_start_date) = '$year'
    ORDER BY l_create_datetime DESC";

} else if ($status == 1) {
    // $sql = "SELECT * FROM leave_list WHERE Month(l_leave_end_date) = '$month' AND l_hr_status = 1 AND l_leave_id <> 7 ORDER BY l_create_datetime DESC";
    $sql = "SELECT * FROM leave_list WHERE l_leave_id NOT IN (6,7)
    AND l_hr_status = 1";
    if ($month != "All") {
        $sql .= " AND Month(l_leave_start_date) = '$month'";
    }
    $sql .= " AND Year(l_leave_start_date) = '$year'
    ORDER BY l_create_datetime DESC";
} else if ($status == 2) {
    // $sql = "SELECT * FROM leave_list WHERE Month(l_leave_end_date) = '$month' AND l_hr_status = 2 AND l_leave_id <> 7 ORDER BY l_create_datetime DESC";
    $sql = "SELECT * FROM leave_list WHERE l_leave_id NOT IN (6,7)
    AND l_hr_status = 2";
    if ($month != "All") {
        $sql .= " AND Month(l_leave_start_date) = '$month'";
    }
    $sql .= " AND Year(l_leave_start_date) = '$year'
    ORDER BY l_create_datetime DESC";
} else {
    $sql = "SELECT * FROM leave_list WHERE l_leave_id NOT IN (6,7)
    AND l_leave_status = 1";
    if ($month != "All") {
        $sql .= " AND Month(l_leave_start_date) = '$month'";
    }
    $sql .= " AND Year(l_leave_start_date) = '$year'
    ORDER BY l_create_datetime DESC";

}

$stmt = $conn->prepare($sql);
if ($status != 0) {
    $stmt->bindParam(':status', $status, PDO::PARAM_INT);
}
$stmt->execute();

// Fetch the results as an associative array
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
echo json_encode($results);