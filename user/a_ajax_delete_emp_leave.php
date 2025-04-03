<?php
session_start();
include '../connect.php';

// Get the values from the AJAX request
$usercode       = $_POST['l_usercode'];
$leaveId        = $_POST['l_leave_id'];
$createDatetime = $_POST['l_create_datetime'];

// Update the leave status to 1 (cancelled) instead of deleting
$sql = "UPDATE leave_list SET l_leave_status = 1 WHERE l_usercode = :usercode AND l_leave_id = :leaveId AND l_create_datetime = :createDatetime";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':usercode', $usercode);
$stmt->bindParam(':leaveId', $leaveId);
$stmt->bindParam(':createDatetime', $createDatetime);

$result = $stmt->execute();

// Return the result
if ($result) {
    echo "success";
} else {
    echo "error";
}