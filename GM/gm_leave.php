<?php
    session_start();
    date_default_timezone_set('Asia/Bangkok');

    include '../connect.php';

    if (! isset($_SESSION['s_usercode'])) {
        header('Location: ../login.php');
        exit();
    }

    $userCode = $_SESSION['s_usercode'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สถิติการลา</title>

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
    <?php require 'gm_navbar.php'?>
    <nav class="navbar bg-body-tertiary" style="background-color: #072ac8; box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.3);
    border: none;">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-auto">
                    <i class="fa-solid fa-chart-line fa-2xl"></i>
                </div>
                <div class="col-auto">
                    <h3>สถิติการลา</h3>
                </div>
            </div>
        </div>
    </nav>
    <div class="container">
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
        <div class="table-responsive">
            <table class="table table-hover table-bordered" style="border-top: 1px solid rgba(0, 0, 0, 0.1);"
                id="leaveTable">
                <thead>
                    <tr class="table-dark text-center align-middle">
                        <th rowspan="2" style="width: 40%;">ประเภทการลา</th>
                        <th rowspan="1" colspan="3">จำนวนวันลาที่ใช้ไป</th>
                        <th rowspan="1" colspan="3">จำนวนวันลาคงเหลือ</th>
                    </tr>
                    <tr class="table-dark text-center align-middle">
                        <th>วัน</th>
                        <th>ชั่วโมง</th>
                        <th>นาที</th>
                        <th>วัน</th>
                        <th>ชั่วโมง</th>
                        <th>นาที</th>
                    </tr>
                </thead>
                <tbody>
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
                        COUNT(CASE WHEN l_leave_id = 7 THEN 1 END) AS total_late,
                        COUNT(CASE WHEN l_leave_id = 6 THEN 1 END) AS stop_work_count,
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
                    AND l_leave_status = 0
                    AND l_approve_status IN (2, 6)
                    AND l_approve_status2 = 4
                    AND (l_leave_end_date BETWEEN :startDate AND :endDate)";

                            $stmt_leave = $conn->prepare($sql_leave);
                            $stmt_leave->bindParam(':leave_id', $leave_id, PDO::PARAM_INT);
                            $stmt_leave->bindParam(':userCode', $userCode);
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
                                $total_stop_work   = $result_leave['stop_work_count'] ?? 0;

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

                                }
                                // ลากิจไม่ได้รับค่าจ้าง
                                else if ($leave_id == 2) {
                                    $total_minutes_used = ($days * 8 * 60) + ($hours * 60) + $minutes;

                                    $total_minutes = $total_personal_no * 8 * 60;

                                    $total_remaining_minutes = $total_minutes - $total_minutes_used;

                                    $remaining_days    = floor($total_remaining_minutes / (8 * 60));
                                    $remaining_hours   = floor(($total_remaining_minutes % (8 * 60)) / 60);
                                    $remaining_minutes = $total_remaining_minutes % 60;
                                }
                                // ลาป่วย
                                else if ($leave_id == 3) {

                                    $total_minutes_used = ($days * 8 * 60) + ($hours * 60) + $minutes;

                                    $total_minutes = $total_sick * 8 * 60;

                                    $total_remaining_minutes = $total_minutes - $total_minutes_used;

                                    $remaining_days    = floor($total_remaining_minutes / (8 * 60));
                                    $remaining_hours   = floor(($total_remaining_minutes % (8 * 60)) / 60);
                                    $remaining_minutes = $total_remaining_minutes % 60;
                                }
                                // ลาป่วยจากงาน
                                else if ($leave_id == 4) {

                                    $total_minutes_used = ($days * 8 * 60) + ($hours * 60) + $minutes;

                                    $total_minutes = $total_sick_work * 8 * 60;

                                    $total_remaining_minutes = $total_minutes - $total_minutes_used;

                                    $remaining_days    = floor($total_remaining_minutes / (8 * 60));
                                    $remaining_hours   = floor(($total_remaining_minutes % (8 * 60)) / 60);
                                    $remaining_minutes = $total_remaining_minutes % 60;
                                }
                                // ลาพักร้อน
                                else if ($leave_id == 5) {

                                    $total_minutes_used = ($days * 8 * 60) + ($hours * 60) + $minutes;

                                    $total_minutes = $total_annual * 8 * 60;

                                    $total_remaining_minutes = $total_minutes - $total_minutes_used;

                                    $remaining_days    = floor($total_remaining_minutes / (8 * 60));
                                    $remaining_hours   = floor(($total_remaining_minutes % (8 * 60)) / 60);
                                    $remaining_minutes = $total_remaining_minutes % 60;

                                }
                                // ขาดงาน
                                else if ($leave_id == 6) {

                                    $days = $total_stop_work;

                                }
                                // มาสาย
                                else if ($leave_id == 7) {

                                    $days = $total_late;

                                }
                                // อื่น ๆ
                                else if ($leave_id == 8) {
                                    $total_minutes_used = ($days * 8 * 60) + ($hours * 60) + $minutes;

                                    $total_minutes = $total_other * 8 * 60;

                                    $total_remaining_minutes = $total_minutes - $total_minutes_used;

                                    $remaining_days    = floor($total_remaining_minutes / (8 * 60));
                                    $remaining_hours   = floor(($total_remaining_minutes % (8 * 60)) / 60);
                                    $remaining_minutes = $total_remaining_minutes % 60;
                                } else {
                                    $remaining_days = '-';
                                }

                                // ไม่รวมพักร้อนกับมาสาย
                                if ($leave_id != 5 && $leave_id != 7) {
                                    $all_total_days += $days;
                                    $all_total_hours += $hours;
                                    $all_total_minutes += $minutes;
                                }

                                if ($all_total_minutes >= 60) {
                                    $all_total_hours += floor($all_total_minutes / 60);
                                    $all_total_minutes = $all_total_minutes % 60;
                                }

                                echo '<tr class="text-center align-middle">';
                                echo '<td style="font-weight: bold;">' . $leave_name . '</td>';

                                if ($leave_id == 6) {
                                    echo '<td colspan="3">' . $days . ' วัน' . '</td>';
                                    echo '<td colspan="3">-</td>';

                                } else if ($leave_id == 7) {
                                    echo '<td colspan="3">' . $days . ' ครั้ง' . '</td>';
                                    echo '<td colspan="3">-</td>';

                                } else {
                                    echo '<td>' . $days . '</td>';
                                    echo '<td>' . $hours . '</td>';
                                    echo '<td>' . $minutes . '</td>';
                                    echo '<td>' . $remaining_days . '</td>';
                                    echo '<td>' . $remaining_hours . '</td>';
                                    echo '<td>' . $remaining_minutes . '</td>';

                                }

                                echo '</tr>';
                            }

                        }
                        echo '<tr class="text-center align-middle">';
                        echo '<td style="font-weight: bold;">รวมจำนวนวันลาทั้งหมด (ยกเว้นลาพักร้อน)</td>';
                        echo '<td colspan="6" style="font-weight: bold;">' . $all_total_days . ' วัน ' . $all_total_hours . ' ชั่วโมง ' . $all_total_minutes . ' นาที' . '</td>';
                        echo '</tr>';

                    ?>
                </tbody>
            </table>
        </div>
        <script>
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

        // เปิดใช้งาน Tooltip เมื่อหน้าเว็บโหลดเสร็จ
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