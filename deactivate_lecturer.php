<?php
include 'db_config.php';

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$lecturer_id = $_GET['id'] ?? 0;
$action = $_GET['action'] ?? '';

if($action == 'deactivate') {
    mysqli_query($conn, "UPDATE users SET status = 'inactive' WHERE id = '$lecturer_id' AND role = 'lecturer'");
    echo "<script>alert('Lecturer account deactivated!'); window.location.href='assign_lecturer.php';</script>";
} elseif($action == 'activate') {
    mysqli_query($conn, "UPDATE users SET status = 'active' WHERE id = '$lecturer_id' AND role = 'lecturer'");
    echo "<script>alert('Lecturer account activated!'); window.location.href='assign_lecturer.php';</script>";
} else {
    header("Location: assign_lecturer.php");
}
?>