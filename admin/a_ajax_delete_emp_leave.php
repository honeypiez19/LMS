<?php
session_start();
date_default_timezone_set('Asia/Bangkok');

require '../connect.php'; 
    // Get the POST data
    $usercode = $_POST['l_usercode'];
    $leaveId = $_POST['l_leave_id'];
    $createDatetime = $_POST['l_create_datetime'];

    $sql = "UPDATE leave_list SET l_leave_status = 1 WHERE l_usercode = :usercode AND l_leave_id = :leaveId AND l_create_datetime = :createDatetime";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':usercode', $usercode);
    $stmt->bindParam(':leaveId', $leaveId, PDO::PARAM_INT);
    $stmt->bindParam(':createDatetime', $createDatetime);

    if ($stmt->execute()) {
        echo "Leave request canceled successfully.";
    } else {
         echo "Error canceling leave request.";
    }
?>