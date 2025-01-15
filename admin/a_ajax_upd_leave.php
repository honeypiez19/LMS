<?php
// เชื่อมต่อฐานข้อมูล
include '../connect.php'; // เชื่อมต่อไฟล์ฐานข้อมูล

// ตรวจสอบว่าได้รับข้อมูลจาก POST
if (isset($_POST['editCreateDateTime']) && isset($_POST['editUserCode'])) {
    // รับค่าจาก AJAX
    $editCreateDateTime = $_POST['editCreateDateTime'];
    $editUserCode = $_POST['editUserCode'];
    $editLeaveType = $_POST['editLeaveType'];
    $editLeaveReason = $_POST['editLeaveReason'];
    $editLeaveStartTime = $_POST['editLeaveStartTime'];
    $editLeaveEndTime = $_POST['editLeaveEndTime'];

    $editLeaveStartDate = DateTime::createFromFormat('d-m-Y', $_POST['editLeaveStartDate'])->format('Y-m-d');
    $editLeaveEndDate = DateTime::createFromFormat('d-m-Y', $_POST['editLeaveEndDate'])->format('Y-m-d');

    // 08:10
    if ($editLeaveStartTime == '08:10') {
        $editLeaveStartTimeLine = '08:10';
        $editLeaveStartTime = '08:30';
        $remark = '08:10:00';
    }
    // 08:15
    else if ($editLeaveStartTime == '08:15') {
        $editLeaveStartTimeLine = '08:15';
        $editLeaveStartTime = '08:30';
        $remark = '08:15:00';
    }
    // 08:45
    else if ($editLeaveStartTime == '08:45') {
        $editLeaveStartTimeLine = '08:45';
        $editLeaveStartTime = '09:00';
        $remark = '08:45:00';
    }
    // 09:10
    else if ($editLeaveStartTime == '09:10') {
        $editLeaveStartTimeLine = '09:10';
        $editLeaveStartTime = '09:30';
        $remark = '09:10:00';
    }
    // 09:15
    else if ($editLeaveStartTime == '09:15') {
        $editLeaveStartTimeLine = '09:15';
        $editLeaveStartTime = '09:30';
        $remark = '09:15:00';
    }
    // 09:45
    else if ($editLeaveStartTime == '09:45') {
        $editLeaveStartTimeLine = '09:45';
        $editLeaveStartTime = '10:00';
        $remark = '09:45:00';
    }
    // 10:10
    else if ($editLeaveStartTime == '10:10') {
        $editLeaveStartTimeLine = '10:10';
        $editLeaveStartTime = '10:30';
        $remark = '10:10:00';
    }
    // 10:15
    else if ($editLeaveStartTime == '10:15') {
        $editLeaveStartTimeLine = '10:15';
        $editLeaveStartTime = '10:30';
        $remark = '10:15:00';
    }
    // 10:45
    else if ($editLeaveStartTime == '10:45') {
        $editLeaveStartTimeLine = '10:45';
        $editLeaveStartTime = '11:00';
        $remark = '10:45:00';
    }
    // 11:45
    else if ($editLeaveStartTime == '12:00') {
        $editLeaveStartTimeLine = '11:45';
    }
    // 12:45
    else if ($editLeaveStartTime == '13:00') {
        $editLeaveStartTimeLine = '12:45';
    }
    // 13:10
    else if ($editLeaveStartTime == '13:10') {
        $editLeaveStartTimeLine = '13:10';
        $editLeaveStartTime = '13:30';
        $remark = '13:10:00';
    }
    // 13:15
    else if ($editLeaveStartTime == '13:15') {
        $editLeaveStartTimeLine = '13:15';
        $editLeaveStartTime = '13:30';
        $remark = '13:15:00';
    }
    // 13:40
    else if ($editLeaveStartTime == '13:40') {
        $editLeaveStartTimeLine = '13:40';
        $editLeaveStartTime = '14:00';
        $remark = '13:40:00';
    }
    // 13:45
    else if ($editLeaveStartTime == '13:45') {
        $editLeaveStartTimeLine = '13:45';
        $editLeaveStartTime = '14:00';
        $remark = '13:45:00';
    }
    // 14:10
    else if ($editLeaveStartTime == '14:10') {
        $editLeaveStartTimeLine = '14:10';
        $editLeaveStartTime = '14:30';
        $remark = '14:10:00';
    }
    // 14:15
    else if ($editLeaveStartTime == '14:15') {
        $editLeaveStartTimeLine = '14:15';
        $editLeaveStartTime = '14:30';
        $remark = '14:15:00';
    }
    // 14:40
    else if ($editLeaveStartTime == '14:40') {
        $editLeaveStartTimeLine = '14:40';
        $editLeaveStartTime = '15:00';
        $remark = '14:40:00';
    }
    // 14:45
    else if ($editLeaveStartTime == '14:45') {
        $editLeaveStartTimeLine = '14:45';
        $editLeaveStartTime = '15:00';
        $remark = '14:45:00';
    }
    // 15:10
    else if ($editLeaveStartTime == '15:10') {
        $editLeaveStartTimeLine = '15:10';
        $editLeaveStartTime = '15:30';
        $remark = '15:10:00';
    }
    // 15:15
    else if ($editLeaveStartTime == '15:15') {
        $editLeaveStartTimeLine = '15:15';
        $editLeaveStartTime = '15:30';
        $remark = '15:15:00';
    }
    // 15:40
    else if ($editLeaveStartTime == '15:40') {
        $editLeaveStartTimeLine = '15:40';
        $editLeaveStartTime = '16:00';
        $remark = '15:40:00';
    }
    // 15:45
    else if ($editLeaveStartTime == '15:45') {
        $editLeaveStartTimeLine = '15:45';
        $editLeaveStartTime = '16:00';
        $remark = '15:45:00';
    }
    // 16:10
    else if ($editLeaveStartTime == '16:10') {
        $editLeaveStartTimeLine = '16:10';
        $editLeaveStartTime = '16:30';
        $remark = '16:10:00';
    }
    // 16:15
    else if ($editLeaveStartTime == '16:15') {
        $editLeaveStartTimeLine = '16:15';
        $editLeaveStartTime = '16:30';
        $remark = '16:15:00';
    }
    // 16:40
    else if ($editLeaveStartTime == '17:00') {
        $editLeaveStartTimeLine = '16:40';
    } else {
        $editLeaveStartTimeLine = $editLeaveStartTime;
    }

    // 08:10
    if ($editLeaveEndTime == '08:10') {
        $editLeaveEndTimeLine = '08:10';
        $editLeaveEndTime = '08:30';
        $remark = '08:10:00';
    }
    // 08:15
    else if ($editLeaveEndTime == '08:15') {
        $editLeaveEndTimeLine = '08:15';
        $editLeaveEndTime = '08:30';
        $remark = '08:15:00';
    }
    // 08:45
    else if ($editLeaveEndTime == '08:45') {
        $editLeaveEndTimeLine = '08:45';
        $editLeaveEndTime = '09:00';
        $remark = '08:45:00';
    }
    // 09:10
    else if ($editLeaveEndTime == '09:10') {
        $editLeaveEndTimeLine = '09:10';
        $editLeaveEndTime = '09:30';
        $remark = '09:10:00';
    }
    // 09:15
    else if ($editLeaveEndTime == '09:15') {
        $editLeaveEndTimeLine = '09:15';
        $editLeaveEndTime = '09:30';
        $remark = '09:15:00';
    }
    // 09:45
    else if ($editLeaveEndTime == '09:45') {
        $editLeaveEndTimeLine = '09:45';
        $editLeaveEndTime = '10:00';
        $remark = '09:45:00';
    }
    // 10:10
    else if ($editLeaveEndTime == '10:10') {
        $editLeaveEndTimeLine = '10:10';
        $editLeaveEndTime = '10:30';
        $remark = '10:10:00';
    }
    // 10:15
    else if ($editLeaveEndTime == '10:15') {
        $editLeaveEndTimeLine = '10:15';
        $editLeaveEndTime = '10:30';
        $remark = '10:15:00';
    }
    // 10:45
    else if ($editLeaveEndTime == '10:45') {
        $editLeaveEndTimeLine = '10:45';
        $editLeaveEndTime = '11:00';
        $remark = '10:45:00';
    }
    // 11:45
    else if ($editLeaveEndTime == '12:00') {
        $editLeaveEndTimeLine = '11:45';
    }
    // 12:45
    else if ($editLeaveEndTime == '13:00') {
        $editLeaveEndTimeLine = '12:45';
    }
    // 13:10
    else if ($editLeaveEndTime == '13:10') {
        $editLeaveEndTimeLine = '13:10';
        $editLeaveEndTime = '13:30';
        $remark = '13:10:00';
    }
    // 13:15
    else if ($editLeaveEndTime == '13:15') {
        $editLeaveEndTimeLine = '13:15';
        $editLeaveEndTime = '13:30';
        $remark = '13:15:00';
    }
    // 13:40
    else if ($editLeaveEndTime == '13:40') {
        $editLeaveEndTimeLine = '13:40';
        $editLeaveEndTime = '14:00';
        $remark = '13:40:00';
    }
    // 13:45
    else if ($editLeaveEndTime == '13:45') {
        $editLeaveEndTimeLine = '13:45';
        $editLeaveEndTime = '14:00';
        $remark = '13:45:00';
    }
    // 14:10
    else if ($editLeaveEndTime == '14:10') {
        $editLeaveEndTimeLine = '14:10';
        $editLeaveEndTime = '14:30';
        $remark = '14:10:00';
    }
    // 14:15
    else if ($editLeaveEndTime == '14:15') {
        $editLeaveEndTimeLine = '14:15';
        $editLeaveEndTime = '14:30';
        $remark = '14:15:00';
    }
    // 14:40
    else if ($editLeaveEndTime == '14:40') {
        $editLeaveEndTimeLine = '14:40';
        $editLeaveEndTime = '15:00';
        $remark = '14:40:00';
    }
    // 14:45
    else if ($editLeaveEndTime == '14:45') {
        $editLeaveEndTimeLine = '14:45';
        $editLeaveEndTime = '15:00';
        $remark = '14:45:00';
    }
    // 15:10
    else if ($editLeaveEndTime == '15:10') {
        $editLeaveEndTimeLine = '15:10';
        $editLeaveEndTime = '15:30';
        $remark = '15:10:00';
    }
    // 15:15
    else if ($editLeaveEndTime == '15:15') {
        $editLeaveEndTimeLine = '15:15';
        $editLeaveEndTime = '15:30';
        $remark = '15:15:00';
    }
    // 15:40
    else if ($editLeaveEndTime == '15:40') {
        $editLeaveEndTimeLine = '15:40';
        $editLeaveEndTime = '16:00';
        $remark = '15:40:00';
    }
    // 15:45
    else if ($editLeaveEndTime == '15:45') {
        $editLeaveEndTimeLine = '15:45';
        $editLeaveEndTime = '16:00';
        $remark = '15:45:00';
    }
    // 16:10
    else if ($editLeaveEndTime == '16:10') {
        $editLeaveEndTimeLine = '16:10';
        $editLeaveEndTime = '16:30';
        $remark = '16:10:00';
    }
    // 16:15
    else if ($editLeaveEndTime == '16:15') {
        $editLeaveEndTimeLine = '16:15';
        $editLeaveEndTime = '16:30';
        $remark = '16:15:00';
    }
    // 16:40
    else if ($editLeaveEndTime == '17:00') {
        $editLeaveEndTimeLine = '16:40';
    } else {
        $editLeaveEndTimeLine = $editLeaveEndTime;
    }

    try {
        // สร้างคำสั่ง SQL สำหรับอัพเดตข้อมูล
        $sql = "UPDATE leave_list SET l_leave_id = :editLeaveType,
        l_leave_reason = :editLeaveReason,
        l_leave_start_date = :editLeaveStartDate,
        l_leave_end_date = :editLeaveEndDate,
        l_leave_start_time = :editLeaveStartTime,
        l_leave_end_time = :editLeaveEndTime,
        l_remark = :remark
        WHERE l_create_datetime = :editCreateDateTime
        AND l_usercode = :editUserCode ";

        // เตรียมคำสั่ง SQL
        $stmt = $conn->prepare($sql);

        // ผูกค่าพารามิเตอร์
        $stmt->bindParam(':editCreateDateTime', $editCreateDateTime);
        $stmt->bindParam(':editUserCode', $editUserCode);
        $stmt->bindParam(':editLeaveType', $editLeaveType);
        $stmt->bindParam(':editLeaveReason', $editLeaveReason);
        $stmt->bindParam(':editLeaveStartDate', $editLeaveStartDate);
        $stmt->bindParam(':editLeaveEndDate', $editLeaveEndDate);
        $stmt->bindParam(':editLeaveStartTime', $editLeaveStartTime);
        $stmt->bindParam(':editLeaveEndTime', $editLeaveEndTime);
        $stmt->bindParam(':remark', $remark);

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