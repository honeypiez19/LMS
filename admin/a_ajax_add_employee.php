<?php
require '../connect.php';
date_default_timezone_set('Asia/Bangkok');

$usercode      = $_POST['add_usercode'];
$username      = $_POST['add_username'];
$password      = $_POST['add_password'];
$name          = $_POST['add_name'];
$yearexp       = $_POST['add_yearexp'];
$add_level     = $_POST['add_level'];
$email         = $_POST['add_email'];
$phone         = $_POST['add_phone'];
$personal      = $_POST['add_personal'];
$personal_no   = $_POST['add_personal_no'];
$sick          = $_POST['add_sick'];
$sick_work     = $_POST['add_sick_work'];
$annual        = $_POST['add_annual'];
$other         = $_POST['add_other'];
$token         = $_POST['add_token'];
$workplace     = $_POST['add_workplace'];
$workStartDate = $_POST['add_work_start_date'];
$formattedDate = date('Y-m-d', strtotime($workStartDate));

$department = $_POST['add_department'];
$subDepart  = $_POST['add_subdepart'];
$subDepart2 = $_POST['add_subdepart2'];
$subDepart3 = $_POST['add_subdepart3'];
$subDepart4 = $_POST['add_subdepart4'];
$subDepart5 = $_POST['add_subdepart5'];

$status      = 0;
$addUsername = $_POST['addUsername'];
$addDatetime = date('Y-m-d H:i:s');

$department_name = [
    1  => 'Management',
    2  => 'Office',
    3  => 'CAD1',
    4  => 'CAD2',
    5  => 'CAM',
    6  => 'RD',
    7  => 'PC',
    8  => 'QC',
    9  => 'MC',
    10 => 'FN',
    11 => 'Modeling',
    12 => 'Design',
];

$add_department_name = $department_name[$department] ?? '';
$add_subdepart_name  = $department_name[$subDepart] ?? '';
$add_subdepart2_name = $department_name[$subDepart2] ?? '';
$add_subdepart3_name = $department_name[$subDepart3] ?? '';
$add_subdepart4_name = $department_name[$subDepart4] ?? '';
$add_subdepart5_name = $department_name[$subDepart5] ?? '';

$level_name = [
    1 => 'user',
    2 => 'leader',
    3 => 'chief',
    4 => 'assisManager',
    5 => 'manager',
    6 => 'manager2',
    7 => 'GM',
    8 => 'admin',
    9 => 'subLeader',

];

$add_level_name = $level_name[$add_level] ?? 'Unknown';

$workplace_name = [
    1 => 'Korat',
    2 => 'Bang Phli',
];

$add_workplace_name = $workplace_name[$workplace] ?? 'Unknown';

$sql = "INSERT INTO employees (e_usercode, e_username, e_password, e_name, e_department, e_yearexp, e_level, e_email, e_phone, e_leave_personal,
e_leave_personal_no, e_leave_sick, e_leave_sick_work,
e_leave_annual, e_other, e_status, e_token, e_workplace, e_work_start_date, e_sub_department, e_sub_department2, e_sub_department3,
e_sub_department4, e_sub_department5, e_add_name, e_add_datetime)
VALUES (:usercode, :username, :password, :name, :department, :yearexp, :add_level, :email, :phone, :personal, :personal_no, :sick, :sick_work, :annual, :other ,
:status, :token, :workplace, :formattedDate, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5
, :addUsername, :addDatetime)";
$stmt = $conn->prepare($sql);

$stmt->bindParam(':usercode', $usercode);
$stmt->bindParam(':username', $username);
$stmt->bindParam(':password', $password);
$stmt->bindParam(':name', $name);
$stmt->bindParam(':department', $add_department_name);
$stmt->bindParam(':yearexp', $yearexp);
$stmt->bindParam(':add_level', $add_level_name);
$stmt->bindParam(':email', $email);
$stmt->bindParam(':phone', $phone);
$stmt->bindParam(':personal', $personal);
$stmt->bindParam(':personal_no', $personal_no);
$stmt->bindParam(':sick', $sick);
$stmt->bindParam(':sick_work', $sick_work);
$stmt->bindParam(':annual', $annual);
$stmt->bindParam(':other', $other);
$stmt->bindParam(':status', $status);
$stmt->bindParam(':token', $token);
$stmt->bindParam(':workplace', $add_workplace_name);
$stmt->bindParam(':formattedDate', $formattedDate);
$stmt->bindParam(':subDepart', $add_subdepart_name);
$stmt->bindParam(':subDepart2', $add_subdepart2_name);
$stmt->bindParam(':subDepart3', $add_subdepart3_name);
$stmt->bindParam(':subDepart4', $add_subdepart4_name);
$stmt->bindParam(':subDepart5', $add_subdepart5_name);
$stmt->bindParam(':addUsername', $addUsername);
$stmt->bindParam(':addDatetime', $addDatetime);

if ($stmt->execute()) {
    $session_sql = "INSERT INTO session (s_usercode, s_username, s_password, s_name, s_department, s_level, s_status, s_workplace)
    VALUES (:usercode, :username, :password, :name, :department, :add_level, :status, :workplace)";
    $session_stmt = $conn->prepare($session_sql);

    $session_stmt->bindParam(':usercode', $usercode);
    $session_stmt->bindParam(':username', $username);
    $session_stmt->bindParam(':password', $password);
    $session_stmt->bindParam(':name', $name);
    $session_stmt->bindParam(':department', $add_department_name);
    $session_stmt->bindParam(':add_level', $add_level_name);
    $session_stmt->bindParam(':status', $status);
    $session_stmt->bindParam(':workplace', $add_workplace_name);

    if ($session_stmt->execute()) {
        echo "พนักงานถูกเพิ่มเรียบร้อยแล้ว และข้อมูลถูกบันทึกลง session";
    } else {
        echo "พนักงานถูกเพิ่มเรียบร้อยแล้ว แต่เกิดข้อผิดพลาดในการบันทึกข้อมูลลง session";
    }
} else {
    echo "เกิดข้อผิดพลาดในการเพิ่มพนักงาน";
}