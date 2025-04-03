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
    <title>ใบลาย้อนหลังของพนักงาน</title>

    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <link rel="icon" href="../logo/logo.png">
    <link rel="stylesheet" href="../css/jquery-ui.css">
    <link rel="stylesheet" href="../css/flatpickr.min.css">

    <script src="../js/jquery-3.7.1.min.js"></script>
    <script src="../js/jquery-ui.min.js"></script>
    <script src="../js/flatpickr"></script>
    <script src="../js/sweetalert2.all.min.js"></script>

    <!-- <script src="https://kit.fontawesome.com/84c1327080.js" crossorigin="anonymous"></script> -->

    <script src="../js/fontawesome.js"></script>
</head>

<body>
    <?php require 'user_navbar.php'?>
    <nav class="navbar bg-body-tertiary" style="background-color: #072ac8; box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.3);
  border: none;">
        <div class=" container-fluid">
            <div class="row align-items-center">
                <div class="col-auto">
                    <i class="fa-solid fa-paste fa-2xl"></i>
                </div>
                <div class="col-auto">
                    <h3>ใบลาย้อนหลังของพนักงาน</h3>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <div class="d-flex justify-content-between align-items-center">
                <form class="mt-3 mb-3 row" method="post" id="yearMonthForm">
                    <label for="" class="mt-2 col-auto">เลือกปี</label>
                    <div class="col-auto">
                        <?php
                            $currentYear = date('Y'); // ปีปัจจุบัน

                            // ตรวจสอบค่าปีจาก POST หรือ GET
                            if (isset($_POST['year'])) {
                                $selectedYear = $_POST['year'];
                            } elseif (isset($_GET['year'])) { // เพิ่มการตรวจสอบจาก GET
                                $selectedYear = $_GET['year'];
                            } else {
                                $selectedYear = $currentYear;
                            }

                            // ตรวจสอบค่าเดือนจาก POST หรือ GET
                            if (isset($_POST['month'])) {
                                $selectedMonth = $_POST['month'];
                            } elseif (isset($_GET['month'])) { // เพิ่มการตรวจสอบจาก GET
                                $selectedMonth = $_GET['month'];
                            } else {
                                $selectedMonth = 'All';
                            }

                            // กำหนดช่วงวันที่เป็น 1 ม.ค. ถึง 31 ธ.ค. ของปีที่เลือก
                            $startDate = date("Y-m-d", strtotime($selectedYear . "-01-01"));
                            $endDate   = date("Y-m-d", strtotime($selectedYear . "-12-31"));

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

                                // ถ้าเลือกเดือนที่ไม่ใช่ All ให้ปรับช่วงวันที่ตามเดือนที่เลือก
                                if ($selectedMonth != 'All') {
                                    $daysInMonth = date('t', strtotime($selectedYear . '-' . $selectedMonth . '-01'));
                                    $startDate   = date("Y-m-d", strtotime($selectedYear . "-" . $selectedMonth . "-01"));
                                    $endDate     = date("Y-m-d", strtotime($selectedYear . "-" . $selectedMonth . "-" . $daysInMonth));
                                }
                            }

                            echo "<select class='form-select' name='month' id='selectedMonth' onchange='document.getElementById(\"yearMonthForm\").submit();'>";
                            foreach ($months as $key => $monthName) {
                                echo "<option value='$key'" . ($key == $selectedMonth ? " selected" : "") . ">$monthName</option>";
                            }
                            echo "</select>";

                        ?>
                    </div>
                </form>


                <!-- ยื่นใบลาย้อนหลัง -->
                <button type="button" class="button-shadow btn btn-primary" data-bs-toggle="modal"
                    data-bs-target="#leaveModal" style="width: 150px;">
                    ยื่นใบลาย้อนหลัง
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
                                <div class="col-6">
                                    <label for="userCode" class="form-label">รหัสพนักงาน</label>
                                    <input type="text" class="form-control" id="userCode" name="userCode"
                                        list="codeList" required>
                                    <datalist id="codeList">
                                        <?php
                                            $sql    = "SELECT * FROM employees WHERE e_status <> 1 ";
                                            $result = $conn->query($sql);
                                            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                                                echo '<option value="' . $row['e_usercode'] .
                                                    '" data-name="' . $row['e_name'] .
                                                    '" data-username="' . $row['e_username'] .
                                                    '" data-depart="' . $row['e_department'] .
                                                    '" data-level="' . $row['e_level'] .
                                                    '" data-telPhone="' . $row['e_phone'] .
                                                    '" data-workplace="' . $row['e_workplace'] .
                                                    '" data-subDepart="' . $row['e_sub_department'] . '"
             > ' . $row['e_name'] . '</option>';
                                            }
                                        ?>
                                    </datalist>
                                    <input type="text" class="form-control" id="userName" name="userName" hidden>
                                    <input type="text" class="form-control" id="depart" name="depart" hidden>
                                    <input type="text" class="form-control" id="level" name="level" hidden>
                                    <input type="text" class="form-control" id="workplace" name="workplace" hidden>
                                    <input type="text" class="form-control" id="subDepart" name="subDepart" hidden>

                                </div>
                                <div class="col-6">
                                    <label for="name" class="form-label">ชื่อพนักงาน</label>
                                    <input type="text" class="form-control" id="name" name="name" disabled>
                                </div>
                            </div>
                            <div class="row">
                                <div class="mt-3 col-12">
                                    <label for="leaveType" class="form-label">ประเภทการลา</label>
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
                                    <input type="text" class="form-control" id="startDate" required>
                                </div>
                                <div class=" col-6">
                                    <label for="startTime" class="form-label">เวลาที่เริ่มต้น</label>
                                    <span style="color: red;">*</span>
                                    <select class="form-select" id="startTime" name="startTime" required>
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
                                    <input type="text" class="form-control" id="endDate" required>
                                </div>
                                <div class="col-6">
                                    <label for="endTime" class="form-label">เวลาที่สิ้นสุด</label>
                                    <span style="color: red;">*</span>
                                    <select class="form-select" id="endTime" name="endTime" required>
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
                                <div class="col-12">
                                    <label for="telPhone" class="form-label">เบอร์โทร</label>
                                    <input type="text" class="form-control" id="telPhone" name="telPhone" disabled>
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

        <div class="table-responsive">
            <table class="table table-hover" style="border-top: 1px solid rgba(0, 0, 0, 0.1);" id="leaveTable">
                <thead>
                    <tr class="text-center align-middle">
                        <th rowspan="2"><?php echo $strNo; ?></th>
                        <th rowspan="1"><?php echo $strEmpCode; ?></th>
                        <th rowspan="1"><?php echo $strEmpName; ?></th>
                        <th rowspan="2"><?php echo $strSubDate; ?></th>
                        <th rowspan="2"><?php echo $strLeaveType; ?></th>
                        <th colspan="2" class="text-center"><?php echo $strDateTime; ?></th>
                        <th rowspan="2">จำนวนวันลา</th>
                        <th rowspan="2"><?php echo $strFile; ?></th>
                        <th rowspan="2"><?php echo $strListStatus; ?></th>
                        <th rowspan="2"><?php echo $strProveName1; ?></th>
                        <th rowspan="2"><?php echo $strStatus1; ?></th>
                        <th rowspan="2"><?php echo $strProveDate1; ?></th>
                        <th rowspan="2"><?php echo $strReason1; ?></th>
                        <th rowspan="2"><?php echo $strProveName2; ?></th>
                        <th rowspan="2"><?php echo $strStatus2; ?></th>
                        <th rowspan="2"><?php echo $strProveDate2; ?></th>
                        <th rowspan="2"><?php echo $strReason2; ?></th>
                        <th rowspan="2">ชื่ออนุมติ_3</th>
                        <th rowspan="2">สถานะอนุมัติ_3</th>
                        <th rowspan="2">วันเวลาที่อนุมัติ_3</th>
                        <th rowspan="2">เหตุผล_3</th>
                        <th rowspan="2"><?php echo $strStatusHR; ?></th>
                        <th rowspan="2">หมายเหตุ</th>
                        <th rowspan="2"></th>
                        <th rowspan="2"></th>
                    </tr>
                    <tr class="text-center">
                        <th><input type="text" class="form-control" id="codeSearch"></th>
                        <th><input type="text" class="form-control" id="nameSearch"></th>
                        <!-- <th><input type="text" class="form-control" id="leaveSearch"></th> -->
                        <!-- <th><input type="text" class="form-control" id="leaveSearch"></th> -->
                        <th>จาก</th>
                        <th>ถึง</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    <?php

                        $itemsPerPage = 10;

                        // คำนวณหน้าปัจจุบัน
                        if (! isset($_GET['page'])) {
                            $currentPage = 1;
                        } else {
                            $currentPage = (int) $_GET['page'];
                        }

                        // สร้าง SQL พื้นฐานสำหรับการค้นหา
                        $baseSql = "";
                        $params  = [];

                        // เตรียม SQL ตามเงื่อนไขที่เลือก
                        if ($selectedMonth == "All") {
                            // กรณีเลือก "ทั้งหมด"
                            $baseSql = "FROM leave_list
                WHERE YEAR(l_create_datetime) = :selectedYear
                AND l_leave_id NOT IN (6,7)
                AND l_remark = 'ลาย้อนหลัง'
                AND l_leave_status <> 1";
                            $params[':selectedYear'] = $selectedYear;
                        } else {
                            // กรณีเลือกเดือนเฉพาะ
                            $baseSql = "FROM leave_list
                WHERE (
                    (MONTH(l_create_datetime) = :selectedMonth AND YEAR(l_create_datetime) = :selectedYear)
                    OR
                    (MONTH(l_leave_end_date) = :selectedMonth AND YEAR(l_leave_end_date) = :selectedYear)
                )
                AND l_leave_id NOT IN (6,7)
                AND l_remark = 'ลาย้อนหลัง'
                AND l_leave_status <> 1";
                            $params[':selectedMonth'] = $selectedMonth;
                            $params[':selectedYear']  = $selectedYear;
                        }

                        // คำสั่ง SQL สำหรับนับจำนวนรายการทั้งหมด
                        $countSql = "SELECT COUNT(*) AS total " . $baseSql;

                        // เตรียมและประมวลผลคำสั่ง SQL นับจำนวน
                        $countStmt = $conn->prepare($countSql);
                        foreach ($params as $key => $value) {
                            if (strpos($key, 'Month') !== false || strpos($key, 'Year') !== false) {
                                $countStmt->bindValue($key, $value, PDO::PARAM_INT);
                            } else {
                                $countStmt->bindValue($key, $value, PDO::PARAM_STR);
                            }
                        }
                        $countStmt->execute();
                        $totalRows = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

                        // คำนวณหน้าทั้งหมด
                        $totalPages = ceil($totalRows / $itemsPerPage);

                        // คำนวณ offset สำหรับ pagination
                        $offset = ($currentPage - 1) * $itemsPerPage;

                        // คำสั่ง SQL สำหรับดึงข้อมูลพร้อม LIMIT และ OFFSET
                        $dataSql = "SELECT * " . $baseSql . " ORDER BY l_create_datetime DESC LIMIT :limit OFFSET :offset";

                        // เตรียมและประมวลผลคำสั่ง SQL ดึงข้อมูล
                        $stmt = $conn->prepare($dataSql);
                        foreach ($params as $key => $value) {
                            if (strpos($key, 'Month') !== false || strpos($key, 'Year') !== false) {
                                $stmt->bindValue($key, $value, PDO::PARAM_INT);
                            } else {
                                $stmt->bindValue($key, $value, PDO::PARAM_STR);
                            }
                        }
                        $stmt->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
                        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
                        $stmt->execute();

                        // แสดงผลลำดับของแถว (เริ่มจากรายการล่าสุดลงไป)
                        $rowNumber = $totalRows - ($currentPage - 1) * $itemsPerPage;

                        // แสดงข้อมูลในตาราง
                        if ($stmt->rowCount() > 0) {
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<tr class="align-middle">';

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
                                    echo $row['l_leave_id'];
                                }
                                echo '</td>';

                                // 1
                                echo '<td hidden>' . $row['l_name'] . '</td>';

                                // 2
                                echo '<td hidden>' . $row['l_department'] . '</td>';

                                // 3
                                echo '<td hidden>' . $row['l_leave_reason'] . '</td>';

                                // 4
                                echo '<td>' . $rowNumber . '</td>';

                                // 5
                                echo '<td>' . $row['l_usercode'] . '</td>';

                                                                                                                                                                        // 6
                                echo '<td>' . '<span class="text-primary">' . $row['l_name'] . '</span>' . '<br>' . 'แผนก : ' . $row['l_department'] . '</td>'; // คอลัมน์ 2 ชื่อพนักงาน + แผนก

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
                                    echo '<span class="text-primary">' . 'ขาดงาน' . '</span>' . '<br>' . 'เหตุผล : ' . $row['l_leave_reason'];
                                } elseif ($row['l_leave_id'] == 7) {
                                    echo '<span class="text-primary">' . 'มาสาย' . '</span>' . '<br>' . 'เหตุผล : ' . $row['l_leave_reason'];
                                } elseif ($row['l_leave_id'] == 8) {
                                    echo '<span class="text-primary">' . 'อื่น ๆ' . '</span>' . '<br>' . 'เหตุผล : ' . $row['l_leave_reason'];
                                } else {
                                    echo 'ไม่พบประเภทการลาและเหตุผลการลา';
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
                            <a href="../upload/' . $row['l_file3'] . '" class="btn btn-sm btn-primary" target="_blank">เปิดไฟล์</a>
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
                                    echo '<span class="text-success">' . $strStatusNormal . '</span>';
                                } else {
                                    echo '<span class="text-danger">' . $strStatusCancel . '</span>';
                                }
                                echo '</td>';

                                // 14
                                echo '<td>' . $row['l_approve_name'] . '</td>';

                                // 15
                                echo '<td>';
                                // รอหัวหน้าอนุมัติ
                                if ($row['l_approve_status'] == 0) {
                                    echo '<div class="text-warning"><b>' . $strStatusProve0 . '</b></div>';
                                }
                                // รอผจกอนุมัติ
                                elseif ($row['l_approve_status'] == 1) {
                                    echo '<div class="text-warning"><b>' . $strStatusProve1 . '</b></div>';
                                }
                                // หัวหน้าอนุมัติ
                                elseif ($row['l_approve_status'] == 2) {
                                    echo '<div class="text-success"><b>' . $strStatusProve2 . '</b></div>';
                                }
                                // หัวหน้าไม่อนุมัติ
                                elseif ($row['l_approve_status'] == 3) {
                                    echo '<div class="text-danger"><b>' . $strStatusProve3 . '</b></div>';
                                }
                                //  ผจก อนุมัติ
                                elseif ($row['l_approve_status'] == 4) {
                                    echo '<div class="text-success"><b>' . $strStatusProve4 . '</b></div>';
                                }
                                //  ผจก ไม่อนุมัติ
                                elseif ($row['l_approve_status'] == 5) {
                                    echo '<div class="text-danger"><b>' . $strStatusProve5 . '</b></div>';
                                } elseif ($row['l_approve_status'] == 6) {
                                    echo '';
                                }
                                // ไม่มีสถานะ
                                else {
                                    echo 'ไม่พบสถานะ';
                                }
                                echo '</td>';

                                // 16
                                echo '<td>' . $row['l_approve_datetime'] . '</td>';

                                // 17
                                echo '<td>' . $row['l_reason'] . '</td>';

                                // 18
                                echo '<td>' . $row['l_approve_name2'] . '</td>';

                                // 19
                                echo '<td>';
                                // รอหัวหน้าอนุมัติ
                                if ($row['l_approve_status2'] == 0) {
                                    echo '<div class="text-warning"><b>' . $strStatusProve0 . '</b></div>';
                                }
                                // รอผจกอนุมัติ
                                elseif ($row['l_approve_status2'] == 1) {
                                    echo '<div class="text-warning"><b>' . $strStatusProve1 . '</b></div>';
                                }
                                // หัวหน้าอนุมัติ
                                elseif ($row['l_approve_status2'] == 2) {
                                    echo '<div class="text-success"><b>' . $strStatusProve2 . '</b></div>';
                                }
                                // หัวหน้าไม่อนุมัติ
                                elseif ($row['l_approve_status2'] == 3) {
                                    echo '<div class="text-danger"><b>' . $strStatusProve3 . '</b></div>';
                                }
                                //  ผจก อนุมัติ
                                elseif ($row['l_approve_status2'] == 4) {
                                    echo '<div class="text-success"><b>' . $strStatusProve4 . '</b></div>';
                                }
                                //  ผจก ไม่อนุมัติ
                                elseif ($row['l_approve_status2'] == 5) {
                                    echo '<div class="text-danger"><b>' . $strStatusProve5 . '</b></div>';
                                } elseif ($row['l_approve_status2'] == 6) {
                                    echo '';
                                }
                                // ไม่มีสถานะ
                                else {
                                    echo 'ไม่พบสถานะ';
                                }
                                echo '</td>';

                                // 20
                                echo '<td>' . $row['l_approve_datetime2'] . '</td>';

                                // 21
                                echo '<td>' . $row['l_reason2'] . '</td>';

                                // 22
                                echo '<td>' . $row['l_approve_name3'] . '</td>';

                                // 23
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

                                // 24
                                echo '<td>' . $row['l_approve_datetime3'] . '</td>';

                                // 25
                                echo '<td>' . $row['l_reason3'] . '</td>';

                                // 26
                                echo '<td >';
                                if ($row['l_hr_status'] == 0) {
                                    echo '<span class="text-warning"><b>' . $strStatusHR0 . '</b></span>';
                                } elseif ($row['l_hr_status'] == 1) {
                                    echo '<span class="text-success"><b>' . $strStatusHR1 . '</b></span>';
                                } elseif ($row['l_hr_status'] == 2) {
                                    echo '<span class="text-danger"><b>' . $strStatusHR2 . '</b></span>';
                                } elseif ($row['l_hr_status'] == 3) {
                                    echo '';
                                } else {
                                    echo '';
                                }
                                echo '</td>';

                                // 27
                                echo '<td>' . $row['l_remark'] . '</td>';

                                // 28
                                // echo "<td><button type='button' class='btn btn-primary leaveChk' data-bs-toggle='modal' data-bs-target='#leaveModal'>$btnCheck</button></td>";

                                // 29
                                echo '<td hidden>
                                <button type="button" class="btn btn-primary btn-sm view-history" data-usercode="' . $row['l_usercode'] . '"><i class="fa-solid fa-clock-rotate-left"></i></button></td>';

                                echo '<td>
    <button type="button" class="btn btn-danger btn-sm cancel-btn"
        data-usercode="' . $row['l_usercode'] . '"
        data-leaveid="' . $row['l_leave_id'] . '"
        data-createdatetime="' . $row['l_create_datetime'] . '">
        <i class="fa-solid fa-trash"></i>
    </button>
</td>';

                                echo '</tr>';
                                $rowNumber--;
                            }
                        } else {
                            echo '<tr><td colspan="26" style="text-align: left; color:red;">ไม่พบข้อมูล</td></tr>';
                        }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>
    document.getElementById('userCode').addEventListener('input', function() {
        var selectedCode = this.value;
        var nameField = document.getElementById('name');
        var telPhoneField = document.getElementById('telPhone');
        var userNameField = document.getElementById('userName');
        var departField = document.getElementById('depart');
        var levelField = document.getElementById('level');
        var workPlaceField = document.getElementById('workplace');
        var subDepartField = document.getElementById('subDepart');

        if (selectedCode === "") {
            nameField.value = "";
            telPhoneField.value = "";
            userNameField.value = "";
            departField.value = "";
            levelField.value = ""; // ตั้งค่าเป็นค่าว่าง
            workPlaceField.value = ""; // ตั้งค่าเป็นค่าว่าง
            subDepartField.value = ""; // ตั้งค่าเป็นค่าว่าง
            return;
        }

        var dataList = document.getElementById('codeList').getElementsByTagName('option');
        for (var i = 0; i < dataList.length; i++) {
            if (dataList[i].value === selectedCode) {
                nameField.value = dataList[i].getAttribute('data-name'); // ตั้งค่าเบอร์โทรที่ถูกต้อง
                telPhoneField.value = dataList[i].getAttribute(
                    'data-telPhone'); // ตั้งค่าเบอร์โทรที่ถูกต้อง
                userNameField.value = dataList[i].getAttribute('data-username');
                departField.value = dataList[i].getAttribute('data-depart');
                levelField.value = dataList[i].getAttribute('data-level');
                workPlaceField.value = dataList[i].getAttribute('data-workplace');
                subDepartField.value = dataList[i].getAttribute('data-subDepart');

                break;
            }
        }
    });

    $(document).ready(function() {

        $.ajax({
            url: 'u_ajax_get_holiday.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                var today = new Date();

                // เพิ่มตัวแปรสำหรับ endDatePicker
                var endDatePicker;

                // กำหนดค่า datepicker สำหรับวันที่เริ่มต้น
                flatpickr("#startDate", {
                    dateFormat: "d-m-Y",
                    defaultDate: today,
                    // minDate: today,
                    disable: response.holidays,
                    // เพิ่ม option onChange
                    onChange: function(selectedDates, dateStr, instance) {
                        // เมื่อเลือกวันที่เริ่มต้นใหม่ ให้ endDate เป็นวันที่เดียวกับวันที่เริ่มต้น
                        endDatePicker.setDate(selectedDates[0]);
                    }
                });

                // กำหนดค่า datepicker สำหรับวันที่สิ้นสุด
                endDatePicker = flatpickr("#endDate", {
                    dateFormat: "d-m-Y",
                    defaultDate: today,
                    // minDate: today,
                    disable: response.holidays
                });

                // คงค่าเดิมสำหรับฟิลด์อื่นๆ
                flatpickr("#urgentStartDate", {
                    dateFormat: "d-m-Y",
                    defaultDate: today,
                    // minDate: today,
                    disable: response.holidays
                });

                flatpickr("#urgentEndDate", {
                    dateFormat: "d-m-Y",
                    defaultDate: today,
                    // minDate: today,
                    disable: response.holidays
                });
            }
        });

        $('#leaveForm').on('submit', function(e) {
            e.preventDefault(); // ป้องกันการส่งฟอร์มแบบปกติ

            // เก็บข้อมูลฟอร์มทั้งหมดรวมถึงไฟล์ที่แนบ
            var formData = new FormData(this);
            var userCode = $('#userCode').val();
            var userName = $('#userName').val();
            var depart = $('#depart').val();
            var name = $('#name').val();
            var level = $('#level').val();
            var workplace = $('#workplace').val();
            var subDepart = $('#subDepart').val();
            var telPhone = $('#telPhone').val();
            var leaveType = $('#leaveType').val();
            var leaveReason = $('#leaveReason').val();
            var startDate = $('#startDate').val();
            var startTime = $('#startTime').val();
            var endDate = $('#endDate').val();
            var endTime = $('#endTime').val();
            var files = $('#file')[0].files;

            formData.append('userCode', $('#userCode').val());
            formData.append('userName', $('#userName').val());
            formData.append('name', $('#name').val());
            formData.append('level', $('#level').val());
            formData.append('workplace', $('#workplace').val());
            formData.append('subDepart', $('#subDepart').val());
            formData.append('depart', $('#depart').val());
            formData.append('telPhone', $('#telPhone').val());
            formData.append('leaveType', $('#leaveType').val());
            formData.append('leaveReason', $('#leaveReason').val());
            formData.append('startDate', $('#startDate').val());
            formData.append('startTime', $('#startTime').val());
            formData.append('endDate', $('#endDate').val());
            formData.append('endTime', $('#endTime').val());

            var addUserName = "<?php echo $userName; ?>";
            formData.append('addUserName', addUserName);

            if (files.length > 0) {
                formData.append('file', files[0]);
            }

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
                if (endDate < startDate) {
                    Swal.fire({
                        title: "ไม่สามารถลาได้",
                        text: "กรุณาเลือกวันที่เริ่มต้นลาใหม่",
                        icon: "error"
                    });
                    return false;
                } else {
                    // แปลงรหัสประเภทการลาเป็นข้อความ
                    var leaveTypeText = '';
                    if (leaveType == '1') {
                        leaveTypeText = 'ลากิจได้รับค่าจ้าง';
                    } else if (leaveType == '2') {
                        leaveTypeText = 'ลากิจไม่ได้รับค่าจ้าง';
                    } else if (leaveType == '3') {
                        leaveTypeText = 'ลาป่วย';
                    } else if (leaveType == '4') {
                        leaveTypeText = 'ลาป่วยจากงาน';
                    } else if (leaveType == '5') {
                        leaveTypeText = 'ลาพักร้อน';
                    } else if (leaveType == '8') {
                        leaveTypeText = 'อื่น ๆ';
                    }

                    if (startTime === '12:00') {
                        startTime = '11:45';
                    } else if (startTime === '13:00') {
                        startTime = '12:45';
                    }


                    if (endTime === '12:00') {
                        endTime = '11:45';
                    } else if (endTime === '13:00') {
                        endTime = '12:45';
                    } else if (endTime === '17:00') {
                        endTime = '16:40';
                    }
                    // สร้าง HTML สำหรับแสดงรายละเอียด
                    var leaveDetails = `
          ประเภทการลา:${leaveTypeText}<br>
เหตุผลการลา: ${leaveReason}<br>
วันที่เริ่มต้น: ${startDate} เวลา ${startTime}<br>
วันที่สิ้นสุด: ${endDate} เวลา ${endTime}<br>
`;

                    // เพิ่ม SweetAlert ยืนยันการยื่นใบลาพร้อมรายละเอียด
                    Swal.fire({
                        title: 'ยืนยันการยื่นใบลา',
                        html: `${leaveDetails}`,
                        icon: 'question',
                        showCancelButton: true,
                        // confirmButtonColor: '#3085d6',
                        // cancelButtonColor: '#d33',
                        confirmButtonText: 'ยืนยัน',
                        cancelButtonText: 'ยกเลิก'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // ถ้าผู้ใช้กดยืนยัน ให้ส่งข้อมูล
                            $.ajax({
                                url: 'a_ajax_add_emp_leave.php',
                                type: 'POST',
                                data: formData,
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
                                    $('#btnSubmitForm1').prop('disabled', false)
                                        .html('ยื่นใบลา');
                                }
                            });
                        }
                    });
                }
            }
        });
        // โค้ดสำหรับการยกเลิกใบลา
        $('.cancel-btn').on('click', function() {
            // Retrieve data attributes from button
            var usercode = $(this).data('usercode');
            var leaveId = $(this).data('leaveid');
            var createDatetime = $(this).data('createdatetime');

            // SweetAlert confirmation dialog
            Swal.fire({
                title: 'ต้องการยกเลิกใบลาหรือไม่ ?',
                // text: "Do you really want to cancel this leave request?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'ใช่',
                cancelButtonText: 'ไม่'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'a_ajax_delete_emp_leave.php', // PHP script to handle cancellation
                        type: 'POST',
                        data: {
                            l_usercode: usercode,
                            l_leave_id: leaveId,
                            l_create_datetime: createDatetime
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
                        error: function(xhr, status, error) {
                            Swal.fire(
                                'Error!',
                                'An error occurred: ' + error,
                                'error'
                            );
                        }
                    });
                }
            });
        });
        $('.cancel-btn').on('click', function() {
            // Retrieve data attributes from button
            var usercode = $(this).data('usercode');
            var leaveId = $(this).data('leaveid');
            var createDatetime = $(this).data('createdatetime');

            // SweetAlert confirmation dialog
            Swal.fire({
                title: 'ต้องการยกเลิกใบลาหรือไม่ ?',
                // text: "Do you really want to cancel this leave request?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'ใช่',
                cancelButtonText: 'ไม่'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'a_ajax_delete_emp_leave.php', // PHP script to handle cancellation
                        type: 'POST',
                        data: {
                            l_usercode: usercode,
                            l_leave_id: leaveId,
                            l_create_datetime: createDatetime
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
                        error: function(xhr, status, error) {
                            Swal.fire(
                                'Error!',
                                'An error occurred: ' + error,
                                'error'
                            );
                        }
                    });
                }
            });
        });
    });
    </script>
    <script src="../js/popper.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/bootstrap.bundle.js"></script>
    <script src="../js/bootstrap.bundle.min.js"></script>
</body>

</html>