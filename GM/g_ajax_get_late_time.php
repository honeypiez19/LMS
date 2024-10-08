<?php
// เชื่อมต่อฐานข้อมูล
include '../connect.php'; // เปลี่ยนเส้นทางไปยังไฟล์เชื่อมต่อฐานข้อมูลของคุณ

if (isset($_GET['userCode'])) {
    $userCode = $_GET['userCode'];
    // คำสั่ง SQL เพื่อดึงข้อมูลของพนักงาน
    $sql = "SELECT * FROM leave_list WHERE l_usercode = :userCode AND l_leave_id = 7";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':userCode', $userCode);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // นับจำนวนแถวทั้งหมด
    $totalRows = count($rows);

    echo '<span><b>' . 'รหัสพนักงาน : ' . $rows[0]['l_usercode'] . '</b></span><br>';
    echo '<span><b>' . 'ชื่อพนักงาน : ' . $rows[0]['l_name'] . '</b></span><br>';
    echo '<span><b>' . 'แผนก : ' . $rows[0]['l_department'] . '</b></span><br>';

    // สร้างตัวแปรเพื่อเก็บลำดับแถว
    $rowNumber = $totalRows;

    echo '<table class="table">
    <thead>
    <tr class="text-center align-middle">
    <th>ลำดับ</th>
    <th>วันเวลาที่มาสาย</th>
    <th>หมายเหตุ</th>
    </tr>
    </thead>
    <tbody>';

    foreach ($rows as $row) {
        echo '<tr class="text-center align-middle">';
        echo '<td>' . $rowNumber . '</td>';
        echo '<td>' . $row['l_leave_start_date'] . '<br>' . $row['l_leave_start_time'] . ' ถึง ' . $row['l_leave_end_time'] . '</td>';
        echo '<td>' . $row['l_remark'] . '</td>';
        echo '</tr>';
        $rowNumber--; // ลดลำดับลงเรื่อย ๆ
    }

    echo '</tbody>
    </table>';

    exit; // เพิ่มคำสั่ง exit เพื่อหยุดการทำงานของไฟล์นี้หลังจากแสดงข้อมูล
}
