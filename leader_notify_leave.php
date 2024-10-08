<?php

include 'connect.php';
date_default_timezone_set('Asia/Bangkok');

// ดึงข้อมูลการแจ้งเตือนที่ส่งไปแล้ว
$sqlLog = "SELECT n_leave_id FROM notification_log WHERE n_send_date >= DATE_SUB(NOW(), INTERVAL 2 DAY)";
$stmtLog = $conn->prepare($sqlLog);
$stmtLog->execute();
$sentLeaveIds = $stmtLog->fetchAll(PDO::FETCH_COLUMN);

// ดึงข้อมูลการลา
$sql = "SELECT li.l_usercode, li.l_username, li.l_name, li.l_leave_start_date, li.l_leave_start_time, li.l_leave_end_date, li.l_leave_end_time,
li.l_leave_id, li.l_department, li.l_workplace ,li.l_level, em.e_sub_department, em.e_sub_department2,
em.e_sub_department3, em.e_sub_department4, em.e_sub_department5 FROM leave_list li
INNER JOIN employees em ON li.l_usercode = em.e_usercode
WHERE l_leave_id <> 6 AND l_leave_id <> 7";

$stmt = $conn->prepare($sql);
$stmt->execute();
$leaveRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

// เช็คข้อมูลการลาแต่ละรายการ
$today = new DateTime();
foreach ($leaveRecords as $row) {
    $userName = $row['l_username'];
    $userCode = $row['l_usercode'];
    $nameEmp = $row['l_name'];
    $startDate = $row['l_leave_start_date'];
    $startTime = $row['l_leave_start_time'];
    $endDate = $row['l_leave_end_date'];
    $endTime = $row['l_leave_end_time'];
    $leaveId = $row['l_leave_id'];
    $depart = $row['l_department'];
    $workplace = $row['l_workplace'];
    $level = $row['l_level'];

    // กำหนดวันลา
    $leaveDate = new DateTime($endDate);
    $leaveDate->setTime(0, 0);

    // คำนวณระยะห่าง
    $interval = $today->diff($leaveDate);

    // URL
    $mURL = 'https://lms.system-samt.com/';

    // แสดงผล
    echo 'Leave Date: ' . $leaveDate->format('Y-m-d') . '<br>';
    echo 'Interval Days: ' . $interval->days . '<br>';
    echo 'Name: ' . $nameEmp . '<br>';
    echo 'userCode: ' . $userCode . '<br>';
    echo 'level: ' . $level . '<br>';
    echo 'depart: ' . $depart . '<br>';

    if ($workplace === 'Bang Phli' && $interval->days == 2) {
        // เช็คใบลาว่าผู้จัดการอนุมัติยัง ? ก่อนวันที่ลา 2 วัน
        $message = "ใบลาของ $nameEmp ยังไม่อนุมัติ\nวันที่ลา : $startDate $startTime ถึง $endDate $endTime\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด : $mURL";
        $sqlLine = "";

        if ($level === 'leader') {
            $sqlLine = "SELECT e_username, e_level, e_token, e_department, e_sub_department, e_sub_department2, e_sub_department3, e_sub_department4, e_sub_department5
            FROM employees
            WHERE (e_level = 'manager')
            AND (
                e_department = :depart OR
                (e_department != :depart AND (
                    e_sub_department = :depart OR
                    e_sub_department2 = :depart OR
                    e_sub_department3 = :depart OR
                    e_sub_department4 = :depart OR
                    e_sub_department5 = :depart
                ))
            )";
        }

        // ตรวจสอบว่า $sqlLine ไม่ว่างเปล่า
        if (!empty($sqlLine)) {
            // เตรียม statement และ query ข้อมูล
            $stmtLine = $conn->prepare($sqlLine);
            $stmtLine->bindParam(':depart', $depart);
            $stmtLine->bindParam(':subDepart', $row['e_sub_department']);
            $stmtLine->bindParam(':subDepart2', $row['e_sub_department2']);
            $stmtLine->bindParam(':subDepart3', $row['e_sub_department3']);
            $stmtLine->bindParam(':subDepart4', $row['e_sub_department4']);
            $stmtLine->bindParam(':subDepart5', $row['e_sub_department5']);

            $stmtLine->execute();

            $lineNotify = $stmtLine->fetchAll(PDO::FETCH_ASSOC);

            foreach ($lineNotify as $lineNotify2) {
                $data = array('message' => $message);
                $token = $lineNotify2['e_token'];

                if (!empty($token)) {
                    // ส่งข้อความแจ้งเตือนผ่าน LINE Notify
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, 'https://notify-api.line.me/api/notify');
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $token));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                    $response = curl_exec($ch);
                    $error = curl_error($ch);
                    curl_close($ch);

                    if ($error) {
                        echo 'cURL Error: ' . $error;
                    } else {
                        echo 'Notification sent to: ' . $lineNotify2['e_username'] . ' for: ' . $nameEmp . ' on ' . $startDate . '<br>';

                        // Insert log สำหรับการแจ้งเตือน
                        $sqlInsert = "INSERT INTO notification_log
                        (n_leave_id, n_name, n_department, n_leave_start_date, n_leave_start_time, n_leave_end_date, n_leave_end_time, n_send_name, n_workplace)
                        VALUES
                        (:n_leave_id, :n_name, :n_department, :n_leave_start_date, :n_leave_start_time, :n_leave_end_date, :n_leave_end_time, :n_send_name, :n_workplace)";

                        $stmtInsert = $conn->prepare($sqlInsert);
                        $stmtInsert->bindParam(':n_leave_id', $leaveId);
                        $stmtInsert->bindParam(':n_name', $nameEmp);
                        $stmtInsert->bindParam(':n_department', $depart);
                        $stmtInsert->bindParam(':n_leave_start_date', $startDate);
                        $stmtInsert->bindParam(':n_leave_start_time', $startTime);
                        $stmtInsert->bindParam(':n_leave_end_date', $endDate);
                        $stmtInsert->bindParam(':n_leave_end_time', $endTime);
                        $stmtInsert->bindParam(':n_send_name', $lineNotify2['e_username']);
                        $stmtInsert->bindParam(':n_workplace', $workplace);

                        if ($stmtInsert->execute()) {
                            echo 'Notification log inserted successfully.<br>';
                        } else {
                            echo 'Failed to insert notification log.<br>';
                        }
                    }
                } else {
                    echo 'Token is empty for: ' . $lineNotify2['e_username'] . '<br>';
                }
            }
        } else {
            echo '<br>' . 'Query is empty. Please check the level and department conditions' . '';
        }

    }
}