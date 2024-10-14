<?php
// Include the database connection file
include '../connect.php';

$status = $_GET['status'];
$month = $_GET['month'];
$depart = $_GET['depart'];
$subDepart = $_GET['subDepart'];

// Prepare a SQL query to select leave data based on the status
if ($status == 'all') {
    // $sql = "SELECT * FROM leave_list WHERE Month(l_create_datetime) = '$month'
    // AND l_department = '$depart'
    // AND l_level = 'user'
    // AND l_leave_id <> 7
    // AND l_leave_id <> 6
    // ORDER BY l_create_datetime DESC";
    $sql = "SELECT li.*, em.e_sub_department, em.e_sub_department2, em.e_sub_department3, em.e_sub_department4, em.e_sub_department5
    FROM leave_list li
    INNER JOIN employees em ON li.l_usercode = em.e_usercode AND em.e_sub_department = :subDepart
    WHERE Month(l_create_datetime) = :month
    -- AND l_department = :depart
    AND l_level = 'user'
    AND l_leave_id <> 6
    AND l_leave_id <> 7
    ORDER BY l_create_datetime DESC";

} else if ($status == 0) {
    // $sql = "SELECT * FROM leave_list WHERE Month(l_create_datetime) = '$month'
    // AND l_department = '$depart'
    // AND l_approve_status = '$status'
    // AND l_level = 'user'
    // AND l_leave_id <> 7
    // AND l_leave_id <> 6
    // ORDER BY l_create_datetime DESC";
    $sql = "SELECT li.*, em.e_sub_department, em.e_sub_department2 , em.e_sub_department3 , em.e_sub_department4, em.e_sub_department5
    FROM leave_list li
    INNER JOIN employees em ON li.l_usercode = em.e_usercode AND em.e_sub_department = :subDepart
    WHERE Month(l_create_datetime) = :month
    -- AND l_department = :depart
    AND l_level = 'user'
    AND l_leave_id <> 6
    AND l_leave_id <> 7
    AND l_approve_status = :status
    ORDER BY l_create_datetime DESC";

} else if ($status == 2) {
    // $sql = "SELECT * FROM leave_list WHERE Month(l_create_datetime) = '$month'
    // AND l_department = '$depart'
    // AND l_approve_status = '$status'
    // AND l_level = 'user'
    // AND l_leave_id <> 7
    // AND l_leave_id <> 6
    // ORDER BY l_create_datetime DESC";
    $sql = "SELECT li.*, em.e_sub_department, em.e_sub_department2 , em.e_sub_department3 , em.e_sub_department4, em.e_sub_department5
            FROM leave_list li
            INNER JOIN employees em ON li.l_usercode = em.e_usercode AND em.e_sub_department = :subDepart
            WHERE Month(l_create_datetime) = :month
            -- AND l_department = :depart
            AND l_level = 'user'
            AND l_leave_id <> 6
            AND l_leave_id <> 7
            AND l_approve_status = :status
            ORDER BY l_create_datetime DESC";

} else if ($status == 3) {
    // $sql = "SELECT * FROM leave_list WHERE Month(l_create_datetime) = '$month'
    // AND l_department = '$depart'
    // AND l_approve_status = '$status'
    // AND l_level = 'user'
    // AND l_leave_id <> 7
    // AND l_leave_id <> 6
    // ORDER BY l_create_datetime DESC";
    $sql = "SELECT li.*, em.e_sub_department, em.e_sub_department2 , em.e_sub_department3 , em.e_sub_department4, em.e_sub_department5
    FROM leave_list li
    INNER JOIN employees em ON li.l_usercode = em.e_usercode AND em.e_sub_department = :subDepart
    WHERE Month(l_create_datetime) = :month
    -- AND l_department = :depart
    AND l_level = 'user'
    AND l_leave_id <> 6
    AND l_leave_id <> 7
    AND l_approve_status = :status
    ORDER BY l_create_datetime DESC";
} else {

}

$stmt = $conn->prepare($sql);
$stmt->bindParam(':subDepart', $subDepart, PDO::PARAM_STR);
$stmt->bindParam(':month', $month, PDO::PARAM_INT);
// $stmt->bindParam(':depart', $depart, PDO::PARAM_STR);

if ($status != 'all') {
    $stmt->bindParam(':status', $status, PDO::PARAM_INT);
}

$stmt->execute();

// Fetch the results as an associative array
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Send the results as JSON
echo json_encode($results);