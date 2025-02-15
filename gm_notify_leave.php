<?php
include 'connect.php';
include 'access_token_channel.php';

date_default_timezone_set('Asia/Bangkok');

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
    em.e_user_id,
    em2.e_username AS gm_username
FROM leave_list li
INNER JOIN employees em ON li.l_usercode = em.e_usercode
LEFT JOIN employees em2 ON (
    em2.e_level = 'GM'
    AND (
        em.e_sub_department = em2.e_sub_department
        OR em.e_sub_department = em2.e_sub_department2
        OR em.e_sub_department = em2.e_sub_department3
        OR em.e_sub_department = em2.e_sub_department4
        OR em.e_sub_department = em2.e_sub_department5
    )
)
WHERE
    li.l_leave_id NOT IN (6, 7)
    AND li.l_approve_status3 = 7
    AND (
        -- แจ้งเตือนก่อนวันลา 1 วัน
        li.l_leave_start_date = CURDATE() + INTERVAL 1 DAY
        -- แจ้งเตือนหากวันลาเลยมา 1 วันแล้ว แต่ยังไม่มีการอนุมัติ
        OR (li.l_leave_start_date = CURDATE() - INTERVAL 1 DAY AND li.l_approve_status3 = 7)
    )
    AND li.l_level IN ('user','leader','chief','manager','manager2','assisManager','subLeader','admin')";

$stmt = $conn->prepare($sql);
$stmt->execute();

$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

$line_api_url = "https://api.line.me/v2/bot/message/push";

foreach ($results as $row) {

    $mURL    = 'https://lms.system-samt.com/';
    $message = "ใบลาของ " . $row['l_name'] . " ยังไม่อนุมัติ" . "\nวันที่ลา : " . $row['l_leave_start_date'] . " " . $row['l_leave_start_time'] . " ถึง " . $row['l_leave_end_date'] . " " . $row['l_leave_end_time'] . "\nกรุณาเข้าสู่ระบบเพื่อดูรายละเอียด : " . $mURL;

    $data = [
        'to'       => $row['e_user_id'],
        'messages' => [
            [
                'type' => 'text',
                'text' => $message,
            ],
        ],
    ];

    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $access_token,
    ];

    $ch = curl_init($line_api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);
    curl_close($ch);

    $insert_sql = "INSERT INTO notification_log
            (n_leave_id, n_name, n_department, n_leave_start_date, n_leave_start_time, n_leave_end_date, n_leave_end_time, n_send_name, n_workplace)
            VALUES
            (:leave_id, :name, :department, :leave_start_date, :leave_start_time, :leave_end_date, :leave_end_time, :send_name, :workplace)";

    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->execute([
        ':leave_id'         => $row['l_leave_id'],
        ':name'             => $row['l_name'],
        ':department'       => $row['l_department'],
        ':leave_start_date' => $row['l_leave_start_date'],
        ':leave_start_time' => $row['l_leave_start_time'],
        ':leave_end_date'   => $row['l_leave_end_date'],
        ':leave_end_time'   => $row['l_leave_end_time'],
        ':send_name'        => $row['gm_username'],
        ':workplace'        => $row['l_workplace'],
    ]);
}