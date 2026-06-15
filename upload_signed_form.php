<?php
include 'db_config.php';

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['signed_form'])) {
    $leave_id = $_POST['leave_id'];
    $file = $_FILES['signed_form'];
    
    $targetDir = "uploads/signed_forms/";
    if(!is_dir($targetDir)) mkdir($targetDir, 0777, true);
    
    $fileName = time() . '_leave_' . $leave_id . '.pdf';
    $targetFile = $targetDir . $fileName;
    
    $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    
    if($fileType != 'pdf') {
        echo "<script>alert('Only PDF files are allowed!'); window.history.back();</script>";
        exit();
    }
    
    if(move_uploaded_file($file['tmp_name'], $targetFile)) {
        mysqli_query($conn, "UPDATE leave_applications SET status = 'Approved', mc_file = '$targetFile' WHERE id = '$leave_id'");
        echo "<script>alert('Signed form uploaded successfully!'); window.location.href='track_status.php';</script>";
    } else {
        echo "<script>alert('Failed to upload form!'); window.history.back();</script>";
    }
} else {
    header("Location: track_status.php");
}
?>