<?php
date_default_timezone_set('Asia/Bangkok');

require_once '../connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // เก็บข้อมูลจาก POST
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
    $level = $_POST['level'];
    $workplace = $_POST['workplace'];
    $leaveType = $_POST['leaveType'];

    $proveDate = date('Y-m-d H:i:s');

    if ($action === 'approve') {
        if($leaveType == 'มาสาย'){
            $status = 2;
            $message = "$proveName อนุมัติการมาสายของ";
        }
        else {
            $status = 2;
            $message = "$proveName อนุมัติหยุดงานของ";
        }
    } elseif ($action === 'deny') {
        if($leaveType == 'มาสาย'){
            $status = 3;
            $message = "$proveName ไม่อนุมัติการมาสายของ";
        }
        else{
            $status = 3;
            $message = "$proveName ไม่อนุมัติหยุดงานของ";
        }
    } elseif ($action === 'confirm') {
        if($leaveType == 'มาสาย'){
            $status = 2;
            $message = "$comfirmName ยืนยันมาสาย";
        }
        else {
            $status = 2;
            $message = "$comfirmName ยืนยันหยุดงาน";
        }
    } else {
        echo 'เกิดข้อผิดพลาดในการร้องขอ';
        exit;
    }

    // Update สถานะการอนุมัติในฐานข้อมูล
    $sql = "UPDATE leave_list SET l_approve_status = :status,
    l_approve_datetime = :proveDate,
    l_approve_name = :userName
    WHERE l_usercode = :userCode AND l_create_datetime = :createDateTime";

    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':status', $status, PDO::PARAM_INT);
    $stmt->bindValue(':proveDate', $proveDate, PDO::PARAM_STR);
    $stmt->bindValue(':userCode', $userCode, PDO::PARAM_STR);
    $stmt->bindValue(':createDateTime', $createDateTime, PDO::PARAM_STR);
    $stmt->bindValue(':userName', $userName, PDO::PARAM_STR);

    if ($stmt->execute()) {
        $sURL = 'https://lms.system-samt.com/';

        if ($action === 'approve') {
                if($leaveType == 'มาสาย'){
                    $sMessage = "$message $name\nวันที่มาสาย : $lateDate\nเวลาที่มาสาย : $lateStart ถึง $lateEnd\nสถานะรายการ : $leaveStatus\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด: $sURL";

                    if ($depart == 'RD') {
                        // แจ้งไลน์โฮซัง
                        $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'manager' AND e_sub_department =  'RD'");
                        // $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE e_department = 'Management' AND e_workplace = :workplace AND e_level = 'manager' AND e_sub_department = :depart");
                        // $stmt = $conn->prepare("SELECT e_username, e_token FROM employees WHERE e_level = 'manager' AND e_workplace = 'Bang Phli' AND e_sub_department = 'RD'");
                        $stmt->bindParam(':workplace', $workplace);
                        // $stmt->bindParam(':depart', $depart);

                    } else if ($level == 'leader') {
                        if ($depart == 'Office') {
                            // แจ้งเตือนไปที่พี่ตุ๊ก
                            $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'manager' AND e_sub_department = 'Office'");
                            $stmt->bindParam(':workplace', $workplace);
                        }
                    } else if ($level == 'chief') {
                        if ($depart == 'Management') {
                            // แจ้งเตือนไปที่พี่ตุ๊ก
                            $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'manager' AND e_sub_department = 'Office'");
                            $stmt->bindParam(':workplace', $workplace);
                        }
                    } else {
                        echo "ไม่พบเงื่อนไข";
                        // $stmt = $conn->prepare("SELECT e_token FROM employees WHERE e_department = :depart AND e_workplace = :workplace AND e_level IN ('chief', 'manager')");
                        // $stmt->bindParam(':depart', $depart);
                        // $stmt->bindParam(':workplace', $workplace);
                    }

                    $stmt->execute();
                    $managers = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if ($managers) {
                        foreach ($managers as $manager) {
                            $sToken = $manager['e_token'];

                            $chOne = curl_init();
                            curl_setopt($chOne, CURLOPT_URL, "https://notify-api.line.me/api/notify");
                            curl_setopt($chOne, CURLOPT_SSL_VERIFYHOST, 0);
                            curl_setopt($chOne, CURLOPT_SSL_VERIFYPEER, 0);
                            curl_setopt($chOne, CURLOPT_POST, 1);
                            curl_setopt($chOne, CURLOPT_POSTFIELDS, "message=" . $sMessage);
                            $headers = array('Content-type: application/x-www-form-urlencoded', 'Authorization: Bearer ' . $sToken . '');
                            curl_setopt($chOne, CURLOPT_HTTPHEADER, $headers);
                            curl_setopt($chOne, CURLOPT_RETURNTRANSFER, 1);
                            $result = curl_exec($chOne);

                            if (curl_error($chOne)) {
                                echo 'Error:' . curl_error($chOne);
                            } else {
                                $result_ = json_decode($result, true);
                                echo "status : " . $result_['status'] . "<br>";
                                echo "message : " . $result_['message'] . "<br>";
                            }
                            curl_close($chOne); // Correct function call
                        }
                    } else {
                        echo "No tokens found for manager";
                    }
                }
                else {
                    $sMessage = "$message $name\nวันที่ : $lateDate\nเวลา : $lateStart ถึง $lateEnd\nสถานะรายการ : $leaveStatus\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด: $sURL";

                    if ($depart == 'RD') {
                        // แจ้งไลน์โฮซัง
                        $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'manager' AND e_sub_department =  'RD'");
                        // $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE e_department = 'Management' AND e_workplace = :workplace AND e_level = 'manager' AND e_sub_department = :depart");
                        // $stmt = $conn->prepare("SELECT e_username, e_token FROM employees WHERE e_level = 'manager' AND e_workplace = 'Bang Phli' AND e_sub_department = 'RD'");
                        $stmt->bindParam(':workplace', $workplace);
                        // $stmt->bindParam(':depart', $depart);

                    } else if ($level == 'leader') {
                        if ($depart == 'Office') {
                            // แจ้งเตือนไปที่พี่ตุ๊ก
                            $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'manager' AND e_sub_department = 'Office'");
                            $stmt->bindParam(':workplace', $workplace);
                        }
                    } else if ($level == 'chief') {
                        if ($depart == 'Management') {
                            // แจ้งเตือนไปที่พี่ตุ๊ก
                            $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'manager' AND e_sub_department = 'Office'");
                            $stmt->bindParam(':workplace', $workplace);
                        }
                    } else {
                        echo "ไม่พบเงื่อนไข";
                        // $stmt = $conn->prepare("SELECT e_token FROM employees WHERE e_department = :depart AND e_workplace = :workplace AND e_level IN ('chief', 'manager')");
                        // $stmt->bindParam(':depart', $depart);
                        // $stmt->bindParam(':workplace', $workplace);
                    }

                    $stmt->execute();
                    $managers = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if ($managers) {
                        foreach ($managers as $manager) {
                            $sToken = $manager['e_token'];

                            $chOne = curl_init();
                            curl_setopt($chOne, CURLOPT_URL, "https://notify-api.line.me/api/notify");
                            curl_setopt($chOne, CURLOPT_SSL_VERIFYHOST, 0);
                            curl_setopt($chOne, CURLOPT_SSL_VERIFYPEER, 0);
                            curl_setopt($chOne, CURLOPT_POST, 1);
                            curl_setopt($chOne, CURLOPT_POSTFIELDS, "message=" . $sMessage);
                            $headers = array('Content-type: application/x-www-form-urlencoded', 'Authorization: Bearer ' . $sToken . '');
                            curl_setopt($chOne, CURLOPT_HTTPHEADER, $headers);
                            curl_setopt($chOne, CURLOPT_RETURNTRANSFER, 1);
                            $result = curl_exec($chOne);

                            if (curl_error($chOne)) {
                                echo 'Error:' . curl_error($chOne);
                            } else {
                                $result_ = json_decode($result, true);
                                echo "status : " . $result_['status'] . "<br>";
                                echo "message : " . $result_['message'] . "<br>";
                            }
                            curl_close($chOne); // Correct function call
                        }
                    } else {
                        echo "No tokens found for manager";
                    }
                }
        } else if ($action === 'deny') {
            $sMessage = "$message $name\nวันที่มาสาย : $lateDate\nเวลาที่มาสาย : $lateStart ถึง $lateEnd\nสถานะรายการ : $leaveStatus\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด: $sURL";

            if($leaveType == 'มาสาย'){
                if ($depart == 'RD') {
                    // แจ้งไลน์โฮซัง
                    $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'manager' AND e_sub_department =  'RD'");
                    // $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE e_department = 'Management' AND e_workplace = :workplace AND e_level = 'manager' AND e_sub_department = :depart");
                    // $stmt = $conn->prepare("SELECT e_username, e_token FROM employees WHERE e_level = 'manager' AND e_workplace = 'Bang Phli' AND e_sub_department = 'RD'");
                    $stmt->bindParam(':workplace', $workplace);
                    // $stmt->bindParam(':depart', $depart);
    
                } else if ($level == 'leader') {
                    if ($depart == 'Office') {
                        // แจ้งเตือนไปที่พี่ตุ๊ก
                        $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'manager' AND e_sub_department = 'Office'");
                        $stmt->bindParam(':workplace', $workplace);
                    }
                } else if ($level == 'chief') {
                    if ($depart == 'Management') {
                        // แจ้งเตือนไปที่พี่ตุ๊ก
                        $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'manager' AND e_sub_department = 'Office'");
                        $stmt->bindParam(':workplace', $workplace);
                    }
                } else {
                    echo "ไม่พบเงื่อนไข";
                    // $stmt = $conn->prepare("SELECT e_token FROM employees WHERE e_department = :depart AND e_workplace = :workplace AND e_level IN ('chief', 'manager')");
                    // $stmt->bindParam(':depart', $depart);
                    // $stmt->bindParam(':workplace', $workplace);
                }
    
                $stmt->execute();
                $managers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
                if ($managers) {
                    foreach ($managers as $manager) {
                        $sToken = $manager['e_token'];
    
                        $chOne = curl_init();
                        curl_setopt($chOne, CURLOPT_URL, "https://notify-api.line.me/api/notify");
                        curl_setopt($chOne, CURLOPT_SSL_VERIFYHOST, 0);
                        curl_setopt($chOne, CURLOPT_SSL_VERIFYPEER, 0);
                        curl_setopt($chOne, CURLOPT_POST, 1);
                        curl_setopt($chOne, CURLOPT_POSTFIELDS, "message=" . $sMessage);
                        $headers = array('Content-type: application/x-www-form-urlencoded', 'Authorization: Bearer ' . $sToken . '');
                        curl_setopt($chOne, CURLOPT_HTTPHEADER, $headers);
                        curl_setopt($chOne, CURLOPT_RETURNTRANSFER, 1);
                        $result = curl_exec($chOne);
    
                        if (curl_error($chOne)) {
                            echo 'Error:' . curl_error($chOne);
                        } else {
                            $result_ = json_decode($result, true);
                            echo "status : " . $result_['status'] . "<br>";
                            echo "message : " . $result_['message'] . "<br>";
                        }
                        curl_close($chOne); // Correct function call
                    }
                } else {
                    echo "No tokens found for manager";
                }
            }
            else {
                if ($depart == 'RD') {
                    // แจ้งไลน์โฮซัง
                    $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'manager' AND e_sub_department =  'RD'");
                    // $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE e_department = 'Management' AND e_workplace = :workplace AND e_level = 'manager' AND e_sub_department = :depart");
                    // $stmt = $conn->prepare("SELECT e_username, e_token FROM employees WHERE e_level = 'manager' AND e_workplace = 'Bang Phli' AND e_sub_department = 'RD'");
                    $stmt->bindParam(':workplace', $workplace);
                    // $stmt->bindParam(':depart', $depart);
    
                } else if ($level == 'leader') {
                    if ($depart == 'Office') {
                        // แจ้งเตือนไปที่พี่ตุ๊ก
                        $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'manager' AND e_sub_department = 'Office'");
                        $stmt->bindParam(':workplace', $workplace);
                    }
                } else if ($level == 'chief') {
                    if ($depart == 'Management') {
                        // แจ้งเตือนไปที่พี่ตุ๊ก
                        $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'manager' AND e_sub_department = 'Office'");
                        $stmt->bindParam(':workplace', $workplace);
                    }
                } else {
                    echo "ไม่พบเงื่อนไข";
                    // $stmt = $conn->prepare("SELECT e_token FROM employees WHERE e_department = :depart AND e_workplace = :workplace AND e_level IN ('chief', 'manager')");
                    // $stmt->bindParam(':depart', $depart);
                    // $stmt->bindParam(':workplace', $workplace);
                }
    
                $stmt->execute();
                $managers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
                if ($managers) {
                    foreach ($managers as $manager) {
                        $sToken = $manager['e_token'];
    
                        $chOne = curl_init();
                        curl_setopt($chOne, CURLOPT_URL, "https://notify-api.line.me/api/notify");
                        curl_setopt($chOne, CURLOPT_SSL_VERIFYHOST, 0);
                        curl_setopt($chOne, CURLOPT_SSL_VERIFYPEER, 0);
                        curl_setopt($chOne, CURLOPT_POST, 1);
                        curl_setopt($chOne, CURLOPT_POSTFIELDS, "message=" . $sMessage);
                        $headers = array('Content-type: application/x-www-form-urlencoded', 'Authorization: Bearer ' . $sToken . '');
                        curl_setopt($chOne, CURLOPT_HTTPHEADER, $headers);
                        curl_setopt($chOne, CURLOPT_RETURNTRANSFER, 1);
                        $result = curl_exec($chOne);
    
                        if (curl_error($chOne)) {
                            echo 'Error:' . curl_error($chOne);
                        } else {
                            $result_ = json_decode($result, true);
                            echo "status : " . $result_['status'] . "<br>";
                            echo "message : " . $result_['message'] . "<br>";
                        }
                        curl_close($chOne); // Correct function call
                    }
                } else {
                    echo "No tokens found for manager";
                }
            }
        } else if ($action === 'confirm') {
            $sMessage = "$message \nวันที่มาสาย : $lateDate\nเวลาที่มาสาย : $lateStart ถึง $lateEnd\nสถานะรายการ : $leaveStatus\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด: $sURL";

            if($leaveType == 'มาสาย'){
                if ($depart == 'RD') {
                    // แจ้งไลน์โฮซัง
                    $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'manager' AND e_sub_department =  'RD'");
                    // $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE e_department = 'Management' AND e_workplace = :workplace AND e_level = 'manager' AND e_sub_department = :depart");
                    // $stmt = $conn->prepare("SELECT e_username, e_token FROM employees WHERE e_level = 'manager' AND e_workplace = 'Bang Phli' AND e_sub_department = 'RD'");
                    $stmt->bindParam(':workplace', $workplace);
                    // $stmt->bindParam(':depart', $depart);
    
                } else if ($level == 'leader') {
                    if ($depart == 'Office') {
                        // แจ้งเตือนไปที่พี่ตุ๊ก
                        $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'manager' AND e_sub_department = 'Office'");
                        $stmt->bindParam(':workplace', $workplace);
                    }
                } else if ($level == 'chief') {
                    if ($depart == 'Management') {
                        // แจ้งเตือนไปที่พี่ตุ๊ก
                        $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'manager' AND e_sub_department = 'Office'");
                        $stmt->bindParam(':workplace', $workplace);
                    }
                } else {
                    echo "ไม่พบเงื่อนไข";
                    // $stmt = $conn->prepare("SELECT e_token FROM employees WHERE e_department = :depart AND e_workplace = :workplace AND e_level IN ('chief', 'manager')");
                    // $stmt->bindParam(':depart', $depart);
                    // $stmt->bindParam(':workplace', $workplace);
                }
    
                $stmt->execute();
                $managers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
                if ($managers) {
                    foreach ($managers as $manager) {
                        $sToken = $manager['e_token'];
    
                        $chOne = curl_init();
                        curl_setopt($chOne, CURLOPT_URL, "https://notify-api.line.me/api/notify");
                        curl_setopt($chOne, CURLOPT_SSL_VERIFYHOST, 0);
                        curl_setopt($chOne, CURLOPT_SSL_VERIFYPEER, 0);
                        curl_setopt($chOne, CURLOPT_POST, 1);
                        curl_setopt($chOne, CURLOPT_POSTFIELDS, "message=" . $sMessage);
                        $headers = array('Content-type: application/x-www-form-urlencoded', 'Authorization: Bearer ' . $sToken . '');
                        curl_setopt($chOne, CURLOPT_HTTPHEADER, $headers);
                        curl_setopt($chOne, CURLOPT_RETURNTRANSFER, 1);
                        $result = curl_exec($chOne);
    
                        if (curl_error($chOne)) {
                            echo 'Error:' . curl_error($chOne);
                        } else {
                            $result_ = json_decode($result, true);
                            echo "status : " . $result_['status'] . "<br>";
                            echo "message : " . $result_['message'] . "<br>";
                        }
                        curl_close($chOne); // Correct function call
                    }
                } else {
                    echo "No tokens found for manager";
                }
            }
            else {
                if ($depart == 'RD') {
                    // แจ้งไลน์โฮซัง
                    $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'manager' AND e_sub_department =  'RD'");
                    // $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE e_department = 'Management' AND e_workplace = :workplace AND e_level = 'manager' AND e_sub_department = :depart");
                    // $stmt = $conn->prepare("SELECT e_username, e_token FROM employees WHERE e_level = 'manager' AND e_workplace = 'Bang Phli' AND e_sub_department = 'RD'");
                    $stmt->bindParam(':workplace', $workplace);
                    // $stmt->bindParam(':depart', $depart);
    
                } else if ($level == 'leader') {
                    if ($depart == 'Office') {
                        // แจ้งเตือนไปที่พี่ตุ๊ก
                        $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'manager' AND e_sub_department = 'Office'");
                        $stmt->bindParam(':workplace', $workplace);
                    }
                } else if ($level == 'chief') {
                    if ($depart == 'Management') {
                        // แจ้งเตือนไปที่พี่ตุ๊ก
                        $stmt = $conn->prepare("SELECT e_token, e_username FROM employees WHERE  e_workplace = :workplace AND e_level = 'manager' AND e_sub_department = 'Office'");
                        $stmt->bindParam(':workplace', $workplace);
                    }
                } else {
                    echo "ไม่พบเงื่อนไข";
                    // $stmt = $conn->prepare("SELECT e_token FROM employees WHERE e_department = :depart AND e_workplace = :workplace AND e_level IN ('chief', 'manager')");
                    // $stmt->bindParam(':depart', $depart);
                    // $stmt->bindParam(':workplace', $workplace);
                }
    
                $stmt->execute();
                $managers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
                if ($managers) {
                    foreach ($managers as $manager) {
                        $sToken = $manager['e_token'];
    
                        $chOne = curl_init();
                        curl_setopt($chOne, CURLOPT_URL, "https://notify-api.line.me/api/notify");
                        curl_setopt($chOne, CURLOPT_SSL_VERIFYHOST, 0);
                        curl_setopt($chOne, CURLOPT_SSL_VERIFYPEER, 0);
                        curl_setopt($chOne, CURLOPT_POST, 1);
                        curl_setopt($chOne, CURLOPT_POSTFIELDS, "message=" . $sMessage);
                        $headers = array('Content-type: application/x-www-form-urlencoded', 'Authorization: Bearer ' . $sToken . '');
                        curl_setopt($chOne, CURLOPT_HTTPHEADER, $headers);
                        curl_setopt($chOne, CURLOPT_RETURNTRANSFER, 1);
                        $result = curl_exec($chOne);
    
                        if (curl_error($chOne)) {
                            echo 'Error:' . curl_error($chOne);
                        } else {
                            $result_ = json_decode($result, true);
                            echo "status : " . $result_['status'] . "<br>";
                            echo "message : " . $result_['message'] . "<br>";
                        }
                        curl_close($chOne); // Correct function call
                    }
                } else {
                    echo "No tokens found for manager";
                }
            }
        }
    } else {
        echo 'เกิดข้อผิดพลาดในการอัปเดตข้อมูล';
    }
} else {
    echo 'เกิดข้อผิดพลาดในการร้องขอ';
}