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

// Send the results as JSON
header('Content-Type: application/json');
echo json_encode($results);
