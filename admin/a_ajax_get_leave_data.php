<?php
// Include the database connection file
include '../connect.php';

$status = $_GET['status'];
$month = $_GET['month'];
$year = $_GET['year'];

// Initialize the SQL query
$sql = "SELECT * FROM leave_list WHERE l_leave_id NOT IN (6,7)";

// Add conditions based on the status
if ($status != 'all') {
    $sql .= " AND l_hr_status = :status";
}

// Add conditions for month and year
if ($month != "All") {
    $sql .= " AND (
        MONTH(l_create_datetime) = :month
        OR MONTH(l_leave_end_date) = :month
    )";
}

$sql .= " AND (
    YEAR(l_create_datetime) = :year
    OR YEAR(l_leave_end_date) = :year
)

ORDER BY l_create_datetime DESC";

// Prepare the SQL statement
$stmt = $conn->prepare($sql);

// Bind parameters
if ($status != 'all') {
    $stmt->bindParam(':status', $status, PDO::PARAM_INT);
}
if ($month != "All") {
    $stmt->bindParam(':month', $month, PDO::PARAM_INT);
}
$stmt->bindParam(':year', $year, PDO::PARAM_INT);

// Execute the query
$stmt->execute();

// Fetch the results
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate leave details for each record
foreach ($results as &$row) {
    $holiday_query = "SELECT COUNT(*) AS holiday_count
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

    // Adjust hours for working hours
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
