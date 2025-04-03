<?php
session_start();
include '../connect.php';
include '../access_token_channel.php'; // สมมติว่าไฟล์นี้มีตัวแปร $access_token สำหรับ LINE Message API

// เพิ่ม error reporting เพื่อช่วยในการ debug
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../error_log.txt');

date_default_timezone_set('Asia/Bangkok'); // เวลาไทย

// รับค่าจาก AJAX
$leaveType      = isset($_POST['leaveType']) ? $_POST['leaveType'] : '';
$empName        = isset($_POST['empName']) ? $_POST['empName'] : '';
$depart         = isset($_POST['depart']) ? $_POST['depart'] : '';
$leaveReason    = isset($_POST['leaveReason']) ? $_POST['leaveReason'] : '';
$userCode       = isset($_POST['userCode']) ? $_POST['userCode'] : '';
$createDate     = isset($_POST['createDate']) ? $_POST['createDate'] : '';
$leaveStartDate = isset($_POST['leaveStartDate']) ? $_POST['leaveStartDate'] : '';
$leaveEndDate   = isset($_POST['leaveEndDate']) ? $_POST['leaveEndDate'] : '';
$checkFirm      = isset($_POST['checkFirm']) ? $_POST['checkFirm'] : '';
$userName       = isset($_POST['userName']) ? $_POST['userName'] : '';
$leaveStatus    = isset($_POST['leaveStatus']) ? $_POST['leaveStatus'] : '';
$rejectReason   = isset($_POST['rejectReason']) ? $_POST['rejectReason'] : '';

// บันทึกข้อมูลที่ได้รับเพื่อตรวจสอบ
error_log("ข้อมูลที่ได้รับจาก AJAX: " . json_encode($_POST));

// แปลงรูปแบบวันที่ createDate เป็น Y-m-d H:i:s
$formattedCreateDate = $createDate;
if (! empty($createDate)) {
    // ตรวจสอบรูปแบบวันที่
    $datePatterns = [
        '/^(\d{2})\/(\d{2})\/(\d{4}) (\d{2}):(\d{2}):(\d{2})$/', // dd/mm/yyyy HH:MM:SS
        '/^(\d{2})-(\d{2})-(\d{4}) (\d{2}):(\d{2}):(\d{2})$/',   // dd-mm-yyyy HH:MM:SS
        '/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/',   // yyyy-mm-dd HH:MM:SS
    ];

    foreach ($datePatterns as $pattern) {
        if (preg_match($pattern, $createDate, $matches)) {
            if (count($matches) >= 7) {
                if ($pattern == $datePatterns[0]) { // dd/mm/yyyy
                    $formattedCreateDate = $matches[3] . '-' . $matches[2] . '-' . $matches[1] . ' ' .
                        $matches[4] . ':' . $matches[5] . ':' . $matches[6];
                } else if ($pattern == $datePatterns[1]) { // dd-mm-yyyy
                    $formattedCreateDate = $matches[3] . '-' . $matches[2] . '-' . $matches[1] . ' ' .
                        $matches[4] . ':' . $matches[5] . ':' . $matches[6];
                }
                // yyyy-mm-dd ไม่ต้องแปลง
            }
            break;
        }
    }
}

$firmDate = date("Y-m-d H:i:s");
$pass     = '1';
$passNo   = '2';

try {
    $conn->beginTransaction();

    // Log transaction start
    error_log("เริ่ม transaction - checkFirm: $checkFirm, leaveStatus: $leaveStatus");

    if ($checkFirm == '1') {
        if ($leaveStatus == 'ยกเลิก') {
            // อัปเดตสถานะตามที่ต้องการโดยไม่ต้องแจ้งเตือน GM หรือ admin
            $sqlUpdateCancelStatus = "UPDATE leave_list SET l_hr_status = :pass, l_hr_datetime = :firmDate, l_hr_name = :userName
                WHERE l_usercode = :userCode AND l_create_datetime = :createDate";

            $stmt = $conn->prepare($sqlUpdateCancelStatus);
            $stmt->bindParam(':pass', $pass);
            $stmt->bindParam(':firmDate', $firmDate);
            $stmt->bindParam(':userName', $userName);
            $stmt->bindParam(':userCode', $userCode);
            $stmt->bindParam(':createDate', $formattedCreateDate);

            // Log query execution
            error_log("Executing SQL (cancel): $sqlUpdateCancelStatus with params: " .
                json_encode(['pass' => $pass, 'firmDate'       => $firmDate, 'userName' => $userName,
                    'userCode'          => $userCode, 'createDate' => $formattedCreateDate]));

            $stmt->execute();
            $affectedRows = $stmt->rowCount();
            error_log("จำนวนแถวที่อัปเดต (ยกเลิก): $affectedRows");

            if ($affectedRows > 0) {
                $conn->commit();
                echo json_encode(['status' => 'success', 'message' => 'อัปเดตสถานะยกเลิกสำเร็จ', 'affected' => $affectedRows]);
            } else {
                $conn->rollBack();
                echo json_encode(['status' => 'error', 'message' => 'ไม่พบข้อมูลที่ต้องการอัปเดต', 'affected' => 0]);
            }
        } else {
            // Update leave status for non-cancel case
            $sql = "UPDATE leave_list SET l_hr_status = :pass, l_hr_datetime = :firmDate, l_hr_name = :userName
                WHERE l_usercode = :userCode AND l_create_datetime = :createDate";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':pass', $pass);
            $stmt->bindParam(':firmDate', $firmDate);
            $stmt->bindParam(':userName', $userName);
            $stmt->bindParam(':userCode', $userCode);
            $stmt->bindParam(':createDate', $formattedCreateDate);

            // Log query execution
            error_log("Executing SQL (approve): $sql with params: " .
                json_encode(['pass' => $pass, 'firmDate'       => $firmDate, 'userName' => $userName,
                    'userCode'          => $userCode, 'createDate' => $formattedCreateDate]));

            $stmt->execute();
            $affectedRows = $stmt->rowCount();
            error_log("จำนวนแถวที่อัปเดต (อนุมัติ): $affectedRows");

            if ($affectedRows > 0) {
                $stmt = $conn->prepare("SELECT e_user_id FROM employees WHERE e_usercode = :userCode");
                $stmt->bindParam(':userCode', $userCode);
                $stmt->execute();
                $userId = $stmt->fetchColumn();

                $sURL     = 'https://lms.system-samt.com/';
                $sMessage = "K." . $empName . "\n\nHR ตรวจสอบใบลาผ่านเรียบร้อย\nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nสถานะใบลา : $leaveStatus\nวันเวลาที่ลา : $leaveStartDate ถึง $leaveEndDate\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $sURL";

                if ($leaveStatus == 'ยกเลิก') {
                    $sMessage = "K." . $empName . "\n\nHR ตรวจสอบยกเลิกใบลาผ่านเรียบร้อย\nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nสถานะใบลา : $leaveStatus\nวันเวลาที่ลา : $leaveStartDate ถึง $leaveEndDate\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $sURL";
                }

                $conn->commit();
                error_log("Committed transaction");

                // แยกการส่งแจ้งเตือนออกจากการอัปเดตข้อมูล
                $notificationStatus  = 'success';
                $notificationMessage = '';

                if ($userId) {
                    $response = sendLineMessage($userId, $sMessage, $access_token);
                    error_log("ผลการส่งแจ้งเตือนไปยัง " . $empName . ": " . json_encode($response));

                    if (isset($response['message'])) {
                        error_log("แจ้งเตือนล้มเหลว: " . $response['message']);
                        $notificationStatus  = 'warning';
                        $notificationMessage = 'อัปเดตสถานะสำเร็จ แต่การส่งแจ้งเตือนล้มเหลว';
                    } else {
                        error_log("ส่งข้อความแจ้งเตือนสำเร็จ");
                        $notificationMessage = 'อัปเดตสถานะผ่านสำเร็จและส่งแจ้งเตือนแล้ว';
                    }
                } else {
                    error_log("ไม่พบ user_id สำหรับพนักงาน " . $empName);
                    $notificationStatus  = 'warning';
                    $notificationMessage = 'อัปเดตสถานะสำเร็จ แต่ไม่พบ LINE ID ของพนักงาน';
                }

                echo json_encode([
                    'status'   => $notificationStatus,
                    'message'  => $notificationMessage,
                    'affected' => $affectedRows,
                ]);
            } else {
                $conn->rollBack();
                error_log("Rolled back transaction - no rows affected");
                echo json_encode(['status' => 'error', 'message' => 'ไม่พบข้อมูลที่ต้องการอัปเดต', 'affected' => 0]);
            }
        }
    } else {
        // ถ้า checkFirm = 2 (ไม่ผ่าน)
        // Update leave status
        $sql = "UPDATE leave_list SET l_hr_status = :passNo, l_hr_datetime = :firmDate, l_hr_name = :userName, l_hr_reason = :rejectReason
                WHERE l_usercode = :userCode AND l_create_datetime = :createDate";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':passNo', $passNo);
        $stmt->bindParam(':firmDate', $firmDate);
        $stmt->bindParam(':userName', $userName);
        $stmt->bindParam(':userCode', $userCode);
        $stmt->bindParam(':createDate', $formattedCreateDate); // แก้ไขให้ใช้ตัวแปรที่ถูกต้อง
        $stmt->bindParam(':rejectReason', $rejectReason);

        // Log query execution
        error_log("Executing SQL (reject): $sql with params: " .
            json_encode(['passNo' => $passNo, 'firmDate'     => $firmDate, 'userName'                => $userName,
                'userCode'            => $userCode, 'createDate' => $formattedCreateDate, 'rejectReason' => $rejectReason]));

        $stmt->execute();
        $affectedRows = $stmt->rowCount();
        error_log("จำนวนแถวที่อัปเดต (ไม่ผ่าน): $affectedRows");

        if ($affectedRows > 0) {
            // รายชื่อผู้ที่ต้องแจ้งเตือน
            $notifyList = [];

            // ดึงข้อมูลใบลาเพื่อหา l_approve_name, l_approve_name2, l_approve_name3, level ของพนักงาน
            $sqlGetLeaveInfo = "SELECT l_approve_name, l_approve_name2, l_approve_name3, l_username, l_level
                               FROM leave_list
                               WHERE l_usercode = :userCode AND l_create_datetime = :createDate";
            $stmt = $conn->prepare($sqlGetLeaveInfo);
            $stmt->bindParam(':userCode', $userCode);
            $stmt->bindParam(':createDate', $formattedCreateDate); // แก้ไขให้ใช้ตัวแปรที่ถูกต้อง
            $stmt->execute();
            $leaveInfo = $stmt->fetch(PDO::FETCH_ASSOC);

            // เก็บระดับของพนักงานที่ขอลา
            $employeeLevel = $leaveInfo['l_level'];

            // เพิ่มพนักงานที่ขอลาเข้าไปในรายการแจ้งเตือน
            $notifyList[] = [
                'username' => $leaveInfo['l_username'],
                'role'     => 'employee',
            ];

            // เพิ่มหัวหน้าคนที่ 1 (l_approve_name) ถ้ามี
            if (! empty($leaveInfo['l_approve_name'])) {
                $notifyList[] = [
                    'username' => $leaveInfo['l_approve_name'],
                    'role'     => 'approver1',
                ];
            }

            // เพิ่มหัวหน้าคนที่ 2 (l_approve_name2) ถ้ามี
            if (! empty($leaveInfo['l_approve_name2'])) {
                $notifyList[] = [
                    'username' => $leaveInfo['l_approve_name2'],
                    'role'     => 'approver2',
                ];
            }

            // เพิ่มหัวหน้าคนที่ 3 (l_approve_name3) ถ้ามี
            if (! empty($leaveInfo['l_approve_name3'])) {
                $notifyList[] = [
                    'username' => $leaveInfo['l_approve_name3'],
                    'role'     => 'approver3',
                ];
            }

            $sURL         = 'https://lms.system-samt.com/';
            $successCount = 0;
            $failCount    = 0;
            $skipCount    = 0;

            // วนลูปส่งแจ้งเตือนไปยังทุกคนในรายการ
            foreach ($notifyList as $recipient) {
                // ดึง LINE user ID และข้อมูลอื่นๆ ของผู้รับ
                $stmt = $conn->prepare("SELECT e_user_id, e_name, e_level FROM employees WHERE e_username = :username");
                $stmt->bindParam(':username', $recipient['username']);
                $stmt->execute();
                $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($userInfo && ! empty($userInfo['e_user_id'])) {
                    // ลบเงื่อนไขที่ข้ามการส่งแจ้งเตือนถ้าเป็น manager และมีระดับเดียวกัน
                    // เพื่อให้ส่งแจ้งเตือนไปยังทุกคนในรายการ รวมถึงพนักงานที่เป็น manager ด้วย

                    // สร้างข้อความแจ้งเตือนแตกต่างกันตามบทบาท
                    if ($recipient['role'] == 'employee') {
                        $sMessage = "K." . $recipient['username'] . "\n\nHR ตรวจสอบใบลาไม่ผ่านเรียบร้อย\nเหตุผลที่ไม่ผ่าน : $rejectReason\nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nสถานะใบลา : $leaveStatus\nวันเวลาที่ลา : $leaveStartDate ถึง $leaveEndDate\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $sURL";

                        if ($leaveStatus == 'ยกเลิก') {
                            $sMessage = "K." . $recipient['username'] . "\n\nHR ตรวจสอบยกเลิกใบลาไม่ผ่านเรียบร้อย\nเหตุผลที่ไม่ผ่าน : $rejectReason\nประเภทการลา: $leaveType\nเหตุผลการลา : $leaveReason\nสถานะใบลา : $leaveStatus\nวันเวลาที่ลา : $leaveStartDate ถึง $leaveEndDate\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $sURL";
                        }
                    } else { // approver1, approver2, approver3
                        $sMessage = "K." . $recipient['username'] . "\n\nHR ตรวจสอบใบลาของ $empName ที่คุณได้อนุมัติไปแล้วไม่ผ่าน\nเหตุผลที่ไม่ผ่าน : $rejectReason\nประเภทการลา  : $leaveType\nเหตุผลการลา : $leaveReason\nสถานะใบลา : $leaveStatus\nวันเวลาที่ลา : $leaveStartDate ถึง $leaveEndDate\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $sURL";

                        if ($leaveStatus == 'ยกเลิก') {
                            $sMessage = "K." . $recipient['username'] . "\n\nHR ตรวจสอบยกเลิกใบลาของ $empName ที่คุณได้อนุมัติไปแล้วไม่ผ่าน\nเหตุผลที่ไม่ผ่าน : $rejectReason\nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nสถานะใบลา : $leaveStatus\nวันเวลาที่ลา : $leaveStartDate ถึง $leaveEndDate\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $sURL";
                        }
                    }

                    $response = sendLineMessage($userInfo['e_user_id'], $sMessage, $access_token);
                    error_log("ผลการส่งแจ้งเตือนไปยัง " . $recipient['username'] . " (" . $recipient['role'] . "): " . print_r($response, true));

                    if (isset($response['message'])) {
                        error_log("แจ้งเตือนล้มเหลวสำหรับ " . $recipient['username'] . ": " . $response['message']);
                        $failCount++;
                    } else {
                        error_log("ส่งข้อความแจ้งเตือนสำเร็จถึง " . $recipient['username']);
                        $successCount++;
                    }
                } else {
                    error_log("ไม่พบ user_id สำหรับ " . $recipient['username']);
                    $failCount++;
                }
            }

            $conn->commit();
            error_log("Committed transaction (reject case)");

            // สรุปผลการส่งแจ้งเตือน
            if ($successCount > 0 && $failCount == 0 && $skipCount == 0) {
                echo json_encode(['status' => 'success', 'message' => 'อัปเดตสถานะไม่ผ่านสำเร็จและส่งแจ้งเตือนครบทุกคน']);
            } else if ($successCount > 0) {
                $message = "อัปเดตสถานะไม่ผ่านสำเร็จ ส่งแจ้งเตือนสำเร็จ $successCount คน";
                if ($failCount > 0) {
                    $message .= " ล้มเหลว $failCount คน";
                }
                if ($skipCount > 0) {
                    $message .= " ข้าม $skipCount คน";
                }
                echo json_encode(['status' => 'warning', 'message' => $message]);
            } else {
                echo json_encode(['status' => 'warning', 'message' => 'อัปเดตสถานะไม่ผ่านสำเร็จ แต่การส่งแจ้งเตือนล้มเหลวทั้งหมด']);
            }
        } else {
            $conn->rollBack();
            error_log("Rolled back transaction (reject case) - no rows affected");
            echo json_encode(['status' => 'error', 'message' => 'ไม่พบข้อมูลที่ต้องการอัปเดต', 'affected' => 0]);
        }
    }
} catch (Exception $e) {
    $conn->rollBack();
    error_log("เกิดข้อผิดพลาด: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}

function sendLineMessage($userId, $message, $access_token)
{
    if (empty($userId) || empty($message) || empty($access_token)) {
        error_log("ข้อมูลสำหรับส่งแจ้งเตือนไม่ครบถ้วน: userId=" . (empty($userId) ? "ไม่มี" : "มี") .
            ", message=" . (empty($message) ? "ไม่มี" : "มี") .
            ", access_token=" . (empty($access_token) ? "ไม่มี" : "มี"));
        return ['status' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วน'];
    }

    $messageData = [
        'to'       => $userId,
        'messages' => [['type' => 'text', 'text' => $message]],
    ];

    $jsonData = json_encode($messageData);
    $ch       = curl_init('https://api.line.me/v2/bot/message/push');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $access_token,
    ]);

    $result    = curl_exec($ch);
    $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);

    if ($curlError) {
        error_log("cURL Error: " . $curlError);
    }

    error_log("LINE API Response Code: " . $httpCode);
    error_log("LINE API Response: " . $result);

    curl_close($ch);

    return json_decode($result, true);
}