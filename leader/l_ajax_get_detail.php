<?php
include '../connect.php';
if (isset($_POST['leaveType'])) {
    $leaveType = $_POST['leaveType'];
    $userCode = $_POST['userCode'];
    $selectedYear = $_POST['selectedYear'];
    // $depart = $_POST['depart'];

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
            AND l_approve_status2 = 4
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

            // 08:45
            if ($leaveTimeStart == '08:45') {
                $leaveTimeStartLine = '08:45';
                $leaveTimeStart = '09:00';
                $remark = '08:45:00';
            }
// 09:45
            else if ($leaveTimeStart == '09:45') {
                $leaveTimeStartLine = '09:45';
                $leaveTimeStart = '10:00';
                $remark = '09:45:00';
            }
// 10:45
            else if ($leaveTimeStart == '10:45') {
                $leaveTimeStartLine = '10:45';
                $leaveTimeStart = '11:00';
                $remark = '10:45:00';
            }
// 11:45
            else if ($leaveTimeStart == '12:00') {
                $leaveTimeStartLine = '11:45';
            }
// 12:45
            else if ($leaveTimeStart == '13:00') {
                $leaveTimeStartLine = '12:45';
            }
// 13:10
            else if ($leaveTimeStart == '13:10') {
                $leaveTimeStartLine = '13:10';
                $leaveTimeStart = '13:30';
                $remark = '13:10:00';
            }
// 13:40
            else if ($leaveTimeStart == '13:40') {
                $leaveTimeStartLine = '13:40';
                $leaveTimeStart = '14:00';
                $remark = '13:40:00';
            }
// 14:10
            else if ($leaveTimeStart == '14:10') {
                $leaveTimeStartLine = '14:10';
                $leaveTimeStart = '14:30';
                $remark = '14:10:00';
            }
// 14:40
            else if ($leaveTimeStart == '14:40') {
                $leaveTimeStartLine = '14:40';
                $leaveTimeStart = '15:00';
                $remark = '14:40:00';
            }
// 15:10
            else if ($leaveTimeStart == '15:10') {
                $leaveTimeStartLine = '15:10';
                $leaveTimeStart = '15:30';
                $remark = '15:10:00';
            }
// 15:40
            else if ($leaveTimeStart == '15:40') {
                $leaveTimeStartLine = '15:40';
                $leaveTimeStart = '16:00';
                $remark = '15:40:00';
            }
// 16:10
            else if ($leaveTimeStart == '16:10') {
                $leaveTimeStartLine = '16:10';
                $leaveTimeStart = '16:30';
                $remark = '16:10:00';
            }
// 16:40
            else if ($leaveTimeStart == '17:00') {
                $leaveTimeStartLine = '16:40';
            } else {
                $leaveTimeStartLine = $leaveTimeStart;
            }

// 08:45
            if ($leaveTimeEnd == '08:45') {
                $leaveTimeEndLine = '08:45';
                $leaveTimeEnd = '09:00';
                $remark = '08:45:00';
            }
// 09:45
            else if ($leaveTimeEnd == '09:45') {
                $leaveTimeEndLine = '09:45';
                $leaveTimeEnd = '10:00';
                $remark = '09:45:00';
            }
// 10:45
            else if ($leaveTimeEnd == '10:45') {
                $leaveTimeEndLine = '10:45';
                $leaveTimeEnd = '11:00';
                $remark = '10:45:00';
            }
// 11:45
            else if ($leaveTimeEnd == '12:00') {
                $leaveTimeEndLine = '11:45';
            }
// 12:45
            else if ($leaveTimeEnd == '13:00') {
                $leaveTimeEndLine = '12:45';
            }
// 13:10
            else if ($leaveTimeEnd == '13:10') {
                $leaveTimeEndLine = '13:10';
                $leaveTimeEnd = '13:30';
                $remark = '13:10:00';
            }
// 13:40
            else if ($leaveTimeEnd == '13:40') {
                $leaveTimeEndLine = '13:40';
                $leaveTimeEnd = '14:00';
                $remark = '13:40:00';
            }
// 14:10
            else if ($leaveTimeEnd == '14:10') {
                $leaveTimeEndLine = '14:10';
                $leaveTimeEnd = '14:30';
                $remark = '14:10:00';
            }
// 14:40
            else if ($leaveTimeEnd == '14:40') {
                $leaveTimeEndLine = '14:40';
                $leaveTimeEnd = '15:00';
                $remark = '14:40:00';
            }
// 15:10
            else if ($leaveTimeEnd == '15:10') {
                $leaveTimeEndLine = '15:10';
                $leaveTimeEnd = '15:30';
                $remark = '15:10:00';
            }
// 15:40
            else if ($leaveTimeEnd == '15:40') {
                $leaveTimeEndLine = '15:40';
                $leaveTimeEnd = '16:00';
                $remark = '15:40:00';
            }
// 16:10
            else if ($leaveTimeEnd == '16:10') {
                $leaveTimeEndLine = '16:10';
                $leaveTimeEnd = '16:30';
                $remark = '16:10:00';
            }
// 16:40
            else if ($leaveTimeEnd == '17:00') {
                $leaveTimeEndLine = '16:40';
            } else {
                $leaveTimeEndLine = $leaveTimeEnd;
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