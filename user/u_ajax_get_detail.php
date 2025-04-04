<?php
include '../connect.php';
if (isset($_POST['leaveType'])) {
    $leaveType = $_POST['leaveType'];
    $userCode = $_POST['userCode'];
    $selectedYear = $_POST['selectedYear'];
    $depart = $_POST['depart'];
    $approveStatus = ($depart == 'RD') ? 2 : (($depart == 'Office') ? 2 : ($depart == '' ? null : 2));

    // คำนวณวันที่เริ่มต้นและสิ้นสุดตามปีที่เลือก
    $startDate = date(($selectedYear - 1) . "-12-01"); // วันที่เริ่มต้น 1 ธันวาคมของปีที่เลือก
    $endDate = date(($selectedYear) . "-11-30"); // วันที่สิ้นสุด 30 พฤศจิกายนของปีถัดไป

    if ($leaveType == 1) {
        $conType = "ลากิจได้รับค่าจ้าง";
    } else if ($leaveType == 2) {
        $conType = "ลากิจไม่ได้รับค่าจ้าง";
    } else if ($leaveType == 3) {
        $conType = "ลาป่วย";
    } else if ($leaveType == 4) {
        $conType = "ลาป่วยจากงาน";
    } else if ($leaveType == 5) {
        $conType = "ลาพักร้อน";
    } else if ($leaveType == 6) {
        $conType = "หยุดงาน";
    } else if ($leaveType == 7) {
        $conType = "มาสาย";
    } else if ($leaveType == 8) {
        $conType = "อื่น ๆ";
    } else {
        echo 'ไม่พบประเภทการลา';
    }

    // ทำความสะอาดข้อมูลก่อนนำไปใช้ใน SQL
    $userCodeQuoted = $conn->quote($userCode);
    $conTypeQuoted = $conn->quote($conType);
    $startDateQuoted = $conn->quote($startDate);
    $endDateQuoted = $conn->quote($endDate);

    // ดึงข้อมูลการลาจากฐานข้อมูล
    $sql = "  SELECT * FROM leave_list
    WHERE l_leave_id = $leaveType
    AND l_usercode = $userCodeQuoted
    -- AND l_leave_start_date BETWEEN $startDateQuoted AND $endDateQuoted
    AND l_approve_status IN (2,3,6)
    AND l_approve_status2 IN (4,5)
    AND YEAR(l_leave_end_date) = $selectedYear
    ORDER BY l_create_datetime DESC";
    $result = $conn->query($sql);
    $totalRows = $result->rowCount();
    $rowNumber = $totalRows; // Start with the total number of rows    // ตรวจสอบว่ามีข้อมูลการลาหรือไม่
    if ($totalRows > 0) {
        // echo '<h5>' . $conType . '</h5>';
        echo '<div class="table-responsive">';
        echo '<table class="table table-hover" >';
        echo '<thead>';
        echo '<tr class="text-center align-middle">';
        echo '<th rowspan="2">ลำดับ</th>';
        echo '<th rowspan="2">วันที่ยื่น</th>';
        echo '<th rowspan="2">ประเภทรายการ</th>';
        echo '<th colspan="2">วันเวลา</th>';
        echo '<th rowspan="2">สถานะรายการ</th>';
        echo '<th rowspan="2">สถานะอนุมัติ_1</th>';
        echo '<th rowspan="2">สถานะอนุมัติ_2</th>';
        echo '<th rowspan="2">สถานะ (เฉพาะ HR)</th>';
        echo '</tr>';

        echo '<tr class="text-center">';
        echo '<th>จาก</th>';
        echo '<th>ถึง</th>';
        echo '</tr>';

        echo '</thead>';
        echo '<tbody>';

        foreach ($result as $row) {
            echo '<tr class="text-center align-middle">';
            echo '<td>' . $rowNumber . '</td>';
            echo '<td>' . $row['l_create_datetime'] . '</td>';

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
                echo '<span class="text-primary">' . 'หยุดงาน' . '</span>' . '<br>';
            } elseif ($row['l_leave_id'] == 7) {
                echo '<span class="text-primary">' . 'มาสาย' . '</span>' . '<br>';
            } elseif ($row['l_leave_id'] == 8) {
                echo '<span class="text-primary">' . 'อื่น ๆ' . '</span>' . '<br>' . 'เหตุผล : ' . $row['l_leave_reason'];
            } else {
                echo $row['l_leave_reason'];
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

            echo '<td>';
            if ($row['l_leave_status'] == 1) {
                echo '<span class="text-danger">ยกเลิกรายการ</span>';
            } else {
                echo '<span class="text-success">ปกติ</span>';
            }
            echo '</td>';

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
            } elseif ($row['l_approve_status'] == 6) {
                echo '';
            }
            // ไม่มีสถานะ
            else {
                echo 'ไม่พบสถานะ';
            }
            echo '</td>';

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
            } elseif ($row['l_approve_status2'] == 6) {
                echo '';
            }
            // ไม่มีสถานะ
            else {
                echo 'ไม่พบสถานะ';
            }
            echo '</td>';

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

            echo '</tr>';
            $rowNumber--;
        }

        echo '</tbody>';
        echo '</table>';
        echo '</div>';

    } else {
        // ถ้าไม่มีข้อมูลการลา
        echo '<div class="leave-details">';
        // echo '<h4>' . $leaveType . '</h4>';
        echo '<p>ไม่พบข้อมูลการลา</p>';
        echo '</div>';
    }
}