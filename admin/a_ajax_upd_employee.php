<?php

session_start();

require '../connect.php';
date_default_timezone_set('Asia/Bangkok');
$updDate = date("Y-m-d H:i:s");

$usercode      = $_POST['usercode'];
$name          = $_POST['name'];
$department    = $_POST['department'];
$level         = $_POST['level'];
$email         = $_POST['email'];
$token         = $_POST['token'];
$phone         = $_POST['phone'];
$username      = $_POST['username'];
$password      = $_POST['password'];
$updUsername   = $_POST['updUsername'];
$personal      = $_POST['personal'];
$personalNo    = $_POST['personalNo'];
$sick          = $_POST['sick'];
$sickWork      = $_POST['sickWork'];
$annual        = $_POST['annual'];
$other         = $_POST['other'];
$workplace     = $_POST['workplace'];
$subDepart     = $_POST['subDepart'];
$subDepart2    = $_POST['subDepart2'];
$subDepart3    = $_POST['subDepart3'];
$subDepart4    = $_POST['subDepart4'];
$subDepart5    = $_POST['subDepart5'];
$workStartDate = $_POST['workStartDate'];

try {
    $sql = "UPDATE employees
            SET e_name = :name,
                e_department = :department,
                e_level = :level,
                e_email = :email,
                e_phone = :phone,
                e_username = :username,
                e_password = :password,
                e_upd_name = :updUsername,
                e_upd_datetime = :updDate,
                e_leave_personal = :personal,
                e_leave_personal_no = :personalNo,
                e_leave_sick = :sick,
                e_leave_sick_work = :sickWork,
                e_leave_annual = :annual,
                e_other = :other,
                e_workplace = :workplace,
                e_sub_department = :subDepart,
                e_sub_department2 = :subDepart2,
                e_sub_department3 = :subDepart3,
                e_sub_department4 = :subDepart4,
                e_sub_department5 = :subDepart5,
                e_token = :token,
                e_work_start_date = :workStartDate
            WHERE e_usercode = :usercode";

    $stmt = $conn->prepare($sql);

    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':department', $department);
    $stmt->bindParam(':level', $level);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password', $password);
    $stmt->bindParam(':updUsername', $updUsername);
    $stmt->bindParam(':updDate', $updDate);
    $stmt->bindParam(':personal', $personal);
    $stmt->bindParam(':personalNo', $personalNo);
    $stmt->bindParam(':sick', $sick);
    $stmt->bindParam(':sickWork', $sickWork);
    $stmt->bindParam(':annual', $annual);
    $stmt->bindParam(':other', $other);
    $stmt->bindParam(':workplace', $workplace);
    $stmt->bindParam(':usercode', $usercode);
    $stmt->bindParam(':subDepart', $subDepart);
    $stmt->bindParam(':subDepart2', $subDepart2);
    $stmt->bindParam(':subDepart3', $subDepart3);
    $stmt->bindParam(':subDepart4', $subDepart4);
    $stmt->bindParam(':subDepart5', $subDepart5);
    $stmt->bindParam(':token', $token);
    $stmt->bindParam(':workStartDate', $workStartDate);

    $stmt->execute();

    echo 'Employee data updated successfully.';
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
}