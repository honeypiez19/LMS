<?php
session_start();
date_default_timezone_set('Asia/Bangkok');

include '../connect.php';
if (!isset($_SESSION['s_usercode'])) {
    header('Location: ../login.php');
    exit();
}

$userCode = $_SESSION['s_usercode'];
// echo $userCode;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หน้าหลัก</title>

    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <link rel="icon" href="../logo/logo.png">
    <link rel="stylesheet" href="../css/jquery-ui.css">
    <link rel="stylesheet" href="../css/flatpickr.min.css">

    <script src="../js/jquery-3.7.1.min.js"></script>
    <script src="../js/jquery-ui.min.js"></script>
    <script src="../js/flatpickr"></script>
    <script src="../js/sweetalert2.all.min.js"></script>

    <script src="../js/fontawesome.js"></script>

    <style>
    @media (max-width: 576px) {
        .filter-card {
            /* มีขนาด flex ความกว้าง 100% แต่ไม่สามารถย่อหรือขยายได้ */
            flex: 0 0 100%;
            /* ความกว้าง 100% ของพื้นที่ที่หน้าจอมีความกว้างไม่เกิน 576px */
            max-width: 100%;
        }
    }
    </style>
</head>

<body>
    <?php include 'manager_navbar.php'?>

    <?php
// มาสาย --------------------------------------------------------------------------------------------
$sql_check_late = "SELECT l_leave_start_date, l_leave_start_time, l_leave_end_time
FROM leave_list
WHERE l_usercode = :userCode
AND l_leave_id = 7
AND l_approve_status = 2
AND l_approve_status2 = 1";

$stmt_check_late = $conn->prepare($sql_check_late);
$stmt_check_late->bindParam(':userCode', $userCode);
$stmt_check_late->execute();

$late_entries = array();
while ($row_late = $stmt_check_late->fetch(PDO::FETCH_ASSOC)) {
    $late_date = date('d/m/Y', strtotime($row_late['l_leave_start_date']));
    $start_time = date('H:i', strtotime($row_late['l_leave_start_time']));
    $end_time = date('H:i', strtotime($row_late['l_leave_end_time']));
    $late_entries[] = "วันที่ $late_date เวลา $start_time - $end_time";
}

$late_entries_list = implode(', ', $late_entries);

if (!empty($late_entries_list)) {
    echo '<div class="alert alert-danger d-flex align-items-center" role="alert">
<i class="fa-solid fa-triangle-exclamation me-2"></i>
<span>คุณมาสาย' . $late_entries_list . ' กรุณาตรวจสอบ</span>
<button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
</div>';
}

// มีใบลาของพนักงาน --------------------------------------------------------------------------------------------
$checkSubDepart = $subDepart;
$checkSubDepart2 = $subDepart2;
$checkSubDepart3 = $subDepart3;
$checkSubDepart4 = $subDepart4;
$checkSubDepart5 = $subDepart5;

$sql_check_leave = "SELECT Distinct
    li.l_username,
    li.l_name,
    li.l_department,
    em.e_department,
    em.e_sub_department,
    em.e_sub_department2,
    em.e_sub_department3,
    em.e_sub_department4,
    em.e_sub_department5
FROM leave_list li
INNER JOIN employees em
    ON li.l_usercode = em.e_usercode
WHERE
    li.l_approve_status IN (2,3,6)
    AND li.l_leave_status = 0
    AND li.l_approve_status2 = 1
    AND li.l_level IN ('user', 'chief', 'leader', 'admin')
    AND li.l_leave_id NOT IN (6,7)";

// Add conditions based on department
if ($checkSubDepart === "Office" || $checkSubDepart2 === "Management") {
    $sql_check_leave .= " AND (
        em.e_department = :checkSubDepart OR li.l_department = :checkSubDepart
        OR li.l_department = :checkSubDepart2
        OR (li.l_department = :checkSubDepart AND em.e_sub_department = 'AC')
    )";
} else {
    $sql_check_leave .= " AND (
        em.e_department = :subDepart OR li.l_department = :subDepart
        OR li.l_department = :subDepart2
        OR li.l_department = :subDepart3
        OR li.l_department = :subDepart4
        OR li.l_department = :subDepart5
    )";
}
$sql_check_leave .= " ORDER BY li.l_name";

$stmt_check_leave = $conn->prepare($sql_check_leave);

if ($checkSubDepart === "Office" || $checkSubDepart2 === "Management") {
    $stmt_check_leave->bindParam(':checkSubDepart', $checkSubDepart, PDO::PARAM_STR);
    $stmt_check_leave->bindParam(':checkSubDepart2', $checkSubDepart2, PDO::PARAM_STR);
} else {
    $stmt_check_leave->bindParam(':subDepart', $subDepart, PDO::PARAM_STR);
    $stmt_check_leave->bindParam(':subDepart2', $subDepart2, PDO::PARAM_STR);
    $stmt_check_leave->bindParam(':subDepart3', $subDepart3, PDO::PARAM_STR);
    $stmt_check_leave->bindParam(':subDepart4', $subDepart4, PDO::PARAM_STR);
    $stmt_check_leave->bindParam(':subDepart5', $subDepart5, PDO::PARAM_STR);
}

$stmt_check_leave->execute();

$employee_names = array();
while ($row_leave = $stmt_check_leave->fetch(PDO::FETCH_ASSOC)) {
    $employee_names[] = $row_leave['l_name'];
}

$employee_list = implode(', ', $employee_names);

// If employee_list is not empty, display the alert
if (!empty($employee_list)) {
    echo '<div class="alert alert-warning d-flex align-items-center" role="alert">
    <i class="fa-solid fa-circle-exclamation me-2"></i>
    <span>มีใบลาของพนักงาน ' . htmlspecialchars($employee_list, ENT_QUOTES, 'UTF-8') . ' กรุณาตรวจสอบ</span>
    <button type="button" class="ms-2 btn btn-primary button-shadow" onclick="window.location.href=\'manager_leave_request.php\'">ตรวจสอบใบลา</button>
    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';
}

// พนักงานยกเลิกใบลา --------------------------------------------------------------------------------------------
$checkSubDepart = $subDepart;
$checkSubDepart2 = $subDepart2;
$checkSubDepart3 = $subDepart3;
$checkSubDepart4 = $subDepart4;
$checkSubDepart5 = $subDepart5;

$sql_cancel_leave = "SELECT Distinct
    li.l_username,
    li.l_name,
    li.l_department,
    em.e_department,
    em.e_sub_department,
    em.e_sub_department2,
    em.e_sub_department3,
    em.e_sub_department4,
    em.e_sub_department5
FROM leave_list li
INNER JOIN employees em
    ON li.l_usercode = em.e_usercode
WHERE
    li.l_approve_status IN (2,3,6)
    AND li.l_leave_status = 1
    AND li.l_approve_status2 = 1
    AND li.l_level IN ('user', 'chief', 'leader', 'admin')
    AND li.l_leave_id NOT IN (6,7)";

// Add conditions based on department
if ($checkSubDepart === "Office" || $checkSubDepart2 === "Management") {
    $sql_cancel_leave .= " AND (
        em.e_department = :checkSubDepart OR li.l_department = :checkSubDepart
        OR li.l_department = :checkSubDepart2
        OR (li.l_department = :checkSubDepart AND em.e_sub_department = 'AC')
    )";
} else {
    $sql_cancel_leave .= " AND (
        em.e_department = :subDepart OR li.l_department = :subDepart
        OR li.l_department = :subDepart2
        OR li.l_department = :subDepart3
        OR li.l_department = :subDepart4
        OR li.l_department = :subDepart5
    )";
}
$sql_cancel_leave .= " ORDER BY li.l_name";

// Prepare the SQL statement
$stmt_cancel_leave = $conn->prepare($sql_cancel_leave);

// Bind parameters
if ($checkSubDepart === "Office" || $checkSubDepart2 === "Management") {
    $stmt_cancel_leave->bindParam(':checkSubDepart', $checkSubDepart, PDO::PARAM_STR);
    $stmt_cancel_leave->bindParam(':checkSubDepart2', $checkSubDepart2, PDO::PARAM_STR);
} else {
    $stmt_cancel_leave->bindParam(':subDepart', $subDepart, PDO::PARAM_STR);
    $stmt_cancel_leave->bindParam(':subDepart2', $subDepart2, PDO::PARAM_STR);
    $stmt_cancel_leave->bindParam(':subDepart3', $subDepart3, PDO::PARAM_STR);
    $stmt_cancel_leave->bindParam(':subDepart4', $subDepart4, PDO::PARAM_STR);
    $stmt_cancel_leave->bindParam(':subDepart5', $subDepart5, PDO::PARAM_STR);
}

// Execute the query
$stmt_cancel_leave->execute();

// Fetch results
$employee_names = [];
while ($row_leave = $stmt_cancel_leave->fetch(PDO::FETCH_ASSOC)) {
    $employee_names[] = $row_leave['l_name'];
}

// Generate the list of employees
$employee_list = implode(', ', $employee_names);

// Display the alert if there are canceled leaves
if (!empty($employee_list)) {
    echo '<div class="alert alert-danger d-flex align-items-center" role="alert">
    <i class="fa-solid fa-circle-exclamation me-2"></i>
    <span>มีการยกเลิกใบลาของ ' . htmlspecialchars($employee_list) . ' กรุณาตรวจสอบ</span>
    <button type="button" class="ms-2 btn btn-primary button-shadow" onclick="window.location.href=\'manager_leave_request.php\'">ตรวจสอบใบลา</button>
    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';
}

// มีพนักงานมาสาย --------------------------------------------------------------------------------------------
$sql_check_leave_id_7 = "SELECT Distinct
    li.l_username,
    li.l_name,
    li.l_department,
    em.e_department,
    em.e_sub_department,
    em.e_sub_department2,
    em.e_sub_department3,
    em.e_sub_department4,
    em.e_sub_department5
FROM leave_list li
INNER JOIN employees em
    ON li.l_usercode = em.e_usercode
WHERE
    li.l_approve_status IN (2,3,6)
    -- AND li.l_leave_status = 1
    AND li.l_approve_status2 = 1
    AND li.l_level IN ('user', 'chief', 'leader', 'admin')
    AND li.l_leave_id IN (6,7)";

// Add conditions based on department
if ($checkSubDepart === "Office" || $checkSubDepart2 === "Management") {
    $sql_check_leave_id_7 .= " AND (
        em.e_department = :checkSubDepart OR li.l_department = :checkSubDepart
        OR li.l_department = :checkSubDepart2
        OR (li.l_department = :checkSubDepart AND em.e_sub_department = 'AC')
    )";
} else {
    $sql_check_leave_id_7 .= " AND (
        em.e_department = :subDepart OR li.l_department = :subDepart
        OR li.l_department = :subDepart2
        OR li.l_department = :subDepart3
        OR li.l_department = :subDepart4
        OR li.l_department = :subDepart5
    )";
}
$sql_check_leave_id_7 .= " ORDER BY li.l_name";

$stmt_check_leave_id_7 = $conn->prepare($sql_check_leave_id_7);
// Bind parameters
if ($checkSubDepart === "Office" || $checkSubDepart2 === "Management") {
    $stmt_check_leave_id_7->bindParam(':checkSubDepart', $checkSubDepart, PDO::PARAM_STR);
    $stmt_check_leave_id_7->bindParam(':checkSubDepart2', $checkSubDepart2, PDO::PARAM_STR);
} else {
    $stmt_check_leave_id_7->bindParam(':subDepart', $subDepart, PDO::PARAM_STR);
    $stmt_check_leave_id_7->bindParam(':subDepart2', $subDepart2, PDO::PARAM_STR);
    $stmt_check_leave_id_7->bindParam(':subDepart3', $subDepart3, PDO::PARAM_STR);
    $stmt_check_leave_id_7->bindParam(':subDepart4', $subDepart4, PDO::PARAM_STR);
    $stmt_check_leave_id_7->bindParam(':subDepart5', $subDepart5, PDO::PARAM_STR);
}
$stmt_check_leave_id_7->execute();

if ($stmt_check_leave_id_7->rowCount() > 0) {
    $employee_names_id_7 = array();
    while ($row_leave_id_7 = $stmt_check_leave_id_7->fetch(PDO::FETCH_ASSOC)) {
        $employee_names_id_7[] = $row_leave_id_7['l_name'];
    }

    $employee_list_id_7 = implode(', ', $employee_names_id_7);

    echo '<div class="alert alert-danger d-flex align-items-center" role="alert">
<i class="fa-solid fa-circle-exclamation me-2"></i>
<span> ' . $employee_list_id_7 . ' มาสาย' . ' กรุณาตรวจสอบ</span>
<button type="button" class="ms-2 btn btn-primary button-shadow" onclick="window.location.href=\'manager_employee_attendance.php\'">ตรวจสอบการมาสาย</button>
<button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
</div>';
}

// รวมสถิติการลาและมาสายของตัวเอง --------------------------------------------------------------------------------------------
$sql_leave = "SELECT
    SUM(
        CASE
            WHEN DATEDIFF(l_leave_end_date, l_leave_start_date) = 0 THEN
                CASE
                    WHEN TIME_TO_SEC(TIMEDIFF(l_leave_end_time, l_leave_start_time)) = 8 * 3600 + 40 * 60 THEN 8
                    WHEN TIME_TO_SEC(TIMEDIFF(l_leave_end_time, l_leave_start_time)) > 4 * 3600 THEN
                        ROUND((TIME_TO_SEC(TIMEDIFF(l_leave_end_time, l_leave_start_time)) - 1 * 3600) / 3600, 1)
                    ELSE
                        ROUND(TIME_TO_SEC(TIMEDIFF(l_leave_end_time, l_leave_start_time)) / 3600, 1)
                END
            ELSE
                (DATEDIFF(l_leave_end_date, l_leave_start_date) * 8) +
                CASE
                    WHEN TIME(l_leave_end_time) <= '11:45:00' THEN 4
                    ELSE 8
                END
        END
    ) AS leave_days,
    SUM(CASE WHEN l_leave_id = 1 THEN 1 ELSE 0 END) AS leave_personal,
    SUM(CASE WHEN l_leave_id = 2 THEN 1 ELSE 0 END) AS leave_personal_no,
    SUM(CASE WHEN l_leave_id = 3 THEN 1 ELSE 0 END) AS leave_sick,
    SUM(CASE WHEN l_leave_id = 4 THEN 1 ELSE 0 END) AS leave_sick_work,
    SUM(CASE WHEN l_leave_id = 5 THEN 1 ELSE 0 END) AS leave_annual,
    SUM(CASE WHEN l_leave_id = 6 THEN 1 ELSE 0 END) AS stop_work,
    SUM(CASE WHEN l_leave_id = 8 THEN 1 ELSE 0 END) AS other_leave
FROM leave_list
WHERE l_usercode = :userCode
AND NOT (TIME(l_leave_start_time) >= '11:45:00' AND TIME(l_leave_end_time) <= '12:45:00')
AND YEAR(l_create_datetime) = :selectedYear
AND l_leave_status = 0";

// Prepare and execute statement
$stmt_leave = $conn->prepare($sql_leave);
$stmt_leave->bindParam(':userCode', $row['e_usercode']);
$stmt_leave->bindParam(':selectedYear', $selectedYear);
$stmt_leave->execute();
$result_leave = $stmt_leave->fetch(PDO::FETCH_ASSOC);

// Retrieve leave counts
$leave_personal_days = floor($result_leave['leave_personal'] * 8 / 8);
$leave_personal_no_days = floor($result_leave['leave_personal_no'] * 8 / 8);
$leave_sick_days = floor($result_leave['leave_sick'] * 8 / 8);
$leave_sick_work_days = floor($result_leave['leave_sick_work'] * 8 / 8);
$leave_annual_days = floor($result_leave['leave_annual'] * 8 / 8);
$other_days = floor($result_leave['other_leave'] * 8 / 8);
$stop_work = $result_leave['stop_work'];

// Calculate stop work days
// $stop_work = floor($late_count / 3);

// Calculate total leave days
$sum_day = $leave_personal_days + $leave_personal_no_days + $leave_sick_days + $leave_sick_work_days + $leave_annual_days + $other_days + $stop_work;

// Display alert with total leave days
if ($sum_day >= 10) {
    echo '<div class="alert d-flex align-items-center" role="alert"  style="background-color: #FFCC66; border: 1px solid #FF9933;">
    <i class="fa-solid fa-chart-line me-2"></i>
    <span>รวมวันลาที่ใช้ไปทั้งหมด : ' . $sum_day . ' วัน</span>
    <button type="button" class="ms-2 btn btn-primary button-shadow" onclick="window.location.href=\'manager_leave.php\'">สถิติการลาและมาสาย</button>
    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
</div>';
}

?>

    <div class="mt-3 container-fluid">
        <div class="row">
            <div class="d-flex justify-content-between align-items-center">
                <form class="mt-3 mb-3 row" method="post" id="yearMonthForm">
                    <label for="" class="mt-2 col-auto">เลือกปี</label>
                    <div class="col-auto">
                        <?php
$currentYear = date('Y'); // ปีปัจจุบัน

if (isset($_POST['year'])) {
    $selectedYear = $_POST['year'];

    $startDate = date("Y-m-d", strtotime(($selectedYear - 1) . "-12-01"));
    $endDate = date("Y-m-d", strtotime($selectedYear . "-11-30"));
} else {
    $selectedYear = $currentYear;
}

echo "<select class='form-select' name='year' id='selectedYear' onchange='document.getElementById(\"yearMonthForm\").submit();'>";

// เพิ่มตัวเลือกของปีหน้า
$nextYear = $currentYear + 1;
echo "<option value='$nextYear'" . ($nextYear == $selectedYear ? " selected" : "") . ">$nextYear</option>";

for ($i = 0; $i <= 4; $i++) {
    $year = $currentYear - $i;
    echo "<option value='$year'" . ($year == $selectedYear ? " selected" : "") . ">$year</option>";
}
echo "</select>";
?>
                    </div>

                    <label for="" class="mt-2 col-auto">เลือกเดือน</label>
                    <div class="col-auto">
                        <?php
$months = [
    'All' => $strAllMonth,
    '01' => $strJan,
    '02' => $strFeb,
    '03' => $strMar,
    '04' => $strApr,
    '05' => $strMay,
    '06' => $strJun,
    '07' => $strJul,
    '08' => $strAug,
    '09' => $strSep,
    '10' => $strOct,
    '11' => $strNov,
    '12' => $strDec,
];

$selectedMonth = 'All';

if (isset($_POST['month'])) {
    $selectedMonth = $_POST['month'];
}

echo "<select class='form-select' name='month' id='selectedMonth' onchange='document.getElementById(\"yearMonthForm\").submit();'>";
foreach ($months as $key => $monthName) {
    echo "<option value='$key'" . ($key == $selectedMonth ? " selected" : "") . ">$monthName</option>";
}
echo "</select>";
?>
                    </div>

                    <div class="col-auto" hidden>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                    </div>
                </form>

                <!-- Button trigger modal -->
                <button type="button" class="button-shadow btn btn-primary" data-bs-toggle="modal"
                    data-bs-target="#leaveRule">
                    <i class="fa-solid fa-file-shield"></i> ระเบียบการลา
                </button>
                <!-- Modal -->
                <div class="modal fade" id="leaveRule" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
                    aria-labelledby="leaveRuleLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="leaveRuleLabel">ระเบียบการลา</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-6">
                                        <table class="table table-bordered">
                                            <thead>
                                                <h5><b>- การลารายชั่วโมง</b></h5>
                                                <tr class="text-center align-middle">
                                                    <th>ช่วงเช้า</th>
                                                    <th>ช่วงบ่าย</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr class="text-center align-middle">
                                                    <td>08:00 - 09:00</td>
                                                    <td>12:45 - 13:45</td>
                                                </tr>
                                                <tr class="text-center align-middle">
                                                    <td>09:00 - 10:00</td>
                                                    <td>13:45 - 14:45</td>
                                                </tr>
                                                <tr class="text-center align-middle">
                                                    <td>10:00 - 11:00</td>
                                                    <td>14:45 - 15:45</td>
                                                </tr>
                                                <tr class="text-center align-middle">
                                                    <td>11:00 - 11:45</td>
                                                    <td>15:45 - 16:40</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <h5><b>- ลาป่วย</b></h5>
                                        <p>พนักงานมีสิทธิ์ลาได้ 30 วัน <span
                                                class="red-text">(มีผลเรื่องการหักโบนัส)</span>
                                        </p>
                                        <h5><b>- ลาป่วยเนื่องจากการทำงานให้บริษัทฯ</b></h5>
                                        <p>พนักงานมีสิทธิ์ลาได้ แต่ถ้าซึ่งปรากฎว่ายังไม่หายจากอาการเจ็บป่วย
                                            หรือยังไม่สามารถทำงานให้บริษัทได้เกินกว่า 60 วันทำงานปกติ
                                            บริษัทจะปลดออกจากงานฐานป่วยนานเกินกำหนดทั้งนี้โดยได้รับค่าชดเชย
                                            และสิทธิอื่นใดตามที่กฎหมายว่าด้วยแรงงาน <span
                                                class="red-text">(ไม่มีผลเรื่องการหักโบนัส)</span></p>
                                        <h5><b>- ลากิจได้รับค่าจ้าง</b></h5>
                                        <p>พนักงานที่มีอายุงานครบ 1 ปี สามารถลาได้ 5 วัน <span
                                                class="red-text">(มีผลเรื่องการหักโบนัส)</span></p>
                                        <h5><b>- ลากิจไม่ได้รับค่าจ้าง</b></h5>
                                        <p>พนักงานที่มีอายุงานไม่ถึง 1
                                            ปีและพนักงานประจำที่ใช้สิทธิ์ลากิจได้รับค่าจ้างครบ 5
                                            วันแล้ว
                                            ไม่ได้จำกัดลาได้กี่วัน <span class="red-text">(มีผลเรื่องการหักโบนัส)</span>
                                        </p>
                                        <h5><b>- ลาพักร้อน</b></h5>
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr class="text-center align-middle">
                                                    <th>อายุงานของพนักงาน (ปี)</th>
                                                    <th>จำนวนวันหยุดพักผ่อนประจำปี
                                                        (วันทำงานปกติ)
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr class="text-center align-middle">
                                                    <td>ครบ 1 ปี แต่ไม่ถึง 2 ปี</td>
                                                    <td>6</td>
                                                </tr>
                                                <tr class="text-center align-middle">
                                                    <td>ครบ 2 ปี แต่ไม่ถึง 3 ปี</td>
                                                    <td>7</td>
                                                </tr>
                                                <tr class="text-center align-middle">
                                                    <td>ครบ 3 ปี แต่ไม่ถึง 4 ปี</td>
                                                    <td>8</td>
                                                </tr>
                                                <tr class="text-center align-middle">
                                                    <td>ครบ 4 ปี แต่ไม่ถึง 5 ปี</td>
                                                    <td>9</td>
                                                </tr>
                                                <tr class="text-center align-middle">
                                                    <td>ครบ 5 ปี ขึ้นไป</td>
                                                    <td>10</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="col-6">
                                        <h5><b>- ลาเพื่อทำหมัน</b></h5>
                                        <p>พนักงานมีสิทธิ์ลาได้ตามที่ระบุไว้ในใบรับรองแพทย์
                                            ลาได้ครั้งเดียวตลอดอายุการเป็นพนักงาน
                                            ยื่นล่วงหน้า 1 วัน</p>
                                        <h5><b>- ลาคลอด</b></h5>
                                        <p>ครรภ์ไม่เกิน 98 วัน ให้นับรวมวันหยุดที่มีอยู่ในระหว่างวันลา
                                            ยื่นใบลาล่วงหน้า 15
                                            วัน
                                            ได้รับค่าจ้างเท่ากับอัตราค่าจ้างต่อชั่วโมงในเวลาทำงานปกติตลอดระยะเวลาที่ลาเพื่อการคลอดแต่ไม่เกิน
                                            45 วัน</p>
                                        <h5><b>- ลาอุปสมบท</b></h5>
                                        <p>พนักงานประจำที่มีอายุงานติดต่อกันครบ 2
                                            ปีบริบูรณ์ขึ้นไปสามารถขอลาอุปสมบทได้ 15
                                            วันทำงานปกติโดยได้รับค่าจ้างและให้ลาได้เพียงครั้งเดียวตลอดระยะเวลาที่เป็นพนักงานของบริษัท
                                            ยื่นใบลาล่วงหน้า 15 วัน</p>
                                        <h5><b>- ลาเพื่อรับราชการทหาร</b></h5>
                                        <p>พนักงานลาได้ไม่เกิน 60 วันต่อปี ได้รับค่าจ้าง ยื่นใบลาล่วงหน้า 15 วัน</p>
                                        <h5><b>- ลาเพื่อจัดการงานศพ</b></h5>
                                        <p>พนักงานประจำสามารถลาเพื่อจัดการงานศพในกรณีที่ บิดา มารดา
                                            คู่สมรสหรือบุตรโดยชอบด้วยกฎหมายถึงแก่กรรม
                                            โดยได้รับค่าจ้าง ลาหยุดงานไม่เกินครั้งละ 3 วัน</p>
                                        <h5><b>- ลาเพื่อพัฒนาและเรียนรู้</b></h5>
                                        <p>พนักงานสามารถขอลาเพื่อพัฒนาและเรียนรู้ได้ตามที่ผู้บังคับบัญชาจะพิจารณาเห็นเป็นการสมควรและอนุมัติให้เป็นคราว
                                            ๆ ไปโดยได้รับค่าจ้างเท่ากับอัตราค่าจ้างต่อชั่วโมงในเวลาทำงานปกติ
                                            ไม่เกินปีละ 3
                                            ครั้ง ลาล่วงหน้า 7 วัน</p>
                                        <h5><b>- ลาเพื่อการสมรส</b></h5>
                                        <p>พนักงานประจำที่มีอายุงานติดต่อกันครบ 1 ปี
                                            บริบูรณ์ขึ้นไปสามารถขอลาเพื่อการสมรสได้
                                            3 วันทำงานปกติโดยได้รับค่าจ้าง
                                            ให้ลาได้เพียงครั้งเดียวตลอดระยะเวลาที่เป็นพนักงานของบริษัท</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row">
            <span class="text-danger">** 0(0.0) = วัน(ชั่วโมง.นาที)</span>
            <span class="text-danger">*** จำนวนวันลาที่ใช้จะแสดงเมื่อการอนุมัติสำเร็จเรียบร้อยแล้ว</span>
            <?php
$leave_types = [
    1 => 'ลากิจได้รับค่าจ้าง',
    2 => 'ลากิจไม่ได้รับค่าจ้าง',
    3 => 'ลาป่วย',
    4 => 'ลาป่วยจากงาน',
    5 => 'ลาพักร้อน',
    7 => 'มาสาย',
    6 => 'หยุดงาน',
    8 => 'อื่น ๆ',
];

foreach ($leave_types as $leave_id => $leave_name) {
    // SQL Query to get leave details and employee leave balances
    $sql_leave_personal = "SELECT
    SUM(
        DATEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))
        - (SELECT COUNT(1)
           FROM holiday
           WHERE h_start_date BETWEEN l_leave_start_date AND l_leave_end_date
             AND h_holiday_status = 'วันหยุด'
             AND h_status = 0)
    ) AS total_leave_days,
    SUM(
        HOUR(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))) % 24
    ) -
    SUM(
        CASE
            WHEN HOUR(CONCAT(l_leave_start_date, ' ', l_leave_start_time)) < 12
                 AND HOUR(CONCAT(l_leave_end_date, ' ', l_leave_end_time)) > 12
            THEN 1
            ELSE 0
        END
    ) AS total_leave_hours,
    SUM(
        MINUTE(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time)))
    ) AS total_leave_minutes,
    (SELECT e_leave_personal FROM employees WHERE e_usercode = :userCode) AS total_personal,
    (SELECT e_leave_personal_no FROM employees WHERE e_usercode = :userCode) AS total_personal_no,
    (SELECT e_leave_sick FROM employees WHERE e_usercode = :userCode) AS total_sick,
    (SELECT e_leave_sick_work FROM employees WHERE e_usercode = :userCode) AS total_sick_work,
    (SELECT e_leave_annual FROM employees WHERE e_usercode = :userCode) AS total_annual,
    (SELECT e_other FROM employees WHERE e_usercode = :userCode) AS total_other,
    (SELECT COUNT(l_list_id) FROM leave_list WHERE l_leave_id = 7 AND l_usercode = :userCode) AS late_count
FROM leave_list
JOIN employees ON employees.e_usercode = leave_list.l_usercode
WHERE l_leave_id = :leave_id
  AND l_usercode = :userCode
  AND YEAR(l_leave_end_date) = :selectedYear
  AND l_leave_status = 0
  AND l_approve_status2 = 4";

    $stmt_leave_personal = $conn->prepare($sql_leave_personal);
    $stmt_leave_personal->bindParam(':leave_id', $leave_id, PDO::PARAM_INT); // Bind the leave_id
    $stmt_leave_personal->bindParam(':userCode', $userCode);
    $stmt_leave_personal->bindParam(':selectedYear', $selectedYear, PDO::PARAM_INT);
    $stmt_leave_personal->execute();
    $result_leave = $stmt_leave_personal->fetch(PDO::FETCH_ASSOC);

    if ($result_leave) {
        $days = $result_leave['total_leave_days'] ?? 0;
        $hours = $result_leave['total_leave_hours'] ?? 0;
        $minutes = $result_leave['total_leave_minutes'] ?? 0;

        // Employee leave balances
        $total_personal = $result_leave['total_personal'] ?? 0;
        $total_personal_no = $result_leave['total_personal_no'] ?? 0;
        $total_sick = $result_leave['total_sick'] ?? 0;
        $total_sick_work = $result_leave['total_sick_work'] ?? 0;
        $total_annual = $result_leave['total_annual'] ?? 0;
        $total_other = $result_leave['total_other'] ?? 0;
        $total_late = $result_leave['late_count'] ?? 0;

        // Convert hours to days if applicable
        $days += floor($hours / 8);
        $hours = $hours % 8;

        // Adjust minutes if necessary
        if ($minutes >= 60) {
            $hours += floor($minutes / 60);
            $minutes = $minutes % 60;
        }

        if ($minutes > 0 && $minutes <= 30) {
            $minutes = 30;
        } elseif ($minutes > 30) {
            $minutes = 0;
            $hours += 1;
        }

        if ($minutes == 30) {
            $minutes = 5;
        }
    }

// Output the leave data
    echo '<div class="col-3 filter-card">';

    // Check the leave type and display the appropriate data
    if ($leave_id == 1) {
        echo '<div class="card text-light mb-3 filter-card" style="background-color: #031B80;" data-leave-id="1">';
        echo '<div class="card-body">';
        echo '    <div class="card-title">';
        echo '        <div class="d-flex justify-content-between">';
        echo '            <div>';
        echo '<h5>' . $days . '(' . $hours . '.' . $minutes . ') / ' . $total_personal . '</h5>';
        echo '<p class="card-text">' . $leave_name . '</p>';
        echo '            </div>';
        echo '            <div class="d-flex justify-content-end">'; // Added this to align icon to the right
        echo '                <i class="mx-2 mt-3 fa-solid fa-sack-dollar fa-2xl"></i>';
        echo '            </div>';
        echo '        </div>';
        echo '    </div>'; // Close card-title
        echo '</div>'; // Close card-body
        echo '</div>'; // Close card
        echo '<input type="hidden" name="personal_days" value="' . $days . '">';
        echo '<input type="hidden" name="personal_hours" value="' . $hours . '">';
        echo '<input type="hidden" name="personal_minutes" value="' . $minutes . '">';
        echo '<input type="hidden" name="total_personal" value="' . $total_personal . '">';
    } else if ($leave_id == 2) {
        echo '<div class="card text-light mb-3 filter-card" style="background-color: #0339A2;" data-leave-id="2">';
        echo '<div class="card-body">';
        echo '    <div class="card-title">';
        echo '        <div class="d-flex justify-content-between">';
        echo '            <div>';
        echo '<h5>' . $days . '(' . $hours . '.' . $minutes . ') / ' . $total_personal_no . '</h5>';
        echo '<p class="card-text">' . $leave_name . '</p>';
        echo '            </div>';
        echo '            <div class="d-flex justify-content-end">'; // Added this to align icon to the right
        echo '<i class="mx-2 mt-3 fa-solid fa-sack-xmark fa-2xl"></i>';
        echo '            </div>';
        echo '        </div>';
        echo '    </div>'; // Close card-title
        echo '</div>'; // Close card-body
        echo '</div>'; // Close card
        echo '<input type="hidden" name="personnel_no_days" value="' . $days . '">';
        echo '<input type="hidden" name="personal_no_hours" value="' . $hours . '">';
        echo '<input type="hidden" name="personal_no_minutes" value="' . $minutes . '">';
        echo '<input type="hidden" name="total_personal_no" value="' . $total_personal_no . '">';
    } else if ($leave_id == 3) {
        echo '<div class="card text-light mb-3 filter-card" style="background-color: #0357C4;" data-leave-id="3">';
        echo '<div class="card-body">';
        echo '    <div class="card-title">';
        echo '        <div class="d-flex justify-content-between">';
        echo '            <div>';
        echo '<h5>' . $days . '(' . $hours . '.' . $minutes . ') / ' . $total_sick . '</h5>';
        echo '<p class="card-text">' . $leave_name . '</p>';
        echo '            </div>';
        echo '            <div class="d-flex justify-content-end">'; // Added this to align icon to the right
        echo '<i class="mx-2 mt-3 fa-solid fa-syringe fa-2xl"></i>';
        echo '            </div>';
        echo '        </div>';
        echo '    </div>'; // Close card-title
        echo '</div>'; // Close card-body
        echo '</div>'; // Close card
        echo '<input type="hidden" name="sick_days" value="' . $days . '">';
        echo '<input type="hidden" name="sick_hours" value="' . $hours . '">';
        echo '<input type="hidden" name="sick_minutes" value="' . $minutes . '">';
        echo '<input type="hidden" name="total_sick" value="' . $total_sick . '">';

    } else if ($leave_id == 4) {
        echo '<div class="card text-light mb-3 filter-card" style="background-color: #0357C4;" data-leave-id="4">';
        echo '<div class="card-body">';
        echo '    <div class="card-title">';
        echo '        <div class="d-flex justify-content-between">';
        echo '            <div>';
        echo '<h5>' . $days . '(' . $hours . '.' . $minutes . ') / ' . $total_sick_work . '</h5>';
        echo '<p class="card-text">' . $leave_name . '</p>';
        echo '            </div>';
        echo '            <div class="d-flex justify-content-end">'; // Added this to align icon to the right
        echo '<i class="mx-2 mt-3 fa-solid fa-user-injured fa-2xl"></i>';
        echo '            </div>';
        echo '        </div>';
        echo '    </div>'; // Close card-title
        echo '</div>'; // Close card-body
        echo '</div>'; // Close card
        echo '<input type="hidden" name="sick_work_days" value="' . $days . '">';
        echo '<input type="hidden" name="sick_work_hours" value="' . $hours . '">';
        echo '<input type="hidden" name="sick_work_minutes" value="' . $minutes . '">';
        echo '<input type="hidden" name="total_sick_work" value="' . $total_sick_work . '">';
    } else if ($leave_id == 5) {
        echo '<div class="card text-light mb-3 filter-card" style="background-color: #0475E6;" data-leave-id="5">';
        echo '<div class="card-body">';
        echo '    <div class="card-title">';
        echo '        <div class="d-flex justify-content-between">';
        echo '            <div>';
        echo '<h5>' . $days . '(' . $hours . '.' . $minutes . ') / ' . $total_annual . '</h5>';
        echo '<p class="card-text">' . $leave_name . '</p>';
        echo '            </div>';
        echo '            <div class="d-flex justify-content-end">'; // Added this to align icon to the right
        echo '<i class="mx-2 mt-3 fa-solid fa-business-time fa-2xl"></i>';
        echo '            </div>';
        echo '        </div>';
        echo '    </div>'; // Close card-title
        echo '</div>'; // Close card-body
        echo '</div>'; // Close card
        echo '<input type="hidden" name="annual_days" value="' . $days . '">';
        echo '<input type="hidden" name="annual_hours" value="' . $hours . '">';
        echo '<input type="hidden" name="annual_minutes" value="' . $minutes . '">';
        echo '<input type="hidden" name="total_annual" value="' . $total_annual . '">';

    } else if ($leave_id == 6) {
        echo '<div class="card text-light mb-3 filter-card" style="background-color: #4B9CED;" data-leave-id="6">';
        echo '<div class="card-body">';
        echo '    <div class="card-title">';
        echo '        <div class="d-flex justify-content-between">';
        echo '            <div>';
        echo '<h5>' . $days . '(' . $hours . '.' . $minutes . ')' . '</h5>';
        echo '<p class="card-text">' . $leave_name . '</p>';
        echo '            </div>';
        echo '            <div class="d-flex justify-content-end">'; // Added this to align icon to the right
        echo '<i class="mx-2 mt-3 fa-solid fa-business-time fa-2xl"></i>';
        echo '            </div>';
        echo '        </div>';
        echo '    </div>'; // Close card-title
        echo '</div>'; // Close card-body
        echo '</div>'; // Close card
    } else if ($leave_id == 7) {
        echo '<div class="card text-light mb-3 filter-card" style="background-color: #2788E9;" data-leave-id="7">';
        echo '<div class="card-body">';
        echo '    <div class="card-title">';
        echo '        <div class="d-flex justify-content-between">';
        echo '            <div>';
        echo '<h5>' . $total_late . '</h5>';
        echo '<p class="card-text">' . $leave_name . '</p>';
        echo '            </div>';
        echo '            <div class="d-flex justify-content-end">'; // Added this to align icon to the right
        echo '<i class="mx-2 mt-3 fa-solid fa-person-running fa-2xl"></i>';
        echo '            </div>';
        echo '        </div>';
        echo '    </div>'; // Close card-title
        echo '</div>'; // Close card-body
        echo '</div>'; // Close card
    } else if ($leave_id == 8) {
        echo '<div class="card text-light mb-3 filter-card" style="background-color: #6FB0F0;" data-leave-id="8">';
        echo '<div class="card-body">';
        echo '    <div class="card-title">';
        echo '        <div class="d-flex justify-content-between">';
        echo '            <div>';
        echo '<h5>' . $days . '(' . $hours . '.' . $minutes . ')' . '</h5>';
        echo '<p class="card-text">' . $leave_name . '</p>';
        echo '            </div>';
        echo '            <div class="d-flex justify-content-end">'; // Added this to align icon to the right
        echo '<i class="mx-2 mt-3 fa-solid fa-bars fa-2xl"></i>';
        echo '            </div>';
        echo '        </div>';
        echo '    </div>'; // Close card-title
        echo '</div>'; // Close card-body
        echo '</div>'; // Close card
        echo '<input type="hidden" name="other_days" value="' . $days . '">';
        echo '<input type="hidden" name="other_hours" value="' . $hours . '">';
        echo '<input type="hidden" name="other_minutes" value="' . $minutes . '">';
        echo '<input type="hidden" name="total_other" value="' . $total_other . '">';

    } else {
        echo 'ไม่พบประเภทการลา';
    }
    echo '</div>'; // Close col-3
}

echo '</div>'; // Close the row div

?>

        </div>

        <!-- Modal -->
        <div class="modal fade" id="leaveDetailsModal" tabindex="-1" aria-labelledby="leaveDetailsModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="leaveDetailsModalLabel">ประวัติทั้งหมด</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid">
            <div class="row">
                <div class="mb-3 d-flex justify-content-end">
                    <!-- ปุ่มยื่นใบลา -->
                    <button type="button" class="button-shadow btn btn-primary mt-3" data-bs-toggle="modal"
                        data-bs-target="#leaveModal" style="width: 100px;">
                        <?php echo $btnAddLeave; ?>
                    </button>
                    <!-- ลาฉุกเฉิน -->
                    <button type="button" class="button-shadow btn btn-danger mt-3 ms-2" data-bs-toggle="modal"
                        data-bs-target="#urgentLeaveModal" style="width: 100px;">
                        <?php echo $btnAddLeaveEmer; ?>
                    </button>
                </div>
            </div>
            <!-- Modal ยื่นใบลา -->
            <div class="modal fade" id="leaveModal" tabindex="-1" aria-labelledby="leaveModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="leaveModalLabel"><?php echo $strLeaveDes; ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="leaveForm" method="POST" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-24 alert alert-danger d-none" role="alert" id="alertCheckDays">
                                        ไม่สามารถลาได้ คุณได้ใช้สิทธิ์ครบกำหนดแล้ว
                                    </div>
                                    <div class="col-12">
                                        <label for="leaveType" class="form-label">ประเภทการลา</label>
                                        <span class="badge rounded-pill text-bg-info" name="totalDays">เหลือ -
                                            วัน</span>
                                        <span style="color: red;">*</span>
                                        <select class="form-select" id="leaveType" required
                                            onchange="checkDays(this.value)">
                                            <option selected>เลือกประเภทการลา</option>
                                            <option value=" 1">ลากิจได้รับค่าจ้าง</option>
                                            <option value="2">ลากิจไม่ได้รับค่าจ้าง</option>
                                            <option value="3">ลาป่วย</option>
                                            <option value="4">ลาป่วยจากงาน</option>
                                            <option value="5">ลาพักร้อน</option>
                                            <option value="8">อื่น ๆ</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mt-3 row">
                                    <div class="col-12">
                                        <label for="leaveReason" class="form-label"><?php echo $strReason; ?></label>
                                        <span style="color: red;">*</span>
                                        <textarea class="form-control mt-2" id="leaveReason" rows="3"
                                            placeholder="กรุณาระบุเหตุผล"></textarea>
                                    </div>
                                </div>
                                <div class="mt-3 row">
                                    <div class="col-6">
                                        <label for="startDate" class="form-label"><?php echo $strStartDate; ?></label>
                                        <span style="color: red;">*</span>
                                        <input type="text" class="form-control" id="startDate" required
                                            onchange="checkDays(document.getElementById('leaveType').value)">
                                    </div>
                                    <div class="col-6">
                                        <label for="startTime" class="form-label"><?php echo $strStartTime; ?></label>
                                        <span style="color: red;">*</span>
                                        <select class="form-select" id="startTime" name="startTime" required
                                            onchange="checkDays(document.getElementById('leaveType').value)">
                                            <option value="08:00" selected>08:00</option>
                                            <option value="08:30">08:30</option>
                                            <option value="08:45">08:45</option>
                                            <option value="09:00">09:00</option>
                                            <option value="09:30">09:30</option>
                                            <option value="09:45">09:45</option>
                                            <option value="10:00">10:00</option>
                                            <option value="10:30">10:30</option>
                                            <option value="10:45">10:45</option>
                                            <option value="11:00">11:00</option>
                                            <option value="12:00">11:45</option>
                                            <option value="13:00">12:45</option>
                                            <option value="13:30">13:15</option>
                                            <option value="14:00">13:45</option>
                                            <option value="14:30">14:15</option>
                                            <option value="15:00">14:45</option>
                                            <option value="15:30">15:15</option>
                                            <option value="16:00">15:45</option>
                                            <option value="16:30">16:15</option>
                                            <option value="17:00">16:40</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mt-3 row">
                                    <div class="col-6">
                                        <label for="endDate" class="form-label"><?php echo $strEndtDate; ?></label>
                                        <span style="color: red;">*</span>
                                        <input type="text" class="form-control" id="endDate" required
                                            onchange="checkDays(document.getElementById('leaveType').value)">
                                    </div>
                                    <div class="col-6">
                                        <label for="endTime" class="form-label"><?php echo $strEndTime; ?></label>
                                        <span style="color: red;">*</span>
                                        <select class="form-select" id="endTime" name="endTime" required
                                            onchange="checkDays(document.getElementById('leaveType').value)">
                                            <option value="08:00">08:00</option>
                                            <option value="08:30">08:30</option>
                                            <option value="08:45">08:45</option>
                                            <option value="09:00">09:00</option>
                                            <option value="09:30">09:30</option>
                                            <option value="09:45">09:45</option>
                                            <option value="10:00">10:00</option>
                                            <option value="10:30">10:30</option>
                                            <option value="10:45">10:45</option>
                                            <option value="11:00">11:00</option>
                                            <option value="12:00">11:45</option>
                                            <option value="13:00">12:45</option>
                                            <option value="13:30">13:15</option>
                                            <option value="14:00">13:45</option>
                                            <option value="14:30">14:15</option>
                                            <option value="15:00">14:45</option>
                                            <option value="15:30">15:15</option>
                                            <option value="16:00">15:45</option>
                                            <option value="16:30">16:15</option>
                                            <option value="17:00" selected>16:40</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mt-3 row">
                                    <div class="col-12">
                                        <label for="telPhone" class="form-label"><?php echo $strPhone; ?></label>
                                        <?php
$sql2 = "SELECT e_phone FROM employees WHERE e_usercode = '$userCode'";
$result2 = $conn->query($sql2);

if ($result2->rowCount() > 0) {
    while ($row2 = $result2->fetch(PDO::FETCH_ASSOC)) {
        echo '<input type="text" class="form-control" id="telPhone" value="' . $row2['e_phone'] . '">';
    }
} else {
    // กรณีไม่พบข้อมูล
}
?>
                                    </div>
                                </div>
                                <div class="mt-3 row">
                                    <div class="col-12">
                                        <label for="file" class="form-label"><?php echo $strFile; ?> (PNG , JPG,
                                            JPEG)</label>
                                        <input class="form-control" type="file" id="file" name="file" />
                                    </div>
                                </div>

                                <div class="mt-3 d-flex justify-content-end">
                                    <button type="submit" class="btn btn-success" id="btnSubmitForm1" name="submit"
                                        style="white-space: nowrap;"><?php echo $btnSave; ?></button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Modal ลาฉุกเฉิน -->
            <div class="modal fade" id="urgentLeaveModal" tabindex="-1" aria-labelledby="urgentLeaveModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="urgentLeaveModalLabel">รายละเอียดการลาฉุกเฉิน</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="urgentLeaveForm" method="POST" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-12">
                                        <label for="urgentLeaveType" class="form-label">ประเภทการลา</label>
                                        <span style="color: red;">*</span>
                                        <select class="form-select" id="urgentLeaveType" required>
                                            <!--  onchange="updateUrgentLeaveReasonField()" -->
                                            <option value="0" selected>เลือกประเภทการลา</option>
                                            <option value="1">ลากิจได้รับค่าจ้าง</option>
                                            <option value="2">ลากิจไม่ได้รับค่าจ้าง</option>
                                            <!-- <option value="3">ลาป่วย</option> -->
                                            <!-- <option value="4">ลาป่วยจากงาน</option> -->
                                            <option value="5">ลาพักร้อนฉุกเฉิน</option>
                                            <!-- <option value="8">อื่น ๆ</option> -->
                                        </select>
                                    </div>
                                </div>

                                <div class="mt-3 row">
                                    <div class="col-12">
                                        <label for="urgentLeaveReason" class="form-label">เหตุผลการลา</label>
                                        <span style="color: red;">*</span>
                                        <!-- <select class="form-select" id="urgentLeaveReason" required
                                        onchange="checkUrgentOther(this)">
                                        <option value="" selected disabled>เลือกเหตุผลการลา</option>
                                    </select> -->
                                        <textarea class="form-control mt-2" id="urgentLeaveReason" rows="3"
                                            placeholder="กรุณาระบุเหตุผล"></textarea>
                                    </div>
                                </div>
                                <div class="mt-3 row">
                                    <div class="col-6">
                                        <label for="urgentStartDate" class="form-label">วันที่เริ่มต้น</label>
                                        <span style="color: red;">*</span>
                                        <input type="text" class="form-control" id="urgentStartDate" required>
                                    </div>
                                    <div class="col-6">
                                        <label for="urgentStartTime" class="form-label">เวลาที่เริ่มต้น</label>
                                        <span style="color: red;">*</span>
                                        <select class="form-select" id="urgentStartTime" name="urgentStartTime"
                                            required>
                                            <option value="08:00" selected>08:00</option>
                                            <option value="08:30">08:30</option>
                                            <option value="08:45">08:45</option>
                                            <option value="09:00">09:00</option>
                                            <option value="09:30">09:30</option>
                                            <option value="09:45">09:45</option>
                                            <option value="10:00">10:00</option>
                                            <option value="10:30">10:30</option>
                                            <option value="10:45">10:45</option>
                                            <option value="11:00">11:00</option>
                                            <option value="12:00">11:45</option>
                                            <option value="13:00">12:45</option>
                                            <option value="13:30">13:15</option>
                                            <option value="14:00">13:45</option>
                                            <option value="14:30">14:15</option>
                                            <option value="15:00">14:45</option>
                                            <option value="15:30">15:15</option>
                                            <option value="16:00">15:45</option>
                                            <option value="16:30">16:15</option>
                                            <option value="17:00">16:40</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mt-3 row">
                                    <div class="col-6">
                                        <label for="urgentEndDate" class="form-label">วันที่สิ้นสุด</label>
                                        <span style="color: red;">*</span>
                                        <input type="text" class="form-control" id="urgentEndDate" required>
                                    </div>
                                    <div class="col-6">
                                        <label for="urgentEndTime" class="form-label">เวลาที่สิ้นสุด</label>
                                        <span style="color: red;">*</span>
                                        <select class="form-select" id="urgentEndTime" name="urgentEndTime" required>
                                            <option value="08:00">08:00</option>
                                            <option value="08:30">08:30</option>
                                            <option value="08:45">08:45</option>
                                            <option value="09:00">09:00</option>
                                            <option value="09:30">09:30</option>
                                            <option value="09:45">09:45</option>
                                            <option value="10:00">10:00</option>
                                            <option value="10:30">10:30</option>
                                            <option value="10:45">10:45</option>
                                            <option value="11:00">11:00</option>
                                            <option value="12:00">11:45</option>
                                            <option value="13:00">12:45</option>
                                            <option value="13:30">13:15</option>
                                            <option value="14:00">13:45</option>
                                            <option value="14:30">14:15</option>
                                            <option value="15:00">14:45</option>
                                            <option value="15:30">15:15</option>
                                            <option value="16:00">15:45</option>
                                            <option value="16:30">16:15</option>
                                            <option value="17:00" selected>16:40</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mt-3 row">
                                    <div class="col-12">
                                        <label for="urgentTelPhone" class="form-label">เบอร์โทร</label>
                                        <?php
// ใช้รหัสเดียวกับฟอร์มลา
$sql2 = "SELECT e_phone FROM employees WHERE e_usercode = '$userCode'";
$result2 = $conn->query($sql2);

if ($result2->rowCount() > 0) {
    while ($row2 = $result2->fetch(PDO::FETCH_ASSOC)) {
        echo '<input type="text" class="form-control" id="urgentTelPhone" value="' . $row2['e_phone'] . '">';
    }
} else {
    // กรณีไม่พบข้อมูล
}
?>
                                    </div>
                                </div>
                                <div class="mt-3 row">
                                    <div class="col-12">
                                        <label for="urgentFile" class="form-label">ไฟล์แนบ (PNG, JPG, JPEG)</label>
                                        <input class="form-control" type="file" id="urgentFile" name="urgentFile" />
                                    </div>
                                </div>


                                <!-- Submit Button -->
                                <div class="mt-3 d-flex justify-content-end">
                                    <button type="submit" class="btn btn-success" name="submit"
                                        style="width: 100px;"><?php echo $btnSave; ?></button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ตารางแสดงข้อมูลการลาและมาสาย / อื่น ๆ -->
            <div class="table-responsive">
                <table class="table table-hover" style="border-top: 1px solid rgba(0, 0, 0, 0.1);" id="leaveTable">
                    <thead class="table table-secondary">
                        <tr class="text-center align-middle">
                            <th rowspan="2"><?php echo $strNo; ?></th>
                            <th rowspan="2"><?php echo $strSubDate; ?></th>
                            <th rowspan="2"><?php echo $strList; ?></th>
                            <th colspan="2"><?php echo $strDateTime; ?></th>
                            <th rowspan="2"><?php echo $strDayCount; ?></th>
                            <th rowspan="2"><?php echo $strFile; ?></th>
                            <th rowspan="2"><?php echo $strListStatus; ?></th>
                            <th rowspan="2"><?php echo $strLateStatus; ?></th>
                            <th rowspan="2"><?php echo $strStatus1; ?></th>
                            <th rowspan="2"><?php echo $strStatus2; ?></th>
                            <th rowspan="2">สถานะอนุมัติ_3</th>
                            <th rowspan="2"><?php echo $strStatusHR; ?></th>
                            <th rowspan="2"></th>
                            <th rowspan="2"></th>
                        </tr>
                        <tr class="text-center">
                            <th><?php echo $strFrom; ?></th>
                            <th><?php echo $strTo; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
// กำหนดจำนวนรายการต่อหน้า
$itemsPerPage = 10;

// คำนวณหน้าปัจจุบัน
if (!isset($_GET['page'])) {
    $currentPage = 1;
} else {
    $currentPage = $_GET['page'];
}

// สร้างคำสั่ง SQL
// $sql = "SELECT * FROM leave_list WHERE l_usercode = '$userCode' AND Month(l_leave_start_date) = '$selectedMonth'
// AND Year(l_leave_start_date) = '$selectedYear' AND l_leave_id <> 6 ORDER BY l_create_datetime DESC ";
$sql = "SELECT * FROM leave_list WHERE l_usercode = '$userCode' ";

if ($selectedMonth != "All") {
    $sql .= " AND Month(l_leave_start_date) = '$selectedMonth'";
}

$sql .= " AND Year(l_leave_start_date) = '$selectedYear' ORDER BY l_create_datetime DESC ";

// หาจำนวนรายการทั้งหมด
$result = $conn->query($sql);
$totalRows = $result->rowCount();

// คำนวณหน้าทั้งหมด
$totalPages = ceil($totalRows / $itemsPerPage);

// คำนวณ offset สำหรับ pagination
$offset = ($currentPage - 1) * $itemsPerPage;

// เพิ่ม LIMIT และ OFFSET ในคำสั่ง SQL
$sql .= " LIMIT $itemsPerPage OFFSET $offset";

// ประมวลผลคำสั่ง SQL
$result = $conn->query($sql);

// แสดงผลลำดับของแถว
$rowNumber = $totalRows - ($currentPage - 1) * $itemsPerPage; // กำหนดลำดับของแถว

// แสดงข้อมูลในตาราง
if ($result->rowCount() > 0) {
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo '<tr class="text-center align-middle">';

// 0
        echo '<td hidden>';
        if ($row['l_leave_id'] == 1) {
            echo '<span class="text-primary">' . 'ลากิจได้รับค่าจ้าง' . '</span>';
        } elseif ($row['l_leave_id'] == 2) {
            echo '<span class="text-primary">' . 'ลากิจไม่ได้รับค่าจ้าง' . '</span>';
        } elseif ($row['l_leave_id'] == 3) {
            echo '<span class="text-primary">' . 'ลาป่วย' . '</span>';
        } elseif ($row['l_leave_id'] == 4) {
            echo '<span class="text-primary">' . 'ลาป่วยจากงาน' . '</span>';
        } elseif ($row['l_leave_id'] == 5) {
            echo '<span class="text-primary">' . 'ลาพักร้อน' . '</span>';
        } elseif ($row['l_leave_id'] == 6) {
            echo '<span class="text-primary">' . 'ขาดงาน' . '</span>';
        } elseif ($row['l_leave_id'] == 7) {
            echo '<span class="text-primary">' . 'มาสาย' . '</span>';
        } elseif ($row['l_leave_id'] == 8) {
            echo '<span class="text-primary">' . 'อื่น ๆ' . '</span>';
        } else {
            echo $row['l_leave_reason'];
        }
        echo '</td>';

        // 1
        echo '<td hidden>' . $row['l_department'] . '</td>';

        // 2
        echo '<td hidden>' . $row['l_leave_reason'] . '</td>';

        // 3
        echo '<td hidden>' . $row['l_leave_start_date'] . '</td>';

        // 4
        echo '<td hidden>' . $row['l_leave_start_time'] . '</td>';

        // 5
        echo '<td hidden>' . $row['l_leave_end_time'] . '</td>';

        // 6
        echo '<td>' . $rowNumber . '</td>';

        // 7
        echo '<td>' . $row['l_create_datetime'] . '</td>';

        // 8
        echo '<td>';
        if ($row['l_leave_id'] == 1) {
            echo '<span class="text-primary">' . 'ลากิจได้รับค่าจ้าง' . '</span>' . '<br>' . 'เหตุผล : ' . $row['l_leave_reason'];
        } elseif ($row['l_leave_id'] == 2) {
            echo '<span class="text-primary">' . 'ลากิจไม่ได้รับค่าจ้าง' . '</span>' . '<br>' . 'เหตุผล : ' . $row['l_leave_reason'];
        } elseif ($row['l_leave_id'] == 3) {
            echo '<span class="text-primary">' . 'ลาป่วย' . '</span>' . '<br>' . 'เหตุผล : ' . $row['l_leave_reason'];
        } elseif ($row['l_leave_id'] == 4) {
            echo '<span class="text-primary">' . 'ลาป่วยจากงาน' . '</span>' . '<br>' . 'เหตุผล : ' . $row['l_leave_reason'];
        } elseif ($row['l_leave_id'] == 5) {
            echo '<span class="text-primary">' . 'ลาพักร้อน' . '</span>' . '<br>' . 'เหตุผล : ' . $row['l_leave_reason'];
        } elseif ($row['l_leave_id'] == 6) {
            echo '<span class="text-primary">' . 'ขาดงาน' . '</span>' . '<br>';
        } elseif ($row['l_leave_id'] == 7) {
            echo '<span class="text-primary">' . 'มาสาย' . '</span>';
        } elseif ($row['l_leave_id'] == 8) {
            echo '<span class="text-primary">' . 'อื่น ๆ' . '</span>' . '<br>' . 'เหตุผล : ' . $row['l_leave_reason'];
        } else {
            echo $row['l_leave_reason'];
        }
        echo '</td>';

        // 9
        // 08:45
        if ($row['l_leave_start_time'] == '09:00:00' && $row['l_remark'] == '08:45:00') {
            echo '<td>' . $row['l_leave_start_date'] . '<br> 08:45:00</td>';
        }
        // 09:45
        else if ($row['l_leave_start_time'] == '10:00:00' && $row['l_remark'] == '09:45:00') {
            echo '<td>' . $row['l_leave_start_date'] . '<br> 09:45:00</td>';
        }
        // 10:45
        else if ($row['l_leave_start_time'] == '11:00:00' && $row['l_remark'] == '10:45:00') {
            echo '<td>' . $row['l_leave_start_date'] . '<br> 10:45:00</td>';
        }
        // 11:45
        else if ($row['l_leave_start_time'] == '12:00:00') {
            echo '<td>' . $row['l_leave_start_date'] . '<br> 11:45:00</td>';
        }
        // 12:45
        else if ($row['l_leave_start_time'] == '13:00:00') {
            echo '<td>' . $row['l_leave_start_date'] . '<br> 12:45:00</td>';
        }
        // 13:15
        else if ($row['l_leave_start_time'] == '13:30:00' && $row['l_remark'] == '13:15:00') {
            echo '<td>' . $row['l_leave_start_date'] . '<br> 13:15:00</td>';
        }
        // 13:45
        else if ($row['l_leave_start_time'] == '14:00:00' && $row['l_remark'] == '13:45:00') {
            echo '<td>' . $row['l_leave_start_date'] . '<br> 13:45:00</td>';
        }
        // 14:15
        else if ($row['l_leave_start_time'] == '14:30:00' && $row['l_remark'] == '14:15:00') {
            echo '<td>' . $row['l_leave_start_date'] . '<br> 14:15:00</td>';
        }
        // 14:45
        else if ($row['l_leave_start_time'] == '15:00:00' && $row['l_remark'] == '14:45:00') {
            echo '<td>' . $row['l_leave_start_date'] . '<br> 14:45:00</td>';
        }
        // 15:15
        else if ($row['l_leave_start_time'] == '15:30:00' && $row['l_remark'] == '15:15:00') {
            echo '<td>' . $row['l_leave_start_date'] . '<br> 15:15:00</td>';
        }
        // 15:45
        else if ($row['l_leave_start_time'] == '16:00:00' && $row['l_remark'] == '15:45:00') {
            echo '<td>' . $row['l_leave_start_date'] . '<br> 15:45:00</td>';
        }
        // 16:15
        else if ($row['l_leave_start_time'] == '16:30:00' && $row['l_remark'] == '16:15:00') {
            echo '<td>' . $row['l_leave_start_date'] . '<br> 16:15:00</td>';
        }
        // 16:40
        else if ($row['l_leave_start_time'] == '17:00:00') {
            echo '<td>' . $row['l_leave_start_date'] . '<br> 16:40:00</td>';
        } else {
            // กรณีอื่น ๆ แสดงเวลาตาม l_leave_start_time
            echo '<td>' . $row['l_leave_start_date'] . '<br> ' . $row['l_leave_start_time'] . '</td>';
        }

        // 10
        // 08:45
        if ($row['l_leave_end_time'] == '09:00:00' && $row['l_remark'] == '08:45:00') {
            echo '<td>' . $row['l_leave_end_date'] . '<br> 08:45:00</td>';
        }
        // 09:45
        else if ($row['l_leave_end_time'] == '10:00:00' && $row['l_remark'] == '09:45:00') {
            echo '<td>' . $row['l_leave_end_date'] . '<br> 09:45:00</td>';
        }
        // 10:45
        else if ($row['l_leave_end_time'] == '11:00:00' && $row['l_remark'] == '10:45:00') {
            echo '<td>' . $row['l_leave_end_date'] . '<br> 10:45:00</td>';
        }
        // 11:45
        else if ($row['l_leave_end_time'] == '12:00:00') {
            echo '<td>' . $row['l_leave_end_date'] . '<br> 11:45:00</td>';
        }
        // 12:45
        else if ($row['l_leave_end_time'] == '13:00:00') {
            echo '<td>' . $row['l_leave_end_date'] . '<br> 12:45:00</td>';
        }
        // 13:15
        else if ($row['l_leave_end_time'] == '13:30:00' && $row['l_remark'] == '13:15:00') {
            echo '<td>' . $row['l_leave_end_date'] . '<br> 13:15:00</td>';
        }
        // 13:45
        else if ($row['l_leave_end_time'] == '14:00:00' && $row['l_remark'] == '13:45:00') {
            echo '<td>' . $row['l_leave_end_date'] . '<br> 13:45:00</td>';
        }
        // 14:15
        else if ($row['l_leave_end_time'] == '14:30:00' && $row['l_remark'] == '14:15:00') {
            echo '<td>' . $row['l_leave_end_date'] . '<br> 14:15:00</td>';
        }
        // 14:45
        else if ($row['l_leave_end_time'] == '15:00:00' && $row['l_remark'] == '14:45:00') {
            echo '<td>' . $row['l_leave_end_date'] . '<br> 14:45:00</td>';
        }
        // 15:15
        else if ($row['l_leave_end_time'] == '15:30:00' && $row['l_remark'] == '15:15:00') {
            echo '<td>' . $row['l_leave_end_date'] . '<br> 15:15:00</td>';
        }
        // 15:45
        else if ($row['l_leave_end_time'] == '16:00:00' && $row['l_remark'] == '15:45:00') {
            echo '<td>' . $row['l_leave_end_date'] . '<br> 15:45:00</td>';
        }
        // 16:15
        else if ($row['l_leave_end_time'] == '16:30:00' && $row['l_remark'] == '16:15:00') {
            echo '<td>' . $row['l_leave_end_date'] . '<br> 16:15:00</td>';
        }
        // 16:40
        else if ($row['l_leave_end_time'] == '17:00:00') {
            echo '<td>' . $row['l_leave_end_date'] . '<br> 16:40:00</td>';
        } else {
            // กรณีอื่น ๆ แสดงเวลาตาม l_leave_end_time
            echo '<td>' . $row['l_leave_end_date'] . '<br> ' . $row['l_leave_end_time'] . '</td>';
        }

        // 11
        echo '<td>';
        // Query to check holidays in the leave period
        $holiday_query = "SELECT COUNT(*) as holiday_count
                  FROM holiday
                  WHERE h_start_date BETWEEN :start_date AND :end_date
                  AND h_holiday_status = 'วันหยุด'
                  AND h_status = 0";

        // Prepare the query
        $holiday_stmt = $conn->prepare($holiday_query);
        $holiday_stmt->bindParam(':start_date', $row['l_leave_start_date']);
        $holiday_stmt->bindParam(':end_date', $row['l_leave_end_date']);
        $holiday_stmt->execute();

        // Fetch the holiday count
        $holiday_data = $holiday_stmt->fetch(PDO::FETCH_ASSOC);
        $holiday_count = $holiday_data['holiday_count'];
        // คำนวณระยะเวลาการลา
        $l_leave_start_date = new DateTime($row['l_leave_start_date'] . ' ' . $row['l_leave_start_time']);
        $l_leave_end_date = new DateTime($row['l_leave_end_date'] . ' ' . $row['l_leave_end_time']);
        $interval = $l_leave_start_date->diff($l_leave_end_date);

        // คำนวณจำนวนวันลา
        $leave_days = $interval->days - $holiday_count;

        // คำนวณจำนวนชั่วโมงและนาทีลา
        $leave_hours = $interval->h;
        $leave_minutes = $interval->i;

        // ตรวจสอบช่วงเวลาและหักชั่วโมงตามเงื่อนไข
        $start_hour = (int) $l_leave_start_date->format('H');
        $end_hour = (int) $l_leave_end_date->format('H');

        if (!((($start_hour >= 8 && $start_hour < 12) && ($end_hour <= 12)) ||
            (($start_hour >= 13 && $start_hour < 17) && ($end_hour <= 17)))) {
            // ถ้าไม่อยู่ในช่วงที่กำหนด ให้หัก 1 ชั่วโมง
            $leave_hours -= 1;
        }

        // ตรวจสอบการหักเวลาเมื่อเกิน 8 ชั่วโมง
        if ($leave_hours >= 8) {
            $leave_days += floor($leave_hours / 8);
            $leave_hours = $leave_hours % 8; // Remaining hours after converting to days
        }

        // ตรวจสอบการนาที
        if ($leave_minutes >= 30) {
            $leave_minutes = 30; // ถ้านาทีมากกว่าหรือเท่ากับ 30 นับเป็น 5 นาที
        }

        // แสดงผลลัพธ์
        if ($row['l_leave_id'] == 7) {
            echo '';
        } else {
            echo '<span class="text-primary">' . $leave_days . ' วัน ' . $leave_hours . ' ชั่วโมง ' . $leave_minutes . ' นาที</span>';
        }

        echo '</td>';

        // 12
        if (!empty($row['l_file'])) {
            echo '<td><button id="imgBtn" class="btn btn-primary" onclick="window.open(\'../upload/' . $row['l_file'] . '\', \'_blank\')"><i class="fa-solid fa-file"></i></button></td>';
        } else {
            echo '<td><button id="imgNoBtn" class="btn btn-primary" disabled><i class="fa-solid fa-file-excel"></i></button></td>';
        }

        // 13
        echo '<td>';
        if ($row['l_leave_status'] == 0) {
            echo '<span class="text-success">ปกติ</span>';
        } else {
            echo '<span class="text-danger">ยกเลิก</span>';
        }
        echo '</td>';

        // 14
        echo '<td>';
        if ($row['l_late_datetime'] == '') {
            echo '';
        } else {
            echo '<span class="text-success">ยืนยัน</span>';
        }
        echo '</td>';

        // 15
        echo '<td>';
        // รอหัวหน้าอนุมัติ
        if ($row['l_approve_status'] == 0) {
            echo '<div class="text-warning"><b>รอหัวหน้าอนุมัติ</b></div>';
        }
        // รอผจกอนุมัติ
        elseif ($row['l_approve_status'] == 1) {
            echo '<div class="text-warning"><b>รอผู้จัดการอนุมัติ</b></div>';
        }
        // หัวหน้าอนุมัติ
        elseif ($row['l_approve_status'] == 2) {
            echo '<div class="text-success"><b>หัวหน้าอนุมัติ</b></div>';
        }
        // หัวหน้าไม่อนุมัติ
        elseif ($row['l_approve_status'] == 3) {
            echo '<div class="text-danger"><b>หัวหน้าไม่อนุมัติ</b></div>';
        }
        //  ผจก อนุมัติ
        elseif ($row['l_approve_status'] == 4) {
            echo '<div class="text-success"><b>ผู้จัดการอนุมัติ</b></div>';
        }
        //  ผจก ไม่อนุมัติ
        elseif ($row['l_approve_status'] == 5) {
            echo '<div class="text-danger"><b>ผู้จัดการไม่อนุมัติ</b></div>';
        } elseif ($row['l_approve_status'] == 6) {
            echo '';
        }
        // ไม่มีสถานะ
        else {
            echo 'ไม่มีสถานะ';
        }
        echo '</td>';

        // 16
        echo '<td>';
        // รอหัวหน้าอนุมัติ
        if ($row['l_approve_status2'] == 0) {
            echo '<div class="text-warning"><b>รอหัวหน้าอนุมัติ</b></div>';
        }
        // รอผจกอนุมัติ
        elseif ($row['l_approve_status2'] == 1) {
            echo '<div class="text-warning"><b>รอผู้จัดการอนุมัติ</b></div>';
        }
        // หัวหน้าอนุมัติ
        elseif ($row['l_approve_status2'] == 2) {
            echo '<div class="text-success"><b>หัวหน้าอนุมัติ</b></div>';
        }
        // หัวหน้าไม่อนุมัติ
        elseif ($row['l_approve_status2'] == 3) {
            echo '<div class="text-danger"><b>หัวหน้าไม่อนุมัติ</b></div>';
        }
        //  ผจก อนุมัติ
        elseif ($row['l_approve_status2'] == 4) {
            echo '<div class="text-success"><b>ผู้จัดการอนุมัติ</b></div>';
        }
        //  ผจก ไม่อนุมัติ
        elseif ($row['l_approve_status2'] == 5) {
            echo '<div class="text-danger"><b>ผู้จัดการไม่อนุมัติ</b></div>';
        } elseif ($row['l_approve_status2'] == 6) {
            echo '';
        }
        // ไม่มีสถานะ
        else {
            echo 'ไม่มีสถานะ';
        }
        echo '</td>';

        // 17
        echo '<td>';
        // รอหัวหน้าอนุมัติ
        if ($row['l_approve_status3'] == 0) {
            echo '<div class="text-warning"><b>' . $strStatusProve0 . '</b></div>';
        }
        // รอผจกอนุมัติ
        elseif ($row['l_approve_status3'] == 1) {
            echo '<div class="text-warning"><b>' . $strStatusProve1 . '</b></div>';
        }
        // หัวหน้าอนุมัติ
        elseif ($row['l_approve_status3'] == 2) {
            echo '<div class="text-success"><b>' . $strStatusProve2 . '</b></div>';
        }
        // หัวหน้าไม่อนุมัติ
        elseif ($row['l_approve_status3'] == 3) {
            echo '<div class="text-danger"><b>' . $strStatusProve3 . '</b></div>';
        }
        //  ผจก อนุมัติ
        elseif ($row['l_approve_status3'] == 4) {
            echo '<div class="text-success"><b>' . $strStatusProve4 . '</b></div>';
        }
        //  ผจก ไม่อนุมัติ
        elseif ($row['l_approve_status3'] == 5) {
            echo '<div class="text-danger"><b>' . $strStatusProve5 . '</b></div>';
        }
        // ช่องว่าง
        elseif ($row['l_approve_status3'] == 6) {
            echo '';
        }
        // รอ GM
        elseif ($row['l_approve_status3'] == 7) {
            echo '<div class="text-warning"><b>' . 'รอ GM อนุมัติ' . '</b></div>';
        }
        // GM อนุมัติ
        elseif ($row['l_approve_status3'] == 8) {
            echo '<div class="text-success"><b>' . 'GM อนุมัติ' . '</b></div>';
        }
        // GM ไม่อนุมัติ
        elseif ($row['l_approve_status3'] == 9) {
            echo '<div class="text-danger"><b>' . 'GM ไม่อนุมัติ' . '</b></div>';
        }
        // ไม่มีสถานะ
        else {
            echo 'ไม่พบสถานะ';
        }
        echo '</td>';

        // 18
        echo '<td>';
        if ($row['l_hr_status'] == 0) {
            echo '<span class="text-warning"><b>รอตรวจสอบ</b></span>';
        } elseif ($row['l_hr_status'] == 1) {
            echo '<span class="text-success"><b>ผ่าน</b></span>';
        } else {
            echo '<span class="text-danger"><b>ไม่ผ่าน</b></span>';
        }
        echo '</td>';

        // 19
        $leaveDate = $row['l_leave_start_date']; // สมมติว่าใช้วันที่ลาหรือวันที่สิ้นสุด
        $currentDate = date('Y-m-d'); // วันที่ปัจจุบัน

        if ($leaveDate < $currentDate) {
            // ถ้าถึงวันที่ลาแล้วไม่ให้กดปุ่มแก้ไข
            echo '<td>';
            echo '<button type="button" class="button-shadow btn btn-warning edit-btn" disabled><i class="fa-solid fa-pen"></i> แก้ไข</button>';
            echo '</td>';
        } else {
            // ถ้ายังไม่ถึงวันที่ลา ให้แสดงปุ่มแก้ไขได้ปกติ
            echo '<td>';
            echo '<button type="button" class="button-shadow btn btn-warning edit-btn" data-createdatetime="' . $row['l_create_datetime'] . '" data-usercode="' . $userCode . '" data-bs-toggle="modal" data-bs-target="#editLeaveModal"><i class="fa-solid fa-pen"></i> แก้ไข</button>';
            echo '</td>';
        }

        // 20
        $disabled = $row['l_leave_status'] == 1 ? 'disabled' : '';
        $dateNow = date('Y-m-d');
        $disabledCancalCheck = (
            $row['l_approve_status'] != 0
            && $row['l_approve_status2'] != 1
            && $row['l_leave_start_date'] < $dateNow
        ) ? 'disabled' : '';

        $disabledConfirmCheck = ($row['l_late_datetime'] != null) ? 'disabled' : '';

        if ($row['l_leave_id'] == 6) {
            echo '<td></td>';
        } else if ($row['l_leave_id'] == 7) {
            echo '<td><button type="button" class="button-shadow btn btn-primary confirm-late-btn" data-createdatetime="' . $row['l_create_datetime'] . '" data-usercode="' . $userCode . '" ' . $disabled . $disabledConfirmCheck . '>ยืนยันรายการ</button></td>';
        } else if ($row['l_leave_id'] != 7) {
            echo '<td><button type="button" class="button-shadow btn btn-danger cancel-leave-btn" data-leaveid="' . $row['l_leave_id'] . '" data-createdatetime="' . $row['l_create_datetime'] . '" data-usercode="' . $userCode . '" ' . $disabled . $disabledCancalCheck . '><i class="fa-solid fa-times"></i> ยกเลิกรายการ</button></td>';
        } else {
            echo '<td></td>';
        }

        echo '</tr>';
        $rowNumber--;
        // echo '<td><img src="../upload/' . $row['Img_file'] . '" id="img" width="100" height="100"></td>';
    }
} else {
    echo "<tr><td colspan='12' style='color: red;'>ไม่พบข้อมูล</td></tr>";
}
// ปิดการเชื่อมต่อ
// $conn = null;
?>

                    </tbody>
                </table>
            </div>
            <?php
echo '<div class="pagination">';
echo '<ul class="pagination">';

// สร้างลิงก์ไปยังหน้าแรกหรือหน้าก่อนหน้า
if ($currentPage > 1) {
    echo '<li class="page-item"><a class="page-link" href="?page=1&month=' . urlencode($selectedMonth) . '">&laquo;</a></li>';
    echo '<li class="page-item"><a class="page-link" href="?page=' . ($currentPage - 1) . '&month=' . urlencode($selectedMonth) . '">&lt;</a></li>';
}

// สร้างลิงก์สำหรับแต่ละหน้า
for ($i = 1; $i <= $totalPages; $i++) {
    if ($i == $currentPage) {
        echo '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
    } else {
        echo '<li class="page-item"><a class="page-link" href="?page=' . $i . '&month=' . urlencode($selectedMonth) . '">' . $i . '</a></li>';
    }
}

// สร้างลิงก์ไปยังหน้าถัดไปหรือหน้าสุดท้าย
if ($currentPage < $totalPages) {
    echo '<li class="page-item"><a class="page-link" href="?page=' . ($currentPage + 1) . '&month=' . urlencode($selectedMonth) . '">&gt;</a></li>';
    echo '<li class="page-item"><a class="page-link" href="?page=' . $totalPages . '&month=' . urlencode($selectedMonth) . '">&raquo;</a></li>';
}

echo '</ul>';
echo '</div>';

?>
            <div class="modal fade" id="imageModal<?=$rowNumber?>" tabindex="-1" aria-labelledby="exampleModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">รูปภาพ</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <!-- แสดงรูปภาพ โดยเรียกใช้ชื่อฟิลด์ที่เก็บชื่อไฟล์ภาพ -->
                            <img src="../upload/<?=$row['Img_file']?>" class="img-fluid">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal แก้ไขใบลา -->
            <div class="modal fade" id="editLeaveModal" tabindex="-1" aria-labelledby="editLeaveModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editLeaveModalLabel">แก้ไขการลา</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="editLeaveForm">
                                <div class="row">
                                    <div class="col-12">
                                        <label for="leaveType" class="form-label">ประเภทการลา</label>
                                        <span style="color: red;">*</span>
                                        <select class="form-select editLeaveType" required>
                                            <option selected>เลือกประเภทการลา</option>
                                            <option value="1">ลากิจได้รับค่าจ้าง</option>
                                            <option value="2">ลากิจไม่ได้รับค่าจ้าง</option>
                                            <option value="3">ลาป่วย</option>
                                            <option value="4">ลาป่วยจากงาน</option>
                                            <option value="5">ลาพักร้อน</option>
                                            <option value="8">อื่น ๆ</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mt-3 row">
                                    <div class="col-12">
                                        <label for="leaveReason" class="form-label">เหตุผลการลา</label>
                                        <span style="color: red;">*</span>
                                        <textarea class="form-control mt-2" id="editLeaveReason" rows="3"
                                            placeholder="กรุณาระบุเหตุผล"></textarea>
                                    </div>
                                </div>

                                <div class="mt-3 row">
                                    <div class="col-6">
                                        <label for="editLeaveStartDate" class="form-label">วันที่เริ่มต้น</label>
                                        <span style="color: red;">*</span>
                                        <input type="text" class="form-control" id="editLeaveStartDate" required>
                                    </div>
                                    <div class=" col-6">
                                        <label for="editLeaveStartTime" class="form-label">เวลาที่เริ่มต้น</label>
                                        <span style="color: red;">* (<input class="form-label" id="editLeaveStartTime2"
                                                value="" style="border: none; width: 70px;  color: red;">เวลาเดิม)
                                        </span>
                                        <select class="form-select" id="editLeaveStartTime" name="editLeaveStartTime"
                                            required>
                                            <option value="08:00" selected>08:00</option>
                                            <option value="08:30">08:30</option>
                                            <option value="08:45">08:45</option>
                                            <option value="09:00">09:00</option>
                                            <option value="09:30">09:30</option>
                                            <option value="09:45">09:45</option>
                                            <option value="10:00">10:00</option>
                                            <option value="10:30">10:30</option>
                                            <option value="10:45">10:45</option>
                                            <option value="11:00">11:00</option>
                                            <option value="12:00">11:45</option>
                                            <option value="13:00">12:45</option>
                                            <option value="13:30">13:15</option>
                                            <option value="14:00">13:45</option>
                                            <option value="14:30">14:15</option>
                                            <option value="15:00">14:45</option>
                                            <option value="15:30">15:15</option>
                                            <option value="16:00">15:45</option>
                                            <option value="16:30">16:15</option>
                                            <option value="17:00">16:40</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mt-3 row">
                                    <div class="col-6">
                                        <label for="editleaveEndDate" class="form-label">วันที่สิ้นสุด</label>
                                        <span style="color: red;">*</span>
                                        <input type="text" class="form-control" id="editLeaveEndDate" required>
                                    </div>
                                    <div class="col-6">
                                        <label for="editleaveEndTime" class="form-label">เวลาที่สิ้นสุด</label>
                                        <span style="color: red;">* (<input class="form-label" id="editLeaveEndTime2"
                                                value="" style="border: none; width: 70px; color: red;">เวลาเดิม)
                                        </span><select class="form-select" id="editLeaveEndTime" name="editLeaveEndTime"
                                            required>
                                            <option value="08:00">08:00</option>
                                            <option value="08:30">08:30</option>
                                            <option value="08:45">08:45</option>
                                            <option value="09:00">09:00</option>
                                            <option value="09:30">09:30</option>
                                            <option value="09:45">09:45</option>
                                            <option value="10:00">10:00</option>
                                            <option value="10:30">10:30</option>
                                            <option value="10:45">10:45</option>
                                            <option value="11:00">11:00</option>
                                            <option value="12:00">11:45</option>
                                            <option value="13:00">12:45</option>
                                            <option value="13:30">13:15</option>
                                            <option value="14:00">13:45</option>
                                            <option value="14:30">14:15</option>
                                            <option value="15:00">14:45</option>
                                            <option value="15:30">15:15</option>
                                            <option value="16:00">15:45</option>
                                            <option value="16:30">16:15</option>
                                            <option value="17:00" selected>16:40</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mt-3 row">
                                    <div class="col-12">
                                        <label for="editTelPhone" class="form-label">เบอร์โทร</label>
                                        <input type="text" class="form-control" id="editTelPhone">
                                    </div>
                                </div>
                                <div class=" mt-3 row">
                                    <div class="col-12">
                                        <label for="editFile" class="form-label">ไฟล์แนบ (PNG, JPG, JPEG)</label>
                                        <input class="form-control" type="file" id="editFile" name="editFile" />
                                        <!-- แสดงชื่อไฟล์เดิม -->
                                        <small id="currentFile" class="form-text text-muted">
                                            <!-- ชื่อไฟล์เดิมจะแสดงที่นี่ -->
                                        </small>
                                        <!-- Preview รูป -->
                                        <div class="mt-3"
                                            style="display: flex; justify-content: center; align-items: center;">
                                            <img id="imagePreview" src="#" alt="Preview Image"
                                                style="max-width: 100%; display: none; width: 200px; height: 200px;" />
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">บันทึกการแก้ไข</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl, {
                html: true // ใช้ HTML ใน tooltip
            })
        })
        // เมื่อ urgentLeaveModal เปิด
        $('#urgentLeaveModal').on('show.bs.modal', function() {
            var totalDaysAlert = $('[name="totalDays"]');
            $('#urgentLeaveType').val('0'); // รีเซ็ตค่า select
            totalDaysAlert.text('คงเหลือ ' + '-' + ' วัน')
            $('*[name="alertCheckDays"]').addClass('d-none'); // ซ่อนข้อความ

        });

        function calculateLeaveDays(startDate, startTime, endDate, endTime) {
            var start = new Date(startDate + ' ' + startTime); // สร้างวันที่เริ่มต้น
            var end = new Date(endDate + ' ' + endTime); // สร้างวันที่สิ้นสุด

            // ตรวจสอบว่ามีการเลือกวันที่สิ้นสุดก่อนวันที่เริ่มต้นหรือไม่
            if (end <= start) {
                console.log("End date/time must be after start date/time."); // แจ้งเตือนเมื่อวันที่สิ้นสุดก่อน
                return 0; // คืนค่าศูนย์หรือจัดการในกรณีนี้ตามต้องการ
            }

            // คำนวณความแตกต่างในหน่วยมิลลิวินาที
            var timeDiff = end - start;

            // แปลงมิลลิวินาทีเป็นชั่วโมง
            var hours = timeDiff / (1000 * 60 * 60);
            console.log("Hours: ", hours); // แสดงจำนวนชั่วโมง

            // แปลงจำนวนชั่วโมงเป็นจำนวนวัน โดย 1 วัน = 7.40 ชั่วโมง
            var days = hours / 7.40;
            console.log("Calculated Leave Days: ", days); // แสดงจำนวนวันที่คำนวณได้

            return days; // คืนค่าจำนวนวันที่คำนวณได้
        }

        function checkDays(typeLeave) {
            var totalDaysAlert = $('[name="totalDays"]');
            var startDate = $('#startDate').val();
            var alertMessage = '';
            var remainingDays = 0,
                remainingHours = 0,
                remainingMinutes = 0;

            var currentLeaveDays = 0,
                currentLeaveHours = 0,
                currentLeaveMinutes = 0,
                totalLeave = 0;

            // เช็คปีว่าเป็นปีปัจจุบันหรือปีถัดไป
            var currentYear = new Date().getFullYear(); // ปีปัจจุบัน
            var startDateObj = new Date(startDate); // สร้างตัวแปร Date จาก startDate
            var startYear = startDateObj.getFullYear(); // ปีของวันที่ลาเริ่มต้น

            // เช็คว่า startDate เป็นปีถัดไปหรือไม่
            if (startYear > currentYear) {
                // รีเซ็ตสิทธิ์การลา
                if (typeLeave == 1) {
                    $('input[name="personal_days"]').val(0);
                    $('input[name="personal_hours"]').val(0);
                    $('input[name="personal_minutes"]').val(0);
                    currentLeaveDays = 0;
                    currentLeaveHours = 0;
                    currentLeaveMinutes = 0;
                    totalLeave = parseFloat($('input[name="total_personal"]').val()) || 0;
                } else if (typeLeave == 2) {
                    $('input[name="personnel_no_days"]').val(0);
                    $('input[name="personal_no_hours"]').val(0);
                    $('input[name="personal_no_minutes"]').val(0);
                    currentLeaveDays = 0;
                    currentLeaveHours = 0;
                    currentLeaveMinutes = 0;
                    totalLeave = parseFloat($('input[name="total_personal_no"]').val()) || 0;
                } else if (typeLeave == 3) {
                    $('input[name="sick_days"]').val(0);
                    $('input[name="sick_hours"]').val(0);
                    $('input[name="sick_minutes"]').val(0);
                    currentLeaveDays = 0;
                    currentLeaveHours = 0;
                    currentLeaveMinutes = 0;
                    totalLeave = parseFloat($('input[name="total_sick"]').val()) || 0;
                } else if (typeLeave == 4) {
                    $('input[name="sick_work_days"]').val(0);
                    $('input[name="sick_work_hours"]').val(0);
                    $('input[name="sick_work_minutes"]').val(0);
                    currentLeaveDays = 0;
                    currentLeaveHours = 0;
                    currentLeaveMinutes = 0;
                    totalLeave = parseFloat($('input[name="total_sick_work"]').val()) || 0;
                } else if (typeLeave == 5) {
                    $('input[name="annual_days"]').val(0);
                    $('input[name="annual_hours"]').val(0);
                    $('input[name="annual_minutes"]').val(0);
                    currentLeaveDays = 0;
                    currentLeaveHours = 0;
                    currentLeaveMinutes = 0;
                    totalLeave = parseFloat($('input[name="total_annual"]').val()) || 0;
                }
            } else {
                // กรณีปีปัจจุบันหรือปีที่เท่ากัน
                if (typeLeave == 1) {
                    currentLeaveDays = parseFloat($('input[name="personal_days"]').val()) || 0;
                    currentLeaveHours = parseFloat($('input[name="personal_hours"]').val()) || 0;
                    currentLeaveMinutes = parseFloat($('input[name="personal_minutes"]').val()) || 0;
                    totalLeave = parseFloat($('input[name="total_personal"]').val()) || 0;
                } else if (typeLeave == 2) {
                    currentLeaveDays = parseFloat($('input[name="personnel_no_days"]').val()) || 0;
                    currentLeaveHours = parseFloat($('input[name="personal_no_hours"]').val()) || 0;
                    currentLeaveMinutes = parseFloat($('input[name="personal_no_minutes"]').val()) || 0;
                    totalLeave = parseFloat($('input[name="total_personal_no"]').val()) || 0;
                } else if (typeLeave == 3) {
                    currentLeaveDays = parseFloat($('input[name="sick_days"]').val()) || 0;
                    currentLeaveHours = parseFloat($('input[name="sick_hours"]').val()) || 0;
                    currentLeaveMinutes = parseFloat($('input[name="sick_minutes"]').val()) || 0;
                    totalLeave = parseFloat($('input[name="total_sick"]').val()) || 0;
                } else if (typeLeave == 4) {
                    currentLeaveDays = parseFloat($('input[name="sick_work_days"]').val()) || 0;
                    currentLeaveHours = parseFloat($('input[name="sick_work_hours"]').val()) || 0;
                    currentLeaveMinutes = parseFloat($('input[name="sick_work_minutes"]').val()) || 0;
                    totalLeave = parseFloat($('input[name="total_sick_work"]').val()) || 0;
                } else if (typeLeave == 5) {
                    currentLeaveDays = parseFloat($('input[name="annual_days"]').val()) || 0;
                    currentLeaveHours = parseFloat($('input[name="annual_hours"]').val()) || 0;
                    currentLeaveMinutes = parseFloat($('input[name="annual_minutes"]').val()) || 0;
                    totalLeave = parseFloat($('input[name="total_annual"]').val()) || 0;
                }
            }

            // แปลงนาทีที่ใช้ไปเป็น 30 นาที
            if (currentLeaveMinutes > 0 && currentLeaveMinutes <= 30) {
                currentLeaveMinutes = 30;
            } else {
                currentLeaveMinutes = 0;
            }

            // คำนวณเวลาที่ใช้ไปทั้งหมด (ในหน่วยนาที)
            var totalCurrentMinutes = (currentLeaveDays * 8 * 60) + (currentLeaveHours * 60) + currentLeaveMinutes;

            // สิทธิ์ทั้งหมดแปลงเป็นนาที
            var totalLeaveMinutes = totalLeave * 8 * 60;

            // คำนวณเวลาที่เหลือ (ในหน่วยนาที)
            var remainingMinutesTotal = totalLeaveMinutes - totalCurrentMinutes;

            if (remainingMinutesTotal < 0) {
                alertMessage = 'ไม่สามารถลาได้ ใช้สิทธิ์ครบกำหนดแล้ว';
                totalDaysAlert.text('คงเหลือ 0 วัน 0 ชั่วโมง 0 นาที');
            } else {
                remainingDays = Math.floor(remainingMinutesTotal / (8 * 60)); // แปลงนาทีที่เหลือเป็นวัน
                remainingHours = Math.floor((remainingMinutesTotal % (8 * 60)) / 60); // แปลงนาทีเป็นชั่วโมง
                remainingMinutes = remainingMinutesTotal % 60; // นาทีที่เหลือ

                // ปรับนาทีเป็น 30 นาที
                if (remainingMinutes > 0 && remainingMinutes <= 30) {
                    remainingMinutes = 30;
                } else {
                    remainingMinutes = 0;
                }

                // ถ้าชั่วโมงเกิน 8 ให้เพิ่มวัน
                if (remainingHours >= 8) {
                    remainingDays += Math.floor(remainingHours / 8);
                    remainingHours = remainingHours % 8;
                }

                totalDaysAlert.text(
                    `คงเหลือ ${remainingDays} วัน ${remainingHours} ชั่วโมง ${remainingMinutes} นาที`
                );
            }

            if (alertMessage) {
                $('*[name="alertCheckDays"]').text(alertMessage).removeClass('d-none');
            } else {
                $('*[name="alertCheckDays"]').addClass('d-none');
            }
        }

        $(document).ready(function() {
            $('.filter-card').click(function() {
                var leaveType = $(this).data('leave-id'); // Get leave ID dynamically
                var userCode = '<?php echo $userCode; ?>';
                var depart = '<?php echo $depart; ?>';
                var selectedYear = <?php echo json_encode($selectedYear); ?>;
                var nameType = '';

                if (leaveType == 1) {
                    nameType = "ลากิจได้รับค่าจ้าง";
                } else if (leaveType == 2) {
                    nameType = "ลากิจไม่ได้รับค่าจ้าง";
                } else if (leaveType == 3) {
                    nameType = "ลาป่วย";
                } else if (leaveType == 4) {
                    nameType = "ลาป่วยจากงาน";
                } else if (leaveType == 5) {
                    nameType = "ลาพักร้อน";
                } else if (leaveType == 6) {
                    nameType = "หยุดงาน";
                } else if (leaveType == 7) {
                    nameType = "มาสาย";
                } else if (leaveType == 8) {
                    nameType = "อื่น ๆ";
                }
                $.ajax({
                    url: 'm_ajax_get_detail.php',
                    method: 'POST',
                    data: {
                        leaveType: leaveType,
                        userCode: userCode,
                        selectedYear: selectedYear,
                        depart: depart
                    },
                    success: function(response) {
                        $('#leaveDetailsModal .modal-title').text(nameType);
                        $('#leaveDetailsModal .modal-body').html(response);
                        $('#leaveDetailsModal').modal('show');
                    },
                    error: function(xhr, status, error) {
                        alert('เกิดข้อผิดพลาด: ' + error);
                    }
                });
            });
            $.ajax({
                url: 'm_ajax_get_holiday.php', // สร้างไฟล์ PHP เพื่อตรวจสอบวันหยุด
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    var today = new Date(); // วันที่ปัจจุบัน

                    // สร้างปฏิทิน Flatpickr พร้อมปิดวันที่เป็นวันหยุด และไม่สามารถเลือกวันที่ก่อนหน้าวันที่ปัจจุบันได้
                    flatpickr("#startDate", {
                        dateFormat: "d-m-Y", // ตั้งค่าเป็น วัน/เดือน/ปี
                        defaultDate: today, // กำหนดวันที่เริ่มต้นเป็นวันที่ปัจจุบัน
                        // minDate: today, // ห้ามเลือกวันที่ในอดีต
                        disable: response.holidays // ปิดวันที่ที่เป็นวันหยุด
                    });

                    flatpickr("#endDate", {
                        dateFormat: "d-m-Y", // ตั้งค่าเป็น วัน/เดือน/ปี
                        defaultDate: today, // กำหนดวันที่สิ้นสุดเป็นวันที่ปัจจุบัน
                        // minDate: today, // ห้ามเลือกวันที่ในอดีต
                        disable: response.holidays // ปิดวันที่ที่เป็นวันหยุด
                    });

                    flatpickr("#urgentStartDate", {
                        dateFormat: "d-m-Y", // ตั้งค่าเป็น วัน/เดือน/ปี
                        defaultDate: today, // กำหนดวันที่เริ่มต้นเป็นวันที่ปัจจุบัน
                        // minDate: today, // ห้ามเลือกวันที่ในอดีต
                        disable: response.holidays // ปิดวันที่ที่เป็นวันหยุด
                    });

                    flatpickr("#urgentEndDate", {
                        dateFormat: "d-m-Y", // ตั้งค่าเป็น วัน/เดือน/ปี
                        defaultDate: today, // กำหนดวันที่สิ้นสุดเป็นวันที่ปัจจุบัน
                        // minDate: today, // ห้ามเลือกวันที่ในอดีต
                        disable: response.holidays // ปิดวันที่ที่เป็นวันหยุด
                    });
                }
            });

            // ยื่นใบลา
            $('#leaveForm').submit(function(e) {
                e.preventDefault(); // ป้องกันฟอร์มจากการส่งอย่างปกติ

                var fd = new FormData(this);

                // เพิ่มข้อมูลจาก PHP variables
                fd.append('userCode', '<?php echo $userCode; ?>');
                fd.append('userName', '<?php echo $userName; ?>');
                fd.append('name', '<?php echo $name; ?>');
                fd.append('telPhone', '<?php echo $telPhone; ?>');
                fd.append('depart', '<?php echo $depart; ?>');
                fd.append('level', '<?php echo $level; ?>');
                fd.append('workplace', '<?php echo $workplace; ?>');
                fd.append('subDepart', '<?php echo $subDepart; ?>');
                fd.append('subDepart2', '<?php echo $subDepart2; ?>');
                fd.append('subDepart3', '<?php echo $subDepart3; ?>');
                fd.append('subDepart4', '<?php echo $subDepart4; ?>');
                fd.append('subDepart5', '<?php echo $subDepart5; ?>');

                // ดึงค่าจากฟอร์ม
                var leaveType = $('#leaveType').val();
                var leaveReason = $('#leaveReason').val();
                var startDate = $('#startDate').val();
                var startTime = $('#startTime').val();
                var endDate = $('#endDate').val();
                var endTime = $('#endTime').val();
                var files = $('#file')[0].files;

                var createDate = new Date();

                var year = createDate.getFullYear();
                var month = ("0" + (createDate.getMonth() + 1)).slice(-2); // Months are zero-based
                var day = ("0" + createDate.getDate()).slice(-2);

                var hours = ("0" + createDate.getHours()).slice(-2);
                var minutes = ("0" + createDate.getMinutes()).slice(-2);
                var seconds = ("0" + createDate.getSeconds()).slice(-2);

                var formattedDate = year + "-" + month + "-" + day + " " + hours + ":" + minutes +
                    ":" +
                    seconds;


                // เช็คว่าหากเหตุผลในการลาเป็น "อื่น ๆ" ให้ใช้ค่าจาก input ที่มี id="otherReason"
                /*  if (leaveReason === 'อื่น ๆ') {
                     leaveReason = $('#otherReason').val();
                 } */

                // เพิ่มข้อมูลจากฟอร์มลงใน FormData object
                fd.append('leaveType', leaveType);
                fd.append('leaveReason', leaveReason);
                fd.append('startDate', startDate);
                fd.append('startTime', startTime);
                fd.append('endDate', endDate);
                fd.append('endTime', endTime);
                fd.append('file', files[0]);
                fd.append('formattedDate', formattedDate);


                // ตรวจสอบหากมี alert ถูกแสดง (ไม่มี class d-none)
                if (!$('#alertCheckDays').hasClass('d-none')) {
                    Swal.fire({
                        title: "ไม่สามารถลาได้",
                        text: "ใช้สิทธิ์หมดแล้ว กรุณาเปลี่ยนประเภทการลา",
                        icon: "error"
                    });
                    console.log("Cannot submit form, alert is visible.");
                    return false; // หยุดการส่งฟอร์ม
                }

                console.log(leaveReason, startTime, endTime);
                if (leaveType == 'เลือกประเภทการลา') {
                    Swal.fire({
                        title: "ไม่สามารถลาได้",
                        text: "กรุณาเลือกประเภทการลา",
                        icon: "error"
                    });
                    return false;
                } else if (leaveReason == '') {
                    Swal.fire({
                        title: "ไม่สามารถลาได้",
                        text: "กรุณาระบุเหตุผลการลา",
                        icon: "error"
                    });
                    return false;
                } else {
                    // ลบ - ออกจากวันที่
                    var startDate = $('#startDate').val().replace(/-/g, '');
                    var endDate = $('#endDate').val().replace(/-/g, '');
                    var startTime = $('#startTime').val(); // เช่น "08:00"
                    var endTime = $('#endTime').val(); // เช่น "17:00"

                    // ตรวจสอบว่าค่าวันที่มีค่าหรือไม่
                    if (!startDate || !endDate || !startTime || !endTime) {
                        Swal.fire({
                            title: "ข้อผิดพลาด",
                            text: "กรุณาเลือกวันที่เริ่มต้น, วันที่สิ้นสุด, เวลาเริ่มต้น และเวลาเสร็จสิ้น",
                            icon: "error"
                        });
                        return false; // หยุดการทำงาน
                    }

                    // แปลงวันที่เป็นรูปแบบ Date พร้อมเวลา
                    var start = new Date(startDate.substring(0, 4), startDate.substring(4, 6) - 1,
                        startDate
                        .substring(6, 8), startTime.split(':')[0], startTime.split(':')[1]);
                    var end = new Date(endDate.substring(0, 4), endDate.substring(4, 6) - 1, endDate
                        .substring(6, 8), endTime.split(':')[0], endTime.split(':')[1]);

                    // ตรวจสอบว่าการแปลงวันที่สำเร็จหรือไม่
                    if (isNaN(start.getTime()) || isNaN(end.getTime())) {
                        Swal.fire({
                            title: "ข้อผิดพลาด",
                            text: "วันที่หรือเวลาไม่ถูกต้อง กรุณาตรวจสอบอีกครั้ง",
                            icon: "error"
                        });
                        return false; // หยุดการทำงาน
                    }

                    // คำนวณความแตกต่างของวันและเวลา
                    var timeDiff = end - start; // ความแตกต่างเป็นมิลลิวินาที
                    var fullDays = Math.floor(timeDiff / (1000 * 3600 * 8)); // จำนวนวันเต็ม
                    var remainingTimeInMs = timeDiff % (1000 * 3600 *
                        8); // มิลลิวินาทีที่เหลือจากวันเต็ม
                    var hoursDiff = Math.floor(remainingTimeInMs / (1000 *
                        3600)); // จำนวนชั่วโมงที่เหลือ
                    var minutesDiff = Math.floor((remainingTimeInMs % (1000 * 3600)) / (1000 *
                        60)); // คำนวณนาทีที่เหลือ

                    // คำนวณวันที่รวมทั้งหมดเป็นทศนิยม (เช่น 2.5 สำหรับ 2 วันกับ 4 ชั่วโมง)
                    var totalDaysWithHoursAndMinutes = fullDays + (hoursDiff / 8) + (minutesDiff /
                        480); // ใช้ 8 ชั่วโมงและ 480 นาทีต่อวันเป็นฐาน

                    // console.log(totalDaysWithHoursAndMinutes); // แสดงผลลัพธ์ใน console

                    // เงื่อนไขสำหรับ leaveType = 3
                    if (leaveType == 3) {
                        if (totalDaysWithHoursAndMinutes > 219145.125) { // หากเวลาลามากกว่า 3 วัน
                            if (files.length === 0) {
                                Swal.fire({
                                    title: "ไม่สามารถลาได้",
                                    text: "กรุณาแนบไฟล์เมื่อลาเกิน 3 วัน",
                                    icon: "error"
                                });
                                return false;
                            }
                        }
                    }

                    // ลากิจ, ลาพักร้อนให้ลาล่วงหน้า 1 วัน
                    if (leaveType == 1 || leaveType == 5) {
                        var startDate = $('#startDate').val();
                        var parts = startDate.split('-');
                        var formattedDate = parts[2] + '-' + parts[1] + '-' + parts[
                            0]; // เปลี่ยนเป็น 'YYYY-MM-DD'

                        // สร้าง Date object โดยไม่ต้องตั้งเวลา
                        var leaveStartDate = new Date(formattedDate +
                            'T00:00:00'); // ตั้งเวลาเป็น 00:00:00

                        var currentDate = new Date();
                        currentDate.setHours(0, 0, 0, 0); // ตั้งเวลาเป็น 00:00:00

                        console.log("leaveStartDate :" + leaveStartDate);
                        console.log("currentDate: " + currentDate);

                        // เช็คว่า startDate เก่ากว่าหรือไม่
                        if (leaveStartDate <= currentDate) {
                            Swal.fire({
                                title: "ไม่สามารถลาได้",
                                text: "กรุณายื่นลาล่วงหน้าก่อน 1 วัน",
                                icon: "error"
                            });
                            return false;
                        }
                    }

                    var checkStartDate = $('#startDate').val();
                    var checkEndDate = $('#endDate').val();

                    // แปลงวันที่จาก string เป็น Date object
                    var startDateParts = checkStartDate.split("-");
                    var endDateParts = checkEndDate.split("-");

                    // แปลงเป็น Date object
                    var startDate = new Date(startDateParts[2], startDateParts[1] - 1,
                        startDateParts[
                            0]); // ปี, เดือน (0-based), วัน
                    var endDate = new Date(endDateParts[2], endDateParts[1] - 1, endDateParts[
                        0]); // ปี, เดือน (0-based), วัน

                    // แสดงข้อมูลวันที่ที่ถูกแปลงแล้ว (ตรวจสอบได้)
                    // alert("Start Date:" + startDate);
                    // alert("End Date:" + endDate);

                    // ตรวจสอบวันที่
                    if (endDate < startDate) {
                        Swal.fire({
                            title: "ไม่สามารถลาได้",
                            text: "กรุณาเลือกวันที่เริ่มต้นลาใหม่",
                            icon: "error"
                        });
                        return false;
                    } else { // ปิดการใช้งานปุ่มส่งข้อมูลและแสดงสถานะการโหลด
                        $('#btnSubmitForm1').prop('disabled', true);
                        $('#btnSubmitForm1').html(
                            '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> <span role="status">Loading...</span>'
                        );
                        $.ajax({
                            url: 'm_ajax_add_leave.php',
                            type: 'POST',
                            data: fd,
                            contentType: false,
                            processData: false,
                            success: function(response) {
                                console.log(response)
                                alert('บันทึกคำขอลาสำเร็จ');
                                location.reload();
                            },
                            error: function() {
                                alert('เกิดข้อผิดพลาดในการบันทึกคำขอลา');
                                location.reload();
                            }
                        });
                    }
                }
            });

            // ลาฉุกเฉิน
            $('#urgentLeaveForm').submit(function(e) {
                e.preventDefault();

                var fd = new FormData(this);

                // เพิ่มข้อมูลจาก PHP variables
                fd.append('userCode', '<?php echo $userCode; ?>');
                fd.append('userName', '<?php echo $userName; ?>');
                fd.append('name', '<?php echo $name; ?>');
                fd.append('telPhone', '<?php echo $telPhone; ?>');
                fd.append('depart', '<?php echo $depart; ?>');
                fd.append('level', '<?php echo $level; ?>');
                fd.append('workplace', '<?php echo $workplace; ?>');
                fd.append('subDepart', '<?php echo $subDepart; ?>');
                fd.append('subDepart2', '<?php echo $subDepart2; ?>');
                fd.append('subDepart3', '<?php echo $subDepart3; ?>');
                fd.append('subDepart4', '<?php echo $subDepart4; ?>');
                fd.append('subDepart5', '<?php echo $subDepart5; ?>');

                // ดึงค่าจากฟอร์ม
                var urgentLeaveType = $('#urgentLeaveType').val();
                var urgentLeaveReason = $('#urgentLeaveReason').val();
                var urgentStartDate = $('#urgentStartDate').val();
                var urgentStartTime = $('#urgentStartTime').val();
                var urgentEndDate = $('#urgentEndDate').val();
                var urgentEndTime = $('#urgentEndTime').val();
                var urgentFiles = $('#urgentFile')[0].files;

                // ตรวจสอบเหตุผลการลา "อื่น ๆ"
                /* if (urgentLeaveReason === 'อื่น ๆ') {
                    urgentLeaveReason = $('#urgentOtherReason').val();
                } */

                // เพิ่มข้อมูลจากฟอร์มลงใน FormData object
                fd.append('urgentLeaveType', urgentLeaveType);
                fd.append('urgentLeaveReason', urgentLeaveReason);
                fd.append('urgentStartDate', urgentStartDate);
                fd.append('urgentStartTime', urgentStartTime);
                fd.append('urgentEndDate', urgentEndDate);
                fd.append('urgentEndTime', urgentEndTime);

                if (urgentFiles.length > 0) {
                    fd.append('urgentFile', urgentFiles[0]);
                }
                console.log(urgentLeaveType)
                // ตรวจสอบประเภทการลา
                if (urgentLeaveType == '0') {
                    Swal.fire({
                        title: "ไม่สามารถลาได้",
                        text: "กรุณาเลือกประเภทการลา",
                        icon: "error"
                    });
                    return false;
                } else if (urgentLeaveReason == '') {
                    Swal.fire({
                        title: "ไม่สามารถลาได้",
                        text: "กรุณาระบุเหตุผลการลา",
                        icon: "error"
                    });
                    return false;
                } else {
                    $.ajax({
                        url: 'm_ajax_add_urgent_leave.php',
                        type: 'POST',
                        data: fd,
                        contentType: false,
                        processData: false,
                        success: function(response) {
                            Swal.fire({
                                title: 'สำเร็จ',
                                text: 'บันทึกคำขอลาเร่งด่วนสำเร็จ',
                                icon: 'success'
                            }).then(() => {
                                $('#urgentLeaveModal').modal('hide');
                                location.reload();
                            });
                        },
                        error: function() {
                            Swal.fire({
                                title: 'ผิดพลาด',
                                text: 'เกิดข้อผิดพลาดในการบันทึกคำขอลาเร่งด่วน',
                                icon: 'error'
                            });
                        }
                    });
                }

            });

            $('.cancel-leave-btn').click(function() {
                var rowData = $(this).closest('tr').children('td');
                var leaveId = $(this).data('leaveid');
                var createDatetime = $(this).closest('tr').find('td:eq(7)').text();
                var usercode = $(this).data('usercode');
                var name = "<?php echo $name ?>";
                var leaveType = $(rowData[0]).text();
                var depart = $(rowData[1]).text();
                var leaveReason = $(rowData[2]).text();
                var startDate = $(rowData[9]).text();
                var endDate = $(rowData[10]).text();
                var leaveStatus = 'ยกเลิก';
                var workplace = "<?php echo $workplace ?>";
                var subDepart = "<?php echo $subDepart ?>";
                var subDepart2 = "<?php echo $subDepart2 ?>";
                var subDepart3 = "<?php echo $subDepart3 ?>";
                var subDepart4 = "<?php echo $subDepart4 ?>";
                var subDepart5 = "<?php echo $subDepart5 ?>";


                // alert(endDate)
                Swal.fire({
                    title: "ต้องการยกเลิกรายการ ?",
                    icon: "question",
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'ใช่',
                    cancelButtonText: 'ไม่'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // ยืนยันก่อนส่ง AJAX request
                        $.ajax({
                            url: 'm_ajax_delete_leave.php',
                            method: 'POST',
                            data: {
                                leaveId: leaveId,
                                createDatetime: createDatetime,
                                usercode: usercode,
                                name: name,
                                leaveType: leaveType,
                                leaveReason: leaveReason,
                                startDate: startDate,
                                endDate: endDate,
                                depart: depart,
                                leaveStatus: leaveStatus,
                                workplace: workplace,
                                subDepart: subDepart,
                                subDepart2: subDepart2,
                                subDepart3: subDepart3,
                                subDepart4: subDepart4,
                                subDepart5: subDepart5

                            },
                            success: function(response) {
                                Swal.fire({
                                    title: 'ยกเลิกใบลาสำเร็จ',
                                    icon: 'success'
                                }).then(() => {
                                    location
                                        .reload(); // โหลดหน้าใหม่หลังจากยกเลิกใบลา
                                });
                            },
                            error: function() {
                                alert('มีบางอย่างผิดพลาด');
                            }
                        });
                    }
                });
            });
            $('.confirm-late-btn').click(function() {
                var rowData = $(this).closest('tr').children('td');
                var createDatetime = $(this).data('createdatetime');
                var userCode = $(this).data('usercode');
                var userName = "<?php echo $userName ?>";
                var comfirmName = "<?php echo $name ?>";
                var workplace = "<?php echo $workplace ?>";
                // var leaveType = $(rowData[0]).text();
                var depart = $(rowData[1]).text();
                var lateDate = $(rowData[3]).text();
                var lateStart = $(rowData[4]).text();
                var lateEnd = $(rowData[5]).text();
                var leaveStatus = $(rowData[13]).text();

                alert(lateDate)
                Swal.fire({
                    title: "ยืนยันรายการมาสาย ?",
                    icon: "question",
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#DC3545',
                    confirmButtonText: 'ยืนยัน',
                    cancelButtonText: 'ยกเลิก'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'm_upd_late_time.php',
                            method: 'POST',
                            data: {
                                userName: userName,
                                createDateTime: createDatetime,
                                depart: depart,
                                lateDate: lateDate,
                                lateStart: lateStart,
                                lateEnd: lateEnd,
                                userCode: userCode,
                                comfirmName: comfirmName,
                                leaveStatus: leaveStatus,
                                workplace: workplace,
                                action: 'confirm'
                            },
                            success: function(response) {
                                Swal.fire({
                                    title: 'ยืนยันสำเร็จ',
                                    icon: 'success'
                                }).then(() => {
                                    location
                                        .reload();
                                });
                            },
                            error: function() {
                                Swal.fire({
                                    title: 'มีบางอย่างผิดพลาด',
                                    icon: 'error'
                                });
                            }
                        });
                    }
                });
            });
            $('.edit-btn').click(function() {
                var createDatetime = $(this).data('createdatetime'); // ดึงค่า createDatetime
                var userCode = $(this).data('usercode');

                // ตั้งค่า createDatetime ให้กับฟอร์ม
                $('#editLeaveForm').data('createdatetime', createDatetime);

                $.ajax({
                    url: 'm_ajax_get_leave.php', // ไฟล์ PHP ที่ดึงข้อมูล
                    type: 'POST',
                    data: {
                        createDatetime: createDatetime,
                        userCode: userCode
                    },
                    dataType: 'json', // แจ้งว่าเราคาดหวังผลลัพธ์เป็น JSON
                    success: function(response) {
                        if (response.error) {
                            alert(response.error); // แสดงข้อความข้อผิดพลาด
                        } else {

                            // ใส่ข้อมูลในฟอร์ม Modal
                            $('.editLeaveType').val(response.l_leave_id);
                            $('#editLeaveReason').val(response.l_leave_reason);
                            $('#editLeaveStartDate').val(response.l_leave_start_date);
                            $('#editLeaveEndDate').val(response.l_leave_end_date);
                            $('#editTelPhone').val(response.l_phone);

                            var existingFile = response.l_file;

                            // ถ้ามีไฟล์เดิม ให้แสดงชื่อไฟล์และพรีวิว
                            if (existingFile && existingFile.trim() !== "") {
                                // แสดงชื่อไฟล์
                                $('#currentFile').text('ไฟล์เดิม: ' + existingFile);

                                // สร้าง URL สำหรับไฟล์พรีวิว
                                var fileUrl = '../upload/' +
                                    existingFile; // เปลี่ยนเป็นเส้นทางของไฟล์ที่ถูกต้องในเซิร์ฟเวอร์

                                // แสดงรูปพรีวิว
                                var previewImage = $(
                                    '#imagePreview'); // สมมติว่ามี <img id="imagePreview">
                                previewImage.attr('src',
                                    fileUrl); // ตั้งค่า src ของรูปให้เป็น URL ของไฟล์
                                previewImage.show(); // แสดงรูปพรีวิว
                            } else {
                                // ถ้าไม่มีไฟล์ ให้เคลียร์ชื่อไฟล์และซ่อนรูปพรีวิว
                                $('#currentFile').text('');
                                $('#imagePreview').hide(); // ซ่อนรูปพรีวิวเมื่อไม่มีไฟล์
                            }


                            // เวลาที่เริ่มต้น
                            // 08:45
                            if (response.l_leave_start_time === "09:00:00" && response
                                .l_remark === "08:45:00") {
                                $('#editLeaveStartTime2').val(response.l_remark);
                            }
                            // 09:45
                            else if (response.l_leave_start_time === "10:00:00" && response
                                .l_remark === "09:45:00") {
                                $('#editLeaveStartTime2').val(response.l_remark);
                            }
                            // 10:45
                            else if (response.l_leave_start_time === "11:00:00" && response
                                .l_remark === "10:45:00") {
                                $('#editLeaveStartTime2').val(response.l_remark);
                            }
                            // 11:45
                            else if (response.l_leave_start_time === "12:00:00" && response
                                .l_remark === "11:45:00") {
                                $('#editLeaveStartTime2').val(response.l_remark);
                            }
                            // 12:45
                            else if (response.l_leave_start_time === "13:00:00") {
                                $('#editLeaveStartTime2').val('12:45:00'); // กำหนดค่าใหม่
                            }
                            // 13:10
                            else if (response.l_leave_start_time === "13:30:00" && response
                                .l_remark === "13:10:00") {
                                $('#editLeaveStartTime2').val(response.l_remark);
                            }
                            // 13:40
                            else if (response.l_leave_start_time === "14:00:00" && response
                                .l_remark === "13:40:00") {
                                $('#editLeaveStartTime2').val(response.l_remark);
                            }
                            // 13:45
                            else if (response.l_leave_start_time === "14:00:00" && response
                                .l_remark === "13:45:00") {
                                $('#editLeaveStartTime2').val(response.l_remark);
                            }
                            // 14:10
                            else if (response.l_leave_start_time === "14:30:00" && response
                                .l_remark === "14:10:00") {
                                $('#editLeaveStartTime2').val(response.l_remark);
                            }
                            // 14:40
                            else if (response.l_leave_start_time === "15:00:00" && response
                                .l_remark === "14:40:00") {
                                $('#editLeaveStartTime2').val(response.l_remark);
                            }
                            // 14:45
                            else if (response.l_leave_start_time === "15:00:00" && response
                                .l_remark === "14:45:00") {
                                $('#editLeaveStartTime2').val(response.l_remark);
                            }
                            // 15:10
                            else if (response.l_leave_start_time === "15:30:00" && response
                                .l_remark === "15:10:00") {
                                $('#editLeaveStartTime2').val(response.l_remark);
                            }
                            // 15:40
                            else if (response.l_leave_start_time === "16:00:00" && response
                                .l_remark === "15:40:00") {
                                $('#editLeaveStartTime2').val(response.l_remark);
                            }
                            // 15:45
                            else if (response.l_leave_start_time === "16:00:00" && response
                                .l_remark === "15:45:00") {
                                $('#editLeaveStartTime2').val(response.l_remark);
                            }
                            // 16:10
                            else if (response.l_leave_start_time === "16:30:00" && response
                                .l_remark === "16:10:00") {
                                $('#editLeaveStartTime2').val(response.l_remark);
                            }
                            // 16:40
                            else if (response.l_leave_start_time === "17:00:00") {
                                $('#editLeaveStartTime2').val('16:40:00');
                            } else {
                                $('#editLeaveStartTime2').val(response
                                    .l_leave_start_time);
                            }

                            // เวลาที่สิ้นสุด
                            // 08:45
                            if (response.l_leave_end_time === "09:00:00" && response
                                .l_remark === "08:45:00") {
                                $('#editLeaveEndTime2').val(response.l_remark);
                            }
                            // 09:45
                            else if (response.l_leave_end_time === "10:00:00" && response
                                .l_remark === "09:45:00") {
                                $('#editLeaveEndTime2').val(response.l_remark);
                            }
                            // 10:45
                            else if (response.l_leave_end_time === "11:00:00" && response
                                .l_remark === "10:45:00") {
                                $('#editLeaveEndTime2').val(response.l_remark);
                            }
                            // 11:45
                            else if (response.l_leave_end_time === "12:00:00" && response
                                .l_remark === "11:45:00") {
                                $('#editLeaveEndTime2').val(response.l_remark);
                            }
                            // 12:45
                            else if (response.l_leave_end_time === "13:00:00" && response
                                .l_remark === "12:45:00") {
                                $('#editLeaveEndTime2').val('12:45:00'); // กำหนดค่าใหม่
                            }
                            // 13:10
                            else if (response.l_leave_end_time === "13:30:00" && response
                                .l_remark === "13:10:00") {
                                $('#editLeaveEndTime2').val(response.l_remark);
                            }
                            // 13:40
                            else if (response.l_leave_end_time === "14:00:00" && response
                                .l_remark === "13:40:00") {
                                $('#editLeaveEndTime2').val(response.l_remark);
                            }
                            // 13:45
                            else if (response.l_leave_end_time === "14:00:00" && response
                                .l_remark === "13:45:00") {
                                $('#editLeaveEndTime2').val(response.l_remark);
                            }
                            // 14:10
                            else if (response.l_leave_end_time === "14:30:00" && response
                                .l_remark === "14:10:00") {
                                $('#editLeaveEndTime2').val(response.l_remark);
                            }
                            // 14:40
                            else if (response.l_leave_end_time === "15:00:00" && response
                                .l_remark === "14:40:00") {
                                $('#editLeaveEndTime2').val(response.l_remark);
                            }
                            // 14:45
                            else if (response.l_leave_end_time === "15:00:00" && response
                                .l_remark === "14:45:00") {
                                $('#editLeaveEndTime2').val(response.l_remark);
                            }
                            // 15:10
                            else if (response.l_leave_end_time === "15:30:00" && response
                                .l_remark === "15:10:00") {
                                $('#editLeaveEndTime2').val(response.l_remark);
                            }
                            // 15:40
                            else if (response.l_leave_end_time === "16:00:00" && response
                                .l_remark === "15:40:00") {
                                $('#editLeaveEndTime2').val(response.l_remark);
                            }
                            // 15:45
                            else if (response.l_leave_end_time === "16:00:00" && response
                                .l_remark === "15:45:00") {
                                $('#editLeaveEndTime2').val(response.l_remark);
                            }
                            // 16:10
                            else if (response.l_leave_end_time === "16:30:00" && response
                                .l_remark === "16:10:00") {
                                $('#editLeaveEndTime2').val(response.l_remark);
                            }
                            // 16:40
                            else if (response.l_leave_end_time === "17:00:00") {
                                $('#editLeaveEndTime2').val('16:40:00');
                            } else {
                                $('#editLeaveEndTime2').val(response
                                    .l_leave_end_time);
                            }
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('ไม่สามารถดึงข้อมูลการลาได้');
                        console.log(xhr
                            .responseText); // ดูข้อความข้อผิดพลาดจากเซิร์ฟเวอร์
                    }
                });
            });

            $('#editLeaveForm').on('submit', function(e) {
                e.preventDefault();

                var formData = new FormData();
                var editFile = $('#editFile')[0].files[0]; // ดึงไฟล์จาก input
                var currentFile = $('#currentFile').val(); // ไฟล์เดิมที่เก็บไว้ใน hidden field

                // ตรวจสอบว่าได้เลือกไฟล์ใหม่หรือไม่
                if (editFile) {
                    formData.append('file', editFile); // เพิ่มไฟล์ใหม่ลงใน FormData
                } else if (currentFile) {
                    formData.append('currentFile', currentFile); // ส่งไฟล์เดิมถ้าไม่มีการเลือกไฟล์ใหม่
                }

                // เพิ่มค่าฟอร์มอื่นๆ
                formData.append('userCode', '<?php echo $userCode; ?>');
                formData.append('userName', '<?php echo $userName ?>');
                formData.append('name', '<?php echo $name ?>');
                formData.append('workplace', '<?php echo $workplace; ?>');
                formData.append('depart', '<?php echo $depart; ?>');
                formData.append('subDepart', '<?php echo $subDepart; ?>');
                formData.append('createDatetime', $(this).data('createdatetime'));
                formData.append('editLeaveType', $('.editLeaveType').val());
                formData.append('editLeaveReason', $('#editLeaveReason').val());
                formData.append('editLeaveStartDate', $('#editLeaveStartDate').val());
                formData.append('editLeaveStartTime', $('#editLeaveStartTime').val());
                formData.append('editLeaveEndDate', $('#editLeaveEndDate').val());
                formData.append('editLeaveEndTime', $('#editLeaveEndTime').val());
                formData.append('editTelPhone', $('#editTelPhone').val());

                // ส่งข้อมูลผ่าน AJAX
                $.ajax({
                    url: 'm_upd_leave.php',
                    type: 'POST',
                    data: formData,
                    contentType: false, // ปิด content type เพื่อให้ส่งข้อมูลแบบ FormData
                    processData: false, // ปิด process data เพื่อให้ส่งไฟล์ได้
                    success: function(response) {
                        try {
                            var res = JSON.parse(response);
                            if (res.status === 'success') {
                                Swal.fire({
                                    title: 'สำเร็จ!',
                                    text: 'อัปโหลดไฟล์และแก้ไขข้อมูลเรียบร้อยแล้ว',
                                    icon: 'success',
                                    confirmButtonText: 'ตกลง',
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    title: 'เกิดข้อผิดพลาด',
                                    text: res.message || 'ไม่สามารถแก้ไขข้อมูลได้',
                                    icon: 'error',
                                    confirmButtonText: 'ตกลง',
                                });
                            }
                        } catch (error) {
                            Swal.fire({
                                title: 'ข้อผิดพลาดในการประมวลผล',
                                text: 'เกิดข้อผิดพลาดในการตอบกลับจากเซิร์ฟเวอร์',
                                icon: 'error',
                                confirmButtonText: 'ตกลง',
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            title: 'เกิดข้อผิดพลาด',
                            text: 'ไม่สามารถแก้ไขข้อมูลได้',
                            icon: 'error',
                            confirmButtonText: 'ตกลง',
                        });
                    },
                });
            });
        });


        function checkOther(select) {
            var otherReasonInput = document.getElementById('otherReason');

            if (select.value === 'อื่น ๆ') {
                otherReasonInput.classList.remove('d-none');
            } else {
                otherReasonInput.classList.add('d-none');
            }
        }

        /* function updateLeaveReasonField() {
            var leaveType = document.getElementById('leaveType').value;

            var leaveReasonField = document.getElementById('leaveReason');
            var otherReasonField = document.getElementById('otherReason');

            // อัปเดตเหตุผลการลา
            if (leaveType === '1') { // ลากิจได้รับค่าจ้าง
                leaveReasonField.innerHTML = '<option value="กิจส่วนตัว">กิจส่วนตัว</option>' +
                    '<option value="อื่น ๆ">อื่น ๆ</option>';
            } else if (leaveType === '2') { // ลากิจไม่ได้รับค่าจ้าง
                leaveReasonField.innerHTML = '<option value="กิจส่วนตัว">กิจส่วนตัว</option>' +
                    '<option value="อื่น ๆ">อื่น ๆ</option>';
            } else if (leaveType === '3') { // ลาป่วย
                leaveReasonField.innerHTML = '<option value="ป่วย">ป่วย</option>' +
                    '<option value="อื่น ๆ">อื่น ๆ</option>';
            } else if (leaveType === '4') { // ลาป่วยจากงาน
                leaveReasonField.innerHTML = '<option value="ป่วย">ป่วย</option>' +
                    '<option value="อื่น ๆ">อื่น ๆ</option>';
            } else if (leaveType === '5') { // ลาพักร้อน
                leaveReasonField.innerHTML = '<option value="พักร้อน">พักร้อน</option>' +
                    '<option value="อื่น ๆ">อื่น ๆ</option>';
            } else if (leaveType === '8') { // อื่น ๆ
                leaveReasonField.innerHTML = '<option value="ลาเพื่อทำหมัน">ลาเพื่อทำหมัน</option>' +
                    '<option value="ลาคลอด">ลาคลอด</option>' +
                    '<option value="ลาอุปสมบท">ลาอุปสมบท</option>' +
                    '<option value="ลาเพื่อรับราชการทหาร">ลาเพื่อรับราชการทหาร</option>' +
                    '<option value="ลาเพื่อจัดการงานศพ">ลาเพื่อจัดการงานศพ</option>' +
                    '<option value="ลาเพื่อพัฒนาและเรียนรู้">ลาเพื่อพัฒนาและเรียนรู้</option>' +
                    '<option value="ลาเพื่อการสมรส">ลาเพื่อการสมรส</option>' +
                    '<option value="อื่น ๆ">อื่น ๆ</option>';
            } else {
                leaveReasonField.innerHTML = '<option selected disabled>เลือกเหตุผลการลา</option>';
            }

            // การจัดการการแสดง/ซ่อนฟิลด์เหตุผล "อื่น ๆ"
            if (leaveType === '5' || leaveType === '8') { // หากเป็นลาพักร้อนหรือประเภทอื่น ๆ
                if (leaveReasonField.value === 'อื่น ๆ') {
                    otherReasonField.classList.remove('d-none');
                } else {
                    otherReasonField.classList.add('d-none');
                }
            } else {
                otherReasonField.classList.add('d-none');
            }
        } */

        // ลาฉุกเฉิน
        function checkUrgentOther(select) {
            var urgentOtherReasonInput = document.getElementById('urgentOtherReason');

            // แสดงหรือซ่อน textarea หากเหตุผลการลาเป็น "อื่น ๆ"
            if (select.value === 'อื่น ๆ') {
                urgentOtherReasonInput.classList.remove('d-none');
            } else {
                urgentOtherReasonInput.classList.add('d-none');
            }
        }

        /*  function updateUrgentLeaveReasonField() {
             var urgentLeaveType = document.getElementById('urgentLeaveType').value;
             var urgentLeaveReasonField = document.getElementById('urgentLeaveReason');
             var urgentOtherReasonField = document.getElementById('urgentOtherReason');

             // อัปเดตเหตุผลการลา
             if (urgentLeaveType === '1' || urgentLeaveType === '2') { // ลากิจได้รับ/ไม่ได้รับค่าจ้าง
                 urgentLeaveReasonField.innerHTML = '<option value="กิจส่วนตัว">กิจส่วนตัว</option>' +
                     '<option value="อื่น ๆ">อื่น ๆ</option>';
             } else if (urgentLeaveType === '5') { // ลาพักร้อน
                 urgentLeaveReasonField.innerHTML = '<option value="พักร้อน">พักร้อน</option>' +
                     '<option value="อื่น ๆ">อื่น ๆ</option>';
             } else {
                 urgentLeaveReasonField.innerHTML = '<option value="" selected disabled>เลือกเหตุผลการลา</option>';
             }

             // รีเซ็ตการแสดง textarea
             urgentOtherReasonField.classList.add('d-none');
         } */
        </script>
        <script src="../js/popper.min.js"></script>
        <script src="../js/bootstrap.min.js"></script>
        <script src="../js/bootstrap.bundle.js"></script>
        <script src="../js/bootstrap.bundle.min.js"></script>
</body>

</html>