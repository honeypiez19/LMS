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

            // คำนวณปีปัจจุบัน
            $currentYear = $currentDate->format('Y');

            // คำนวณว่าในปีปัจจุบันพนักงานจะครบกี่ปี
            $anniversaryDateThisYear = new DateTime($currentYear . '-' . $startDate->format('m-d'));
            $yearsThisAnniversary    = $currentYear - $startDate->format('Y');

            // คำนวณอายุงานปัจจุบัน (สำหรับการแสดงผลเท่านั้น)
            $diff   = $currentDate->diff($startDate);
            $years  = $diff->y;
            $months = $diff->m;
            $days   = $diff->d;

            // สร้างข้อความอายุงานในรูปแบบ ปี เดือน วัน
            $e_yearexp = "{$years}Y {$months}M {$days}D";

            // กำหนดค่า e_leave_annual ตามอายุงานที่จะครบในปีนี้ (ล่วงหน้า)
            if ($yearsThisAnniversary >= 5) {
                $e_leave_annual = 10;
            } elseif ($yearsThisAnniversary >= 4) {
                $e_leave_annual = 9;
            } elseif ($yearsThisAnniversary >= 3) {
                $e_leave_annual = 8;
            } elseif ($yearsThisAnniversary >= 2) {
                $e_leave_annual = 7;
            } elseif ($yearsThisAnniversary >= 1) {
                $e_leave_annual = 6;
            } else {
                $e_leave_annual = 0;
            }

            // วันลากิจ 5 วันเมื่อครบ 1 ปีขึ้นไป (ใช้อายุงานจริงในปัจจุบัน)
            $e_leave_personal = ($years >= 1) ? 5 : 0;

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