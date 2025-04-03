<?php
include 'connect.php';
include 'access_token_channel.php';

date_default_timezone_set('Asia/Bangkok');

// ดึงข้อมูลวันหยุดจากฐานข้อมูล
function getHolidays($conn)
{
    try {
        $query    = $conn->query("SELECT h_start_date, h_end_date FROM holiday");
        $holidays = $query->fetchAll(PDO::FETCH_ASSOC);

        $holidayDates = [];
        foreach ($holidays as $holiday) {
            // เพิ่มวันที่เริ่มต้นเข้าไปในอาร์เรย์
            $holidayDates[] = $holiday['h_start_date'];

            // ถ้าวันที่สิ้นสุดไม่เท่ากับวันที่เริ่มต้น (ช่วงวันหยุด)
            if ($holiday['h_end_date'] != $holiday['h_start_date']) {
                $start     = new DateTime($holiday['h_start_date']);
                $end       = new DateTime($holiday['h_end_date']);
                $interval  = new DateInterval('P1D'); // 1 วัน
                $dateRange = new DatePeriod($start, $interval, $end);

                // เพิ่มทุกวันในช่วงเข้าไปในอาร์เรย์
                foreach ($dateRange as $date) {
                    if ($date->format('Y-m-d') != $holiday['h_start_date']) {
                        $holidayDates[] = $date->format('Y-m-d');
                    }
                }

                // เพิ่มวันสุดท้าย
                $holidayDates[] = $holiday['h_end_date'];
            }
        }

        return $holidayDates;
    } catch (PDOException $e) {
        return [];
    }
}

// ดึงวันหยุด
$holidays = getHolidays($conn);

// คำนวณวันทำงานถัดไปที่ไม่ใช่วันหยุด
function getNextWorkingDay($date, $days, $holidays)
{
    $currentDate = new DateTime($date);
    $daysAdded   = 0;

    while ($daysAdded < $days) {
        $currentDate->modify('+1 day');
        $dateStr = $currentDate->format('Y-m-d');

        // ถ้าไม่ใช่วันเสาร์อาทิตย์และไม่ใช่วันหยุด เพิ่มวันทำงาน
        $weekDay = $currentDate->format('N');
        if ($weekDay < 6 && ! in_array($dateStr, $holidays)) {
            $daysAdded++;
        }
    }

    return $currentDate->format('Y-m-d');
}

// คำนวณวันทำงานที่ผ่านมาที่ไม่ใช่วันหยุด
function getPreviousWorkingDay($date, $days, $holidays)
{
    $currentDate    = new DateTime($date);
    $daysSubtracted = 0;

    while ($daysSubtracted < $days) {
        $currentDate->modify('-1 day');
        $dateStr = $currentDate->format('Y-m-d');

        // ถ้าไม่ใช่วันเสาร์อาทิตย์และไม่ใช่วันหยุด เพิ่มวันทำงาน
        $weekDay = $currentDate->format('N');
        if ($weekDay < 6 && ! in_array($dateStr, $holidays)) {
            $daysSubtracted++;
        }
    }

    return $currentDate->format('Y-m-d');
}

// คำนวณวันที่ควรแจ้งเตือน (3 วันทำงานก่อนและหลัง)
$today        = date('Y-m-d');
$notifyBefore = getNextWorkingDay($today, 3, $holidays);
$notifyAfter  = getPreviousWorkingDay($today, 3, $holidays);

// ดึงข้อมูลรายการลาที่ยังไม่อนุมัติจากหัวหน้า
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
    em.e_username AS employee_username
FROM leave_list li
INNER JOIN employees em ON li.l_usercode = em.e_usercode
WHERE
    li.l_leave_id NOT IN (6, 7)
    AND li.l_approve_status = 0
    AND (
        -- แจ้งเตือนล่วงหน้า 3 วันทำงาน (ไม่นับวันหยุด)
        li.l_leave_start_date = :notifyBefore
        -- แจ้งเตือนหากผ่านมา 3 วันทำงานแล้ว (ไม่นับวันหยุด) แต่ยังไม่มีการอนุมัติ
        OR (li.l_leave_start_date = :notifyAfter AND li.l_approve_status = 0)
    )
    AND li.l_level IN ('user')";

try {
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':notifyBefore', $notifyBefore);
    $stmt->bindParam(':notifyAfter', $notifyAfter);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ถ้าไม่มีข้อมูลก็ไม่ต้องทำอะไรต่อ
    if (empty($results)) {
        exit; // ไม่มีใบลาที่ต้องแจ้งเตือน
    }
} catch (PDOException $e) {
    exit;
}

$line_api_url = "https://api.line.me/v2/bot/message/push";
$mURL         = 'https://lms.system-samt.com/';

// ฟังก์ชันสำหรับหาหัวหน้าที่เกี่ยวข้องกับพนักงาน
function getSupervisors($conn, $employee)
{
    // ตรวจสอบค่า null หรือว่างในแผนก
    $depart     = ! empty($employee['e_department']) ? $employee['e_department'] : '';
    $subDepart  = ! empty($employee['e_sub_department']) ? $employee['e_sub_department'] : '';
    $subDepart2 = ! empty($employee['e_sub_department2']) ? $employee['e_sub_department2'] : '';
    $subDepart3 = ! empty($employee['e_sub_department3']) ? $employee['e_sub_department3'] : '';
    $subDepart4 = ! empty($employee['e_sub_department4']) ? $employee['e_sub_department4'] : '';
    $subDepart5 = ! empty($employee['e_sub_department5']) ? $employee['e_sub_department5'] : '';

    $supervisor_sql = "SELECT e_user_id, e_username
                        FROM employees
                        WHERE e_level IN ('leader', 'chief', 'subLeader')
                        AND e_level <> :level
                        AND e_workplace = :workplace
                        AND (
                              (e_sub_department IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND e_sub_department <> '')
                              OR
                              (e_sub_department2 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND e_sub_department2 <> '')
                              OR
                              (e_sub_department3 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND e_sub_department3 <> '')
                              OR
                              (e_sub_department4 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND e_sub_department4 <> '')
                              OR
                              (e_sub_department5 IN (:depart, :subDepart, :subDepart2, :subDepart3, :subDepart4, :subDepart5) AND e_sub_department5 <> '')
                          )";

    try {
        $supervisor_stmt = $conn->prepare($supervisor_sql);
        $supervisor_stmt->execute([
            ':level'      => $employee['l_level'],
            ':workplace'  => $employee['l_workplace'],
            ':depart'     => $depart,
            ':subDepart'  => $subDepart,
            ':subDepart2' => $subDepart2,
            ':subDepart3' => $subDepart3,
            ':subDepart4' => $subDepart4,
            ':subDepart5' => $subDepart5,
        ]);

        $supervisors = $supervisor_stmt->fetchAll(PDO::FETCH_ASSOC);

        return $supervisors;
    } catch (PDOException $e) {
        return [];
    }
}

// จัดเตรียมใบลาสำหรับแต่ละหัวหน้า
$supervisorLeaves = [];

// จัดกลุ่มใบลาตามหัวหน้า
foreach ($results as $leave) {
    // หาหัวหน้าที่เกี่ยวข้อง
    $supervisors = getSupervisors($conn, $leave);

    if (empty($supervisors)) {
        continue;
    }

    // จัดกลุ่มใบลาตามหัวหน้า
    foreach ($supervisors as $supervisor) {
        $userId = $supervisor['e_user_id'];
        if (! empty($userId)) {
            if (! isset($supervisorLeaves[$userId])) {
                $supervisorLeaves[$userId] = [
                    'supervisor' => $supervisor,
                    'leaves'     => [],
                ];
            }
            $supervisorLeaves[$userId]['leaves'][] = $leave;
        }
    }
}

// ตรวจสอบ access token
if (empty($access_token)) {
    exit;
}

// ส่งข้อความแจ้งเตือนให้แต่ละหัวหน้า
foreach ($supervisorLeaves as $userData) {
    $supervisor = $userData['supervisor'];
    $leaves     = $userData['leaves'];

    // สร้างข้อความแจ้งเตือน
    $message = "รายการใบลาที่รอการอนุมัติ\n\n";

    foreach ($leaves as $index => $leave) {
        $message .= ($index + 1) . ". " . $leave['l_name'] . " แผนก : " . $leave['l_department'] .
            "\nวันที่ลา: " . $leave['l_leave_start_date'] . "\n\n";

        // บันทึกประวัติการส่งข้อความ
        try {
            $insert_sql = "INSERT INTO notification_log
                    (n_leave_id, n_name, n_department, n_leave_start_date, n_leave_start_time, n_leave_end_date, n_leave_end_time, n_send_name, n_workplace)
                    VALUES
                    (:leave_id, :name, :department, :leave_start_date, :leave_start_time, :leave_end_date, :leave_end_time, :send_name, :workplace)";

            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->execute([
                ':leave_id'         => $leave['l_leave_id'],
                ':name'             => $leave['l_name'],
                ':department'       => $leave['l_department'],
                ':leave_start_date' => $leave['l_leave_start_date'],
                ':leave_start_time' => $leave['l_leave_start_time'],
                ':leave_end_date'   => $leave['l_leave_end_date'],
                ':leave_end_time'   => $leave['l_leave_end_time'],
                ':send_name'        => $supervisor['e_username'],
                ':workplace'        => $leave['l_workplace'],
            ]);
        } catch (PDOException $e) {
            // Error handling
        }
    }

    $message .= "กรุณาเข้าสู่ระบบเพื่อดูรายละเอียด: " . $mURL;

    // เตรียมข้อมูลสำหรับส่ง LINE
    $data = [
        'to'       => $supervisor['e_user_id'],
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

    // ส่งข้อความ
    $ch = curl_init($line_api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response  = curl_exec($ch);
    $err       = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
}
