<?php
// ปิดการแสดง warning และ notice ต่างๆ
error_reporting(0);

include '../connect.php';
include '../access_token_channel.php';

// ตั้งค่า header สำหรับ JSON
header('Content-Type: application/json');

// รับค่าจาก formData
$updName = isset($_POST['userName']) ? $_POST['userName'] : '';

$editCreateDateTime = isset($_POST['editCreateDateTime']) ? $_POST['editCreateDateTime'] : '';
$editUserCode       = isset($_POST['editUserCode']) ? $_POST['editUserCode'] : '';
$editLeaveType      = isset($_POST['editLeaveType']) ? $_POST['editLeaveType'] : '';
$editLeaveReason    = isset($_POST['editLeaveReason']) ? $_POST['editLeaveReason'] : '';
$editLeaveStartTime = isset($_POST['editLeaveStartTime']) ? $_POST['editLeaveStartTime'] : '';
$editLeaveEndTime   = isset($_POST['editLeaveEndTime']) ? $_POST['editLeaveEndTime'] : '';
$editTelPhone       = isset($_POST['editTelPhone']) ? $_POST['editTelPhone'] : '';
$editLeaveStartDate = isset($_POST['editLeaveStartDate']) ? $_POST['editLeaveStartDate'] : '';
$editLeaveEndDate   = isset($_POST['editLeaveEndDate']) ? $_POST['editLeaveEndDate'] : '';

$updDate = date('Y-m-d H:i:s');

// แก้ไขการแปลงวันที่ให้มีการตรวจสอบที่รัดกุมมากขึ้น
try {
    // ตรวจสอบว่ามีค่าวันที่ส่งมาหรือไม่
    if (empty($editLeaveStartDate) || empty($editLeaveEndDate)) {
        throw new Exception("ไม่ได้ระบุวันที่เริ่มต้นหรือวันที่สิ้นสุด");
    }

    // แปลงวันที่เริ่มต้น
    $startDateObj = DateTime::createFromFormat('d-m-Y', $editLeaveStartDate);
    if (! $startDateObj) {
        $debug['date_error'] = "ไม่สามารถแปลงวันที่เริ่มต้น: " . $editLeaveStartDate;
        throw new Exception("รูปแบบวันที่เริ่มต้นไม่ถูกต้อง");
    }
    $startDate = $startDateObj->format('Y-m-d');

    // แปลงวันที่สิ้นสุด
    $endDateObj = DateTime::createFromFormat('d-m-Y', $editLeaveEndDate);
    if (! $endDateObj) {
        $debug['date_error'] = "ไม่สามารถแปลงวันที่สิ้นสุด: " . $editLeaveEndDate;
        throw new Exception("รูปแบบวันที่สิ้นสุดไม่ถูกต้อง");
    }
    $endDate = $endDateObj->format('Y-m-d');

    // เพิ่มการบันทึกค่าวันที่เพื่อการตรวจสอบ
    $debug['converted_dates'] = [
        'start_date_raw' => $editLeaveStartDate,
        'end_date_raw'   => $editLeaveEndDate,
        'start_date'     => $startDate,
        'end_date'       => $endDate,
    ];

} catch (Exception $e) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'ข้อผิดพลาดในการแปลงวันที่: ' . $e->getMessage(),
        'debug'   => $debug,
    ]);
    exit;
}

$leaveTypes = [
    1 => 'ลากิจได้รับค่าจ้าง',
    2 => 'ลากิจไม่ได้รับค่าจ้าง',
    3 => 'ลาป่วย',
    4 => 'ลาป่วยจากงาน',
    5 => 'ลาพักร้อน',
    8 => 'อื่น ๆ',
];
$leaveName = isset($leaveTypes[$editLeaveType]) ? $leaveTypes[$editLeaveType] : 'ไม่พบประเภทการลา';

$timeMappings = [
    '08:00' => ['08:00', '08:00', '08:00:00'],
    '08:10' => ['08:10', '08:30', '08:10:00'],
    '08:15' => ['08:15', '08:30', '08:15:00'],
    '08:30' => ['08:30', '08:30', '08:30:00'],
    '08:45' => ['08:45', '09:00', '08:45:00'],
    '09:00' => ['09:00', '09:00', '09:00:00'],
    '09:10' => ['09:10', '09:30', '09:10:00'],
    '09:15' => ['09:15', '09:30', '09:15:00'],
    '09:30' => ['09:30', '09:30', '09:30:00'],
    '09:45' => ['09:45', '10:00', '09:45:00'],
    '10:00' => ['10:00', '10:00', '10:00:00'],
    '10:10' => ['10:10', '10:30', '10:10:00'],
    '10:15' => ['10:15', '10:30', '10:15:00'],
    '10:30' => ['10:30', '10:30', '10:30:00'],
    '10:45' => ['10:45', '11:00', '10:45:00'],
    '11:00' => ['11:00', '11:00', '11:00:00'],
    '11:10' => ['11:10', '11:30', '11:10:00'],
    '11:15' => ['11:15', '11:30', '11:30:00'],
    '11:30' => ['11:30', '11:30', '11:30:00'],
    '11:45' => ['11:45', '12:00', '11:45:00'],
    '12:00' => ['11:45', '12:00', '11:45:00'],
    '12:45' => ['12:45', '13:00', '12:45:00'],
    '13:00' => ['12:45', '13:00', '12:45:00'],
    '13:10' => ['13:10', '13:30', '13:10:00'],
    '13:15' => ['13:15', '13:30', '13:15:00'],
    '13:40' => ['13:40', '14:00', '13:40:00'],
    '13:45' => ['13:45', '14:00', '13:45:00'],
    '14:10' => ['14:10', '14:30', '14:10:00'],
    '14:15' => ['14:15', '14:30', '14:15:00'],
    '14:40' => ['14:40', '15:00', '14:40:00'],
    '14:45' => ['14:45', '15:00', '14:45:00'],
    '15:10' => ['15:10', '15:30', '15:10:00'],
    '15:15' => ['15:15', '15:30', '15:15:00'],
    '15:40' => ['15:40', '16:00', '15:40:00'],
    '15:45' => ['15:45', '16:00', '15:45:00'],
    '16:10' => ['16:10', '16:30', '16:10:00'],
    '16:15' => ['16:15', '16:30', '16:15:00'],
    '16:40' => ['16:40', '17:00', '16:40:00'],
    '17:00' => ['16:40', '17:00', '16:40:00'],
];

// กำหนดค่าเริ่มต้น
$editLeaveStartTimeLine = $editLeaveStartTime;
$timeRemark             = null;

if (isset($timeMappings[$editLeaveStartTime])) {
    $startTimeData          = $timeMappings[$editLeaveStartTime];
    $editLeaveStartTimeLine = $startTimeData[0];
    $editLeaveStartTime     = $startTimeData[1] . ':00';
    $timeRemark             = $startTimeData[2];
} else {
    // ถ้าไม่พบในตาราง ให้เพิ่ม :00
    $editLeaveStartTime = $editLeaveStartTime . ':00';
}

// กำหนดค่าเริ่มต้น
$editLeaveEndTimeLine = $editLeaveEndTime;
$timeRemark2          = null;

if (isset($timeMappings[$editLeaveEndTime])) {
    $endTimeData          = $timeMappings[$editLeaveEndTime];
    $editLeaveEndTimeLine = $endTimeData[0];
    $editLeaveEndTime     = $endTimeData[1] . ':00';
    $timeRemark2          = $endTimeData[2];
} else {
    // ถ้าไม่พบในตาราง ให้เพิ่ม :00
    $editLeaveEndTime = $editLeaveEndTime . ':00';
}

// ตั้งค่าตัวแปรไฟล์เริ่มต้นเป็น null
$uploadedFiles = [];

// จัดการอัปโหลดไฟล์ที่มาจาก editFile[]
if (isset($_FILES['editFile']) && is_array($_FILES['editFile']['name'])) {
    $fileCount = count($_FILES['editFile']['name']);

    // จำกัดไฟล์สูงสุด 3 ไฟล์
    $maxFiles = min($fileCount, 3);

    for ($i = 0; $i < $maxFiles; $i++) {
        if ($_FILES['editFile']['error'][$i] === UPLOAD_ERR_OK) {
            $fileName = $_FILES['editFile']['name'][$i];
            $tmpName  = $_FILES['editFile']['tmp_name'][$i];
            $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            // สร้างชื่อไฟล์ใหม่
            $newFileName = time() . '_' . ($i + 1) . '_' . $fileName;
            $location    = "../upload/" . $newFileName;

            $valid_extensions = ["jpg", "jpeg", "png", "pdf"];
            if (in_array($fileType, $valid_extensions)) {
                if (move_uploaded_file($tmpName, $location)) {
                    // เก็บข้อมูลไฟล์ที่อัปโหลดสำเร็จ
                    $uploadedFiles[] = $newFileName;
                } else {
                    echo json_encode([
                        'status'  => 'error',
                        'message' => 'ไม่สามารถอัปโหลดไฟล์ที่ ' . ($i + 1) . ' ได้',
                        'debug'   => $debug,
                    ]);
                    exit;
                }
            } else {
                echo json_encode([
                    'status'  => 'error',
                    'message' => 'รูปแบบไฟล์ที่ ' . ($i + 1) . ' ไม่ถูกต้อง (อนุญาตเฉพาะ jpg, jpeg, png, pdf)',
                    'debug'   => $debug,
                ]);
                exit;
            }
        }
    }
}

try {
    // ดึงข้อมูลรายการลาก่อนว่ามีอยู่หรือไม่
    $checkStmt = $conn->prepare("SELECT * FROM leave_list WHERE l_usercode = :userCode AND l_create_datetime = :createDatetime LIMIT 1");
    $checkStmt->bindParam(':userCode', $editUserCode);
    $checkStmt->bindParam(':createDatetime', $editCreateDateTime);
    $checkStmt->execute();

    if ($checkStmt->rowCount() === 0) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'ไม่พบข้อมูลรายการลาที่ต้องการแก้ไข',
            'debug'   => $debug,
        ]);
        exit;
    }

    // เตรียม SQL สำหรับการอัพเดต
    $sql = "UPDATE leave_list SET
            l_leave_id = :editLeaveType,
            l_leave_reason = :editLeaveReason,
            l_leave_start_date = :startDate,
            l_leave_start_time = :editLeaveStartTime,
            l_leave_end_date = :endDate,
            l_leave_end_time = :editLeaveEndTime,
            l_time_remark = :timeRemark,
            l_time_remark2 = :timeRemark2,
            l_phone = :editTelPhone,
            l_upd_datetime = :updDate,
            l_hr_upd_name = :updName";

    // เพิ่มเงื่อนไขการอัปเดตไฟล์เฉพาะเมื่อมีการอัปโหลดใหม่
    if (count($uploadedFiles) > 0) {
        if (isset($uploadedFiles[0])) {
            $sql .= ", l_file = :file1";
        }
        if (isset($uploadedFiles[1])) {
            $sql .= ", l_file2 = :file2";
        }
        if (isset($uploadedFiles[2])) {
            $sql .= ", l_file3 = :file3";
        }
    }

    $sql .= " WHERE l_usercode = :userCode AND l_create_datetime = :createDatetime";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':editLeaveType', $editLeaveType);
    $stmt->bindParam(':editLeaveReason', $editLeaveReason);
    $stmt->bindParam(':startDate', $startDate);
    $stmt->bindParam(':editLeaveStartTime', $editLeaveStartTime);
    $stmt->bindParam(':endDate', $endDate);
    $stmt->bindParam(':editLeaveEndTime', $editLeaveEndTime);
    $stmt->bindParam(':timeRemark', $timeRemark);
    $stmt->bindParam(':timeRemark2', $timeRemark2);
    $stmt->bindParam(':editTelPhone', $editTelPhone);
    $stmt->bindParam(':updDate', $updDate);
    $stmt->bindParam(':updName', $updName);

    // เพิ่ม bind เฉพาะเมื่อมีไฟล์
    if (count($uploadedFiles) > 0) {
        if (isset($uploadedFiles[0])) {
            $stmt->bindParam(':file1', $uploadedFiles[0]);
        }
        if (isset($uploadedFiles[1])) {
            $stmt->bindParam(':file2', $uploadedFiles[1]);
        }
        if (isset($uploadedFiles[2])) {
            $stmt->bindParam(':file3', $uploadedFiles[2]);
        }
    }

    $stmt->bindParam(':userCode', $editUserCode);
    $stmt->bindParam(':createDatetime', $editCreateDateTime);

    // ดำเนินการ SQL
    $result = $stmt->execute();

    if ($result) {
        // 🔍 ดึง e_user_id ของพนักงานจากตาราง employees
        $stmtEmp = $conn->prepare("SELECT e_user_id FROM employees WHERE e_usercode = :userCode");
        $stmtEmp->bindParam(':userCode', $editUserCode);
        $stmtEmp->execute();

        if ($stmtEmp->rowCount() > 0) {
            $empData   = $stmtEmp->fetch(PDO::FETCH_ASSOC);
            $empLineId = $empData['e_user_id'];

            if (! empty($empLineId)) {
                // ✅ เตรียมข้อความแจ้งเตือน
                $sMessage = "แจ้งเตือน\n"
                    . "ใบลาของคุณถูกแก้ไขเป็น\n"
                    . "ประเภท: $leaveName\n"
                    . "เหตุผล: $editLeaveReason\n"
                    . "วันที่: $editLeaveStartDate $editLeaveStartTimeLine ถึง $editLeaveEndDate $editLeaveEndTimeLine";

                $data = [
                    'to'       => $empLineId,
                    'messages' => [[
                        'type' => 'text',
                        'text' => $sMessage,
                    ]],
                ];

                $ch = curl_init('https://api.line.me/v2/bot/message/push');
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $access_token,
                ]);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                $response = curl_exec($ch);
                curl_close($ch);

                $debug['line_notify_response'] = $response;
            }
        }

        // ✅ ส่ง response กลับไป frontend
        echo json_encode([
            'status'  => 'success',
            'message' => 'อัพเดทข้อมูลการลาสำเร็จ และส่งแจ้งเตือนพนักงานแล้ว',
            'files'   => $uploadedFiles,
            'debug'   => $debug,
        ]);
    } else {
        echo json_encode([
            'status'  => 'error',
            'message' => 'ไม่สามารถอัพเดทข้อมูลได้',
            'debug'   => array_merge($debug, ['error_info' => $stmt->errorInfo()]),
        ]);
    }

} catch (PDOException $e) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'เกิดข้อผิดพลาด database: ' . $e->getMessage(),
        'debug'   => $debug,
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'เกิดข้อผิดพลาดทั่วไป: ' . $e->getMessage(),
        'debug'   => $debug,
    ]);
}