<?php
// Include the database connection file
include '../connect.php';

$status = $_GET['status'];
$month = $_GET['month'];
$year = $_GET['year'];

// Prepare a SQL query to select leave data based on the status
if ($status == 'all') {
    $sql = "SELECT * FROM leave_list WHERE Year(l_leave_end_date) = :year
            AND Month(l_leave_end_date) = :month
            
            AND l_leave_id NOT IN (6, 7)
            AND l_department <> 'RD'
            ORDER BY l_create_datetime DESC";
} else if ($status == 1) {
    $sql = "SELECT * FROM leave_list WHERE Year(l_leave_end_date) = :year
            AND Month(l_leave_end_date) = :month
            AND l_leave_id NOT IN (6, 7)
            AND l_department <> 'RD'
            AND l_approve_status2 = :status
            ORDER BY l_create_datetime DESC";
} else if ($status == 4) {
    $sql = "SELECT * FROM leave_list WHERE Year(l_leave_end_date) = :year
            AND Month(l_leave_end_date) = :month
            AND l_leave_id NOT IN (6, 7)
            AND l_department <> 'RD'
            AND l_approve_status2 = :status
            ORDER BY l_create_datetime DESC";
} else if ($status == 5) {
    $sql = "SELECT * FROM leave_list WHERE Year(l_leave_end_date) = :year
            AND Month(l_leave_end_date) = :month
            AND l_leave_id NOT IN (6, 7)
            AND l_department <> 'RD'
            AND l_approve_status2 = :status
            ORDER BY l_create_datetime DESC";
} else {
    echo 'ไม่พบสถานะ';
}

$stmt = $conn->prepare($sql);
$stmt->bindParam(':year', $year, PDO::PARAM_INT);
$stmt->bindParam(':month', $month, PDO::PARAM_INT);

if ($status != 'all') {
    $stmt->bindParam(':status', $status, PDO::PARAM_INT);
}

$stmt->execute();

// Fetch the results as an associative array
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Send the results as JSON
echo json_encode($results);