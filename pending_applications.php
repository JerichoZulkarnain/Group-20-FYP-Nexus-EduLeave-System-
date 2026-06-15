<?php
include 'db_config.php';

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'lecturer') {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];
$lecturer_id = $user['id'];

if(isset($_POST['reject_submit'])) {
    $id = $_POST['leave_id'];
    $reason = mysqli_real_escape_string($conn, $_POST['rejection_reason']);
    mysqli_query($conn, "UPDATE leave_applications SET status='Rejected', rejection_reason='$reason' WHERE id='$id'");
    header("Location: pending_applications.php");
    exit();
}

$search = "";
$statusFilter = "All";

if(isset($_GET['search'])) {
    $search = trim($_GET['search']);
}
if(isset($_GET['status'])) {
    $statusFilter = $_GET['status'];
}

// Get applications where this lecturer is assigned
$sql = "SELECT la.*, u.full_name, u.nexus_id
FROM leave_applications la
INNER JOIN users u ON la.student_id = u.id
INNER JOIN student_lecturers sl ON u.id = sl.student_id
WHERE sl.lecturer_id = '$lecturer_id'
ORDER BY la.created_at DESC";

$result = mysqli_query($conn, $sql);

// Count statistics
$pendingQuery = mysqli_query($conn, "
    SELECT COUNT(*) as total
    FROM leave_applications la
    INNER JOIN student_lecturers sl ON la.student_id = sl.student_id
    WHERE sl.lecturer_id = '$lecturer_id' 
    AND la.status != 'Approved' AND la.status != 'Rejected'
");
$pendingData = mysqli_fetch_assoc($pendingQuery);
$totalPending = $pendingData['total'];

$approvedQuery = mysqli_query($conn, "
    SELECT COUNT(*) as total
    FROM leave_applications la
    INNER JOIN student_lecturers sl ON la.student_id = sl.student_id
    WHERE sl.lecturer_id = '$lecturer_id' AND la.status = 'Approved'
");
$approvedData = mysqli_fetch_assoc($approvedQuery);
$totalApproved = $approvedData['total'];

$rejectedQuery = mysqli_query($conn, "
    SELECT COUNT(*) as total
    FROM leave_applications la
    INNER JOIN student_lecturers sl ON la.student_id = sl.student_id
    WHERE sl.lecturer_id = '$lecturer_id' AND la.status = 'Rejected'
");
$rejectedData = mysqli_fetch_assoc($rejectedQuery);
$totalRejected = $rejectedData['total'];

$totalQuery = mysqli_query($conn, "
    SELECT COUNT(*) as total
    FROM leave_applications la
    INNER JOIN student_lecturers sl ON la.student_id = sl.student_id
    WHERE sl.lecturer_id = '$lecturer_id'
");
$totalData = mysqli_fetch_assoc($totalQuery);
$totalAll = $totalData['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pending Applications - Nexus</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="dashboard-page">

<div class="sidebar">

    <img src="images/logo.png" class="sidebar-logo">

    <div class="sidebar-title">
        EduLeave System
    </div>

    <div class="sidebar-date">
        <?php echo date("l d M Y"); ?>
    </div>

    <nav class="nav-menu">

        <a href="dashboard_lecturer.php" class="nav-link">
            <i class="fa-solid fa-chart-column nav-fa"></i>
            Dashboard
        </a>

        <a href="profile_lecturer.php" class="nav-link">
            <i class="fa-solid fa-user nav-fa"></i>
            Profile
        </a>

        <br>

        <a href="pending_applications.php" class="nav-link active">
            <i class="fa-solid fa-envelope nav-fa"></i>
            Pending Applications
        </a>

        <a href="search_students.php" class="nav-link">
            <i class="fa-solid fa-magnifying-glass nav-fa"></i>
            Search Students
        </a>

    </nav>

</div>

<div class="dashboard-main">
    <div class="topbar">
        <div class="topbar-name"><?php echo strtoupper($user['full_name']); ?> (LECTURER)</div>
        <div class="topbar-right">
            <span style="color:#ccc; font-size:24px;">|</span>
            <a href="logout.php" class="logout-link"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
        </div>
    </div>

    <div class="dashboard-content">
        <div class="dashboard-card">
            <h1 style="font-size:48px; margin-bottom:5px;">Pending Leave Application</h1>
            <p style="font-size:18px; color:#666;">Review and process student leave requests.</p>

            <div style="display:flex; gap:20px; margin:20px 0; padding:15px; background:#f0f0f0; border-radius:10px;">
                <div><strong>Pending:</strong> <?php echo $totalPending; ?></div>
                <div><strong>Approved:</strong> <?php echo $totalApproved; ?></div>
                <div><strong>Rejected:</strong> <?php echo $totalRejected; ?></div>
                <div><strong>Total:</strong> <?php echo $totalAll; ?></div>
            </div>

            <form method="GET">
                <div style="display:flex; gap:15px; margin:25px 0;">
                    <div style="position:relative; flex:1;">
                        <i class="fa-solid fa-magnifying-glass" style="position:absolute; left:15px; top:14px; color:#777;"></i>
                        <input type="text" name="search" placeholder="Search Students" value="<?php echo $search; ?>" style="width:100%; padding:12px 15px 12px 45px; border:1px solid #ccc; border-radius:8px; font-size:18px;">
                    </div>
                    <select name="status" onchange="this.form.submit()" style="width:220px; padding:12px; border-radius:8px; border:1px solid #ccc; font-size:17px;">
                        <option value="All">All Status</option>
                        <option value="Pending" <?php if($statusFilter == "Pending") echo "selected"; ?>>Pending</option>
                        <option value="Approved" <?php if($statusFilter == "Approved") echo "selected"; ?>>Approved</option>
                        <option value="Rejected" <?php if($statusFilter == "Rejected") echo "selected"; ?>>Rejected</option>
                    </select>
                </div>
            </form>

            <div style="border:1px solid #dcdcdc; border-radius:10px; overflow:auto;">
                <table style="width:100%; border-collapse:collapse; background:white;">
                    <thead>
                        <tr style="background:#f8f8f8;">
                            <th style="padding:12px; border:1px solid #ddd;">Name</th>
                            <th style="padding:12px; border:1px solid #ddd;">ID</th>
                            <th style="padding:12px; border:1px solid #ddd;">Leave Type</th>
                            <th style="padding:12px; border:1px solid #ddd;">Start Date</th>
                            <th style="padding:12px; border:1px solid #ddd;">End Date</th>
                            <th style="padding:12px; border:1px solid #ddd;">Reason</th>
                            <th style="padding:12px; border:1px solid #ddd;">Subjects</th>
                            <th style="padding:12px; border:1px solid #ddd;">MC</th>
                            <th style="padding:12px; border:1px solid #ddd;">Evidence</th>
                            <th style="padding:12px; border:1px solid #ddd;">Approval Progress</th>
                            <th style="padding:12px; border:1px solid #ddd;">Status</th>
                            <th style="padding:12px; border:1px solid #ddd;">Action</th>
                         </tr>
                    </thead>
                    <tbody>
                    <?php
                    while($row = mysqli_fetch_assoc($result)) {
                        $studentId = $row['student_id'];
                        $combined = strtolower($row['full_name'] . $row['nexus_id']);
                        if($search != "" && strpos($combined, strtolower($search)) === false) continue;
                        if($statusFilter != "All" && $row['status'] != $statusFilter) continue;
                        
                        // Get subjects grouped by lecturer
                        $subjectsGroupQuery = mysqli_query($conn, "
                            SELECT ls.lecturer_id, GROUP_CONCAT(DISTINCT ls.subject SEPARATOR ', ') as subjects
                            FROM lecturer_signatures ls
                            WHERE ls.leave_id = '{$row['id']}' AND ls.subject != ''
                            GROUP BY ls.lecturer_id
                        ");
                        $subjectsOutput = [];
                        while($group = mysqli_fetch_assoc($subjectsGroupQuery)) {
                            $lecNameQuery = mysqli_query($conn, "SELECT full_name FROM users WHERE id = '{$group['lecturer_id']}'");
                            $lecName = mysqli_fetch_assoc($lecNameQuery);
                            $subjectsOutput[] = ($lecName ? $lecName['full_name'] : 'Unknown') . ': ' . $group['subjects'];
                        }
                        
                        // Get evidence
                       $evidenceQuery = mysqli_query($conn, "SELECT file_path FROM evidence WHERE leave_id = '{$row['id']}' ORDER BY uploaded_at DESC LIMIT 1");
                        $evidence = mysqli_fetch_assoc($evidenceQuery);
                        
                        // Get approval progress for this application
                        $progressQuery = mysqli_query($conn, "
                            SELECT COUNT(DISTINCT lecturer_id) as total_lecturers,
                                   COUNT(DISTINCT CASE WHEN status = 'Approved' THEN lecturer_id END) as approved_lecturers
                            FROM lecturer_signatures ls
                            WHERE ls.leave_id = '{$row['id']}'
                        ");
                        $progress = mysqli_fetch_assoc($progressQuery);
                        $totalLecturers = $progress['total_lecturers'];
                        $approvedLecturers = $progress['approved_lecturers'];
                        
                        // Check if current lecturer already approved
                        $alreadyApproved = false;
                        $checkOwn = mysqli_query($conn, "SELECT status FROM lecturer_signatures WHERE leave_id = '{$row['id']}' AND lecturer_id = '$lecturer_id' LIMIT 1");
                        $ownStatus = mysqli_fetch_assoc($checkOwn);
                        if($ownStatus && $ownStatus['status'] == 'Approved') {
                            $alreadyApproved = true;
                        }
                    ?>
                        <tr>
                            <td style="padding:12px; border:1px solid #ddd;"><?php echo strtoupper($row['full_name']); ?></td>
                            <td style="padding:12px; border:1px solid #ddd;"><?php echo strtoupper($row['nexus_id']); ?></td>
                            <td style="padding:12px; border:1px solid #ddd;"><?php echo $row['leave_type']; ?></td>
                            <td style="padding:12px; border:1px solid #ddd;"><?php echo date("d M Y", strtotime($row['start_date'])); ?></td>
                            <td style="padding:12px; border:1px solid #ddd;"><?php echo date("d M Y", strtotime($row['end_date'])); ?></td>
                            <td style="padding:12px; border:1px solid #ddd; max-width:200px;"><?php echo substr($row['reason'], 0, 50); ?></td>
                            <td style="padding:12px; border:1px solid #ddd;">
                                <?php echo !empty($subjectsOutput) ? implode('<br>', $subjectsOutput) : '-'; ?>
                             </td>
                            <td style="padding:12px; border:1px solid #ddd;">
                                <?php if(!empty($row['mc_file'])): ?>
                                    <a href="<?php echo $row['mc_file']; ?>" target="_blank" style="color:#2c6cff;">View MC</a>
                                <?php else: ?>
                                    <span style="color:#999;">No MC</span>
                                <?php endif; ?>
                             </td>
                            <td style="padding:12px; border:1px solid #ddd;">
                                <?php if(!empty($evidence['file_path'])): ?>
                                    <a href="<?php echo $evidence['file_path']; ?>" target="_blank" style="color:#2c6cff;">View Evidence</a>
                                <?php else: ?>
                                    <span style="color:#999;">No Evidence</span>
                                <?php endif; ?>
                             </td>
                            <td style="padding:12px; border:1px solid #ddd;">
                                <?php if($totalLecturers > 0): ?>
                                    <div style="width:100%; background:#e9ecef; border-radius:10px; height:20px; overflow:hidden;">
                                        <div style="width:<?php echo ($approvedLecturers / $totalLecturers * 100); ?>%; background:#28a745; height:20px; border-radius:10px; text-align:center; color:white; font-size:11px; line-height:20px;">
                                            <?php echo $approvedLecturers . '/' . $totalLecturers; ?>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <span style="color:#999;">No lecturers</span>
                                <?php endif; ?>
                             </td>
                            <td style="padding:12px; border:1px solid #ddd;">
                                <?php
                                if($row['status'] == "Approved") {
                                    echo "<span style='color:green; font-weight:bold;'>Approved</span>";
                                } elseif($row['status'] == "Rejected") {
                                    echo "<span style='color:red; font-weight:bold;'>Rejected</span>";
                                } else {
                                    echo "<span style='color:#ff8c00; font-weight:bold;'>Pending</span>";
                                }
                                ?>
                             </td>
                            <td style="padding:12px; border:1px solid #ddd;">
                                <?php if($row['status'] != "Approved" && $row['status'] != "Rejected"): ?>
                                    <?php if($alreadyApproved): ?>
                                        <span style="color:green; font-weight:bold;">✓ Already Approved</span>
                                    <?php else: ?>
                                        <span onclick="openApprovePopup(<?php echo $row['id']; ?>, '<?php echo strtoupper($row['full_name']); ?>')" style="color:green; cursor:pointer; margin-right:10px;">Approve</span>
                                        <span onclick="openRejectPopupForApplication(<?php echo $row['id']; ?>, '<?php echo strtoupper($row['full_name']); ?>')" style="color:red; cursor:pointer;">Reject</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span style="color:#999;">-</span>
                                <?php endif; ?>
                             </td>
                         </tr>
                    <?php } ?>
                    </tbody>
                 </table>
            </div>
        </div>
    </div>
</div>

<div id="approvePopup" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.4); justify-content:center; align-items:center; z-index:999;">
    <div style="background:white; width:400px; padding:35px; border-radius:12px; text-align:center;">
        <p style="font-size:20px;">Approve leave application for</p>
        <p id="approveStudentName" style="color:#19b219; font-size:28px; font-weight:bold; margin-bottom:30px;"></p>
        <div style="display:flex; justify-content:center; gap:20px;">
            <a id="approveBtn" href="" style="padding:10px 28px; background:#28a745; color:white; border:none; border-radius:30px; text-decoration:none;">Yes, Approve</a>
            <button onclick="closeApprovePopup()" style="padding:10px 28px; border:1px solid #999; border-radius:30px; background:white; cursor:pointer;">Cancel</button>
        </div>
    </div>
</div>

<div id="rejectPopup" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.4); justify-content:center; align-items:center; z-index:999;">
    <div style="background:white; width:450px; padding:35px; border-radius:12px;">
        <p style="font-size:20px; text-align:center; margin-bottom:10px;">Reject this application?</p>
        <p id="rejectStudentName" style="color:red; font-size:28px; font-weight:bold; text-align:center; margin-bottom:20px;"></p>
        <form method="POST" action="">
            <input type="hidden" name="leave_id" id="rejectLeaveId">
            <label style="display:block; margin-bottom:8px; font-weight:600;">Reason for Rejection:</label>
            <textarea name="rejection_reason" rows="4" style="width:100%; border:1px solid #ccc; border-radius:8px; padding:10px; font-size:16px; font-family:'Baloo 2', cursive;" required placeholder="Please provide a reason for rejecting this application..."></textarea>
            <div style="display:flex; justify-content:center; gap:20px; margin-top:25px;">
                <button type="submit" name="reject_submit" style="padding:10px 28px; background:red; color:white; border:none; border-radius:30px; cursor:pointer;">Reject</button>
                <button type="button" onclick="closeRejectPopup()" style="padding:10px 28px; border:1px solid #999; border-radius:30px; background:white; cursor:pointer;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openApprovePopup(leaveId, studentName) {
    fetch('get_signature_id.php?leave_id=' + leaveId)
        .then(response => response.json())
        .then(data => {
            if(data.signature_id) {
                document.getElementById("approvePopup").style.display = "flex";
                document.getElementById("approveStudentName").innerHTML = studentName;
                document.getElementById("approveBtn").href = "process_approval.php?id=" + data.signature_id + "&action=approve&leave_id=" + leaveId;
            } else {
                alert("Error: Could not find your approval request for leave ID: " + leaveId + ". Please make sure you are assigned to this student.");
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("Error: " + error);
        });
}
function closeApprovePopup() {
    document.getElementById("approvePopup").style.display = "none";
}
function openRejectPopupForApplication(id, name) {
    document.getElementById("rejectPopup").style.display = "flex";
    document.getElementById("rejectStudentName").innerHTML = name;
    document.getElementById("rejectLeaveId").value = id;
}
function closeRejectPopup() {
    document.getElementById("rejectPopup").style.display = "none";
}
</script>

</body>
</html>