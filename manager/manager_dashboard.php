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
WHERE l_department = :depart
AND l_leave_status = 0
AND l_approve_status2 = 1
AND l_level = 'manager'
-- AND l_approve_status2 = 1
AND l_leave_id = 7";
$stmt_check_late = $conn->prepare($sql_check_late);
$stmt_check_late->bindParam(':depart', $depart);
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
$sql_check_leave = "SELECT COUNT(l_list_id) AS leave_count, l_name
FROM leave_list
WHERE
l_department = 'Office'
AND l_approve_status = 2
AND l_level IN ('user','chief')
AND l_approve_status2 = 1
AND (l_leave_id <> 6 AND l_leave_id <> 7)
AND l_leave_status = 0
GROUP BY l_name";
$stmt_check_leave = $conn->prepare($sql_check_leave);
$stmt_check_leave->bindParam(':depart', $depart);
$stmt_check_leave->execute();

$employee_names = array();
while ($row_leave = $stmt_check_leave->fetch(PDO::FETCH_ASSOC)) {
    $employee_names[] = $row_leave['l_name'];
}

$employee_list = implode(', ', $employee_names);

if (!empty($employee_list)) {
    echo '<div class="alert alert-warning d-flex align-items-center" role="alert">
    <i class="fa-solid fa-circle-exclamation me-2"></i>
    <span>มีใบลาของพนักงาน ' . $employee_list . ' กรุณาตรวจสอบ</span>
    <button type="button" class="ms-2 btn btn-primary button-shadow" onclick="window.location.href=\'manager_leave_request.php\'">ตรวจสอบใบลา</button>
    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
</div>';
}

// พนักงานยกเลิกใบลา --------------------------------------------------------------------------------------------
$sql_cancel_leave = "SELECT COUNT(l_list_id) AS leave_count, l_name
FROM leave_list
WHERE
l_department = 'Office'
AND l_approve_status = 2
AND l_level IN ('user','chief')
AND l_approve_status2 = 1
AND (l_leave_id <> 6 AND l_leave_id <> 7)
AND l_leave_status = 1
GROUP BY l_name";
$stmt_cancel_leave = $conn->prepare($sql_cancel_leave);
$stmt_cancel_leave->bindParam(':depart', $depart);
$stmt_cancel_leave->execute();

$employee_names = array();
while ($row_leave = $stmt_cancel_leave->fetch(PDO::FETCH_ASSOC)) {
    $employee_names[] = $row_leave['l_name'];
}

$employee_list = implode(', ', $employee_names);

if (!empty($employee_list)) {
    echo '<div class="alert alert-danger d-flex align-items-center" role="alert">
<i class="fa-solid fa-circle-exclamation me-2"></i>
<span>มีการยกเลิกใบลาของ ' . $employee_list . ' กรุณาตรวจสอบ</span>
<button type="button" class="ms-2 btn btn-primary button-shadow" onclick="window.location.href=\'manager_leave_request.php\'">ตรวจสอบใบลา</button>
<button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
</div>';
}

// มีพนักงานมาสาย --------------------------------------------------------------------------------------------
$sql_check_leave_id_7 = "SELECT COUNT(l_list_id) AS leave_count, l_name
FROM leave_list
WHERE l_department = 'Office'
AND l_leave_id = 7
AND l_approve_status = 2
OR  l_approve_status = 6
AND l_approve_status2 = 1
GROUP BY l_name";
$stmt_check_leave_id_7 = $conn->prepare($sql_check_leave_id_7);
$stmt_check_leave_id_7->bindParam(':depart', $depart);
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
                <form class="mt-3 mb-3 row" method="post">
                    <label for="" class="mt-2 col-auto">เลือกปี</label>
                    <div class="col-auto">
                        <?php
$currentYear = date('Y'); // ปีปัจจุบัน

if (isset($_POST['year'])) {
    $selectedYear = $_POST['year'];
} else {
    $selectedYear = $currentYear;
}

echo "<select class='form-select' name='year' id='selectedYear'>";
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
    '01' => 'มกราคม',
    '02' => 'กุมภาพันธ์',
    '03' => 'มีนาคม',
    '04' => 'เมษายน',
    '05' => 'พฤษภาคม',
    '06' => 'มิถุนายน',
    '07' => 'กรกฎาคม',
    '08' => 'สิงหาคม',
    '09' => 'กันยายน',
    '10' => 'ตุลาคม',
    '11' => 'พฤศจิกายน',
    '12' => 'ธันวาคม',
];

$selectedMonth = date('m'); // เดือนปัจจุบัน

if (isset($_POST['month'])) {
    $selectedMonth = $_POST['month'];
}

echo "<select class='form-select' name='month' id='selectedMonth'>";
foreach ($months as $key => $monthName) {
    echo "<option value='$key'" . ($key == $selectedMonth ? " selected" : "") . ">$monthName</option>";
}
echo "</select>";
?>
                    </div>

                    <div class="col-auto">
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
        <div class="mt-3 row">
            <div class="col-3 filter-card">
                <div class="card text-light mb-3" style="background-color: #031B80; ">
                    <div class="card-body">
                        <div class="card-title">
                            <?php
// ลากิจได้รับค่าจ้าง ----------------------------------------------------------------
$sql_leave_personal = "SELECT
-- SUM(
--     CASE
--         WHEN DATEDIFF(l_leave_end_date, l_leave_start_date) = 0 THEN
--             -- กรณีลาในวันเดียว
--             CASE
--                 WHEN TIME_TO_SEC(TIMEDIFF(l_leave_end_time, l_leave_start_time)) = 8 * 3600 + 40 * 60 THEN 8
--                 WHEN TIME_TO_SEC(TIMEDIFF(l_leave_end_time, l_leave_start_time)) > 4 * 3600 THEN
--                     ROUND((TIME_TO_SEC(TIMEDIFF(l_leave_end_time, l_leave_start_time)) - 1 * 3600) / 3600, 1)
--                 ELSE
--                     ROUND(TIME_TO_SEC(TIMEDIFF(l_leave_end_time, l_leave_start_time)) / 3600, 1)
--             END
--         WHEN DATEDIFF(l_leave_end_date, l_leave_start_date) = 1 THEN
--             -- กรณีลา 1 วัน 1 ชั่วโมง
--             CASE
--                 WHEN TIME_TO_SEC(TIMEDIFF(l_leave_end_time, l_leave_start_time)) >= 8 * 3600 + 60 * 60 THEN
--                     1 + ROUND((TIME_TO_SEC(TIMEDIFF(l_leave_end_time, l_leave_start_time)) - 8 * 3600) / 3600, 1) -- 1 วัน + ชั่วโมง
--                 WHEN TIME_TO_SEC(TIMEDIFF(l_leave_end_time, l_leave_start_time)) >= 7 * 3600 + 40 * 60 THEN
--                     1 -- นับเป็น 1 วัน
--                 WHEN TIME_TO_SEC(TIMEDIFF(l_leave_end_time, l_leave_start_time)) >= 3 * 3600 + 45 * 60 THEN
--                     0.5 -- นับเป็น 1 วันครึ่ง
--                 ELSE
--                     0 -- ไม่ใช่วันลา
--             END
--         ELSE
--             -- กรณีลาในหลายวัน
--             ((DATEDIFF(l_leave_end_date, l_leave_start_date)) -
--              (SELECT COUNT(*) FROM holiday
--               WHERE h_start_date BETWEEN leave_list.l_leave_start_date AND leave_list.l_leave_end_date
--               AND h_holiday_status = 'วันหยุด'
--               AND h_status = 0)) * 8
--             +
--             -- ตรวจสอบวันสุดท้าย
--             CASE
--                 WHEN TIME(l_leave_start_time) >= '08:00:00' AND TIME(l_leave_end_time) <= '11:45:00' THEN
--                     4 -- วันแรกนับเต็มวัน
--                 WHEN TIME(l_leave_start_time) >= '12:45:00' AND TIME(l_leave_end_time) <= '16:40:00' THEN
--                     4 -- ลาในช่วง 12:45 ถึง 16:40 นับเป็นครึ่งวัน
--                 ELSE
--                     8 -- ลาเต็มวันในวันสุดท้าย
--             END
--     END
 SUM(
        CASE
            WHEN DATEDIFF(l_leave_end_date, l_leave_start_date) = 0 THEN
                -- กรณีลาในวันเดียว
                CASE
                    WHEN TIME_TO_SEC(TIMEDIFF(l_leave_end_time, l_leave_start_time)) = 8 * 3600 + 40 * 60 THEN 8
                    WHEN TIME_TO_SEC(TIMEDIFF(l_leave_end_time, l_leave_start_time)) > 4 * 3600 THEN
                        ROUND((TIME_TO_SEC(TIMEDIFF(l_leave_end_time, l_leave_start_time)) - 1 * 3600) / 3600, 1)
                    ELSE
                        ROUND(TIME_TO_SEC(TIMEDIFF(l_leave_end_time, l_leave_start_time)) / 3600, 1)
                END
            ELSE
                -- กรณีลาในหลายวัน และไม่นับวันหยุด
                ((DATEDIFF(l_leave_end_date, l_leave_start_date) + 1) -
                 (SELECT COUNT(*) FROM holiday
                  WHERE h_start_date BETWEEN leave_list.l_leave_start_date AND leave_list.l_leave_end_date
                  AND h_holiday_status = 'วันหยุด'
                  AND h_status = 0)) * 8
                +
                -- ตรวจสอบว่ามีการลาครึ่งวันในวันหยุดหรือไม่
                CASE
                    WHEN EXISTS (
                        SELECT 1
                        FROM holiday
                        WHERE h_start_date = l_leave_end_date
                        AND h_holiday_status = 'วันหยุด'
                        AND h_status = 0
                    ) AND TIME(l_leave_end_time) <= '12:00:00' THEN 4 -- นับครึ่งวันถ้าวันหยุดและลาแค่ครึ่งวัน
                    ELSE 0
                END
        END
) AS leave_personal_count,
(SELECT e_leave_personal FROM employees WHERE e_usercode = :userCode) AS total_personal
FROM leave_list
WHERE l_leave_id = 1
AND l_usercode = :userCode
AND NOT (TIME(l_leave_start_time) >= '11:45:00' AND TIME(l_leave_end_time) <= '12:45:00')
AND YEAR(l_create_datetime) = :selectedYear
AND l_leave_status = 0
";

$stmt_leave_personal = $conn->prepare($sql_leave_personal);
$stmt_leave_personal->bindParam(':userCode', $userCode);
$stmt_leave_personal->bindParam(':selectedYear', $selectedYear, PDO::PARAM_INT);
$stmt_leave_personal->execute();
$result_leave_personal = $stmt_leave_personal->fetch(PDO::FETCH_ASSOC);

if ($result_leave_personal) {
    $total_personal = $result_leave_personal['total_personal'] ?? 0;
    $leave_personal_count = $result_leave_personal['leave_personal_count'] ?? 0;

    // คำนวณวันและชั่วโมงจากผลรวม
    $leave_personal_count = round($leave_personal_count * 2) / 2; // ปัดเป็นครึ่งชั่วโมง
    $leave_personal_days = floor($leave_personal_count / 8);
    $leave_personal_hours_remain = floor($leave_personal_count % 8);
    $leave_personal_minutes_remain = ($leave_personal_count - floor($leave_personal_count)) * 60;

    $leave_personal_minutes_remain = round($leave_personal_minutes_remain / 30) * 30;

    if ($leave_personal_minutes_remain == 30) {
        $leave_personal_minutes_remain = 5;
    } else {
        $leave_personal_minutes_remain = 0;
    }

    echo '<div class="d-flex justify-content-between">';
    echo '<div>';
    echo '<h5>' . $leave_personal_days . '(' . $leave_personal_hours_remain . '.' . $leave_personal_minutes_remain . ') / ' . $total_personal . '</h5>';

    // เพิ่ม input hidden สำหรับข้อมูลที่ต้องการ
    echo '<input type="hidden" name="leave_personal_days" value="' . $leave_personal_days . '">';
    echo '<input type="hidden" name="leave_personal_hours_remain" value="' . $leave_personal_hours_remain . '">';
    echo '<input type="hidden" name="leave_personal_minutes_remain" value="' . $leave_personal_minutes_remain . '">';
    echo '<input type="hidden" name="total_personal" value="' . $total_personal . '">';

    echo '</div>';
    echo '<div>';
    echo '<i class="mx-2 fa-solid fa-sack-dollar fa-2xl"></i>';
    echo '</div>';
    echo '</div>';
} else {
    echo '<p>No data found</p>';
}

?>
                            <p class="card-text">
                                ลากิจได้รับค่าจ้าง
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-3 filter-card">
                <div class="card text-light mb-3" style="background-color: #0339A2;">
                    <div class="card-body">
                        <div class="card-title">
                            <?php
// ลากิจไม่ได้รับค่าจ้าง ----------------------------------------------------------------
$sql_leave_personal_no = "SELECT
    SUM(
        CASE
            WHEN DATEDIFF(l_leave_end_date, l_leave_start_date) = 0 THEN
                -- กรณีลาในวันเดียว
                CASE
                    WHEN TIME_TO_SEC(TIMEDIFF(l_leave_end_time, l_leave_start_time)) = 8 * 3600 + 40 * 60 THEN 8
                    WHEN TIME_TO_SEC(TIMEDIFF(l_leave_end_time, l_leave_start_time)) > 4 * 3600 THEN
                        ROUND((TIME_TO_SEC(TIMEDIFF(l_leave_end_time, l_leave_start_time)) - 1 * 3600) / 3600, 1)
                    ELSE
                        ROUND(TIME_TO_SEC(TIMEDIFF(l_leave_end_time, l_leave_start_time)) / 3600, 1)
                END
            ELSE
                -- กรณีลาในหลายวัน และไม่นับวันหยุด
                ((DATEDIFF(l_leave_end_date, l_leave_start_date) + 1) -
                 (SELECT COUNT(*) FROM holiday
                  WHERE h_start_date BETWEEN leave_list.l_leave_start_date AND leave_list.l_leave_end_date
                  AND h_holiday_status = 'วันหยุด'
                  AND h_status = 0)) * 8
                +
                -- ตรวจสอบว่ามีการลาครึ่งวันในวันหยุดหรือไม่
                CASE
                    WHEN EXISTS (
                        SELECT 1
                        FROM holiday
                        WHERE h_start_date = l_leave_end_date
                        AND h_holiday_status = 'วันหยุด'
                        AND h_status = 0
                    ) AND TIME(l_leave_end_time) <= '12:00:00' THEN 4 -- นับครึ่งวันถ้าวันหยุดและลาแค่ครึ่งวัน
                    ELSE 0
                END
        END
    ) AS leave_personal_no_count,
    (SELECT e_leave_personal_no FROM employees WHERE e_usercode = :userCode) AS total_personal_no
FROM leave_list
WHERE l_leave_id = 2
AND l_usercode = :userCode
AND NOT (TIME(l_leave_start_time) >= '11:45:00' AND TIME(l_leave_end_time) <= '12:45:00')
AND YEAR(l_create_datetime) = :selectedYear
AND l_leave_status = 0";

$stmt_leave_personal_no = $conn->prepare($sql_leave_personal_no);
$stmt_leave_personal_no->bindParam(':userCode', $userCode);
$stmt_leave_personal_no->bindParam(':selectedYear', $selectedYear, PDO::PARAM_INT);
$stmt_leave_personal_no->execute();
$result_leave_personal_no = $stmt_leave_personal_no->fetch(PDO::FETCH_ASSOC);

if ($result_leave_personal_no) {
    $total_personal_no = $result_leave_personal_no['total_personal_no'] ?? 0;
    $leave_personal_no_count = $result_leave_personal_no['leave_personal_no_count'] ?? 0;

    // Round the total leave count to the nearest half-hour
    $leave_personal_no_count = round($leave_personal_no_count * 2) / 2;

    // Calculate days, hours, and minutes
    $leave_personal_no_days = floor($leave_personal_no_count / 8);
    $leave_personal_no_hours_remain = floor($leave_personal_no_count % 8);
    $leave_personal_no_minutes_remain = ($leave_personal_no_count - floor($leave_personal_no_count)) * 60;

    // Adjust minutes to nearest 30-minute interval
    $leave_personal_no_minutes_remain = round($leave_personal_no_minutes_remain / 30) * 30;

    if ($leave_personal_no_minutes_remain == 30) {
        $leave_personal_no_minutes_remain = 5;
    } else {
        $leave_personal_no_minutes_remain = 0;
    }

    echo '<div class="d-flex justify-content-between">';
    echo '<div>';
    echo '<h5>' . $leave_personal_no_days . '(' . $leave_personal_no_hours_remain . '.' . $leave_personal_no_minutes_remain . ') / ' . $total_personal_no . '</h5>';
    echo '</div>';
    echo '<div>';
    echo '<i class="mx-2 fa-solid fa-sack-xmark fa-2xl"></i>';
    echo '</div>';
    echo '</div>';
} else {
    echo '<p>No data found</p>';
}

?>
                            <p class="card-text">
                                ลากิจไม่ได้รับค่าจ้าง
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-3 filter-card">
                <div class="card text-light mb-3" style="background-color: #0357C4;">
                    <div class="card-body">
                        <div class="card-title">
                            <?php
// ลาป่วย ----------------------------------------------------------------
$sql_leave_sick = "SELECT
    SUM(
        CASE
            WHEN DATEDIFF(l_leave_end_date, l_leave_start_date) = 0 THEN
                -- กรณีลาในวันเดียว
                CASE
                    WHEN TIME_TO_SEC(TIMEDIFF(l_leave_end_time, l_leave_start_time)) = 8 * 3600 + 40 * 60 THEN 8
                    WHEN TIME_TO_SEC(TIMEDIFF(l_leave_end_time, l_leave_start_time)) > 4 * 3600 THEN
                        ROUND((TIME_TO_SEC(TIMEDIFF(l_leave_end_time, l_leave_start_time)) - 1 * 3600) / 3600, 1)
                    ELSE
                        ROUND(TIME_TO_SEC(TIMEDIFF(l_leave_end_time, l_leave_start_time)) / 3600, 1)
                END
            ELSE
                -- กรณีลาในหลายวัน และไม่นับวันหยุด
                ((DATEDIFF(l_leave_end_date, l_leave_start_date) + 1) -
                 (SELECT COUNT(*) FROM holiday
                  WHERE h_start_date BETWEEN leave_list.l_leave_start_date AND leave_list.l_leave_end_date
                  AND h_holiday_status = 'วันหยุด'
                  AND h_status = 0)) * 8
                +
                -- ตรวจสอบว่ามีการลาครึ่งวันในวันหยุดหรือไม่
                CASE
                    WHEN EXISTS (
                        SELECT 1
                        FROM holiday
                        WHERE h_start_date = l_leave_end_date
                        AND h_holiday_status = 'วันหยุด'
                        AND h_status = 0
                    ) AND TIME(l_leave_end_time) <= '12:00:00' THEN 4 -- นับครึ่งวันถ้าวันหยุดและลาแค่ครึ่งวัน
                    ELSE 0
                END
        END
    ) AS leave_sick_count,
    (SELECT e_leave_sick FROM employees WHERE e_usercode = :userCode) AS total_sick
FROM leave_list
WHERE l_leave_id = 3
AND l_usercode = :userCode
AND NOT (TIME(l_leave_start_time) >= '11:45:00' AND TIME(l_leave_end_time) <= '12:45:00')
AND YEAR(l_create_datetime) = :selectedYear
AND l_leave_status = 0";

$stmt_leave_sick = $conn->prepare($sql_leave_sick);
$stmt_leave_sick->bindParam(':userCode', $userCode);
$stmt_leave_sick->bindParam(':selectedYear', $selectedYear, PDO::PARAM_INT);
$stmt_leave_sick->execute();
$result_leave_sick = $stmt_leave_sick->fetch(PDO::FETCH_ASSOC);

if ($result_leave_sick) {
    $total_sick = $result_leave_sick['total_sick'] ?? 0;
    $leave_sick_count = $result_leave_sick['leave_sick_count'] ?? 0;

    // คำนวณวันและชั่วโมงจากผลรวม
    // ปัดเป็นชั่วโมงหากเกินค่าที่กำหนด
    $leave_sick_count = round($leave_sick_count * 2) / 2; // ปัดขึ้นเป็นครึ่งชั่วโมง
    $leave_sick_days = floor($leave_sick_count / 8);
    $leave_sick_hours_remain = floor($leave_sick_count % 8);
    $leave_sick_minutes_remain = ($leave_sick_count - floor($leave_sick_count)) * 60;

    $leave_sick_minutes_remain = round($leave_sick_minutes_remain / 30) * 30;

    if ($leave_sick_minutes_remain == 30) {
        $leave_sick_minutes_remain = 5;
    } else {
        $leave_sick_minutes_remain = 0;
    }

    echo '<div class="d-flex justify-content-between">';
    echo '<div>';
    // แสดงข้อมูลในรูปแบบ h5
    echo '<h5>' . $leave_sick_days . '(' . $leave_sick_hours_remain . '.' . $leave_sick_minutes_remain . ') / ' . $total_sick . '</h5>';

    // เพิ่ม input type hidden สำหรับค่า leave_sick_days, leave_sick_hours_remain, leave_sick_minutes_remain และ total_sick
    echo '<input type="hidden" name="leave_sick_days" value="' . $leave_sick_days . '">';
    echo '<input type="hidden" name="leave_sick_hours_remain" value="' . $leave_sick_hours_remain . '">';
    echo '<input type="hidden" name="leave_sick_minutes_remain" value="' . $leave_sick_minutes_remain . '">';
    echo '<input type="hidden" name="total_sick" value="' . $total_sick . '">';

    echo '</div>';
    echo '<div>';
    echo '<i class="mx-2 fa-solid fa-syringe fa-2xl"></i>';
    echo '</div>';
    echo '</div>';
} else {
    echo '<p>No data found</p>';
}

?>
                            <p class="card-text">
                                ลาป่วย
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-3 filter-card">
                <div class="card text-light mb-3" style="background-color: #0475E6;">
                    <div class="card-body">
                        <div class="card-title">
                            <?php
// ลาป่วยจากงาน ----------------------------------------------------------------
$sql_leave_sick_work = "SELECT
    SUM(
        CASE
            WHEN DATEDIFF(l_leave_end_date, l_leave_start_date) = 0 THEN
                -- กรณีลาในวันเดียว
                CASE
                    WHEN TIME_TO_SEC(TIMEDIFF(l_leave_end_time, l_leave_start_time)) = 8 * 3600 + 40 * 60 THEN 8
                    WHEN TIME_TO_SEC(TIMEDIFF(l_leave_end_time, l_leave_start_time)) > 4 * 3600 THEN
                        ROUND((TIME_TO_SEC(TIMEDIFF(l_leave_end_time, l_leave_start_time)) - 1 * 3600) / 3600, 1)
                    ELSE
                        ROUND(TIME_TO_SEC(TIMEDIFF(l_leave_end_time, l_leave_start_time)) / 3600, 1)
                END
            ELSE
                -- กรณีลาในหลายวัน และไม่นับวันหยุด
                ((DATEDIFF(l_leave_end_date, l_leave_start_date) + 1) -
                 (SELECT COUNT(*) FROM holiday
                  WHERE h_start_date BETWEEN leave_list.l_leave_start_date AND leave_list.l_leave_end_date
                  AND h_holiday_status = 'วันหยุด'
                  AND h_status = 0)) * 8
                +
                -- ตรวจสอบว่ามีการลาครึ่งวันในวันหยุดหรือไม่
                CASE
                    WHEN EXISTS (
                        SELECT 1
                        FROM holiday
                        WHERE h_start_date = l_leave_end_date
                        AND h_holiday_status = 'วันหยุด'
                        AND h_status = 0
                    ) AND TIME(l_leave_end_time) <= '12:00:00' THEN 4 -- นับครึ่งวันถ้าวันหยุดและลาแค่ครึ่งวัน
                    ELSE 0
                END
            END
) AS leave_sick_work_count,
(SELECT e_leave_sick_work FROM employees WHERE e_usercode = :userCode) AS total_leave_sick_work
FROM leave_list
WHERE l_leave_id = 4
AND l_usercode = :userCode
AND NOT (TIME(l_leave_start_time) >= '11:45:00' AND TIME(l_leave_end_time) <= '12:45:00')
AND YEAR(l_create_datetime) = :selectedYear
AND l_leave_status = 0";

$stmt_leave_sick_work = $conn->prepare($sql_leave_sick_work);
$stmt_leave_sick_work->bindParam(':userCode', $userCode);
$stmt_leave_sick_work->bindParam(':selectedYear', $selectedYear, PDO::PARAM_INT);
$stmt_leave_sick_work->execute();
$result_leave_sick_work = $stmt_leave_sick_work->fetch(PDO::FETCH_ASSOC);

if ($result_leave_sick_work) {
    $total_sick_work = $result_leave_sick_work['total_leave_sick_work'] ?? 0;
    $leave_sick_work_count = $result_leave_sick_work['leave_sick_work_count'] ?? 0;

    // คำนวณวันและชั่วโมงจากผลรวม
    // ปัดเป็นชั่วโมงหากเกินค่าที่กำหนด
    $leave_sick_work_count = round($leave_sick_work_count * 2) / 2; // ปัดขึ้นเป็นครึ่งชั่วโมง
    $leave_sick_work_days = floor($leave_sick_work_count / 8);
    $leave_sick_work_hours_remain = floor($leave_sick_work_count % 8);
    $leave_sick_work_minutes_remain = ($leave_sick_work_count - floor($leave_sick_work_count)) * 60;

    $leave_sick_work_minutes_remain = round($leave_sick_work_minutes_remain / 30) * 30;

    if ($leave_sick_work_minutes_remain == 30) {
        $leave_sick_work_minutes_remain = 5;
    } else {
        $leave_sick_work_minutes_remain = 0;
    }

    echo '<div class="d-flex justify-content-between">';
    echo '<div>';
    echo '<h5>' . $leave_sick_work_days . '(' . $leave_sick_work_hours_remain . '.' . $leave_sick_work_minutes_remain . ') / ' . $total_sick_work . '</h5>';

    // เพิ่ม input hidden สำหรับข้อมูลที่ต้องการ
    echo '<input type="hidden" name="leave_sick_work_days" value="' . $leave_sick_work_days . '">';
    echo '<input type="hidden" name="leave_sick_work_hours_remain" value="' . $leave_sick_work_hours_remain . '">';
    echo '<input type="hidden" name="leave_sick_work_minutes_remain" value="' . $leave_sick_work_minutes_remain . '">';
    echo '<input type="hidden" name="total_sick_work" value="' . $total_sick_work . '">';
    echo '</div>';
    echo '<div>';
    echo '<i class="mx-2 fa-solid fa-user-injured fa-2xl"></i>';
    echo '</div>';
    echo '</div>';
} else {
    echo '<p>No data found</p>';
}
?>
                            <p class="card-text">
                                ลาป่วยจากงาน
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-3 row">
            <div class="col-3 filter-card">
                <div class="card text-light mb-3" style="background-color: #0475E6;">
                    <div class="card-body">
                        <div class="card-title">
                            <?php
// ลาพักร้อน ----------------------------------------------------------------
$sql_leave_annual = "SELECT
    SUM(
        CASE
            WHEN DATEDIFF(l_leave_end_date, l_leave_start_date) = 0 THEN
                -- กรณีลาในวันเดียว
                CASE
                    WHEN TIME_TO_SEC(TIMEDIFF(l_leave_end_time, l_leave_start_time)) = 8 * 3600 + 40 * 60 THEN 8
                    WHEN TIME_TO_SEC(TIMEDIFF(l_leave_end_time, l_leave_start_time)) > 4 * 3600 THEN
                        ROUND((TIME_TO_SEC(TIMEDIFF(l_leave_end_time, l_leave_start_time)) - 1 * 3600) / 3600, 1)
                    ELSE
                        ROUND(TIME_TO_SEC(TIMEDIFF(l_leave_end_time, l_leave_start_time)) / 3600, 1)
                END
            ELSE
                -- กรณีลาในหลายวัน และไม่นับวันหยุด
                ((DATEDIFF(l_leave_end_date, l_leave_start_date) + 1) -
                 (SELECT COUNT(*) FROM holiday
                  WHERE h_start_date BETWEEN leave_list.l_leave_start_date AND leave_list.l_leave_end_date
                  AND h_holiday_status = 'วันหยุด'
                  AND h_status = 0)) * 8
                +
                -- ตรวจสอบว่ามีการลาครึ่งวันในวันหยุดหรือไม่
                CASE
                    WHEN EXISTS (
                        SELECT 1
                        FROM holiday
                        WHERE h_start_date = l_leave_end_date
                        AND h_holiday_status = 'วันหยุด'
                        AND h_status = 0
                    ) AND TIME(l_leave_end_time) <= '12:00:00' THEN 4 -- นับครึ่งวันถ้าวันหยุดและลาแค่ครึ่งวัน
                    ELSE 0
                END
        END
) AS leave_annual_count,
(SELECT e_leave_annual FROM employees WHERE e_usercode = :userCode) AS total_annual
FROM leave_list
WHERE l_leave_id = 5
AND l_usercode = :userCode
AND NOT (TIME(l_leave_start_time) >= '11:45:00' AND TIME(l_leave_end_time) <= '12:45:00')
AND YEAR(l_create_datetime) = :selectedYear
AND l_leave_status = 0";

$stmt_leave_annual = $conn->prepare($sql_leave_annual);
$stmt_leave_annual->bindParam(':userCode', $userCode);
$stmt_leave_annual->bindParam(':selectedYear', $selectedYear, PDO::PARAM_INT);
$stmt_leave_annual->execute();
$result_leave_annual = $stmt_leave_annual->fetch(PDO::FETCH_ASSOC);

if ($result_leave_annual) {
    $total_annual = $result_leave_annual['total_annual'] ?? 0;
    $leave_annual_count = $result_leave_annual['leave_annual_count'] ?? 0;

    // คำนวณวันและชั่วโมงจากผลรวม
    // ปัดเป็นชั่วโมงหากเกินค่าที่กำหนด
    $leave_annual_count = round($leave_annual_count * 2) / 2; // ปัดขึ้นเป็นครึ่งชั่วโมง
    $leave_annual_days = floor($leave_annual_count / 8);
    $leave_annual_hours_remain = floor($leave_annual_count % 8);
    $leave_annual_minutes_remain = ($leave_annual_count - floor($leave_annual_count)) * 60;

    $leave_annual_minutes_remain = round($leave_annual_minutes_remain / 30) * 30;

    if ($leave_annual_minutes_remain == 30) {
        $leave_annual_minutes_remain = 5;
    } else {
        $leave_annual_minutes_remain = 0;
    }

    echo '<div class="d-flex justify-content-between">';
    echo '<div>';
    echo '<h5>' . $leave_annual_days . '(' . $leave_annual_hours_remain . '.' . $leave_annual_minutes_remain . ') / ' . $total_annual . '</h5>';
    echo '<input type="hidden" name="leave_annual_days" value="' . $leave_annual_days . '">';
    echo '<input type="hidden" name="total_annual" value="' . $total_annual . '">';

    echo '</div>';
    echo '<div>';
    echo '<i class="mx-2 fa-solid fa-business-time fa-2xl"></i>';
    echo '</div>';
    echo '</div>';
} else {
    echo '<p>No data found</p>';
}

?>
                            <p class="card-text">
                                ลาพักร้อน
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-3 filter-card">
                <div class="card text-light mb-3" style="background-color: #2788E9; ">
                    <div class="card-body">
                        <div class="card-title">
                            <?php
// มาสาย
$sql_late = "SELECT COUNT(l_list_id) AS late_count FROM leave_list WHERE l_leave_id = '7' AND l_usercode = '$userCode' AND Year(l_create_datetime) = '$selectedYear'";
$result_late = $conn->query($sql_late)->fetch(PDO::FETCH_ASSOC);
$late_count = $result_late['late_count'];

// แสดงผล
echo '<div class="d-flex justify-content-between">';
echo '<div>';
echo '<h5>' . $late_count . '</h5>'; // แสดงจำนวนครั้งที่มาสาย
echo '</div>';
echo '<div>';
echo '<i class="mx-2 fa-solid fa-person-running fa-2xl"></i>'; // แสดงไอคอนสัญลักษณ์
echo '</div>';
echo '</div>';

?>
                            <p class="card-text">
                                มาสาย (ครั้ง)
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-3 filter-card">
                <div class="card text-light mb-3" style="background-color: #4B9CED;">
                    <div class="card-body">
                        <div class="card-title">
                            <?php
// หยุดงาน
$sql_absence_work = "SELECT COUNT(l_list_id) AS stop_work FROM leave_list WHERE l_leave_id = '6' AND YEAR(l_leave_start_date) = '$selectedYear'";
$result_absence_work = $conn->query($sql_absence_work)->fetch(PDO::FETCH_ASSOC);
$stop_work = $result_absence_work['stop_work'];

// แสดงผล
echo '<div class="d-flex justify-content-between">';
echo '<div>';
echo '<h5>' . $stop_work . '</h5>'; // แสดงจำนวนวันขาดงานทั้งหมด
echo '</div>';
echo '<div>';
echo '<i class="fa-solid fa-circle-minus fa-2xl"></i>'; // แสดงไอคอนสัญลักษณ์
echo '</div>';
echo '</div>';
?>
                            <p class="card-text">
                                หยุดงาน
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-3 filter-card">
                <div class="card text-light mb-3" style="background-color: #6FB0F0; ">
                    <div class="card-body">
                        <div class="card-title">
                            <?php
// อื่น ๆ ----------------------------------------------------------------
$sql_other = "SELECT
    SUM(
        CASE
            WHEN DATEDIFF(l_leave_end_date, l_leave_start_date) = 0 THEN
                -- กรณีลาในวันเดียว
                CASE
                    WHEN TIME_TO_SEC(TIMEDIFF(l_leave_end_time, l_leave_start_time)) = 8 * 3600 + 40 * 60 THEN 8
                    WHEN TIME_TO_SEC(TIMEDIFF(l_leave_end_time, l_leave_start_time)) > 4 * 3600 THEN
                        ROUND((TIME_TO_SEC(TIMEDIFF(l_leave_end_time, l_leave_start_time)) - 1 * 3600) / 3600, 1)
                    ELSE
                        ROUND(TIME_TO_SEC(TIMEDIFF(l_leave_end_time, l_leave_start_time)) / 3600, 1)
                END
            ELSE
                -- กรณีลาในหลายวัน และไม่นับวันหยุด
                ((DATEDIFF(l_leave_end_date, l_leave_start_date) + 1) -
                 (SELECT COUNT(*) FROM holiday
                  WHERE h_start_date BETWEEN leave_list.l_leave_start_date AND leave_list.l_leave_end_date
                  AND h_holiday_status = 'วันหยุด'
                  AND h_status = 0)) * 8
                +
                -- ตรวจสอบว่ามีการลาครึ่งวันในวันหยุดหรือไม่
                CASE
                    WHEN EXISTS (
                        SELECT 1
                        FROM holiday
                        WHERE h_start_date = l_leave_end_date
                        AND h_holiday_status = 'วันหยุด'
                        AND h_status = 0
                    ) AND TIME(l_leave_end_time) <= '12:00:00' THEN 4 -- นับครึ่งวันถ้าวันหยุดและลาแค่ครึ่งวัน
                    ELSE 0
                END
        END
    ) AS other_count,
    (SELECT e_other FROM employees WHERE e_usercode = :userCode) AS total_other
FROM leave_list
WHERE l_leave_id = 8
AND l_usercode = :userCode
AND NOT (TIME(l_leave_start_time) >= '11:45:00' AND TIME(l_leave_end_time) <= '12:45:00')
AND YEAR(l_create_datetime) = :selectedYear
AND l_leave_status = 0";

$stmt_other = $conn->prepare($sql_other);
$stmt_other->bindParam(':userCode', $userCode);
$stmt_other->bindParam(':selectedYear', $selectedYear, PDO::PARAM_INT);
$stmt_other->execute();
$result_other = $stmt_other->fetch(PDO::FETCH_ASSOC);

if ($result_other) {
    $total_other = $result_other['total_other'] ?? 0;
    $other_count = $result_other['other_count'] ?? 0;

    // คำนวณวันและชั่วโมงจากผลรวม
    // ปัดเป็นชั่วโมงหากเกินค่าที่กำหนด
    $other_count = round($other_count * 2) / 2; // ปัดขึ้นเป็นครึ่งชั่วโมง
    $other_days = floor($other_count / 8);
    $other_hours_remain = floor($other_count % 8);
    $other_minutes_remain = ($other_count - floor($other_count)) * 60;

    $other_minutes_remain = round($other_minutes_remain / 30) * 30;

    if ($other_minutes_remain == 30) {
        $other_minutes_remain = 5;
    } else {
        $other_minutes_remain = 0;
    }

    echo '<div class="d-flex justify-content-between">';
    echo '<div>';
    echo '<h5>' . $other_days . '(' . $other_hours_remain . '.' . $other_minutes_remain . ')' . '</h5>';
    echo '</div>';
    echo '<div>';
    echo '<i class="mx-2 fa-solid fa-bars fa-2xl"></i>';
    echo '</div>';
    echo '</div>';
} else {
    echo '<p>No data found</p>';
}
?>
                            <p class="card-text">
                                อื่น ๆ
                            </p>
                        </div>
                    </div>
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
                    ยื่นใบลา
                </button>
                <!-- ลาฉุกเฉิน -->
                <button type="button" class="button-shadow btn btn-danger mt-3 ms-2" data-bs-toggle="modal"
                    data-bs-target="#urgentLeaveModal" style="width: 100px;">
                    ลาฉุกเฉิน
                </button>
            </div>
        </div>
        <!-- Modal ยื่นใบลา -->
        <div class="modal fade" id="leaveModal" tabindex="-1" aria-labelledby="leaveModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="leaveModalLabel">รายละเอียดการลา</h5>
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
                                    <span class="badge rounded-pill text-bg-info" id="totalDays">เหลือ - วัน</span>
                                    <span style="color: red;">*</span>
                                    <select class="form-select" id="leaveType" required
                                        onchange="checkDays(this.value)">
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
                                    <textarea class="form-control mt-2" id="leaveReason" rows="3"
                                        placeholder="กรุณาระบุเหตุผล"></textarea>
                                </div>
                            </div>
                            <div class="mt-3 row">
                                <div class="col-6">
                                    <label for="startDate" class="form-label">วันที่เริ่มต้น</label>
                                    <span style="color: red;">*</span>
                                    <input type="text" class="form-control" id="startDate" required
                                        onchange="checkDays(document.getElementById('leaveType').value)">
                                </div>
                                <div class="col-6">
                                    <label for="startTime" class="form-label">เวลาที่เริ่มต้น</label>
                                    <span style="color: red;">*</span>
                                    <select class="form-select" id="startTime" name="startTime" required
                                        onchange="checkDays(document.getElementById('leaveType').value)">
                                        <option value="08:00">08:00</option>
                                        <option value="08:30">08:30</option>
                                        <option value="09:00">09:00</option>
                                        <option value="09:30">09:30</option>
                                        <option value="10:00">10:00</option>
                                        <option value="10:30">10:30</option>
                                        <option value="11:00">11:00</option>
                                        <option value="11:30">11:30</option>
                                        <option value="11:45">11:45</option>
                                        <option value="12:45">12:45</option>
                                        <option value="13:00">13:00</option>
                                        <option value="13:30">13:30</option>
                                        <option value="14:00">14:00</option>
                                        <option value="14:30">14:30</option>
                                        <option value="15:00">15:00</option>
                                        <option value="15:30">15:30</option>
                                        <option value="16:00">16:00</option>
                                        <option value="16:30">16:30</option>
                                        <option value="16:40">16:40</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mt-3 row">
                                <div class="col-6">
                                    <label for="endDate" class="form-label">วันที่สิ้นสุด</label>
                                    <span style="color: red;">*</span>
                                    <input type="text" class="form-control" id="endDate" required
                                        onchange="checkDays(document.getElementById('leaveType').value)">
                                </div>
                                <div class="col-6">
                                    <label for="endTime" class="form-label">เวลาที่สิ้นสุด</label>
                                    <span style="color: red;">*</span>
                                    <select class="form-select" id="endTime" name="endTime" required
                                        onchange="checkDays(document.getElementById('leaveType').value)">
                                        <option value="08:00">08:00</option>
                                        <option value="08:30">08:30</option>
                                        <option value="09:00">09:00</option>
                                        <option value="09:30">09:30</option>
                                        <option value="10:00">10:00</option>
                                        <option value="10:30">10:30</option>
                                        <option value="11:00">11:00</option>
                                        <option value="11:30">11:30</option>
                                        <option value="11:45">11:45</option>
                                        <option value="12:45">12:45</option>
                                        <option value="13:00">13:00</option>
                                        <option value="13:30">13:30</option>
                                        <option value="14:00">14:00</option>
                                        <option value="14:30">14:30</option>
                                        <option value="15:00">15:00</option>
                                        <option value="15:30">15:30</option>
                                        <option value="16:00">16:00</option>
                                        <option value="16:30">16:30</option>
                                        <option value="16:40" selected>16:40</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mt-3 row">
                                <div class="col-12">
                                    <label for="telPhone" class="form-label">เบอร์โทร</label>
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
                                    <label for="file" class="form-label">ไฟล์แนบ (PNG , JPG, JPEG)</label>
                                    <input class="form-control" type="file" id="file" name="file" />
                                </div>
                            </div>

                            <div class="mt-3 d-flex justify-content-end">
                                <button type="submit" class="btn btn-success" id="btnSubmitForm1" name="submit"
                                    style="white-space: nowrap;">บันทึก</button>
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
                                    <select class="form-select" id="urgentStartTime" name="urgentStartTime" required>
                                        <option value="08:00">08:00</option>
                                        <option value="08:30">08:30</option>
                                        <option value="09:00">09:00</option>
                                        <option value="09:30">09:30</option>
                                        <option value="10:00">10:00</option>
                                        <option value="10:30">10:30</option>
                                        <option value="11:00">11:00</option>
                                        <option value="11:30">11:30</option>
                                        <option value="11:45">11:45</option>
                                        <option value="12:45">12:45</option>
                                        <option value="13:00">13:00</option>
                                        <option value="13:30">13:30</option>
                                        <option value="14:00">14:00</option>
                                        <option value="14:30">14:30</option>
                                        <option value="15:00">15:00</option>
                                        <option value="15:30">15:30</option>
                                        <option value="16:00">16:00</option>
                                        <option value="16:30">16:30</option>
                                        <option value="16:40">16:40</option>
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
                                        <option value="09:00">09:00</option>
                                        <option value="09:30">09:30</option>
                                        <option value="10:00">10:00</option>
                                        <option value="10:30">10:30</option>
                                        <option value="11:00">11:00</option>
                                        <option value="11:30">11:30</option>
                                        <option value="11:45">11:45</option>
                                        <option value="12:45">12:45</option>
                                        <option value="13:00">13:00</option>
                                        <option value="13:30">13:30</option>
                                        <option value="14:00">14:00</option>
                                        <option value="14:30">14:30</option>
                                        <option value="15:00">15:00</option>
                                        <option value="15:30">15:30</option>
                                        <option value="16:00">16:00</option>
                                        <option value="16:30">16:30</option>
                                        <option value="16:40" selected>16:40</option>
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
                                    style="width: 100px;">บันทึก</button>
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
                        <th rowspan="2">ลำดับ</th>
                        <th rowspan="2">วันที่ยื่น</th>
                        <th rowspan="2">ประเภทรายการ</th>
                        <th colspan="2">วันเวลา</th>
                        <th rowspan="2">จำนวนวันลา</th>
                        <th rowspan="2">ไฟล์แนบ</th>
                        <th rowspan="2">สถานะรายการ</th>
                        <th rowspan="2">สถานะมาสาย</th>
                        <th rowspan="2">สถานะอนุมัติ_1</th>
                        <th rowspan="2">สถานะอนุมัติ_2</th>
                        <th rowspan="2">สถานะ (เฉพาะ HR)</th>
                        <th rowspan="2"></th>
                    </tr>
                    <tr class="text-center">
                        <th>จาก</th>
                        <th>ถึง</th>
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
$sql = "SELECT * FROM leave_list WHERE l_usercode = '$userCode' AND Month(l_leave_start_date) = '$selectedMonth'
AND Year(l_leave_start_date) = '$selectedYear' AND l_leave_id <> 6 ORDER BY l_create_datetime DESC ";

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
        echo '<td>' . $row['l_leave_start_date'] . '<br> ' . $row['l_leave_start_time'] . '</td>';

        // 10
        echo '<td>' . $row['l_leave_end_date'] . '<br> ' . $row['l_leave_end_time'] . '</td>';

        // 11
        echo '<td>';

        // Query ตรวจสอบวันหยุดในช่วงเวลาการลา
        $holiday_query = "SELECT COUNT(*) as holiday_count
        FROM holiday
        WHERE h_start_date BETWEEN :start_date AND :end_date
        AND h_holiday_status = 'วันหยุด'
        AND h_status = 0";

// เตรียม query
        $holiday_stmt = $conn->prepare($holiday_query);
        $holiday_stmt->bindParam(':start_date', $row['l_leave_start_date']);
        $holiday_stmt->bindParam(':end_date', $row['l_leave_end_date']);
        $holiday_stmt->execute();

// ดึงจำนวนวันหยุด
        $holiday_data = $holiday_stmt->fetch(PDO::FETCH_ASSOC);
        $holiday_count = $holiday_data['holiday_count'];

// คำนวณระยะเวลาการลา
        $l_leave_start_date = new DateTime($row['l_leave_start_date'] . ' ' . $row['l_leave_start_time']);
        $l_leave_end_date = new DateTime($row['l_leave_end_date'] . ' ' . $row['l_leave_end_time']);
        $interval = $l_leave_start_date->diff($l_leave_end_date);

// หักวันหยุดออกจากวันลา
        $leave_days = $interval->days - $holiday_count;
        $leave_hours = $interval->h;
        $leave_minutes = $interval->i;

// ลดเวลาพักเที่ยงหากเวลาเกิน 4 ชั่วโมง
        if ($leave_hours > 4 || ($leave_hours == 4 && $leave_minutes > 0)) {
            $leave_hours -= 1;
        }

// ตรวจสอบกรณีต่างๆ
        if ($leave_days != 0 && $leave_hours == 3 && ($leave_minutes == 45 || $leave_minutes == 55)) {
            $leave_result = $leave_days . ' วันครึ่ง';
        } elseif ($leave_days == 0 && $leave_hours == 7 && $leave_minutes == 40) {
            $leave_result = ($leave_days + 1) . ' วัน';
        } elseif ($leave_days != 0 && $leave_hours == 7 && $leave_minutes == 40) {
            $leave_result = ($leave_days + 1) . ' วัน';
        } elseif ($leave_days == 0 && $leave_hours == 3 && ($leave_minutes == 45 || $leave_minutes == 55)) {
            $leave_result = 'ครึ่งวัน';
        } else {
            $leave_result = $leave_days . ' วัน ' . $leave_hours . ' ชั่วโมง ' . $leave_minutes . ' นาที';
        }
// แสดงผลลัพธ์
        if ($row['l_leave_id'] == 7) {
            echo '<span class="text-primary">' . $late_count . ' ครั้ง</span>';
        } else {
            echo '<span class="text-primary">' . $leave_result . '</span>';
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
        }
        // ไม่มีสถานะ
        else {
            echo 'ไม่มีสถานะ';
        }
        echo '</td>';

        // 17
        echo '<td>';
        if ($row['l_hr_status'] == 0) {
            echo '<span class="text-warning"><b>รอตรวจสอบ</b></span>';
        } elseif ($row['l_hr_status'] == 1) {
            echo '<span class="text-success"><b>ผ่าน</b></span>';
        } else {
            echo '<span class="text-danger"><b>ไม่ผ่าน</b></span>';
        }
        echo '</td>';

        // 18
        $disabled = $row['l_leave_status'] == 1 ? 'disabled' : '';
        if ($row['l_leave_id'] != 7) {
            echo '<td><button type="button" class="button-shadow btn btn-danger cancel-leave-btn" data-leaveid="' . $row['l_leave_id'] . '" data-createdatetime="' . $row['l_create_datetime'] . '" data-usercode="' . $userCode . '" ' . $disabled . '><i class="fa-solid fa-times"></i> ยกเลิกรายการ</button></td>';
        } else if ($row['l_leave_id'] == 7) {
            echo '<td><button type="button" class="button-shadow btn btn-primary confirm-late-btn" data-createdatetime="' . $row['l_create_datetime'] . '" data-usercode="' . $userCode . '" ' . $disabled . '>ยืนยันรายการ</button></td>';
        } else {
            echo '<td></td>'; // กรณีที่ l_leave_id เท่ากับ 7 ไม่แสดงปุ่มและเว้นคอลัมน์ว่าง
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
    echo '<li class="page-item"><a class="page-link" href="?page=1">&laquo;</a></li>';
    echo '<li class="page-item"><a class="page-link" href="?page=' . ($currentPage - 1) . '">&lt;</a></li>';
}

// สร้างลิงก์สำหรับแต่ละหน้า
for ($i = 1; $i <= $totalPages; $i++) {
    if ($i == $currentPage) {
        echo '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
    } else {
        echo '<li class="page-item"><a class="page-link" href="?page=' . $i . '">' . $i . '</a></li>';
    }
}

// สร้างลิงก์ไปยังหน้าถัดไปหรือหน้าสุดท้าย
if ($currentPage < $totalPages) {
    echo '<li class="page-item"><a class="page-link" href="?page=' . ($currentPage + 1) . '">&gt;</a></li>';
    echo '<li class="page-item"><a class="page-link" href="?page=' . $totalPages . '">&raquo;</a></li>';
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
    </div>

    <script>
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
        var startDate = $('#startDate').val();
        var startTime = $('#startTime').val();
        var endDate = $('#endDate').val();
        var endTime = $('#endTime').val();

        // แสดงค่าที่ดึงได้
        console.log("Start Date: ", startDate);
        console.log("Start Time: ", startTime);
        console.log("End Date: ", endDate);
        console.log("End Time: ", endTime);

        var leaveDays = calculateLeaveDays(startDate, startTime, endDate, endTime);

        var alertMessage = '';
        var totalLeaveDays = 0;
        var currentLeaveDays = 0;
        var totalLeave = 0;
        var totalDaysAlert = $('#totalDays');
        // แสดงค่าที่คำนวณได้
        console.log("Leave Days: ", leaveDays);

        if (typeLeave == 1) {
            currentLeaveDays = parseFloat($('input[name="leave_personal_days"]').val()) || 0;
            totalLeave = parseFloat($('input[name="total_personal"]').val()) || 0;
            totalLeaveDays = currentLeaveDays + leaveDays;
            alertMessage = currentLeaveDays > totalLeave ?
                'ไม่สามารถลาได้ คุณได้ใช้สิทธิ์ลากิจได้รับค่าจ้างครบกำหนดแล้ว' : '';
            totalDaysAlert.text('คงเหลือ ' + (totalLeave - currentLeaveDays) + ' วัน')
        } else if (typeLeave == 2) {
            currentLeaveDays = parseFloat($('input[name="leave_personal_no_days"]').val()) || 0;
            totalLeave = parseFloat($('input[name="total_personal_no"]').val()) || 0;
            alertMessage = currentLeaveDays >= totalLeave ?
                'ไม่สามารถลาได้ คุณได้ใช้สิทธิ์ลากิจไม่ได้รับค่าจ้างครบกำหนดแล้ว' : '';
            totalDaysAlert.text('คงเหลือ ' + (totalLeave - currentLeaveDays) + ' วัน')
        } else if (typeLeave == 3) {
            currentLeaveDays = parseFloat($('input[name="leave_sick_days"]').val()) || 0;
            totalLeave = parseFloat($('input[name="total_sick"]').val()) || 0;
            alertMessage = currentLeaveDays >= totalLeave ? 'ไม่สามารถลาได้ คุณได้ใช้สิทธิ์ลาป่วยครบกำหนดแล้ว' : '';
            totalDaysAlert.text('คงเหลือ ' + (totalLeave - currentLeaveDays) + ' วัน')
        } else if (typeLeave == 4) {
            currentLeaveDays = parseFloat($('input[name="leave_sick_work_days"]').val()) || 0;
            totalLeave = parseFloat($('input[name="total_sick_work"]').val()) || 0;
            alertMessage = currentLeaveDays >= totalLeave ? 'ไม่สามารถลาได้ คุณได้ใช้สิทธิ์ลาป่วยจากงานครบกำหนดแล้ว' :
                '';
            totalDaysAlert.text('คงเหลือ ' + (totalLeave - currentLeaveDays) + ' วัน')
        } else if (typeLeave == 5) {
            currentLeaveDays = parseFloat($('input[name="leave_annual_days"]').val()) || 0;
            totalLeave = parseFloat($('input[name="total_annual"]').val()) || 0;
            alertMessage = currentLeaveDays >= totalLeave ? 'ไม่สามารถลาได้ คุณได้ใช้สิทธิ์ลาพักร้อนครบกำหนดแล้ว' : '';
            totalDaysAlert.text('คงเหลือ ' + (totalLeave - currentLeaveDays) + ' วัน')
        } else {
            totalDaysAlert.text('คงเหลือ ' + '-' + ' วัน')

        }

        // แสดงข้อความแจ้งเตือนถ้าจำเป็น
        if (alertMessage) {
            $('#alertCheckDays').text(alertMessage).removeClass('d-none'); // แสดงข้อความ
        } else {
            $('#alertCheckDays').addClass('d-none'); // ซ่อนข้อความ
        }
    }

    $(document).ready(function() {

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
                    minDate: today, // ห้ามเลือกวันที่ในอดีต
                    disable: response.holidays // ปิดวันที่ที่เป็นวันหยุด
                });

                flatpickr("#endDate", {
                    dateFormat: "d-m-Y", // ตั้งค่าเป็น วัน/เดือน/ปี
                    defaultDate: today, // กำหนดวันที่สิ้นสุดเป็นวันที่ปัจจุบัน
                    minDate: today, // ห้ามเลือกวันที่ในอดีต
                    disable: response.holidays // ปิดวันที่ที่เป็นวันหยุด
                });

                flatpickr("#urgentStartDate", {
                    dateFormat: "d-m-Y", // ตั้งค่าเป็น วัน/เดือน/ปี
                    defaultDate: today, // กำหนดวันที่เริ่มต้นเป็นวันที่ปัจจุบัน
                    minDate: today, // ห้ามเลือกวันที่ในอดีต
                    disable: response.holidays // ปิดวันที่ที่เป็นวันหยุด
                });

                flatpickr("#urgentEndDate", {
                    dateFormat: "d-m-Y", // ตั้งค่าเป็น วัน/เดือน/ปี
                    defaultDate: today, // กำหนดวันที่สิ้นสุดเป็นวันที่ปัจจุบัน
                    minDate: today, // ห้ามเลือกวันที่ในอดีต
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

            var formattedDate = year + "-" + month + "-" + day + " " + hours + ":" + minutes + ":" +
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
                // ปิดการใช้งานปุ่มส่งข้อมูลและแสดงสถานะการโหลด
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
                    }
                });
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

            // alert(comfirmName)
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
                            action: 'comfirm'
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