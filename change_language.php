<?php
session_start();
$_SESSION["lang"] = $_GET["lang"];
session_write_close();

$level = $_GET["level"];

if ($level === 'user') {
    header("Location: user/user_dashboard.php");
} elseif ($level === 'leader') {
    header("Location: leader/leader_dashboard.php");
} elseif ($level === 'chief') {
    header("Location: chief/chief_dashboard.php");
} elseif ($level === 'manager') {
    header("Location: manager/manager_dashboard.php");
} elseif ($level === 'GM') {
    header("Location: gm/gm_dashboard.php");
} elseif ($level === 'admin') {
    header("Location: admin/adin_dashboard.php");
} else {
    // Default fallback, e.g., an error page or login page
    header("Location: error_page.php");
}
exit();