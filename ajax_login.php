<?php
session_start();
date_default_timezone_set('Asia/Bangkok'); // Set the timezone to Asia/Bangkok

include 'connect.php';

$userCode = $_POST['userCode'];
$passWord = $_POST['passWord'];
$statusLog = 1;

$sql = "SELECT * FROM session WHERE s_usercode ='$userCode' AND s_password='$passWord' ";
$result = $conn->query($sql);

if ($result->rowCount() > 0) {
    // อัปเดตเวลาที่เข้าสู่ระบบ
    $loginTime = date('Y-m-d H:i:s');
    $updateSql = "UPDATE session SET s_login_datetime = '$loginTime', s_log_status = '$statusLog' WHERE s_usercode ='$userCode'";
    $conn->query($updateSql);

    // กำหนด session สำหรับ usercode
    $_SESSION['s_usercode'] = $userCode;

    // ตรวจสอบระดับการเข้าใช้งาน (admin/user)
    $row = $result->fetch(PDO::FETCH_ASSOC);
    if ($row['s_level'] == 'admin') {
        echo 'admin';
    } elseif ($row['s_level'] == 'user') {
        echo 'user';
    } elseif ($row['s_level'] == 'leader') {
        echo 'leader';
    } elseif ($row['s_level'] == 'chief') {
        echo 'chief';
    } elseif ($row['s_level'] == 'assisManager') {
        echo 'assisManager';
    } elseif ($row['s_level'] == 'manager') {
        echo 'manager';
    } elseif ($row['s_level'] == 'manager2') {
        echo 'manager2';
    } elseif ($row['s_level'] == 'GM') {
        echo 'GM';
    }
} else {
    echo 'error';
}
// }

$conn = null;