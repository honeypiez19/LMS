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
    <?php require 'admin_navbar.php'?>
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
                    $startDate = date("Y-m-d", strtotime((date('Y') - 1) . "-12-01")); // 1 ธันวาคม ปีที่แล้ว
                    $endDate   = date("Y-m-d", strtotime(date('Y') . "-11-30"));       // 30 พฤศจิกายน ปีปัจจุบัน

                    // หากมีการส่งข้อมูลจากฟอร์ม
                    if (isset($_POST['start_date']) && isset($_POST['end_date'])) {
                        $startDate = $_POST['start_date'];
                        $endDate   = $_POST['end_date'];
                    }
                ?>
                <input type="text" name="start_date" class="form-control" id="startDate"
                    value="<?php echo date("d-m-Y", strtotime($startDate)); ?>"> <!-- แสดง d-m-Y -->
                <!-- <input type="text" name="start_date" class="form-control" id="startDate"
                    value="<?php echo $startDate; ?>"> -->
            </div>
            <div class="col-auto">
                <input type="text" name="end_date" class="form-control" id="endDate"
                    value="<?php echo date("d-m-Y", strtotime($endDate)); ?>"> <!-- แสดง d-m-Y -->
            </div>
        </form>

        <!-- แสดงช่วงวันที่ที่เลือก -->
        <div>
            <p>ช่วงวันที่: <strong id="startDateText">
                    <?php
                        $startDateObj = DateTime::createFromFormat('Y-m-d', $startDate);
                        echo $startDateObj->format('d-m-Y'); // แสดงวันที่ในรูปแบบ dd-mm-yyyy
                    ?>
                </strong> ถึง <strong id="endDateText">
                    <?php
                        $endDateObj = DateTime::createFromFormat('Y-m-d', $endDate);
                        echo $endDateObj->format('d-m-Y'); // แสดงวันที่ในรูปแบบ dd-mm-yyyy
                    ?>
                </strong>
            </p>
        </div>

        <table class="mt-3 table table-hover table-bordered" style="border-top: 1px solid rgba(0, 0, 0, 0.1);"
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

                    $total_days    = 0;
                    $total_hours   = 0;
                    $total_minutes = 0;

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
                            (SELECT COUNT(l_list_id) FROM leave_list WHERE l_leave_id = 7 AND l_usercode = :userCode) AS late_count,
                            (SELECT COUNT(l_list_id) FROM leave_list WHERE l_leave_id = 6 AND l_usercode = :userCode) AS stop_work_count
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

                            // Employee leave balances
                            $total_personal    = $result_leave['total_personal'] ?? 0;
                            $total_personal_no = $result_leave['total_personal_no'] ?? 0;
                            $total_sick        = $result_leave['total_sick'] ?? 0;
                            $total_sick_work   = $result_leave['total_sick_work'] ?? 0;
                            $total_annual      = $result_leave['total_annual'] ?? 0;
                            $total_other       = $result_leave['total_other'] ?? 0;
                            $total_late        = $result_leave['late_count'] ?? 0;
                            $total_stop_work   = $result_leave['stop_work_count'] ?? 0;

                            // Convert hours to days if applicable
                            $days += floor($hours / 8);
                            $hours = $hours % 8;

                            // Adjust minutes if necessary
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

                            if ($leave_id == 1) {
                                                                                                   // คำนวณจำนวนวันที่ใช้ลาในนาที
                                $total_minutes_used = ($days * 8 * 60) + ($hours * 60) + $minutes; // แปลงทั้งหมดเป็นนาที

                                                                           // คำนวณจำนวนวันลาในนาที
                                $total_minutes = $total_personal * 8 * 60; // จำนวนวันทั้งหมดในนาที

                                // คำนวณนาทีที่เหลือ
                                $total_remaining_minutes = $total_minutes - $total_minutes_used;

                                $remaining_days    = floor($total_remaining_minutes / (8 * 60));        // วัน
                                $remaining_hours   = floor(($total_remaining_minutes % (8 * 60)) / 60); // ชั่วโมง
                                $remaining_minutes = $total_remaining_minutes % 60;                     // นาที

                                // แสดงผลลัพธ์ในตาราง
                                echo '<tr class="text-center align-middle">';
                                echo '<td>' . htmlspecialchars($leave_name) . '</td>';
                                echo '<td>' . $days . '</td>';
                                echo '<td>' . $hours . '</td>';
                                echo '<td>' . $minutes . '</td>';
                                echo '<td>' . $remaining_days . '</td>';    // แสดงวันที่เหลือ
                                echo '<td>' . $remaining_hours . '</td>';   // แสดงชั่วโมงที่เหลือ
                                echo '<td>' . $remaining_minutes . '</td>'; // แสดงนาทีที่เหลือ
                                echo '</tr>';

                                // สะสมผลรวมวัน, ชั่วโมง, นาที
                                $total_days += $days;
                                $total_hours += $hours;
                                $total_minutes += $minutes;
                            } else if ($leave_id == 2) {
                                                                                                   // คำนวณจำนวนวันที่ใช้ลาในนาที
                                $total_minutes_used = ($days * 8 * 60) + ($hours * 60) + $minutes; // แปลงทั้งหมดเป็นนาที
                                                                                                   // คำนวณจำนวนวันลาในนาที
                                $total_minutes = $total_personal_no * 8 * 60;                      // จำนวนวันทั้งหมดในนาที

                                // คำนวณนาทีที่เหลือ
                                $total_remaining_minutes = $total_minutes - $total_minutes_used;

                                $remaining_days    = floor($total_remaining_minutes / (8 * 60));        // วัน
                                $remaining_hours   = floor(($total_remaining_minutes % (8 * 60)) / 60); // ชั่วโมง
                                $remaining_minutes = $total_remaining_minutes % 60;                     // นาที

                                // แสดงผลลัพธ์ในตาราง
                                echo '<tr class="text-center align-middle">';
                                echo '<td>' . htmlspecialchars($leave_name) . '</td>';
                                echo '<td>' . $days . '</td>';
                                echo '<td>' . $hours . '</td>';
                                echo '<td>' . $minutes . '</td>';
                                echo '<td>' . $remaining_days . '</td>';    // แสดงวันที่เหลือ
                                echo '<td>' . $remaining_hours . '</td>';   // แสดงชั่วโมงที่เหลือ
                                echo '<td>' . $remaining_minutes . '</td>'; // แสดงนาทีที่เหลือ
                                echo '</tr>';

                                $total_days += $days;
                                $total_hours += $hours;
                                $total_minutes += $minutes;

                            } else if ($leave_id == 3) {
                                                                                                   // คำนวณจำนวนวันที่ใช้ลาในนาที
                                $total_minutes_used = ($days * 8 * 60) + ($hours * 60) + $minutes; // แปลงทั้งหมดเป็นนาที
                                                                                                   // คำนวณจำนวนวันลาในนาที
                                $total_minutes = $total_sick * 8 * 60;                             // จำนวนวันทั้งหมดในนาที

                                // คำนวณนาทีที่เหลือ
                                $total_remaining_minutes = $total_minutes - $total_minutes_used;

                                $remaining_days    = floor($total_remaining_minutes / (8 * 60));        // วัน
                                $remaining_hours   = floor(($total_remaining_minutes % (8 * 60)) / 60); // ชั่วโมง
                                $remaining_minutes = $total_remaining_minutes % 60;                     // นาที

                                // แสดงผลลัพธ์ในตาราง
                                echo '<tr class="text-center align-middle">';
                                echo '<td>' . htmlspecialchars($leave_name) . '</td>';
                                echo '<td>' . $days . '</td>';
                                echo '<td>' . $hours . '</td>';
                                echo '<td>' . $minutes . '</td>';
                                echo '<td>' . $remaining_days . '</td>';    // แสดงวันที่เหลือ
                                echo '<td>' . $remaining_hours . '</td>';   // แสดงชั่วโมงที่เหลือ
                                echo '<td>' . $remaining_minutes . '</td>'; // แสดงนาทีที่เหลือ
                                echo '</tr>';

                                $total_days += $days;
                                $total_hours += $hours;
                                $total_minutes += $minutes;

                            } else if ($leave_id == 4) {
                                                                                                   // คำนวณจำนวนวันที่ใช้ลาในนาที
                                $total_minutes_used = ($days * 8 * 60) + ($hours * 60) + $minutes; // แปลงทั้งหมดเป็นนาที
                                                                                                   // คำนวณจำนวนวันลาในนาที
                                $total_minutes = $total_sick_work * 8 * 60;                        // จำนวนวันทั้งหมดในนาที

                                // คำนวณนาทีที่เหลือ
                                $total_remaining_minutes = $total_minutes - $total_minutes_used;

                                $remaining_days    = floor($total_remaining_minutes / (8 * 60));        // วัน
                                $remaining_hours   = floor(($total_remaining_minutes % (8 * 60)) / 60); // ชั่วโมง
                                $remaining_minutes = $total_remaining_minutes % 60;                     // นาที

                                // แสดงผลลัพธ์ในตาราง
                                echo '<tr class="text-center align-middle">';
                                echo '<td>' . htmlspecialchars($leave_name) . '</td>';
                                echo '<td>' . $days . '</td>';
                                echo '<td>' . $hours . '</td>';
                                echo '<td>' . $minutes . '</td>';
                                echo '<td>' . $remaining_days . '</td>';    // แสดงวันที่เหลือ
                                echo '<td>' . $remaining_hours . '</td>';   // แสดงชั่วโมงที่เหลือ
                                echo '<td>' . $remaining_minutes . '</td>'; // แสดงนาทีที่เหลือ
                                echo '</tr>';

                                $total_days += $days;
                                $total_hours += $hours;
                                $total_minutes += $minutes;

                            } else if ($leave_id == 5) {
                                                                                                   // คำนวณจำนวนวันที่ใช้ลาในนาที
                                $total_minutes_used = ($days * 8 * 60) + ($hours * 60) + $minutes; // แปลงทั้งหมดเป็นนาที
                                                                                                   // คำนวณจำนวนวันลาในนาที
                                $total_minutes = $total_annual * 8 * 60;                           // จำนวนวันทั้งหมดในนาที

                                // คำนวณนาทีที่เหลือ
                                $total_remaining_minutes = $total_minutes - $total_minutes_used;

                                $remaining_days    = floor($total_remaining_minutes / (8 * 60));        // วัน
                                $remaining_hours   = floor(($total_remaining_minutes % (8 * 60)) / 60); // ชั่วโมง
                                $remaining_minutes = $total_remaining_minutes % 60;                     // นาที

                                // แสดงผลลัพธ์ในตาราง
                                echo '<tr class="text-center align-middle">';
                                echo '<td>' . htmlspecialchars($leave_name) . '</td>';
                                echo '<td>' . $days . '</td>';
                                echo '<td>' . $hours . '</td>';
                                echo '<td>' . $minutes . '</td>';
                                echo '<td>' . $remaining_days . '</td>';    // แสดงวันที่เหลือ
                                echo '<td>' . $remaining_hours . '</td>';   // แสดงชั่วโมงที่เหลือ
                                echo '<td>' . $remaining_minutes . '</td>'; // แสดงนาทีที่เหลือ
                                echo '</tr>';

                                $total_days += $days;

                            } else if ($leave_id == 6) {
                                echo '<tr class="text-center align-middle">';
                                echo '<td>' . 'ขาดงาน' . '</td>';
                                echo '<td colspan="3">' . $total_stop_work . ' ครั้ง</td>';
                                echo '<td colspan="3"  class="text-color-custom">' . '-' . '</td>';
                                echo '</tr>';
                            } else if ($leave_id == 7) {
                                echo '<tr class="text-center align-middle">';
                                echo '<td>' . 'มาสาย' . '</td>';
                                echo '<td colspan="3">' . $total_late . ' ครั้ง</td>';
                                echo '<td colspan="3"  class="text-color-custom">' . '-' . '</td>';
                                echo '</tr>';
                            } else if ($leave_id == 8) {
                                $total_minutes_used = ($days * 8 * 60) + ($hours * 60) + $minutes; // แปลงทั้งหมดเป็นนาที
                                                                                                   // คำนวณจำนวนวันลาในนาที
                                $total_minutes = $total_other * 8 * 60;                            // จำนวนวันทั้งหมดในนาที

                                // คำนวณนาทีที่เหลือ
                                $total_remaining_minutes = $total_minutes - $total_minutes_used;

                                $remaining_days    = floor($total_remaining_minutes / (8 * 60));        // วัน
                                $remaining_hours   = floor(($total_remaining_minutes % (8 * 60)) / 60); // ชั่วโมง
                                $remaining_minutes = $total_remaining_minutes % 60;                     // นาที

                                // แสดงผลลัพธ์ในตาราง
                                echo '<tr class="text-center align-middle">';
                                echo '<td>' . htmlspecialchars($leave_name) . '</td>';
                                echo '<td>' . $days . '</td>';
                                echo '<td>' . $hours . '</td>';
                                echo '<td>' . $minutes . '</td>';
                                echo '<td>' . $remaining_days . '</td>';    // แสดงวันที่เหลือ
                                echo '<td>' . $remaining_hours . '</td>';   // แสดงชั่วโมงที่เหลือ
                                echo '<td>' . $remaining_minutes . '</td>'; // แสดงนาทีที่เหลือ
                                echo '</tr>';

                                $total_days += $days;
                                $total_hours += $hours;
                                $total_minutes += $minutes;

                            }
                        }
                    }
                    echo '<tr class="text-center align-middle">';
                    echo '<td style="font-weight: bold;">' . 'รวมจำนวนวันลาทั้งหมด (ยกเว้นลาพักร้อน / อื่น ๆ)' . '</td>';
                    echo '<td colspan="6" style="font-weight: bold;">' . $total_days . ' วัน ' . $total_hours . ' ชั่วโมง ' . $total_minutes . ' นาที' . '</td>';
                    echo '</tr>';
                ?>
            </tbody>
        </table>
    </div>
    <script>
    flatpickr("#startDate", {
        dateFormat: "Y-m-d", // ใช้เก็บในรูปแบบ yyyy-mm-dd
        altFormat: "d-m-Y", // แสดงในรูปแบบ dd-mm-yyyy
        defaultDate: "<?php echo $startDate; ?>",
        onChange: function(selectedDates, dateStr, instance) {
            // แสดงวันที่ในรูปแบบ dd-mm-yyyy พร้อมเดือนเป็นตัวเลขสองหลัก
            document.getElementById("startDateText").textContent = instance.formatDate(selectedDates[0],
                "d-m-Y");
            document.getElementById("dateForm").submit();
        }
    });

    flatpickr("#endDate", {
        dateFormat: "Y-m-d", // ใช้เก็บในรูปแบบ yyyy-mm-dd
        altFormat: "d-m-Y", // แสดงในรูปแบบ dd-mm-yyyy
        defaultDate: "<?php echo $endDate; ?>",
        onChange: function(selectedDates, dateStr, instance) {
            // แสดงวันที่ในรูปแบบ dd-mm-yyyy พร้อมเดือนเป็นตัวเลขสองหลัก
            document.getElementById("endDateText").textContent = instance.formatDate(selectedDates[0],
                "d-m-Y");
            document.getElementById("dateForm").submit();
        }
    });
    </script>
    <script src="../js/popper.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/bootstrap.bundle.js"></script>
    <script src="../js/bootstrap.bundle.min.js"></script>
</body>

</html>