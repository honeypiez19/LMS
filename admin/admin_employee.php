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
                        $sql    = "SELECT Distinct e_department FROM employees";
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
                    if (! isset($_GET['items'])) {
                        $itemsPerPage = 10;
                    } else {
                        $itemsPerPage = (int) $_GET['items'];
                        // ตรวจสอบว่าค่าที่รับมาตรงกับตัวเลือกที่กำหนดไว้
                        if (! in_array($itemsPerPage, [10, 25, 50, 100])) {
                            $itemsPerPage = 10; // ถ้าไม่ตรงให้ใช้ค่าเริ่มต้น
                        }
                    }

                    // รับค่าหน้าปัจจุบันจาก URL parameter
                    if (! isset($_GET['page'])) {
                        $currentPage = 1;
                    } else {
                        $currentPage = (int) $_GET['page'];
                        if ($currentPage < 1) {
                            $currentPage = 1;
                        }
                    }

                    $sql = "SELECT * FROM employees WHERE e_status <> 1
    AND e_workplace = :workplace
    ORDER BY e_usercode DESC";

                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':workplace', $workplace);

                    $stmt->execute();
                    $totalRows = $stmt->rowCount();

                    // Calculate total pages
                    $totalPages = ceil($totalRows / $itemsPerPage);
                    if ($totalPages < 1) {
                        $totalPages = 1;
                    }
                    // กรณีไม่มีข้อมูล ให้มี 1 หน้า

                    // ตรวจสอบว่าหน้าปัจจุบันไม่เกินจำนวนหน้าทั้งหมด
                    if ($currentPage > $totalPages) {
                        $currentPage = $totalPages;
                    }

                    // Calculate offset for pagination
                    $offset = ($currentPage - 1) * $itemsPerPage;

                    // Add LIMIT and OFFSET to the SQL statement for pagination
                    $sqlWithPagination = $sql . " LIMIT :limit OFFSET :offset";

                    // Prepare the final query with pagination
                    $stmt = $conn->prepare($sqlWithPagination);
                    $stmt->bindParam(':workplace', $workplace);

                    $stmt->bindParam(':limit', $itemsPerPage, PDO::PARAM_INT);
                    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

                    // Execute the paginated query
                    $stmt->execute();

                    // Display row number starting from the correct count
                    $rowNumber = $totalRows - ($currentPage - 1) * $itemsPerPage;

                    // แสดงข้อมูลในตาราง
                    if ($stmt->rowCount() > 0) {
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo '<tr class="text-center align-middle">';
                            echo '<td>' . $rowNumber . '</td>';
                            echo '<td>' . $row['e_usercode'] . '</td>';
                            echo '<td>' . $row['e_name'] . '</td>';
                            echo '<td>' . $row['e_department'] . '</td>';
                            echo '<td>' . $row['e_yearexp'] . '</td>';
                            echo '<td>' . $row['e_level'] . '</td>';
                            echo '<td>' . $row['e_workplace'] . '</td>';
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
    <!-- Pagination -->
    <div class="container-fluid mt-3">
        <div class="row align-items-center">
            <!-- ปุ่มเลื่อนหน้าด้านซ้าย พร้อมตัวเลือกจำนวนรายการต่อหน้า -->
            <div class="col-md-6">
                <div class="d-flex align-items-center">
                    <nav aria-label="Page navigation" class="me-3">
                        <ul class="pagination mb-0">
                            <?php if ($currentPage > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=1&items=<?php echo $itemsPerPage; ?>"
                                    aria-label="First">
                                    <span aria-hidden="true">&laquo;&laquo;</span>
                                </a>
                            </li>
                            <li class="page-item">
                                <a class="page-link"
                                    href="?page=<?php echo $currentPage - 1; ?>&items=<?php echo $itemsPerPage; ?>"
                                    aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            <?php endif; ?>

                            <?php
                                                  // แสดงปุ่มทีละ 5 ปุ่ม
                                $pagesToShow = 5; // จำนวนปุ่มที่ต้องการแสดง

                                // คำนวณหน้าเริ่มต้นและหน้าสุดท้ายที่จะแสดง
                                if ($currentPage >= 5) {
                                    // กรณีที่หน้าปัจจุบันมากกว่าหรือเท่ากับ 5 ให้แสดงแบบย้อนกลับ
                                    $startPage = min($currentPage + 2, $totalPages);
                                    $endPage   = max($startPage - 4, 1);

                                    // แสดงปุ่มแบบย้อนกลับ จากมากไปน้อย
                                    for ($i = $startPage; $i >= $endPage; $i--):
                                        $activeClass = ($i == $currentPage) ? ' active' : '';
                                    ?>
                            <li class="page-item<?php echo $activeClass; ?>">
                                <a class="page-link"
                                    href="?page=<?php echo $i; ?>&items=<?php echo $itemsPerPage; ?>"><?php echo $i; ?></a>
                            </li>
                            <?php endfor;
                                    } else {
                                        // กรณีหน้าปัจจุบันน้อยกว่า 5 ให้แสดงแบบปกติ
                                        $endPage = min($pagesToShow, $totalPages);

                                        // แสดงปุ่มแบบปกติ จากน้อยไปมาก
                                        for ($i = 1; $i <= $endPage; $i++):
                                            $activeClass = ($i == $currentPage) ? ' active' : '';
                                        ?>
                            <li class="page-item<?php echo $activeClass; ?>">
                                <a class="page-link"
                                    href="?page=<?php echo $i; ?>&items=<?php echo $itemsPerPage; ?>"><?php echo $i; ?></a>
                            </li>
                            <?php endfor;
                                    }
                                ?>

                            <?php if ($currentPage < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link"
                                    href="?page=<?php echo $currentPage + 1; ?>&items=<?php echo $itemsPerPage; ?>"
                                    aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                            <li class="page-item">
                                <a class="page-link"
                                    href="?page=<?php echo $totalPages; ?>&items=<?php echo $itemsPerPage; ?>"
                                    aria-label="Last">
                                    <span aria-hidden="true">&raquo;&raquo;</span>
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </nav>

                    <!-- ฟอร์มสำหรับกระโดดไปยังหน้าที่ต้องการ -->
                    <div class="d-flex align-items-center me-3">
                        <form action="" method="GET" class="d-flex flex-wrap align-items-center"
                            onsubmit="return validateJumpToPage()">
                            <input type="hidden" name="items" value="<?php echo $itemsPerPage; ?>">
                            <label for="jumpToPage" class="me-2">ไปที่หน้า:</label>
                            <div class="d-flex flex-grow-1">
                                <input type="number" id="jumpToPage" name="page" class="form-control form-control-md"
                                    min="1" max="<?php echo $totalPages; ?>" style="width: 70px;">
                                <button type="submit" class="btn btn-sm btn-primary ms-2">ไป</button>
                            </div>
                        </form>
                    </div>

                    <!-- แสดงรายการต่อหน้าอยู่ถัดจากปุ่มเลข -->
                    <div class="d-flex align-items-center">
                        <label for="perPage" class="me-2">จำนวนรายการ :</label>
                        <select id="perPage" class="form-select form-select-md" style="width: 70px;"
                            onchange="changeItemsPerPage(this.value)">
                            <option value="10" <?php echo $itemsPerPage == 10 ? 'selected' : ''; ?>>10
                            </option>
                            <option value="25" <?php echo $itemsPerPage == 25 ? 'selected' : ''; ?>>25
                            </option>
                            <option value="50" <?php echo $itemsPerPage == 50 ? 'selected' : ''; ?>>50
                            </option>
                            <option value="100" <?php echo $itemsPerPage == 100 ? 'selected' : ''; ?>>100
                            </option>
                        </select>
                        <span class="ms-2">รายการต่อหน้า</span>
                    </div>
                </div>
            </div>

            <!-- ข้อความแสดงรายการอยู่ด้านขวา -->
            <div class="col-md-6 text-end">
                <div class="pagination-info">
                    <?php if ($totalRows > 0): ?>
                    แสดงรายการที่&nbsp;<?php echo($currentPage - 1) * $itemsPerPage + 1; ?>&nbsp;-&nbsp;<?php echo min($currentPage * $itemsPerPage, $totalRows); ?>&nbsp;จากทั้งหมด&nbsp;<?php echo $totalRows; ?>&nbsp;รายการ
                    <?php else: ?>
                    ไม่พบรายการ
                    <?php endif; ?>
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
                                    <select class="form-control" id="add_level" style="border-radius: 20px;" required
                                        oninvalid="this.setCustomValidity('กรุณาเลือกระดับ')"
                                        oninput="this.setCustomValidity('')">
                                        <option value="" selected disabled>กรุณาเลือกระดับ</option>
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
                                    <label for="departLabel">แผนกหลัก</label>
                                    <span style="color: red;"> *</span>
                                    <select class="form-control" id="add_department" style="border-radius: 20px;"
                                        required oninvalid="this.setCustomValidity('กรุณาเลือกแผนกหลัก')"
                                        oninput="this.setCustomValidity('')">
                                        <option value="" selected disabled>กรุณาเลือกแผนกหลัก</option>
                                        <?php
                                            $department_sql    = "SELECT * FROM department WHERE d_department NOT IN ('Modeling', 'Design', 'AC', 'Sales', 'Store')";
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
                                    <select class="form-control" id="add_subdepart" style="border-radius: 20px;"
                                        required oninvalid="this.setCustomValidity('กรุณาเลือกแผนกย่อย')"
                                        oninput="this.setCustomValidity('')">
                                        <option value="" selected disabled>กรุณาเลือกแผนกย่อย</option>
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
                                    <select class="form-control" id="add_workplace" style="border-radius: 20px;"
                                        required oninvalid="this.setCustomValidity('กรุณาเลือกสถานที่ทำงาน')"
                                        oninput="this.setCustomValidity('')">
                                        <option value="" selected disabled>กรุณาเลือกสถานที่ทำงาน</option>
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
                                        name="add_work_start_date" onchange="calculateLeaveDays()" required
                                        oninvalid="this.setCustomValidity('กรุณากรอกวันที่เริ่มงาน')"
                                        oninput="this.setCustomValidity('')">

                                </div>
                                <!-- <div class="col-3">
                                    <label for="emailLabel">อีเมล</label>
                                    <input class="form-control" type="text" id="add_email" name="add_email">
                                </div> -->
                                <div class="col-3">
                                    <label for="phoneLabel">เบอร์โทรศัพท์</label>
                                    <span style="color: red;">Ex. XXX-XXXXXXX</span>
                                    <input class="form-control" type="text" id="add_phone" name="add_phone">
                                </div>
                            </div>
                            <div class="mt-3 row">

                                <div class="col-6">
                                    <label for="tokenLabel">User ID (แจ้งเตือนไลน์)</label>
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
            var addUsername = '<?php echo $userName; ?>';

            var formData = {
                addUsername: addUsername,
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
                add_personal: $("#add_personal").val(),
                add_personal_no: $("#add_personal_no").val(),
                add_sick: $("#add_sick").val(),
                add_sick_work: $("#add_sick_work").val(),
                add_annual: $("#add_annual").val(),
                add_other: $("#add_other").val(),
                add_token: $("#add_token").val(),
                add_workplace: $("#add_workplace").val(),
                add_work_start_date: $("#add_work_start_date").val(),
                add_phone: $("#add_phone").val()
            };

            console.log(formData);

            $.ajax({
                url: 'a_ajax_add_employee.php',
                type: 'POST',
                data: formData,
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'บันทึกสำเร็จ'
                        // text: response
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
            var level = $("#edit_level").val();
            var email = $("#edit_email").val();
            var token = $("#edit_line").val();
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
            var subDepart = $("#edit_subdepart").val();
            var subDepart2 = $("#edit_subdepart2").val();
            var subDepart3 = $("#edit_subdepart3").val();
            var subDepart4 = $("#edit_subdepart4").val();
            var subDepart5 = $("#edit_subdepart5").val();
            var workStartDate = $("#edit_work_start_date").val();

            $.ajax({
                url: "a_ajax_upd_employee.php",
                type: "POST",
                data: {
                    usercode: usercode,
                    name: name,
                    department: department,
                    level: level,
                    email: email,
                    token: token,
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
                    workplace: workplace,
                    subDepart: subDepart,
                    subDepart2: subDepart2,
                    subDepart3: subDepart3,
                    subDepart4: subDepart4,
                    subDepart5: subDepart5,
                    workStartDate: workStartDate
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
    // ฟังก์ชันค้นหาแบบรวม
    function filterTable() {
        var nameValue = $("#nameSearch").val().toLowerCase();
        var codeValue = $("#codeSearch").val().toLowerCase();
        var depValue = $("#depSearch").val().toLowerCase();

        $("#empTable tbody tr").each(function() {
            var codeCell = $(this).find("td:eq(1)").text().toLowerCase(); // คอลัมน์รหัสพนักงาน
            var nameCell = $(this).find("td:eq(2)").text().toLowerCase(); // คอลัมน์ชื่อพนักงาน
            var depCell = $(this).find("td:eq(3)").text().toLowerCase(); // คอลัมน์แผนก

            var nameMatch = nameValue === "" || nameCell.indexOf(nameValue) > -1;
            var codeMatch = codeValue === "" || codeCell.indexOf(codeValue) > -1;
            var depMatch = depValue === "" || depCell.indexOf(depValue) > -1;

            // แสดงแถวเฉพาะที่ตรงกับทุกเงื่อนไขที่กำหนด
            $(this).toggle(nameMatch && codeMatch && depMatch);
        });
    }

    // ทำการค้นหาเมื่อมีการพิมพ์ในช่องค้นหาใดๆ
    $("#nameSearch").on("keyup", filterTable);
    $("#codeSearch").on("keyup", filterTable);
    $("#depSearch").on("keyup", filterTable);

    var today = new Date();
    flatpickr("#add_work_start_date", {
        dateFormat: "d-m-Y",
        defaultDate: today,
        onChange: function(selectedDates, dateStr, instance) {
            calculateLeaveDays();
        }
    });

    function calculateLeaveDays() {
        var workStartDate = new Date(document.getElementById("add_work_start_date").value);

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

        var currentDate = new Date(); // วันที่ปัจจุบัน
        console.log("Current Date:", currentDate); // แสดงค่าวันที่ปัจจุบัน

        console.log("Work Start Date:", workStartDate); // แสดงวันที่เริ่มงาน

        var yearsOfExperience = currentDate.getFullYear() - workStartDate.getFullYear();
        console.log("Years of Experience:", yearsOfExperience); // แสดงจำนวนปีที่ทำงาน

        if (yearsOfExperience >= 1 && yearsOfExperience < 2) {
            personal.value = "5";
            annual.value = "6"; // ครบ 1 ปี แต่ไม่ถึง 2 ปี
        } else if (yearsOfExperience >= 2 && yearsOfExperience < 3) {
            personal.value = "5";
            annual.value = "7"; // ครบ 2 ปี แต่ไม่ถึง 3 ปี
        } else if (yearsOfExperience >= 3 && yearsOfExperience < 4) {
            personal.value = "5";
            annual.value = "8"; // ครบ 3 ปี แต่ไม่ถึง 4 ปี
        } else if (yearsOfExperience >= 4 && yearsOfExperience < 5) {
            personal.value = "5";
            annual.value = "9"; // ครบ 4 ปี แต่ไม่ถึง 5 ปี
        } else if (yearsOfExperience >= 5) {
            personal.value = "5";
            annual.value = "10"; // ครบ 5 ปีขึ้นไป
        } else {
            personal.value = "0";
            annual.value = "0";
        }
    }

    window.onload = function() {
        calculateLeaveDays();
    }

    function changeItemsPerPage(items) {
        let url = new URL(window.location.href);
        url.searchParams.set('page', 1); // กลับไปหน้าแรกเมื่อเปลี่ยนจำนวนรายการ
        url.searchParams.set('items', items);

        // ไปยัง URL ใหม่
        window.location.href = url.toString();
    }

    function validateJumpToPage() {
        const pageInput = document.getElementById('jumpToPage');
        if (!pageInput.value) {
            Swal.fire({
                title: 'แจ้งเตือน',
                text: 'กรุณากรอกเลขหน้าที่ต้องการ',
                icon: 'warning',
                confirmButtonText: 'ตกลง'
            });
            return false;
        }
        return true;
    }
    </script>
    <script src="../js/popper.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/bootstrap.bundle.js"></script>
    <script src="../js/bootstrap.bundle.min.js"></script>
</body>

</html>