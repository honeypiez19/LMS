<?php

include '../connect.php';
include '../session_lang.php';

$itemsPerPage = 15;

if (! isset($_GET['page'])) {
    $currentPage = 1;
} else {
    $currentPage = $_GET['page'];
}

$selectedYear  = isset($_GET['year']) ? $_GET['year'] : date("Y");      // ถ้าไม่มีส่งปีมาให้ใช้ปีปัจจุบัน
$searchCode    = isset($_GET['codeSearch']) ? $_GET['codeSearch'] : ''; // รับค่า codeSearch จาก AJAX
$selectedMonth = isset($_GET['month']) ? $_GET['month'] : 'All';        // รับค่า selectedMonth จาก AJAX

$sql = "SELECT * FROM leave_list WHERE l_leave_id NOT IN (6,7) AND l_hr_status <> 3
AND l_usercode LIKE '%" . $searchCode . "%'";

if ($selectedMonth != "All") {
    $sql .= " AND (
        MONTH(l_create_datetime) = $selectedMonth
        OR MONTH(l_leave_end_date) = $selectedMonth
    )";
}

$sql .= " AND (
    YEAR(l_create_datetime) = $selectedYear
    OR YEAR(l_leave_end_date) = $selectedYear
)

ORDER BY l_create_datetime DESC";

// คำนวณผลลัพธ์ที่จะแสดง
$result    = $conn->query($sql);
$totalRows = $result->rowCount();

// คำนวณหน้าทั้งหมด
$totalPages = ceil($totalRows / $itemsPerPage);

// คำนวณ offset สำหรับ pagination
$offset = ($currentPage - 1) * $itemsPerPage;

// เพิ่ม LIMIT และ OFFSET ในคำสั่ง SQL
$sql .= " LIMIT $itemsPerPage OFFSET $offset";

// ประมวลผลคำสั่ง SQL
$result = $conn->query($sql);

// แสดงผลลำดับของแถว
$rowNumber = $totalRows - ($currentPage - 1) * $itemsPerPage; // กำหนดลำดับของแถว

// แสดงข้อมูลในตาราง
if ($result->rowCount() > 0) {
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
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
        // 13:15
        else if ($row['l_leave_start_time'] == '13:30:00' && $row['l_remark'] == '13:15:00') {
            echo '<td>' . $row['l_leave_start_date'] . '<br> 13:15:00</td>';
        }
        // 13:45
        else if ($row['l_leave_start_time'] == '14:00:00' && $row['l_remark'] == '13:45:00') {
            echo '<td>' . $row['l_leave_start_date'] . '<br> 13:45:00</td>';
        }
        // 14:15
        else if ($row['l_leave_start_time'] == '14:30:00' && $row['l_remark'] == '14:15:00') {
            echo '<td>' . $row['l_leave_start_date'] . '<br> 14:15:00</td>';
        }
        // 14:45
        else if ($row['l_leave_start_time'] == '15:00:00' && $row['l_remark'] == '14:45:00') {
            echo '<td>' . $row['l_leave_start_date'] . '<br> 14:45:00</td>';
        }
        // 15:15
        else if ($row['l_leave_start_time'] == '15:30:00' && $row['l_remark'] == '15:15:00') {
            echo '<td>' . $row['l_leave_start_date'] . '<br> 15:15:00</td>';
        }
        // 15:45
        else if ($row['l_leave_start_time'] == '16:00:00' && $row['l_remark'] == '15:45:00') {
            echo '<td>' . $row['l_leave_start_date'] . '<br> 15:45:00</td>';
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
        // 13:15
        else if ($row['l_leave_end_time'] == '13:30:00' && $row['l_remark'] == '13:15:00') {
            echo '<td>' . $row['l_leave_end_date'] . '<br> 13:15:00</td>';
        }
        // 13:45
        else if ($row['l_leave_end_time'] == '14:00:00' && $row['l_remark'] == '13:45:00') {
            echo '<td>' . $row['l_leave_end_date'] . '<br> 13:45:00</td>';
        }
        // 14:15
        else if ($row['l_leave_end_time'] == '14:30:00' && $row['l_remark'] == '14:15:00') {
            echo '<td>' . $row['l_leave_end_date'] . '<br> 14:15:00</td>';
        }
        // 14:45
        else if ($row['l_leave_end_time'] == '15:00:00' && $row['l_remark'] == '14:45:00') {
            echo '<td>' . $row['l_leave_end_date'] . '<br> 14:45:00</td>';
        }
        // 15:15
        else if ($row['l_leave_end_time'] == '15:30:00' && $row['l_remark'] == '15:15:00') {
            echo '<td>' . $row['l_leave_end_date'] . '<br> 15:15:00</td>';
        }
        // 15:45
        else if ($row['l_leave_end_time'] == '16:00:00' && $row['l_remark'] == '15:45:00') {
            echo '<td>' . $row['l_leave_end_date'] . '<br> 15:45:00</td>';
        }
        // 16:15
        else if ($row['l_leave_end_time'] == '16:30:00' && $row['l_remark'] == '16:15:00') {
            echo '<td>' . $row['l_leave_end_date'] . '<br> 16:15:00</td>';
        }
        // 16:40
        else if ($row['l_leave_end_time'] == '17:00:00') {
            echo '<td>' . $row['l_leave_end_date'] . '<br> 16:40:00</td>';
        } else {
            // กรณีอื่น ๆ แสดงเวลาตาม l_leave_end_time
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
            echo '<td><button id="imgNoBtn" class="btn btn-secondary" disabled><i class="fa-solid fa-file-excel"></i></button></td>';
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
        echo '<td>' . $row['l_remark'] . '</td>';

        // 28
        echo '<td hidden>' . $row['l_workplace'] . '</td>';

        // 29
        echo '<td><button type="button" class="btn btn-warning  edit-btn" data-createdatetime="' . $row['l_create_datetime'] . '" data-usercode="' . $row['l_usercode'] . '">
        <i class="fa-solid fa-pen-to-square"></i>แก้ไข</button></td>';

        // 30
        // if ($row['l_hr_status'] == 2 || $row['l_hr_status'] == 3) {
        //     echo "<td><button type='button' class='btn btn-primary leaveChk' data-bs-toggle='modal' data-bs-target='#leaveModal' disabled>$btnCheck</button></td>";
        // } else {
        //     echo "<td><button type='button' class='btn btn-primary leaveChk' data-bs-toggle='modal' data-bs-target='#leaveModal'>$btnCheck</button></td>";
        // }
        echo '<td><button type="button" class="btn btn-danger cancel-btn" data-createdatetime="' . $row['l_create_datetime'] . '" data-usercode="' . $row['l_usercode'] . '">
        <i class="fa-solid fa-ban"></i> ยกเลิก</button></td>';

        echo "<td><button type='button' class='btn btn-primary leaveChk' data-bs-toggle='modal' data-bs-target='#leaveModal'>$btnCheck</button></td>";

        echo '</tr>';
        $rowNumber--;
    }
} else {
    echo '<tr><td colspan="18" style="text-align: left; color:red;">ไม่พบข้อมูล</td></tr>';
}
echo '<div class="pagination">';
echo '<ul class="pagination">';

if ($currentPage > 1) {
    echo '<li class="page-item"><a class="page-link" href="?page=1&month=' . urlencode($selectedMonth) . '&codeSearch=' . urlencode($searchCode) . '">&laquo;</a></li>';
    echo '<li class="page-item"><a class="page-link" href="?page=' . ($currentPage - 1) . '&month=' . urlencode($selectedMonth) . '&codeSearch=' . urlencode($searchCode) . '">&lt;</a></li>';
}

$startPage = max(1, $currentPage - 2);
$endPage   = min($totalPages, $currentPage + 2);

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