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

if($month != "All"){
$sql .= " AND Month(l_leave_start_date) = '$month'";
}

$sql .= " AND Year(l_leave_start_date) = '$year' ORDER BY l_create_datetime DESC";

} else if ($status == 0) {
    // $sql = "SELECT * FROM leave_list WHERE Month(l_leave_end_date) = '$month' AND l_hr_status = 0 AND l_leave_id <> 7 ORDER BY l_create_datetime DESC";
    $sql = "SELECT * FROM leave_list WHERE l_leave_id NOT IN (6,7)
    AND l_hr_status = 0";
    if($month != "All"){
    $sql .= " AND Month(l_leave_start_date) = '$month'";
    }
    $sql .= " AND Year(l_leave_start_date) = '$year' 
    ORDER BY l_create_datetime DESC";
    
} else if ($status == 1) {
    // $sql = "SELECT * FROM leave_list WHERE Month(l_leave_end_date) = '$month' AND l_hr_status = 1 AND l_leave_id <> 7 ORDER BY l_create_datetime DESC";
    $sql = "SELECT * FROM leave_list WHERE l_leave_id NOT IN (6,7)
    AND l_hr_status = 1";
    if($month != "All"){
    $sql .= " AND Month(l_leave_start_date) = '$month'";
    }
    $sql .= " AND Year(l_leave_start_date) = '$year' 
    ORDER BY l_create_datetime DESC";
} else if ($status == 2) {
    // $sql = "SELECT * FROM leave_list WHERE Month(l_leave_end_date) = '$month' AND l_hr_status = 2 AND l_leave_id <> 7 ORDER BY l_create_datetime DESC";
    $sql = "SELECT * FROM leave_list WHERE l_leave_id NOT IN (6,7)
    AND l_hr_status = 2";
    if($month != "All"){
    $sql .= " AND Month(l_leave_start_date) = '$month'";
    }
    $sql .= " AND Year(l_leave_start_date) = '$year' 
    ORDER BY l_create_datetime DESC";
} else {

}

$stmt = $conn->prepare($sql);
if ($status != 0) {
    $stmt->bindParam(':status', $status, PDO::PARAM_INT);
}
$stmt->execute();

// Fetch the results as an associative array
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Send the results as JSON
echo json_encode($results);