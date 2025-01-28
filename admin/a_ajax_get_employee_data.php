<?php
session_start();

require '../connect.php';

if (isset($_POST['usercode'])) {
    $usercode = $_POST['usercode'];

    $sql  = "SELECT * FROM employees WHERE e_usercode = :usercode";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':usercode', $usercode);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch();

        echo '<input class="form-control" type="hidden" id="edit_usercode" value="' . $row['e_usercode'] . '">';
        echo '<div class="row">
        <h5>ข้อมูลการเข้าระบบ</h5>
        <div class="col-6">
            <label for="codeLabel">รหัสพนักงาน</label>
            <input  class="form-control" type="text" id="edit_usercode" value="' . $row['e_usercode'] . '" disabled>
        </div>
        <div class="col-6">
            <label for="usernameLabel">ชื่อผู้ใช้</label>
            <input  class="form-control" type="text" id="edit_username" value="' . $row['e_username'] . '">
        </div>
        </div>';

        echo '<div class="mt-3 row">
        <div class="col-6">
        <label for="passwordLabel">รหัสผ่าน</label>
        <input  class="form-control" type="text" id="edit_password" value="' . $row['e_password'] . '">
        </div>
        </div>';

        // ข้อมูลพนักงาน ---------------------------------------------------------------------
        echo '<div class="mt-3 row">';
        echo '<h5>ข้อมูลพนักงาน</h5>';
        // ชื่อ - นามสกุล
        echo ' <div class="col-6">
        <label for="nameLabel">ชื่อ - นามสกุล</label>
        <input  class="form-control" type="text" id="edit_name" value="' . $row['e_name'] . '">
        </div>';

        $employee_sql = "SELECT employees.e_level, level.l_level
        FROM employees
        INNER JOIN level ON employees.e_level = level.l_level
        WHERE employees.e_usercode = :usercode";
        $stmt_employee = $conn->prepare($employee_sql);
        $stmt_employee->bindParam(':usercode', $usercode);
        $stmt_employee->execute();
        $employee_data = $stmt_employee->fetch();

        $level_sql  = "SELECT * FROM level";
        $stmt_level = $conn->prepare($level_sql);
        $stmt_level->execute();
        $levels = $stmt_level->fetchAll();

        // ระดับ
        echo '<div class="col-3">';
        echo '<label for="levelLabel">ระดับ</label>';
        echo '<select class="form-control" id="edit_level" style="border-radius: 50px;">';

        foreach ($levels as $level) {
            echo '<option value="' . $level['l_level'] . '"';

            if ($employee_data['e_level'] == $level['l_level']) {
                echo ' selected';
            }

            echo '>' . $level['l_level'] . '</option>';
        }

        echo '</select>';

        echo '</div>';

        $employee_sql = "SELECT
            employees.e_department,
            department.d_department,
            employees.e_sub_department,
            employees.e_sub_department2,
            employees.e_sub_department3,
            employees.e_sub_department4,
            employees.e_sub_department5
        FROM employees
        INNER JOIN department ON employees.e_department = department.d_department
        WHERE employees.e_usercode = :usercode";

        $stmt_employee = $conn->prepare($employee_sql);
        $stmt_employee->bindParam(':usercode', $usercode);
        $stmt_employee->execute();
        $employee_data = $stmt_employee->fetch();

        $department_sql  = "SELECT * FROM department WHERE d_department NOT IN ('Modeling', 'Design', 'AC', 'Sales', 'Store')";
        $stmt_department = $conn->prepare($department_sql);
        $stmt_department->execute();
        $departments = $stmt_department->fetchAll();

        // แผนกหลัก
        echo '<div class="col-3">';
        echo '<label for="edit_department">แผนกหลัก</label>';
        echo '<select class="form-control" id="edit_department" style="border-radius: 20px;">';

        foreach ($departments as $department) {
            echo '<option value="' . $department['d_department'] . '"';

            if ($employee_data['d_department'] == $department['d_department']) {
                echo ' selected';
            }

            echo '>' . $department['d_department'] . '</option>';
        }

        echo '</select>';
        echo '</div>';
        echo '</div>';

        $subdepart_sql   = "SELECT * FROM department";
        $stmt_department = $conn->prepare($subdepart_sql);
        $stmt_department->execute();
        $subdeparts = $stmt_department->fetchAll();

        echo '<div class="mt-3 row">';
        // แผนกย่อย_1
        echo '<div class="col-3">';
        echo '<label for="subdepartLabel">แผนกย่อย_1</label>';
        echo '<select class="form-control" id="edit_subdepart" style="border-radius: 20px;" required>';

        echo '<option value="">กรุณาเลือกแผนกย่อย_1</option>';

        foreach ($subdeparts as $subdepart) {
            echo '<option value="' . $subdepart['d_department'] . '"';

            if ($employee_data['e_sub_department'] == $subdepart['d_department']) {
                echo ' selected';
            }

            echo '>' . $subdepart['d_department'] . '</option>';
        }

        echo '</select>';
        echo '</div>';

        // แผนกย่อย_2
        echo '<div class="col-3">';
        echo '<label for="subdepart2Label">แผนกย่อย_2</label>';
        echo '<select class="form-control" id="edit_subdepart2" style="border-radius: 20px;" required>';

        echo '<option value="">กรุณาเลือกแผนกย่อย_2</option>';

        foreach ($subdeparts as $subdepart) {
            echo '<option value="' . $subdepart['d_department'] . '"';

            if ($employee_data['e_sub_department2'] == $subdepart['d_department']) {
                echo ' selected';
            }

            echo '>' . $subdepart['d_department'] . '</option>';
        }

        echo '</select>';
        echo '</div>';

        // แผนกย่อย_3
        echo '<div class="col-3">';
        echo '<label for="subdepart3Label">แผนกย่อย_3</label>';
        echo '<select class="form-control" id="edit_subdepart3" style="border-radius: 20px;" required>';

        echo '<option value="">กรุณาเลือกแผนกย่อย_3</option>';

        foreach ($subdeparts as $subdepart) {
            echo '<option value="' . $subdepart['d_department'] . '"';

            if ($employee_data['e_sub_department3'] == $subdepart['d_department']) {
                echo ' selected';
            }

            echo '>' . $subdepart['d_department'] . '</option>';
        }

        echo '</select>';
        echo '</div>';

        // แผนกย่อย_4
        echo '<div class="col-3">';
        echo '<label for="subdepart4Label">แผนกย่อย_4</label>';
        echo '<select class="form-control" id="edit_subdepart4" style="border-radius: 20px;" required>';

        echo '<option value="">กรุณาเลือกแผนกย่อย_4</option>';

        foreach ($subdeparts as $subdepart) {
            echo '<option value="' . $subdepart['d_department'] . '"';

            if ($employee_data['e_sub_department4'] == $subdepart['d_department']) {
                echo ' selected';
            }
            echo '>' . $subdepart['d_department'] . '</option>';
        }

        echo '</select>';
        echo '</div>';
        echo '</div>';

        echo '<div class="mt-3 row">';

        // แผนกย่อย_5
        echo '<div class="col-3">';
        echo '<label for="subdepart5Label">แผนกย่อย_5</label>';
        echo '<select class="form-control" id="edit_subdepart5" style="border-radius: 20px;" required>';

        echo '<option value="">กรุณาเลือกแผนกย่อย_5</option>';

        foreach ($subdeparts as $subdepart) {
            echo '<option value="' . $subdepart['d_department'] . '"';

            if ($employee_data['e_sub_department5'] == $subdepart['d_department']) {
                echo ' selected';
            }
            echo '>' . $subdepart['d_department'] . '</option>';
        }

        echo '</select>';
        echo '</div>';

        $employee_sql = "SELECT employees.e_workplace, workplace.w_name
        FROM employees
        INNER JOIN workplace ON employees.e_workplace = workplace.w_name
        WHERE employees.e_usercode = :usercode";
        $stmt_employee = $conn->prepare($employee_sql);
        $stmt_employee->bindParam(':usercode', $usercode);
        $stmt_employee->execute();
        $employee_data = $stmt_employee->fetch();

        $workplace_sql  = "SELECT * FROM workplace";
        $stmt_workplace = $conn->prepare($workplace_sql);
        $stmt_workplace->execute();
        $workplaces = $stmt_workplace->fetchAll();

        // สถานที่ทำงาน
        echo '<div class="col-3">';
        echo '<label for="workplaceLabel">สถานที่ทำงาน</label>';
        echo '<select class="form-control" id="edit_workplace" style="border-radius: 50px;">';

        foreach ($workplaces as $workplace) {
            echo '<option value="' . $workplace['w_name'] . '"';

            if ($employee_data['e_workplace'] == $workplace['w_name']) {
                echo ' selected';
            }

            echo '>' . $workplace['w_name'] . '</option>';
        }
        echo '</select>';
        echo '</div>';

        // วันที่เริ่มงาน
        echo '<div class="col-3">';
        echo '<label for="workStartDateLabel">วันที่เริ่มงาน</label>';
        echo '<input class="form-control" type="date" id="edit_work_start_date" value="' . $row['e_work_start_date'] . '">';
        echo '</div>';

        // เบอร์โทรศัพท์
        echo '<div class="col-3">';
        echo '<label for="phoneLabel">เบอร์โทรศัพท์</label>
        <span style="color: red;">Ex. XXX-XXXXXXX</span>
        <input  class="form-control" type="text" id="edit_phone" value="' . $row['e_phone'] . '">
        </div>';
        echo '</div>';
        echo '</div>';

        // Line token
        echo '<div class="mt-3 row">';
        echo '<div class="col-6">';
        echo '<label for="lineLabel">Line token</label>';
        echo '<input class="form-control" type="text" id="edit_line" value="' . $row['e_token'] . '">';
        echo '</div>';
        echo '</div>';

        echo '<div class="mt-3 row">
        <h5>จำนวนวันลาที่ได้รับ</h5>
        <div class="col-3">
        <label for="personalLabel">ลากิจได้รับค่าจ้าง</label>
        <input  class="form-control" type="text" id="edit_personal" value="' . $row['e_leave_personal'] . '">
        </div>

        <div class="col-3">
        <label for="personalNoLabel">ลากิจไม่ได้รับค่าจ้าง</label>
        <input  class="form-control" type="text" id="edit_personal_no" value="' . $row['e_leave_personal_no'] . '">
        </div>

        <div class="col-3">
        <label for="sickLabel">ลาป่วย</label>
        <input  class="form-control" type="text" id="edit_sick" value="' . $row['e_leave_sick'] . '">
        </div>

        <div class="col-3">
        <label for="sickWorkLabel">ลาป่วยจากงาน</label>
        <input  class="form-control" type="text" id="edit_sick_work" value="' . $row['e_leave_sick_work'] . '">
        </div>
        </div>';

        echo '<div class="mt-3 row">
        <div class="col-3">
        <label for="annualLabel">ลาพักร้อน</label>
        <input  class="form-control" type="text" id="edit_annual" value="' . $row['e_leave_annual'] . '">
        </div>

        <div class="col-3">
        <label for="otherLabel">อื่น ๆ</label>
        <input  class="form-control" type="text" id="edit_other" value="' . $row['e_other'] . '">
        </div>
        </div>';

    }
}