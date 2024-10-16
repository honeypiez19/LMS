<?php
// Include the database connection file
include '../connect.php';

$status = $_GET['status'];
$month = $_GET['month'];
$depart = $_GET['depart'];
$subDepart = $_GET['subDepart'];

// All -----------------------------------------------------------------------
if ($status == 'all') {
    $sql = "SELECT li.*,
    em.e_sub_department,
    em.e_sub_department2,
    em.e_sub_department3,
    em.e_sub_department4,
    em.e_sub_department5
FROM leave_list li
INNER JOIN employees em
    ON li.l_usercode = em.e_usercode
    AND (CASE
            WHEN em.e_sub_department IS NULL OR em.e_sub_department = '' THEN li.l_department = :depart
            ELSE em.e_sub_department = :subDepart
         END)
    AND Month(li.l_create_datetime) = :month
    AND li.l_level = 'user'
    -- AND l_approve_status2 = 1
    AND (li.l_leave_id <> 6 AND li.l_leave_id <> 7)
    ORDER BY li.l_create_datetime DESC";
}
// รออนุมัติ -----------------------------------------------------------------------
else if ($status == 0) {
    $sql = "SELECT li.*,
    em.e_sub_department,
    em.e_sub_department2,
    em.e_sub_department3,
    em.e_sub_department4,
    em.e_sub_department5
FROM leave_list li
INNER JOIN employees em
    ON li.l_usercode = em.e_usercode
    AND (CASE
            WHEN em.e_sub_department IS NULL OR em.e_sub_department = '' THEN li.l_department = :depart
            ELSE em.e_sub_department = :subDepart
         END)
    AND Month(li.l_create_datetime) = :month
    AND li.l_level = 'user'
    AND li.l_approve_status = :status
    AND (li.l_leave_id <> 6 AND li.l_leave_id <> 7)
    ORDER BY li.l_create_datetime DESC";
}
// อนุมัติ -----------------------------------------------------------------------
else if ($status == 2) {
    $sql = "SELECT li.*,
    em.e_sub_department,
    em.e_sub_department2,
    em.e_sub_department3,
    em.e_sub_department4,
    em.e_sub_department5
FROM leave_list li
INNER JOIN employees em
    ON li.l_usercode = em.e_usercode
    AND (CASE
            WHEN em.e_sub_department IS NULL OR em.e_sub_department = '' THEN li.l_department = :depart
            ELSE em.e_sub_department = :subDepart
         END)
    AND Month(li.l_create_datetime) = :month
    AND li.l_level = 'user'
    AND li.l_approve_status = :status
    AND (li.l_leave_id <> 6 AND li.l_leave_id <> 7)
    ORDER BY li.l_create_datetime DESC";
}
// ไม่อนุมัติ -----------------------------------------------------------------------
else if ($status == 3) {
    $sql = "SELECT li.*,
    em.e_sub_department,
    em.e_sub_department2,
    em.e_sub_department3,
    em.e_sub_department4,
    em.e_sub_department5
FROM leave_list li
INNER JOIN employees em
    ON li.l_usercode = em.e_usercode
    AND (CASE
            WHEN em.e_sub_department IS NULL OR em.e_sub_department = '' THEN li.l_department = :depart
            ELSE em.e_sub_department = :subDepart
         END)
    AND Month(li.l_create_datetime) = :month
    AND li.l_level = 'user'
    AND li.l_approve_status = :status
    AND (li.l_leave_id <> 6 AND li.l_leave_id <> 7)
    ORDER BY li.l_create_datetime DESC";
} else {
    echo 'ไม่พบสถานะ';
}

$stmt = $conn->prepare($sql);
$stmt->bindParam(':subDepart', $subDepart, PDO::PARAM_STR);
$stmt->bindParam(':month', $month, PDO::PARAM_INT);
$stmt->bindParam(':depart', $depart, PDO::PARAM_STR);

if ($status != 'all') {
    $stmt->bindParam(':status', $status, PDO::PARAM_INT);
}

$stmt->execute();

// Fetch the results as an associative array
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Send the results as JSON
echo json_encode($results);