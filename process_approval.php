<?php
include 'db_config.php';
include 'notification_helper.php';

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'lecturer') {
    header("Location: login.php");
    exit();
}

$lecturer_id = $_SESSION['user']['id'];
$lecturer_name = $_SESSION['user']['full_name'];

if(isset($_GET['leave_id']) && isset($_GET['action'])) {
    $leave_id = $_GET['leave_id'];
    $action = $_GET['action'];
    
    if($action == 'approve') {
        // UPDATE ALL signatures for this lecturer (ALL subjects)
        $approved_at = date('Y-m-d H:i:s');
        $update = mysqli_query($conn, "
            UPDATE lecturer_signatures 
            SET status = 'Approved', signed_at = '$approved_at' 
            WHERE leave_id = '$leave_id' AND lecturer_id = '$lecturer_id'
        ");
        
        if($update) {
            // Get student info
            $leaveQuery = mysqli_query($conn, "SELECT student_id, leave_type FROM leave_applications WHERE id = '$leave_id'");
            $leaveData = mysqli_fetch_assoc($leaveQuery);
            $student_id = $leaveData['student_id'];
            $leave_type = $leaveData['leave_type'];
            
            // Check if ALL lecturers have fully approved
            $checkAll = mysqli_query($conn, "
                SELECT 
                    COUNT(DISTINCT lecturer_id) as total_lecturers,
                    SUM(CASE 
                        WHEN total_subjects = approved_subjects THEN 1 
                        ELSE 0 
                    END) as fully_approved
                FROM (
                    SELECT 
                        lecturer_id,
                        COUNT(*) as total_subjects,
                        SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as approved_subjects
                    FROM lecturer_signatures 
                    WHERE leave_id = '$leave_id'
                    GROUP BY lecturer_id
                ) as lecturer_status
            ");
            $result = mysqli_fetch_assoc($checkAll);
            
            if($result['total_lecturers'] == $result['fully_approved']) {
                mysqli_query($conn, "UPDATE leave_applications SET status = 'Approved' WHERE id = '$leave_id'");
                addNotification($conn, $student_id, 'Leave Approved', 'Your ' . $leave_type . ' leave application has been fully approved.', 'leave');
                echo "<script>alert('All subjects approved! Application fully approved.'); window.location.href='pending_applications.php';</script>";
            } else {
                addNotification($conn, $student_id, 'Leave Update', 'Your ' . $leave_type . ' leave application has been approved by ' . $lecturer_name . ' for all their subjects.', 'leave');
                echo "<script>alert('Approved for all your subjects!'); window.location.href='pending_applications.php';</script>";
            }
        } else {
            echo "<script>alert('Error approving application!'); window.location.href='pending_applications.php';</script>";
        }
    } elseif($action == 'reject') {
        $rejection_reason = $_GET['reason'] ?? 'No reason provided';
        $approved_at = date('Y-m-d H:i:s');
        
        mysqli_query($conn, "
            UPDATE lecturer_signatures 
            SET status = 'Rejected', signed_at = '$approved_at', approval_reason = '$rejection_reason' 
            WHERE leave_id = '$leave_id' AND lecturer_id = '$lecturer_id'
        ");
        
        $leaveQuery = mysqli_query($conn, "SELECT student_id, leave_type FROM leave_applications WHERE id = '$leave_id'");
        $leaveData = mysqli_fetch_assoc($leaveQuery);
        $student_id = $leaveData['student_id'];
        $leave_type = $leaveData['leave_type'];
        
        mysqli_query($conn, "UPDATE leave_applications SET status = 'Rejected', rejection_reason = '$rejection_reason' WHERE id = '$leave_id'");
        addNotification($conn, $student_id, 'Leave Rejected', 'Your ' . $leave_type . ' leave application has been rejected by ' . $lecturer_name . '. Reason: ' . $rejection_reason, 'leave');
        
        echo "<script>alert('Application rejected!'); window.location.href='pending_applications.php';</script>";
    }
} else {
    header("Location: pending_applications.php");
}
?>