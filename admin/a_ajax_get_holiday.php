<?php
include '../connect.php';
date_default_timezone_set('Asia/Bangkok');

// Get data from AJAX
if (isset($_GET['start']) && isset($_GET['end'])) {
    $startDate = $_GET['start'];
    $endDate = $_GET['end'];

    // SQL query to get events for the specified date range with a limit
    $sql = "SELECT DISTINCT h_name, h_start_date FROM holiday
            WHERE h_start_date BETWEEN :startDate AND :endDate
            AND h_status = 0
            ORDER BY h_start_date ASC"; // Optional: Order by date to improve performance
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':startDate', $startDate);
    $stmt->bindParam(':endDate', $endDate);
    $stmt->execute();

    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Return events as JSON
    echo json_encode($events);
} else {
    // Handle the case where no date is provided
    echo json_encode([]);
}

// เชื่อมต่อกับฐานข้อมูล
// include '../connect.php';

// ดึงวันหยุดจาก table holiday
// $query = $conn->query("SELECT h_start_date, h_end_date FROM holiday");
// $holidays = $query->fetchAll(PDO::FETCH_ASSOC);

// // แปลงวันที่เป็นรูปแบบ d-m-Y
// $formattedHolidays = array_map(function ($holiday) {
//     return date("d-m-Y", strtotime($holiday['h_start_date']));
// }, $holidays);

// // ส่งข้อมูลวันหยุดกลับในรูปแบบ JSON
// echo json_encode(['holidays' => $formattedHolidays]);