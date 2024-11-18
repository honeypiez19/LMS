<?php

include '../connect.php';

$itemsPerPage = 15;

if (!isset($_GET['page'])) {
    $currentPage = 1;
} else {
    $currentPage = $_GET['page'];
}

$selectedYear = isset($_GET['year']) ? $_GET['year'] : date("Y"); // ถ้าไม่มีส่งปีมาให้ใช้ปีปัจจุบัน
$searchCode = isset($_GET['codeSearch']) ? $_GET['codeSearch'] : ''; // รับค่า codeSearch จาก AJAX
$selectedMonth = isset($_GET['month']) ? $_GET['month'] : 'All'; // รับค่า selectedMonth จาก AJAX

$sql = "SELECT * FROM leave_list WHERE l_leave_id NOT IN (6,7) 
AND l_usercode LIKE '%".$searchCode."%'";

if ($selectedMonth != "All") {
    $sql .= " AND Month(l_leave_start_date) = '$selectedMonth'";
}

$sql .= " AND Year(l_leave_start_date) = '$selectedYear' ORDER BY l_create_datetime DESC";

// คำนวณผลลัพธ์ที่จะแสดง
$result = $conn->query($sql);
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
            echo '<td>' . $row['l_leave_end_date'] . '<br> ' . '11:45:00' . '</td>';

        } else if ($row['l_leave_end_time'] == '13:00:00') {
            echo '<td>' . $row['l_leave_end_date'] . '<br> ' . '12:45:00' . '</td>';
        } else if ($row['l_leave_end_time'] == '17:00:00') {
            echo '<td>' . $row['l_leave_end_date'] . '<br> ' . '16:40:00' . '</td>';
        } else {
            echo '<td>' . $row['l_leave_end_date'] . '<br> ' . $row['l_leave_end_time'] . '</td>';
        }

        // 11
        echo '</td>';
        if (!empty($row['l_file'])) {
            echo '<td><button id="imgBtn" class="btn btn-primary" onclick="window.open(\'../upload/' . $row['l_file'] . '\', \'_blank\')"><i class="fa-solid fa-file"></i></button></td>';
        } else {
            echo '<td><button id="imgNoBtn" class="btn btn-primary" disabled><i class="fa-solid fa-file-excel"></i></button></td>';
        }
        echo '</td>';

        // 12
        echo '<td>';
        if ($row['l_leave_status'] == 0) {
            echo '<span class="text-success">ปกติ</span>';
        } else {
            echo '<span class="text-danger">ยกเลิกใบลา</span>';
        }
        echo '</td>';

        // 13
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
        // อื่น ๆ
        elseif ($row['l_approve_status'] == 6) {
            echo '';
        }
        // ไม่มีสถานะ
        else {
            echo 'ไม่พบสถานะ';
        }
        echo '</td>';

        // 14
        echo '<td>' . $row['l_approve_datetime'] . '</td>';

        // 15
        echo '<td>' . $row['l_reason'] . '</td>';

        // 16
        echo '<td>' . $row['l_approve_name'] . '</td>';

        // 17
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

        // 18
        echo '<td>' . $row['l_approve_datetime2'] . '</td>';

        // 19
        echo '<td>' . $row['l_reason2'] . '</td>';

        // 20
        echo '<td>' . $row['l_approve_name2'] . '</td>';

        // 21
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

        // 22 ปุ่มตรวจสอบ
        if ($row['l_approve_status'] == 2 || $row['l_approve_status'] == 3) {
            echo "<td><button type='button' class='btn btn-primary leaveChk' data-bs-toggle='modal' data-bs-target='#leaveModal'>ตรวจสอบ</button></td>";
        } else {
            echo "<td><button type='button' class='btn btn-primary leaveChk' data-bs-toggle='modal' data-bs-target='#leaveModal'>ตรวจสอบ</button></td>";
        }
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
$endPage = min($totalPages, $currentPage + 2);

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


?>