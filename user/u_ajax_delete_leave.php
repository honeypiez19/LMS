<?php
// เชื่อมต่อฐานข้อมูล
include_once '../connect.php';
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
$leaveStatus    = $_POST['leaveStatus'];

$canDatetime = date('Y-m-d H:i:s');

$workplace  = $_POST['workplace'];
$subDepart  = $_POST['subDepart'];
$subDepart2 = $_POST['subDepart2'];
$subDepart3 = $_POST['subDepart3'];
$subDepart4 = $_POST['subDepart4'];
$subDepart5 = $_POST['subDepart5'];

// if ($subDepart == '') {
//     $proveStatus  = 6;
//     $proveStatus2 = 1;
// } else {
//     $proveStatus  = 0;
//     $proveStatus2 = 1;
// }

$chkApprover = "SELECT * FROM employees WHERE e_username = :approver";
$stmt        = $conn->prepare($chkApprover);
$stmt->bindParam(':approver', $approver, PDO::PARAM_STR);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

$proveStatus  = null;
$proveStatus2 = null;
$proveStatus3 = null;

if ($result) {
    $subDepartment = $result['e_sub_department'];
    $levelApprover = $result['e_level'];
    $workplace     = $result['e_workplace'];

    $departments = ['RD', 'CAD1', 'CAD2', 'CAM', 'Modeling', 'Design', 'Office', 'AC', 'Sales', 'Store', 'MC', 'FN', 'PC', 'QC'];
    $leaders     = ['leader', 'subLeader', 'chief'];
    $managers    = ['manager', 'manager2', 'assisManager'];
    $workplaceAt = ['Bang Phli', 'Korat'];

    if (in_array($levelApprover, $leaders) && in_array($subDepartment, $departments) && in_array($workplace, $workplaceAt)) {
        $proveStatus  = 0;
        $proveStatus2 = 1;
        $proveStatus3 = 6;
    } elseif (in_array($levelApprover, $managers) && in_array($subDepartment, $departments) && in_array($workplace, $workplaceAt)) {
        $proveStatus  = 6;
        $proveStatus2 = 1;
        $proveStatus3 = 6;
    } elseif ($levelApprover == 'GM' && in_array($workplace, $workplaceAt)) {
        $proveStatus  = 6;
        $proveStatus2 = 6;
        $proveStatus3 = 7;
    } elseif ($levelApprover == 'admin' && in_array($workplace, $workplaceAt)) {
        $proveStatus  = 6;
        $proveStatus2 = 6;
        $proveStatus3 = 6;
    } else {
        echo "ไม่พบแผนกหรือสถานที่";
    }
}

// คืนจำนวนวันลา
$sqlReturn = "UPDATE leave_list SET
                l_leave_status = 1,
                l_cancel_datetime = :canDatetime,
                l_approve_status = :proveStatus,
                l_approve_name = '',
                l_approve_datetime = NULL,
                l_reason = '',
                l_approve_status2 = :proveStatus2,
                l_approve_name2 = '',
                l_approve_datetime2 = NULL,
                l_reason2 = '',
                l_hr_status = 0,
                l_hr_name = '',
                l_hr_datetime = NULL
              WHERE l_leave_id = :leaveID AND l_create_datetime = :createDatetime";
$stmtReturn = $conn->prepare($sqlReturn);
$stmtReturn->bindParam(':leaveID', $leaveID);
$stmtReturn->bindParam(':createDatetime', $createDatetime);
$stmtReturn->bindParam(':canDatetime', $canDatetime);
$stmtReturn->bindParam(':proveStatus', $proveStatus);
$stmtReturn->bindParam(':proveStatus2', $proveStatus2);

if ($stmtReturn->execute()) {
    $sURL = 'https://lms.system-samt.com/';
    // $sMessage = "$name ยกเลิกใบลา\nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nวันเวลาที่ลา : $startDate ถึง $endDate\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $sURL";
    $sMessage = "$name ยกเลิกใบลา\nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nวันเวลาที่ลา : $startDate ถึง $endDate\nสถานะใบลา : $leaveStatus\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด $sURL";

    // แจ้งเตือนไลน์หัวหน้ากับ ผจก ในแผนก
    // $stmt = $conn->prepare("SELECT e_token FROM employees WHERE e_department = :depart AND e_level IN ('chief', 'manager')");

    // $stmt->bindParam(':depart', $depart);

    if ($depart == 'RD') {
        // แจ้งไลน์โฮซัง
        $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'leader' AND e_sub_department =  'RD'");
        // $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE e_department = 'Management' AND e_workplace = :workplace AND e_level = 'manager' AND e_sub_department = :depart");
        // $stmt = $conn->prepare("SELECT e_username, e_token FROM employees WHERE e_level = 'manager' AND e_workplace = 'Bang Phli' AND e_sub_department = 'RD'");
        $stmt->bindParam(':workplace', $workplace);
        // $stmt->bindParam(':depart', $depart);

    } else if ($depart == 'Office') {
        // บัญชี
        if ($subDepart == 'AC') {
            // แจ้งเตือนพี่แวว
            // $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'chief' AND e_sub_department = :subDepart");
            $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'chief' AND e_sub_department = 'AC'");
            $stmt->bindParam(':workplace', $workplace);
            // $stmt->bindParam(':subDepart', $subDepart);
        }
        // เซลล์
        else if ($subDepart == 'Sales') {
            // แจ้งเตือนพี่เจี๊ยบหรือพี่อ้อม
            // $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE e_workplace = :workplace AND e_level = 'chief' AND e_sub_department = :subDepart");
            $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE e_workplace = :workplace AND e_level = 'chief' AND e_sub_department = 'Sales'");
            $stmt->bindParam(':workplace', $workplace);
            // $stmt->bindParam(':subDepart', $subDepart);
        }
        // สโตร์
        else if ($subDepart == 'Store') {
            // แจ้งเตือนพี่เก๋
            $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'leader' AND e_sub_department = 'Store'");
            $stmt->bindParam(':workplace', $workplace);
            // $stmt->bindParam(':subDepart', $subDepart);
        }
        // HR
        else if ($subDepart == 'All') {
            $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'manager' AND e_sub_department = 'Office'");
            $stmt->bindParam(':workplace', $workplace);
            // $stmt->bindParam(':subDepart', $subDepart);
        }
        // พี่เต๋ / พี่น้อย / พี่ไว
        else if ($subDepart == '') {
            $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'manager' AND e_sub_department = 'Office'");
            $stmt->bindParam(':workplace', $workplace);
            // $stmt->bindParam(':subDepart', $subDepart);
        }
    } else {
        echo "ไม่พบเงื่อนไข";
    }

    $stmt->execute();
    $tokens = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if ($tokens) {
        foreach ($tokens as $sToken) {
            $chOne = curl_init();
            curl_setopt($chOne, CURLOPT_URL, "https://notify-api.line.me/api/notify");
            curl_setopt($chOne, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($chOne, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($chOne, CURLOPT_POST, 1);
            curl_setopt($chOne, CURLOPT_POSTFIELDS, "message=" . $sMessage);
            $headers = [
                'Content-type: application/x-www-form-urlencoded',
                'Authorization: Bearer ' . $sToken,
            ];
            curl_setopt($chOne, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($chOne, CURLOPT_RETURNTRANSFER, 1);
            $result = curl_exec($chOne);

            if (curl_error($chOne)) {
                echo 'Error:' . curl_error($chOne);
            } else {
                $result_ = json_decode($result, true);
                echo "status : " . $result_['status'];
                echo "message : " . $result_['message'];
            }

            curl_close($chOne);
        }
    } else {
        echo "ไม่พบ Token ของหัวหน้าหรือผู้จัดการ";
    }

} else {
    echo "Error";
}