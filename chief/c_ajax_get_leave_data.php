<?php
// Include the database connection file
include '../connect.php';

$status = $_GET['status'];
$month = $_GET['month'];
$year = $_GET['year'];
$subDepart = $_GET['subDepart'];

// Base SQL query
$sql = "SELECT li.*, em.e_sub_department, em.e_sub_department2, em.e_sub_department3, em.e_sub_department4, em.e_sub_department5
FROM leave_list li
INNER JOIN employees em ON li.l_usercode = em.e_usercode
WHERE em.e_sub_department = :subDepart
AND Year(li.l_leave_end_date) = :year";

// Add condition for month if selected
if ($month != "All") {
    $sql .= " AND Month(li.l_leave_end_date) = :month";
}

// Add conditions based on status
if ($status != 'all') {
    $sql .= " AND li.l_approve_status = :status";
}

// Add other fixed conditions
$sql .= " AND li.l_level = 'user'
AND li.l_leave_id NOT IN (6,7)
ORDER BY li.l_create_datetime DESC";

// Prepare the statement
$stmt = $conn->prepare($sql);

// Bind parameters
$stmt->bindParam(':subDepart', $subDepart, PDO::PARAM_STR);
$stmt->bindParam(':year', $year, PDO::PARAM_INT);
if ($month != "All") {
    $stmt->bindParam(':month', $month, PDO::PARAM_INT);
}
if ($status != 'all') {
    $stmt->bindParam(':status', $status, PDO::PARAM_INT);
}

// Execute the query
$stmt->execute();

// Fetch the results as an associative array
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Send the results as JSON
echo json_encode($results);