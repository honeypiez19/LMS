<?php
include 'connect.php';

try {
    // ดึงข้อมูล e_usercode และ e_work_start_date
    $sql  = "SELECT e_usercode, e_work_start_date FROM employees WHERE e_work_start_date IS NOT NULL";
    $stmt = $conn->query($sql);

    if ($stmt->rowCount() > 0) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $e_usercode        = $row['e_usercode'];
            $e_work_start_date = $row['e_work_start_date'];

            // คำนวณอายุงาน
            $startDate   = new DateTime($e_work_start_date);
            $currentDate = new DateTime();
            $diff        = $currentDate->diff($startDate);

            $years  = $diff->y;
            $months = $diff->m;
            $days   = $diff->d;

            // สร้างข้อความอายุงานในรูปแบบ ปี เดือน วัน
            $e_yearexp = "{$years}Y {$months}M {$days}D";

            // กำหนดค่า e_leave_annual และ e_leave_personal ตามอายุงาน
            if ($years >= 5) {
                $e_leave_annual   = 10;
                $e_leave_personal = 5;
            } elseif ($years >= 4) {
                $e_leave_annual   = 9;
                $e_leave_personal = 5;
            } elseif ($years >= 3) {
                $e_leave_annual   = 8;
                $e_leave_personal = 5;
            } elseif ($years >= 2) {
                $e_leave_annual   = 7;
                $e_leave_personal = 5;
            } elseif ($years >= 1) {
                $e_leave_annual   = 6;
                $e_leave_personal = 5;
            } else {
                $e_leave_annual   = 0;
                $e_leave_personal = 0;
            }

            // เพิ่มวันพักร้อน 1 วัน ถ้าอายุงานมากกว่า 1 ปี และไม่ถึง 10 วัน
            if ($years >= 1 && $e_leave_annual < 10) {
                $e_leave_annual += 1;
            }

            // อัปเดตข้อมูลในฐานข้อมูล
            $update_sql = "UPDATE employees
                SET
                    e_yearexp = :e_yearexp,
                    e_leave_annual = :e_leave_annual,
                    e_leave_personal = :e_leave_personal
                WHERE e_usercode = :e_usercode";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bindParam(':e_yearexp', $e_yearexp);
            $update_stmt->bindParam(':e_leave_annual', $e_leave_annual);
            $update_stmt->bindParam(':e_leave_personal', $e_leave_personal);
            $update_stmt->bindParam(':e_usercode', $e_usercode);

            $update_stmt->execute();
        }

        echo "Updated successfully!";
    } else {
        echo "No records found!";
    }
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

// ปิดการเชื่อมต่อ
$conn = null;
