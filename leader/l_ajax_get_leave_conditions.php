<?php
// เชื่อมต่อกับฐานข้อมูล
include '../connect.php';

try {
    // ดึงข้อมูลจากตาราง leave_type2
    $sql  = "SELECT lt2_id, lt2_name FROM leave_type2 ORDER BY lt2_id ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    // ดึงข้อมูลทั้งหมด
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ส่งข้อมูลกลับเป็น JSON
    header('Content-Type: application/json');
    echo json_encode($results);
} catch (PDOException $e) {
    // ส่งข้อความผิดพลาดกลับ
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => $e->getMessage()]);
}