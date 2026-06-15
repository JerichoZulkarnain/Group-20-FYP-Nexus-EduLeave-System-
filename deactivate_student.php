<?php
include 'db_config.php';
include 'log_activity.php';
logActivity($conn, $_SESSION['user']['id'], $_SESSION['user']['full_name'], $_SESSION['user']['role'], 'Account Update', $action . 'd student account');

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$student_id = $_GET['id'] ?? 0;
$action = $_GET['action'] ?? '';

if($action == 'deactivate') {
    mysqli_query($conn, "UPDATE users SET status = 'inactive' WHERE id = '$student_id' AND role = 'student'");
    echo "<script>alert('Student account deactivated!'); window.location.href='manage_students.php';</script>";
} elseif($action == 'activate') {
    mysqli_query($conn, "UPDATE users SET status = 'active' WHERE id = '$student_id' AND role = 'student'");
    echo "<script>alert('Student account activated!'); window.location.href='manage_students.php';</script>";
} else {
    header("Location: manage_students.php");
}
?>