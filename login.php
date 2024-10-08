<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ</title>

    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link rel="icon" href="logo/logo.png">
    <link rel="stylesheet" href="css/jquery-ui.css">
    <link rel="stylesheet" href="css/flatpickr.min.css">

    <script src="js/jquery-3.7.1.min.js"></script>
    <script src="js/jquery-ui.min.js"></script>
    <script src="js/flatpickr"></script>
    <script src="js/sweetalert2.all.min.js"></script>
    <script src="js/fontawesome.js"></script>

    <style>
    body {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        background-color: #f8f9fa;
    }

    .login-form {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        max-width: 400px;
        width: 100%;
    }

    .login-form h2 {
        margin-bottom: 20px;
    }

    .login-form .form-control {
        margin-bottom: 15px;
    }

    .login-form button {
        width: 100%;
    }
    </style>
</head>

<body>
    <div class="login-form">
        <h2 class="text-center">เข้าสู่ระบบ</h2>
        <form id="loginForm">
            <div class="mb-3">
                <label for="usercode" class="form-label">Usercode</label>
                <input type="text" class="form-control" id="usercode" name="usercode" placeholder="Usercode" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Password"
                    required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
        <div id="message" class="mt-3"></div>
    </div>

    <script>
    $(document).ready(function() {
        $("#loginForm").submit(function(e) {
            e.preventDefault(); // หยุดการ submit form ปกติ
            var userCode = $("#usercode").val(); // รับค่า usercode จากฟอร์ม
            var passWord = $("#password").val(); // รับค่า password จากฟอร์ม
            // ทำการส่งข้อมูลด้วย AJAX
            $.ajax({
                type: "POST",
                url: "ajax_login.php", // ไฟล์ PHP ที่ใช้ในการตรวจสอบ login
                data: {
                    userCode: userCode,
                    passWord: passWord
                },
                success: function(response) {
                    // แสดงข้อความตามสถานะที่ได้รับ
                    if (response == "admin") {
                        // Swal.fire({
                        //     title: "Welcome admin",
                        //     text: "Redirecting to admin dashboard...",
                        //     icon: "success"
                        // }).then(() => {
                        //     window.location.href = "admin/admin_dashboard.php";
                        // });
                        let timerInterval;

                        Swal.fire({
                            title: "Welcome admin",
                            html: "Redirecting to admin dashboard...",
                            timer: 500,
                            timerProgressBar: true,
                            didOpen: () => {
                                Swal.showLoading();
                                const timer = Swal.getHtmlContainer()
                                    .querySelector("b");
                                timerInterval = setInterval(() => {
                                    timer.textContent = Swal
                                        .getTimerLeft();
                                }, 100);
                            },
                            willClose: () => {
                                clearInterval(timerInterval);
                            }
                        }).then((result) => {
                            if (result.dismiss === Swal.DismissReason.timer) {
                                window.location.href = "admin/admin_dashboard.php";
                            }
                        });
                    } else if (response == "user") {
                        // Swal.fire({
                        //     title: "Welcome user",
                        //     text: "Redirecting to user dashboard...",
                        //     icon: "success"
                        // }).then(() => {
                        //     window.location.href = "user/user_dashboard.php";
                        // });
                        Swal.fire({
                            title: "Welcome user",
                            html: "Redirecting to user dashboard...",
                            timer: 500,
                            timerProgressBar: true,
                            didOpen: () => {
                                Swal.showLoading();
                                const timer = Swal.getHtmlContainer()
                                    .querySelector("b");
                                timerInterval = setInterval(() => {
                                    timer.textContent = Swal
                                        .getTimerLeft();
                                }, 100);
                            },
                            willClose: () => {
                                clearInterval(timerInterval);
                            }
                        }).then((result) => {
                            if (result.dismiss === Swal.DismissReason.timer) {
                                window.location.href = "user/user_dashboard.php";
                            }
                        });
                    } else if (response == "chief") {
                        // Swal.fire({
                        //     title: "Welcome chief",
                        //     text: "Redirecting to chief dashboard...",
                        //     icon: "success"
                        // }).then(() => {
                        //     window.location.href = "chief/chief_dashboard.php";
                        // });
                        Swal.fire({
                            title: "Welcome chief",
                            html: "Redirecting to chief dashboard...",
                            timer: 500,
                            timerProgressBar: true,
                            didOpen: () => {
                                Swal.showLoading();
                                const timer = Swal.getHtmlContainer()
                                    .querySelector("b");
                                timerInterval = setInterval(() => {
                                    timer.textContent = Swal
                                        .getTimerLeft();
                                }, 100);
                            },
                            willClose: () => {
                                clearInterval(timerInterval);
                            }
                        }).then((result) => {
                            if (result.dismiss === Swal.DismissReason.timer) {
                                window.location.href = "chief/chief_dashboard.php";
                            }
                        });
                    } else if (response == "leader") {
                        // Swal.fire({
                        //     title: "Welcome chief",
                        //     text: "Redirecting to chief dashboard...",
                        //     icon: "success"
                        // }).then(() => {
                        //     window.location.href = "chief/chief_dashboard.php";
                        // });
                        Swal.fire({
                            title: "Welcome leader",
                            html: "Redirecting to leader dashboard...",
                            timer: 500,
                            timerProgressBar: true,
                            didOpen: () => {
                                Swal.showLoading();
                                const timer = Swal.getHtmlContainer()
                                    .querySelector("b");
                                timerInterval = setInterval(() => {
                                    timer.textContent = Swal
                                        .getTimerLeft();
                                }, 100);
                            },
                            willClose: () => {
                                clearInterval(timerInterval);
                            }
                        }).then((result) => {
                            if (result.dismiss === Swal.DismissReason.timer) {
                                window.location.href =
                                    "leader/leader_dashboard.php";
                            }
                        });
                    } else if (response == "manager" || response == "manager2") {

                        Swal.fire({
                            title: "Welcome manager",
                            html: "Redirecting to manager dashboard...",
                            timer: 500,
                            timerProgressBar: true,
                            didOpen: () => {
                                Swal.showLoading();
                                const timer = Swal.getHtmlContainer()
                                    .querySelector("b");
                                timerInterval = setInterval(() => {
                                    timer.textContent = Swal
                                        .getTimerLeft();
                                }, 100);
                            },
                            willClose: () => {
                                clearInterval(timerInterval);
                            }
                        }).then((result) => {
                            if (result.dismiss === Swal.DismissReason.timer) {
                                window.location.href =
                                    "manager/manager_dashboard.php";
                            }
                        });
                    } else if (response == "GM") {

                        Swal.fire({
                            title: "Welcome GM",
                            html: "Redirecting to GM dashboard...",
                            timer: 500,
                            timerProgressBar: true,
                            didOpen: () => {
                                Swal.showLoading();
                                const timer = Swal.getHtmlContainer()
                                    .querySelector("b");
                                timerInterval = setInterval(() => {
                                    timer.textContent = Swal
                                        .getTimerLeft();
                                }, 100);
                            },
                            willClose: () => {
                                clearInterval(timerInterval);
                            }
                        }).then((result) => {
                            if (result.dismiss === Swal.DismissReason.timer) {
                                window.location.href =
                                    "GM/gm_dashboard.php";
                            }
                        });
                    } else if (response == "already_logged_in") {
                        alert('มีการเข้าสู่ระบบอยู่แล้ว')
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Invalid Usercode or Password',
                            text: 'Please try again.'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while processing your request. Please try again later.'
                    });
                }
            });
        });
    });
    </script>
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/bootstrap.bundle.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
</body>

</html>
