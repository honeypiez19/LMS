<?php
session_start();
date_default_timezone_set('Asia/Bangkok');

include '../connect.php';
if (!isset($_SESSION['s_usercode'])) {
    header('Location: ../login.php');
    exit();
}

$userCode = $_SESSION['s_usercode'];
// echo $userCode;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ประวัติการลา</title>

    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <link rel="icon" href="../logo/logo.png">
    <link rel="stylesheet" href="../css/jquery-ui.css">
    <link rel="stylesheet" href="../css/flatpickr.min.css">

    <script src="../js/jquery-3.7.1.min.js"></script>
    <script src="../js/jquery-ui.min.js"></script>
    <script src="../js/flatpickr"></script>
    <script src="../js/sweetalert2.all.min.js"></script>

    <script src="../js/fontawesome.js"></script>
</head>

<body>
    <?php include 'admin_navbar.php'?>
    <nav class="navbar bg-body-tertiary" style="background-color: #072ac8; box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.3);
  border: none;">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-auto">
                    <i class="fa-solid fa-clock-rotate-left fa-2xl"></i>
                </div>
                <div class="col-auto">
                    <h3>ประวัติการลาและการมาสาย</h3>
                </div>
            </div>
        </div>
    </nav>
    <div class="container">
        <form class="mt-3 mb-3 row" method="post">
            <label for="" class="mt-2 col-auto">เลือกปี</label>
            <div class="col-auto">
                <?php
$selectedYear = date('Y'); // ปีปัจจุบัน
if (isset($_POST['year'])) {
    $selectedYear = $_POST['year'];
}
echo "<select class='form-select' name='year' id='selectYear'>";
for ($i = 0; $i <= 2; $i++) {
    $year = date('Y') - $i;
    echo "<option value='$year'" . ($year == $selectedYear ? " selected" : "") . ">$year</option>";
}
echo "</select>";
?>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary button-shadow">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </div>
        </form>

        <table class="mt-3 table table-hover table-bordered" style="border-top: 1px solid rgba(0, 0, 0, 0.1);"
            id="leaveTable">
            <thead class="table table-secondary">
                <tr class="text-center align-middle">
                    <th rowspan="2">ประเภทรายการ</th>
                    <th colspan="12">เดือน</th>
                    <th rowspan="2"></th>
                </tr>
                <tr class="text-center align-middle">
                    <td><b>ม.ค.</b></td>
                    <td><b>ก.พ.</b></td>
                    <td><b>มี.ค.</b></td>
                    <td><b>เม.ย.</b></td>
                    <td><b>พ.ค.</b></td>
                    <td><b>มิ.ย.</b></td>
                    <td><b>ก.ค.</b></td>
                    <td><b>ส.ค.</b></td>
                    <td><b>ก.ย.</b></td>
                    <td><b>ต.ค.</b></td>
                    <td><b>พ.ย.</b></td>
                    <td><b>ธ.ค.</b></td>
                </tr>
            </thead>
            <tbody>
                <?php
// Prepare the main query for employee
$sql = "SELECT * FROM employees WHERE e_usercode = :userCode";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':userCode', $userCode);
$stmt->execute();

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $leave_types = [
        1 => 'ลากิจได้รับค่าจ้าง',
        2 => 'ลากิจไม่ได้รับค่าจ้าง',
        3 => 'ลาป่วย',
        4 => 'ลาป่วยจากงาน',
        5 => 'ลาพักร้อน',
        7 => 'มาสาย',
        6 => 'หยุดงาน',
        8 => 'อื่น ๆ'
        ,

    ];

    foreach ($leave_types as $leave_id => $leave_name) {
        echo '<tr class="text-center align-middle">';
        echo '<td>' . $leave_name . '</td>';

        for ($i = 1; $i <= 12; $i++) {
            $sql_count = "SELECT COUNT(l_list_id) AS leave_count FROM leave_list WHERE l_leave_id = :leave_id AND YEAR(l_leave_start_date) = :selectedYear AND MONTH(l_leave_start_date) = :month";
            $stmt_count = $conn->prepare($sql_count);
            $stmt_count->bindParam(':leave_id', $leave_id);
            $stmt_count->bindParam(':selectedYear', $selectedYear);
            $stmt_count->bindParam(':month', $i);
            $stmt_count->execute();

            $row_count = $stmt_count->fetch(PDO::FETCH_ASSOC);
            echo '<td>' . $row_count['leave_count'] . '</td>';
        }

        echo '<td><button type="button" class="btn btn-primary view-button"><i class="fa-solid fa-magnifying-glass"></i></button></td>';
        echo '</tr>';
    }
}
?>
            </tbody>
        </table>
        <!-- Modal -->
        <div class="modal fade" id="leaveDetailsModal" tabindex="-1" aria-labelledby="leaveDetailsModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="leaveDetailsModalLabel">ประวัติทั้งหมด</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                    </div>
                </div>
            </div>
        </div>

    </div>
    <script>
    $('.view-button').click(function() {
        var row = $(this).closest('tr');
        var leaveType = row.find('td:first').text();

        $.ajax({
            url: 'a_u_ajax_get_detail.php',
            method: 'POST',
            data: {
                leaveType: leaveType
            },
            success: function(response) {
                $('#leaveDetailsModal .modal-body').html(response);
                $('#leaveDetailsModal').modal('show');
            },
            error: function(xhr, status, error) {
                alert('เกิดข้อผิดพลาด: ' + error);
            }
        });
    });
    </script>
    <script src="../js/popper.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/bootstrap.bundle.js"></script>
    <script src="../js/bootstrap.bundle.min.js"></script>
</body>

</html>