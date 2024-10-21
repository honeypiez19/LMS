<?php
include '../connect.php';
if (isset($_POST['leaveType'])) {
    $leaveType = $_POST['leaveType'];
    $userCode = $_POST['userCode'];
    $selectedYear = $_POST['selectedYear'];

    // คำนวณวันที่เริ่มต้นและสิ้นสุดตามปีที่เลือก
    $startDate = date(($selectedYear - 1) . "-12-01"); // วันที่เริ่มต้น 1 ธันวาคมของปีที่เลือก
    $endDate = date(($selectedYear) . "-11-30"); // วันที่สิ้นสุด 30 พฤศจิกายนของปีถัดไป

    if ($leaveType == 'ลากิจได้รับค่าจ้าง') {
        $conType = str_replace("ลากิจได้รับค่าจ้าง", "1", $leaveType);
    } else if ($leaveType == 'ลากิจไม่ได้รับค่าจ้าง') {
        $conType = str_replace("ลากิจไม่ได้รับค่าจ้าง", "2", $leaveType);
    } else if ($leaveType == 'ลาป่วย') {
        $conType = str_replace("ลาป่วย", "3", $leaveType);
    } else if ($leaveType == 'ลาป่วยจากงาน') {
        $conType = str_replace("ลาป่วยจากงาน", "4", $leaveType);
    } else if ($leaveType == 'ลาพักร้อน') {
        $conType = str_replace("ลาพักร้อน", "5", $leaveType);
    } else if ($leaveType == 'หยุดงาน') {
        $conType = str_replace("หยุดงาน", "6", $leaveType);
    } else if ($leaveType == 'มาสาย') {
        $conType = str_replace("มาสาย", "7", $leaveType);
    } else if ($leaveType == 'อื่น ๆ') {
        $conType = str_replace("อื่น ๆ", "8", $leaveType);
    } else {
        echo 'ไม่มีประเภทการลา';
    }

    // ทำความสะอาดข้อมูลก่อนนำไปใช้ใน SQL
    $userCodeQuoted = $conn->quote($userCode);
    $conTypeQuoted = $conn->quote($conType);
    $startDateQuoted = $conn->quote($startDate);
    $endDateQuoted = $conn->quote($endDate);

    // ดึงข้อมูลการลาจากฐานข้อมูล
    $sql = "SELECT * FROM leave_list
            WHERE l_leave_id = $conTypeQuoted
            AND l_usercode = $userCodeQuoted
            AND l_leave_start_date BETWEEN $startDateQuoted AND $endDateQuoted
            ORDER BY l_leave_start_date DESC";
    $result = $conn->query($sql);
    $totalRows = $result->rowCount();
    $rowNumber = $totalRows; // Start with the total number of rows    // ตรวจสอบว่ามีข้อมูลการลาหรือไม่
    if ($totalRows > 0) {
        echo '<h5>' . $leaveType . '</h5>';
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
            if ($row['l_leave_start_time'] == '12:00:00') {
                echo '<td>' . $row['l_leave_start_date'] . '<br> ' . '11:45:00' . '</td>';
            } else if ($row['l_leave_start_time'] == '13:00:00') {
                echo '<td>' . $row['l_leave_start_date'] . '<br> ' . '12:45:00' . '</td>';
            } else if ($row['l_leave_start_time'] == '17:00:00') {
                echo '<td>' . $row['l_leave_start_date'] . '<br> ' . '16:40:00' . '</td>';
            } else {
                echo '<td>' . $row['l_leave_start_date'] . '<br> ' . $row['l_leave_start_time'] . '</td>';
            }

// echo '<td>' . $row['l_leave_start_date'] . '<br> ' . $row['l_leave_start_time'] . '</td>';

// 10
            if ($row['l_leave_end_time'] == '12:00:00') {
                echo '<td>' . $row['l_leave_start_date'] . '<br> ' . '11:45:00' . '</td>';

            } else if ($row['l_leave_end_time'] == '13:00:00') {
                echo '<td>' . $row['l_leave_start_date'] . '<br> ' . '12:45:00' . '</td>';
            } else if ($row['l_leave_end_time'] == '17:00:00') {
                echo '<td>' . $row['l_leave_start_date'] . '<br> ' . '16:40:00' . '</td>';
            } else {
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

    } else {
        // ถ้าไม่มีข้อมูลการลา
        echo '<div class="leave-details">';
        echo '<h4>' . $leaveType . '</h4>';
        echo '<p>ไม่มีข้อมูลการลา</p>';
        echo '</div>';
    }
}