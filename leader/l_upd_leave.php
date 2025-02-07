<?php
include '../connect.php';
include '../access_token_channel.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userCode           = $_POST['userCode'] ?? '';
    $editLeaveType      = $_POST['editLeaveType'] ?? '';
    $editLeaveReason    = $_POST['editLeaveReason'] ?? '';
    $editLeaveStartDate = ! empty($_POST['editLeaveStartDate']) ? date('Y-m-d', strtotime($_POST['editLeaveStartDate'])) : '';
    $editLeaveStartTime = $_POST['editLeaveStartTime'] ?? '';
    $editLeaveEndDate   = ! empty($_POST['editLeaveEndDate']) ? date('Y-m-d', strtotime($_POST['editLeaveEndDate'])) : '';
    $editLeaveEndTime   = $_POST['editLeaveEndTime'] ?? '';
    $currentFile        = $_POST['currentFile'] ?? '';
    $createDatetime     = $_POST['createDatetime'] ?? '';

    $userName  = $_POST['userName'];
    $name      = $_POST['name'];
    $workplace = $_POST['workplace'];
    $depart    = $_POST['depart'];

    $subDepart  = $_POST['subDepart'];
    $subDepart2 = $_POST['subDepart2'];
    $subDepart3 = $_POST['subDepart3'];
    $subDepart4 = $_POST['subDepart4'];
    $subDepart5 = $_POST['subDepart5'];

    $leaveTypes = [
        1 => 'ลากิจได้รับค่าจ้าง',
        2 => 'ลากิจไม่ได้รับค่าจ้าง',
        3 => 'ลาป่วย',
        4 => 'ลาป่วยจากงาน',
        5 => 'ลาพักร้อน',
        8 => 'อื่น ๆ',
    ];
    $leaveName = $leaveTypes[$editLeaveType] ?? 'ไม่พบประเภทการลา';

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

    if (isset($timeMappings[$editLeaveStartTime])) {
        [$editLeaveStartTimeLine, $editLeaveStartTime, $timeRemark] = $timeMappings[$editLeaveStartTime];
    } else {
        $editLeaveStartTimeLine = $editLeaveStartTime;
    }

    $timeMappings2 = [
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

    if (isset($editLeaveEndTime, $timeMappings2)) {
        list($editLeaveEndTimeLine, $editLeaveEndTime, $timeRemark2) = $timeMappings2[$editLeaveEndTime];
    } else {
        $editLeaveEndTimeLine = $editLeaveEndTime;
    }

    $filename = null;
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $tempName  = $_FILES['file']['tmp_name'];
        $extension = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

        if (in_array($extension, ["jpg", "jpeg", "png"])) {
            $filename   = uniqid() . '.' . $extension;
            $uploadPath = "../upload/" . $filename;

            if (! move_uploaded_file($tempName, $uploadPath)) {
                echo json_encode(['status' => 'error', 'message' => 'อัปโหลดไฟล์ไม่สำเร็จ']);
                exit;
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'ชนิดไฟล์ไม่รองรับ']);
            exit;
        }
    } else {
        $filename = $currentFile;
    }
    try {
        $sql = "UPDATE leave_list SET l_leave_id = :editLeaveType, l_leave_reason = :editLeaveReason,
            l_leave_start_date = :editLeaveStartDate, l_leave_start_time = :editLeaveStartTime,
            l_leave_end_date = :editLeaveEndDate, l_leave_end_time = :editLeaveEndTime,
            l_time_remark = :timeRemark,
            l_time_remark2 = :timeRemark2,
            l_file = :filename
            WHERE l_usercode = :userCode AND l_create_datetime = :createDatetime";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':editLeaveType', $editLeaveType);
        $stmt->bindParam(':editLeaveReason', $editLeaveReason);
        $stmt->bindParam(':editLeaveStartDate', $editLeaveStartDate);
        $stmt->bindParam(':editLeaveStartTime', $editLeaveStartTime);
        $stmt->bindParam(':editLeaveEndDate', $editLeaveEndDate);
        $stmt->bindParam(':editLeaveEndTime', $editLeaveEndTime);
        $stmt->bindParam(':filename', $filename);
        $stmt->bindParam(':createDatetime', $createDatetime);
        $stmt->bindParam(':userCode', $userCode);
        $stmt->bindParam(':timeRemark', $timeRemark);
        $stmt->bindParam(':timeRemark2', $timeRemark2);

        $stmt->execute();

        $sql = "SELECT e_user_id
            FROM employees
            WHERE e_workplace = :workplace
            AND e_level IN ('chief','manager','manger2','assisManager')
            AND (
                e_sub_department = :subDepart
                OR e_sub_department2 = :subDepart2
                OR e_sub_department3 = :subDepart3
                OR e_sub_department4 = :subDepart4
                OR e_sub_department5 = :subDepart5
            )";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':workplace', $workplace);
        $stmt->bindParam(':subDepart', $subDepart);
        $stmt->bindParam(':subDepart2', $subDepart2);
        $stmt->bindParam(':subDepart3', $subDepart3);
        $stmt->bindParam(':subDepart4', $subDepart4);
        $stmt->bindParam(':subDepart5', $subDepart5);

        $stmt->execute();

        $sURL     = 'https://lms.system-samt.com/';
        $sMessage = "$name แก้ไขใบลา\nประเภทการลา : $leaveName\nเหตุผลการลา : $editLeaveReason\nวันเวลาที่ลา : $editLeaveStartDate $editLeaveStartTimeLine ถึง $editLeaveEndDate $editLeaveEndTimeLine\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $sURL";

        $userIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($userIds as $userId) {
            $data = [
                'to'       => $userId,
                'messages' => [
                    [
                        'type' => 'text',
                        'text' => $sMessage,
                    ],
                ],
            ];

            $ch = curl_init('https://api.line.me/v2/bot/message/push');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $access_token,
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

            $responseLine = curl_exec($ch);
            if (curl_errno($ch)) {
                echo json_encode(['status' => 'error', 'message' => 'Error: ' . curl_error($ch)]);
            } else {
                curl_close($ch);
            }

        }
        echo json_encode(['status' => 'success', 'message' => 'แก้ไขข้อมูลสำเร็จ และแจ้งเตือนไปยังหัวหน้าแล้ว']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถแก้ไขข้อมูลได้: ' . $e->getMessage()]);
    }
}