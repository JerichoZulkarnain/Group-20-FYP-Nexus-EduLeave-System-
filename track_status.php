<?php
include 'db_config.php';

if(!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

if($_SESSION['user']['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];
$student_id = $user['id'];

$sql = "SELECT * FROM leave_applications WHERE student_id='$student_id' ORDER BY created_at DESC LIMIT 1";
$result = mysqli_query($conn, $sql);
$leave = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Track Status - EduLeave</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .status-approved { color: green; }
        .status-pending { color: orange; }
        .status-rejected { color: red; }
        .status-cancelled { color: #888; }
        .progress-table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; }
        .progress-table th { background: #123966; color: white; padding: 12px; text-align: left; }
        .progress-table td { padding: 12px; border-bottom: 1px solid #ddd; }
        .step-completed { color: #28a745; }
        .step-pending { color: #999; }
        .cancelled-box {
            background: #f0f0f0;
            border-left: 5px solid #888;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 25px;
        }
        .apply-again-btn {
            display: inline-block;
            background: #123966;
            color: white;
            padding: 12px 30px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: bold;
            margin-top: 15px;
        }
        .apply-again-btn:hover {
            background: #0a2a4a;
        }
    </style>
</head>

<body class="dashboard-page">

<div class="sidebar">
    <img src="images/logo.png" class="sidebar-logo">
    <div class="sidebar-title">EduLeave System</div>
    <div class="sidebar-date"><?php echo date("l d M Y"); ?></div>

    <nav class="nav-menu">
        <a href="dashboard_student.php" class="nav-link"><i class="fa-solid fa-house nav-fa"></i> Home</a>
        <a href="profile_student.php" class="nav-link"><i class="fa-solid fa-user nav-fa"></i> Profile</a>
        <br>
        <a href="apply_leave.php" class="nav-link"><i class="fa-solid fa-file-pen nav-fa"></i> Apply Leave</a>
        <a href="leave_history.php" class="nav-link"><i class="fa-solid fa-clock-rotate-left nav-fa"></i> Leave History</a>
        <a href="track_status.php" class="nav-link active"><i class="fa-solid fa-calendar-check nav-fa"></i> Track Status</a>
    </nav>
</div>

<div class="dashboard-main">
    <div class="topbar">
        <div class="topbar-name"><?php echo strtoupper($user['full_name']); ?> (STUDENT)</div>
        <div class="topbar-right">
            <span style="color:#ccc; font-size:24px;">|</span>
            <a href="logout.php" class="logout-link"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
        </div>
    </div>

    <div class="dashboard-content">
        <div class="dashboard-card">
            <h1 style="font-size:48px; margin-bottom:20px;">Track Leave Application Status</h1>

            <?php if($leave) { 
                $status = $leave['status'];
                
                // IF STATUS IS CANCELLED
                if($status == "Cancelled"): 
            ?>
                <div class="cancelled-box">
                    <i class="fa-solid fa-circle-info" style="font-size:48px; color:#888; margin-bottom:15px;"></i>
                    <h2 style="color:#888; margin-bottom:10px;">You have cancelled your latest leave application</h2>
                    <p style="font-size:16px; color:#666;">Your leave request has been cancelled and no longer requires approval.</p>
                    <a href="apply_leave.php" class="apply-again-btn"><i class="fa-regular fa-file-lines"></i> Apply Leave Now</a>
                </div>
                
                <!-- Show cancelled application details -->
                <div style="margin-top:30px; border:1px solid #ddd; border-radius:8px; padding:20px; background:white;">
                    <h3 style="margin-bottom:15px;">Cancelled Application Details</h3>
                    <p><strong>Applied Date:</strong> <?php echo date("d M Y", strtotime($leave['created_at'])); ?></p>
                    <p><strong>Leave Type:</strong> <?php echo $leave['leave_type']; ?> Leave</p>
                    <p><strong>Duration:</strong> <?php echo $leave['start_date'] . ' to ' . $leave['end_date']; ?></p>
                    <p><strong>Reason:</strong> <?php echo nl2br(htmlspecialchars($leave['reason'])); ?></p>
                    <p><strong>Status:</strong> <span style="color:#888; font-weight:bold;">Cancelled</span></p>
                </div>

            <?php else: 
                
                // Get approval progress - GROUP BY lecturer
                $approvalQuery = mysqli_query($conn, "
                    SELECT 
                        ls.lecturer_id,
                        u.full_name as lecturer_name,
                        GROUP_CONCAT(DISTINCT ls.subject ORDER BY ls.subject SEPARATOR ', ') as subjects,
                        CASE 
                            WHEN SUM(CASE WHEN ls.status = 'Approved' THEN 1 ELSE 0 END) = COUNT(*) THEN 'Approved'
                            WHEN SUM(CASE WHEN ls.status = 'Rejected' THEN 1 ELSE 0 END) > 0 THEN 'Rejected'
                            ELSE 'Pending'
                        END as lecturer_status,
                        MAX(ls.signed_at) as signed_at
                    FROM lecturer_signatures ls
                    JOIN users u ON ls.lecturer_id = u.id
                    WHERE ls.leave_id = '{$leave['id']}'
                    GROUP BY ls.lecturer_id, u.full_name
                    ORDER BY u.full_name
                ");
                
                $approvals = [];
                $totalApprovals = 0;
                $approvedCount = 0;
                $rejectedCount = 0;
                
                while($app = mysqli_fetch_assoc($approvalQuery)) {
                    $approvals[] = $app;
                    $totalApprovals++;
                    if($app['lecturer_status'] == 'Approved') $approvedCount++;
                    if($app['lecturer_status'] == 'Rejected') $rejectedCount++;
                }
            ?>

            <p style="font-size:18px; color:#666; margin-bottom:10px;">Current Status</p>
            <div style="border:1px solid #ddd; border-radius:8px; padding:15px 20px; margin-bottom:25px; display:flex; align-items:center; gap:12px; font-size:28px; font-weight:600;">
                <?php if($status == "Pending" || $status == "Pending_Approvals"): ?>
                    <div style="width:28px; height:28px; border-radius:50%; background:#ff8c00;"></div>
                    <span style="color:#ff8c00;">PENDING APPROVALS (<?php echo $approvedCount; ?>/<?php echo $totalApprovals; ?> lecturers)</span>
                <?php elseif($status == "Approved"): ?>
                    <div style="width:28px; height:28px; border-radius:50%; background:#39c339;"></div>
                    <span style="color:green;">APPROVED</span>
                <?php elseif($status == "Rejected"): ?>
                    <div style="width:28px; height:28px; border-radius:50%; background:red;"></div>
                    <span style="color:red;">REJECTED</span>
                <?php endif; ?>
            </div>

            <p style="font-size:18px; color:#666; margin-bottom:30px;">
                <?php
                if($status == "Pending" || $status == "Pending_Approvals") {
                    echo "Waiting for lecturer approvals: $approvedCount / $totalApprovals lecturers have fully approved all your subjects.";
                }
                elseif($status == "Approved") {
                    echo "Your leave application has been approved by all lecturers.";
                }
                else {
                    echo "Your leave application has been rejected.";
                }
                ?>
            </p>

            <?php if($status == "Rejected" && !empty($leave['rejection_reason'])) { ?>
                <div style="background:#ffe6e6; border-left:5px solid red; padding:15px 20px; margin-bottom:25px; border-radius:8px;">
                    <strong style="color:red; font-size:18px;">Rejection Reason:</strong>
                    <p style="color:#333; font-size:16px; margin-top:8px;"><?php echo htmlspecialchars($leave['rejection_reason']); ?></p>
                </div>
            <?php } ?>

            <!-- DOWNLOAD FORM BUTTON - ONLY for Long Vacation when Approved -->
            <?php if($leave['leave_type'] == 'Long Vacation' && $status == "Approved" && !empty($leave['form_token'])): ?>
                <div style="margin-bottom:25px;">
                    <a href="download_form.php?token=<?php echo $leave['form_token']; ?>" style="display: inline-flex; align-items: center; gap: 10px; background: #123966; color: white; border: none; padding: 12px 25px; text-decoration: none; border-radius: 25px;">
                        <i class="fa-solid fa-file-pdf"></i> Download Long Vacation Form
                    </a>
                </div>
            <?php endif; ?>

            <!-- APPROVAL PROGRESS TABLE - GROUPED BY LECTURER -->
            <?php if($totalApprovals > 0): ?>
                <div style="margin-top:20px; margin-bottom:25px; background:#f8f9fa; border-radius:10px; padding:20px;">
                    <h3 style="margin-bottom:15px;">Lecturer Approvals</h3>
                    <table class="progress-table">
                        <thead>
                            <tr>
                                <th>Lecturer Name</th>
                                <th>Subject(s)</th>
                                <th>Status</th>
                                <th>Approved Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($approvals as $app): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($app['lecturer_name']); ?></td>
                                    <td><?php echo htmlspecialchars($app['subjects']); ?></td>
                                    <td>
                                        <?php if($app['lecturer_status'] == 'Approved'): ?>
                                            <span style="color:green; font-weight:bold;">✓ Approved</span>
                                        <?php elseif($app['lecturer_status'] == 'Rejected'): ?>
                                            <span style="color:red; font-weight:bold;">✗ Rejected</span>
                                        <?php else: ?>
                                            <span style="color:orange; font-weight:bold;">⏳ Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $app['signed_at'] ? date("d M Y H:i", strtotime($app['signed_at'])) : '-'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <!-- Progress Steps -->
            <div style="margin-top:30px;">
                <h3>Application Progress</h3>
                <div style="display:flex; flex-direction:column; gap:20px; margin-top:20px;">
                    <div style="display:flex; align-items:center; gap:15px;">
                        <i class="fa-solid fa-circle-check" style="font-size:28px; color:#28a745;"></i>
                        <span style="font-size:16px;">Application Submitted</span>
                    </div>
                    
                    <div style="display:flex; align-items:center; gap:15px;">
                        <?php if($approvedCount == $totalApprovals && $totalApprovals > 0): ?>
                            <i class="fa-solid fa-circle-check" style="font-size:28px; color:#28a745;"></i>
                        <?php else: ?>
                            <i class="fa-solid fa-hourglass-half" style="font-size:28px; color:#ff8c00;"></i>
                        <?php endif; ?>
                        <span style="font-size:16px;">Lecturer Approvals</span>
                        <span style="font-size:14px; color:#666;">(<?php echo $approvedCount; ?>/<?php echo $totalApprovals; ?> lecturers)</span>
                    </div>
                    
                    <?php if($leave['leave_type'] == 'Long Vacation'): ?>
                    <div style="display:flex; align-items:center; gap:15px;">
                        <?php if($status == "Approved"): ?>
                            <i class="fa-solid fa-circle-check" style="font-size:28px; color:#28a745;"></i>
                            <span style="font-size:16px;">Form Ready for Download</span>
                        <?php else: ?>
                            <i class="fa-solid fa-lock" style="font-size:28px; color:#999;"></i>
                            <span style="font-size:16px;">Form Ready for Download (Locked)</span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Application Details -->
            <div style="margin-top:30px; border:1px solid #ddd; border-radius:8px; padding:20px; background:white;">
                <h3 style="margin-bottom:15px;">Application Details</h3>
                <p><strong>Applied Date:</strong> <?php echo date("d M Y", strtotime($leave['created_at'])); ?></p>
                <p><strong>Leave Type:</strong> <?php echo $leave['leave_type']; ?> Leave</p>
                <p><strong>Duration:</strong> <?php echo $leave['start_date'] . ' to ' . $leave['end_date']; ?></p>
                <p><strong>Reason:</strong> <?php echo nl2br(htmlspecialchars($leave['reason'])); ?></p>
                <?php if(!empty($leave['student_section'])): ?>
                    <p><strong>Section:</strong> <?php echo htmlspecialchars($leave['student_section']); ?></p>
                <?php endif; ?>
            </div>

            <?php endif; // end else for cancelled ?>
            
            <?php } else { ?>
                <div style="text-align:center; padding:60px 20px;">
                    <i class="fa-solid fa-calendar-check" style="font-size:64px; color:#ccc; margin-bottom:20px;"></i>
                    <p style="font-size:18px; color:#666;">You have not submitted any leave application yet.</p>
                    <a href="apply_leave.php" style="display:inline-block; margin-top:20px; background:#123966; color:white; padding:12px 30px; text-decoration:none; border-radius:30px;">
                        <i class="fa-regular fa-file-lines"></i> Apply Leave Now
                    </a>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

</body>
</html>