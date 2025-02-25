<?php
// ปิดการแสดง warning และ notice ต่างๆ
error_reporting(0);

include '../connect.php';
include '../access_token_channel.php';

// ตั้งค่า header สำหรับ JSON
header('Content-Type: application/json');

// ตรวจสอบ request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

// รับค่าจาก formData
$userCode           = isset($_POST['userCode']) ? $_POST['userCode'] : '';
$userName           = isset($_POST['userName']) ? $_POST['userName'] : '';
$name               = isset($_POST['name']) ? $_POST['name'] : '';
$workplace          = isset($_POST['workplace']) ? $_POST['workplace'] : '';
$depart             = isset($_POST['depart']) ? $_POST['depart'] : '';
$subDepart          = isset($_POST['subDepart']) ? $_POST['subDepart'] : '';
$subDepart2         = isset($_POST['subDepart2']) ? $_POST['subDepart2'] : '';
$subDepart3         = isset($_POST['subDepart3']) ? $_POST['subDepart3'] : '';
$subDepart4         = isset($_POST['subDepart4']) ? $_POST['subDepart4'] : '';
$subDepart5         = isset($_POST['subDepart5']) ? $_POST['subDepart5'] : '';
$level              = isset($_POST['level']) ? $_POST['level'] : '';
$createDatetime     = isset($_POST['createDatetime']) ? $_POST['createDatetime'] : '';
$editLeaveType      = isset($_POST['editLeaveType']) ? $_POST['editLeaveType'] : '';
$editLeaveReason    = isset($_POST['editLeaveReason']) ? $_POST['editLeaveReason'] : '';
$editLeaveStartDate = isset($_POST['editLeaveStartDate']) ? $_POST['editLeaveStartDate'] : '';
$editLeaveStartTime = isset($_POST['editLeaveStartTime']) ? $_POST['editLeaveStartTime'] : '';
$editLeaveEndDate   = isset($_POST['editLeaveEndDate']) ? $_POST['editLeaveEndDate'] : '';
$editLeaveEndTime   = isset($_POST['editLeaveEndTime']) ? $_POST['editLeaveEndTime'] : '';
$editTelPhone       = isset($_POST['editTelPhone']) ? $_POST['editTelPhone'] : '';

// ตรวจสอบข้อมูลที่จำเป็น
if (empty($userCode) || empty($createDatetime)) {
    echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วน']);
    exit;
}

$startDate = date("Y-m-d", strtotime($editLeaveStartDate));
$endDate   = date("Y-m-d", strtotime($editLeaveEndDate));

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
    '08:10' => ['08:10', '08:30', '08:10:00'],
    '08:15' => ['08:15', '08:30', '08:15:00'],
    '08:45' => ['08:45', '09:00', '08:45:00'],
    '09:10' => ['09:10', '09:30', '09:10:00'],
    '09:15' => ['09:15', '09:30', '09:15:00'],
    '09:45' => ['09:45', '10:00', '09:45:00'],
    '10:10' => ['10:10', '10:30', '10:10:00'],
    '10:15' => ['10:15', '10:30', '10:15:00'],
    '10:45' => ['10:45', '11:00', '10:45:00'],
    '12:00' => ['11:45', '12:00', null],
    '13:00' => ['12:45', '13:00', null],
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
    '17:00' => ['16:40', '17:00', null],
];

// กำหนดค่าเริ่มต้น
$editLeaveStartTimeLine = $editLeaveStartTime;
$timeRemark             = null;

if (isset($timeMappings[$editLeaveStartTime])) {
    $startTimeData          = $timeMappings[$editLeaveStartTime];
    $editLeaveStartTimeLine = $startTimeData[0];
    $editLeaveStartTime     = $startTimeData[1];
    $timeRemark             = $startTimeData[2];
}

// กำหนดค่าเริ่มต้น
$editLeaveEndTimeLine = $editLeaveEndTime;
$timeRemark2          = null;

if (isset($timeMappings[$editLeaveEndTime])) {
    $endTimeData          = $timeMappings[$editLeaveEndTime];
    $editLeaveEndTimeLine = $endTimeData[0];
    $editLeaveEndTime     = $endTimeData[1];
    $timeRemark2          = $endTimeData[2];
}

// จัดการไฟล์
$filename = null; // กำหนดค่าเริ่มต้น

if (isset($_FILES['file']) && $_FILES['file']['error'] == UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['file']['tmp_name'];
    $fileName    = $_FILES['file']['name'];
    $fileSize    = $_FILES['file']['size'];
    $fileType    = $_FILES['file']['type'];

    $allowedFileTypes = ['image/jpeg', 'image/png', 'application/pdf'];
    if (in_array($fileType, $allowedFileTypes)) {
        $uploadDir = 'uploads/';
        $dest_path = $uploadDir . $fileName;

        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            $filename = $dest_path;
        } else {
            echo json_encode([
                'status'  => 'error',
                'message' => 'ไม่สามารถอัปโหลดไฟล์ได้',
            ]);
            exit;
        }
    } else {
        echo json_encode([
            'status'  => 'error',
            'message' => 'ไฟล์ไม่ถูกต้อง',
        ]);
        exit;
    }
} elseif (isset($_POST['currentFile']) && ! empty($_POST['currentFile'])) {
    $filename = $_POST['currentFile'];
}

try {
    $sql = "UPDATE leave_list SET l_leave_id = :editLeaveType, l_leave_reason = :editLeaveReason,
            l_leave_start_date = :startDate, l_leave_start_time = :editLeaveStartTime,
            l_leave_end_date = :endDate, l_leave_end_time = :editLeaveEndTime,
            l_time_remark = :timeRemark,
            l_time_remark2 = :timeRemark2,
            l_file = :filename
            WHERE l_usercode = :userCode AND l_create_datetime = :createDatetime";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':editLeaveType', $editLeaveType);
    $stmt->bindParam(':editLeaveReason', $editLeaveReason);
    $stmt->bindParam(':startDate', $startDate);
    $stmt->bindParam(':editLeaveStartTime', $editLeaveStartTime);
    $stmt->bindParam(':endDate', $endDate);
    $stmt->bindParam(':editLeaveEndTime', $editLeaveEndTime);
    $stmt->bindParam(':filename', $filename);
    $stmt->bindParam(':createDatetime', $createDatetime);
    $stmt->bindParam(':userCode', $userCode);
    $stmt->bindParam(':timeRemark', $timeRemark);
    $stmt->bindParam(':timeRemark2', $timeRemark2);

    if ($stmt->execute()) {
        $sURL        = 'https://lms.system-samt.com/';
        $lineMessage = "$name แก้ไขใบลา\nประเภทการลา : $leaveName\nเหตุผลการลา : $editLeaveReason\nวันเวลาที่ลา : $editLeaveStartDate $editLeaveStartTimeLine ถึง $editLeaveEndDate $editLeaveEndTimeLine\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $sURL";

        $sql =
            "SELECT e_user_id, e_username
            FROM employees
            WHERE e_level IN ('leader', 'chief', 'assisManager', 'manager', 'manager2', 'GM', 'subLeader')
            AND e_level <> :level
            AND (
                (e_sub_department IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND e_sub_department <> '')
                OR (e_sub_department2 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND e_sub_department2 <> '')
                OR (e_sub_department3 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND e_sub_department3 <> '')
                OR (e_sub_department4 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND e_sub_department4 <> '')
                OR (e_sub_department5 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND e_sub_department5 <> '')
                OR (
                    e_level = 'GM'
                    AND :depart <> 'RD'
                    AND (
                        e_sub_department IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5)
                        OR e_sub_department2 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5)
                        OR e_sub_department3 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5)
                        OR e_sub_department4 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5)
                        OR e_sub_department5 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5)
                        OR (
                            e_sub_department IS NULL
                            AND e_sub_department2 IS NULL
                            AND e_sub_department3 IS NULL
                            AND e_sub_department4 IS NULL
                            AND e_sub_department5 IS NULL
                        )
                    )
                )
            )
            AND e_workplace = :workplace";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':subDepart', $subDepart);
        $stmt->bindParam(':subDepart2', $subDepart2);
        $stmt->bindParam(':subDepart3', $subDepart3);
        $stmt->bindParam(':subDepart4', $subDepart4);
        $stmt->bindParam(':subDepart5', $subDepart5);
        $stmt->bindParam(':depart', $depart);
        $stmt->bindParam(':workplace', $workplace);
        $stmt->bindParam(':level', $level);

        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // ส่งข้อความ Line (ข้ามส่วนนี้หากต้องการทดสอบเฉพาะการอัปเดตข้อมูล)

        foreach ($result as $row) {
            $user_id = $row['e_user_id'];

            $postData = [
                'to'       => $user_id,
                'messages' => [
                    [
                        'type' => 'text',
                        'text' => $lineMessage,
                    ],
                ],
            ];

            $headers = [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $access_token,
            ];

            $ch = curl_init('https://api.line.me/v2/bot/message/push');
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
            curl_exec($ch);
            curl_close($ch);
        }

        echo json_encode([
            'status'  => 'success',
            'message' => 'แก้ไขข้อมูลสำเร็จ',
        ]);
    } else {
        $errorInfo = $stmt->errorInfo();
        echo json_encode([
            'status'  => 'error',
            'message' => 'ไม่สามารถอัปเดตข้อมูลได้: ' . $errorInfo[2],
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage(),
    ]);
}