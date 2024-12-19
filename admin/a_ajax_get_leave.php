<?php
// เชื่อมต่อฐานข้อมูล
include '../connect.php'; // เชื่อมต่อไฟล์ฐานข้อมูล

header('Content-Type: application/json; charset=UTF-8'); // ระบุว่าเราส่ง JSON

// ตรวจสอบค่าที่ถูกส่งมาจาก AJAX
if (isset($_POST['createDateTime']) && isset($_POST['userCode'])) {
    // รับค่าจาก AJAX
    $createDateTime = $_POST['createDateTime'];
    $userCode = $_POST['userCode'];

    try {
        // Query to fetch data
        $sql = "SELECT * FROM leave_list WHERE l_create_datetime = :createDateTime AND l_usercode = :userCode";
        $stmt = $conn->prepare($sql);

        // Bind parameters
        $stmt->bindParam(':createDateTime', $createDateTime);
        $stmt->bindParam(':userCode', $userCode);

        // Execute the query
        $stmt->execute();

        // Check if any row is returned
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // เพิ่มสถานะ success
            $row['status'] = 'success';
            echo json_encode($row); // ส่งข้อมูล JSON กลับไป
        } else {
            // If no data found
            echo json_encode(['status' => 'error', 'message' => 'ไม่พบข้อมูล']);
        }
    } catch (PDOException $e) {
        // Handle PDO errors
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    // Handle case when parameters are missing
    echo json_encode(['status' => 'error', 'message' => 'Missing parameters']);
}