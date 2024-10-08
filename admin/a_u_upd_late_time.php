<?php
require_once '../connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userName = $_POST['userName'];
    $proveName = $_POST['proveName'];
    $createDateTime = $_POST['createDateTime'];
    $depart = $_POST['depart'];
    $lateDate = $_POST['lateDate'];
    $lateStart = $_POST['lateStart'];
    $lateEnd = $_POST['lateEnd'];
    $userCode = $_POST['userCode'];
    $name = $_POST['name'];
    $leaveStatus = $_POST['leaveStatus'];
    $action = $_POST['action'];
    $comfirmName = $_POST['comfirmName'];
    $workplace = $_POST['workplace'];

    $proveDate = date('Y-m-d H:i:s');
    $action = 'comfirm';

    $sql = "UPDATE leave_list SET l_late_datetime = :proveDate
    WHERE l_usercode = :userCode
    AND l_create_datetime = :createDateTime";

    $stmt = $conn->prepare($sql);
    // $stmt->bindValue(':status', $status, PDO::PARAM_INT);
    $stmt->bindValue(':proveDate', $proveDate, PDO::PARAM_STR);
    $stmt->bindValue(':userCode', $userCode, PDO::PARAM_STR);
    $stmt->bindValue(':createDateTime', $createDateTime, PDO::PARAM_STR);
    // $stmt->bindValue(':userName', $userName, PDO::PARAM_STR);

    if ($stmt->execute()) {
        $sURL = 'https://lms.system-samt.com/';
        $sMessage = "$comfirmName ยืนยันมาสาย\nวันที่มาสาย : $lateDate\nเวลาที่มาสาย : $lateStart ถึง $lateEnd\nสถานะรายการ : $leaveStatus\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด: $sURL";

        // แจ้งเตือนไลน์หัวหน้ากับ ผจก ในแผนก
        // $stmt = $conn->prepare("SELECT e_token FROM employees WHERE e_department = :depart AND e_level IN ('chief', 'manager')");
        // $stmt->bindParam(':depart', $depart);

        if ($depart == 'RD') {
            // แจ้งไลน์โฮซัง
            $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'manager' AND e_sub_department =  'RD'");
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
            echo "No tokens found for chief or manager";
        }
    } else {
        echo 'เกิดข้อผิดพลาดในการอัปเดตข้อมูล';
    }
} else {
    echo 'เกิดข้อผิดพลาดในการร้องขอ';
}