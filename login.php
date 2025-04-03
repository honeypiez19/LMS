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
        background: hsla(217, 100%, 50%, 1);

        background: linear-gradient(90deg, hsla(217, 100%, 50%, 1) 0%, hsla(186, 100%, 69%, 1) 100%);

        background: -moz-linear-gradient(90deg, hsla(217, 100%, 50%, 1) 0%, hsla(186, 100%, 69%, 1) 100%);

        background: -webkit-linear-gradient(90deg, hsla(217, 100%, 50%, 1) 0%, hsla(186, 100%, 69%, 1) 100%);

        filter: progid: DXImageTransform.Microsoft.gradient(startColorstr="#0061FF", endColorstr="#60EFFF", GradientType=1);
    }

    .login-form {
        background: #fff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1), 0 4px 6px rgba(0, 0, 0, 0.1);
        max-width: 400px;
        width: 100%;
        border: 1px solid #ddd;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
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

    .btn {
        flex: 1 1 auto;

        text-align: center;
        text-transform: uppercase;
        transition: 0.5s;
        background-size: 200% auto;
        color: white;
        /* text-shadow: 0px 0px 10px rgba(0,0,0,0.2);*/
        box-shadow: 0 0 20px #eee;
        border-radius: 10px;
    }

    /* Demo Stuff End -> */

    /* <- Magic Stuff Start */

    .btn:hover {
        background-position: right center;
        color: white;

        /* change the direction of the change here */
    }

    .btn-1 {
        background-image: linear-gradient(to right, #5e9efe 0%, #0d6efd 51%, #aecffe 100%);
    }

    .snowflake {
        user-select: none;
        pointer-events: none;
        z-index: 9999;
        color: white;
        font-weight: bold;
        font-family: "Arial", sans-serif;
        white-space: nowrap;
    }

    .back-to-dashboard {
        margin-top: 20px;
    }

    .back-to-dashboard button {
        width: 100%;
    }
    </style>
</head>

<body>
    <div class="login-form">
        <h2 class="text-center">เข้าสู่ระบบ</h2>
        <form id="loginForm">
            <div class="mb-3">
                <label for="usercode" class="form-label">รหัสพนักงาน</label>
                <input type="text" class="form-control" id="usercode" name="usercode" placeholder="Usercode" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">รหัสผ่าน</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Password"
                    required>
            </div>
            <button type="submit" class="mt-3 btn btn-1 ">เข้าสู่ระบบ</button>
        </form>

        <div class="mt-3 text-center">
            <a href="#" id="forgotPasswordLink" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">ลืมรหัสผ่าน
                ?</a>
        </div>

        <div class="back-to-dashboard text-center">
            <!-- OR Text -->
            <div class="or-text" style="margin-bottom: 20px; color: #555;">-------------------- OR --------------------
            </div>

            <!-- Back to dashboard Button -->
            <button type="button" class="btn btn-1"
                onclick="window.location.href='https://system-samt.com/dashboard-program/';">Back to dashboard</button>
        </div>

    </div>

    <!-- Modal ลืมรหัสผ่าน -->
    <div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="forgotPasswordModalLabel">รีเซ็ตรหัสผ่านใหม่</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h4 class="text-danger">โปรดติดต่อ HR</h4>
                    <!-- <form id="forgotPasswordForm">
                        <div class="mb-3">
                            <label for="usercode" class="form-label">รหัสพนักงาน</label>
                            <input type="text" class="form-control" id="userCode" name="userCode"
                                placeholder="รหัสพนักงาน" required>
                        </div>
                        <div class="mb-3">
                            <label for="newPassword" class="form-label">รหัสผ่านใหม่</label>
                            <input type="password" class="form-control" id="newPassword" name="newPassword"
                                placeholder="รหัสผ่านใหม่" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirmNewPassword" class="form-label">ยืนยันรหัสผ่านใหม่</label>
                            <input type="password" class="form-control" id="confirmNewPassword"
                                name="confirmNewPassword" placeholder="ยืนยันรหัสผ่านใหม่" required>
                        </div>
                    </form> -->
                </div>
                <div class="modal-footer">
                    <!-- <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"></button> -->
                    <button type="button" class="btn btn-primary">ตกลง</button>
                    <!-- <button type="button" class="btn btn-primary" id="submitNewPassword">ตกลง</button> -->

                </div>
            </div>
        </div>
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
                    } else if (response == "leader" || response == "subLeader") {
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
                    } else if (response == "manager" || response == "manager2" ||
                        response == "assisManager") {

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
        // เปิด Modal เมื่อคลิกที่ลิงก์ "ลืมรหัสผ่าน"
        $('#forgotPasswordLink').on('click', function(e) {
            e.preventDefault(); // ป้องกันการทำงานของลิงก์
            $('#forgotPasswordModal').modal('show'); // แสดง Modal
        });

        // จัดการส่งฟอร์มกรอกรหัสผ่านใหม่
        $('#submitNewPassword').on('click', function() {
            var formData = $('#forgotPasswordForm').serialize();

            $.ajax({
                type: 'POST',
                url: 'change_password.php', // ใส่ชื่อไฟล์ PHP ที่จัดการการเปลี่ยนรหัสผ่าน
                data: formData,
                success: function(response) {
                    // $('#forgotPasswordModal').modal('hide'); // ปิด Modal
                    $('#message').html(response); // แสดงข้อความตอบกลับ
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
                                location.reload(); // รีโหลดหน้าหลังจากกด OK
                            }
                        });
                    }
                },
                error: function() {
                    $('#message').html(
                        '<div class="alert alert-danger">เกิดข้อผิดพลาดในการเปลี่ยนรหัสผ่าน</div>'
                    );
                }
            });
        });
    });
    document.addEventListener("DOMContentLoaded", function() {
        const currentDate = new Date();
        const currentYear = currentDate.getFullYear();
        const startSnowfallDate = new Date(currentYear, 10, 1); // 1 พฤศจิกายน ปีปัจจุบัน
        const endSnowfallDate = new Date(currentYear + 1, 0, 6); // 6 มกราคม ปีถัดไป

        // ตรวจสอบว่าปัจจุบันอยู่ในช่วงที่กำหนดหรือไม่
        if (currentDate >= startSnowfallDate || currentDate <= endSnowfallDate) {
            const maxFlakes = 100; // เพิ่มจำนวนหิมะเป็น 100
            const flakes = [];
            const snowflakeCharacters = ['♫', '○', '♡', 'ꕤ', '×'];

            setInterval(() => {
                if (flakes.length < maxFlakes) {
                    createSnowflake();
                }
            }, 300); // ปรับให้หิมะเกิดช้าลง

            function createSnowflake() {
                const snowflake = document.createElement("div");
                snowflake.className = "snowflake";

                // เลือกตัวอักษรหิมะแบบสุ่ม
                const randomChar = snowflakeCharacters[Math.floor(Math.random() * snowflakeCharacters.length)];
                snowflake.innerHTML = randomChar;

                document.body.appendChild(snowflake);
                flakes.push(snowflake);

                const startPos = Math.random() * window.innerWidth;
                const startOpacity = Math.random();
                const duration = Math.random() * 5 + 8; // ปรับเวลาให้หิมะตกช้าลง (เพิ่มระยะเวลา)
                const size = Math.random() * 20 + 10; // ขนาดหิมะแบบสุ่ม

                snowflake.style.fontSize = `${size}px`;
                snowflake.style.opacity = startOpacity;
                snowflake.style.position = "fixed"; // ตั้งให้หิมะอยู่ที่ตำแหน่งคงที่
                snowflake.style.top = "0";
                snowflake.style.left = `${startPos}px`;

                // เลือกทิศทางการหมุนแบบสุ่ม
                const rotationDirection = Math.random() > 0.5 ? 1 : -
                    1; // 1 หมุนตามเข็มนาฬิกา, -1 หมุนทวนเข็มนาฬิกา

                snowflake.animate(
                    [{
                            transform: `translate(0, 0) rotate(0deg)`
                        },
                        {
                            transform: `translate(0, 100vh) rotate(${rotationDirection * 360}deg)`
                        }
                    ], {
                        duration: duration * 1000, // เพิ่มระยะเวลาให้หิมะตกช้าลง
                        easing: "linear",
                        iterations: Infinity
                    }
                );
            }
        }
    });
    </script>
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/bootstrap.bundle.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
</body>

</html>