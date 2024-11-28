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
// echo $user
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>การมาสายและหยุดงานของพนักงาน</title>

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
    <?php include 'leader_navbar.php'?>
    <nav class="navbar bg-body-tertiary">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-auto">
                    <i class="fa-solid fa-user-clock fa-2xl"></i>
                </div>
                <div class="col-auto">
                    <h3>ข้อมูลมาสายและหยุดงานของพนักงาน</h3>
                </div>
            </div>
        </div>
    </nav>

    <div class="mt-5 container-fluid">
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#tab1">มาสายและหยุดงาน</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab2">ประวัติพนักงานมาสาย</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab3" hidden>ประวัติพนักงานที่ขาดงาน</a>
            </li>
        </ul>
    </div>

    <div class="container">
        <div class="tab-content">
            <!-- การมาสาย -->
            <div class="tab-pane fade show active" id="tab1">
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

$sql = "SELECT
    li.*,
    em.*
FROM leave_list li
INNER JOIN employees em
    ON li.l_usercode = em.e_usercode
WHERE
    li.l_approve_status IN (0, 1, 2, 3, 6)
    AND li.l_level IN ('user')
    AND li.l_leave_id IN (6,7)
    AND YEAR(li.l_leave_end_date) = :selectedYear
    AND MONTH(li.l_leave_end_date) = :selectedMonth
    AND (
        (em.e_sub_department = :subDepart AND li.l_department = :depart)
        OR (em.e_sub_department2 = :subDepart2 AND li.l_department = :depart)
        OR (em.e_sub_department3 = :subDepart3 AND li.l_department = :depart)
        OR (em.e_sub_department4 = :subDepart4 AND li.l_department = :depart)
        OR (em.e_sub_department5 = :subDepart5 AND li.l_department = :depart)
    )
ORDER BY li.l_create_datetime DESC";
// Prepare the statement
$stmt = $conn->prepare($sql);

// Bind the parameters
$stmt->bindParam(':selectedYear', $selectedYear, PDO::PARAM_INT);
$stmt->bindParam(':selectedMonth', $selectedMonth, PDO::PARAM_INT);
$stmt->bindParam(':subDepart', $subDepart, PDO::PARAM_STR);
$stmt->bindParam(':subDepart2', $subDepart2, PDO::PARAM_STR);
$stmt->bindParam(':subDepart3', $subDepart3, PDO::PARAM_STR);
$stmt->bindParam(':subDepart4', $subDepart4, PDO::PARAM_STR);
$stmt->bindParam(':subDepart5', $subDepart5, PDO::PARAM_STR);
$stmt->bindParam(':depart', $depart, PDO::PARAM_STR);

// Execute the statement to get total rows
$stmt->execute();

// Get total rows for pagination
$totalRows = $stmt->rowCount();

// Calculate total pages
$totalPages = ceil($totalRows / $itemsPerPage);

// Calculate offset for pagination
$offset = ($currentPage - 1) * $itemsPerPage;

// Add LIMIT and OFFSET to the SQL query
$sql .= " LIMIT :limit OFFSET :offset";

// Prepare the statement again with the complete query
$stmt = $conn->prepare($sql);

// Bind the parameters again for pagination
$stmt->bindParam(':limit', $itemsPerPage, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

// Bind the previously bound parameters again
$stmt->bindParam(':selectedYear', $selectedYear, PDO::PARAM_INT);
$stmt->bindParam(':selectedMonth', $selectedMonth, PDO::PARAM_INT);
$stmt->bindParam(':subDepart', $subDepart, PDO::PARAM_STR);
$stmt->bindParam(':subDepart2', $subDepart2, PDO::PARAM_STR);
$stmt->bindParam(':subDepart3', $subDepart3, PDO::PARAM_STR);
$stmt->bindParam(':subDepart4', $subDepart4, PDO::PARAM_STR);
$stmt->bindParam(':subDepart5', $subDepart5, PDO::PARAM_STR);
$stmt->bindParam(':depart', $depart, PDO::PARAM_STR);

// Execute the statement
$stmt->execute();

// Fetch the results
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($result) > 0) {
    echo '<table class="table">';
    echo '<thead>';
    echo '<tr class="text-center align-middle">
        <th>ลำดับ</th>
        <th>รหัสพนักงาน</th>
        <th>ชื่อพนักงาน</th>
        <th>ประเภท</th>
        <th>วันที่มาสาย</th>
        <th>สถานะรายการ</th>
        <th>สถานะ_1</th>
        <th>สถานะ_2</th>
        <th>สถานะ (เฉพาะ HR)</th>
        <th>หมายเหตุ</th>
        <th></th>
        </tr>';
    echo '</thead>';
    echo '<tbody>';
    foreach ($result as $index => $row) {
        $rowNumber = $totalRows - ($offset + $index);
        echo '<tr class="text-center align-middle">';

        // 0
        echo '<td hidden>' . $row['l_department'] . '</td>';

        // 1
        echo '<td hidden>' . $row['l_leave_start_date'] . '</td>';

        // 2
        echo '<td hidden>' . $row['l_leave_start_time'] . '</td>';

        // 3
        echo '<td hidden>' . $row['l_leave_end_time'] . '</td>';

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
            echo 'หยุดงาน';
        } else {
            echo $row['l_leave_id'];
        }
        echo '</td>';
        // 8
        echo '<td>' . $row['l_leave_start_date'] . '<br>' . $row['l_leave_start_time'] . ' ถึง ' . $row['l_leave_end_time'] . '</td>';

        // 9
        echo '<td>';
        if ($row['l_leave_status'] == 1) {
            echo '<span class="text-danger">ยกเลิกมาสาย</span>';
        } else {
            echo '<span class="text-success">ปกติ</span>';
        }
        echo '</td>';

        // 10
        echo '<td>';
        // รอหัวหน้ารับทราบ
        if ($row['l_approve_status'] == 0) {
            echo '<div class="text-warning"><b>รอหัวหน้ารับทราบ</b></div>';
        }
        // รอผจกรับทราบ
        elseif ($row['l_approve_status'] == 1) {
            echo '<div class="text-warning"><b>รอผู้จัดการรับทราบ</b></div>';
        }
        // หัวหน้ารับทราบ
        elseif ($row['l_approve_status'] == 2) {
            echo '<div class="text-success"><b>หัวหน้ารับทราบ</b></div>';
        }
        // หัวหน้าไม่อนุมัติ
        elseif ($row['l_approve_status'] == 3) {
            echo '<div class="text-danger"><b>หัวหน้าไม่อนุมัติ</b></div>';
        }
        //  ผจก อนุมัติ
        elseif ($row['l_approve_status'] == 4) {
            echo '<div class="text-success"><b>ผู้จัดการรับทราบ</b></div>';
        }
        //  ผจก ไม่อนุมัติ
        elseif ($row['l_approve_status'] == 5) {
            echo '<div class="text-danger"><b>ผู้จัดการไม่อนุมัติ</b></div>';
        } elseif ($row['l_approve_status'] == 6) {
            echo '';
        }
        // ไม่พบสถานะ
        else {
            echo 'ไม่พบสถานะ';
        }
        echo '</td>';

        // 11
        echo '<td>';
        // รอหัวหน้ารับทราบ
        if ($row['l_approve_status2'] == 0) {
            echo '<div class="text-warning"><b>รอหัวหน้ารับทราบ</b></div>';
        }
        // รอผจกรับทราบ
        elseif ($row['l_approve_status2'] == 1) {
            echo '<div class="text-warning"><b>รอผู้จัดการรับทราบ</b></div>';
        }
        // หัวหน้ารับทราบ
        elseif ($row['l_approve_status2'] == 2) {
            echo '<div class="text-success"><b>หัวหน้ารับทราบ</b></div>';
        }
        // หัวหน้าไม่อนุมัติ
        elseif ($row['l_approve_status2'] == 3) {
            echo '<div class="text-danger"><b>หัวหน้าไม่อนุมัติ</b></div>';
        }
        //  ผจก รับทราบ
        elseif ($row['l_approve_status2'] == 4) {
            echo '<div class="text-success"><b>ผู้จัดการรับทราบ</b></div>';
        }
        //  ผจก ไม่รับทราบ
        elseif ($row['l_approve_status2'] == 5) {
            echo '<div class="text-danger"><b>ผู้จัดการไม่รับทราบ</b></div>';
        }
        //
        elseif ($row['l_approve_status2'] == 6) {
            echo '';
        }
        // ไม่พบสถานะ
        else {
            echo 'ไม่พบสถานะ';
        }
        echo '</td>';

        // 12
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

        // 13
        echo '<td>' . $row['l_remark'] . '</td>';
        if ($row['l_approve_status'] == 2) {
            echo '<td>';
            echo '<button type="button" class="btn btn-primary button-shadow btn-approve" data-usercode="' . $row['l_usercode'] . '" data-create-datetime="' . $row['l_create_datetime'] . '" disabled>ยืนยัน</button>';
            echo '</td>';
        } else {
            echo '<td>';
            echo '<button type="button" class="btn btn-primary button-shadow btn-approve" data-usercode="' . $row['l_usercode'] . '" data-create-datetime="' . $row['l_create_datetime'] . '">ยืนยัน</button>';
            echo '</td>';
        }
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
    echo '<span style="text-align: left; color:red;">ไม่พบข้อมูลมาสาย</span>';
}
?>
            </div>

            <!-- ประวัติมาสาย -->
            <div class="tab-pane fade" id="tab2">
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
// คำสั่ง SQL เพื่อดึงข้อมูลมาสายและขาดงาน
// $sql = "SELECT li.*, em.e_sub_department, em.e_sub_department2 , em.e_sub_department3 , em.e_sub_department4, em.e_sub_department5
// FROM leave_list li
// INNER JOIN employees em ON li.l_usercode = em.e_usercode AND em.e_sub_department = '$subDepart'
// AND Year(l_hr_create_datetime) = '$selectedYear'
// AND Month(l_hr_create_datetime) = '$selectedMonth'
// AND l_level = 'user'
// AND l_leave_id = 7
// ORDER BY l_hr_create_datetime DESC";
// $result = $conn->query($sql);
// $totalRows = $result->rowCount();
$sql = "SELECT
    li.*,
    em.*
FROM leave_list li
INNER JOIN employees em
    ON li.l_usercode = em.e_usercode
WHERE
    li.l_approve_status IN (0, 1, 2, 3, 6)
    AND li.l_level IN ('user')
    AND li.l_leave_id = 7
    AND YEAR(li.l_leave_end_date) = :selectedYear
    AND MONTH(li.l_leave_end_date) = :selectedMonth
    AND (
        (em.e_sub_department = :subDepart AND li.l_department = :depart)
        OR (em.e_sub_department2 = :subDepart2 AND li.l_department = :depart)
        OR (em.e_sub_department3 = :subDepart3 AND li.l_department = :depart)
        OR (em.e_sub_department4 = :subDepart4 AND li.l_department = :depart)
        OR (em.e_sub_department5 = :subDepart5 AND li.l_department = :depart)
    )
ORDER BY li.l_create_datetime DESC";
// Prepare the statement
$stmt = $conn->prepare($sql);

// Bind the parameters
$stmt->bindParam(':selectedYear', $selectedYear, PDO::PARAM_INT);
$stmt->bindParam(':selectedMonth', $selectedMonth, PDO::PARAM_INT);
$stmt->bindParam(':subDepart', $subDepart, PDO::PARAM_STR);
$stmt->bindParam(':subDepart2', $subDepart2, PDO::PARAM_STR);
$stmt->bindParam(':subDepart3', $subDepart3, PDO::PARAM_STR);
$stmt->bindParam(':subDepart4', $subDepart4, PDO::PARAM_STR);
$stmt->bindParam(':subDepart5', $subDepart5, PDO::PARAM_STR);
$stmt->bindParam(':depart', $depart, PDO::PARAM_STR);

// Execute the statement to get total rows
$stmt->execute();

// Get total rows for pagination
$totalRows = $stmt->rowCount();

// Calculate total pages
$totalPages = ceil($totalRows / $itemsPerPage);

// Calculate offset for pagination
$offset = ($currentPage - 1) * $itemsPerPage;

// Add LIMIT and OFFSET to the SQL query
$sql .= " LIMIT :limit OFFSET :offset";

// Prepare the statement again with the complete query
$stmt = $conn->prepare($sql);

// Bind the parameters again for pagination
$stmt->bindParam(':limit', $itemsPerPage, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

// Bind the previously bound parameters again
$stmt->bindParam(':selectedYear', $selectedYear, PDO::PARAM_INT);
$stmt->bindParam(':selectedMonth', $selectedMonth, PDO::PARAM_INT);
$stmt->bindParam(':subDepart', $subDepart, PDO::PARAM_STR);
$stmt->bindParam(':subDepart2', $subDepart2, PDO::PARAM_STR);
$stmt->bindParam(':subDepart3', $subDepart3, PDO::PARAM_STR);
$stmt->bindParam(':subDepart4', $subDepart4, PDO::PARAM_STR);
$stmt->bindParam(':subDepart5', $subDepart5, PDO::PARAM_STR);
$stmt->bindParam(':depart', $depart, PDO::PARAM_STR);

// Execute the statement
$stmt->execute();

// Fetch the results
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($result) > 0) {
    echo '<table class="table">';
    echo '<thead>';
    echo '<tr class="text-center align-middle">
    <th>ลำดับ</th>
    <th>รหัสพนักงาน</th>
    <th>ชื่อพนักงาน</th>
    <th>ประเภท</th>
    <th>วันที่มาสาย</th>
    <th>สถานะรายการ</th>
    <th>สถานะรับทราบ_1</th>
    <th>สถานะรับทราบ_2</th>
    <th>สถานะ (เฉพาะ HR)</th>
    <th>หมายเหตุ</th>
    </tr>';
    echo '</thead>';
    echo '<tbody>';
    foreach ($result as $index => $row) {
        $rowNumber = $totalRows - ($offset + $index);
        echo '<tr class="text-center align-middle">';

        // 0
        echo '<td hidden>' . $row['l_department'] . '</td>';

        // 1
        echo '<td hidden>' . $row['l_leave_start_date'] . '</td>';

        // 2
        echo '<td hidden>' . $row['l_leave_start_time'] . '</td>';

        // 3
        echo '<td hidden>' . $row['l_leave_end_time'] . '</td>';

        // 4
        echo '<td>' . $rowNumber . '</td>';

        // 5
        echo '<td>' . $row['l_usercode'] . '</td>';

        // 6
        echo '<td>' . $row['l_name'] . '</td>';

        // 7
        echo '<td>' . ($row['l_leave_id'] == 7 ? 'มาสาย' : $row['l_leave_id']) . '</td>';

        // 8
        echo '<td>' . $row['l_leave_start_date'] . '<br>' . $row['l_leave_start_time'] . ' ถึง ' . $row['l_leave_end_time'] . '</td>';

        // 9
        echo '<td>';
        if ($row['l_leave_status'] == 1) {
            echo '<span class="text-danger">ยกเลิกมาสาย</span>';
        } else {
            echo '<span class="text-success">ปกติ</span>';
        }
        echo '</td>';

        // 10
        echo '<td>';
        // รอหัวหน้ารับทราบ
        if ($row['l_approve_status'] == 0) {
            echo '<div class="text-warning"><b>รอหัวหน้ารับทราบ</b></div>';
        }
        // รอผจกรับทราบ
        elseif ($row['l_approve_status'] == 1) {
            echo '<div class="text-warning"><b>รอผู้จัดการรับทราบ</b></div>';
        }
        // หัวหน้ารับทราบ
        elseif ($row['l_approve_status'] == 2) {
            echo '<div class="text-success"><b>หัวหน้ารับทราบ</b></div>';
        }
        // หัวหน้าไม่รับทราบ
        elseif ($row['l_approve_status'] == 3) {
            echo '<div class="text-danger"><b>หัวหน้าไม่รับทราบ</b></div>';
        }
        //  ผจก รับทราบ
        elseif ($row['l_approve_status'] == 4) {
            echo '<div class="text-success"><b>ผู้จัดการรับทราบ</b></div>';
        }
        //  ผจก ไม่รับทราบ
        elseif ($row['l_approve_status'] == 5) {
            echo '<div class="text-danger"><b>ผู้จัดการไม่รับทราบ</b></div>';
        }
        // ไม่มีสถานะ
        else {
            echo 'ไม่พบสถานะ';
        }
        echo '</td>';

        // 11
        echo '<td>';
        // รอหัวหน้ารับทราบ
        if ($row['l_approve_status2'] == 0) {
            echo '<div class="text-warning"><b>รอหัวหน้ารับทราบ</b></div>';
        }
        // รอผจกรับทราบ
        elseif ($row['l_approve_status2'] == 1) {
            echo '<div class="text-warning"><b>รอผู้จัดการรับทราบ</b></div>';
        }
        // หัวหน้ารับทราบ
        elseif ($row['l_approve_status2'] == 2) {
            echo '<div class="text-success"><b>หัวหน้ารับทราบ</b></div>';
        }
        // หัวหน้าไม่รับทราบ
        elseif ($row['l_approve_status2'] == 3) {
            echo '<div class="text-danger"><b>หัวหน้าไม่รับทราบ</b></div>';
        }
        //  ผจก รับทราบ
        elseif ($row['l_approve_status2'] == 4) {
            echo '<div class="text-success"><b>ผู้จัดการรับทราบ</b></div>';
        }
        //  ผจก ไม่รับทราบ
        elseif ($row['l_approve_status2'] == 5) {
            echo '<div class="text-danger"><b>ผู้จัดการไม่รับทราบ</b></div>';
        }
        // ไม่มีสถานะ
        else {
            echo 'ไม่พบสถานะ';
        }
        echo '</td>';

        // 12
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

        // 13
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
    echo '<span style="text-align: left; color:red;">ไม่พบข้อมูลมาสาย</span>';
}

?>
            </div>


            <!-- ประวัติขาดงาน -->
            <div class="tab-pane fade" id="tab3">

                <?php
$itemsPerPage = 10;

// คำนวณหน้าปัจจุบัน
if (!isset($_GET['page'])) {
    $currentPage = 1;
} else {
    $currentPage = $_GET['page'];
}
// คำสั่ง SQL เพื่อดึงข้อมูลมาสายและขาดงาน
$sql = "SELECT * FROM leave_list WHERE l_leave_id = 6 AND l_department = '$depart'
AND Month(l_create_datetime) = '$selectedMonth'AND Year(l_create_datetime) = $selectedYear";
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
    echo '<table class="table">';
    echo '<thead>';
    echo '<tr class="text-center align-middle">
    <th>ลำดับ</th>
    <th>รหัสพนักงาน</th>
    <th>ชื่อพนักงาน</th>
    <th>ประเภท</th>
    <th>หมายเหตุ</th>
    <th></th>
    </tr>';
    echo '</thead>';
    echo '<tbody>';
    foreach ($result as $index => $row) {
        $rowNumber = $totalRows - ($offset + $index);
        echo '<tr class="text-center align-middle">';
        // 0
        echo '<td hidden>' . $row['l_department'] . '</td>';

        // 1
        echo '<td hidden>' . $row['l_leave_start_date'] . '</td>';

        // 2
        echo '<td hidden>' . $row['l_leave_start_time'] . '</td>';

        // 3
        echo '<td hidden>' . $row['l_leave_end_time'] . '</td>';

        // 4
        echo '<td>' . $rowNumber . '</td>';

        // 5
        echo '<td>' . $row['l_usercode'] . '</td>';

        // 6
        echo '<td>' . $row['l_name'] . '</td>';

        // 7
        echo '<td>' . ($row['l_leave_id'] == 7 ? 'มาสาย' : $row['l_leave_id']) . '</td>';

        // 8
        echo '<td>' . $row['l_leave_start_date'] . '<br>' . $row['l_leave_start_time'] . ' ถึง ' . $row['l_leave_end_time'] . '</td>';

        // 9
        echo '<td>';
        if ($row['l_leave_status'] == 1) {
            echo '<span class="text-danger">ยกเลิกมาสาย</span>';
        } else {
            echo '<span class="text-success">ปกติ</span>';
        }
        echo '</td>';

        // 10
        echo '<td>';
        // รอหัวหน้ารับทราบ
        if ($row['l_approve_status'] == 0) {
            echo '<div class="text-warning"><b>รอหัวหน้ารับทราบ</b></div>';
        }
        // รอผจกรับทราบ
        elseif ($row['l_approve_status'] == 1) {
            echo '<div class="text-warning"><b>รอผู้จัดการรับทราบ</b></div>';
        }
        // หัวหน้ารับทราบ
        elseif ($row['l_approve_status'] == 2) {
            echo '<div class="text-success"><b>หัวหน้ารับทราบ</b></div>';
        }
        // หัวหน้าไม่รับทราบ
        elseif ($row['l_approve_status'] == 3) {
            echo '<div class="text-danger"><b>หัวหน้าไม่รับทราบ</b></div>';
        }
        //  ผจก รับทราบ
        elseif ($row['l_approve_status'] == 4) {
            echo '<div class="text-success"><b>ผู้จัดการรับทราบ</b></div>';
        }
        //  ผจก ไม่รับทราบ
        elseif ($row['l_approve_status'] == 5) {
            echo '<div class="text-danger"><b>ผู้จัดการไม่รับทราบ</b></div>';
        }
        // ไม่มีสถานะ
        else {
            echo 'ไม่พบสถานะ';
        }
        echo '</td>';

        // 11
        echo '<td>';
        // รอหัวหน้ารับทราบ
        if ($row['l_approve_status2'] == 0) {
            echo '<div class="text-warning"><b>รอหัวหน้ารับทราบ</b></div>';
        }
        // รอผจกรับทราบ
        elseif ($row['l_approve_status2'] == 1) {
            echo '<div class="text-warning"><b>รอผู้จัดการรับทราบ</b></div>';
        }
        // หัวหน้ารับทราบ
        elseif ($row['l_approve_status2'] == 2) {
            echo '<div class="text-success"><b>หัวหน้าทราบ</b></div>';
        }
        // หัวหน้าไม่รับทราบ
        elseif ($row['l_approve_status2'] == 3) {
            echo '<div class="text-danger"><b>หัวหน้าไม่รับทราบ</b></div>';
        }
        //  ผจก รับทราบ
        elseif ($row['l_approve_status2'] == 4) {
            echo '<div class="text-success"><b>ผู้จัดการรับทราบ</b></div>';
        }
        //  ผจก ไม่รับทราบ
        elseif ($row['l_approve_status2'] == 5) {
            echo '<div class="text-danger"><b>ผู้จัดการไม่รับทราบ</b></div>';
        }
        // ไม่มีสถานะ
        else {
            echo 'ไม่พบสถานะ';
        }
        echo '</td>';

        // 12
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

        // 13
        echo '<td>' . $row['l_remark'] . '</td>';

        echo '<td><button type="button" class="btn btn-primary btn-open-modal" data-toggle="modal" data-target="#employeeModal" data-usercode="' . $row['l_usercode'] . '"><i class="fa-solid fa-magnifying-glass"></i></button></td>';

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

        $('.btn-open-modal').click(function() {
            var userCode = $(this).data('usercode');
            $.ajax({
                type: 'GET',
                url: 'l_ajax_get_late_time.php', // ตัวอย่าง URL ที่ต้องการเรียกใช้เพื่อดึงข้อมูลพนักงาน
                data: {
                    userCode: userCode
                },
                success: function(data) {
                    $('#employeeModalBody').html(data);
                    $('#employeeModal').modal(
                        'show'); // แสดง Modal เมื่อโหลดข้อมูลเสร็จสิ้น
                }
            });
        });
        $('.btn-approve').click(function() {
            var rowData = $(this).closest('tr').children('td'); // แก้ไขเพื่อหาค่าจากแถวที่เกี่ยวข้อง
            var userName = '<?php echo $userName; ?>';
            var proveName = '<?php echo $name; ?>';
            var level = '<?php echo $level; ?>';
            var subDepart = '<?php echo $subDepart; ?>';
            var subDepart2 = '<?php echo $subDepart2; ?>';
            var subDepart3 = '<?php echo $subDepart3; ?>';
            var subDepart4 = '<?php echo $subDepart4; ?>';
            var subDepart5 = '<?php echo $subDepart5; ?>';
            var workplace = '<?php echo $workplace; ?>';

            var createDateTime = $(this).data(
                'create-datetime'); // เพิ่มบรรทัดนี้เพื่อรับค่า l_create_datetime
            var depart = $(rowData[0]).text(); // แผนก
            var lateDate = $(rowData[1]).text(); // วันที่สร้างใบลา (index ของ td ที่ซ่อนอยู่)
            var lateStart = $(rowData[2]).text(); // เวลาเริ่มต้นที่มาสาย
            var lateEnd = $(rowData[3]).text(); // เวลาสิ้นสุดที่มาสาย
            var userCode = $(rowData[5]).text();
            var name = $(rowData[6]).text();
            var leaveType = $(rowData[7]).text();
            var leaveStatus = $(rowData[9]).text();

            // alert(workplace)
            // alert(leaveType)
            $('.btn-approve').off('click');
            Swal.fire({
                title: "ต้องการยืนยันหรือไม่ ?",
                text: name + " " + leaveType,
                icon: "question",
                showDenyButton: true,
                confirmButtonColor: '#198754',
                /*  cancelButtonColor: '#DC3545', */
                denyButtonColor: '#DC3545',
                confirmButtonText: 'รับทราบ',
                /* cancelButtonText: 'ไม่อนุมัติ', */
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
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'l_upd_late_time.php',
                        method: 'POST',
                        data: {
                            userName: userName,
                            proveName: proveName,
                            createDateTime: createDateTime,
                            depart: depart,
                            lateDate: lateDate,
                            lateStart: lateStart,
                            lateEnd: lateEnd,
                            userCode: userCode,
                            name: name,
                            leaveStatus: leaveStatus,
                            level: level,
                            workplace: workplace,
                            leaveType: leaveType,
                            subDepart: subDepart,
                            subDepart2: subDepart2,
                            subDepart3: subDepart3,
                            subDepart4: subDepart4,
                            subDepart5: subDepart5,
                            action: 'approve'
                        },
                        success: function(response) {
                            Swal.fire({
                                title: 'สำเร็จ',
                                text: 'รับทราบ' +
                                    leaveType +
                                    'ของ ' + name +
                                    ' ของวันที่ ' +
                                    lateDate,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr, status, error) {
                            Swal.fire({
                                title: 'ไม่สำเร็จ!',
                                text: 'เกิดข้อผิดพลาดในการรับทราบ',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    });
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    $.ajax({
                        url: 'l_upd_late_time.php',
                        method: 'POST',
                        data: {
                            userName: userName,
                            proveName: proveName,
                            createDateTime: createDateTime,
                            depart: depart,
                            lateDate: lateDate,
                            lateStart: lateStart,
                            lateEnd: lateEnd,
                            userCode: userCode,
                            name: name,
                            leaveStatus: leaveStatus,
                            level: level,
                            workplace: workplace,
                            leaveType: leaveType,
                            subDepart: subDepart,
                            subDepart2: subDepart2,
                            subDepart3: subDepart3,
                            subDepart4: subDepart4,
                            subDepart5: subDepart5,
                            action: 'deny'
                        },
                        success: function(response) {
                            Swal.fire({
                                title: 'สำเร็จ',
                                html: 'ไม่อนุมัติ' + leaveType +
                                    'ของ ' + name +
                                    '<br>ของวันที่ ' + lateDate,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr, status, error) {
                            Swal.fire({
                                title: 'ไม่สำเร็จ!',
                                text: 'เกิดข้อผิดพลาดในการไม่อนุมัติ',
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
            });
        });
    });

    document.getElementById('codeSearch').addEventListener('input', function() {
        var selectedCode = this.value;
        var dataList = document.getElementById('codeList').getElementsByTagName('option');
        for (var i = 0; i < dataList.length; i++) {
            if (dataList[i].value === selectedCode) {
                document.getElementById('name').value = dataList[i].getAttribute('data-name');
                document.getElementById('userName').value = dataList[i].getAttribute('data-username');
                document.getElementById('department').value = dataList[i].getAttribute('data-depart');
                document.getElementById('level').value = dataList[i].getAttribute('data-level');
                document.getElementById('telPhone').value = dataList[i].getAttribute('data-telPhone');
                break;
            }
        }
    });

    document.getElementById('codeSearch').addEventListener('change', function() {
        if (this.value === '') {
            document.getElementById('name').value = '';
            document.getElementById('userName').value = '';
            document.getElementById('department').value = '';
            document.getElementById('level').value = '';
            document.getElementById('telPhone').value = '';
        }
    });

    document.getElementById('codeSearch').addEventListener('keyup', function(e) {
        if (e.keyCode === 8 || e.keyCode === 46) {
            document.getElementById('name').value = '';
            document.getElementById('userName').value = '';
            document.getElementById('department').value = '';
            document.getElementById('level').value = '';
            document.getElementById('telPhone').value = '';
        }
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
    });
    </script>
    <script src="../js/popper.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/bootstrap.bundle.js"></script>
    <script src="../js/bootstrap.bundle.min.js"></script>
</body>

</html>