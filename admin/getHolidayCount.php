<?php

require '../connect.php';

// getHolidayCount.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // รับค่าจากการร้องขอ AJAX
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    // SQL query สำหรับหาจำนวนวันหยุดในช่วงเวลาที่ระบุ
    $holiday_query = "SELECT COUNT(*) as holiday_count
                      FROM holiday
                      WHERE h_start_date BETWEEN :start_date AND :end_date
                      AND h_holiday_status = 'วันหยุด'
                      AND h_status = 0";

    // เตรียมคำสั่ง SQL
    $holiday_stmt = $conn->prepare($holiday_query);
    $holiday_stmt->bindParam(':start_date', $start_date);
    $holiday_stmt->bindParam(':end_date', $end_date);
    $holiday_stmt->execute();

    // ดึงข้อมูลจากฐานข้อมูล
    $holiday_data = $holiday_stmt->fetch(PDO::FETCH_ASSOC);
    $holiday_count = $holiday_data['holiday_count'];

    // ส่งข้อมูล holidayCount กลับไปยัง JavaScript
    echo $holiday_count;
}