<?php
// เชื่อมต่อฐานข้อมูล
include '../connect.php'; // เชื่อมต่อไฟล์ฐานข้อมูล

// ตรวจสอบว่าได้รับข้อมูลจาก POST
if (isset($_POST['editCreateDateTime']) && isset($_POST['editUserCode'])) {
    // รับค่าจาก AJAX
    $editCreateDateTime = $_POST['editCreateDateTime'];
    $editUserCode = $_POST['editUserCode'];

    try {
        // สร้างคำสั่ง SQL สำหรับอัพเดตข้อมูล
        $sql = "UPDATE leave_list SET l_usercode = :editUserCode WHERE l_create_datetime = :editCreateDateTime";

        // เตรียมคำสั่ง SQL
        $stmt = $conn->prepare($sql);

        // ผูกค่าพารามิเตอร์
        $stmt->bindParam(':editCreateDateTime', $editCreateDateTime);
        $stmt->bindParam(':editUserCode', $editUserCode);

        // ประมวลผลคำสั่ง SQL
        $stmt->execute();

        // ตรวจสอบผลลัพธ์
        if ($stmt->rowCount() > 0) {
            // ถ้าข้อมูลถูกอัพเดตสำเร็จ
            echo 'success';
        } else {
            // ถ้าไม่พบข้อมูลหรือไม่อัพเดต
            echo 'error';
        }
    } catch (PDOException $e) {
        // ถ้ามีข้อผิดพลาดในการเชื่อมต่อหรือคำสั่ง SQL
        echo 'error: ' . $e->getMessage();
    }
} else {
    // ถ้าไม่ได้รับข้อมูล
    echo 'error: Missing parameters';
}