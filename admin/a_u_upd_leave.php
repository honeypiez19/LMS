<?php
// เชื่อมต่อฐานข้อมูล
include '../connect.php';

// รับข้อมูลจาก POST
$createDatetime = $_POST['createDatetime'] ?? null;
$editLeaveType = $_POST['editLeaveType'] ?? null;
$editLeaveReason = $_POST['editLeaveReason'] ?? null;
$editLeaveStartDate = $_POST['editLeaveStartDate'] ?? null;
$editLeaveStartTime = $_POST['editLeaveStartTime'] ?? null;
$editLeaveEndDate = $_POST['editLeaveEndDate'] ?? null;
$editLeaveEndTime = $_POST['editLeaveEndTime'] ?? null;
$editTelPhone = $_POST['editTelPhone'] ?? null;
// $editApprover = $_POST['editApprover'] ?? null;

$userCode = $_POST['userCode'];
$userName = $_POST['userName'];
$name = $_POST['name'];
$workplace = $_POST['workplace'];
$depart = $_POST['depart'];
$subDepart = $_POST['subDepart'];

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

// จัดการอัปโหลดไฟล์
$filename = null;
if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    // ถ้ามีการอัปโหลดไฟล์ใหม่
    $tempName = $_FILES['file']['tmp_name'];
    $extension = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

    $validExtensions = ["jpg", "jpeg", "png"];
    if (in_array($extension, $validExtensions)) {
        $filename = uniqid() . '.' . $extension;
        $uploadPath = "../upload/" . $filename;

        if (!move_uploaded_file($tempName, $uploadPath)) {
            echo json_encode(['status' => 'error', 'message' => 'อัปโหลดไฟล์ไม่สำเร็จ']);
            exit;
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'ชนิดไฟล์ไม่รองรับ']);
        exit;
    }
} else {
    // ถ้าไม่มีการอัปโหลดไฟล์ใหม่ ใช้ไฟล์เดิม
    $filename = isset($_POST['currentFile']) ? $_POST['currentFile'] : null;
}

if ($editTelPhone) {
    // อัปเดตเบอร์โทรศัพท์ในตาราง employees
    $updateEmployeeSql = "UPDATE employees SET e_phone = :editTelPhone WHERE e_usercode = :userCode";

    $updateEmployeeStmt = $conn->prepare($updateEmployeeSql);
    $updateEmployeeStmt->bindParam(':editTelPhone', $editTelPhone);
    $updateEmployeeStmt->bindParam(':userCode', $userCode);

    // Execute the update
    if (!$updateEmployeeStmt->execute()) {
        echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถอัปเดตเบอร์โทรศัพท์ในตาราง employees']);
        exit;
    }
}

if ($subDepart == '') {
    $proveStatus = 6;
    $proveStatus2 = 1;
    $proveStatus3 = 6;
}

// if ($subDepart == 'RD') {
//     $proveStatus = 0;
//     $proveStatus2 = 1;
//     $proveStatus3 = 6;
// } else {
//     $proveStatus = 0;
//     $proveStatus2 = 1;
//     $proveStatus3 = 7;
// }

$sql = "UPDATE leave_list
        SET l_leave_id = :editLeaveType,
            l_leave_reason = :editLeaveReason,
            l_leave_start_date = :editLeaveStartDate,
            l_leave_start_time = :editLeaveStartTime,
            l_leave_end_date = :editLeaveEndDate,
            l_leave_end_time = :editLeaveEndTime,
            l_approve_status = 6,
            l_approve_status2 = 1,
            l_approve_status3 = 7,
            l_phone = :editTelPhone,
            l_hr_status = 0,
            l_remark = :remark";

// ตรวจสอบว่า $filename มีค่า (หมายความว่าไฟล์ใหม่ถูกอัปโหลด) แล้วอัปเดตข้อมูลไฟล์
if ($filename) {
    $sql .= ", l_file = :filename";
}

$sql .= " WHERE l_create_datetime = :createDatetime";

$stmt = $conn->prepare($sql);

$stmt->bindParam(':editLeaveType', $editLeaveType);
$stmt->bindParam(':editLeaveReason', $editLeaveReason);
$stmt->bindParam(':editLeaveStartDate', $editLeaveStartDate);
$stmt->bindParam(':editLeaveStartTime', $editLeaveStartTime);
$stmt->bindParam(':editLeaveEndDate', $editLeaveEndDate);
$stmt->bindParam(':editLeaveEndTime', $editLeaveEndTime);
$stmt->bindParam(':remark', $remark);
$stmt->bindParam(':editTelPhone', $editTelPhone);
// $stmt->bindParam(':proveStatus', $proveStatus);
$stmt->bindParam(':createDatetime', $createDatetime);

// ตรวจสอบว่าไฟล์ถูกอัปโหลดก่อนที่จะ bind ค่า $filename
if ($filename) {
    $stmt->bindParam(':filename', $filename);
}

$stmt->bindParam(':createDatetime', $createDatetime);

// Execute the query
if ($stmt->execute()) {
    // ส่งการแจ้งเตือน LINE
    $URL = 'https://lms.system-samt.com/';
    $message = "มีการแก้ไขใบลา $name\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด : $URL";

    if ($depart == 'RD') {
        $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE e_workplace = :workplace AND e_level = 'leader' AND e_sub_department = 'RD'");
    } else if ($depart == 'Office') {
        // บัญชี
        if ($subDepart == 'AC') {
            // แจ้งเตือนพี่แวว
            $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'chief' AND e_sub_department = 'AC'");
        }
        // เซลล์
        else if ($subDepart == 'Sales') {
            // แจ้งเตือนพี่เจี๊ยบหรือพี่อ้อม
            $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE e_workplace = :workplace AND e_level = 'chief' AND e_sub_department = 'Sales'");
        }
        // สโตร์
        else if ($subDepart == 'Store') {
            // แจ้งเตือนพี่เก๋
            $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'leader' AND e_sub_department = 'Store'");
        }
        // HR
        else if ($subDepart == 'All') {
            $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'manager' AND e_sub_department = 'Office'");
        }
        // พี่เต๋ / พี่น้อย / พี่ไว
        else if ($subDepart == '') {
            $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'manager' AND e_sub_department = 'Office'");
        }
    } else if ($depart == 'CAD1') {
        if ($subDepart == 'Modeling') {
            $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'leader' AND e_sub_department = 'Modeling'");
        } else if ($subDepart == 'Design') {
            $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'leader' AND e_sub_department = 'Design'");
        } else {
            $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'leader' AND e_sub_department = 'Design'");
        }
    } else if ($depart == 'CAD2') {
        $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'leader' AND e_sub_department = 'CAD2'");
    } else if ($depart == 'CAM') {
        $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'leader' AND e_sub_department = 'CAM'");
    } else {
        echo "ไม่พบเงื่อนไข";
    }

    $stmt->bindParam(':workplace', $workplace);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // ถ้ามีผลลัพธ์ของการค้นหาก็ส่ง Line Notify
    if ($result) {
        $token = $result['e_token'];

        // การส่ง LINE Notify
        $url = "https://notify-api.line.me/api/notify";
        $data = [
            'message' => $message,
        ];
        $headers = [
            'Authorization: Bearer ' . $token,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); // เปลี่ยนเป็น http_build_query
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // ส่งคำขอ
        $result = curl_exec($ch);
        curl_close($ch);
    }

    // ส่งผลลัพธ์การอัปเดตข้อมูล
    echo json_encode(['status' => 'success', 'message' => 'อัปเดตข้อมูลสำเร็จ']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาดในการอัปเดตข้อมูล']);
}