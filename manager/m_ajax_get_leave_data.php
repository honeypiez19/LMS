<?php
// Include the database connection file
include '../connect.php';

$status = $_GET['status'];
$month = $_GET['month'];
$year = $_GET['year'];
$depart = $_GET['depart'];
$subDepart = $_GET['subDepart'];

// Prepare a SQL query to select leave data based on the status
if ($status == 'all') {
    // $sql = "SELECT * FROM leave_list WHERE Year(l_create_datetime) = '$year'
    // AND Month(l_create_datetime) = '$month'
    // AND l_department = '$depart'
    // AND l_level IN ('user','chief')
    // AND l_leave_id NOT IN (6, 7)
    // ORDER BY l_create_datetime DESC";
    $sql = "SELECT
    li.*,
    em.e_sub_department,
    em.e_sub_department2,
    em.e_sub_department3,
    em.e_sub_department4,
    em.e_sub_department5
FROM leave_list li
INNER JOIN employees em
    ON li.l_usercode = em.e_usercode
WHERE
     li.l_approve_status IN (0,2, 3, 6)
    AND li.l_level IN ('user', 'chief', 'leader')
    AND li.l_leave_id NOT IN (6, 7)
    AND Year(li.l_create_datetime) = :year
    AND Month(li.l_create_datetime) = :month
    AND (
        -- Check for matching department or sub-department
        (em.e_department = :subDepart AND li.l_department = :subDepart)
        OR
        -- Check if chief in Management
        (li.l_level = 'chief' AND em.e_department = 'Management')
        OR
        -- Check if Management and matching sub-department
        (em.e_department = 'Management' AND li.l_department IN (
            em.e_sub_department,
            em.e_sub_department2,
            em.e_sub_department3,
            em.e_sub_department4,
            em.e_sub_department5))
    )
    ORDER BY l_create_datetime DESC";

// รอ ผจก อนุมัติ
} else if ($status == 1) {
    // $sql = "SELECT * FROM leave_list WHERE Year(l_create_datetime) = '$year'
    // AND Month(l_create_datetime) = '$month'
    // AND l_department = '$depart'
    // AND l_level IN ('user','chief')
    // AND l_leave_id NOT IN (6, 7)
    // AND l_approve_status2 = '$status'
    // ORDER BY l_create_datetime DESC";
    $sql = "SELECT
    li.*,
    em.e_sub_department,
    em.e_sub_department2,
    em.e_sub_department3,
    em.e_sub_department4,
    em.e_sub_department5
FROM leave_list li
INNER JOIN employees em
    ON li.l_usercode = em.e_usercode
WHERE
     li.l_approve_status IN (0,2, 3, 6)
    AND li.l_level IN ('user', 'chief', 'leader')
    AND li.l_leave_id NOT IN (6, 7)
    AND Year(li.l_create_datetime) = :year
    AND Month(li.l_create_datetime) = :month
    AND li.l_approve_status2 = :status
    AND (
        -- Check for matching department or sub-department
        (em.e_department = :subDepart AND li.l_department = :subDepart)
        OR
        -- Check if chief in Management
        (li.l_level = 'chief' AND em.e_department = 'Management')
        OR
        -- Check if Management and matching sub-department
        (em.e_department = 'Management' AND li.l_department IN (
            em.e_sub_department,
            em.e_sub_department2,
            em.e_sub_department3,
            em.e_sub_department4,
            em.e_sub_department5))
    )
    ORDER BY l_create_datetime DESC";
} else if ($status == 4) {
    // $sql = "SELECT * FROM leave_list WHERE Year(l_create_datetime) = '$year'
    // AND Month(l_create_datetime) = '$month'
    // AND l_department = '$depart'
    // AND l_level IN ('user','chief')
    // AND l_leave_id NOT IN (6, 7)
    // AND l_approve_status2 = '$status'
    // ORDER BY l_create_datetime DESC";
    $sql = "SELECT
    li.*,
    em.e_sub_department,
    em.e_sub_department2,
    em.e_sub_department3,
    em.e_sub_department4,
    em.e_sub_department5
FROM leave_list li
INNER JOIN employees em
    ON li.l_usercode = em.e_usercode
WHERE
     li.l_approve_status IN (0,2, 3, 6)
    AND li.l_level IN ('user', 'chief', 'leader')
    AND li.l_leave_id NOT IN (6, 7)
    AND Year(li.l_create_datetime) = :year
    AND Month(li.l_create_datetime) = :month
    AND li.l_approve_status2 = :status
    AND (
        -- Check for matching department or sub-department
        (em.e_department = :subDepart AND li.l_department = :subDepart)
        OR
        -- Check if chief in Management
        (li.l_level = 'chief' AND em.e_department = 'Management')
        OR
        -- Check if Management and matching sub-department
        (em.e_department = 'Management' AND li.l_department IN (
            em.e_sub_department,
            em.e_sub_department2,
            em.e_sub_department3,
            em.e_sub_department4,
            em.e_sub_department5))
    )
    ORDER BY l_create_datetime DESC";
} else if ($status == 5) {
    // $sql = "SELECT * FROM leave_list WHERE Year(l_create_datetime) = '$year'
    // AND Month(l_create_datetime) = '$month'
    // AND l_department = '$depart'
    // AND l_level IN ('user','chief')
    // AND l_leave_id NOT IN (6, 7)
    // AND l_approve_status2 = '$status'
    // ORDER BY l_create_datetime DESC";
    $sql = "SELECT
    li.*,
    em.e_sub_department,
    em.e_sub_department2,
    em.e_sub_department3,
    em.e_sub_department4,
    em.e_sub_department5
FROM leave_list li
INNER JOIN employees em
    ON li.l_usercode = em.e_usercode
WHERE
     li.l_approve_status IN (0,2, 3, 6)
    AND li.l_level IN ('user', 'chief', 'leader')
    AND li.l_leave_id NOT IN (6, 7)
    AND Year(li.l_create_datetime) = :year
    AND Month(li.l_create_datetime) = :month
    AND li.l_approve_status2 = :status
    AND (
        -- Check for matching department or sub-department
        (em.e_department = :subDepart AND li.l_department = :subDepart)
        OR
        -- Check if chief in Management
        (li.l_level = 'chief' AND em.e_department = 'Management')
        OR
        -- Check if Management and matching sub-department
        (em.e_department = 'Management' AND li.l_department IN (
            em.e_sub_department,
            em.e_sub_department2,
            em.e_sub_department3,
            em.e_sub_department4,
            em.e_sub_department5))
    )
    ORDER BY l_create_datetime DESC";
} else {
    echo 'ไม่พบสถานะ';
}

// Prepare and execute the statement
$stmt = $conn->prepare($sql);
$stmt->bindParam(':subDepart', $subDepart, PDO::PARAM_STR);
$stmt->bindParam(':month', $month, PDO::PARAM_INT);
$stmt->bindParam(':year', $year, PDO::PARAM_INT); // Missing binding for year
$stmt->bindParam(':depart', $depart, PDO::PARAM_STR);

// Conditionally bind the status if it's not 'all'
if ($status != 'all') {
    $stmt->bindParam(':status', $status, PDO::PARAM_INT);
}

$stmt->execute();

// Fetch the results as an associative array
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Send the results as JSON
header('Content-Type: application/json');
echo json_encode($results);