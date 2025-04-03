<?php
include '../connect.php';
include '../access_token_channel.php';

date_default_timezone_set('Asia/Bangkok');

$leaveID        = $_POST['leaveId'];
$createDatetime = $_POST['createDatetime'];
$usercode       = $_POST['usercode'];
$name           = $_POST['name'];
$leaveType      = $_POST['leaveType'];
$leaveReason    = $_POST['leaveReason'];
$startDate      = $_POST['startDate'];
$endDate        = $_POST['endDate'];
$depart         = $_POST['depart'];
$level          = $_POST['level'];
$workplace      = $_POST['workplace'];
$subDepart      = $_POST['subDepart'];
$subDepart2     = $_POST['subDepart2'];
$subDepart3     = $_POST['subDepart3'];
$subDepart4     = $_POST['subDepart4'];
$subDepart5     = $_POST['subDepart5'];
$leaveStatus    = $_POST['leaveStatus'];

$canDatetime = date('Y-m-d H:i:s');
$proveDate3  = date('Y-m-d H:i:s');
$proveName3  = $_POST['userName']; // ชื่อผู้ใช้ปัจจุบัน (ผู้ที่กำลังทำรายการ)
try {
    // เริ่ม transaction
    $conn->beginTransaction();

    // ทำการ UPDATE ข้อมูลก่อน
    $sql = "UPDATE leave_list
                    SET l_leave_status = 1,
                    l_cancel_datetime = :canDatetime,
                    l_approve_status3 = 8,
                    l_approve_name3 = :proveName3,
                    l_approve_datetime3 = :proveDate3
                    WHERE l_leave_id = :leaveID
                    AND l_create_datetime = :createDatetime
            AND l_usercode= :usercode
            AND l_workplace= :workplace";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':leaveID', $leaveID);
    $stmt->bindParam(':createDatetime', $createDatetime);
    $stmt->bindParam(':canDatetime', $canDatetime);
    $stmt->bindParam(':usercode', $usercode);
    $stmt->bindParam(':workplace', $workplace);
    $stmt->bindParam(':proveName3', $proveName3);
    $stmt->bindParam(':proveDate3', $proveDate3);

    $updateSuccess = $stmt->execute();

    if (! $updateSuccess) {
        // ถ้าการอัพเดตไม่สำเร็จ ให้ rollback และส่งข้อความผิดพลาด
        $conn->rollBack();
        echo json_encode([
            'status'  => 'error',
            'message' => 'Error ในการอัพเดตข้อมูลการลา',
        ]);
        exit;
    }

    // หลังจาก UPDATE สำเร็จแล้ว ค่อยตรวจสอบเงื่อนไขต่างๆ เพื่อการแจ้งเตือน
    $sqlCheck = "SELECT l_approve_status, l_approve_status2, l_approve_status3, l_leave_status, l_hr_status,
                     l_approve_name, l_approve_name2, l_approve_name3, l_hr_name
                 FROM leave_list
                 WHERE l_usercode = :usercode AND l_create_datetime = :createDatetime";
    $stmtCheck = $conn->prepare($sqlCheck);
    $stmtCheck->bindParam(':usercode', $usercode);
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

    $isLeader  = in_array($level, ['leader', 'chief', 'subLeader']);
    $isManager = in_array($level, ['manager', 'manager2', 'assisManager']); // เพิ่มตัวแปรสำหรับตรวจสอบผู้ใช้ระดับ 2
    $isGM      = $level == 'GM';

    // ตรวจสอบและกำหนดเงื่อนไขการแจ้งเตือน
    $status1  = $currentStatus['l_approve_status'];
    $status2  = $currentStatus['l_approve_status2'];
    $status3  = $currentStatus['l_approve_status3'];
    $hrStatus = $currentStatus['l_hr_status'];

    // Case 1: status1 = 0, status2 = 1, status3 = 7, hrStatus = 0 - แจ้งเตือนแค่หัวหน้า 1
    if ($leaveStatus == 1 && $status1 == 0 && $status2 == 1 && $status3 == 7 && $hrStatus == 0) {
        $notifyLevel1 = true;
    }
    // Case 2: status1 = 0, status2 = 6, status3 = 7, hrStatus = 0 - แจ้งเตือนแค่หัวหน้า 1
    else if ($leaveStatus == 1 && $status1 == 0 && $status2 == 6 && $status3 == 7 && $hrStatus == 0) {
        $notifyLevel1 = true;
    }
    // Case 3: status1 = 2, status2 = 6, status3 = 8, hrStatus = 0 - แจ้งเตือนหัวหน้า 1, GM, admin
    else if ($leaveStatus == 1 && $status1 == 2 && $status2 == 6 && $status3 == 8 && $hrStatus == 0) {
        $notifyLevel1 = true;
        $notifyLevel3 = true;
        $notifyAdmin  = true;
    }
    // Case 4: status1 = 2, status2 = 6, status3 = 8, hrStatus = 1 - แจ้งเตือนหัวหน้า 1, GM, admin
    else if ($leaveStatus == 1 && $status1 == 2 && $status2 == 6 && $status3 == 8 && $hrStatus == 1) {
        $notifyLevel1 = true;
        $notifyLevel3 = true;
        $notifyAdmin  = true;
    }
    // Case 5: status1 = 2, status2 = 1, status3 = 7, hrStatus = 0 - แจ้งเตือนหัวหน้า 1 กับ 2
    else if ($leaveStatus == 1 && $status1 == 2 && $status2 == 1 && $status3 == 7 && $hrStatus == 0) {
        $notifyLevel1 = true;
        $notifyLevel2 = true;
    }
    // Case 6: status1 = 2, status2 = 4, status3 = 7, hrStatus = 0 - แจ้งเตือนหัวหน้า 1, หัวหน้า 2, GM
    else if ($leaveStatus == 1 && $status1 == 2 && $status2 == 4 && $status3 == 7 && $hrStatus == 0) {
        $notifyLevel1 = true;
        $notifyLevel2 = true;
        $notifyLevel3 = true;
    }
    // Case 7: status1 = 2, status2 = 4, status3 = 8, hrStatus = 0 - แจ้งเตือนหัวหน้า 1, หัวหน้า 2, GM, admin
    else if ($leaveStatus == 1 && $status1 == 2 && $status2 == 4 && $status3 == 8 && $hrStatus == 0) {
        $notifyLevel1 = true;
        $notifyLevel2 = true;
        $notifyLevel3 = true;
        $notifyAdmin  = true;
    }
    // Case 8: status1 = 2, status2 = 4, status3 = 8, hrStatus = 1 - แจ้งเตือนหัวหน้า 1, หัวหน้า 2, GM, admin
    else if ($leaveStatus == 1 && $status1 == 2 && $status2 == 4 && $status3 == 8 && $hrStatus == 1) {
        $notifyLevel1 = true;
        $notifyLevel2 = true;
        $notifyLevel3 = true;
        $notifyAdmin  = true;
    }
    // Case 9: status1 = 6, status2 = 1, status3 = 7, hrStatus = 0 - แจ้งเตือนแค่หัวหน้า 2
    else if ($leaveStatus == 1 && $status1 == 6 && $status2 == 1 && $status3 == 7 && $hrStatus == 0) {
        $notifyLevel2 = true;
    }
    // Case 10: status1 = 6, status2 = 4, status3 = 7, hrStatus = 0 - แจ้งเตือนหัวหน้า 2, GM
    else if ($leaveStatus == 1 && $status1 == 6 && $status2 == 4 && $status3 == 7 && $hrStatus == 0) {
        $notifyLevel2 = true;
        $notifyLevel3 = true;
    }
    // Case 11-13: status1 = 6, status2 = 4, status3 = 8 - แจ้งเตือนหัวหน้า 2, GM, admin
    else if ($leaveStatus == 1 && $status1 == 6 && $status2 == 4 && $status3 == 8) {
        $notifyLevel2 = true;
        $notifyLevel3 = true;
        $notifyAdmin  = true;
    }
    // Case 14: status1 = 6, status2 = 6, status3 = 7, hrStatus = 0 - แจ้งเตือนแค่ GM
    else if ($leaveStatus == 1 && $status1 == 6 && $status2 == 6 && $status3 == 7 && $hrStatus == 0) {
        $notifyLevel3 = true;
    }
    // Case 15-16: status1 = 6, status2 = 6, status3 = 8 - แจ้งเตือน GM, admin
    else if ($leaveStatus == 1 && $status1 == 6 && $status2 == 6 && $status3 == 8) {
        $notifyLevel3 = true;
        $notifyAdmin  = true;
    }
    // 17 status1 = 2, status2 = 6, status3 = 7, hrStatus = 0 - แจ้งเตือนหัวหน้า 1, GM
    else if ($leaveStatus == 1 && $status1 == 2 && $status2 == 6 && $status3 == 7 && $hrStatus == 0) {
        $notifyLevel1 = true;
        $notifyLevel3 = true;
    }
    // กรณีอื่นๆ ที่ไม่เข้าเงื่อนไขด้านบน
    else if ($leaveStatus == 1) {
        $notifyAdmin = true; // แจ้ง admin เสมอเมื่อมีการแก้ไขใบลา
    }

    if ($isLeader) {
        $notifyLevel1 = false;
    }

    if ($isManager) {
        $notifyLevel2 = false;
    }

// ถ้าผู้ใช้เป็น GM จะไม่ส่งแจ้งเตือน GM
    if ($isGM) {
        $notifyLevel3 = false;
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

    // ยืนยัน transaction เมื่อการอัพเดตข้อมูลและการดึงข้อมูลเรียบร้อย
    $conn->commit();

    // ส่งแจ้งเตือนผ่าน LINE API
    foreach ($notifiedUsers as $user) {
        if ($user['e_username'] === $proveName3) {
            continue; // ข้ามการส่งแจ้งเตือนให้กับตัวผู้ใช้เอง
        }

        $proveNamee = $user['e_username'];
        $sMessage   = "K." . $proveNamee . "\n\n$name ยกเลิกใบลา\n" .
            "ประเภทการลา : $leaveType\n" .
            "เหตุผลการลา : $leaveReason\n" .
            "วันเวลาที่ลา : $startDate ถึง $endDate\n" .
            "สถานะใบลา : ยกเลิก\n" .
            "กรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $sURL";

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

} catch (Exception $e) {
    // กรณีเกิดข้อผิดพลาด ให้ rollback transaction
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    // บันทึกข้อผิดพลาดและส่งข้อความกลับ
    error_log("เกิดข้อผิดพลาด: " . $e->getMessage());
    echo json_encode([
        'status'  => 'error',
        'message' => 'Error: ' . $e->getMessage(),
    ]);
}