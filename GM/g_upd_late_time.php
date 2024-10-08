<?php
require_once '../connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userCode = $_POST['userCode'];
    $userName = $_POST['userName'];
    $proveName = $_POST['proveName'];
    $createDateTime = $_POST['createDateTime'];
    $depart = $_POST['depart'];
    $lateDate = $_POST['lateDate'];
    $lateStart = $_POST['lateStart'];
    $lateEnd = $_POST['lateEnd'];
    $name = $_POST['name'];
    $leaveStatus = $_POST['leaveStatus'];
    $action = $_POST['action'];
    $comfirmName = $_POST['comfirmName'];

    $proveDate = date('Y-m-d H:i:s');

    if ($action === 'approve') {
        $status = 4;
        $message = "$proveName อนุมัติมาสาย";
    } elseif ($action === 'deny') {
        $status = 5;
        $message = "$proveName ไม่อนุมัติมาสายของ";
    } elseif ($action === 'comfirm') {
        $status = 4;
        $message = "$comfirmName ยืนยันมาสาย";
    } else {
        echo 'เกิดข้อผิดพลาดในการร้องขอ';
        exit;
    }

    $sql = "UPDATE leave_list SET l_approve_status2 = :status,
            l_approve_datetime2 = :proveDate,
            l_approve_name2 = :userName
            WHERE l_usercode = :userCode AND l_create_datetime = :createDateTime";

    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':status', $status, PDO::PARAM_INT);
    $stmt->bindValue(':proveDate', $proveDate, PDO::PARAM_STR);
    $stmt->bindValue(':userCode', $userCode, PDO::PARAM_STR);
    $stmt->bindValue(':createDateTime', $createDateTime, PDO::PARAM_STR);
    $stmt->bindValue(':userName', $userName, PDO::PARAM_STR);

    if ($stmt->execute()) {
        $stmt = $conn->prepare("SELECT e_token FROM employees WHERE e_usercode = :userCode");
        $stmt->bindParam(':userCode', $userCode);
        $stmt->execute();
        $sToken = $stmt->fetchColumn();
        $sURL = 'https://lms.system-samt.com/';

        // ข้อความแจ้งเตือน
        $sMessage = "$message\nวันที่มาสาย : $lateDate\nเวลาที่มาสาย : $lateStart ถึง $lateEnd\nสถานะรายการ : $leaveStatus\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด: $sURL";

        // แจ้งเตือน พนง
        if ($sToken) {
            $chOne = curl_init();
            curl_setopt($chOne, CURLOPT_URL, "https://notify-api.line.me/api/notify");
            curl_setopt($chOne, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($chOne, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($chOne, CURLOPT_POST, 1);
            curl_setopt($chOne, CURLOPT_POSTFIELDS, "message=" . urlencode($sMessage));
            $headers = array(
                'Content-type: application/x-www-form-urlencoded',
                'Authorization: Bearer ' . $sToken,
            );
            curl_setopt($chOne, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($chOne, CURLOPT_RETURNTRANSFER, 1);
            $result = curl_exec($chOne);

            if (curl_error($chOne)) {
                echo 'Error:' . curl_error($chOne);
            } else {
                $result_ = json_decode($result, true);
                echo "status : " . $result_['status'] . "\n";
                echo "message : " . $result_['message'] . "\n";
            }
            curl_close($chOne);
        }

        if ($userName == 'Anchana') {
            // แจ้งเตือน Pornsuk
            $stmt = $conn->prepare("SELECT e_token FROM employees WHERE e_username = 'Pornsuk'");
            $stmt->execute();
            $pornsukToken = $stmt->fetchColumn();

            // $pornsukMess = "K.PS";
            $sMessage = "$message ของ $name\nวันที่มาสาย : $lateDate\nเวลาที่มาสาย : $lateStart ถึง $lateEnd\nสถานะรายการ : $leaveStatus\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด: $sURL";

            if ($pornsukToken) {
                $chOne = curl_init();
                curl_setopt($chOne, CURLOPT_URL, "https://notify-api.line.me/api/notify");
                curl_setopt($chOne, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($chOne, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($chOne, CURLOPT_POST, 1);
                curl_setopt($chOne, CURLOPT_POSTFIELDS, "message=" . urlencode($sMessage));
                $headers = array(
                    'Content-type: application/x-www-form-urlencoded',
                    'Authorization: Bearer ' . $pornsukToken,
                );
                curl_setopt($chOne, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($chOne, CURLOPT_RETURNTRANSFER, 1);
                $result = curl_exec($chOne);

                if (curl_error($chOne)) {
                    echo 'Error:' . curl_error($chOne);
                } else {
                    $result_ = json_decode($result, true);
                    echo "status : " . $result_['status'] . "\n";
                    echo "message : " . $result_['message'] . "\n";
                }
                curl_close($chOne);
            }
        } else if ($userName == 'Horita') {
            // แจ้งเตือน Pornsuk
            $stmt = $conn->prepare("SELECT e_token FROM employees WHERE e_username = 'Matsumoto'");
            $stmt->execute();
            $pornsukToken = $stmt->fetchColumn();

            // $pornsukMess = "Mat san";
            $sMessage = "$message ของ $name\nวันที่มาสาย : $lateDate\nเวลาที่มาสาย : $lateStart ถึง $lateEnd\nสถานะรายการ : $leaveStatus\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด: $sURL";

            if ($pornsukToken) {
                $chOne = curl_init();
                curl_setopt($chOne, CURLOPT_URL, "https://notify-api.line.me/api/notify");
                curl_setopt($chOne, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($chOne, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($chOne, CURLOPT_POST, 1);
                curl_setopt($chOne, CURLOPT_POSTFIELDS, "message=" . urlencode($sMessage));
                $headers = array(
                    'Content-type: application/x-www-form-urlencoded',
                    'Authorization: Bearer ' . $pornsukToken,
                );
                curl_setopt($chOne, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($chOne, CURLOPT_RETURNTRANSFER, 1);
                $result = curl_exec($chOne);

                if (curl_error($chOne)) {
                    echo 'Error:' . curl_error($chOne);
                } else {
                    $result_ = json_decode($result, true);
                    echo "status : " . $result_['status'] . "\n";
                    echo "message : " . $result_['message'] . "\n";
                }
                curl_close($chOne);
            }
        } elseif ($userName == 'Pornsuk' || $userName == 'Matsumoto') {
            // แจ้งเตือน Anchana
            $stmt = $conn->prepare("SELECT e_token FROM employees WHERE e_level = 'admin'");
            $stmt->execute();
            $adminTokens = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // $adminMess = "admin";
            $sMessage = "$message ของ $name\nวันที่มาสาย : $lateDate\nเวลาที่มาสาย : $lateStart ถึง $lateEnd\nสถานะรายการ : $leaveStatus\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด: $sURL";

            foreach ($adminTokens as $adminToken) {
                $chOne = curl_init();
                curl_setopt($chOne, CURLOPT_URL, "https://notify-api.line.me/api/notify");
                curl_setopt($chOne, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($chOne, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($chOne, CURLOPT_POST, 1);
                curl_setopt($chOne, CURLOPT_POSTFIELDS, "message=" . urlencode($sMessage));
                $headers = array(
                    'Content-type: application/x-www-form-urlencoded',
                    'Authorization: Bearer ' . $adminToken,
                );
                curl_setopt($chOne, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($chOne, CURLOPT_RETURNTRANSFER, 1);
                $result = curl_exec($chOne);

                if (curl_error($chOne)) {
                    echo 'Error:' . curl_error($chOne);
                } else {
                    $result_ = json_decode($result, true);
                    echo "status : " . $result_['status'] . "\n";
                    echo "message : " . $result_['message'] . "\n";
                }
                curl_close($chOne);
            }

        }
    } else {
        echo 'เกิดข้อผิดพลาดในการอัปเดตข้อมูล';
    }
} else {
    echo 'เกิดข้อผิดพลาดในการร้องขอ';
}