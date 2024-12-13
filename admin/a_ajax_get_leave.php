<?php
// เชื่อมต่อฐานข้อมูล
include '../connect.php';

header('Content-Type: application/json; charset=UTF-8'); // ระบุว่าเราส่ง JSON

if (isset($_POST['createDate']) && isset($_POST['userCode'])) {
    $createDate = $_POST['createDate'];
    $userCode = $_POST['userCode'];

    // ดึงข้อมูลจากฐานข้อมูล
    $sql = "SELECT * FROM leave_list WHERE l_create_datetime = :createDate AND l_usercode = :userCode";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':createDate', $createDate);
    $stmt->bindParam(':userCode', $userCode);
    $stmt->execute();

    // ดึงผลลัพธ์
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        // ส่งข้อมูลกลับเป็น JSON
        echo json_encode($result);
    } else {
        echo json_encode(array('error' => 'ไม่พบข้อมูล'));
    }
} else {
    echo json_encode(array('error' => 'ข้อมูลไม่ครบถ้วน'));
}