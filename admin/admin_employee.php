<?php
    session_start();
    date_default_timezone_set('Asia/Bangkok');

    include '../connect.php';
    if (! isset($_SESSION['s_usercode'])) {
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
    <title>ข้อมูลของพนักงาน</title>

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
                    <i class="fa-solid fa-users fa-2xl"></i>
                </div>
                <div class="col-auto">
                    <h3>ข้อมูลของพนักงาน</h3>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row">
            <div class="col-4">
                <label for="codeSearch">รหัสพนักงาน</label>
                <input type="text" class="form-control" id="codeSearch" list="codeList">
                <datalist id="codeList">
                    <?php
                        $sql    = "SELECT e_usercode FROM employees";
                        $result = $conn->query($sql);
                        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                            echo '<option value="' . $row['e_usercode'] . '">';
                        }
                    ?>
                </datalist>
            </div>
            <div class="col-4">
                <label for="nameLabel">ชื่อพนักงาน</label>
                <input type="text" class="form-control" id="nameSearch" list="nameList">
                <datalist id="nameList">
                    <?php
                        $sql    = "SELECT e_name FROM employees";
                        $result = $conn->query($sql);
                        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                            echo '<option value="' . $row['e_name'] . '">';
                        }
                    ?>
                </datalist>
            </div>
            <div class="col-4">
                <label for="depLabel">แผนก</label>
                <input type="text" class="form-control" id="depSearch" list="depList">
                <datalist id="depList">
                    <?php
                        $sql    = "SELECT e_department FROM employees";
                        $result = $conn->query($sql);
                        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                            echo '<option value="' . $row['e_department'] . '">';
                        }
                    ?>
                </datalist>
            </div>
        </div>
    </div>

    <div class="container-fluid mt-5">
        <table class="table table-hover table-bordered" style="border-top: 1px solid rgba(0, 0, 0, 0.1);" id="empTable">
            <thead>
                <tr class="text-center align-middle">
                    <th rowspan="2">ลำดับ</th>
                    <th rowspan="2">รหัสพนักงาน</th>
                    <th rowspan="2" style="width: 10%;">ชื่อ - นามสกุล</th>
                    <th rowspan="2">แผนก</th>
                    <th rowspan="2">อายุงาน</th>
                    <th rowspan="2">ระดับ</th>
                    <th rowspan="2">สถานที่ทำงาน</th>
                    <!-- <th rowspan="2" style="width: 10%;">อีเมล</th> -->
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

                    $sql       = "SELECT * FROM employees WHERE e_status <> 1 AND e_usercode <> '999999' ORDER BY e_add_datetime DESC";
                    $result    = $conn->query($sql);
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
                            echo '<td>' . $row['e_name'] . '</td>';
                            echo '<td>' . $row['e_department'] . '</td>';
                            echo '<td>' . $row['e_yearexp'] . '</td>';
                            echo '<td>' . $row['e_level'] . '</td>';
                            echo '<td>' . $row['e_workplace'] . '</td>';
                            // echo '<td>' . $row['e_email'] . '</td>';
                            echo '<td>' . $row['e_phone'] . '</td>';
                            echo '<td>' . $row['e_leave_personal'] . '</td>';
                            echo '<td>' . $row['e_leave_personal_no'] . '</td>';
                            echo '<td>' . $row['e_leave_sick'] . '</td>';
                            echo '<td>' . $row['e_leave_sick_work'] . '</td>';
                            echo '<td>' . $row['e_leave_annual'] . '</td>';
                            echo '<td>' . $row['e_other'] . '</td>';
                            echo '<td>' . $row['e_username'] . '</td>';
                            echo '<td>' . $row['e_password'] . '</td>';
                            echo '<td>';
                            echo '<button type="button" class="btn btn-warning edit-btn" data-bs-toggle="modal" data-bs-target="#empModal" data-usercode="' . $row['e_usercode'] . '">แก้ไข</button>';
                            echo '<button type="button" class="mx-2 btn btn-danger delete-btn" data-usercode="' . $row['e_usercode'] . '"><i class="fa-solid fa-trash"> ลบ</i></button>';
                            echo '</td>';
                            echo '</tr>';
                            $rowNumber--;
                        }
                    }
                ?>
            </tbody>
        </table>
        <?php
            echo '<div class="pagination">';
            echo '<ul class="pagination">';

            // สร้างลิงก์ไปยังหน้าแรกหรือหน้าก่อนหน้า
            if ($currentPage > 1) {
                echo '<li class="page-item"><a class="page-link" href="?page=1">&laquo;</a></li>';
                echo '<li class="page-item"><a class="page-link" href="?page=' . ($currentPage - 1) . '">&lt;</a></li>';
            }

            // สร้างลิงก์สำหรับแต่ละหน้า
            for ($i = 1; $i <= $totalPages; $i++) {
                if ($i == $currentPage) {
                    echo '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
                } else {
                    echo '<li class="page-item"><a class="page-link" href="?page=' . $i . '">' . $i . '</a></li>';
                }
            }

            // สร้างลิงก์ไปยังหน้าถัดไปหรือหน้าสุดท้าย
            if ($currentPage < $totalPages) {
                echo '<li class="page-item"><a class="page-link" href="?page=' . ($currentPage + 1) . '">&gt;</a></li>';
                echo '<li class="page-item"><a class="page-link" href="?page=' . $totalPages . '">&raquo;</a></li>';
            }

            echo '</ul>';
            echo '</div>';

        ?>
        <!-- Modal แก้ไข -->
        <div class="modal fade" id="empModal" tabindex="-1" aria-labelledby="empModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="empModalLabel">แก้ไขข้อมูลพนักงาน</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="empModalBody">
                        <!-- ที่นี่จะแสดงฟอร์มหรือข้อมูลของพนักงานที่ต้องการแก้ไข -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="button" class="btn btn-primary" id="saveChangesBtn">บันทึก</button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="mt-3 container-fluid">
        <!-- ปุ่มเพิ่มพนักงาน -->
        <div class="row d-flex justify-content-end">
            <div class="col-1">
                <button class="btn" id="addEmp" data-bs-toggle="modal" data-bs-target="#addEmployeeModal"><i
                        class="fa-solid fa-user-plus fa-2xl" style="color: #ffffff;"></i></button>
            </div>
        </div>
        <!-- Modal เพิ่มพนักงาน -->
        <div class="modal fade" id="addEmployeeModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
            aria-labelledby="addEmployeeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="addEmployeeModalLabel">เพิ่มข้อมูลพนักงาน</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addEmpForm">
                            <div class="row">
                                <h5>ข้อมูลการเข้าระบบ</h5>
                                <div class="col-6">
                                    <label for="codeLabel">รหัสพนักงาน</label>
                                    <span style="color: red;"> *</span>
                                    <input class="form-control" type="text" id="add_usercode" name="add_usercode"
                                        required oninvalid="this.setCustomValidity('กรุณากรอกรหัสพนักงาน')"
                                        oninput="this.setCustomValidity('')">

                                </div>
                                <div class="col-6">
                                    <label for="usernameLabel">ชื่อผู้ใช้</label>
                                    <span style="color: red;"> *</span>
                                    <input class="form-control" type="text" id="add_username" name="add_username"
                                        required oninvalid="this.setCustomValidity('กรุณากรอกชื่อผู้ใช้')"
                                        oninput="this.setCustomValidity('')">
                                </div>
                            </div>
                            <div class="mt-3 row">
                                <div class="col-6">
                                    <label for="passwordLabel">รหัสผ่าน</label>
                                    <span style="color: red;"> *</span>
                                    <input class="form-control" type="text" id="add_password" name="add_password"
                                        pattern="[0-9]{1,}" title="กรุณากรอกรหัสผ่านเป็นตัวเลขเท่านั้น" required
                                        oninvalid="this.setCustomValidity('กรุณากรอกรหัสผ่าน')"
                                        oninput="this.setCustomValidity('')">
                                </div>
                            </div>
                            <div class="mt-3 row">
                                <h5>ข้อมูลพนักงาน</h5>
                                <div class="col-6">
                                    <label for="nameLabel">ชื่อ - นามสกุล</label>
                                    <span style="color: red;"> *</span>
                                    <input class="form-control" type="text" id="add_name" name="add_name" required
                                        oninvalid="this.setCustomValidity('กรุณากรอกชื่อ - นามสกุล')"
                                        oninput="this.setCustomValidity('')">
                                </div>
                                <div class="col-3">
                                    <label for="levelLabel">ระดับ</label>
                                    <span style="color: red;"> *</span>
                                    <select class="form-control" id="add_level" style="border-radius: 20px;">
                                        <option value="select" selected>กรุณาเลือกระดับ</option>
                                        <?php
                                            $level_sql    = "SELECT * FROM level";
                                            $level_result = $conn->query($level_sql);
                                            if ($level_result->rowCount() > 0) {
                                                while ($level_row = $level_result->fetch()) {
                                                    echo '<option value="' . $level_row['l_id'] . '">' . $level_row['l_level'] . '</option>';
                                                }
                                            }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-3">
                                    <label for="departLabel">แผนก</label>
                                    <span style="color: red;"> *</span>
                                    <!-- <input class="form-control" list="departmentList" id="add_department"
                                        name="add_department" type="text"> -->
                                    <!-- <datalist id="departmentList">
                                       <?php
                                           // สร้างคำสั่ง SQL เพื่อดึงข้อมูล
                                           $sql  = "SELECT * FROM department WHERE d_department NOT IN ('Modeling', 'Design')";
                                           $stmt = $conn->prepare($sql);
                                           $stmt->execute();

                                           // ดึงผลลัพธ์และแสดงใน datalist
                                           echo '<datalist id="departmentList">';
                                           while ($row = $stmt->fetch()) {
                                               echo '<option value="' . htmlspecialchars($row['d_department'], ENT_QUOTES, 'UTF-8') . '">';
                                           }
                                           echo '</datalist>';
                                       ?>
                                    </datalist> -->
                                    <select class="form-control" id="add_department" style="border-radius: 20px;">
                                        <option value="select" selected>กรุณาเลือกแผนก</option>
                                        <?php
                                            $department_sql    = "SELECT * FROM department";
                                            $department_result = $conn->query($department_sql);
                                            if ($department_result->rowCount() > 0) {
                                                while ($department_row = $department_result->fetch(PDO::FETCH_ASSOC)) {
                                                    echo '<option value="' . $department_row['d_id'] . '">' . $department_row['d_department'] . '</option>';
                                                }
                                            }
                                        ?>
                                    </select>
                                </div>
                                <!-- <div class="col-3">
                                    <label for="yearexpLabel">อายุงาน</label>
                                    <span style="color: red;"> *</span>
                                    <input class="form-control" type="text" id="add_yearexp" name="add_yearexp"
                                        onchange="calculateLeaveDays()" required
                                        oninvalid="this.setCustomValidity('กรุณากรอกอายุงาน')"
                                        oninput="this.setCustomValidity('')">
                                </div> -->

                            </div>
                            <div class="mt-3 row">
                                <div class="col-3">
                                    <label for="subDepartLabel">แผนกย่อย_1</label>
                                    <span style="color: red;">*</span>
                                    <?php
                                        $sql  = "SELECT * FROM department";
                                        $stmt = $conn->prepare($sql);
                                        $stmt->execute();

                                        echo '<datalist id="subDepartList">';
                                        while ($row = $stmt->fetch()) {
                                            echo '<option value="' . $row['d_department'] . '">';
                                        }
                                        echo '</datalist>';
                                    ?>
                                    <select class="form-control" id="add_subdepart" style="border-radius: 20px;">
                                        <option value="select" selected>กรุณาเลือกแผนกย่อย</option>
                                        <?php
                                            $department_sql    = "SELECT * FROM department";
                                            $department_result = $conn->query($department_sql);
                                            if ($department_result->rowCount() > 0) {
                                                while ($department_row = $department_result->fetch(PDO::FETCH_ASSOC)) {
                                                    echo '<option value="' . $department_row['d_id'] . '">' . $department_row['d_department'] . '</option>';
                                                }
                                            }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-3">
                                    <label for="subDepart2Label">แผนกย่อย_2</label>
                                    <?php
                                        $sql  = "SELECT * FROM department";
                                        $stmt = $conn->prepare($sql);
                                        $stmt->execute();

                                        echo '<datalist id="subDepart2List">';
                                        while ($row = $stmt->fetch()) {
                                            echo '<option value="' . $row['d_department'] . '">';
                                        }
                                        echo '</datalist>';
                                    ?>
                                    <select class="form-control" id="add_subdepart2" style="border-radius: 20px;">
                                        <option value="select" selected>กรุณาเลือกแผนกย่อย 2</option>
                                        <?php
                                            $department_sql    = "SELECT * FROM department";
                                            $department_result = $conn->query($department_sql);
                                            if ($department_result->rowCount() > 0) {
                                                while ($department_row = $department_result->fetch(PDO::FETCH_ASSOC)) {
                                                    echo '<option value="' . $department_row['d_id'] . '">' . $department_row['d_department'] . '</option>';
                                                }
                                            }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-3">
                                    <label for="subDepart3Label">แผนกย่อย_3</label>
                                    <?php
                                        $sql  = "SELECT * FROM department";
                                        $stmt = $conn->prepare($sql);
                                        $stmt->execute();

                                        echo '<datalist id="subDepart3List">';
                                        while ($row = $stmt->fetch()) {
                                            echo '<option value="' . $row['d_department'] . '">';
                                        }
                                        echo '</datalist>';
                                    ?>
                                    <select class="form-control" id="add_subdepart3" style="border-radius: 20px;">
                                        <option value="select" selected>กรุณาเลือกแผนกย่อย 3</option>
                                        <?php
                                            $department_sql    = "SELECT * FROM department";
                                            $department_result = $conn->query($department_sql);
                                            if ($department_result->rowCount() > 0) {
                                                while ($department_row = $department_result->fetch(PDO::FETCH_ASSOC)) {
                                                    echo '<option value="' . $department_row['d_id'] . '">' . $department_row['d_department'] . '</option>';
                                                }
                                            }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-3">
                                    <label for="subDepart4Label">แผนกย่อย_4</label>
                                    <?php
                                        $sql  = "SELECT * FROM department";
                                        $stmt = $conn->prepare($sql);
                                        $stmt->execute();

                                        echo '<datalist id="subDepart4List">';
                                        while ($row = $stmt->fetch()) {
                                            echo '<option value="' . $row['d_department'] . '">';
                                        }
                                        echo '</datalist>';
                                    ?>
                                    <select class="form-control" id="add_subdepart4" style="border-radius: 20px;">
                                        <option value="select" selected>กรุณาเลือกแผนกย่อย 4</option>
                                        <?php
                                            $department_sql    = "SELECT * FROM department";
                                            $department_result = $conn->query($department_sql);
                                            if ($department_result->rowCount() > 0) {
                                                while ($department_row = $department_result->fetch(PDO::FETCH_ASSOC)) {
                                                    echo '<option value="' . $department_row['d_id'] . '">' . $department_row['d_department'] . '</option>';
                                                }
                                            }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="mt-3 row">
                                <div class="col-3">
                                    <label for="subDepart5Label">แผนกย่อย_5</label>
                                    <?php
                                        $sql  = "SELECT * FROM department";
                                        $stmt = $conn->prepare($sql);
                                        $stmt->execute();

                                        echo '<datalist id="subDepart3List">';
                                        while ($row = $stmt->fetch()) {
                                            echo '<option value="' . $row['d_department'] . '">';
                                        }
                                        echo '</datalist>';
                                    ?>
                                    <select class="form-control" id="add_subdepart5" style="border-radius: 20px;">
                                        <option value="select" selected>กรุณาเลือกแผนกย่อย 5</option>
                                        <?php
                                            $department_sql    = "SELECT * FROM department";
                                            $department_result = $conn->query($department_sql);
                                            if ($department_result->rowCount() > 0) {
                                                while ($department_row = $department_result->fetch(PDO::FETCH_ASSOC)) {
                                                    echo '<option value="' . $department_row['d_id'] . '">' . $department_row['d_department'] . '</option>';
                                                }
                                            }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-3">
                                    <label for="workplaceLabel">สถานที่ทำงาน</label>
                                    <span style="color: red;"> *</span>
                                    <select class="form-control" id="add_workplace" style="border-radius: 20px;">
                                        <option value="select" selected>กรุณาเลือกสถานที่ทำงาน</option>
                                        <?php
                                            $workplace_sql    = "SELECT * FROM workplace";
                                            $workplace_result = $conn->query($workplace_sql);
                                            if ($workplace_result->rowCount() > 0) {
                                                while ($workplace_row = $workplace_result->fetch()) {
                                                    echo '<option value="' . $workplace_row['w_id'] . '">' . $workplace_row['w_name'] . '</option>';
                                                }
                                            }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-3">
                                    <label for="workStartDateLabel">วันที่เริ่มงาน</label>
                                    <span style="color: red;"> *</span>
                                    <input class="form-control" type="text" id="add_work_start_date"
                                        name="add_work_start_date" onchange="calculateLeaveDays()">
                                </div>
                                <!-- <div class="col-3">
                                    <label for="emailLabel">อีเมล</label>
                                    <input class="form-control" type="text" id="add_email" name="add_email">
                                </div> -->
                                <div class="col-3">
                                    <label for="phoneLabel">เบอร์โทรศัพท์</label>
                                    <input class="form-control" type="text" id="add_phone" name="add_phone">
                                </div>
                            </div>
                            <div class="mt-3 row">

                                <div class="col-6">
                                    <label for="tokenLabel">Line token</label>
                                    <span style="color: red;">*</span>
                                    <input class="form-control" type="text" id="add_token" name="add_token">
                                </div>
                            </div>
                            <div class="mt-3 row">
                                <h5>จำนวนวันลาที่ได้รับ</h5>
                                <div class="col-3">
                                    <label for="personalLabel">ลากิจได้รับค่าจ้าง</label>
                                    <span style="color: red;"> *</span>
                                    <input class="form-control" type="text" id="add_personal" name="add_personal"
                                        required oninvalid="this.setCustomValidity('กรุณากรอกจำนวนวันที่ได้')"
                                        oninput="this.setCustomValidity('')">
                                </div>
                                <div class="col-3">
                                    <label for="personalNoLabel">ลากิจไม่ได้รับค่าจ้าง</label>
                                    <span style="color: red;"> *</span>
                                    <input class="form-control" type="text" id="add_personal_no" name="add_personal_no"
                                        required oninvalid="this.setCustomValidity('กรุณากรอกจำนวนวันที่ได้')"
                                        oninput="this.setCustomValidity('')">
                                </div>
                                <div class="col-3">
                                    <label for="sickLabel">ลาป่วย</label>
                                    <span style="color: red;"> *</span>
                                    <input class="form-control" type="text" id="add_sick" name="add_sick" required
                                        oninvalid="this.setCustomValidity('กรุณากรอกจำนวนวันที่ได้')"
                                        oninput="this.setCustomValidity('')">
                                </div>
                                <div class="col-3">
                                    <label for="sickWorkLabel">ลาป่วยจากงาน</label>
                                    <span style="color: red;"> *</span>
                                    <input class="form-control" type="text" id="add_sick_work" name="add_sick_work"
                                        required oninvalid="this.setCustomValidity('กรุณากรอกจำนวนวันที่ได้')"
                                        oninput="this.setCustomValidity('')">
                                </div>
                            </div>
                            <div class="mt-3 row">
                                <div class="col-3">
                                    <label for="annualLabel">ลาพักร้อน</label>
                                    <span style="color: red;"> *</span>
                                    <input class="form-control" type="text" id="add_annual" required
                                        oninvalid="this.setCustomValidity('กรุณากรอกจำนวนวันที่ได้')"
                                        oninput="this.setCustomValidity('')">
                                </div>
                                <div class="col-3">
                                    <label for="otherLabel">อื่น ๆ</label>
                                    <span style="color: red;"> *</span>
                                    <input class="form-control" type="text" id="add_other" required
                                        oninvalid="this.setCustomValidity('กรุณากรอกจำนวนวันที่ได้')"
                                        oninput="this.setCustomValidity('')">
                                </div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-danger mx-2" id="cancelBtn">ยกเลิก</button>
                                <button type="submit" class="btn btn-success">บันทึก</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
    $(document).ready(function() {

        $('#addEmpForm').on('submit', function(e) {
            e.preventDefault();

            var formData = {
                add_usercode: $("#add_usercode").val(),
                add_username: $("#add_username").val(),
                add_password: $("#add_password").val(),
                add_name: $("#add_name").val(),

                add_department: $("#add_department").val(),
                add_subdepart: $("#add_subdepart").val(),
                add_subdepart2: $("#add_subdepart2").val(),
                add_subdepart3: $("#add_subdepart3").val(),
                add_subdepart4: $("#add_subdepart4").val(),
                add_subdepart5: $("#add_subdepart5").val(),
                add_level: $("#add_level").val(),
                add_annual: $("#add_annual").val(),
                add_other: $("#add_other").val(),
                add_token: $("#add_token").val(),
                add_workplace: $("#add_workplace").val(),
                add_work_start_date: $("#add_work_start_date").val()
            };

            console.log(formData);

            $.ajax({
                url: 'a_ajax_add_employee.php',
                type: 'POST',
                data: formData,
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'บันทึกสำเร็จ',
                        text: response
                    }).then((result) => {
                        if (result.isConfirmed) {
                            location.reload();
                        }
                    });
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาด',
                        text: xhr.responseText
                    });
                }
            });
        });

        // ปุ่มแก้ไข
        $('.edit-btn').click(function() {
            var usercode = $(this).data('usercode');

            $.ajax({
                url: 'a_ajax_get_employee_data.php',
                method: 'POST',
                data: {
                    usercode: usercode
                },
                success: function(response) {
                    $('#empModalBody').html(response);
                    $('#empModal').modal('show');
                }
            });
        });
        $("#saveChangesBtn").click(function() {
            var updUsername = '<?php echo $userName; ?>';
            var usercode = $("#edit_usercode").val();
            var name = $("#edit_name").val();
            var department = $("#edit_department").val();
            var yearexp = $("#edit_yearexp").val();
            var level = $("#edit_level").val();
            var email = $("#edit_email").val();
            var id_line = $("#edit_id_line").val();
            var phone = $("#edit_phone").val();
            var username = $("#edit_username").val();
            var password = $("#edit_password").val();
            var personal = $("#edit_personal").val();
            var personalNo = $("#edit_personal_no").val();
            var sick = $("#edit_sick").val();
            var sickWork = $("#edit_sick_work").val();
            var annual = $("#edit_annual").val();
            var other = $("#edit_other").val();
            var workplace = $("#edit_workplace").val();

            var workplaceName = '';
            if (workplace == 1) {
                workplaceName = 'Korat';
            } else {
                workplaceName = 'Bang Phli';
            }
            alert(workplaceName)

            $.ajax({
                url: "a_ajax_upd_employee.php",
                type: "POST",
                data: {
                    usercode: usercode,
                    name: name,
                    department: department,
                    yearexp: yearexp,
                    level: level,
                    email: email,
                    id_line: id_line,
                    phone: phone,
                    username: username,
                    password: password,
                    updUsername: updUsername,
                    personal: personal,
                    personalNo: personalNo,
                    sick: sick,
                    sickWork: sickWork,
                    annual: annual,
                    other: other,
                    workplaceName: workplaceName
                },
                success: function(response) {
                    Swal.fire({
                        title: "แก้ไขข้อมูลสำเร็จ",
                        text: "",
                        icon: "success",
                        confirmButtonText: "ตกลง",
                    }).then((result) => {
                        if (result.isConfirmed) {
                            location.reload();
                        }
                    });
                }
            });
        });
        $('.delete-btn').click(function() {
            var usercode = $(this).data('usercode');
            if (confirm('คุณต้องการลบข้อมูลพนักงานนี้ใช่หรือไม่?')) {
                $.ajax({
                    type: 'POST',
                    url: 'a_ajax_delete_employee.php',
                    data: {
                        usercode: usercode
                    },
                    success: function(response) {
                        alert('ลบข้อมูลพนักงานสำเร็จ');
                        location.reload(); // Reload the page after successful deletion
                    },
                    error: function(xhr, status, error) {
                        alert('เกิดข้อผิดพลาดในการลบข้อมูล');
                        console.error(xhr.responseText);
                    }
                });
            }
        });
    });
    // ค้นหาชื่อ
    $("#nameSearch").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("tbody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });
    // ค้นหารหัสพนักงาน
    $("#codeSearch").on("keyup", function() {
        var value2 = $(this).val().toLowerCase();
        $("#empTable tbody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value2) > -1);
        });
    });

    flatpickr("#add_work_start_date", {
        dateFormat: "Y-m-d", // รูปแบบวันที่เป็น วัน-เดือน-ปี
        locale: "th", // กำหนดเป็นภาษาไทย (ต้องโหลด locale ไทยเพิ่ม)
    });

    // กรอกวันที่เริ่มงานให้จำนวนวันลาขึ้นเอง
    function calculateLeaveDays() {
        var yearsOfExperience = parseInt(document.getElementById("add_work_start_date").value);
        var personal = document.getElementById("add_personal");
        var personalNo = document.getElementById("add_personal_no");
        var sick = document.getElementById("add_sick");
        var sickWork = document.getElementById("add_sick_work");
        var annual = document.getElementById("add_annual");
        var other = document.getElementById("add_other");

        personal.value = "0";
        personalNo.value = "365";
        sick.value = "30";
        sickWork.value = "365";
        annual.value = "0";
        other.value = "365";

    }
    </script>
    <script src="../js/popper.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/bootstrap.bundle.js"></script>
    <script src="../js/bootstrap.bundle.min.js"></script>
</body>

</html>