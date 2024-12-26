<?php
require '../vendor/autoload.php'; // โหลด PhpSpreadsheet
require '../connect.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

date_default_timezone_set('Asia/Bangkok');

function readExcelFile($filePath)
{
    try {
        // โหลดไฟล์ Excel
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();

        $dates = []; // อาร์เรย์สำหรับเก็บวันที่ทั้งหมด

        // วนลูปคอลัมน์ AG และ AL เพื่ออ่านข้อมูล
        $startRowAG = 18; // เริ่มต้นที่ AG18
        $startRowAL = 32; // เริ่มต้นที่ AL32

        while (true) {
            // อ่านค่าในคอลัมน์ AG
            $dateAG = $worksheet->getCell("AG{$startRowAG}")->getValue();
            if (!empty($dateAG)) {
                // ตรวจสอบว่าเป็นค่า Date หรือไม่
                if (Date::isDateTime($worksheet->getCell("AG{$startRowAG}"))) {
                    // แปลงค่าเป็นวันที่ที่ถูกต้อง
                    $dateAG = Date::excelToDateTimeObject($dateAG);
                    $formattedDateAG = $dateAG->format('Y-m-d'); // แปลงเป็นรูปแบบ YYYY-MM-DD

                    // ตรวจสอบว่ามีวันที่นี้อยู่ในอาร์เรย์หรือไม่
                    if (!in_array($formattedDateAG, array_column($dates, 'date'))) {
                        $dates[] = ['column' => 'AG', 'row' => $startRowAG, 'date' => $formattedDateAG];
                    }
                }
            }

            // อ่านค่าในคอลัมน์ AL
            $dateAL = $worksheet->getCell("AL{$startRowAL}")->getValue();
            if (!empty($dateAL)) {
                // ตรวจสอบว่าเป็นค่า Date หรือไม่
                if (Date::isDateTime($worksheet->getCell("AL{$startRowAL}"))) {
                    // แปลงค่าเป็นวันที่ที่ถูกต้อง
                    $dateAL = Date::excelToDateTimeObject($dateAL);
                    $formattedDateAL = $dateAL->format('Y-m-d'); // แปลงเป็นรูปแบบ YYYY-MM-DD

                    // ตรวจสอบว่ามีวันที่นี้อยู่ในอาร์เรย์หรือไม่
                    if (!in_array($formattedDateAL, array_column($dates, 'date'))) {
                        $dates[] = ['column' => 'AL', 'row' => $startRowAL, 'date' => $formattedDateAL];
                    }
                }
            }

            // เพิ่มแถวถัดไป
            $startRowAG++;
            $startRowAL++;

            // หากไม่มีค่าในทั้งสองคอลัมน์ให้หยุดลูป
            if (empty($dateAG) && empty($dateAL)) {
                break;
            }
        }

        return $dates; // ส่งคืนข้อมูลวันที่ทั้งหมด
    } catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

// ตรวจสอบว่าไฟล์ถูกอัพโหลดหรือไม่
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excelFile']) && $_FILES['excelFile']['error'] === UPLOAD_ERR_OK) {
    // กำหนดที่อยู่ของไฟล์ที่อัพโหลด
    $uploadedFile = $_FILES['excelFile']['tmp_name'];

    // เรียกใช้งานฟังก์ชันเพื่ออ่านไฟล์ Excel
    $result = readExcelFile($uploadedFile);

    // แสดงผลลัพธ์
    if (isset($result['error'])) {
        echo 'Error: ' . $result['error'];
    } else {
        echo '<h3>วันที่ในไฟล์ Excel (ไม่ซ้ำกัน):</h3>';
        foreach ($result as $row) {
            // การตรวจสอบวันหยุดในฐานข้อมูลก่อน
            $stmt = $conn->prepare("SELECT COUNT(*) FROM holiday WHERE h_start_date = :h_start_date");
            $stmt->execute([':h_start_date' => $row['date']]);
            $dateExists = $stmt->fetchColumn() > 0;

            if (!$dateExists) {
                // การ insert ข้อมูลลงในฐานข้อมูล
                $holidayName = "วันหยุด"; // กำหนดชื่อวันหยุด
                $holidayStatus = "วันหยุด"; // กำหนดสถานะวันหยุด
                $status = 0; // ค่าเริ่มต้นของสถานะเป็น 0
                $addName = 'Admin';
                $createDate = date('Y-m-d H:i:s');

                // สร้างคำสั่ง SQL สำหรับ insert ข้อมูล
                $query = "INSERT INTO holiday (h_name, h_start_date, h_end_date, h_holiday_status, h_status, h_hr_name, h_hr_datetime)
                          VALUES (:h_name, :h_start_date, :h_end_date, :h_holiday_status, :h_status, :addName, :createDate)";

                // เตรียมคำสั่ง SQL
                $stmt = $conn->prepare($query);

                // bind parameters
                $stmt->bindParam(':h_name', $holidayName);
                $stmt->bindParam(':h_start_date', $row['date']);
                $stmt->bindParam(':h_end_date', $row['date']);
                $stmt->bindParam(':h_holiday_status', $holidayStatus);
                $stmt->bindParam(':h_status', $status, PDO::PARAM_INT);
                $stmt->bindParam(':addName', $addName);
                $stmt->bindParam(':createDate', $createDate);

                // execute statement
                if ($stmt->execute()) {
                    echo "วันที่: {$row['date']} ถูกเพิ่มลงในฐานข้อมูล<br>";
                } else {
                    echo "เกิดข้อผิดพลาดในการเพิ่มข้อมูล<br>";
                }
            } else {
                echo "วันที่: {$row['date']} มีอยู่แล้วในฐานข้อมูล<br>";
            }
        }
    }
} else {
    echo 'กรุณาเลือกไฟล์ Excel และอัพโหลด!';
}

// ปิดการเชื่อมต่อฐานข้อมูล
$conn = null;