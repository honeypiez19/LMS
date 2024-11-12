<?php
include '../connect.php';
date_default_timezone_set('Asia/Bangkok');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Retrieve data from the request
        $userCode = $_POST['userCodeS'];
        $userName = $_POST['userNameS'];
        $name = $_POST['nameS'];
        $department = $_POST['departmentS'];
        $level = $_POST['levelS'];
        $telPhone = $_POST['telPhoneS'];
        $reason = $_POST['reasonS'];
        $startTime = $_POST['startTimeS'];
        $endTime = $_POST['endTimeS'];
        $addName = $_POST['addNameS'];
        $workplace = $_POST['workplaceS'];
        $subDepart = $_POST['subDepartS'];
        
        $startDate = date('Y-m-d', strtotime($_POST['startDateS']));
        $endDate = date('Y-m-d', strtotime($_POST['endDateS']));
        $createDatetime = date('Y-m-d H:i:s');

        // Default values
        $leaveType = 6;
        $leaveStatus = 0;
        $proveStatus = 0;
        $proveStatus2 = 1;

        // Insert late record
        $leaveSql = "INSERT INTO leave_list (
            l_usercode, l_username, l_name, l_department, l_level, l_phone, l_leave_id,
            l_leave_reason, l_leave_start_date, l_leave_start_time, l_leave_end_date,
            l_leave_end_time, l_leave_status,
            l_approve_status, l_approve_status2, l_hr_create_name, l_hr_create_datetime, l_workplace, l_create_datetime
        ) VALUES (
            :userCode, :userName, :name, :department, :level, :telPhone, :leaveType,
            :reason, :startDate, :startTime, :endDate, :endTime, :leaveStatus,
            :proveStatus, :proveStatus2, :addName, :addDate, :workplace, :createDatetime
        )";

        $stmt = $conn->prepare($leaveSql);
        $stmt->bindParam(':userCode', $userCode);
        $stmt->bindParam(':userName', $userName);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':department', $department);
        $stmt->bindParam(':level', $level);
        $stmt->bindParam(':telPhone', $telPhone);
        $stmt->bindParam(':leaveType', $leaveType);
        $stmt->bindParam(':reason', $reason);
        $stmt->bindParam(':startDate', $startDate);
        $stmt->bindParam(':startTime', $startTime);
        $stmt->bindParam(':endDate', $endDate);
        $stmt->bindParam(':endTime', $endTime);
        $stmt->bindParam(':leaveStatus', $leaveStatus);
        $stmt->bindParam(':proveStatus', $proveStatus);
        $stmt->bindParam(':proveStatus2', $proveStatus2);
        $stmt->bindParam(':addName', $addName);
        $stmt->bindParam(':addDate', $createDatetime);
        $stmt->bindParam(':workplace', $workplace);
        $stmt->bindParam(':createDatetime', $createDatetime);

        if ($stmt->execute()) {
            // Define notification message
            $supervisorURL = 'https://lms.system-samt.com/';
            $supervisorMessage = "$name หยุดงาน\nวันที่ : $startDate\nเวลา : $startTime ถึง $endTime\nสถานะรายการ : ปกติ\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $supervisorURL";

            // Prepare department-specific statement
            if ($department == 'RD') {
                $stmtDept = $conn->prepare("SELECT e_token, e_username FROM employees WHERE e_workplace = :workplace AND e_level = 'manager' AND e_sub_department = 'RD'");
            } elseif ($department == 'Office') {
                if ($subDepart == 'AC') {
                    $stmtDept = $conn->prepare("SELECT e_token, e_username FROM employees WHERE e_workplace = :workplace AND e_level = 'chief' AND e_sub_department = 'AC'");
                } elseif ($subDepart == 'Sales') {
                    $stmtDept = $conn->prepare("SELECT e_token, e_username FROM employees WHERE e_workplace = :workplace AND e_level = 'chief' AND e_sub_department = 'Sales'");
                } elseif ($subDepart == 'Store') {
                    $stmtDept = $conn->prepare("SELECT e_token, e_username FROM employees WHERE e_workplace = :workplace AND e_level = 'leader' AND e_sub_department = 'Store'");
                } elseif ($subDepart == 'All') {
                    $stmtDept = $conn->prepare("SELECT e_token, e_username FROM employees WHERE e_workplace = :workplace AND e_level = 'manager' AND e_sub_department = 'Office'");
                }
            } else {
                echo "ไม่พบเงื่อนไข";
                exit;
            }

            $stmtDept->bindParam(':workplace', $workplace);
            $stmtDept->execute();
            $supervisorTokens = $stmtDept->fetchAll(PDO::FETCH_COLUMN);

            // Send LINE Notify messages
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
                $result = curl_exec($chSupervisor);

                if (curl_errno($chSupervisor)) {
                    echo json_encode(['status' => 'error', 'message' => 'Notification Error: ' . curl_error($chSupervisor)]);
                }
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