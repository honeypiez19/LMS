<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../fullcalendar/fullcalendar.min.css" />

    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js'></script>
    <script src="../js/jquery-3.7.1.min.js"></script>
    <script src="../js/sweetalert2.all.min.js"></script>
    <script src="../js/fontawesome.js"></script>

    <style>
    body {
        font-family: Arial, Helvetica, sans-serif;
    }

    form button {
        margin-right: 20px;
    }

    #calendar {
        width: 100%;
        max-width: 1000px;
        /* ปรับความกว้างสูงสุดของปฏิทิน */
        margin: 0 auto;
    }

    #uploadForm {
        padding: 12px;
        /* ระยะห่างภายในฟอร์ม */
        border-radius: 10px;
        /* ทำให้มุมของฟอร์มโค้ง */
        border: 2px solid #ddd;
        /* เส้นขอบของฟอร์ม */
        margin-right: 20px;
        margin-left: 20px;

    }

    #uploadForm button {
        background-color: #198754;
        /* สีพื้นหลังเป็นเขียว */
        color: white;
        /* สีข้อความในปุ่มเป็นสีขาว */
        border: none;
        /* ไม่ต้องการเส้นขอบ */
        padding: 10px 20px;
        /* ระยะห่างภายในปุ่ม */
        border-radius: 20px;
        /* ทำมุมโค้งมน */
        cursor: pointer;
        /* เปลี่ยนเคอร์เซอร์เมื่อชี้ไปที่ปุ่ม */
        transition: background-color 0.3s ease, transform 0.3s ease;
        /* เพิ่มการเปลี่ยนสีเมื่อ hover */
        margin-right: 20px;

    }

    /* เปลี่ยนสีพื้นหลังเมื่อ hover */
    #uploadForm button:hover {
        background-color: #167549;
        /* สีพื้นหลังเป็นเขียวเข้มเมื่อ hover */
        transform: scale(1.05);
        /* ขยายขนาดเล็กน้อยเมื่อ hover */
    }

    /* เปลี่ยนสีพื้นหลังเมื่อปุ่มถูกคลิก */
    #uploadForm button:active {
        background-color: #167549;
        /* สีพื้นหลังเมื่อคลิก */
    }

    .container {
        display: flex;
        /* justify-content: space-between; */
        /* จัดให้ทั้งสองอิลิเมนต์อยู่ข้างๆ กัน */
        align-items: center;
        /* จัดให้อยู่ในแนวเดียวกัน */
        margin: 20px 0;
        /* กำหนดระยะห่าง */
    }

    .back-button-container {
        flex-shrink: 0;
        text-align: left;
        margin-left: 20px;
    }

    .back-button {
        display: inline-block;
        padding: 10px 20px;
        background-color: #007bff;
        color: white;
        text-decoration: none;
        border-radius: 20px;
        transition: background-color 0.3s ease, transform 0.3s ease;

    }

    .back-button:hover {
        background-color: #0056b3;
        transform: scale(1.05);

    }

    .fc-daygrid-day.fc-day-sat {
        background-color: #E6E6FA;
        color: black;
    }

    .fc-daygrid-day.fc-day-sun {
        background-color: #FFE4E1;
        color: black;
    }

    .fc-button {
        min-width: 80px !important;
        height: 40px !important;
    }

    /* Media queries สำหรับหน้าจอขนาดเล็ก */
    @media screen and (max-width: 1200px) {
        #calendar {
            width: 90%;
        }
    }

    @media screen and (max-width: 768px) {
        body {
            font-size: 12px;
            /* ปรับขนาดฟอนต์ */
        }

        .back-button {
            padding: 8px 16px;
            font-size: 12px;
        }

        #calendar {
            width: 100%;
            margin: 0 10px;
        }

        .fc-toolbar {
            flex-wrap: wrap;
        }

        .fc-toolbar .fc-left,
        .fc-toolbar .fc-right {
            flex: 100%;
            text-align: center;
        }

        .fc-toolbar .fc-center {
            margin-top: 10px;
        }
    }

    @media screen and (max-width: 480px) {
        body {
            font-size: 10px;
            /* ปรับขนาดฟอนต์ */
        }

        .back-button {
            padding: 6px 12px;
            font-size: 10px;
        }

        #calendar {
            width: 100%;
            margin: 0;
        }

        .fc-toolbar {
            flex-direction: column;
        }

        .fc-toolbar .fc-left,
        .fc-toolbar .fc-right,
        .fc-toolbar .fc-center {
            flex: 100%;
            text-align: center;
            margin-bottom: 10px;
        }
    }
    </style>
</head>

<body>
    <div class="container">
        <div class="back-button-container">
            <a href="admin_dashboard.php" class="back-button"><i class="fa-solid fa-chevron-left"></i> ย้อนกลับ</a>
        </div>

        <!-- Excel -->
        <form id="uploadForm" enctype="multipart/form-data">
            <label for="excelFile">เลือกไฟล์ :</label>
            <input type="file" name="excelFile" id="excelFile" accept=".xlsx, .xls">
            <button type="submit">อัปโหลดไฟล์ <i class="fa-solid fa-file-import"></i></button>
        </form>
    </div>

    <!-- <div class="response"></div> -->
    <div id='calendar'></div>

    <script>
    $(document).ready(function() {
        $('#uploadForm').submit(function(e) {
            e.preventDefault(); // ป้องกันการรีเฟรชหน้าเมื่อ submit ฟอร์ม

            if ($('#excelFile').val() === '') { // #fileInput คือ id ของ input type="file"
                Swal.fire({
                    title: 'Error!',
                    text: 'กรุณาเลือกไฟล์',
                    icon: 'error',
                    confirmButtonText: 'ตกลง'
                });
                return;
            }

            // ปิดปุ่ม upload ระหว่างที่กำลังอัปโหลด
            $('#uploadForm button').prop('disabled', true);

            // แสดง SweetAlert กำลังโหลด
            Swal.fire({
                title: 'กำลังโหลดข้อมูล...',
                // text: 'Please wait while we upload and process the file.',
                showConfirmButton: false, // ไม่ให้มีปุ่ม OK
                allowOutsideClick: false, // ไม่ให้ปิด SweetAlert โดยการคลิกนอก
                willOpen: () => {
                    Swal.showLoading();
                }
            });

            var formData = new FormData(this);

            $.ajax({
                url: 'a_ajax_add_holi_file.php',
                type: 'POST',
                data: formData,
                processData: false, // ไม่ต้องแปลงข้อมูลให้เป็น string
                contentType: false, // ใช้ form-data ไม่ต้องกำหนด content type
                success: function(response) {
                    // ปิด SweetAlert กำลังโหลด
                    Swal.close();

                    // แสดง SweetAlert การอัปโหลดสำเร็จ
                    Swal.fire({
                        title: 'สำเร็จ',
                        text: 'อัปโหลดไฟล์สำเร็จ',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            location.reload();
                        }
                    });

                    // เปิดปุ่ม upload ใหม่
                    $('#uploadForm button').prop('disabled', false);
                },
                error: function() {
                    // ปิด SweetAlert กำลังโหลด
                    Swal.close();

                    // แสดง SweetAlert ถ้ามีข้อผิดพลาด
                    Swal.fire({
                        title: 'Error!',
                        text: 'An error occurred while uploading the file.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // รีเฟรชหน้าหลังจากผู้ใช้กด OK
                            location.reload(); // รีเฟรชหน้า
                        }
                    });

                    // เปิดปุ่ม upload ใหม่
                    $('#uploadForm button').prop('disabled', false);
                }
            });
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            selectable: true,
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            dateClick: function(info) {
                var eventTitle = prompt('Enter Event Title:');
                if (eventTitle) {
                    // AJAX request to PHP to save event
                    $.ajax({
                        url: 'a_ajax_add_holiday.php',
                        method: 'POST',
                        data: {
                            eventTitle: eventTitle,
                            h_start_date: info.dateStr,
                            h_end_date: info.dateStr,
                            h_hr_name: 'Admin'
                        },
                        success: function(response) {
                            if (response.trim() === 'success') {
                                calendar.addEvent({
                                    title: eventTitle,
                                    start: info.dateStr,
                                    allDay: true
                                });
                                alert('Event added successfully');
                            } else {
                                alert('Failed to add event');
                            }
                        },
                        error: function() {
                            alert('Error communicating with server.');
                        }
                    });
                }
            },
            events: function(info, successCallback, failureCallback) {
                $.ajax({
                    url: 'a_ajax_get_holiday.php',
                    method: 'GET',
                    data: {
                        start: info.startStr, // Start date of the visible range
                        end: info.endStr // End date of the visible range
                    },
                    success: function(response) {
                        // console.log(response); // Check the response structure

                        var events = JSON.parse(response);

                        if (Array.isArray(events)) {
                            var formattedEvents = events.map(function(event) {
                                return {
                                    title: event.h_name, // ชื่อวันหยุด
                                    start: event
                                        .h_start_date, // วันที่เริ่ม
                                    end: event
                                        .h_end_date, // วันที่สิ้นสุด (หากมี)
                                    allDay: true // กำหนดว่าเป็นวันหยุดทั้งวัน
                                };
                            });
                            successCallback(formattedEvents);
                        } else {
                            console.error('Expected an array, but got:', events);
                            failureCallback('Error: Unexpected response format.');
                        }
                    },
                    error: function() {
                        failureCallback('Error communicating with server.');
                    }
                });
            },
            eventClick: function(info) {
                // แปลงวันที่ให้ถูกต้องตาม Timezone ของระบบ
                var startDate = info.event.start.toLocaleDateString(
                    'en-CA'); // รูปแบบ YYYY-MM-DD

                alert(startDate); // แสดงวันที่เพื่อยืนยันว่าถูกต้อง

                if (confirm('Do you want to delete this event?')) {
                    $.ajax({
                        url: 'a_ajax_delete_holiday.php',
                        method: 'POST',
                        data: {
                            start: startDate // ส่งวันที่ไปยัง PHP
                        },
                        success: function(response) {
                            if (response.trim() === 'success') {
                                info.event.remove(); // ลบเหตุการณ์จากปฏิทิน
                                alert('Event deleted successfully');
                            } else {
                                alert('Failed to delete event');
                            }
                        },
                        error: function() {
                            alert('Error communicating with server.');
                        }
                    });
                }
            }
        });

        calendar.render();
    });

    // function addHolidays(year) {
    //     const holidays = [];

    //     // วันสงกรานต์
    //     // const songkranStart = new Date(year, 3, 13); // 13 เมษายน
    //     // const songkranEnd = new Date(year, 3, 15); // 15 เมษายน
    //     // let currentDate = songkranStart;
    //     // while (currentDate <= songkranEnd) {
    //     //     holidays.push({
    //     //         eventTitle: 'วันสงกรานต์',
    //     //         h_start_date: formatDate(currentDate),
    //     //         h_end_date: formatDate(currentDate),
    //     //         h_hr_name: 'Admin'
    //     //     });
    //     //     currentDate.setDate(currentDate.getDate() + 1); // วันถัดไป
    //     // }

    //     // หาวันอาทิตย์ทั้งหมดในปี
    //     let date = new Date(year, 0, 1); // เริ่มต้นที่วันที่ 1 มกราคม

    //     // หาวันอาทิตย์แรกของปี
    //     while (date.getDay() !== 0) {
    //         date.setDate(date.getDate() + 1);
    //     }

    //     // เพิ่มวันอาทิตย์ทั้งหมดในปีนั้น
    //     while (date.getFullYear() === year) {
    //         holidays.push({
    //             eventTitle: 'วันหยุดวันอาทิตย์',
    //             h_start_date: formatDate(date),
    //             h_end_date: formatDate(date),
    //             h_hr_name: 'Admin',
    //         });
    //         date.setDate(date.getDate() + 7); // ไปที่วันอาทิตย์ถัดไป
    //     }

    //     // วันแรงงาน (1 พฤษภาคม)
    //     // holidays.push({
    //     //     eventTitle: 'วันแรงงาน',
    //     //     h_start_date: `${year}-05-01`,
    //     //     h_end_date: `${year}-05-01`,
    //     //     h_hr_name: 'Admin'
    //     // });

    //     // // 3 มิถุนายน Queen Suthida's Birthday
    //     // holidays.push({
    //     //     eventTitle: "วันเฉลิมพระชนมพรรษา สมเด็จพระนางเจ้าฯ พระบรมราชินี",
    //     //     h_start_date: `${year}-06-03`,
    //     //     h_end_date: `${year}-06-03`,
    //     //     h_hr_name: 'Admin'
    //     // });

    //     // // 29 กรกฎาคม King's Birthday
    //     // holidays.push({
    //     //     eventTitle: "วันเฉลิมพระชนมพรรษา พระบาทสมเด็จพระเจ้าอยู่หัว",
    //     //     h_start_date: `${year}-07-29`,
    //     //     h_end_date: `${year}-07-29`,
    //     //     h_hr_name: 'Admin'
    //     // });

    //     // // 12 สิงหาคม Queen Sirikit The Queen Mother's birthday
    //     // holidays.push({
    //     //     eventTitle: "วันเฉลิมพระชนมพรรษา สมเด็จพระนางเจ้าสิริกิติ์ฯ",
    //     //     h_start_date: `${year}-08-12`,
    //     //     h_end_date: `${year}-08-12`,
    //     //     h_hr_name: 'Admin'
    //     // });

    //     // // 13 ตุลาคม วันคล้ายวันสวรรคตในหลวงรัชกาลที่ 9
    //     // holidays.push({
    //     //     eventTitle: "วันคล้ายวันสวรรคต ร.9",
    //     //     h_start_date: `${year}-10-13`,
    //     //     h_end_date: `${year}-10-13`,
    //     //     h_hr_name: 'Admin'
    //     // });

    //     // // 5 ธันวาคม วันพ่อแห่งชาติ
    //     // holidays.push({
    //     //     eventTitle: "วันพ่อแห่งชาติ",
    //     //     h_start_date: `${year}-12-05`,
    //     //     h_end_date: `${year}-12-05`,
    //     //     h_hr_name: 'Admin'
    //     // });

    //     // // 31 ธันวาคม วันสิ้นปี
    //     // holidays.push({
    //     //     eventTitle: "วันสิ้นปี",
    //     //     h_start_date: `${year}-12-31`,
    //     //     h_end_date: `${year}-12-31`,
    //     //     h_hr_name: 'Admin'
    //     // });

    //     // ส่งข้อมูลวันหยุดไปยังเซิร์ฟเวอร์
    //     $.ajax({
    //         url: 'a_ajax_add_holiday.php',
    //         method: 'POST',
    //         contentType: 'application/json',
    //         data: JSON.stringify({
    //             holidays: holidays
    //         }),
    //         success: function(response) {
    //             console.log('All holidays added successfully');
    //         },
    //         error: function() {
    //             alert('Error communicating with server.');
    //         }
    //     });
    // }

    // ฟังก์ชันในการแปลงวันเป็นรูปแบบ YYYY-MM-DD
    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0'); // เดือนเริ่มต้นที่ 0
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    // เพิ่มวันหยุดสำหรับปีปัจจุบัน
    const year = new Date().getFullYear();
    addHolidays(year);
    </script>
</body>

</html>