<?php

date_default_timezone_set('Asia/Bangkok'); // Set the timezone to Asia/Bangkok

include '../connect.php';

if (isset($_SESSION['s_usercode'])) {
    $userCode = $_SESSION['s_usercode'];
    $sql = "SELECT * FROM session
            JOIN employees ON session.s_usercode = employees.e_usercode
            WHERE session.s_usercode = :userCode";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':userCode', $userCode, PDO::PARAM_STR);
    $stmt->execute();
    $userName = "";
    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $userName = $row['e_username'];
        $name = $row['e_name'];
        $telPhone = $row['e_phone'];
        $depart = $row['e_department'];
        $workDate = $row['e_work_start_date'];
        $level = $row['e_level'];
        $workplace = $row['e_workplace'];
        $subDepart = $row['e_sub_department'];
        $subDepart2 = $row['e_sub_department2'];
        $subDepart3 = $row['e_sub_department3'];
        $subDepart4 = $row['e_sub_department4'];
        $subDepart5 = $row['e_sub_department5'];
        $yearExp = $row['e_yearexp'];
        $imageUser = !empty($row['e_image']) ? $row['e_image'] : "default_img.png";
    }
} else {
    $userName = "";
    $name = "";
    $telPhone = "";
    $depart = "";
    $level = "";
    $workplace = "";
    $subDepart = "";
    $subDepart2 = "";
    $subDepart3 = "";
    $subDepart4 = "";
    $subDepart5 = "";
}

// เมื่อมีการกดปุ่ม "ออกจากระบบ"
if (isset($_POST['logoutButton'])) {
    $userCode = $_SESSION['s_usercode'];
    $logoutTime = date('Y-m-d H:i:s');
    $statusLog = 0; // กำหนดสถานะของ log
    $sql = "UPDATE session SET s_logout_datetime = :logoutTime, s_log_status = :statusLog WHERE s_usercode = :userCode";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':logoutTime', $logoutTime, PDO::PARAM_STR);
    $stmt->bindParam(':statusLog', $statusLog, PDO::PARAM_INT);
    $stmt->bindParam(':userCode', $userCode, PDO::PARAM_STR);
    $stmt->execute();

    session_unset();
    session_destroy();

    header("Location: ../login.php");
    exit;
}

/* อัปโหลดรูปโปรไฟล์ */
if (isset($_FILES['profilePicture']) && $_FILES['profilePicture']['error'] == 0) {
    // กำหนด path ที่จะบันทึกไฟล์
    $uploadDir = '../img-profile/';
    $userCode = $_SESSION['s_usercode']; // ค่า userCode ที่ใช้เป็นชื่อไฟล์
    $fileName = $userCode . '.png'; // ตั้งชื่อไฟล์เป็น userCode.png
    $filePath = $uploadDir . $fileName;

    // ตรวจสอบว่าไฟล์เป็นรูปภาพ
    $fileType = mime_content_type($_FILES['profilePicture']['tmp_name']);
    if (strpos($fileType, 'image') === false) {
        echo "โปรดเลือกไฟล์รูปภาพเท่านั้น.";
        exit;
    }

    // ใช้ GD เพื่อปรับขนาดภาพ
    $maxWidth = 300; // กำหนดความกว้างสูงสุด
    $maxHeight = 300; // กำหนดความสูงสูงสุด

    // สร้าง resource ของภาพจากไฟล์ที่อัปโหลด
    $image = null;
    if ($fileType == 'image/jpeg' || $fileType == 'image/jpg') {
        $image = imagecreatefromjpeg($_FILES['profilePicture']['tmp_name']);
    } elseif ($fileType == 'image/png') {
        $image = imagecreatefrompng($_FILES['profilePicture']['tmp_name']);
    } elseif ($fileType == 'image/gif') {
        $image = imagecreatefromgif($_FILES['profilePicture']['tmp_name']);
    }

    if ($image) {
        // หาขนาดเดิมของภาพ
        $originalWidth = imagesx($image);
        $originalHeight = imagesy($image);

        // คำนวณอัตราส่วนของภาพ
        $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
        $newWidth = floor($originalWidth * $ratio);
        $newHeight = floor($originalHeight * $ratio);

        // สร้างภาพใหม่ที่มีขนาดเล็กลง
        $newImage = imagecreatetruecolor($newWidth, $newHeight);

        // คัดลอกภาพต้นฉบับไปยังภาพใหม่ที่ขนาดเล็กลง
        imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);

        // บันทึกไฟล์ภาพที่ลดขนาดแล้วลงในเซิร์ฟเวอร์
        // บันทึกเป็นไฟล์ PNG
        imagepng($newImage, $filePath, 7); // 7 คือค่าความคมชัดของ PNG (ค่าตั้งต้น: 0, สูงสุด: 9)

        // ทำความสะอาด resource ของภาพ
        imagedestroy($image);
        imagedestroy($newImage);

        // ต่อไปเป็นการอัพเดตชื่อไฟล์ในฐานข้อมูล
        $sql = "UPDATE employees SET e_image = :fileName WHERE e_usercode = :userCode";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bindValue(':fileName', $fileName, PDO::PARAM_STR);
            $stmt->bindValue(':userCode', $userCode, PDO::PARAM_INT);

            if ($stmt->execute()) {
                echo "<script>
                    alert('อัพโหลดรูปภาพสำเร็จ.');
                    location.href = 'user_dashboard.php';
                </script>";
            } else {
                //echo "เกิดข้อผิดพลาดในการบันทึกข้อมูลในฐานข้อมูล.";
            }
        }
    } else {
        //echo "ไม่สามารถเปิดไฟล์ภาพได้.";
    }
}
?>

<style>
.navbar-toggler {
    border-color: rgba(255, 255, 255, 0.5);
    /* เปลี่ยนสีขอบของปุ่ม */
}

.navbar-toggler-icon {
    background-image: url('data:image/svg+xml;charset=utf8,%3Csvg xmlns%3D"http://www.w3.org/2000/svg" viewBox%3D"0 0 30 30"%3E%3Cpath stroke%3D"rgba(255, 255, 255, 1)" stroke-width%3D"2" stroke-linecap%3D"round" stroke-miterlimit%3D"10" d%3D"M4 7h22M4 15h22M4 23h22"/%3E%3C/svg%3E');
}
</style>
<!-- edit by pim -->


<body>
    <nav class="navbar navbar-expand-lg" style="background-color: #072ac8; box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.3);
  border: none;">

        <div class="container-fluid">
            <!-- edit by pim -->
            <!-- Button for toggling the navbar -->
            <!-- ปุ่มกลับหน้าหลัก -->
            <!-- ปุ่ม Toggle -->
            <button class="navbar-toggler" type="button" id="navbar-toggler">
                <span class="navbar-toggler-icon"></span>
            </button>
            <form method="post">
                <ul class="nav d-lg-none d-xl-none d-xxl-none">
                    <?php if (!empty($userName)): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#" id="userDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <!-- เพิ่มรูปภาพในที่นี้ -->
                            <img src="../img-profile/<?php echo $imageUser; ?>" alt="Profile Picture"
                                class="rounded-circle" width="35" height="35">
                            <?php echo '[' . $depart . '] ' . $userName; ?>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal"
                                    data-bs-target="#changePasswordModal">เปลี่ยนรหัสผ่าน</a></li>
                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal"
                                    data-bs-target="#changePicModal">อัปโหลดรูปโปรไฟล์</a></li>
                        </ul>
                    </li>
                    <?php endif;?>
                </ul>
            </form>
            <!-- edit by pim -->
            <div class="collapse navbar-collapse" id="navbarText">
                <ul class="navbar-nav d-flex align-items-center me-auto mb-2 mb-lg-0 d-lg-none d-xl-none d-xxl-none">
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="user_dashboard.php"
                            style="color: white;">หน้าหลัก</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                            aria-expanded="false" style="color: white;">
                            การลาและการมาสาย
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="user_leave.php">สถิติการลาและการมาสาย</a></li>
                            <!-- <li><a class="dropdown-item" href="user_history.php">ประวัติการลาและการมาสาย</a></li> -->
                        </ul>
                    </li>
                </ul>

                <ul class="navbar-nav me-auto mb-2 mb-lg-0 d-none d-lg-flex d-xl-flex d-xxl-flex">
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="user_dashboard.php"
                            style="color: white;">หน้าหลัก</a>
                    </li>
                    <!-- <li class="nav-item">
                    <a class="nav-link" href="user_leave.php" style="color: white;">การลา</a>
                </li> -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                            aria-expanded="false" style="color: white;">
                            การลาและการมาสาย
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="user_leave.php">สถิติการลาและการมาสาย</a></li>
                            <!-- <li><a class="dropdown-item" href="user_history.php">ประวัติการลาและการมาสาย</a></li> -->
                        </ul>
                    </li>
                </ul>
                <form method="post" class="d-flex">
                    <ul class="navbar-nav ms-auto">
                        <?php if (!empty($userName)): ?>
                        <li class="nav-item dropdown d-none d-lg-flex d-xl-flex d-xxl-flex">
                            <a class="nav-link dropdown-toggle text-white" href="#" id="userDropdown" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <!-- เพิ่มรูปภาพในที่นี้ -->
                                <img src="../img-profile/<?php echo $imageUser; ?>" alt="Profile Picture"
                                    class="rounded-circle mx-2" width="35" height="35">
                                <?php echo '[' . $depart . '] ' . $userName; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal"
                                        data-bs-target="#changePasswordModal">เปลี่ยนรหัสผ่าน</a></li>
                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal"
                                        data-bs-target="#changePicModal">อัปโหลดรูปโปรไฟล์</a></li>
                            </ul>
                        </li>
                        <li class="nav-item me-4">
                            <a class="nav-link d-flex align-items-center text-white gap-2">
                                <button type="button" class="btn btn-sm btn-light" data-bs-toggle="tooltip"
                                    data-bs-placement="bottom"
                                    data-bs-title="<?php echo 'อายุงาน : <br>' . $yearExp ?>">
                                    <i class="fa-solid fa-briefcase fs-5"></i>
                                </button>
                                <span>
                                    <strong>
                                        <?php
$dateNow = date("Y-m-d"); // วันที่ปัจจุบัน

// สร้าง DateTime objects
$now = new DateTime($dateNow);
$work = new DateTime($workDate);

// คำนวณความแตกต่าง
$interval = $work->diff($now);

// แสดงผลลัพธ์
echo $interval->y . "Y " . $interval->m . "M";
?>
                                    </strong>
                                </span>
                            </a>
                        </li>
                        <?php endif;?>
                    </ul>
                    <!-- /* edit by pim */ -->
                    <ul class="nav">
                        <li class="nav-item d-flex align-items-center">
                            <a href="#"><img src="../logo/th.png" alt="TH Language"
                                    style="width:30px;height:30px; margin: auto 0;"></a>
                        </li>
                        <li class="nav-item d-flex align-items-center">
                            <a href="#" class="ms-2"><img src="../logo/en.png" alt="EN Language"
                                    style="width:30px;height:30px; margin: auto 0;"></a>
                        </li>
                        <li class="nav-item  d-flex align-items-center ms-3">
                            <button type="submit" name="logoutButton"
                                class="ms-2 form-control btn btn-dark">ออกจากระบบ</button>
                        </li>
                    </ul>
                </form>
                <!-- /* edit by pim */ -->
            </div>
        </div>
    </nav>
    <!-- Modal สำหรับเปลี่ยนรหัสผ่าน -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="changePasswordModalLabel">เปลี่ยนรหัสผ่าน</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="changePasswordForm">
                        <div class="mb-3">
                            <label for="newPassword" class="form-label">รหัสผ่านใหม่</label>
                            <input type="password" class="form-control" id="newPassword" name="newPassword" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirmNewPassword" class="form-label">ยืนยันรหัสผ่านใหม่</label>
                            <input type="password" class="form-control" id="confirmNewPassword"
                                name="confirmNewPassword" required>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">เปลี่ยนรหัสผ่าน</button>
                        </div>
                    </form>
                    <div id="changePasswordMessage" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
    <!-- <script src="../js/jquery.min.js"></script> -->
    <!-- /* edit by pim */ -->
    <!-- Modal สำหรับเปลี่ยนรูป -->
    <div class="modal fade" id="changePicModal" tabindex="-1" aria-labelledby="changePicModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="changePicModalLabel">อัปโหลดรูปภาพ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="changePicForm" enctype="multipart/form-data" method="POST">
                        <div class="mb-3">
                            <label for="profilePicture" class="form-label">เลือกรูปภาพ</label>
                            <input type="file" class="form-control" id="profilePicture" name="profilePicture"
                                accept="image/*" required>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">อัปโหลด</button>
                        </div>
                    </form>
                    <div id="uploadMessage" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
    <!-- /* edit by pim */ -->
    <script>
    const navbarToggler = document.getElementById('navbar-toggler');
    const navbarText = document.getElementById('navbarText');

    navbarToggler.addEventListener('click', () => {
        navbarText.classList.toggle('show');
    });

    $(document).ready(function() {
        $('#changePasswordForm').on('submit', function(e) {
            e.preventDefault();

            // รับข้อมูลจากฟอร์ม
            var formData = $(this).serialize();

            $.ajax({
                type: 'POST',
                url: 'u_change_password.php',
                data: formData,
                success: function(response) {
                    $('#changePasswordMessage').html(
                        response);
                    if (response == 'เปลี่ยนรหัสผ่านใหม่สำเร็จ') {
                        // $('#changePasswordModal').modal(
                        //     'hide');
                        // แสดง SweetAlert
                        Swal.fire({
                            title: 'สำเร็จ !',
                            text: 'เปลี่ยนรหัสผ่านสำเร็จ',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                location.reload();
                            }
                        });
                    }
                },
                error: function() {
                    $('#changePasswordMessage').html(
                        '<div class="alert alert-danger">เกิดข้อผิดพลาดในการเปลี่ยนรหัสผ่าน</div>'
                    );
                }
            });
        });
    });
    </script>

    <!-- <script src="../js/popper.min.js"></script> -->
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/bootstrap.bundle.js"></script>
    <script src="../js/bootstrap.bundle.min.js"></script>
</body>