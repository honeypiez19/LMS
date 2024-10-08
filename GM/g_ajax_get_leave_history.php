<?php
include '../connect.php';

if (isset($_POST['userCode'])) {
    $userCode = $_POST['userCode'];

    // สร้างคำสั่ง SQL เพื่อดึงข้อมูลประวัติการลา
    $sql = "SELECT * FROM leave_list WHERE l_usercode = :userCode
    AND l_leave_id <> 6
    AND l_leave_id <> 7
    ORDER BY l_create_datetime DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':userCode', $userCode, PDO::PARAM_STR);
    $stmt->execute();

    $rowCount = $stmt->rowCount();
    if ($rowCount > 0) {
        $rowNumber = $rowCount;
        echo '<table class="table table-hover">';
        echo '<thead>
        <tr class="text-center align-middle">
        <th rowspan="2">ลำดับ</th>
        <th rowspan="2">วันที่ยื่นใบลา</th>
        <th rowspan="2">ประเภทการลา</th>
        <th colspan="2">วันเวลาที่ลา</th>
        <th rowspan="2">สถานะรายการ</th>
        <th rowspan="2">สถานะอนุมัติ_1</th>
        <th rowspan="2">สถานะอนุมัติ_2</th>
        <th rowspan="2">สถานะ (เฉพาะ HR)</th>
        </tr>
        <tr class="text-center">
        <th>จาก</th>
        <th>ถึง</th>
        </tr>
        </thead>';
        echo '<tbody class="text-center">';
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo '<tr class="align-middle">';

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
                echo '<span class="text-primary">' . 'ขาดงาน' . '</span>' . '<br>' . 'เหตุผล : ' . $row['l_leave_reason'];
            } elseif ($row['l_leave_id'] == 7) {
                echo '<span class="text-primary">' . 'มาสาย' . '</span>' . '<br>' . 'เหตุผล : ' . $row['l_leave_reason'];
            } elseif ($row['l_leave_id'] == 8) {
                echo '<span class="text-primary">' . 'อื่น ๆ' . '</span>' . '<br>' . 'เหตุผล : ' . $row['l_leave_reason'];
            } else {
                echo 'ไม่พบประเภทการลาและเหตุผลการลา';
            }
            echo '</td>';

            echo '<td>' . $row['l_leave_start_date'] . '<br> ' . $row['l_leave_start_time'] . '</td>';
            echo '<td>' . $row['l_leave_end_date'] . '<br> ' . $row['l_leave_end_time'] . '</td>';

            echo '<td>';
            if ($row['l_leave_status'] == 0) {
                echo '<span class="text-success">ปกติ</span>';
            } else {
                echo '<span class="text-danger">ยกเลิกใบลา</span>';
            }
            echo '</td>';

            echo '<td>';
            // รอหัวหน้าอนุมัติ
            if ($row['l_approve_status'] == 0) {
                echo '<div class="text-warning"><b>รอหัวหน้าอนุมัติ</b></div>';
            }
            // รอผจกอนุมัติ
            elseif ($row['l_approve_status'] == 1) {
                echo '<div class="text-success"><b>รอผู้จัดการอนุมัติ</b></div>';
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
                echo '<div class="text-danger"><b>ผู้จัดการอนุมัติ</b></div>';
            }
            //  ผจก ไม่อนุมัติ
            elseif ($row['l_approve_status'] == 5) {
                echo '<div class="text-danger"><b>ผู้จัดการไม่อนุมัติ</b></div>';
            }
            // ค่าว่าง
            elseif ($row['l_approve_status'] == 6) {
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
            }
            // ค่าว่าง
            elseif ($row['l_approve_status'] == 6) {
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
                echo $row['l_hr_status'];
            }
            echo '</td>';

            echo '</tr>';
            $rowNumber--; // ลดค่าตัวแปร rowNumber ในแต่ละลูป
        }
        echo '</tbody>';
        echo '</table>';
    } else {
        echo 'ไม่พบข้อมูลประวัติการลา';
    }
} else {
    echo 'ไม่พบรหัสพนักงาน';
}