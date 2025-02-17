<?php
// Connect to your database
include '../connect.php';

$searchCode    = isset($_GET['codeSearch']) ? trim($_GET['codeSearch']) : '';
$selectedYear  = isset($_GET['year']) ? $_GET['year'] : '';
$selectedMonth = isset($_GET['month']) ? $_GET['month'] : '';
$page          = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$itemsPerPage  = 10;
$offset        = ($page - 1) * $itemsPerPage;

// SQL Query นับจำนวนทั้งหมดก่อน
$countSql = "SELECT COUNT(*) FROM leave_list WHERE 1=1";
if (! empty($searchCode)) {
    $countSql .= " AND l_usercode LIKE :searchCode";
}
if (! empty($selectedYear)) {
    $countSql .= " AND YEAR(l_create_datetime) = :selectedYear";
}
if (! empty($selectedMonth)) {
    $countSql .= " AND MONTH(l_create_datetime) = :selectedMonth";
}

$countStmt = $conn->prepare($countSql);
if (! empty($searchCode)) {
    $countStmt->bindValue(':searchCode', "%$searchCode%", PDO::PARAM_STR);
}
if (! empty($selectedYear)) {
    $countStmt->bindValue(':selectedYear', $selectedYear, PDO::PARAM_INT);
}
if (! empty($selectedMonth)) {
    $countStmt->bindValue(':selectedMonth', $selectedMonth, PDO::PARAM_INT);
}
$countStmt->execute();
$totalRows  = $countStmt->fetchColumn();        // ดึงจำนวนแถวทั้งหมด
$totalPages = ceil($totalRows / $itemsPerPage); // คำนวณจำนวนหน้า

// ดึงข้อมูลที่ต้องการ
$sql = "SELECT * FROM leave_list WHERE 1=1";
if (! empty($searchCode)) {
    $sql .= " AND l_usercode LIKE :searchCode";
}
if (! empty($selectedYear)) {
    $sql .= " AND YEAR(l_create_datetime) = :selectedYear";
}
if (! empty($selectedMonth)) {
    $sql .= " AND MONTH(l_create_datetime) = :selectedMonth";
}

$sql .= " ORDER BY l_create_datetime DESC LIMIT $itemsPerPage OFFSET $offset"; // ใช้ค่าโดยตรงกับ LIMIT และ OFFSET

$stmt = $conn->prepare($sql);
if (! empty($searchCode)) {
    $stmt->bindValue(':searchCode', "%$searchCode%", PDO::PARAM_STR);
}
if (! empty($selectedYear)) {
    $stmt->bindValue(':selectedYear', $selectedYear, PDO::PARAM_INT);
}
if (! empty($selectedMonth)) {
    $stmt->bindValue(':selectedMonth', $selectedMonth, PDO::PARAM_INT);
}
$stmt->execute();

$rowNumber = $totalRows - $offset; // กำหนดหมายเลขลำดับ

$data = '';
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $data .= '<tr class="align-middle">';
    $data .= '<td>' . $rowNumber . '</td>';
    $data .= '<td>' . htmlspecialchars($row['l_usercode']) . '</td>';
    $data .= '<td>' . htmlspecialchars($row['l_name']) . '</td>';
    $data .= '</tr>';
    $rowNumber--; // ลดค่าลงตามลำดับ
}

if (empty($data)) {
    file_put_contents('debug_log.txt', "🔴 No data found\n", FILE_APPEND);
}

// สร้าง Pagination HTML
$pagination = '<nav><ul class="pagination">';
for ($i = 1; $i <= $totalPages; $i++) {
    $activeClass = ($i == $page) ? ' active' : ''; // เพิ่มคลาส active สำหรับหน้าปัจจุบัน
    $pagination .= '<li class="page-item' . $activeClass . '">
                        <a class="page-link" href="#" data-page="' . $i . '">' . $i . '</a>
                    </li>';
}
$pagination .= '</ul></nav>';

// สร้าง JSON
echo json_encode(['data' => $data, 'totalRows' => $totalRows, 'pagination' => $pagination]);