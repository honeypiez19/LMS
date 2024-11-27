<?php
session_start();
date_default_timezone_set('Asia/Bangkok');

include '../connect.php';
include '../session_lang.php';

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
    <?php include 'user_navbar.php'?>

    <?php
// echo $depart;
// echo $subDepart;
// มาสาย --------------------------------------------------------------------------------------------
$sql_check_late = "SELECT l_leave_start_date, l_leave_start_time, l_leave_end_time
FROM leave_list
WHERE l_usercode = :userCode
AND l_late_datetime <> NULL
AND l_leave_id = 7";

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
?>
    <div class="mt-3 container-fluid">
        <!-- <button type="button" class="btn btn-primary rounded-circle position-fixed bottom-0 start-0 m-3 btn-sm"
            data-bs-toggle="tooltip" data-bs-placement="top" style="width: 30px;" title="
        <strong>ความหมายของ :</strong> <code>ตัวอย่าง 1(3.5)</code><br>
        <ul>
            <li><strong>ตัวอักษรแรก (1)</strong> = จำนวนวัน</li>
            <li><strong>ตัวอักษรที่สอง (3)</strong> = จำนวนชั่วโมง</li>
            <li><strong>ตัวอักษรที่สาม (5)</strong> = จำนวนนาที</li>
        </ul>">
            <i class="fa-solid fa-exclamation"></i>
        </button> -->
        <div class="row">
            <div class="d-flex justify-content-between align-items-center">
                <form class="mt-3 mb-3 row" method="post">
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

echo "<select class='form-select' name='year' id='selectedYear'>";

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

                <!-- ปุ่มระเบียบการลา -->
                <button type="button" class="button-shadow btn btn-primary" data-bs-toggle="modal"
                    data-bs-target="#leaveRule">
                    <i class="fa-solid fa-file-shield"></i> ระเบียบการลา
                </button>
                <!-- Modal ระเบียบการลา -->
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
  AND YEAR(l_create_datetime) = :selectedYear
  AND l_leave_status = 0
  AND l_approve_status IN (2,6)
  AND l_approve_status2 = 4";

    $stmt_leave_personal = $conn->prepare($sql_leave_personal);
    $stmt_leave_personal->bindParam(':leave_id', $leave_id, PDO::PARAM_INT); // Bind the leave_id
    $stmt_leave_personal->bindParam(':userCode', $userCode);
    $stmt_leave_personal->bindParam(':selectedYear', $selectedYear, PDO::PARAM_INT);
    $stmt_leave_personal->execute();
    $result_leave_personal = $stmt_leave_personal->fetch(PDO::FETCH_ASSOC);

    if ($result_leave_personal) {
        $leave_personal_days = $result_leave_personal['total_leave_days'] ?? 0;
        $leave_personal_hours = $result_leave_personal['total_leave_hours'] ?? 0;
        $leave_personal_minutes = $result_leave_personal['total_leave_minutes'] ?? 0;

        // Employee leave balances
        $total_personal = $result_leave_personal['total_personal'] ?? 0;
        $total_personal_no = $result_leave_personal['total_personal_no'] ?? 0;
        $total_sick = $result_leave_personal['total_sick'] ?? 0;
        $total_sick_work = $result_leave_personal['total_sick_work'] ?? 0;
        $total_annual = $result_leave_personal['total_annual'] ?? 0;
        $total_other = $result_leave_personal['total_other'] ?? 0;
        $total_late = $result_leave_personal['late_count'] ?? 0;

        // Convert hours to days if applicable
        $leave_personal_days += floor($leave_personal_hours / 8);
        $leave_personal_hours = $leave_personal_hours % 8;

        // Adjust minutes if necessary
        if ($leave_personal_minutes >= 60) {
            $leave_personal_hours += floor($leave_personal_minutes / 60);
            $leave_personal_minutes = $leave_personal_minutes % 60;
        }

        if ($leave_personal_minutes > 0 && $leave_personal_minutes <= 30) {
            $leave_personal_minutes = 30;
        } elseif ($leave_personal_minutes > 30) {
            $leave_personal_minutes = 0;
            $leave_personal_hours += 1;
        }

        if ($leave_personal_minutes == 30) {
            $leave_personal_minutes = 5;
        }
    }

// Output the leave data
    echo '<div class="col-3">';

    // Check the leave type and display the appropriate data
    if ($leave_id == 1) {
        echo '<div class="card text-light mb-3 filter-card" style="background-color: #031B80;" data-leave-id="1">';
        echo '<div class="card-body">';
        echo '    <div class="card-title">';
        echo '        <div class="d-flex justify-content-between">';
        echo '            <div>';
        echo '<h5>' . $leave_personal_days . '(' . $leave_personal_hours . '.' . $leave_personal_minutes . ') / ' . $total_personal . '</h5>';
        echo '<p class="card-text">' . $leave_name . '</p>';
        echo '            </div>';
        echo '            <div class="d-flex justify-content-end">'; // Added this to align icon to the right
        echo '                <i class="mx-2 mt-3 fa-solid fa-sack-dollar fa-2xl"></i>';
        echo '            </div>';
        echo '        </div>';
        echo '    </div>'; // Close card-title
        echo '</div>'; // Close card-body
        echo '</div>'; // Close card
    } else if ($leave_id == 2) {
        echo '<div class="card text-light mb-3 filter-card" style="background-color: #0339A2;" data-leave-id="2">';
        echo '<div class="card-body">';
        echo '    <div class="card-title">';
        echo '        <div class="d-flex justify-content-between">';
        echo '            <div>';
        echo '<h5>' . $leave_personal_days . '(' . $leave_personal_hours . '.' . $leave_personal_minutes . ') / ' . $total_personal_no . '</h5>';
        echo '<p class="card-text">' . $leave_name . '</p>';
        echo '            </div>';
        echo '            <div class="d-flex justify-content-end">'; // Added this to align icon to the right
        echo '<i class="mx-2 mt-3 fa-solid fa-sack-xmark fa-2xl"></i>';
        echo '            </div>';
        echo '        </div>';
        echo '    </div>'; // Close card-title
        echo '</div>'; // Close card-body
        echo '</div>'; // Close card
    } else if ($leave_id == 3) {
        echo '<div class="card text-light mb-3 filter-card" style="background-color: #0357C4;" data-leave-id="3">';
        echo '<div class="card-body">';
        echo '    <div class="card-title">';
        echo '        <div class="d-flex justify-content-between">';
        echo '            <div>';
        echo '<h5>' . $leave_personal_days . '(' . $leave_personal_hours . '.' . $leave_personal_minutes . ') / ' . $total_sick . '</h5>';
        echo '<p class="card-text">' . $leave_name . '</p>';
        echo '            </div>';
        echo '            <div class="d-flex justify-content-end">'; // Added this to align icon to the right
        echo '<i class="mx-2 mt-3 fa-solid fa-syringe fa-2xl"></i>';
        echo '            </div>';
        echo '        </div>';
        echo '    </div>'; // Close card-title
        echo '</div>'; // Close card-body
        echo '</div>'; // Close card
    } else if ($leave_id == 4) {
        echo '<div class="card text-light mb-3 filter-card" style="background-color: #0357C4;" data-leave-id="4">';
        echo '<div class="card-body">';
        echo '    <div class="card-title">';
        echo '        <div class="d-flex justify-content-between">';
        echo '            <div>';
        echo '<h5>' . $leave_personal_days . '(' . $leave_personal_hours . '.' . $leave_personal_minutes . ') / ' . $total_sick_work . '</h5>';
        echo '<p class="card-text">' . $leave_name . '</p>';
        echo '            </div>';
        echo '            <div class="d-flex justify-content-end">'; // Added this to align icon to the right
        echo '<i class="mx-2 mt-3 fa-solid fa-user-injured fa-2xl"></i>';
        echo '            </div>';
        echo '        </div>';
        echo '    </div>'; // Close card-title
        echo '</div>'; // Close card-body
        echo '</div>'; // Close card
    } else if ($leave_id == 5) {
        echo '<div class="card text-light mb-3 filter-card" style="background-color: #0475E6;" data-leave-id="5">';
        echo '<div class="card-body">';
        echo '    <div class="card-title">';
        echo '        <div class="d-flex justify-content-between">';
        echo '            <div>';
        echo '<h5>' . $leave_personal_days . '(' . $leave_personal_hours . '.' . $leave_personal_minutes . ') / ' . $total_annual . '</h5>';
        echo '<p class="card-text">' . $leave_name . '</p>';
        echo '            </div>';
        echo '            <div class="d-flex justify-content-end">'; // Added this to align icon to the right
        echo '<i class="mx-2 mt-3 fa-solid fa-business-time fa-2xl"></i>';
        echo '            </div>';
        echo '        </div>';
        echo '    </div>'; // Close card-title
        echo '</div>'; // Close card-body
        echo '</div>'; // Close card
    } else if ($leave_id == 6) {
        echo '<div class="card text-light mb-3 filter-card" style="background-color: #4B9CED;" data-leave-id="6">';
        echo '<div class="card-body">';
        echo '    <div class="card-title">';
        echo '        <div class="d-flex justify-content-between">';
        echo '            <div>';
        echo '<h5>' . $leave_personal_days . '(' . $leave_personal_hours . '.' . $leave_personal_minutes . ')' . '</h5>';
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
        echo '<h5>' . $leave_personal_days . '(' . $leave_personal_hours . '.' . $leave_personal_minutes . ')' . '</h5>';
        echo '<p class="card-text">' . $leave_name . '</p>';
        echo '            </div>';
        echo '            <div class="d-flex justify-content-end">'; // Added this to align icon to the right
        echo '<i class="mx-2 mt-3 fa-solid fa-bars fa-2xl"></i>';
        echo '            </div>';
        echo '        </div>';
        echo '    </div>'; // Close card-title
        echo '</div>'; // Close card-body
        echo '</div>'; // Close card
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
                            <form id="leaveForm" method="POST" enctype="multipart/form-data"
                                action="user_dashboard.php">
                                <div class="row">
                                    <div class="col-24 alert alert-danger d-none" role="alert" name="alertCheckDays">
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
                                        <input type="text" class="form-control" id="startDate"
                                            onchange="updateLeaveDays()" required>
                                    </div>
                                    <div class=" col-6">
                                        <label for="startTime" class="form-label">เวลาที่เริ่มต้น</label>
                                        <span style="color: red;">*</span>
                                        <select class="form-select" id="startTime" name="startTime" required>
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
                                            <option value="13:10">13:10</option>
                                            <option value="13:30">13:30</option>
                                            <option value="13:40">13:40</option>
                                            <option value="14:00">14:00</option>
                                            <option value="14:10">14:10</option>
                                            <option value="14:30">14:30</option>
                                            <option value="14:40">14:40</option>
                                            <option value="15:00">15:00</option>
                                            <option value="15:10">15:10</option>
                                            <option value="15:30">15:30</option>
                                            <option value="15:40">15:40</option>
                                            <option value="16:00">16:00</option>
                                            <option value="16:10">16:10</option>
                                            <option value="17:00">16:40</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mt-3 row">
                                    <div class="col-6">
                                        <label for="endDate" class="form-label">วันที่สิ้นสุด</label>
                                        <span style="color: red;">*</span>
                                        <input type="text" class="form-control" id="endDate" required>
                                    </div>
                                    <div class="col-6">
                                        <label for="endTime" class="form-label">เวลาที่สิ้นสุด</label>
                                        <span style="color: red;">*</span>
                                        <select class="form-select" id="endTime" name="endTime" required>
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
                                            <option value="13:10">13:10</option>
                                            <option value="13:30">13:30</option>
                                            <option value="13:40">13:40</option>
                                            <option value="14:00">14:00</option>
                                            <option value="14:10">14:10</option>
                                            <option value="14:30">14:30</option>
                                            <option value="14:40">14:40</option>
                                            <option value="15:00">15:00</option>
                                            <option value="15:10">15:10</option>
                                            <option value="15:30">15:30</option>
                                            <option value="15:40">15:40</option>
                                            <option value="16:00">16:00</option>
                                            <option value="16:10">16:10</option>
                                            <option value="17:00" selected>16:40</option>
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
                            <div class="col-24 alert alert-danger d-none" role="alert" name="alertCheckDays">
                                ไม่สามารถลาได้ คุณได้ใช้สิทธิ์ครบกำหนดแล้ว
                            </div>
                            <form id="urgentLeaveForm" method="POST" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-12">
                                        <label for="urgentLeaveType" class="form-label">ประเภทการลา</label>
                                        <span style="color: red;">*</span>
                                        <span class="badge rounded-pill text-bg-info" name="totalDays">เหลือ -
                                            วัน</span>
                                        <select class="form-select" id="urgentLeaveType"
                                            onchange="checkDays(this.value)" required>
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
                                            <option value="13:10">13:10</option>
                                            <option value="13:30">13:30</option>
                                            <option value="13:40">13:40</option>
                                            <option value="14:00">14:00</option>
                                            <option value="14:10">14:10</option>
                                            <option value="14:30">14:30</option>
                                            <option value="14:40">14:40</option>
                                            <option value="15:00">15:00</option>
                                            <option value="15:10">15:10</option>
                                            <option value="15:30">15:30</option>
                                            <option value="15:40">15:40</option>
                                            <option value="16:00">16:00</option>
                                            <option value="16:10">16:10</option>
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
                                            <option value="13:10">13:10</option>
                                            <option value="13:30">13:30</option>
                                            <option value="13:40">13:40</option>
                                            <option value="14:00">14:00</option>
                                            <option value="14:10">14:10</option>
                                            <option value="14:30">14:30</option>
                                            <option value="14:40">14:40</option>
                                            <option value="15:00">15:00</option>
                                            <option value="15:10">15:10</option>
                                            <option value="15:30">15:30</option>
                                            <option value="15:40">15:40</option>
                                            <option value="16:00">16:00</option>
                                            <option value="16:10">16:10</option>
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
                                    <button type="submit" class="btn btn-success" name="submit" style="width: 100px;"
                                        id="btnSubmitForm2">บันทึก</button>
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
        echo '';

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
        // 13:10
        else if ($row['l_leave_start_time'] == '13:30:00' && $row['l_remark'] == '13:10:00') {
            echo '<td>' . $row['l_leave_start_date'] . '<br> 13:10:00</td>';
        }
        // 13:40
        else if ($row['l_leave_start_time'] == '14:00:00' && $row['l_remark'] == '13:40:00') {
            echo '<td>' . $row['l_leave_start_date'] . '<br> 13:40:00</td>';
        }
        // 14:10
        else if ($row['l_leave_start_time'] == '14:30:00' && $row['l_remark'] == '14:10:00') {
            echo '<td>' . $row['l_leave_start_date'] . '<br> 14:10:00</td>';
        }
        // 14:40
        else if ($row['l_leave_start_time'] == '15:00:00' && $row['l_remark'] == '14:40:00') {
            echo '<td>' . $row['l_leave_start_date'] . '<br> 14:40:00</td>';
        }
        // 15:10
        else if ($row['l_leave_start_time'] == '15:30:00' && $row['l_remark'] == '15:10:00') {
            echo '<td>' . $row['l_leave_start_date'] . '<br> 15:10:00</td>';
        }
        // 15:40
        else if ($row['l_leave_start_time'] == '16:00:00' && $row['l_remark'] == '15:40:00') {
            echo '<td>' . $row['l_leave_start_date'] . '<br> 15:40:00</td>';
        }
        // 16:10
        else if ($row['l_leave_start_time'] == '16:30:00' && $row['l_remark'] == '16:10:00') {
            echo '<td>' . $row['l_leave_start_date'] . '<br> 16:10:00</td>';
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
        // 13:10
        else if ($row['l_leave_end_time'] == '13:30:00' && $row['l_remark'] == '13:10:00') {
            echo '<td>' . $row['l_leave_end_date'] . '<br> 13:10:00</td>';
        }
        // 13:40
        else if ($row['l_leave_end_time'] == '14:00:00' && $row['l_remark'] == '13:40:00') {
            echo '<td>' . $row['l_leave_end_date'] . '<br> 13:40:00</td>';
        }
        // 14:10
        else if ($row['l_leave_end_time'] == '14:30:00' && $row['l_remark'] == '14:10:00') {
            echo '<td>' . $row['l_leave_end_date'] . '<br> 14:10:00</td>';
        }
        // 14:40
        else if ($row['l_leave_end_time'] == '15:00:00' && $row['l_remark'] == '14:40:00') {
            echo '<td>' . $row['l_leave_end_date'] . '<br> 14:40:00</td>';
        }
        // 15:10
        else if ($row['l_leave_end_time'] == '15:30:00' && $row['l_remark'] == '15:10:00') {
            echo '<td>' . $row['l_leave_end_date'] . '<br> 15:10:00</td>';
        }
        // 15:40
        else if ($row['l_leave_end_time'] == '16:00:00' && $row['l_remark'] == '15:40:00') {
            echo '<td>' . $row['l_leave_end_date'] . '<br> 15:40:00</td>';
        }
        // 16:10
        else if ($row['l_leave_end_time'] == '16:30:00' && $row['l_remark'] == '16:10:00') {
            echo '<td>' . $row['l_leave_end_date'] . '<br> 16:10:00</td>';
        }
        // 16:40
        else if ($row['l_leave_end_time'] == '17:00:00') {
            echo '<td>' . $row['l_leave_end_date'] . '<br> 16:40:00</td>';
        } else {
            // กรณีอื่น ๆ แสดงเวลาตาม l_leave_start_time
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

        $dateNow = date('Y-m-d');
        $disabledCancalCheck = (
            $row['l_approve_status'] != 0
            && $row['l_approve_status2'] != 1
            && $row['l_leave_start_date'] <= $dateNow
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
            var totalDaysAlert = $('[name="totalDays"]');

            // ตรวจสอบปีเพื่อเช็คว่าเป็นปีถัดไปหรือไม่
            var currentYear = new Date().getFullYear(); // ปีปัจจุบัน
            var startYear = new Date(startDate).getFullYear(); // ปีของวันที่เริ่มต้น

            // ถ้าเป็นปีหน้าให้คืนสิทธิ์
            if (startYear > currentYear) {
                if (typeLeave == 1) {
                    currentLeaveDays = parseFloat($('input[name="leave_personal_days"]').val()) || 0;
                    totalLeave = parseFloat($('input[name="total_personal"]').val()) || 0;
                    totalLeaveDays = currentLeaveDays + leaveDays;
                    alertMessage = currentLeaveDays >= totalLeave ?
                        'ไม่สามารถลาได้ คุณได้ใช้สิทธิ์ลากิจได้รับค่าจ้างครบกำหนดแล้ว' : '';
                    totalDaysAlert.text('คงเหลือ ' + (totalLeave) + ' วัน');
                } else if (typeLeave == 2) {
                    currentLeaveDays = parseFloat($('input[name="leave_personal_no_days"]').val()) || 0;
                    totalLeave = parseFloat($('input[name="total_personal_no"]').val()) || 0;
                    alertMessage = currentLeaveDays >= totalLeave ?
                        'ไม่สามารถลาได้ คุณได้ใช้สิทธิ์ลากิจไม่ได้รับค่าจ้างครบกำหนดแล้ว' : '';
                    totalDaysAlert.text('คงเหลือ ' + (totalLeave) + ' วัน');
                } else if (typeLeave == 3) {
                    currentLeaveDays = parseFloat($('input[name="leave_sick_days"]').val()) || 0;
                    totalLeave = parseFloat($('input[name="total_sick"]').val()) || 0;
                    alertMessage = currentLeaveDays >= totalLeave ?
                        'ไม่สามารถลาได้ คุณได้ใช้สิทธิ์ลาป่วยครบกำหนดแล้ว' : '';
                    totalDaysAlert.text('คงเหลือ ' + (totalLeave) + ' วัน');
                } else if (typeLeave == 4) {
                    currentLeaveDays = parseFloat($('input[name="leave_sick_work_days"]').val()) || 0;
                    totalLeave = parseFloat($('input[name="total_sick_work"]').val()) || 0;
                    alertMessage = currentLeaveDays >= totalLeave ?
                        'ไม่สามารถลาได้ คุณได้ใช้สิทธิ์ลาป่วยจากงานครบกำหนดแล้ว' : '';
                    totalDaysAlert.text('คงเหลือ ' + (totalLeave) + ' วัน');
                } else if (typeLeave == 5) {
                    currentLeaveDays = parseFloat($('input[name="leave_annual_days"]').val()) || 0;
                    totalLeave = parseFloat($('input[name="total_annual"]').val()) || 0;
                    alertMessage = currentLeaveDays >= totalLeave ?
                        'ไม่สามารถลาได้ คุณได้ใช้สิทธิ์ลาพักร้อนครบกำหนดแล้ว' : '';
                    totalDaysAlert.text('คงเหลือ ' + (totalLeave) + ' วัน');
                } else {
                    totalDaysAlert.text('คงเหลือ ' + '-' + ' วัน');
                }
            } else {
                // คำนวณตามปกติสำหรับปีปัจจุบัน
                if (typeLeave == 1) {
                    currentLeaveDays = parseFloat($('input[name="leave_personal_days"]').val()) || 0;
                    totalLeave = parseFloat($('input[name="total_personal"]').val()) || 0;
                    totalLeaveDays = currentLeaveDays + leaveDays;
                    alertMessage = currentLeaveDays >= totalLeave ?
                        'ไม่สามารถลาได้ คุณได้ใช้สิทธิ์ลากิจได้รับค่าจ้างครบกำหนดแล้ว' : '';
                    totalDaysAlert.text('คงเหลือ ' + (totalLeave - currentLeaveDays) + ' วัน');
                } else if (typeLeave == 2) {
                    currentLeaveDays = parseFloat($('input[name="leave_personal_no_days"]').val()) || 0;
                    totalLeave = parseFloat($('input[name="total_personal_no"]').val()) || 0;
                    alertMessage = currentLeaveDays >= totalLeave ?
                        'ไม่สามารถลาได้ คุณได้ใช้สิทธิ์ลากิจไม่ได้รับค่าจ้างครบกำหนดแล้ว' : '';
                    totalDaysAlert.text('คงเหลือ ' + (totalLeave - currentLeaveDays) + ' วัน');
                } else if (typeLeave == 3) {
                    currentLeaveDays = parseFloat($('input[name="leave_sick_days"]').val()) || 0;
                    totalLeave = parseFloat($('input[name="total_sick"]').val()) || 0;
                    alertMessage = currentLeaveDays >= totalLeave ?
                        'ไม่สามารถลาได้ คุณได้ใช้สิทธิ์ลาป่วยครบกำหนดแล้ว' : '';
                    totalDaysAlert.text('คงเหลือ ' + (totalLeave - currentLeaveDays) + ' วัน');
                } else if (typeLeave == 4) {
                    currentLeaveDays = parseFloat($('input[name="leave_sick_work_days"]').val()) || 0;
                    totalLeave = parseFloat($('input[name="total_sick_work"]').val()) || 0;
                    alertMessage = currentLeaveDays >= totalLeave ?
                        'ไม่สามารถลาได้ คุณได้ใช้สิทธิ์ลาป่วยจากงานครบกำหนดแล้ว' : '';
                    totalDaysAlert.text('คงเหลือ ' + (totalLeave - currentLeaveDays) + ' วัน');
                } else if (typeLeave == 5) {
                    currentLeaveDays = parseFloat($('input[name="leave_annual_days"]').val()) || 0;
                    totalLeave = parseFloat($('input[name="total_annual"]').val()) || 0;
                    alertMessage = currentLeaveDays >= totalLeave ?
                        'ไม่สามารถลาได้ คุณได้ใช้สิทธิ์ลาพักร้อนครบกำหนดแล้ว' : '';
                    totalDaysAlert.text('คงเหลือ ' + (totalLeave - currentLeaveDays) + ' วัน');
                } else {
                    totalDaysAlert.text('คงเหลือ ' + '-' + ' วัน');
                }
            }

            // แสดงข้อความแจ้งเตือนถ้าจำเป็น
            if (alertMessage) {
                $('*[name="alertCheckDays"]').text(alertMessage).removeClass('d-none'); // แสดงข้อความ
            } else {
                $('*[name="alertCheckDays"]').addClass('d-none'); // ซ่อนข้อความ
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
                    url: 'u_ajax_get_detail.php',
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

            $('#startDate').change(function() {
                var leaveType = $('#leaveType').val();
                checkDays(leaveType);
            });

            $.ajax({
                url: 'u_ajax_get_holiday.php', // สร้างไฟล์ PHP เพื่อตรวจสอบวันหยุด
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

                // if (startTime == '08:45') {
                //     startTime = '09:00';
                // } else if (startTime == '09:45') {
                //     startTime = '10:00';
                // } else if (startTime == '10:45') {
                //     startTime = '11:00';
                // } else {
                //     startTime;
                // }

                // เพิ่มข้อมูลจากฟอร์มลงใน FormData object
                fd.append('leaveType', leaveType);
                fd.append('leaveReason', leaveReason);
                fd.append('startDate',
                    startDate);
                fd.append('startTime', startTime);
                fd.append('endDate', endDate);
                fd.append(
                    'endTime', endTime);
                if (files.length > 0) {
                    fd.append('file', files[0]);
                }

                var createDate = new Date();
                createDate.setHours(createDate.getHours() + 7); // Adjust to Thai timezone (UTC+7)
                var formattedDate = createDate.toISOString().slice(0, 19).replace('T', ' ');
                fd.append('formattedDate', formattedDate);
                // alert(formattedDate);

                // ตรวจสอบหากมี alert ถูกแสดง (ไม่มี class d-none)
                if (!$('*[name="alertCheckDays"]').hasClass('d-none')) {
                    Swal.fire({
                        title: "ไม่สามารถลาได้",
                        text: "ใช้สิทธิ์หมดแล้ว กรุณาเปลี่ยนประเภทการลา",
                        icon: "error"
                    });
                    return false; // หยุดการส่งฟอร์ม
                }

                // ตรวจสอบข้อมูลก่อนการส่ง
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
                            return false; // หยุดการส่งแบบฟอร์ม
                        }
                    }

                    var checkStartDate = $('#startDate').val();
                    var checkEndDate = $('#endDate').val();

                    // แปลงวันที่จาก string เป็น Date object
                    var startDateParts = checkStartDate.split("-");
                    var endDateParts = checkEndDate.split("-");

                    // แปลงเป็น Date object
                    var startDate = new Date(startDateParts[2], startDateParts[1] - 1, startDateParts[
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
                    } else {
                        // ปิดการใช้งานปุ่มส่งข้อมูลและแสดงสถานะการโหลด
                        $('#btnSubmitForm1').prop('disabled', true).html(
                            '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> <span role="status">Loading...</span>'
                        );

                        // ส่งข้อมูลแบบ AJAX
                        $.ajax({
                            url: 'u_ajax_add_leave.php',
                            type: 'POST',
                            data: fd,
                            contentType: false,
                            processData: false,
                            success: function(response) {
                                Swal.fire({
                                    title: "บันทึกสำเร็จ",
                                    text: "บันทึกคำขอลาสำเร็จ",
                                    icon: "success"
                                }).then(() => {
                                    location.reload();
                                });
                            },
                            error: function() {
                                Swal.fire({
                                    title: "เกิดข้อผิดพลาด",
                                    text: "ไม่สามารถบันทึกคำขอลาได้",
                                    icon: "error"
                                });
                            },
                            complete: function() {
                                // เปิดการใช้งานปุ่มอีกครั้ง
                                $('#btnSubmitForm1').prop('disabled', false).html(
                                    'ยื่นใบลา');
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

                // ตรวจสอบหากมี alert ถูกแสดง (ไม่มี class d-none)
                if (!$('*[name="alertCheckDays"]').hasClass('d-none')) {
                    Swal.fire({
                        title: "ไม่สามารถลาได้",
                        text: "ใช้สิทธิ์หมดแล้ว กรุณาเปลี่ยนประเภทการลา",
                        icon: "error"
                    });
                    console.log("Cannot submit form, alert is visible.");
                    return false; // หยุดการส่งฟอร์ม
                }

                // console.log(urgentLeaveType)
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
                    if (urgentEndDate < urgentStartDate) {
                        Swal.fire({
                            title: "ไม่สามารถลาได้",
                            text: "กรุณาเลือกวันที่เริ่มต้นลาใหม่",
                            icon: "error"
                        });
                    } else {
                        $.ajax({
                            url: 'u_ajax_add_urgent_leave.php',
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
                            url: 'u_ajax_delete_leave.php',
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
                            url: 'u_upd_late_time.php',
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