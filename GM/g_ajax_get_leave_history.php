<?php
include '../connect.php';
include '../session_lang.php';

if (isset($_POST['userCode'])) {
    $userCode      = $_POST['userCode'];
    $selectedYear  = isset($_POST['selectedYear']) ? $_POST['selectedYear'] : date('Y');
    $selectedMonth = isset($_POST['selectedMonth']) ? $_POST['selectedMonth'] : 'All';

    $sql = "SELECT * FROM leave_list
            WHERE l_usercode = :userCode
            AND (
                YEAR(l_create_datetime) = :selectedYear
                OR YEAR(l_leave_end_date) = :selectedYear
            )";

    if ($selectedMonth != "All") {
        $sql .= " AND (
            MONTH(l_create_datetime) = :selectedMonth
            OR MONTH(l_leave_end_date) = :selectedMonth
        )";
    }

    $sql .= " ORDER BY l_create_datetime DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':userCode', $userCode, PDO::PARAM_STR);
    $stmt->bindParam(':selectedYear', $selectedYear);

    if ($selectedMonth != "All") {
        $stmt->bindParam(':selectedMonth', $selectedMonth);
    }

    $stmt->execute();
    $rowCount = $stmt->rowCount();

    if ($rowCount > 0) {
        $rowNumber = $rowCount;
        echo '<div class="table-responsive">'; // เพิ่ม table-responsive สำหรับการแสดงผลบนมือถือ
        echo '<table class="table table-hover">';
        echo '<thead>
        <tr class="text-center align-middle">
        <th>ลำดับ</th>
        <th>วันที่ยื่น</th>
        <th>ประเภท</th>
        <th>จาก</th>
        <th>ถึง</th>
        <th>สถานะรายการ</th>
        <th>สถานะอนุมัติ_1</th>
        <th>สถานะอนุมัติ_2</th>
        <th>สถานะอนุมัติ_3</th>
        <th>สถานะ HR</th>
        </tr>
        </thead>';
        echo '<tbody class="text-center">';

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo '<tr class="align-middle">';

            echo '<td>' . $rowNumber . '</td>';
            echo '<td>' . $row['l_create_datetime'] . '</td>';

            echo '<td>';
            if ($row['l_leave_id'] == 1) {
                echo '<span class="text-primary">ลากิจได้รับค่าจ้าง</span><br>เหตุผล: ' . $row['l_leave_reason'];
            } elseif ($row['l_leave_id'] == 2) {
                echo '<span class="text-primary">ลากิจไม่ได้รับค่าจ้าง</span><br>เหตุผล: ' . $row['l_leave_reason'];
            } elseif ($row['l_leave_id'] == 3) {
                echo '<span class="text-primary">ลาป่วย</span><br>เหตุผล: ' . $row['l_leave_reason'];
            } elseif ($row['l_leave_id'] == 4) {
                echo '<span class="text-primary">ลาป่วยจากงาน</span><br>เหตุผล: ' . $row['l_leave_reason'];
            } elseif ($row['l_leave_id'] == 5) {
                echo '<span class="text-primary">ลาพักร้อน</span><br>เหตุผล: ' . $row['l_leave_reason'];
            } elseif ($row['l_leave_id'] == 6) {
                echo '<span class="text-primary">ขาดงาน</span><br>เหตุผล: ' . $row['l_leave_reason'];
            } elseif ($row['l_leave_id'] == 7) {
                echo '<span class="text-primary">มาสาย</span><br>เหตุผล: ' . $row['l_leave_reason'];
            } elseif ($row['l_leave_id'] == 8) {
                echo '<span class="text-primary">อื่น ๆ</span><br>เหตุผล: ' . $row['l_leave_reason'];
            } else {
                echo 'ไม่พบประเภทการลา';
            }
            echo '</td>';

            // แสดงวันเวลาที่ลาเริ่มต้น
            // 08:10
            if ($row['l_leave_start_time'] == '08:30:00' && $row['l_time_remark'] == '08:10:00') {
                echo '<td>' . $row['l_leave_start_date'] . '<br> 08:10:00</td>';
            }

            // 08:15
            else if ($row['l_leave_start_time'] == '08:30:00' && $row['l_time_remark'] == '08:15:00') {
                echo '<td>' . $row['l_leave_start_date'] . '<br> 08:15:00</td>';
            }
            // 08:45
            else if ($row['l_leave_start_time'] == '09:00:00' && $row['l_time_remark'] == '08:45:00') {
                echo '<td>' . $row['l_leave_start_date'] . '<br> 08:45:00</td>';
            }
            // 09:10
            else if ($row['l_leave_start_time'] == '09:30:00' && $row['l_time_remark'] == '09:10:00') {
                echo '<td>' . $row['l_leave_start_date'] . '<br> 09:10:00</td>';
            }
            // 09:15
            else if ($row['l_leave_start_time'] == '09:30:00' && $row['l_time_remark'] == '09:15:00') {
                echo '<td>' . $row['l_leave_start_date'] . '<br> 09:15:00</td>';
            }
            // 09:45
            else if ($row['l_leave_start_time'] == '10:00:00' && $row['l_time_remark'] == '09:45:00') {
                echo '<td>' . $row['l_leave_start_date'] . '<br> 09:45:00</td>';
            }
            // 10:10
            else if ($row['l_leave_start_time'] == '10:30:00' && $row['l_time_remark'] == '10:10:00') {
                echo '<td>' . $row['l_leave_start_date'] . '<br> 10:10:00</td>';
            }
            // 10:15
            else if ($row['l_leave_start_time'] == '10:30:00' && $row['l_time_remark'] == '10:15:00') {
                echo '<td>' . $row['l_leave_start_date'] . '<br> 10:15:00</td>';
            }
            // 10:45
            else if ($row['l_leave_start_time'] == '11:00:00' && $row['l_time_remark'] == '10:45:00') {
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
            // 13:10
            else if ($row['l_leave_start_time'] == '13:30:00' && $row['l_time_remark'] == '13:10:00') {
                echo '<td>' . $row['l_leave_start_date'] . '<br> 13:10:00</td>';
            }
            // 13:15
            else if ($row['l_leave_start_time'] == '13:30:00' && $row['l_time_remark'] == '13:15:00') {
                echo '<td>' . $row['l_leave_start_date'] . '<br> 13:15:00</td>';
            }
            // 13:40
            else if ($row['l_leave_start_time'] == '14:00:00' && $row['l_time_remark'] == '13:40:00') {
                echo '<td>' . $row['l_leave_start_date'] . '<br> 13:40:00</td>';
            }
            // 13:45
            else if ($row['l_leave_start_time'] == '14:00:00' && $row['l_time_remark'] == '13:45:00') {
                echo '<td>' . $row['l_leave_start_date'] . '<br> 13:45:00</td>';
            }
            // 14:10
            else if ($row['l_leave_start_time'] == '14:30:00' && $row['l_time_remark'] == '14:10:00') {
                echo '<td>' . $row['l_leave_start_date'] . '<br> 14:10:00</td>';
            }
            // 14:15
            else if ($row['l_leave_start_time'] == '14:30:00' && $row['l_time_remark'] == '14:15:00') {
                echo '<td>' . $row['l_leave_start_date'] . '<br> 14:15:00</td>';
            }
            // 14:40
            else if ($row['l_leave_start_time'] == '15:00:00' && $row['l_time_remark'] == '14:40:00') {
                echo '<td>' . $row['l_leave_start_date'] . '<br> 14:40:00</td>';
            }
            // 14:45
            else if ($row['l_leave_start_time'] == '15:00:00' && $row['l_time_remark'] == '14:45:00') {
                echo '<td>' . $row['l_leave_start_date'] . '<br> 14:45:00</td>';
            }
            // 15:10
            else if ($row['l_leave_start_time'] == '15:30:00' && $row['l_time_remark'] == '15:10:00') {
                echo '<td>' . $row['l_leave_start_date'] . '<br> 15:10:00</td>';
            }
            // 15:15
            else if ($row['l_leave_start_time'] == '15:30:00' && $row['l_time_remark'] == '15:15:00') {
                echo '<td>' . $row['l_leave_start_date'] . '<br> 15:15:00</td>';
            }
            // 15:40
            else if ($row['l_leave_start_time'] == '16:00:00' && $row['l_time_remark'] == '15:40:00') {
                echo '<td>' . $row['l_leave_start_date'] . '<br> 15:40:00</td>';
            }
            // 15:45
            else if ($row['l_leave_start_time'] == '16:00:00' && $row['l_time_remark'] == '15:45:00') {
                echo '<td>' . $row['l_leave_start_date'] . '<br> 15:45:00</td>';
            }
            // 16:10
            else if ($row['l_leave_start_time'] == '16:30:00' && $row['l_time_remark'] == '16:10:00') {
                echo '<td>' . $row['l_leave_start_date'] . '<br> 16:10:00</td>';
            }
            // 16:15
            else if ($row['l_leave_start_time'] == '16:30:00' && $row['l_time_remark'] == '16:15:00') {
                echo '<td>' . $row['l_leave_start_date'] . '<br> 16:15:00</td>';
            }
            // 16:40
            else if ($row['l_leave_start_time'] == '17:00:00') {
                echo '<td>' . $row['l_leave_start_date'] . '<br> 16:40:00</td>';
            } else {
                // กรณีอื่น ๆ แสดงเวลาตาม l_leave_start_time
                echo '<td>' . $row['l_leave_start_date'] . '<br> ' . $row['l_leave_start_time'] . '</td>';
            }

            // แสดงวันเวลาที่ลาสิ้นสุด
            // 08:10
            if ($row['l_leave_end_time'] == '08:30:00' && $row['l_time_remark2'] == '08:10:00') {
                echo '<td>' . $row['l_leave_end_date'] . '<br> 08:10:00</td>';
            }
            // 08:15
            else if ($row['l_leave_end_time'] == '08:30:00' && $row['l_time_remark2'] == '08:15:00') {
                echo '<td>' . $row['l_leave_end_date'] . '<br> 08:15:00</td>';
            }
            // 08:45
            else if ($row['l_leave_end_time'] == '09:00:00' && $row['l_time_remark2'] == '08:45:00') {
                echo '<td>' . $row['l_leave_end_date'] . '<br> 08:45:00</td>';
            }
            // 09:10
            else if ($row['l_leave_end_time'] == '09:30:00' && $row['l_time_remark2'] == '09:10:00') {
                echo '<td>' . $row['l_leave_end_date'] . '<br> 09:10:00</td>';
            }
            // 09:15
            else if ($row['l_leave_end_time'] == '09:30:00' && $row['l_time_remark2'] == '09:15:00') {
                echo '<td>' . $row['l_leave_end_date'] . '<br> 09:15:00</td>';
            }
            // 09:45
            else if ($row['l_leave_end_time'] == '10:00:00' && $row['l_time_remark2'] == '09:45:00') {
                echo '<td>' . $row['l_leave_end_date'] . '<br> 09:45:00</td>';
            }
            // 10:10
            else if ($row['l_leave_end_time'] == '10:30:00' && $row['l_time_remark2'] == '10:10:00') {
                echo '<td>' . $row['l_leave_end_date'] . '<br> 10:10:00</td>';
            }
            // 10:15
            else if ($row['l_leave_end_time'] == '10:30:00' && $row['l_time_remark2'] == '10:15:00') {
                echo '<td>' . $row['l_leave_end_date'] . '<br> 10:15:00</td>';
            }
            // 10:45
            else if ($row['l_leave_end_time'] == '11:00:00' && $row['l_time_remark2'] == '10:45:00') {
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
            // 13:10
            else if ($row['l_leave_end_time'] == '13:30:00' && $row['l_time_remark2'] == '13:10:00') {
                echo '<td>' . $row['l_leave_end_date'] . '<br> 13:10:00</td>';
            }
            // 13:15
            else if ($row['l_leave_end_time'] == '13:30:00' && $row['l_time_remark2'] == '13:15:00') {
                echo '<td>' . $row['l_leave_end_date'] . '<br> 13:15:00</td>';
            }
            // 13:40
            else if ($row['l_leave_end_time'] == '14:00:00' && $row['l_time_remark2'] == '13:40:00') {
                echo '<td>' . $row['l_leave_end_date'] . '<br> 13:40:00</td>';
            }
            // 13:45
            else if ($row['l_leave_end_time'] == '14:00:00' && $row['l_time_remark2'] == '13:45:00') {
                echo '<td>' . $row['l_leave_start_date'] . '<br> 13:45:00</td>';
            }
            // 14:10
            else if ($row['l_leave_end_time'] == '14:30:00' && $row['l_time_remark2'] == '14:10:00') {
                echo '<td>' . $row['l_leave_end_date'] . '<br> 14:10:00</td>';
            }
            // 14:15
            else if ($row['l_leave_end_time'] == '14:30:00' && $row['l_time_remark2'] == '14:15:00') {
                echo '<td>' . $row['l_leave_end_date'] . '<br> 14:15:00</td>';
            }
            // 14:40
            else if ($row['l_leave_end_time'] == '15:00:00' && $row['l_time_remark2'] == '14:40:00') {
                echo '<td>' . $row['l_leave_end_date'] . '<br> 14:40:00</td>';
            }
            // 14:45
            else if ($row['l_leave_end_time'] == '15:00:00' && $row['l_time_remark2'] == '14:45:00') {
                echo '<td>' . $row['l_leave_end_date'] . '<br> 14:45:00</td>';
            }
            // 15:10
            else if ($row['l_leave_end_time'] == '15:30:00' && $row['l_time_remark2'] == '15:10:00') {
                echo '<td>' . $row['l_leave_end_date'] . '<br> 15:10:00</td>';
            }
            // 15:15
            else if ($row['l_leave_end_time'] == '15:30:00' && $row['l_time_remark2'] == '15:15:00') {
                echo '<td>' . $row['l_leave_end_date'] . '<br> 15:15:00</td>';
            }
            // 15:40
            else if ($row['l_leave_end_time'] == '16:00:00' && $row['l_time_remark2'] == '15:40:00') {
                echo '<td>' . $row['l_leave_end_date'] . '<br> 15:40:00</td>';
            }
            // 15:45
            else if ($row['l_leave_end_time'] == '16:00:00' && $row['l_time_remark2'] == '15:45:00') {
                echo '<td>' . $row['l_leave_end_date'] . '<br> 15:45:00</td>';
            }
            // 16:10
            else if ($row['l_leave_end_time'] == '16:30:00' && $row['l_time_remark2'] == '16:10:00') {
                echo '<td>' . $row['l_leave_end_date'] . '<br> 16:10:00</td>';
            }
            // 16:15
            else if ($row['l_leave_end_time'] == '16:30:00' && $row['l_time_remark2'] == '16:15:00') {
                echo '<td>' . $row['l_leave_end_date'] . '<br> 16:15:00</td>';
            }
            // 16:40
            else if ($row['l_leave_end_time'] == '17:00:00') {
                echo '<td>' . $row['l_leave_end_date'] . '<br> 16:40:00</td>';
            } else {
                // กรณีอื่น ๆ แสดงเวลาตาม l_leave_start_time
                echo '<td>' . $row['l_leave_end_date'] . '<br> ' . $row['l_leave_end_time'] . '</td>';
            }

            echo '<td>';
            if ($row['l_leave_status'] == 0) {
                echo '<span class="text-success">ปกติ</span>';
            } else {
                echo '<span class="text-danger">ยกเลิก</span>';
            }
            echo '</td>';

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
            } // ไม่มีสถานะ
            else {
                echo 'ไม่พบสถานะ';
            }

            echo '</td>';

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
            echo '<td>';
            if ($row['l_hr_status'] == 0) {
                echo '<div class="text-warning"><b>รอตรวจสอบ</b></div>';
            } elseif ($row['l_hr_status'] == 1) {
                echo '<div class="text-success"><b>ผ่าน</b></div>';
            } elseif ($row['l_hr_status'] == 2) {
                echo '<div class="text-danger"><b>ไม่ผ่าน</b></div>';
            } elseif ($row['l_hr_status'] == 3) {
                echo '';
            } else {
                echo $row['l_hr_status'];
            }
            echo '</td>';

            echo '<td>' . $row['l_remark'] . '</td>';

            echo '</tr>';
            $rowNumber--; // ลดค่าตัวแปร rowNumber ในแต่ละลูป
        }
        echo '</tbody>';
        echo '</table>';
        echo '</div>'; // ปิด div table-responsive

        // เพิ่ม CSS สำหรับการแสดงผลบนหน้าจอขนาดเล็ก
        echo '<style>
        @media (max-width: 767.98px) {
            .table {
                font-size: 0.85rem;
            }
            .table td, .table th {
                padding: 0.4rem;
            }
            /* ซ่อนคำอธิบายเพิ่มเติมบนหน้าจอเล็ก */
            .table td br {
                display: none;
            }
        }
        @media (max-width: 575.98px) {
            .table {
                font-size: 0.75rem;
            }
            .table td, .table th {
                padding: 0.3rem;
                white-space: nowrap;
            }
        }
        </style>';
    } else {
        echo '<div class="alert alert-info">ไม่พบข้อมูลประวัติการลา</div>';
    }
} else {
    echo '<div class="alert alert-danger">ไม่พบรหัสพนักงาน</div>';
}
