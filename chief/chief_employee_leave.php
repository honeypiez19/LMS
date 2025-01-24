<?php
    session_start();
    date_default_timezone_set('Asia/Bangkok');

    include '../connect.php';
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
    <?php require 'chief_navbar.php'?>

    <nav class="navbar bg-body-tertiary">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-auto">
                    <i class="fa-solid fa-folder-open fa-2xl"></i>
                </div>
                <div class="col-auto">
                    <h3>สถิติการลาของพนักงาน
                    </h3>
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
                        $sql = "SELECT * FROM employees
                             WHERE e_usercode <> :userCode
                        AND e_level <> :level
                        AND e_workplace = :workplace
                        AND e_status <> '1'
                        AND (
                                (e_sub_department = :subDepart)
                                OR (e_sub_department2 = :subDepart2)
                                OR (e_sub_department3 = :subDepart3)
                                OR (e_sub_department4 = :subDepart4)
                                OR (e_sub_department5 = :subDepart5)
                            )
                        ";
                        $stmt = $conn->prepare($sql);

                        $stmt->bindParam(':userCode', $userCode);
                        $stmt->bindParam(':level', $level);
                        $stmt->bindParam(':workplace', $workplace);
                        $stmt->bindParam(':subDepart', $subDepart);
                        $stmt->bindParam(':subDepart2', $subDepart2);
                        $stmt->bindParam(':subDepart3', $subDepart3);
                        $stmt->bindParam(':subDepart4', $subDepart4);
                        $stmt->bindParam(':subDepart5', $subDepart5);

                        $stmt->execute();

                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo '<option value="' . htmlspecialchars($row['e_usercode'], ENT_QUOTES, 'UTF-8') . '">';
                        }
                    ?>
                </datalist>
            </div>
            <div class="col-3">
                <label for="nameLabel" class="form-label">ชื่อพนักงาน</label>
                <input type="text" class="form-control" id="nameSearch" list="nameList">
                <datalist id="nameList">
                    <?php
                        $sql = "SELECT * FROM employees
                             WHERE e_usercode <> :userCode
                        AND e_level <> :level
                        AND e_workplace = :workplace
                        AND e_status <> '1'
                        AND (
                                (e_sub_department = :subDepart)
                                OR (e_sub_department2 = :subDepart2)
                                OR (e_sub_department3 = :subDepart3)
                                OR (e_sub_department4 = :subDepart4)
                                OR (e_sub_department5 = :subDepart5)
                            )
                        ";
                        $stmt = $conn->prepare($sql);

                        $stmt->bindParam(':userCode', $userCode);
                        $stmt->bindParam(':level', $level);
                        $stmt->bindParam(':workplace', $workplace);
                        $stmt->bindParam(':subDepart', $subDepart);
                        $stmt->bindParam(':subDepart2', $subDepart2);
                        $stmt->bindParam(':subDepart3', $subDepart3);
                        $stmt->bindParam(':subDepart4', $subDepart4);
                        $stmt->bindParam(':subDepart5', $subDepart5);

                        $stmt->execute();

                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo '<option value="' . htmlspecialchars($row['e_name'], ENT_QUOTES, 'UTF-8') . '">';
                        }
                    ?>
                </datalist>
            </div>
            <div class="col-3">
                <label for="depLabel" class="form-label">แผนก</label>
                <input type="text" class="form-control" id="depSearch" list="depList">
                <datalist id="depList">
                    <?php
                        $sql = "SELECT * FROM employees
                             WHERE e_usercode <> :userCode
                        AND e_level <> :level
                        AND e_workplace = :workplace
                        AND e_status <> '1'
                        AND (
                                (e_sub_department = :subDepart)
                                OR (e_sub_department2 = :subDepart2)
                                OR (e_sub_department3 = :subDepart3)
                                OR (e_sub_department4 = :subDepart4)
                                OR (e_sub_department5 = :subDepart5)
                            )
                        ";
                        $stmt = $conn->prepare($sql);

                        $stmt->bindParam(':userCode', $userCode);
                        $stmt->bindParam(':level', $level);
                        $stmt->bindParam(':workplace', $workplace);
                        $stmt->bindParam(':subDepart', $subDepart);
                        $stmt->bindParam(':subDepart2', $subDepart2);
                        $stmt->bindParam(':subDepart3', $subDepart3);
                        $stmt->bindParam(':subDepart4', $subDepart4);
                        $stmt->bindParam(':subDepart5', $subDepart5);

                        $stmt->execute();

                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo '<option value="' . htmlspecialchars($row['e_department'], ENT_QUOTES, 'UTF-8') . '">';
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
        <form class="mt-4 mb-3 row" method="post" id="dateForm">
            <label for="startDate" class="mt-2 col-auto">เลือกช่วงเวลา :</label>
            <div class="col-auto">
                <?php
                    $startDate          = date('Y', strtotime('-1 year')) . "-12-01"; // 1 ธันวาคม ปีที่แล้ว
                    $startDateFormatted = date('d-m-Y', strtotime($startDate));

                    $endDate          = date('Y' . "-11-30"); // 30 พฤศจิกายน ปีปัจจุบัน
                    $endDateFormatted = date('d-m-Y', strtotime($endDate));

                    if (isset($_POST['start_date']) && isset($_POST['end_date'])) {
                        $startDate          = DateTime::createFromFormat('d-m-Y', $_POST['start_date'])->format('Y-m-d');
                        $startDateFormatted = $_POST['start_date'];

                        $endDate          = DateTime::createFromFormat('d-m-Y', $_POST['end_date'])->format('Y-m-d');
                        $endDateFormatted = $_POST['end_date'];
                    }
                ?>
                <input type="text" name="start_date" class="form-control" id="startDate"
                    value="<?php echo $startDateFormatted; ?>">
            </div>
            <div class="col-auto">
                <input type="text" name="end_date" class="form-control" id="endDate"
                    value="<?php echo $endDateFormatted; ?>">
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-secondary" id="resetButton" data-bs-toggle="tooltip"
                    title="01-12-ปีที่แล้ว ถึง 30-11-ปีปัจจุบัน">
                    ค่าเริ่มต้น
                </button>
            </div>
        </form>

        <!-- แสดงช่วงวันที่ที่เลือก -->
        <div>
            <p>ช่วงวันที่ : <strong id="startDateText">
                    <?php
                        echo $startDateFormatted;
                    ?>
                </strong> ถึง <strong id="endDateText">
                    <?php
                        echo $endDateFormatted;
                    ?>
                </strong>
            </p>
        </div>
        <!-- เลือกปี -->
        <!-- <form class="mt-3 mb-3 row" method="post" id="yearForm">
            <label for="" class="mt-2 col-auto">เลือกปี</label>
            <div class="col-auto">
                <?php
                    $selectedYear = date('Y'); // ปีปัจจุบัน
                    if (isset($_POST['year'])) {
                        $selectedYear = $_POST['year'];
                        $startDate    = date("Y-m-d", strtotime(($selectedYear - 1) . "-12-01"));
                        $endDate      = date("Y-m-d", strtotime($selectedYear . "-11-30"));

                        echo $startDate;
                        echo $endDate;
                    }
                    echo "<select class='form-select' name='year' id='selectYear'>";
                    for ($i = -1; $i <= 2; $i++) {
                        $year = date('Y', strtotime("last day of -$i year"));
                        echo "<option value='$year'" . ($year == $selectedYear ? " selected" : "") . ">$year</option>";
                    }
                    echo "</select>";
                ?>
            </div>
        </form> -->
    </div>

    <!-- ตารางข้อมูลพนักงาน -->
    <div class="mt-3 container-fluid">
        <div class="table-responsive">
            <table class="mt-3 my-table" id="leaveEmpTable">
                <thead>
                    <tr class="text-center align-middle">
                        <th rowspan="3">ลำดับ</th>
                        <th rowspan="3">รหัสพนักงาน</th>
                        <th rowspan="3">ชื่อ - นามสกุล</th>
                        <th rowspan="3">แผนก</th>
                        <th rowspan="3">อายุงาน</th>
                        <th rowspan="3">ระดับ</th>
                        <th rowspan="3">สถานที่ทำงาน</th>

                        <th colspan="19" style="background-color: #DCDCDC;">ประเภทการลาและจำนวนวัน</th>
                        <th rowspan="3">รวมวันลาที่ใช้ (ยกเว้นพักร้อน)</th>
                    </tr>
                    <tr class="text-center align-middle">
                        <th colspan="3">ลากิจได้รับค่าจ้าง</th>
                        <th colspan="3">
                            ลากิจไม่ได้รับค่าจ้าง</th>
                        <th colspan="3">ลาป่วย</th>
                        <th colspan="3">ลาป่วยจากงาน</th>
                        <th colspan="3">ลาพักร้อน</th>
                        <th colspan="3">อื่น ๆ</th>
                        <th colspan="1" rowspan="3">มาสาย</th>
                        <!-- <th colspan="1" rowspan="3">ขาดงาน</th> -->
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
                <tbody class="text-center my-table">
                    <?php
                        $sql = "SELECT * FROM employees
                             WHERE e_usercode <> :userCode
                        AND e_level <> :level
                        AND e_workplace = :workplace
                        AND e_status <> '1'
                        AND (
                                (e_sub_department = :subDepart)
                                OR (e_sub_department2 = :subDepart2)
                                OR (e_sub_department3 = :subDepart3)
                                OR (e_sub_department4 = :subDepart4)
                                OR (e_sub_department5 = :subDepart5)
                            )
                        ";
                        $stmt = $conn->prepare($sql);

                        $stmt->bindParam(':userCode', $userCode);
                        $stmt->bindParam(':level', $level);
                        $stmt->bindParam(':workplace', $workplace);
                        $stmt->bindParam(':subDepart', $subDepart);
                        $stmt->bindParam(':subDepart2', $subDepart2);
                        $stmt->bindParam(':subDepart3', $subDepart3);
                        $stmt->bindParam(':subDepart4', $subDepart4);
                        $stmt->bindParam(':subDepart5', $subDepart5);

                        $stmt->execute();

                        $rowNumber = 1;

                        $leave_types = [
                            1 => 'ลากิจได้รับค่าจ้าง',
                            2 => 'ลากิจไม่ได้รับค่าจ้าง',
                            3 => 'ลาป่วย',
                            4 => 'ลาป่วยจากงาน',
                            5 => 'ลาพักร้อน',
                            7 => 'มาสาย',
                            6 => 'ขาดงาน',
                            8 => 'อื่น ๆ',
                        ];

                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo '<tr>';
                            echo '<td>' . $rowNumber . '</td>';
                            echo '<td>' . $row['e_usercode'] . '</td>';
                            echo '<td>' . $row['e_name'] . '</td>';
                            echo '<td>' . $row['e_department'] . '</td>';
                            echo '<td>' . $row['e_yearexp'] . '</td>';
                            echo '<td>' . $row['e_level'] . '</td>';
                            echo '<td>' . $row['e_workplace'] . '</td>';

                            $all_total_days    = 0;
                            $all_total_hours   = 0;
                            $all_total_minutes = 0;

                            foreach ($leave_types as $leave_id => $leave_name) {
                                $sql_leave = "SELECT
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
                        (SELECT e_leave_personal FROM employees WHERE e_usercode = :userCode) AS total_personal,
                        (SELECT e_leave_personal_no FROM employees WHERE e_usercode = :userCode) AS total_personal_no,
                        (SELECT e_leave_sick FROM employees WHERE e_usercode = :userCode) AS total_sick,
                        (SELECT e_leave_sick_work FROM employees WHERE e_usercode = :userCode) AS total_sick_work,
                        (SELECT e_leave_annual FROM employees WHERE e_usercode = :userCode) AS total_annual,
                        (SELECT e_other FROM employees WHERE e_usercode = :userCode) AS total_other,
                        (SELECT COUNT(l_list_id) FROM leave_list WHERE l_leave_id = 7 AND l_usercode = :userCode) AS late_count
                        -- (SELECT l_list_id, l_username FROM leave_list WHERE l_leave_id = 6 AND l_usercode = :userCode) AS stop_work_count
                        FROM leave_list
                        JOIN employees ON employees.e_usercode = leave_list.l_usercode
                        WHERE l_leave_id = :leave_id
                        AND l_usercode = :userCode
                        AND l_leave_status = 0
                        AND l_approve_status IN (2, 6)
                        AND l_approve_status2 IN (4, 6)
                        AND l_approve_status3 IN (8, 6)
                        AND (l_leave_end_date BETWEEN :startDate AND :endDate)";

                                $stmt_leave = $conn->prepare($sql_leave);
                                $stmt_leave->bindParam(':leave_id', $leave_id, PDO::PARAM_INT);
                                $stmt_leave->bindParam(':userCode', $row['e_usercode']);
                                $stmt_leave->bindParam(':startDate', $startDate);
                                $stmt_leave->bindParam(':endDate', $endDate);
                                $stmt_leave->execute();

                                $result_leave = $stmt_leave->fetch(PDO::FETCH_ASSOC);

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
                                    $total_late        = $result_leave['late_count'] ?? 0;
                                    // $total_stop_work   = $result_leave['stop_work_count'] ?? 0;

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

                                    // ลากิจได้รับค่าจ้าง
                                    if ($leave_id == 1) {
                                        $total_minutes_used      = ($days * 8 * 60) + ($hours * 60) + $minutes;
                                        $total_minutes           = $total_personal * 8 * 60;
                                        $total_remaining_minutes = $total_minutes - $total_minutes_used;
                                        $remaining_days          = floor($total_remaining_minutes / (8 * 60));
                                        $remaining_hours         = floor(($total_remaining_minutes % (8 * 60)) / 60);
                                        $remaining_minutes       = $total_remaining_minutes % 60;

                                        if ($remaining_minutes == 30 && $minutes == 30) {
                                            $remaining_minutes = 5;
                                            $minutes           = 5;
                                        } elseif ($remaining_minutes > 30) {
                                            $remaining_minutes = 0;
                                            $remaining_hours += 1;
                                        }

                                        echo '<td>' . $total_personal . '</td>';
                                        echo '<td>' . $days . '(' . $hours . '.' . $minutes . ')' . '</td>';
                                        echo '<td>' . $remaining_days . '(' . $remaining_hours . '.' . $remaining_minutes . ')' . '</td>';
                                    }
                                    // ลากิจไม่ได้รับค่าจ้าง
                                    else if ($leave_id == 2) {
                                        $total_minutes_used = ($days * 8 * 60) + ($hours * 60) + $minutes;

                                        $total_minutes = $total_personal_no * 8 * 60;

                                        $total_remaining_minutes = $total_minutes - $total_minutes_used;

                                        $remaining_days    = floor($total_remaining_minutes / (8 * 60));
                                        $remaining_hours   = floor(($total_remaining_minutes % (8 * 60)) / 60);
                                        $remaining_minutes = $total_remaining_minutes % 60;

                                        if ($remaining_minutes == 30 && $minutes == 30) {
                                            $remaining_minutes = 5;
                                            $minutes           = 5;
                                        } elseif ($remaining_minutes > 30) {
                                            $remaining_minutes = 0;
                                            $remaining_hours += 1;
                                        }

                                        echo '<td>' . $total_personal_no . '</td>';
                                        echo '<td>' . $days . '(' . $hours . '.' . $minutes . ')' . '</td>';
                                        echo '<td>' . $remaining_days . '(' . $remaining_hours . '.' . $remaining_minutes . ')' . '</td>';
                                    }
                                    // ลาป่วย
                                    else if ($leave_id == 3) {

                                        $total_minutes_used = ($days * 8 * 60) + ($hours * 60) + $minutes;

                                        $total_minutes = $total_sick * 8 * 60;

                                        $total_remaining_minutes = $total_minutes - $total_minutes_used;

                                        $remaining_days    = floor($total_remaining_minutes / (8 * 60));
                                        $remaining_hours   = floor(($total_remaining_minutes % (8 * 60)) / 60);
                                        $remaining_minutes = $total_remaining_minutes % 60;

                                        if ($remaining_minutes == 30 && $minutes == 30) {
                                            $remaining_minutes = 5;
                                            $minutes           = 5;
                                        } elseif ($remaining_minutes > 30) {
                                            $remaining_minutes = 0;
                                            $remaining_hours += 1;
                                        }

                                        echo '<td>' . $total_sick . '</td>';
                                        echo '<td>' . $days . '(' . $hours . '.' . $minutes . ')' . '</td>';
                                        echo '<td>' . $remaining_days . '(' . $remaining_hours . '.' . $remaining_minutes . ')' . '</td>';

                                    }
                                    // ลาป่วยจากงาน
                                    else if ($leave_id == 4) {

                                        $total_minutes_used = ($days * 8 * 60) + ($hours * 60) + $minutes;

                                        $total_minutes = $total_sick_work * 8 * 60;

                                        $total_remaining_minutes = $total_minutes - $total_minutes_used;

                                        $remaining_days    = floor($total_remaining_minutes / (8 * 60));
                                        $remaining_hours   = floor(($total_remaining_minutes % (8 * 60)) / 60);
                                        $remaining_minutes = $total_remaining_minutes % 60;

                                        if ($remaining_minutes == 30 && $minutes == 30) {
                                            $remaining_minutes = 5;
                                            $minutes           = 5;
                                        } elseif ($remaining_minutes > 30) {
                                            $remaining_minutes = 0;
                                            $remaining_hours += 1;
                                        }

                                        echo '<td>' . $total_sick_work . '</td>';
                                        echo '<td>' . $days . '(' . $hours . '.' . $minutes . ')' . '</td>';
                                        echo '<td>' . $remaining_days . '(' . $remaining_hours . '.' . $remaining_minutes . ')' . '</td>';

                                    }
                                    // ลาพักร้อน
                                    else if ($leave_id == 5) {

                                        $total_minutes_used = ($days * 8 * 60) + ($hours * 60) + $minutes;

                                        $total_minutes = $total_annual * 8 * 60;

                                        $total_remaining_minutes = $total_minutes - $total_minutes_used;

                                        $remaining_days    = floor($total_remaining_minutes / (8 * 60));
                                        $remaining_hours   = floor(($total_remaining_minutes % (8 * 60)) / 60);
                                        $remaining_minutes = $total_remaining_minutes % 60;

                                        if ($remaining_minutes == 30 && $minutes == 30) {
                                            $remaining_minutes = 5;
                                            $minutes           = 5;
                                        } elseif ($remaining_minutes > 30) {
                                            $remaining_minutes = 0;
                                            $remaining_hours += 1;
                                        }

                                        echo '<td>' . $total_annual . '</td>';
                                        echo '<td>' . $days . '(' . $hours . '.' . $minutes . ')' . '</td>';
                                        echo '<td>' . $remaining_days . '(' . $remaining_hours . '.' . $remaining_minutes . ')' . '</td>';

                                    }
                                    // อื่น ๆ
                                    else if ($leave_id == 8) {
                                        $total_minutes_used = ($days * 8 * 60) + ($hours * 60) + $minutes;

                                        $total_minutes = $total_other * 8 * 60;

                                        $total_remaining_minutes = $total_minutes - $total_minutes_used;

                                        $remaining_days    = floor($total_remaining_minutes / (8 * 60));
                                        $remaining_hours   = floor(($total_remaining_minutes % (8 * 60)) / 60);
                                        $remaining_minutes = $total_remaining_minutes % 60;

                                        if ($remaining_minutes == 30 && $minutes == 30) {
                                            $remaining_minutes = 5;
                                            $minutes           = 5;
                                        } elseif ($remaining_minutes > 30) {
                                            $remaining_minutes = 0;
                                            $remaining_hours += 1;
                                        }

                                        echo '<td>' . $total_other . '</td>';
                                        echo '<td>' . $days . '(' . $hours . '.' . $minutes . ')' . '</td>';
                                        echo '<td>' . $remaining_days . '(' . $remaining_hours . '.' . $remaining_minutes . ')' . '</td>';

                                    }

                                    // ไม่รวมพักร้อนกับมาสาย
                                    if ($leave_id != 5 && $leave_id != 7 && $leave_id != 6) {
                                        $all_total_days += $days;
                                        $all_total_hours += $hours;
                                        $all_total_minutes += $minutes;
                                    }
                                }
                            }

                            echo '<td>' . $total_late . ' ครั้ง' . '</td>';
                            // echo '<td>' . $days . ' วัน' . '</td>';

                            // Display the total leave used, excluding annual leave
                            echo '<td >' . $all_total_days . '(' . $all_total_hours . '.' . $all_total_minutes . ')' . '</td>';
                            echo '</tr>';

                            $rowNumber++;

                        }
                    ?>
                </tbody>
            </table>
        </div>

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
        $("#depSearch").on("keyup", function() {
            var value3 = $(this).val().toLowerCase();
            $("tbody tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value3) > -1);
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

    // วันที่เริ่มต้น
    flatpickr("#startDate", {
        dateFormat: "d-m-Y",
        defaultDate: "<?php echo $startDateFormatted; ?>",
        onChange: function(selectedDates, dateStr, instance) {
            if (selectedDates.length > 0) {
                document.getElementById("startDateText").textContent = instance.formatDate(
                    selectedDates[0],
                    "d-m-Y");
                document.getElementById("dateForm").submit();
            }
        }
    });

    // วันที่สิ้นสุด
    flatpickr("#endDate", {
        dateFormat: "d-m-Y",
        defaultDate: "<?php echo $endDateFormatted; ?>",
        onChange: function(selectedDates, dateStr, instance) {
            if (selectedDates.length > 0) {
                document.getElementById("endDateText").textContent = instance.formatDate(selectedDates[
                        0],
                    "d-m-Y");
                document.getElementById("dateForm").submit();
            }
        }
    });

    // ปุ่ม reset ค่าเริ่มต้น 12-01-ปีที่แล้ว ถึง 11-30-ปีปัจจุบัน
    document.getElementById('resetButton').addEventListener('click', function() {
        const defaultStartDate = "<?php echo date('d-m-Y', strtotime((date('Y') - 1) . '-12-01')); ?>";
        const defaultEndDate = "<?php echo date('d-m-Y', strtotime(date('Y') . '-11-30')); ?>";

        document.getElementById('startDate').value = defaultStartDate;
        document.getElementById('endDate').value = defaultEndDate;

        document.getElementById("dateForm").submit();
    });

    document.addEventListener('DOMContentLoaded', function() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(function(tooltipTriggerEl) {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
    </script>
    <script src="../js/popper.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/bootstrap.bundle.js"></script>
    <script src="../js/bootstrap.bundle.min.js"></script>
</body>

</html>