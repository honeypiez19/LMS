<?php
session_start();
date_default_timezone_set('Asia/Bangkok');

include '../connect.php';
include '../access_token_channel.php';

// เพิ่มการตรวจสอบข้อมูลที่ส่งมา
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // เพิ่ม error reporting เพื่อดูข้อผิดพลาด
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // รับข้อมูลพื้นฐาน
    $userCode  = isset($_POST['userCode']) ? $_POST['userCode'] : null;
    $userName  = isset($_POST['userName']) ? $_POST['userName'] : null;
    $name      = isset($_POST['name']) ? $_POST['name'] : null;
    $telPhone  = isset($_POST['telPhone']) ? $_POST['telPhone'] : null;
    $depart    = isset($_POST['depart']) ? $_POST['depart'] : null;
    $level     = isset($_POST['level']) ? $_POST['level'] : null;
    $workplace = isset($_POST['workplace']) ? $_POST['workplace'] : null;

    // ข้อมูลการลา
    $leaveType   = isset($_POST['leaveType']) ? $_POST['leaveType'] : null;
    $leaveReason = isset($_POST['leaveReason']) ? $_POST['leaveReason'] : null;
    $approver    = isset($_POST['approver']) ? $_POST['approver'] : null;

    $proveDate2 = date('Y-m-d H:i:s');
    $proveName2 = $userName;

    // ตรวจสอบว่ามีข้อมูลจำเป็นครบถ้วนหรือไม่
    if (! $userCode || ! $userName || ! $name || ! $leaveType || ! $leaveReason || ! $approver) {
        echo "Error: ข้อมูลไม่ครบถ้วน";
        exit;
    }

    // ประเภทการลา
    $leaveTypes = [
        1 => 'ลากิจได้รับค่าจ้าง',
        2 => 'ลากิจไม่ได้รับค่าจ้าง',
        3 => 'ลาป่วย',
        4 => 'ลาป่วยจากงาน',
        5 => 'ลาพักร้อน',
        8 => 'อื่น ๆ',
    ];
    $leaveName = $leaveTypes[$leaveType] ?? 'ไม่พบประเภทการลา';

    // วันที่ + เวลาเริ่มต้นที่ลา
    $startDate = isset($_POST['startDate']) ? $_POST['startDate'] : null;
    $startTime = isset($_POST['startTime']) ? $_POST['startTime'] : null;

    if (! $startDate || ! $startTime) {
        echo "Error: วันที่หรือเวลาเริ่มต้นไม่ถูกต้อง";
        exit;
    }

    // แปลงรูปแบบวันที่
    $dateParts = explode('-', $startDate);
    if (count($dateParts) !== 3) {
        echo "Error: รูปแบบวันที่เริ่มต้นไม่ถูกต้อง (ต้องเป็น dd-mm-yyyy)";
        exit;
    }
    $leaveDateStart = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0]; // yyyy-mm-dd

    // วันที่ + เวลาสิ้นสุดที่ลา
    $endDate = isset($_POST['endDate']) ? $_POST['endDate'] : null;
    $endTime = isset($_POST['endTime']) ? $_POST['endTime'] : null;

    if (! $endDate || ! $endTime) {
        echo "Error: วันที่หรือเวลาสิ้นสุดไม่ถูกต้อง";
        exit;
    }

    // แปลงรูปแบบวันที่
    $dateParts = explode('-', $endDate);
    if (count($dateParts) !== 3) {
        echo "Error: รูปแบบวันที่สิ้นสุดไม่ถูกต้อง (ต้องเป็น dd-mm-yyyy)";
        exit;
    }
    $leaveDateEnd = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0]; // yyyy-mm-dd

    $leaveTimeStart = $startTime;
    $leaveTimeEnd   = $endTime;

    // กำหนดค่า mapping สำหรับเวลา
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
        '11:15' => ['11:15', '11:30', '11:15:00'],
        '11:45' => ['11:45', '12:00', null],
        '12:45' => ['12:45', '13:00', null],
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
        '16:40' => ['16:40', '17:00', null],
    ];

    // กำหนดค่าเริ่มต้นสำหรับตัวแปร time_remark
    $timeRemark         = null;
    $timeRemark2        = null;
    $leaveTimeStartLine = $leaveTimeStart;
    $leaveTimeEndLine   = $leaveTimeEnd;

    // ตรวจสอบและกำหนดค่าเวลาเริ่มต้น
    if (isset($timeMappings[$leaveTimeStart])) {
        $leaveTimeStartLine = $timeMappings[$leaveTimeStart][0];
        $leaveTimeStart     = $timeMappings[$leaveTimeStart][1] . ':00';
        $timeRemark         = $timeMappings[$leaveTimeStart][2];
    } else {
        // เพิ่มเวลาในรูปแบบที่ถูกต้องถ้าไม่พบในการ mapping
        $leaveTimeStart = $leaveTimeStart . ':00';
    }

    // ตรวจสอบและกำหนดค่าเวลาสิ้นสุด
    if (isset($timeMappings[$leaveTimeEnd])) {
        $leaveTimeEndLine = $timeMappings[$leaveTimeEnd][0];
        $leaveTimeEnd     = $timeMappings[$leaveTimeEnd][1] . ':00';
        $timeRemark2      = $timeMappings[$leaveTimeEnd][2];
    } else {
        // เพิ่มเวลาในรูปแบบที่ถูกต้องถ้าไม่พบในการ mapping
        $leaveTimeEnd = $leaveTimeEnd . ':00';
    }

    // รับค่าวันเวลาที่บันทึก
    $formattedDate = isset($_POST['formattedDate']) ? $_POST['formattedDate'] : date('Y-m-d H:i:s');

                          // สถานะใบลา
    $leaveStatus     = 0; // ปกติ
    $leaveStatusName = ($leaveStatus == 0) ? 'ปกติ' : 'ยกเลิก';
    $comfirmStatus   = 0;

    // รับค่า subDepart
    $subDepart  = isset($_POST['subDepart']) ? $_POST['subDepart'] : '';
    $subDepart2 = isset($_POST['subDepart2']) ? $_POST['subDepart2'] : '';
    $subDepart3 = isset($_POST['subDepart3']) ? $_POST['subDepart3'] : '';
    $subDepart4 = isset($_POST['subDepart4']) ? $_POST['subDepart4'] : '';
    $subDepart5 = isset($_POST['subDepart5']) ? $_POST['subDepart5'] : '';

    // ตัวแปรสำหรับเก็บชื่อไฟล์ทั้ง 3 ไฟล์
    $filename  = null;
    $filename2 = null;
    $filename3 = null;

    // ตรวจสอบและอัปโหลดไฟล์ที่ 1
    if (isset($_FILES['file1']) && $_FILES['file1']['error'] === UPLOAD_ERR_OK) {
        $filename = time() . '_1_' . $_FILES['file1']['name']; // เพิ่ม timestamp และลำดับไฟล์เพื่อป้องกันชื่อซ้ำ
        $location = "../upload/" . $filename;
        $fileType = strtolower(pathinfo($location, PATHINFO_EXTENSION));

        $valid_extensions = ["jpg", "jpeg", "png", "pdf"]; // เพิ่ม PDF เข้าไปในรายการไฟล์ที่อนุญาต
        if (in_array($fileType, $valid_extensions)) {
            if (move_uploaded_file($_FILES['file1']['tmp_name'], $location)) {
                // อัปโหลดสำเร็จ
            } else {
                echo "Error: ไม่สามารถอัปโหลดไฟล์ที่ 1 ได้";
                $filename = null; // กรณีอัปโหลดไม่สำเร็จ
            }
        } else {
            echo "Error: รูปแบบไฟล์ที่ 1 ไม่ถูกต้อง";
            $filename = null; // กรณีไฟล์ไม่ใช่รูปภาพหรือ PDF ที่รองรับ
        }
    }

    // จัดการอัปโหลดไฟล์ที่ 2
    if (isset($_FILES['file2']) && $_FILES['file2']['error'] === UPLOAD_ERR_OK) {
        $filename2 = time() . '_2_' . $_FILES['file2']['name'];
        $location  = "../upload/" . $filename2;
        $fileType  = strtolower(pathinfo($location, PATHINFO_EXTENSION));

        $valid_extensions = ["jpg", "jpeg", "png", "pdf"]; // เพิ่ม PDF เข้าไปในรายการไฟล์ที่อนุญาต
        if (in_array($fileType, $valid_extensions)) {
            if (move_uploaded_file($_FILES['file2']['tmp_name'], $location)) {
                // อัปโหลดสำเร็จ
            } else {
                echo "Error: ไม่สามารถอัปโหลดไฟล์ที่ 2 ได้";
                $filename2 = null;
            }
        } else {
            echo "Error: รูปแบบไฟล์ที่ 2 ไม่ถูกต้อง";
            $filename2 = null;
        }
    }

    // จัดการอัปโหลดไฟล์ที่ 3
    if (isset($_FILES['file3']) && $_FILES['file3']['error'] === UPLOAD_ERR_OK) {
        $filename3 = time() . '_3_' . $_FILES['file3']['name'];
        $location  = "../upload/" . $filename3;
        $fileType  = strtolower(pathinfo($location, PATHINFO_EXTENSION));

        $valid_extensions = ["jpg", "jpeg", "png", "pdf"]; // เพิ่ม PDF เข้าไปในรายการไฟล์ที่อนุญาต
        if (in_array($fileType, $valid_extensions)) {
            if (move_uploaded_file($_FILES['file3']['tmp_name'], $location)) {
                // อัปโหลดสำเร็จ
            } else {
                echo "Error: ไม่สามารถอัปโหลดไฟล์ที่ 3 ได้";
                $filename3 = null;
            }
        } else {
            echo "Error: รูปแบบไฟล์ที่ 3 ไม่ถูกต้อง";
            $filename3 = null;
        }
    }

    // เช็คหัวหน้าอนุมัติ
    try {

        $chkApprover = "SELECT e_sub_department, e_sub_department2, e_sub_department3, e_sub_department4, e_sub_department5, e_level FROM employees WHERE e_name = :approver";
        $stmt        = $conn->prepare($chkApprover);
        $stmt->bindParam(':approver', $approver, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

// กำหนดค่าเริ่มต้น
        $proveStatus  = 0;
        $proveStatus2 = 0;
        $proveStatus3 = 0;

        if ($result) {
            $departments = [
                $result['e_sub_department'],
                $result['e_sub_department2'],
                $result['e_sub_department3'],
                $result['e_sub_department4'],
                $result['e_sub_department5'],
            ];

            $levelApprover = $result['e_level'];

            $leaderChiefDepartments = ['Store', 'AC', 'Office', 'CAD1 Design Modeling', 'CAD2', 'CAM', 'Sales', 'MC', 'FN', 'PC', 'QC', 'RD'];
            $managerDepartments     = ['Store', 'AC', 'Office', 'CAD1 Design Modeling', 'CAD2', 'CAM', 'Sales', 'MC', 'FN', 'PC', 'QC', 'RD'];

            if (in_array($levelApprover, ['leader', 'chief'])) {
                if (in_array('Office', $departments)) {
                    $proveStatus = 6;
                }
                if (in_array('QC', $departments) || in_array('Sales', $departments)) {
                    $proveStatus2 = 6;
                } else {
                    $proveStatus2 = 4;
                }
                $proveStatus3 = 7;
            } elseif (in_array($levelApprover, ['manager', 'manager2', 'assisManager'])) {
                $proveStatus = 6;
                if (in_array('Sales', $departments) || in_array('QC', $departments)) {
                    $proveStatus2 = 6;
                } else {
                    $proveStatus2 = 4;
                }
                $proveStatus3 = 7;
            } elseif ($levelApprover === 'GM') {
                $proveStatus  = 6;
                $proveStatus2 = 4;
                $proveStatus3 = 7;
            } elseif ($levelApprover === 'admin') {
                $proveStatus  = 6;
                $proveStatus2 = 4;
                $proveStatus3 = 6;
            }
        }

// ทดสอบค่า
        // echo "$proveStatus, $proveStatus2, $proveStatus3";

// บันทึกข้อมูล
        try {
            $stmt = $conn->prepare("INSERT INTO leave_list (
l_usercode, l_username, l_name, l_department, l_phone, l_leave_id, l_leave_reason,
l_leave_start_date, l_leave_start_time, l_leave_end_date, l_leave_end_time, l_create_datetime,
l_file, l_file2, l_file3, l_leave_status, l_hr_status, l_approve_status,
l_level, l_approve_status2, l_workplace, l_remark, l_approve_status3, l_remark2,l_approve_name2,l_approve_datetime2)
VALUES (
:userCode, :userName, :name, :depart, :telPhone, :leaveType, :leaveReason,
:leaveDateStart, :leaveTimeStart, :leaveDateEnd, :leaveTimeEnd, :formattedDate,
:filename, :filename2, :filename3, :leaveStatus, :comfirmStatus, :proveStatus,
:level, :proveStatus2, :workplace, :timeRemark, :proveStatus3, :timeRemark2,:proveName2,:proveDate2)");

            $stmt->bindParam(':userCode', $userCode);
            $stmt->bindParam(':userName', $userName);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':depart', $depart);
            $stmt->bindParam(':telPhone', $telPhone);
            $stmt->bindParam(':leaveType', $leaveType);
            $stmt->bindParam(':leaveReason', $leaveReason);
            $stmt->bindParam(':leaveDateStart', $leaveDateStart);
            $stmt->bindParam(':leaveTimeStart', $leaveTimeStart);
            $stmt->bindParam(':leaveDateEnd', $leaveDateEnd);
            $stmt->bindParam(':leaveTimeEnd', $leaveTimeEnd);
            $stmt->bindParam(':formattedDate', $formattedDate);
            $stmt->bindParam(':filename', $filename);
            $stmt->bindParam(':filename2', $filename2);
            $stmt->bindParam(':filename3', $filename3);
            $stmt->bindParam(':leaveStatus', $leaveStatus);
            $stmt->bindParam(':comfirmStatus', $comfirmStatus);
            $stmt->bindParam(':proveStatus', $proveStatus);
            $stmt->bindParam(':proveStatus2', $proveStatus2);
            $stmt->bindParam(':level', $level);
            $stmt->bindParam(':workplace', $workplace);
            $stmt->bindParam(':timeRemark', $timeRemark);
            $stmt->bindParam(':timeRemark2', $timeRemark2);
            $stmt->bindParam(':proveStatus3', $proveStatus3);
            $stmt->bindParam(':proveName2', $proveName2);
            $stmt->bindParam(':proveDate2', $proveDate2);

            if ($stmt->execute()) {
                try {
                    $sql  = "SELECT e_user_id, e_username FROM employees WHERE e_name = :approver AND e_workplace = :workplace";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':approver', $approver);
                    $stmt->bindParam(':workplace', $workplace);
                    $stmt->execute();
                    $userList = $stmt->fetchAll(PDO::FETCH_ASSOC); // ดึงข้อมูลเป็น associative array

                    if ($userList) {
                        $sURL = 'https://lms.system-samt.com/';

                        foreach ($userList as $user) {
                            $userId     = $user['e_user_id'];
                            $proveNamee = $user['e_username'];

                            $sMessage = "K." . $proveNamee . "\n\nมีใบลาของ $name \nประเภทการลา : $leaveName\nเหตุผลการลา : $leaveReason\n" .
                                "วันเวลาที่ลา : $leaveDateStart $leaveTimeStartLine ถึง $leaveDateEnd $leaveTimeEndLine\n" .
                                "สถานะใบลา : $leaveStatusName\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด : $sURL";

                            $data = [
                                'to'       => $userId,
                                'messages' => [
                                    [
                                        'type' => 'text',
                                        'text' => $sMessage,
                                    ],
                                ],
                            ];

                            // ส่ง cURL request
                            $ch = curl_init('https://api.line.me/v2/bot/message/push');
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_POST, true);
                            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                                'Content-Type: application/json',
                                'Authorization: Bearer ' . $access_token,
                            ]);

                            $response  = curl_exec($ch);
                            $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                            $curlError = curl_error($ch);
                            curl_close($ch);

                            if ($response === false || $httpCode !== 200) {
                                error_log("Error sending Line message to $userId: " . ($curlError ?: "HTTP Code $httpCode"));
                            }
                        }
                    }
                } catch (Exception $e) {
                    error_log("Error in Line Notification: " . $e->getMessage());
                }

                echo "success"; // ส่งสถานะกลับไปยัง AJAX
            } else {
                echo "Error: " . $stmt->errorInfo()[2];
            }

        } catch (PDOException $e) {
            echo "Database Error: " . $e->getMessage();
        }
    } catch (PDOException $e) {
        echo "Database Connection Error: " . $e->getMessage();
    }

    $conn = null;
} else {
    echo "Invalid request method";
}
