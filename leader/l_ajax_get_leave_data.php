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

// All -----------------------------------------------------------------------
if ($status == 'all') {
    $sql = "SELECT
    li.*,
    em.*
FROM leave_list li
INNER JOIN employees em
    ON li.l_usercode = em.e_usercode
WHERE
    li.l_approve_status IN (0, 1, 2, 3, 6)
    AND li.l_level IN ('user')
    AND li.l_leave_id NOT IN (6, 7)
    AND YEAR(li.l_leave_end_date) = :year";

    if ($month != "All") {
        $sql .= " AND Month(li.l_leave_end_date) = :month ";
    }

    $sql .= " AND (
        (em.e_sub_department = :subDepart AND li.l_department = :depart)
        OR (em.e_sub_department2 = :subDepart2 AND li.l_department = :depart)
        OR (em.e_sub_department3 = :subDepart3 AND li.l_department = :depart)
        OR (em.e_sub_department4 = :subDepart4 AND li.l_department = :depart)
        OR (em.e_sub_department5 = :subDepart5 AND li.l_department = :depart)
    )
    ORDER BY li.l_create_datetime DESC";
//     $sql = "SELECT li.*,
    //     em.e_sub_department,
    //     em.e_sub_department2,
    //     em.e_sub_department3,
    //     em.e_sub_department4,
    //     em.e_sub_department5
    // FROM leave_list li
    // INNER JOIN employees em
    //     ON li.l_usercode = em.e_usercode
    //     AND (CASE
    //             WHEN em.e_sub_department IS NULL OR em.e_sub_department = '' THEN li.l_department = :depart
    //             ELSE em.e_sub_department = :subDepart
    //          END)
    //     AND Month(li.l_create_datetime) = :month
    //     AND li.l_level = 'user'
    //     -- AND l_approve_status2 = 1
    //     AND (li.l_leave_id <> 6 AND li.l_leave_id <> 7)
    //     ORDER BY li.l_create_datetime DESC";
}
// รออนุมัติ -----------------------------------------------------------------------
else if ($status == 0) {
    $sql = "SELECT
    li.*,
    em.*
FROM leave_list li
INNER JOIN employees em
    ON li.l_usercode = em.e_usercode
WHERE
    li.l_approve_status = 0
    AND li.l_level IN ('user')
    AND li.l_leave_id NOT IN (6, 7)
    AND YEAR(li.l_leave_end_date) = :year";

    if ($month != "All") {
        $sql .= " AND Month(li.l_leave_end_date) = :month ";
    }

    $sql .= " AND (
        (em.e_sub_department = :subDepart AND li.l_department = :depart)
        OR (em.e_sub_department2 = :subDepart2 AND li.l_department = :depart)
        OR (em.e_sub_department3 = :subDepart3 AND li.l_department = :depart)
        OR (em.e_sub_department4 = :subDepart4 AND li.l_department = :depart)
        OR (em.e_sub_department5 = :subDepart5 AND li.l_department = :depart)
    )
    ORDER BY li.l_create_datetime DESC";
}
// อนุมัติ -----------------------------------------------------------------------
else if ($status == 2) {
    $sql = "SELECT
    li.*,
    em.*
FROM leave_list li
INNER JOIN employees em
    ON li.l_usercode = em.e_usercode
WHERE
    li.l_approve_status = 2
    AND li.l_level IN ('user')
    AND li.l_leave_id NOT IN (6, 7)
    AND YEAR(li.l_leave_end_date) = :year";

    if ($month != "All") {
        $sql .= " AND Month(li.l_leave_end_date) = :month ";
    }

    $sql .= " AND (
        (em.e_sub_department = :subDepart AND li.l_department = :depart)
        OR (em.e_sub_department2 = :subDepart2 AND li.l_department = :depart)
        OR (em.e_sub_department3 = :subDepart3 AND li.l_department = :depart)
        OR (em.e_sub_department4 = :subDepart4 AND li.l_department = :depart)
        OR (em.e_sub_department5 = :subDepart5 AND li.l_department = :depart)
    )
    ORDER BY li.l_create_datetime DESC";
}
// ไม่อนุมัติ -----------------------------------------------------------------------
else if ($status == 3) {
    $sql = "SELECT
    li.*,
    em.*
FROM leave_list li
INNER JOIN employees em
    ON li.l_usercode = em.e_usercode
WHERE
    li.l_approve_status = 3
    AND li.l_level IN ('user')
    AND li.l_leave_id NOT IN (6, 7)
    AND YEAR(li.l_leave_end_date) = :year";

    if ($month != "All") {
        $sql .= " AND Month(li.l_leave_end_date) = :month ";
    }

    $sql .= " AND (
        (em.e_sub_department = :subDepart AND li.l_department = :depart)
        OR (em.e_sub_department2 = :subDepart2 AND li.l_department = :depart)
        OR (em.e_sub_department3 = :subDepart3 AND li.l_department = :depart)
        OR (em.e_sub_department4 = :subDepart4 AND li.l_department = :depart)
        OR (em.e_sub_department5 = :subDepart5 AND li.l_department = :depart)
    )
    ORDER BY li.l_create_datetime DESC";
} else {
    echo 'ไม่พบสถานะ';
}

$stmt = $conn->prepare($sql);
$stmt->bindParam(':subDepart', $subDepart, PDO::PARAM_STR);
if ($month != "ALL") {
    $stmt->bindParam(':month', $month, PDO::PARAM_INT);

}
$stmt->bindParam(':depart', $depart, PDO::PARAM_STR);
$stmt->bindParam(':subDepart2', $subDepart2);
$stmt->bindParam(':subDepart3', $subDepart3);
$stmt->bindParam(':subDepart4', $subDepart4);
$stmt->bindParam(':subDepart5', $subDepart5);
$stmt->bindParam(':year', $year);

if ($status != 'all') {
    $stmt->bindParam(':status', $status, PDO::PARAM_INT);
}

$stmt->execute();

// Fetch the results as an associative array
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Send the results as JSON
echo json_encode($results);