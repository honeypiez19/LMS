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
    <?php require 'user_navbar.php'?>
    <nav class="navbar bg-body-tertiary" style="background-color: #072ac8; box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.3);
  border: none;">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-auto">
                    <i class="fa-solid fa-chart-line fa-2xl"></i>
                </div>
                <div class="col-auto">
                    <h3>สถิติการลา</h3>
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
                    <th>จำนวนวันลาคงเหลือ</th>
                </tr>
            </thead>
            <tbody>
                <?php
// ถ้าเลือกปี
if (isset($_POST['year'])) {
    $selectedYear = $_POST['year'];

    // ลากิจได้รับค่าจ้าง ----------------------------------------------------------------
    $sql_leave_personal = "SELECT
       SUM(
        DATEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))
        -
        (SELECT COUNT(1)
         FROM holiday
         WHERE h_start_date BETWEEN l_leave_start_date AND l_leave_end_date
         AND h_holiday_status = 'วันหยุด'
         AND h_status = 0)
    ) AS total_leave_days,
    SUM(HOUR(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))) % 24) -
    SUM(CASE
        WHEN HOUR(CONCAT(l_leave_start_date, ' ', l_leave_start_time)) < 12
             AND HOUR(CONCAT(l_leave_end_date, ' ', l_leave_end_time)) > 12
        THEN 1
        ELSE 0
    END) AS total_leave_hours,
    SUM(MINUTE(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time)))) AS total_leave_minutes,

    (SELECT e_leave_personal FROM employees WHERE e_usercode = :userCode) AS total_personal
FROM leave_list
WHERE l_leave_id = 1
AND l_usercode = :userCode
AND YEAR(l_create_datetime) = :selectedYear
AND l_leave_status = 0";

    $stmt_leave_personal = $conn->prepare($sql_leave_personal);
    $stmt_leave_personal->bindParam(':userCode', $userCode);
    $stmt_leave_personal->bindParam(':selectedYear', $selectedYear, PDO::PARAM_INT);
    $stmt_leave_personal->execute();
    $result_leave_personal = $stmt_leave_personal->fetch(PDO::FETCH_ASSOC);

    if ($result_leave_personal) {
        // Fetch total personal leave and leave durations
        $total_personal = $result_leave_personal['total_personal'] ?? 0;
        $leave_personal_days = $result_leave_personal['total_leave_days'] ?? 0;
        $leave_personal_hours = $result_leave_personal['total_leave_hours'] ?? 0;
        $leave_personal_minutes = $result_leave_personal['total_leave_minutes'] ?? 0;

        // Convert total hours to days (8 hours = 1 day)
        $leave_personal_days += floor($leave_personal_hours / 8);
        $leave_personal_hours = $leave_personal_hours % 8; // Remaining hours after converting to days

        if ($leave_personal_minutes >= 60) {
            $leave_personal_hours += floor($leave_personal_minutes / 60);
            $leave_personal_minutes = $leave_personal_minutes % 60;
        }
        // ปัดนาทีให้เป็น 30 นาที
        if ($leave_personal_minutes > 0 && $leave_personal_minutes <= 30) {
            $leave_personal_minutes = 30; // ปัดขึ้นเป็น 30 นาที
        } elseif ($leave_personal_minutes > 30) {
            $leave_personal_minutes = 0; // ปัดกลับเป็น 0 แล้วเพิ่มชั่วโมง
            $leave_personal_hours += 1;
        }
    } else {
        echo '<p>ไม่พบข้อมูล</p>';
    }
// ลากิจไม่ได้รับค่าจ้าง ----------------------------------------------------------------
    $sql_leave_personal_no = "SELECT
    SUM(
        DATEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))
        -
        (SELECT COUNT(1)
         FROM holiday
         WHERE h_start_date BETWEEN l_leave_start_date AND l_leave_end_date
         AND h_holiday_status = 'วันหยุด'
         AND h_status = 0)
    ) AS total_leave_days,
    SUM(HOUR(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))) % 24) -
    SUM(CASE
        WHEN HOUR(CONCAT(l_leave_start_date, ' ', l_leave_start_time)) < 12
             AND HOUR(CONCAT(l_leave_end_date, ' ', l_leave_end_time)) > 12
        THEN 1
        ELSE 0
    END) AS total_leave_hours,
    SUM(MINUTE(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time)))) AS total_leave_minutes,

    (SELECT e_leave_personal_no FROM employees WHERE e_usercode = :userCode) AS total_personal_no
FROM leave_list
WHERE l_leave_id = 2
AND l_usercode = :userCode
AND YEAR(l_create_datetime) = :selectedYear
AND l_leave_status = 0";

    $stmt_leave_personal_no = $conn->prepare($sql_leave_personal_no);
    $stmt_leave_personal_no->bindParam(':userCode', $userCode);
    $stmt_leave_personal_no->bindParam(':selectedYear', $selectedYear, PDO::PARAM_INT);
    $stmt_leave_personal_no->execute();
    $result_leave_personal_no = $stmt_leave_personal_no->fetch(PDO::FETCH_ASSOC);

    if ($result_leave_personal_no) {
        // Fetch total personal leave and leave durations
        $total_personal_no = $result_leave_personal_no['total_personal_no'] ?? 0;
        $leave_personal_no_days = $result_leave_personal_no['total_leave_days'] ?? 0;
        $leave_personal_no_hours = $result_leave_personal_no['total_leave_hours'] ?? 0;
        $leave_personal_no_minutes = $result_leave_personal_no['total_leave_minutes'] ?? 0;

        // Convert total hours to days (8 hours = 1 day)
        $leave_personal_no_days += floor($leave_personal_no_hours / 8);
        $leave_personal_no_hours = $leave_personal_no_hours % 8; // Remaining hours after converting to days

        if ($leave_personal_no_minutes >= 60) {
            $leave_personal_no_hours += floor($leave_personal_no_minutes / 60);
            $leave_personal_no_minutes = $leave_personal_no_minutes % 60;
        }
        // ปัดนาทีให้เป็น 30 นาที
        if ($leave_personal_no_minutes > 0 && $leave_personal_no_minutes <= 30) {
            $leave_personal_no_minutes = 30; // ปัดขึ้นเป็น 30 นาที
        } elseif ($leave_personal_no_minutes > 30) {
            $leave_personal_no_minutes = 0; // ปัดกลับเป็น 0 แล้วเพิ่มชั่วโมง
            $leave_personal_no_hours += 1;
        }
    } else {
        echo '<p>ไม่พบข้อมูล</p>';
    }
// ลาป่วย ----------------------------------------------------------------
    $sql_leave_sick = "SELECT
    SUM(
        DATEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))
        -
        (SELECT COUNT(1)
         FROM holiday
         WHERE h_start_date BETWEEN l_leave_start_date AND l_leave_end_date
         AND h_holiday_status = 'วันหยุด'
         AND h_status = 0)
    ) AS total_leave_days,
    SUM(HOUR(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))) % 24) -
    SUM(CASE
        WHEN HOUR(CONCAT(l_leave_start_date, ' ', l_leave_start_time)) < 12
             AND HOUR(CONCAT(l_leave_end_date, ' ', l_leave_end_time)) > 12
        THEN 1
        ELSE 0
    END) AS total_leave_hours,
    SUM(MINUTE(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time)))) AS total_leave_minutes,

    (SELECT e_leave_sick FROM employees WHERE e_usercode = :userCode) AS total_sick
FROM leave_list
WHERE l_leave_id = 3
AND l_usercode = :userCode
AND YEAR(l_create_datetime) = :selectedYear
AND l_leave_status = 0";

    $stmt_leave_sick = $conn->prepare($sql_leave_sick);
    $stmt_leave_sick->bindParam(':userCode', $userCode);
    $stmt_leave_sick->bindParam(':selectedYear', $selectedYear, PDO::PARAM_INT);
    $stmt_leave_sick->execute();
    $result_leave_sick = $stmt_leave_sick->fetch(PDO::FETCH_ASSOC);

    if ($result_leave_sick) {
        // Fetch total personal leave and leave durations
        $total_sick = $result_leave_sick['total_sick'] ?? 0;
        $leave_sick_days = $result_leave_sick['total_leave_days'] ?? 0;
        $leave_sick_hours = $result_leave_sick['total_leave_hours'] ?? 0;
        $leave_sick_minutes = $result_leave_sick['total_leave_minutes'] ?? 0;

        // Convert total hours to days (8 hours = 1 day)
        $leave_sick_days += floor($leave_sick_hours / 8);
        $leave_sick_hours = $leave_sick_hours % 8; // Remaining hours after converting to days

        if ($leave_sick_minutes >= 60) {
            $leave_sick_hours += floor($leave_sick_minutes / 60);
            $leave_sick_minutes = $leave_sick_minutes % 60;
        }

        if ($leave_sick_minutes > 0 && $leave_sick_minutes <= 30) {
            $leave_sick_minutes = 30; // ปัดขึ้นเป็น 30 นาที
        } elseif ($leave_sick_minutes > 30) {
            $leave_sick_minutes = 0; // ปัดกลับเป็น 0 แล้วเพิ่มชั่วโมง
            $leave_sick_hours += 1;
        }
    } else {
        echo '<p>ไม่พบข้อมูล</p>';
    }

// ลาป่วยจากงาน ----------------------------------------------------------------
    $sql_leave_sick_work = "SELECT
    SUM(
        DATEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))
        -
        (SELECT COUNT(1)
         FROM holiday
         WHERE h_start_date BETWEEN l_leave_start_date AND l_leave_end_date
         AND h_holiday_status = 'วันหยุด'
         AND h_status = 0)
    ) AS total_leave_days,
    SUM(HOUR(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))) % 24) -
    SUM(CASE
        WHEN HOUR(CONCAT(l_leave_start_date, ' ', l_leave_start_time)) < 12
             AND HOUR(CONCAT(l_leave_end_date, ' ', l_leave_end_time)) > 12
        THEN 1
        ELSE 0
    END) AS total_leave_hours,
    SUM(MINUTE(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time)))) AS total_leave_minutes,

(SELECT e_leave_sick_work FROM employees WHERE e_usercode = :userCode) AS total_leave_sick_work
FROM leave_list
WHERE l_leave_id = 4
AND l_usercode = :userCode
AND YEAR(l_create_datetime) = :selectedYear
AND l_leave_status = 0";

    $stmt_leave_sick_work = $conn->prepare($sql_leave_sick_work);
    $stmt_leave_sick_work->bindParam(':userCode', $userCode);
    $stmt_leave_sick_work->bindParam(':selectedYear', $selectedYear, PDO::PARAM_INT);
    $stmt_leave_sick_work->execute();
    $result_leave_sick_work = $stmt_leave_sick_work->fetch(PDO::FETCH_ASSOC);

    if ($result_leave_sick_work) {
        // Fetch total personal leave and leave durations
        $total_sick_work = $result_leave_sick_work['total_leave_sick_work'] ?? 0;
        $leave_sick_work_days = $result_leave_sick_work['total_leave_days'] ?? 0;
        $leave_sick_work_hours = $result_leave_sick_work['total_leave_hours'] ?? 0;
        $leave_sick_work_minutes = $result_leave_sick_work['total_leave_minutes'] ?? 0;

        // Convert total hours to days (8 hours = 1 day)
        $leave_sick_work_days += floor($leave_sick_work_hours / 8);
        $leave_sick_work_hours = $leave_sick_work_hours % 8; // Remaining hours after converting to days

        if ($leave_sick_work_minutes >= 60) {
            $leave_sick_work_hours += floor($leave_sick_work_minutes / 60);
            $leave_sick_work_minutes = $leave_sick_work_minutes % 60;
        }

        if ($leave_sick_work_minutes > 0 && $leave_sick_work_minutes <= 30) {
            $leave_sick_work_minutes = 30; // ปัดขึ้นเป็น 30 นาที
        } elseif ($leave_sick_minutes > 30) {
            $leave_sick_work_minutes = 0; // ปัดกลับเป็น 0 แล้วเพิ่มชั่วโมง
            $leave_sick_work_hours += 1;
        }

    } else {
        echo '<p>ไม่พบข้อมูล</p>';
    }

// ลาพักร้อน ----------------------------------------------------------------
    $sql_leave_annual = "SELECT
    SUM(
        DATEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))
        -
        (SELECT COUNT(1)
         FROM holiday
         WHERE h_start_date BETWEEN l_leave_start_date AND l_leave_end_date
         AND h_holiday_status = 'วันหยุด'
         AND h_status = 0)
    ) AS total_leave_days,
    SUM(HOUR(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))) % 24) -
    SUM(CASE
        WHEN HOUR(CONCAT(l_leave_start_date, ' ', l_leave_start_time)) < 12
             AND HOUR(CONCAT(l_leave_end_date, ' ', l_leave_end_time)) > 12
        THEN 1
        ELSE 0
    END) AS total_leave_hours,
    SUM(MINUTE(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time)))) AS total_leave_minutes,

(SELECT e_leave_annual FROM employees WHERE e_usercode = :userCode) AS total_annual
FROM leave_list
WHERE l_leave_id = 5
AND l_usercode = :userCode
AND YEAR(l_create_datetime) = :selectedYear
AND l_leave_status = 0";

    $stmt_leave_annual = $conn->prepare($sql_leave_annual);
    $stmt_leave_annual->bindParam(':userCode', $userCode);
    $stmt_leave_annual->bindParam(':selectedYear', $selectedYear, PDO::PARAM_INT);
    $stmt_leave_annual->execute();
    $result_leave_annual = $stmt_leave_annual->fetch(PDO::FETCH_ASSOC);

    if ($result_leave_annual) {
        $total_annual = $result_leave_annual['total_annual'] ?? 0;
        $leave_annual_days = $result_leave_annual['total_leave_days'] ?? 0;
        $leave_annual_hours = $result_leave_annual['total_leave_hours'] ?? 0;
        $leave_annual_minutes = $result_leave_annual['total_leave_minutes'] ?? 0;

        // Convert total hours to days (8 hours = 1 day)
        $leave_annual_days += floor($leave_annual_hours / 8);
        $leave_annual_hours = $leave_annual_hours % 8; // Remaining hours after converting to days

        if ($leave_annual_minutes >= 60) {
            $leave_annual_hours += floor($leave_annual_minutes / 60);
            $leave_annual_minutes = $leave_annual_minutes % 60;
        }

        if ($leave_annual_minutes > 0 && $leave_annual_minutes <= 30) {
            $leave_annual_minutes = 30; // ปัดขึ้นเป็น 30 นาที
        } elseif ($leave_annual_minutes > 30) {
            $leave_annual_minutes = 0; // ปัดกลับเป็น 0 แล้วเพิ่มชั่วโมง
            $leave_annual_hours += 1;
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
        DATEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))
        -
        (SELECT COUNT(1)
         FROM holiday
         WHERE h_start_date BETWEEN l_leave_start_date AND l_leave_end_date
         AND h_holiday_status = 'วันหยุด'
         AND h_status = 0)
    ) AS total_leave_days,
    SUM(HOUR(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))) % 24) -
    SUM(CASE
        WHEN HOUR(CONCAT(l_leave_start_date, ' ', l_leave_start_time)) < 12
             AND HOUR(CONCAT(l_leave_end_date, ' ', l_leave_end_time)) > 12
        THEN 1
        ELSE 0
    END) AS total_leave_hours,
    SUM(MINUTE(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time)))) AS total_leave_minutes,

    (SELECT e_other FROM employees WHERE e_usercode = :userCode) AS total_other
FROM leave_list
WHERE l_leave_id = 8
AND l_usercode = :userCode
AND YEAR(l_create_datetime) = :selectedYear
AND l_leave_status = 0";

    $stmt_other = $conn->prepare($sql_other);
    $stmt_other->bindParam(':userCode', $userCode);
    $stmt_other->bindParam(':selectedYear', $selectedYear, PDO::PARAM_INT);
    $stmt_other->execute();
    $result_other = $stmt_other->fetch(PDO::FETCH_ASSOC);

    if ($result_other) {
        // Fetch total personal leave and leave durations
        $total_other = $result_other['total_other'] ?? 0;
        $other_days = $result_other['total_leave_days'] ?? 0;
        $other_hours = $result_other['total_leave_hours'] ?? 0;
        $other_minutes = $result_other['total_leave_minutes'] ?? 0;

        // Convert total hours to days (8 hours = 1 day)
        $other_days += floor($other_hours / 8);
        $other_hours = $other_hours % 8; // Remaining hours after converting to days

        if ($other_minutes >= 60) {
            $other_hours += floor($other_minutes / 60);
            $other_minutes = $other_minutes % 60;
        }

        if ($other_minutes > 0 && $other_minutes <= 30) {
            $other_minutes = 30; // ปัดขึ้นเป็น 30 นาที
        } elseif ($other_minutes > 30) {
            $other_minutes = 0; // ปัดกลับเป็น 0 แล้วเพิ่มชั่วโมง
            $other_hours += 1;
        }
    } else {
        echo '<p>No data found</p>';
    }
    // ----------------------------------------------------------------------------------------------

    $total_personal_remaining_days = max($total_personal - $leave_personal_days, 0);

    echo '<tr class="text-center align-middle">';
    echo '<td>ลากิจได้รับค่าจ้าง</td>';
    echo '<td>' . $leave_personal_days . ' วัน ' . $leave_personal_hours . ' ชั่วโมง ' . $leave_personal_minutes . ' นาที</td>';
    echo '<td>' . $total_personal_remaining_days . ' วัน</td>';
    echo '</tr>';

    $total_personal_no_remaining_days = max($total_personal_no - $leave_personal_no_days, 0);

    echo '<tr class="text-center align-middle">';
    echo '<td>' . 'ลากิจไม่ได้รับค่าจ้าง' . '</td>';
    echo '<td>' . $leave_personal_no_days . ' วัน ' . $leave_personal_no_hours . ' ชั่วโมง ' . $leave_personal_no_minutes . ' นาที ' . '</td>';
    echo '<td>' . $total_personal_no_remaining_days . ' วัน</td>';
    echo '</tr>';

    $total_sick_remaining_days = max($total_sick - $leave_sick_days, 0);

    echo '<tr class="text-center align-middle">';
    echo '<td>' . 'ลาป่วย' . '</td>';
    echo '<td>' . $leave_sick_days . ' วัน ' . $leave_sick_hours . ' ชั่วโมง ' . $leave_sick_minutes . ' นาที ' . '</td>';
    echo '<td>' . $total_sick_remaining_days . ' วัน</td>';
    echo '</tr>';

    $total_sick_work_remaining_days = max($total_sick_work - $leave_sick_work_days, 0);

    echo '<tr class="text-center align-middle">';
    echo '<td>' . 'ลาป่วยจากงาน' . '</td>';
    echo '<td>' . $leave_sick_work_days . ' วัน ' . $leave_sick_work_hours . ' ชั่วโมง ' . $leave_sick_work_minutes . ' นาที ' . '</td>';
    echo '<td>' . $total_sick_work_remaining_days . ' วัน</td>';
    echo '<tr class="text-center align-middle">';

    $total_annual_remaining_days = max($total_annual - $leave_annual_days, 0);

    echo '<tr class="text-center align-middle">';
    echo '<td>' . 'ลาพักร้อน' . '</td>';
    echo '<td>' . $leave_annual_days . ' วัน ' . $leave_annual_hours . ' ชั่วโมง ' . $leave_annual_minutes . ' นาที' . '</td>';
    echo '<td>' . $total_annual_remaining_days . ' วัน</td>';
    echo '</tr>';

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

    $total_other_remaining_days = max($total_other - $other_days, 0);

    echo '<tr class="text-center align-middle">';
    echo '<td>' . 'อื่น ๆ' . '</td>';
    echo '<td>' . $other_days . ' วัน ' . $other_hours . ' ชั่วโมง ' . $other_minutes . ' นาที' . '</td>';
    echo '<td>' . $total_other_remaining_days . ' วัน</td>';
    echo '</tr>';

    $sum_day = $leave_personal_days + $leave_personal_no_days + $leave_sick_days + $leave_sick_work_days + $stop_work;
    $sum_hours = $leave_personal_hours + $leave_personal_no_hours + $leave_sick_hours + $leave_sick_work_hours;
    $sum_minutes = $leave_personal_minutes + $leave_personal_no_minutes + $leave_sick_minutes + $leave_sick_work_minutes;

    if ($sum_minutes > 0 && $sum_minutes <= 30) {
        $sum_minutes = 30; // ปัดขึ้นเป็น 30 นาที
    } elseif ($sum_minutes > 30) {
        $sum_minutes = 0; // ปัดกลับเป็น 0 แล้วเพิ่มชั่วโมง
        $sum_hours += 1;
    }

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

    // ลากิจได้รับค่าจ้าง ----------------------------------------------------------------
    $sql_leave_personal = "SELECT
        SUM(
         DATEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))
         -
         (SELECT COUNT(1)
          FROM holiday
          WHERE h_start_date BETWEEN l_leave_start_date AND l_leave_end_date
          AND h_holiday_status = 'วันหยุด'
          AND h_status = 0)
     ) AS total_leave_days,
     SUM(HOUR(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))) % 24) -
     SUM(CASE
         WHEN HOUR(CONCAT(l_leave_start_date, ' ', l_leave_start_time)) < 12
              AND HOUR(CONCAT(l_leave_end_date, ' ', l_leave_end_time)) > 12
         THEN 1
         ELSE 0
     END) AS total_leave_hours,
     SUM(MINUTE(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time)))) AS total_leave_minutes,

     (SELECT e_leave_personal FROM employees WHERE e_usercode = :userCode) AS total_personal
 FROM leave_list
 WHERE l_leave_id = 1
 AND l_usercode = :userCode
 AND YEAR(l_create_datetime) = :selectedYear
 AND l_leave_status = 0";

    $stmt_leave_personal = $conn->prepare($sql_leave_personal);
    $stmt_leave_personal->bindParam(':userCode', $userCode);
    $stmt_leave_personal->bindParam(':selectedYear', $selectedYear, PDO::PARAM_INT);
    $stmt_leave_personal->execute();
    $result_leave_personal = $stmt_leave_personal->fetch(PDO::FETCH_ASSOC);

    if ($result_leave_personal) {
        // Fetch total personal leave and leave durations
        $total_personal = $result_leave_personal['total_personal'] ?? 0;
        $leave_personal_days = $result_leave_personal['total_leave_days'] ?? 0;
        $leave_personal_hours = $result_leave_personal['total_leave_hours'] ?? 0;
        $leave_personal_minutes = $result_leave_personal['total_leave_minutes'] ?? 0;

        // Convert total hours to days (8 hours = 1 day)
        $leave_personal_days += floor($leave_personal_hours / 8);
        $leave_personal_hours = $leave_personal_hours % 8; // Remaining hours after converting to days

        if ($leave_personal_minutes >= 60) {
            $leave_personal_hours += floor($leave_personal_minutes / 60);
            $leave_personal_minutes = $leave_personal_minutes % 60;
        }
        // ปัดนาทีให้เป็น 30 นาที
        if ($leave_personal_minutes > 0 && $leave_personal_minutes <= 30) {
            $leave_personal_minutes = 30; // ปัดขึ้นเป็น 30 นาที
        } elseif ($leave_personal_minutes > 30) {
            $leave_personal_minutes = 0; // ปัดกลับเป็น 0 แล้วเพิ่มชั่วโมง
            $leave_personal_hours += 1;
        }
    } else {
        echo '<p>ไม่พบข้อมูล</p>';
    }
    // ลากิจไม่ได้รับค่าจ้าง ----------------------------------------------------------------
    $sql_leave_personal_no = "SELECT
     SUM(
         DATEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))
         -
         (SELECT COUNT(1)
          FROM holiday
          WHERE h_start_date BETWEEN l_leave_start_date AND l_leave_end_date
          AND h_holiday_status = 'วันหยุด'
          AND h_status = 0)
     ) AS total_leave_days,
     SUM(HOUR(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))) % 24) -
     SUM(CASE
         WHEN HOUR(CONCAT(l_leave_start_date, ' ', l_leave_start_time)) < 12
              AND HOUR(CONCAT(l_leave_end_date, ' ', l_leave_end_time)) > 12
         THEN 1
         ELSE 0
     END) AS total_leave_hours,
     SUM(MINUTE(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time)))) AS total_leave_minutes,

     (SELECT e_leave_personal_no FROM employees WHERE e_usercode = :userCode) AS total_personal_no
 FROM leave_list
 WHERE l_leave_id = 2
 AND l_usercode = :userCode
 AND YEAR(l_create_datetime) = :selectedYear
 AND l_leave_status = 0";

    $stmt_leave_personal_no = $conn->prepare($sql_leave_personal_no);
    $stmt_leave_personal_no->bindParam(':userCode', $userCode);
    $stmt_leave_personal_no->bindParam(':selectedYear', $selectedYear, PDO::PARAM_INT);
    $stmt_leave_personal_no->execute();
    $result_leave_personal_no = $stmt_leave_personal_no->fetch(PDO::FETCH_ASSOC);

    if ($result_leave_personal_no) {
        // Fetch total personal leave and leave durations
        $total_personal_no = $result_leave_personal_no['total_personal_no'] ?? 0;
        $leave_personal_no_days = $result_leave_personal_no['total_leave_days'] ?? 0;
        $leave_personal_no_hours = $result_leave_personal_no['total_leave_hours'] ?? 0;
        $leave_personal_no_minutes = $result_leave_personal_no['total_leave_minutes'] ?? 0;

        // Convert total hours to days (8 hours = 1 day)
        $leave_personal_no_days += floor($leave_personal_no_hours / 8);
        $leave_personal_no_hours = $leave_personal_no_hours % 8; // Remaining hours after converting to days

        if ($leave_personal_no_minutes >= 60) {
            $leave_personal_no_hours += floor($leave_personal_no_minutes / 60);
            $leave_personal_no_minutes = $leave_personal_no_minutes % 60;
        }
        // ปัดนาทีให้เป็น 30 นาที
        if ($leave_personal_no_minutes > 0 && $leave_personal_no_minutes <= 30) {
            $leave_personal_no_minutes = 30; // ปัดขึ้นเป็น 30 นาที
        } elseif ($leave_personal_no_minutes > 30) {
            $leave_personal_no_minutes = 0; // ปัดกลับเป็น 0 แล้วเพิ่มชั่วโมง
            $leave_personal_no_hours += 1;
        }
    } else {
        echo '<p>ไม่พบข้อมูล</p>';
    }
    // ลาป่วย ----------------------------------------------------------------
    $sql_leave_sick = "SELECT
     SUM(
         DATEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))
         -
         (SELECT COUNT(1)
          FROM holiday
          WHERE h_start_date BETWEEN l_leave_start_date AND l_leave_end_date
          AND h_holiday_status = 'วันหยุด'
          AND h_status = 0)
     ) AS total_leave_days,
     SUM(HOUR(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))) % 24) -
     SUM(CASE
         WHEN HOUR(CONCAT(l_leave_start_date, ' ', l_leave_start_time)) < 12
              AND HOUR(CONCAT(l_leave_end_date, ' ', l_leave_end_time)) > 12
         THEN 1
         ELSE 0
     END) AS total_leave_hours,
     SUM(MINUTE(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time)))) AS total_leave_minutes,

     (SELECT e_leave_sick FROM employees WHERE e_usercode = :userCode) AS total_sick
 FROM leave_list
 WHERE l_leave_id = 3
 AND l_usercode = :userCode
 AND YEAR(l_create_datetime) = :selectedYear
 AND l_leave_status = 0";

    $stmt_leave_sick = $conn->prepare($sql_leave_sick);
    $stmt_leave_sick->bindParam(':userCode', $userCode);
    $stmt_leave_sick->bindParam(':selectedYear', $selectedYear, PDO::PARAM_INT);
    $stmt_leave_sick->execute();
    $result_leave_sick = $stmt_leave_sick->fetch(PDO::FETCH_ASSOC);

    if ($result_leave_sick) {
        // Fetch total personal leave and leave durations
        $total_sick = $result_leave_sick['total_sick'] ?? 0;
        $leave_sick_days = $result_leave_sick['total_leave_days'] ?? 0;
        $leave_sick_hours = $result_leave_sick['total_leave_hours'] ?? 0;
        $leave_sick_minutes = $result_leave_sick['total_leave_minutes'] ?? 0;

        // Convert total hours to days (8 hours = 1 day)
        $leave_sick_days += floor($leave_sick_hours / 8);
        $leave_sick_hours = $leave_sick_hours % 8; // Remaining hours after converting to days

        if ($leave_sick_minutes >= 60) {
            $leave_sick_hours += floor($leave_sick_minutes / 60);
            $leave_sick_minutes = $leave_sick_minutes % 60;
        }

        if ($leave_sick_minutes > 0 && $leave_sick_minutes <= 30) {
            $leave_sick_minutes = 30; // ปัดขึ้นเป็น 30 นาที
        } elseif ($leave_sick_minutes > 30) {
            $leave_sick_minutes = 0; // ปัดกลับเป็น 0 แล้วเพิ่มชั่วโมง
            $leave_sick_hours += 1;
        }
    } else {
        echo '<p>ไม่พบข้อมูล</p>';
    }

    // ลาป่วยจากงาน ----------------------------------------------------------------
    $sql_leave_sick_work = "SELECT
     SUM(
         DATEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))
         -
         (SELECT COUNT(1)
          FROM holiday
          WHERE h_start_date BETWEEN l_leave_start_date AND l_leave_end_date
          AND h_holiday_status = 'วันหยุด'
          AND h_status = 0)
     ) AS total_leave_days,
     SUM(HOUR(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))) % 24) -
     SUM(CASE
         WHEN HOUR(CONCAT(l_leave_start_date, ' ', l_leave_start_time)) < 12
              AND HOUR(CONCAT(l_leave_end_date, ' ', l_leave_end_time)) > 12
         THEN 1
         ELSE 0
     END) AS total_leave_hours,
     SUM(MINUTE(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time)))) AS total_leave_minutes,

 (SELECT e_leave_sick_work FROM employees WHERE e_usercode = :userCode) AS total_leave_sick_work
 FROM leave_list
 WHERE l_leave_id = 4
 AND l_usercode = :userCode
 AND YEAR(l_create_datetime) = :selectedYear
 AND l_leave_status = 0";

    $stmt_leave_sick_work = $conn->prepare($sql_leave_sick_work);
    $stmt_leave_sick_work->bindParam(':userCode', $userCode);
    $stmt_leave_sick_work->bindParam(':selectedYear', $selectedYear, PDO::PARAM_INT);
    $stmt_leave_sick_work->execute();
    $result_leave_sick_work = $stmt_leave_sick_work->fetch(PDO::FETCH_ASSOC);

    if ($result_leave_sick_work) {
        // Fetch total personal leave and leave durations
        $total_sick_work = $result_leave_sick_work['total_leave_sick_work'] ?? 0;
        $leave_sick_work_days = $result_leave_sick_work['total_leave_days'] ?? 0;
        $leave_sick_work_hours = $result_leave_sick_work['total_leave_hours'] ?? 0;
        $leave_sick_work_minutes = $result_leave_sick_work['total_leave_minutes'] ?? 0;

        // Convert total hours to days (8 hours = 1 day)
        $leave_sick_work_days += floor($leave_sick_work_hours / 8);
        $leave_sick_work_hours = $leave_sick_work_hours % 8; // Remaining hours after converting to days

        if ($leave_sick_work_minutes >= 60) {
            $leave_sick_work_hours += floor($leave_sick_work_minutes / 60);
            $leave_sick_work_minutes = $leave_sick_work_minutes % 60;
        }

        if ($leave_sick_work_minutes > 0 && $leave_sick_work_minutes <= 30) {
            $leave_sick_work_minutes = 30; // ปัดขึ้นเป็น 30 นาที
        } elseif ($leave_sick_minutes > 30) {
            $leave_sick_work_minutes = 0; // ปัดกลับเป็น 0 แล้วเพิ่มชั่วโมง
            $leave_sick_work_hours += 1;
        }

    } else {
        echo '<p>ไม่พบข้อมูล</p>';
    }

    // ลาพักร้อน ----------------------------------------------------------------
    $sql_leave_annual = "SELECT
     SUM(
         DATEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))
         -
         (SELECT COUNT(1)
          FROM holiday
          WHERE h_start_date BETWEEN l_leave_start_date AND l_leave_end_date
          AND h_holiday_status = 'วันหยุด'
          AND h_status = 0)
     ) AS total_leave_days,
     SUM(HOUR(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))) % 24) -
     SUM(CASE
         WHEN HOUR(CONCAT(l_leave_start_date, ' ', l_leave_start_time)) < 12
              AND HOUR(CONCAT(l_leave_end_date, ' ', l_leave_end_time)) > 12
         THEN 1
         ELSE 0
     END) AS total_leave_hours,
     SUM(MINUTE(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time)))) AS total_leave_minutes,

 (SELECT e_leave_annual FROM employees WHERE e_usercode = :userCode) AS total_annual
 FROM leave_list
 WHERE l_leave_id = 5
 AND l_usercode = :userCode
 AND YEAR(l_create_datetime) = :selectedYear
 AND l_leave_status = 0";

    $stmt_leave_annual = $conn->prepare($sql_leave_annual);
    $stmt_leave_annual->bindParam(':userCode', $userCode);
    $stmt_leave_annual->bindParam(':selectedYear', $selectedYear, PDO::PARAM_INT);
    $stmt_leave_annual->execute();
    $result_leave_annual = $stmt_leave_annual->fetch(PDO::FETCH_ASSOC);

    if ($result_leave_annual) {
        $total_annual = $result_leave_annual['total_annual'] ?? 0;
        $leave_annual_days = $result_leave_annual['total_leave_days'] ?? 0;
        $leave_annual_hours = $result_leave_annual['total_leave_hours'] ?? 0;
        $leave_annual_minutes = $result_leave_annual['total_leave_minutes'] ?? 0;

        // Convert total hours to days (8 hours = 1 day)
        $leave_annual_days += floor($leave_annual_hours / 8);
        $leave_annual_hours = $leave_annual_hours % 8; // Remaining hours after converting to days

        if ($leave_annual_minutes >= 60) {
            $leave_annual_hours += floor($leave_annual_minutes / 60);
            $leave_annual_minutes = $leave_annual_minutes % 60;
        }

        if ($leave_annual_minutes > 0 && $leave_annual_minutes <= 30) {
            $leave_annual_minutes = 30; // ปัดขึ้นเป็น 30 นาที
        } elseif ($leave_annual_minutes > 30) {
            $leave_annual_minutes = 0; // ปัดกลับเป็น 0 แล้วเพิ่มชั่วโมง
            $leave_annual_hours += 1;
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
         DATEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))
         -
         (SELECT COUNT(1)
          FROM holiday
          WHERE h_start_date BETWEEN l_leave_start_date AND l_leave_end_date
          AND h_holiday_status = 'วันหยุด'
          AND h_status = 0)
     ) AS total_leave_days,
     SUM(HOUR(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))) % 24) -
     SUM(CASE
         WHEN HOUR(CONCAT(l_leave_start_date, ' ', l_leave_start_time)) < 12
              AND HOUR(CONCAT(l_leave_end_date, ' ', l_leave_end_time)) > 12
         THEN 1
         ELSE 0
     END) AS total_leave_hours,
     SUM(MINUTE(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time)))) AS total_leave_minutes,

     (SELECT e_other FROM employees WHERE e_usercode = :userCode) AS total_other
 FROM leave_list
 WHERE l_leave_id = 8
 AND l_usercode = :userCode
 AND YEAR(l_create_datetime) = :selectedYear
 AND l_leave_status = 0";

    $stmt_other = $conn->prepare($sql_other);
    $stmt_other->bindParam(':userCode', $userCode);
    $stmt_other->bindParam(':selectedYear', $selectedYear, PDO::PARAM_INT);
    $stmt_other->execute();
    $result_other = $stmt_other->fetch(PDO::FETCH_ASSOC);

    if ($result_other) {
        // Fetch total personal leave and leave durations
        $total_other = $result_other['total_other'] ?? 0;
        $other_days = $result_other['total_leave_days'] ?? 0;
        $other_hours = $result_other['total_leave_hours'] ?? 0;
        $other_minutes = $result_other['total_leave_minutes'] ?? 0;

        // Convert total hours to days (8 hours = 1 day)
        $other_days += floor($other_hours / 8);
        $other_hours = $other_hours % 8; // Remaining hours after converting to days

        if ($other_minutes >= 60) {
            $other_hours += floor($other_minutes / 60);
            $other_minutes = $other_minutes % 60;
        }

        if ($other_minutes > 0 && $other_minutes <= 30) {
            $other_minutes = 30; // ปัดขึ้นเป็น 30 นาที
        } elseif ($other_minutes > 30) {
            $other_minutes = 0; // ปัดกลับเป็น 0 แล้วเพิ่มชั่วโมง
            $other_hours += 1;
        }
    } else {
        echo '<p>No data found</p>';
    }
    // ----------------------------------------------------------------------------------------------

    $total_personal_remaining_days = max($total_personal - $leave_personal_days, 0);

    echo '<tr class="text-center align-middle">';
    echo '<td>ลากิจได้รับค่าจ้าง</td>';
    echo '<td>' . $leave_personal_days . ' วัน ' . $leave_personal_hours . ' ชั่วโมง ' . $leave_personal_minutes . ' นาที</td>';
    echo '<td>' . $total_personal_remaining_days . ' วัน</td>';
    echo '</tr>';

    $total_personal_no_remaining_days = max($total_personal_no - $leave_personal_no_days, 0);

    echo '<tr class="text-center align-middle">';
    echo '<td>' . 'ลากิจไม่ได้รับค่าจ้าง' . '</td>';
    echo '<td>' . $leave_personal_no_days . ' วัน ' . $leave_personal_no_hours . ' ชั่วโมง ' . $leave_personal_no_minutes . ' นาที ' . '</td>';
    echo '<td>' . $total_personal_no_remaining_days . ' วัน</td>';
    echo '</tr>';

    $total_sick_remaining_days = max($total_sick - $leave_sick_days, 0);

    echo '<tr class="text-center align-middle">';
    echo '<td>' . 'ลาป่วย' . '</td>';
    echo '<td>' . $leave_sick_days . ' วัน ' . $leave_sick_hours . ' ชั่วโมง ' . $leave_sick_minutes . ' นาที ' . '</td>';
    echo '<td>' . $total_sick_remaining_days . ' วัน</td>';
    echo '</tr>';

    $total_sick_work_remaining_days = max($total_sick_work - $leave_sick_work_days, 0);

    echo '<tr class="text-center align-middle">';
    echo '<td>' . 'ลาป่วยจากงาน' . '</td>';
    echo '<td>' . $leave_sick_work_days . ' วัน ' . $leave_sick_work_hours . ' ชั่วโมง ' . $leave_sick_work_minutes . ' นาที ' . '</td>';
    echo '<td>' . $total_sick_work_remaining_days . ' วัน</td>';
    echo '<tr class="text-center align-middle">';

    $total_annual_remaining_days = max($total_annual - $leave_annual_days, 0);

    echo '<tr class="text-center align-middle">';
    echo '<td>' . 'ลาพักร้อน' . '</td>';
    echo '<td>' . $leave_annual_days . ' วัน ' . $leave_annual_hours . ' ชั่วโมง ' . $leave_annual_minutes . ' นาที' . '</td>';
    echo '<td>' . $total_annual_remaining_days . ' วัน</td>';
    echo '</tr>';

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

    $total_other_remaining_days = max($total_other - $other_days, 0);

    echo '<tr class="text-center align-middle">';
    echo '<td>' . 'อื่น ๆ' . '</td>';
    echo '<td>' . $other_days . ' วัน ' . $other_hours . ' ชั่วโมง ' . $other_minutes . ' นาที' . '</td>';
    echo '<td>' . $total_other_remaining_days . ' วัน</td>';
    echo '</tr>';

    $sum_day = $leave_personal_days + $leave_personal_no_days + $leave_sick_days + $leave_sick_work_days + $stop_work;
    $sum_hours = $leave_personal_hours + $leave_personal_no_hours + $leave_sick_hours + $leave_sick_work_hours;
    $sum_minutes = $leave_personal_minutes + $leave_personal_no_minutes + $leave_sick_minutes + $leave_sick_work_minutes;

    if ($sum_minutes > 0 && $sum_minutes <= 30) {
        $sum_minutes = 30; // ปัดขึ้นเป็น 30 นาที
    } elseif ($sum_minutes > 30) {
        $sum_minutes = 0; // ปัดกลับเป็น 0 แล้วเพิ่มชั่วโมง
        $sum_hours += 1;
    }

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