<?php
include 'connect.php';
date_default_timezone_set('Asia/Bangkok');

// ดึงข้อมูลการแจ้งเตือนที่ส่งไปแล้ว
$sqlLog = "SELECT n_leave_id FROM notification_log WHERE n_send_date >= DATE_SUB(NOW(), INTERVAL 2 DAY)";
$stmtLog = $conn->prepare($sqlLog);
$stmtLog->execute();
$sentLeaveIds = $stmtLog->fetchAll(PDO::FETCH_COLUMN);

$sql = "SELECT 
    li.l_usercode, 
    li.l_username, 
    li.l_name, 
    li.l_leave_start_date, 
    li.l_leave_start_time, 
    li.l_leave_end_date, 
    li.l_leave_end_time,
    li.l_leave_id, 
    li.l_department, 
    li.l_workplace, 
    li.l_level,
    em.e_department, 
    em.e_sub_department, 
    em.e_sub_department2,
    em.e_sub_department3, 
    em.e_sub_department4, 
    em.e_sub_department5,
    leader.e_username AS leader_username
FROM leave_list li
INNER JOIN employees em ON li.l_usercode = em.e_usercode
LEFT JOIN employees leader ON (
    leader.e_level = 'GM'
)
WHERE 
    li.l_leave_id NOT IN (6, 7) 
    AND li.l_department <> 'RD'
    AND li.l_approve_status IN (2,3)
    AND li.l_approve_status2 IN (4,5)
    AND li.l_level IN ('user','leader','chief','admin','manager','assisManager')
    ";

$stmt = $conn->prepare($sql);
$stmt->execute();
$leaveRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    $subDepart = $row['e_sub_department'];
    $subDepart2 = $row['e_sub_department2'];
    $subDepart3 = $row['e_sub_department3'];
    $subDepart4 = $row['e_sub_department4'];
    $subDepart5 = $row['e_sub_department5'];

    $leaveDate = new DateTime($endDate);
    $leaveDate->setTime(0, 0);
    $interval = $today->diff($leaveDate);

    $daysDifference = $interval->days;

    // ตรวจสอบว่าต้องเป็นค่าติดลบหรือไม่
    if ($today > $leaveDate) {
        $daysDifference *= -1;
    }
    
    $mURL = 'https://lms.system-samt.com/';

    echo 'Leave Date: ' . $leaveDate->format('Y-m-d') . '<br>';
    echo 'Interval Days: ' . $interval->days . '<br>';
    echo 'Name: ' . $nameEmp . '<br>';
    echo 'userCode: ' . $userCode . '<br>';
    echo 'level: ' . $level . '<br>';
    echo 'depart: ' . $depart . '<br>';

    if ($workplace === 'Bang Phli' && $daysDifference == 1) {
        
        $message = "ใบลาของ $nameEmp ยังไม่อนุมัติ\nวันที่ลา : $startDate $startTime ถึง $endDate $endTime\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด : $mURL";
        
        // ตรวจสอบแผนกเพื่อเลือกผู้จัดการ
        if($depart == 'Office') {
            if( $subDepart == 'AC'){
                $sqlLine = "SELECT e_token, e_username FROM employees WHERE e_level = 'GM'";
            } else if( $subDepart == 'Sales'){
                $sqlLine = "SELECT e_token, e_username FROM employees WHERE e_level = 'GM'";
            } else if( $subDepart == 'Store'){
                $sqlLine = "SELECT e_token, e_username FROM employees WHERE e_level = 'GM'";
            } else if( $subDepart == 'All'){
                $sqlLine = "SELECT e_token, e_username FROM employees WHERE e_level = 'GM'";
            } else if( $subDepart == ''){
                $sqlLine = "SELECT e_token, e_username FROM employees WHERE e_level = 'GM'";
            } 
        }    
        else if ($depart == 'CAD1') {
            if($subDepart == 'Modeling'){
                $sqlLine = "SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'leader' AND e_sub_department = 'Modeling'";
            }
            else if($subDepart == 'Design'){
                $sqlLine = "SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'leader' AND e_sub_department = 'Design'";
            }
        } else if ($depart == 'CAD2') {
            $sqlLine = "SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'leader' AND e_sub_department2 = 'CAD2'";
        } else if ($depart == 'CAM') {
            $sqlLine = "SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'leader' AND e_sub_department3 = 'CAM2'";
        } 
        else if ($depart == 'Management') {
            if( $subDepart == 'AC'){
                $sqlLine = "SELECT e_token, e_username FROM employees WHERE e_level = 'GM'";
            } else if( $subDepart == 'Sales'){
                $sqlLine = "SELECT e_token, e_username FROM employees WHERE e_level = 'GM'";
            } else if( $subDepart == 'Store'){
                $sqlLine = "SELECT e_token, e_username FROM employees WHERE e_level = 'GM'";
            } else if( $subDepart == 'All'){
                $sqlLine = "SELECT e_token, e_username FROM employees WHERE e_level = 'GM'";
            } else if( $subDepart == ''){
                $sqlLine = "SELECT e_token, e_username FROM employees WHERE e_level = 'GM'";
            }         
        } 
        else {
            echo "ไม่พบเงื่อนไข";
        }       
         
        $stmtLine = $conn->prepare($sqlLine);
        $stmtLine->bindParam(':workplace', $workplace);
        $stmtLine->execute();
        $lineNotify = $stmtLine->fetchAll(PDO::FETCH_ASSOC);
        foreach ($lineNotify as $lineNotify2) {
            $data = array('message' => $message);
            $token = $lineNotify2['e_token'];

            if (!empty($token)) {
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
    }
}
?>