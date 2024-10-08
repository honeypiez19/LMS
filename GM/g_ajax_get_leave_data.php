<?php
// Include the database connection file
include '../connect.php';

$status = $_GET['status'];
$month = $_GET['month'];
$year = $_GET['year'];
$depart = $_GET['depart'];

// Prepare a SQL query to select leave data based on the status
if ($status == 'all') {
    // $sql = "SELECT * FROM leave_list ORDER BY l_create_datetime DESC";
    // $sql = "SELECT * FROM leave_list WHERE Month(l_create_datetime) = '$month' AND l_department = '$depart' AND l_level <> 'chief' AND l_leave_id <> 7  ORDER BY l_create_datetime DESC ";
    $sql = "SELECT * FROM leave_list WHERE Year(l_create_datetime) = '$year'
    AND Month(l_create_datetime) = '$month'
    AND l_department = '$depart'
    AND l_level IN ('user','chief')
    AND l_leave_id NOT IN (6, 7)
    ORDER BY l_create_datetime DESC";

// รอ ผจก อนุมัติ
} else if ($status == 1) {
    // $sql = "SELECT * FROM leave_list WHERE l_hr_status = 0 ORDER BY l_create_datetime DESC";
    $sql = "SELECT * FROM leave_list WHERE Year(l_create_datetime) = '$year'
    AND Month(l_create_datetime) = '$month'
    AND l_department = '$depart'
    AND l_level IN ('user','chief')
    AND l_leave_id NOT IN (6, 7)
    AND l_approve_status2 = '$status'
    ORDER BY l_create_datetime DESC";

} else if ($status == 4) {
    // $sql = "SELECT * FROM leave_list WHERE l_hr_status = 1 ORDER BY l_create_datetime DESC";
    $sql = "SELECT * FROM leave_list WHERE Year(l_create_datetime) = '$year'
    AND Month(l_create_datetime) = '$month'
    AND l_department = '$depart'
    AND l_level IN ('user','chief')
    AND l_leave_id NOT IN (6, 7)
    AND l_approve_status2 = '$status'
    ORDER BY l_create_datetime DESC";

} else if ($status == 5) {
    // $sql = "SELECT * FROM leave_list WHERE l_hr_status = 2 ORDER BY l_create_datetime DESC";
    $sql = "SELECT * FROM leave_list WHERE Year(l_create_datetime) = '$year'
    AND Month(l_create_datetime) = '$month'
    AND l_department = '$depart'
    AND l_level IN ('user','chief')
    AND l_leave_id NOT IN (6, 7)
    AND l_approve_status2 = '$status'
    ORDER BY l_create_datetime DESC";
} else {

}

$stmt = $conn->prepare($sql);
if ($status != 0) {
    $stmt->bindParam(':status', $status, PDO::PARAM_INT);
    $stmt->bindParam(':year', $year, PDO::PARAM_INT);
    $stmt->bindParam(':month', $month, PDO::PARAM_INT);
}
$stmt->execute();

// Fetch the results as an associative array
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Send the results as JSON
echo json_encode($results);