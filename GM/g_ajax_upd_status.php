<?php
// Start session
session_start();

include '../connect.php';
include '../access_token_channel.php';     // Include database connection file
                                           // Include database connection file
date_default_timezone_set('Asia/Bangkok'); // เวลาไทย

$appDate = date("Y-m-d H:i:s");

// รับค่าที่ส่งมาจาก AJAX
$userCode       = $_POST['userCode'];
$createDate     = $_POST['createDate'];
$status         = $_POST['status'];
$empName        = $_POST['empName'];
$userName       = $_POST['userName'];
$proveName      = $_POST['proveName'];
$leaveType      = $_POST['leaveType'];
$leaveReason    = $_POST['leaveReason'];
$leaveStartDate = $_POST['leaveStartDate'];
$leaveEndDate   = $_POST['leaveEndDate'];
$depart         = $_POST['depart'];
$leaveStatus    = $_POST['leaveStatus'];
$subDepart      = $_POST['subDepart'];
$reasonNoProve  = $_POST['reasonNoProve'];

if ($status == 8) {
    // เตรียมคำสั่ง SQL
    $sql = "UPDATE leave_list SET l_approve_status3 = :status, l_approve_datetime3 = :appDate, l_approve_name3 = :userName
            WHERE l_usercode = :userCode AND l_create_datetime = :createDate";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':appDate', $appDate);
    $stmt->bindParam(':userName', $userName);
    $stmt->bindParam(':userCode', $userCode);
    $stmt->bindParam(':createDate', $createDate);

    if ($stmt->execute()) {
        $stmt = $conn->prepare("SELECT e_user_id FROM employees WHERE e_usercode = :usercode");
        $stmt->bindParam(':usercode', $userCode);
        $stmt->execute();
        $userId = $stmt->fetchColumn(); // เปลี่ยนชื่อตัวแปรเพื่อความชัดเจน
        $sURL   = 'https://lms.system-samt.com/';

        // ข้อความแจ้งเตือน
        $message = "$proveName อนุมัติใบลา\nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nวันเวลาที่ลา : $leaveStartDate ถึง $leaveEndDate";

        if ($leaveStatus == 'ยกเลิกใบลา') {
            $message = "$proveName อนุมัติยกเลิกใบลาของ $empName\nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nวันเวลาที่ลา : $leaveStartDate ถึง $leaveEndDate";
        }

        // แจ้งเตือนพนักงาน
        if ($userId) {
            // สร้าง message data
            $messageData = [
                'to'       => $userId,
                'messages' => [
                    [
                        'type' => 'text',
                        'text' => $message,
                    ],
                ],
            ];

            // แปลงข้อมูลเป็น JSON
            $jsonData = json_encode($messageData);

            // ส่งข้อความด้วย cURL
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.line.me/v2/bot/message/push');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $access_token,
            ]);

            $result = curl_exec($ch);

            if (curl_error($ch)) {
                echo 'Error: ' . curl_error($ch);
            } else {
                $responseData = json_decode($result, true);
                if (isset($responseData['message'])) {
                    echo "Error message: " . $responseData['message'] . "\n";
                } else {
                    echo "Message sent to employee successfully\n";
                }
            }
            curl_close($ch);
        }

        $adminMessage = "ข้อมูลการลา - $empName\nการอนุมัติโดย: $proveName\nประเภทการลา: $leaveType\nสถานะ: " .
            ($leaveStatus == 'ยกเลิกใบลา' ? 'อนุมัติการยกเลิก' : 'อนุมัติ') .
            "\nเหตุผลการลา: $leaveReason\nวันเวลาที่ลา: $leaveStartDate ถึง $leaveEndDate";

        // ดึงข้อมูลผู้ใช้ระดับ admin
        $adminStmt = $conn->prepare("SELECT e_user_id FROM employees WHERE e_level = 'admin' AND e_workplace = :workplace");
        $stmt->bindParam(':workplace', $workplace);

        $adminStmt->execute();
        $adminUsers = $adminStmt->fetchAll(PDO::FETCH_COLUMN);

        // ส่งข้อความแจ้งเตือนไปยังผู้ใช้ระดับ admin ทุกคน
        foreach ($adminUsers as $adminUserId) {
            if (! empty($adminUserId)) {
                // สร้าง message data สำหรับ admin
                $adminMessageData = [
                    'to'       => $adminUserId,
                    'messages' => [
                        [
                            'type' => 'text',
                            'text' => $adminMessage,
                        ],
                    ],
                ];

                // แปลงข้อมูลเป็น JSON
                $adminJsonData = json_encode($adminMessageData);

                // ส่งข้อความด้วย cURL
                $adminCh = curl_init();
                curl_setopt($adminCh, CURLOPT_URL, 'https://api.line.me/v2/bot/message/push');
                curl_setopt($adminCh, CURLOPT_POST, true);
                curl_setopt($adminCh, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($adminCh, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($adminCh, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($adminCh, CURLOPT_POSTFIELDS, $adminJsonData);
                curl_setopt($adminCh, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $access_token,
                ]);

                $adminResult = curl_exec($adminCh);

                if (curl_error($adminCh)) {
                    echo 'Error sending to admin: ' . curl_error($adminCh) . "\n";
                } else {
                    $adminResponseData = json_decode($adminResult, true);
                    if (isset($adminResponseData['message'])) {
                        echo "Error sending to admin: " . $adminResponseData['message'] . "\n";
                    } else {
                        echo "Message sent to admin successfully\n";
                    }
                }
                curl_close($adminCh);
            }
        }

        // แจ้งเตือนตามเงื่อนไขของผู้อนุมัติ
        if ($userName == 'Anchana') {
            // แจ้งเตือน Pornsuk
            $stmt = $conn->prepare("SELECT e_user_id FROM employees WHERE e_username = 'Pornsuk'");
            $stmt->execute();
            $pornsukUserId = $stmt->fetchColumn();

            $notifyMessage = "มีใบลาของ $empName\n$proveName อนุมัติใบลาเรียบร้อย\nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nวันเวลาที่ลา : $leaveStartDate ถึง $leaveEndDate";

            if ($leaveStatus == 'ยกเลิกใบลา') {
                $notifyMessage = "$proveName อนุมัติยกเลิกใบลาของ $empName\nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nวันเวลาที่ลา : $leaveStartDate ถึง $leaveEndDate";
            }

            if ($pornsukUserId) {
                $messageData = [
                    'to'       => $pornsukUserId,
                    'messages' => [
                        [
                            'type' => 'text',
                            'text' => $notifyMessage,
                        ],
                    ],
                ];

                $jsonData = json_encode($messageData);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://api.line.me/v2/bot/message/push');
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $access_token,
                ]);

                $result = curl_exec($ch);

                if (curl_error($ch)) {
                    echo 'Error: ' . curl_error($ch);
                } else {
                    $responseData = json_decode($result, true);
                    if (isset($responseData['message'])) {
                        echo "Error message (Pornsuk): " . $responseData['message'] . "\n";
                    } else {
                        echo "Message sent to Pornsuk successfully\n";
                    }
                }
                curl_close($ch);
            }
        } else if ($userName == 'Horita') {
            // แจ้งเตือน Matsumoto
            $stmt = $conn->prepare("SELECT e_user_id FROM employees WHERE e_username = 'Matsumoto'");
            $stmt->execute();
            $matsumotoUserId = $stmt->fetchColumn();

            $notifyMessage = "มีใบลาของ $empName\n$proveName อนุมัติใบลาเรียบร้อย \nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nวันเวลาที่ลา : $leaveStartDate ถึง $leaveEndDate";

            if ($leaveStatus == 'ยกเลิกใบลา') {
                $notifyMessage = "$proveName อนุมัติยกเลิกใบลาของ $empName\nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nวันเวลาที่ลา : $leaveStartDate ถึง $leaveEndDate";
            }

            if ($matsumotoUserId) {
                $messageData = [
                    'to'       => $matsumotoUserId,
                    'messages' => [
                        [
                            'type' => 'text',
                            'text' => $notifyMessage,
                        ],
                    ],
                ];

                $jsonData = json_encode($messageData);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://api.line.me/v2/bot/message/push');
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $access_token,
                ]);

                $result = curl_exec($ch);

                if (curl_error($ch)) {
                    echo 'Error: ' . curl_error($ch);
                } else {
                    $responseData = json_decode($result, true);
                    if (isset($responseData['message'])) {
                        echo "Error message (Matsumoto): " . $responseData['message'] . "\n";
                    } else {
                        echo "Message sent to Matsumoto successfully\n";
                    }
                }
                curl_close($ch);
            }
        } elseif ($userName == 'Chaikorn') {
            // แจ้งเตือนผู้ที่มีระดับ GM
            $stmt = $conn->prepare("SELECT e_user_id, e_username FROM employees WHERE e_level = 'GM'");
            $stmt->execute();
            $gmUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $notifyMessage = "มีใบลาของ $empName\n$proveName อนุมัติใบลาเรียบร้อย\nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nวันเวลาที่ลา : $leaveStartDate ถึง $leaveEndDate";

            if ($leaveStatus == 'ยกเลิกใบลา') {
                $notifyMessage = "$proveName อนุมัติยกเลิกใบลาของ $empName\nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nวันเวลาที่ลา : $leaveStartDate ถึง $leaveEndDate";
            }

            foreach ($gmUsers as $gmUser) {
                $gmUserId   = $gmUser['e_user_id'];
                $gmUsername = $gmUser['e_username'];

                if (! $gmUserId) {
                    echo "ไม่พบ User ID สำหรับ GM: " . $gmUsername . "<br>";
                    continue;
                }

                $messageData = [
                    'to'       => $gmUserId,
                    'messages' => [
                        [
                            'type' => 'text',
                            'text' => $notifyMessage,
                        ],
                    ],
                ];

                $jsonData = json_encode($messageData);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://api.line.me/v2/bot/message/push');
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $access_token,
                ]);

                $result = curl_exec($ch);

                if (curl_error($ch)) {
                    echo 'Error: ' . curl_error($ch) . "<br>";
                } else {
                    $responseData = json_decode($result, true);
                    if (isset($responseData['message'])) {
                        echo "GM User: " . $gmUsername . " - Error: " . $responseData['message'] . "<br>";
                    } else {
                        echo "Message sent to GM: " . $gmUsername . " successfully<br>";
                    }
                }
                curl_close($ch);
            }
        }
    }
} else if ($status == 9) {
    $sql = "UPDATE leave_list SET l_approve_status3 = :status, l_approve_datetime3 = :appDate, l_approve_name3 = :userName, l_reason3 = :reasonNoProve
            WHERE l_usercode = :userCode AND l_create_datetime = :createDate";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':appDate', $appDate);
    $stmt->bindParam(':userName', $userName);
    $stmt->bindParam(':userCode', $userCode);
    $stmt->bindParam(':createDate', $createDate);
    $stmt->bindParam(':reasonNoProve', $reasonNoProve); // Binding l_reason

    if ($stmt->execute()) {
        $stmt = $conn->prepare("SELECT e_user_id FROM employees WHERE e_usercode = :usercode");
        $stmt->bindParam(':usercode', $userCode);
        $stmt->execute();
        $userId = $stmt->fetchColumn(); // เปลี่ยนชื่อตัวแปรเพื่อความชัดเจน
        $sURL   = 'https://lms.system-samt.com/';

        // ข้อความแจ้งเตือน
        $message = "$proveName ไม่อนุมัติใบลา\nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nวันเวลาที่ลา : $leaveStartDate ถึง $leaveEndDate";

        if ($leaveStatus == 'ยกเลิกใบลา') {
            $message = "$proveName ไม่อนุมัติยกเลิกใบลาของ $empName\nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nวันเวลาที่ลา : $leaveStartDate ถึง $leaveEndDate";
        }

        // แจ้งเตือนพนักงาน
        if ($userId) {
            // สร้าง message data
            $messageData = [
                'to'       => $userId,
                'messages' => [
                    [
                        'type' => 'text',
                        'text' => $message,
                    ],
                ],
            ];

            // แปลงข้อมูลเป็น JSON
            $jsonData = json_encode($messageData);

            // ส่งข้อความด้วย cURL
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.line.me/v2/bot/message/push');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $access_token,
            ]);

            $result = curl_exec($ch);

            if (curl_error($ch)) {
                echo 'Error: ' . curl_error($ch);
            } else {
                $responseData = json_decode($result, true);
                if (isset($responseData['message'])) {
                    echo "Error message: " . $responseData['message'] . "\n";
                } else {
                    echo "Message sent to employee successfully\n";
                }
            }
            curl_close($ch);
        }

        // แจ้งเตือนตามเงื่อนไขของผู้อนุมัติ
        if ($userName == 'Anchana') {
            // แจ้งเตือน Pornsuk
            $stmt = $conn->prepare("SELECT e_user_id FROM employees WHERE e_username = 'Pornsuk'");
            $stmt->execute();
            $pornsukUserId = $stmt->fetchColumn();

            $notifyMessage = "มีใบลาของ $empName\n$proveName ไม่อนุมัติใบลาเรียบร้อย\nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nวันเวลาที่ลา : $leaveStartDate ถึง $leaveEndDate";

            if ($leaveStatus == 'ยกเลิกใบลา') {
                $notifyMessage = "$proveName ไม่อนุมัติยกเลิกใบลาของ $empName\nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nวันเวลาที่ลา : $leaveStartDate ถึง $leaveEndDate";
            }

            if ($pornsukUserId) {
                $messageData = [
                    'to'       => $pornsukUserId,
                    'messages' => [
                        [
                            'type' => 'text',
                            'text' => $notifyMessage,
                        ],
                    ],
                ];

                $jsonData = json_encode($messageData);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://api.line.me/v2/bot/message/push');
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $access_token,
                ]);

                $result = curl_exec($ch);

                if (curl_error($ch)) {
                    echo 'Error: ' . curl_error($ch);
                } else {
                    $responseData = json_decode($result, true);
                    if (isset($responseData['message'])) {
                        echo "Error message (Pornsuk): " . $responseData['message'] . "\n";
                    } else {
                        echo "Message sent to Pornsuk successfully\n";
                    }
                }
                curl_close($ch);
            }
        } else if ($userName == 'Horita') {
            // แจ้งเตือน Matsumoto
            $stmt = $conn->prepare("SELECT e_user_id FROM employees WHERE e_username = 'Matsumoto'");
            $stmt->execute();
            $matsumotoUserId = $stmt->fetchColumn();

            $notifyMessage = "มีใบลาของ $empName\n$proveName ไม่อนุมัติใบลาเรียบร้อย \nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nวันเวลาที่ลา : $leaveStartDate ถึง $leaveEndDate";

            if ($leaveStatus == 'ยกเลิกใบลา') {
                $notifyMessage = "$proveName ไม่อนุมัติยกเลิกใบลาของ $empName\nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nวันเวลาที่ลา : $leaveStartDate ถึง $leaveEndDate";
            }

            if ($matsumotoUserId) {
                $messageData = [
                    'to'       => $matsumotoUserId,
                    'messages' => [
                        [
                            'type' => 'text',
                            'text' => $notifyMessage,
                        ],
                    ],
                ];

                $jsonData = json_encode($messageData);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://api.line.me/v2/bot/message/push');
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $access_token,
                ]);

                $result = curl_exec($ch);

                if (curl_error($ch)) {
                    echo 'Error: ' . curl_error($ch);
                } else {
                    $responseData = json_decode($result, true);
                    if (isset($responseData['message'])) {
                        echo "Error message (Matsumoto): " . $responseData['message'] . "\n";
                    } else {
                        echo "Message sent to Matsumoto successfully\n";
                    }
                }
                curl_close($ch);
            }
        } elseif ($userName == 'Chaikorn') {
            // แจ้งเตือนผู้ที่มีระดับ GM
            $stmt = $conn->prepare("SELECT e_user_id, e_username FROM employees WHERE e_level = 'GM'");
            $stmt->execute();
            $gmUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $notifyMessage = "มีใบลาของ $empName\n$proveName ไม่อนุมัติใบลาเรียบร้อย\nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nวันเวลาที่ลา : $leaveStartDate ถึง $leaveEndDate";

            if ($leaveStatus == 'ยกเลิกใบลา') {
                $notifyMessage = "$proveName ไม่อนุมัติยกเลิกใบลาของ $empName\nประเภทการลา : $leaveType\nเหตุผลการลา : $leaveReason\nวันเวลาที่ลา : $leaveStartDate ถึง $leaveEndDate";
            }

            foreach ($gmUsers as $gmUser) {
                $gmUserId   = $gmUser['e_user_id'];
                $gmUsername = $gmUser['e_username'];

                if (! $gmUserId) {
                    echo "ไม่พบ User ID สำหรับ GM: " . $gmUsername . "<br>";
                    continue;
                }

                $messageData = [
                    'to'       => $gmUserId,
                    'messages' => [
                        [
                            'type' => 'text',
                            'text' => $notifyMessage,
                        ],
                    ],
                ];

                $jsonData = json_encode($messageData);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://api.line.me/v2/bot/message/push');
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $access_token,
                ]);

                $result = curl_exec($ch);

                if (curl_error($ch)) {
                    echo 'Error: ' . curl_error($ch) . "<br>";
                } else {
                    $responseData = json_decode($result, true);
                    if (isset($responseData['message'])) {
                        echo "GM User: " . $gmUsername . " - Error: " . $responseData['message'] . "<br>";
                    } else {
                        echo "Message sent to GM: " . $gmUsername . " successfully<br>";
                    }
                }
                curl_close($ch);
            }
        }
    } else {
        echo 'อัปเดตสถานะผ่านไม่สำเร็จ';
    }
} else {
    echo "ไม่มีสถานะนี้";
}

// ปิดการเชื่อมต่อกับฐานข้อมูล
$conn = null;