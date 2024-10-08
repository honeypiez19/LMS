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
    <title>การลาของพนักงาน</title>

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
    <?php include 'gm_navbar.php'?>

    <nav class="navbar bg-body-tertiary">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-auto">
                    <i class="fa-solid fa-folder-open fa-2xl"></i>
                </div>
                <div class="col-auto">
                    <h3>การลาของพนักงาน</h3>
                </div>
            </div>
        </div>
    </nav>

    <div class="mt-3 container">
        <div class="row">
            <div class="col-3">
                <label for="userCodeLabel" class="form-label">รหัสพนักงาน</label>
                <input type="text" class="form-control" id="codeSearch" list="codeList">
                <datalist id="codeList">
                    <?php
$sql = "SELECT * FROM employees WHERE e_status <> '1' AND e_department = 'Office'";
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
$sql = "SELECT * FROM employees WHERE e_status <> '1' AND e_department = 'Office'";
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
$sql = "SELECT DISTINCT e_department FROM employees WHERE e_status <> '1' AND e_department = 'Office'";
$result = $conn->query($sql);
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    echo '<option value="' . $row['e_department'] . '">';
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
                    <th colspan="20" style="background-color: #DCDCDC;">ประเภทการลาและจำนวนวัน</th>
                    <th rowspan="3">รวมวันลาที่ใช้ (ยกเว้นพักร้อน)</th>
                </tr>
                <tr class="text-center align-middle">
                    <th colspan="3">ลากิจได้รับค่าจ้าง</th>
                    <th colspan="3">ลากิจไม่ได้รับค่าจ้าง</th>
                    <th colspan="3">ลาป่วย</th>
                    <th colspan="3">ลาป่วยจากงาน</th>
                    <th colspan="3">ลาพักร้อน</th>
                    <th colspan="3">อื่น ๆ (ระบุ)</th>
                    <th colspan="1">มาสาย</th>
                    <th colspan="1">หยุดงาน</th>
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
                    <th>ครั้ง</th>
                    <th>วัน</th>
                </tr>
            </thead>
            <!-- เนื้อหาของตาราง -->
            <tbody class="text-center my-table">
                <?php
$sql = "SELECT * FROM employees WHERE e_status <> '1'
AND e_department = 'Office' AND e_level <> 'manager'";
$result = $conn->query($sql);

$rowNumber = 1;

while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    echo '<tr>';
    echo '<td>' . $rowNumber . '</td>';
    echo '<td>' . $row['e_usercode'] . '</td>';
    echo '<td>' . $row['e_name'] . '</td>';
    echo '<td>' . $row['e_department'] . '</td>';
    echo '<td>' . $row['e_yearexp'] . '</td>';
    echo '<td>' . $row['e_level'] . '</td>';

    $selectedYear = date('Y');
    $sql_leave = "WITH leave_chk AS (
    SELECT
        l_leave_id,
        DATEDIFF(l_leave_end_date, l_leave_start_date) AS diff_days,
        TIMEDIFF(l_leave_end_time, l_leave_start_time) AS diff_time,
        CASE
            WHEN DATEDIFF(l_leave_end_date, l_leave_start_date) = 0 THEN
                CASE
                    WHEN TIMEDIFF(l_leave_end_time, l_leave_start_time) = '08:40:00' THEN 8
                    WHEN TIMEDIFF(l_leave_end_time, l_leave_start_time) IN ('07:30:00', '07:00:00', '06:30:00', '06:00:00', '05:30:00', '05:00:00', '03:30:00', '03:00:00', '02:30:00', '02:00:00', '01:30:00', '01:00:00', '00:30:00') THEN ROUND(TIME_TO_SEC(TIMEDIFF(l_leave_end_time, l_leave_start_time)) / 3600, 1)
                    WHEN TIMEDIFF(l_leave_end_time, l_leave_start_time) IN ('03:45:00', '03:55:00') THEN 4
                    ELSE 1
                END
            WHEN TIMEDIFF(l_leave_end_time, l_leave_start_time) = '08:40:00' THEN DATEDIFF(l_leave_end_date, l_leave_start_date) * 8
            WHEN (TIME(l_leave_start_time) >= '08:00:00' AND TIME(l_leave_end_time) <= '11:45:00') OR (TIME(l_leave_start_time) >= '12:45:00' AND TIME(l_leave_end_time) <= '16:40:00') THEN (DATEDIFF(l_leave_end_date, l_leave_start_date) + 1) * 8 + 4
            ELSE (DATEDIFF(l_leave_end_date, l_leave_start_date) + 1) * 8
        END AS calculate_time
    FROM leave_list
    WHERE YEAR(l_leave_start_date) = :selectedYear
    AND NOT (TIME(l_leave_start_time) >= '11:45:00' AND TIME(l_leave_end_time) <= '12:45:00')
    AND l_leave_status = '0'
    AND l_usercode = :userCode
)

SELECT
    SUM(CASE WHEN l_leave_id = '1' AND diff_days BETWEEN 0 AND 5 THEN calculate_time ELSE 0 END) AS leave_personal_count,
    (SELECT e_leave_personal FROM employees WHERE e_usercode = :userCode AND e_status <> '1') AS total_personal,

    SUM(CASE WHEN l_leave_id = '2' AND diff_days BETWEEN 0 AND 365 THEN calculate_time ELSE 0 END) AS leave_personal_no_count,
    (SELECT e_leave_personal_no FROM employees WHERE e_usercode = :userCode AND e_status <> '1') AS total_personal_no,

    SUM(CASE WHEN l_leave_id = '3' AND diff_days BETWEEN 0 AND 30 THEN calculate_time ELSE 0 END) AS leave_sick_count,
    (SELECT e_leave_sick FROM employees WHERE e_usercode = :userCode AND e_status <> '1') AS total_sick,

    SUM(CASE WHEN l_leave_id = '4' AND diff_days BETWEEN 0 AND 365 THEN calculate_time ELSE 0 END) AS leave_sick_work_count,
    (SELECT e_leave_sick_work FROM employees WHERE e_usercode = :userCode AND e_status <> '1') AS total_leave_sick_work,

    SUM(CASE WHEN l_leave_id = '5' AND diff_days BETWEEN 0 AND 10 THEN calculate_time ELSE 0 END) AS leave_annual_count,
    (SELECT e_leave_annual FROM employees WHERE e_usercode = :userCode AND e_status <> '1') AS total_annual,

    SUM(CASE WHEN l_leave_id = '8' AND diff_days BETWEEN 0 AND 365 THEN calculate_time ELSE 0 END) AS other_count,
    (SELECT e_other FROM employees WHERE e_usercode = :userCode AND e_status <> '1') AS total_other,

    SUM(CASE WHEN l_leave_id = '7' THEN 1 ELSE 0 END) AS late_count
    FROM leave_chk
    WHERE l_leave_id IN ('1', '2', '3', '4', '5', '7', '8')";

    $stmt_leave = $conn->prepare($sql_leave);
    $stmt_leave->bindParam(':userCode', $row['e_usercode']);
    $stmt_leave->bindParam(':selectedYear', $selectedYear);
    $stmt_leave->execute();
    $result_leave = $stmt_leave->fetch(PDO::FETCH_ASSOC);

    $total_personal = $result_leave['total_personal'];
    $leave_personal_hours = $result_leave['leave_personal_count'];
    $leave_personal_days = floor($leave_personal_hours / 8); // หาจำนวนวันที่เหลือ
    $leave_personal_hours_remain = $leave_personal_hours % 8; // หาจำนวนชั่วโมงที่เหลือไม่เอาเศษ
    $leave_personal_hours_remain2 = fmod($leave_personal_hours, 8); // หาจำนวนชั่วโมงที่เหลือเอาเศษ

// echo $leave_personal_hours;
    if (in_array($leave_personal_hours_remain2, [0.5, 1.5, 2.5, 3.5, 4.5, 5.5, 6.5, 7.5, 8.5, 9.5, 10.5])) {
        $leave_personal_minutes_remain = 5; // 30 นาที คือ 0.5
    } else {
        $leave_personal_minutes_remain = 0;
    }

    $total_personal_no = $result_leave['total_personal_no'];
    $leave_personal_no_hours = $result_leave['leave_personal_no_count'];
    $leave_personal_no_days = floor($leave_personal_no_hours / 8);
    $leave_personal_no_hours_remain = $leave_personal_no_hours % 8;
    $leave_personal_no_hours_remain2 = fmod($leave_personal_no_hours, 8);
    if (in_array($leave_personal_no_hours_remain2, [0.5, 1.5, 2.5, 3.5, 4.5, 5.5, 6.5, 7.5, 8.5, 9.5, 10.5])) {
        $leave_personal_no_minutes_remain = 5; // 30 นาที คือ 0.5
    } else {
        $leave_personal_no_minutes_remain = 0;
    }

    $total_sick = $result_leave['total_sick'];
    $leave_sick_hours = $result_leave['leave_sick_count'];
    $leave_sick_days = floor($leave_sick_hours / 8);
    $leave_sick_hours_remain = $leave_sick_hours % 8;
    $leave_sick_hours_remain2 = fmod($leave_sick_hours, 8); // หาจำนวนชั่วโมงที่เหลือเอาเศษ

    // echo $leave_sick_hours;
    if (in_array($leave_sick_hours_remain2, [0.5, 1.5, 2.5, 3.5, 4.5, 5.5, 6.5, 7.5, 8.5, 9.5, 10.5])) {
        $leave_sick_minutes_remain = 5; // 30 นาที คือ 0.5
    } else {
        $leave_sick_minutes_remain = 0;
    }

    $total_sick_work = $result_leave['total_leave_sick_work'];
    $leave_sick_work_hours = $result_leave['leave_sick_work_count'];
    $leave_sick_work_days = floor($leave_sick_work_hours / 8); // หาจำนวนวันที่เหลือ
    $leave_sick_work_hours_remain = $leave_sick_work_hours % 8; // หาจำนวนชั่วโมงที่เหลือไม่เอาเศษ
    $leave_sick_work_hours_remain2 = fmod($leave_sick_work_hours, 8); // หาจำนวนชั่วโมงที่เหลือเอาเศษ

    // echo $leave_sick_hours;
    if (in_array($leave_sick_work_hours_remain2, [0.5, 1.5, 2.5, 3.5, 4.5, 5.5, 6.5, 7.5, 8.5, 9.5, 10.5])) {
        $leave_sick_work_minutes_remain = 5; // 30 นาที คือ 0.5
    } else {
        $leave_sick_work_minutes_remain = 0;
    }

    $total_annual = $result_leave['total_annual'];
    $leave_annual_hours = $result_leave['leave_annual_count'];
    $leave_annual_days = floor($leave_annual_hours / 8); // หาจำนวนวันที่เหลือ
    $leave_annual_hours_remain = $leave_annual_hours % 8; // หาจำนวนชั่วโมงที่เหลือไม่เอาเศษ
    $leave_annual_hours_remain2 = fmod($leave_annual_hours, 8); // หาจำนวนชั่วโมงที่เหลือเอาเศษ

    // คำนวณนาทีที่เหลือ
    if (in_array($leave_annual_hours_remain2, [0.5, 1.5, 2.5, 3.5, 4.5, 5.5, 6.5, 7.5, 8.5, 9.5, 10.5])) {
        $leave_annual_minutes_remain = 5; // 30 นาที คือ 0.5
    } else {
        $leave_annual_minutes_remain = 0;
    }

    $total_other = $result_leave['total_other'];
    $other_hours = $result_leave['other_count'];
    $other_days = floor($other_hours / 8); // หาจำนวนวันที่เหลือ
    $other_hours_remain = $other_hours % 8; // หาจำนวนชั่วโมงที่เหลือไม่เอาเศษ
    $other_hours_remain2 = fmod($leave_sick_hours, 8); // หาจำนวนชั่วโมงที่เหลือเอาเศษ

// echo $leave_sick_hours;
    if (in_array($other_hours_remain2, [0.5, 1.5, 2.5, 3.5, 4.5, 5.5, 6.5, 7.5, 8.5, 9.5, 10.5])) {
        $other_minutes_remain = 5; // 30 นาที คือ 0.5
    } else {
        $other_minutes_remain = 0;
    }

    $late_count = $result_leave['late_count'] ?? 0;
    $stop_work = 0;

    if ($late_count >= 3) {
        $stop_work = floor($late_count / 3); // คำนวณจำนวนวัน
    }

    $total_personal_remaining_days = max($total_personal - $leave_personal_days, 0);

    echo '<td>' . $total_personal . '</td>';
    echo '<td>' . $leave_personal_days . '(' . $leave_personal_hours_remain . '.' . $leave_personal_minutes_remain . ')' . '</td>';
    echo '<td>' . $total_personal_remaining_days . '</td>';

    $total_personal_no_remaining_days = max($total_personal_no - $leave_personal_no_days, 0);

    echo '<td>' . $total_personal_no . '</td>';
    echo '<td>' . $leave_personal_no_days . '(' . $leave_personal_no_hours_remain . '.' . $leave_personal_no_minutes_remain . ')' . '</td>';
    echo '<td>' . $total_personal_no_remaining_days . '</td>';

    $total_sick_remaining_days = max($total_sick - $leave_sick_days, 0);

    echo '<td>' . $total_sick . '</td>';
    echo '<td>' . $leave_sick_days . '(' . $leave_sick_hours_remain . '.' . $leave_sick_minutes_remain . ')' . '</td>';
    echo '<td>' . $total_sick_remaining_days . '</td>';

    $total_sick_work_remaining_days = max($total_sick_work - $leave_sick_work_days, 0);

    echo '<td>' . $total_sick_work . '</td>';
    echo '<td>' . $leave_sick_work_days . '(' . $leave_sick_work_hours_remain . '.' . $leave_sick_work_minutes_remain . ')' . '</td>';
    echo '<td>' . $total_sick_work_remaining_days . '</td>';

    $total_annual_remaining_days = max($total_annual - $leave_annual_days, 0);

    echo '<td>' . $total_annual . '</td>';
    echo '<td>' . $leave_annual_days . '(' . $leave_annual_hours_remain . '.' . $leave_annual_minutes_remain . ')' . '</td>';
    echo '<td>' . $total_annual_remaining_days . '</td>';

    $total_other_remaining_days = max($total_other - $other_days, 0);

    echo '<td>' . $total_other . '</td>';
    echo '<td>' . $other_days . '(' . $other_hours_remain . '.' . $other_minutes_remain . ')' . '</td>';
    echo '<td>' . $total_other_remaining_days . '</td>';

    // echo "Total Late Count: " . $result_leave['late_count'];

    echo '<td>' . $late_count . '</td>';
    echo '<td>' . $stop_work . '</td>';

    $sum_day = $leave_personal_days + $leave_personal_no_days + $leave_sick_days + $leave_sick_work_days + $stop_work;
    $sum_hours = $leave_personal_hours_remain + $leave_personal_no_hours_remain + $leave_sick_hours_remain + $leave_sick_work_hours_remain;
    $sum_minutes = $leave_personal_minutes_remain + $leave_personal_no_minutes_remain + $leave_sick_minutes_remain + $leave_sick_work_minutes_remain;

    echo '<td>' . $sum_day . '(' . $sum_hours . '.' . $sum_minutes . ')' . '</td>';

    echo '</tr>';
    $rowNumber++;
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