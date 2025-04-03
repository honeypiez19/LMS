<?php
    header('Content-Type: text/html; charset=utf-8');
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
    <title>สถิติการลาของพนักงาน</title>

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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <style>
    body {
        font-family: 'THSarabunNew', sans-serif;
    }

    .my-table {
        /* width: 100%; */
        border-collapse: collapse;
    }

    .my-table th,
    .my-table td {
        font-family: 'THSarabunNew', sans-serif;

        border: 1px solid #ddd;
        padding: 8px;
    }

    .my-table tbody tr:hover {
        background-color: #f5f5f5;
    }
    </style>

</head>

<body>
    <?php include 'admin_navbar.php';

        // Date range selection section
        $currentYear = date('Y');
        $lastYear    = $currentYear - 1;

        // Set default date range: Dec 31 of last year to Nov 30 of current year
        $defaultStartDate = date('Y-m-d', strtotime($lastYear . '-12-01'));
        $defaultEndDate   = date('Y-m-d', strtotime($currentYear . '-11-30'));

        // Format for display
        $defaultStartDateFormatted = date('d-m-Y', strtotime($defaultStartDate));
        $defaultEndDateFormatted   = date('d-m-Y', strtotime($defaultEndDate));

        // Get dates from POST if submitted
        if (isset($_POST['start_date']) && isset($_POST['end_date'])) {
            $startDateFormatted = $_POST['start_date'];
            $endDateFormatted   = $_POST['end_date'];

            // Convert to Y-m-d format for database queries
            $startDate = DateTime::createFromFormat('d-m-Y', $startDateFormatted)->format('Y-m-d');
            $endDate   = DateTime::createFromFormat('d-m-Y', $endDateFormatted)->format('Y-m-d');
        } else {
            // Use defaults
            $startDate          = $defaultStartDate;
            $endDate            = $defaultEndDate;
            $startDateFormatted = $defaultStartDateFormatted;
            $endDateFormatted   = $defaultEndDateFormatted;
        }

    ?>

    <nav class="navbar bg-body-tertiary">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-auto">
                    <i class="fa-solid fa-folder-open fa-2xl"></i>
                </div>
                <div class="col-auto">
                    <h3>สถิติการลาของพนักงาน</h3>
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
                        $sql = "SELECT * FROM employees
                        WHERE e_status <> '1'";

                        $stmt = $conn->prepare($sql);

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
                        WHERE e_status <> '1'";

                        $stmt = $conn->prepare($sql);

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
                        WHERE e_status <> '1'";

                        $stmt = $conn->prepare($sql);

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
                <input type="text" name="start_date" class="form-control" id="startDate"
                    value="<?php echo $startDateFormatted; ?>">
            </div>
            <div class="col-auto">
                <input type="text" name="end_date" class="form-control" id="endDate"
                    value="<?php echo $endDateFormatted; ?>">
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-secondary" id="resetButton" data-bs-toggle="tooltip"
                    title="31-12-ปีที่แล้ว ถึง 30-11-ปีปัจจุบัน">
                    ค่าเริ่มต้น
                </button>
            </div>
        </form>

        <!-- แสดงช่วงวันที่ที่เลือก -->
        <div>
            <p>ช่วงวันที่ : <strong id="startDateText"><?php echo $startDateFormatted; ?></strong>
                ถึง <strong id="endDateText"><?php echo $endDateFormatted; ?></strong>
            </p>
        </div>
    </div>

    <!-- ตารางข้อมูลพนักงาน -->
    <div class="mt-3 container-fluid">
        <div class="table-responsive">
            <table class="mt-3 my-table" id="leaveEmpTable">
                <thead>
                    <!-- Table headers remain unchanged -->
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
                        <th colspan="3">ลากิจไม่ได้รับค่าจ้าง</th>
                        <th colspan="3">ลาป่วย</th>
                        <th colspan="3">ลาป่วยจากงาน</th>
                        <th colspan="3">ลาพักร้อน</th>
                        <th colspan="3">อื่น ๆ</th>
                        <th colspan="1" rowspan="3">มาสาย</th>
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
                        // Define leave types once
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

                        // Mapping leave types to employee field names
                        $leave_field_mapping = [
                            1 => 'e_leave_personal',
                            2 => 'e_leave_personal_no',
                            3 => 'e_leave_sick',
                            4 => 'e_leave_sick_work',
                            5 => 'e_leave_annual',
                            8 => 'e_other',
                        ];

                        // Fetch employees
                        $sql = "SELECT * FROM employees
            WHERE e_status <> '1'
            AND e_workplace = :workplace
            ORDER BY e_usercode ASC";

                        $stmt = $conn->prepare($sql);
                        $stmt->bindParam(':workplace', $workplace, PDO::PARAM_STR);

                        $stmt->execute();

                        $rowNumber = 1;

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
                            $total_late        = 0;

                            // Prepare the base SQL for leave calculation using date range
                            $base_sql_leave = "SELECT
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
COUNT(CASE WHEN l_leave_id = 7 THEN 1 END) AS total_late
FROM leave_list
JOIN employees ON employees.e_usercode = leave_list.l_usercode
WHERE l_leave_id = :leave_id
  AND l_usercode = :userCode
  AND l_leave_status = 0
  AND (
      -- Filter for leaves that overlap with the selected date range
      (l_leave_start_date BETWEEN :startDate AND :endDate) OR
      (l_leave_end_date BETWEEN :startDate AND :endDate) OR
      (:startDate BETWEEN l_leave_start_date AND l_leave_end_date) OR
      (:endDate BETWEEN l_leave_start_date AND l_leave_end_date)
  )";

                            // Process each leave type
                            foreach ($leave_types as $leave_id => $leave_name) {
                                // Create a copy of the base SQL
                                $sql_leave = $base_sql_leave;

                                $stmt_leave = $conn->prepare($sql_leave);
                                $stmt_leave->bindParam(':leave_id', $leave_id, PDO::PARAM_INT);
                                $stmt_leave->bindParam(':userCode', $row['e_usercode']);
                                $stmt_leave->bindParam(':startDate', $startDate);
                                $stmt_leave->bindParam(':endDate', $endDate);

                                $stmt_leave->execute();
                                $result_leave = $stmt_leave->fetch(PDO::FETCH_ASSOC);

                                // Initialize variables
                                $days            = 0;
                                $hours           = 0;
                                $minutes         = 0;
                                $display_minutes = 0;

                                // Get leave data if results exist
                                if ($result_leave) {
                                    $days    = $result_leave['total_leave_days'] ?? 0;
                                    $hours   = $result_leave['total_leave_hours'] ?? 0;
                                    $minutes = $result_leave['total_leave_minutes'] ?? 0;

                                    if ($leave_id == 7) {
                                        $total_late = $result_leave['total_late'] ?? 0;
                                    }

                                    // Convert hours to days if applicable
                                    $days += floor($hours / 8);
                                    $hours = $hours % 8;

                                    // Adjust minutes - first convert any minutes >= 60 to hours
                                    if ($minutes >= 60) {
                                        $hours += floor($minutes / 60);
                                        $minutes = $minutes % 60;
                                    }

                                    // Apply rounding rules for minutes
                                    if ($minutes > 0) {
                                        if ($minutes <= 30) {
                                            // Less than or equal to 30 minutes rounds to 30 minutes
                                            $minutes         = 30;
                                            $display_minutes = 5; // Display as 5
                                        } else {
                                            // More than 30 minutes rounds to 1 hour
                                            $hours += 1;
                                            $minutes         = 0;
                                            $display_minutes = 0;
                                        }
                                    }

                                    // Convert any hours >= 8 to days
                                    if ($hours >= 8) {
                                        $days += floor($hours / 8);
                                        $hours = $hours % 8;
                                    }
                                }

                                // Process and display leave information for specific leave types
                                if (in_array($leave_id, [1, 2, 3, 4, 5, 8])) {
                                    $field_name      = $leave_field_mapping[$leave_id];
                                    $total_available = $row[$field_name] ?? 0;

                                    echo '<td>' . $total_available . '</td>';
                                    echo '<td>' . $days . '(' . $hours . '.' . ($minutes > 0 ? $display_minutes : 0) . ')' . '</td>';

                                    // Calculate remaining leave (using actual minutes, not display minutes)
                                    $total_minutes_used      = ($days * 8 * 60) + ($hours * 60) + $minutes;
                                    $total_minutes           = $total_available * 8 * 60;
                                    $total_remaining_minutes = $total_minutes - $total_minutes_used;

                                    $remaining_days    = floor($total_remaining_minutes / (8 * 60));
                                    $remaining_hours   = floor(($total_remaining_minutes % (8 * 60)) / 60);
                                    $remaining_minutes = $total_remaining_minutes % 60;

                                    // Apply same rounding rules to remaining minutes
                                    $remaining_display_minutes = 0;
                                    if ($remaining_minutes > 0) {
                                        if ($remaining_minutes <= 30) {
                                            $remaining_minutes         = 30;
                                            $remaining_display_minutes = 5;
                                        } else {
                                            $remaining_hours += 1;
                                            $remaining_minutes = 0;
                                        }
                                    }

                                    echo '<td>' . $remaining_days . '(' . $remaining_hours . '.' .
                                        ($remaining_minutes > 0 ? $remaining_display_minutes : 0) . ')' . '</td>';

                                    // Add to totals for types that should be included in the sum
                                    if ($leave_id != 5 && $leave_id != 7 && $leave_id != 6) {
                                        $all_total_days += $days;
                                        $all_total_hours += $hours;
                                        $all_total_minutes += $minutes;
                                    }
                                }
                            }

                            // Display late count
                            echo '<td>' . $total_late . ' ครั้ง' . '</td>';

                            // Adjust total time calculations
                            if ($all_total_minutes >= 60) {
                                $all_total_hours += floor($all_total_minutes / 60);
                                $all_total_minutes = $all_total_minutes % 60;
                            }

                            // Apply rounding rules to total minutes
                            $all_display_minutes = 0;
                            if ($all_total_minutes > 0) {
                                if ($all_total_minutes <= 30) {
                                    $all_total_minutes   = 30;
                                    $all_display_minutes = 5;
                                } else {
                                    $all_total_hours += 1;
                                    $all_total_minutes = 0;
                                }
                            }

                            if ($all_total_hours >= 8) {
                                $all_total_days += floor($all_total_hours / 8);
                                $all_total_hours = $all_total_hours % 8;
                            }

                            // Display total leave used
                            echo '<td>' . $all_total_days . '(' . $all_total_hours . '.' .
                                ($all_total_minutes > 0 ? $all_display_minutes : 0) . ')' . '</td>';
                            echo '</tr>';

                            $rowNumber++;
                        }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // เพิ่ม event listener สำหรับปุ่ม "Export PDF"
        document.getElementById('generate-pdf').addEventListener('click', exportToPDF);

        // กำหนดค่า flatpickr สำหรับเลือกวันที่
        flatpickr('#startDate', {
            dateFormat: 'd-m-Y',
            altFormat: 'd-m-Y',
            onChange: function(selectedDates, dateStr) {
                document.getElementById('startDateText').textContent = dateStr;
                // ส่งฟอร์มอัตโนมัติเมื่อเลือกวันที่
                setTimeout(function() {
                    document.getElementById('dateForm').submit();
                }, 100);
            }
        });

        flatpickr('#endDate', {
            dateFormat: 'd-m-Y',
            altFormat: 'd-m-Y',
            onChange: function(selectedDates, dateStr) {
                document.getElementById('endDateText').textContent = dateStr;
                // ส่งฟอร์มอัตโนมัติเมื่อเลือกวันที่
                setTimeout(function() {
                    document.getElementById('dateForm').submit();
                }, 100);
            }
        });

        // ฟังก์ชันรีเซ็ตฟิลเตอร์ในการค้นหา
        window.resetFields = function() {
            document.getElementById('codeSearch').value = '';
            document.getElementById('nameSearch').value = '';
            document.getElementById('depSearch').value = '';
        };

        // ฟังก์ชันค้นหาในตาราง
        function filterTable() {
            const codeSearch = document.getElementById('codeSearch').value.toLowerCase();
            const nameSearch = document.getElementById('nameSearch').value.toLowerCase();
            const depSearch = document.getElementById('depSearch').value.toLowerCase();

            const table = document.getElementById('leaveEmpTable');
            const rows = table.getElementsByTagName('tr');

            // เริ่มจากแถวที่ 4 (ข้ามส่วนหัวตาราง)
            for (let i = 3; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName('td');
                if (cells.length > 0) {
                    const codeCell = cells[1].textContent.toLowerCase();
                    const nameCell = cells[2].textContent.toLowerCase();
                    const depCell = cells[3].textContent.toLowerCase();

                    const codeMatch = codeCell.includes(codeSearch);
                    const nameMatch = nameCell.includes(nameSearch);
                    const depMatch = depCell.includes(depSearch);

                    if (codeMatch && nameMatch && depMatch) {
                        rows[i].style.display = '';
                    } else {
                        rows[i].style.display = 'none';
                    }
                }
            }
        }

        // เพิ่ม event listener สำหรับช่องค้นหา
        document.getElementById('codeSearch').addEventListener('input', filterTable);
        document.getElementById('nameSearch').addEventListener('input', filterTable);
        document.getElementById('depSearch').addEventListener('input', filterTable);

        // ปุ่มรีเซ็ตวันที่กลับไปเป็นค่าเริ่มต้น
        document.getElementById('resetButton').addEventListener('click', function() {
            // กำหนดค่าวันที่เริ่มต้น (1 ธ.ค. ปีที่แล้ว ถึง 30 พ.ย. ปีปัจจุบัน)
            var lastYear = new Date().getFullYear() - 1;
            var currentYear = new Date().getFullYear();

            // รูปแบบวันที่ dd-mm-yyyy
            var defaultStartDate = '01-12-' + lastYear;
            var defaultEndDate = '30-11-' + currentYear;

            // อัพเดตค่าในฟิลด์
            document.getElementById('startDate').value = defaultStartDate;
            document.getElementById('endDate').value = defaultEndDate;

            // อัพเดตข้อความแสดงวันที่
            document.getElementById('startDateText').textContent = defaultStartDate;
            document.getElementById('endDateText').textContent = defaultEndDate;

            // ส่งฟอร์มเพื่ออัพเดตข้อมูล
            document.getElementById('dateForm').submit();
        });

        // อัพเดตข้อความวันที่เมื่อมีการเปลี่ยนแปลงในฟิลด์
        document.getElementById('startDate').addEventListener('change', function() {
            document.getElementById('startDateText').textContent = this.value;
        });

        document.getElementById('endDate').addEventListener('change', function() {
            document.getElementById('endDateText').textContent = this.value;
        });
    });

    // ฟังก์ชันสำหรับการ Export PDF
    function exportToPDF() {
        // แสดงการโหลดด้วย SweetAlert2
        Swal.fire({
            title: 'กำลังสร้างไฟล์ PDF',
            text: 'กรุณารอสักครู่...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // ตรวจสอบการกรองข้อมูล
        const codeSearch = document.getElementById('codeSearch').value;
        const nameSearch = document.getElementById('nameSearch').value;
        const depSearch = document.getElementById('depSearch').value;

        let reportSubtitle = '';
        if (codeSearch || nameSearch || depSearch) {
            reportSubtitle = 'ข้อมูลที่กรอง: ';
            if (codeSearch) reportSubtitle += `รหัส: ${codeSearch} `;
            if (nameSearch) reportSubtitle += `ชื่อ: ${nameSearch} `;
            if (depSearch) reportSubtitle += `แผนก: ${depSearch}`;
        }

        // กำหนดค่าสำหรับส่วนหัวของเอกสาร
        const reportTitle = 'รายงานสถิติการลาของพนักงาน';
        const startDate = document.getElementById('startDateText').textContent.trim();
        const endDate = document.getElementById('endDateText').textContent.trim();
        const dateRange = `ช่วงวันที่: ${startDate} ถึง ${endDate}`;
        const today = new Date();
        const currentDate = `วันที่พิมพ์: ${today.getDate()}-${today.getMonth() + 1}-${today.getFullYear()}`;

        // สร้างเอกสารใหม่สำหรับการพิมพ์
        const printWindow = window.open('', '_blank');
        printWindow.document.write('<html><head><title>สถิติการลาของพนักงาน</title>');

        // เพิ่ม CSS สำหรับการพิมพ์
        printWindow.document.write(`
        <style>
            @font-face {
                font-family: 'THSarabunNew';
                src: url('../fonts/THSarabunNew.ttf') format('truetype');
            }
            body {
                font-family: 'THSarabunNew', sans-serif;
                padding: 20px;
            }
            .report-header {
                text-align: center;
                margin-bottom: 20px;
            }
            .report-title {
                font-size: 24px;
                font-weight: bold;
            }
            .report-subtitle {
                font-size: 16px;
                margin-top: 5px;
            }
            .report-date {
                font-size: 16px;
                margin-top: 10px;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
            }
            th, td {
                border: 1px solid #000;
                padding: 4px;
                text-align: center;
                font-size: 14px;
            }
            th {
                background-color: #f2f2f2;
            }
            @media print {
                .no-print {
                    display: none;
                }
                body {
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }
                table {
                    page-break-inside: auto;
                }
                tr {
                    page-break-inside: avoid;
                    page-break-after: auto;
                }
                thead {
                    display: table-header-group;
                }
                tfoot {
                    display: table-footer-group;
                }
            }
        </style>
    `);

        printWindow.document.write('</head><body>');

        // เพิ่มส่วนหัวรายงาน
        printWindow.document.write(`
        <div class="report-header">
            <div class="report-title">${reportTitle}</div>
            ${reportSubtitle ? `<div class="report-subtitle">${reportSubtitle}</div>` : ''}
            <div class="report-date">${dateRange}</div>
            <div class="report-date">${currentDate}</div>
        </div>
    `);

        // คัดลอกตารางข้อมูลที่ถูกกรองแล้ว
        const originalTable = document.getElementById('leaveEmpTable');
        const clonedTable = originalTable.cloneNode(true);

        // กรองแถวที่ถูกซ่อน
        const rows = Array.from(clonedTable.querySelectorAll('tbody tr'));
        rows.forEach(row => {
            const originalRow = Array.from(originalTable.querySelectorAll('tbody tr')).find(
                r => r.cells[1].textContent === row.cells[1].textContent
            );
            if (originalRow && originalRow.style.display === 'none') {
                row.remove();
            }
        });

        // ลำดับแถวใหม่
        let rowNumber = 1;
        rows.forEach(row => {
            if (row.parentNode) { // ตรวจสอบว่าแถวยังคงอยู่ในตาราง
                row.cells[0].textContent = rowNumber++;
            }
        });

        printWindow.document.write(clonedTable.outerHTML);

        // เพิ่มปุ่มพิมพ์
        printWindow.document.write(`
        <div class="no-print" style="text-align: center; margin-top: 20px;">
            <button onclick="window.print(); setTimeout(function() { window.close(); }, 500);" style="padding: 10px 20px; font-size: 16px; cursor: pointer;">พิมพ์รายงาน</button>
            <button onclick="window.close();" style="padding: 10px 20px; font-size: 16px; cursor: pointer; margin-left: 10px;">ปิด</button>
        </div>
    `);

        printWindow.document.write('</body></html>');
        printWindow.document.close();

        // เปิดหน้าต่างการพิมพ์หลังจากโหลดเนื้อหาเสร็จ
        printWindow.onload = function() {
            // ปิด SweetAlert
            Swal.close();
        };
    }
    </script>
    <script src="../js/popper.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/bootstrap.bundle.js"></script>
    <script src="../js/bootstrap.bundle.min.js"></script>
</body>

</html>