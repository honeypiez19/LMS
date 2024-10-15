<?php
include '../connect.php';
date_default_timezone_set('Asia/Bangkok');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $userCode = $_POST['userCode'];
        $userName = $_POST['userName'];
        $name = $_POST['name'];
        $department = $_POST['department'];
        $level = $_POST['level'];
        $telPhone = $_POST['telPhone'];
        $reason = $_POST['reason'];
        $startTime = $_POST['startTime'];
        $endTime = $_POST['endTime'];
        $addName = $_POST['addName'];
        $addDate = date('Y-m-d H:i:s');
        $workplace = $_POST['workplace'];
        $subDepart = $_POST['subDepart'];

        $startDate = date('Y-m-d', strtotime($_POST['startDate']));
        $endDate = date('Y-m-d', strtotime($_POST['endDate']));
        $createDatetime = date('Y-m-d H:i:s');

        $lateDatetime = date('Y-m-d H:i:s');

        $leaveType = 7;
        $leaveStatus = 0;
        $proveStatus = 0;
        $proveStatus2 = 1;

        // Check late count for user
        $stmt = $conn->prepare("SELECT COUNT(*) FROM leave_list WHERE l_usercode = :userCode AND l_leave_id = :leaveType");
        $stmt->bindParam(':userCode', $userCode, PDO::PARAM_STR);
        $stmt->bindParam(':leaveType', $leaveType, PDO::PARAM_INT);
        $stmt->execute();
        $lateCount = $stmt->fetchColumn();

        // Increment late count
        $lateCount++;

        // Add remark for supervisor
        $remark = "มาสายครั้งที่ $lateCount";

        // Insert late record
        $leaveSql = "INSERT INTO leave_list (
            l_usercode, l_username, l_name, l_department, l_level, l_phone, l_leave_id,
            l_leave_reason, l_leave_start_date, l_leave_start_time, l_leave_end_date,
            l_leave_end_time, l_leave_status, l_remark,
            l_approve_status, l_approve_status2, l_hr_create_name, l_hr_create_datetime, l_workplace, l_create_datetime
        ) VALUES (
            :userCode, :userName, :name, :department, :level, :telPhone, :leaveType,
            :reason, :startDate, :startTime, :endDate, :endTime, :leaveStatus,
             :remark, :proveStatus, :proveStatus2, :addName, :addDate, :workplace, :createDatetime
        )";

        $stmt = $conn->prepare($leaveSql);
        $stmt->bindParam(':userCode', $userCode, PDO::PARAM_STR);
        $stmt->bindParam(':userName', $userName, PDO::PARAM_STR);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':department', $department, PDO::PARAM_STR);
        $stmt->bindParam(':level', $level, PDO::PARAM_STR);
        $stmt->bindParam(':telPhone', $telPhone, PDO::PARAM_STR);
        $stmt->bindParam(':leaveType', $leaveType, PDO::PARAM_INT);
        $stmt->bindParam(':reason', $reason, PDO::PARAM_STR);
        $stmt->bindParam(':startDate', $startDate, PDO::PARAM_STR);
        $stmt->bindParam(':startTime', $startTime, PDO::PARAM_STR);
        $stmt->bindParam(':endDate', $endDate, PDO::PARAM_STR);
        $stmt->bindParam(':endTime', $endTime, PDO::PARAM_STR);
        $stmt->bindParam(':leaveStatus', $leaveStatus, PDO::PARAM_INT);
        $stmt->bindParam(':remark', $remark, PDO::PARAM_STR);
        $stmt->bindParam(':proveStatus', $proveStatus, PDO::PARAM_INT);
        $stmt->bindParam(':proveStatus2', $proveStatus2, PDO::PARAM_INT);
        $stmt->bindParam(':addName', $addName, PDO::PARAM_STR);
        $stmt->bindParam(':addDate', $addDate, PDO::PARAM_STR);
        $stmt->bindParam(':workplace', $workplace, PDO::PARAM_STR);
        $stmt->bindParam(':createDatetime', $createDatetime, PDO::PARAM_STR);

        if ($stmt->execute()) {
            // Additional action on 3rd late occurrence
            if ($lateCount % 3 == 0) {
                $remarkStopWork = "มาสายรวม $lateCount ครั้ง";
                $leaveSqlStopWork = "INSERT INTO leave_list (
                    l_usercode, l_username, l_name, l_department, l_level, l_phone,
                    l_leave_id, l_leave_reason, l_leave_start_date, l_leave_start_time,
                    l_leave_end_date, l_leave_end_time, l_leave_status,
                    l_remark, l_approve_status, l_approve_status2, l_hr_create_name, l_hr_create_datetime,  l_workplace , l_create_datetime
                ) VALUES (
                    :userCode, :userName, :name, :department, :level, :telPhone, 6,
                    :reason, :startDate, :startTime, :endDate, :endTime, :leaveStatus,
                     :remarkStopWork, :proveStatus, :proveStatus2, :addName, :addDate, :workplace, :createDatetime
                )";

                $stmtStopWork = $conn->prepare($leaveSqlStopWork);
                $stmtStopWork->bindParam(':userCode', $userCode);
                $stmtStopWork->bindParam(':userName', $userName);
                $stmtStopWork->bindParam(':name', $name);
                $stmtStopWork->bindParam(':department', $department);
                $stmtStopWork->bindParam(':level', $level);
                $stmtStopWork->bindParam(':telPhone', $telPhone);
                $stmtStopWork->bindParam(':reason', $reason);
                $stmtStopWork->bindParam(':startDate', $startDate);
                $stmtStopWork->bindParam(':startTime', $startTime);
                $stmtStopWork->bindParam(':endDate', $endDate);
                $stmtStopWork->bindParam(':endTime', $endTime);
                $stmtStopWork->bindParam(':leaveStatus', $leaveStatus);
                $stmtStopWork->bindParam(':remarkStopWork', $remarkStopWork);
                $stmtStopWork->bindParam(':proveStatus', $proveStatus);
                $stmtStopWork->bindParam(':proveStatus2', $proveStatus2);
                $stmtStopWork->bindParam(':addName', $addName);
                $stmtStopWork->bindParam(':addDate', $addDate);
                $stmtStopWork->bindParam(':workplace', $workplace);
                $stmt->bindParam(':createDatetime', $createDatetime, PDO::PARAM_STR);

                $stmtStopWork->execute();
            }

            // Prepare LINE Notify message
            $supervisorURL = 'https://lms.system-samt.com/';
            $supervisorMessage = "$name มาสาย\nวันที่มาสาย : $startDate\nเวลาที่มาสาย : $startTime ถึง $endTime\nสถานะรายการ : ปกติ\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $supervisorURL";

            if ($department == 'RD') {
                // แจ้งไลน์โฮซัง
                $stmtDept = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'manager' AND e_sub_department =  'RD'");
                // $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE e_department = 'Management' AND e_workplace = :workplace AND e_level = 'manager' AND e_sub_department = :depart");
                // $stmt = $conn->prepare("SELECT e_username, e_token FROM employees WHERE e_level = 'manager' AND e_workplace = 'Bang Phli' AND e_sub_department = 'RD'");
                $stmtDept->bindParam(':workplace', $workplace);
                // $stmt->bindParam(':depart', $depart);

            } else if ($department == 'Office') {
                // บัญชี
                if ($subDepart == 'AC') {
                    // แจ้งเตือนพี่แวว
                    // $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'chief' AND e_sub_department = :subDepart");
                    $stmtDept = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'chief' AND e_sub_department = 'AC'");
                    $stmtDept->bindParam(':workplace', $workplace);
                    // $stmt->bindParam(':subDepart', $subDepart);
                }
                // เซลล์
                else if ($subDepart == 'Sales') {
                    // แจ้งเตือนพี่เจี๊ยบหรือพี่อ้อม
                    // $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE e_workplace = :workplace AND e_level = 'chief' AND e_sub_department = :subDepart");
                    $stmtDept = $conn->prepare("SELECT e_token, e_username FROM employees WHERE e_workplace = :workplace AND e_level = 'chief' AND e_sub_department = 'Sales'");
                    $stmtDept->bindParam(':workplace', $workplace);
                    // $stmt->bindParam(':subDepart', $subDepart);
                }
                // สโตร์
                else if ($subDepart == 'Store') {
                    // แจ้งเตือนพี่เก๋
                    $stmtDept = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'leader' AND e_sub_department = 'Store'");
                    $stmtDept->bindParam(':workplace', $workplace);
                    // $stmt->bindParam(':subDepart', $subDepart);
                }
                // HR
                else if ($subDepart == 'All') {
                    $stmtDept = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'manager' AND e_sub_department = 'Office'");
                    $stmtDept->bindParam(':workplace', $workplace);
                    // $stmt->bindParam(':subDepart', $subDepart);
                }
                // พี่เต๋ / พี่น้อย / พี่ไว
                else if ($subDepart == '') {
                    $stmtDept = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'manager' AND e_sub_department = 'Office'");
                    $stmtDept->bindParam(':workplace', $workplace);
                    // $stmt->bindParam(':subDepart', $subDepart);
                }
            } else {
                echo "ไม่พบเงื่อนไข";
            }

            // $stmtDept->bindParam(':workplace', $workplace);
            $stmtDept->execute();
            $supervisorTokens = $stmtDept->fetchAll(PDO::FETCH_COLUMN);

            // Send notifications
            foreach ($supervisorTokens as $supervisorToken) {
                $chSupervisor = curl_init();
                curl_setopt($chSupervisor, CURLOPT_URL, "https://notify-api.line.me/api/notify");
                curl_setopt($chSupervisor, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($chSupervisor, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($chSupervisor, CURLOPT_POST, 1);
                curl_setopt($chSupervisor, CURLOPT_POSTFIELDS, "message=" . urlencode($supervisorMessage));
                $headersSupervisor = [
                    'Content-type: application/x-www-form-urlencoded',
                    'Authorization: Bearer ' . $supervisorToken,
                ];
                curl_setopt($chSupervisor, CURLOPT_HTTPHEADER, $headersSupervisor);
                curl_exec($chSupervisor);
                curl_close($chSupervisor);
            }
            echo json_encode(['status' => 'success', 'message' => 'บันทึกข้อมูลและส่ง LINE แจ้งเตือนเรียบร้อยแล้ว']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล']);
        }
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
}