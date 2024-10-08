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
        $level = $row['e_level'];
        $workplace = $row['e_workplace'];
        $subDepart = $row['e_sub_department'];
        $subDepart2 = $row['e_sub_department2'];
        $subDepart3 = $row['e_sub_department3'];
        $subDepart4 = $row['e_sub_department4'];
        $subDepart5 = $row['e_sub_department5'];
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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    <nav class="navbar navbar-expand-lg"
        style="background-color: #072ac8; box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.3); border: none;">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText"
                aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarText">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="gm_dashboard.php"
                            style="color: white;">หน้าหลัก</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                            aria-expanded="false" style="color: white;">
                            การลาและมาสาย
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="gm_leave.php">สถิติการลาและมาสาย</a></li>
                            <li><a class="dropdown-item" href="gm_history.php">ประวัติการลาและมาสาย</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                            aria-expanded="false" style="color: white;">
                            พนักงาน
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="gm_leave_request.php">ใบลาของพนักงาน</a></li>
                            <li><a class="dropdown-item" href="gm_employee_leave.php">การลาของพนักงาน</a>
                            </li>
                            <li><a class="dropdown-item" href="gm_employee_attendance.php">พนักงานมาสาย</a>
                            </li>
                        </ul>
                    </li>
                </ul>
                <form method="post">
                    <ul class="nav justify-content-end">
                        <?php if (!empty($userName)): ?>
                        <li class="nav-item">
                            <label class="mt-2 mx-2" style="color: white;"><?php echo $userName; ?></label>
                        </li>
                        <?php endif;?>
                        <li class="nav-item d-flex align-items-center">
                            <a href="#"><img src="../logo/th.png" alt="TH Language"
                                    style="width:30px;height:30px; margin: auto 0;"></a>
                        </li>
                        <li class="nav-item d-flex align-items-center">
                            <a href="#" class="ms-2"><img src="../logo/en.png" alt="EN Language"
                                    style="width:30px;height:30px; margin: auto 0;"></a>
                        </li>
                        <li class="nav-item">
                            <button type="submit" name="logoutButton"
                                class="form-control btn btn-dark">ออกจากระบบ</button>
                        </li>
                    </ul>
                </form>
            </div>
        </div>
    </nav>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/bootstrap.bundle.js"></script>
    <script src="../js/bootstrap.bundle.min.js"></script>
</body>

</html>