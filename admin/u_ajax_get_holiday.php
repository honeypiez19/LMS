<?php
// เชื่อมต่อกับฐานข้อมูล
include '../connect.php';

// ดึงวันหยุดจาก table holiday
$query = $conn->query("SELECT h_start_date, h_end_date FROM holiday");
$holidays = $query->fetchAll(PDO::FETCH_ASSOC);

// แปลงวันที่เป็นรูปแบบ d-m-Y
$formattedHolidays = array_map(function ($holiday) {
    return date("d-m-Y", strtotime($holiday['h_start_date']));
}, $holidays);

// ส่งข้อมูลวันหยุดกลับในรูปแบบ JSON
echo json_encode(['holidays' => $formattedHolidays]);