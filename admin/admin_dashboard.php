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
    <title>ใบลาของพนักงาน</title>

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


    <style>
    /* Make all action buttons columns sticky */
    #leaveTable th:nth-last-child(4),
    #leaveTable td:nth-last-child(4) {
        position: sticky;
        right: 120px;
        /* ปุ่มแก้ไข */
        background-color: #fff;
        z-index: 2;
        box-shadow: -2px 0 5px rgba(0, 0, 0, 0.1);
    }

    #leaveTable th:nth-last-child(3),
    #leaveTable td:nth-last-child(3) {
        position: sticky;
        right: 80px;
        /* ปุ่มยกเลิก */
        background-color: #fff;
        z-index: 2;
        box-shadow: -2px 0 5px rgba(0, 0, 0, 0.1);
    }

    #leaveTable th:nth-last-child(2),
    #leaveTable td:nth-last-child(2) {
        position: sticky;
        right: 40px;
        /* ปุ่มตรวจสอบ */
        background-color: #fff;
        z-index: 2;
        box-shadow: -2px 0 5px rgba(0, 0, 0, 0.1);
    }

    #leaveTable th:last-child,
    #leaveTable td:last-child {
        position: sticky;
        right: 0;
        /* ปุ่มดูประวัติ */
        background-color: #fff;
        z-index: 2;
        box-shadow: -2px 0 5px rgba(0, 0, 0, 0.1);
    }

    /* Hover effect for all sticky buttons */
    #leaveTable td:nth-last-child(4):hover,
    #leaveTable td:nth-last-child(3):hover,
    #leaveTable td:nth-last-child(2):hover,
    #leaveTable td:last-child:hover {
        background-color: #f8f9fa;
    }
    </style>
</head>

<body>
    <?php include 'admin_navbar.php'; ?>
    <nav class="navbar bg-body-tertiary">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-auto">
                    <i class="fa-solid fa-file-signature fa-2xl"></i>
                </div>
                <div class="col-auto">
                    <h3>ใบลาของพนักงาน</h3>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
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

            <!-- เพิ่มปุ่ม Export -->
            <div class="col-auto">
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#exportModal">
                    <i class="fa-solid fa-file-export"></i> Export
                </button>
            </div>
        </form>
    </div>

    <div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exportModalLabel">เลือกช่วงเวลาสำหรับ Export ข้อมูลการลา</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="export_leave_data.php" method="post">
                    <div class="modal-body">
                        <!-- เพิ่มช่องค้นหารหัสพนักงาน -->
                        <div class="mb-3">
                            <label for="exportEmployeeCode" class="form-label">ค้นหารหัสพนักงาน</label>
                            <input type="text" class="form-control" id="exportEmployeeCode" name="exportEmployeeCode"
                                placeholder="ระบุรหัสพนักงาน">
                        </div>

                        <hr class="my-3">

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="dateRangeType" id="currentSelection"
                                    value="current" checked>
                                <label class="form-check-label" for="currentSelection">
                                    ใช้ช่วงเวลาที่กำลังเลือกอยู่
                                    (<?php
                                         $displayMonth = $selectedMonth != 'All' ? $months[$selectedMonth] : $strAllMonth;
                                     echo $displayMonth . ' ' . $selectedYear;
                                     ?>)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="dateRangeType" id="customRange"
                                    value="custom">
                                <label class="form-check-label" for="customRange">
                                    เลือกช่วงเวลาเอง
                                </label>
                            </div>
                        </div>

                        <div id="customDateRange" class="mt-3" style="display: none;">
                            <div class="row g-3">
                                <div class="col">
                                    <label for="startDate" class="form-label">วันที่เริ่มต้น</label>
                                    <input type="date" class="form-control" id="startDate" name="startDate"
                                        value="<?php echo $startDate; ?>">
                                </div>
                                <div class="col">
                                    <label for="endDate" class="form-label">วันที่สิ้นสุด</label>
                                    <input type="date" class="form-control" id="endDate" name="endDate"
                                        value="<?php echo $endDate; ?>">
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="includeHeader" name="includeHeader"
                                    checked>
                                <label class="form-check-label" for="includeHeader">
                                    รวมส่วนหัวของตาราง
                                </label>
                            </div>
                        </div>

                        <div class="mt-3">
                            <label for="exportFormat" class="form-label">รูปแบบไฟล์</label>
                            <select class="form-select" id="exportFormat" name="exportFormat">
                                <option value="excel">Excel (.xlsx)</option>
                            </select>
                        </div>

                        <!-- ส่งค่าที่กำลังเลือกอยู่ไปด้วย -->
                        <input type="hidden" name="year" value="<?php echo $selectedYear; ?>">
                        <input type="hidden" name="month" value="<?php echo $selectedMonth; ?>">
                        <input type="hidden" name="startDateCurrent" value="<?php echo $startDate; ?>">
                        <input type="hidden" name="endDateCurrent" value="<?php echo $endDate; ?>">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary">Export</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="d-flex justify-content-center align-items-center flex-wrap mt-3">
            <!-- รายการลาทั้งหมด -->
            <div class="col-2 filter-card mx-2" data-status="all">
                <div class="card text-bg-primary mb-3">
                    <div class="card-body">
                        <h5 class="card-title">
                            <div class="d-flex justify-content-between">
                                <span id="total-count-all"></span>
                                <i class="mt-4 fas fa-file-alt ml-2 fa-2xl"></i>
                            </div>
                        </h5>
                        <p class="card-text">
                            รายการลาทั้งหมด
                        </p>
                    </div>
                </div>
            </div>

            <!-- รายการลาที่รออนุมัติ -->
            <div class="col-2 filter-card mx-2" data-status="0">
                <div class="card text-bg-warning mb-3">
                    <div class="card-body">
                        <h5 class="card-title">
                            <div class="d-flex justify-content-between">
                                <span id="total-count-0"></span>
                                <i class="mt-4 fa-solid fa-clock-rotate-left fa-2xl" style="color: #ffffff;"></i>
                            </div>
                        </h5>
                        <p class="card-text">
                            รายการลาที่รอตรวจสอบ
                        </p>
                    </div>
                </div>
            </div>

            <!-- รายการลาที่อนุมัติ -->
            <div class="col-2 filter-card mx-2" data-status="1">
                <div class="card text-bg-success mb-3">
                    <div class="card-body">
                        <h5 class="card-title">
                            <div class="d-flex justify-content-between">
                                <span id="total-count-1"></span>
                                <i class="mt-4 fa-solid fa-thumbs-up fa-2xl"></i>
                            </div>
                        </h5>
                        <p class="card-text">
                            รายการลาที่ผ่าน
                        </p>
                    </div>
                </div>
            </div>

            <!-- รายการลาที่ไม่อนุมัติ -->
            <div class="col-2 filter-card mx-2" data-status="2">
                <div class="card text-bg-danger mb-3">
                    <div class="card-body">
                        <h5 class="card-title">
                            <div class="d-flex justify-content-between">
                                <span id="total-count-2"></span>
                                <i class="mt-4 fa-solid fa-thumbs-down fa-2xl"></i>
                            </div>
                        </h5>
                        <p class="card-text">
                            รายการลาที่ไม่ผ่าน
                        </p>
                    </div>
                </div>
            </div>

            <!-- รายการลาที่ยกเลิก -->
            <div class="col-2 filter-card mx-2" data-status="4">
                <div class="card text-bg-info mb-3">
                    <div class="card-body">
                        <h5 class="card-title">
                            <div class="d-flex justify-content-between">
                                <span id="total-count-4"></span>
                                <i class="mt-4 fa-solid fa-ban fa-2xl" style="color: #ffffff;"></i>
                            </div>
                        </h5>
                        <p class="card-text">
                            รายการลาที่ยกเลิก
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ตารางข้อมูลการลา -->
    <div class="container-fluid">
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
                        <th rowspan="2"></th>
                    </tr>
                    <tr class="text-center">
                        <?php $searchCode = isset($_GET['codeSearch']) ? $_GET['codeSearch'] : '';
                        ?>
                        <th><input type="text" class="form-control" id="codeSearch"
                                value="<?php echo htmlspecialchars($searchCode); ?>"></th>
                        <th> <input type="text" class="form-control" id="nameSearch"></th>
                        <th style="width: 8%;">จาก</th>
                        <th style="width: 8%;">ถึง</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    <?php
                        // echo $subDepart;
                        $itemsPerPage = 10;

                        // คำนวณหน้าปัจจุบัน
                        if (! isset($_GET['page'])) {
                            $currentPage = 1;
                        } else {
                            $currentPage = $_GET['page'];
                        }

                        $sql = "SELECT
    li.*,
    em.*
FROM leave_list li
INNER JOIN employees em
    ON li.l_usercode = em.e_usercode
WHERE
    li.l_hr_status <> 3
    AND li.l_leave_id NOT IN (6, 7)
    AND (
        YEAR(li.l_create_datetime) = :selectedYear
        OR YEAR(li.l_leave_end_date) = :selectedYear
    )";

                        if ($selectedMonth != "All") {
                            $sql .= " AND (
        Month(li.l_create_datetime) = :selectedMonth
        OR Month(li.l_leave_end_date) = :selectedMonth
    )";
                        }

                        $sql .= " AND li.l_workplace = :workplace
                    ORDER BY li.l_create_datetime DESC";
                        // Prepare the statement
                        $stmt = $conn->prepare($sql);

                        $stmt->bindParam(':workplace', $workplace);
                        $stmt->bindParam(':selectedYear', $selectedYear, PDO::PARAM_INT);

                        if ($selectedMonth != "All") {
                            $stmt->bindParam(':selectedMonth', $selectedMonth, PDO::PARAM_INT);
                        }
                        // Execute the query to get the total number of rows
                        $stmt->execute();
                        $totalRows = $stmt->rowCount();

                        // Calculate total pages
                        $totalPages = ceil($totalRows / $itemsPerPage);

                        // Calculate offset for pagination
                        $offset = ($currentPage - 1) * $itemsPerPage;

                        // Add LIMIT and OFFSET to the SQL statement for pagination
                        $sql .= " LIMIT :limit OFFSET :offset";

                        // Prepare the final query with pagination
                        $stmt = $conn->prepare($sql);

                        // Bind the limit and offset parameters
                        $stmt->bindParam(':limit', $itemsPerPage, PDO::PARAM_INT);
                        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
                        $stmt->bindParam(':workplace', $workplace);
                        $stmt->bindParam(':selectedYear', $selectedYear, PDO::PARAM_INT);
                        if ($selectedMonth != "All") {
                            $stmt->bindParam(':selectedMonth', $selectedMonth, PDO::PARAM_INT);
                        }
                        // Execute the paginated query
                        $stmt->execute();

                        // Display row number starting from the correct count
                        $rowNumber = $totalRows - ($currentPage - 1) * $itemsPerPage;

                        // Show data in the table
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
                                // 08:45
                                if ($row['l_leave_start_time'] == '09:00:00' && $row['l_remark'] == '08:45:00') {
                                    echo '<td>' . date('d-m-Y', strtotime($row['l_leave_start_date'])) . '<br> 08:45:00</td>';
                                }
                                // 09:45
                                else if ($row['l_leave_start_time'] == '10:00:00' && $row['l_remark'] == '09:45:00') {
                                    echo '<td>' . date('d-m-Y', strtotime($row['l_leave_start_date'])) . '<br> 09:45:00</td>';
                                }
                                // 10:45
                                else if ($row['l_leave_start_time'] == '11:00:00' && $row['l_remark'] == '10:45:00') {
                                    echo '<td>' . date('d-m-Y', strtotime($row['l_leave_start_date'])) . '<br> 10:45:00</td>';
                                }
                                // 11:45
                                else if ($row['l_leave_start_time'] == '12:00:00') {
                                    echo '<td>' . date('d-m-Y', strtotime($row['l_leave_start_date'])) . '<br> 11:45:00</td>';
                                }
                                // 12:45
                                else if ($row['l_leave_start_time'] == '13:00:00') {
                                    echo '<td>' . date('d-m-Y', strtotime($row['l_leave_start_date'])) . '<br> 12:45:00</td>';
                                }
                                // 13:10
                                else if ($row['l_leave_start_time'] == '13:30:00' && $row['l_remark'] == '13:10:00') {
                                    echo '<td>' . date('d-m-Y', strtotime($row['l_leave_start_date'])) . '<br> 13:10:00</td>';
                                }
                                // 13:40
                                else if ($row['l_leave_start_time'] == '14:00:00' && $row['l_remark'] == '13:40:00') {
                                    echo '<td>' . date('d-m-Y', strtotime($row['l_leave_start_date'])) . '<br> 13:40:00</td>';
                                }
                                // 13:45
                                else if ($row['l_leave_start_time'] == '14:00:00' && $row['l_remark'] == '13:45:00') {
                                    echo '<td>' . date('d-m-Y', strtotime($row['l_leave_start_date'])) . '<br> 13:45:00</td>';
                                }
                                // 14:10
                                else if ($row['l_leave_start_time'] == '14:30:00' && $row['l_remark'] == '14:10:00') {
                                    echo '<td>' . date('d-m-Y', strtotime($row['l_leave_start_date'])) . '<br> 14:10:00</td>';
                                }
                                // 14:40
                                else if ($row['l_leave_start_time'] == '15:00:00' && $row['l_remark'] == '14:40:00') {
                                    echo '<td>' . date('d-m-Y', strtotime($row['l_leave_start_date'])) . '<br> 14:40:00</td>';
                                }
                                // 14:45
                                else if ($row['l_leave_start_time'] == '15:00:00' && $row['l_remark'] == '14:45:00') {
                                    echo '<td>' . date('d-m-Y', strtotime($row['l_leave_start_date'])) . '<br> 14:45:00</td>';
                                }
                                // 15:10
                                else if ($row['l_leave_start_time'] == '15:30:00' && $row['l_remark'] == '15:10:00') {
                                    echo '<td>' . date('d-m-Y', strtotime($row['l_leave_start_date'])) . '<br> 15:10:00</td>';
                                }
                                // 15:40
                                else if ($row['l_leave_start_time'] == '16:00:00' && $row['l_remark'] == '15:40:00') {
                                    echo '<td>' . date('d-m-Y', strtotime($row['l_leave_start_date'])) . '<br> 15:40:00</td>';
                                }
                                // 15:45
                                else if ($row['l_leave_start_time'] == '16:00:00' && $row['l_remark'] == '15:45:00') {
                                    echo '<td>' . date('d-m-Y', strtotime($row['l_leave_start_date'])) . '<br> 15:45:00</td>';
                                }
                                // 16:10
                                else if ($row['l_leave_start_time'] == '16:30:00' && $row['l_remark'] == '16:10:00') {
                                    echo '<td>' . date('d-m-Y', strtotime($row['l_leave_start_date'])) . '<br> 16:10:00</td>';
                                }
                                // 16:40
                                else if ($row['l_leave_start_time'] == '17:00:00') {
                                    echo '<td>' . date('d-m-Y', strtotime($row['l_leave_start_date'])) . '<br> 16:40:00</td>';
                                } else {
                                    // กรณีอื่น ๆ แสดงเวลาตาม l_leave_start_time
                                    echo '<td>' . date('d-m-Y', strtotime($row['l_leave_start_date'])) . '<br> ' . $row['l_leave_start_time'] . '</td>';
                                }

                                // 10
                                // 08:45
                                if ($row['l_leave_end_time'] == '09:00:00' && $row['l_remark'] == '08:45:00') {
                                    echo '<td>' . date('d-m-Y', strtotime($row['l_leave_end_date'])) . '<br> 08:45:00</td>';
                                }
                                // 09:45
                                else if ($row['l_leave_end_time'] == '10:00:00' && $row['l_remark'] == '09:45:00') {
                                    echo '<td>' . date('d-m-Y', strtotime($row['l_leave_end_date'])) . '<br> 09:45:00</td>';
                                }
                                // 10:45
                                else if ($row['l_leave_end_time'] == '11:00:00' && $row['l_remark'] == '10:45:00') {
                                    echo '<td>' . date('d-m-Y', strtotime($row['l_leave_end_date'])) . '<br> 10:45:00</td>';
                                }
                                // 11:45
                                else if ($row['l_leave_end_time'] == '12:00:00') {
                                    echo '<td>' . date('d-m-Y', strtotime($row['l_leave_end_date'])) . '<br> 11:45:00</td>';
                                }
                                // 12:45
                                else if ($row['l_leave_end_time'] == '13:00:00') {
                                    echo '<td>' . date('d-m-Y', strtotime($row['l_leave_end_date'])) . '<br> 12:45:00</td>';
                                }
                                // 13:10
                                else if ($row['l_leave_end_time'] == '13:30:00' && $row['l_remark'] == '13:10:00') {
                                    echo '<td>' . date('d-m-Y', strtotime($row['l_leave_end_date'])) . '<br> 13:10:00</td>';
                                }
                                // 13:40
                                else if ($row['l_leave_end_time'] == '14:00:00' && $row['l_remark'] == '13:40:00') {
                                    echo '<td>' . date('d-m-Y', strtotime($row['l_leave_end_date'])) . '<br> 13:40:00</td>';
                                }
                                // 13:45
                                else if ($row['l_leave_end_time'] == '14:00:00' && $row['l_remark'] == '13:45:00') {
                                    echo '<td>' . date('d-m-Y', strtotime($row['l_leave_end_date'])) . '<br> 13:45:00</td>';
                                }
                                // 14:10
                                else if ($row['l_leave_end_time'] == '14:30:00' && $row['l_remark'] == '14:10:00') {
                                    echo '<td>' . date('d-m-Y', strtotime($row['l_leave_end_date'])) . '<br> 14:10:00</td>';
                                }
                                // 14:40
                                else if ($row['l_leave_end_time'] == '15:00:00' && $row['l_remark'] == '14:40:00') {
                                    echo '<td>' . date('d-m-Y', strtotime($row['l_leave_end_date'])) . '<br> 14:40:00</td>';
                                }
                                // 14:45
                                else if ($row['l_leave_end_time'] == '15:00:00' && $row['l_remark'] == '14:45:00') {
                                    echo '<td>' . date('d-m-Y', strtotime($row['l_leave_end_date'])) . '<br> 14:45:00</td>';
                                }
                                // 15:10
                                else if ($row['l_leave_end_time'] == '15:30:00' && $row['l_remark'] == '15:10:00') {
                                    echo '<td>' . date('d-m-Y', strtotime($row['l_leave_end_date'])) . '<br> 15:10:00</td>';
                                }
                                // 15:40
                                else if ($row['l_leave_end_time'] == '16:00:00' && $row['l_remark'] == '15:40:00') {
                                    echo '<td>' . date('d-m-Y', strtotime($row['l_leave_end_date'])) . '<br> 15:40:00</td>';
                                }
                                // 15:45
                                else if ($row['l_leave_end_time'] == '16:00:00' && $row['l_remark'] == '15:45:00') {
                                    echo '<td>' . date('d-m-Y', strtotime($row['l_leave_end_date'])) . '<br> 15:45:00</td>';
                                }
                                // 16:10
                                else if ($row['l_leave_end_time'] == '16:30:00' && $row['l_remark'] == '16:10:00') {
                                    echo '<td>' . date('d-m-Y', strtotime($row['l_leave_end_date'])) . '<br> 16:10:00</td>';
                                }
                                // 16:40
                                else if ($row['l_leave_end_time'] == '17:00:00') {
                                    echo '<td>' . date('d-m-Y', strtotime($row['l_leave_end_date'])) . '<br> 16:40:00</td>';
                                } else {
                                    // กรณีอื่น ๆ แสดงเวลาตาม l_leave_end_time
                                    echo '<td>' . date('d-m-Y', strtotime($row['l_leave_end_date'])) . '<br> ' . $row['l_leave_end_time'] . '</td>';
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
                                    echo $row['l_hr_status'];
                                }
                                echo '</td>';

                                // 27
                                echo '<td>' . $row['l_remark'] . '</td>';

                                // 28
                                echo '<td><button type="button" class="btn btn-warning  edit-btn" data-createdatetime="' . $row['l_create_datetime'] . '" data-usercode="' . $row['l_usercode'] . '">
        <i class="fa-solid fa-pen-to-square"></i>แก้ไข</button></td>';

                                // 29
                                echo '<td><button type="button" class="btn btn-danger cancel-btn" data-createdatetime="' . $row['l_create_datetime'] . '" data-usercode="' . $row['l_usercode'] . '">
        <i class="fa-solid fa-ban"></i> ยกเลิก</button></td>';

                                // 30
                                // if ($row['l_hr_status'] == 1 || $row['l_hr_status'] == 2) {
                                echo "<td><button type='button' class='btn btn-primary leaveChk' data-bs-toggle='modal' data-bs-target='#leaveModal'>$btnCheck</button></td>";
                                // } 
                                // else {
                                //     echo "<td><button type='button' class='btn btn-primary leaveChk' data-bs-toggle='modal' data-bs-target='#leaveModal'>$btnCheck</button></td>";
                                // }

                                // 31
                                echo '<td>
                                 <button type="button" class="btn btn-primary btn-sm view-history" data-usercode="' . $row['l_usercode'] . '"><i class="fa-solid fa-clock-rotate-left"></i></button></td>';

                                echo '</tr>';
                                $rowNumber--;
                            }
                        } else {
                            echo '<tr><td colspan="31" style="text-align: left; color:red;">ไม่พบข้อมูล</td></tr>';
                        }
                    ?>
                </tbody>
            </table>
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
                                            href="?page=1<?php echo isset($_POST['year']) ? '&year=' . $_POST['year'] : ''; ?><?php echo isset($_POST['month']) ? '&month=' . $_POST['month'] : ''; ?>"
                                            aria-label="First">
                                            <span aria-hidden="true">&laquo;&laquo;</span>
                                        </a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link"
                                            href="?page=<?php echo $currentPage - 1; ?><?php echo isset($_POST['year']) ? '&year=' . $_POST['year'] : ''; ?><?php echo isset($_POST['month']) ? '&month=' . $_POST['month'] : ''; ?>"
                                            aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                    <?php endif; ?>

                                    <?php
                                        // ปรับปรุงให้แสดงเฉพาะช่วงของหน้าที่อยู่ใกล้หน้าปัจจุบัน
                                        $startPage = max(1, $currentPage - 2);
                                        $endPage   = min($totalPages, $currentPage + 2);

                                        if ($startPage > 1) {
                                            echo '<li class="page-item disabled"><a class="page-link">...</a></li>';
                                        }

                                        for ($i = $startPage; $i <= $endPage; $i++):
                                    ?>
                                    <li class="page-item<?php echo $i == $currentPage ? 'active' : ''; ?>">
                                        <a class="page-link"
                                            href="?page=<?php echo $i; ?><?php echo isset($_POST['year']) ? '&year=' . $_POST['year'] : ''; ?><?php echo isset($_POST['month']) ? '&month=' . $_POST['month'] : ''; ?>"><?php echo $i; ?></a>
                                    </li>
                                    <?php endfor;

                                        if ($endPage < $totalPages) {
                                            echo '<li class="page-item disabled"><a class="page-link">...</a></li>';
                                        }
                                    ?>

                                    <?php if ($currentPage < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link"
                                            href="?page=<?php echo $currentPage + 1; ?><?php echo isset($_POST['year']) ? '&year=' . $_POST['year'] : ''; ?><?php echo isset($_POST['month']) ? '&month=' . $_POST['month'] : ''; ?>"
                                            aria-label="Next">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link"
                                            href="?page=<?php echo $totalPages; ?><?php echo isset($_POST['year']) ? '&year=' . $_POST['year'] : ''; ?><?php echo isset($_POST['month']) ? '&month=' . $_POST['month'] : ''; ?>"
                                            aria-label="Last">
                                            <span aria-hidden="true">&raquo;&raquo;</span>
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>

                            <!-- ฟอร์มสำหรับกระโดดไปยังหน้าที่ต้องการ -->
                            <div class="d-flex align-items-center me-3">
                                <form id="jumpToPageForm" class="d-flex align-items-center"
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
                                <select id="perPage" class="form-select form-select-md" style="width: 80px;">
                                    <option value="10" <?php echo $itemsPerPage == 10 ? 'selected' : ''; ?>>10</option>
                                    <option value="25" <?php echo $itemsPerPage == 25 ? 'selected' : ''; ?>>25</option>
                                    <option value="50" <?php echo $itemsPerPage == 50 ? 'selected' : ''; ?>>50</option>
                                    <option value="100" <?php echo $itemsPerPage == 100 ? 'selected' : ''; ?>>100
                                    </option>
                                </select>
                                <span class="ms-2">รายการต่อหน้า</span>
                            </div>
                        </div>
                    </div>

                    <!-- ข้อความแสดงรายการอยู่ด้านขวา -->
                    <div class="col-md-6 text-end">
                        <div class="pagination-info">
                            แสดงรายการที่ <?php echo($currentPage - 1) * $itemsPerPage + 1; ?> -
                            <?php echo min($currentPage * $itemsPerPage, $totalRows); ?>
                            จากทั้งหมด<?php echo $totalRows; ?> รายการ
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal เช็คการลา -->
            <div class="modal fade" id="leaveModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
                aria-labelledby="staticBackdropLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title
                        01f s-5" id="staticBackdropLabel">รายละเอียดการลา</h4>
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">X</button>
                        </div>
                        <div class="modal-body">

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger button-shadow">ไม่ผ่าน</button>
                            <button type="button" class="btn btn-success button-shadow">ผ่าน</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Modal แก้ไข-->
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
                                        <!-- <span style="color: red;">* (<input class="form-label" id="editLeaveStartTime2"
                                                value="" style="border: none; width: 70px;  color: red;">เวลาเดิม)
                                        </span> -->
                                        <select class="form-select" id="editLeaveStartTime" name="editLeaveStartTime"
                                            required>

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
                                        <!-- <span style="color: red;">* (<input class="form-label" id="editLeaveEndTime2"
                                                value="" style="border: none; width: 70px; color: red;">เวลาเดิม)
                                        </span> -->
                                        <select class="form-select" id="editLeaveEndTime" name="editLeaveEndTime"
                                            required>

                                        </select>
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
            <!-- Modal HTML -->
            <div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="historyModalLabel">ประวัติการลา</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <!-- ประวัติการลาจะถูกโหลดที่นี่ -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const radioButtons = document.querySelectorAll('input[name="dateRangeType"]');
        const customDateRange = document.getElementById('customDateRange');

        for (const radioButton of radioButtons) {
            radioButton.addEventListener('change', function() {
                if (this.value === 'custom') {
                    customDateRange.style.display = 'block';
                } else {
                    customDateRange.style.display = 'none';
                }
            });
        }
    });
    // เพิ่มตัวแปร Global สำหรับเก็บค่า status ที่กำลังใช้งานอยู่
    var currentFilterStatus;

    // เพิ่มฟังก์ชันสำหรับการเปลี่ยนหน้าด้วย AJAX
    function loadPage(page) {
        // เก็บค่าการค้นหาและการกรองปัจจุบัน
        var selectedYear = $("#selectedYear").val();
        var selectedMonth = $("#selectedMonth").val();

        // ใช้ค่า currentFilterStatus ถ้ามี มิฉะนั้นใช้ค่าจาก active filter card
        var status = currentFilterStatus !== undefined ? currentFilterStatus : $(".filter-card.active").data("status");

        // อัพเดต currentFilterStatus
        currentFilterStatus = status;

        var codeSearch = $("#codeSearch").val();
        var nameSearch = $("#nameSearch").val();
        var leaveSearch = $("#leaveSearch").val();
        var perPage = $("#perPage").val() || 10;

        // แสดง loading
        $("#leaveTable tbody").html(
            '<tr><td colspan="31" class="text-center"><i class="fa fa-spinner fa-spin"></i> กำลังโหลดข้อมูล...</td></tr>'
        );

        $.ajax({
            url: 'a_ajax_get_leave_data.php',
            method: 'GET',
            data: {
                page: page,
                per_page: perPage,
                year: selectedYear,
                month: selectedMonth,
                status: status,
                codeSearch: codeSearch,
                nameSearch: nameSearch,
                leaveSearch: leaveSearch,
                depart: '<?php echo $depart; ?>',
                subDepart: '<?php echo $subDepart; ?>',
                subDepart2: '<?php echo $subDepart2; ?>',
                subDepart3: '<?php echo $subDepart3; ?>',
                subDepart4: '<?php echo $subDepart4; ?>',
                subDepart5: '<?php echo $subDepart5; ?>',
                workplace: '<?php echo $workplace; ?>'
            },
            dataType: 'json',
            success: function(response) {
                // อัพเดทข้อมูลตาราง - ส่ง response.pagination เป็นพารามิเตอร์ที่สอง
                updateTable(response.data, response.pagination);

                // อัพเดท pagination
                updatePagination(response.pagination);

                // อัพเดทข้อความแสดงจำนวนรายการ
                $(".pagination-info").html(
                    'แสดงรายการที่ ' + response.pagination.from + ' - ' +
                    response.pagination.to + ' จากทั้งหมด ' +
                    response.pagination.total_rows + ' รายการ'
                );

                // อัปเดตจำนวนที่แสดงใน card
                $("#total-count-all").text(response.status_counts.all);
                $("#total-count-0").text(response.status_counts['0']);
                $("#total-count-1").text(response.status_counts['1']);
                $("#total-count-2").text(response.status_counts['2']);
                $("#total-count-4").text(response.status_counts['4']);

                updateURLParameter('page', page);
                updateURLParameter('per_page', perPage);

                $(".filter-card").removeClass("active");
                $(".filter-card[data-status='" + currentFilterStatus + "']").addClass("active");
            },
            error: function(xhr, status, error) {
                console.error('Error fetching data:', error);
                $("#leaveTable tbody").html(
                    '<tr><td colspan="26" class="text-danger">เกิดข้อผิดพลาดในการโหลดข้อมูล</td></tr>');
            }
        });
    }

    function formatDateToDMY(dateString) {
        if (!dateString) return '';

        // ตรวจสอบรูปแบบวันที่
        let parts;

        // ถ้าเป็นรูปแบบ yyyy-mm-dd (จากฐานข้อมูล)
        if (dateString.includes('-')) {
            parts = dateString.split('-');
            // ตรวจสอบว่ามี 3 ส่วนคือ ปี-เดือน-วัน
            if (parts.length === 3) {
                // แปลงเป็น วัน-เดือน-ปี
                return parts[2] + '-' + parts[1] + '-' + parts[0];
            }
        }

        // ถ้าไม่ใช่รูปแบบที่รองรับหรือข้อมูลไม่สมบูรณ์ ส่งคืนค่าเดิม
        return dateString;
    }
    // ฟังก์ชันสำหรับแปลงรูปแบบวันที่สร้างรายการเป็น วัน-เดือน-ปี คงเวลาไว้เหมือนเดิม
    function formatCreateDateTime(dateTimeString) {
        if (!dateTimeString) return '';

        // แยกวันที่และเวลา
        let parts = dateTimeString.split(' ');

        if (parts.length >= 1) {
            let datePart = parts[0]; // ส่วนวันที่ (yyyy-mm-dd)
            let timePart = parts.length > 1 ? ' ' + parts[1] : ''; // ส่วนเวลา (hh:mm:ss)

            // แปลงวันที่เป็น dd-mm-yyyy
            if (datePart.includes('-')) {
                let dateParts = datePart.split('-');
                if (dateParts.length === 3) {
                    return dateParts[2] + '-' + dateParts[1] + '-' + dateParts[0] + timePart;
                }
            }
        }

        // ถ้าไม่สามารถแปลงได้ ส่งคืนค่าเดิม
        return dateTimeString;
    }
    // ฟังก์ชันอัพเดตตาราง - แก้ไขให้รับพารามิเตอร์ pagination ด้วย
    function updateTable(data, pagination) {
        var tbody = $("#leaveTable tbody");
        tbody.empty();

        if (data.length === 0) {
            tbody.append('<tr><td colspan="31" class="text-danger" style="text-align: left;">ไม่พบข้อมูล</td></tr>');
            return;
        }

        // คำนวณลำดับรายการ
        var totalRows = pagination.total_rows;
        var currentPage = pagination.current_page;
        var perPage = pagination.per_page;
        var offset = (currentPage - 1) * perPage;

        // สร้างแถวสำหรับแต่ละรายการข้อมูล
        $.each(data, function(index, row) {
            // คำนวณลำดับแถวแบบย้อนกลับ (จากมากไปน้อย)
            var rowNumber = totalRows - offset - index;

            // สถานะใบลา
            var leaveStatus = '';
            if (row['l_leave_status'] == 0) {
                leaveStatus =
                    '<div class="text-success"><?php echo $strStatusNormal ?></div>';
            } else if (row['l_leave_status'] == 1) {
                leaveStatus =
                    '<div class="text-danger"><?php echo $strStatusCancel ?></div>';
            } else {
                leaveStatus = 'ไม่พบสถานะใบลา';
            }

            // สถานะอนุมัติ 1
            var approveStatus;
            if (row['l_approve_status'] == 0) {
                approveStatus =
                    '<div class="text-warning"><b><?php echo $strStatusProve0 ?></b></div>';
            } else if (row['l_approve_status'] == 1) {
                approveStatus =
                    '<div class="text-warning"><b><?php echo $strStatusProve1 ?></b></div>';
            } else if (row['l_approve_status'] == 2) {
                approveStatus =
                    '<div class="text-success"><b><?php echo $strStatusProve2 ?></b></div>';
            } else if (row['l_approve_status'] == 3) {
                approveStatus =
                    '<div class="text-danger"><b><?php echo $strStatusProve3 ?></b></div>';
            } else if (row['l_approve_status'] == 4) {
                approveStatus =
                    '<div class="text-success"><b><?php echo $strStatusProve4 ?></b></div>';
            } else if (row['l_approve_status'] == 5) {
                approveStatus =
                    '<div class="text-danger"><b><?php echo $strStatusProve5 ?></b></div>';
            } else if (row['l_approve_status'] == 6) {
                approveStatus =
                    '';
            } else {
                approveStatus = 'ไม่พบสถานะ';
            }

            // สถานะอนุมัติ 2
            var approveStatus2;
            if (row['l_approve_status2'] == 0) {
                approveStatus2 =
                    '<div class="text-warning"><b><?php echo $strStatusProve0 ?></b></div>';
            } else if (row['l_approve_status2'] == 1) {
                approveStatus2 =
                    '<div class="text-warning"><b><?php echo $strStatusProve1 ?></b></div>';
            } else if (row['l_approve_status2'] == 2) {
                approveStatus2 =
                    '<div class="text-success"><b><?php echo $strStatusProve2 ?></b></div>';
            } else if (row['l_approve_status2'] == 3) {
                approveStatus2 =
                    '<div class="text-danger"><b><?php echo $strStatusProve3 ?></b></div>';
            } else if (row['l_approve_status2'] == 4) {
                approveStatus2 =
                    '<div class="text-success"><b><?php echo $strStatusProve4 ?></b></div>';
            } else if (row['l_approve_status2'] == 5) {
                approveStatus2 =
                    '<div class="text-danger"><b><?php echo $strStatusProve5 ?></b></div>';
            } else if (row['l_approve_status2'] == 6) {
                approveStatus2 =
                    '';
            } else {
                approveStatus2 = 'ไม่พบสถานะ';
            }

            // สถานะอนุมัติ 3
            var approveStatus3;
            if (row['l_approve_status3'] == 0) {
                approveStatus3 =
                    '<div class="text-warning"><b><?php echo $strStatusProve0 ?></b></div>';
            } else if (row['l_approve_status3'] == 1) {
                approveStatus3 =
                    '<div class="text-warning"><b><?php echo $strStatusProve1 ?></b></div>';
            } else if (row['l_approve_status3'] == 2) {
                approveStatus3 =
                    '<div class="text-success"><b><?php echo $strStatusProve2 ?></b></div>';
            } else if (row['l_approve_status3'] == 3) {
                approveStatus3 =
                    '<div class="text-danger"><b><?php echo $strStatusProve3 ?></b></div>';
            } else if (row['l_approve_status3'] == 4) {
                approveStatus3 =
                    '<div class="text-success"><b><?php echo $strStatusProve4 ?></b></div>';
            } else if (row['l_approve_status3'] == 5) {
                approveStatus3 =
                    '<div class="text-danger"><b><?php echo $strStatusProve5 ?></b></div>';
            } else if (row['l_approve_status3'] == 6) {
                approveStatus3 =
                    '';
            } else if (row['l_approve_status3'] == 7) {
                approveStatus3 =
                    '<div class="text-warning"><b>รอ GM อนุมัติ</b></div>';
            } else if (row['l_approve_status3'] == 8) {
                approveStatus3 =
                    '<div class="text-success"><b>GM อนุมัติ</b></div>';
            } else if (row['l_approve_status3'] == 9) {
                approveStatus3 =
                    '<div class="text-danger"><b>GM ไม่อนุมัติ</b></div>';
            } else {
                approveStatus3 = 'ไม่พบสถานะ';
            }

            // สถานะ HR
            var confirmStatus = '';
            if (row['l_hr_status'] == 0) {
                confirmStatus =
                    '<div class="text-warning"><b><?php echo $strStatusHR0 ?></b></div>';
            } else if (row['l_hr_status'] == 1) {
                confirmStatus =
                    '<div class="text-success"><b><?php echo $strStatusHR1 ?></b></div>';
            } else if (row['l_hr_status'] == 2) {
                confirmStatus =
                    '<div class="text-danger"><b><?php echo $strStatusHR2 ?></b></div>';
            } else if (row['l_hr_status'] == 3) {
                confirmStatus =
                    '';
            } else {
                confirmStatus = row['l_hr_status'];
            }

            var newRow = '<tr class="align-middle">' +
                // 0
                '<td hidden>' +
                (row['l_leave_id'] == 1 ?
                    '<span class="text-primary">ลากิจได้รับค่าจ้าง</span>' :
                    '') +
                (row['l_leave_id'] == 2 ?
                    '<span class="text-primary">ลากิจไม่ได้รับค่าจ้าง</span>' :
                    '') +
                (row['l_leave_id'] == 3 ?
                    '<span class="text-primary">ลาป่วย</span>' :
                    '') +
                (row['l_leave_id'] == 4 ?
                    '<span class="text-primary">ลาป่วยจากงาน</span>' :
                    '') +
                (row['l_leave_id'] == 5 ?
                    '<span class="text-primary">ลาพักร้อน</span>' :
                    '') +
                (row['l_leave_id'] == 6 ?
                    '<span class="text-primary">ขาดงาน</span>' :
                    '') +
                (row['l_leave_id'] == 7 ?
                    '<span class="text-primary">มาสาย</span>' :
                    '') +
                (row['l_leave_id'] == 8 ?
                    '<span class="text-primary">อื่น ๆ</span>' :
                    '') +
                '</td>' +

                // 1
                '<td hidden>' + (row['l_name'] ? row['l_name'] : '') +
                '</td>' +

                // 2
                '<td hidden>' + (row['l_department'] ? row['l_department'] :
                    '') +
                '</td>' +

                // 3
                '<td hidden>' + (row['l_leave_reason'] ? row[
                        'l_leave_reason'] :
                    '') +
                '</td>' +

                // 4
                '<td>' + rowNumber + '</td>' +

                // 5
                '<td>' + (row['l_usercode'] ? row['l_usercode'] : '') +
                '</td>' +

                // 6
                '<td>' + '<span class="text-primary">' + (row['l_name'] ?
                    row[
                        'l_name'] : '') + '</span><br>' +
                'แผนก : ' + (row['l_department'] ? row['l_department'] :
                    '') +
                '</td>' +

                // 7
                '<td>' + (row['l_create_datetime'] ? formatCreateDateTime(row['l_create_datetime']) : '') +
                '</td>' + // Creation Date Time
                '<td>';

            // 8
            if (row['l_leave_id'] == 1) {
                newRow +=
                    '<span class="text-primary">ลากิจได้รับค่าจ้าง</span><br>เหตุผล : ' +
                    row['l_leave_reason'];
            } else if (row['l_leave_id'] == 2) {
                newRow +=
                    '<span class="text-primary">ลากิจไม่ได้รับค่าจ้าง</span><br>เหตุผล : ' +
                    row['l_leave_reason'];
            } else if (row['l_leave_id'] == 3) {
                newRow +=
                    '<span class="text-primary">ลาป่วย</span><br>เหตุผล : ' +
                    row['l_leave_reason'];
            } else if (row['l_leave_id'] == 4) {
                newRow +=
                    '<span class="text-primary">ลาป่วยจากงาน</span><br>เหตุผล : ' +
                    row['l_leave_reason'];
            } else if (row['l_leave_id'] == 5) {
                newRow +=
                    '<span class="text-primary">ลาพักร้อน</span><br>เหตุผล : ' +
                    row['l_leave_reason'];
            } else if (row['l_leave_id'] == 6) {
                newRow +=
                    '<span class="text-primary">ขาดงาน</span><br>เหตุผล : ' +
                    row['l_leave_reason'];
            } else if (row['l_leave_id'] == 7) {
                newRow +=
                    '<span class="text-primary">มาสาย</span><br>เหตุผล : ' +
                    row['l_leave_reason'];
            } else if (row['l_leave_id'] == 8) {
                newRow +=
                    '<span class="text-primary">อื่น ๆ</span><br>เหตุผล : ' +
                    row['l_leave_reason'];
            } else if (row['l_leave_id'] == 9) {
                newRow +=
                    '<span class="text-primary">สลับวันหยุด</span><br>เหตุผล : ' +
                    row['l_leave_reason'];
            } else {
                newRow +=
                    '<span class="text-danger">ไม่พบประเภทการลาและเหตุผลการลา</span>';
            }
            newRow += '</td>' +
                // 9
                '<td>' + (row['l_leave_start_date'] ? formatDateToDMY(row['l_leave_start_date']) : '') +
                '<br> ' +
                (row['l_leave_start_time'] ? (
                    // Check if the l_leave_start_time and l_remark match certain values
                    (row['l_leave_start_time'] == '09:00:00' && row[
                        'l_time_remark'] == '08:45:00') ? '08:45:00' :
                    (row['l_leave_start_time'] == '10:00:00' && row[
                        'l_time_remark'] == '09:45:00') ? '09:45:00' :
                    (row['l_leave_start_time'] == '11:00:00' && row[
                        'l_time_remark'] == '10:45:00') ? '10:45:00' :
                    (row['l_leave_start_time'] == '12:00:00') ? '11:45:00' :
                    (row['l_leave_start_time'] == '13:00:00') ? '12:45:00' :
                    (row['l_leave_start_time'] == '13:30:00' && row[
                        'l_time_remark'] == '13:10:00') ? '13:10:00' :
                    (row['l_leave_start_time'] == '14:00:00' && row[
                        'l_time_remark'] == '13:40:00') ? '13:40:00' :
                    (row['l_leave_start_time'] == '14:00:00' && row[
                        'l_time_remark'] == '13:45:00') ? '13:45:00' :
                    (row['l_leave_start_time'] == '14:30:00' && row[
                        'l_time_remark'] == '14:10:00') ? '14:10:00' :
                    (row['l_leave_start_time'] == '15:00:00' && row[
                        'l_time_remark'] == '14:40:00') ? '14:40:00' :
                    (row['l_leave_start_time'] == '15:00:00' && row[
                        'l_time_remark'] == '14:45:00') ? '14:45:00' :
                    (row['l_leave_start_time'] == '15:30:00' && row[
                        'l_time_remark'] == '15:10:00') ? '15:10:00' :
                    (row['l_leave_start_time'] == '16:00:00' && row[
                        'l_time_remark'] == '15:40:00') ? '15:40:00' :
                    (row['l_leave_start_time'] == '16:00:00' && row[
                        'l_time_remark'] == '15:45:00') ? '15:45:00' :
                    (row['l_leave_start_time'] == '16:30:00' && row[
                        'l_time_remark'] == '16:10:00') ? '16:10:00' :
                    (row['l_leave_start_time'] == '17:00:00') ? '16:40:00' :
                    row['l_leave_start_time']
                ) : '') +
                '</td>' +

                // 10
                '<td>' + (row['l_leave_end_date'] ? formatDateToDMY(row['l_leave_end_date']) : '') + '<br> ' +
                (row['l_leave_end_time'] ? (
                    // Check if the l_leave_start_time and l_remark match certain values
                    (row['l_leave_start_time'] == '08:00:00' && row[
                        'l_time_remark2'] == '08:00:00') ? '08:00:00' :
                    (row['l_leave_end_time'] == '09:00:00' && row[
                        'l_time_remark2'] == '08:45:00') ? '08:45:00' :
                    (row['l_leave_end_time'] == '10:00:00' && row[
                        'l_time_remark2'] == '09:45:00') ? '09:45:00' :
                    (row['l_leave_end_time'] == '11:00:00' && row[
                        'l_time_remark2'] == '10:45:00') ? '10:45:00' :
                    (row['l_leave_end_time'] == '12:00:00') ? '11:45:00' :
                    (row['l_leave_end_time'] == '13:00:00') ? '12:45:00' :
                    (row['l_leave_end_time'] == '13:30:00' && row[
                        'l_time_remark2'] == '13:10:00') ? '13:10:00' :
                    (row['l_leave_end_time'] == '14:00:00' && row[
                        'l_time_remark2'] == '13:40:00') ? '13:40:00' :
                    (row['l_leave_end_time'] == '14:00:00' && row[
                        'l_time_remark2'] == '13:45:00') ? '13:45:00' :
                    (row['l_leave_end_time'] == '14:30:00' && row[
                        'l_time_remark2'] == '14:10:00') ? '14:10:00' :
                    (row['l_leave_end_time'] ==
                        '15:00:00' && row[
                            'l_time_remark2'] == '14:40:00') ? '14:40:00' :
                    (row['l_leave_end_time'] == '15:00:00' && row[
                        'l_time_remark2'] == '14:45:00') ? '14:45:00' :
                    (row['l_leave_end_time'] == '15:30:00' && row[
                        'l_time_remark2'] == '15:10:00') ? '15:10:00' :
                    (row['l_leave_end_time'] == '16:00:00' && row[
                        'l_time_remark2'] == '15:40:00') ? '15:40:00' :
                    (row['l_leave_end_time'] == '16:00:00' && row[
                        'l_time_remark2'] == '15:45:00') ? '15:45:00' :
                    (row['l_leave_end_time'] == '16:30:00' && row[
                        'l_time_remark2'] == '16:10:00') ? '16:10:00' :
                    (row['l_leave_end_time'] == '16:30:00' && row[
                        'l_time_remark2'] == '16:15:00') ? '16:15:00' :
                    (row['l_leave_end_time'] == '17:00:00') ? '16:40:00' :
                    row['l_leave_end_time']
                ) : '') +
                '</td>' +

                // 11
                '<td>' + (row['calculated_leave'] ?
                    `<span class="text-primary">${row['calculated_leave'].days} วัน ${row['calculated_leave'].hours} ชั่วโมง ${row['calculated_leave'].minutes} นาที</span>` :
                    '') +
                '</td>';

            // 12
            newRow += '<td>';

            var hasFiles = row['l_file'] || row['l_file2'] || row[
                'l_file3'];
            var fileCount = 0;

            if (row['l_file']) {
                fileCount++;
            }
            if (row['l_file2']) {
                fileCount++;
            }
            if (row['l_file3']) {
                fileCount++;
            }

            if (hasFiles) {
                // สร้างปุ่มเปิดแกลเลอรี่
                var galleryId = 'fileGallery' + row['l_leave_id'] +
                    '_' + row['l_usercode'];
                newRow +=
                    '<button id="imgBtn" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#' +
                    galleryId +
                    '"><i class="fa-solid fa-file"></i> (' +
                    fileCount + ')</button>';

                // สร้าง Modal สำหรับแกลเลอรี่
                newRow += '<div class="modal fade" id="' +
                    galleryId +
                    '" tabindex="-1" aria-hidden="true">' +
                    '<div class="modal-dialog modal-xl">' +
                    '<div class="modal-content">' +
                    '<div class="modal-header">' +
                    '<h5 class="modal-title">ไฟล์แนบทั้งหมด</h5>' +
                    '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>' +
                    '</div>' +
                    '<div class="modal-body">' +
                    '<div class="row">';

                // ส่วนของไฟล์ที่ 1
                if (row['l_file']) {
                    var fileExt = row['l_file'].split('.').pop()
                        .toLowerCase();
                    var isImage = ['jpg', 'jpeg', 'png', 'gif']
                        .includes(fileExt);

                    if (isImage) {
                        newRow += '<div class="col-md-4 mb-3">' +
                            '<div class="card h-100">' +
                            '<img src="../upload/' + row['l_file'] +
                            '" class="card-img-top img-fluid" style="max-height: 500px; object-fit: contain;">' +
                            '<div class="card-body text-center">' +
                            '<h6 class="card-title">ไฟล์ที่ 1</h6>' +
                            '</div></div></div>';
                    } else {
                        newRow += '<div class="col-md-4 mb-3">' +
                            '<div class="card h-100">' +
                            '<div class="card-body text-center">' +
                            '<i class="fa-solid fa-file fa-5x mb-3"></i>' +
                            '<h6 class="card-title">ไฟล์ที่ 1 (' +
                            fileExt + ')</h6>' +
                            '<a href="../upload/' + row['l_file'] +
                            '" class="btn btn-sm btn-primary" target="_blank">เปิดไฟล์</a>' +
                            '</div></div></div>';
                    }
                }

                // ส่วนของไฟล์ที่ 2
                if (row['l_file2']) {
                    var fileExt2 = row['l_file2'].split('.').pop()
                        .toLowerCase();
                    var isImage2 = ['jpg', 'jpeg', 'png', 'gif']
                        .includes(fileExt2);

                    if (isImage2) {
                        newRow += '<div class="col-md-4 mb-3">' +
                            '<div class="card h-100">' +
                            '<img src="../upload/' + row[
                                'l_file2'] +
                            '" class="card-img-top img-fluid" style="max-height: 500px; object-fit: contain;">' +
                            '<div class="card-body text-center">' +
                            '<h6 class="card-title">ไฟล์ที่ 2</h6>' +
                            '</div></div></div>';
                    } else {
                        newRow += '<div class="col-md-4 mb-3">' +
                            '<div class="card h-100">' +
                            '<div class="card-body text-center">' +
                            '<i class="fa-solid fa-file fa-5x mb-3"></i>' +
                            '<h6 class="card-title">ไฟล์ที่ 2 (' +
                            fileExt2 + ')</h6>' +
                            '<a href="../upload/' + row['l_file2'] +
                            '" class="btn btn-sm btn-primary" target="_blank">เปิดไฟล์</a>' +
                            '</div></div></div>';
                    }
                }

                // ส่วนของไฟล์ที่ 3
                if (row['l_file3']) {
                    var fileExt3 = row['l_file3'].split('.').pop()
                        .toLowerCase();
                    var isImage3 = ['jpg', 'jpeg', 'png', 'gif']
                        .includes(fileExt3);

                    if (isImage3) {
                        newRow += '<div class="col-md-4 mb-3">' +
                            '<div class="card h-100">' +
                            '<img src="../upload/' + row[
                                'l_file3'] +
                            '" class="card-img-top img-fluid" style="max-height: 500px; object-fit: contain;">' +
                            '<div class="card-body text-center">' +
                            '<h6 class="card-title">ไฟล์ที่ 3</h6>' +
                            '</div></div></div>';
                    } else {
                        newRow += '<div class="col-md-4 mb-3">' +
                            '<div class="card h-100">' +
                            '<div class="card-body text-center">' +
                            '<i class="fa-solid fa-file fa-5x mb-3"></i>' +
                            '<h6 class="card-title">ไฟล์ที่ 3 (' +
                            fileExt3 + ')</h6>' +
                            '<a href="../upload/' + row['l_file3'] +
                            '" class="btn btn-sm btn-primary" target="_blank">เปิดไฟล์</a>' +
                            '</div></div></div>';
                    }
                }

                // ปิด Modal
                newRow += '</div></div></div></div></div>';

            } else {
                // ถ้าไม่มีไฟล์แนบเลย
                newRow +=
                    '<button id="imgNoBtn" class="btn btn-secondary" disabled><i class="fa-solid fa-file-excel"></i> </button>';
            }

            newRow += '</td>';
            newRow +=
                // 13
                '<td>' + leaveStatus + '</td>' +

                // 14
                '<td>' + (row['l_approve_name'] ? row['l_approve_name'] : '') +
                '</td>' +

                // 15
                '<td>' + approveStatus + '</td>' +

                // 16
                '<td>' + (row['l_approve_datetime'] !== null ? formatCreateDateTime(row[
                    'l_approve_datetime']) : '') + '</td>' +

                // 17
                '<td>' + (row['l_reason'] ? row['l_reason'] : '') + '</td>' +

                // 18
                '<td>' + (row['l_approve_name2'] ? row['l_approve_name2'] :
                    '') + '</td>' +

                // 19
                '<td>' + approveStatus2 + '</td>' +

                // 20
                '<td>' + (row['l_approve_datetime2'] !== null ? formatCreateDateTime(row[
                    'l_approve_datetime2']) : '') + '</td>' +

                // 21
                '<td>' + (row['l_reason2'] ? row['l_reason2'] : '') + '</td>' +

                // 22
                '<td>' + (row['l_approve_name3'] ? row['l_approve_name3'] :
                    '') + '</td>' +

                // 23
                '<td>' + approveStatus3 + '</td>' +

                // 24
                '<td>' + (row['l_approve_datetime3'] !== null ? formatCreateDateTime(row[
                    'l_approve_datetime3']) : '') + '</td>' +

                // 25
                '<td>' + (row['l_reason3'] ? row['l_reason3'] : '') + '</td>' +

                // 26
                '<td>' + confirmStatus + '</td>' +

                // 27
                '<td>' + (row['l_remark'] ? row['l_remark'] : '') + '</td>' +

                //28
                '<td>' +
                '<button type="button" class="btn btn-warning btn-md edit-btn" ' +
                'data-createdatetime="' + row['l_create_datetime'] + '" ' +
                'data-usercode="' + row['l_usercode'] + '">' +
                '<i class="fa-solid fa-pen-to-square"></i> แก้ไข' +
                '</button>' +
                '</td>' +

                // 29
                '<td>' +
                '<button type="button" class="btn btn-danger btn-md cancel-btn" ' +
                'data-createdatetime="' + row['l_create_datetime'] + '" ' +
                'data-usercode="' + row['l_usercode'] + '">' +
                '<i class="fa-solid fa-ban"></i> ยกเลิก' +
                '</button>' +
                '</td>' +

                // 30
                '<td>' +
                '<button type="button" class="btn btn-primary leaveChk" data-bs-toggle="modal" data-bs-target="#leaveModal" ><?php echo $btnCheck ?></button>';

            newRow += '</td>' +
                // 31
                '<td>' +

                '<button type="button" class="btn btn-primary btn-sm view-history" data-usercode="' +
                row['l_usercode'] + '">' +
                '<i class="fa-solid fa-clock-rotate-left"></i></button>' +
                '</td>' +
                '</tr>';

            $("tbody").append(newRow);
        });

        // ผูกเหตุการณ์คลิกสำหรับปุ่มในตาราง
        attachTableEvents();
    }

    // ฟังก์ชันอัพเดต pagination
    function updatePagination(pagination) {
        var paginationHtml = '';

        // สร้างลิงค์ไปยังหน้าแรกและหน้าก่อนหน้า
        if (pagination.current_page > 1) {
            paginationHtml +=
                '<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="loadPage(1)">&laquo;&laquo;</a></li>';
            paginationHtml +=
                '<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="loadPage(' + (
                    pagination
                    .current_page - 1) + ')">&laquo;</a></li>';
        }

        // แสดงเฉพาะช่วงของหน้าที่อยู่ใกล้หน้าปัจจุบัน
        var startPage = Math.max(1, pagination.current_page - 2);
        var endPage = Math.min(pagination.total_pages, pagination.current_page + 2);

        if (startPage > 1) {
            paginationHtml += '<li class="page-item disabled"><a class="page-link">...</a></li>';
        }

        for (var i = startPage; i <= endPage; i++) {
            if (i === pagination.current_page) {
                paginationHtml += '<li class="page-item active"><span class="page-link">' + i + '</span></li>';
            } else {
                paginationHtml +=
                    '<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="loadPage(' + i +
                    ')">' + i + '</a></li>';
            }
        }

        if (endPage < pagination.total_pages) {
            paginationHtml += '<li class="page-item disabled"><a class="page-link">...</a></li>';
        }

        // สร้างลิงค์ไปยังหน้าถัดไปและหน้าสุดท้าย
        if (pagination.current_page < pagination.total_pages) {
            paginationHtml +=
                '<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="loadPage(' + (
                    pagination
                    .current_page + 1) + ')">&raquo;</a></li>';
            paginationHtml +=
                '<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="loadPage(' +
                pagination
                .total_pages + ')">&raquo;&raquo;</a></li>';
        }

        // อัพเดต pagination UI
        $(".pagination").html(paginationHtml);
    }

    // ฟังก์ชันสำหรับอัพเดต URL parameter
    function updateURLParameter(param, value) {
        var url = new URL(window.location.href);
        url.searchParams.set(param, value);
        window.history.pushState({}, '', url);
    }

    // เมื่อเปลี่ยนจำนวนรายการต่อหน้า
    $("#perPage").change(function() {
        loadPage(1); // กลับไปหน้าแรกเมื่อเปลี่ยนจำนวนรายการต่อหน้า
    });

    // เมื่อคลิกที่ลิงค์ pagination ที่สร้างแบบปกติ (ไม่ใช่ AJAX)
    $(document).on('click', '.pagination .page-link', function(e) {
        var href = $(this).attr('href');
        if (href && href.indexOf('javascript:void(0)') === -1) {
            e.preventDefault();
            var page = new URL(href, window.location.origin).searchParams.get('page') || 1;
            loadPage(parseInt(page));
        }
    });

    // ผูกเหตุการณ์คลิกสำหรับปุ่มในตาราง
    function attachTableEvents() {

        // ผูกเหตุการณ์คลิกสำหรับปุ่มตรวจสอบการลา
        $(".leaveChk").click(function() {
            var rowData = $(this).closest("tr").find("td");

            // Populate modal content
            $('#leaveModal .modal-body').html(
                '<table class="table table-bordered">' +
                '<tr>' +
                '<th>รหัสพนักงาน</th>' +
                '<td>' + $(rowData[5]).text() + '</td>' +
                '</tr>' +
                '<tr>' +
                '<th>ชื่อ - นามสกุล</th>' +
                '<td>' + $(rowData[1]).text() + '</td>' +
                '</tr>' +
                '<tr>' +
                '<th>แผนก</th>' +
                '<td>' + $(rowData[2]).text() + '</td>' +
                '</tr>' +
                '<tr>' +
                '<th>วันที่ยื่นใบลา</th>' +
                '<td>' + $(rowData[7]).text() + '</td>' +
                '</tr>' +
                '<tr>' +
                '<th>ประเภทการลา</th>' +
                '<td>' + $(rowData[0]).text() + '</td>' +
                '</tr>' +
                '<tr>' +
                '<th>เหตุผลการลา</th>' +
                '<td>' + $(rowData[3]).text() + '</td>' +
                '</tr>' +
                '<tr>' +
                '<th>วันเวลาที่ลา</th>' +
                '<td>' + $(rowData[9]).text() + ' ถึง ' + $(rowData[10])
                .text() + '</td>' +
                '</tr>' +
                '<tr>' +
                '<th>จำนวนวันลา</th>' +
                '<td>' + $(rowData[11]).text() + '</td>' +
                '</tr>' +
                '<tr>' +
                '<th>สถานะรายการ</th>' +
                '<td>' + $(rowData[13]).html() + '</td>' +
                '</tr>' +
                '</table>'
            );
            $('#leaveModal').modal('show');

            // แก้ไขโค้ดส่วนการคลิกปุ่มตรวจสอบผ่าน
            // แก้ไขโค้ดส่วนการคลิกปุ่มตรวจสอบผ่าน
            $('.modal-footer .btn-success').off('click').on('click',
                function() {
                    var userCode = $(rowData[5]).text().trim(); // รหัสพนักงาน
                    var createDate = $(rowData[7]).text().trim(); // วันที่ยื่นใบลา
                    var leaveType = $(rowData[0]).text().trim(); // ประเภทการลา
                    var empName = $(rowData[1]).text().trim(); // ชื่อพนักงาน
                    var depart = $(rowData[2]).text().trim(); // แผนก
                    var leaveReason = $(rowData[3]).text().trim(); // เหตุผลการลา
                    var leaveStartDate = $(rowData[9]).text().trim(); // วันเวลาที่ลาเริ่มต้น
                    var leaveEndDate = $(rowData[10]).text().trim(); // วันเวลาที่ลาสิ้นสุด
                    var leaveStatus = $(rowData[13]).text().trim(); // สถานะใบลา
                    var checkFirm = '1'; // ผ่าน

                    var userName = '<?php echo $userName; ?>';

                    // เพิ่ม debug log เพื่อตรวจสอบข้อมูลก่อนส่ง
                    console.log("ข้อมูลที่จะส่ง AJAX:", {
                        createDate,
                        userCode,
                        userName,
                        leaveType,
                        leaveReason,
                        leaveStartDate,
                        leaveEndDate,
                        depart,
                        checkFirm,
                        empName,
                        leaveStatus
                    });

                    // แสดง loading
                    Swal.fire({
                        title: 'กำลังดำเนินการ...',
                        text: 'กรุณารอสักครู่',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: 'a_ajax_upd_status.php',
                        method: 'POST',
                        data: {
                            createDate: createDate,
                            userCode: userCode,
                            userName: userName,
                            leaveType: leaveType,
                            leaveReason: leaveReason,
                            leaveStartDate: leaveStartDate,
                            leaveEndDate: leaveEndDate,
                            depart: depart,
                            checkFirm: checkFirm,
                            empName: empName,
                            leaveStatus: leaveStatus
                        },
                        dataType: 'json', // รับข้อมูลกลับมาเป็น JSON
                        success: function(response) {
                            $('#leaveModal').modal('hide');
                            Swal.close();

                            console.log("AJAX Response:", response);

                            // บันทึกสถานะปัจจุบันลงใน localStorage
                            localStorage.setItem('leaveSelectedYear', $("#selectedYear")
                                .val());
                            localStorage.setItem('leaveSelectedMonth', $("#selectedMonth")
                                .val());
                            localStorage.setItem('leaveFilterStatus', $(
                                    ".filter-card.active")
                                .data("status"));
                            localStorage.setItem('leaveCurrentPage', $(
                                ".pagination .active .page-link").text() || 1);
                            localStorage.setItem('leaveCodeSearch', $("#codeSearch").val());
                            localStorage.setItem('leaveNameSearch', $("#nameSearch").val());

                            // ตรวจสอบผลการทำงาน
                            if (response.status === 'success') {
                                Swal.fire({
                                    title: 'สำเร็จ!',
                                    text: response.message || 'ตรวจสอบผ่านสำเร็จ',
                                    icon: 'success',
                                    confirmButtonText: 'ตกลง'
                                }).then(() => {
                                    location
                                        .reload(); // Reload the page after user clicks confirm
                                });
                            } else if (response.status === 'warning') {
                                Swal.fire({
                                    title: 'สำเร็จบางส่วน',
                                    text: response.message ||
                                        'อัปเดตข้อมูลสำเร็จแต่อาจมีบางส่วนที่ไม่สมบูรณ์',
                                    icon: 'warning',
                                    confirmButtonText: 'ตกลง'
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    title: 'ผิดพลาด!',
                                    text: response.message ||
                                        'ไม่สามารถอัปเดตข้อมูลได้',
                                    icon: 'error',
                                    confirmButtonText: 'ตกลง'
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            $('#leaveModal').modal('hide');
                            Swal.close();

                            console.error("AJAX Error:", error);
                            console.log("Response text:", xhr.responseText);

                            Swal.fire({
                                title: 'เกิดข้อผิดพลาด!',
                                text: 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้ กรุณาลองใหม่อีกครั้ง',
                                icon: 'error',
                                confirmButtonText: 'ตกลง'
                            });
                        }
                    });
                });
            $('.modal-footer .btn-danger').off('click').on('click', function() {
                // ซ่อน modal หลัก
                $('#leaveModal').modal('hide');

                // เรียกใช้ SweetAlert2 หลังจากที่ modal หลักถูกซ่อนไปแล้ว
                setTimeout(function() {
                        showInputDialog(); // เรียกใช้ฟังก์ชันเพื่อแสดงกล่องโต้ตอบ
                    },
                    300
                ); // เพิ่ม delay เล็กน้อยเพื่อให้ modal หลักปิดสนิท
            });

            function showInputDialog() {
                Swal.fire({
                    title: 'กรุณากรอกข้อมูล',
                    input: 'text',
                    inputLabel: 'เหตุผล',
                    inputPlaceholder: 'กรอกเหตุผลการไม่ผ่าน',
                    showCancelButton: true,
                    confirmButtonText: 'ตกลง',
                    cancelButtonText: 'ยกเลิก',
                    preConfirm: (inputValue) => {
                        if (!inputValue) {
                            Swal.showValidationMessage(
                                'กรุณากรอกเหตุผลการไม่อนุมัติ');
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const reasonNoProve = result
                            .value; // รับค่าจาก input
                        noApprove(reasonNoProve); // เรียกใช้ฟังก์ชัน noApprove
                    } else {
                        $('#leaveModal').modal('show');
                    }
                });
            }

            function noApprove(reasonNoProve) {
                // แสดง loading ก่อนเริ่มกระบวนการ
                Swal.fire({
                    title: 'กำลังโหลด...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading(); // แสดง icon โหลด
                    }
                });

                var rejectReason = reasonNoProve;
                var userCode = $(rowData[5]).text(); // รหัสพนักงาน
                var createDate = $(rowData[7]).text(); // วันที่ยื่นใบลา
                var leaveType = $(rowData[0]).text(); // ประเภทการลา
                var empName = $(rowData[1]).text(); // ชื่อพนักงาน
                var depart = $(rowData[2]).text(); // แผนก
                var leaveReason = $(rowData[3]).text(); // เหตุผลการลา
                var leaveStartDate = $(rowData[9])
                    .text(); // วันเวลาที่ลาเริ่มต้น
                var leaveEndDate = $(rowData[10]).text(); // วันเวลาที่ลาสิ้นสุด
                var leaveStatus = $(rowData[13]).text(); // สถานะใบลา

                var checkFirm = '2'; // ไม่ผ่าน
                var userName = '<?php echo $userName; ?>';

                $.ajax({
                    url: 'a_ajax_upd_status.php',
                    method: 'POST',
                    data: {
                        createDate: createDate,
                        userCode: userCode,
                        userName: userName,
                        leaveType: leaveType,
                        leaveReason: leaveReason,
                        leaveStartDate: leaveStartDate,
                        leaveEndDate: leaveEndDate,
                        depart: depart,
                        checkFirm: checkFirm,
                        empName: empName,
                        leaveStatus: leaveStatus,
                        rejectReason: rejectReason // เพิ่มเหตุผลการไม่อนุมัติ
                    },
                    success: function(response) {
                        $('#leaveModal').modal('hide'); // ปิด modal

                        var selectedYear = $("#selectedYear").val();
                        var selectedMonth = $("#selectedMonth").val();
                        var filterStatus = $(".filter-card.active").data("status");
                        var currentPage = $(".pagination .active .page-link").text() || 1;
                        var codeSearch = $("#codeSearch").val();
                        var nameSearch = $("#nameSearch").val();

                        localStorage.setItem('leaveFilterStatus', filterStatus);
                        localStorage.setItem('leaveCurrentPage', currentPage);
                        localStorage.setItem('leaveSelectedYear', selectedYear);
                        localStorage.setItem('leaveSelectedMonth', selectedMonth);
                        localStorage.setItem('leaveCodeSearch', codeSearch);
                        localStorage.setItem('leaveNameSearch', nameSearch);

                        Swal.fire({
                            title: 'ไม่ผ่านสำเร็จ !',
                            icon: 'success',
                            confirmButtonText: 'ตกลง'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                var currentUrl = new URL(window.location.href);
                                currentUrl.searchParams.set('page', currentPage);
                                currentUrl.searchParams.set('year', selectedYear);
                                currentUrl.searchParams.set('month', selectedMonth);
                                currentUrl.searchParams.set('status',
                                    filterStatus);
                                if (codeSearch) currentUrl.searchParams.set(
                                    'codeSearch',
                                    codeSearch);
                                if (nameSearch) {
                                    currentUrl.searchParams.set('nameSearch',
                                        nameSearch);
                                }
                                window.location.href = currentUrl.toString();
                            }
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                });
            }
        });

        // ผูกเหตุการณ์คลิกสำหรับปุ่มดูประวัติ
        $('.view-history').click(function() {
            var userCode = $(this).data('usercode');
            var selectedYear = $("#selectedYear").val();
            var selectedMonth = $("#selectedMonth").val();

            $.ajax({
                url: 'a_ajax_get_leave_history.php',
                type: 'POST',
                data: {
                    userCode: userCode,
                    selectedYear: selectedYear,
                    selectedMonth: selectedMonth
                },
                success: function(response) {
                    // แสดงข้อมูลประวัติการลาหรือทำสิ่งที่ต้องการหลังจากได้รับข้อมูล
                    $('#historyModal .modal-body').html(response);
                    $('#historyModal').modal('show');
                },
                error: function() {
                    alert('เกิดข้อผิดพลาดในการดึงข้อมูล');
                }
            });
        });

        $('.edit-btn').click(function() {
            var createDatetime = $(this).data('createdatetime');
            var userCode = $(this).data('usercode');

            console.log("กดปุ่มแก้ไข - createDatetime:", createDatetime, "userCode:", userCode);

            // เก็บข้อมูลไว้ใน form data attributes
            $('#editLeaveForm').data('createdatetime', createDatetime);
            $('#editLeaveForm').data('usercode', userCode);

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

            // เปิด Modal ก่อนที่จะโหลดข้อมูล
            $('#editLeaveModal').modal('show');

            // เพิ่มการโหลดวันหยุดและตั้งค่า flatpickr หลังจากที่ Modal เปิด
            $.ajax({
                url: 'a_ajax_get_holiday.php',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    // ต้องตรวจสอบว่า response.holidays มีค่าและเป็น array หรือไม่
                    var disableDates = [];
                    if (response && response.holidays && Array.isArray(response.holidays)) {
                        disableDates = response.holidays;
                    }
                    // สร้าง flatpickr สำหรับวันที่เริ่มต้น (ลบ locale ออก)
                    flatpickr("#editLeaveStartDate", {
                        dateFormat: "d-m-Y",
                        disable: disableDates // ใช้ค่าที่ตรวจสอบแล้ว
                    });
                    // สร้าง flatpickr สำหรับวันที่สิ้นสุด (ลบ locale ออก)
                    flatpickr("#editLeaveEndDate", {
                        dateFormat: "d-m-Y",
                        disable: disableDates // ใช้ค่าที่ตรวจสอบแล้ว
                    });
                },
                error: function(xhr, status, error) {
                    console.error("ไม่สามารถโหลดวันหยุดได้:", error);
                    // ถ้าเกิดข้อผิดพลาด ให้สร้าง flatpickr โดยไม่มีการปิดวันที่
                    flatpickr("#editLeaveStartDate", {
                        dateFormat: "d-m-Y"
                    });
                    flatpickr("#editLeaveEndDate", {
                        dateFormat: "d-m-Y"
                    });
                }
            });

            $.ajax({
                url: 'a_ajax_get_leave.php',
                type: 'POST',
                data: {
                    createDatetime: createDatetime,
                    userCode: userCode
                },
                dataType: 'json',
                success: function(response) {
                    console.log("ดึงข้อมูลสำเร็จ:", response);

                    if (response.error) {
                        alert(response.error);
                        $('#editLeaveModal').modal('hide');
                    } else {
                        // ใส่ข้อมูลในฟอร์ม Modal
                        $('.editLeaveType').val(response.l_leave_id);
                        $('#editLeaveReason').val(response.l_leave_reason);
                        $('#editLeaveStartDate').val(response.l_leave_start_date);
                        $('#editLeaveEndDate').val(response.l_leave_end_date);
                        $('#editTelPhone').val(response.l_phone);

                        // กำหนดค่า select สำหรับเวลาเริ่มต้น - ให้ความสำคัญกับ l_time_remark เป็นอันดับแรก
                        var startTimeValue = "08:00"; // ค่าเริ่มต้น

                        if (response.l_time_remark && response.l_time_remark.trim() !== "") {
                            // ใช้ค่า l_time_remark เป็นหลัก ถ้ามี
                            startTimeValue = response.l_time_remark.substring(0, 5);
                        } else {
                            // ถ้าไม่มี l_time_remark ให้แปลงจาก l_leave_start_time
                            if (response.l_leave_start_time === "12:00:00") {
                                startTimeValue = "11:45";
                            } else if (response.l_leave_start_time === "13:00:00") {
                                startTimeValue = "12:45";
                            } else if (response.l_leave_start_time === "17:00:00") {
                                startTimeValue = "16:40";
                            } else if (response.l_leave_start_time) {
                                startTimeValue = response.l_leave_start_time.substring(0, 5);
                            }
                        }

                        // เลือก option ที่มีค่าตรงกับ startTimeValue
                        $('#editLeaveStartTime').val(startTimeValue);
                        if (document.getElementById('editLeaveStartTime2')) {
                            $('#editLeaveStartTime2').val(startTimeValue); // แสดงในช่องเวลาเดิม
                        }

                        // กำหนดค่า select สำหรับเวลาสิ้นสุด - ให้ความสำคัญกับ l_time_remark2 เป็นอันดับแรก
                        var endTimeValue = "16:40"; // ค่าเริ่มต้น

                        if (response.l_time_remark2 && response.l_time_remark2.trim() !== "") {
                            // ใช้ค่า l_time_remark2 เป็นหลัก ถ้ามี
                            endTimeValue = response.l_time_remark2.substring(0, 5);
                        } else {
                            // ถ้าไม่มี l_time_remark2 ให้แปลงจาก l_leave_end_time
                            if (response.l_leave_end_time === "12:00:00") {
                                endTimeValue = "11:45";
                            } else if (response.l_leave_end_time === "13:00:00") {
                                endTimeValue = "12:45";
                            } else if (response.l_leave_end_time === "17:00:00") {
                                endTimeValue = "16:40";
                            } else if (response.l_leave_end_time) {
                                endTimeValue = response.l_leave_end_time.substring(0, 5);
                            }
                        }

                        // เลือก option ที่มีค่าตรงกับ endTimeValue
                        $('#editLeaveEndTime').val(endTimeValue);
                        if (document.getElementById('editLeaveEndTime2')) {
                            $('#editLeaveEndTime2').val(endTimeValue); // แสดงในช่องเวลาเดิม
                        }

                        // จัดการไฟล์เดิม
                        var existingFile = response.l_file;
                        if (existingFile && existingFile.trim() !== "") {
                            var fileUrl = '../upload/' + existingFile;
                            var previewImage = $('#imagePreview');
                            if (previewImage.length > 0) {
                                previewImage.attr('src', fileUrl);
                                previewImage.show();
                            }
                        } else {
                            $('#imagePreview').hide();
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error("เกิดข้อผิดพลาดในการดึงข้อมูล:", error);
                    console.log("Response text:", xhr.responseText);
                    alert('ไม่สามารถดึงข้อมูลการลาได้');
                    $('#editLeaveModal').modal('hide');
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

        $('#editLeaveForm').submit(function(e) {
            e.preventDefault();
            console.log("กำลัง submit form");

            // เก็บข้อมูลจาก data attributes และฟอร์ม
            var formData = new FormData();
            formData.append('userName', '<?php echo $userName ?>');
            formData.append('editCreateDateTime', $('#editLeaveForm').data('createdatetime'));
            formData.append('editUserCode', $('#editLeaveForm').data('usercode'));
            formData.append('editLeaveType', $('.editLeaveType').val());
            formData.append('editLeaveReason', $('#editLeaveReason').val());
            formData.append('editLeaveStartDate', $('#editLeaveStartDate').val());
            formData.append('editLeaveEndDate', $('#editLeaveEndDate').val());
            formData.append('editLeaveStartTime', $('#editLeaveStartTime').val());
            formData.append('editLeaveEndTime', $('#editLeaveEndTime').val());
            formData.append('editTelPhone', $('#editTelPhone').val());

            // ตรวจสอบวันที่และเวลา
            var startDateParts = $('#editLeaveStartDate').val().split('-');
            var endDateParts = $('#editLeaveEndDate').val().split('-');

            // ถ้าวันที่อยู่ในรูปแบบ dd-mm-yyyy
            if (startDateParts.length === 3 && endDateParts.length === 3) {
                var startDate = new Date(startDateParts[2], startDateParts[1] - 1, startDateParts[0]);
                var endDate = new Date(endDateParts[2], endDateParts[1] - 1, endDateParts[0]);

                if (endDate < startDate) {
                    Swal.fire({
                        title: 'ไม่สามารถลาได้',
                        text: 'วันที่สิ้นสุดต้องมากกว่าหรือเท่ากับวันที่เริ่มต้น',
                        icon: 'error',
                        confirmButtonText: 'ตกลง'
                    });
                    return false;
                }
            }

            var startTime = $('#editLeaveStartTime').val();
            var endTime = $('#editLeaveEndTime').val();

            if (startDateParts[0] === endDateParts[0] && startDateParts[1] === endDateParts[1] &&
                startDateParts[2] === endDateParts[2] && startTime > endTime) {
                Swal.fire({
                    title: 'ไม่สามารถลาได้',
                    text: 'เวลาสิ้นสุดต้องมากกว่าหรือเท่ากับเวลาเริ่มต้น (ในวันเดียวกัน)',
                    icon: 'error',
                    confirmButtonText: 'ตกลง'
                });
                return false;
            }

            // เพิ่มไฟล์แนบ (ถ้ามี)
            var files = $('#editFile')[0].files;
            if (files.length > 0) {
                for (var i = 0; i < files.length; i++) {
                    formData.append('editFile[]', files[i]);
                }
            }

            // ล็อก formData เพื่อตรวจสอบ
            console.log("ข้อมูลที่จะส่ง:");
            for (var pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }

            // แสดง loading
            Swal.fire({
                title: 'กำลังบันทึกข้อมูล...',
                text: 'กรุณารอสักครู่',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // ส่งข้อมูลไปยังเซิร์ฟเวอร์
            $.ajax({
                url: 'a_ajax_upd_leave.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    console.log("ผลการบันทึกข้อมูล:", response);
                    Swal.close();

                    if (response.status === 'success') {
                        Swal.fire({
                            title: 'บันทึกสำเร็จ',
                            text: 'แก้ไขข้อมูลการลาเรียบร้อยแล้ว',
                            icon: 'success',
                            confirmButtonText: 'ตกลง'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                location.reload();
                            }
                        });
                    } else {
                        let errorMsg = response.message || 'เกิดข้อผิดพลาดในการบันทึกข้อมูล';
                        Swal.fire({
                            title: 'เกิดข้อผิดพลาด',
                            text: errorMsg,
                            icon: 'error',
                            confirmButtonText: 'ตกลง'
                        });

                        // แสดงข้อมูลเพิ่มเติมใน console สำหรับการ debug
                        if (response.debug) {
                            console.log("Debug info:", response.debug);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error("เกิดข้อผิดพลาดในการส่งข้อมูล:", error);
                    console.log("Response text:", xhr.responseText);
                    Swal.close();

                    Swal.fire({
                        title: 'เกิดข้อผิดพลาด',
                        text: 'ไม่สามารถบันทึกข้อมูลได้ กรุณาลองใหม่อีกครั้ง',
                        icon: 'error',
                        confirmButtonText: 'ตกลง'
                    });
                }
            });
        });

        $('.cancel-btn').click(function() {
            var createDateTime = $(this).data('createdatetime'); // ดึงค่า createDateTime
            var userCode = $(this).data('usercode'); // ดึงค่า userCode
            var nameCan = "<?php echo $userName; ?>";

            var rowData = $(this).closest('tr').find('td');

            var leaveType = $(rowData[0]).text(); // ประเภทการลา
            var leaveReason = $(rowData[3]).text(); // เหตุผลการลา
            var leaveStartDate = $(rowData[9]).text(); // วันเวลาที่ลาเริ่มต้น
            var leaveEndDate = $(rowData[10]).text(); // วันเวลาที่ลาสิ้นสุด

            Swal.fire({
                title: 'ยืนยันการยกเลิก?',
                text: "คุณต้องการยกเลิกรายการนี้หรือไม่?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'ใช่',
                cancelButtonText: 'ไม่'
            }).then((result) => {
                if (result.isConfirmed) {
                    // เก็บค่าตัวกรองปัจจุบันไว้ก่อนส่ง Ajax
                    var selectedYear = $("#selectedYear").val();
                    var selectedMonth = $("#selectedMonth").val();
                    var filterStatus = $(".filter-card.active").data("status");
                    var currentPage = $(".pagination .active .page-link").text() || 1;
                    var codeSearch = $("#codeSearch").val();
                    var nameSearch = $("#nameSearch").val();
                    var leaveSearch = $("#leaveSearch").val();
                    var perPage = $("#perPage").val() || 10;

                    // บันทึกลงใน localStorage เพื่อใช้หลังจาก reload
                    localStorage.setItem('leaveSelectedYear', selectedYear);
                    localStorage.setItem('leaveSelectedMonth', selectedMonth);
                    localStorage.setItem('leaveFilterStatus', filterStatus);
                    localStorage.setItem('leaveCurrentPage', currentPage);
                    localStorage.setItem('leaveCodeSearch', codeSearch);
                    localStorage.setItem('leaveNameSearch', nameSearch);
                    localStorage.setItem('leavePerPage', perPage);
                    if (leaveSearch) {
                        localStorage.setItem('leaveSearch', leaveSearch);
                    }

                    $.ajax({
                        url: 'a_ajax_delete_leave.php',
                        type: 'POST',
                        data: {
                            createDateTime: createDateTime,
                            userCode: userCode,
                            nameCan: nameCan,
                            leaveType: leaveType,
                            leaveReason: leaveReason,
                            leaveStartDate: leaveStartDate,
                            leaveEndDate: leaveEndDate
                        },
                        success: function(response) {
                            if (response === 'success') {
                                Swal.fire(
                                    'สำเร็จ!',
                                    'ยกเลิกรายการเรียบร้อยแล้ว',
                                    'success'
                                ).then(() => {
                                    location
                                        .reload(); // หน้าจะ reload และโค้ดใน $(document).ready จะโหลดค่าที่บันทึกไว้
                                });
                            } else {
                                Swal.fire(
                                    'ผิดพลาด!',
                                    'ไม่สามารถยกเลิกรายการได้',
                                    'error'
                                );
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error:', error);
                            Swal.fire(
                                'ผิดพลาด!',
                                'เกิดข้อผิดพลาดในการเชื่อมต่อ',
                                'error'
                            );
                        }
                    });
                }
            });
        });
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

        // Call loadPage with the requested page number instead of submitting the form
        loadPage(parseInt(pageInput.value));

        // Return false to prevent the form's default submission
        return false;
    }

    // ทำการโหลดข้อมูลเมื่อค้นหาหรือกรอง
    $("#codeSearch, #nameSearch").on("keyup", function() {
        // รอสักครู่ก่อนส่งคำขอ AJAX เพื่อลดการส่งคำขอมากเกินไป
        clearTimeout($.data(this, 'timer'));
        var search_term = $(this).val();
        $.data(this, 'timer', setTimeout(function() {
            loadPage(1); // กลับไปหน้าแรกเมื่อค้นหา
        }, 500));
    });

    // เมื่อคลิกที่การ์ดกรอง
    $(".filter-card").click(function() {
        // ลบ active จากการ์ดทั้งหมด
        $(".filter-card").removeClass("active");
        $(".filter-card .card").removeClass("active");

        // เพิ่ม active ให้การ์ดที่คลิกห
        $(this).addClass("active");
        $(this).find(".card").addClass("active");

        // อัพเดตค่า currentFilterStatus
        currentFilterStatus = $(this).data("status");

        loadPage(1); // กลับไปหน้าแรกเมื่อกรอง
    });

    $(document).ready(function() {
        var savedFilterStatus = localStorage.getItem('leaveFilterStatus');
        var savedCurrentPage = localStorage.getItem('leaveCurrentPage');
        var savedSelectedYear = localStorage.getItem('leaveSelectedYear');
        var savedSelectedMonth = localStorage.getItem('leaveSelectedMonth');
        var savedCodeSearch = localStorage.getItem('leaveCodeSearch');
        var savedNameSearch = localStorage.getItem('leaveNameSearch'); // Get saved name search

        var urlFilterStatus = new URLSearchParams(window.location.search).get('status');
        var urlCodeSearch = new URLSearchParams(window.location.search).get('codeSearch');
        var urlNameSearch = new URLSearchParams(window.location.search).get(
            'nameSearch'); // Get URL name search

        var filterStatusToUse = urlFilterStatus || savedFilterStatus || 'all';
        var codeSearchToUse = urlCodeSearch || savedCodeSearch || '';
        var nameSearchToUse = urlNameSearch || savedNameSearch || ''; // Determine name search to use

        // ล้างสถานะเดิม
        $(".filter-card").removeClass("active");
        $(".filter-card .card").removeClass("active");

        // กำหนดสถานะตามที่บันทึกไว้หรือจาก URL
        $(".filter-card[data-status='" + filterStatusToUse + "']").addClass("active");
        $(".filter-card[data-status='" + filterStatusToUse + "'] .card").addClass("active");

        // ตั้งค่า currentFilterStatus
        currentFilterStatus = filterStatusToUse;

        // ตั้งค่าการค้นหารหัสพนักงาน (ถ้ามี)
        if (codeSearchToUse) {
            $("#codeSearch").val(codeSearchToUse);
        }

        // ตั้งค่าการค้นหาชื่อพนักงาน (ถ้ามี)
        if (nameSearchToUse) {
            $("#nameSearch").val(nameSearchToUse);
        }

        // ล้างค่าใน localStorage เพื่อไม่ให้มีผลในครั้งต่อไป
        localStorage.removeItem('leaveFilterStatus');
        localStorage.removeItem('leaveCodeSearch');
        localStorage.removeItem('leaveNameSearch'); // Clear saved name search

        if (savedSelectedYear) {
            $("#selectedYear").val(savedSelectedYear);
            localStorage.removeItem('leaveSelectedYear');
        }

        if (savedSelectedMonth) {
            $("#selectedMonth").val(savedSelectedMonth);
            localStorage.removeItem('leaveSelectedMonth');
        }

        // หมายเหตุ: ส่วนนี้คงไว้เหมือนเดิม
        if ($(".pagination-info").length === 0) {
            $(".container-fluid").append(
                '<div class="row mt-3">' +
                '<div class="col-md-6">' +
                '<div class="pagination-info">แสดงรายการที่ 1 - 10 จากทั้งหมด 0 รายการ</div>' +
                '</div>' +
                '<div class="col-md-6 text-end">' +
                '<div class="form-inline d-inline-flex">' +
                '<label for="perPage" class="me-2">แสดง:</label>' +
                '<select id="perPage" class="form-select form-select-md me-2" style="width: 80px;">' +
                '<option value="10" selected>10</option>' +
                '<option value="25">25</option>' +
                '<option value="50">50</option>' +
                '<option value="100">100</option>' +
                '</select>' +
                '<span>รายการต่อหน้า</span>' +
                '</div>' +
                '</div>' +
                '</div>'
            );
        }

        var pageToLoad = savedCurrentPage || 1;
        if (savedCurrentPage) {
            localStorage.removeItem('leaveCurrentPage');
        }
        loadPage(parseInt(pageToLoad));

        // แสดงข้อความช่วยเหลือเมื่อ hover เมาส์เหนือช่องค้นหา
        $("#codeSearch").attr("placeholder", "รหัสพนักงาน");
        $("#nameSearch").attr("placeholder", "ชื่อพนักงาน");
        // $("#leaveSearch").attr("placeholder", "ค้นหาประเภทการลา");

        // Cuando se abre el modal de exportación
        $('#exportModal').on('show.bs.modal', function(e) {
            // Obtener el valor actual de búsqueda de código de empleado
            const codeSearchValue = $('#codeSearch').val();

            // Agregar o actualizar un campo oculto para el código de búsqueda
            if ($('input[name="codeSearch"]', '#exportModal form').length === 0) {
                // Si no existe el campo, crearlo
                const codeSearchField = $('<input>').attr({
                    type: 'hidden',
                    name: 'codeSearch',
                    value: codeSearchValue
                });
                $('#exportModal form').append(codeSearchField);
            } else {
                // Si ya existe, actualizar su valor
                $('input[name="codeSearch"]', '#exportModal form').val(codeSearchValue);
            }
        });

        // Manejar el envío del formulario de exportación
        $('#exportModal form').submit(function(e) {
            e.preventDefault();
            $('#exportModal').modal('hide');

            // Mostrar un indicador de carga
            Swal.fire({
                title: 'กำลังส่งออกข้อมูล...',
                text: 'กรุณารอสักครู่',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const formData = $(this).serialize();

            // Primero, verificar si hay datos para exportar
            $.ajax({
                url: 'export_leave_data.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    // Si hay un error, mostrar el mensaje de error
                    if (response.status === 'error') {
                        Swal.fire({
                            title: 'ข้อผิดพลาด',
                            text: response.message,
                            icon: 'error',
                            confirmButtonText: 'ตกลง'
                        });
                    }
                },
                error: function(xhr) {
                    // Si la respuesta no es JSON, significa que se está descargando el archivo
                    if (xhr.status === 200) {
                        // Descargar el archivo - usar la misma URL pero con descarga directa
                        const downloadForm = $('<form>')
                            .attr('action', 'export_leave_data.php')
                            .attr('method', 'post')
                            .css('display', 'none');

                        // Copiar todos los campos del formulario original
                        $('#exportModal form input, #exportModal form select')
                            .each(function() {
                                const input = $('<input>')
                                    .attr('type', 'hidden')
                                    .attr('name', $(this).attr('name'))
                                    .val($(this).val());
                                downloadForm.append(input);
                            });

                        $('body').append(downloadForm);
                        downloadForm.submit();
                        downloadForm.remove();

                        // Cerrar el modal
                        $('#exportModal').modal('hide');

                        // Mostrar mensaje de éxito y redirigir a la página de administración
                        Swal.fire({
                            title: 'สำเร็จ!',
                            text: 'ส่งออกข้อมูลสำเร็จแล้ว',
                            icon: 'success',
                            confirmButtonText: 'ตกลง'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Redirigir a la página de administración
                                window.location.href = 'admin_dashboard.php';
                            }
                        });
                    } else {
                        // Mostrar mensaje de error en caso de problemas con la solicitud
                        Swal.fire({
                            title: 'ข้อผิดพลาด',
                            text: 'เกิดข้อผิดพลาดในการส่งออกข้อมูล โปรดลองอีกครั้ง',
                            icon: 'error',
                            confirmButtonText: 'ตกลง'
                        });
                    }
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