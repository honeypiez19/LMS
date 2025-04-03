<?php
// a_ajax_add_late_time.php
header('Content-Type: application/json');
session_start();

// Database connection
try {
    $db = new PDO('mysql:host=localhost;dbname=your_database_name;charset=utf8', 'username', 'password');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec("SET NAMES utf8");
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'การเชื่อมต่อฐานข้อมูลล้มเหลว: ' . $e->getMessage()]);
    exit;
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'คำขอไม่ถูกต้อง']);
    exit;
}

// Get form data
$userCode   = $_POST['userCode'] ?? '';
$userName   = $_POST['userName'] ?? '';
$name       = $_POST['name'] ?? '';
$department = $_POST['department'] ?? '';
$level      = $_POST['level'] ?? '';
$workplace  = $_POST['workplace'] ?? '';
$telPhone   = $_POST['telPhone'] ?? '';
$reason     = $_POST['reason'] ?? '';
$startDate  = $_POST['startDate'] ?? '';
$endDate    = $_POST['endDate'] ?? '';
$startTime  = $_POST['startTime'] ?? '';
$endTime    = $_POST['endTime'] ?? '';
$subDepart  = $_POST['subDepart'] ?? '';
$subDepart2 = $_POST['subDepart2'] ?? '';
$subDepart3 = $_POST['subDepart3'] ?? '';
$subDepart4 = $_POST['subDepart4'] ?? '';
$subDepart5 = $_POST['subDepart5'] ?? '';

// Validate required fields
if (empty($userCode) || empty($userName) || empty($startDate) || empty($startTime)) {
    echo json_encode(['status' => 'error', 'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน']);
    exit;
}

try {
    // Begin transaction
    $db->beginTransaction();

    // 1. Find supervisors (head 1) who are at levels leader, chief, subLeader in the employee's department
    $head1 = findSupervisorByLevels($db, $department, $subDepart, $subDepart2, $subDepart3, $subDepart4, $subDepart5, ['leader', 'chief', 'subLeader'], $userCode);

    // 2. Find supervisors (head 2) who are at levels assisManager, manager, manager2 in the employee's department
    $head2 = findSupervisorByLevels($db, $department, $subDepart, $subDepart2, $subDepart3, $subDepart4, $subDepart5, ['assisManager', 'manager', 'manager2'], $userCode);

    // 3. Find GM
    $gm = findEmployeeByLevel($db, 'GM');

    // 4. Find admin
    $admin = findEmployeeByLevel($db, 'admin');

                                              // Set approval statuses based on existence of supervisors
    $approveStatus  = ! empty($head1) ? 0 : 6; // 0 if head1 exists, 6 if not
    $approveStatus2 = ! empty($head2) ? 1 : 6; // 1 if head2 exists, 6 if not
    $approveStatus3 = ! empty($gm) ? 7 : 6;    // 7 if GM exists, 6 if not
    $hrStatus       = 1;                      // Always 1 for admin

    // Insert late record into leave_list table
    $sql = "INSERT INTO leave_list (
                l_usercode, l_username, l_name, l_department, l_level,
                l_workplace, l_phone, l_leave_id, l_leave_reason,
                l_leave_start_date, l_leave_start_time, l_leave_end_date, l_leave_end_time,
                l_approve_status, l_approve_status2, l_approve_status3, l_hr_status,
                l_create_date
            ) VALUES (
                :usercode, :username, :name, :department, :level,
                :workplace, :phone, 7, 'มาสาย',
                :start_date, :start_time, :end_date, :end_time,
                :approve_status, :approve_status2, :approve_status3, :hr_status,
                NOW()
            )";

    $stmt = $db->prepare($sql);
    $stmt->bindParam(':usercode', $userCode);
    $stmt->bindParam(':username', $userName);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':department', $department);
    $stmt->bindParam(':level', $level);
    $stmt->bindParam(':workplace', $workplace);
    $stmt->bindParam(':phone', $telPhone);
    $stmt->bindParam(':start_date', $startDate);
    $stmt->bindParam(':start_time', $startTime);
    $stmt->bindParam(':end_date', $endDate);
    $stmt->bindParam(':end_time', $endTime);
    $stmt->bindParam(':approve_status', $approveStatus, PDO::PARAM_INT);
    $stmt->bindParam(':approve_status2', $approveStatus2, PDO::PARAM_INT);
    $stmt->bindParam(':approve_status3', $approveStatus3, PDO::PARAM_INT);
    $stmt->bindParam(':hr_status', $hrStatus, PDO::PARAM_INT);
    $stmt->execute();

    $leaveId = $db->lastInsertId();

    // Send notifications to all relevant supervisors
    if (! empty($head1)) {
        sendNotification($head1, $name, $startDate, $startTime, $reason, $leaveId, 'หัวหน้า1');
    }

    if (! empty($head2)) {
        sendNotification($head2, $name, $startDate, $startTime, $reason, $leaveId, 'หัวหน้า2');
    }

    if (! empty($gm)) {
        sendNotification($gm, $name, $startDate, $startTime, $reason, $leaveId, 'GM');
    }

    if (! empty($admin)) {
        sendNotification($admin, $name, $startDate, $startTime, $reason, $leaveId, 'admin');
    }

    // Commit transaction
    $db->commit();

    echo json_encode(['status' => 'success', 'message' => 'บันทึกข้อมูลการมาสายสำเร็จ']);

} catch (PDOException $e) {
    // Rollback transaction on error
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
    exit;
}

/**
 * Find supervisors by specific levels in the employee's department hierarchy
 *
 * @param PDO $db Database connection
 * @param string $department Department
 * @param string $subDepart Sub department
 * @param string $subDepart2 Sub department 2
 * @param string $subDepart3 Sub department 3
 * @param string $subDepart4 Sub department 4
 * @param string $subDepart5 Sub department 5
 * @param array $levels Array of level values to search for
 * @param string $excludeUserCode User code to exclude from results
 * @return array|false Supervisor data or false if not found
 */
function findSupervisorByLevels($db, $department, $subDepart, $subDepart2, $subDepart3, $subDepart4, $subDepart5, $levels, $excludeUserCode = '')
{
    // Create named parameters for the IN clause
    $inParams = [];
    $inNames  = [];

    foreach ($levels as $i => $level) {
        $paramName            = ":level$i";
        $inNames[]            = $paramName;
        $inParams[$paramName] = $level;
    }

    $inClause = implode(',', $inNames);

    $sql = "SELECT * FROM employees
            WHERE (
                (e_department = :department) OR
                (e_sub_department = :subDepart AND :subDepart != '') OR
                (e_sub_department2 = :subDepart2 AND :subDepart2 != '') OR
                (e_sub_department3 = :subDepart3 AND :subDepart3 != '') OR
                (e_sub_department4 = :subDepart4 AND :subDepart4 != '') OR
                (e_sub_department5 = :subDepart5 AND :subDepart5 != '')
            )
            AND e_level IN ($inClause)
            AND e_status = 1
            AND e_usercode != :excludeUserCode
            LIMIT 1";

    $stmt = $db->prepare($sql);

    // Bind department parameters
    $stmt->bindParam(':department', $department);
    $stmt->bindParam(':subDepart', $subDepart);
    $stmt->bindParam(':subDepart2', $subDepart2);
    $stmt->bindParam(':subDepart3', $subDepart3);
    $stmt->bindParam(':subDepart4', $subDepart4);
    $stmt->bindParam(':subDepart5', $subDepart5);
    $stmt->bindParam(':excludeUserCode', $excludeUserCode);

    // Bind level parameters
    foreach ($inParams as $param => $value) {
        $stmt->bindValue($param, $value);
    }

    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Find employee by specific level
 *
 * @param PDO $db Database connection
 * @param string $level Employee level to search for
 * @return array|false Employee data or false if not found
 */
function findEmployeeByLevel($db, $level)
{
    $sql  = "SELECT * FROM employees WHERE e_level = :level AND e_status = 1 LIMIT 1";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':level', $level);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Send notification using message API
 *
 * @param array $recipient Recipient data
 * @param string $employeeName Employee name
 * @param string $date Date of late arrival
 * @param string $time Time of late arrival
 * @param string $reason Reason for late arrival
 * @param int $leaveId Leave record ID
 * @param string $recipientType Type of recipient (e.g., 'หัวหน้า1', 'หัวหน้า2', 'GM', 'admin')
 * @return bool Success status
 */
function sendNotification($recipient, $employeeName, $date, $time, $reason, $leaveId, $recipientType)
{
    // Message API implementation goes here

    $message = "แจ้งเตือน: พนักงาน $employeeName มาสายในวันที่ $date เวลา $time เนื่องจาก $reason";

    // Log notification
    error_log("Sending notification to $recipientType ({$recipient['e_usercode']}): $message");

    // Example implementation with message API (replace with actual API)
    /*
    $apiUrl = "https://your-message-api-endpoint.com/send";
    $recipientId = $recipient['e_usercode']; // Assuming e_usercode is the appropriate field
    
    $data = [
        'recipient' => $recipientId,
        'message' => $message,
        'reference' => $leaveId
    ];
    
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
    */

    return true; // Placeholder return
}