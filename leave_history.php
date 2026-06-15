<?php
include 'db_config.php';

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];

// ==========================================
// CANCEL LEAVE - UPDATE STATUS TO CANCELLED
// ==========================================

if(isset($_GET['cancel_id'])) {
    $cancel_id = $_GET['cancel_id'];
    // Only cancel if status is Pending or Pending_Approvals (not yet approved)
    mysqli_query($conn, "UPDATE leave_applications SET status = 'Cancelled' WHERE id = '$cancel_id' AND (status = 'Pending' OR status = 'Pending_Approvals')");
    header("Location: leave_history.php");
    exit();
}

// ==========================================
// GET LEAVE HISTORY FROM DATABASE
// ==========================================

$student_id = $user['id'];

$sql = "SELECT * FROM leave_applications
WHERE student_id = '$student_id'
ORDER BY created_at DESC";

$result = mysqli_query($conn, $sql);

// ==========================================
// SEARCH + FILTER
// ==========================================

$search = "";
$statusFilter = "All";

if(isset($_GET['search'])) {
    $search = strtolower(trim($_GET['search']));
}

if(isset($_GET['status'])) {
    $statusFilter = $_GET['status'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Leave History - Nexus</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
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
        <a href="leave_history.php" class="nav-link active"><i class="fa-solid fa-clock-rotate-left nav-fa"></i> Leave History</a>
        <a href="track_status.php" class="nav-link"><i class="fa-solid fa-calendar-check nav-fa"></i> Track Status</a>
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
            <h1 class="history-title">Leave Application History</h1>

            <?php if(mysqli_num_rows($result) > 0) { ?>

            <p class="history-subtitle">View all your submitted leave applications below.</p>

            <form method="GET">
                <div class="history-top">
                    <div>
                        <label class="history-label">Filter by Status:</label>
                        <select name="status" class="history-select" onchange="this.form.submit()">
                            <option value="All">All</option>
                            <option value="Pending" <?php if($statusFilter == "Pending") echo "selected"; ?>>Pending</option>
                            <option value="Approved" <?php if($statusFilter == "Approved") echo "selected"; ?>>Approved</option>
                            <option value="Rejected" <?php if($statusFilter == "Rejected") echo "selected"; ?>>Rejected</option>
                            <option value="Cancelled" <?php if($statusFilter == "Cancelled") echo "selected"; ?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="search-box">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" name="search" placeholder="Search" class="history-search" value="<?php echo $search; ?>">
                    </div>
                </div>
            </form>

            <div class="history-table-box">
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Leave Type</th>
                            <th>Date Applied</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Duration</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $no = 1;
                    while($leave = mysqli_fetch_assoc($result)) {
                        if($statusFilter != "All" && $leave['status'] != $statusFilter) continue;
                        
                        $combinedText = strtolower($leave['leave_type'] . $leave['duration'] . $leave['status'] . $leave['reason']);
                        if($search != "" && strpos($combinedText, $search) === false) continue;
                        
                        $status_lower = strtolower($leave['status']);
                        
                        // Check approval counts for Long Vacation
                        $approvedCount = 0;
                        $totalApprovals = 0;
                        if($leave['leave_type'] == 'Long Vacation' && ($status_lower == "pending" || $status_lower == "pending_approvals" || $status_lower == "partial_signatures")) {
                            $approvalQuery = mysqli_query($conn, "
                                SELECT COUNT(DISTINCT lecturer_id) as total, 
                                       SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as approved
                                FROM lecturer_signatures 
                                WHERE leave_id = '{$leave['id']}'
                            ");
                            $approvalData = mysqli_fetch_assoc($approvalQuery);
                            $totalApprovals = $approvalData['total'];
                            $approvedCount = $approvalData['approved'];
                        }
                        
                        // Determine status display text
                        $status_text = '';
                        if($status_lower == "pending" || $status_lower == "pending_approvals") {
                            if($leave['leave_type'] == 'Long Vacation' && $totalApprovals > 0) {
                                $status_text = 'Pending (' . $approvedCount . '/' . $totalApprovals . ' approvals)';
                            } else {
                                $status_text = 'Pending';
                            }
                        }
                        else if($status_lower == "partial_signatures") {
                            $status_text = 'Pending (' . $approvedCount . '/' . $totalApprovals . ' approvals)';
                        }
                        else if($status_lower == "approved_form_ready") {
                            $status_text = 'Approved';
                        }
                        else if($status_lower == "approved") {
                            $status_text = 'Approved';
                        }
                        else if($status_lower == "rejected") {
                            $status_text = 'Rejected';
                        }
                        else if($status_lower == "cancelled") {
                            $status_text = 'Cancelled';
                        }
                        else {
                            $status_text = $leave['status'];
                        }
                    ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo $leave['leave_type']; ?></td>
                            <td><?php echo date("d M Y", strtotime($leave['created_at'])); ?></td>
                            <td><?php echo date("d M Y", strtotime($leave['start_date'])); ?></td>
                            <td><?php echo date("d M Y", strtotime($leave['end_date'])); ?></td>
                            <td><?php echo $leave['duration']; ?></td>
                            <td style="max-width:200px;"><?php echo substr($leave['reason'], 0, 60); ?><?php echo strlen($leave['reason']) > 60 ? '...' : ''; ?></td>
                            <td>
                                <?php
                                if($status_lower == "pending" || $status_lower == "pending_approvals" || $status_lower == "partial_signatures") {
                                    echo '<span class="pending-text">' . $status_text . '</span>';
                                }
                                else if($status_lower == "approved" || $status_lower == "approved_form_ready") {
                                    echo '<span class="approved-text">' . $status_text . '</span>';
                                }
                                else if($status_lower == "rejected") {
                                    echo '<span class="rejected-text">' . $status_text . '</span>';
                                }
                                else if($status_lower == "cancelled") {
                                    echo '<span class="cancelled-text" style="color:#888; font-weight:bold;">' . $status_text . '</span>';
                                }
                                else {
                                    echo '<span class="pending-text">' . $status_text . '</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                if(($status_lower == "pending" || $status_lower == "pending_approvals" || $status_lower == "partial_signatures") && $status_lower != "cancelled") {
                                ?>
                                    <span class="cancel-text" onclick="openCancelPopup(<?php echo $leave['id']; ?>)">Cancel</span>
                                <?php
                                }
                                else {
                                ?>
                                    <span class="view-text" onclick="openViewPopup(
'<?php echo $leave['leave_type']; ?>',
'<?php echo date("d M Y", strtotime($leave['created_at'])); ?>',
'<?php echo $leave['duration']; ?>',
'<?php echo $status_text; ?>'
)">View</span>
                                <?php
                                }
                                ?>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>

            <?php } else { ?>

            <p class="empty-title">No leave application history available.</p>
            <div class="empty-box">
                <p class="empty-text">You have not submitted any leave application yet.</p>
                <a href="apply_leave.php" class="apply-now-btn"><i class="fa-regular fa-file-lines"></i> Apply Leave Now</a>
            </div>

            <?php } ?>
        </div>
    </div>
</div>

<!-- CANCEL POPUP -->
<div id="cancelPopup" class="popup-overlay">
    <div class="popup-box">
        <p style="font-size:18px; text-align:center; margin-bottom:30px;">Are you sure you want to cancel this leave request?</p>
        <div class="popup-buttons">
            <a id="confirmCancelBtn" href="" class="popup-btn">Yes</a>
            <button onclick="closeCancelPopup()" class="popup-btn">No</button>
        </div>
    </div>
</div>

<!-- VIEW POPUP -->
<div id="viewPopup" class="popup-overlay">
    <div class="popup-box" style="width:500px;">
        <h1 style="margin-top:0; font-size:24px;">Leave Application Details</h1>
        <div style="margin-top:25px; line-height:1.8; font-size:16px;">
            <div>Leave Type: <span id="viewLeaveType"></span></div>
            <div>Applied Date: <span id="viewDate"></span></div>
            <div>Duration: <span id="viewDuration"></span></div>
            <div>Status: <span id="viewStatus"></span></div>
        </div>
        <div style="text-align:right; margin-top:25px;">
            <button onclick="closeViewPopup()" class="popup-btn">Close</button>
        </div>
    </div>
</div>

<script>
function openCancelPopup(id) {
    document.getElementById("cancelPopup").style.display = "flex";
    document.getElementById("confirmCancelBtn").href = "leave_history.php?cancel_id=" + id;
}
function closeCancelPopup() {
    document.getElementById("cancelPopup").style.display = "none";
}
function openViewPopup(type, date, duration, status) {
    document.getElementById("viewPopup").style.display = "flex";
    document.getElementById("viewLeaveType").innerHTML = type;
    document.getElementById("viewDate").innerHTML = date;
    document.getElementById("viewDuration").innerHTML = duration;
    
    let color = "orange";
    let statusLower = status.toLowerCase();
    if(statusLower == "approved") {
        color = "limegreen";
    }
    else if(statusLower == "rejected") {
        color = "red";
    }
    else if(statusLower == "cancelled") {
        color = "#888";
    }
    
    document.getElementById("viewStatus").innerHTML = `<span style="display:inline-block; width:14px; height:14px; border-radius:50%; background:${color}; margin-right:8px;"></span>` + status;
}
function closeViewPopup() {
    document.getElementById("viewPopup").style.display = "none";
}
</script>

</body>
</html>