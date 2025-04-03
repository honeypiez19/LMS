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

$updDate = date('Y-m-d H:i:s');

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
    '11:10' => ['11:10', '11:30', '11:10:00'],
    '11:15' => ['11:15', '11:30', '11:30:00'],
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

// ตั้งค่าตัวแปรไฟล์เริ่มต้นเป็น null
$filename1 = null;
$filename2 = null;
$filename3 = null;

// ตรวจสอบและอัปโหลดไฟล์ที่ 1
if (isset($_FILES['file1']) && $_FILES['file1']['error'] === UPLOAD_ERR_OK) {
    $filename1 = time() . '_1_' . $_FILES['file1']['name']; // เพิ่ม timestamp และลำดับไฟล์เพื่อป้องกันชื่อซ้ำ
    $location  = "../upload/" . $filename1;
    $fileType  = strtolower(pathinfo($location, PATHINFO_EXTENSION));

    $valid_extensions = ["jpg", "jpeg", "png", "pdf"]; // เพิ่ม PDF เข้าไปในรายการไฟล์ที่อนุญาต
    if (in_array($fileType, $valid_extensions)) {
        if (move_uploaded_file($_FILES['file1']['tmp_name'], $location)) {
            // อัปโหลดสำเร็จ
        } else {
            echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถอัปโหลดไฟล์ที่ 1 ได้']);
            exit;
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'รูปแบบไฟล์ที่ 1 ไม่ถูกต้อง']);
        exit;
    }
}

// จัดการอัปโหลดไฟล์ที่ 2
if (isset($_FILES['file2']) && $_FILES['file2']['error'] === UPLOAD_ERR_OK) {
    $filename2 = time() . '_2_' . $_FILES['file2']['name'];
    $location  = "../upload/" . $filename2;
    $fileType  = strtolower(pathinfo($location, PATHINFO_EXTENSION));

    $valid_extensions = ["jpg", "jpeg", "png", "pdf"];
    if (in_array($fileType, $valid_extensions)) {
        if (move_uploaded_file($_FILES['file2']['tmp_name'], $location)) {
            // อัปโหลดสำเร็จ
        } else {
            echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถอัปโหลดไฟล์ที่ 2 ได้']);
            exit;
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'รูปแบบไฟล์ที่ 2 ไม่ถูกต้อง']);
        exit;
    }
}

// จัดการอัปโหลดไฟล์ที่ 3
if (isset($_FILES['file3']) && $_FILES['file3']['error'] === UPLOAD_ERR_OK) {
    $filename3 = time() . '_3_' . $_FILES['file3']['name'];
    $location  = "../upload/" . $filename3;
    $fileType  = strtolower(pathinfo($location, PATHINFO_EXTENSION));

    $valid_extensions = ["jpg", "jpeg", "png", "pdf"];
    if (in_array($fileType, $valid_extensions)) {
        if (move_uploaded_file($_FILES['file3']['tmp_name'], $location)) {
            // อัปโหลดสำเร็จ
        } else {
            echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถอัปโหลดไฟล์ที่ 3 ได้']);
            exit;
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'รูปแบบไฟล์ที่ 3 ไม่ถูกต้อง']);
        exit;
    }
}

try {
    // ดึงข้อมูลสถานะการอนุมัติเดิมก่อน
    $sqlCheck = "SELECT l_approve_status, l_approve_status2, l_approve_status3, l_leave_status, l_hr_status,
                 l_approve_name, l_approve_name2, l_approve_name3, l_hr_name
                 FROM leave_list
                 WHERE l_usercode = :userCode AND l_create_datetime = :createDatetime";
    $stmtCheck = $conn->prepare($sqlCheck);
    $stmtCheck->bindParam(':userCode', $userCode);
    $stmtCheck->bindParam(':createDatetime', $createDatetime);
    $stmtCheck->execute();
    $currentStatus = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    $leaveStatus     = $currentStatus['l_leave_status'];
    $leaveStatusText = ($leaveStatus == 0) ? "ปกติ" : (($leaveStatus == 1) ? "ยกเลิก" : $leaveStatus);

    // ค้นหาและเตรียมข้อมูลผู้รับการแจ้งเตือนก่อน
    $sURL              = 'https://lms.system-samt.com/';
    $notifiedUsers     = [];
    $notifiedUsernames = []; // เก็บ username ที่ได้รับการแจ้งเตือนแล้วเพื่อป้องกันการส่งซ้ำ

                           // กำหนดระดับที่ต้องการแจ้งเตือนตามเงื่อนไข
    $notifyLevel1 = false; // หัวหน้า 1: leader, subLeader, chief
    $notifyLevel2 = false; // หัวหน้า 2: manager, manager2, assisManager
    $notifyLevel3 = false; // GM
    $notifyAdmin  = false; // admin

    // ตรวจสอบและกำหนดเงื่อนไขการแจ้งเตือน
    $status1  = $currentStatus['l_approve_status'];
    $status2  = $currentStatus['l_approve_status2'];
    $status3  = $currentStatus['l_approve_status3'];
    $hrStatus = $currentStatus['l_hr_status'];

// กำหนดค่าเริ่มต้นสำหรับระดับการแจ้งเตือน
    $notifyLevel1 = false; // หัวหน้า 1: leader, subLeader, chief
    $notifyLevel2 = false; // หัวหน้า 2: manager, manager2, assisManager
    $notifyLevel3 = false; // GM
    $notifyAdmin  = false; // admin

// Case 1: status1 = 0, status2 = 1, status3 = 7, hrStatus = 0 - แจ้งเตือนแค่หัวหน้า 1
    if ($status1 == 0 && $status2 == 1 && $status3 == 7 && $hrStatus == 0) {
        $notifyLevel1 = true;
    }
// Case 2: status1 = 0, status2 = 6, status3 = 7, hrStatus = 0 - แจ้งเตือนแค่หัวหน้า 1
    else if ($status1 == 0 && $status2 == 6 && $status3 == 7 && $hrStatus == 0) {
        $notifyLevel1 = true;
    }
// Case 3: status1 = 2, status2 = 6, status3 = 8, hrStatus = 0 - แจ้งเตือนหัวหน้า 1, GM, admin
    else if ($status1 == 2 && $status2 == 6 && $status3 == 8 && $hrStatus == 0) {
        $notifyLevel1 = true;
        $notifyLevel3 = true;
        $notifyAdmin  = true;
    }
// Case 4: status1 = 2, status2 = 6, status3 = 8, hrStatus = 1 - แจ้งเตือนหัวหน้า 1, GM, admin
    else if ($status1 == 2 && $status2 == 6 && $status3 == 8 && $hrStatus == 1) {
        $notifyLevel1 = true;
        $notifyLevel3 = true;
        $notifyAdmin  = true;
    }
// Case 5: status1 = 2, status2 = 1, status3 = 7, hrStatus = 0 - แจ้งเตือนหัวหน้า 1 กับ 2
    else if ($status1 == 2 && $status2 == 1 && $status3 == 7 && $hrStatus == 0) {
        $notifyLevel1 = true;
        $notifyLevel2 = true;
    }
// Case 6: status1 = 2, status2 = 4, status3 = 7, hrStatus = 0 - แจ้งเตือนหัวหน้า 1, หัวหน้า 2, GM
    else if ($status1 == 2 && $status2 == 4 && $status3 == 7 && $hrStatus == 0) {
        $notifyLevel1 = true;
        $notifyLevel2 = true;
        $notifyLevel3 = true;
    }
// Case 7: status1 = 2, status2 = 4, status3 = 8, hrStatus = 0 - แจ้งเตือนหัวหน้า 1, หัวหน้า 2, GM, admin
    else if ($status1 == 2 && $status2 == 4 && $status3 == 8 && $hrStatus == 0) {
        $notifyLevel1 = true;
        $notifyLevel2 = true;
        $notifyLevel3 = true;
        $notifyAdmin  = true;
    }
// Case 8: status1 = 2, status2 = 4, status3 = 8, hrStatus = 1 - แจ้งเตือนหัวหน้า 1, หัวหน้า 2, GM, admin
    else if ($status1 == 2 && $status2 == 4 && $status3 == 8 && $hrStatus == 1) {
        $notifyLevel1 = true;
        $notifyLevel2 = true;
        $notifyLevel3 = true;
        $notifyAdmin  = true;
    }
// Case 9: status1 = 6, status2 = 1, status3 = 7, hrStatus = 0 - แจ้งเตือนแค่หัวหน้า 2
    else if ($status1 == 6 && $status2 == 1 && $status3 == 7 && $hrStatus == 0) {
        $notifyLevel2 = true;
    }
// Case 10: status1 = 6, status2 = 4, status3 = 7, hrStatus = 0 - แจ้งเตือนหัวหน้า 2, GM
    else if ($status1 == 6 && $status2 == 4 && $status3 == 7 && $hrStatus == 0) {
        $notifyLevel2 = true;
        $notifyLevel3 = true;
    }
// Case 11-13: status1 = 6, status2 = 4, status3 = 8 - แจ้งเตือนหัวหน้า 2, GM, admin
    else if ($status1 == 6 && $status2 == 4 && $status3 == 8) {
        $notifyLevel2 = true;
        $notifyLevel3 = true;
        $notifyAdmin  = true;
    }
// Case 14: status1 = 6, status2 = 6, status3 = 7, hrStatus = 0 - แจ้งเตือนแค่ GM
    else if ($status1 == 6 && $status2 == 6 && $status3 == 7 && $hrStatus == 0) {
        $notifyLevel3 = true;
    }
// Case 15-16: status1 = 6, status2 = 6, status3 = 8 - แจ้งเตือน GM, admin
    else if ($status1 == 6 && $status2 == 6 && $status3 == 8) {
        $notifyLevel3 = true;
        $notifyAdmin  = true;
    }
    // 17 status1 = 2, status2 = 6, status3 = 7, hrStatus = 0 - แจ้งเตือนหัวหน้า 1, GM
    else if ($status1 == 2 && $status2 == 6 && $status3 == 7 && $hrStatus == 0) {
        $notifyLevel1 = true;
        $notifyLevel3 = true;
    }
// กรณีอื่นๆ ที่ไม่เข้าเงื่อนไขด้านบน
    else {
        $notifyAdmin = true; // แจ้ง admin เสมอเมื่อมีการแก้ไขใบลา
    }

    // เตรียมรายชื่อผู้อนุมัติที่ต้องแจ้งเตือน
    $approveNames = [];

    // เพิ่มชื่อผู้อนุมัติที่ไม่ซ้ำกันและไม่เป็นค่าว่าง
    if (! empty($currentStatus['l_approve_name'])) {
        $approveNames[] = $currentStatus['l_approve_name'];
    }

    if (! empty($currentStatus['l_approve_name2']) && ! in_array($currentStatus['l_approve_name2'], $approveNames)) {
        $approveNames[] = $currentStatus['l_approve_name2'];
    }

    if (! empty($currentStatus['l_approve_name3']) && ! in_array($currentStatus['l_approve_name3'], $approveNames)) {
        $approveNames[] = $currentStatus['l_approve_name3'];
    }

    if (! empty($currentStatus['l_hr_name']) && ! in_array($currentStatus['l_hr_name'], $approveNames)) {
        $approveNames[] = $currentStatus['l_hr_name'];
    }

    // ถ้ามีชื่อผู้อนุมัติ
    if (! empty($approveNames)) {
        $placeholders = implode(',', array_fill(0, count($approveNames), '?'));

        $sqlFindApprovers = "SELECT e_user_id, e_username FROM employees WHERE e_username IN ($placeholders)";
        $stmtApprovers    = $conn->prepare($sqlFindApprovers);
        $stmtApprovers->execute(array_values($approveNames));

        $approverUsers = $stmtApprovers->fetchAll(PDO::FETCH_ASSOC);

        // เพิ่มผู้อนุมัติเข้าไปในรายชื่อผู้รับแจ้งเตือน
        foreach ($approverUsers as $user) {
            if (! in_array($user['e_username'], $notifiedUsernames)) {
                $notifiedUsers[]     = $user;
                $notifiedUsernames[] = $user['e_username'];
            }
        }
    }

    // ค้นหาและเพิ่มผู้รับแจ้งเตือนตามระดับ
    // แจ้งเตือนระดับ 1 (leader, chief, subLeader)
    if ($notifyLevel1) {
        $sqlManagers = "SELECT e_user_id, e_username
                      FROM employees
                      WHERE e_level IN ('leader', 'chief', 'subLeader')
                      AND e_level <> :level
                      AND e_workplace = :workplace
                      AND (
                          (e_sub_department IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND e_sub_department <> '')
                          OR
                          (e_sub_department2 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND e_sub_department2 <> '')
                          OR
                          (e_sub_department3 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND e_sub_department3 <> '')
                          OR
                          (e_sub_department4 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND e_sub_department4 <> '')
                          OR
                          (e_sub_department5 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND e_sub_department5 <> '')
                      )";
        $stmtManagers = $conn->prepare($sqlManagers);
        $stmtManagers->bindParam(':depart', $depart);
        $stmtManagers->bindParam(':subDepart', $subDepart);
        $stmtManagers->bindParam(':subDepart2', $subDepart2);
        $stmtManagers->bindParam(':subDepart3', $subDepart3);
        $stmtManagers->bindParam(':subDepart4', $subDepart4);
        $stmtManagers->bindParam(':subDepart5', $subDepart5);
        $stmtManagers->bindParam(':workplace', $workplace);
        $stmtManagers->bindParam(':level', $level);

        if ($stmtManagers->execute()) {
            $managers = $stmtManagers->fetchAll(PDO::FETCH_ASSOC);
            foreach ($managers as $manager) {
                if (! in_array($manager['e_username'], $notifiedUsernames)) {
                    $notifiedUsers[]     = $manager;
                    $notifiedUsernames[] = $manager['e_username'];
                }
            }
        }
    }

    // แจ้งเตือนระดับ 2 (assisManager, manager, manager2)
    if ($notifyLevel2) {
        $sqlManagers2 = "SELECT e_user_id, e_username
                      FROM employees
                      WHERE e_level IN ('assisManager', 'manager', 'manager2')
                      AND e_level <> :level
                      AND e_workplace = :workplace
                      AND (
                          (e_sub_department IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND e_sub_department <> '')
                          OR
                          (e_sub_department2 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND e_sub_department2 <> '')
                          OR
                          (e_sub_department3 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND e_sub_department3 <> '')
                          OR
                          (e_sub_department4 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND e_sub_department4 <> '')
                          OR
                          (e_sub_department5 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND e_sub_department5 <> '')
                      )";
        $stmtManagers2 = $conn->prepare($sqlManagers2);
        // bind parameters เหมือนเดิม
        $stmtManagers2->bindParam(':depart', $depart);
        $stmtManagers2->bindParam(':subDepart', $subDepart);
        $stmtManagers2->bindParam(':subDepart2', $subDepart2);
        $stmtManagers2->bindParam(':subDepart3', $subDepart3);
        $stmtManagers2->bindParam(':subDepart4', $subDepart4);
        $stmtManagers2->bindParam(':subDepart5', $subDepart5);
        $stmtManagers2->bindParam(':workplace', $workplace);
        $stmtManagers2->bindParam(':level', $level);

        if ($stmtManagers2->execute()) {
            $managers2 = $stmtManagers2->fetchAll(PDO::FETCH_ASSOC);
            foreach ($managers2 as $manager) {
                if (! in_array($manager['e_username'], $notifiedUsernames)) {
                    $notifiedUsers[]     = $manager;
                    $notifiedUsernames[] = $manager['e_username'];
                }
            }
        }
    }

    // แจ้งเตือนระดับ 3 (GM)
    if ($notifyLevel3) {
        $sqlGM = "SELECT e_user_id, e_username
                  FROM employees
                  WHERE e_level = 'GM'
                  AND e_workplace = :workplace";
        $stmtGM = $conn->prepare($sqlGM);
        $stmtGM->bindParam(':workplace', $workplace);

        if ($stmtGM->execute()) {
            $gmUsers = $stmtGM->fetchAll(PDO::FETCH_ASSOC);
            foreach ($gmUsers as $gm) {
                if (! in_array($gm['e_username'], $notifiedUsernames)) {
                    $notifiedUsers[]     = $gm;
                    $notifiedUsernames[] = $gm['e_username'];
                }
            }
        }
    }

    // แจ้งเตือน Admin
    if ($notifyAdmin) {
        $sqlAdmin = "SELECT e_user_id, e_username
                     FROM employees
                     WHERE e_level = 'admin'
                     AND e_workplace = :workplace";
        $stmtAdmin = $conn->prepare($sqlAdmin);
        $stmtAdmin->bindParam(':workplace', $workplace);

        if ($stmtAdmin->execute()) {
            $adminUsers = $stmtAdmin->fetchAll(PDO::FETCH_ASSOC);
            foreach ($adminUsers as $admin) {
                if (! in_array($admin['e_username'], $notifiedUsernames)) {
                    $notifiedUsers[]     = $admin;
                    $notifiedUsernames[] = $admin['e_username'];
                }
            }
        }
    }

    // เตรียม SQL สำหรับการอัพเดต
    $sql = "UPDATE leave_list SET l_leave_id = :editLeaveType, l_leave_reason = :editLeaveReason,
            l_leave_start_date = :startDate, l_leave_start_time = :editLeaveStartTime,
            l_leave_end_date = :endDate, l_leave_end_time = :editLeaveEndTime,
            l_time_remark = :timeRemark,
            l_time_remark2 = :timeRemark2,
            l_upd_datetime = :updDate";

    // เพิ่มเงื่อนไขการอัปเดตไฟล์เฉพาะเมื่อมีการอัปโหลดใหม่
    if ($filename1 !== null) {
        $sql .= ", l_file = :file1";
    }
    if ($filename2 !== null) {
        $sql .= ", l_file2 = :file2";
    }
    if ($filename3 !== null) {
        $sql .= ", l_file3 = :file3";
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
    $stmt->bindParam(':updDate', $updDate);

    if ($filename1 !== null) {

        $stmt->bindParam(':file1', $filename1);
    }
    if ($filename2 !== null) {
        $stmt->bindParam(':file2', $filename2);
    }
    if ($filename3 !== null) {
        $stmt->bindParam(':file3', $filename3);
    }

    $stmt->bindParam(':userCode', $userCode);
    $stmt->bindParam(':createDatetime', $createDatetime);

    if ($stmt->execute()) {
        // ส่งแจ้งเตือนผ่าน LINE API
        foreach ($notifiedUsers as $user) {
            $proveNamee = $user['e_username'];
            $sMessage   = "K." . $proveNamee . "\n\n$name แก้ไขใบลาเป็น\nประเภทการลา : $leaveName\nเหตุผลการลา : $editLeaveReason\nวันเวลาที่ลา : $editLeaveStartDate $editLeaveStartTimeLine ถึง $editLeaveEndDate $editLeaveEndTimeLine\nสถานะใบลา : $leaveStatusText\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $sURL";

            $data = [
                'to'       => $user['e_user_id'],
                'messages' => [['type' => 'text', 'text' => $sMessage]],
            ];

            $ch = curl_init('https://api.line.me/v2/bot/message/push');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $access_token,
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

            curl_exec($ch);
            curl_close($ch);
        }

        // ส่งการตอบกลับเป็น JSON
        echo json_encode([
            'status'  => 'success',
            'message' => 'อัพเดตข้อมูลการลาสำเร็จ',
        ]);
    } else {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Error ในการอัพเดตข้อมูลการลา',
        ]);
    }

} catch (PDOException $e) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage(),
    ]);
}