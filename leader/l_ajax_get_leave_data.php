<?php
// Include the database connection file
include '../connect.php';

$status = $_GET['status'];
$month = $_GET['month'];
$year = $_GET['year'];
$depart = $_GET['depart'];
$subDepart = $_GET['subDepart'];
$subDepart2 = $_GET['subDepart2'];
$subDepart3 = $_GET['subDepart3'];
$subDepart4 = $_GET['subDepart4'];
$subDepart5 = $_GET['subDepart5'];

// SQL query creation based on status
if ($status == 'all') {
    $sql = "SELECT li.*, em.*
            FROM leave_list li
            INNER JOIN employees em ON li.l_usercode = em.e_usercode
            WHERE li.l_approve_status IN (0, 1, 2, 3, 6)
              AND li.l_level IN ('user')
              AND li.l_leave_id NOT IN (6, 7)
              AND YEAR(li.l_create_datetime) = :year";

    if ($month != "All") {
        $sql .= " AND Month(li.l_create_datetime) = :month";
    }

    $sql .= " AND (
                (em.e_sub_department = :subDepart AND li.l_department = :depart) OR
                (em.e_sub_department2 = :subDepart2 AND li.l_department = :depart) OR
                (em.e_sub_department3 = :subDepart3 AND li.l_department = :depart) OR
                (em.e_sub_department4 = :subDepart4 AND li.l_department = :depart) OR
                (em.e_sub_department5 = :subDepart5 AND li.l_department = :depart)
              )
              ORDER BY li.l_create_datetime DESC";
} elseif (in_array($status, [0, 2, 3])) {
    $sql = "SELECT li.*, em.*
            FROM leave_list li
            INNER JOIN employees em ON li.l_usercode = em.e_usercode
            WHERE li.l_approve_status = :status
              AND li.l_level IN ('user')
              AND li.l_leave_id NOT IN (6, 7)
              AND YEAR(li.l_create_datetime) = :year";

    if ($month != "All") {
        $sql .= " AND Month(li.l_create_datetime) = :month";
    }

    $sql .= " AND (
                (em.e_sub_department = :subDepart AND li.l_department = :depart) OR
                (em.e_sub_department2 = :subDepart2 AND li.l_department = :depart) OR
                (em.e_sub_department3 = :subDepart3 AND li.l_department = :depart) OR
                (em.e_sub_department4 = :subDepart4 AND li.l_department = :depart) OR
                (em.e_sub_department5 = :subDepart5 AND li.l_department = :depart)
              )
              ORDER BY li.l_create_datetime DESC";
} else {
    echo json_encode(['error' => 'ไม่พบสถานะ']);
    exit;
}

$stmt = $conn->prepare($sql);
$stmt->bindParam(':year', $year, PDO::PARAM_INT);

if ($month != "All") {
    $stmt->bindParam(':month', $month, PDO::PARAM_INT);
}

$stmt->bindParam(':depart', $depart, PDO::PARAM_STR);
$stmt->bindParam(':subDepart', $subDepart, PDO::PARAM_STR);
$stmt->bindParam(':subDepart2', $subDepart2, PDO::PARAM_STR);
$stmt->bindParam(':subDepart3', $subDepart3, PDO::PARAM_STR);
$stmt->bindParam(':subDepart4', $subDepart4, PDO::PARAM_STR);
$stmt->bindParam(':subDepart5', $subDepart5, PDO::PARAM_STR);

if ($status != 'all') {
    $stmt->bindParam(':status', $status, PDO::PARAM_INT);
}

$stmt->execute();
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

echo json_encode($results);