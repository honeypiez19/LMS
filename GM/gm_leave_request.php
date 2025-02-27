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
</head>

<body>
    <?php include 'gm_navbar.php'?>

    <!--                                                                                                                                                                         <?php echo $subDepart; ?>
<?php echo $subDepart2; ?>
<?php echo $userName; ?> -->

    <nav class="navbar bg-body-tertiary" style="background-color: #072ac8; box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.3);
  border: none;">
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

                    if (isset($_POST['year'])) {
                        $selectedYear = $_POST['year'];
                        $startDate    = date("Y-m-d", strtotime(($selectedYear - 1) . "-12-01"));
                        $endDate      = date("Y-m-d", strtotime($selectedYear . "-11-30"));
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
                        'All' => 'ทั้งหมด',
                        '01'  => 'มกราคม',
                        '02'  => 'กุมภาพันธ์',
                        '03'  => 'มีนาคม',
                        '04'  => 'เมษายน',
                        '05'  => 'พฤษภาคม',
                        '06'  => 'มิถุนายน',
                        '07'  => 'กรกฎาคม',
                        '08'  => 'สิงหาคม',
                        '09'  => 'กันยายน',
                        '10'  => 'ตุลาคม',
                        '11'  => 'พฤศจิกายน',
                        '12'  => 'ธันวาคม',
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
        </form>
    </div>

    <div class="container">
        <div class="mt-3 row">
            <div class="col-3 filter-card" data-status="all">
                <div class="card text-bg-primary mb-3">
                    <!-- <div class="card-header">รายการลาทั้งหมด</div> -->
                    <div class="card-body">
                        <h5 class="card-title">
                            <?php
                                // เตรียมคำสั่ง SQL
                                $sql = "SELECT
    COUNT(li.l_list_id) AS leave_count,
    li.l_name
FROM
leave_list li
WHERE
li.l_department <> 'RD'
AND li.l_leave_id NOT IN (6, 7)
AND li.l_level IN ('user', 'chief', 'leader','admin','assisManager','manager','subLeader')
    AND (
        YEAR(li.l_create_datetime) = :selectedYear
        OR YEAR(li.l_leave_end_date) = :selectedYear
    )";
                                if ($selectedMonth != "All") {
                                    $sql .= " AND (
        Month(li.l_create_datetime) = :selectedMonth
        OR Month(li.l_leave_end_date) = :selectedMonth
    ) ";
                                }
                                // เตรียมและรัน query
                                $stmt = $conn->prepare($sql);
                                // $stmt->bindParam(':subDepart', $subDepart);
                                if ($selectedMonth != "All") {
                                    $stmt->bindParam(':selectedMonth', $selectedMonth, PDO::PARAM_INT);
                                }
                                $stmt->bindParam(':selectedYear', $selectedYear);

                                $stmt->execute();

                                // ดึงผลลัพธ์
                                $totalLeaveItems = $stmt->fetchColumn();

                            ?>
                            <div class="d-flex justify-content-between">
                                <?php echo $totalLeaveItems; ?>
                                <!-- <i class="mt-4 fas fa-file-alt ml-2 fa-2xl"></i> -->
                                <i class="mt-4 fa-regular fa-folder-open fa-2xl"></i>
                            </div>
                        </h5>
                        <p class="card-text">
                            รายการลาทั้งหมด
                        </p>
                    </div>
                </div>
            </div>

            <!-- รายการลาที่รออนุมัติ -->
            <div class="col-3 filter-card" data-status="7">
                <div class="card text-bg-warning mb-3">
                    <div class="card-body">
                        <h5 class="card-title">
                            <?php
                                $sql = "SELECT
    COUNT(li.l_list_id) AS leave_count,
    li.l_name
FROM
leave_list li
WHERE
li.l_department <> 'RD'
AND li.l_leave_id NOT IN (6, 7)
AND li.l_level IN ('user', 'chief', 'leader','admin','assisManager','manager','subLeader')
AND li.l_approve_status3 = 7
 AND (
        YEAR(li.l_create_datetime) = :selectedYear
        OR YEAR(li.l_leave_end_date) = :selectedYear
    )";
                                if ($selectedMonth != "All") {
                                    $sql .= " AND (
        Month(li.l_create_datetime) = :selectedMonth
        OR Month(li.l_leave_end_date) = :selectedMonth
    ) ";
                                }

                                // เตรียมและรัน query
                                $stmt = $conn->prepare($sql);
                                // $stmt->bindParam(':subDepart', $subDepart);
                                if ($selectedMonth != "All") {
                                    $stmt->bindParam(':selectedMonth', $selectedMonth, PDO::PARAM_INT);
                                }
                                $stmt->bindParam(':selectedYear', $selectedYear);

                                $stmt->execute();

                                // ดึงผลลัพธ์
                                $totalLeaveItems = $stmt->fetchColumn();
                            ?>
                            <div class="d-flex justify-content-between">
                                <?php echo $totalLeaveItems; ?>
                                <!-- <i class="mt-4 fa-solid fa-clock-rotate-left fa-2xl" style="color: #ffffff;"></i> -->
                                <i class="mt-4 fa-solid fa-hourglass-half fa-2xl" style="color: #ffffff;"></i>
                            </div>
                        </h5>
                        <p class="card-text">
                            รายการลาที่รออนุมัติ
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-3 filter-card" data-status="8">
                <div class="card text-bg-success mb-3">
                    <!-- <div class="card-header">รายการลาทั้งหมด</div> -->
                    <div class="card-body">
                        <h5 class="card-title">
                            <?php
                                $sql = "SELECT
    COUNT(li.l_list_id) AS leave_count,
    li.l_name
FROM
leave_list li
WHERE
li.l_department <> 'RD'
AND li.l_leave_id NOT IN (6, 7)
AND li.l_level IN ('user', 'chief', 'leader','admin','assisManager','manager','subLeader')
AND li.l_approve_status3 = 8
 AND (
        YEAR(li.l_create_datetime) = :selectedYear
        OR YEAR(li.l_leave_end_date) = :selectedYear
    )";

                                if ($selectedMonth != "All") {
                                    $sql .= " AND (
        Month(li.l_create_datetime) = :selectedMonth
        OR Month(li.l_leave_end_date) = :selectedMonth
    ) ";
                                }

                                // เตรียมและรัน query
                                $stmt = $conn->prepare($sql);
                                // $stmt->bindParam(':subDepart', $subDepart);
                                if ($selectedMonth != "All") {
                                    $stmt->bindParam(':selectedMonth', $selectedMonth, PDO::PARAM_INT);
                                }

                                $stmt->bindParam(':selectedYear', $selectedYear);
                                $stmt->execute();

                                // ดึงผลลัพธ์
                                $totalLeaveItems = $stmt->fetchColumn();
                            ?>
                            <div class="d-flex justify-content-between">
                                <?php echo $totalLeaveItems; ?>
                                <!-- <i class="mt-4 fa-solid fa-thumbs-up fa-2xl"></i> -->
                                <i class="mt-4 fa-regular fa-face-smile fa-2xl"></i>
                            </div>
                        </h5>
                        <p class="card-text">
                            รายการลาที่อนุมัติ
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-3 filter-card" data-status="9">
                <div class="card text-bg-danger mb-3">
                    <!-- <div class="card-header">รายการลาทั้งหมด</div> -->
                    <div class="card-body">
                        <h5 class="card-title">
                            <?php
                                $sql = "SELECT
    COUNT(li.l_list_id) AS leave_count,
    li.l_name
FROM
leave_list li
WHERE
li.l_department <> 'RD'
AND li.l_leave_id NOT IN (6, 7)
AND li.l_level IN ('user', 'chief', 'leader','admin','assisManager','manager','subLeader')
AND li.l_approve_status3 = 9
 AND (
        YEAR(li.l_create_datetime) = :selectedYear
        OR YEAR(li.l_leave_end_date) = :selectedYear
    )";

                                if ($selectedMonth != "All") {
                                    $sql .= " AND (
        Month(li.l_create_datetime) = :selectedMonth
        OR Month(li.l_leave_end_date) = :selectedMonth
    ) ";
                                }

                                // เตรียมและรัน query
                                $stmt = $conn->prepare($sql);
                                // $stmt->bindParam(':subDepart', $subDepart);
                                if ($selectedMonth != "All") {
                                    $stmt->bindParam(':selectedMonth', $selectedMonth, PDO::PARAM_INT);
                                }

                                $stmt->bindParam(':selectedYear', $selectedYear);
                                $stmt->execute();

                                // ดึงผลลัพธ์
                                $totalLeaveItems = $stmt->fetchColumn();
                            ?>
                            <div class="d-flex justify-content-between">
                                <?php echo $totalLeaveItems; ?>
                                <!-- <i class="mt-4 fa-solid fa-thumbs-down fa-2xl"></i> -->
                                <i class="mt-4 fa-regular fa-face-frown fa-2xl"></i>
                            </div>
                        </h5>
                        <p class="card-text">
                            รายการลาที่ไม่อนุมัติ
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
                        <th rowspan="1"><?php echo $strLeaveType; ?></th>
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
                        <th> <input type="text" class="form-control" id="nameSearch"></th>
                        <th> <input type="text" class="form-control" id="leaveSearch"></th>
                        <th style="width: 8%;">จาก</th>
                        <th style="width: 8%;">ถึง</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    <?php
                        // กำหนดจำนวนรายการต่อหน้า
                        $itemsPerPage = 10;

                        // ตรวจสอบหน้าปัจจุบัน
                        $currentPage = isset($_GET['page']) ? (int) $_GET['page'] : 1;

                        // คำนวณค่า offset สำหรับ pagination
                        $offset = ($currentPage - 1) * $itemsPerPage;

                        // สร้างคำสั่ง SQL
                        $sql = "SELECT
            li.*
        FROM
            leave_list li
        WHERE
            li.l_department <> 'RD'
            AND li.l_leave_id NOT IN (6, 7)
            AND li.l_level IN ('user', 'chief', 'leader','admin','assisManager','manager','subLeader')
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

                        $sql .= " ORDER BY li.l_create_datetime DESC";

                        // ประมวลผลคำสั่ง SQL เพื่อหาจำนวนแถวทั้งหมด
                        $stmt = $conn->prepare($sql);

                        // ผูกค่า (bind parameters)
                        $stmt->bindParam(':selectedYear', $selectedYear, PDO::PARAM_INT);

                        if ($selectedMonth != "All") {
                            $stmt->bindParam(':selectedMonth', $selectedMonth, PDO::PARAM_INT);
                        }

                        $stmt->execute();

                        // นับจำนวนแถวทั้งหมด
                        $totalRows = $stmt->rowCount();

                        // คำนวณจำนวนหน้าทั้งหมด
                        $totalPages = ceil($totalRows / $itemsPerPage);

                        // เพิ่ม LIMIT และ OFFSET ในคำสั่ง SQL
                        $sql .= " LIMIT :itemsPerPage OFFSET :offset";

                        // เตรียมคำสั่ง SQL ใหม่สำหรับดึงข้อมูลรายการในหน้าที่กำหนด
                        $stmt = $conn->prepare($sql);

                        // ผูกค่า (bind parameters) ใหม่
                        $stmt->bindParam(':selectedYear', $selectedYear, PDO::PARAM_INT);

                        if ($selectedMonth != "All") {
                            $stmt->bindParam(':selectedMonth', $selectedMonth, PDO::PARAM_INT);
                        }

                        $stmt->bindParam(':itemsPerPage', $itemsPerPage, PDO::PARAM_INT);
                        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

                        $stmt->execute();

                                                           // แสดงข้อมูลในตาราง
                        $rowNumber = $totalRows - $offset; // กำหนดลำดับของแถวเริ่มต้น
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
                                // 13:45
                                else if ($row['l_leave_start_time'] == '14:00:00' && $row['l_remark'] == '13:45:00') {
                                    echo '<td>' . $row['l_leave_start_date'] . '<br> 13:45:00</td>';
                                }
                                // 14:10
                                else if ($row['l_leave_start_time'] == '14:30:00' && $row['l_remark'] == '14:10:00') {
                                    echo '<td>' . $row['l_leave_start_date'] . '<br> 14:10:00</td>';
                                }
                                // 14:40
                                else if ($row['l_leave_start_time'] == '15:00:00' && $row['l_remark'] == '14:40:00') {
                                    echo '<td>' . $row['l_leave_start_date'] . '<br> 14:40:00</td>';
                                }
                                // 14:45
                                else if ($row['l_leave_start_time'] == '15:00:00' && $row['l_remark'] == '14:45:00') {
                                    echo '<td>' . $row['l_leave_start_date'] . '<br> 14:45:00</td>';
                                }
                                // 15:10
                                else if ($row['l_leave_start_time'] == '15:30:00' && $row['l_remark'] == '15:10:00') {
                                    echo '<td>' . $row['l_leave_start_date'] . '<br> 15:10:00</td>';
                                }
                                // 15:40
                                else if ($row['l_leave_start_time'] == '16:00:00' && $row['l_remark'] == '15:40:00') {
                                    echo '<td>' . $row['l_leave_start_date'] . '<br> 15:40:00</td>';
                                }
                                // 15:45
                                else if ($row['l_leave_start_time'] == '16:00:00' && $row['l_remark'] == '15:45:00') {
                                    echo '<td>' . $row['l_leave_start_date'] . '<br> 15:45:00</td>';
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
                                // 13:45
                                else if ($row['l_leave_end_time'] == '14:00:00' && $row['l_remark'] == '13:45:00') {
                                    echo '<td>' . $row['l_leave_end_date'] . '<br> 13:45:00</td>';
                                }
                                // 14:10
                                else if ($row['l_leave_end_time'] == '14:30:00' && $row['l_remark'] == '14:10:00') {
                                    echo '<td>' . $row['l_leave_end_date'] . '<br> 14:10:00</td>';
                                }
                                // 14:40
                                else if ($row['l_leave_end_time'] == '15:00:00' && $row['l_remark'] == '14:40:00') {
                                    echo '<td>' . $row['l_leave_end_date'] . '<br> 14:40:00</td>';
                                }
                                // 14:45
                                else if ($row['l_leave_end_time'] == '15:00:00' && $row['l_remark'] == '14:45:00') {
                                    echo '<td>' . $row['l_leave_end_date'] . '<br> 14:45:00</td>';
                                }
                                // 15:10
                                else if ($row['l_leave_end_time'] == '15:30:00' && $row['l_remark'] == '15:10:00') {
                                    echo '<td>' . $row['l_leave_end_date'] . '<br> 15:10:00</td>';
                                }
                                // 15:40
                                else if ($row['l_leave_end_time'] == '16:00:00' && $row['l_remark'] == '15:40:00') {
                                    echo '<td>' . $row['l_leave_end_date'] . '<br> 15:40:00</td>';
                                }
                                // 15:45
                                else if ($row['l_leave_end_time'] == '16:00:00' && $row['l_remark'] == '15:45:00') {
                                    echo '<td>' . $row['l_leave_end_date'] . '<br> 15:45:00</td>';
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
                                echo '</td>';
                                if (! empty($row['l_file'])) {
                                    echo '<td><button id="imgBtn" class="btn btn-primary" onclick="window.open(\'../upload/' . $row['l_file'] . '\', \'_blank\')"><i class="fa-solid fa-file"></i></button></td>';
                                } else {
                                    echo '<td><button id="imgNoBtn" class="btn btn-primary" disabled><i class="fa-solid fa-file-excel"></i></button></td>';
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
                                } else {
                                    echo $row['l_hr_status'];
                                }
                                echo '</td>';

                                // 27
                                echo '<td>' . $row['l_remark2'] . '</td>';

                                // 28
                                if ($row['l_approve_status3'] == 8 || $row['l_approve_status3'] == 9) {
                                    echo "<td><button type='button' class='btn btn-primary leaveChk' data-bs-toggle='modal' data-bs-target='#leaveModal' disabled>$btnCheck</button></td>";
                                } else {
                                    echo "<td><button type='button' class='btn btn-primary leaveChk' data-bs-toggle='modal' data-bs-target='#leaveModal'>$btnCheck</button></td>";
                                }

                                // 29
                                echo '<td>
                                <button type="button" class="btn btn-primary btn-sm view-history" data-usercode="' . $row['l_usercode'] . '"><i class="fa-solid fa-clock-rotate-left"></i></button></td>';

                                echo '</tr>';

                                $rowNumber--;
                            }

                        } else {
                            echo '<tr><td colspan="19" style="text-align: left; color:red;">ไม่พบข้อมูล</td></tr>';
                        }
                    ?>
                </tbody>
            </table>
        </div>
        <?php
            echo '<div class="mt-3">';
            echo '<nav>';
            echo '<ul class="pagination justify-content-start">';

            // ปุ่มย้อนกลับ
            if ($currentPage > 1) {
                echo '<li class="page-item"><a class="page-link" href="?page=' . ($currentPage - 1) . '&year=' . $selectedYear . '&month=' . $selectedMonth . '">«</a></li>';
            }

            // แสดงหมายเลขหน้า
            for ($i = 1; $i <= $totalPages; $i++) {
                if ($i == $currentPage) {
                    echo '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
                } else {
                    echo '<li class="page-item"><a class="page-link" href="?page=' . $i . '&year=' . $selectedYear . '&month=' . $selectedMonth . '">' . $i . '</a></li>';
                }
            }

            // ปุ่มถัดไป
            if ($currentPage < $totalPages) {
                echo '<li class="page-item"><a class="page-link" href="?page=' . ($currentPage + 1) . '&year=' . $selectedYear . '&month=' . $selectedMonth . '">»</a></li>';
            }

            echo '</ul>';
            echo '</nav>';
            echo '</div>';

        ?>
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
                        <button type="button" class="btn btn-danger button-shadow">ไม่อนุมัติ</button>
                        <button type="button" class="btn btn-success button-shadow">อนุมัติ</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- ประวัติลาของพนักงาน -->
        <div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
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

    <script>
    $(".leaveChk").click(function() {
        var rowData = $(this).closest("tr").find("td");

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
            '<td>' + $(rowData[9]).text() + ' ถึง ' + $(rowData[10]).text() + '</td>' +
            '</tr>' +
            '<tr>' +
            '<th>สถานะใบลา</th>' +
            '<td>' + $(rowData[13]).html() + '</td>' +
            '</tr>' +
            '</table>'
        );

        $('.modal-footer .btn-success').off('click').on('click', function() {
            var userCode = $(rowData[5]).text(); // รหัสพนักงาน
            var createDate = $(rowData[7]).text(); // วันที่ยื่นใบลา
            var leaveType = $(rowData[0]).text(); // ประเภทการลา
            var empName = $(rowData[1]).text(); // ชื่อพนักงาน
            var depart = $(rowData[2]).text(); // แผนก
            var leaveReason = $(rowData[3]).text(); // เหตุผลการลา
            var leaveStartDate = $(rowData[9]).text(); // วันเวลาที่ลาเริ่มต้น
            var leaveEndDate = $(rowData[10]).text(); // วันเวลาที่ลาสิ้นสุด
            var leaveStatus = $(rowData[12]).text(); // สถานะใบลา

            var status = 8; // อนุมัติ
            var userName = '<?php echo $userName; ?>';
            var proveName = '<?php echo $name; ?>';

            // alert(leaveStatus)
            $.ajax({
                url: 'g_ajax_upd_status.php',
                method: 'POST',
                data: {
                    createDate: createDate,
                    userCode: userCode,
                    status: status,
                    userName: userName,
                    proveName: proveName,
                    leaveType: leaveType,
                    leaveReason: leaveReason,
                    leaveStartDate: leaveStartDate,
                    leaveEndDate: leaveEndDate,
                    depart: depart,
                    empName: empName,
                    leaveStatus: leaveStatus
                },
                success: function(response) {
                    $('#leaveModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'อนุมัติใบลาสำเร็จ !',
                        confirmButtonText: 'ตกลง'
                    }).then((result) => {
                        if (result
                            .isConfirmed) {
                            location
                                .reload();
                        }
                    });
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        });

        $('.modal-footer .btn-danger').off('click').on('click', function() {
            // ซ่อน modal หลัก
            $('#leaveModal').modal('hide');

            // เรียกใช้ SweetAlert2 หลังจากที่ modal หลักถูกซ่อนไปแล้ว
            setTimeout(function() {
                showInputDialog(); // เรียกใช้ฟังก์ชันเพื่อแสดงกล่องโต้ตอบ
            }, 300); // เพิ่ม delay เล็กน้อยเพื่อให้ modal หลักปิดสนิท
        });

        function showInputDialog() {
            Swal.fire({
                title: 'กรุณากรอกข้อมูล',
                input: 'text',
                inputLabel: 'เหตุผล',
                inputPlaceholder: 'กรอกเหตุผลการไม่อนุมัติ',
                showCancelButton: true,
                confirmButtonText: 'ตกลง',
                cancelButtonText: 'ยกเลิก',
                preConfirm: (inputValue) => {
                    if (!inputValue) {
                        Swal.showValidationMessage('กรุณากรอกเหตุผลการไม่อนุมัติ');
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const reasonNoProve = result.value; // รับค่าจาก input
                    console.log(reasonNoProve); // ตรวจสอบค่าที่กรอก
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

            var userCode = $(rowData[5]).text(); // รหัสพนักงาน
            var createDate = $(rowData[7]).text(); // วันที่ยื่นใบลา
            var leaveType = $(rowData[0]).text(); // ประเภทการลา
            var empName = $(rowData[1]).text(); // ชื่อพนักงาน
            var depart = $(rowData[2]).text(); // แผนก
            var leaveReason = $(rowData[3]).text(); // เหตุผลการลา
            var leaveStartDate = $(rowData[9]).text(); // วันเวลาที่ลาเริ่มต้น
            var leaveEndDate = $(rowData[10]).text(); // วันเวลาที่ลาสิ้นสุด
            var leaveStatus = $(rowData[12]).text(); // สถานะใบลา

            var status = 9; // ไม่อนุมัติ
            var userName = '<?php echo $userName; ?>';
            var proveName = '<?php echo $name; ?>';

            var reason = reasonNoProve;

            $.ajax({
                url: 'g_ajax_upd_status.php',
                method: 'POST',
                data: {
                    createDate: createDate,
                    userCode: userCode,
                    status: status,
                    userName: userName,
                    proveName: proveName,
                    leaveType: leaveType,
                    leaveReason: leaveReason,
                    leaveStartDate: leaveStartDate,
                    leaveEndDate: leaveEndDate,
                    depart: depart,
                    leaveStatus: leaveStatus,
                    empName: empName,
                    reasonNoProve: reasonNoProve
                },
                success: function(response) {
                    $('#leaveModal').modal('hide'); // ปิด modal
                    Swal.fire({
                        title: 'สำเร็จ!',
                        text: 'ทำรายการเสร็จสิ้น',
                        icon: 'success',
                        confirmButtonText: 'ตกลง'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            location.reload(); // โหลดหน้าใหม่เมื่อกดตกลง
                        }
                    });
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        }

    });

    $(".filter-card").click(function() {
        // ลบ active จากการ์ดทั้งหมด
        $(".filter-card .card").removeClass("active");

        // เพิ่ม active ให้การ์ดภายใน filter-card ที่คลิก
        $(this).find(".card").addClass("active");

        var status = $(this).data("status");
        var selectedMonth = $("#selectedMonth").val();
        var selectedYear = $("#selectedYear").val();

        // alert(selectedYear)
        $.ajax({
            url: 'g_ajax_get_leave_data.php',
            method: 'GET',
            data: {
                status: status,
                month: selectedMonth,
                year: selectedYear
            },
            dataType: 'json',
            success: function(data) {
                // Clear existing table rows
                $("tbody").empty();

                if (data.length === 0) {
                    $("tbody").append(
                        '<tr><td colspan="26" class="text-danger" style="text-align: left;">ไม่พบข้อมูล</td></tr>'
                    );
                } else {
                    var totalItems = data.length; // Store total count

                    $.each(data, function(index, row) {
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
                            '<td>' + (totalItems - index) + '</td>' +

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
                            '<td>' + (row['l_create_datetime'] ? row[
                                    'l_create_datetime'] :
                                '') + '</td>' + // Creation Date Time
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
                        } else {
                            newRow +=
                                '<span class="text-danger">ไม่พบประเภทการลาและเหตุผลการลา</span>';
                        }
                        newRow += '</td>' +
                            // 9
                            '<td>' + (row['l_leave_start_date'] ? row[
                                'l_leave_start_date'] : '') + '<br>' +
                            (row['l_leave_start_time'] ? (
                                // Check if the l_leave_start_time and l_remark match certain values
                                (row['l_leave_start_time'] == '09:00:00' && row[
                                    'l_remark'] == '08:45:00') ? '08:45:00' :
                                (row['l_leave_start_time'] == '10:00:00' && row[
                                    'l_remark'] == '09:45:00') ? '09:45:00' :
                                (row['l_leave_start_time'] == '11:00:00' && row[
                                    'l_remark'] == '10:45:00') ? '10:45:00' :
                                (row['l_leave_start_time'] == '12:00:00') ? '11:45:00' :
                                (row['l_leave_start_time'] == '13:00:00') ? '12:45:00' :
                                (row['l_leave_start_time'] == '13:30:00' && row[
                                    'l_remark'] == '13:10:00') ? '13:10:00' :
                                (row['l_leave_start_time'] == '14:00:00' && row[
                                    'l_remark'] == '13:40:00') ? '13:40:00' :
                                (row['l_leave_start_time'] == '14:00:00' && row[
                                    'l_remark'] == '13:45:00') ? '13:45:00' :
                                (row['l_leave_start_time'] == '14:30:00' && row[
                                    'l_remark'] == '14:10:00') ? '14:10:00' :
                                (row['l_leave_start_time'] == '15:00:00' && row[
                                    'l_remark'] == '14:40:00') ? '14:40:00' :
                                (row['l_leave_start_time'] == '15:00:00' && row[
                                    'l_remark'] == '14:45:00') ? '14:45:00' :
                                (row['l_leave_start_time'] == '15:30:00' && row[
                                    'l_remark'] == '15:10:00') ? '15:10:00' :
                                (row['l_leave_start_time'] == '16:00:00' && row[
                                    'l_remark'] == '15:40:00') ? '15:40:00' :
                                (row['l_leave_start_time'] == '16:00:00' && row[
                                    'l_remark'] == '15:45:00') ? '15:45:00' :
                                (row['l_leave_start_time'] == '16:30:00' && row[
                                    'l_remark'] == '16:10:00') ? '16:10:00' :
                                (row['l_leave_start_time'] == '17:00:00') ? '16:40:00' :
                                row['l_leave_start_time']
                            ) : '') +
                            '</td>' +

                            // 10
                            '<td>' + (row['l_leave_end_date'] ? row[
                                'l_leave_end_date'] : '') + '<br>' +
                            (row['l_leave_end_time'] ? (
                                // Check if the l_leave_start_time and l_remark match certain values
                                (row['l_leave_end_time'] == '09:00:00' && row[
                                    'l_remark'] == '08:45:00') ? '08:45:00' :
                                (row['l_leave_end_time'] == '10:00:00' && row[
                                    'l_remark'] == '09:45:00') ? '09:45:00' :
                                (row['l_leave_end_time'] == '11:00:00' && row[
                                    'l_remark'] == '10:45:00') ? '10:45:00' :
                                (row['l_leave_end_time'] == '12:00:00') ? '11:45:00' :
                                (row['l_leave_end_time'] == '13:00:00') ? '12:45:00' :
                                (row['l_leave_end_time'] == '13:30:00' && row[
                                    'l_remark'] == '13:10:00') ? '13:10:00' :
                                (row['l_leave_end_time'] == '14:00:00' && row[
                                    'l_remark'] == '13:40:00') ? '13:40:00' :
                                (row['l_leave_end_time'] == '14:00:00' && row[
                                    'l_remark'] == '13:45:00') ? '13:45:00' :
                                (row['l_leave_end_time'] == '14:30:00' && row[
                                    'l_remark'] == '14:10:00') ? '14:10:00' :
                                (row['l_leave_end_time'] ==
                                    '15:00:00' && row[
                                        'l_remark'] == '14:40:00') ? '14:40:00' :
                                (row['l_leave_end_time'] == '15:00:00' && row[
                                    'l_remark'] == '14:45:00') ? '14:45:00' :
                                (row['l_leave_end_time'] == '15:30:00' && row[
                                    'l_remark'] == '15:10:00') ? '15:10:00' :
                                (row['l_leave_end_time'] == '16:00:00' && row[
                                    'l_remark'] == '15:40:00') ? '15:40:00' :
                                (row['l_leave_end_time'] == '16:00:00' && row[
                                    'l_remark'] == '15:45:00') ? '15:45:00' :
                                (row['l_leave_end_time'] == '16:30:00' && row[
                                    'l_remark'] == '16:10:00') ? '16:10:00' :
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
                        if (row['l_file']) {
                            newRow +=
                                '<td><button id="imgBtn" class="btn btn-primary" onclick="window.open(\'../upload/' +
                                row['l_file'] +
                                '\', \'_blank\')"><i class="fa-solid fa-file"></i></button></td>';
                        } else {
                            newRow +=
                                '<td><button id="imgNoBtn" class="btn btn-primary" disabled><i class="fa-solid fa-file-excel"></i></button></td>';
                        }
                        newRow +=
                            // 13
                            '<td>' + leaveStatus + '</td>' +

                            // 14
                            '<td>' + (row['l_approve_name'] ? row['l_approve_name'] : '') +
                            '</td>' +

                            // 15
                            '<td>' + approveStatus + '</td>' +

                            // 16
                            '<td>' + (row['l_approve_datetime'] !== null ? row[
                                'l_approve_datetime'] : '') + '</td>' +

                            // 17
                            '<td>' + (row['l_reason'] ? row['l_reason'] : '') + '</td>' +

                            // 18
                            '<td>' + (row['l_approve_name2'] ? row['l_approve_name2'] :
                                '') + '</td>' +

                            // 19
                            '<td>' + approveStatus2 + '</td>' +

                            // 20
                            '<td>' + (row['l_approve_datetime2'] !== null ? row[
                                'l_approve_datetime2'] : '') + '</td>' +

                            // 21
                            '<td>' + (row['l_reason2'] ? row['l_reason2'] : '') + '</td>' +

                            // 22
                            '<td>' + (row['l_approve_name3'] ? row['l_approve_name3'] :
                                '') + '</td>' +

                            // 23
                            '<td>' + approveStatus3 + '</td>' +

                            // 24
                            '<td>' + (row['l_approve_datetime3'] !== null ? row[
                                'l_approve_datetime3'] : '') + '</td>' +

                            // 25
                            '<td>' + (row['l_reason3'] ? row['l_reason3'] : '') + '</td>' +

                            // 26
                            '<td>' + confirmStatus + '</td>' +

                            // 27
                            '<td>' + (row['l_remark2'] ? row['l_remark2'] : '') + '</td>' +

                            //28
                            '<td>';
                        if (row['l_approve_status3'] == 8 || row['l_approve_status3'] ==
                            9) {
                            newRow +=
                                '<button type="button" class="btn btn-primary leaveChk" data-bs-toggle="modal" data-bs-target="#leaveModal" disabled><?php echo $btnCheck ?></button>';
                        } else {
                            newRow +=
                                '<button type="button" class="btn btn-primary leaveChk" data-bs-toggle="modal" data-bs-target="#leaveModal"><?php echo $btnCheck ?></button>';
                        }
                        newRow += '</td>' +
                            // 29
                            '<td>' +

                            '<button type="button" class="btn btn-primary btn-sm view-history" data-usercode="' +
                            row['l_usercode'] + '">' +
                            '<i class="fa-solid fa-clock-rotate-left"></i></button>' +
                            '</td>' +


                            '</tr>';

                        $("tbody").append(newRow);
                    });
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
                            '<th>สถานะใบลา</th>' +
                            '<td>' + $(rowData[13]).html() + '</td>' +
                            '</tr>' +
                            '</table>'
                        );
                        $('#leaveModal').modal('show');
                        $('.modal-footer .btn-success').off('click').on('click',
                            function() {
                                var userCode = $(rowData[5]).text(); // รหัสพนักงาน
                                var createDate = $(rowData[7])
                                    .text(); // วันที่ยื่นใบลา
                                var leaveType = $(rowData[0]).text(); // ประเภทการลา
                                var empName = $(rowData[1]).text(); // ชื่อพนักงาน
                                var depart = $(rowData[2]).text(); // แผนก
                                var leaveReason = $(rowData[3])
                                    .text(); // เหตุผลการลา
                                var leaveStartDate = $(rowData[9])
                                    .text(); // วันเวลาที่ลาเริ่มต้น
                                var leaveEndDate = $(rowData[10])
                                    .text(); // วันเวลาที่ลาสิ้นสุด
                                var leaveStatus = $(rowData[12])
                                    .text(); // สถานะใบลา


                                var status = 8; // อนุมัติ
                                var userName = '<?php echo $userName; ?>';
                                var proveName = '<?php echo $name; ?>';

                                $.ajax({
                                    url: 'g_ajax_upd_status.php',
                                    method: 'POST',
                                    data: {
                                        createDate: createDate,
                                        userCode: userCode,
                                        status: status,
                                        userName: userName,
                                        proveName: proveName,
                                        leaveType: leaveType,
                                        leaveReason: leaveReason,
                                        leaveStartDate: leaveStartDate,
                                        leaveEndDate: leaveEndDate,
                                        depart: depart,
                                        leaveStatus: leaveStatus,
                                        empName: empName
                                    },
                                    success: function(response) {
                                        $('#leaveModal').modal('hide');
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'อนุมัติใบลาสำเร็จ !',
                                            confirmButtonText: 'ตกลง'
                                        }).then((result) => {
                                            if (result
                                                .isConfirmed) {
                                                location
                                                    .reload();
                                            }
                                        });
                                    },
                                    error: function(xhr, status, error) {
                                        console.error(error);
                                    }
                                });
                            });
                        $('.modal-footer .btn-danger').off('click').on('click',
                            function() {
                                // ซ่อน modal หลัก
                                $('#leaveModal').modal('hide');

                                // เรียกใช้ SweetAlert2 หลังจากที่ modal หลักถูกซ่อนไปแล้ว
                                setTimeout(function() {
                                        showInputDialog
                                            (); // เรียกใช้ฟังก์ชันเพื่อแสดงกล่องโต้ตอบ
                                    },
                                    300
                                ); // เพิ่ม delay เล็กน้อยเพื่อให้ modal หลักปิดสนิท
                            });

                        function showInputDialog() {
                            Swal.fire({
                                title: 'กรุณากรอกข้อมูล',
                                input: 'text',
                                inputLabel: 'เหตุผล',
                                inputPlaceholder: 'กรอกเหตุผลการไม่อนุมัติ',
                                showCancelButton: true,
                                confirmButtonText: 'ตกลง',
                                cancelButtonText: 'ยกเลิก',
                                preConfirm: (inputValue) => {
                                    if (!inputValue) {
                                        Swal.showValidationMessage(
                                            'กรุณากรอกเหตุผลการไม่อนุมัติ'
                                        );
                                    }
                                }
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    const reasonNoProve = result
                                        .value; // รับค่าจาก input
                                    console.log(
                                        reasonNoProve); // ตรวจสอบค่าที่กรอก
                                    noApprove(
                                        reasonNoProve
                                    ); // เรียกใช้ฟังก์ชัน noApprove
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


                            var userCode = $(rowData[5]).text(); // รหัสพนักงาน
                            var createDate = $(rowData[7]).text(); // วันที่ยื่นใบลา
                            var leaveType = $(rowData[0]).text(); // ประเภทการลา
                            var empName = $(rowData[1]).text(); // ชื่อพนักงาน
                            var depart = $(rowData[2]).text(); // แผนก
                            var leaveReason = $(rowData[3]).text(); // เหตุผลการลา
                            var leaveStartDate = $(rowData[9])
                                .text(); // วันเวลาที่ลาเริ่มต้น
                            var leaveEndDate = $(rowData[10])
                                .text(); // วันเวลาที่ลาสิ้นสุด
                            var leaveStatus = $(rowData[12]).text(); // สถานะใบลา

                            var status = 9; // ไม่อนุมัติ
                            var userName = '<?php echo $userName; ?>';
                            var proveName = '<?php echo $name; ?>';

                            var reason = reasonNoProve;

                            $.ajax({
                                url: 'g_ajax_upd_status.php',
                                method: 'POST',
                                data: {
                                    createDate: createDate,
                                    userCode: userCode,
                                    status: status,
                                    userName: userName,
                                    proveName: proveName,
                                    leaveType: leaveType,
                                    leaveReason: leaveReason,
                                    leaveStartDate: leaveStartDate,
                                    leaveEndDate: leaveEndDate,
                                    depart: depart,
                                    leaveStatus: leaveStatus,
                                    empName: empName,
                                    reasonNoProve: reasonNoProve
                                },
                                success: function(response) {
                                    $('#leaveModal').modal(
                                        'hide'); // ปิด modal
                                    Swal.fire({
                                        title: 'สำเร็จ!',
                                        text: 'ทำรายการเสร็จสิ้น',
                                        icon: 'success',
                                        confirmButtonText: 'ตกลง'
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            location
                                                .reload(); // โหลดหน้าใหม่เมื่อกดตกลง
                                        }
                                    });
                                },
                                error: function(xhr, status, error) {
                                    console.error(error);
                                }
                            });
                        }
                    });
                    $('.view-history').click(function() {
                        var userCode = $(this).data(
                            'usercode'); // ดึงรหัสพนักงานจาก data attribute

                        $.ajax({
                            url: 'g_ajax_get_leave_history.php', // URL ของไฟล์ PHP ที่จะจัดการข้อมูล
                            type: 'POST',
                            data: {
                                userCode: userCode
                            },
                            success: function(response) {
                                // แสดงข้อมูลประวัติการลาหรือทำสิ่งที่ต้องการหลังจากได้รับข้อมูล
                                // เช่น แสดงใน modal หรือ alert
                                $('#historyModal .modal-body').html(
                                    response);
                                $('#historyModal').modal('show');
                            },
                            error: function() {
                                alert('เกิดข้อผิดพลาดในการดึงข้อมูล');
                            }
                        });
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching data:', error);
            }
        });
    });

    $('.view-history').click(function() {
        var userCode = $(this).data('usercode'); // ดึงรหัสพนักงานจาก data attribute

        $.ajax({
            url: 'g_ajax_get_leave_history.php', // URL ของไฟล์ PHP ที่จะจัดการข้อมูล
            type: 'POST',
            data: {
                userCode: userCode
            },
            success: function(response) {
                // แสดงข้อมูลประวัติการลาหรือทำสิ่งที่ต้องการหลังจากได้รับข้อมูล
                // เช่น แสดงใน modal หรือ alert
                $('#historyModal .modal-body').html(response);
                $('#historyModal').modal('show');
            },
            error: function() {
                alert('เกิดข้อผิดพลาดในการดึงข้อมูล');
            }
        });
    });

    $("#nameSearch").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("tbody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    $("#leaveSearch").on("keyup", function() {
        var value2 = $(this).val().toLowerCase();
        $("tbody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value2) > -1);
        });
    });
    </script>
    <script src="../js/popper.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/bootstrap.bundle.js"></script>
    <script src="../js/bootstrap.bundle.min.js"></script>
</body>

</html>