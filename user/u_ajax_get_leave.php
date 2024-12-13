<?php
// เชื่อมต่อฐานข้อมูล
include '../connect.php';

header('Content-Type: application/json; charset=UTF-8'); // ระบุว่าเราส่ง JSON

if (isset($_POST['createDatetime']) && isset($_POST['userCode'])) {
    $createDatetime = $_POST['createDatetime'];
    $userCode = $_POST['userCode'];

    try {
        // เตรียมคำสั่ง SQL
        $sql = "SELECT * FROM leave_list WHERE l_create_datetime = :createDatetime AND l_usercode = :userCode";
        $stmt = $conn->prepare($sql);

        // ผูกค่าพารามิเตอร์
        $stmt->bindParam(':createDatetime', $createDatetime, PDO::PARAM_STR);
        $stmt->bindParam(':userCode', $userCode, PDO::PARAM_STR);

        // รันคำสั่ง SQL
        $stmt->execute();

        // ตรวจสอบผลลัพธ์
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($row); // ส่งข้อมูล JSON กลับไป
        } else {
            echo json_encode(['error' => 'ไม่พบข้อมูล']); // กรณีไม่พบข้อมูล
        }
    } catch (Exception $e) {
        // จัดการข้อผิดพลาด
        echo json_encode(['error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'ข้อมูลไม่ครบ']);
}