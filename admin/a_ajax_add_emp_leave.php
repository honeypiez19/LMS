<?php
session_start();
date_default_timezone_set('Asia/Bangkok');

require '../connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userCode = $_POST['userCode'];
    $userName = $_POST['userName'];
    $name = $_POST['name'];
    $telPhone = $_POST['telPhone'];
    $depart = $_POST['depart'];
    $level = $_POST['level'];
    $workplace = $_POST['workplace'];
    $subDepart = $_POST['subDepart'];
    $addUserName = $_POST['addUserName'];

    $leaveType = $_POST['leaveType'];
    $leaveReason = $_POST['leaveReason'];
    $remark = 'HR ลาย้อนหลัง';

    $createDateByHR = date('Y-m-d H:i:s');
    $createDate = date('Y-m-d H:i:s');

    // ตรวจสอบประเภทการลา
    $leaveTypes = [
        1 => 'ลากิจได้รับค่าจ้าง',
        2 => 'ลากิจไม่ได้รับค่าจ้าง',
        3 => 'ลาป่วย',
        4 => 'ลาป่วยจากงาน',
        5 => 'ลาพักร้อน',
        8 => 'อื่น ๆ',
    ];
    $leaveName = $leaveTypes[$leaveType] ?? 'ไม่พบประเภทการลา';

    // วันที่ + เวลาเริ่มต้นที่ลา
    $leaveDateStart = date('Y-m-d', strtotime($_POST['startDate']));
    $leaveTimeStart = $_POST['startTime'];

    // วันที่ + เวลาสิ้นสุดที่ลา
    $leaveDateEnd = date('Y-m-d', strtotime($_POST['endDate']));
    $leaveTimeEnd = $_POST['endTime'];

    if($leaveTimeStart == '12:00'){
        $leaveTimeStartLine = '11:45';
    } else if($leaveTimeStart == '13:00'){
        $leaveTimeStartLine = '12:45';
    } else if($leaveTimeStart == '17:00'){
        $leaveTimeStartLine = '16:40';
    } else{
        $leaveTimeStartLine = $leaveTimeStart;
    }

    if($leaveTimeEnd == '12:00'){
        $leaveTimeEndLine = '11:45';
    } else if($leaveTimeEnd == '13:00'){
        $leaveTimeEndLine = '12:45';
    } else if($leaveTimeEnd == '17:00'){
        $leaveTimeEndLine = '16:40';
    } else{
        $leaveTimeEndLine = $leaveTimeEnd;
    }
    
    // เช็คระดับ > เช็คแผก > สถานะอนุมัติ
    if($level == 'user'){
        // RD
        if($depart == 'RD'){
            $proveStatus = 0;
            $proveStatus2 = 1;
            $comfirmStatus = 0;
        } 
        // Office
        else if($depart == 'Office'){
            if($subDepart == 'Store' || $subDepart == 'AC' || $subDepart == 'Sales'){
                $proveStatus = 0;
                $proveStatus2 = 1;
                $comfirmStatus = 0;
            } else if($subDepart == ''){
                $proveStatus = 6;
                $proveStatus2 = 1;
                $comfirmStatus = 0;
            } else {
                echo 'ไม่พบแผนก';
            }
        } 
        // CAD1 / CAD2 / CAM
        else if($depart == 'CAD1' || $depart == 'CAD2' || $depart = 'CAM'){
            if($subDepart == 'Modeling' || $subDepart == 'Design'){
                $proveStatus = 0;
                $proveStatus2 = 1;
                $comfirmStatus = 0;
            } else if($subDepart == 'CAD2'){
                $proveStatus = 0;
                $proveStatus2 = 1;
                $comfirmStatus = 0;
            } else if($subDepart == 'CAM'){
                $proveStatus = 0;
                $proveStatus2 = 1;
                $comfirmStatus = 0;
            } else {
                echo 'ไม่พบแผนก';
            }
        }
        // 
    } else if(($level == 'leader')){
         // RD
        if($depart == 'RD'){
            $proveStatus = 0;
            $proveStatus2 = 1;
            $comfirmStatus = 0;
        } 
        else if($depart == 'Office'){
            if($subDepart == 'Store' || $subDepart == 'AC' || $subDepart == 'Sales'){
                $proveStatus = 0;
                $proveStatus2 = 1;
                $comfirmStatus = 0;
            } else if($subDepart == ''){
                $proveStatus = 0;
                $proveStatus2 = 1;
                $comfirmStatus = 0;
            } else {
                echo 'ไม่พบแผนก';
            }
        }  // CAD1 / CAD2 / CAM
        else if($depart == 'CAD1' || $depart == 'CAD2' || $depart = 'CAM'){
            if($subDepart == 'Modeling' || $subDepart == 'Design'){
                $proveStatus = 0;
                $proveStatus2 = 1;
                $comfirmStatus = 0;
            } else if($subDepart == 'CAD2'){
               $proveStatus = 0;
                $proveStatus2 = 1;
                $comfirmStatus = 0;
            } else if($subDepart == 'CAM'){
                $proveStatus = 0;
                $proveStatus2 = 1;
                $comfirmStatus = 0;
            } else {
                echo 'ไม่พบแผนก';
            }
        }
        else if($depart == 'Management'){
            if($subDepart == 'AC' || $subDepart == 'Sales'){
                $proveStatus = 0;
                $proveStatus2 = 1;
                $comfirmStatus = 0;
            }
        }
    } 
    else if(($level == 'chief')){
        if($depart == 'Management'){
            if($subDepart == 'AC' || $subDepart == 'Sales'){
                $proveStatus = 0;
                $proveStatus2 = 1;
                $comfirmStatus = 0;
            }
        }
    }
    else if(($level == 'assisManager')){
        if($depart == 'Management'){
            if($subDepart == 'CAD1'){
                $proveStatus = 0;
                $proveStatus2 = 1;
                $comfirmStatus = 0;
            }
        }
    }
    else if(($level == 'manager')){
           // RD
        if($depart == 'RD'){
            $proveStatus = 0;
            $proveStatus2 = 1;
            $comfirmStatus = 0;
        } 
        else if($depart == 'Office'){
            if($subDepart == 'Store' || $subDepart == 'AC' || $subDepart == 'Sales'){
                $proveStatus = 0;
                $proveStatus2 = 1;
                $comfirmStatus = 0;
            } else if($subDepart == ''){
                $proveStatus = 0;
                $proveStatus2 = 1;
                $comfirmStatus = 0;
            } else {
                echo 'ไม่พบแผนก';
            }
        }  // CAD1 / CAD2 / CAM
        else if($depart == 'CAD1' || $depart == 'CAD2' || $depart = 'CAM'){
            if($subDepart == 'Modeling' || $subDepart == 'Design'){
                $proveStatus = 0;
                $proveStatus2 = 1;
                $comfirmStatus = 0;
            } else if($subDepart == 'CAD2'){
                $proveStatus = 0;
                $proveStatus2 = 1;
                $comfirmStatus = 0;
            } else if($subDepart == 'CAM'){
                $proveStatus = 0;
                $proveStatus2 = 1;
                $comfirmStatus = 0;
            } else {
                echo 'ไม่พบแผนก';
            }
        } else if($depart == 'Management'){
                $proveStatus = 0;
                $proveStatus2 = 1;
                $comfirmStatus = 0;
        }
    }
    
    // สถานะใบลา
    $leaveStatus = 0;
    $leaveStatusName = ($leaveStatus == 0) ? 'ปกติ' : 'ยกเลิก';

    // $comfirmStatus = 0;
    // $proveStatus = 0;
    // $proveStatus2 = 1;
    
    $filename = null;
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $filename = $_FILES['file']['name'];
        $location = "../upload/" . $filename;
        $imageFileType = strtolower(pathinfo($location, PATHINFO_EXTENSION));

        $valid_extensions = ["jpg", "jpeg", "png"];
        if (in_array($imageFileType, $valid_extensions)) {
            if (move_uploaded_file($_FILES['file']['tmp_name'], $location)) {
                $response = $location;
            } else {
                echo json_encode(["error" => "ไม่สามารถบันทึกไฟล์ได้"]);
            }
        } else {
            echo json_encode(["error" => "ประเภทไฟล์ไม่ถูกต้อง"]);
        }
    } else {
        echo json_encode(["error" => "ไม่มีไฟล์ที่ถูกต้อง"]);
    }


    $stmt = $conn->prepare("INSERT INTO leave_list (l_usercode, l_username, l_name, l_department, l_phone, l_leave_id, l_leave_reason,
    l_leave_start_date, l_leave_start_time, l_leave_end_date, l_leave_end_time, 
    l_hr_create_datetime, l_file, l_leave_status, l_hr_status, l_approve_status, 
    l_level, l_approve_status2, l_workplace,l_hr_create_name,l_remark,l_create_datetime)
    VALUES (:userCode, :userName, :name, :depart, :telPhone, :leaveType, :leaveReason, :leaveDateStart, :leaveTimeStart,
    :leaveDateEnd, :leaveTimeEnd, :createDateByHR, :filename, :leaveStatus, 
    :comfirmStatus, :proveStatus, :level, :proveStatus2, :workplace, :addUserName, :remark, :createDate)");

    $stmt->bindParam(':userCode', $userCode);
    $stmt->bindParam(':userName', $userName);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':depart', $depart);
    $stmt->bindParam(':telPhone', $telPhone);
    $stmt->bindParam(':leaveType', $leaveType);
    $stmt->bindParam(':leaveReason', $leaveReason);
    $stmt->bindParam(':leaveDateStart', $leaveDateStart);
    $stmt->bindParam(':leaveTimeStart', $leaveTimeStart);
    $stmt->bindParam(':leaveDateEnd', $leaveDateEnd);
    $stmt->bindParam(':leaveTimeEnd', $leaveTimeEnd);
    $stmt->bindParam(':createDateByHR', $createDateByHR);
    $stmt->bindParam(':filename', $filename);
    $stmt->bindParam(':leaveStatus', $leaveStatus);
    $stmt->bindParam(':comfirmStatus', $comfirmStatus);
    $stmt->bindParam(':proveStatus', $proveStatus);
    $stmt->bindParam(':proveStatus2', $proveStatus2);
    $stmt->bindParam(':level', $level);
    $stmt->bindParam(':workplace', $workplace);
    $stmt->bindParam(':addUserName', $addUserName);
    $stmt->bindParam(':remark', $remark);
    $stmt->bindParam(':createDate', $createDate);


    if($stmt->execute()){
        echo 'บันทึกข้อมูลสำเร็จ';
    } else {
        echo 'เกิดข้อผิดพลาดในการบันทึกข้อมูล';
    }
    $conn = null;
}