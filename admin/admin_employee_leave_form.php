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
    <title>ใบลาย้อนหลังของพนักงาน</title>

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
    <?php require 'admin_navbar.php'?>
    <nav class="navbar bg-body-tertiary" style="background-color: #072ac8; box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.3);
  border: none;">
        <div class=" container-fluid">
            <div class="row align-items-center">
                <div class="col-auto">
                    <i class="fa-solid fa-paste fa-2xl"></i>
                </div>
                <div class="col-auto">
                    <h3>ใบลาย้อนหลังของพนักงาน</h3>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <button type="button" class="button-shadow btn btn-primary mt-3" data-bs-toggle="modal"
                data-bs-target="#leaveModal" style="width: 100px;">
                ยื่นใบลาย้อนหลัง
            </button>
        </div>
        <!-- Modal ยื่นใบลา -->
        <div class="modal fade" id="leaveModal" tabindex="-1" aria-labelledby="leaveModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="leaveModalLabel">รายละเอียดการลา</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="leaveForm" method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-6">
                                    <label for="employeeCode" class="form-label">รหัสพนักงาน</label>
                                    <input type="text" class="form-control" id="codeSearch" name="userCode"
                                        list="codeList" required>
                                    <datalist id="codeList">
                                        <?php
        $sql = "SELECT * FROM employees WHERE e_level <> 'admin' AND e_status <> 1";
        $result = $conn->query($sql);
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            echo '<option value="' . $row['e_usercode'] . '" data-name="' . $row['e_name'] . '" data-username="' . $row['e_username'] . 
            '" data-depart="' . $row['e_department'] . '" data-level="' . $row['e_level'] . '" data-telPhone="' . $row['e_phone'] . '" data-sub-department="' . $row['e_sub_department'] . '" data-sub-department2="' . $row['e_sub_department2'] . '" data-sub-department3="' . $row['e_sub_department3'] . '" data-sub-department4="' . $row['e_sub_department4'] . '" data-sub-department5="' . $row['e_sub_department5'] . '" data-workplace="' . $row['e_workplace'] . '"></option>';
        }
        ?>
                                    </datalist>
                                    <input type="text" class="form-control" id="userName" name="userName" hidden>
                                    <input type="text" class="form-control" id="department" name="department" hidden>
                                    <input type="text" class="form-control" id="level" name="level" hidden>
                                    <!-- <input type="text" class="form-control" id="telPhone" name="telPhone"> -->
                                    <input type="text" class="form-control" id="reason" name="reason" value="มาสาย"
                                        hidden>
                                    <input type="text" class="form-control" id="workplace" name="workplace" hidden>
                                    <input type="text" class="form-control" id="subDepart" name="subDepart" hidden>
                                    <!-- Correct subDepart -->
                                    <input type="text" class="form-control" id="subDepart2" name="subDepart2" hidden>
                                    <!-- Correct subDepart2 -->
                                    <input type="text" class="form-control" id="subDepart3" name="subDepart3" hidden>
                                    <!-- Correct subDepart3 -->
                                    <input type="text" class="form-control" id="subDepart4" name="subDepart4" hidden>
                                    <!-- Correct subDepart4 -->
                                    <input type="text" class="form-control" id="subDepart5" name="subDepart5" hidden>
                                </div>
                                <div class="col-6">
                                    <label for="empName" class="form-label">ชื่อพนักงาน</label>
                                    <input type="text" class="form-control" id="name" name="name" disabled>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-24 alert alert-danger d-none" role="alert" name="alertCheckDays">
                                    ไม่สามารถลาได้ คุณได้ใช้สิทธิ์ครบกำหนดแล้ว
                                </div>
                                <div class="mt-3 col-12">
                                    <label for="leaveType" class="form-label">ประเภทการลา</label>
                                    <span class="badge rounded-pill text-bg-info" name="totalDays">เหลือ - วัน</span>
                                    <span style="color: red;">*</span>
                                    <select class="form-select" id="leaveType" required
                                        onchange="checkDays(this.value)">
                                        <option selected>เลือกประเภทการลา</option>
                                        <option value="1">ลากิจได้รับค่าจ้าง</option>
                                        <option value="2">ลากิจไม่ได้รับค่าจ้าง</option>
                                        <option value="3">ลาป่วย</option>
                                        <option value="4">ลาป่วยจากงาน</option>
                                        <option value="5">ลาพักร้อน</option>
                                        <option value="8">อื่น ๆ</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mt-3 row">
                                <div class="col-12">
                                    <label for="leaveReason" class="form-label">เหตุผลการลา</label>
                                    <span style="color: red;">*</span>
                                    <textarea class="form-control mt-2" id="leaveReason" rows="3"
                                        placeholder="กรุณาระบุเหตุผล"></textarea>
                                </div>
                            </div>
                            <div class="mt-3 row">
                                <div class="col-6">
                                    <label for="startDate" class="form-label">วันที่เริ่มต้น</label>
                                    <span style="color: red;">*</span>
                                    <input type="text" class="form-control" id="startDate" required>
                                </div>
                                <div class=" col-6">
                                    <label for="startTime" class="form-label">เวลาที่เริ่มต้น</label>
                                    <span style="color: red;">*</span>
                                    <select class="form-select" id="startTime" name="startTime" required>
                                        <option value="08:00" selected>08:00</option>
                                        <option value="08:30">08:30</option>
                                        <option value="09:00">09:00</option>
                                        <option value="09:30">09:30</option>
                                        <option value="10:00">10:00</option>
                                        <option value="10:30">10:30</option>
                                        <option value="11:00">11:00</option>
                                        <!-- <option value="11:30">11:30</option> -->
                                        <option value="12:00">11:45</option>
                                        <option value="13:00">12:45</option>
                                        <!-- <option value="13:00">13:00</option> -->
                                        <option value="13:30">13:30</option>
                                        <option value="14:00">14:00</option>
                                        <option value="14:30">14:30</option>
                                        <option value="15:00">15:00</option>
                                        <option value="15:30">15:30</option>
                                        <option value="16:00">16:00</option>
                                        <!-- <option value="16:30">16:30</option> -->
                                        <option value="17:00">16:40</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mt-3 row">
                                <div class="col-6">
                                    <label for="endDate" class="form-label">วันที่สิ้นสุด</label>
                                    <span style="color: red;">*</span>
                                    <input type="text" class="form-control" id="endDate" required>
                                </div>
                                <div class="col-6">
                                    <label for="endTime" class="form-label">เวลาที่สิ้นสุด</label>
                                    <span style="color: red;">*</span>
                                    <select class="form-select" id="endTime" name="endTime" required>
                                        <option value="08:00">08:00</option>
                                        <option value="08:30">08:30</option>
                                        <option value="09:00">09:00</option>
                                        <option value="09:30">09:30</option>
                                        <option value="10:00">10:00</option>
                                        <option value="10:30">10:30</option>
                                        <option value="11:00">11:00</option>
                                        <!-- <option value="11:30">11:30</option> -->
                                        <option value="12:00">11:45</option>
                                        <option value="13:00">12:45</option>
                                        <!-- <option value="13:00">13:00</option> -->
                                        <option value="13:30">13:30</option>
                                        <option value="14:00">14:00</option>
                                        <option value="14:30">14:30</option>
                                        <option value="15:00">15:00</option>
                                        <option value="15:30">15:30</option>
                                        <option value="16:00">16:00</option>
                                        <!-- <option value="16:30">16:30</option> -->
                                        <option value="17:00" selected>16:40</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mt-3 row">
                                <div class="col-12">
                                    <label for="telPhone" class="form-label">เบอร์โทร</label>
                                    <input type="text" class="form-control" id="telPhone" name="telPhone" disabled>
                                </div>
                            </div>
                            <div class="mt-3 row">
                                <div class="col-12">
                                    <label for="file" class="form-label">ไฟล์แนบ (PNG , JPG, JPEG)</label>
                                    <input class="form-control" type="file" id="file" name="file" />
                                </div>
                            </div>

                            <div class="mt-3 d-flex justify-content-end">
                                <button type="submit" class="btn btn-success" id="btnSubmitForm1" name="submit"
                                    style="white-space: nowrap;">บันทึก</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table>

            </table>
        </div>
    </div>
    <script>
    document.getElementById('codeSearch').addEventListener('input', function() {
        var selectedCode = this.value;
        var nameField = document.getElementById('name');
        var telPhoneField = document.getElementById('telPhone');

        if (selectedCode === "") {
            // เคลียร์ฟิลด์เบอร์โทรเมื่อรหัสพนักงานว่างเปล่า
            nameField.value = "";
            telPhoneField.value = "";
            return;
        }

        var dataList = document.getElementById('codeList').getElementsByTagName('option');
        for (var i = 0; i < dataList.length; i++) {
            if (dataList[i].value === selectedCode) {
                nameField.value = dataList[i].getAttribute('data-name'); // ตั้งค่าเบอร์โทรที่ถูกต้อง
                telPhoneField.value = dataList[i].getAttribute('data-telPhone'); // ตั้งค่าเบอร์โทรที่ถูกต้อง
                break;
            }
        }
    });


    $(document).ready(function() {
        $.ajax({
            url: 'a_u_ajax_get_holiday.php', // สร้างไฟล์ PHP เพื่อตรวจสอบวันหยุด
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                var today = new Date(); // วันที่ปัจจุบัน

                // สร้างปฏิทิน Flatpickr พร้อมปิดวันที่เป็นวันหยุด และไม่สามารถเลือกวันที่ก่อนหน้าวันที่ปัจจุบันได้
                flatpickr("#startDate", {
                    dateFormat: "d-m-Y", // ตั้งค่าเป็น วัน/เดือน/ปี
                    defaultDate: today, // กำหนดวันที่เริ่มต้นเป็นวันที่ปัจจุบัน
                    // minDate: today, // ห้ามเลือกวันที่ในอดีต
                    disable: response.holidays // ปิดวันที่ที่เป็นวันหยุด
                });

                flatpickr("#endDate", {
                    dateFormat: "d-m-Y", // ตั้งค่าเป็น วัน/เดือน/ปี
                    defaultDate: today, // กำหนดวันที่สิ้นสุดเป็นวันที่ปัจจุบัน
                    // minDate: today, // ห้ามเลือกวันที่ในอดีต
                    disable: response.holidays // ปิดวันที่ที่เป็นวันหยุด
                });

                flatpickr("#urgentStartDate", {
                    dateFormat: "d-m-Y", // ตั้งค่าเป็น วัน/เดือน/ปี
                    defaultDate: today, // กำหนดวันที่เริ่มต้นเป็นวันที่ปัจจุบัน
                    // minDate: today, // ห้ามเลือกวันที่ในอดีต
                    disable: response.holidays // ปิดวันที่ที่เป็นวันหยุด
                });

                flatpickr("#urgentEndDate", {
                    dateFormat: "d-m-Y", // ตั้งค่าเป็น วัน/เดือน/ปี
                    defaultDate: today, // กำหนดวันที่สิ้นสุดเป็นวันที่ปัจจุบัน
                    // minDate: today, // ห้ามเลือกวันที่ในอดีต
                    disable: response.holidays // ปิดวันที่ที่เป็นวันหยุด
                });
            }
        });

        $('#leaveForm').on('submit', function(e) {
            e.preventDefault(); // ป้องกันการส่งฟอร์มแบบปกติ

            // เก็บข้อมูลฟอร์มทั้งหมดรวมถึงไฟล์ที่แนบ
            var formData = new FormData(this);

            $.ajax({
                url: 'a_ajax_add_emp_leave.php', // ไฟล์ PHP ที่จะใช้บันทึกข้อมูล
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    // ตรวจสอบการตอบสนองจากเซิร์ฟเวอร์
                    if (response.success) {
                        alert('บันทึกข้อมูลสำเร็จ');
                        $('#leaveModal').modal('hide'); // ปิด modal
                        $('#leaveForm')[0].reset(); // รีเซ็ตฟอร์ม
                    } else {
                        alert('เกิดข้อผิดพลาด: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                }
            });
        });
    });
    </script>
    <script src="../js/popper.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/bootstrap.bundle.js"></script>
    <script src="../js/bootstrap.bundle.min.js"></script>
</body>

</html>