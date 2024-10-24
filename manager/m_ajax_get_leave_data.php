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

// Prepare a SQL query to select leave data based on the status
$sql = "SELECT
    li.*,
    em.e_sub_department,
    em.e_sub_department2,
    em.e_sub_department3,
    em.e_sub_department4,
    em.e_sub_department5
FROM leave_list li
INNER JOIN employees em ON li.l_usercode = em.e_usercode
WHERE 
 li.l_level IN ('user', 'chief', 'leader')
AND (li.l_leave_id <> 6 AND li.l_leave_id <> 7)
AND Year(li.l_create_datetime) = :year
AND Month(li.l_create_datetime) = :month
AND (
    (em.e_department = :subDepart AND li.l_department = :subDepart)
    OR (li.l_department = :subDepart2)
    OR (li.l_department = :subDepart3)
    OR (li.l_department = :subDepart4)
    OR (li.l_department = :subDepart5)
)";

// Conditionally add filters based on status
if ($status != 'all') {
    $sql .= " AND li.l_approve_status = 2 AND li.l_approve_status2 = :status";
}


$sql .= " ORDER BY l_create_datetime DESC";

// Prepare and execute the statement
$stmt = $conn->prepare($sql);
$stmt->bindParam(':subDepart', $subDepart, PDO::PARAM_STR);
$stmt->bindParam(':subDepart2', $subDepart2, PDO::PARAM_STR);
$stmt->bindParam(':subDepart3', $subDepart3, PDO::PARAM_STR);
$stmt->bindParam(':subDepart4', $subDepart4, PDO::PARAM_STR);
$stmt->bindParam(':subDepart5', $subDepart5, PDO::PARAM_STR);
$stmt->bindParam(':month', $month, PDO::PARAM_INT);
$stmt->bindParam(':year', $year, PDO::PARAM_INT);

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