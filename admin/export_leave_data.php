<?php

// เริ่มเซสชัน (ถ้าจำเป็น)
session_start();

include '../connect.php';

// รับค่าจากฟอร์ม
$dateRangeType = $_POST['dateRangeType'] ?? 'current';
$exportFormat  = $_POST['exportFormat'] ?? 'excel';
$includeHeader = isset($_POST['includeHeader']);

// รับค่าการค้นหารหัสพนักงาน
$codeSearch = $_POST['exportEmployeeCode'] ?? '';

// กำหนดช่วงวันที่
if ($dateRangeType === 'custom') {
    $startDate = $_POST['startDate'];
    $endDate   = $_POST['endDate'];
} else {
    $startDate = $_POST['startDateCurrent'];
    $endDate   = $_POST['endDateCurrent'];
}

// รับค่าปีและเดือนที่เลือก
$selectedYear  = $_POST['year'] ?? date('Y');
$selectedMonth = $_POST['month'] ?? 'All';

// ตรวจสอบความถูกต้องของวันที่
if (! validateDate($startDate) || ! validateDate($endDate)) {
    die("รูปแบบวันที่ไม่ถูกต้อง");
}

// ตรวจสอบว่าวันที่เริ่มต้นต้องมาก่อนวันที่สิ้นสุด
if (strtotime($startDate) > strtotime($endDate)) {
    die("วันที่เริ่มต้นต้องมาก่อนวันที่สิ้นสุด");
}

// รับค่า workplace ถ้ามี
$workplace = $_POST['workplace'] ?? null;

// สร้างคำสั่ง SQL สำหรับดึงข้อมูลการลา
$sql = "SELECT
    li.*,
    em.*
FROM leave_list li
INNER JOIN employees em
    ON li.l_usercode = em.e_usercode
WHERE
    li.l_hr_status <> 3
    AND l_leave_status <> 1";
// เพิ่มเงื่อนไขค้นหาตามรหัสพนักงาน (ถ้ามี)
if (! empty($codeSearch)) {
    $sql .= " AND li.l_usercode LIKE :codeSearch";
}

// เพิ่มเงื่อนไขกรองตามช่วงวันที่ที่เลือก - กรองเฉพาะข้อมูลที่มีวันลาอยู่ในช่วงที่เลือก
$sql .= " AND (
    (li.l_leave_start_date <= :endDate AND li.l_leave_end_date >= :startDate)
)";

// เพิ่มเงื่อนไขการกรองตามปีและเดือนที่เลือก (ถ้าต้องการให้คงไว้)
if ($selectedMonth != "All") {
    $sql .= " AND (MONTH(li.l_leave_start_date) = :selectedMonth OR MONTH(li.l_leave_end_date) = :selectedMonth)";
}
$sql .= " AND (YEAR(li.l_leave_start_date) = :selectedYear OR YEAR(li.l_leave_end_date) = :selectedYear)";

if (isset($workplace) && ! empty($workplace)) {
    $sql .= " AND li.l_workplace = :workplace";
}

$sql .= " ORDER BY li.l_leave_end_date DESC";

try {
    // เตรียมและประมวลผลคำสั่ง SQL ด้วย PDO
    $stmt = $conn->prepare($sql);

    // เพิ่ม Parameter สำหรับการค้นหารหัสพนักงาน
    if (! empty($codeSearch)) {
        $searchParam = "%$codeSearch%";
        $stmt->bindParam(':codeSearch', $searchParam, PDO::PARAM_STR);
    }

    // Bind parameters สำหรับช่วงวันที่
    $stmt->bindParam(':startDate', $startDate, PDO::PARAM_STR);
    $stmt->bindParam(':endDate', $endDate, PDO::PARAM_STR);

    // Bind parameters เพิ่มเติม
    $stmt->bindParam(':selectedYear', $selectedYear, PDO::PARAM_STR);

    if ($selectedMonth != "All") {
        $stmt->bindParam(':selectedMonth', $selectedMonth, PDO::PARAM_STR);
    }

    if (isset($workplace) && ! empty($workplace)) {
        $stmt->bindParam(':workplace', $workplace, PDO::PARAM_STR);
    }

    // Execute query
    $stmt->execute();

    // Check if any rows were returned
    if ($stmt->rowCount() <= 0) {
        // ส่งกลับเป็น JSON เพื่อให้ JS แสดง SweetAlert
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'ไม่พบข้อมูลการลาในช่วงเวลาที่เลือก']);
        exit();
    }
} catch (PDOException $e) {
    // ส่งกลับเป็น JSON เพื่อให้ JS แสดง SweetAlert
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาดในการค้นหาข้อมูล: ' . $e->getMessage()]);
    exit();
}

// ฟังก์ชันตรวจสอบรูปแบบวันที่
function validateDate($date, $format = 'Y-m-d')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

// ฟังก์ชันสำหรับการแปลงเวลาเริ่มต้นตามรูปแบบที่กำหนด
function formatStartTime($startTime, $remark = null)
{
    // แปลงรูปแบบเวลาตามเงื่อนไขที่กำหนด
    if ($startTime == '08:30:00' && $remark == '08:10:00') {
        return '08:10:00';
    } elseif ($startTime == '08:30:00' && $remark == '08:15:00') {
        return '08:15:00';
    } elseif ($startTime == '09:00:00' && $remark == '08:45:00') {
        return '08:45:00';
    } elseif ($startTime == '09:30:00' && $remark == '09:10:00') {
        return '09:10:00';
    } elseif ($startTime == '09:30:00' && $remark == '09:15:00') {
        return '09:15:00';
    } elseif ($startTime == '10:00:00' && $remark == '09:45:00') {
        return '09:45:00';
    } elseif ($startTime == '10:30:00' && $remark == '10:10:00') {
        return '10:10:00';
    } elseif ($startTime == '10:30:00' && $remark == '10:15:00') {
        return '10:15:00';
    } elseif ($startTime == '11:00:00' && $remark == '10:45:00') {
        return '10:45:00';
    } elseif ($startTime == '12:00:00') {
        return '11:45:00';
    } elseif ($startTime == '13:00:00') {
        return '12:45:00';
    } elseif ($startTime == '13:30:00' && $remark == '13:10:00') {
        return '13:10:00';
    } elseif ($startTime == '13:30:00' && $remark == '13:15:00') {
        return '13:15:00';
    } elseif ($startTime == '14:00:00' && $remark == '13:40:00') {
        return '13:40:00';
    } elseif ($startTime == '14:00:00' && $remark == '13:45:00') {
        return '13:45:00';
    } elseif ($startTime == '14:30:00' && $remark == '14:10:00') {
        return '14:10:00';
    } elseif ($startTime == '14:30:00' && $remark == '14:15:00') {
        return '14:15:00';
    } elseif ($startTime == '15:00:00' && $remark == '14:40:00') {
        return '14:40:00';
    } elseif ($startTime == '15:00:00' && $remark == '14:45:00') {
        return '14:45:00';
    } elseif ($startTime == '15:30:00' && $remark == '15:10:00') {
        return '15:10:00';
    } elseif ($startTime == '15:30:00' && $remark == '15:15:00') {
        return '15:15:00';
    } elseif ($startTime == '16:00:00' && $remark == '15:40:00') {
        return '15:40:00';
    } elseif ($startTime == '16:00:00' && $remark == '15:45:00') {
        return '15:45:00';
    } elseif ($startTime == '16:30:00' && $remark == '16:10:00') {
        return '16:10:00';
    } elseif ($startTime == '16:30:00' && $remark == '16:15:00') {
        return '16:15:00';
    } elseif ($startTime == '17:00:00') {
        return '16:40:00';
    } else {
        return $startTime;
    }
}

// ฟังก์ชันสำหรับการแปลงเวลาสิ้นสุดตามรูปแบบที่กำหนด
function formatEndTime($endTime, $remark = null)
{
    // แปลงรูปแบบเวลาตามเงื่อนไขที่กำหนด
    if ($endTime == '08:30:00' && $remark == '08:10:00') {
        return '08:10:00';
    } elseif ($endTime == '08:30:00' && $remark == '08:15:00') {
        return '08:15:00';
    } elseif ($endTime == '09:00:00' && $remark == '08:45:00') {
        return '08:45:00';
    } elseif ($endTime == '09:30:00' && $remark == '09:10:00') {
        return '09:10:00';
    } elseif ($endTime == '09:30:00' && $remark == '09:15:00') {
        return '09:15:00';
    } elseif ($endTime == '10:00:00' && $remark == '09:45:00') {
        return '09:45:00';
    } elseif ($endTime == '10:30:00' && $remark == '10:10:00') {
        return '10:10:00';
    } elseif ($endTime == '10:30:00' && $remark == '10:15:00') {
        return '10:15:00';
    } elseif ($endTime == '11:00:00' && $remark == '10:45:00') {
        return '10:45:00';
    } elseif ($endTime == '12:00:00') {
        return '11:45:00';
    } elseif ($endTime == '13:00:00') {
        return '12:45:00';
    } elseif ($endTime == '13:30:00' && $remark == '13:10:00') {
        return '13:10:00';
    } elseif ($endTime == '13:30:00' && $remark == '13:15:00') {
        return '13:15:00';
    } elseif ($endTime == '14:00:00' && $remark == '13:40:00') {
        return '13:40:00';
    } elseif ($endTime == '14:00:00' && $remark == '13:45:00') {
        return '13:45:00';
    } elseif ($endTime == '14:30:00' && $remark == '14:10:00') {
        return '14:10:00';
    } elseif ($endTime == '14:30:00' && $remark == '14:15:00') {
        return '14:15:00';
    } elseif ($endTime == '15:00:00' && $remark == '14:40:00') {
        return '14:40:00';
    } elseif ($endTime == '15:00:00' && $remark == '14:45:00') {
        return '14:45:00';
    } elseif ($endTime == '15:30:00' && $remark == '15:10:00') {
        return '15:10:00';
    } elseif ($endTime == '15:30:00' && $remark == '15:15:00') {
        return '15:15:00';
    } elseif ($endTime == '16:00:00' && $remark == '15:40:00') {
        return '15:40:00';
    } elseif ($endTime == '16:00:00' && $remark == '15:45:00') {
        return '15:45:00';
    } elseif ($endTime == '16:30:00' && $remark == '16:10:00') {
        return '16:10:00';
    } elseif ($endTime == '16:30:00' && $remark == '16:15:00') {
        return '16:15:00';
    } elseif ($endTime == '17:00:00') {
        return '16:40:00';
    } else {
        return $endTime;
    }
}

// ฟังก์ชันสำหรับการส่งออกไฟล์ Excel (.xlsx)
function exportToExcel($stmt, $includeHeader, $startDate, $endDate)
{
    global $conn; // เพิ่มการเข้าถึง $conn

    require '../vendor/autoload.php'; // ต้องติดตั้ง PhpSpreadsheet ผ่าน Composer ก่อน

    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet       = $spreadsheet->getActiveSheet();

    // แก้ไขจุดนี้: ใช้ $spreadsheet แทน $sheet สำหรับการตั้งค่า default style
    $spreadsheet->getDefaultStyle()->getFont()->setSize(16);

    // ตั้งชื่อ sheet
    $sheet->setTitle('ข้อมูลการลา');

    // กำหนดหัวข้อคอลัมน์ - เพิ่มคอลัมน์วันที่ยื่นใบลา และคอลัมน์หมายเหตุ
    $headers = ['ลำดับ', 'รหัสพนักงาน', 'ชื่อ-นามสกุล', 'ประเภทการลา', 'วันที่ยื่นใบลา', 'วันเวลาที่เริ่มต้น', 'วันเวลาที่สิ้นสุด', 'จำนวนวัน', 'เหตุผล', 'หมายเหตุ'];

    $row = 1;

    // เพิ่มหัวข้อรายงาน
    if ($includeHeader) {
        $sheet->setCellValue('A' . $row, 'รายงานข้อมูลการลา');
        $sheet->mergeCells('A' . $row . ':J' . $row);                        // แก้ไขจาก I เป็น J เนื่องจากเพิ่มคอลัมน์
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(18); // เพิ่มขนาดหัวข้อใหญ่เป็น 18
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $row++;

        $sheet->setCellValue('A' . $row, 'ช่วงวันที่: ' . date('d/m/Y', strtotime($startDate)) . ' ถึง ' . date('d/m/Y', strtotime($endDate)));
        $sheet->mergeCells('A' . $row . ':J' . $row); // แก้ไขจาก I เป็น J เนื่องจากเพิ่มคอลัมน์
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $row++;
        $row++; // เว้นบรรทัด
    }

    // เก็บแถวเริ่มต้นของตาราง เพื่อสร้างขอบตารางทั้งหมดภายหลัง
    $tableStartRow = $row;

    // เพิ่มหัวข้อคอลัมน์
    for ($i = 0; $i < count($headers); $i++) {
        $sheet->setCellValue(chr(65 + $i) . $row, $headers[$i]);
        $sheet->getStyle(chr(65 + $i) . $row)->getFont()->setBold(true);
        $sheet->getStyle(chr(65 + $i) . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('D3D3D3');
    }

    // ปรับความกว้างคอลัมน์อัตโนมัติ
    for ($i = 0; $i < count($headers); $i++) {
        $sheet->getColumnDimension(chr(65 + $i))->setAutoSize(true);
    }

    $row++;

    // นับจำนวนแถวทั้งหมด
    $total_rows = $stmt->rowCount();

    // ดึงข้อมูลทั้งหมดมาก่อน
    $all_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // กำหนดตัวนับเริ่มต้นเป็น 1 และเพิ่มขึ้นทีละ 1
    $counter = 1;

    // เพิ่มข้อมูล - ใช้ array ที่ดึงมาแล้ว
    foreach ($all_data as $data) {
// ปรับวันลาให้อยู่ในช่วงที่เลือก
        $leaveStartDateTime = new DateTime($data['l_leave_start_date'] . ' ' . $data['l_leave_start_time']);
        $leaveEndDateTime   = new DateTime($data['l_leave_end_date'] . ' ' . $data['l_leave_end_time']);

// ช่วงเวลาเลือกตั้งแต่ 08:00 - 17:00
        $filterStartDateTime = new DateTime($startDate . ' 08:00:00');
        $filterEndDateTime   = new DateTime($endDate . ' 17:00:00');

// ตัดวันที่ลาให้ตรงตามช่วงที่เลือกเท่านั้น
        if ($leaveStartDateTime < $filterStartDateTime) {
            $leaveStartDateTime = $filterStartDateTime;
        }

        if ($leaveEndDateTime > $filterEndDateTime) {
            $leaveEndDateTime = $filterEndDateTime;
        }

        // แสดงเวลาตาม formatStartTime และ formatEndTime
        $formattedStartTime = formatStartTime($leaveStartDateTime->format('H:i:s'), $data['l_time_remark'] ?? null);
        $formattedEndTime   = formatEndTime($leaveEndDateTime->format('H:i:s'), $data['l_time_remark2'] ?? null);

        $sheet->setCellValue('A' . $row, $counter);
        $sheet->setCellValue('B' . $row, $data['e_usercode'] ?? $data['l_usercode']);
        $sheet->setCellValue('C' . $row, $data['e_name'] ?? $data['l_name']);

        // ประเภทการลา - ปรับตามโครงสร้างข้อมูลจริง
        $leaveType = '';
        switch ($data['l_leave_id']) {
            case 1:$leaveType = 'ลากิจได้รับค่าจ้าง';
                break;
            case 2:$leaveType = 'ลากิจไม่ได้รับค่าจ้าง';
                break;
            case 3:$leaveType = 'ลาป่วย';
                break;
            case 4:$leaveType = 'ลาป่วยจากงาน';
                break;
            case 5:$leaveType = 'ลาพักร้อน';
                break;
            case 6:$leaveType = 'ขาดงาน';
                break;
            case 7:$leaveType = 'มาสาย';
                break;
            default:$leaveType = 'อื่นๆ';
        }

        $sheet->setCellValue('D' . $row, $leaveType);

        // เพิ่มวันที่ยื่นใบลา (l_create_datetime)
        if (isset($data['l_create_datetime'])) {
            $create_date = new DateTime($data['l_create_datetime']);
            $sheet->setCellValue('E' . $row, $create_date->format('d/m/Y H:i:s'));
        } else {
            $sheet->setCellValue('E' . $row, '');
        }

        // วันที่และเวลาเริ่มต้น - ใช้ฟังก์ชัน formatStartTime
        $sheet->setCellValue(
            'F' . $row,
            $leaveStartDateTime->format('d/m/Y') . ' ' . $formattedStartTime
        );

        // วันที่และเวลาสิ้นสุด - ใช้ฟังก์ชัน formatEndTime
        $sheet->setCellValue(
            'G' . $row,
            $leaveEndDateTime->format('d/m/Y') . ' ' . $formattedEndTime
        );
        // ส่วนของการคำนวณระยะเวลาการลาและอื่นๆ ยังคงเหมือนเดิม

        // คำนวณระยะเวลาการลา
        $holiday_query = "SELECT COUNT(*) as holiday_count
                  FROM holiday
                  WHERE h_start_date BETWEEN :start_date AND :end_date
                  AND h_holiday_status = 'วันหยุด'
                  AND h_status = 0";

        $holiday_stmt = $conn->prepare($holiday_query);
        $holiday_stmt->bindValue(':start_date', $leaveStartDateTime->format('Y-m-d'));
        $holiday_stmt->bindValue(':end_date', $leaveEndDateTime->format('Y-m-d'));
        $holiday_stmt->execute();

        $holiday_data  = $holiday_stmt->fetch(PDO::FETCH_ASSOC);
        $holiday_count = $holiday_data['holiday_count'];

// คำนวณช่วงเวลาระหว่างเริ่มต้นและสิ้นสุด
        $interval = $leaveStartDateTime->diff($leaveEndDateTime);

// คำนวณจำนวนวันลา (หักวันหยุด)
        $leave_days = $interval->days - $holiday_count;

// ค่าเริ่มต้นสำหรับชั่วโมงและนาที
        $leave_hours   = $interval->h;
        $leave_minutes = $interval->i;

        $start_hour   = (int) $leaveStartDateTime->format('H');
        $start_minute = (int) $leaveStartDateTime->format('i');
        $end_hour     = (int) $leaveEndDateTime->format('H');

// กรณีมาสาย (l_leave_id = 7)
        if ($data['l_leave_id'] == 7) {
            // กรณีมาสายช่วง 08:01-08:30 ให้คิดเป็น 30 นาที (.5)
            if ($start_hour == 8 && $start_minute > 0 && $start_minute <= 30) {
                $leave_display = '0(0.5)';
            } else {
                // มาสายกรณีอื่นๆ ให้คำนวณตามปกติ

                // หักชั่วโมงกรณีข้ามช่วงพัก
                if (! ((($start_hour >= 8 && $start_hour < 12) && ($end_hour <= 12)) ||
                    (($start_hour >= 13 && $start_hour < 17) && ($end_hour <= 17)))) {
                    $leave_hours -= 1;
                }

                // คำนวณจำนวนวันเมื่อเกิน 8 ชั่วโมง
                if ($leave_hours >= 8) {
                    $leave_days += floor($leave_hours / 8);
                    $leave_hours = $leave_hours % 8;
                }

                // ปรับค่านาที
                if ($leave_minutes >= 30) {
                    $minutes_display = '.5';
                } else {
                    $minutes_display = '.0';
                }

                $hours_from_days = $leave_days * 8;
                $leave_display   = $hours_from_days . '(' . $leave_hours . $minutes_display . ')';
            }
        } else {
            // กรณีประเภทการลาอื่นๆ

            // หักชั่วโมงกรณีข้ามช่วงพัก
            if (! ((($start_hour >= 8 && $start_hour < 12) && ($end_hour <= 12)) ||
                (($start_hour >= 13 && $start_hour < 17) && ($end_hour <= 17)))) {
                $leave_hours -= 1;
            }

            // คำนวณจำนวนวันเมื่อเกิน 8 ชั่วโมง
            if ($leave_hours >= 8) {
                $leave_days += floor($leave_hours / 8);
                $leave_hours = $leave_hours % 8;
            }

            // ปรับค่านาที
            if ($leave_minutes >= 30) {
                $minutes_display = '.5';
            } else {
                $minutes_display = '.0';
            }

            $hours_from_days = $leave_days * 8;
            $leave_display   = $hours_from_days . '(' . $leave_hours . $minutes_display . ')';
        }

// เซ็ตค่าลงเซลล์ Excel
        $sheet->setCellValue('H' . $row, $leave_display);
        $sheet->setCellValue('I' . $row, $data['l_leave_reason'] ?? '');
        $sheet->setCellValue('J' . $row, $data['l_remark'] ?? '');

        $row++;
        $counter++; // เพิ่มลำดับขึ้นทีละ 1
    }

    // กำหนดเส้นขอบให้กับตารางทั้งหมด
    $tableEndRow = $row - 1;
    $sheet->getStyle('A' . $tableStartRow . ':J' . $tableEndRow)->getBorders()->getAllBorders()->setBorderStyle(
        \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
    ); // เปลี่ยนจาก I เป็น J

    // ตั้งค่าให้หัวข้อตารางแสดงทุกหน้า
    $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd($tableStartRow, $tableStartRow);

    // ตั้งค่าพื้นที่พิมพ์
    $sheet->getPageSetup()->setPrintArea('A1:J' . $tableEndRow);

    // ตั้งค่าการพิมพ์ให้แสดงเส้นกริด
    $sheet->setShowGridlines(true);
    $sheet->getPageSetup()->setFitToWidth(1);
    $sheet->getPageSetup()->setFitToHeight(0);

    // สร้างไฟล์ Excel
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

    // กำหนดชื่อไฟล์
    $filename = 'รายงานการลา_' . date('Ymd_His') . '.xlsx';

    // ส่งไฟล์ให้ดาวน์โหลด
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $writer->save('php://output');
    exit;
}

// ส่งออกไฟล์ตามรูปแบบที่เลือก
switch ($exportFormat) {
    case 'excel':
        exportToExcel($stmt, $includeHeader, $startDate, $endDate);
        break;
    default:
        // ส่งกลับเป็น JSON เพื่อให้ JS แสดง SweetAlert
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'รูปแบบไฟล์ไม่ถูกต้อง']);
        exit();
}