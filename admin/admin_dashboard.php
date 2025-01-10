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

    <!-- <script src="https://kit.fontawesome.com/84c1327080.js" crossorigin="anonymous"></script> -->

    <script src="../js/fontawesome.js"></script>
</head>

<body>
    <?php require 'admin_navbar.php'?>

    <div class="container-fluid">
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
    </div>

    <div class="container-fluid">
        <div class="d-flex justify-content-center align-items-center flex-wrap mt-3">
            <div class="col-2 filter-card mx-2" data-status="all">
                <div class="card text-bg-primary mb-3">
                    <!-- <div class="card-header">รายการลาทั้งหมด</div> -->
                    <div class="card-body">
                        <h5 class="card-title">
                            <?php
$sql = "SELECT COUNT(l_list_id) AS totalLeaveItems FROM leave_list WHERE l_leave_id NOT IN (6,7)";

if ($selectedMonth != "All") {
    $sql .= " AND (
        MONTH(l_create_datetime) = :selectedMonth
        OR MONTH(l_leave_end_date) = :selectedMonth
    )";
}

$sql .= " AND (
    YEAR(l_create_datetime) = :selectedYear
    OR YEAR(l_leave_end_date) = :selectedYear
)";

$stmt = $conn->prepare($sql);

// Bind parameters to prevent SQL injection
if ($selectedMonth != "All") {
    $stmt->bindParam(':selectedMonth', $selectedMonth, PDO::PARAM_INT);
}
$stmt->bindParam(':selectedYear', $selectedYear, PDO::PARAM_INT);

$stmt->execute();
$totalLeaveItems = $stmt->fetchColumn();

?>
                            <div class="d-flex justify-content-between">
                                <?php echo $totalLeaveItems; ?>
                                <i class="mt-4 fas fa-file-alt ml-2 fa-2xl"></i>
                            </div>
                        </h5>
                        <p class="card-text">
                            รายการลาทั้งหมด
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-2 filter-card mx-2" data-status="0">
                <div class="card text-bg-warning mb-3">
                    <!-- <div class="card-header">รายการลาทั้งหมด</div> -->
                    <div class="card-body">
                        <h5 class="card-title">
                            <?php
// $sql = "SELECT COUNT(l_list_id) AS totalLeaveItems FROM leave_list WHERE l_hr_status = 0 AND Month(l_leave_end_date) = '$selectedMonth'
// AND Year(l_leave_end_date) = '$selectedYear'
// AND l_leave_id <> 6 AND l_leave_id <> 7";
$sql = "SELECT COUNT(l_list_id) AS totalLeaveItems FROM leave_list WHERE l_leave_id NOT IN (6,7)
AND l_hr_status = 0";

if ($selectedMonth != "All") {
    $sql .= " AND (
        MONTH(l_create_datetime) = :selectedMonth
        OR MONTH(l_leave_end_date) = :selectedMonth
    )";
}

$sql .= " AND (
    YEAR(l_create_datetime) = :selectedYear
    OR YEAR(l_leave_end_date) = :selectedYear
)";

$stmt = $conn->prepare($sql);

// Bind parameters to prevent SQL injection
if ($selectedMonth != "All") {
    $stmt->bindParam(':selectedMonth', $selectedMonth, PDO::PARAM_INT);
}
$stmt->bindParam(':selectedYear', $selectedYear, PDO::PARAM_INT);

$stmt->execute();
$totalLeaveItems = $stmt->fetchColumn();

?>
                            <div class="d-flex justify-content-between">
                                <?php echo $totalLeaveItems; ?>
                                <i class="mt-4 fa-solid fa-clock-rotate-left fa-2xl" style="color: #ffffff;"></i>
                            </div>
                        </h5>
                        <p class="card-text">
                            รายการลาที่รอตรวจสอบ
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-2 filter-card mx-2" data-status="1">
                <div class="card text-bg-success mb-3">
                    <!-- <div class="card-header">รายการลาทั้งหมด</div> -->
                    <div class="card-body">
                        <h5 class="card-title">
                            <?php
// $sql = "SELECT COUNT(l_list_id) AS totalLeaveItems FROM leave_list WHERE l_hr_status = 1 AND Month(l_leave_end_date) = '$selectedMonth'
// AND Year(l_leave_end_date) = '$selectedYear'
// AND l_leave_id <> 6 AND l_leave_id <> 7";
$sql = "SELECT COUNT(l_list_id) AS totalLeaveItems FROM leave_list WHERE l_leave_id NOT IN (6,7)
AND l_hr_status = 1";

if ($selectedMonth != "All") {
    $sql .= " AND (
        MONTH(l_create_datetime) = :selectedMonth
        OR MONTH(l_leave_end_date) = :selectedMonth
    )";
}

$sql .= " AND (
    YEAR(l_create_datetime) = :selectedYear
    OR YEAR(l_leave_end_date) = :selectedYear
)";

$stmt = $conn->prepare($sql);

// Bind parameters to prevent SQL injection
if ($selectedMonth != "All") {
    $stmt->bindParam(':selectedMonth', $selectedMonth, PDO::PARAM_INT);
}
$stmt->bindParam(':selectedYear', $selectedYear, PDO::PARAM_INT);

$stmt->execute();
$totalLeaveItems = $stmt->fetchColumn();
?>
                            <div class="d-flex justify-content-between">
                                <?php echo $totalLeaveItems; ?>
                                <i class="mt-4 fa-solid fa-thumbs-up fa-2xl"></i>
                            </div>
                        </h5>
                        <p class="card-text">
                            รายการลาที่ตรวจสอบผ่าน
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-2 filter-card mx-2" data-status="2">
                <div class="card text-bg-danger mb-3">
                    <!-- <div class="card-header">รายการลาทั้งหมด</div> -->
                    <div class="card-body">
                        <h5 class="card-title">
                            <?php
$sql = "SELECT COUNT(l_list_id) AS totalLeaveItems FROM leave_list WHERE l_leave_id NOT IN (6,7)
AND l_hr_status = 2";

if ($selectedMonth != "All") {
    $sql .= " AND (
        MONTH(l_create_datetime) = :selectedMonth
        OR MONTH(l_leave_end_date) = :selectedMonth
    )";
}

$sql .= " AND (
    YEAR(l_create_datetime) = :selectedYear
    OR YEAR(l_leave_end_date) = :selectedYear
)";

$stmt = $conn->prepare($sql);

// Bind parameters to prevent SQL injection
if ($selectedMonth != "All") {
    $stmt->bindParam(':selectedMonth', $selectedMonth, PDO::PARAM_INT);
}
$stmt->bindParam(':selectedYear', $selectedYear, PDO::PARAM_INT);

$stmt->execute();
$totalLeaveItems = $stmt->fetchColumn();
?>
                            <div class="d-flex justify-content-between">
                                <?php echo $totalLeaveItems; ?>
                                <i class="mt-4 fa-solid fa-thumbs-down fa-2xl"></i>
                            </div>
                        </h5>
                        <p class="card-text">
                            รายการลาที่ตรวจสอบไม่ผ่าน
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-2 filter-card mx-2" data-status="3">
                <div class="card text-bg-info mb-3">
                    <!-- <div class="card-header">รายการลาทั้งหมด</div> -->
                    <div class="card-body">
                        <h5 class="card-title" style="color: #ffffff;">
                            <?php
$sql = "SELECT COUNT(l_list_id) AS totalLeaveItems FROM leave_list WHERE l_leave_id NOT IN (6,7)
AND l_leave_status = 1";

if ($selectedMonth != "All") {
    $sql .= " AND (
        MONTH(l_create_datetime) = :selectedMonth
        OR MONTH(l_leave_end_date) = :selectedMonth
    )";
}

$sql .= " AND (
    YEAR(l_create_datetime) = :selectedYear
    OR YEAR(l_leave_end_date) = :selectedYear
)";

$stmt = $conn->prepare($sql);

// Bind parameters to prevent SQL injection
if ($selectedMonth != "All") {
    $stmt->bindParam(':selectedMonth', $selectedMonth, PDO::PARAM_INT);
}
$stmt->bindParam(':selectedYear', $selectedYear, PDO::PARAM_INT);

$stmt->execute();
$totalLeaveItems = $stmt->fetchColumn();
?>
                            <div class="d-flex justify-content-between">
                                <?php echo $totalLeaveItems; ?>
                                <i class="mt-4 fa-solid fa-ban fa-2xl" style="color: #ffffff;"></i>
                            </div>
                        </h5>
                        <p class="card-text" style="color: #ffffff;">
                            รายการลาที่ยกเลิก
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- ตารางข้อมูลการลา -->
    <div class="container-fluid">
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
                    <!-- <th><input type="text" class="form-control" id="codeSearch"></th>
                      -->
                    <?php $searchCode = isset($_GET['codeSearch']) ? $_GET['codeSearch'] : '';
?>
                    <th><input type="text" class="form-control" id="codeSearch"
                            value="<?php echo htmlspecialchars($searchCode); ?>"></th>
                    <th><input type="text" class="form-control" id="nameSearch"></th>
                    <th><input type="text" class="form-control" id="leaveSearch"></th>
                    <!-- <th><input type="text" class="form-control" id="leaveSearch"></th> -->
                    <th>จาก</th>
                    <th>ถึง</th>
                </tr>
            </thead>
            <tbody class="text-center">
                <?php
$itemsPerPage = 15;

// คำนวณหน้าปัจจุบัน
$currentPage = isset($_GET['page']) ? (int) $_GET['page'] : 1;

// คำนวณ offset สำหรับ pagination
$offset = ($currentPage - 1) * $itemsPerPage;

// สร้างคำสั่ง SQL หลัก
$sql = "SELECT *
        FROM leave_list
        WHERE l_leave_id NOT IN (6,7)
        AND l_usercode LIKE :searchCode
        AND (
            YEAR(l_create_datetime) = :selectedYear
            OR YEAR(l_leave_end_date) = :selectedYear
        )";

if ($selectedMonth != "All") {
    $sql .= " AND (
                Month(l_create_datetime) = :selectedMonth
                OR Month(l_leave_end_date) = :selectedMonth
             )";
}

// นับจำนวนแถวทั้งหมด
$countSql = $sql; // ใช้ SQL เดิมสำหรับการนับจำนวนแถวทั้งหมด
$stmt = $conn->prepare($countSql);
$stmt->bindValue(':searchCode', '%' . $searchCode . '%', PDO::PARAM_STR);
$stmt->bindValue(':selectedYear', $selectedYear, PDO::PARAM_INT);

if ($selectedMonth != "All") {
    $stmt->bindValue(':selectedMonth', $selectedMonth, PDO::PARAM_INT);
}

$stmt->execute();
$totalRows = $stmt->rowCount(); // นับจำนวนแถวทั้งหมด

// คำนวณจำนวนหน้าทั้งหมด
$totalPages = ceil($totalRows / $itemsPerPage);

// เพิ่ม ORDER BY, LIMIT และ OFFSET สำหรับดึงข้อมูลรายการ
$sql .= " ORDER BY l_create_datetime DESC
           LIMIT :itemsPerPage OFFSET :offset";

// เตรียมและประมวลผลคำสั่ง SQL
$stmt = $conn->prepare($sql);
$stmt->bindValue(':searchCode', '%' . $searchCode . '%', PDO::PARAM_STR);
$stmt->bindValue(':selectedYear', $selectedYear, PDO::PARAM_INT);

if ($selectedMonth != "All") {
    $stmt->bindValue(':selectedMonth', $selectedMonth, PDO::PARAM_INT);
}

$stmt->bindValue(':itemsPerPage', $itemsPerPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

$stmt->execute();

// แสดงผลลำดับของแถว
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
        // 08:10
        if ($row['l_leave_start_time'] == '08:30:00' && $row['l_remark'] == '08:10:00') {
            echo '<td>' . $row['l_leave_start_date'] . '<br> 08:10:00</td>';
        }
        // 08:15
        else if ($row['l_leave_start_time'] == '08:30:00' && $row['l_remark'] == '08:15:00') {
            echo '<td>' . $row['l_leave_start_date'] . '<br> 08:15:00</td>';
        }
        // 08:45
        else if ($row['l_leave_start_time'] == '09:00:00' && $row['l_remark'] == '08:45:00') {
            echo '<td>' . $row['l_leave_start_date'] . '<br> 08:45:00</td>';
        }
        // 09:10
        else if ($row['l_leave_start_time'] == '09:30:00' && $row['l_remark'] == '09:10:00') {
            echo '<td>' . $row['l_leave_start_date'] . '<br> 09:10:00</td>';
        }
        // 09:15
        else if ($row['l_leave_start_time'] == '09:30:00' && $row['l_remark'] == '09:15:00') {
            echo '<td>' . $row['l_leave_start_date'] . '<br> 09:15:00</td>';
        }
        // 09:45
        else if ($row['l_leave_start_time'] == '10:00:00' && $row['l_remark'] == '09:45:00') {
            echo '<td>' . $row['l_leave_start_date'] . '<br> 09:45:00</td>';
        }
        // 10:10
        else if ($row['l_leave_start_time'] == '10:30:00' && $row['l_remark'] == '10:10:00') {
            echo '<td>' . $row['l_leave_start_date'] . '<br> 10:10:00</td>';
        }
        // 10:15
        else if ($row['l_leave_start_time'] == '10:30:00' && $row['l_remark'] == '10:15:00') {
            echo '<td>' . $row['l_leave_start_date'] . '<br> 10:15:00</td>';
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
        // 13:15
        else if ($row['l_leave_start_time'] == '13:30:00' && $row['l_remark'] == '13:15:00') {
            echo '<td>' . $row['l_leave_start_date'] . '<br> 13:15:00</td>';
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
        // 14:15
        else if ($row['l_leave_start_time'] == '14:30:00' && $row['l_remark'] == '14:15:00') {
            echo '<td>' . $row['l_leave_start_date'] . '<br> 14:15:00</td>';
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
        // 15:15
        else if ($row['l_leave_start_time'] == '15:30:00' && $row['l_remark'] == '15:15:00') {
            echo '<td>' . $row['l_leave_start_date'] . '<br> 15:15:00</td>';
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
        // 08:10
        if ($row['l_leave_end_time'] == '08:30:00' && $row['l_remark'] == '08:10:00') {
            echo '<td>' . $row['l_leave_end_date'] . '<br> 08:10:00</td>';
        }
        // 08:15
        else if ($row['l_leave_end_time'] == '08:30:00' && $row['l_remark'] == '08:15:00') {
            echo '<td>' . $row['l_leave_end_date'] . '<br> 08:15:00</td>';
        }
        // 08:45
        else if ($row['l_leave_end_time'] == '09:00:00' && $row['l_remark'] == '08:45:00') {
            echo '<td>' . $row['l_leave_end_date'] . '<br> 08:45:00</td>';
        }
        // 09:10
        else if ($row['l_leave_end_time'] == '09:30:00' && $row['l_remark'] == '09:10:00') {
            echo '<td>' . $row['l_leave_end_date'] . '<br> 09:10:00</td>';
        }
        // 09:15
        else if ($row['l_leave_end_time'] == '09:30:00' && $row['l_remark'] == '09:15:00') {
            echo '<td>' . $row['l_leave_end_date'] . '<br> 09:15:00</td>';
        }
        // 09:45
        else if ($row['l_leave_end_time'] == '10:00:00' && $row['l_remark'] == '09:45:00') {
            echo '<td>' . $row['l_leave_end_date'] . '<br> 09:45:00</td>';
        }
        // 10:10
        else if ($row['l_leave_end_time'] == '10:30:00' && $row['l_remark'] == '10:10:00') {
            echo '<td>' . $row['l_leave_end_date'] . '<br> 10:10:00</td>';
        }
        // 10:15
        else if ($row['l_leave_end_time'] == '10:30:00' && $row['l_remark'] == '10:15:00') {
            echo '<td>' . $row['l_leave_end_date'] . '<br> 10:15:00</td>';
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
        // 13:15
        else if ($row['l_leave_end_time'] == '13:30:00' && $row['l_remark'] == '13:15:00') {
            echo '<td>' . $row['l_leave_end_date'] . '<br> 13:15:00</td>';
        }
        // 13:40
        else if ($row['l_leave_end_time'] == '14:00:00' && $row['l_remark'] == '13:40:00') {
            echo '<td>' . $row['l_leave_end_date'] . '<br> 13:40:00</td>';
        }
        // 13:45
        else if ($row['l_leave_end_time'] == '14:00:00' && $row['l_remark'] == '13:45:00') {
            echo '<td>' . $row['l_leave_start_date'] . '<br> 13:45:00</td>';
        }
        // 14:10
        else if ($row['l_leave_end_time'] == '14:30:00' && $row['l_remark'] == '14:10:00') {
            echo '<td>' . $row['l_leave_end_date'] . '<br> 14:10:00</td>';
        }
        // 14:15
        else if ($row['l_leave_end_time'] == '14:30:00' && $row['l_remark'] == '14:15:00') {
            echo '<td>' . $row['l_leave_end_date'] . '<br> 14:15:00</td>';
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
        // 15:15
        else if ($row['l_leave_end_time'] == '15:30:00' && $row['l_remark'] == '15:15:00') {
            echo '<td>' . $row['l_leave_end_date'] . '<br> 15:15:00</td>';
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
        // 16:15
        else if ($row['l_leave_end_time'] == '16:30:00' && $row['l_remark'] == '16:15:00') {
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

        if ($row['l_leave_id'] == 7) {
            echo '';
        } else {
            echo '<span class="text-primary">' . $leave_days . ' วัน ' . $leave_hours . ' ชั่วโมง ' . $leave_minutes . ' นาที</span>';
        }
        echo '</td>';

        // 12
        echo '</td>';
        if (!empty($row['l_file'])) {
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
        echo '<td hidden>' . $row['l_workplace'] . '</td>';

        // 29
        echo '<td><button type="button" class="btn btn-warning  edit-btn" data-createdatetime="' . $row['l_create_datetime'] . '" data-usercode="' . $row['l_usercode'] . '">
        <i class="fa-solid fa-pen-to-square"></i>แก้ไข</button></td>';

        // 30
        if ($row['l_hr_status'] == 2 || $row['l_hr_status'] == 3) {
            echo "<td><button type='button' class='btn btn-primary leaveChk' data-bs-toggle='modal' data-bs-target='#leaveModal' disabled>$btnCheck</button></td>";
        } else {
            echo "<td><button type='button' class='btn btn-primary leaveChk' data-bs-toggle='modal' data-bs-target='#leaveModal'>$btnCheck</button></td>";
        }

        echo '</tr>';
        $rowNumber--;
    }
} else {
    echo '<tr><td colspan="18" style="text-align: left; color:red;">ไม่พบข้อมูล</td></tr>';
}
?>
            </tbody>
        </table>
        <?php
echo '<div class="pagination">';
echo '<ul class="pagination">';

if ($currentPage > 1) {
    echo '<li class="page-item"><a class="page-link" href="?page=1&month=' . urlencode($selectedMonth) . '&codeSearch=' . urlencode($searchCode) . '">&laquo;</a></li>';
    echo '<li class="page-item"><a class="page-link" href="?page=' . ($currentPage - 1) . '&month=' . urlencode($selectedMonth) . '&codeSearch=' . urlencode($searchCode) . '">&lt;</a></li>';
}

$startPage = max(1, $currentPage - 2);
$endPage = min($totalPages, $currentPage + 2);

for ($i = $startPage; $i <= $endPage; $i++) {
    if ($i == $currentPage) {
        echo '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
    } else {
        echo '<li class="page-item"><a class="page-link" href="?page=' . $i . '&month=' . urlencode($selectedMonth) . '&codeSearch=' . urlencode($searchCode) . '">' . $i . '</a></li>';
    }
}

if ($currentPage < $totalPages) {
    echo '<li class="page-item"><a class="page-link" href="?page=' . ($currentPage + 1) . '&month=' . urlencode($selectedMonth) . '&codeSearch=' . urlencode($searchCode) . '">&gt;</a></li>';
    echo '<li class="page-item"><a class="page-link" href="?page=' . $totalPages . '&month=' . urlencode($selectedMonth) . '&codeSearch=' . urlencode($searchCode) . '">&raquo;</a></li>';
}

echo '</ul>';

// Input to jump to a specific page
echo '<input type="number" id="page-input" max="' . $totalPages . '" class="mx-2 form-control d-inline" style="width: 100px; height: 40px; text-align: center;" placeholder="เลขหน้า" value="' . $currentPage . '" onchange="changePage(this.value, \'' . $selectedMonth . '\', \'' . $searchCode . '\')">';

echo '</div>';

?>

        <!-- Modal เช็คการลา -->
        <div class="modal fade" id="leaveModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
            aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title
                        01f s-5" id="staticBackdropLabel">ข้อมูลการลา</h4>
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">X</button>
                    </div>
                    <div class="modal-body">

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger">ไม่ผ่าน</button>
                        <button type="button" class="btn btn-success">ผ่าน</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal แก้ไข-->
        <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">แก้ไขข้อมูล</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Form to edit data -->
                        <form id="editForm">
                            <div class="row">
                                <div class="col-6">
                                    <label for="userCode" class="form-label">รหัสพนักงาน</label>
                                    <input type="text" class="form-control" id="editUserCode" disabled>
                                </div>
                                <div class="col-6">
                                    <label for="name" class="form-label">ชื่อพนักงาน</label>
                                    <input type="text" class="form-control" id="editName" disabled>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <label for="editLeaveType" class="form-label">ประเภทการลา</label>
                                    <span style="color: red;">*</span>
                                    <select class="form-select editLeaveType" id="editLeaveType" required>
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
                                    <textarea class="form-control" id="editLeaveReason" rows="3"
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

                            <div class="mb-3" hidden>
                                <label for="createDateTime" class="form-label">วันที่สร้าง</label>
                                <input type="text" class="form-control" id="editCreateDateTime">
                            </div>

                            <div class="mt-3 d-flex justify-content-end">
                                <button type="submit" class="btn btn-success">บันทึกการแก้ไข</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <script>
    $(document).ready(function() {
        $.ajax({
            url: 'a_ajax_get_holiday.php', // สร้างไฟล์ PHP เพื่อตรวจสอบวันหยุด
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                var today = new Date(); // วันที่ปัจจุบัน

                flatpickr("#editLeaveStartDate", {
                    dateFormat: "d-m-Y", // ตั้งค่าเป็น วัน/เดือน/ปี
                    // defaultDate: today, // กำหนดวันที่เริ่มต้นเป็นวันที่ปัจจุบัน
                    disable: response.holidays // ปิดวันที่ที่เป็นวันหยุด
                });

                flatpickr("#editLeaveEndDate", {
                    dateFormat: "d-m-Y", // ตั้งค่าเป็น วัน/เดือน/ปี
                    // defaultDate: today, // กำหนดวันที่สิ้นสุดเป็นวันที่ปัจจุบัน
                    disable: response.holidays // ปิดวันที่ที่เป็นวันหยุด
                });

            }
        });

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
                var checkFirm = '1'; // ผ่าน

                var userName = '<?php echo $userName; ?>';

                // alert(userCode)
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
                    success: function(response) {
                        $('#leaveModal').modal('hide');
                        Swal.fire({
                            title: 'สำเร็จ!',
                            text: 'ตรวจสอบผ่านสำเร็จ',
                            icon: 'success',
                            confirmButtonText: 'ตกลง'
                        }).then(() => {
                            location
                                .reload(); // Reload the page after user clicks confirm
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                });
            });
            $('.modal-footer .btn-danger').off('click').on('click', function() {
                var userCode = $(rowData[5]).text(); // รหัสพนักงาน
                var createDate = $(rowData[7]).text(); // วันที่ยื่นใบลา
                var leaveType = $(rowData[0]).text(); // ประเภทการลา
                var empName = $(rowData[1]).text(); // ชื่อพนักงาน
                var depart = $(rowData[2]).text(); // แผนก
                var leaveReason = $(rowData[3]).text(); // เหตุผลการลา
                var leaveStartDate = $(rowData[9]).text(); // วันเวลาที่ลาเริ่มต้น
                var leaveEndDate = $(rowData[10]).text(); // วันเวลาที่ลาสิ้นสุด
                var leaveStatus = $(rowData[12]).text(); // สถานะใบลา

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
                        leaveStatus: leaveStatus

                    },
                    success: function(response) {
                        $('#leaveModal').modal('hide');
                        Swal.fire({
                            title: 'สำเร็จ!',
                            text: 'ตรวจสอบไม่ผ่านสำเร็จ',
                            icon: 'success',
                            confirmButtonText: 'ตกลง'
                        }).then(() => {
                            location
                                .reload(); // Reload the page after user clicks confirm
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                });
            });
        });

        $(".filter-card").click(function() {
            var status = $(this).data("status");
            var selectedMonth = $("#selectedMonth").val();
            var selectedYear = $("#selectedYear").val();

            // alert(status)
            $.ajax({
                url: 'a_ajax_get_leave_data.php',
                method: 'GET',
                data: {
                    status: status,
                    month: selectedMonth,
                    year: selectedYear
                },
                dataType: 'json',
                success: function(data) {
                    $("tbody").empty();
                    if (data.length === 0) {
                        $("tbody").append(
                            '<tr><td colspan="19" class="text-danger" style="text-align: left;">ไม่พบข้อมูล</td></tr>'
                        );
                    } else {
                        var totalItems = data.length;

                        $.each(data, function(index, row) {
                            // สถานะใบลา
                            var leaveStatus = '';
                            if (row['l_leave_status'] == 0) {
                                leaveStatus =
                                    '<div class="text-success"><?=$strStatusNormal?></div>';
                            } else if (row['l_leave_status'] == 1) {
                                leaveStatus =
                                    '<div class="text-danger"><?=$strStatusCancel?></div>';
                            } else {
                                leaveStatus = 'ไม่พบสถานะใบลา';
                            }

                            // สถานะอนุมัติ 1
                            var approveStatus;
                            if (row['l_approve_status'] == 0) {
                                approveStatus =
                                    '<div class="text-warning"><b><?=$strStatusProve0?></b></div>';
                            } else if (row['l_approve_status'] == 1) {
                                approveStatus =
                                    '<div class="text-warning"><b><?=$strStatusProve1?></b></div>';
                            } else if (row['l_approve_status'] == 2) {
                                approveStatus =
                                    '<div class="text-success"><b><?=$strStatusProve2?></b></div>';
                            } else if (row['l_approve_status'] == 3) {
                                approveStatus =
                                    '<div class="text-danger"><b><?=$strStatusProve3?></b></div>';
                            } else if (row['l_approve_status'] == 4) {
                                approveStatus =
                                    '<div class="text-success"><b><?=$strStatusProve4?></b></div>';
                            } else if (row['l_approve_status'] == 5) {
                                approveStatus =
                                    '<div class="text-danger"><b><?=$strStatusProve5?></b></div>';
                            } else if (row['l_approve_status'] == 6) {
                                approveStatus =
                                    '';
                            } else if (row['l_approve_status3'] == 7) {
                                approveStatus =
                                    '<div class="text-danger"><b>รอ GM อนุมัติ</b></div>';
                            } else if (row['l_approve_status3'] == 8) {
                                approveStatus =
                                    '<div class="text-danger"><b>GM อนุมัติ</b></div>';
                            } else if (row['l_approve_status3'] == 9) {
                                approveStatus =
                                    '<div class="text-danger"><b>GM ไม่อนุมัติ</b></div>';
                            } else {
                                approveStatus = 'ไม่พบสถานะ';
                            }

                            // สถานะอนุมัติ 2
                            var approveStatus2;
                            if (row['l_approve_status2'] == 0) {
                                approveStatus2 =
                                    '<div class="text-warning"><b><?=$strStatusProve0?></b></div>';
                            } else if (row['l_approve_status2'] == 1) {
                                approveStatus2 =
                                    '<div class="text-warning"><b><?=$strStatusProve1?></b></div>';
                            } else if (row['l_approve_status2'] == 2) {
                                approveStatus2 =
                                    '<div class="text-success"><b><?=$strStatusProve2?></b></div>';
                            } else if (row['l_approve_status2'] == 3) {
                                approveStatus2 =
                                    '<div class="text-danger"><b><?=$strStatusProve3?></b></div>';
                            } else if (row['l_approve_status2'] == 4) {
                                approveStatus2 =
                                    '<div class="text-success"><b><?=$strStatusProve4?></b></div>';
                            } else if (row['l_approve_status2'] == 5) {
                                approveStatus2 =
                                    '<div class="text-danger"><b><?=$strStatusProve5?></b></div>';
                            } else if (row['l_approve_status2'] == 6) {
                                approveStatus2 =
                                    '';
                            } else if (row['l_approve_status3'] == 7) {
                                approveStatus2 =
                                    '<div class="text-danger"><b>รอ GM อนุมัติ</b></div>';
                            } else if (row['l_approve_status3'] == 8) {
                                approveStatus2 =
                                    '<div class="text-danger"><b>GM อนุมัติ</b></div>';
                            } else if (row['l_approve_status3'] == 9) {
                                approveStatus2 =
                                    '<div class="text-danger"><b>GM ไม่อนุมัติ</b></div>';
                            } else {
                                approveStatus2 = 'ไม่พบสถานะ';
                            }

                            // สถานะอนุมัติ 3
                            var approveStatus3;
                            if (row['l_approve_status3'] == 0) {
                                approveStatus3 =
                                    '<div class="text-warning"><b><?=$strStatusProve0?></b></div>';
                            } else if (row['l_approve_status3'] == 1) {
                                approveStatus3 =
                                    '<div class="text-warning"><b><?=$strStatusProve1?></b></div>';
                            } else if (row['l_approve_status3'] == 2) {
                                approveStatus3 =
                                    '<div class="text-success"><b><?=$strStatusProve2?></b></div>';
                            } else if (row['l_approve_status3'] == 3) {
                                approveStatus3 =
                                    '<div class="text-danger"><b><?=$strStatusProve3?></b></div>';
                            } else if (row['l_approve_status3'] == 4) {
                                approveStatus3 =
                                    '<div class="text-success"><b><?=$strStatusProve4?></b></div>';
                            } else if (row['l_approve_status3'] == 5) {
                                approveStatus3 =
                                    '<div class="text-danger"><b><?=$strStatusProve5?></b></div>';
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
                            } else if (row['l_approve_status3'] == 7) {
                                approveStatus3 =
                                    '<div class="text-danger"><b>รอ GM อนุมัติ</b></div>';
                            } else if (row['l_approve_status3'] == 8) {
                                approveStatus3 =
                                    '<div class="text-danger"><b>GM อนุมัติ</b></div>';
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
                                    '<div class="text-warning"><b><?=$strStatusHR0?></b></div>';
                            } else if (row['l_hr_status'] == 1) {
                                confirmStatus =
                                    '<div class="text-success"><b><?=$strStatusHR1?></b></div>';
                            } else if (row['l_hr_status'] == 2) {
                                confirmStatus =
                                    '<div class="text-danger"><b><?=$strStatusHR2?></b></div>';
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
                                '<td hidden>' + (row['l_name'] ? row['l_name'] :
                                    '') +
                                '</td>' +

                                // 2
                                '<td hidden>' + (row['l_department'] ? row[
                                        'l_department'] :
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
                                '<td>' + (row['l_usercode'] ? row[
                                        'l_usercode'] :
                                    '') +
                                '</td>' +

                                // 6
                                '<td>' + '<span class="text-primary">' + (row[
                                        'l_name'] ?
                                    row[
                                        'l_name'] : '') + '</span><br>' +
                                'แผนก : ' + (row['l_department'] ? row[
                                        'l_department'] :
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
                                    (row['l_leave_start_time'] ==
                                        '09:00:00' &&
                                        row[
                                            'l_remark'] == '08:45:00') ?
                                    '08:45:00' :
                                    (row['l_leave_start_time'] ==
                                        '10:00:00' &&
                                        row[
                                            'l_remark'] == '09:45:00') ?
                                    '09:45:00' :
                                    (row['l_leave_start_time'] ==
                                        '11:00:00' &&
                                        row[
                                            'l_remark'] == '10:45:00') ?
                                    '10:45:00' :
                                    (row['l_leave_start_time'] ==
                                        '12:00:00') ?
                                    '11:45:00' :
                                    (row['l_leave_start_time'] ==
                                        '13:00:00') ?
                                    '12:45:00' :
                                    (row['l_leave_start_time'] ==
                                        '13:30:00' &&
                                        row[
                                            'l_remark'] == '13:15:00') ?
                                    '13:15:00' :
                                    (row['l_leave_start_time'] ==
                                        '14:00:00' &&
                                        row[
                                            'l_remark'] == '13:45:00') ?
                                    '13:45:00' :
                                    (row['l_leave_start_time'] ==
                                        '14:30:00' &&
                                        row[
                                            'l_remark'] == '14:15:00') ?
                                    '14:15:00' :
                                    (row['l_leave_start_time'] ==
                                        '15:00:00' &&
                                        row[
                                            'l_remark'] == '14:45:00') ?
                                    '14:45:00' :
                                    (row['l_leave_start_time'] ==
                                        '15:30:00' &&
                                        row[
                                            'l_remark'] == '15:15:00') ?
                                    '15:15:00' :
                                    (row['l_leave_start_time'] ==
                                        '16:00:00' &&
                                        row[
                                            'l_remark'] == '15:45:00') ?
                                    '15:45:00' :
                                    (row['l_leave_start_time'] ==
                                        '16:30:00' &&
                                        row[
                                            'l_remark'] == '16:15:00') ?
                                    '16:15:00' :
                                    (row['l_leave_start_time'] ==
                                        '17:00:00') ?
                                    '16:40:00' :
                                    row['l_leave_start_time']
                                ) : '') +
                                '</td>' +

                                // 10
                                '<td>' + (row['l_leave_end_date'] ? row[
                                    'l_leave_end_date'] : '') + '<br>' +
                                (row['l_leave_end_time'] ? (
                                    // Check if the l_leave_start_time and l_remark match certain values
                                    (row['l_leave_end_time'] ==
                                        '09:00:00' &&
                                        row[
                                            'l_remark'] == '08:45:00') ?
                                    '08:45:00' :
                                    (row['l_leave_end_time'] ==
                                        '10:00:00' &&
                                        row[
                                            'l_remark'] == '09:45:00') ?
                                    '09:45:00' :
                                    (row['l_leave_end_time'] ==
                                        '11:00:00' &&
                                        row[
                                            'l_remark'] == '10:45:00') ?
                                    '10:45:00' :
                                    (row['l_leave_end_time'] ==
                                        '12:00:00') ?
                                    '11:45:00' :
                                    (row['l_leave_end_time'] ==
                                        '13:00:00') ?
                                    '12:45:00' :
                                    (row['l_leave_end_time'] ==
                                        '13:30:00' &&
                                        row[
                                            'l_remark'] == '13:15:00') ?
                                    '13:15:00' :
                                    (row['l_leave_end_time'] ==
                                        '14:00:00' &&
                                        row[
                                            'l_remark'] == '13:45:00') ?
                                    '13:45:00' :
                                    (row['l_leave_end_time'] ==
                                        '14:30:00' &&
                                        row[
                                            'l_remark'] == '14:15:00') ?
                                    '14:15:00' :
                                    (row['l_leave_end_time'] ==
                                        '15:00:00' &&
                                        row[
                                            'l_remark'] == '14:45:00') ?
                                    '14:45:00' :
                                    (row['l_leave_end_time'] ==
                                        '15:30:00' &&
                                        row[
                                            'l_remark'] == '15:15:00') ?
                                    '15:15:00' :
                                    (row['l_leave_end_time'] ==
                                        '16:00:00' &&
                                        row[
                                            'l_remark'] == '15:45:00') ?
                                    '15:45:00' :
                                    (row['l_leave_end_time'] ==
                                        '16:30:00' &&
                                        row[
                                            'l_remark'] == '16:15:00') ?
                                    '16:15:00' :
                                    (row['l_leave_end_time'] ==
                                        '17:00:00') ?
                                    '16:40:00' :
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
                                '<td>' + (row['l_approve_name'] ? row[
                                    'l_approve_name'] : '') +
                                '</td>' +

                                // 15
                                '<td>' + approveStatus + '</td>' +

                                // 16
                                '<td>' + (row['l_approve_datetime'] !== null ?
                                    row[
                                        'l_approve_datetime'] : '') + '</td>' +

                                // 17
                                '<td>' + (row['l_reason'] ? row['l_reason'] :
                                    '') +
                                '</td>' +

                                // 18
                                '<td>' + (row['l_approve_name2'] ? row[
                                        'l_approve_name2'] :
                                    '') + '</td>' +

                                // 19
                                '<td>' + approveStatus2 + '</td>' +

                                // 20
                                '<td>' + (row['l_approve_datetime2'] !== null ?
                                    row[
                                        'l_approve_datetime2'] : '') + '</td>' +

                                // 21
                                '<td>' + (row['l_reason2'] ? row['l_reason2'] :
                                    '') + '</td>' +

                                // 22
                                '<td>' + (row['l_approve_name3'] ? row[
                                        'l_approve_name3'] :
                                    '') + '</td>' +

                                // 23
                                '<td>' + approveStatus3 + '</td>' +

                                // 24
                                '<td>' + (row['l_approve_datetime3'] !== null ?
                                    row[
                                        'l_approve_datetime3'] : '') + '</td>' +

                                // 25
                                '<td>' + (row['l_reason3'] ? row['l_reason3'] :
                                    '') + '</td>' +

                                // 26
                                '<td>' + confirmStatus + '</td>' +

                                // 27
                                '<td>' + (row['l_remark2'] ? row['l_remark2'] :
                                    '') + '</td>' +

                                // 28
                                '<td>' +
                                '<button type="button" class="btn btn-warning edit-btn" ' +
                                'data-createdatetime="' + (row[
                                    'l_create_datetime']) +
                                '" ' +
                                'data-usercode="' + (row[
                                    'l_usercode']) +
                                '">' +
                                '<i class="fa-solid fa-pen-to-square"></i> แก้ไข' +
                                '</button>' +
                                '</td>' +
                                '<td>';
                            if (row['l_hr_status'] == 2 || row[
                                    'l_hr_status'] ==
                                3) {
                                newRow +=
                                    '<button type="button" class="btn btn-primary leaveChk" data-bs-toggle="modal" data-bs-target="#leaveModal">ตรวจสอบ</button>';
                            } else {
                                newRow +=
                                    '<button type="button" class="btn btn-primary leaveChk" data-bs-toggle="modal" data-bs-target="#leaveModal">ตรวจสอบ</button>';
                            }
                            newRow += '</td></tr>';

                            $("tbody").append(newRow);
                        });

                        $(".leaveChk").click(function() {
                            var rowData = $(this).closest("tr").find(
                                "td");

                            // Populate modal content
                            $('#leaveModal .modal-body').html(
                                '<table class="table table-bordered">' +
                                '<tr>' +
                                '<th>รหัสพนักงาน</th>' +
                                '<td>' + $(rowData[5]).text() +
                                '</td>' +
                                '</tr>' +
                                '<tr>' +
                                '<th>ชื่อ - นามสกุล</th>' +
                                '<td>' + $(rowData[1]).text() +
                                '</td>' +
                                '</tr>' +
                                '<tr>' +
                                '<th>แผนก</th>' +
                                '<td>' + $(rowData[2]).text() +
                                '</td>' +
                                '</tr>' +
                                '<tr>' +
                                '<th>วันที่ยื่นใบลา</th>' +
                                '<td>' + $(rowData[7]).text() +
                                '</td>' +
                                '</tr>' +
                                '<tr>' +
                                '<th>ประเภทการลา</th>' +
                                '<td>' + $(rowData[0]).text() +
                                '</td>' +
                                '</tr>' +
                                '<tr>' +
                                '<th>เหตุผลการลา</th>' +
                                '<td>' + $(rowData[3]).text() +
                                '</td>' +
                                '</tr>' +
                                '<tr>' +
                                '<th>วันเวลาที่ลา</th>' +
                                '<td>' + $(rowData[9]).text() +
                                ' ถึง ' + $(
                                    rowData[10])
                                .text() + '</td>' +
                                '</tr>' +
                                '<tr>' +
                                '<th>สถานะใบลา</th>' +
                                '<td>' + $(rowData[13]).html() +
                                '</td>' +
                                '</tr>' +
                                '</table>'
                            );

                            $('#leaveModal').modal('show');
                            $('.modal-footer .btn-success').off('click')
                                .on(
                                    'click',
                                    function() {
                                        var leaveType = $(rowData[0])
                                            .text(); // ประเภทการลา
                                        var empName = $(rowData[1])
                                            .text(); // ชื่อพนักงาน
                                        var depart = $(rowData[2])
                                            .text(); // แผนก
                                        var leaveReason = $(rowData[3])
                                            .text(); // เหตุผลการลา
                                        var userCode = $(rowData[5])
                                            .text(); // รหัสพนักงาน
                                        var createDate = $(rowData[7])
                                            .text(); // วันที่ยื่นใบลา
                                        var leaveStartDate = $(rowData[
                                                9])
                                            .text(); // วันเวลาที่ลาเริ่มต้น
                                        var leaveEndDate = $(rowData[
                                                10])
                                            .text(); // วันเวลาที่ลาสิ้นสุด
                                        var leaveStatus = $(rowData[12])
                                            .text(); // สถานะใบลา

                                        var checkFirm = '1'; // ผ่าน
                                        var userName =
                                            '<?php echo $userName; ?>';

                                        $.ajax({
                                            url: 'a_ajax_upd_status.php',
                                            method: 'POST',
                                            data: {
                                                leaveType: leaveType,
                                                empName: empName,
                                                depart: depart,
                                                leaveReason: leaveReason,
                                                userCode: userCode,
                                                createDate: createDate,
                                                leaveStartDate: leaveStartDate,
                                                leaveEndDate: leaveEndDate,
                                                checkFirm: checkFirm,
                                                userName: userName,
                                                empName: empName,
                                                leaveStatus: leaveStatus
                                                // name: name,
                                                // createDate: createDate,
                                                // userCode: userCode,
                                                // userName: userName,
                                                // leaveType: leaveType,
                                                // leaveReason: leaveReason,
                                                // leaveStartDate: leaveStartDate,
                                                // leaveEndDate: leaveEndDate,
                                                // depart: depart,
                                                // checkFirm: checkFirm
                                            },
                                            success: function(
                                                response) {
                                                $('#leaveModal')
                                                    .modal(
                                                        'hide'
                                                    );
                                                Swal.fire({
                                                    title: 'สำเร็จ!',
                                                    text: 'ตรวจสอบผ่านสำเร็จ',
                                                    icon: 'success',
                                                    confirmButtonText: 'ตกลง'
                                                }).then(
                                                    () => {
                                                        location
                                                            .reload(); // Reload the page after user clicks confirm
                                                    });
                                            },
                                            error: function(xhr,
                                                status,
                                                error) {
                                                console
                                                    .error(
                                                        error
                                                    );
                                            }
                                        });
                                    });
                            $('.modal-footer .btn-danger').off('click')
                                .on(
                                    'click',
                                    function() {
                                        var leaveType = $(rowData[0])
                                            .text(); // ประเภทการลา
                                        var empName = $(rowData[1])
                                            .text(); // ชื่อพนักงาน
                                        var depart = $(rowData[2])
                                            .text(); // แผนก
                                        var leaveReason = $(rowData[3])
                                            .text(); // เหตุผลการลา
                                        var userCode = $(rowData[5])
                                            .text(); // รหัสพนักงาน
                                        var createDate = $(rowData[7])
                                            .text(); // วันที่ยื่นใบลา
                                        var leaveStartDate = $(rowData[
                                                9])
                                            .text(); // วันเวลาที่ลาเริ่มต้น
                                        var leaveEndDate = $(rowData[
                                                10])
                                            .text(); // วันเวลาที่ลาสิ้นสุด
                                        var leaveStatus = $(rowData[12])
                                            .text(); // สถานะใบลา

                                        var checkFirm = '2'; // ไม่ผ่าน
                                        var userName =
                                            '<?php echo $userName; ?>';

                                        console.log(leaveType)
                                        console.log(name)
                                        console.log(depart)
                                        console.log(leaveReason)
                                        console.log(userCode)
                                        console.log(createDate)
                                        console.log(leaveStartDate)
                                        console.log(leaveEndDate)
                                        console.log(checkFirm)
                                        console.log(userName)

                                        $.ajax({
                                            url: 'a_ajax_upd_status.php',
                                            method: 'POST',
                                            data: {
                                                leaveType: leaveType,
                                                empName: empName,
                                                depart: depart,
                                                leaveReason: leaveReason,
                                                userCode: userCode,
                                                createDate: createDate,
                                                leaveStartDate: leaveStartDate,
                                                leaveEndDate: leaveEndDate,
                                                checkFirm: checkFirm,
                                                userName: userName,
                                                empName: empName,
                                                leaveStatus: leaveStatus
                                            },
                                            success: function(
                                                response) {
                                                $('#leaveModal')
                                                    .modal(
                                                        'hide'
                                                    );
                                                Swal.fire({
                                                    title: 'สำเร็จ!',
                                                    text: 'ตรวจสอบไม่ผ่านสำเร็จ',
                                                    icon: 'success',
                                                    confirmButtonText: 'ตกลง'
                                                }).then(
                                                    () => {
                                                        location
                                                            .reload(); // Reload the page after user clicks confirm
                                                    });
                                            },
                                            error: function(xhr,
                                                status,
                                                error) {
                                                console
                                                    .error(
                                                        error
                                                    );
                                            }
                                        });
                                    });
                        });

                        $('.edit-btn').click(function() {
                            // Get data attributes from the button
                            var createDateTime = $(this).data('createdatetime');
                            var userCode = $(this).data('usercode');

                            // Send AJAX request to fetch data from the server
                            $.ajax({
                                url: 'a_ajax_get_leave.php', // Replace with your PHP file to fetch data
                                method: 'POST',
                                data: {
                                    createDateTime: createDateTime,
                                    userCode: userCode,
                                },
                                dataType: 'json', // Expect JSON response
                                success: function(response) {
                                    if (response.status ===
                                        'success') {
                                        // Populate modal fields with the fetched data
                                        $('.editLeaveType').val(
                                            response
                                            .l_leave_id);
                                        $('#editCreateDateTime')
                                            .val(
                                                response
                                                .l_create_datetime);
                                        $('#editUserCode').val(
                                            response
                                            .l_usercode);
                                        $('#editLeaveReason').val(
                                            response
                                            .l_leave_reason);
                                        $('#editName').val(response
                                            .l_name);

                                        var startDate = response
                                            .l_leave_start_date;
                                        var endDate = response
                                            .l_leave_end_date;

                                        var dateParts = startDate
                                            .split(
                                                '-'); // แยกวันที่
                                        var dateParts2 = endDate
                                            .split(
                                                '-'); // แยกวันที่

                                        var formattedDate =
                                            dateParts[
                                                2] + '-' +
                                            dateParts[
                                                1] +
                                            '-' +
                                            dateParts[
                                                0
                                            ]; // แปลงเป็น d-m-y
                                        var formattedDate2 =
                                            dateParts2[
                                                2] + '-' +
                                            dateParts2[
                                                1] + '-' +
                                            dateParts2[
                                                0
                                            ]; // แปลงเป็น d-m-y

                                        $('#editLeaveStartDate')
                                            .val(
                                                formattedDate);
                                        $('#editLeaveEndDate').val(
                                            formattedDate2);

                                        // เวลาที่เริ่มต้น
                                        // 08:10
                                        if (response
                                            .l_leave_start_time ===
                                            "08:30:00" &&
                                            response
                                            .l_remark === "08:10:00"
                                        ) {
                                            $('#editLeaveStartTime2')
                                                .val(response
                                                    .l_remark);
                                        }
                                        // 08:15
                                        else if (response
                                            .l_leave_start_time ===
                                            "08:30:00" &&
                                            response
                                            .l_remark === "08:15:00"
                                        ) {
                                            $('#editLeaveStartTime2')
                                                .val(response
                                                    .l_remark);
                                        }
                                        // 08:45
                                        else if (response
                                            .l_leave_start_time ===
                                            "09:00:00" &&
                                            response
                                            .l_remark === "08:45:00"
                                        ) {
                                            $('#editLeaveStartTime2')
                                                .val(response
                                                    .l_remark);
                                        }
                                        // 09:10
                                        else if (response
                                            .l_leave_start_time ===
                                            "09:30:00" &&
                                            response
                                            .l_remark === "09:10:00"
                                        ) {
                                            $('#editLeaveStartTime2')
                                                .val(response
                                                    .l_remark);
                                        }
                                        // 09:15
                                        else if (response
                                            .l_leave_start_time ===
                                            "09:30:00" &&
                                            response
                                            .l_remark === "09:15:00"
                                        ) {
                                            $('#editLeaveStartTime2')
                                                .val(response
                                                    .l_remark);
                                        }
                                        // 09:45
                                        else if (response
                                            .l_leave_start_time ===
                                            "10:00:00" &&
                                            response
                                            .l_remark === "09:45:00"
                                        ) {
                                            $('#editLeaveStartTime2')
                                                .val(response
                                                    .l_remark);
                                        }
                                        // 10:10
                                        else if (response
                                            .l_leave_start_time ===
                                            "10:30:00" &&
                                            response
                                            .l_remark === "10:10:00"
                                        ) {
                                            $('#editLeaveStartTime2')
                                                .val(response
                                                    .l_remark);
                                        }
                                        // 10:15
                                        else if (response
                                            .l_leave_start_time ===
                                            "10:30:00" &&
                                            response
                                            .l_remark === "10:15:00"
                                        ) {
                                            $('#editLeaveStartTime2')
                                                .val(response
                                                    .l_remark);
                                        }
                                        // 10:45
                                        else if (response
                                            .l_leave_start_time ===
                                            "11:00:00" &&
                                            response
                                            .l_remark === "10:45:00"
                                        ) {
                                            $('#editLeaveStartTime2')
                                                .val(response
                                                    .l_remark);
                                        }
                                        // 11:45
                                        else if (response
                                            .l_leave_start_time ===
                                            "12:00:00" &&
                                            response
                                            .l_remark === "11:45:00"
                                        ) {
                                            $('#editLeaveStartTime2')
                                                .val(response
                                                    .l_remark);
                                        }
                                        // 12:45
                                        else if (response
                                            .l_leave_start_time ===
                                            "13:00:00") {
                                            $('#editLeaveStartTime2')
                                                .val(
                                                    '12:45:00'
                                                ); // กำหนดค่าใหม่
                                        }
                                        // 13:10
                                        else if (response
                                            .l_leave_start_time ===
                                            "13:30:00" &&
                                            response
                                            .l_remark === "13:10:00"
                                        ) {
                                            $('#editLeaveStartTime2')
                                                .val(response
                                                    .l_remark);
                                        }
                                        // 13:15
                                        else if (response
                                            .l_leave_start_time ===
                                            "13:30:00" &&
                                            response
                                            .l_remark === "13:15:00"
                                        ) {
                                            $('#editLeaveStartTime2')
                                                .val(response
                                                    .l_remark);
                                        }
                                        // 13:40
                                        else if (response
                                            .l_leave_start_time ===
                                            "14:00:00" &&
                                            response
                                            .l_remark === "13:40:00"
                                        ) {
                                            $('#editLeaveStartTime2')
                                                .val(response
                                                    .l_remark);
                                        }
                                        // 13:45
                                        else if (response
                                            .l_leave_start_time ===
                                            "14:00:00" &&
                                            response
                                            .l_remark === "13:45:00"
                                        ) {
                                            $('#editLeaveStartTime2')
                                                .val(response
                                                    .l_remark);
                                        }
                                        // 14:10
                                        else if (response
                                            .l_leave_start_time ===
                                            "14:30:00" &&
                                            response
                                            .l_remark === "14:10:00"
                                        ) {
                                            $('#editLeaveStartTime2')
                                                .val(response
                                                    .l_remark);
                                        }
                                        // 14:15
                                        else if (response
                                            .l_leave_start_time ===
                                            "14:30:00" &&
                                            response
                                            .l_remark === "14:15:00"
                                        ) {
                                            $('#editLeaveStartTime2')
                                                .val(response
                                                    .l_remark);
                                        }
                                        // 14:40
                                        else if (response
                                            .l_leave_start_time ===
                                            "15:00:00" &&
                                            response
                                            .l_remark === "14:40:00"
                                        ) {
                                            $('#editLeaveStartTime2')
                                                .val(response
                                                    .l_remark);
                                        }
                                        // 14:45
                                        else if (response
                                            .l_leave_start_time ===
                                            "15:00:00" &&
                                            response
                                            .l_remark === "14:45:00"
                                        ) {
                                            $('#editLeaveStartTime2')
                                                .val(response
                                                    .l_remark);
                                        }
                                        // 15:10
                                        else if (response
                                            .l_leave_start_time ===
                                            "15:30:00" &&
                                            response
                                            .l_remark === "15:10:00"
                                        ) {
                                            $('#editLeaveStartTime2')
                                                .val(response
                                                    .l_remark);
                                        }
                                        // 15:15
                                        else if (response
                                            .l_leave_start_time ===
                                            "15:30:00" &&
                                            response
                                            .l_remark === "15:15:00"
                                        ) {
                                            $('#editLeaveStartTime2')
                                                .val(response
                                                    .l_remark);
                                        }
                                        // 15:40
                                        else if (response
                                            .l_leave_start_time ===
                                            "16:00:00" &&
                                            response
                                            .l_remark === "15:40:00"
                                        ) {
                                            $('#editLeaveStartTime2')
                                                .val(response
                                                    .l_remark);
                                        }
                                        // 15:45
                                        else if (response
                                            .l_leave_start_time ===
                                            "16:00:00" &&
                                            response
                                            .l_remark === "15:45:00"
                                        ) {
                                            $('#editLeaveStartTime2')
                                                .val(response
                                                    .l_remark);
                                        }
                                        // 16:10
                                        else if (response
                                            .l_leave_start_time ===
                                            "16:30:00" &&
                                            response
                                            .l_remark === "16:10:00"
                                        ) {
                                            $('#editLeaveStartTime2')
                                                .val(response
                                                    .l_remark);
                                        }
                                        // 16:15
                                        else if (response
                                            .l_leave_start_time ===
                                            "16:30:00" &&
                                            response
                                            .l_remark === "16:15:00"
                                        ) {
                                            $('#editLeaveStartTime2')
                                                .val(response
                                                    .l_remark);
                                        }
                                        // 16:40
                                        else if (response
                                            .l_leave_start_time ===
                                            "17:00:00") {
                                            $('#editLeaveStartTime2')
                                                .val('16:40:00');
                                        } else {
                                            $('#editLeaveStartTime2')
                                                .val(response
                                                    .l_leave_start_time
                                                );
                                        }

                                        // เวลาที่สิ้นสุด
                                        // 08:10
                                        if (response
                                            .l_leave_end_time ===
                                            "08:30:00" && response
                                            .l_remark === "08:10:00"
                                        ) {
                                            $('#editLeaveEndTime2')
                                                .val(
                                                    response
                                                    .l_remark);
                                        }
                                        // 08:15
                                        else if (response
                                            .l_leave_end_time ===
                                            "08:30:00" &&
                                            response
                                            .l_remark === "08:15:00"
                                        ) {
                                            $('#editLeaveEndTime2')
                                                .val(
                                                    response
                                                    .l_remark);
                                        }
                                        // 08:45
                                        else if (response
                                            .l_leave_end_time ===
                                            "09:00:00" &&
                                            response
                                            .l_remark === "08:45:00"
                                        ) {
                                            $('#editLeaveEndTime2')
                                                .val(
                                                    response
                                                    .l_remark);
                                        }
                                        // 09:10
                                        else if (response
                                            .l_leave_end_time ===
                                            "09:30:00" &&
                                            response
                                            .l_remark === "09:10:00"
                                        ) {
                                            $('#editLeaveEndTime2')
                                                .val(
                                                    response
                                                    .l_remark);
                                        }
                                        // 09:15
                                        else if (response
                                            .l_leave_end_time ===
                                            "09:30:00" &&
                                            response
                                            .l_remark === "09:15:00"
                                        ) {
                                            $('#editLeaveEndTime2')
                                                .val(
                                                    response
                                                    .l_remark);
                                        }
                                        // 09:45
                                        else if (response
                                            .l_leave_end_time ===
                                            "10:00:00" &&
                                            response
                                            .l_remark === "09:45:00"
                                        ) {
                                            $('#editLeaveEndTime2')
                                                .val(
                                                    response
                                                    .l_remark);
                                        }
                                        // 10:10
                                        else if (response
                                            .l_leave_end_time ===
                                            "10:30:00" &&
                                            response
                                            .l_remark === "10:10:00"
                                        ) {
                                            $('#editLeaveEndTime2')
                                                .val(
                                                    response
                                                    .l_remark);
                                        }
                                        // 10:15
                                        else if (response
                                            .l_leave_end_time ===
                                            "10:30:00" &&
                                            response
                                            .l_remark === "10:15:00"
                                        ) {
                                            $('#editLeaveEndTime2')
                                                .val(
                                                    response
                                                    .l_remark);
                                        }
                                        // 10:45
                                        else if (response
                                            .l_leave_end_time ===
                                            "11:00:00" &&
                                            response
                                            .l_remark === "10:45:00"
                                        ) {
                                            $('#editLeaveEndTime2')
                                                .val(
                                                    response
                                                    .l_remark);
                                        }
                                        // 11:45
                                        else if (response
                                            .l_leave_end_time ===
                                            "12:00:00" &&
                                            response
                                            .l_remark === "11:45:00"
                                        ) {
                                            $('#editLeaveEndTime2')
                                                .val(
                                                    response
                                                    .l_remark);
                                        }
                                        // 12:45
                                        else if (response
                                            .l_leave_end_time ===
                                            "13:00:00") {
                                            $('#editLeaveEndTime2')
                                                .val(
                                                    '12:45:00'
                                                ); // กำหนดค่าใหม่
                                        }
                                        // 13:10
                                        else if (response
                                            .l_leave_end_time ===
                                            "13:30:00" &&
                                            response
                                            .l_remark === "13:10:00"
                                        ) {
                                            $('#editLeaveEndTime2')
                                                .val(
                                                    response
                                                    .l_remark);
                                        }
                                        // 13:15
                                        else if (response
                                            .l_leave_end_time ===
                                            "13:30:00" &&
                                            response
                                            .l_remark === "13:15:00"
                                        ) {
                                            $('#editLeaveEndTime2')
                                                .val(
                                                    response
                                                    .l_remark);
                                        }
                                        // 13:40
                                        else if (response
                                            .l_leave_end_time ===
                                            "14:00:00" &&
                                            response
                                            .l_remark === "13:40:00"
                                        ) {
                                            $('#editLeaveEndTime2')
                                                .val(
                                                    response
                                                    .l_remark);
                                        }
                                        // 13:45
                                        else if (response
                                            .l_leave_end_time ===
                                            "14:00:00" &&
                                            response
                                            .l_remark === "13:45:00"
                                        ) {
                                            $('#editLeaveEndTime2')
                                                .val(
                                                    response
                                                    .l_remark);
                                        }
                                        // 14:10
                                        else if (response
                                            .l_leave_end_time ===
                                            "14:30:00" &&
                                            response
                                            .l_remark === "14:10:00"
                                        ) {
                                            $('#editLeaveEndTime2')
                                                .val(
                                                    response
                                                    .l_remark);
                                        }
                                        // 14:15
                                        else if (response
                                            .l_leave_end_time ===
                                            "14:30:00" &&
                                            response
                                            .l_remark === "14:15:00"
                                        ) {
                                            $('#editLeaveEndTime2')
                                                .val(
                                                    response
                                                    .l_remark);
                                        }
                                        // 14:40
                                        else if (response
                                            .l_leave_end_time ===
                                            "15:00:00" &&
                                            response
                                            .l_remark === "14:40:00"
                                        ) {
                                            $('#editLeaveEndTime2')
                                                .val(
                                                    response
                                                    .l_remark);
                                        }
                                        // 14:45
                                        else if (response
                                            .l_leave_end_time ===
                                            "15:00:00" &&
                                            response
                                            .l_remark === "14:45:00"
                                        ) {
                                            $('#editLeaveEndTime2')
                                                .val(
                                                    response
                                                    .l_remark);
                                        }
                                        // 15:10
                                        else if (response
                                            .l_leave_end_time ===
                                            "15:30:00" &&
                                            response
                                            .l_remark === "15:10:00"
                                        ) {
                                            $('#editLeaveEndTime2')
                                                .val(
                                                    response
                                                    .l_remark);
                                        }
                                        // 15:15
                                        else if (response
                                            .l_leave_end_time ===
                                            "15:30:00" &&
                                            response
                                            .l_remark === "15:15:00"
                                        ) {
                                            $('#editLeaveEndTime2')
                                                .val(
                                                    response
                                                    .l_remark);
                                        }
                                        // 15:40
                                        else if (response
                                            .l_leave_end_time ===
                                            "16:00:00" &&
                                            response
                                            .l_remark === "15:40:00"
                                        ) {
                                            $('#editLeaveEndTime2')
                                                .val(
                                                    response
                                                    .l_remark);
                                        }
                                        // 15:45
                                        else if (response
                                            .l_leave_end_time ===
                                            "16:00:00" &&
                                            response
                                            .l_remark === "15:45:00"
                                        ) {
                                            $('#editLeaveEndTime2')
                                                .val(
                                                    response
                                                    .l_remark);
                                        }
                                        // 16:10
                                        else if (response
                                            .l_leave_end_time ===
                                            "16:30:00" &&
                                            response
                                            .l_remark === "16:10:00"
                                        ) {
                                            $('#editLeaveEndTime2')
                                                .val(
                                                    response
                                                    .l_remark);
                                        }
                                        // 16:15
                                        else if (response
                                            .l_leave_end_time ===
                                            "16:30:00" &&
                                            response
                                            .l_remark === "16:15:00"
                                        ) {
                                            $('#editLeaveEndTime2')
                                                .val(
                                                    response
                                                    .l_remark);
                                        }
                                        // 16:40
                                        else if (response
                                            .l_leave_end_time ===
                                            "17:00:00") {
                                            $('#editLeaveEndTime2')
                                                .val(
                                                    '16:40:00');
                                        } else {
                                            $('#editLeaveEndTime2')
                                                .val(
                                                    response
                                                    .l_leave_end_time
                                                );
                                        }

                                        // Show the modal
                                        $('#editModal').modal(
                                            'show');
                                    } else {
                                        // Show error message from response
                                        alert(response.message ||
                                            'ไม่พบข้อมูล');
                                    }
                                },
                                error: function() {
                                    alert(
                                        'เกิดข้อผิดพลาดในการดึงข้อมูล'
                                    );
                                },
                            });
                        });

                        $('#editForm').submit(function(e) {
                            e.preventDefault();

                            var editCreateDateTime = $('#editCreateDateTime')
                                .val();
                            var editUserCode = $('#editUserCode').val();
                            var editLeaveType = $('#editLeaveType').val();
                            var editLeaveReason = $('#editLeaveReason').val();
                            var editLeaveStartDate = $('#editLeaveStartDate')
                                .val();
                            var editLeaveEndDate = $('#editLeaveEndDate').val();
                            var editLeaveStartTime = $('#editLeaveStartTime')
                                .val();
                            var editLeaveEndTime = $('#editLeaveEndTime').val();

                            if (editLeaveStartDate > editLeaveEndDate) {
                                Swal.fire({
                                    title: 'ไม่สามารถลาได้',
                                    text: 'กรุณาเลือกวันที่เริ่มต้นลาใหม่',
                                    icon: 'error',
                                    confirmButtonText: 'ตกลง'
                                })
                            } else if (editLeaveStartTime > editLeaveEndTime) {
                                Swal.fire({
                                    title: 'ไม่สามารถลาได้',
                                    text: 'กรุณาเลือกเวลาเริ่มต้นใหม่',
                                    icon: 'error',
                                    confirmButtonText: 'ตกลง'
                                })
                            } else {
                                $.ajax({
                                    url: 'a_upd_leave.php',
                                    method: 'POST',
                                    data: {
                                        editCreateDateTime: editCreateDateTime,
                                        editUserCode: editUserCode,
                                        editLeaveType: editLeaveType,
                                        editLeaveReason: editLeaveReason,
                                        editLeaveStartDate: editLeaveStartDate,
                                        editLeaveEndDate: editLeaveEndDate,
                                        editLeaveStartTime: editLeaveStartTime,
                                        editLeaveEndTime: editLeaveEndTime
                                    },
                                    success: function(response) {
                                        Swal.fire({
                                            title: 'แก้ไขสำเร็จ',
                                            icon: 'success',
                                            confirmButtonText: 'ตกลง'
                                        }).then((result) => {
                                            if (result
                                                .isConfirmed
                                            ) {
                                                location
                                                    .reload();
                                            }
                                        });

                                        $('#editModal').modal(
                                            'hide');
                                    },
                                    error: function() {
                                        alert(
                                            'เกิดข้อผิดพลาดในการบันทึกข้อมูล'
                                        );
                                    }
                                });
                            }
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching data:', error);
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

        $("#codeSearch").on("keyup", function() {
            var value3 = $(this).val().toLowerCase(); // ค่าที่กรอกในช่องค้นหา
            var page = "<?php echo $currentPage; ?>"; // หน้าปัจจุบันที่แสดงอยู่
            var selectedMonth = "<?php echo $selectedMonth; ?>"; // เดือนที่เลือก
            var selectedYear = "<?php echo $selectedYear; ?>"; // ปีที่เลือก

            var searchCode = value3; // ค่าของ codeSearch ที่กรอก

            $.ajax({
                url: "a_ajax_get_data_usercode.php", // ชื่อไฟล์ PHP ที่ใช้แสดงข้อมูล
                type: "GET",
                data: {
                    page: page,
                    month: selectedMonth,
                    year: selectedYear,
                    codeSearch: searchCode
                },
                success: function(response) {
                    // แทนที่เนื้อหาตารางด้วยข้อมูลใหม่ที่ได้จาก response
                    $("tbody").html(response);

                    // เปิด modal และกำหนดเนื้อหา
                    $(".leaveChk").click(function() {
                        var rowData = $(this).closest("tr").find("td");
                        var modalContent =
                            '<table class="table table-bordered">' +
                            '<tr><th>รหัสพนักงาน</th><td>' + $(rowData[
                                5])
                            .text() +
                            '</td></tr>' +
                            '<tr><th>ชื่อ - นามสกุล</th><td>' + $(
                                rowData[1])
                            .text() +
                            '</td></tr>' +
                            '<tr><th>แผนก</th><td>' + $(rowData[2])
                            .text() +
                            '</td></tr>' +
                            '<tr><th>วันที่ยื่นใบลา</th><td>' + $(
                                rowData[7])
                            .text() +
                            '</td></tr>' +
                            '<tr><th>ประเภทการลา</th><td>' + $(rowData[
                                0])
                            .text() +
                            '</td></tr>' +
                            '<tr><th>เหตุผลการลา</th><td>' + $(rowData[
                                3])
                            .text() +
                            '</td></tr>' +
                            '<tr><th>วันเวลาที่ลา</th><td>' + $(rowData[
                                9])
                            .text() +
                            ' ถึง ' +
                            $(rowData[10]).text() + '</td></tr>' +
                            '<tr><th>สถานะใบลา</th><td>' + $(rowData[
                                12])
                            .html() +
                            '</td></tr>' +
                            '</table>';

                        $('#leaveModal .modal-body').html(modalContent);

                        $('.modal-footer .btn-success').off('click').on('click',
                            function() {
                                var modalData = {
                                    createDate: $(rowData[7]).text(),
                                    userCode: $(rowData[5]).text(),
                                    userName: '<?php echo $userName; ?>',
                                    leaveType: $(rowData[0]).text(),
                                    leaveReason: $(rowData[3]).text(),
                                    leaveStartDate: $(rowData[9]).text(),
                                    leaveEndDate: $(rowData[10]).text(),
                                    depart: $(rowData[2]).text(),
                                    checkFirm: '1', // ผ่าน
                                    empName: $(rowData[1]).text(),
                                    leaveStatus: $(rowData[12]).text()
                                };

                                $.ajax({
                                    url: 'a_ajax_upd_status.php',
                                    method: 'POST',
                                    data: modalData,
                                    success: function(response) {
                                        $('#leaveModal').modal(
                                            'hide'); // ซ่อน Modal
                                        $('.modal-backdrop')
                                            .remove(); // ลบ Backdrop ที่ค้างอยู่
                                        $('body').removeClass(
                                            'modal-open'
                                        ); // เอา class modal-open ออกจาก body
                                        $('body').css(
                                            'padding-right', ''
                                        ); // ลบ padding-right หากเกิดจาก scroll bar

                                        // ใช้ AJAX เพื่อโหลดข้อมูลหน้าปัจจุบันอีกครั้ง
                                        var codeSearch = $(
                                                "#codeSearch").val()
                                            .toLowerCase();
                                        var page =
                                            "<?php echo $currentPage; ?>";
                                        var selectedMonth =
                                            "<?php echo $selectedMonth; ?>";
                                        var selectedYear =
                                            "<?php echo $selectedYear; ?>";

                                        $.ajax({
                                            url: "a_ajax_get_data_usercode.php",
                                            type: "GET",
                                            data: {
                                                page: page,
                                                month: selectedMonth,
                                                year: selectedYear,
                                                codeSearch: codeSearch
                                            },
                                            success: function(
                                                response
                                            ) {

                                            },
                                            error: function(
                                                xhr,
                                                status,
                                                error) {
                                                console
                                                    .error(
                                                        "Error:",
                                                        error
                                                    );
                                            }
                                        });
                                    },
                                    error: function(xhr, status,
                                        error) {
                                        console.error("Error:",
                                            error);
                                    }
                                });
                            });


                        // ปรับปรุงปุ่มใน modal สำหรับ "ไม่ผ่าน"
                        $('.modal-footer .btn-danger').off('click').on(
                            'click',
                            function() {
                                var modalData = {
                                    createDate: $(rowData[7])
                                        .text(),
                                    userCode: $(rowData[5])
                                        .text(),
                                    userName: '<?php echo $userName; ?>',
                                    leaveType: $(rowData[0])
                                        .text(),
                                    leaveReason: $(rowData[3])
                                        .text(),
                                    leaveStartDate: $(rowData[
                                            9])
                                        .text(),
                                    leaveEndDate: $(rowData[10])
                                        .text(),
                                    depart: $(rowData[2])
                                        .text(),
                                    checkFirm: '2', // ไม่ผ่าน
                                    empName: $(rowData[1])
                                        .text(),
                                    leaveStatus: $(rowData[12])
                                        .text()
                                };

                                $.ajax({
                                    url: 'a_ajax_upd_status.php',
                                    method: 'POST',
                                    data: modalData,
                                    success: function(
                                        response) {
                                        $('#leaveModal')
                                            .modal(
                                                'hide');
                                        location
                                            .reload(); // รีโหลดหน้าเมื่ออัพเดตเสร็จ
                                    },
                                    error: function(xhr,
                                        status,
                                        error) {
                                        console.error(
                                            error);
                                    }
                                });
                            });
                    });

                    $('.edit-btn').click(function() {
                        // Get data attributes from the button
                        var createDateTime = $(this).data(
                            'createdatetime');
                        var userCode = $(this).data('usercode');

                        // Send AJAX request to fetch data from the server
                        $.ajax({
                            url: 'a_ajax_get_leave.php', // Replace with your PHP file to fetch data
                            method: 'POST',
                            data: {
                                createDateTime: createDateTime,
                                userCode: userCode,
                            },
                            dataType: 'json', // Expect JSON response
                            success: function(response) {
                                if (response.status ===
                                    'success') {
                                    // Populate modal fields with the fetched data
                                    $('.editLeaveType').val(
                                        response
                                        .l_leave_id);
                                    $('#editCreateDateTime')
                                        .val(
                                            response
                                            .l_create_datetime
                                        );
                                    $('#editUserCode').val(
                                        response
                                        .l_usercode);
                                    $('#editLeaveReason')
                                        .val(response
                                            .l_leave_reason
                                        );
                                    $('#editName').val(
                                        response.l_name);

                                    var startDate = response
                                        .l_leave_start_date;
                                    var endDate = response
                                        .l_leave_end_date;

                                    var dateParts =
                                        startDate.split(
                                            '-'
                                        ); // แยกวันที่
                                    var dateParts2 = endDate
                                        .split(
                                            '-'
                                        ); // แยกวันที่

                                    var formattedDate =
                                        dateParts[2] +
                                        '-' + dateParts[1] +
                                        '-' +
                                        dateParts[
                                            0
                                        ]; // แปลงเป็น d-m-y
                                    var formattedDate2 =
                                        dateParts2[2] +
                                        '-' + dateParts2[
                                            1] + '-' +
                                        dateParts2[
                                            0
                                        ]; // แปลงเป็น d-m-y

                                    $('#editLeaveStartDate')
                                        .val(
                                            formattedDate);
                                    $('#editLeaveEndDate')
                                        .val(
                                            formattedDate2);

                                    // เวลาที่เริ่มต้น
                                    // 08:10
                                    if (response
                                        .l_leave_start_time ===
                                        "08:30:00" &&
                                        response
                                        .l_remark ===
                                        "08:10:00") {
                                        $('#editLeaveStartTime2')
                                            .val(
                                                response
                                                .l_remark);
                                    }
                                    // 08:15
                                    else if (response
                                        .l_leave_start_time ===
                                        "08:30:00" &&
                                        response
                                        .l_remark ===
                                        "08:15:00") {
                                        $('#editLeaveStartTime2')
                                            .val(
                                                response
                                                .l_remark);
                                    }
                                    // 08:45
                                    else if (response
                                        .l_leave_start_time ===
                                        "09:00:00" &&
                                        response
                                        .l_remark ===
                                        "08:45:00") {
                                        $('#editLeaveStartTime2')
                                            .val(
                                                response
                                                .l_remark);
                                    }
                                    // 09:10
                                    else if (response
                                        .l_leave_start_time ===
                                        "09:30:00" &&
                                        response
                                        .l_remark ===
                                        "09:10:00") {
                                        $('#editLeaveStartTime2')
                                            .val(
                                                response
                                                .l_remark);
                                    }
                                    // 09:15
                                    else if (response
                                        .l_leave_start_time ===
                                        "09:30:00" &&
                                        response
                                        .l_remark ===
                                        "09:15:00") {
                                        $('#editLeaveStartTime2')
                                            .val(
                                                response
                                                .l_remark);
                                    }
                                    // 09:45
                                    else if (response
                                        .l_leave_start_time ===
                                        "10:00:00" &&
                                        response
                                        .l_remark ===
                                        "09:45:00") {
                                        $('#editLeaveStartTime2')
                                            .val(
                                                response
                                                .l_remark);
                                    }
                                    // 10:10
                                    else if (response
                                        .l_leave_start_time ===
                                        "10:30:00" &&
                                        response
                                        .l_remark ===
                                        "10:10:00") {
                                        $('#editLeaveStartTime2')
                                            .val(
                                                response
                                                .l_remark);
                                    }
                                    // 10:15
                                    else if (response
                                        .l_leave_start_time ===
                                        "10:30:00" &&
                                        response
                                        .l_remark ===
                                        "10:15:00") {
                                        $('#editLeaveStartTime2')
                                            .val(
                                                response
                                                .l_remark);
                                    }
                                    // 10:45
                                    else if (response
                                        .l_leave_start_time ===
                                        "11:00:00" &&
                                        response
                                        .l_remark ===
                                        "10:45:00") {
                                        $('#editLeaveStartTime2')
                                            .val(
                                                response
                                                .l_remark);
                                    }
                                    // 11:45
                                    else if (response
                                        .l_leave_start_time ===
                                        "12:00:00" &&
                                        response
                                        .l_remark ===
                                        "11:45:00") {
                                        $('#editLeaveStartTime2')
                                            .val(
                                                response
                                                .l_remark);
                                    }
                                    // 12:45
                                    else if (response
                                        .l_leave_start_time ===
                                        "13:00:00") {
                                        $('#editLeaveStartTime2')
                                            .val(
                                                '12:45:00'
                                            ); // กำหนดค่าใหม่
                                    }
                                    // 13:10
                                    else if (response
                                        .l_leave_start_time ===
                                        "13:30:00" &&
                                        response
                                        .l_remark ===
                                        "13:10:00") {
                                        $('#editLeaveStartTime2')
                                            .val(
                                                response
                                                .l_remark);
                                    }
                                    // 13:15
                                    else if (response
                                        .l_leave_start_time ===
                                        "13:30:00" &&
                                        response
                                        .l_remark ===
                                        "13:15:00") {
                                        $('#editLeaveStartTime2')
                                            .val(
                                                response
                                                .l_remark);
                                    }
                                    // 13:40
                                    else if (response
                                        .l_leave_start_time ===
                                        "14:00:00" &&
                                        response
                                        .l_remark ===
                                        "13:40:00") {
                                        $('#editLeaveStartTime2')
                                            .val(
                                                response
                                                .l_remark);
                                    }
                                    // 13:45
                                    else if (response
                                        .l_leave_start_time ===
                                        "14:00:00" &&
                                        response
                                        .l_remark ===
                                        "13:45:00") {
                                        $('#editLeaveStartTime2')
                                            .val(
                                                response
                                                .l_remark);
                                    }
                                    // 14:10
                                    else if (response
                                        .l_leave_start_time ===
                                        "14:30:00" &&
                                        response
                                        .l_remark ===
                                        "14:10:00") {
                                        $('#editLeaveStartTime2')
                                            .val(
                                                response
                                                .l_remark);
                                    }
                                    // 14:15
                                    else if (response
                                        .l_leave_start_time ===
                                        "14:30:00" &&
                                        response
                                        .l_remark ===
                                        "14:15:00") {
                                        $('#editLeaveStartTime2')
                                            .val(
                                                response
                                                .l_remark);
                                    }
                                    // 14:40
                                    else if (response
                                        .l_leave_start_time ===
                                        "15:00:00" &&
                                        response
                                        .l_remark ===
                                        "14:40:00") {
                                        $('#editLeaveStartTime2')
                                            .val(
                                                response
                                                .l_remark);
                                    }
                                    // 14:45
                                    else if (response
                                        .l_leave_start_time ===
                                        "15:00:00" &&
                                        response
                                        .l_remark ===
                                        "14:45:00") {
                                        $('#editLeaveStartTime2')
                                            .val(
                                                response
                                                .l_remark);
                                    }
                                    // 15:10
                                    else if (response
                                        .l_leave_start_time ===
                                        "15:30:00" &&
                                        response
                                        .l_remark ===
                                        "15:10:00") {
                                        $('#editLeaveStartTime2')
                                            .val(
                                                response
                                                .l_remark);
                                    }
                                    // 15:15
                                    else if (response
                                        .l_leave_start_time ===
                                        "15:30:00" &&
                                        response
                                        .l_remark ===
                                        "15:15:00") {
                                        $('#editLeaveStartTime2')
                                            .val(
                                                response
                                                .l_remark);
                                    }
                                    // 15:40
                                    else if (response
                                        .l_leave_start_time ===
                                        "16:00:00" &&
                                        response
                                        .l_remark ===
                                        "15:40:00") {
                                        $('#editLeaveStartTime2')
                                            .val(
                                                response
                                                .l_remark);
                                    }
                                    // 15:45
                                    else if (response
                                        .l_leave_start_time ===
                                        "16:00:00" &&
                                        response
                                        .l_remark ===
                                        "15:45:00") {
                                        $('#editLeaveStartTime2')
                                            .val(
                                                response
                                                .l_remark);
                                    }
                                    // 16:10
                                    else if (response
                                        .l_leave_start_time ===
                                        "16:30:00" &&
                                        response
                                        .l_remark ===
                                        "16:10:00") {
                                        $('#editLeaveStartTime2')
                                            .val(
                                                response
                                                .l_remark);
                                    }
                                    // 16:15
                                    else if (response
                                        .l_leave_start_time ===
                                        "16:30:00" &&
                                        response
                                        .l_remark ===
                                        "16:15:00") {
                                        $('#editLeaveStartTime2')
                                            .val(
                                                response
                                                .l_remark);
                                    }
                                    // 16:40
                                    else if (response
                                        .l_leave_start_time ===
                                        "17:00:00") {
                                        $('#editLeaveStartTime2')
                                            .val(
                                                '16:40:00');
                                    } else {
                                        $('#editLeaveStartTime2')
                                            .val(
                                                response
                                                .l_leave_start_time
                                            );
                                    }

                                    // เวลาที่สิ้นสุด
                                    // 08:10
                                    if (response
                                        .l_leave_end_time ===
                                        "08:30:00" &&
                                        response
                                        .l_remark ===
                                        "08:10:00") {
                                        $('#editLeaveEndTime2')
                                            .val(
                                                response
                                                .l_remark);
                                    }
                                    // 08:15
                                    else if (response
                                        .l_leave_end_time ===
                                        "08:30:00" &&
                                        response
                                        .l_remark ===
                                        "08:15:00") {
                                        $('#editLeaveEndTime2')
                                            .val(
                                                response
                                                .l_remark);
                                    }
                                    // 08:45
                                    else if (response
                                        .l_leave_end_time ===
                                        "09:00:00" &&
                                        response
                                        .l_remark ===
                                        "08:45:00") {
                                        $('#editLeaveEndTime2')
                                            .val(
                                                response
                                                .l_remark);
                                    }
                                    // 09:10
                                    else if (response
                                        .l_leave_end_time ===
                                        "09:30:00" &&
                                        response
                                        .l_remark ===
                                        "09:10:00") {
                                        $('#editLeaveEndTime2')
                                            .val(
                                                response
                                                .l_remark);
                                    }
                                    // 09:15
                                    else if (response
                                        .l_leave_end_time ===
                                        "09:30:00" &&
                                        response
                                        .l_remark ===
                                        "09:15:00") {
                                        $('#editLeaveEndTime2')
                                            .val(
                                                response
                                                .l_remark);
                                    }
                                    // 09:45
                                    else if (response
                                        .l_leave_end_time ===
                                        "10:00:00" &&
                                        response
                                        .l_remark ===
                                        "09:45:00") {
                                        $('#editLeaveEndTime2')
                                            .val(
                                                response
                                                .l_remark);
                                    }
                                    // 10:10
                                    else if (response
                                        .l_leave_end_time ===
                                        "10:30:00" &&
                                        response
                                        .l_remark ===
                                        "10:10:00") {
                                        $('#editLeaveEndTime2')
                                            .val(
                                                response
                                                .l_remark);
                                    }
                                    // 10:15
                                    else if (response
                                        .l_leave_end_time ===
                                        "10:30:00" &&
                                        response
                                        .l_remark ===
                                        "10:15:00") {
                                        $('#editLeaveEndTime2')
                                            .val(
                                                response
                                                .l_remark);
                                    }
                                    // 10:45
                                    else if (response
                                        .l_leave_end_time ===
                                        "11:00:00" &&
                                        response
                                        .l_remark ===
                                        "10:45:00") {
                                        $('#editLeaveEndTime2')
                                            .val(
                                                response
                                                .l_remark);
                                    }
                                    // 11:45
                                    else if (response
                                        .l_leave_end_time ===
                                        "12:00:00" &&
                                        response
                                        .l_remark ===
                                        "11:45:00") {
                                        $('#editLeaveEndTime2')
                                            .val(
                                                response
                                                .l_remark);
                                    }
                                    // 12:45
                                    else if (response
                                        .l_leave_end_time ===
                                        "13:00:00"
                                    ) {
                                        $('#editLeaveEndTime2')
                                            .val(
                                                '12:45:00'
                                            ); // กำหนดค่าใหม่
                                    }
                                    // 13:10
                                    else if (response
                                        .l_leave_end_time ===
                                        "13:30:00" &&
                                        response
                                        .l_remark ===
                                        "13:10:00") {
                                        $('#editLeaveEndTime2')
                                            .val(
                                                response
                                                .l_remark);
                                    }
                                    // 13:15
                                    else if (response
                                        .l_leave_end_time ===
                                        "13:30:00" &&
                                        response
                                        .l_remark ===
                                        "13:15:00") {
                                        $('#editLeaveEndTime2')
                                            .val(
                                                response
                                                .l_remark);
                                    }
                                    // 13:40
                                    else if (response
                                        .l_leave_end_time ===
                                        "14:00:00" &&
                                        response
                                        .l_remark ===
                                        "13:40:00") {
                                        $('#editLeaveEndTime2')
                                            .val(
                                                response
                                                .l_remark);
                                    }
                                    // 13:45
                                    else if (response
                                        .l_leave_end_time ===
                                        "14:00:00" &&
                                        response
                                        .l_remark ===
                                        "13:45:00") {
                                        $('#editLeaveEndTime2')
                                            .val(
                                                response
                                                .l_remark);
                                    }
                                    // 14:10
                                    else if (response
                                        .l_leave_end_time ===
                                        "14:30:00" &&
                                        response
                                        .l_remark ===
                                        "14:10:00") {
                                        $('#editLeaveEndTime2')
                                            .val(
                                                response
                                                .l_remark);
                                    }
                                    // 14:15
                                    else if (response
                                        .l_leave_end_time ===
                                        "14:30:00" &&
                                        response
                                        .l_remark ===
                                        "14:15:00") {
                                        $('#editLeaveEndTime2')
                                            .val(
                                                response
                                                .l_remark);
                                    }
                                    // 14:40
                                    else if (response
                                        .l_leave_end_time ===
                                        "15:00:00" &&
                                        response
                                        .l_remark ===
                                        "14:40:00") {
                                        $('#editLeaveEndTime2')
                                            .val(
                                                response
                                                .l_remark);
                                    }
                                    // 14:45
                                    else if (response
                                        .l_leave_end_time ===
                                        "15:00:00" &&
                                        response
                                        .l_remark ===
                                        "14:45:00") {
                                        $('#editLeaveEndTime2')
                                            .val(
                                                response
                                                .l_remark);
                                    }
                                    // 15:10
                                    else if (response
                                        .l_leave_end_time ===
                                        "15:30:00" &&
                                        response
                                        .l_remark ===
                                        "15:10:00") {
                                        $('#editLeaveEndTime2')
                                            .val(
                                                response
                                                .l_remark);
                                    }
                                    // 15:15
                                    else if (response
                                        .l_leave_end_time ===
                                        "15:30:00" &&
                                        response
                                        .l_remark ===
                                        "15:15:00") {
                                        $('#editLeaveEndTime2')
                                            .val(
                                                response
                                                .l_remark);
                                    }
                                    // 15:40
                                    else if (response
                                        .l_leave_end_time ===
                                        "16:00:00" &&
                                        response
                                        .l_remark ===
                                        "15:40:00") {
                                        $('#editLeaveEndTime2')
                                            .val(
                                                response
                                                .l_remark);
                                    }
                                    // 15:45
                                    else if (response
                                        .l_leave_end_time ===
                                        "16:00:00" &&
                                        response
                                        .l_remark ===
                                        "15:45:00") {
                                        $('#editLeaveEndTime2')
                                            .val(
                                                response
                                                .l_remark);
                                    }
                                    // 16:10
                                    else if (response
                                        .l_leave_end_time ===
                                        "16:30:00" &&
                                        response
                                        .l_remark ===
                                        "16:10:00") {
                                        $('#editLeaveEndTime2')
                                            .val(
                                                response
                                                .l_remark);
                                    }
                                    // 16:15
                                    else if (response
                                        .l_leave_end_time ===
                                        "16:30:00" &&
                                        response
                                        .l_remark ===
                                        "16:15:00") {
                                        $('#editLeaveEndTime2')
                                            .val(
                                                response
                                                .l_remark);
                                    }
                                    // 16:40
                                    else if (response
                                        .l_leave_end_time ===
                                        "17:00:00"
                                    ) {
                                        $('#editLeaveEndTime2')
                                            .val(
                                                '16:40:00');
                                    } else {
                                        $('#editLeaveEndTime2')
                                            .val(
                                                response
                                                .l_leave_end_time
                                            );
                                    }

                                    // Show the modal
                                    $('#editModal').modal(
                                        'show');
                                } else {
                                    // Show error message from response
                                    alert(response
                                        .message ||
                                        'ไม่พบข้อมูล');
                                }
                            },
                            error: function() {
                                alert(
                                    'เกิดข้อผิดพลาดในการดึงข้อมูล'
                                );
                            },
                        });
                    });

                    $('#editForm').submit(function(e) {
                        e.preventDefault();

                        var editCreateDateTime = $(
                            '#editCreateDateTime').val();
                        var editUserCode = $('#editUserCode').val();
                        var editLeaveType = $('#editLeaveType').val();
                        var editLeaveReason = $('#editLeaveReason')
                            .val();
                        var editLeaveStartDate = $(
                            '#editLeaveStartDate').val();
                        var editLeaveEndDate = $('#editLeaveEndDate')
                            .val();
                        var editLeaveStartTime = $(
                            '#editLeaveStartTime').val();
                        var editLeaveEndTime = $('#editLeaveEndTime')
                            .val();

                        if (editLeaveStartDate > editLeaveEndDate) {
                            Swal.fire({
                                title: 'ไม่สามารถลาได้',
                                text: 'กรุณาเลือกวันที่เริ่มต้นลาใหม่',
                                icon: 'error',
                                confirmButtonText: 'ตกลง'
                            })
                        } else if (editLeaveStartTime >
                            editLeaveEndTime) {
                            Swal.fire({
                                title: 'ไม่สามารถลาได้',
                                text: 'กรุณาเลือกเวลาเริ่มต้นใหม่',
                                icon: 'error',
                                confirmButtonText: 'ตกลง'
                            })
                        } else {
                            $.ajax({
                                url: 'a_upd_leave.php',
                                method: 'POST',
                                data: {
                                    editCreateDateTime: editCreateDateTime,
                                    editUserCode: editUserCode,
                                    editLeaveType: editLeaveType,
                                    editLeaveReason: editLeaveReason,
                                    editLeaveStartDate: editLeaveStartDate,
                                    editLeaveEndDate: editLeaveEndDate,
                                    editLeaveStartTime: editLeaveStartTime,
                                    editLeaveEndTime: editLeaveEndTime
                                },
                                success: function(response) {
                                    Swal.fire({
                                        title: 'แก้ไขสำเร็จ',
                                        icon: 'success',
                                        confirmButtonText: 'ตกลง'
                                    }).then((
                                        result) => {
                                        if (result
                                            .isConfirmed
                                        ) {
                                            location
                                                .reload();
                                        }
                                    });

                                    $('#editModal').modal(
                                        'hide');
                                },
                                error: function() {
                                    alert(
                                        'เกิดข้อผิดพลาดในการบันทึกข้อมูล'
                                    );
                                }
                            });
                        }
                    });
                }
            });
        });

        $('.edit-btn').click(function() {
            // Get data attributes from the button
            var createDateTime = $(this).data('createdatetime');
            var userCode = $(this).data('usercode');

            // Send AJAX request to fetch data from the server
            $.ajax({
                url: 'a_ajax_get_leave.php', // Replace with your PHP file to fetch data
                method: 'POST',
                data: {
                    createDateTime: createDateTime,
                    userCode: userCode,
                },
                dataType: 'json', // Expect JSON response
                success: function(response) {
                    if (response.status === 'success') {
                        // Populate modal fields with the fetched data
                        $('.editLeaveType').val(response.l_leave_id);
                        $('#editCreateDateTime').val(response
                            .l_create_datetime);
                        $('#editUserCode').val(response.l_usercode);
                        $('#editLeaveReason').val(response.l_leave_reason);
                        $('#editName').val(response.l_name);

                        var startDate = response.l_leave_start_date;
                        var endDate = response.l_leave_end_date;

                        var dateParts = startDate.split('-'); // แยกวันที่
                        var dateParts2 = endDate.split('-'); // แยกวันที่

                        var formattedDate = dateParts[2] + '-' + dateParts[1] +
                            '-' +
                            dateParts[0]; // แปลงเป็น d-m-y
                        var formattedDate2 = dateParts2[2] + '-' + dateParts2[
                                1] + '-' +
                            dateParts2[0]; // แปลงเป็น d-m-y

                        $('#editLeaveStartDate').val(formattedDate);
                        $('#editLeaveEndDate').val(formattedDate2);

                        // เวลาที่เริ่มต้น
                        // 08:10
                        if (response.l_leave_start_time === "08:30:00" &&
                            response
                            .l_remark === "08:10:00") {
                            $('#editLeaveStartTime2').val(response.l_remark);
                        }
                        // 08:15
                        else if (response.l_leave_start_time === "08:30:00" &&
                            response
                            .l_remark === "08:15:00") {
                            $('#editLeaveStartTime2').val(response.l_remark);
                        }
                        // 08:45
                        else if (response.l_leave_start_time === "09:00:00" &&
                            response
                            .l_remark === "08:45:00") {
                            $('#editLeaveStartTime2').val(response.l_remark);
                        }
                        // 09:10
                        else if (response.l_leave_start_time === "09:30:00" &&
                            response
                            .l_remark === "09:10:00") {
                            $('#editLeaveStartTime2').val(response.l_remark);
                        }
                        // 09:15
                        else if (response.l_leave_start_time === "09:30:00" &&
                            response
                            .l_remark === "09:15:00") {
                            $('#editLeaveStartTime2').val(response.l_remark);
                        }
                        // 09:45
                        else if (response.l_leave_start_time === "10:00:00" &&
                            response
                            .l_remark === "09:45:00") {
                            $('#editLeaveStartTime2').val(response.l_remark);
                        }
                        // 10:10
                        else if (response.l_leave_start_time === "10:30:00" &&
                            response
                            .l_remark === "10:10:00") {
                            $('#editLeaveStartTime2').val(response.l_remark);
                        }
                        // 10:15
                        else if (response.l_leave_start_time === "10:30:00" &&
                            response
                            .l_remark === "10:15:00") {
                            $('#editLeaveStartTime2').val(response.l_remark);
                        }
                        // 10:45
                        else if (response.l_leave_start_time === "11:00:00" &&
                            response
                            .l_remark === "10:45:00") {
                            $('#editLeaveStartTime2').val(response.l_remark);
                        }
                        // 11:45
                        else if (response.l_leave_start_time === "12:00:00" &&
                            response
                            .l_remark === "11:45:00") {
                            $('#editLeaveStartTime2').val(response.l_remark);
                        }
                        // 12:45
                        else if (response.l_leave_start_time === "13:00:00") {
                            $('#editLeaveStartTime2').val(
                                '12:45:00'); // กำหนดค่าใหม่
                        }
                        // 13:10
                        else if (response.l_leave_start_time === "13:30:00" &&
                            response
                            .l_remark === "13:10:00") {
                            $('#editLeaveStartTime2').val(response.l_remark);
                        }
                        // 13:15
                        else if (response.l_leave_start_time === "13:30:00" &&
                            response
                            .l_remark === "13:15:00") {
                            $('#editLeaveStartTime2').val(response.l_remark);
                        }
                        // 13:40
                        else if (response.l_leave_start_time === "14:00:00" &&
                            response
                            .l_remark === "13:40:00") {
                            $('#editLeaveStartTime2').val(response.l_remark);
                        }
                        // 13:45
                        else if (response.l_leave_start_time === "14:00:00" &&
                            response
                            .l_remark === "13:45:00") {
                            $('#editLeaveStartTime2').val(response.l_remark);
                        }
                        // 14:10
                        else if (response.l_leave_start_time === "14:30:00" &&
                            response
                            .l_remark === "14:10:00") {
                            $('#editLeaveStartTime2').val(response.l_remark);
                        }
                        // 14:15
                        else if (response.l_leave_start_time === "14:30:00" &&
                            response
                            .l_remark === "14:15:00") {
                            $('#editLeaveStartTime2').val(response.l_remark);
                        }
                        // 14:40
                        else if (response.l_leave_start_time === "15:00:00" &&
                            response
                            .l_remark === "14:40:00") {
                            $('#editLeaveStartTime2').val(response.l_remark);
                        }
                        // 14:45
                        else if (response.l_leave_start_time === "15:00:00" &&
                            response
                            .l_remark === "14:45:00") {
                            $('#editLeaveStartTime2').val(response.l_remark);
                        }
                        // 15:10
                        else if (response.l_leave_start_time === "15:30:00" &&
                            response
                            .l_remark === "15:10:00") {
                            $('#editLeaveStartTime2').val(response.l_remark);
                        }
                        // 15:15
                        else if (response.l_leave_start_time === "15:30:00" &&
                            response
                            .l_remark === "15:15:00") {
                            $('#editLeaveStartTime2').val(response.l_remark);
                        }
                        // 15:40
                        else if (response.l_leave_start_time === "16:00:00" &&
                            response
                            .l_remark === "15:40:00") {
                            $('#editLeaveStartTime2').val(response.l_remark);
                        }
                        // 15:45
                        else if (response.l_leave_start_time === "16:00:00" &&
                            response
                            .l_remark === "15:45:00") {
                            $('#editLeaveStartTime2').val(response.l_remark);
                        }
                        // 16:10
                        else if (response.l_leave_start_time === "16:30:00" &&
                            response
                            .l_remark === "16:10:00") {
                            $('#editLeaveStartTime2').val(response.l_remark);
                        }
                        // 16:15
                        else if (response.l_leave_start_time === "16:30:00" &&
                            response
                            .l_remark === "16:15:00") {
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
                        // 08:10
                        if (response.l_leave_end_time === "08:30:00" && response
                            .l_remark === "08:10:00") {
                            $('#editLeaveEndTime2').val(response.l_remark);
                        }
                        // 08:15
                        else if (response.l_leave_end_time === "08:30:00" &&
                            response
                            .l_remark === "08:15:00") {
                            $('#editLeaveEndTime2').val(response.l_remark);
                        }
                        // 08:45
                        else if (response.l_leave_end_time === "09:00:00" &&
                            response
                            .l_remark === "08:45:00") {
                            $('#editLeaveEndTime2').val(response.l_remark);
                        }
                        // 09:10
                        else if (response.l_leave_end_time === "09:30:00" &&
                            response
                            .l_remark === "09:10:00") {
                            $('#editLeaveEndTime2').val(response.l_remark);
                        }
                        // 09:15
                        else if (response.l_leave_end_time === "09:30:00" &&
                            response
                            .l_remark === "09:15:00") {
                            $('#editLeaveEndTime2').val(response.l_remark);
                        }
                        // 09:45
                        else if (response.l_leave_end_time === "10:00:00" &&
                            response
                            .l_remark === "09:45:00") {
                            $('#editLeaveEndTime2').val(response.l_remark);
                        }
                        // 10:10
                        else if (response.l_leave_end_time === "10:30:00" &&
                            response
                            .l_remark === "10:10:00") {
                            $('#editLeaveEndTime2').val(response.l_remark);
                        }
                        // 10:15
                        else if (response.l_leave_end_time === "10:30:00" &&
                            response
                            .l_remark === "10:15:00") {
                            $('#editLeaveEndTime2').val(response.l_remark);
                        }
                        // 10:45
                        else if (response.l_leave_end_time === "11:00:00" &&
                            response
                            .l_remark === "10:45:00") {
                            $('#editLeaveEndTime2').val(response.l_remark);
                        }
                        // 11:45
                        else if (response.l_leave_end_time === "12:00:00" &&
                            response
                            .l_remark === "11:45:00") {
                            $('#editLeaveEndTime2').val(response.l_remark);
                        }
                        // 12:45
                        else if (response.l_leave_end_time === "13:00:00") {
                            $('#editLeaveEndTime2').val(
                                '12:45:00'); // กำหนดค่าใหม่
                        }
                        // 13:10
                        else if (response.l_leave_end_time === "13:30:00" &&
                            response
                            .l_remark === "13:10:00") {
                            $('#editLeaveEndTime2').val(response.l_remark);
                        }
                        // 13:15
                        else if (response.l_leave_end_time === "13:30:00" &&
                            response
                            .l_remark === "13:15:00") {
                            $('#editLeaveEndTime2').val(response.l_remark);
                        }
                        // 13:40
                        else if (response.l_leave_end_time === "14:00:00" &&
                            response
                            .l_remark === "13:40:00") {
                            $('#editLeaveEndTime2').val(response.l_remark);
                        }
                        // 13:45
                        else if (response.l_leave_end_time === "14:00:00" &&
                            response
                            .l_remark === "13:45:00") {
                            $('#editLeaveEndTime2').val(response.l_remark);
                        }
                        // 14:10
                        else if (response.l_leave_end_time === "14:30:00" &&
                            response
                            .l_remark === "14:10:00") {
                            $('#editLeaveEndTime2').val(response.l_remark);
                        }
                        // 14:15
                        else if (response.l_leave_end_time === "14:30:00" &&
                            response
                            .l_remark === "14:15:00") {
                            $('#editLeaveEndTime2').val(response.l_remark);
                        }
                        // 14:40
                        else if (response.l_leave_end_time === "15:00:00" &&
                            response
                            .l_remark === "14:40:00") {
                            $('#editLeaveEndTime2').val(response.l_remark);
                        }
                        // 14:45
                        else if (response.l_leave_end_time === "15:00:00" &&
                            response
                            .l_remark === "14:45:00") {
                            $('#editLeaveEndTime2').val(response.l_remark);
                        }
                        // 15:10
                        else if (response.l_leave_end_time === "15:30:00" &&
                            response
                            .l_remark === "15:10:00") {
                            $('#editLeaveEndTime2').val(response.l_remark);
                        }
                        // 15:15
                        else if (response.l_leave_end_time === "15:30:00" &&
                            response
                            .l_remark === "15:15:00") {
                            $('#editLeaveEndTime2').val(response.l_remark);
                        }
                        // 15:40
                        else if (response.l_leave_end_time === "16:00:00" &&
                            response
                            .l_remark === "15:40:00") {
                            $('#editLeaveEndTime2').val(response.l_remark);
                        }
                        // 15:45
                        else if (response.l_leave_end_time === "16:00:00" &&
                            response
                            .l_remark === "15:45:00") {
                            $('#editLeaveEndTime2').val(response.l_remark);
                        }
                        // 16:10
                        else if (response.l_leave_end_time === "16:30:00" &&
                            response
                            .l_remark === "16:10:00") {
                            $('#editLeaveEndTime2').val(response.l_remark);
                        }
                        // 16:15
                        else if (response.l_leave_end_time === "16:30:00" &&
                            response
                            .l_remark === "16:15:00") {
                            $('#editLeaveEndTime2').val(response.l_remark);
                        }
                        // 16:40
                        else if (response.l_leave_end_time === "17:00:00") {
                            $('#editLeaveEndTime2').val('16:40:00');
                        } else {
                            $('#editLeaveEndTime2').val(response
                                .l_leave_end_time);
                        }

                        // Show the modal
                        $('#editModal').modal('show');
                    } else {
                        // Show error message from response
                        alert(response.message || 'ไม่พบข้อมูล');
                    }
                },
                error: function() {
                    alert('เกิดข้อผิดพลาดในการดึงข้อมูล');
                },
            });
        });

        $('#editForm').submit(function(e) {
            e.preventDefault();

            var editCreateDateTime = $('#editCreateDateTime').val();
            var editUserCode = $('#editUserCode').val();
            var editLeaveType = $('#editLeaveType').val();
            var editLeaveReason = $('#editLeaveReason').val();
            var editLeaveStartDate = $('#editLeaveStartDate').val();
            var editLeaveEndDate = $('#editLeaveEndDate').val();
            var editLeaveStartTime = $('#editLeaveStartTime').val();
            var editLeaveEndTime = $('#editLeaveEndTime').val();

            if (editLeaveStartDate > editLeaveEndDate) {
                Swal.fire({
                    title: 'ไม่สามารถลาได้',
                    text: 'กรุณาเลือกวันที่เริ่มต้นลาใหม่',
                    icon: 'error',
                    confirmButtonText: 'ตกลง'
                })
            } else if (editLeaveStartTime > editLeaveEndTime) {
                Swal.fire({
                    title: 'ไม่สามารถลาได้',
                    text: 'กรุณาเลือกเวลาเริ่มต้นใหม่',
                    icon: 'error',
                    confirmButtonText: 'ตกลง'
                })
            } else {
                $.ajax({
                    url: 'a_upd_leave.php',
                    method: 'POST',
                    data: {
                        editCreateDateTime: editCreateDateTime,
                        editUserCode: editUserCode,
                        editLeaveType: editLeaveType,
                        editLeaveReason: editLeaveReason,
                        editLeaveStartDate: editLeaveStartDate,
                        editLeaveEndDate: editLeaveEndDate,
                        editLeaveStartTime: editLeaveStartTime,
                        editLeaveEndTime: editLeaveEndTime
                    },
                    success: function(response) {
                        Swal.fire({
                            title: 'แก้ไขสำเร็จ',
                            icon: 'success',
                            confirmButtonText: 'ตกลง'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                location.reload();
                            }
                        });

                        $('#editModal').modal('hide');
                    },
                    error: function() {
                        alert('เกิดข้อผิดพลาดในการบันทึกข้อมูล');
                    }
                });
            }
        });
    });

    function changePage(page, selectedMonth, searchCode) {
        // สร้าง URL ใหม่โดยส่งค่าทั้งหมด (page, selectedMonth, และ codeSearch)
        var newUrl = "?page=" + page + "&month=" + encodeURIComponent(selectedMonth) +
            "&codeSearch=" + encodeURIComponent(searchCode);
        window.location.href = newUrl; // รีเฟรชหน้าโดยการโหลด URL ใหม่
    }
    document.getElementById('page-input').addEventListener('input', function() {
        const page = this.value;
        const month = '<?php echo urlencode($selectedMonth); ?>';
        if (page >= 1 && page <= <?php echo $totalPages; ?>) {
            window.location.href = `?page=${page}&month=${month}`;
        }
    });
    </script>
    <script src="../js/popper.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/bootstrap.bundle.js"></script>
    <script src="../js/bootstrap.bundle.min.js"></script>
</body>

</html>