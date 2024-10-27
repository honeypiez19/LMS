<?php
include '../connect.php';

?>

<!DOCTYPE html>
<html lang="th">


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ข้อมูลพนักงาน</title>

    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <link rel="icon" href="../logo/logo.png">
    <link rel="stylesheet" href="../css/jquery-ui.css">
    <link rel="stylesheet" href="../css/flatpickr.min.css">

    <script src="../js/jquery-3.7.1.min.js"></script>
    <script src="../js/jquery-ui.min.js"></script>
    <script src="../js/flatpickr"></script>
    <script src="../js/sweetalert2.all.min.js"></script>

    <!-- <script src="https://kit.fontawesome.com/84c1327080.js" crossorigin="anonymous"></script> -->

    <script src="../js/fontawesome.js"></script>
</head>

<body>
    <div class="container mt-5">
        <div class="form-group">
            <label for="codeSearch">ค้นหารหัสพนักงาน</label>
            <input type="text" class="form-control" id="codeSearch" list="codeList">
            <datalist id="codeList">
                <?php
$sql = "SELECT e_usercode FROM employees";
$result = $conn->query($sql);
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    echo '<option value="' . $row['e_usercode'] . '">';
}
?>
            </datalist>
        </div>

        <table class="table table-hover table-bordered table-sm" style="border-top: 1px solid rgba(0, 0, 0, 0.1);"
            id="empTable">
            <thead>
                <tr class="text-center align-middle">
                    <th rowspan="2">ลำดับ</th>
                    <th rowspan="2">รหัสพนักงาน</th>
                    <th rowspan="2" style="width: 10%;">ชื่อ - นามสกุล</th>
                    <th rowspan="2">แผนก</th>
                    <th rowspan="2">อายุงาน</th>
                    <th rowspan="2">ระดับ</th>
                    <th rowspan="2" style="width: 10%;">อีเมล</th>
                    <th rowspan="2">เบอร์โทรศัพท์</th>
                    <th rowspan="1" colspan="6" class="table-secondary">ประเภทการลาและจำนวนวันที่ได้รับ</th>
                    <th rowspan="2">ชื่อผู้ใช้</th>
                    <th rowspan="2">รหัสผ่าน</th>
                    <th rowspan="2" style="width: 10%;"></th>
                </tr>
                <tr class="text-center align-middle">
                    <th style="background-color: #ff99c8; width: 5%;">ลากิจได้รับค่าจ้าง</th>
                    <th style="background-color: #fcf6bd; width: 5%;">ลากิจไม่ได้รับค่าจ้าง</th>
                    <th style="background-color: #d0f4de; width: 5%;">ลาป่วย</th>
                    <th style="background-color: #a9def9; width: 5%;">ลาป่วยจากงาน</th>
                    <th style="background-color: #e4c1f9; width: 5%;">ลาพักร้อน</th>
                    <th>อื่น ๆ (ระบุ)</th>
                </tr>
            </thead>
            <tbody>
                <?php
$itemsPerPage = 10;

// คำนวณหน้าปัจจุบัน
$currentPage = isset($_GET['page']) ? $_GET['page'] : 1;

$sql = "SELECT * FROM employees WHERE e_status <> 0 AND e_usercode <> '999999' ORDER BY e_add_datetime DESC";
$result = $conn->query($sql);
$totalRows = $result->rowCount();

// คำนวณหน้าทั้งหมด
$totalPages = ceil($totalRows / $itemsPerPage);

// คำนวณ offset สำหรับ pagination
$offset = ($currentPage - 1) * $itemsPerPage;

// เพิ่ม LIMIT และ OFFSET ในคำสั่ง SQL
$sql .= " LIMIT $itemsPerPage OFFSET $offset";

// ประมวลผลคำสั่ง SQL
$result = $conn->query($sql);

// แสดงผลลำดับของแถว
$rowNumber = $totalRows - ($currentPage - 1) * $itemsPerPage;

// แสดงข้อมูลในตาราง
if ($result->rowCount() > 0) {
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo '<tr class="text-center align-middle">';
        echo '<td>' . $rowNumber . '</td>';
        echo '<td>' . $row['e_usercode'] . '</td>';
        // Add other columns similarly
        echo '</tr>';
        $rowNumber--;
    }
}
?>
            </tbody>
        </table>
    </div>

    <script>
    $("#codeSearch").on("keyup", function() {
        var value2 = $(this).val().toLowerCase();
        $("#empTable tbody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value2) > -1);
        });
    });
    </script>
</body>

</html>