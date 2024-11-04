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
    <title>ข้อมูลการลาพนักงาน</title>

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

    <script src="../js/html2canvas.js"></script>
    <script src="../js/html2canvas.min.js"></script>
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script> -->
    <script src="../js/jspdf.min.js"></script>


    <style>
    .my-table {
        /* width: 100%; */
        border-collapse: collapse;
    }

    .my-table th,
    .my-table td {
        border: 1px solid #ddd;
        padding: 8px;
    }

    .my-table tbody tr:hover {
        background-color: #f5f5f5;
    }
    </style>

</head>

<body>
    <?php require 'leader_navbar.php'?>

    <nav class="navbar bg-body-tertiary">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-auto">
                    <i class="fa-solid fa-folder-open fa-2xl"></i>
                </div>
                <div class="col-auto">
                    <h3>ข้อมูลการลาของพนักงาน</h3>
                </div>
            </div>
        </div>
    </nav>
    <div></div>
    <div class="mt-3 container">
        <div class="row">
            <div class="col-3">
                <label for="userCodeLabel" class="form-label">รหัสพนักงาน</label>
                <input type="text" class="form-control" id="codeSearch" list="codeList">
                <datalist id="codeList">
                    <?php
$sql = "SELECT * FROM employees WHERE e_level <> 'admin' AND e_status <> '1' AND e_sub_department = '$subDepart' AND e_usercode <> '$userCode'";
$result = $conn->query($sql);
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    echo '<option value="' . $row['e_usercode'] . '">';
}
?>
                </datalist>
            </div>
            <div class="col-3">
                <label for="nameLabel" class="form-label">ชื่อพนักงาน</label>
                <input type="text" class="form-control" id="nameSearch" list="nameList">
                <datalist id="nameList">
                    <?php
$sql = "SELECT * FROM employees WHERE e_level <> 'admin' AND e_status <> '1' AND e_sub_department = '$subDepart' AND e_usercode <> '$userCode'";
$result = $conn->query($sql);
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    echo '<option value="' . $row['e_name'] . '">';
}
?>
                </datalist>
            </div>
            <div class="col-3">
                <label for="depLabel" class="form-label">แผนก</label>
                <input type="text" class="form-control" id="depSearch" list="depList">
                <datalist id="depList">
                    <?php
$sql = "SELECT * FROM employees WHERE e_level <> 'admin' AND e_status <> '1' AND e_sub_department = '$subDepart' AND e_usercode <> '$userCode'";
$result = $conn->query($sql);
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    echo '<option value="' . $row['e_sub_department'] . '">';
}
?>
                </datalist>
            </div>
            <div class="col-3 d-flex align-items-end">
                <button class="btn btn-secondary button-shadow " onclick="resetFields()" type="button">รีเซ็ต</button>
                <!-- <button class="btn btn-primary" onclick="capture()">Capture</button> -->
                <button class="btn btn-primary button-shadow ms-2" id="generate-pdf" type="button">Export PDF</button>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <form class="mt-3 mb-3 row" method="post" id="yearForm">
            <label for="" class="mt-2 col-auto">เลือกปี</label>
            <div class="col-auto">
                <?php
        $selectedYear = date('Y'); // ปีปัจจุบัน
        if (isset($_POST['year'])) {
            $selectedYear = $_POST['year'];
            // กำหนดวันที่เริ่มต้นและสิ้นสุดสำหรับช่วง 12/2023 - 11/2024
            $startDate = date("Y-m-d", strtotime(($selectedYear - 1) . "-12-01"));
            $endDate = date("Y-m-d", strtotime($selectedYear . "-11-30"));
        }
        echo "<select class='form-select' name='year' id='selectYear'>";
        for ($i = -1; $i <= 2; $i++) {
            $year = date('Y', strtotime("last day of -$i year"));
            echo "<option value='$year'" . ($year == $selectedYear ? " selected" : "") . ">$year</option>";
        }
        echo "</select>";
        ?>
            </div>
        </form>
    </div>


    <!-- ตารางข้อมูลพนักงาน -->
    <div class="mt-3 container-fluid">
        <!-- <table class="mt-3 table  table-bordered" id="leaveEmpTable"> -->
        <table class="mt-3 my-table" id="leaveEmpTable">
            <thead>
                <tr class="text-center align-middle">
                    <th rowspan="3">ลำดับ</th>
                    <th rowspan="3">รหัสพนักงาน</th>
                    <th rowspan="3">ชื่อ - นามสกุล</th>
                    <th rowspan="3">แผนก</th>
                    <th rowspan="3">อายุงาน</th>
                    <th rowspan="3">ระดับ</th>
                    <th colspan="19" style="background-color: #DCDCDC;">ประเภทการลาและจำนวนวัน</th>
                    <th rowspan="3">รวมวันลาที่ใช้ (ยกเว้นพักร้อน)</th>
                </tr>
                <tr class="text-center align-middle">
                    <th colspan="3">ลากิจได้รับค่าจ้าง</th>
                    <th colspan="3">ลากิจไม่ได้รับค่าจ้าง</th>
                    <th colspan="3">ลาป่วย</th>
                    <th colspan="3">ลาป่วยจากงาน</th>
                    <th colspan="3">ลาพักร้อน</th>
                    <th colspan="3">อื่น ๆ (ระบุ)</th>
                    <th colspan="1" rowspan="3">หยุดงาน</th>
                </tr>
                <tr class="text-center align-middle">
                    <th>จำนวนวันที่ได้</th>
                    <th>ใช้ไป</th>
                    <th>คงเหลือ</th>
                    <th>จำนวนวันที่ได้</th>
                    <th>ใช้ไป</th>
                    <th>คงเหลือ</th>
                    <th>จำนวนวันที่ได้</th>
                    <th>ใช้ไป</th>
                    <th>คงเหลือ</th>
                    <th>จำนวนวันที่ได้</th>
                    <th>ใช้ไป</th>
                    <th>คงเหลือ</th>
                    <th>จำนวนวันที่ได้</th>
                    <th>ใช้ไป</th>
                    <th>คงเหลือ</th>
                    <th>จำนวนวันที่ได้</th>
                    <th>ใช้ไป</th>
                    <th>คงเหลือ</th>
                </tr>
            </thead>
            <!-- เนื้อหาของตาราง -->
            <tbody class="text-center my-table">
                <?php
                // $sql = "SELECT li.*, em.*
// FROM leave_list li
// INNER JOIN employees em ON li.l_usercode = em.e_usercode AND em.e_sub_department = '$subDepart'
// AND l_level = 'user'
// AND l_leave_id <> 6
// AND l_leave_id <> 7
// ORDER BY l_usercode DESC";

// ------------------------------------------------------------------------------
// เลือกปี
if (isset($_POST['year'])) {
    // $selectedYear = $_POST['year'];
    $approveStatus = ($depart == 'RD') ? 2 : (($depart == 'Office') ? 2 : ($depart == '' ? NULL : 2));

    $sql = "SELECT * FROM employees 
    WHERE e_usercode <> :userCode
    AND e_status <> '1' 
    AND (
            (e_sub_department = :subDepart)
            OR (e_sub_department2 = :subDepart2)
            OR (e_sub_department3 = :subDepart3)
            OR (e_sub_department4 = :subDepart4)
            OR (e_sub_department5 = :subDepart5)
        )
    ";
    
    // Prepare the statement
    $stmt = $conn->prepare($sql);
    
    // Bind parameters
    $stmt->bindParam(':userCode', $userCode);
    $stmt->bindParam(':subDepart', $subDepart);
    $stmt->bindParam(':subDepart2', $subDepart2);
    $stmt->bindParam(':subDepart3', $subDepart3);
    $stmt->bindParam(':subDepart4', $subDepart4);
    $stmt->bindParam(':subDepart5', $subDepart5);
    
    // Execute the query
    $stmt->execute();
    
    // Initialize row number
    $rowNumber = 1;
    
    // Fetch data and display it
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo '<tr>';
        echo '<td>' . $rowNumber . '</td>';
        echo '<td>' . $row['e_usercode'] . '</td>';
        echo '<td>' . $row['e_name'] . '</td>';
        echo '<td>' . $row['e_department'] . '</td>';
        echo '<td>' . $row['e_yearexp'] . '</td>';
        echo '<td>' . $row['e_level'] . '</td>';
    
        // $selectedYear = date('Y'); // ปีปัจจุบัน
        // // กำหนดวันที่เริ่มต้นและสิ้นสุดสำหรับช่วงปีปัจจุบัน
        // $startDate = date("Y-m-d", strtotime(($selectedYear - 1) . "-12-01"));
        // $endDate = date("Y-m-d", strtotime($selectedYear . "-11-30"));
    $sql_leave = "SELECT
      -- ลากิจได้รับค่าจ้าง
    SUM(
        CASE
            WHEN l_leave_id = '1' AND YEAR(l_leave_start_date) = :selectedYear
            THEN DATEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))
            - (SELECT COUNT(1)
               FROM holiday
               WHERE h_start_date BETWEEN l_leave_start_date AND l_leave_end_date
               AND h_holiday_status = 'วันหยุด'
               AND h_status = 0)
            ELSE 0
        END
    ) AS personal_leave_days,
    
    SUM(
        CASE
            WHEN l_leave_id = '1'
            THEN (HOUR(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))) % 24)
            - CASE
                  WHEN HOUR(CONCAT(l_leave_start_date, ' ', l_leave_start_time)) < 12
                       AND HOUR(CONCAT(l_leave_end_date, ' ', l_leave_end_time)) > 12
                  THEN 1
                  ELSE 0
              END
            ELSE 0
        END
    ) AS personal_leave_hours,
    
    SUM(
        CASE
            WHEN l_leave_id = '1'
            THEN MINUTE(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time)))
            ELSE 0
        END
    ) AS personal_leave_minutes,
    
    -- ลากิจไม่ได้รับค่าจ้าง
    SUM(
        CASE
            WHEN l_leave_id = '2'
            THEN DATEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))
            - (SELECT COUNT(1)
               FROM holiday
               WHERE h_start_date BETWEEN l_leave_start_date AND l_leave_end_date
               AND h_holiday_status = 'วันหยุด'
               AND h_status = 0)
            ELSE 0
        END
    ) AS personal_no_leave_days,
    
    SUM(
        CASE
            WHEN l_leave_id = '2'
            THEN (HOUR(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))) % 24)
            - CASE
                  WHEN HOUR(CONCAT(l_leave_start_date, ' ', l_leave_start_time)) < 12
                       AND HOUR(CONCAT(l_leave_end_date, ' ', l_leave_end_time)) > 12
                  THEN 1
                  ELSE 0
              END
            ELSE 0
        END
    ) AS personal_no_leave_hours,
    
    SUM(
        CASE
            WHEN l_leave_id = '2'
            THEN MINUTE(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time)))
            ELSE 0
        END
    ) AS personal_no_leave_minutes,
    
    -- ลาป่วย
  SUM(
        CASE
            WHEN l_leave_id = '3'
            THEN DATEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))
            - (SELECT COUNT(1)
               FROM holiday
               WHERE h_start_date BETWEEN l_leave_start_date AND l_leave_end_date
               AND h_holiday_status = 'วันหยุด'
               AND h_status = 0)
            ELSE 0
        END
    ) AS sick_leave_days,
    
    SUM(
        CASE
            WHEN l_leave_id = '3'
            THEN (HOUR(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))) % 24)
            - CASE
                  WHEN HOUR(CONCAT(l_leave_start_date, ' ', l_leave_start_time)) < 12
                       AND HOUR(CONCAT(l_leave_end_date, ' ', l_leave_end_time)) > 12
                  THEN 1
                  ELSE 0
              END
            ELSE 0
        END
    ) AS sick_leave_hours,
    
    SUM(
        CASE
            WHEN l_leave_id = '3'
            THEN MINUTE(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time)))
            ELSE 0
        END
    ) AS sick_leave_minutes,
    
    -- ลาป่วยจากงาน
    SUM(
        CASE
            WHEN l_leave_id = '4'
            THEN DATEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))
            - (SELECT COUNT(1)
               FROM holiday
               WHERE h_start_date BETWEEN l_leave_start_date AND l_leave_end_date
               AND h_holiday_status = 'วันหยุด'
               AND h_status = 0)
            ELSE 0
        END
    ) AS sick_work_leave_days,
    
    SUM(
        CASE
            WHEN l_leave_id = '4'
            THEN (HOUR(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))) % 24)
            - CASE
                  WHEN HOUR(CONCAT(l_leave_start_date, ' ', l_leave_start_time)) < 12
                       AND HOUR(CONCAT(l_leave_end_date, ' ', l_leave_end_time)) > 12
                  THEN 1
                  ELSE 0
              END
            ELSE 0
        END
    ) AS sick_work_leave_hours,
    
    SUM(
        CASE
            WHEN l_leave_id = '4'
            THEN MINUTE(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time)))
            ELSE 0
        END
    ) AS sick_work_leave_minutes,
    
    -- ลาพักร้อน
    SUM(
        CASE
            WHEN l_leave_id = '5'
            THEN DATEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))
            - (SELECT COUNT(1)
               FROM holiday
               WHERE h_start_date BETWEEN l_leave_start_date AND l_leave_end_date
               AND h_holiday_status = 'วันหยุด'
               AND h_status = 0)
            ELSE 0
        END
    ) AS annual_leave_days,
    
    SUM(
        CASE
            WHEN l_leave_id = '5'
            THEN (HOUR(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))) % 24)
            - CASE
                  WHEN HOUR(CONCAT(l_leave_start_date, ' ', l_leave_start_time)) < 12
                       AND HOUR(CONCAT(l_leave_end_date, ' ', l_leave_end_time)) > 12
                  THEN 1
                  ELSE 0
              END
            ELSE 0
        END
    ) AS annual_leave_hours,
    
    SUM(
        CASE
            WHEN l_leave_id = '5'
            THEN MINUTE(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time)))
            ELSE 0
        END
    ) AS annual_leave_minutes,
    
    -- หยุดงาน
      SUM(
            CASE
                WHEN l_leave_id = '6'
                THEN IFNULL(DATEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time)), 0)
                - (SELECT COUNT(1)
                   FROM holiday
                   WHERE h_start_date BETWEEN l_leave_start_date AND l_leave_end_date
                   AND h_holiday_status = 'วันหยุด'
                   AND h_status = 0)
                ELSE 0
            END
        ) AS stop_work_days,
    
        SUM(
            CASE
                WHEN l_leave_id = '6'
                THEN IFNULL(HOUR(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))) % 24, 0)
                - CASE
                      WHEN HOUR(CONCAT(l_leave_start_date, ' ', l_leave_start_time)) < 12
                           AND HOUR(CONCAT(l_leave_end_date, ' ', l_leave_end_time)) > 12
                      THEN 1
                      ELSE 0
                  END
                ELSE 0
            END
        ) AS stop_work_hours,
    
        SUM(
            CASE
                WHEN l_leave_id = '6'
                THEN IFNULL(MINUTE(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))), 0)
                ELSE 0
            END
        ) AS stop_work_minutes,
    
    -- อื่น ๆ
    SUM(
        CASE
            WHEN l_leave_id = '8'
            THEN DATEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))
            - (SELECT COUNT(1)
               FROM holiday
               WHERE h_start_date BETWEEN l_leave_start_date AND l_leave_end_date
               AND h_holiday_status = 'วันหยุด'
               AND h_status = 0)
            ELSE 0
        END
    ) AS other_leave_days,
    
    SUM(
        CASE
            WHEN l_leave_id = '8'
            THEN (HOUR(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))) % 24)
            - CASE
                  WHEN HOUR(CONCAT(l_leave_start_date, ' ', l_leave_start_time)) < 12
                       AND HOUR(CONCAT(l_leave_end_date, ' ', l_leave_end_time)) > 12
                  THEN 1
                  ELSE 0
              END
            ELSE 0
        END
    ) AS other_leave_hours,
    
    SUM(
        CASE
            WHEN l_leave_id = '8'
            THEN MINUTE(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time)))
            ELSE 0
        END
    ) AS other_leave_minutes,
    
        (SELECT e_leave_personal FROM employees WHERE e_usercode = :userCode) AS total_personal,
        (SELECT e_leave_personal_no FROM employees WHERE e_usercode = :userCode) AS total_personal_no,
        (SELECT e_leave_sick FROM employees WHERE e_usercode = :userCode) AS total_sick,
        (SELECT e_leave_sick_work FROM employees WHERE e_usercode = :userCode) AS total_leave_sick_work,
        (SELECT e_leave_annual FROM employees WHERE e_usercode = :userCode) AS total_annual,
        (SELECT e_other FROM employees WHERE e_usercode = :userCode) AS total_other,
    
        -- Count of late occurrences (leave_id = 7)
        SUM(
            CASE
                WHEN l_leave_id = '7' THEN 1
                ELSE 0
            END
        ) AS late_count
    
        FROM leave_list
        WHERE (
            (l_leave_id IN ('1', '2', '3', '4', '6', '8') AND YEAR(l_leave_end_date) BETWEEN :startDate AND :endDate)
            OR (l_leave_id = '5' AND YEAR(l_leave_end_date) = :selectedYear)
            )
        AND l_leave_status = 0
        AND l_usercode = :userCode
        AND l_approve_status = :approveStatus
        AND l_approve_status2 = 4
        ";
        // echo $startDate;
        // echo $endDate;
    
        $stmt_leave = $conn->prepare($sql_leave);
        $stmt_leave->bindParam(':userCode', $row['e_usercode']);
        $stmt_leave->bindParam(':selectedYear', $selectedYear);
        $stmt_leave->bindParam(':startDate', $startDate);
        $stmt_leave->bindParam(':endDate', $endDate);
        $stmt_leave->bindParam(':approveStatus', $approveStatus);

        $stmt_leave->execute();
        $stmt_leave->execute();
        $result_leave = $stmt_leave->fetch(PDO::FETCH_ASSOC);
    
        // ลากิจได้รับค่าจ้าง ----------------------------------------------------------------
        $total_personal = $result_leave['total_personal'] ?? 0;
        $leave_personal_days = $result_leave['personal_leave_days'] ?? 0;
        $leave_personal_hours = $result_leave['personal_leave_hours'] ?? 0;
        $leave_personal_minutes = $result_leave['personal_leave_minutes'] ?? 0;
    
    // Convert hours to days (8 hours = 1 day)
        $leave_personal_days += floor($leave_personal_hours / 8);
        $leave_personal_hours = $leave_personal_hours % 8; // Remaining hours after converting to days
    
        if ($leave_personal_minutes >= 60) {
            $leave_personal_hours += floor($leave_personal_minutes / 60);
            $leave_personal_minutes = $leave_personal_minutes % 60;
        }
    
    // Round minutes to 30 minutes
        if ($leave_personal_minutes > 0 && $leave_personal_minutes <= 30) {
            $leave_personal_minutes = 30;
        } elseif ($leave_personal_minutes > 30) {
            $leave_personal_minutes = 0;
            $leave_personal_hours += 1;
        }
        if ($leave_personal_minutes == 30) {
            $leave_personal_minutes = 5;
        }
    
    // ลากิจไม่ได้รับค่าจ้าง ----------------------------------------------------------------
        $total_personal_no = $result_leave['total_personal_no'] ?? 0;
        $leave_personal_no_days = $result_leave['personal_no_leave_days'] ?? 0;
        $leave_personal_no_hours = $result_leave['personal_no_leave_hours'] ?? 0;
        $leave_personal_no_minutes = $result_leave['personal_no_leave_minutes'] ?? 0;
    
    // Convert hours to days
        $leave_personal_no_days += floor($leave_personal_no_hours / 8);
        $leave_personal_no_hours = $leave_personal_no_hours % 8;
    
        if ($leave_personal_no_minutes >= 60) {
            $leave_personal_no_hours += floor($leave_personal_no_minutes / 60);
            $leave_personal_no_minutes = $leave_personal_no_minutes % 60;
        }
    
    // Round minutes to 30 minutes
        if ($leave_personal_no_minutes > 0 && $leave_personal_no_minutes <= 30) {
            $leave_personal_no_minutes = 30;
        } elseif ($leave_personal_no_minutes > 30) {
            $leave_personal_no_minutes = 0;
            $leave_personal_no_hours += 1;
        }
    
        if ($leave_personal_no_minutes == 30) {
            $leave_personal_no_minutes = 5;
        }
    
        // ลาป่วย ----------------------------------------------------------------
        // Fetch total personal leave and leave durations
        $total_sick = $result_leave['total_sick'] ?? 0;
        $leave_sick_days = $result_leave['sick_leave_days'] ?? 0;
        $leave_sick_hours = $result_leave['sick_leave_hours'] ?? 0;
        $leave_sick_minutes = $result_leave['sick_leave_minutes'] ?? 0;
    
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
    
        if ($leave_sick_minutes == 30) {
            $leave_sick_minutes = 5;
        }
    
        // ลาป่วยจากงาน ----------------------------------------------------------------
        // Fetch total personal leave and leave durations
        $total_sick_work = $result_leave['total_leave_sick_work'] ?? 0;
        $leave_sick_work_days = $result_leave['sick_work_leave_days'] ?? 0;
        $leave_sick_work_hours = $result_leave['sick_work_leave_hours'] ?? 0;
        $leave_sick_work_minutes = $result_leave['sick_work_leave_minutes'] ?? 0;
    
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
    
        if ($leave_sick_work_minutes == 30) {
            $leave_sick_work_minutes = 5;
        }
    
        // ลาพักร้อน ----------------------------------------------------------------
        // Fetch total personal leave and leave durations
        $total_annual = $result_leave['total_annual'] ?? 0;
        $leave_annual_days = $result_leave['annual_leave_days'] ?? 0;
        $leave_annual_hours = $result_leave['annual_leave_hours'] ?? 0;
        $leave_annual_minutes = $result_leave['annual_leave_minutes'] ?? 0;
    
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
    
        if ($leave_annual_minutes == 30) {
            $leave_annual_minutes = 5;
        }
        // หยุดงาน ----------------------------------------------------------------
        $stop_work_days = $result_leave['stop_work_days'] ?? 0;
        $stop_work_hours = $result_leave['stop_work_hours'] ?? 0;
        $stop_work_minutes = $result_leave['stop_work_minutes'] ?? 0;
    
        // Convert total hours to days (8 hours = 1 day)
        $stop_work_days += floor($stop_work_hours / 8);
        $stop_work_hours = $stop_work_hours % 8; // Remaining hours after converting to days
    
        // Convert minutes to hours if applicable
        if ($stop_work_minutes >= 60) {
            $stop_work_hours += floor($stop_work_minutes / 60);
            $stop_work_minutes = $stop_work_minutes % 60;
        }
    
        // Round minutes to either 30 or 0
        if ($stop_work_minutes > 0 && $stop_work_minutes <= 30) {
            $stop_work_minutes = 30; // ปัดขึ้นเป็น 30 นาที
        } elseif ($stop_work_minutes > 30) {
            $stop_work_minutes = 0; // ปัดกลับเป็น 0 แล้วเพิ่มชั่วโมง
            $stop_work_hours += 1;
        }
    
        // ปรับจำนวน minutes ให้เป็น 5 นาทีในกรณี 30 นาที
        if ($stop_work_minutes == 30) {
            $stop_work_minutes = 5;
        }
            
        // อื่น ๆ ----------------------------------------------------------------
        // Fetch total personal leave and leave durations
        $total_other = $result_leave['total_other'] ?? 0;
        $other_days = $result_leave['other_leave_days'] ?? 0;
        $other_hours = $result_leave['other_leave_hours'] ?? 0;
        $other_minutes = $result_leave['other_leave_minutes'] ?? 0;
    
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
    
        if ($other_minutes == 30) {
            $other_minutes = 5;
        }
        // Output the result
        echo '<td>' . $total_personal . '</td>';
        echo '<td>' . $leave_personal_days . '(' . $leave_personal_hours . '.' . $leave_personal_minutes . ')' . '</td>';
        echo '<td>' . ($total_personal - $leave_personal_days) . '</td>';
    
        echo '<td>' . $total_personal_no . '</td>';
        echo '<td>' . $leave_personal_no_days . '(' . $leave_personal_no_hours . '.' . $leave_personal_no_minutes . ')' . '</td>';
        echo '<td>' . ($total_personal_no - $leave_personal_no_days) . '</td>';
    
        echo '<td>' . $total_sick . '</td>';
        echo '<td>' . $leave_sick_days . '(' . $leave_sick_hours . '.' . $leave_sick_minutes . ')' . '</td>';
        echo '<td>' . ($total_sick - $leave_sick_days) . '</td>';
    
        echo '<td>' . $total_sick_work . '</td>';
        echo '<td>' . $leave_sick_work_days . '(' . $leave_sick_work_hours . '.' . $leave_sick_work_minutes . ')' . '</td>';
        echo '<td>' . ($total_sick_work - $leave_sick_work_days) . '</td>';
    
        echo '<td>' . $total_annual . '</td>';
        echo '<td>' . $leave_annual_days . '(' . $leave_annual_hours . '.' . $leave_annual_minutes . ')' . '</td>';
        echo '<td>' . ($total_annual - $leave_annual_days) . '</td>';
    
        echo '<td>' . $total_other . '</td>';
        echo '<td>' . $other_days . '(' . $other_hours . '.' . $other_minutes . ')' . '</td>';
        echo '<td>' . ($total_other - $other_days) . '</td>';
    
        echo '<td>' . $stop_work_days . '(' . $stop_work_hours . '.' . $stop_work_minutes . ')' . '</td>';
        
        // echo "Total Late Count: " . $result_leave['late_count'];
    
        // คำนวณจำนวนวัน, ชั่วโมง, และนาที
        $sum_day = $leave_personal_days + $leave_personal_no_days + $leave_sick_days + $leave_sick_work_days + $stop_work_days;
        $sum_hours = $leave_personal_hours + $leave_personal_no_hours + $leave_sick_hours + $leave_sick_work_hours + $stop_work_hours;
        $sum_minutes = $leave_personal_minutes + $leave_personal_no_minutes + $leave_sick_minutes + $leave_sick_work_minutes + $stop_work_minutes ;
    
        // คำนวณชั่วโมงรวม
        $total_hours = $sum_hours + floor($sum_minutes / 60); // เพิ่มชั่วโมงจากนาที
        $total_minutes = $sum_minutes % 60; // นาทียังคงอยู่
    
        // Check if total minutes can round up to an hour
        if ($total_minutes >= 10) {
            $total_hours += 1; // Convert 10 minutes to 1 hour
            $total_minutes -= 10; // Subtract the 10 minutes
        }
    
        // ถ้าชั่วโมงรวมมากกว่า 8 ชั่วโมงให้เพิ่มจำนวนวัน
        if ($total_hours >= 8) {
            $extra_days = floor($total_hours / 8); // จำนวนวันเพิ่มเติมจากชั่วโมง
            $sum_day += $extra_days; // เพิ่มจำนวนวัน
            $total_hours = $total_hours % 8; // คำนวณชั่วโมงที่เหลือ
        }
        
        // แสดงผล
        echo '<td>' . $sum_day . '(' . $total_hours . '.' . $total_minutes . ')' . '</td>';
        echo '</tr>';
        $rowNumber++;
    }
}
// ------------------------------------------------------------------------------
// ถ้าไม่เลือกปี
else {
    // $selectedYear = date('Y');
    // // กำหนดวันที่เริ่มต้นและสิ้นสุดสำหรับช่วง 12/2023 - 11/2024
    $startDate = date("Y-m-d", strtotime(($selectedYear - 1) . "-12-01"));
    $endDate = date("Y-m-d", strtotime($selectedYear . "-11-30"));
    
    $approveStatus = ($depart == 'RD') ? 2 : (($depart == 'Office') ? 2 : ($depart == '' ? NULL : 2));

    $sql = "SELECT * FROM employees 
    WHERE e_usercode <> :userCode
    AND e_status <> '1' 
    AND (
            (e_sub_department = :subDepart)
            OR (e_sub_department2 = :subDepart2)
            OR (e_sub_department3 = :subDepart3)
            OR (e_sub_department4 = :subDepart4)
            OR (e_sub_department5 = :subDepart5)
        )
    ";
    
    // Prepare the statement
    $stmt = $conn->prepare($sql);
    
    // Bind parameters
    $stmt->bindParam(':userCode', $userCode);
    $stmt->bindParam(':subDepart', $subDepart);
    $stmt->bindParam(':subDepart2', $subDepart2);
    $stmt->bindParam(':subDepart3', $subDepart3);
    $stmt->bindParam(':subDepart4', $subDepart4);
    $stmt->bindParam(':subDepart5', $subDepart5);
    
    // Execute the query
    $stmt->execute();
    
    // Initialize row number
    $rowNumber = 1;
    
    // Fetch data and display it
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo '<tr>';
        echo '<td>' . $rowNumber . '</td>';
        echo '<td>' . $row['e_usercode'] . '</td>';
        echo '<td>' . $row['e_name'] . '</td>';
        echo '<td>' . $row['e_department'] . '</td>';
        echo '<td>' . $row['e_yearexp'] . '</td>';
        echo '<td>' . $row['e_level'] . '</td>';
    
        // $selectedYear = date('Y'); // ปีปัจจุบัน
        // // กำหนดวันที่เริ่มต้นและสิ้นสุดสำหรับช่วงปีปัจจุบัน
        // $startDate = date("Y-m-d", strtotime(($selectedYear - 1) . "-12-01"));
        // $endDate = date("Y-m-d", strtotime($selectedYear . "-11-30"));
    $sql_leave = "SELECT
      -- ลากิจได้รับค่าจ้าง
    SUM(
        CASE
            WHEN l_leave_id = '1' AND YEAR(l_leave_start_date) = :selectedYear
            THEN DATEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))
            - (SELECT COUNT(1)
               FROM holiday
               WHERE h_start_date BETWEEN l_leave_start_date AND l_leave_end_date
               AND h_holiday_status = 'วันหยุด'
               AND h_status = 0)
            ELSE 0
        END
    ) AS personal_leave_days,
    
    SUM(
        CASE
            WHEN l_leave_id = '1'
            THEN (HOUR(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))) % 24)
            - CASE
                  WHEN HOUR(CONCAT(l_leave_start_date, ' ', l_leave_start_time)) < 12
                       AND HOUR(CONCAT(l_leave_end_date, ' ', l_leave_end_time)) > 12
                  THEN 1
                  ELSE 0
              END
            ELSE 0
        END
    ) AS personal_leave_hours,
    
    SUM(
        CASE
            WHEN l_leave_id = '1'
            THEN MINUTE(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time)))
            ELSE 0
        END
    ) AS personal_leave_minutes,
    
    -- ลากิจไม่ได้รับค่าจ้าง
    SUM(
        CASE
            WHEN l_leave_id = '2'
            THEN DATEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))
            - (SELECT COUNT(1)
               FROM holiday
               WHERE h_start_date BETWEEN l_leave_start_date AND l_leave_end_date
               AND h_holiday_status = 'วันหยุด'
               AND h_status = 0)
            ELSE 0
        END
    ) AS personal_no_leave_days,
    
    SUM(
        CASE
            WHEN l_leave_id = '2'
            THEN (HOUR(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))) % 24)
            - CASE
                  WHEN HOUR(CONCAT(l_leave_start_date, ' ', l_leave_start_time)) < 12
                       AND HOUR(CONCAT(l_leave_end_date, ' ', l_leave_end_time)) > 12
                  THEN 1
                  ELSE 0
              END
            ELSE 0
        END
    ) AS personal_no_leave_hours,
    
    SUM(
        CASE
            WHEN l_leave_id = '2'
            THEN MINUTE(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time)))
            ELSE 0
        END
    ) AS personal_no_leave_minutes,
    
    -- ลาป่วย
  SUM(
        CASE
            WHEN l_leave_id = '3'
            THEN DATEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))
            - (SELECT COUNT(1)
               FROM holiday
               WHERE h_start_date BETWEEN l_leave_start_date AND l_leave_end_date
               AND h_holiday_status = 'วันหยุด'
               AND h_status = 0)
            ELSE 0
        END
    ) AS sick_leave_days,
    
    SUM(
        CASE
            WHEN l_leave_id = '3'
            THEN (HOUR(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))) % 24)
            - CASE
                  WHEN HOUR(CONCAT(l_leave_start_date, ' ', l_leave_start_time)) < 12
                       AND HOUR(CONCAT(l_leave_end_date, ' ', l_leave_end_time)) > 12
                  THEN 1
                  ELSE 0
              END
            ELSE 0
        END
    ) AS sick_leave_hours,
    
    SUM(
        CASE
            WHEN l_leave_id = '3'
            THEN MINUTE(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time)))
            ELSE 0
        END
    ) AS sick_leave_minutes,
    
    -- ลาป่วยจากงาน
    SUM(
        CASE
            WHEN l_leave_id = '4'
            THEN DATEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))
            - (SELECT COUNT(1)
               FROM holiday
               WHERE h_start_date BETWEEN l_leave_start_date AND l_leave_end_date
               AND h_holiday_status = 'วันหยุด'
               AND h_status = 0)
            ELSE 0
        END
    ) AS sick_work_leave_days,
    
    SUM(
        CASE
            WHEN l_leave_id = '4'
            THEN (HOUR(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))) % 24)
            - CASE
                  WHEN HOUR(CONCAT(l_leave_start_date, ' ', l_leave_start_time)) < 12
                       AND HOUR(CONCAT(l_leave_end_date, ' ', l_leave_end_time)) > 12
                  THEN 1
                  ELSE 0
              END
            ELSE 0
        END
    ) AS sick_work_leave_hours,
    
    SUM(
        CASE
            WHEN l_leave_id = '4'
            THEN MINUTE(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time)))
            ELSE 0
        END
    ) AS sick_work_leave_minutes,
    
    -- ลาพักร้อน
    SUM(
        CASE
            WHEN l_leave_id = '5'
            THEN DATEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))
            - (SELECT COUNT(1)
               FROM holiday
               WHERE h_start_date BETWEEN l_leave_start_date AND l_leave_end_date
               AND h_holiday_status = 'วันหยุด'
               AND h_status = 0)
            ELSE 0
        END
    ) AS annual_leave_days,
    
    SUM(
        CASE
            WHEN l_leave_id = '5'
            THEN (HOUR(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))) % 24)
            - CASE
                  WHEN HOUR(CONCAT(l_leave_start_date, ' ', l_leave_start_time)) < 12
                       AND HOUR(CONCAT(l_leave_end_date, ' ', l_leave_end_time)) > 12
                  THEN 1
                  ELSE 0
              END
            ELSE 0
        END
    ) AS annual_leave_hours,
    
    SUM(
        CASE
            WHEN l_leave_id = '5'
            THEN MINUTE(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time)))
            ELSE 0
        END
    ) AS annual_leave_minutes,
    
    -- หยุดงาน
      SUM(
            CASE
                WHEN l_leave_id = '6'
                THEN IFNULL(DATEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time)), 0)
                - (SELECT COUNT(1)
                   FROM holiday
                   WHERE h_start_date BETWEEN l_leave_start_date AND l_leave_end_date
                   AND h_holiday_status = 'วันหยุด'
                   AND h_status = 0)
                ELSE 0
            END
        ) AS stop_work_days,
    
        SUM(
            CASE
                WHEN l_leave_id = '6'
                THEN IFNULL(HOUR(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))) % 24, 0)
                - CASE
                      WHEN HOUR(CONCAT(l_leave_start_date, ' ', l_leave_start_time)) < 12
                           AND HOUR(CONCAT(l_leave_end_date, ' ', l_leave_end_time)) > 12
                      THEN 1
                      ELSE 0
                  END
                ELSE 0
            END
        ) AS stop_work_hours,
    
        SUM(
            CASE
                WHEN l_leave_id = '6'
                THEN IFNULL(MINUTE(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))), 0)
                ELSE 0
            END
        ) AS stop_work_minutes,
    
    -- อื่น ๆ
    SUM(
        CASE
            WHEN l_leave_id = '8'
            THEN DATEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))
            - (SELECT COUNT(1)
               FROM holiday
               WHERE h_start_date BETWEEN l_leave_start_date AND l_leave_end_date
               AND h_holiday_status = 'วันหยุด'
               AND h_status = 0)
            ELSE 0
        END
    ) AS other_leave_days,
    
    SUM(
        CASE
            WHEN l_leave_id = '8'
            THEN (HOUR(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time))) % 24)
            - CASE
                  WHEN HOUR(CONCAT(l_leave_start_date, ' ', l_leave_start_time)) < 12
                       AND HOUR(CONCAT(l_leave_end_date, ' ', l_leave_end_time)) > 12
                  THEN 1
                  ELSE 0
              END
            ELSE 0
        END
    ) AS other_leave_hours,
    
    SUM(
        CASE
            WHEN l_leave_id = '8'
            THEN MINUTE(TIMEDIFF(CONCAT(l_leave_end_date, ' ', l_leave_end_time), CONCAT(l_leave_start_date, ' ', l_leave_start_time)))
            ELSE 0
        END
    ) AS other_leave_minutes,
    
        (SELECT e_leave_personal FROM employees WHERE e_usercode = :userCode) AS total_personal,
        (SELECT e_leave_personal_no FROM employees WHERE e_usercode = :userCode) AS total_personal_no,
        (SELECT e_leave_sick FROM employees WHERE e_usercode = :userCode) AS total_sick,
        (SELECT e_leave_sick_work FROM employees WHERE e_usercode = :userCode) AS total_leave_sick_work,
        (SELECT e_leave_annual FROM employees WHERE e_usercode = :userCode) AS total_annual,
        (SELECT e_other FROM employees WHERE e_usercode = :userCode) AS total_other,
    
        -- Count of late occurrences (leave_id = 7)
        SUM(
            CASE
                WHEN l_leave_id = '7' THEN 1
                ELSE 0
            END
        ) AS late_count
    
        FROM leave_list
        WHERE (
            (l_leave_id IN ('1', '2', '3', '4', '6', '8') AND YEAR(l_leave_end_date) BETWEEN :startDate AND :endDate)
            OR (l_leave_id = '5' AND YEAR(l_leave_end_date) = :selectedYear)
            )
        AND l_leave_status = 0
        AND l_usercode = :userCode
        AND l_approve_status = :approveStatus
        AND l_approve_status2 = 4
        ";
        // echo $startDate;
        // echo $endDate;
    
        $stmt_leave = $conn->prepare($sql_leave);
        $stmt_leave->bindParam(':userCode', $row['e_usercode']);
        $stmt_leave->bindParam(':selectedYear', $selectedYear);
        $stmt_leave->bindParam(':startDate', $startDate);
        $stmt_leave->bindParam(':endDate', $endDate);
        $stmt_leave->bindParam(':approveStatus', $approveStatus);

        $stmt_leave->execute();
        $stmt_leave->execute();
        $result_leave = $stmt_leave->fetch(PDO::FETCH_ASSOC);
    
        // ลากิจได้รับค่าจ้าง ----------------------------------------------------------------
        $total_personal = $result_leave['total_personal'] ?? 0;
        $leave_personal_days = $result_leave['personal_leave_days'] ?? 0;
        $leave_personal_hours = $result_leave['personal_leave_hours'] ?? 0;
        $leave_personal_minutes = $result_leave['personal_leave_minutes'] ?? 0;
    
    // Convert hours to days (8 hours = 1 day)
        $leave_personal_days += floor($leave_personal_hours / 8);
        $leave_personal_hours = $leave_personal_hours % 8; // Remaining hours after converting to days
    
        if ($leave_personal_minutes >= 60) {
            $leave_personal_hours += floor($leave_personal_minutes / 60);
            $leave_personal_minutes = $leave_personal_minutes % 60;
        }
    
    // Round minutes to 30 minutes
        if ($leave_personal_minutes > 0 && $leave_personal_minutes <= 30) {
            $leave_personal_minutes = 30;
        } elseif ($leave_personal_minutes > 30) {
            $leave_personal_minutes = 0;
            $leave_personal_hours += 1;
        }
        if ($leave_personal_minutes == 30) {
            $leave_personal_minutes = 5;
        }
    
    // ลากิจไม่ได้รับค่าจ้าง ----------------------------------------------------------------
        $total_personal_no = $result_leave['total_personal_no'] ?? 0;
        $leave_personal_no_days = $result_leave['personal_no_leave_days'] ?? 0;
        $leave_personal_no_hours = $result_leave['personal_no_leave_hours'] ?? 0;
        $leave_personal_no_minutes = $result_leave['personal_no_leave_minutes'] ?? 0;
    
    // Convert hours to days
        $leave_personal_no_days += floor($leave_personal_no_hours / 8);
        $leave_personal_no_hours = $leave_personal_no_hours % 8;
    
        if ($leave_personal_no_minutes >= 60) {
            $leave_personal_no_hours += floor($leave_personal_no_minutes / 60);
            $leave_personal_no_minutes = $leave_personal_no_minutes % 60;
        }
    
    // Round minutes to 30 minutes
        if ($leave_personal_no_minutes > 0 && $leave_personal_no_minutes <= 30) {
            $leave_personal_no_minutes = 30;
        } elseif ($leave_personal_no_minutes > 30) {
            $leave_personal_no_minutes = 0;
            $leave_personal_no_hours += 1;
        }
    
        if ($leave_personal_no_minutes == 30) {
            $leave_personal_no_minutes = 5;
        }
    
        // ลาป่วย ----------------------------------------------------------------
        // Fetch total personal leave and leave durations
        $total_sick = $result_leave['total_sick'] ?? 0;
        $leave_sick_days = $result_leave['sick_leave_days'] ?? 0;
        $leave_sick_hours = $result_leave['sick_leave_hours'] ?? 0;
        $leave_sick_minutes = $result_leave['sick_leave_minutes'] ?? 0;
    
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
    
        if ($leave_sick_minutes == 30) {
            $leave_sick_minutes = 5;
        }
    
        // ลาป่วยจากงาน ----------------------------------------------------------------
        // Fetch total personal leave and leave durations
        $total_sick_work = $result_leave['total_leave_sick_work'] ?? 0;
        $leave_sick_work_days = $result_leave['sick_work_leave_days'] ?? 0;
        $leave_sick_work_hours = $result_leave['sick_work_leave_hours'] ?? 0;
        $leave_sick_work_minutes = $result_leave['sick_work_leave_minutes'] ?? 0;
    
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
    
        if ($leave_sick_work_minutes == 30) {
            $leave_sick_work_minutes = 5;
        }
    
        // ลาพักร้อน ----------------------------------------------------------------
        // Fetch total personal leave and leave durations
        $total_annual = $result_leave['total_annual'] ?? 0;
        $leave_annual_days = $result_leave['annual_leave_days'] ?? 0;
        $leave_annual_hours = $result_leave['annual_leave_hours'] ?? 0;
        $leave_annual_minutes = $result_leave['annual_leave_minutes'] ?? 0;
    
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
    
        if ($leave_annual_minutes == 30) {
            $leave_annual_minutes = 5;
        }
        // หยุดงาน ----------------------------------------------------------------
        $stop_work_days = $result_leave['stop_work_days'] ?? 0;
        $stop_work_hours = $result_leave['stop_work_hours'] ?? 0;
        $stop_work_minutes = $result_leave['stop_work_minutes'] ?? 0;
    
        // Convert total hours to days (8 hours = 1 day)
        $stop_work_days += floor($stop_work_hours / 8);
        $stop_work_hours = $stop_work_hours % 8; // Remaining hours after converting to days
    
        // Convert minutes to hours if applicable
        if ($stop_work_minutes >= 60) {
            $stop_work_hours += floor($stop_work_minutes / 60);
            $stop_work_minutes = $stop_work_minutes % 60;
        }
    
        // Round minutes to either 30 or 0
        if ($stop_work_minutes > 0 && $stop_work_minutes <= 30) {
            $stop_work_minutes = 30; // ปัดขึ้นเป็น 30 นาที
        } elseif ($stop_work_minutes > 30) {
            $stop_work_minutes = 0; // ปัดกลับเป็น 0 แล้วเพิ่มชั่วโมง
            $stop_work_hours += 1;
        }
    
        // ปรับจำนวน minutes ให้เป็น 5 นาทีในกรณี 30 นาที
        if ($stop_work_minutes == 30) {
            $stop_work_minutes = 5;
        }
            
        // อื่น ๆ ----------------------------------------------------------------
        // Fetch total personal leave and leave durations
        $total_other = $result_leave['total_other'] ?? 0;
        $other_days = $result_leave['other_leave_days'] ?? 0;
        $other_hours = $result_leave['other_leave_hours'] ?? 0;
        $other_minutes = $result_leave['other_leave_minutes'] ?? 0;
    
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
    
        if ($other_minutes == 30) {
            $other_minutes = 5;
        }
        // Output the result
        echo '<td>' . $total_personal . '</td>';
        echo '<td>' . $leave_personal_days . '(' . $leave_personal_hours . '.' . $leave_personal_minutes . ')' . '</td>';
        echo '<td>' . ($total_personal - $leave_personal_days) . '</td>';
    
        echo '<td>' . $total_personal_no . '</td>';
        echo '<td>' . $leave_personal_no_days . '(' . $leave_personal_no_hours . '.' . $leave_personal_no_minutes . ')' . '</td>';
        echo '<td>' . ($total_personal_no - $leave_personal_no_days) . '</td>';
    
        echo '<td>' . $total_sick . '</td>';
        echo '<td>' . $leave_sick_days . '(' . $leave_sick_hours . '.' . $leave_sick_minutes . ')' . '</td>';
        echo '<td>' . ($total_sick - $leave_sick_days) . '</td>';
    
        echo '<td>' . $total_sick_work . '</td>';
        echo '<td>' . $leave_sick_work_days . '(' . $leave_sick_work_hours . '.' . $leave_sick_work_minutes . ')' . '</td>';
        echo '<td>' . ($total_sick_work - $leave_sick_work_days) . '</td>';
    
        echo '<td>' . $total_annual . '</td>';
        echo '<td>' . $leave_annual_days . '(' . $leave_annual_hours . '.' . $leave_annual_minutes . ')' . '</td>';
        echo '<td>' . ($total_annual - $leave_annual_days) . '</td>';
    
        echo '<td>' . $total_other . '</td>';
        echo '<td>' . $other_days . '(' . $other_hours . '.' . $other_minutes . ')' . '</td>';
        echo '<td>' . ($total_other - $other_days) . '</td>';
    
        echo '<td>' . $stop_work_days . '(' . $stop_work_hours . '.' . $stop_work_minutes . ')' . '</td>';
        
        // echo "Total Late Count: " . $result_leave['late_count'];
    
        // คำนวณจำนวนวัน, ชั่วโมง, และนาที
        $sum_day = $leave_personal_days + $leave_personal_no_days + $leave_sick_days + $leave_sick_work_days + $stop_work_days;
        $sum_hours = $leave_personal_hours + $leave_personal_no_hours + $leave_sick_hours + $leave_sick_work_hours + $stop_work_hours;
        $sum_minutes = $leave_personal_minutes + $leave_personal_no_minutes + $leave_sick_minutes + $leave_sick_work_minutes + $stop_work_minutes ;
    
        // คำนวณชั่วโมงรวม
        $total_hours = $sum_hours + floor($sum_minutes / 60); // เพิ่มชั่วโมงจากนาที
        $total_minutes = $sum_minutes % 60; // นาทียังคงอยู่
    
        // Check if total minutes can round up to an hour
        if ($total_minutes >= 10) {
            $total_hours += 1; // Convert 10 minutes to 1 hour
            $total_minutes -= 10; // Subtract the 10 minutes
        }
    
        // ถ้าชั่วโมงรวมมากกว่า 8 ชั่วโมงให้เพิ่มจำนวนวัน
        if ($total_hours >= 8) {
            $extra_days = floor($total_hours / 8); // จำนวนวันเพิ่มเติมจากชั่วโมง
            $sum_day += $extra_days; // เพิ่มจำนวนวัน
            $total_hours = $total_hours % 8; // คำนวณชั่วโมงที่เหลือ
        }
        
        // แสดงผล
        echo '<td>' . $sum_day . '(' . $total_hours . '.' . $total_minutes . ')' . '</td>';
        echo '</tr>';
        $rowNumber++;
    }
} 

?>
            </tbody>
        </table>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const {
            jsPDF
        } = window.jspdf;

        document.getElementById("generate-pdf").addEventListener("click", function() {
            html2canvas(document.getElementById("leaveEmpTable")).then(canvas => {
                var imgData = canvas.toDataURL('image/png');
                var pdf = new jsPDF('landscape', 'pt', 'a4');
                var imgWidth = 841.89 -
                    40; // ความกว้างสำหรับ a4 แนวนอน ลบออก 40 pt เพื่อเว้นขอบซ้ายขวา
                var pageHeight = 595.28;
                var imgHeight = canvas.height * imgWidth / canvas.width;
                var heightLeft = imgHeight;

                var position = 20; // ระยะห่างจากขอบบน
                var margin = 20; // ระยะห่างจากขอบซ้ายขวา

                pdf.addImage(imgData, 'PNG', margin, position, imgWidth, imgHeight);
                pdf.save("leaveData.pdf");
            });
        });
    });

    async function capture() {
        const element = document.querySelector("#leaveEmpTable");
        const canvas = await html2canvas(element);
        const imgData = canvas.toDataURL('image/png');

        const link = document.createElement('a');
        link.href = imgData;
        link.download = 'capture.png';
        link.click();
    }

    $(document).ready(function() {
        $("#nameSearch").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $("tbody tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });
        $("#codeSearch").on("keyup", function() {
            var value2 = $(this).val().toLowerCase();
            $("tbody tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value2) > -1);
            });
        });

        document.getElementById('selectYear').addEventListener('change', function() {
            document.getElementById('yearForm').submit();
        });
    });

    function resetInput(inputId) {
        document.getElementById(inputId).value = '';
        var codeValue = document.getElementById("codeSearch").value.toLowerCase();
        var nameValue = document.getElementById("nameSearch").value.toLowerCase();

        $("tbody tr").each(function() {
            var code = $(this).find("td:nth-child(2)").text().toLowerCase();
            var name = $(this).find("td:nth-child(3)").text().toLowerCase();
            if (code.includes(codeValue) && name.includes(nameValue)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }

    function resetFields() {
        document.getElementById('codeSearch').value = '';
        document.getElementById('nameSearch').value = '';
        document.getElementById('depSearch').value = '';
    }
    </script>
    <script src="../js/popper.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/bootstrap.bundle.js"></script>
    <script src="../js/bootstrap.bundle.min.js"></script>
</body>

</html>