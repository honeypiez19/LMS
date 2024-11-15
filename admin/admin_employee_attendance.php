<?php
session_start();
date_default_timezone_set('Asia/Bangkok');

include '../connect.php';
if (!isset($_SESSION['s_usercode'])) {
    header('Location: ../login.php');
    exit();
}

$userCode = $_SESSION['s_usercode'];
// echo $user
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>บันทึกเวลามาสาย</title>

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
    <?php include 'admin_navbar.php'?>
    <nav class="navbar bg-body-tertiary">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-auto">
                    <i class="fa-solid fa-user-clock fa-2xl"></i>
                </div>
                <div class="col-auto">
                    <h3>บันทึกเวลามาสาย</h3>
                </div>
            </div>
        </div>
    </nav>

    <div class="mt-5 container-fluid">
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#tab1">บันทึกเวลามาสาย</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab5">บันทึกหยุดงาน</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab4">รายการมาสายและขาดงานของพนักงาน</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab2">ประวัติมาสายและขาดงานของพนักงาน</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab3" hidden>ประวัติพนักงานที่ขาดงาน</a>
            </li>
        </ul>
    </div>

    <div class="mt-5 container">
        <div class="tab-content">
            <!-- มาสาย -->
            <div class="tab-pane fade show active" id="tab1">
                <form id="leaveForm">
                    <div class="row">
                        <div class="col-6">
                            <div class="mt-3">
                                <label for="employeeCode" class="form-label">รหัสพนักงาน</label>
                                <input type="text" class="form-control" id="codeSearch" name="userCode" list="codeList"
                                    required>
                                <datalist id="codeList">
                                    <?php
$sql = "SELECT * FROM employees WHERE e_status <> 1";
$result = $conn->query($sql);
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    echo '<option value="' . $row['e_usercode'] . '" data-name="' . $row['e_name'] . '" data-username="' . $row['e_username'] . '" data-depart="' .
        $row['e_department'] . '" data-level="' . $row['e_level'] . '" data-telPhone="' . $row['e_phone'] . '" data-sub-department="' . $row['e_sub_department'] . '"
    data-sub-department2="' . $row['e_sub_department2'] . '" data-sub-department3="' . $row['e_sub_department3'] . '" data-sub-department4="' .
        $row['e_sub_department4'] . '" data-sub-department5="' . $row['e_sub_department5'] . '" data-workplace="' . $row['e_workplace'] . '" >';

}
?>
                                </datalist>
                                <input type="text" class="form-control" id="userName" name="userName" hidden>
                                <input type="text" class="form-control" id="department" name="department" hidden>
                                <input type="text" class="form-control" id="level" name="level" hidden>
                                <input type="text" class="form-control" id="telPhone" name="telPhone" hidden>
                                <input type="text" class="form-control" id="reason" name="reason" value="มาสาย" hidden>
                                <input type="text" class="form-control" id="workplace" name="workplace" hidden>
                                <input type="text" class="form-control" id="subDepart" name="subDepart" hidden>
                                <!-- Correct subDepart -->
                                <input type="text" class="form-control" id="subDepart2" name="subDepart2" hidden>
                                <!-- Correct subDepart2 -->
                                <input type="text" class="form-control" id="subDepart3" name="subDepart3" hidden>
                                <!-- Correct subDepart3 -->
                                <input type="text" class="form-control" id="subDepart4" name="subDepart4" hidden>
                                <!-- Correct subDepart4 -->
                                <input type="text" class="form-control" id="subDepart5" name="subDepart5" hidden>
                                <!-- Correct subDepart5 -->

                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mt-3">
                                <label for="employeeName" class="form-label">ชื่อพนักงาน</label>
                                <input type="text" class="form-control" id="name" name="name" disabled>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 row">
                        <div class="col-6">
                            <label for="startDate" class="form-label">วันที่เริ่มต้น</label>
                            <input type="text" class="form-control" id="startDate" placeholder="YYYY-MM-DD">
                        </div>
                        <div class="col-6">
                            <label for="endDate" class="form-label">วันที่สิ้นสุด</label>
                            <input type="text" class="form-control" id="endDate" placeholder="YYYY-MM-DD">
                        </div>
                    </div>
                    <div class="mt-3 row">
                        <div class="col-6">
                            <label for="" class="form-label">เวลาเริ่มต้น</label>
                            <input type="text" id="leaveType" class="form-control" hidden value="7">
                            <select class="form-select" id="startTime" name="startTime" required>
                                <option value="08:01" selected>08:01</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label for="" class="form-label">เวลาสิ้นสุด</label>
                            <input type="text" id="leaveType" class="form-control" hidden value="7">
                            <select class="form-select" id="endTime" name="endTime" required>
                                <?php
for ($i = 2; $i <= 30; $i++) {
    $time = sprintf('08:%02d', $i);
    echo '<option value="' . $time . '">' . $time . '</option>';
}
?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="mt-3 btn btn-success button-shadow">บันทึก</button>
                        </div>
                    </div>
                    <!-- <button type="submit" class="mt-3 btn btn-primary">บันทึก</button> -->
                </form>
            </div>
            <!-- //////////////////////////////////////////////////////////////////////////////// -->
            <!-- บันทึกหยุดงาน -->
            <div class="tab-pane fade show" id="tab5">
                <form id="stopWorkForm">
                    <div class="row">
                        <div class="col-6">
                            <div class="mt-3">
                                <label for="employeeCode" class="form-label">รหัสพนักงาน</label>
                                <input type="text" class="form-control" id="userCodeS" name="userCodeS" list="codeListS"
                                    required>
                                <datalist id="codeListS">
                                    <?php
$sql = "SELECT * FROM employees WHERE  e_status <> 1";
$result = $conn->query($sql);
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    echo '<option value="' . $row['e_usercode'] . '" data-name="' . $row['e_name'] . '" data-username="' . $row['e_username'] . '" data-depart="' .
        $row['e_department'] . '" data-level="' . $row['e_level'] . '" data-telPhone="' . $row['e_phone'] . '" data-sub-department="' . $row['e_sub_department'] . '"
    data-sub-department2="' . $row['e_sub_department2'] . '" data-sub-department3="' . $row['e_sub_department3'] . '" data-sub-department4="' .
        $row['e_sub_department4'] . '" data-sub-department5="' . $row['e_sub_department5'] . '" data-workplace="' . $row['e_workplace'] . '" >';

}
?>
                                </datalist>
                                <input type="text" class="form-control" id="userNameS" name="userNameS" hidden>
                                <input type="text" class="form-control" id="departmentS" name="departmentS" hidden>
                                <input type="text" class="form-control" id="levelS" name="levelS" hidden>
                                <input type="text" class="form-control" id="telPhoneS" name="telPhoneS" hidden>
                                <input type="text" class="form-control" id="reasonS" name="reasonS" value="ขาดงาน"
                                    hidden>
                                <input type="text" class="form-control" id="workplaceS" name="workplaceS" hidden>
                                <input type="text" class="form-control" id="subDepartS" name="subDepartS" hidden>
                                <!-- Correct subDepart -->
                                <input type="text" class="form-control" id="subDepartS2" name="subDepartS2" hidden>
                                <!-- Correct subDepart2 -->
                                <input type="text" class="form-control" id="subDepartS3" name="subDepartS3" hidden>
                                <!-- Correct subDepart3 -->
                                <input type="text" class="form-control" id="subDepartS4" name="subDepartS4" hidden>
                                <!-- Correct subDepart4 -->
                                <input type="text" class="form-control" id="subDepartS5" name="subDepartS5" hidden>
                                <!-- Correct subDepart5 -->
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mt-3">
                                <label for="employeeName" class="form-label">ชื่อพนักงาน</label>
                                <input type="text" class="form-control" id="nameS" name="nameS" disabled>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 row">
                        <div class="col-6">
                            <label for="startDate" class="form-label">วันที่เริ่มต้น</label>
                            <input type="text" class="form-control" id="startDateS" placeholder="YYYY-MM-DD">
                        </div>
                        <div class="col-6">
                            <label for="endDate" class="form-label">วันที่สิ้นสุด</label>
                            <input type="text" class="form-control" id="endDateS" placeholder="YYYY-MM-DD">
                        </div>
                    </div>
                    <div class="mt-3 row">
                        <div class="col-6">
                            <label for="" class="form-label">เวลาเริ่มต้น</label>
                            <input type="text" id="leaveType" class="form-control" hidden value="7">
                            <select class="form-select" id="startTimeS" name="startTimeS" required>
                                <option value="08:00" selected>08:00</option>
                                <option value="08:30">08:30</option>
                                <option value="09:00">09:00</option>
                                <option value="09:30">09:30</option>
                                <option value="10:00">10:00</option>
                                <option value="10:30">10:30</option>
                                <option value="11:00">11:00</option>
                                <!-- <option value="11:30">11:30</option> -->
                                <option value="12:00">11:45</option>
                                <option value="13:00">12:45</option>
                                <!-- <option value="13:00">13:00</option> -->
                                <option value="13:30">13:30</option>
                                <option value="14:00">14:00</option>
                                <option value="14:30">14:30</option>
                                <option value="15:00">15:00</option>
                                <option value="15:30">15:30</option>
                                <option value="16:00">16:00</option>
                                <!-- <option value="16:30">16:30</option> -->
                                <option value="17:00">16:40</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label for="" class="form-label">เวลาสิ้นสุด</label>
                            <input type="text" id="leaveType" class="form-control" hidden value="7">
                            <select class="form-select" id="endTimeS" name="endTimeS" required>
                                <option value="08:00">08:00</option>
                                <option value="08:30">08:30</option>
                                <option value="09:00">09:00</option>
                                <option value="09:30">09:30</option>
                                <option value="10:00">10:00</option>
                                <option value="10:30">10:30</option>
                                <option value="11:00">11:00</option>
                                <!-- <option value="11:30">11:30</option> -->
                                <option value="12:00">11:45</option>
                                <option value="13:00">12:45</option>
                                <!-- <option value="13:00">13:00</option> -->
                                <option value="13:30">13:30</option>
                                <option value="14:00">14:00</option>
                                <option value="14:30">14:30</option>
                                <option value="15:00">15:00</option>
                                <option value="15:30">15:30</option>
                                <option value="16:00">16:00</option>
                                <!-- <option value="16:30">16:30</option> -->
                                <option value="17:00" selected>16:40</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="mt-3 btn btn-success button-shadow">บันทึก</button>
                        </div>
                    </div>
                    <!-- <button type="submit" class="mt-3 btn btn-primary">บันทึก</button> -->
                </form>
            </div>
            <!-- /////////////////////////////////////////////////////////////////////////////////// -->
            <!-- รายการมาสาย -->
            <div class="tab-pane fade" id="tab4">
                <form class="row" method="post">
                    <label for="" class="mt-2 col-auto">เลือกปี</label>
                    <div class="col-auto">
                        <?php
$currentYear = date('Y'); // ปีปัจจุบัน+

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
                <div class="mt-3 row">
                    <div class="col-4">
                        <label for="userCodeLabel" class="form-label">รหัสพนักงาน</label>
                        <input type="text" class="form-control" id="codeSearch2">
                    </div>
                </div>
                <?php
$itemsPerPage = 10;

// คำนวณหน้าปัจจุบัน
if (!isset($_GET['page'])) {
    $currentPage = 1;
} else {
    $currentPage = $_GET['page'];
}

// คำสั่ง SQL เพื่อดึงข้อมูลมาสายและขาดงาน
// $sql = "SELECT * FROM leave_list WHERE l_leave_id = 7 ORDER BY l_create_datetime DESC";
$sql = "SELECT * FROM leave_list WHERE l_leave_id IN (6,7)
AND Month(l_leave_end_date) = '$selectedMonth'
AND Year(l_leave_end_date) = $selectedYear
ORDER BY l_leave_end_date DESC";

$result = $conn->query($sql);
$totalRows = $result->rowCount();

// คำนวณหน้าทั้งหมด
$totalPages = ceil($totalRows / $itemsPerPage);

// คำนวณ offset สำหรับ pagination
$offset = ($currentPage - 1) * $itemsPerPage;

// เพิ่ม LIMIT และ OFFSET ในคำสั่ง SQL
$sql .= " LIMIT $itemsPerPage OFFSET $offset";

// ประมวลผลคำสั่ง SQL
$stmt = $conn->prepare($sql);
$stmt->execute();

$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($result) > 0) {
    echo '<table class="mt-3 table">';
    echo '<thead>';
    echo '<tr class="text-center align-middle">
        <th>ลำดับ</th>
        <th>รหัสพนักงาน</th>
        <th>ชื่อพนักงาน</th>
        <th>ประเภท</th>
        <th>วันที่มาสาย</th>
        <th>สถานะรายการ</th>
        <th>สถานะอนุมัติ_1</th>
        <th>สถานะอนุมัติ_2</th>
        <th>สถานะ (เฉพาะ HR)</th>
        <th>หมายเหตุ</th>
        <th style="width: 200px;"></th>
        </tr>';
    echo '</thead>';
    echo '<tbody>';
    foreach ($result as $index => $row) {
        $rowNumber = $totalRows - ($offset + $index);
        echo '<tr class="text-center align-middle">';

        // 0
        echo '<td hidden>' . $row['l_leave_start_date'] . '</td>';
        // 1
        echo '<td hidden>' . $row['l_leave_start_time'] . '</td>';
        // 2
        echo '<td hidden>' . $row['l_leave_end_time'] . '</td>';
        // 3
        echo '<td hidden>' . $row['l_department'] . '</td>';

        // 4
        echo '<td>' . $rowNumber . '</td>';

        // 5
        echo '<td>' . $row['l_usercode'] . '</td>';

        // 6
        echo '<td>' . $row['l_name'] . '</td>';

        // 7
        echo '<td>';
        if ($row['l_leave_id'] == 7) {
            echo 'มาสาย';
        } elseif ($row['l_leave_id'] == 6) {
            echo 'ขาดงาน';
        } else {
            echo $row['l_leave_id'];
        }
        echo '</td>';
        // 8
        echo '<td>' . $row['l_leave_start_date'] . '<br>' . $row['l_leave_start_time'] . ' ถึง ' . $row['l_leave_end_time'] . '</td>';

        // 9
        echo '<td>';
        if ($row['l_leave_status'] == 0) {
            echo '<span class="text-success">ปกติ</span>';
        } else {
            echo '<span class="text-danger">ยกเลิก</span>';
        }
        echo '</td>';

        // 10
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
            echo 'ไม่พบสถานะ';
        }
        echo '</td>';

        // 11
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
            echo 'ไม่พบสถานะ';
        }
        echo '</td>';

        // 11
        echo '<td >';
        if ($row['l_hr_status'] == 0) {
            echo '<div class="text-warning"><b>รอตรวจสอบ</b></div>';
        } elseif ($row['l_hr_status'] == 1) {
            echo '<div class="text-success"><b>ผ่าน</b></div>';
        } elseif ($row['l_hr_status'] == 2) {
            echo '<div class="text-danger"><b>ไม่ผ่าน</b></div>';
        } else {
            echo 'ไม่พบสถานะ';
        }
        echo '</td>';

        // 12
        echo '<td>' . $row['l_remark'] . '</td>';

        // 13
        echo '<td hidden data-datetime="' . $row['l_create_datetime'] . '">' . $row['l_create_datetime'] . '</td>';

        // 14
        echo '<td>';
        if ($row['l_leave_status'] == 0) {
            echo '<button type="button" class="btn btn-primary button-shadow chkLatebtn" data-usercode="' . $row['l_usercode'] . '" data-datetime="' . $row['l_create_datetime'] . '">ตรวจสอบ</button> ';
            echo '<button type="button" class="btn btn-danger button-shadow cancelLeaveBtn" data-usercode="' . $row['l_usercode'] . '" data-datetime="' . $row['l_create_datetime'] . '">ยกเลิก</button>';
        } else {
            // echo '<button type="button" class="btn btn-success button-shadow chkLatebtn" data-usercode="' . $row['l_usercode'] . '" data-datetime="' . $row['l_create_datetime'] . '" disabled>อนุมัติ</button> ';
            echo '<button type="button" class="btn btn-primary button-shadow chkLatebtn" data-usercode="' . $row['l_usercode'] . '" data-datetime="' . $row['l_create_datetime'] . '" >ตรวจสอบ</button> ';

            // echo '<button type="button" class="btn btn-danger button-shadow cancelLeaveBtn" data-usercode="' . $row['l_usercode'] . '" data-datetime="' . $row['l_create_datetime'] . '" disabled>ยกเลิก</button>';
            echo '<button type="button" class="btn btn-danger button-shadow cancelLeaveBtn" data-usercode="' . $row['l_usercode'] . '" data-datetime="' . $row['l_create_datetime'] . '" >ยกเลิก</button>';
        }
        echo '</td>';

        echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';

    // แสดง pagination
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
} else {
    echo '<span style="text-align: left; color:red;">ไม่พบข้อมูลการมาสายและขาดงาน</span>';
}
?>
            </div>
            <!-- //////////////////////////////////////////////////////////////////////////////// -->
            <!-- ประวัติพนักงานมาสาย -->
            <div class="tab-pane fade" id="tab2">
                <form class="row" method="post">
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
                <div class="mt-3 row">
                    <div class="col-4">
                        <label for="userCodeLabel" class="form-label">รหัสพนักงาน</label>
                        <input type="text" class="form-control" id="codeSearch3">
                    </div>
                </div>
                <?php
$itemsPerPage = 10;

// คำนวณหน้าปัจจุบัน
if (!isset($_GET['page'])) {
    $currentPage = 1;
} else {
    $currentPage = $_GET['page'];
}
// คำสั่ง SQL เพื่อดึงข้อมูลมาสายและขาดงาน
// $sql = "SELECT * FROM leave_list WHERE l_leave_id = 7 ORDER BY l_create_datetime DESC";
// $sql = "SELECT * FROM leave_list WHERE l_leave_id = 7 
// AND Month(l_create_datetime) = '$selectedMonth' 
// AND Year(l_create_datetime) = $selectedYear 
// ORDER BY l_create_datetime DESC";
$sql = "SELECT * FROM leave_list WHERE l_leave_id IN (6,7)
AND Month(l_leave_end_date) = '$selectedMonth'
AND Year(l_leave_end_date) = $selectedYear
ORDER BY l_leave_end_date DESC";

$result = $conn->query($sql);
$totalRows = $result->rowCount();

// คำนวณหน้าทั้งหมด
$totalPages = ceil($totalRows / $itemsPerPage);

// คำนวณ offset สำหรับ pagination
$offset = ($currentPage - 1) * $itemsPerPage;

// เพิ่ม LIMIT และ OFFSET ในคำสั่ง SQL
$sql .= " LIMIT $itemsPerPage OFFSET $offset";

// ประมวลผลคำสั่ง SQL
$stmt = $conn->prepare($sql);
$stmt->execute();

$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($result) > 0) {
    echo '<table class="mt-3 table">';
    echo '<thead>';
    echo '<tr class="text-center align-middle">
        <th>ลำดับ</th>
        <th>รหัสพนักงาน</th>
        <th>ชื่อพนักงาน</th>
        <th>ประเภท</th>
        <th>วันที่มาสาย</th>
        <th>สถานะรายการ</th>
        <th>สถานะอนุมัติ_1</th>
        <th>สถานะอนุมัติ_2</th>
        <th>สถานะ (เฉพาะ HR)</th>
        <th>หมายเหตุ</th>
    </tr>';
    echo '</thead>';
    echo '<tbody>';
    foreach ($result as $index => $row) {
        $rowNumber = $totalRows - ($offset + $index);
        echo '<tr class="text-center align-middle">';

        // 0
        echo '<td hidden>' . $row['l_leave_start_date'] . '</td>';
        // 1
        echo '<td hidden>' . $row['l_leave_start_time'] . '</td>';
        // 2
        echo '<td hidden>' . $row['l_leave_end_time'] . '</td>';
        // 3
        echo '<td hidden>' . $row['l_department'] . '</td>';

        // 4
        echo '<td>' . $rowNumber . '</td>';

        // 5
        echo '<td>' . $row['l_usercode'] . '</td>';

        // 6
        echo '<td>' . $row['l_name'] . '</td>';

        // 7
        echo '<td>';
        if ($row['l_leave_id'] == 7) {
            echo 'มาสาย';
        } elseif ($row['l_leave_id'] == 6) {
            echo 'ขาดงาน';
        } else {
            echo $row['l_leave_id'];
        }
        echo '</td>';
        // 8
        echo '<td>' . $row['l_leave_start_date'] . '<br>' . $row['l_leave_start_time'] . ' ถึง ' . $row['l_leave_end_time'] . '</td>';

        // 9
        echo '<td>';
        if ($row['l_leave_status'] == 0) {
            echo '<span class="text-success">ปกติ</span>';
        } else {
            echo '<span class="text-danger">ยกเลิก</span>';
        }
        echo '</td>';

        // 10
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
            echo 'ไม่พบสถานะ';
        }
        echo '</td>';

        // 11
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
            echo 'ไม่พบสถานะ';
        }
        echo '</td>';

        // 11
        echo '<td >';
        if ($row['l_hr_status'] == 0) {
            echo '<div class="text-warning"><b>รอตรวจสอบ</b></div>';
        } elseif ($row['l_hr_status'] == 1) {
            echo '<div class="text-success"><b>ผ่าน</b></div>';
        } elseif ($row['l_hr_status'] == 2) {
            echo '<div class="text-danger"><b>ไม่ผ่าน</b></div>';
        } else {
            echo 'ไม่พบสถานะ';
        }
        echo '</td>';

        // 12
        echo '<td>' . $row['l_remark'] . '</td>';
        echo '</tr>';
        // $rowNumber--;

    }
    echo '</tbody>';
    echo '</table>';

    // แสดง pagination
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
} else {
    echo '<span style="text-align: left; color:red;">ไม่พบข้อมูลการมาสายและขาดงาน</span>';
}

?>
            </div>
            <!-- //////////////////////////////////////////////////////////////////////////////// -->
            <!-- ประวัติขาดงาน -->
            <div class="tab-pane fade" id="tab3">
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
                <?php
$itemsPerPage = 10;

// คำนวณหน้าปัจจุบัน
if (!isset($_GET['page'])) {
    $currentPage = 1;
} else {
    $currentPage = $_GET['page'];
}
// คำสั่ง SQL เพื่อดึงข้อมูลขาดงานและมาสายที่ไม่ถูกยกเลิก
$sql = "SELECT * FROM leave_list WHERE (l_leave_id = 6 OR l_leave_id = 7) AND Month(l_create_datetime) = :selectedMonth AND Year(l_create_datetime) = :selectedYear AND l_leave_status <> 1 ORDER BY l_create_datetime DESC";

$stmt = $conn->prepare($sql);
$stmt->execute(['selectedMonth' => $selectedMonth, 'selectedYear' => $selectedYear]);

// ดึงข้อมูลทั้งหมด
$allResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

// คำนวณการมาสายและการขาดงาน
$lateCounts = [];
$absences = [];
foreach ($allResults as $row) {
    if ($row['l_leave_id'] == 7) { // รหัสมาสาย
        if (!isset($lateCounts[$row['l_usercode']])) {
            $lateCounts[$row['l_usercode']] = 0;
        }
        $lateCounts[$row['l_usercode']]++;
    } elseif ($row['l_leave_id'] == 6) { // รหัสขาดงาน
        $absences[] = $row;
    }
}

// ปรับข้อมูลการขาดงานตามการมาสาย
$processedUsercodes = [];

// ปรับข้อมูลการขาดงานตามการมาสาย
$filteredAbsences = [];
foreach ($absences as $absence) {
    $usercode = $absence['l_usercode'];
    // เช็คว่ารหัสพนักงานนี้ได้ทำการประมวลผลแล้วหรือยัง
    if (!in_array($usercode, $processedUsercodes) && isset($lateCounts[$usercode]) && $lateCounts[$usercode] >= 3) {
        $filteredAbsences[] = $absence;
        $processedUsercodes[] = $usercode; // เพิ่มรหัสพนักงานเข้าไปในรายการที่ประมวลผลแล้ว
    }
}

// นับจำนวนรายการทั้งหมด
$totalRows = count($filteredAbsences);

// คำนวณหน้าทั้งหมด
$totalPages = ceil($totalRows / $itemsPerPage);

// คำนวณ offset สำหรับ pagination
$offset = ($currentPage - 1) * $itemsPerPage;

// ดึงข้อมูลหน้าปัจจุบัน
$currentResults = array_slice($filteredAbsences, $offset, $itemsPerPage);

// แสดงผลตาราง
if (count($currentResults) > 0) {
    echo '<table class="table">';
    echo '<thead>';
    echo '<tr class="text-center align-middle">
    <th>ลำดับ</th>
    <th>รหัสพนักงาน</th>
    <th>ชื่อพนักงาน</th>
    <th>ประเภท</th>
    <th>วันที่มาสาย</th>
    <th>สถานะรายการ</th>
    <th>สถานะอนุมัติ_1</th>
    <th>สถานะอนุมัติ_2</th>
    <th>สถานะ (เฉพาะ HR)</th>
    <th>หมายเหตุ</th>
    <th></th>
    </tr>';
    echo '</thead>';
    echo '<tbody>';
    foreach ($currentResults as $index => $row) {
        $rowNumber = $totalRows - ($offset + $index);
        echo '<tr class="text-center align-middle">';

        // 0
        echo '<td>' . $rowNumber . '</td>';

        // 1
        echo '<td>' . $row['l_usercode'] . '</td>';

        // 2
        echo '<td>' . $row['l_name'] . '</td>';

        // 3
        echo '<td>' . ($row['l_leave_id'] == 6 ? 'ขาดงาน' : $row['l_leave_id']) . '</td>';

        // 4
        echo '<td>';
        // รอหัวหน้าอนุมัติ
        if ($row['l_approve_status'] == 0) {
            echo '<div class="text-warning"><b>รอหัวหน้าอนุมัติ</b></div>';
        }
        // รอผจกอนุมัติ
        elseif ($row['l_approve_status'] == 1) {
            echo '<div class="text-success"><b>รอผู้จัดการอนุมัติ</b></div>';
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
            echo '<div class="text-danger"><b>ผู้จัดการอนุมัติ</b></div>';
        }
        //  ผจก ไม่อนุมัติ
        elseif ($row['l_approve_status'] == 5) {
            echo '<div class="text-danger"><b>ผู้จัดการไม่อนุมัติ</b></div>';
        }
        // ไม่มีสถานะ
        else {
            echo 'ไม่พบสถานะ';
        }
        echo '</td>';

        // 5
        echo '<td>' . $row['l_remark'] . '</td>';

        // 6
        echo '<td><button type="button" class="btn btn-primary btn-open-modal" data-toggle="modal" data-target="#employeeModal" data-usercode="' . $row['l_usercode'] . '"><i class="fa-solid fa-magnifying-glass"></i></button></td>';

        echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';

    // แสดง pagination
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
} else {
    echo '<span style="text-align: left; color:red;">ไม่พบข้อมูลขาดงาน</span>';
}
?>
            </div>
        </div>
        <div class="modal fade" id="employeeModal" tabindex="-1" role="dialog" aria-labelledby="employeeModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="employeeModalLabel">รายละเอียด</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

                    </div>
                    <div class="modal-body" id="employeeModalBody">
                        <!-- ใส่โค้ด HTML ที่ต้องการแสดงข้อมูลพนักงานที่นี่ -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
    $('.nav-tabs a').on('shown.bs.tab', function(e) {
        localStorage.setItem('activeTab', $(e.target).attr('href'));
    });

    $(document).ready(function() {
        var activeTab = localStorage.getItem('activeTab');
        if (activeTab) {
            $('.nav-tabs a[href="' + activeTab + '"]').tab('show');
        }

        $('#leaveForm').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const name = $('#name').val();
            const userCode = $('#codeSearch').val();
            const userName = $('#userName').val();
            const department = $('#department').val();
            const level = $('#level').val();
            const startTime = $('#startTime').val();
            const endTime = $('#endTime').val();
            const telPhone = $('#telPhone').val();
            const reason = $('#reason').val();
            const startDate = $('#startDate').val();
            const endDate = $('#endDate').val();
            const workplace = $('#workplace').val();
            const subDepart = $('#subDepart').val();
            // const subDeart2 = $('#subDeart2').val();
            // const subDeart3 = $('#subDeart3').val();
            // const subDeart4 = $('#subDeart4').val();
            // const subDeart5 = $('#subDeart5').val();

            var addName = '<?php echo $userName; ?>';

            alert(subDepart)
            formData.append('userCode', userCode);
            formData.append('userName', userName);
            formData.append('name', name);
            formData.append('department', department);
            formData.append('level', level);
            formData.append('startTime', startTime);
            formData.append('endTime', endTime);
            formData.append('telPhone', telPhone);
            formData.append('addName', addName);
            formData.append('reason', reason);
            formData.append('startDate', startDate);
            formData.append('endDate', endDate);
            formData.append('workplace', workplace);
            formData.append('subDepart', subDepart);
            // formData.append('subDepart2', subDepart2);
            // formData.append('subDepart3', subDepart3);
            // formData.append('subDepart4', subDepart4);
            // formData.append('subDepart5', subDepart5);


            $.ajax({
                url: 'a_ajax_add_late_time.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    // alert(response);
                    Swal.fire({
                        title: "บันทึกข้อมูลการมาสายสำเร็จ",
                        text: "บันทึกข้อมูลการมาสายของ " + name + " สำเร็จ",
                        icon: "success",
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            location.reload();
                        }
                    });
                },
                error: function(xhr, status, error) {
                    console.error('เกิดข้อผิดพลาด: ', error);
                }
            });
        });

        // Form หยุดงาน
        $('#stopWorkForm').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const name = $('#nameS').val();
            const userCode = $('#userCodeS').val();
            const userName = $('#userNameS').val();
            const department = $('#departmentS').val();
            const level = $('#levelS').val();
            const startTime = $('#startTimeS').val();
            const endTime = $('#endTimeS').val();
            const telPhone = $('#telPhoneS').val();
            const reason = $('#reasonS').val();
            const startDate = $('#startDateS').val();
            const endDate = $('#endDateS').val();
            const workplace = $('#workplaceS').val();
            const subDepart = $('#subDepartS').val();
            // const subDeart2 = $('#subDeart2').val();
            // const subDeart3 = $('#subDeart3').val();
            // const subDeart4 = $('#subDeart4').val();
            // const subDeart5 = $('#subDeart5').val();

            var addName = '<?php echo $userName; ?>';

            alert(department)
            formData.append('userCodeS', userCode);
            formData.append('userNameS', userName);
            formData.append('nameS', name);
            formData.append('departmentS', department);
            formData.append('levelS', level);
            formData.append('startTimeS', startTime);
            formData.append('endTimeS', endTime);
            formData.append('telPhoneS', telPhone);
            formData.append('addNameS', addName);
            formData.append('reasonS', reason);
            formData.append('startDateS', startDate);
            formData.append('endDateS', endDate);
            formData.append('workplaceS', workplace);
            formData.append('subDepartS', subDepart);
            // formData.append('subDepart2', subDepart2);
            // formData.append('subDepart3', subDepart3);
            // formData.append('subDepart4', subDepart4);
            // formData.append('subDepart5', subDepart5);


            $.ajax({
                url: 'a_ajax_add_stop_work.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    // alert(response);
                    Swal.fire({
                        title: "บันทึกข้อมูลหยุดงานสำเร็จ",
                        text: "บันทึกข้อมูลหยุดงานของ " + name + " สำเร็จ",
                        icon: "success",
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            location.reload();
                        }
                    });
                },
                error: function(xhr, status, error) {
                    console.error('เกิดข้อผิดพลาด: ', error);
                }
            });
        });

        $('.btn-open-modal').click(function() {
            var userCode = $(this).data('usercode');
            $.ajax({
                type: 'GET',
                url: 'a_ajax_get_late_time.php', // ตัวอย่าง URL ที่ต้องการเรียกใช้เพื่อดึงข้อมูลพนักงาน
                data: {
                    userCode: userCode
                },
                success: function(data) {
                    $('#employeeModalBody').html(data);
                    $('#employeeModal').modal(
                        'show');
                }
            });
        });
        // ตรวจสอบ
        $('.chkLatebtn').click(function() {
            var usercode = $(this).data('usercode');
            var createDatetime = $(this).closest('tr').find('td[data-datetime]').data('datetime');
            var userName = "<?php echo $userName; ?>";
            var rowData = $(this).closest('tr').children('td');
            var lateDate = $(rowData[0]).text();
            var lateStart = $(rowData[1]).text();
            var lateEnd = $(rowData[2]).text();
            var department = $(rowData[3]).text();
            var name = $(rowData[6]).text();
            var leaveStatus = $(rowData[9]).text();

            $('.chkLatebtn').off('click');
            Swal.fire({
                title: "ต้องการอนุมัติการมาสายหรือไม่?",
                icon: "question",
                showCancelButton: true,
                showDenyButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#DC3545',
                denyButtonColor: '#0D6EFD',
                confirmButtonText: 'อนุมัติ',
                cancelButtonText: 'ไม่อนุมัติ',
                denyButtonText: 'ยกเลิก',
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                customClass: {
                    actions: 'my-actions',
                    cancelButton: 'order-2',
                    confirmButton: 'order-1',
                    denyButton: 'order-3'
                }
            }).then((result) => {
                // อนุมัติ
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'a_ajax_upd_late_status.php',
                        method: 'POST',
                        data: {
                            usercode: usercode,
                            createDatetime: createDatetime,
                            userName: userName,
                            lateDate: lateDate,
                            lateStart: lateStart,
                            lateEnd: lateEnd,
                            department: department,
                            name: name,
                            leaveStatus: leaveStatus,
                            action: 'approve'
                        },
                        success: function(response) {
                            Swal.fire({
                                title: 'ตรวจสอบรายการสำเร็จ',
                                // text: '',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    location.reload();
                                }
                            });
                        },
                        error: function() {
                            Swal.fire({
                                title: 'ข้อผิดพลาด!',
                                text: 'มีบางอย่างผิดพลาด',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    });
                }
                // ยกเลิก
                else if (result.isDenied) {
                    location.reload();
                }
                // ไม่อนุมัติ
                else if (result.dismiss === Swal.DismissReason.cancel) {
                    $.ajax({
                        url: 'a_ajax_upd_late_status.php',
                        method: 'POST',
                        data: {
                            usercode: usercode,
                            createDatetime: createDatetime,
                            userName: userName,
                            lateDate: lateDate,
                            lateStart: lateStart,
                            lateEnd: lateEnd,
                            department: department,
                            name: name,
                            leaveStatus: leaveStatus,
                            action: 'deny'
                        },
                        success: function(response) {
                            Swal.fire({
                                title: 'ตรวจสอบรายการสำเร็จ',
                                text: '',
                                icon: 'success',
                                allowOutsideClick: false,
                                allowEscapeKey: false
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    location.reload();
                                }
                            });
                        },
                        error: function() {
                            Swal.fire({
                                title: 'ข้อผิดพลาด!',
                                text: 'มีบางอย่างผิดพลาด',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    });
                } else {
                    $('.chkLatebtn').on('click');
                }
            });
        });

        $('.cancelLeaveBtn').click(function() {
            var usercode = $(this).data('usercode');
            var createDatetime = $(this).closest('tr').find('td[data-datetime]').data('datetime');
            var userName = "<?php echo $userName; ?>";
            var rowData = $(this).closest('tr').children('td');
            var lateDate = $(rowData[5]).text();
            var lateStart = $(rowData[6]).text();
            var lateEnd = $(rowData[7]).text();
            var department = $(rowData[8]).text();

            $('.cancelLeaveBtn').off('click');

            Swal.fire({
                title: "ต้องการยกเลิกการมาสาย ?",
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#DC3545',
                confirmButtonText: 'ใช่',
                cancelButtonText: 'ไม่',
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'a_ajax_upd_late_status.php',
                        method: 'POST',
                        data: {
                            usercode: usercode,
                            createDatetime: createDatetime,
                            userName: userName,
                            lateDate: lateDate,
                            lateStart: lateStart,
                            lateEnd: lateEnd,
                            department: department,
                            action: 'cancel'
                        },
                        success: function(response) {

                            alert('ยกเลิกการมาสายสำเร็จแล้ว');
                            location
                                .reload();
                        },
                        error: function() {
                            alert('มีบางอย่างผิดพลาด');
                        }
                    });
                } else {
                    $('.cancelLeaveBtn').on('click');
                    location.reload();
                }
            });
        });

        // ค้นหารหัสพนักงานในรายการมาสาย
        $("#codeSearch2").on("keyup", function() {
            var value2 = $(this).val().toLowerCase();
            $("tbody tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value2) > -1);
            });
        });
        $("#codeSearch3").on("keyup", function() {
            var value3 = $(this).val().toLowerCase();
            $("tbody tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value3) > -1);
            });
        });
    });

    const codeSearchElements = document.querySelectorAll('#codeSearch, #userCodeS');

    // Add 'input' event listener to both elements
    codeSearchElements.forEach(function(element) {
        element.addEventListener('input', function() {
            var selectedCode = this.value;
            var dataList = document.getElementById('codeList').getElementsByTagName('option');
            for (var i = 0; i < dataList.length; i++) {
                if (dataList[i].value === selectedCode) {
                    document.getElementById('name').value = dataList[i].getAttribute('data-name');
                    document.getElementById('nameS').value = dataList[i].getAttribute('data-name');
                    document.getElementById('userName').value = dataList[i].getAttribute(
                        'data-username');
                    document.getElementById('userNameS').value = dataList[i].getAttribute(
                        'data-username');
                    document.getElementById('department').value = dataList[i].getAttribute(
                        'data-depart');
                    document.getElementById('departmentS').value = dataList[i].getAttribute(
                        'data-depart');
                    document.getElementById('level').value = dataList[i].getAttribute('data-level');
                    document.getElementById('levelS').value = dataList[i].getAttribute('data-level');
                    document.getElementById('telPhone').value = dataList[i].getAttribute(
                        'data-telPhone');
                    document.getElementById('telPhoneS').value = dataList[i].getAttribute(
                        'data-telPhone');
                    document.getElementById('workplace').value = dataList[i].getAttribute(
                        'data-workplace');
                    document.getElementById('workplaceS').value = dataList[i].getAttribute(
                        'data-workplace')
                    document.getElementById('subDepart').value = dataList[i].getAttribute(
                        'data-sub-department');
                    document.getElementById('subDepartS').value = dataList[i].getAttribute(
                        'data-sub-department');
                    document.getElementById('subDepart2').value = dataList[i].getAttribute(
                        'data-sub-department2');
                    document.getElementById('subDepartS2').value = dataList[i].getAttribute(
                        'data-sub-department2');
                    document.getElementById('subDepart3').value = dataList[i].getAttribute(
                        'data-sub-department3');
                    document.getElementById('subDepartS3').value = dataList[i].getAttribute(
                        'data-sub-department3');
                    document.getElementById('subDepart4').value = dataList[i].getAttribute(
                        'data-sub-department4');
                    document.getElementById('subDepartS4').value = dataList[i].getAttribute(
                        'data-sub-department4');
                    document.getElementById('subDepart5').value = dataList[i].getAttribute(
                        'data-sub-department5');
                    document.getElementById('subDepartS5').value = dataList[i].getAttribute(
                        'data-sub-department5')
                    break;
                }
            }
        });

        // Add 'change' event listener to both elements
        element.addEventListener('change', function() {
            if (this.value === '') {
                document.getElementById('name').value = '';
                document.getElementById('nameS').value = '';
                document.getElementById('userName').value = '';
                document.getElementById('userNameS').value = '';
                document.getElementById('department').value = '';
                document.getElementById('departmentS').value = '';
                document.getElementById('level').value = '';
                document.getElementById('levelS').value = '';
                document.getElementById('telPhone').value = '';
                document.getElementById('telPhoneS').value = '';
                document.getElementById('workplace').value = '';
                document.getElementById('workplaceS').value = '';
                document.getElementById('subDepart').value = '';
                document.getElementById('subDepartS').value = '';
                document.getElementById('subDepart2').value = '';
                document.getElementById('subDepartS2').value = '';
                document.getElementById('subDepart3').value = '';
                document.getElementById('subDepartS3').value = '';
                document.getElementById('subDepart4').value = '';
                document.getElementById('subDepartS4').value = '';
                document.getElementById('subDepart5').value = '';
                document.getElementById('subDepartS5').value = '';
            }
        });

        // Add 'keyup' event listener to both elements
        element.addEventListener('keyup', function(e) {
            if (e.keyCode === 8 || e.keyCode === 46) {
                document.getElementById('name').value = '';
                document.getElementById('nameS').value = '';
                document.getElementById('userName').value = '';
                document.getElementById('userNameS').value = '';
                document.getElementById('department').value = '';
                document.getElementById('departmentS').value = '';
                document.getElementById('level').value = '';
                document.getElementById('levelS').value = '';
                document.getElementById('telPhone').value = '';
                document.getElementById('telPhoneS').value = '';
                document.getElementById('workplace').value = '';
                document.getElementById('workplaceS').value = '';
                document.getElementById('subDepart').value = '';
                document.getElementById('subDepartS').value = '';
                document.getElementById('subDepart2').value = '';
                document.getElementById('subDepartS2').value = '';
                document.getElementById('subDepart3').value = '';
                document.getElementById('subDepartS3').value = '';
                document.getElementById('subDepart4').value = '';
                document.getElementById('subDepartS4').value = '';
                document.getElementById('subDepart5').value = '';
                document.getElementById('subDepartS5').value = '';
            }
        });
    });

    $(function() {
        $.datepicker.regional['th'] = {
            closeText: 'ปิด',
            prevText: '&#xAB;&#xA0;ย้อน',
            nextText: 'ถัดไป&#xA0;&#xBB;',
            currentText: 'วันนี้',
            monthNames: ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
                'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'
            ],
            monthNamesShort: ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.',
                'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'
            ],
            dayNames: ['อาทิตย์', 'จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์'],
            dayNamesShort: ['อา.', 'จ.', 'อ.', 'พ.', 'พฤ.', 'ศ.', 'ส.'],
            dayNamesMin: ['อา.', 'จ.', 'อ.', 'พ.', 'พฤ.', 'ศ.', 'ส.'],
            weekHeader: 'Wk',
            dateFormat: 'dd-mm-yy',
            firstDay: 0,
            isRTL: false,
            showMonthAfterYear: false,
            yearSuffix: ''
        };
        $.datepicker.setDefaults($.datepicker.regional['th']);

        $('#startDate').datepicker({
            showButtonPanel: true, // แสดงปุ่มกดตรง datepicker
            changeMonth: true, // ให้แสดงเลือกเดือน
            defaultDate: new Date() // กำหนดให้วันที่เริ่มต้นเป็นวันปัจจุบัน
        }).datepicker("setDate", new Date()); // ให้แสดงวันที่ปัจจุบัน

        $('#endDate').datepicker({
            showButtonPanel: true, // แสดงปุ่มกดตรง datepicker
            changeMonth: true, // ให้แสดงเลือกเดือน
            defaultDate: new Date() // กำหนดให้วันที่เริ่มต้นเป็นวันปัจจุบัน
        }).datepicker("setDate", new Date()); // ให้แสดงวันที่ปัจจุบัน

        $('#startDateS').datepicker({
            showButtonPanel: true, // แสดงปุ่มกดตรง datepicker
            changeMonth: true, // ให้แสดงเลือกเดือน
            defaultDate: new Date() // กำหนดให้วันที่เริ่มต้นเป็นวันปัจจุบัน
        }).datepicker("setDate", new Date()); // ให้แสดงวันที่ปัจจุบัน

        $('#endDateS').datepicker({
            showButtonPanel: true, // แสดงปุ่มกดตรง datepicker
            changeMonth: true, // ให้แสดงเลือกเดือน
            defaultDate: new Date() // กำหนดให้วันที่เริ่มต้นเป็นวันปัจจุบัน
        }).datepicker("setDate", new Date()); // ให้แสดงวันที่ปัจจุบัน
    });
    </script>
    <script src="../js/popper.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/bootstrap.bundle.js"></script>
    <script src="../js/bootstrap.bundle.min.js"></script>
</body>

</html>