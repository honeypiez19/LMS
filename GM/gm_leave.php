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
    <title>สถิติการลา</title>

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
</head>

<body>
    <?php include 'manager_navbar.php'?>

    <nav class="navbar bg-body-tertiary" style="background-color: #072ac8; box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.3);
  border: none;">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-auto">
                    <i class="fa-solid fa-chart-line fa-2xl"></i>
                </div>
                <div class="col-auto">
                    <h3>สถิติการลาและการมาสาย</h3>
                </div>
            </div>
        </div>
    </nav>
    <div class="container">
        <form class="mt-3 mb-3 row" method="post">
            <label for="" class="mt-2 col-auto">เลือกปี</label>
            <div class="col-auto">
                <?php
$selectedYear = date('Y'); // ปีปัจจุบัน
if (isset($_POST['year'])) {
    $selectedYear = $_POST['year'];
}
echo "<select class='form-select' name='year' id='selectYear'>";
for ($i = 0; $i <= 2; $i++) {
    $year = date('Y', strtotime("last day of -$i year"));
    echo "<option value='$year'" . ($year == $selectedYear ? " selected" : "") . ">$year</option>";
}
echo "</select>";
?>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary button-shadow">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </div>
        </form>

        <table class="mt-3 table table-hover table-bordered" style="border-top: 1px solid rgba(0, 0, 0, 0.1);"
            id="leaveTable">
            <thead>
                <tr class="table-dark text-center align-middle">
                    <th style="width: 40%;">ประเภทการลา</th>
                    <th>จำนวนวันลาที่ใช้ไป</th>
                    <th>จำนวนวันลาที่เหลือ</th>
                </tr>
            </thead>
            <tbody>
                <?php
// ------------------------------------------------------------------------------
// ถ้าเลือกปี
if (isset($_POST['year'])) {
    $selectedYear = $_POST['year'];

    // ลากิจได้รับค่าจ้าง
    $sql_leave_personal = "SELECT
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
                -- กรณีลาในหลายวัน
                (DATEDIFF(l_leave_end_date, l_leave_start_date) * 8) +
                CASE
                    WHEN TIME(l_leave_end_time) <= '11:45:00' THEN 4
                    ELSE 8
                END
        END
        ) AS leave_personal_count,
        (SELECT e_leave_personal FROM employees WHERE e_usercode = :userCode) AS total_personal
        FROM leave_list
        WHERE l_leave_id = 1
        AND l_usercode = :userCode
        AND NOT (TIME(l_leave_start_time) >= '11:45:00' AND TIME(l_leave_end_time) <= '12:45:00')
        AND YEAR(l_create_datetime) = :selectedYear
        AND l_leave_status = 0";

    $stmt_leave_personal = $conn->prepare($sql_leave_personal);
    $stmt_leave_personal->bindParam(':userCode', $userCode);
    $stmt_leave_personal->bindParam(':selectedYear', $selectedYear, PDO::PARAM_INT);
    $stmt_leave_personal->execute();
    $result_leave_personal = $stmt_leave_personal->fetch(PDO::FETCH_ASSOC);

    if ($result_leave_personal) {
        $total_personal = $result_leave_personal['total_personal'] ?? 0;
        $leave_personal_count = $result_leave_personal['leave_personal_count'] ?? 0;

        // คำนวณวันและชั่วโมงจากผลรวม
        // ปัดเป็นชั่วโมงหากเกินค่าที่กำหนด
        $leave_personal_count = round($leave_personal_count * 2) / 2; // ปัดขึ้นเป็นครึ่งชั่วโมง
        $leave_personal_days = floor($leave_personal_count / 8);
        $leave_personal_hours_remain = floor($leave_personal_count % 8);
        $leave_personal_minutes_remain = ($leave_personal_count - floor($leave_personal_count)) * 60;

        $leave_personal_minutes_remain = round($leave_personal_minutes_remain / 30) * 30;

        if ($leave_personal_minutes_remain == 30) {
            $leave_personal_minutes_remain = 5;
        } else {
            $leave_personal_minutes_remain = 0;
        }

    } else {
        echo '<p>ไม่พบข้อมูล</p>';
    }

    // ----------------------------------------------------------------------------------------------
    // ลากิจไม่ได้รับค่าจ้าง
    $sql_leave_personal_no = "SELECT
    SUM(
        CASE
            WHEN DATEDIFF(l_leave_end_date, l_leave_start_date) = 0 THEN
                -- Leave on the same day
                CASE
                    WHEN TIME_TO_SEC(TIMEDIFF(l_leave_end_time, l_leave_start_time)) = 8 * 3600 + 40 * 60 THEN 8
                    WHEN TIME_TO_SEC(TIMEDIFF(l_leave_end_time, l_leave_start_time)) > 4 * 3600 THEN
                        ROUND((TIME_TO_SEC(TIMEDIFF(l_leave_end_time, l_leave_start_time)) - 1 * 3600) / 3600, 1)
                    ELSE
                        ROUND(TIME_TO_SEC(TIMEDIFF(l_leave_end_time, l_leave_start_time)) / 3600, 1)
                END
            ELSE
                -- Leave spanning multiple days
                (DATEDIFF(l_leave_end_date, l_leave_start_date) * 8) +
                CASE
                    WHEN TIME(l_leave_end_time) <= '11:45:00' THEN 4
                    ELSE 8
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
    } else {
        echo '<p>ไม่พบข้อมูล</p>';
    }
    // ----------------------------------------------------------------------------------------------
    // ลาป่วย
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
            -- กรณีลาในหลายวัน
            (DATEDIFF(l_leave_end_date, l_leave_start_date) * 8) +
            CASE
                WHEN TIME(l_leave_end_time) <= '11:45:00' THEN 4
                ELSE 8
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
    } else {
        echo '<p>ไม่พบข้อมูล</p>';
    }
    // ----------------------------------------------------------------------------------------------
    // ลาป่วยจากงาน
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
            -- กรณีลาในหลายวัน
            (DATEDIFF(l_leave_end_date, l_leave_start_date) * 8) +
            CASE
                WHEN TIME(l_leave_end_time) <= '11:45:00' THEN 4
                ELSE 8
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

    } else {
        echo '<p>ไม่พบข้อมูล</p>';
    }
    // ----------------------------------------------------------------------------------------------
    // ลาพักร้อน
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
            -- กรณีลาในหลายวัน
            (DATEDIFF(l_leave_end_date, l_leave_start_date) * 8) +
            CASE
                WHEN TIME(l_leave_end_time) <= '11:45:00' THEN 4
                ELSE 8
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

    } else {
        echo '<p>ไม่พบข้อมูล</p>';
    }
    // ----------------------------------------------------------------------------------------------
    // มาสาย
    $sql_late = "SELECT COUNT(l_list_id) AS late_count FROM leave_list WHERE l_leave_id = '7' AND l_usercode = '$userCode' AND Year(l_create_datetime) = '$selectedYear'";
    $result_late = $conn->query($sql_late)->fetch(PDO::FETCH_ASSOC);
    $late_count = $result_late['late_count'];

    // ----------------------------------------------------------------------------------------------
    // หยุดงาน
    $sql_absence_work = "SELECT COUNT(l_list_id) AS stop_work FROM leave_list WHERE l_leave_id = '6' AND YEAR(l_leave_start_date) = '$selectedYear'";
    $result_absence_work = $conn->query($sql_absence_work)->fetch(PDO::FETCH_ASSOC);
    $stop_work = $result_absence_work['stop_work'];

    // ----------------------------------------------------------------------------------------------
    // อื่น ๆ
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
                -- กรณีลาในหลายวัน
                (DATEDIFF(l_leave_end_date, l_leave_start_date) * 8) +
                CASE
                    WHEN TIME(l_leave_end_time) <= '11:45:00' THEN 4
                    ELSE 8
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

    } else {
        echo '<p>No data found</p>';
    }
    // ----------------------------------------------------------------------------------------------

    $total_personal_remaining_days = max($total_personal - $leave_personal_days, 0);

    echo '<tr class="text-center align-middle">';
    echo '<td>ลากิจได้รับค่าจ้าง</td>';
    echo '<td>' . $leave_personal_days . ' วัน ' . $leave_personal_hours_remain . ' ชั่วโมง ' . $leave_personal_minutes_remain . ' นาที</td>';
    echo '<td>' . $total_personal_remaining_days . ' วัน</td>';
    echo '</tr>';

    $total_personal_no_remaining_days = max($total_personal_no - $leave_personal_no_days, 0);

    echo '<tr class="text-center align-middle">';
    echo '<td>' . 'ลากิจไม่ได้รับค่าจ้าง' . '</td>';
    echo '<td>' . $leave_personal_no_days . ' วัน ' . $leave_personal_no_hours_remain . ' ชั่วโมง ' . $leave_personal_no_minutes_remain . ' นาที ' . '</td>';
    echo '<td>' . $total_personal_no_remaining_days . ' วัน</td>';
    echo '</tr>';

    $total_sick_remaining_days = max($total_sick - $leave_sick_days, 0);

    echo '<tr class="text-center align-middle">';
    echo '<td>' . 'ลาป่วย' . '</td>';
    echo '<td>' . $leave_sick_days . ' วัน ' . $leave_sick_hours_remain . ' ชั่วโมง ' . $leave_sick_minutes_remain . ' นาที ' . '</td>';
    echo '<td>' . $total_sick_remaining_days . ' วัน</td>';
    echo '</tr>';

    $total_sick_work_remaining_days = max($total_sick_work - $leave_sick_work_days, 0);

    echo '<tr class="text-center align-middle">';
    echo '<td>' . 'ลาป่วยจากงาน' . '</td>';
    echo '<td>' . $leave_sick_work_days . ' วัน ' . $leave_sick_work_hours_remain . ' ชั่วโมง ' . $leave_sick_work_minutes_remain . ' นาที ' . '</td>';
    echo '<td>' . $total_sick_work_remaining_days . ' วัน</td>';
    echo '<tr class="text-center align-middle">';

    $total_annual_remaining_days = max($total_annual - $leave_annual_days, 0);

    echo '<tr class="text-center align-middle">';
    echo '<td>' . 'ลาพักร้อน' . '</td>';
    echo '<td>' . $leave_annual_days . ' วัน ' . $leave_annual_hours_remain . ' ชั่วโมง ' . $leave_annual_minutes_remain . ' นาที' . '</td>';
    echo '<td>' . $total_annual_remaining_days . ' วัน</td>';
    echo '</tr>';

    $total_other_remaining_days = max($total_other - $other_days, 0);

    echo '<tr class="text-center align-middle">';
    echo '<td>' . 'มาสาย' . '</td>';
    echo '<td>' . $late_count . ' ครั้ง</td>';
    echo '<td>' . '-' . '</td>';
    echo '</tr>';

    echo '<tr class="text-center align-middle">';
    echo '<td>' . 'หยุดงาน' . '</td>';
    echo '<td>' . $stop_work . ' วัน</td>';
    echo '<td>' . '-' . '</td>';
    echo '</tr>';

    echo '<tr class="text-center align-middle">';
    echo '<td>' . 'อื่น ๆ' . '</td>';
    echo '<td>' . $other_days . ' วัน ' . $other_hours_remain . ' ชั่วโมง ' . $other_minutes_remain . ' นาที' . '</td>';
    echo '<td>' . $total_other_remaining_days . ' วัน</td>';
    echo '</tr>';

    $sum_day = $leave_personal_days + $leave_personal_no_days + $leave_sick_days + $leave_sick_work_days + $stop_work;
    $sum_hours = $leave_personal_hours_remain + $leave_personal_no_hours_remain + $leave_sick_hours_remain + $leave_sick_work_hours_remain;
    $sum_minutes = $leave_personal_minutes_remain + $leave_personal_no_minutes_remain + $leave_sick_minutes_remain + $leave_sick_work_minutes_remain;

    echo '<tr class="text-center align-middle">';
    if ($sum_day < 10) {
        echo '<td style="font-weight: bold;">' . 'รวมจำนวนวันลาทั้งหมด (ยกเว้นลาพักร้อน / อื่น ๆ)' . '</td>';
        echo '<td colspan="2" style="font-weight: bold;">' . $sum_day . ' วัน ' . $sum_hours . ' ชั่วโมง ' . $sum_minutes . ' นาที' . '</td>';
    } else if ($sum_day == 10) {
        echo '<div class="alert alert-primary" role="alert">';
        echo '<i class="fa-solid fa-circle-exclamation"></i>' . ' รวมจำนวนวันลาทั้งหมด ' . $sum_day . ' วัน (ยกเว้นลาพักร้อน / อื่น ๆ)';
        echo '</div>';
        echo '<td style="font-weight: bold;">' . 'รวมจำนวนวันลาทั้งหมด (ยกเว้นลาพักร้อน / อื่น ๆ)' . '</td>';
        echo '<td colspan="2" style="font-weight: bold;" class="text-primary">' . $sum_day . ' วัน ' . $sum_hours . ' ชั่วโมง ' . $sum_minutes . ' นาที' . '</td>';
    } else if ($sum_day == 11) {
        echo '<div class="alert alert-primary" role="alert">';
        echo '<i class="fa-solid fa-circle-exclamation"></i>' . ' รวมจำนวนวันลาทั้งหมด ' . $sum_day . ' วัน (ยกเว้นลาพักร้อน / อื่น ๆ)';
        echo '</div>';
        echo '<td style="font-weight: bold;">' . 'รวมจำนวนวันลาทั้งหมด (ยกเว้นลาพักร้อน / อื่น ๆ)' . '</td>';
        echo '<td colspan="2" style="font-weight: bold;" class="text-primary">' . $sum_day . ' วัน ' . $sum_hours . ' ชั่วโมง ' . $sum_minutes . ' นาที' . '</td>';
    } else if ($sum_day == 12) {
        echo '<div class="alert alert-warning" role="alert">';
        echo '<i class="fa-solid fa-circle-exclamation"></i>' . ' รวมจำนวนวันลาทั้งหมด ' . $sum_day . ' วัน (ยกเว้นลาพักร้อน / อื่น ๆ)';
        echo '</div>';
        echo '<td style="font-weight: bold;">' . 'รวมจำนวนวันลาทั้งหมด (ยกเว้นลาพักร้อน / อื่น ๆ)' . '</td>';
        echo '<td colspan="2" style="font-weight: bold;" class="text-warning">' . $sum_day . ' วัน ' . $sum_hours . ' ชั่วโมง ' . $sum_minutes . ' นาที' . '</td>';
    } else if ($sum_day == 13) {
        echo '<div class="alert alert-danger" role="alert">';
        echo '<i class="fa-solid fa-circle-exclamation"></i>' . ' รวมจำนวนวันลาทั้งหมด ' . $sum_day . ' วัน (ยกเว้นลาพักร้อน / อื่น ๆ)';
        echo '</div>';
        echo '<td style="font-weight: bold;">' . 'รวมจำนวนวันลาทั้งหมด (ยกเว้นลาพักร้อน / อื่น ๆ)' . '</td>';
        echo '<td colspan="2" style="font-weight: bold;" class="text-danger">' . $sum_day . ' วัน ' . $sum_hours . ' ชั่วโมง ' . $sum_minutes . ' นาที' . '</td>';
    } else if ($sum_day >= 14) {
        echo '<div class="alert alert-danger" role="alert">';
        echo '<i class="fa-solid fa-circle-exclamation"></i>' . ' รวมจำนวนวันลาทั้งหมด ' . $sum_day . ' วัน (ยกเว้นลาพักร้อน / อื่น ๆ)';
        echo '</div>';
        echo '<td style="font-weight: bold;">' . 'รวมจำนวนวันลาทั้งหมด (ยกเว้นลาพักร้อน / อื่น ๆ)' . '</td>';
        echo '<td colspan="2" style="font-weight: bold;" class="text-danger">' . $sum_day . ' วัน ' . $sum_hours . ' ชั่วโมง ' . $sum_minutes . ' นาที' . '</td>';
    } else {
        // echo '<td colspan="2" style="font-weight: bold;" class="text-danger">' . $sum_day . ' วัน ' . $sum_hours . ' ชั่วโมง ' . $sum_minutes . ' นาที' . '</td>';
    }
}
// ------------------------------------------------------------------------------
// ถ้าไม่เลือกปี
else {
    $selectedYear = date('Y');
    // ลากิจได้รับค่าจ้าง
    $sql_leave_personal = "SELECT
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
                -- กรณีลาในหลายวัน
                (DATEDIFF(l_leave_end_date, l_leave_start_date) * 8) +
                CASE
                    WHEN TIME(l_leave_end_time) <= '11:45:00' THEN 4
                    ELSE 8
                END
        END
        ) AS leave_personal_count,
        (SELECT e_leave_personal FROM employees WHERE e_usercode = :userCode) AS total_personal
        FROM leave_list
        WHERE l_leave_id = 1
        AND l_usercode = :userCode
        AND NOT (TIME(l_leave_start_time) >= '11:45:00' AND TIME(l_leave_end_time) <= '12:45:00')
        AND YEAR(l_create_datetime) = :selectedYear
        AND l_leave_status = 0";

    $stmt_leave_personal = $conn->prepare($sql_leave_personal);
    $stmt_leave_personal->bindParam(':userCode', $userCode);
    $stmt_leave_personal->bindParam(':selectedYear', $selectedYear, PDO::PARAM_INT);
    $stmt_leave_personal->execute();
    $result_leave_personal = $stmt_leave_personal->fetch(PDO::FETCH_ASSOC);

    if ($result_leave_personal) {
        $total_personal = $result_leave_personal['total_personal'] ?? 0;
        $leave_personal_count = $result_leave_personal['leave_personal_count'] ?? 0;

        // คำนวณวันและชั่วโมงจากผลรวม
        // ปัดเป็นชั่วโมงหากเกินค่าที่กำหนด
        $leave_personal_count = round($leave_personal_count * 2) / 2; // ปัดขึ้นเป็นครึ่งชั่วโมง
        $leave_personal_days = floor($leave_personal_count / 8);
        $leave_personal_hours_remain = floor($leave_personal_count % 8);
        $leave_personal_minutes_remain = ($leave_personal_count - floor($leave_personal_count)) * 60;

        $leave_personal_minutes_remain = round($leave_personal_minutes_remain / 30) * 30;

        if ($leave_personal_minutes_remain == 30) {
            $leave_personal_minutes_remain = 5;
        } else {
            $leave_personal_minutes_remain = 0;
        }

    } else {
        echo '<p>ไม่พบข้อมูล</p>';
    }

    // ----------------------------------------------------------------------------------------------
    // ลากิจไม่ได้รับค่าจ้าง
    $sql_leave_personal_no = "SELECT
    SUM(
        CASE
            WHEN DATEDIFF(l_leave_end_date, l_leave_start_date) = 0 THEN
                -- Leave on the same day
                CASE
                    WHEN TIME_TO_SEC(TIMEDIFF(l_leave_end_time, l_leave_start_time)) = 8 * 3600 + 40 * 60 THEN 8
                    WHEN TIME_TO_SEC(TIMEDIFF(l_leave_end_time, l_leave_start_time)) > 4 * 3600 THEN
                        ROUND((TIME_TO_SEC(TIMEDIFF(l_leave_end_time, l_leave_start_time)) - 1 * 3600) / 3600, 1)
                    ELSE
                        ROUND(TIME_TO_SEC(TIMEDIFF(l_leave_end_time, l_leave_start_time)) / 3600, 1)
                END
            ELSE
                -- Leave spanning multiple days
                (DATEDIFF(l_leave_end_date, l_leave_start_date) * 8) +
                CASE
                    WHEN TIME(l_leave_end_time) <= '11:45:00' THEN 4
                    ELSE 8
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
    } else {
        echo '<p>ไม่พบข้อมูล</p>';
    }
    // ----------------------------------------------------------------------------------------------
    // ลาป่วย
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
            -- กรณีลาในหลายวัน
            (DATEDIFF(l_leave_end_date, l_leave_start_date) * 8) +
            CASE
                WHEN TIME(l_leave_end_time) <= '11:45:00' THEN 4
                ELSE 8
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
    } else {
        echo '<p>ไม่พบข้อมูล</p>';
    }
    // ----------------------------------------------------------------------------------------------
    // ลาป่วยจากงาน
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
            -- กรณีลาในหลายวัน
            (DATEDIFF(l_leave_end_date, l_leave_start_date) * 8) +
            CASE
                WHEN TIME(l_leave_end_time) <= '11:45:00' THEN 4
                ELSE 8
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

    } else {
        echo '<p>ไม่พบข้อมูล</p>';
    }
    // ----------------------------------------------------------------------------------------------
    // ลาพักร้อน
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
            -- กรณีลาในหลายวัน
            (DATEDIFF(l_leave_end_date, l_leave_start_date) * 8) +
            CASE
                WHEN TIME(l_leave_end_time) <= '11:45:00' THEN 4
                ELSE 8
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

    } else {
        echo '<p>ไม่พบข้อมูล</p>';
    }
    // ----------------------------------------------------------------------------------------------
    // มาสาย
    $sql_late = "SELECT COUNT(l_list_id) AS late_count FROM leave_list WHERE l_leave_id = '7' AND l_usercode = '$userCode' AND Year(l_create_datetime) = '$selectedYear'";
    $result_late = $conn->query($sql_late)->fetch(PDO::FETCH_ASSOC);
    $late_count = $result_late['late_count'];

    // ----------------------------------------------------------------------------------------------
    // หยุดงาน
    $sql_absence_work = "SELECT COUNT(l_list_id) AS stop_work FROM leave_list WHERE l_leave_id = '6' AND YEAR(l_leave_start_date) = '$selectedYear'";
    $result_absence_work = $conn->query($sql_absence_work)->fetch(PDO::FETCH_ASSOC);
    $stop_work = $result_absence_work['stop_work'];

    // ----------------------------------------------------------------------------------------------
    // อื่น ๆ
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
                -- กรณีลาในหลายวัน
                (DATEDIFF(l_leave_end_date, l_leave_start_date) * 8) +
                CASE
                    WHEN TIME(l_leave_end_time) <= '11:45:00' THEN 4
                    ELSE 8
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

    } else {
        echo '<p>No data found</p>';
    }
    // ----------------------------------------------------------------------------------------------

    $total_personal_remaining_days = max($total_personal - $leave_personal_days, 0);

    echo '<tr class="text-center align-middle">';
    echo '<td>ลากิจได้รับค่าจ้าง</td>';
    echo '<td>' . $leave_personal_days . ' วัน ' . $leave_personal_hours_remain . ' ชั่วโมง ' . $leave_personal_minutes_remain . ' นาที</td>';
    echo '<td>' . $total_personal_remaining_days . ' วัน</td>';
    echo '</tr>';

    $total_personal_no_remaining_days = max($total_personal_no - $leave_personal_no_days, 0);

    echo '<tr class="text-center align-middle">';
    echo '<td>' . 'ลากิจไม่ได้รับค่าจ้าง' . '</td>';
    echo '<td>' . $leave_personal_no_days . ' วัน ' . $leave_personal_no_hours_remain . ' ชั่วโมง ' . $leave_personal_no_minutes_remain . ' นาที ' . '</td>';
    echo '<td>' . $total_personal_no_remaining_days . ' วัน</td>';
    echo '</tr>';

    $total_sick_remaining_days = max($total_sick - $leave_sick_days, 0);

    echo '<tr class="text-center align-middle">';
    echo '<td>' . 'ลาป่วย' . '</td>';
    echo '<td>' . $leave_sick_days . ' วัน ' . $leave_sick_hours_remain . ' ชั่วโมง ' . $leave_sick_minutes_remain . ' นาที ' . '</td>';
    echo '<td>' . $total_sick_remaining_days . ' วัน</td>';
    echo '</tr>';

    $total_sick_work_remaining_days = max($total_sick_work - $leave_sick_work_days, 0);

    echo '<tr class="text-center align-middle">';
    echo '<td>' . 'ลาป่วยจากงาน' . '</td>';
    echo '<td>' . $leave_sick_work_days . ' วัน ' . $leave_sick_work_hours_remain . ' ชั่วโมง ' . $leave_sick_work_minutes_remain . ' นาที ' . '</td>';
    echo '<td>' . $total_sick_work_remaining_days . ' วัน</td>';
    echo '<tr class="text-center align-middle">';

    $total_annual_remaining_days = max($total_annual - $leave_annual_days, 0);

    echo '<tr class="text-center align-middle">';
    echo '<td>' . 'ลาพักร้อน' . '</td>';
    echo '<td>' . $leave_annual_days . ' วัน ' . $leave_annual_hours_remain . ' ชั่วโมง ' . $leave_annual_minutes_remain . ' นาที' . '</td>';
    echo '<td>' . $total_annual_remaining_days . ' วัน</td>';
    echo '</tr>';

    $total_other_remaining_days = max($total_other - $other_days, 0);

    echo '<tr class="text-center align-middle">';
    echo '<td>' . 'มาสาย' . '</td>';
    echo '<td>' . $late_count . ' ครั้ง</td>';
    echo '<td>' . '-' . '</td>';
    echo '</tr>';

    echo '<tr class="text-center align-middle">';
    echo '<td>' . 'หยุดงาน' . '</td>';
    echo '<td>' . $stop_work . ' วัน</td>';
    echo '<td>' . '-' . '</td>';
    echo '</tr>';

    echo '<tr class="text-center align-middle">';
    echo '<td>' . 'อื่น ๆ' . '</td>';
    echo '<td>' . $other_days . ' วัน ' . $other_hours_remain . ' ชั่วโมง ' . $other_minutes_remain . ' นาที' . '</td>';
    echo '<td>' . $total_other_remaining_days . ' วัน</td>';
    echo '</tr>';

    $sum_day = $leave_personal_days + $leave_personal_no_days + $leave_sick_days + $leave_sick_work_days + $stop_work;
    $sum_hours = $leave_personal_hours_remain + $leave_personal_no_hours_remain + $leave_sick_hours_remain + $leave_sick_work_hours_remain;
    $sum_minutes = $leave_personal_minutes_remain + $leave_personal_no_minutes_remain + $leave_sick_minutes_remain + $leave_sick_work_minutes_remain;

    echo '<tr class="text-center align-middle">';
    if ($sum_day < 10) {
        echo '<td style="font-weight: bold;">' . 'รวมจำนวนวันลาทั้งหมด (ยกเว้นลาพักร้อน / อื่น ๆ)' . '</td>';
        echo '<td colspan="2" style="font-weight: bold;">' . $sum_day . ' วัน ' . $sum_hours . ' ชั่วโมง ' . $sum_minutes . ' นาที' . '</td>';
    } else if ($sum_day == 10) {
        echo '<div class="alert alert-primary" role="alert">';
        echo '<i class="fa-solid fa-circle-exclamation"></i>' . ' รวมจำนวนวันลาทั้งหมด ' . $sum_day . ' วัน (ยกเว้นลาพักร้อน / อื่น ๆ)';
        echo '</div>';
        echo '<td style="font-weight: bold;">' . 'รวมจำนวนวันลาทั้งหมด (ยกเว้นลาพักร้อน / อื่น ๆ)' . '</td>';
        echo '<td colspan="2" style="font-weight: bold;" class="text-primary">' . $sum_day . ' วัน ' . $sum_hours . ' ชั่วโมง ' . $sum_minutes . ' นาที' . '</td>';
    } else if ($sum_day == 11) {
        echo '<div class="alert alert-primary" role="alert">';
        echo '<i class="fa-solid fa-circle-exclamation"></i>' . ' รวมจำนวนวันลาทั้งหมด ' . $sum_day . ' วัน (ยกเว้นลาพักร้อน / อื่น ๆ)';
        echo '</div>';
        echo '<td style="font-weight: bold;">' . 'รวมจำนวนวันลาทั้งหมด (ยกเว้นลาพักร้อน / อื่น ๆ)' . '</td>';
        echo '<td colspan="2" style="font-weight: bold;" class="text-primary">' . $sum_day . ' วัน ' . $sum_hours . ' ชั่วโมง ' . $sum_minutes . ' นาที' . '</td>';
    } else if ($sum_day == 12) {
        echo '<div class="alert alert-warning" role="alert">';
        echo '<i class="fa-solid fa-circle-exclamation"></i>' . ' รวมจำนวนวันลาทั้งหมด ' . $sum_day . ' วัน (ยกเว้นลาพักร้อน / อื่น ๆ)';
        echo '</div>';
        echo '<td style="font-weight: bold;">' . 'รวมจำนวนวันลาทั้งหมด (ยกเว้นลาพักร้อน / อื่น ๆ)' . '</td>';
        echo '<td colspan="2" style="font-weight: bold;" class="text-warning">' . $sum_day . ' วัน ' . $sum_hours . ' ชั่วโมง ' . $sum_minutes . ' นาที' . '</td>';
    } else if ($sum_day == 13) {
        echo '<div class="alert alert-danger" role="alert">';
        echo '<i class="fa-solid fa-circle-exclamation"></i>' . ' รวมจำนวนวันลาทั้งหมด ' . $sum_day . ' วัน (ยกเว้นลาพักร้อน / อื่น ๆ)';
        echo '</div>';
        echo '<td style="font-weight: bold;">' . 'รวมจำนวนวันลาทั้งหมด (ยกเว้นลาพักร้อน / อื่น ๆ)' . '</td>';
        echo '<td colspan="2" style="font-weight: bold;" class="text-danger">' . $sum_day . ' วัน ' . $sum_hours . ' ชั่วโมง ' . $sum_minutes . ' นาที' . '</td>';
    } else if ($sum_day >= 14) {
        echo '<div class="alert alert-danger" role="alert">';
        echo '<i class="fa-solid fa-circle-exclamation"></i>' . ' รวมจำนวนวันลาทั้งหมด ' . $sum_day . ' วัน (ยกเว้นลาพักร้อน / อื่น ๆ)';
        echo '</div>';
        echo '<td style="font-weight: bold;">' . 'รวมจำนวนวันลาทั้งหมด (ยกเว้นลาพักร้อน / อื่น ๆ)' . '</td>';
        echo '<td colspan="2" style="font-weight: bold;" class="text-danger">' . $sum_day . ' วัน ' . $sum_hours . ' ชั่วโมง ' . $sum_minutes . ' นาที' . '</td>';
    } else {
        // echo '<td colspan="2" style="font-weight: bold;" class="text-danger">' . $sum_day . ' วัน ' . $sum_hours . ' ชั่วโมง ' . $sum_minutes . ' นาที' . '</td>';
    }
}
// รวมวันลาที่ใช้ไปทั้งหมด
// $sum_all = $leave_personal_days + $leave_personal_no_days + $leave_sick_days + $leave_sick_work_days;

?>

            </tbody>
        </table>
    </div>
    <script src="../js/popper.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/bootstrap.bundle.js"></script>
    <script src="../js/bootstrap.bundle.min.js"></script>
</body>

</html>