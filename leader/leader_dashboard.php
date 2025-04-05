<?php
    session_start();
    date_default_timezone_set('Asia/Bangkok');

    include '../connect.php';
    include '../session_lang.php';

    if (! isset($_SESSION['s_usercode'])) {
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
    <?php
        include 'leader_navbar.php';

        $currentYear = date('Y'); // ปีปัจจุบัน

        if (isset($_POST['year'])) {
            $selectedYear = $_POST['year'];
        } elseif (isset($_GET['year'])) {
            $selectedYear = $_GET['year'];
        } else {
            $selectedYear = $currentYear;
        }

        if (isset($_POST['month'])) {
            $selectedMonth = $_POST['month'];
        } elseif (isset($_GET['month'])) {
            $selectedMonth = $_GET['month'];
        } else {
            $selectedMonth = 'All';
        }

        // กำหนดช่วงวันที่เป็น 1 ม.ค. ถึง 31 ธ.ค. ของปีที่เลือก
        $startDate = date("Y-m-d", strtotime($selectedYear . "-01-01"));
        $endDate   = date("Y-m-d", strtotime($selectedYear . "-12-31"));

        // มีใบลาของพนักงาน --------------------------------------------------------------------------------------------
        $sql_check_leave = "SELECT
COUNT(li.l_list_id) AS totalLeaveItems,
li.*,
em.*
FROM leave_list li
INNER JOIN employees em
    ON li.l_usercode = em.e_usercode
WHERE
    li.l_leave_status = 0
    AND li.l_approve_status = 0
    AND li.l_level IN ('user')
    AND li.l_leave_id NOT IN (6, 7)
    AND (
        YEAR(li.l_leave_end_date) = :selectedYear
    )";

        if ($selectedMonth != "All") {
            $sql_check_leave .= " AND (
        Month(li.l_leave_end_date) = :selectedMonth
    )";
        }

        $sql_check_leave .= " AND (
    (em.e_sub_department IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND em.e_sub_department <> '')
    OR
    (em.e_sub_department2 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND em.e_sub_department2 <> '')
    OR
    (em.e_sub_department3 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND em.e_sub_department3 <> '')
    OR
    (em.e_sub_department4 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND em.e_sub_department4 <> '')
    OR
    (em.e_sub_department5 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND em.e_sub_department5 <> '')
)
ORDER BY li.l_username DESC";

        $stmt_check_leave = $conn->prepare($sql_check_leave);
        $stmt_check_leave->bindParam(':depart', $depart);
        $stmt_check_leave->bindParam(':subDepart', $subDepart);
        $stmt_check_leave->bindParam(':subDepart2', $subDepart2);
        $stmt_check_leave->bindParam(':subDepart3', $subDepart3);
        $stmt_check_leave->bindParam(':subDepart4', $subDepart4);
        $stmt_check_leave->bindParam(':subDepart5', $subDepart5);
        $stmt_check_leave->bindParam(':selectedYear', $selectedYear);

        if ($selectedMonth != "All") {
            $stmt_check_leave->bindParam(':selectedMonth', $selectedMonth);
        }

        $stmt_check_leave->execute();

        $employee_names = [];
        while ($row_leave = $stmt_check_leave->fetch(PDO::FETCH_ASSOC)) {
            $employee_names[] = $row_leave['l_username'];
        }

        $employee_list = implode(', ', $employee_names);

        if (! empty($employee_list)) {
            echo '<div class="alert alert-warning d-flex align-items-center" role="alert">
<i class="fa-solid fa-circle-exclamation me-2"></i>
<span>มีใบลาของ ' . $employee_list . ' กรุณาตรวจสอบ</span>
<button type="button" class="ms-2 btn btn-primary button-shadow" onclick="window.location.href=\'leader_leave_request.php\'">ตรวจสอบใบลา</button>
<button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
</div>';
        }

        // พนักงานยกเลิกใบลา --------------------------------------------------------------------------------------------
        $sql_cancel_leave = "SELECT
COUNT(li.l_list_id) AS totalLeaveItems,
li.*,
em.*
FROM leave_list li
INNER JOIN employees em
    ON li.l_usercode = em.e_usercode
WHERE
    li.l_approve_status = 0
    AND li.l_leave_status = 1
    AND li.l_level IN ('user')
    AND li.l_leave_id NOT IN (6, 7)
    AND (
         YEAR(li.l_leave_end_date) = :selectedYear
    )";

        if ($selectedMonth != "All") {
            $sql_cancel_leave .= " AND (
        Month(li.l_leave_end_date) = :selectedMonth
    )";
        }

        $sql_cancel_leave .= " AND (
    (em.e_sub_department IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND em.e_sub_department <> '')
    OR
    (em.e_sub_department2 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND em.e_sub_department2 <> '')
    OR
    (em.e_sub_department3 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND em.e_sub_department3 <> '')
    OR
    (em.e_sub_department4 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND em.e_sub_department4 <> '')
    OR
    (em.e_sub_department5 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND em.e_sub_department5 <> '')
)
ORDER BY li.l_username DESC";

        $stmt_cancel_leave = $conn->prepare($sql_cancel_leave);
        $stmt_cancel_leave->bindParam(':depart', $depart);
        $stmt_cancel_leave->bindParam(':subDepart', $subDepart);
        $stmt_cancel_leave->bindParam(':subDepart2', $subDepart2);
        $stmt_cancel_leave->bindParam(':subDepart3', $subDepart3);
        $stmt_cancel_leave->bindParam(':subDepart4', $subDepart4);
        $stmt_cancel_leave->bindParam(':subDepart5', $subDepart5);
        $stmt_cancel_leave->bindParam(':selectedYear', $selectedYear);

        if ($selectedMonth != "All") {
            $stmt_cancel_leave->bindParam(':selectedMonth', $selectedMonth);
        }

        $stmt_cancel_leave->execute();

        $employee_names = [];
        while ($row_leave = $stmt_cancel_leave->fetch(PDO::FETCH_ASSOC)) {
            $employee_names[] = $row_leave['l_username'];
        }

        $employee_list = implode(', ', $employee_names);

        if (! empty($employee_list)) {
            echo '<div class="alert alert-danger d-flex align-items-center" role="alert">
<i class="fa-solid fa-circle-exclamation me-2"></i>
<span>มีการยกเลิกใบลาของ ' . $employee_list . ' กรุณาตรวจสอบ</span>
<button type="button" class="ms-2 btn btn-primary button-shadow" onclick="window.location.href=\'leader_leave_request.php\'">ตรวจสอบใบลา</button>
<button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
</div>';
        }

        // พนักงานมาสาย --------------------------------------------------------------------------------------------
        $sql_chk_late = "SELECT
COUNT(li.l_list_id) AS totalLeaveItems,
li.*,
em.*
FROM leave_list li
INNER JOIN employees em
    ON li.l_usercode = em.e_usercode
WHERE
    li.l_approve_status = 0
    AND li.l_leave_status = 0
    AND li.l_level IN ('user')
    AND li.l_leave_id = 7
    AND (
         YEAR(li.l_leave_end_date) = :selectedYear
    )";

        if ($selectedMonth != "All") {
            $sql_chk_late .= " AND (
   Month(li.l_leave_end_date) = :selectedMonth
    )";
        }

        $sql_chk_late .= " AND (
    (em.e_sub_department IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND em.e_sub_department <> '')
    OR
    (em.e_sub_department2 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND em.e_sub_department2 <> '')
    OR
    (em.e_sub_department3 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND em.e_sub_department3 <> '')
    OR
    (em.e_sub_department4 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND em.e_sub_department4 <> '')
    OR
    (em.e_sub_department5 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND em.e_sub_department5 <> '')
)
ORDER BY li.l_username DESC";

        $stmt_chk_late = $conn->prepare($sql_chk_late);
        $stmt_chk_late->bindParam(':depart', $depart);
        $stmt_chk_late->bindParam(':subDepart', $subDepart);
        $stmt_chk_late->bindParam(':subDepart2', $subDepart2);
        $stmt_chk_late->bindParam(':subDepart3', $subDepart3);
        $stmt_chk_late->bindParam(':subDepart4', $subDepart4);
        $stmt_chk_late->bindParam(':subDepart5', $subDepart5);
        $stmt_chk_late->bindParam(':selectedYear', $selectedYear);

        if ($selectedMonth != "All") {
            $stmt_chk_late->bindParam(':selectedMonth', $selectedMonth);
        }

        $stmt_chk_late->execute();

        $employee_names = [];
        while ($row_leave = $stmt_chk_late->fetch(PDO::FETCH_ASSOC)) {
            $employee_names[] = $row_leave['l_username'];
        }

        $employee_list = implode(', ', $employee_names);

        if (! empty($employee_list)) {
            echo '<div class="alert alert-danger d-flex align-items-center" role="alert">
<i class="fa-solid fa-circle-exclamation me-2"></i>
<span> ' . $employee_list . ' มาสาย' . ' กรุณาตรวจสอบ</span>
<button type="button" class="ms-2 btn btn-primary button-shadow" onclick="window.location.href=\'leader_employee_attendance.php\'">ตรวจสอบการมาสาย</button>
<button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
</div>';
        }
        // พนักงานขาดงาน --------------------------------------------------------------------------------------------
        $sql_stop_work = "SELECT
COUNT(li.l_list_id) AS totalLeaveItems,
li.*,
em.*
FROM leave_list li
INNER JOIN employees em
    ON li.l_usercode = em.e_usercode
WHERE
    li.l_approve_status = 0
    AND li.l_leave_status = 0
    AND li.l_level IN ('user')
    AND li.l_leave_id = 6
    AND (
        YEAR(li.l_leave_end_date) = :selectedYear
    )";

        if ($selectedMonth != "All") {
            $sql_stop_work .= " AND (
 Month(li.l_leave_end_date) = :selectedMonth
    )";
        }

        $sql_stop_work .= " AND (
    (em.e_sub_department IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND em.e_sub_department <> '')
    OR
    (em.e_sub_department2 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND em.e_sub_department2 <> '')
    OR
    (em.e_sub_department3 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND em.e_sub_department3 <> '')
    OR
    (em.e_sub_department4 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND em.e_sub_department4 <> '')
    OR
    (em.e_sub_department5 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND em.e_sub_department5 <> '')
)
GROUP BY li.l_username
ORDER BY li.l_username DESC";

        $stmt_stop_work = $conn->prepare($sql_stop_work);
        $stmt_stop_work->bindParam(':depart', $depart);
        $stmt_stop_work->bindParam(':subDepart', $subDepart);
        $stmt_stop_work->bindParam(':subDepart2', $subDepart2);
        $stmt_stop_work->bindParam(':subDepart3', $subDepart3);
        $stmt_stop_work->bindParam(':subDepart4', $subDepart4);
        $stmt_stop_work->bindParam(':subDepart5', $subDepart5);
        $stmt_stop_work->bindParam(':selectedYear', $selectedYear);

        if ($selectedMonth != "All") {
            $stmt_stop_work->bindParam(':selectedMonth', $selectedMonth);
        }

        $stmt_stop_work->execute();

        $employee_names = [];
        while ($row_leave = $stmt_stop_work->fetch(PDO::FETCH_ASSOC)) {
            $employee_names[] = $row_leave['l_username'];
        }

        $employee_list = implode(', ', $employee_names);

        if (! empty($employee_list)) {
            echo '<div class="alert alert-danger d-flex align-items-center" role="alert">
<i class="fa-solid fa-circle-exclamation me-2"></i>
<span> ' . $employee_list . ' หยุดงาน' . ' กรุณาตรวจสอบ</span>
<button type="button" class="ms-2 btn btn-primary button-shadow" onclick="window.location.href=\'leader_employee_attendance.php\'">ตรวจสอบ</button>
<button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
</div>';
        }

        // --------------------------------------------------------------------------------------------
    ?>
    <div class="mt-3 container-fluid">
        <div class="row">
            <div class="d-flex justify-content-between align-items-center">
                <form class="mt-3 mb-3 row" method="post" id="yearMonthForm">
                    <label for="" class="mt-2 col-auto">เลือกปี</label>
                    <div class="col-auto">
                        <?php

                            echo "<select class='form-select' name='year' id='selectedYear' onchange='document.getElementById(\"yearMonthForm\").submit();'>";

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
                                '01'  => $strJan,
                                '02'  => $strFeb,
                                '03'  => $strMar,
                                '04'  => $strApr,
                                '05'  => $strMay,
                                '06'  => $strJun,
                                '07'  => $strJul,
                                '08'  => $strAug,
                                '09'  => $strSep,
                                '10'  => $strOct,
                                '11'  => $strNov,
                                '12'  => $strDec,
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
            COUNT(CASE WHEN l_leave_id = 7 THEN 1 END) AS total_late,
        (SELECT e_leave_personal FROM employees WHERE e_usercode = :userCode) AS total_personal,
        (SELECT e_leave_personal_no FROM employees WHERE e_usercode = :userCode) AS total_personal_no,
        (SELECT e_leave_sick FROM employees WHERE e_usercode = :userCode) AS total_sick,
        (SELECT e_leave_sick_work FROM employees WHERE e_usercode = :userCode) AS total_sick_work,
        (SELECT e_leave_annual FROM employees WHERE e_usercode = :userCode) AS total_annual,
        (SELECT e_other FROM employees WHERE e_usercode = :userCode) AS total_other
        FROM leave_list
        JOIN employees ON employees.e_usercode = leave_list.l_usercode
        WHERE l_leave_id = :leave_id
        AND l_usercode = :userCode
        AND (
            YEAR(l_leave_end_date) = :selectedYear
        )";

                    if ($selectedMonth != "All") {
                        $sql_leave_personal .= " AND (
            MONTH(l_leave_end_date) = :selectedMonth
        )";
                    }

                    $sql_leave_personal .= " AND l_leave_status = 0
        AND l_approve_status IN (2,6)
        AND l_approve_status2 IN (4,6)
        AND l_approve_status3 IN (8,6)";

                    $stmt_leave_personal = $conn->prepare($sql_leave_personal);
                    $stmt_leave_personal->bindParam(':leave_id', $leave_id, PDO::PARAM_INT);
                    $stmt_leave_personal->bindParam(':userCode', $userCode);
                    $stmt_leave_personal->bindParam(':selectedYear', $selectedYear, PDO::PARAM_INT);

                    if ($selectedMonth != "All") {
                        $stmt_leave_personal->bindParam(':selectedMonth', $selectedMonth, PDO::PARAM_INT);
                    }

                    $stmt_leave_personal->execute();
                    $result_leave = $stmt_leave_personal->fetch(PDO::FETCH_ASSOC);

                    if ($result_leave) {
                        $days    = $result_leave['total_leave_days'] ?? 0;
                        $hours   = $result_leave['total_leave_hours'] ?? 0;
                        $minutes = $result_leave['total_leave_minutes'] ?? 0;

                        $total_personal    = $result_leave['total_personal'] ?? 0;
                        $total_personal_no = $result_leave['total_personal_no'] ?? 0;
                        $total_sick        = $result_leave['total_sick'] ?? 0;
                        $total_sick_work   = $result_leave['total_sick_work'] ?? 0;
                        $total_annual      = $result_leave['total_annual'] ?? 0;
                        $total_other       = $result_leave['total_other'] ?? 0;
                        $total_late        = $result_leave['total_late'] ?? 0;

                        $days += floor($hours / 8);
                        $hours = $hours % 8;

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

                    echo '<div class="col-3 filter-card">';

                    if ($leave_id == 1) {
                        echo '<div class="card text-light mb-3 filter-card" style="background-color: #031B80;" data-leave-id="1">';
                        echo '<div class="card-body">';
                        echo '    <div class="card-title">';
                        echo '        <div class="d-flex justify-content-between">';
                        echo '            <div>';
                        echo '<h5>' . $days . '(' . $hours . '.' . $minutes . ') / ' . $total_personal . '</h5>';
                        echo '<p class="card-text">' . $leave_name . '</p>';
                        echo '            </div>';
                        echo '            <div class="d-flex justify-content-end">';
                        echo '                <i class="mx-2 mt-3 fa-solid fa-sack-dollar fa-2xl"></i>';
                        echo '            </div>';
                        echo '        </div>';
                        echo '    </div>';
                        echo '</div>';
                        echo '</div>';
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
                        echo '            <div class="d-flex justify-content-end">';
                        echo '<i class="mx-2 mt-3 fa-solid fa-sack-xmark fa-2xl"></i>';
                        echo '            </div>';
                        echo '        </div>';
                        echo '    </div>';
                        echo '</div>';
                        echo '</div>';
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
                        echo '            <div class="d-flex justify-content-end">';
                        echo '<i class="mx-2 mt-3 fa-solid fa-syringe fa-2xl"></i>';
                        echo '            </div>';
                        echo '        </div>';
                        echo '    </div>';
                        echo '</div>';
                        echo '</div>';
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
                        echo '            <div class="d-flex justify-content-end">';
                        echo '<i class="mx-2 mt-3 fa-solid fa-user-injured fa-2xl"></i>';
                        echo '            </div>';
                        echo '        </div>';
                        echo '    </div>';
                        echo '</div>';
                        echo '</div>';
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
                        echo '            <div class="d-flex justify-content-end">';
                        echo '<i class="mx-2 mt-3 fa-solid fa-business-time fa-2xl"></i>';
                        echo '            </div>';
                        echo '        </div>';
                        echo '    </div>';
                        echo '</div>';
                        echo '</div>';
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
                        echo '            <div class="d-flex justify-content-end">';
                        echo '<i class="mx-2 mt-3 fa-solid fa-business-time fa-2xl"></i>';
                        echo '            </div>';
                        echo '        </div>';
                        echo '    </div>';
                        echo '</div>';
                        echo '</div>';
                    } else if ($leave_id == 7) {
                        echo '<div class="card text-light mb-3 filter-card" style="background-color: #2788E9;" data-leave-id="7">';
                        echo '<div class="card-body">';
                        echo '    <div class="card-title">';
                        echo '        <div class="d-flex justify-content-between">';
                        echo '            <div>';
                        echo '<h5>' . $total_late . '</h5>';
                        echo '<p class="card-text">' . $leave_name . '</p>';
                        echo '            </div>';
                        echo '            <div class="d-flex justify-content-end">';
                        echo '<i class="mx-2 mt-3 fa-solid fa-person-running fa-2xl"></i>';
                        echo '            </div>';
                        echo '        </div>';
                        echo '    </div>';
                        echo '</div>';
                        echo '</div>';
                    } else if ($leave_id == 8) {
                        echo '<div class="card text-light mb-3 filter-card" style="background-color: #6FB0F0;" data-leave-id="8">';
                        echo '<div class="card-body">';
                        echo '    <div class="card-title">';
                        echo '        <div class="d-flex justify-content-between">';
                        echo '            <div>';
                        echo '<h5>' . $days . '(' . $hours . '.' . $minutes . ')' . '</h5>';
                        echo '<p class="card-text">' . $leave_name . '</p>';
                        echo '            </div>';
                        echo '            <div class="d-flex justify-content-end">';
                        echo '<i class="mx-2 mt-3 fa-solid fa-bars fa-2xl"></i>';
                        echo '            </div>';
                        echo '        </div>';
                        echo '    </div>';
                        echo '</div>';
                        echo '</div>';
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
                                        <span class="badge rounded-pill text-bg-info" hidden>เหลือ
                                            <span id="remaining-days">0 </span> วัน
                                            <span id="remaining-hours">0 </span> ชั่วโมง
                                            <span id="remaining-minutes">0 </span> นาที
                                        </span>
                                        <span style="color: red;">*</span>
                                        <select class="form-select" id="leaveType" required>
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
                                            onchange="calculateLeaveDuration()">
                                    </div>
                                    <div class=" col-6">
                                        <label for="startTime" class="form-label">เวลาที่เริ่มต้น</label>
                                        <span style="color: red;">*</span>
                                        <select class="form-select" id="startTime" name="startTime" required
                                            onchange="calculateLeaveDuration()">
                                            <option value="08:00" selected>08:00</option>
                                            <option value="08:10">08:10</option>
                                            <option value="08:15">08:15</option>
                                            <option value="08:30">08:30</option>
                                            <option value="08:45">08:45</option>
                                            <option value="09:00">09:00</option>
                                            <option value="09:10">09:10</option>
                                            <option value="09:15">09:15</option>
                                            <option value="09:30">09:30</option>
                                            <option value="09:45">09:45</option>
                                            <option value="10:00">10:00</option>
                                            <option value="10:10">10:10</option>
                                            <option value="10:15">10:15</option>
                                            <option value="10:30">10:30</option>
                                            <option value="10:45">10:45</option>
                                            <option value="11:00">11:00</option>
                                            <option value="11:10">11:10</option>
                                            <option value="11:15">11:15</option>
                                            <option value="11:30">11:30</option>
                                            <option value="12:00">11:45</option>
                                            <option value="13:00">12:45</option>
                                            <option value="13:10">13:10</option>
                                            <option value="13:15">13:15</option>
                                            <option value="13:40">13:40</option>
                                            <option value="13:45">13:45</option>
                                            <option value="14:10">14:10</option>
                                            <option value="14:15">14:15</option>
                                            <option value="14:40">14:40</option>
                                            <option value="14:45">14:45</option>
                                            <option value="15:10">15:10</option>
                                            <option value="15:15">15:15</option>
                                            <option value="15:40">15:40</option>
                                            <option value="15:45">15:45</option>
                                            <option value="16:10">16:10</option>
                                            <option value="16:15">16:15</option>
                                            <option value="17:00">16:40</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mt-3 row">
                                    <div class="col-6">
                                        <label for="endDate" class="form-label">วันที่สิ้นสุด</label>
                                        <span style="color: red;">*</span>
                                        <input type="text" class="form-control" id="endDate" required
                                            onchange="calculateLeaveDuration()">
                                    </div>
                                    <div class="col-6">
                                        <label for="endTime" class="form-label">เวลาที่สิ้นสุด</label>
                                        <span style="color: red;">*</span>
                                        <select class="form-select" id="endTime" name="endTime" required
                                            onchange="calculateLeaveDuration()">
                                            <option value="08:00">08:00</option>
                                            <option value="08:10">08:10</option>
                                            <option value="08:15">08:15</option>
                                            <option value="08:30">08:30</option>
                                            <option value="08:45">08:45</option>
                                            <option value="09:00">09:00</option>
                                            <option value="09:10">09:10</option>
                                            <option value="09:15">09:15</option>
                                            <option value="09:30">09:30</option>
                                            <option value="09:45">09:45</option>
                                            <option value="10:00">10:00</option>
                                            <option value="10:10">10:10</option>
                                            <option value="10:15">10:15</option>
                                            <option value="10:30">10:30</option>
                                            <option value="10:45">10:45</option>
                                            <option value="11:00">11:00</option>
                                            <option value="11:10">11:10</option>
                                            <option value="11:15">11:15</option>
                                            <option value="11:30">11:30</option>
                                            <option value="12:00">11:45</option>
                                            <option value="13:00">12:45</option>
                                            <option value="13:10">13:10</option>
                                            <option value="13:15">13:15</option>
                                            <option value="13:40">13:40</option>
                                            <option value="13:45">13:45</option>
                                            <option value="14:10">14:10</option>
                                            <option value="14:15">14:15</option>
                                            <option value="14:40">14:40</option>
                                            <option value="14:45">14:45</option>
                                            <option value="15:10">15:10</option>
                                            <option value="15:15">15:15</option>
                                            <option value="15:40">15:40</option>
                                            <option value="15:45">15:45</option>
                                            <option value="16:10">16:10</option>
                                            <option value="16:15">16:15</option>
                                            <option value="17:00" selected>16:40</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mt-3 row">
                                    <div class="col-6">
                                        <label for="leaveDuration" class="form-label text-primary">** ระยะเวลาการลา :
                                        </label>
                                        <span id="leaveDuration" class="form-label text-primary"></span>
                                    </div>
                                </div>
                                <div class="mt-2 row">
                                    <div class="col-6">
                                        <?php
                                            // กำหนดตัวแปรสำหรับผู้ใช้งานพิเศษ
                                            $specialUsers = ['Pornsuk'];
                                            if (in_array($subDepart, ['CAD1', 'CAD2', 'CAM', 'Modeling', 'Design'])) {
                                                $specialUsers[] = 'Chaikorn'; // เพิ่ม Chaikorn เฉพาะกรณีแผนกที่กำหนด
                                            } else if (in_array($subDepart, ['Office', 'Store', 'AC'])) {
                                                $specialUsers[] = 'Anchana';
                                            }

                                            // ลองดึงข้อมูลผู้ใช้พิเศษก่อน
                                            $specialUserRecords = [];
                                            foreach ($specialUsers as $specialUserss) {
                                                $sqlSpecial = "SELECT * FROM employees WHERE e_username = :specialUserss AND e_workplace = :workplace";
                                                $stmt       = $conn->prepare($sqlSpecial);
                                                $stmt->bindParam(':specialUserss', $specialUserss);
                                                $stmt->bindParam(':workplace', $workplace);
                                                $stmt->execute();
                                                $specialUser = $stmt->fetch(PDO::FETCH_ASSOC);

                                                if ($specialUser) {
                                                    $specialUserRecords[] = $specialUser;
                                                }
                                            }

                                            // ดึงข้อมูลหัวหน้างานและผู้จัดการตามเงื่อนไขเดิม
                                            $sql = "SELECT *
    FROM employees
    WHERE e_level IN ('leader', 'chief', 'assisManager', 'manager', 'manager2', 'GM', 'subLeader')
    AND e_level <> :level
    AND (
        (e_sub_department IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND e_sub_department <> '')
        OR (e_sub_department2 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND e_sub_department2 <> '')
        OR (e_sub_department3 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND e_sub_department3 <> '')
        OR (e_sub_department4 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND e_sub_department4 <> '')
        OR (e_sub_department5 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND e_sub_department5 <> '')
        OR (
            e_level = 'GM'
            AND :depart <> 'RD'
            AND (
                e_sub_department IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5)
                OR e_sub_department2 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5)
                OR e_sub_department3 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5)
                OR e_sub_department4 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5)
                OR e_sub_department5 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5)
                OR (
                    e_sub_department IS NULL
                    AND e_sub_department2 IS NULL
                    AND e_sub_department3 IS NULL
                    AND e_sub_department4 IS NULL
                    AND e_sub_department5 IS NULL
                )
            )
        )
    )
    AND e_workplace = :workplace
    ORDER BY e_username ASC";

                                            $stmt = $conn->prepare($sql);
                                            $stmt->bindParam(':subDepart', $subDepart);
                                            $stmt->bindParam(':subDepart2', $subDepart2);
                                            $stmt->bindParam(':subDepart3', $subDepart3);
                                            $stmt->bindParam(':subDepart4', $subDepart4);
                                            $stmt->bindParam(':subDepart5', $subDepart5);
                                            $stmt->bindParam(':depart', $depart);
                                            $stmt->bindParam(':workplace', $workplace);
                                            $stmt->bindParam(':level', $level);
                                            // ลบ $stmt->bindParam(':userCode', $userCode); ออกเพราะไม่มีในคำสั่ง SQL

                                            $stmt->execute();
                                            $regularResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                            // กรอง regularResults ไม่ให้มีรายชื่อผู้ใช้พิเศษที่ซ้ำกัน
                                            $filteredResults = [];
                                            foreach ($regularResults as $row) {
                                                if (! in_array($row['e_username'], $specialUsers)) {
                                                    $filteredResults[] = $row;
                                                }
                                            }

                                            // รวมรายชื่อปกติกับผู้ใช้พิเศษ (ผู้ใช้พิเศษอยู่ท้ายสุด)
                                            usort($specialUserRecords, function ($a, $b) {
                                                return strcmp($a['e_username'], $b['e_username']);
                                            });

                                            // รวมรายชื่อปกติกับผู้ใช้พิเศษ (ผู้ใช้พิเศษอยู่ท้ายสุด)
                                            $filteredResults = array_merge($filteredResults, $specialUserRecords);

                                            // ตั้งค่า default approver (ไม่ต้องเปลี่ยน)
                                            $defaultApprover = '';
                                            if ($subDepart === 'RD') {
                                                foreach ($filteredResults as $row) {
                                                    if ($row['e_level'] == 'leader' || $row['e_level'] == 'manager' || $row['e_level'] == 'GM') {
                                                        $defaultApprover = $row['e_username'];
                                                        break;
                                                    }
                                                }
                                            } else {
                                                foreach ($filteredResults as $row) {
                                                    if (in_array($row['e_level'], ['chief', 'manager', 'assisManager', 'GM'])) {
                                                        $defaultApprover = $row['e_username'];
                                                        break;
                                                    }
                                                }
                                            }

                                        ?>

                                        <label for="labelApprover" class="form-label">หัวหน้าอนุมัติ</label>
                                        <span style="color: red;">* </span>
                                        <select class="form-select" id="approver" name="approver">
                                            <option value="เลือกหัวหน้า" selected>เลือกหัวหน้า</option>
                                            <?php foreach ($filteredResults as $row): ?>
                                            <option value="<?php echo htmlspecialchars($row['e_name']) ?>">
                                                <?php echo htmlspecialchars($row['e_username']) ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="mt-3 row">
                                    <div class="col-12">
                                        <label for="telPhone" class="form-label">เบอร์โทร</label>
                                        <?php
                                            $sql2    = "SELECT e_phone FROM employees WHERE e_usercode = '$userCode'";
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
                                        <label for="file" class="form-label">ไฟล์แนบ (สูงสุด 3 ไฟล์: PNG, JPG, JPEG,
                                            PDF)</label>
                                        <input class="form-control" type="file" id="file" name="file" multiple
                                            accept="image/png, image/jpeg, image/jpg, application/pdf" />
                                        <small class="text-muted">เลือกไฟล์รูปภาพหรือ PDF ได้สูงสุด 3 ไฟล์</small>
                                        <div id="filePreview" class="mt-2 d-flex flex-wrap gap-2"></div>
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
                            <form id="urgentLeaveForm" method="POST" enctype="multipart/form-data"
                                action="user_dashboard.php">
                                <div class="row">
                                    <div class="col-24 alert alert-danger d-none" role="alert" name="alertCheckDays">
                                        ไม่สามารถลาได้ คุณได้ใช้สิทธิ์ครบกำหนดแล้ว
                                    </div>
                                    <div class="col-12">
                                        <label for="urgentLeaveType" class="form-label">ประเภทการลา</label>
                                        <span class="badge rounded-pill text-bg-info" hidden>เหลือ
                                            <span id="remaining-days">0 </span> วัน
                                            <span id="remaining-hours">0 </span> ชั่วโมง
                                            <span id="remaining-minutes">0 </span> นาที
                                        </span>
                                        <span style="color: red;">*</span>
                                        <select class="form-select" id="urgentLeaveType" required>
                                            <option selected>เลือกประเภทการลา</option>
                                            <option value="1">ลากิจได้รับค่าจ้าง</option>
                                            <option value="2">ลากิจไม่ได้รับค่าจ้าง</option>
                                            <option value="5">ลาพักร้อนฉุกเฉิน</option>
                                        </select>
                                    </div>
                                    <!-- <div id="leave-balance">
                                        <p>วันเหลือ: <span id="remaining-days">0</span></p>
                                        <p>ชั่วโมงเหลือ: <span id="remaining-hours">0</span></p>
                                        <p>นาทีเหลือ: <span id="remaining-minutes">0</span></p>
                                    </div> -->
                                </div>
                                <div class="mt-3 row">
                                    <div class="col-12">
                                        <label for="urgentLeaveReason" class="form-label">เหตุผลการลา</label>
                                        <span style="color: red;">*</span>
                                        <textarea class="form-control mt-2" id="urgentLeaveReason" rows="3"
                                            placeholder="กรุณาระบุเหตุผล"></textarea>
                                    </div>
                                </div>
                                <div class="mt-3 row">
                                    <div class="col-6">
                                        <label for="urgentStartDate" class="form-label">วันที่เริ่มต้น</label>
                                        <span style="color: red;">*</span>
                                        <input type="text" class="form-control" id="urgentStartDate" required
                                            onchange="calculateLeaveDuration()">
                                    </div>
                                    <div class=" col-6">
                                        <label for="urgentStartTime" class="form-label">เวลาที่เริ่มต้น</label>
                                        <span style="color: red;">*</span>
                                        <select class="form-select" id="urgentStartTime" name="urgentStartTime" required
                                            onchange="calculateLeaveDuration()">
                                            <option value="08:00" selected>08:00</option>
                                            <option value="08:10">08:10</option>
                                            <option value="08:15">08:15</option>
                                            <option value="08:30">08:30</option>
                                            <option value="08:45">08:45</option>
                                            <option value="09:00">09:00</option>
                                            <option value="09:10">09:10</option>
                                            <option value="09:15">09:15</option>
                                            <option value="09:30">09:30</option>
                                            <option value="09:45">09:45</option>
                                            <option value="10:00">10:00</option>
                                            <option value="10:10">10:10</option>
                                            <option value="10:15">10:15</option>
                                            <option value="10:30">10:30</option>
                                            <option value="10:45">10:45</option>
                                            <option value="11:00">11:00</option>
                                            <option value="11:10">11:10</option>
                                            <option value="11:15">11:15</option>
                                            <option value="11:30">11:30</option>
                                            <option value="12:00">11:45</option>
                                            <option value="13:00">12:45</option>
                                            <option value="13:10">13:10</option>
                                            <option value="13:15">13:15</option>
                                            <option value="13:40">13:40</option>
                                            <option value="13:45">13:45</option>
                                            <option value="14:10">14:10</option>
                                            <option value="14:15">14:15</option>
                                            <option value="14:40">14:40</option>
                                            <option value="14:45">14:45</option>
                                            <option value="15:10">15:10</option>
                                            <option value="15:15">15:15</option>
                                            <option value="15:40">15:40</option>
                                            <option value="15:45">15:45</option>
                                            <option value="16:10">16:10</option>
                                            <option value="16:15">16:15</option>
                                            <option value="17:00">16:40</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mt-3 row">
                                    <div class="col-6">
                                        <label for="urgentEndDate" class="form-label">วันที่สิ้นสุด</label>
                                        <span style="color: red;">*</span>
                                        <input type="text" class="form-control" id="urgentEndDate" required
                                            onchange="calculateLeaveDuration()">
                                    </div>
                                    <div class="col-6">
                                        <label for="urgentEndTime" class="form-label">เวลาที่สิ้นสุด</label>
                                        <span style="color: red;">*</span>
                                        <select class="form-select" id="urgentEndTime" name="urgentEndTime" required
                                            onchange="calculateLeaveDuration()">
                                            <option value="08:00">08:00</option>
                                            <option value="08:10">08:10</option>
                                            <option value="08:15">08:15</option>
                                            <option value="08:30">08:30</option>
                                            <option value="08:45">08:45</option>
                                            <option value="09:00">09:00</option>
                                            <option value="09:10">09:10</option>
                                            <option value="09:15">09:15</option>
                                            <option value="09:30">09:30</option>
                                            <option value="09:45">09:45</option>
                                            <option value="10:00">10:00</option>
                                            <option value="10:10">10:10</option>
                                            <option value="10:15">10:15</option>
                                            <option value="10:30">10:30</option>
                                            <option value="10:45">10:45</option>
                                            <option value="11:00">11:00</option>
                                            <option value="11:10">11:10</option>
                                            <option value="11:15">11:15</option>
                                            <option value="11:30">11:30</option>
                                            <option value="12:00">11:45</option>
                                            <option value="13:00">12:45</option>
                                            <option value="13:10">13:10</option>
                                            <option value="13:15">13:15</option>
                                            <option value="13:40">13:40</option>
                                            <option value="13:45">13:45</option>
                                            <option value="14:10">14:10</option>
                                            <option value="14:15">14:15</option>
                                            <option value="14:40">14:40</option>
                                            <option value="14:45">14:45</option>
                                            <option value="15:10">15:10</option>
                                            <option value="15:15">15:15</option>
                                            <option value="15:40">15:40</option>
                                            <option value="15:45">15:45</option>
                                            <option value="16:10">16:10</option>
                                            <option value="16:15">16:15</option>
                                            <option value="17:00" selected>16:40</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mt-3 row">
                                    <div class="col-6">
                                        <label for="urgentLeaveDuration" class="form-label text-primary">**
                                            ระยะเวลาการลา :</label>
                                        <span id="urgentLeaveDuration" class="form-label text-primary"></span>
                                    </div>
                                </div>
                                <div class="mt-2 row">
                                    <div class="col-6">
                                        <?php
                                            // กำหนดตัวแปรสำหรับผู้ใช้งานพิเศษ
                                            $specialUsers = ['Pornsuk'];
                                            if (in_array($subDepart, ['CAD1', 'CAD2', 'CAM', 'Modeling', 'Design'])) {
                                                $specialUsers[] = 'Chaikorn'; // เพิ่ม Chaikorn เฉพาะกรณีแผนกที่กำหนด
                                            } else if (in_array($subDepart, ['Office', 'Store', 'AC'])) {
                                                $specialUsers[] = 'Anchana';
                                            }

                                            // ลองดึงข้อมูลผู้ใช้พิเศษก่อน
                                            $specialUserRecords = [];
                                            foreach ($specialUsers as $specialUserss) {
                                                $sqlSpecial = "SELECT * FROM employees WHERE e_username = :specialUserss AND e_workplace = :workplace";
                                                $stmt       = $conn->prepare($sqlSpecial);
                                                $stmt->bindParam(':specialUserss', $specialUserss);
                                                $stmt->bindParam(':workplace', $workplace);
                                                $stmt->execute();
                                                $specialUser = $stmt->fetch(PDO::FETCH_ASSOC);

                                                if ($specialUser) {
                                                    $specialUserRecords[] = $specialUser;
                                                }
                                            }

                                            // ดึงข้อมูลหัวหน้างานและผู้จัดการตามเงื่อนไขเดิม
                                            $sql = "SELECT *
    FROM employees
    WHERE e_level IN ('leader', 'chief', 'assisManager', 'manager', 'manager2', 'GM', 'subLeader')
    AND e_level <> :level
    AND (
        (e_sub_department IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND e_sub_department <> '')
        OR (e_sub_department2 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND e_sub_department2 <> '')
        OR (e_sub_department3 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND e_sub_department3 <> '')
        OR (e_sub_department4 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND e_sub_department4 <> '')
        OR (e_sub_department5 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND e_sub_department5 <> '')
        OR (
            e_level = 'GM'
            AND :depart <> 'RD'
            AND (
                e_sub_department IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5)
                OR e_sub_department2 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5)
                OR e_sub_department3 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5)
                OR e_sub_department4 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5)
                OR e_sub_department5 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5)
                OR (
                    e_sub_department IS NULL
                    AND e_sub_department2 IS NULL
                    AND e_sub_department3 IS NULL
                    AND e_sub_department4 IS NULL
                    AND e_sub_department5 IS NULL
                )
            )
        )
    )
    AND e_workplace = :workplace
    ORDER BY e_username ASC";

                                            $stmt = $conn->prepare($sql);
                                            $stmt->bindParam(':subDepart', $subDepart);
                                            $stmt->bindParam(':subDepart2', $subDepart2);
                                            $stmt->bindParam(':subDepart3', $subDepart3);
                                            $stmt->bindParam(':subDepart4', $subDepart4);
                                            $stmt->bindParam(':subDepart5', $subDepart5);
                                            $stmt->bindParam(':depart', $depart);
                                            $stmt->bindParam(':workplace', $workplace);
                                            $stmt->bindParam(':level', $level);
                                            // ลบ $stmt->bindParam(':userCode', $userCode); ออกเพราะไม่มีในคำสั่ง SQL

                                            $stmt->execute();
                                            $regularResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                            // กรอง regularResults ไม่ให้มีรายชื่อผู้ใช้พิเศษที่ซ้ำกัน
                                            $filteredResults = [];
                                            foreach ($regularResults as $row) {
                                                if (! in_array($row['e_username'], $specialUsers)) {
                                                    $filteredResults[] = $row;
                                                }
                                            }

                                            // รวมรายชื่อปกติกับผู้ใช้พิเศษ (ผู้ใช้พิเศษอยู่ท้ายสุด)
                                            usort($specialUserRecords, function ($a, $b) {
                                                return strcmp($a['e_username'], $b['e_username']);
                                            });

                                            // รวมรายชื่อปกติกับผู้ใช้พิเศษ (ผู้ใช้พิเศษอยู่ท้ายสุด)
                                            $filteredResults = array_merge($filteredResults, $specialUserRecords);

                                            // ตั้งค่า default approver (ไม่ต้องเปลี่ยน)
                                            $defaultApprover = '';
                                            if ($subDepart === 'RD') {
                                                foreach ($filteredResults as $row) {
                                                    if ($row['e_level'] == 'leader' || $row['e_level'] == 'manager' || $row['e_level'] == 'GM') {
                                                        $defaultApprover = $row['e_username'];
                                                        break;
                                                    }
                                                }
                                            } else {
                                                foreach ($filteredResults as $row) {
                                                    if (in_array($row['e_level'], ['chief', 'manager', 'assisManager', 'GM'])) {
                                                        $defaultApprover = $row['e_username'];
                                                        break;
                                                    }
                                                }
                                            }
                                        ?>

                                        <label for="labelApprover" class="form-label">หัวหน้าอนุมัติ</label>
                                        <span style="color: red;">* </span>
                                        <select class="form-select" id="urgentApprover" name="urgentApprover">
                                            <option value="เลือกหัวหน้า" selected>เลือกหัวหน้า</option>
                                            <?php foreach ($filteredResults as $row): ?>
                                            <option value="<?php echo htmlspecialchars($row['e_name']) ?>">
                                                <?php echo htmlspecialchars($row['e_username']) ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="mt-3 row">
                                    <div class="col-12">
                                        <label for="urgentTelPhone" class="form-label">เบอร์โทร</label>
                                        <?php
                                            $sql2    = "SELECT e_phone FROM employees WHERE e_usercode = '$userCode'";
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
                                        <label for="urgentFile" class="form-label">ไฟล์แนบ (PNG, JPG, JPEG, PDF)</label>
                                        <input class="form-control" type="file" id="urgentFile" name="urgentFile"
                                            multiple accept="image/png, image/jpeg, image/jpg, application/pdf" />
                                        <small class="text-muted">เลือกไฟล์รูปภาพหรือ PDF ได้สูงสุด 3 ไฟล์</small>
                                        <div id="urgentFilePreview" class="mt-2 d-flex flex-wrap gap-2"></div>
                                    </div>
                                </div>
                                <div class="mt-3 d-flex justify-content-end">
                                    <button type="submit" class="btn btn-success" id="btnSubmitForm2" name="submit"
                                        style="white-space: nowrap;">บันทึก</button>
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
                            <th rowspan="2">สถานะอนุมัติ_3</th>
                            <th rowspan="2">สถานะ (เฉพาะ HR)</th>
                            <th rowspan="2"></th>
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
                            if (! isset($_GET['page'])) {
                                $currentPage = 1;
                            } else {
                                $currentPage = $_GET['page'];
                            }

                            $sql = "SELECT * FROM leave_list WHERE l_usercode = :userCode";

                            if ($selectedMonth != "All") {
                                $sql .= " AND Month(l_leave_end_date) = :selectedMonth";
                            }

                            $sql .= " AND Year(l_leave_end_date) = :selectedYear ORDER BY l_create_datetime DESC ";

                            // Prepare the statement for counting total rows
                            $stmt = $conn->prepare($sql);

                            // Bind parameters for the count query
                            $stmt->bindParam(':userCode', $userCode);
                            $stmt->bindParam(':selectedYear', $selectedYear, PDO::PARAM_INT);

                            if ($selectedMonth != "All") {
                                $stmt->bindParam(':selectedMonth', $selectedMonth, PDO::PARAM_INT);
                            }

                            // Execute the query to get the total number of rows
                            $stmt->execute();
                            $totalRows = $stmt->rowCount();

                            // Calculate total pages
                            $totalPages = ceil($totalRows / $itemsPerPage);
                            if ($totalPages < 1) {
                                $totalPages = 1;
                            }
                            // กรณีไม่มีข้อมูล ให้มี 1 หน้า

                            // ตรวจสอบว่าหน้าปัจจุบันไม่เกินจำนวนหน้าทั้งหมด
                            if ($currentPage > $totalPages) {
                                $currentPage = $totalPages;
                            }

                            // Calculate offset for pagination
                            $offset = ($currentPage - 1) * $itemsPerPage;

                            // Add LIMIT and OFFSET to the SQL statement for pagination
                            $sqlWithPagination = $sql . " LIMIT :limit OFFSET :offset";

                            // Prepare the final query with pagination
                            $stmt = $conn->prepare($sqlWithPagination);

                            // Bind parameters for the paginated query
                            $stmt->bindParam(':userCode', $userCode);
                            $stmt->bindParam(':selectedYear', $selectedYear, PDO::PARAM_INT);

                            if ($selectedMonth != "All") {
                                $stmt->bindParam(':selectedMonth', $selectedMonth, PDO::PARAM_INT);
                            }

                            // Bind the limit and offset parameters
                            $stmt->bindParam(':limit', $itemsPerPage, PDO::PARAM_INT);
                            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

                            // Execute the paginated query
                            $stmt->execute();

                            // Display row number starting from the correct count
                            $rowNumber = $totalRows - ($currentPage - 1) * $itemsPerPage;

                            // แสดงข้อมูลในตาราง
                            if ($stmt->rowCount() > 0) {
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
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
                                        // เช็คว่ามีการลาฉุกเฉินหรือไม่
                                        if (! empty($row['l_remark']) && $row['l_remark'] === 'ลาฉุกเฉิน') {
                                            echo '<span class="text-primary">ลากิจได้รับค่าจ้างฉุกเฉิน</span><br>';
                                        } else {
                                            echo '<span class="text-primary">ลากิจได้รับค่าจ้าง</span><br>';
                                        }

                                        echo 'เหตุผล : ' . $row['l_leave_reason'];
                                    } elseif ($row['l_leave_id'] == 2) {
                                        // เช็คว่ามีการลาฉุกเฉินหรือไม่
                                        if (! empty($row['l_remark']) && $row['l_remark'] === 'ลาฉุกเฉิน') {
                                            echo '<span class="text-primary">ลากิจไม่ได้รับค่าจ้างฉุกเฉิน</span><br>';
                                        } else {
                                            echo '<span class="text-primary">ลากิจไม่ได้รับค่าจ้าง</span><br>';
                                        }

                                        echo 'เหตุผล : ' . $row['l_leave_reason'];

                                        // echo '<span class="text-primary">' . 'ลากิจไม่ได้รับค่าจ้าง' . '</span>' . '<br>' . 'เหตุผล : ' . $row['l_leave_reason'];
                                    } elseif ($row['l_leave_id'] == 3) {
                                        echo '<span class="text-primary">' . 'ลาป่วย' . '</span>' . '<br>' . 'เหตุผล : ' . $row['l_leave_reason'];
                                    } elseif ($row['l_leave_id'] == 4) {
                                        echo '<span class="text-primary">' . 'ลาป่วยจากงาน' . '</span>' . '<br>' . 'เหตุผล : ' . $row['l_leave_reason'];
                                    } elseif ($row['l_leave_id'] == 5) {
                                        // แสดงข้อความหลักว่าเป็นลาพักร้อน
                                        echo '<span class="text-primary">ลาพักร้อน</span>';

                                        // ตรวจสอบว่ามีค่า lt2_id (ข้อมูลเงื่อนไขการลาฉุกเฉิน) หรือไม่
                                        if (! empty($row['l_leave_id2'])) {
                                            // ดึงข้อมูลเงื่อนไขจากตาราง leave_type2
                                            $condition_sql  = "SELECT lt2_id,lt2_name FROM leave_type2 WHERE lt2_id = :l_leave_id2";
                                            $condition_stmt = $conn->prepare($condition_sql);
                                            $condition_stmt->bindParam(':l_leave_id2', $row['l_leave_id2'], PDO::PARAM_INT);
                                            $condition_stmt->execute();
                                            $condition_data = $condition_stmt->fetch(PDO::FETCH_ASSOC);

                                            if ($condition_data) {
                                                // แสดงว่าเป็นลาพักร้อนฉุกเฉินพร้อมเงื่อนไข
                                                // echo '<span class="text-primary">ฉุกเฉิน (' . $condition_data['lt2_name'] . ')</span>';
                                                echo '<span class="text-primary">ฉุกเฉิน</span>';

                                            }
                                        }

                                        // แสดงเหตุผลการลา
                                        echo '<br>เหตุผล : ' . $row['l_leave_reason'];
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
                                    // 08:10
                                    if ($row['l_leave_start_time'] == '08:30:00' && $row['l_time_remark'] == '08:10:00') {
                                        echo '<td>' . $row['l_leave_start_date'] . '<br> 08:10:00</td>';
                                    }
                                    // 08:15
                                    else if ($row['l_leave_start_time'] == '08:30:00' && $row['l_time_remark'] == '08:15:00') {
                                        echo '<td>' . $row['l_leave_start_date'] . '<br> 08:15:00</td>';
                                    }
                                    // 08:45
                                    else if ($row['l_leave_start_time'] == '09:00:00' && $row['l_time_remark'] == '08:45:00') {
                                        echo '<td>' . $row['l_leave_start_date'] . '<br> 08:45:00</td>';
                                    }
                                    // 09:10
                                    else if ($row['l_leave_start_time'] == '09:30:00' && $row['l_time_remark'] == '09:10:00') {
                                        echo '<td>' . $row['l_leave_start_date'] . '<br> 09:10:00</td>';
                                    }
                                    // 09:15
                                    else if ($row['l_leave_start_time'] == '09:30:00' && $row['l_time_remark'] == '09:15:00') {
                                        echo '<td>' . $row['l_leave_start_date'] . '<br> 09:15:00</td>';
                                    }
                                    // 09:45
                                    else if ($row['l_leave_start_time'] == '10:00:00' && $row['l_time_remark'] == '09:45:00') {
                                        echo '<td>' . $row['l_leave_start_date'] . '<br> 09:45:00</td>';
                                    }
                                    // 10:10
                                    else if ($row['l_leave_start_time'] == '10:30:00' && $row['l_time_remark'] == '10:10:00') {
                                        echo '<td>' . $row['l_leave_start_date'] . '<br> 10:10:00</td>';
                                    }
                                    // 10:15
                                    else if ($row['l_leave_start_time'] == '10:30:00' && $row['l_time_remark'] == '10:15:00') {
                                        echo '<td>' . $row['l_leave_start_date'] . '<br> 10:15:00</td>';
                                    }
                                    // 10:45
                                    else if ($row['l_leave_start_time'] == '11:00:00' && $row['l_time_remark'] == '10:45:00') {
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
                                    else if ($row['l_leave_start_time'] == '13:30:00' && $row['l_time_remark'] == '13:10:00') {
                                        echo '<td>' . $row['l_leave_start_date'] . '<br> 13:10:00</td>';
                                    }
                                    // 13:15
                                    else if ($row['l_leave_start_time'] == '13:30:00' && $row['l_time_remark'] == '13:15:00') {
                                        echo '<td>' . $row['l_leave_start_date'] . '<br> 13:15:00</td>';
                                    }
                                    // 13:40
                                    else if ($row['l_leave_start_time'] == '14:00:00' && $row['l_time_remark'] == '13:40:00') {
                                        echo '<td>' . $row['l_leave_start_date'] . '<br> 13:40:00</td>';
                                    }
                                    // 13:45
                                    else if ($row['l_leave_start_time'] == '14:00:00' && $row['l_time_remark'] == '13:45:00') {
                                        echo '<td>' . $row['l_leave_start_date'] . '<br> 13:45:00</td>';
                                    }
                                    // 14:10
                                    else if ($row['l_leave_start_time'] == '14:30:00' && $row['l_time_remark'] == '14:10:00') {
                                        echo '<td>' . $row['l_leave_start_date'] . '<br> 14:10:00</td>';
                                    }
                                    // 14:15
                                    else if ($row['l_leave_start_time'] == '14:30:00' && $row['l_time_remark'] == '14:15:00') {
                                        echo '<td>' . $row['l_leave_start_date'] . '<br> 14:15:00</td>';
                                    }
                                    // 14:40
                                    else if ($row['l_leave_start_time'] == '15:00:00' && $row['l_time_remark'] == '14:40:00') {
                                        echo '<td>' . $row['l_leave_start_date'] . '<br> 14:40:00</td>';
                                    }
                                    // 14:45
                                    else if ($row['l_leave_start_time'] == '15:00:00' && $row['l_time_remark'] == '14:45:00') {
                                        echo '<td>' . $row['l_leave_start_date'] . '<br> 14:45:00</td>';
                                    }
                                    // 15:10
                                    else if ($row['l_leave_start_time'] == '15:30:00' && $row['l_time_remark'] == '15:10:00') {
                                        echo '<td>' . $row['l_leave_start_date'] . '<br> 15:10:00</td>';
                                    }
                                    // 15:15
                                    else if ($row['l_leave_start_time'] == '15:30:00' && $row['l_time_remark'] == '15:15:00') {
                                        echo '<td>' . $row['l_leave_start_date'] . '<br> 15:15:00</td>';
                                    }
                                    // 15:40
                                    else if ($row['l_leave_start_time'] == '16:00:00' && $row['l_time_remark'] == '15:40:00') {
                                        echo '<td>' . $row['l_leave_start_date'] . '<br> 15:40:00</td>';
                                    }
                                    // 15:45
                                    else if ($row['l_leave_start_time'] == '16:00:00' && $row['l_time_remark'] == '15:45:00') {
                                        echo '<td>' . $row['l_leave_start_date'] . '<br> 15:45:00</td>';
                                    }
                                    // 16:10
                                    else if ($row['l_leave_start_time'] == '16:30:00' && $row['l_time_remark'] == '16:10:00') {
                                        echo '<td>' . $row['l_leave_start_date'] . '<br> 16:10:00</td>';
                                    }
                                    // 16:15
                                    else if ($row['l_leave_start_time'] == '16:30:00' && $row['l_time_remark'] == '16:15:00') {
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
                                    // 08:10
                                    if ($row['l_leave_end_time'] == '08:30:00' && $row['l_time_remark2'] == '08:10:00') {
                                        echo '<td>' . $row['l_leave_end_date'] . '<br> 08:10:00</td>';
                                    }
                                    // 08:15
                                    else if ($row['l_leave_end_time'] == '08:30:00' && $row['l_time_remark2'] == '08:15:00') {
                                        echo '<td>' . $row['l_leave_end_date'] . '<br> 08:15:00</td>';
                                    }
                                    // 08:45
                                    else if ($row['l_leave_end_time'] == '09:00:00' && $row['l_time_remark2'] == '08:45:00') {
                                        echo '<td>' . $row['l_leave_end_date'] . '<br> 08:45:00</td>';
                                    }
                                    // 09:10
                                    else if ($row['l_leave_end_time'] == '09:30:00' && $row['l_time_remark2'] == '09:10:00') {
                                        echo '<td>' . $row['l_leave_end_date'] . '<br> 09:10:00</td>';
                                    }
                                    // 09:15
                                    else if ($row['l_leave_end_time'] == '09:30:00' && $row['l_time_remark2'] == '09:15:00') {
                                        echo '<td>' . $row['l_leave_end_date'] . '<br> 09:15:00</td>';
                                    }
                                    // 09:45
                                    else if ($row['l_leave_end_time'] == '10:00:00' && $row['l_time_remark2'] == '09:45:00') {
                                        echo '<td>' . $row['l_leave_end_date'] . '<br> 09:45:00</td>';
                                    }
                                    // 10:10
                                    else if ($row['l_leave_end_time'] == '10:30:00' && $row['l_time_remark2'] == '10:10:00') {
                                        echo '<td>' . $row['l_leave_end_date'] . '<br> 10:10:00</td>';
                                    }
                                    // 10:15
                                    else if ($row['l_leave_end_time'] == '10:30:00' && $row['l_time_remark2'] == '10:15:00') {
                                        echo '<td>' . $row['l_leave_end_date'] . '<br> 10:15:00</td>';
                                    }
                                    // 10:45
                                    else if ($row['l_leave_end_time'] == '11:00:00' && $row['l_time_remark2'] == '10:45:00') {
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
                                    else if ($row['l_leave_end_time'] == '13:30:00' && $row['l_time_remark2'] == '13:10:00') {
                                        echo '<td>' . $row['l_leave_end_date'] . '<br> 13:10:00</td>';
                                    }
                                    // 13:15
                                    else if ($row['l_leave_end_time'] == '13:30:00' && $row['l_time_remark2'] == '13:15:00') {
                                        echo '<td>' . $row['l_leave_end_date'] . '<br> 13:15:00</td>';
                                    }
                                    // 13:40
                                    else if ($row['l_leave_end_time'] == '14:00:00' && $row['l_time_remark2'] == '13:40:00') {
                                        echo '<td>' . $row['l_leave_end_date'] . '<br> 13:40:00</td>';
                                    }
                                    // 13:45
                                    else if ($row['l_leave_end_time'] == '14:00:00' && $row['l_time_remark2'] == '13:45:00') {
                                        echo '<td>' . $row['l_leave_start_date'] . '<br> 13:45:00</td>';
                                    }
                                    // 14:10
                                    else if ($row['l_leave_end_time'] == '14:30:00' && $row['l_time_remark2'] == '14:10:00') {
                                        echo '<td>' . $row['l_leave_end_date'] . '<br> 14:10:00</td>';
                                    }
                                    // 14:15
                                    else if ($row['l_leave_end_time'] == '14:30:00' && $row['l_time_remark2'] == '14:15:00') {
                                        echo '<td>' . $row['l_leave_end_date'] . '<br> 14:15:00</td>';
                                    }
                                    // 14:40
                                    else if ($row['l_leave_end_time'] == '15:00:00' && $row['l_time_remark2'] == '14:40:00') {
                                        echo '<td>' . $row['l_leave_end_date'] . '<br> 14:40:00</td>';
                                    }
                                    // 14:45
                                    else if ($row['l_leave_end_time'] == '15:00:00' && $row['l_time_remark2'] == '14:45:00') {
                                        echo '<td>' . $row['l_leave_end_date'] . '<br> 14:45:00</td>';
                                    }
                                    // 15:10
                                    else if ($row['l_leave_end_time'] == '15:30:00' && $row['l_time_remark2'] == '15:10:00') {
                                        echo '<td>' . $row['l_leave_end_date'] . '<br> 15:10:00</td>';
                                    }
                                    // 15:15
                                    else if ($row['l_leave_end_time'] == '15:30:00' && $row['l_time_remark2'] == '15:15:00') {
                                        echo '<td>' . $row['l_leave_end_date'] . '<br> 15:15:00</td>';
                                    }
                                    // 15:40
                                    else if ($row['l_leave_end_time'] == '16:00:00' && $row['l_time_remark2'] == '15:40:00') {
                                        echo '<td>' . $row['l_leave_end_date'] . '<br> 15:40:00</td>';
                                    }
                                    // 15:45
                                    else if ($row['l_leave_end_time'] == '16:00:00' && $row['l_time_remark2'] == '15:45:00') {
                                        echo '<td>' . $row['l_leave_end_date'] . '<br> 15:45:00</td>';
                                    }
                                    // 16:10
                                    else if ($row['l_leave_end_time'] == '16:30:00' && $row['l_time_remark2'] == '16:10:00') {
                                        echo '<td>' . $row['l_leave_end_date'] . '<br> 16:10:00</td>';
                                    }
                                    // 16:15
                                    else if ($row['l_leave_end_time'] == '16:30:00' && $row['l_time_remark2'] == '16:15:00') {
                                        echo '<td>' . $row['l_leave_end_date'] . '<br> 16:15:00</td>';
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
                                    $holiday_data  = $holiday_stmt->fetch(PDO::FETCH_ASSOC);
                                    $holiday_count = $holiday_data['holiday_count'];
                                    // คำนวณระยะเวลาการลา
                                    $l_leave_start_date = new DateTime($row['l_leave_start_date'] . ' ' . $row['l_leave_start_time']);
                                    $l_leave_end_date   = new DateTime($row['l_leave_end_date'] . ' ' . $row['l_leave_end_time']);
                                    $interval           = $l_leave_start_date->diff($l_leave_end_date);

                                    // คำนวณจำนวนวันลา
                                    $leave_days = $interval->days - $holiday_count;

                                    // คำนวณจำนวนชั่วโมงและนาทีลา
                                    $leave_hours   = $interval->h;
                                    $leave_minutes = $interval->i;

                                    // ตรวจสอบช่วงเวลาและหักชั่วโมงตามเงื่อนไข
                                    $start_hour = (int) $l_leave_start_date->format('H');
                                    $end_hour   = (int) $l_leave_end_date->format('H');

                                    if (! ((($start_hour >= 8 && $start_hour < 12) && ($end_hour <= 12)) ||
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
                                    echo '<td>';
                                    $hasFiles  = ! empty($row['l_file']) || ! empty($row['l_file2']) || ! empty($row['l_file3']);
                                    $fileCount = 0;
                                    if (! empty($row['l_file'])) {
                                        $fileCount++;
                                    }

                                    if (! empty($row['l_file2'])) {
                                        $fileCount++;
                                    }

                                    if (! empty($row['l_file3'])) {
                                        $fileCount++;
                                    }

                                    if ($hasFiles) {
                                        // แสดงปุ่มเปิดแกลเลอรี่
                                        echo '<button id="imgBtn" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#fileGallery' . $row['l_leave_id'] . '_' . $row['l_usercode'] . '">
            <i class="fa-solid fa-file"></i> (' . $fileCount . ')
        </button>';

                                        // สร้าง Modal สำหรับแสดงแกลเลอรี่
                                        echo '<div class="modal fade" id="fileGallery' . $row['l_leave_id'] . '_' . $row['l_usercode'] . '" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">ไฟล์แนบทั้งหมด</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">';

                                        // แสดงไฟล์ทั้งหมดในแกลเลอรี่
                                        if (! empty($row['l_file'])) {
                                            $fileExt = strtolower(pathinfo($row['l_file'], PATHINFO_EXTENSION));
                                            if (in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif'])) {
                                                echo '<div class="col-md-4 mb-3">
                        <div class="card h-100">
                            <img src="../upload/' . $row['l_file'] . '" class="card-img-top img-fluid" style="max-height: 500px; object-fit: contain;">
                            <div class="card-body text-center">
                                <h6 class="card-title">ไฟล์ที่ 1</h6>
                            </div>
                        </div>
                      </div>';
                                            } else {
                                                echo '<div class="col-md-4 mb-3">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="fa-solid fa-file fa-5x mb-3"></i>
                                <h6 class="card-title">ไฟล์ที่ 1 (' . $fileExt . ')</h6>
                                <a href="../upload/' . $row['l_file'] . '" class="btn btn-sm btn-primary" target="_blank">เปิดไฟล์</a>
                            </div>
                        </div>
                      </div>';
                                            }
                                        }

                                        if (! empty($row['l_file2'])) {
                                            $fileExt = strtolower(pathinfo($row['l_file2'], PATHINFO_EXTENSION));
                                            if (in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif'])) {
                                                echo '<div class="col-md-4 mb-3">
                        <div class="card h-100">
                            <img src="../upload/' . $row['l_file2'] . '" class="card-img-top img-fluid" style="max-height: 500px; object-fit: contain;">
                            <div class="card-body text-center">
                                <h6 class="card-title">ไฟล์ที่ 2</h6>
                            </div>
                        </div>
                      </div>';
                                            } else {
                                                echo '<div class="col-md-4 mb-3">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="fa-solid fa-file fa-5x mb-3"></i>
                                <h6 class="card-title">ไฟล์ที่ 2 (' . $fileExt . ')</h6>
                                <a href="../upload/' . $row['l_file2'] . '" class="btn btn-sm btn-primary" target="_blank">เปิดไฟล์</a>
                            </div>
                        </div>
                      </div>';
                                            }
                                        }

                                        if (! empty($row['l_file3'])) {
                                            $fileExt = strtolower(pathinfo($row['l_file3'], PATHINFO_EXTENSION));
                                            if (in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif'])) {
                                                echo '<div class="col-md-4 mb-3">
                        <div class="card h-100">
                            <img src="../upload/' . $row['l_file3'] . '" class="card-img-top img-fluid" style="max-height: 500px; object-fit: contain;">
                            <div class="card-body text-center">
                                <h6 class="card-title">ไฟล์ที่ 3</h6>
                            </div>
                        </div>
                      </div>';
                                            } else {
                                                echo '<div class="col-md-4 mb-3">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="fa-solid fa-file fa-5x mb-3"></i>
                                <h6 class="card-title">ไฟล์ที่ 3 (' . $fileExt . ')</h6>
                            </div>
                        </div>
                      </div>';
                                            }
                                        }

                                        echo '</div>
              </div>

            </div>
          </div>
        </div>';
                                    } else {
                                        // ถ้าไม่มีไฟล์แนบเลย
                                        echo '<button id="imgNoBtn" class="btn btn-secondary" disabled><i class="fa-solid fa-file-excel"></i> </button>';
                                    }

                                    echo '</td>';

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
                                    } elseif ($row['l_hr_status'] == 2) {
                                        echo '<span class="text-danger"><b>ไม่ผ่าน</b></span>';
                                    } elseif ($row['l_hr_status'] == 3) {
                                        echo '';
                                    } else {
                                        echo '<span class="text-danger"><b>ไม่ผ่าน</b></span>';
                                    }
                                    echo '</td>';

                                    // 19 - Edit button section
                                    $leaveDate   = $row['l_leave_end_date'];
                                    $currentDate = date('Y-m-d');
                                    // คำนวณวันที่สิ้นสุดลาบวก 2 วัน
                                    $endDatePlus2 = date('Y-m-d', strtotime($leaveDate . ' +2 days'));

                                    // เช็คเงื่อนไขสองกรณี: เกิน 2 วัน หรือ มีการยกเลิก
                                    $disableEditButton = ($endDatePlus2 < $currentDate || $row['l_leave_status'] == 1);

                                    // ถ้าเข้าเงื่อนไข disable ปุ่ม
                                    if ($disableEditButton) {
                                        echo '<td>';
                                        echo '<button type="button" class="button-shadow btn btn-warning edit-btn" disabled><i class="fa-solid fa-pen"></i> แก้ไข</button>';
                                        echo '</td>';
                                    } else {
                                        // ถ้าไม่เข้าเงื่อนไข แสดงปุ่มแก้ไขปกติ
                                        echo '<td>';
                                        echo '<button type="button" class="button-shadow btn btn-warning edit-btn" data-createdatetime="' . $row['l_create_datetime'] . '" data-usercode="' . $userCode . '" data-bs-toggle="modal" data-bs-target="#editLeaveModal"><i class="fa-solid fa-pen"></i> แก้ไข</button>';
                                        echo '</td>';
                                    }

                                    // 20 - Cancel button section
                                    // ใช้เงื่อนไขเหมือนกันกับปุ่มแก้ไข เพื่อให้ปุ่มยกเลิกก็ไม่สามารถกดได้เมื่อเกิน 2 วัน
                                    $disableCancelButton  = ($endDatePlus2 < $currentDate || $row['l_leave_status'] == 1);
                                    $disabledConfirmCheck = ($row['l_late_datetime'] != null) ? 'disabled' : '';

                                    if ($row['l_leave_id'] == 6) {
                                        echo '<td></td>';
                                    } else if ($row['l_leave_id'] == 7) {
                                        $disabledLate = $disableCancelButton ? 'disabled' : $disabledConfirmCheck;
                                        echo '<td><button type="button" class="button-shadow btn btn-primary confirm-late-btn" data-createdatetime="' . $row['l_create_datetime'] . '" data-usercode="' . $userCode . '" ' . $disabledLate . '>ยืนยันรายการ</button></td>';
                                    } else if ($row['l_leave_id'] != 7) {
                                        $disabledCancel = $disableCancelButton ? 'disabled' : '';
                                        echo '<td><button type="button" class="button-shadow btn btn-danger cancel-leave-btn" data-leaveid="' . $row['l_leave_id'] . '" data-createdatetime="' . $row['l_create_datetime'] . '" data-usercode="' . $userCode . '" ' . $disabledCancel . '><i class="fa-solid fa-times"></i> ยกเลิกรายการ</button></td>';
                                    } else {
                                        echo '<td></td>';
                                    }

                                    echo '</tr>';
                                    $rowNumber--;
                                }
                            } else {
                                echo "<tr><td colspan='16' style='color: red;'>ไม่พบข้อมูล</td></tr>";
                            }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="container-fluid mt-3">
                <div class="row align-items-center">
                    <!-- ปุ่มเลื่อนหน้าด้านซ้าย พร้อมตัวเลือกจำนวนรายการต่อหน้า -->
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <nav aria-label="Page navigation" class="me-3">
                                <ul class="pagination mb-0">
                                    <?php if ($currentPage > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link"
                                            href="?page=1&year=<?php echo $selectedYear; ?>&month=<?php echo $selectedMonth; ?>&items=<?php echo $itemsPerPage; ?>"
                                            aria-label="First">
                                            <span aria-hidden="true">&laquo;&laquo;</span>
                                        </a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link"
                                            href="?page=<?php echo $currentPage - 1; ?>&year=<?php echo $selectedYear; ?>&month=<?php echo $selectedMonth; ?>&items=<?php echo $itemsPerPage; ?>"
                                            aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                    <?php endif; ?>

                                    <?php
                                                          // แสดงปุ่มทีละ 5 ปุ่ม
                                        $pagesToShow = 5; // จำนวนปุ่มที่ต้องการแสดง

                                        // คำนวณหน้าเริ่มต้นและหน้าสุดท้ายที่จะแสดง
                                        if ($currentPage >= 5) {
                                            // กรณีที่หน้าปัจจุบันมากกว่าหรือเท่ากับ 5 ให้แสดงแบบย้อนกลับ
                                            $startPage = min($currentPage + 2, $totalPages);
                                            $endPage   = max($startPage - 4, 1);

                                            // แสดงปุ่มแบบย้อนกลับ จากมากไปน้อย
                                            for ($i = $startPage; $i >= $endPage; $i--):
                                                $activeClass = ($i == $currentPage) ? ' active' : '';
                                            ?>
			                                    <li class="page-item<?php echo $activeClass; ?>">
			                                        <a class="page-link"
			                                            href="?page=<?php echo $i; ?>&year=<?php echo $selectedYear; ?>&month=<?php echo $selectedMonth; ?>&items=<?php echo $itemsPerPage; ?>"><?php echo $i; ?></a>
			                                    </li>
			                                    <?php endfor;
                                                    } else {
                                                        // กรณีหน้าปัจจุบันน้อยกว่า 5 ให้แสดงแบบปกติ
                                                        $endPage = min($pagesToShow, $totalPages);

                                                        // แสดงปุ่มแบบปกติ จากน้อยไปมาก
                                                        for ($i = 1; $i <= $endPage; $i++):
                                                            $activeClass = ($i == $currentPage) ? ' active' : '';
                                                        ?>
			                                    <li class="page-item<?php echo $activeClass; ?>">
			                                        <a class="page-link"
			                                            href="?page=<?php echo $i; ?>&year=<?php echo $selectedYear; ?>&month=<?php echo $selectedMonth; ?>&items=<?php echo $itemsPerPage; ?>"><?php echo $i; ?></a>
			                                    </li>
			                                    <?php endfor;
                                                    }
                                                ?>

                                    <?php if ($currentPage < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link"
                                            href="?page=<?php echo $currentPage + 1; ?>&year=<?php echo $selectedYear; ?>&month=<?php echo $selectedMonth; ?>&items=<?php echo $itemsPerPage; ?>"
                                            aria-label="Next">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link"
                                            href="?page=<?php echo $totalPages; ?>&year=<?php echo $selectedYear; ?>&month=<?php echo $selectedMonth; ?>&items=<?php echo $itemsPerPage; ?>"
                                            aria-label="Last">
                                            <span aria-hidden="true">&raquo;&raquo;</span>
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>

                            <!-- ฟอร์มสำหรับกระโดดไปยังหน้าที่ต้องการ -->
                            <div class="d-flex align-items-center me-3">
                                <form action="" method="GET" class="d-flex align-items-center"
                                    onsubmit="return validateJumpToPage()">
                                    <input type="hidden" name="year" value="<?php echo $selectedYear; ?>">
                                    <input type="hidden" name="month" value="<?php echo $selectedMonth; ?>">
                                    <input type="hidden" name="items" value="<?php echo $itemsPerPage; ?>">
                                    <label for="jumpToPage" class="me-2">ไปที่หน้า :</label>
                                    <input type="number" id="jumpToPage" name="page"
                                        class="form-control form-control-sd mx-2" min="1"
                                        max="<?php echo $totalPages; ?>" style="width: 70px;">
                                    <button type="submit" class="btn btn-sm btn-primary md-2">ไป</button>
                                </form>
                            </div>

                            <!-- แสดงรายการต่อหน้าอยู่ถัดจากปุ่มเลข -->
                            <div class="d-flex align-items-center">
                                <label for="perPage" class="me-2">จำนวนรายการ :</label>
                                <select id="perPage" class="form-select form-select-md" style="width: 80px;"
                                    onchange="changeItemsPerPage(this.value)">
                                    <option value="10"                                                                                                                                                                   <?php echo $itemsPerPage == 10 ? 'selected' : ''; ?>>10</option>
                                    <option value="25"                                                                                                                                                                   <?php echo $itemsPerPage == 25 ? 'selected' : ''; ?>>25</option>
                                    <option value="50"                                                                                                                                                                   <?php echo $itemsPerPage == 50 ? 'selected' : ''; ?>>50</option>
                                    <option value="100"                                                                                                                                                                      <?php echo $itemsPerPage == 100 ? 'selected' : ''; ?>>100
                                    </option>
                                </select>
                                <span class="ms-2">รายการต่อหน้า</span>
                            </div>
                        </div>
                    </div>

                    <!-- ข้อความแสดงรายการอยู่ด้านขวา -->
                    <div class="col-md-6 text-end">
                        <div class="pagination-info">
                            <?php if ($totalRows > 0): ?>
                            แสดงรายการที่&nbsp;<?php echo($currentPage - 1) * $itemsPerPage + 1; ?>&nbsp;-&nbsp;<?php echo min($currentPage * $itemsPerPage, $totalRows); ?>&nbsp;จากทั้งหมด&nbsp;<?php echo $totalRows; ?>&nbsp;รายการ
                            <?php else: ?>
                            ไม่พบรายการ
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="imageModal<?php echo $rowNumber ?>" tabindex="-1"
                aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">รูปภาพ</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <!-- แสดงรูปภาพ โดยเรียกใช้ชื่อฟิลด์ที่เก็บชื่อไฟล์ภาพ -->
                            <img src="../upload/<?php echo $row['Img_file'] ?>" class="img-fluid">
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
                                        <span class="badge rounded-pill text-bg-info" hidden>เหลือ
                                            <span id="remaining-days">0 </span> วัน
                                            <span id="remaining-hours">0 </span> ชั่วโมง
                                            <span id="remaining-minutes">0 </span> นาที
                                        </span>
                                        <span style="color: red;">*</span>
                                        <select class="form-select editLeaveType" required>
                                            <!-- <option selected>เลือกประเภทการลา</option>
                                            <option value="1">ลากิจได้รับค่าจ้าง</option>
                                            <option value="2">ลากิจไม่ได้รับค่าจ้าง</option>
                                            <option value="3">ลาป่วย</option>
                                            <option value="4">ลาป่วยจากงาน</option>
                                            <option value="5">ลาพักร้อน</option>
                                            <option value="8">อื่น ๆ</option> -->
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
                                        <input type="text" class="form-control" id="editLeaveStartDate" required
                                            onchange="calculateLeaveDuration()">
                                    </div>
                                    <div class=" col-6">
                                        <label for="editLeaveStartTime" class="form-label">เวลาที่เริ่มต้น</label>
                                        <!-- <span style="color: red;">* (<input class="form-label" id="editLeaveStartTime2"
                                                value="" style="border: none; width: 70px;  color: red;">เวลาเดิม)
                                        </span> -->
                                        <select class="form-select" id="editLeaveStartTime" name="editLeaveStartTime"
                                            required onchange="calculateLeaveDuration()">

                                        </select>
                                    </div>
                                </div>
                                <div class="mt-3 row">
                                    <div class="col-6">
                                        <label for="editleaveEndDate" class="form-label">วันที่สิ้นสุด</label>
                                        <span style="color: red;">*</span>
                                        <input type="text" class="form-control" id="editLeaveEndDate" required
                                            onchange="calculateLeaveDuration()">
                                    </div>
                                    <div class="col-6">
                                        <label for="editleaveEndTime" class="form-label">เวลาที่สิ้นสุด</label>
                                        <!-- <span style="color: red;">* (<input class="form-label" id="editLeaveEndTime2"
                                                value="" style="border: none; width: 70px; color: red;">เวลาเดิม)
                                        </span> -->
                                        <select class="form-select" id="editLeaveEndTime" name="editLeaveEndTime"
                                            required onchange="calculateLeaveDuration()">

                                        </select>
                                    </div>
                                </div>
                                <div class="mt-3 row">
                                    <div class="col-6">
                                        <label for="editLeaveDuration" class="form-label text-primary">**
                                            ระยะเวลาการลา :</label>
                                        <span id="editLeaveDuration" class="form-label text-primary"></span>
                                    </div>
                                </div>
                                <div class="mt-3 row">
                                    <div class="col-12">
                                        <label for="editTelPhone" class="form-label">เบอร์โทร</label>
                                        <input type="text" class="form-control" id="editTelPhone">
                                    </div>
                                </div>
                                <div class="mt-3 row">
                                    <div class="col-12">
                                        <label for="file" class="form-label">ไฟล์แนบ (สูงสุด 3 ไฟล์: PNG, JPG, JPEG,
                                            PDF)</label>
                                        <input class="form-control" type="file" id="editFile" name="editFile" multiple
                                            accept="image/png, image/jpeg, image/jpg, application/pdf" />
                                        <small class="text-muted">เลือกไฟล์รูปภาพหรือ PDF ได้สูงสุด 3 ไฟล์</small>
                                        <div id="editFilePreview" class="mt-2 d-flex flex-wrap gap-2"></div>
                                    </div>
                                </div>
                                <div class="mt-3 d-flex justify-content-end">
                                    <button type="submit" class="btn btn-success" id="btnSubmitForm3" name="submit"
                                        style="white-space: nowrap;">บันทึก</button>
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
                html: true
            })
        })

        $(document).ready(function() {

            $('.filter-card').click(function() {
                var leaveType = $(this).data('leave-id');
                var userCode = '<?php echo $userCode; ?>';
                var depart = '<?php echo $depart; ?>';
                var selectedYear =                                                                                                       <?php echo json_encode($selectedYear); ?>;
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
                    url: 'l_ajax_get_detail.php',
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
                url: 'l_ajax_get_holiday.php', // สร้างไฟล์ PHP เพื่อตรวจสอบวันหยุด
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

                    flatpickr("#editLeaveStartDate", {
                        dateFormat: "d-m-Y", // ตั้งค่าเป็น วัน/เดือน/ปี
                        // defaultDate: today, // กำหนดวันที่เริ่มต้นเป็นวันที่ปัจจุบัน
                        // minDate: today, // ห้ามเลือกวันที่ในอดีต
                        disable: response.holidays // ปิดวันที่ที่เป็นวันหยุด
                    });

                    flatpickr("#editLeaveEndDate", {
                        dateFormat: "d-m-Y", // ตั้งค่าเป็น วัน/เดือน/ปี
                        // defaultDate: today, // กำหนดวันที่สิ้นสุดเป็นวันที่ปัจจุบัน
                        // minDate: today, // ห้ามเลือกวันที่ในอดีต
                        disable: response.holidays // ปิดวันที่ที่เป็นวันหยุด
                    });
                }
            });

            $('#file').change(function() {
                const fileInput = this;
                const filePreview = $('#filePreview');
                filePreview.empty();

                // ตรวจสอบว่ามีไฟล์เกิน 3 ไฟล์หรือไม่
                if (fileInput.files.length > 3) {
                    Swal.fire({
                        title: "จำนวนไฟล์เกินกำหนด",
                        text: "กรุณาเลือกไฟล์ไม่เกิน 3 ไฟล์",
                        icon: "warning"
                    });
                    fileInput.value = ''; // ล้างค่าไฟล์ที่เลือก
                    return;
                }

                // แสดงตัวอย่างรูปภาพ
                for (let i = 0; i < fileInput.files.length; i++) {
                    const file = fileInput.files[i];
                    if (file.type.match('image.*')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            filePreview.append(`
                    <div class="position-relative">
                        <img src="${e.target.result}" class="img-thumbnail" style="height: 100px;">
                        <span class="position-absolute top-0 end-0 badge bg-primary">${i+1}</span>
                    </div>
                `);
                        }
                        reader.readAsDataURL(file);
                    }
                }
            });

            $('#urgentFile').change(function() {
                const urgentFileInput = this;
                const urgentFilePreview = $('#urgentFilePreview');
                urgentFilePreview.empty();

                // ตรวจสอบว่ามีไฟล์เกิน 3 ไฟล์หรือไม่
                if (urgentFileInput.files.length > 3) {
                    Swal.fire({
                        title: "จำนวนไฟล์เกินกำหนด",
                        text: "กรุณาเลือกไฟล์ไม่เกิน 3 ไฟล์สำหรับการลาฉุกเฉิน",
                        icon: "warning"
                    });
                    urgentFileInput.value = ''; // ล้างค่าไฟล์ที่เลือก
                    return;
                }

                // แสดงตัวอย่างไฟล์
                for (let i = 0; i < urgentFileInput.files.length; i++) {
                    const file = urgentFileInput.files[i];
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        if (file.type.match('image.*')) {
                            // แสดงตัวอย่างรูปภาพ
                            urgentFilePreview.append(`
                    <div class="position-relative">
                        <img src="${e.target.result}" class="img-thumbnail" style="height: 100px;">
                        <span class="position-absolute top-0 end-0 badge bg-primary">${i+1}</span>
                    </div>
                `);
                        } else if (file.type === 'application/pdf') {
                            // แสดงตัวอย่างไฟล์ PDF
                            urgentFilePreview.append(`
                    <div class="position-relative">
                        <div class="img-thumbnail d-flex flex-column align-items-center justify-content-center" style="height: 100px; width: 100px;">
                            <i class="fa fa-file-pdf text-danger" style="font-size: 40px;"></i>
                            <small class="text-center text-truncate" style="max-width: 90px;">${file.name}</small>
                        </div>
                        <span class="position-absolute top-0 end-0 badge bg-primary">${i+1}</span>
                    </div>
                `);
                        } else {
                            // ไฟล์ประเภทที่ไม่รองรับ
                            Swal.fire({
                                title: "รูปแบบไฟล์ไม่ถูกต้อง",
                                text: "กรุณาแนบไฟล์ที่เป็นรูปภาพ (PNG, JPG, JPEG) หรือ PDF เท่านั้น",
                                icon: "error"
                            });
                            urgentFileInput.value = ''; // ล้างค่าไฟล์ที่เลือก
                            urgentFilePreview.empty();
                            return;
                        }
                    };

                    reader.readAsDataURL(file);
                }
            });

            $('#editFile').change(function() {
                const editFileInput = this;
                const editFilePreview = $('#editFilePreview');
                editFilePreview.empty();

                // ตรวจสอบว่ามีไฟล์เกิน 3 ไฟล์หรือไม่
                if (editFileInput.files.length > 3) {
                    Swal.fire({
                        title: "จำนวนไฟล์เกินกำหนด",
                        text: "กรุณาเลือกไฟล์ไม่เกิน 3 ไฟล์สำหรับการลาฉุกเฉิน",
                        icon: "warning"
                    });
                    editFileInput.value = ''; // ล้างค่าไฟล์ที่เลือก
                    return;
                }

                // แสดงตัวอย่างไฟล์
                for (let i = 0; i < editFileInput.files.length; i++) {
                    const file = editFileInput.files[i];
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        if (file.type.match('image.*')) {
                            // แสดงตัวอย่างรูปภาพ
                            editFilePreview.append(`
                    <div class="position-relative">
                        <img src="${e.target.result}" class="img-thumbnail" style="height: 100px;">
                        <span class="position-absolute top-0 end-0 badge bg-primary">${i+1}</span>
                    </div>
                `);
                        } else if (file.type === 'application/pdf') {
                            // แสดงตัวอย่างไฟล์ PDF
                            editFilePreview.append(`
                    <div class="position-relative">
                        <div class="img-thumbnail d-flex flex-column align-items-center justify-content-center" style="height: 100px; width: 100px;">
                            <i class="fa fa-file-pdf text-danger" style="font-size: 40px;"></i>
                            <small class="text-center text-truncate" style="max-width: 90px;">${file.name}</small>
                        </div>
                        <span class="position-absolute top-0 end-0 badge bg-primary">${i+1}</span>
                    </div>
                `);
                        } else {
                            // ไฟล์ประเภทที่ไม่รองรับ
                            Swal.fire({
                                title: "รูปแบบไฟล์ไม่ถูกต้อง",

                                text: "กรุณาแนบไฟล์ที่เป็นรูปภาพ (PNG, JPG, JPEG) หรือ PDF เท่านั้น",
                                icon: "error"
                            });
                            editFileInput.value = ''; // ล้างค่าไฟล์ที่เลือก
                            editFilePreview.empty();
                            return;
                        }
                    };

                    reader.readAsDataURL(file);
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
                var approver = $('#approver').val();

                // เพิ่มข้อมูลจากฟอร์มลงใน FormData object
                fd.append('leaveType', leaveType);
                fd.append('leaveReason', leaveReason);
                fd.append('startDate', startDate);
                fd.append('startTime', startTime);
                fd.append('endDate', endDate);
                fd.append('endTime', endTime);
                fd.append('approver', approver);

                // เพิ่มส่วนนี้ 28/02/68
                if (files.length > 0) {
                    for (let i = 0; i < Math.min(files.length, 3); i++) {
                        fd.append('file' + (i + 1), files[i]);
                    }
                }
                // alert(approver)
                // เช็คลาซ้ำ
                $.ajax({
                    url: 'l_ajax_chk_leave_double.php',
                    type: 'POST',
                    data: {
                        startDate: startDate,
                        startTime: startTime,
                        endDate: endDate,
                        endTime: endTime,
                        leaveType: leaveType,
                        userCode: '<?php echo $userCode; ?>'
                    },
                    success: function(response) {
                        console.log(response);
                        if (response === 'double') {
                            Swal.fire({
                                title: "ไม่สามารถลาได้",
                                text: "พบรายการลาซ้ำในช่วงวันเวลาที่เลือก กรุณาตรวจสอบ",
                                icon: "warning",
                                confirmButtonText: "ตกลง",
                            }).then(() => {
                                return false;
                            });
                        } else {
                            // หากไม่มีการลาซ้ำ ก็ทำการส่งฟอร์มตามปกติ
                            // submitLeaveForm(fd);
                            var createDate = new Date();
                            createDate.setHours(createDate.getHours() +
                                7); // Adjust to Thai timezone (UTC+7)
                            var formattedDate = createDate.toISOString().slice(0, 19)
                                .replace('T', ' ');
                            fd.append('formattedDate', formattedDate);
                            // alert(formattedDate);


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
                            } else if (approver == 'เลือกหัวหน้า') {
                                Swal.fire({
                                    title: "ไม่สามารถลาได้",
                                    text: "กรุณาเลือกหัวหน้า",
                                    icon: "error"
                                });
                                return false;
                            } else {
                                var origStartDate = $('#startDate')
                                    .val(); // รูปแบบ dd-mm-yyyy
                                var origEndDate = $('#endDate').val(); // รูปแบบ dd-mm-yyyy
                                var startTime = $('#startTime').val(); // รูปแบบ hh:mm
                                var endTime = $('#endTime').val(); // รูปแบบ hh:mm

                                // ตรวจสอบว่าค่าวันที่และเวลามีครบหรือไม่
                                if (!origStartDate || !origEndDate || !startTime || !
                                    endTime) {
                                    Swal.fire({
                                        title: "ข้อผิดพลาด",
                                        text: "กรุณาเลือกวันที่เริ่มต้น, วันที่สิ้นสุด, เวลาเริ่มต้น และเวลาเสร็จสิ้น",
                                        icon: "error"
                                    });
                                    return false;
                                }

                                // แยกส่วนประกอบของวันที่
                                var startParts = origStartDate.split('-'); // [dd, mm, yyyy]
                                var endParts = origEndDate.split('-'); // [dd, mm, yyyy]

                                // แปลงเป็น Date object
                                var start = new Date(
                                    parseInt(startParts[2]), // ปี
                                    parseInt(startParts[1]) - 1, // เดือน (0-based)
                                    parseInt(startParts[0]), // วัน
                                    parseInt(startTime.split(':')[0]), // ชั่วโมง
                                    parseInt(startTime.split(':')[1]) // นาที
                                );

                                var end = new Date(
                                    parseInt(endParts[2]), // ปี
                                    parseInt(endParts[1]) - 1, // เดือน (0-based)
                                    parseInt(endParts[0]), // วัน
                                    parseInt(endTime.split(':')[0]), // ชั่วโมง
                                    parseInt(endTime.split(':')[1]) // นาที
                                );

                                // ตรวจสอบความถูกต้องของวันที่
                                if (isNaN(start.getTime()) || isNaN(end.getTime())) {
                                    Swal.fire({
                                        title: "ข้อผิดพลาด",
                                        text: "วันที่หรือเวลาไม่ถูกต้อง กรุณาตรวจสอบอีกครั้ง",
                                        icon: "error"
                                    });
                                    return false;
                                }

                                if (end < start) {
                                    Swal.fire({
                                        title: "ไม่สามารถลาได้",
                                        html: "กรุณาเลือกวันที่เริ่มต้นใหม่<br>เนื่องจากวันที่เริ่มต้นมากกว่าวันที่สิ้นสุด",
                                        icon: "error"
                                    });
                                    return false;
                                }

                                // คำนวณความแตกต่างของเวลาเป็นมิลลิวินาที
                                var timeDiff = end.getTime() - start.getTime();

                                // คำนวณจำนวนวันทำงาน (1 วันทำงาน = 8 ชั่วโมง)
                                var workDayInMs = 8 * 60 * 60 *
                                    1000; // 8 ชั่วโมงในหน่วยมิลลิวินาที
                                var totalWorkDays = timeDiff / workDayInMs;

                                // ปัดเศษเพื่อความแม่นยำ
                                totalWorkDays = Math.round(totalWorkDays * 100) / 100;

                                // ตรวจสอบว่าค่าติดลบหรือไม่
                                if (totalWorkDays < 0) {
                                    totalWorkDays = Math.abs(totalWorkDays);
                                }

                                // ตรวจสอบเงื่อนไขการลาป่วย
                                if (leaveType == 3) { // ประเภทการลาป่วย
                                    console.log("ตรวจสอบเงื่อนไขลาป่วย:", totalWorkDays,
                                        "วัน");
                                    if (totalWorkDays >= 13.13) { // ถ้าลาป่วยมากกว่า 3 วัน
                                        if (files.length === 0) {
                                            Swal.fire({
                                                title: "ไม่สามารถลาได้",
                                                text: "กรุณาแนบไฟล์ เนื่องจากลาป่วยมากกว่า 3 วัน",
                                                icon: "error"
                                            });
                                            return false;
                                        }
                                    }
                                }

                                for (var i = 0; i < files.length; i++) {
                                    var fileType = files[i].type;
                                    if (fileType !== 'image/jpeg' && fileType !==
                                        'image/png' && fileType !== 'image/jpg' &&
                                        fileType !== 'application/pdf') {
                                        Swal.fire({
                                            title: "รูปแบบไฟล์ไม่ถูกต้อง",
                                            text: "กรุณาแนบไฟล์ที่เป็นรูปภาพ (PNG, JPG, JPEG) หรือ PDF เท่านั้น",
                                            icon: "error"
                                        });
                                        return false;
                                    }
                                }

                                if (leaveType == 1 || leaveType == 5) {
                                    var startDate = $('#startDate').val();
                                    var parts = startDate.split('-');
                                    var formattedDate = parts[2] + '-' + parts[1] + '-' +
                                        parts[0]; // เปลี่ยนเป็น 'YYYY-MM-DD'

                                    // สร้าง Date object โดยไม่ต้องตั้งเวลา
                                    var leaveStartDate = new Date(formattedDate +
                                        'T00:00:00'); // ตั้งเวลาเป็น 00:00:00

                                    var currentDate = new Date();
                                    currentDate.setHours(0, 0, 0,
                                        0); // ตั้งเวลาเป็น 00:00:00

                                    var tomorrow = new Date();
                                    tomorrow.setDate(currentDate.getDate() +
                                        1); // วันพรุ่งนี้
                                    tomorrow.setHours(0, 0, 0, 0);

                                    console.log("leaveStartDate: " + leaveStartDate);
                                    console.log("currentDate: " + currentDate);
                                    console.log("tomorrow: " + tomorrow);

                                    // ไม่สามารถลาในวันที่ปัจจุบันได้
                                    if (leaveStartDate.getTime() === currentDate
                                        .getTime()) {
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
                                var startDate = new Date(startDateParts[2], startDateParts[
                                    1] - 1, startDateParts[
                                    0]); // ปี, เดือน (0-based), วัน
                                var endDate = new Date(endDateParts[2], endDateParts[1] - 1,
                                    endDateParts[
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
                                    // ดึงค่าจาก input
                                    var startDate = $('#startDate').val();
                                    var endDate = $('#endDate').val();

                                    // แยกส่วนวันที่
                                    var startParts = startDate.split('-');
                                    var endParts = endDate.split('-');

                                    // dd-mm-yyyy
                                    var formattedStartDate = startParts[2] + '-' +
                                        startParts[1] + '-' + startParts[
                                            0];
                                    var formattedEndDate = endParts[2] + '-' + endParts[1] +
                                        '-' + endParts[
                                            0];

                                    // console.log("Formatted Start Date: " + formattedStartDate);
                                    // console.log("Formatted End Date: " + formattedEndDate);

                                    if (startTime === '12:00') {
                                        startTime = '11:45';
                                    } else if (startTime === '13:00') {
                                        startTime = '12:45';
                                    } else if (endTime === '12:00') {
                                        endTime = '11:45';
                                    } else if (endTime === '13:00') {
                                        endTime = '12:45';
                                    } else if (endTime === '17:00') {
                                        endTime = '16:40';
                                    }

                                    let fileInfo = '';
                                    if (files.length > 0) {
                                        fileInfo = `<br>ไฟล์แนบ: ${files.length} ไฟล์`;
                                    } else {
                                        fileInfo = '<br>ไม่มีไฟล์แนบ';
                                    }

                                    let details = `
ประเภทการลา: ${$('#leaveType option:selected').text()}<br>
เหตุผลการลา: ${leaveReason}<br>
วันที่เริ่มต้น: ${startDate} เวลา ${startTime}<br>
วันที่สิ้นสุด: ${endDate} เวลา ${endTime}<br>
หัวหน้าอนุมัติ: ${$('#approver option:selected').text()}
${fileInfo}
`;

                                    Swal.fire({
                                        title: "ยืนยันการยื่นใบลา",
                                        html: details, // ใช้ HTML ในการแสดงข้อความ
                                        icon: "question",
                                        showCancelButton: true,
                                        confirmButtonText: "ยืนยัน",
                                        cancelButtonText: "ยกเลิก"
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            // ปิดการใช้งานปุ่มส่งข้อมูลและแสดงสถานะการโหลด
                                            $('#btnSubmitForm1').prop('disabled',
                                                true).html(
                                                '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> <span role="status">Loading...</span>'
                                            );

                                            // ส่งข้อมูลแบบ AJAX
                                            $.ajax({
                                                url: 'l_ajax_add_leave.php',
                                                type: 'POST',
                                                data: fd,
                                                contentType: false,
                                                processData: false,
                                                success: function(
                                                    response) {
                                                    Swal.fire({
                                                        title: "บันทึกสำเร็จ",
                                                        text: "บันทึกคำขอลาสำเร็จ",
                                                        icon: "success"
                                                    }).then(() => {
                                                        location
                                                            .reload();
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
                                                    $('#btnSubmitForm1')
                                                        .prop(
                                                            'disabled',
                                                            false)
                                                        .html(
                                                            'ยื่นใบลา'
                                                        );
                                                }
                                            });
                                        } else {
                                            Swal.fire({
                                                title: "ยกเลิก",
                                                text: "คุณได้ยกเลิกการยื่นใบลา",
                                                icon: "info"
                                            });
                                        }
                                    });
                                }
                            }
                        }
                    },
                    error: function() {
                        Swal.fire({
                            title: "เกิดข้อผิดพลาด",
                            text: "ไม่สามารถตรวจสอบการลาซ้ำได้",
                            icon: "error"
                        });
                    }
                });
            });

            // ลาฉุกเฉิน
            $('#urgentLeaveType').on('change', function() {
                var selectedValue = $(this).val();

                // ลบ select เงื่อนไขเดิม (ถ้ามี)
                $('#leaveConditionContainer').remove();

                // ถ้าเลือกลาพักร้อนฉุกเฉิน (value = 5)
                if (selectedValue == '5') {
                    // สร้าง container สำหรับ select เงื่อนไขการลา
                    var conditionHtml = `
                <div class="mt-3 row" id="leaveConditionContainer">
                    <div class="col-12">
                        <label for="leaveCondition" class="form-label">เงื่อนไขการลา</label>
                        <span style="color: red;">*</span>
                        <select class="form-select" id="leaveCondition" name="leaveCondition" required>
                            <option value="" selected>เลือกเงื่อนไขการลา</option>
                        </select>
                    </div>
                </div>
            `;

                    // แทรก HTML หลัง row ของ urgentLeaveType
                    $(this).closest('.row').after(conditionHtml);

                    // เรียกข้อมูลเงื่อนไขจาก server
                    loadLeaveConditions();
                }
            });

            // ฟังก์ชันโหลดข้อมูลเงื่อนไขการลาพักร้อนฉุกเฉิน
            function loadLeaveConditions() {
                $.ajax({
                    url: 'l_ajax_get_leave_conditions.php',
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        var selectBox = $('#leaveCondition');

                        // เพิ่มตัวเลือกจากข้อมูลที่ได้รับ
                        $.each(data, function(index, item) {
                            selectBox.append($('<option>', {
                                value: item.lt2_id,
                                text: item.lt2_name
                            }));
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching leave conditions:', error);
                        Swal.fire({
                            title: "เกิดข้อผิดพลาด",
                            text: "ไม่สามารถดึงข้อมูลเงื่อนไขการลาได้",
                            icon: "error"
                        });
                    }
                });
            }

            // ฟอร์มลาฉุกเฉิน
            $('#urgentLeaveForm').submit(function(e) {
                e.preventDefault();

                var fd = new FormData(this);

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

                var urgentLeaveType = $('#urgentLeaveType').val();
                var urgentLeaveReason = $('#urgentLeaveReason').val();
                var urgentStartDate = $('#urgentStartDate').val();
                var urgentStartTime = $('#urgentStartTime').val();
                var urgentEndDate = $('#urgentEndDate').val();
                var urgentEndTime = $('#urgentEndTime').val();
                var urgentFiles = $('#urgentFile')[0].files;
                var urgentApprover = $('#urgentApprover').val();

                var leaveCondition = '';
                var leaveConditionText = '';
                if (urgentLeaveType == '5' && $('#leaveCondition').length) {
                    leaveCondition = $('#leaveCondition').val();
                    leaveConditionText = $('#leaveCondition option:selected').text();

                    // ตรวจสอบว่าได้เลือกเงื่อนไขการลาหรือไม่
                    if (leaveCondition == '') {
                        Swal.fire({
                            title: "ไม่สามารถลาได้",
                            text: "กรุณาเลือกเงื่อนไขการลา",
                            icon: "error"
                        });
                        return false;
                    }

                    fd.append('leaveCondition', leaveCondition);
                    fd.append('leaveConditionText',
                        leaveConditionText); // เพิ่มชื่อเงื่อนไขเพื่อใช้ในการแสดงผล

                    if (leaveCondition == '1') {
                        // ตรวจสอบว่าไม่มีไฟล์แนบ
                        if (urgentFiles.length === 0) {
                            Swal.fire({
                                title: "ไม่สามารถลาได้",
                                text: "กรุณาแนบไฟล์ เนื่องจากเงื่อนไขที่เลือกจำเป็นต้องมีเอกสารยืนยัน",
                                icon: "error"
                            });
                            return false;
                        }
                    }
                }

                // และเปลี่ยนการแสดงผลในส่วน details
                let conditionDisplay = '';
                if (urgentLeaveType == '5' && leaveConditionText) {
                    conditionDisplay = `เงื่อนไขฉุกเฉิน: ${leaveConditionText}<br>`;
                }

                fd.append('urgentLeaveType', urgentLeaveType);
                fd.append('urgentLeaveReason', urgentLeaveReason);
                fd.append('urgentStartDate', urgentStartDate);
                fd.append('urgentStartTime', urgentStartTime);
                fd.append('urgentEndDate', urgentEndDate);
                fd.append('urgentEndTime', urgentEndTime);
                fd.append('urgentApprover', urgentApprover);

                // Handle up to 3 files
                if (urgentFiles.length > 0) {
                    for (var i = 0; i < Math.min(urgentFiles.length, 3); i++) {
                        fd.append('file' + (i + 1), urgentFiles[i]);
                    }
                }

                $.ajax({
                    url: 'l_ajax_chk_urgent_leave_double.php',
                    type: 'POST',
                    data: {
                        urgentStartDate: urgentStartDate,
                        urgentStartTime: urgentStartTime,
                        urgentEndDate: urgentEndDate,
                        urgentEndTime: urgentEndTime,
                        urgentLeaveType: urgentLeaveType,
                        leaveCondition: leaveCondition,
                        userCode: '<?php echo $userCode; ?>'
                    },
                    success: function(response) {
                        if (response === 'double') {
                            Swal.fire({
                                title: "ไม่สามารถลาได้",
                                text: "พบรายการลาซ้ำในช่วงวันเวลาที่เลือก กรุณาตรวจสอบ",
                                icon: "warning",
                                confirmButtonText: "ตกลง",
                            });
                            return;
                        } else if (response === 'condition4_used') {
                            Swal.fire({
                                title: "ไม่สามารถลาได้",
                                text: "คุณใช้สิทธิ์ลาพักร้อนฉุกเฉินเงื่อนไขนี้ไปแล้ว",
                                icon: "warning",
                                confirmButtonText: "ตกลง",
                            });
                            return;
                        } else {
                            var createDate = new Date();
                            createDate.setHours(createDate.getHours() +
                                7); // Adjust to Thai timezone (UTC+7)
                            var formattedDate = createDate.toISOString().slice(0, 19)
                                .replace('T', ' ');
                            fd.append('formattedDate', formattedDate);


                            if (urgentLeaveType == 'เลือกประเภทการลา') {
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
                            } else if (urgentApprover == 'เลือกหัวหน้า') {
                                Swal.fire({
                                    title: "ไม่สามารถลาได้",
                                    text: "กรุณาเลือกหัวหน้า",
                                    icon: "error"
                                });
                                return false;
                            } else {
                                var origStartDate = $('#urgentStartDate')
                                    .val(); // รูปแบบ dd-mm-yyyy
                                var origEndDate = $('#urgentEndDate')
                                    .val(); // รูปแบบ dd-mm-yyyy
                                var startTime = $('#urgentStartTime').val(); // รูปแบบ hh:mm
                                var endTime = $('#urgentEndTime').val(); // รูปแบบ hh:mm

                                // ตรวจสอบว่าค่าวันที่และเวลามีครบหรือไม่
                                if (!origStartDate || !origEndDate || !startTime || !
                                    endTime) {
                                    Swal.fire({
                                        title: "ข้อผิดพลาด",
                                        text: "กรุณาเลือกวันที่เริ่มต้น, วันที่สิ้นสุด, เวลาเริ่มต้น และเวลาเสร็จสิ้น",
                                        icon: "error"
                                    });
                                    return false;
                                }

                                // แยกส่วนประกอบของวันที่
                                var startParts = origStartDate.split('-'); // [dd, mm, yyyy]
                                var endParts = origEndDate.split('-'); // [dd, mm, yyyy]

                                // แปลงเป็น Date object
                                var start = new Date(
                                    parseInt(startParts[2]), // ปี
                                    parseInt(startParts[1]) - 1, // เดือน (0-based)
                                    parseInt(startParts[0]), // วัน
                                    parseInt(startTime.split(':')[0]), // ชั่วโมง
                                    parseInt(startTime.split(':')[1]) // นาที
                                );

                                var end = new Date(
                                    parseInt(endParts[2]), // ปี
                                    parseInt(endParts[1]) - 1, // เดือน (0-based)
                                    parseInt(endParts[0]), // วัน
                                    parseInt(endTime.split(':')[0]), // ชั่วโมง
                                    parseInt(endTime.split(':')[1]) // นาที
                                );

                                // ตรวจสอบความถูกต้องของวันที่
                                if (isNaN(start.getTime()) || isNaN(end.getTime())) {
                                    Swal.fire({
                                        title: "ข้อผิดพลาด",
                                        text: "วันที่หรือเวลาไม่ถูกต้อง กรุณาตรวจสอบอีกครั้ง",
                                        icon: "error"
                                    });
                                    return false;
                                }

                                if (end < start) {
                                    Swal.fire({
                                        title: "ไม่สามารถลาได้",
                                        html: "กรุณาเลือกวันที่เริ่มต้นใหม่<br>เนื่องจากวันที่เริ่มต้นมากกว่าวันที่สิ้นสุด",
                                        icon: "error"
                                    });
                                    return false;
                                }

                                // คำนวณความแตกต่างของเวลาเป็นมิลลิวินาที
                                var timeDiff = end.getTime() - start.getTime();

                                // คำนวณจำนวนวันทำงาน (1 วันทำงาน = 8 ชั่วโมง)
                                var workDayInMs = 8 * 60 * 60 *
                                    1000; // 8 ชั่วโมงในหน่วยมิลลิวินาที
                                var totalWorkDays = timeDiff / workDayInMs;

                                // ปัดเศษเพื่อความแม่นยำ
                                totalWorkDays = Math.round(totalWorkDays * 100) / 100;

                                // ตรวจสอบว่าค่าติดลบหรือไม่
                                if (totalWorkDays < 0) {
                                    totalWorkDays = Math.abs(totalWorkDays);
                                }

                                // ตรวจสอบเงื่อนไขการลาป่วย
                                if (urgentLeaveType == 3) { // ประเภทการลาป่วย
                                    console.log("ตรวจสอบเงื่อนไขลาป่วย:", totalWorkDays,
                                        "วัน");
                                    if (totalWorkDays >= 13.13) { // ถ้าลาป่วยมากกว่า 3 วัน
                                        if (urgentFiles.length === 0) {
                                            Swal.fire({
                                                title: "ไม่สามารถลาได้",
                                                text: "กรุณาแนบไฟล์ เนื่องจากลาป่วยมากกว่า 3 วัน",
                                                icon: "error"
                                            });
                                            return false;
                                        }
                                    }
                                }

                                for (var i = 0; i < urgentFiles.length; i++) {
                                    var fileType = urgentFiles[i].type;
                                    if (fileType !== 'image/jpeg' && fileType !==
                                        'image/png' && fileType !== 'image/jpg' &&
                                        fileType !== 'application/pdf') {
                                        Swal.fire({
                                            title: "รูปแบบไฟล์ไม่ถูกต้อง",
                                            text: "กรุณาแนบไฟล์ที่เป็นรูปภาพ (PNG, JPG, JPEG) หรือ PDF เท่านั้น",
                                            icon: "error"
                                        });
                                        return false;
                                    }
                                }
                                var checkStartDate = $('#urgentStartDate')
                                    .val();
                                var checkEndDate = $('#urgentEndDate').val();

                                var startDateParts = checkStartDate.split("-");
                                var endDateParts = checkEndDate.split("-");

                                var startDate = new Date(startDateParts[2],
                                    startDateParts[
                                        1] - 1, startDateParts[
                                        0]);
                                var endDate = new Date(endDateParts[2],
                                    endDateParts[
                                        1] - 1,
                                    endDateParts[
                                        0]);

                                // alert("Start Date:" + startDate);
                                // alert("End Date:" + endDate);

                                if (endDate < startDate) {
                                    Swal.fire({
                                        title: "ไม่สามารถลาได้",
                                        text: "กรุณาเลือกวันที่เริ่มต้นลาใหม่",
                                        icon: "error"
                                    });
                                    return false;
                                } else {
                                    var startDate = $('#urgentStartDate').val();
                                    var endDate = $('#urgentEndDate').val();

                                    // แยกส่วนวันที่
                                    var startParts = startDate.split('-');
                                    var endParts = endDate.split('-');

                                    // dd-mm-yyyy
                                    var formattedStartDate = startParts[2] +
                                        '-' +
                                        startParts[1] + '-' + startParts[
                                            0];
                                    var formattedEndDate = endParts[2] + '-' +
                                        endParts[
                                            1] +
                                        '-' + endParts[
                                            0];

                                    // console.log("Formatted Start Date: " + formattedStartDate);
                                    // console.log("Formatted End Date: " + formattedEndDate);

                                    if (startTime === '12:00') {
                                        startTime = '11:45';
                                    } else if (startTime === '13:00') {
                                        startTime = '12:45';
                                    } else if (endTime === '12:00') {
                                        endTime = '11:45';
                                    } else if (endTime === '13:00') {
                                        endTime = '12:45';
                                    } else if (endTime === '17:00') {
                                        endTime = '16:40';
                                    }

                                    // เพิ่มส่วนนี้ 28/02/68
                                    let fileInfo = '';
                                    if (urgentFiles.length > 0) {
                                        fileInfo =
                                            `<br>ไฟล์แนบ: ${urgentFiles.length} ไฟล์`;
                                    } else {
                                        fileInfo = '<br>ไม่มีไฟล์แนบ';
                                    }

                                    let details = `
ประเภทการลา: ${$('#urgentLeaveType option:selected').text()}<br>
${conditionDisplay}
เหตุผลการลา: ${urgentLeaveReason}<br>
วันที่เริ่มต้น: ${startDate} เวลา ${startTime}<br>
วันที่สิ้นสุด: ${endDate} เวลา ${endTime}<br>
หัวหน้าอนุมัติ: ${$('#urgentApprover option:selected').text()}
${fileInfo}
`;
                                    Swal.fire({
                                        title: "ยืนยันการยื่นใบลาฉุกเฉิน",
                                        html: details,
                                        icon: "question",
                                        showCancelButton: true,
                                        confirmButtonText: "ยืนยัน",
                                        cancelButtonText: "ยกเลิก"
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            $('#btnSubmitForm2').prop(
                                                'disabled',
                                                true).html(
                                                '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> <span role="status">Loading...</span>'
                                            );
                                            $.ajax({
                                                url: 'l_ajax_add_urgent_leave.php',
                                                type: 'POST',
                                                data: fd,
                                                contentType: false,
                                                processData: false,
                                                success: function(
                                                    response
                                                ) {
                                                    Swal.fire({
                                                            title: "บันทึกสำเร็จ",
                                                            text: "บันทึกลาฉุกเฉินสำเร็จ",
                                                            icon: "success"
                                                        })
                                                        .then(
                                                            () => {
                                                                location
                                                                    .reload();
                                                            }
                                                        );
                                                },
                                                error: function() {
                                                    Swal.fire({
                                                        title: "เกิดข้อผิดพลาด",
                                                        text: "ไม่สามารถบันทึกลาฉุกเฉินได้",
                                                        icon: "error"
                                                    });
                                                },
                                                complete: function() {
                                                    $('#btnSubmitForm2')
                                                        .prop(
                                                            'disabled',
                                                            false
                                                        )
                                                        .html(
                                                            'ยื่นใบลา'
                                                        );
                                                }
                                            });
                                        } else {
                                            Swal.fire({
                                                title: "ยกเลิก",
                                                text: "คุณได้ยกเลิกการยื่นใบลาฉุกเฉิน",
                                                icon: "info"
                                            });
                                        }
                                    });
                                }
                            }
                        }
                    }
                });
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
                var leaveStatus = $(rowData[13]).text();
                var workplace = "<?php echo $workplace ?>";
                var level = "<?php echo $level ?>";
                var subDepart = "<?php echo $subDepart ?>";
                var subDepart2 = "<?php echo $subDepart2 ?>";
                var subDepart3 = "<?php echo $subDepart3 ?>";
                var subDepart4 = "<?php echo $subDepart4 ?>";
                var subDepart5 = "<?php echo $subDepart5 ?>";
                var userName = "<?php echo $userName ?>";

                alert(leaveStatus)
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
                            url: 'l_ajax_delete_leave.php',
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
                                subDepart5: subDepart5,
                                level: level,
                                userName: userName

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
                            url: 'l_upd_late_time.php',
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

            function updateLeaveTypeOptions(isUrgent) {
                // ตัวเลือกสำหรับลาปกติ
                const normalLeaveOptions = `
        <option selected>เลือกประเภทการลา</option>
        <option value="1">ลากิจได้รับค่าจ้าง</option>
        <option value="2">ลากิจไม่ได้รับค่าจ้าง</option>
        <option value="3">ลาป่วย</option>
        <option value="4">ลาป่วยจากงาน</option>
        <option value="5">ลาพักร้อน</option>
        <option value="8">อื่น ๆ</option>
    `;

                // ตัวเลือกสำหรับลาฉุกเฉิน
                const urgentLeaveOptions = `
        <option selected>เลือกประเภทการลา</option>
        <option value="1">ลากิจได้รับค่าจ้าง</option>
        <option value="2">ลากิจไม่ได้รับค่าจ้าง</option>
        <option value="5">ลาพักร้อนฉุกเฉิน</option>
    `;

                // เลือกตัวเลือกที่เหมาะสมตามเงื่อนไข
                const options = isUrgent ? urgentLeaveOptions : normalLeaveOptions;

                // แทนที่ตัวเลือกในรายการ dropdown
                $('.editLeaveType').html(options);
            }
            $('.edit-btn').click(function() {
                var createDatetime = $(this).data('createdatetime');
                var userCode = $(this).data('usercode');

                // ตั้งค่า createDatetime ให้กับฟอร์ม
                $('#editLeaveForm').data('createdatetime', createDatetime);

                // สร้าง options สำหรับเวลา
                var timeOptions = `
        <option value="08:00">08:00</option>
        <option value="08:10">08:10</option>
        <option value="08:15">08:15</option>
        <option value="08:30">08:30</option>
        <option value="08:45">08:45</option>
        <option value="09:00">09:00</option>
        <option value="09:10">09:10</option>
        <option value="09:15">09:15</option>
        <option value="09:30">09:30</option>
        <option value="09:45">09:45</option>
        <option value="10:00">10:00</option>
        <option value="10:10">10:10</option>
        <option value="10:15">10:15</option>
        <option value="10:30">10:30</option>
        <option value="10:45">10:45</option>
        <option value="11:00">11:00</option>
        <option value="11:10">11:10</option>
        <option value="11:15">11:15</option>
        <option value="11:30">11:30</option>
        <option value="11:45">11:45</option>
        <option value="12:45">12:45</option>
        <option value="13:10">13:10</option>
        <option value="13:15">13:15</option>
        <option value="13:40">13:40</option>
        <option value="13:45">13:45</option>
        <option value="14:10">14:10</option>
        <option value="14:15">14:15</option>
        <option value="14:40">14:40</option>
        <option value="14:45">14:45</option>
        <option value="15:10">15:10</option>
        <option value="15:15">15:15</option>
        <option value="15:40">15:40</option>
        <option value="15:45">15:45</option>
        <option value="16:10">16:10</option>
        <option value="16:15">16:15</option>
        <option value="16:40">16:40</option>
    `;

                // ใส่ options เข้าไปใน dropdown
                $('#editLeaveStartTime').html(timeOptions);
                $('#editLeaveEndTime').html(timeOptions);

                // ฟังก์ชันสำหรับแปลงวันที่จากรูปแบบ YYYY-MM-DD เป็น DD-MM-YYYY (สำหรับแสดงผล)
                function formatDateForDisplay(dateStr) {
                    if (dateStr && typeof dateStr === 'string') {
                        // ตรวจสอบว่าเป็นรูปแบบ YYYY-MM-DD หรือไม่
                        if (/^\d{4}-\d{2}-\d{2}$/.test(dateStr)) {
                            const parts = dateStr.split('-');
                            return `${parts[2]}-${parts[1]}-${parts[0]}`; // แปลงเป็น DD-MM-YYYY
                        }
                    }
                    return dateStr; // คืนค่าเดิมถ้าไม่ใช่รูปแบบที่คาดหวัง
                }

                // แก้ไขส่วนที่กำหนดค่าวันที่ในฟอร์ม (ในส่วน Ajax success)
                $.ajax({
                    url: 'l_ajax_get_leave.php',
                    type: 'POST',
                    data: {
                        createDatetime: createDatetime,
                        userCode: userCode
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.error) {
                            alert(response.error);
                        } else {
                            // ตรวจสอบว่าเป็นการลาฉุกเฉินหรือไม่
                            const isUrgentLeave = response.l_remark && response.l_remark
                                .includes('ลาฉุกเฉิน');

                            // อัปเดตตัวเลือกประเภทการลาตามเงื่อนไข
                            updateLeaveTypeOptions(isUrgentLeave);

                            // ใส่ข้อมูลในฟอร์ม Modal
                            $('.editLeaveType').val(response.l_leave_id);
                            $('#editLeaveReason').val(response.l_leave_reason);

                            // แปลงรูปแบบวันที่ก่อนแสดงผล (ถ้ามีฟังก์ชันนี้)
                            const startDateFormatted = formatDateForDisplay ?
                                formatDateForDisplay(response.l_leave_start_date) : response
                                .l_leave_start_date;
                            const endDateFormatted = formatDateForDisplay ?
                                formatDateForDisplay(response.l_leave_end_date) : response
                                .l_leave_end_date;

                            // แสดงวันที่ในรูปแบบที่เหมาะสม
                            $('#editLeaveStartDate').val(startDateFormatted);
                            $('#editLeaveEndDate').val(endDateFormatted);
                            $('#editTelPhone').val(response.l_phone);

                            // แสดงหรือซ่อน badge ข้อมูลวันลาคงเหลือ
                            // if (isUrgentLeave) {
                            //     $('.badge.rounded-pill.text-bg-info').removeAttr('hidden');
                            // } else {
                            //     $('.badge.rounded-pill.text-bg-info').removeAttr('hidden');
                            // }

                            // ส่วนโค้ดที่เหลือยังคงเหมือนเดิม...
                            // กำหนดค่า select สำหรับเวลาเริ่มต้น
                            var startTimeValue = "08:00"; // ค่าเริ่มต้น

                            if (response.l_time_remark && response.l_time_remark.trim() !==
                                "") {
                                startTimeValue = response.l_time_remark.substring(0, 5);
                            } else if (response.l_leave_start_time) {
                                // กำหนดเวลาตามเงื่อนไขที่มีอยู่เดิม
                                if (response.l_leave_start_time === "12:00:00") {
                                    startTimeValue = "11:45";
                                } else if (response.l_leave_start_time === "13:00:00") {
                                    startTimeValue = "12:45";
                                } else if (response.l_leave_start_time === "17:00:00") {
                                    startTimeValue = "16:40";
                                } else {
                                    startTimeValue = response.l_leave_start_time.substring(
                                        0, 5);
                                }
                            }

                            // เลือกเวลาเริ่มต้น
                            $('#editLeaveStartTime').val(startTimeValue);

                            // กำหนดค่าเวลาสิ้นสุด (โค้ดเดิม)
                            var endTimeValue = "17:00"; // ค่าเริ่มต้น

                            if (response.l_time_remark2 && response.l_time_remark2
                                .trim() !== "") {
                                endTimeValue = response.l_time_remark2.substring(0, 5);
                            } else if (response.l_leave_end_time) {
                                // กำหนดเวลาตามเงื่อนไขที่มีอยู่เดิม
                                if (response.l_leave_end_time === "12:00:00") {
                                    endTimeValue = "11:45";
                                } else if (response.l_leave_end_time === "13:00:00") {
                                    endTimeValue = "12:45";
                                } else if (response.l_leave_end_time === "17:00:00") {
                                    endTimeValue = "16:40";
                                } else {
                                    endTimeValue = response.l_leave_end_time.substring(0,
                                        5);
                                }
                            }

                            // เลือกเวลาสิ้นสุด
                            $('#editLeaveEndTime').val(endTimeValue);

                            // จัดการไฟล์เดิม (โค้ดเดิม)
                            var existingFile = response.l_file;
                            if (existingFile && existingFile.trim() !== "") {
                                var fileUrl = '../upload/' + existingFile;
                                var previewImage = $('#imagePreview');
                                previewImage.attr('src', fileUrl);
                                previewImage.show();
                            } else {
                                $('#imagePreview').hide();
                            }

                            // คำนวณระยะเวลาการลา
                            setTimeout(calculateLeaveDuration, 100);
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('ไม่สามารถดึงข้อมูลการลาได้');
                        console.log(xhr.responseText);
                    }
                });
            });

            // ฟังก์ชันสำหรับตั้งค่า dropdown ตามเวลาที่ได้รับ
            function setTimeDropdown(selector, timeValue) {
                // ตัดส่วน :00 ด้านหลังออก
                var timeStr = timeValue.substring(0, 5);

                // สร้าง mapping ของเวลาใน response กับ value ใน dropdown
                var timeMapping = {
                    "08:30": "08:30",
                    "09:00": "09:00",
                    "09:30": "09:30",
                    "10:00": "10:00",
                    "10:30": "10:30",
                    "11:00": "11:00",
                    "12:00": "12:00",
                    "13:00": "13:00",
                    "13:30": "13:30",
                    "14:00": "14:00",
                    "14:30": "14:30",
                    "15:00": "15:00",
                    "15:30": "15:30",
                    "16:00": "16:00",
                    "16:30": "16:30",
                    "17:00": "17:00"
                };

                // ถ้าเวลาที่ได้มาอยู่ใน mapping ให้ใช้ค่านั้น
                var valueToSelect = timeMapping[timeStr] || timeStr;

                // ตั้งค่า dropdown
                $(selector).val(valueToSelect);

                // ถ้าไม่พบค่าที่ตรงกัน (val ไม่ทำงาน) ให้หาและตั้งค่า selected ด้วย attr
                if ($(selector).val() !== valueToSelect) {
                    $(selector + ' option').each(function() {
                        if ($(this).val() === valueToSelect) {
                            $(this).prop('selected', true);
                            return false; // หยุด loop เมื่อเจอค่าที่ต้องการ
                        }
                    });
                }
            }

            $('#editLeaveModal').on('shown.bs.modal', function() {
                // เมื่อ modal แสดงแล้ว ให้คำนวณระยะเวลาการลา
                setTimeout(calculateLeaveDuration, 500);
            });

            $('#editLeaveStartDate').on('change', function() {
                let startDateValue = $(this).val();
                // ตรวจสอบว่ามีการเลือกวันที่เริ่มต้นหรือไม่
                if (startDateValue) {
                    console.log('วันที่เริ่มต้นเปลี่ยนเป็น:', startDateValue);
                    // ตั้งค่าวันที่สิ้นสุดให้เป็นวันเดียวกับวันที่เริ่มต้น
                    $('#editLeaveEndDate').val(startDateValue);
                    // คำนวณระยะเวลาการลาใหม่
                    calculateLeaveDuration();
                }
            });

            $('.editLeaveType').change(function() {
                calculateLeaveDuration();
            });


            // Update form validation before submit
            $('#editLeaveForm').on('submit', function(e) {
                e.preventDefault();
                console.log("Edit form submitted, validating");

                // Additional validation before submitting
                var startDate = new Date($('#editLeaveStartDate').val());
                var endDate = new Date($('#editLeaveEndDate').val());

                if (endDate < startDate) {
                    Swal.fire({
                        title: 'ไม่สามารถลาได้',
                        text: 'กรุณาเลือกวันที่เริ่มต้นลาใหม่',
                        icon: 'error',
                        confirmButtonText: 'ตกลง',
                    });
                    return false;
                }

                // Check if button is disabled (which means validation failed)
                if ($('#btnSubmitForm3').prop('disabled')) {
                    Swal.fire({
                        title: 'ไม่สามารถลาได้',
                        text: 'จำนวนวันลาเกินกว่าจำนวนวันลาคงเหลือที่ใช้ได้',
                        icon: 'error',
                        confirmButtonText: 'ตกลง',
                    });
                    return false;
                }

                // Continue with existing form submission...
                var formData = new FormData();
                var files = $('#editFile')[0].files;

                if (files.length > 0) {
                    for (let i = 0; i < Math.min(files.length, 3); i++) {
                        formData.append('file' + (i + 1), files[i]);
                    }
                }

                // Add other form values
                formData.append('userCode', '<?php echo $userCode; ?>');
                formData.append('userName', '<?php echo $userName ?>');
                formData.append('name', '<?php echo $name ?>');
                formData.append('workplace', '<?php echo $workplace; ?>');
                formData.append('depart', '<?php echo $depart; ?>');
                formData.append('subDepart', '<?php echo $subDepart; ?>');
                formData.append('subDepart2', '<?php echo $subDepart2; ?>');
                formData.append('subDepart3', '<?php echo $subDepart3; ?>');
                formData.append('subDepart4', '<?php echo $subDepart4; ?>');
                formData.append('subDepart5', '<?php echo $subDepart5; ?>');
                formData.append('level', '<?php echo $level; ?>');
                formData.append('createDatetime', $(this).data('createdatetime'));
                formData.append('editLeaveType', $('.editLeaveType').val());
                formData.append('editLeaveReason', $('#editLeaveReason').val());
                formData.append('editLeaveStartDate', $('#editLeaveStartDate').val());
                formData.append('editLeaveStartTime', $('#editLeaveStartTime').val());
                formData.append('editLeaveEndDate', $('#editLeaveEndDate').val());
                formData.append('editLeaveEndTime', $('#editLeaveEndTime').val());
                formData.append('editTelPhone', $('#editTelPhone').val());

                console.log("Form validation passed, submitting data");

                // Send via AJAX
                $.ajax({
                    url: 'l_upd_leave.php',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    success: function(response) {
                        console.log('Response received:', response);

                        if (response.status === 'success') {
                            Swal.fire({
                                title: 'สำเร็จ!',
                                text: 'แก้ไขข้อมูลเรียบร้อยแล้ว',
                                icon: 'success',
                                confirmButtonText: 'ตกลง',
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'เกิดข้อผิดพลาด',
                                text: response.message || 'ไม่สามารถแก้ไขข้อมูลได้',
                                icon: 'error',
                                confirmButtonText: 'ตกลง',
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX error:', {
                            xhr: xhr,
                            status: status,
                            error: error
                        });
                        console.log('Response text:', xhr.responseText);

                        Swal.fire({
                            title: 'เกิดข้อผิดพลาด',
                            text: 'ไม่สามารถแก้ไขข้อมูลได้ - ' + error,
                            icon: 'error',
                            confirmButtonText: 'ตกลง',
                        });
                    }
                });
            });
        });

        // ฟังก์ชันสำหรับจัดรูปแบบวันที่
        function formatDate(dateStr) {
            if (!dateStr) return "";

            // ถ้าเป็นรูปแบบ DD-MM-YYYY ให้แปลงเป็น YYYY-MM-DD
            if (/^\d{2}-\d{2}-\d{4}$/.test(dateStr)) {
                const parts = dateStr.split('-');
                return `${parts[2]}-${parts[1]}-${parts[0]}`; // แปลงจาก DD-MM-YYYY เป็น YYYY-MM-DD
            }

            // ถ้าเป็นรูปแบบ YYYY-MM-DD คืนค่าเดิม
            if (/^\d{4}-\d{2}-\d{2}$/.test(dateStr)) {
                return dateStr;
            }

            // ถ้าไม่ตรงกับรูปแบบใดเลย แสดงข้อความเตือนและคืนค่า string เปล่า
            console.error("รูปแบบวันที่ไม่ถูกต้อง:", dateStr);
            return "";
        }

        function calculateLeaveDuration() {
            // Check which form is active (regular, urgent, or edit)
            const isUrgent = document.getElementById('urgentLeaveModal') && document.getElementById('urgentLeaveModal')
                .classList.contains('show');
            const isEdit = document.getElementById('editLeaveModal') && document.getElementById('editLeaveModal')
                .classList.contains('show');

            // Select data from the active form
            let startDate, startTime, endDate, endTime, targetElement, submitButton, currentLeaveType;

            if (isEdit) {
                startDate = document.getElementById('editLeaveStartDate').value;
                startTime = document.getElementById('editLeaveStartTime').value;
                endDate = document.getElementById('editLeaveEndDate').value;
                endTime = document.getElementById('editLeaveEndTime').value;
                targetElement = document.getElementById(
                    'editLeaveDuration');
                submitButton = document.getElementById('btnSubmitForm3');
                currentLeaveType = $('.editLeaveType').val();
            } else if (isUrgent) {
                // Handle Urgent Leave Form (existing code)
                startDate = document.getElementById('urgentStartDate').value;
                startTime = document.getElementById('urgentStartTime').value;
                endDate = document.getElementById('urgentEndDate').value;
                endTime = document.getElementById('urgentEndTime').value;
                targetElement = document.getElementById('urgentLeaveDuration');
                submitButton = document.getElementById('btnSubmitForm2');
                currentLeaveType = document.getElementById('urgentLeaveType').value;
            } else {
                // Handle Regular Leave Form (existing code)
                startDate = document.getElementById('startDate').value;
                startTime = document.getElementById('startTime').value;
                endDate = document.getElementById('endDate').value;
                endTime = document.getElementById('endTime').value;
                targetElement = document.getElementById('leaveDuration');
                submitButton = document.getElementById('btnSubmitForm1');
                currentLeaveType = document.getElementById('leaveType').value;
            }

            if (!startDate || !startTime || !endDate || !endTime) {
                // console.log("ข้อมูลวันที่หรือเวลาไม่ครบ กำหนดค่าเริ่มต้นเป็น 1 วัน");
                targetElement.textContent = "1 วัน 0 ชั่วโมง 0 นาที"; // กำหนดค่าเริ่มต้นเป็น 1 วัน
                return;
            }

            // Format dates
            const formattedStartDate = formatDate(startDate);
            const formattedEndDate = formatDate(endDate);

            let startDateTime = new Date(`${formattedStartDate}T${startTime}:00`);
            let endDateTime = new Date(`${formattedEndDate}T${endTime}:00`);

            const startMinutes = startDateTime.getMinutes();
            if (startMinutes > 0 && startMinutes <= 15) {
                startDateTime.setMinutes(30); // ปัดเป็น 30 นาที
            } else if (startMinutes > 15 && startMinutes < 30) {
                startDateTime.setMinutes(30); // ปัดเป็น 30 นาที
            } else if (startMinutes > 30 && startMinutes <= 45) {
                startDateTime.setMinutes(0); // ปัดเป็นชั่วโมงถัดไป
                startDateTime.setHours(startDateTime.getHours() + 1);
            } else if (startMinutes > 45) {
                startDateTime.setMinutes(0); // ปัดเป็นชั่วโมงถัดไป
                startDateTime.setHours(startDateTime.getHours() + 1);
            }

            const endMinutes = endDateTime.getMinutes();
            if (endMinutes > 0 && endMinutes <= 15) {
                endDateTime.setMinutes(30); // ปัดเป็น 30 นาที
            } else if (endMinutes > 15 && endMinutes < 30) {
                endDateTime.setMinutes(30); // ปัดเป็น 30 นาที
            } else if (endMinutes > 30 && endMinutes <= 45) {
                endDateTime.setMinutes(0); // ปัดเป็นชั่วโมงถัดไป
                endDateTime.setHours(endDateTime.getHours() + 1);
            } else if (endMinutes > 45) {
                endDateTime.setMinutes(0); // ปัดเป็นชั่วโมงถัดไป
                endDateTime.setHours(endDateTime.getHours() + 1);
            }

            let holidays = [];
            $.ajax({
                url: 'l_ajax_get_holiday.php',
                async: false,
                success: function(response) {
                    holidays = JSON.parse(response).holidays;
                    // console.log("ดึงข้อมูลวันหยุดสำเร็จ:", holidays);
                },
                error: function(error) {
                    console.error("เกิดข้อผิดพลาดในการดึงข้อมูลวันหยุด:", error);
                }
            });

            // ฟังก์ชันเช็คว่าเป็นวันหยุดหรือไม่
            function isHoliday(date) {
                let formattedDate = formatDateToHoliday(date);
                return holidays.includes(formattedDate);
            }

            // ฟังก์ชันแปลงวันที่ให้ตรงกับรูปแบบวันหยุด
            function formatDateToHoliday(date) {
                // Ensure date is a string
                if (typeof date !== 'string') {
                    // Convert the Date object to a string if necessary
                    date = date.toISOString().split('T')[0]; // Formats as "YYYY-MM-DD"
                }

                let [year, month, day] = date.split("-"); // Split the date string
                return `${day}-${month}-${year}`; // Return in the "DD-MM-YYYY" format
            }

            // ตรวจสอบความถูกต้องของวันที่และเวลา
            if (endDateTime < startDateTime) {
                // console.log("วันเวลาสิ้นสุดน้อยกว่าวันเวลาเริ่มต้น");
                targetElement.textContent = "";
                return;
            }

            // การคำนวณเวลาทำงาน
            const workStart = 8; // 8:00 AM
            const workEnd = 17; // 5:00 PM
            const lunchStart = 12; // 12:00 PM
            const lunchEnd = 13; // 1:00 PM

            let totalMilliseconds = 0;

            // วนลูปคำนวณระยะเวลาในแต่ละวัน
            let currentDate = new Date(startDateTime);
            // console.log("เริ่มคำนวณระยะเวลาวันต่อวัน...");

            while (currentDate < endDateTime) {
                // ปรับเวลาเริ่มต้นให้อยู่ในช่วงเวลาทำงาน
                if (currentDate.getHours() < workStart) {
                    currentDate.setHours(workStart, 0, 0, 0);
                }

                // ถ้าเลยเวลาทำงาน ให้เริ่มวันใหม่
                if (currentDate.getHours() >= workEnd) {
                    currentDate.setDate(currentDate.getDate() + 1);
                    currentDate.setHours(workStart, 0, 0, 0);
                    continue;
                }

                // กำหนดเวลาสิ้นสุดของวันทำงาน
                const currentWorkEnd = new Date(currentDate);
                currentWorkEnd.setHours(workEnd, 0, 0, 0);

                // ใช้เวลาสิ้นสุดที่เร็วกว่าระหว่างเวลาสิ้นสุดของวันทำงานและเวลาสิ้นสุดการลา
                const effectiveEndTime = endDateTime < currentWorkEnd ? endDateTime : currentWorkEnd;

                // คำนวณระยะเวลาในวันนี้
                let dailyDuration = effectiveEndTime - currentDate;

                // console.log(
                //     `วันที่: ${currentDate.toDateString()}, ระยะเวลาเบื้องต้น: ${dailyDuration / (1000 * 60 * 60)} ชั่วโมง`
                // );

                // หักเวลาพักเที่ยงถ้าจำเป็น
                const lunchStartTime = new Date(currentDate);
                lunchStartTime.setHours(lunchStart, 0, 0, 0);
                const lunchEndTime = new Date(currentDate);
                lunchEndTime.setHours(lunchEnd, 0, 0, 0);

                if (currentDate < lunchEndTime && effectiveEndTime > lunchStartTime) {
                    dailyDuration -= 60 * 60 * 1000; // หัก 1 ชั่วโมง
                    // console.log("หักเวลาพักเที่ยง 1 ชั่วโมง");
                }

                // หักวันหยุด
                if (isHoliday(currentDate)) {
                    // console.log(`${currentDate.toDateString()} เป็นวันหยุด ไม่นับเวลา`);
                    dailyDuration = 0; // ถ้าเป็นวันหยุด ไม่ต้องนับวันนั้น
                }

                totalMilliseconds += dailyDuration;
                // console.log(`ระยะเวลาสะสม: ${totalMilliseconds / (1000 * 60 * 60)} ชั่วโมง`);

                // เตรียมสำหรับวันถัดไป
                currentDate.setDate(currentDate.getDate() + 1);
                currentDate.setHours(workStart, 0, 0, 0);
            }

            // แปลงมิลลิวินาทีเป็นวัน ชั่วโมง นาที
            const millisecondsPerDay = 8 * 60 * 60 * 1000;
            const leaveDays = Math.floor(totalMilliseconds / millisecondsPerDay);
            totalMilliseconds %= millisecondsPerDay;

            const millisecondsPerHour = 60 * 60 * 1000;
            const leaveHours = Math.floor(totalMilliseconds / millisecondsPerHour);
            totalMilliseconds %= millisecondsPerHour;

            const millisecondsPerMinute = 60 * 1000;
            let leaveMinutes = Math.floor(totalMilliseconds / millisecondsPerMinute);

            // แสดงผลลัพธ์ทันทีหลังคำนวณ
            // console.log(`ผลการคำนวณ: ${leaveDays} วัน ${leaveHours} ชั่วโมง ${leaveMinutes} นาที`);

            // แสดงผลลัพธ์ทันทีสำหรับทั้งฟอร์มปกติและฉุกเฉิน
            targetElement.textContent = `${leaveDays} วัน ${leaveHours} ชั่วโมง ${leaveMinutes} นาที`;

            // Check remaining leave days for validation
            var userCode = '<?php echo $userCode; ?>'; // Value from PHP
            var selectedDate = isEdit ? document.getElementById('editLeaveStartDate').value :
                (isUrgent ? document.getElementById('urgentStartDate').value :
                    document.getElementById('startDate').value);

            // Enable submit button if dates and times are selected
            if (startDate && startTime && endDate && endTime && submitButton) {
                submitButton.disabled = false;
            }

            if (selectedDate && currentLeaveType && currentLeaveType != 'เลือกประเภทการลา') {
                var parts = selectedDate.split('-'); // Split date from DD-MM-YYYY format
                if (parts.length === 3) {
                    var selectedDay = parseInt(parts[0]); // Get day
                    var selectedMonth = parseInt(parts[1]); // Get month
                    var selectedYear = parseInt(parts[2]); // Get year

                    // Determine which leave type to send based on active form
                    const requestLeaveType = isEdit ? $('.editLeaveType').val() :
                        (isUrgent ? document.getElementById('urgentLeaveType').value :
                            document.getElementById('leaveType').value);

                    // Update remaining days display in edit form
                    if (isEdit) {
                        // Reset remaining days display first
                        $('#remaining-days').text('0');
                        $('#remaining-hours').text('0');
                        $('#remaining-minutes').text('0');
                    }

                    $.ajax({
                        url: 'l_ajax_get_leave_balance.php',
                        type: 'POST',
                        data: {
                            leaveType: requestLeaveType,
                            userCode: userCode,
                            selectedYear: selectedYear
                        },
                        success: function(response) {
                            try {
                                // Parse response if it's a string
                                if (typeof response === 'string') {
                                    response = JSON.parse(response);
                                }

                                if (response && response.remaining_days !== undefined &&
                                    response.remaining_hours !== undefined &&
                                    response.remaining_minutes !== undefined) {

                                    const remainingDays = response.remaining_days;
                                    const remainingHours = response.remaining_hours;
                                    const remainingMinutes = response.remaining_minutes;

                                    // Update remaining days display in edit form
                                    if (isEdit) {
                                        $('#remaining-days').text(remainingDays);
                                        $('#remaining-hours').text(remainingHours);
                                        $('#remaining-minutes').text(remainingMinutes);
                                    }

                                    // 1 day = 8 hours = 480 minutes
                                    const leaveTotalMinutes = (leaveDays * 8 * 60) + (leaveHours * 60) +
                                        leaveMinutes;
                                    const remainingTotalMinutes = (remainingDays * 8 * 60) + (
                                        remainingHours * 60) + remainingMinutes;

                                    if (leaveTotalMinutes > remainingTotalMinutes) {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'ไม่สามารถลาได้',
                                            html: 'จำนวนวันลาเกินกว่าจำนวนวันลาคงเหลือที่ใช้ได้<br>' +
                                                'ลาน้อยกว่าหรือเท่ากับ ' +
                                                remainingDays + ' วัน ' +
                                                remainingHours + ' ชั่วโมง ' +
                                                remainingMinutes + ' นาที',
                                            confirmButtonText: 'ตกลง'
                                        });
                                        if (targetElement) {
                                            targetElement.textContent =
                                                `${leaveDays} วัน ${leaveHours} ชั่วโมง ${leaveMinutes} นาที`;
                                        }
                                        if (submitButton) {
                                            submitButton.disabled = true; // Disable submit button
                                        }
                                    } else {
                                        if (targetElement) {
                                            targetElement.textContent =
                                                `${leaveDays} วัน ${leaveHours} ชั่วโมง ${leaveMinutes} นาที`;
                                        }
                                        if (submitButton) {
                                            submitButton.disabled = false; // Enable submit button
                                        }
                                    }
                                } else {
                                    console.error('Incomplete remaining leave days data', response);
                                    alert('ข้อมูลวันลาคงเหลือไม่สมบูรณ์');
                                }
                            } catch (e) {
                                console.error('Error parsing response:', e, response);
                                alert('เกิดข้อผิดพลาดในการประมวลผลข้อมูลวันลาคงเหลือ');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("AJAX error:", status, error);
                            alert('เกิดข้อผิดพลาดในการดึงข้อมูลวันลาคงเหลือ');
                        }
                    });
                } else {
                    console.error("Invalid date format: " + selectedDate);
                }
            }
        }

        // เพิ่ม Event listener สำหรับการเปิดฟอร์มฉุกเฉิน
        document.addEventListener('DOMContentLoaded', function() {
            // ตรวจสอบว่ามี element จริงๆ
            const leaveDurationElement = document.getElementById('leaveDuration');
            if (leaveDurationElement) {
                console.log('พบ element leaveDuration');
                // เรียกฟังก์ชันคำนวณ
                calculateLeaveDuration();
            } else {
                console.error('ไม่พบ element leaveDuration');
            }

            const urgentLeaveDurationElement = document.getElementById('urgentLeaveDuration');
            if (urgentLeaveDurationElement) {
                console.log('พบ element urgentLeaveDuration');
                // เรียกฟังก์ชันคำนวณ
                calculateLeaveDuration();
            } else {
                console.error('ไม่พบ element urgentLeaveDuration');
            }

            // เพิ่ม Event listener สำหรับการเปิดฟอร์มฉุกเฉิน
            const urgentLeaveModal = document.getElementById('urgentLeaveModal');
            if (urgentLeaveModal) {
                // ใช้ MutationObserver เพื่อตรวจจับการเปลี่ยนแปลงของ class บน modal
                const observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.attributeName === 'class') {
                            if (urgentLeaveModal.classList.contains('show')) {
                                console.log(
                                    'ฟอร์มฉุกเฉินถูกเปิด - เริ่มคำนวณระยะเวลาการลา'
                                );

                                // คำนวณระยะเวลาการลาเท่านั้น โดยไม่ตรวจสอบประเภทการลาเมื่อเพิ่งเปิด Modal
                                const isModalJustOpened = true;

                                // เฉพาะคำนวณแสดงผลระยะเวลาการลา แต่ยังไม่ตรวจสอบวันลาคงเหลือ
                                setTimeout(function() {
                                    // รีเซ็ตค่าเริ่มต้นของปุ่มบันทึกและระยะเวลาการลา
                                    const urgentStartDate = document
                                        .getElementById(
                                            'urgentStartDate').value;
                                    const urgentStartTime = document
                                        .getElementById(
                                            'urgentStartTime').value;
                                    const urgentEndDate = document
                                        .getElementById(
                                            'urgentEndDate').value;
                                    const urgentEndTime = document
                                        .getElementById(
                                            'urgentEndTime').value;

                                    if (urgentStartDate &&
                                        urgentStartTime &&
                                        urgentEndDate && urgentEndTime) {
                                        calculateLeaveDuration();
                                    } else {
                                        // กรณียังไม่มีข้อมูลวันที่/เวลา ให้แสดงค่าเริ่มต้น
                                        const urgentLeaveDuration = document
                                            .getElementById(
                                                'urgentLeaveDuration');
                                        if (urgentLeaveDuration) {
                                            urgentLeaveDuration
                                                .textContent =
                                                "1 วัน 0 ชั่วโมง 0 นาที";
                                        }
                                    }

                                    // แก้ไข: ไม่ต้อง disable ปุ่มบันทึกตั้งแต่เริ่มแล้ว
                                    // เราจะเปิดปุ่มบันทึกไว้ก่อน
                                    const submitButton = document
                                        .getElementById(
                                            'btnSubmitForm2');
                                    if (submitButton) {
                                        submitButton.disabled = false;
                                    }
                                }, 200); // รอให้ Modal แสดงผลก่อน
                            }
                        }
                    });
                });

                // กำหนดค่า observer สำหรับ modal
                observer.observe(urgentLeaveModal, {
                    attributes: true
                });
                // console.log('เพิ่ม observer สำหรับฟอร์มฉุกเฉินเรียบร้อย');
            }

            // เพิ่ม Event listener สำหรับการเปลี่ยนแปลงวันที่และเวลาในฟอร์มฉุกเฉิน
            const urgentDateTimeInputs = [
                document.getElementById('urgentStartDate'),
                document.getElementById('urgentStartTime'),
                document.getElementById('urgentEndDate'),
                document.getElementById('urgentEndTime')
            ];

            urgentDateTimeInputs.forEach(input => {
                if (input) {
                    input.addEventListener('change', function() {
                        // console.log(
                        //     'มีการเปลี่ยนแปลงวันที่/เวลาในฟอร์มฉุกเฉิน - คำนวณระยะเวลาการลาใหม่'
                        // );
                        calculateLeaveDuration();
                    });
                }
            });

            // แยก Event listener สำหรับการเปลี่ยนประเภทการลาฉุกเฉิน
            const urgentLeaveTypeSelect = document.getElementById('urgentLeaveType');
            if (urgentLeaveTypeSelect) {
                urgentLeaveTypeSelect.addEventListener('change', function() {
                    // console.log('มีการเปลี่ยนประเภทการลาฉุกเฉิน');
                    calculateLeaveDuration(); // คำนวณระยะเวลาการลาและตรวจสอบวันลาคงเหลือ
                });
            }
        });

        document.getElementById('startDate').addEventListener('change', function() {
            let startDateValue = this.value;
            let endDateInput = document.getElementById('endDate');

            if (startDateValue) {
                endDateInput.value = startDateValue; // ตั้งค่า endDate เป็นวันเดียวกัน
                calculateLeaveDuration(); // เรียกคำนวณใหม่
            }
        });

        document.getElementById('urgentStartDate').addEventListener('change', function() {
            let startDateValue = this.value;
            let endDateInput = document.getElementById('urgentEndDate');

            if (startDateValue) {
                endDateInput.value = startDateValue; // ตั้งค่า endDate เป็นวันเดียวกัน
                calculateLeaveDuration(); // เรียกคำนวณใหม่
            }
        });

        function checkOther(select) {
            var otherReasonInput = document.getElementById('otherReason');

            if (select.value === 'อื่น ๆ') {
                otherReasonInput.classList.remove('d-none');
            } else {
                otherReasonInput.classList.add('d-none');
            }
        }

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

        function changeItemsPerPage(items) {
            window.location.href =
                '?page=1&year=<?php echo $selectedYear; ?>&month=<?php echo $selectedMonth; ?>&items=' + items;
        }

        function validateJumpToPage() {
            const pageInput = document.getElementById('jumpToPage');
            if (!pageInput.value) {
                Swal.fire({
                    title: 'แจ้งเตือน',
                    text: 'กรุณากรอกเลขหน้าที่ต้องการ',
                    icon: 'warning',
                    confirmButtonText: 'ตกลง'
                });
                return false;
            }
            return true;
        }

        // ฟังก์ชันโหลดข้อมูลเงื่อนไขการลาพักร้อนฉุกเฉิน
        function loadLeaveConditions() {
            $.ajax({
                url: 'u_ajax_get_leave_conditions.php',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    var selectBox = $('#leaveCondition');

                    // เพิ่มตัวเลือกจากข้อมูลที่ได้รับ
                    $.each(data, function(index, item) {
                        selectBox.append($('<option>', {
                            value: item.lt2_id,
                            text: item.lt2_name
                        }));
                    });
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching leave conditions:', error);
                    Swal.fire({
                        title: "เกิดข้อผิดพลาด",
                        text: "ไม่สามารถดึงข้อมูลเงื่อนไขการลาได้",
                        icon: "error"
                    });
                }
            });
        }
        </script>
        <script src="../js/popper.min.js"></script>
        <script src="../js/bootstrap.min.js"></script>
        <script src="../js/bootstrap.bundle.js"></script>
        <script src="../js/bootstrap.bundle.min.js"></script>
</body>

</html>