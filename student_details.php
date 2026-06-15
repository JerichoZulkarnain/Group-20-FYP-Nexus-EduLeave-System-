<?php
include 'db_config.php';
include 'notification_helper.php';

// Allow both lecturer and admin to view
if(!isset($_SESSION['user']) || ($_SESSION['user']['role'] !== 'lecturer' && $_SESSION['user']['role'] !== 'admin')) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];

if(!isset($_GET['id'])) {
    header("Location: search_students.php");
    exit();
}

$student_id = $_GET['id'];

// Get referrer page to know where user came from
$referrer = $_SERVER['HTTP_REFERER'] ?? '';
$from_page = '';

if(strpos($referrer, 'high_absence_students.php') !== false) {
    $from_page = 'high_absence';
} elseif(strpos($referrer, 'manage_students.php') !== false) {
    $from_page = 'manage_students';
} elseif(strpos($referrer, 'search_students.php') !== false) {
    $from_page = 'search_students';
} else {
    $from_page = 'manage_students';
}

// =====================================
// GET STUDENT DETAILS
// =====================================

$studentQuery = mysqli_query($conn,
"SELECT * FROM users
WHERE id = '$student_id'
AND role = 'student'");

$student = mysqli_fetch_assoc($studentQuery);

if(!$student) {
    header("Location: search_students.php");
    exit();
}

// =====================================
// GET LEAVE HISTORY
// =====================================

$leaveQuery = mysqli_query($conn,
"SELECT * FROM leave_applications
WHERE student_id = '$student_id'
ORDER BY created_at DESC");

// =====================================
// SUMMARY
// =====================================

$totalLeave = mysqli_num_rows($leaveQuery);

mysqli_data_seek($leaveQuery, 0);

$pending = 0;
$approved = 0;
$rejected = 0;

while($row = mysqli_fetch_assoc($leaveQuery)) {
    if($row['status'] == "Pending" || $row['status'] == "Pending_Approvals") {
        $pending++;
    }
    else if($row['status'] == "Approved" || $row['status'] == "Approved_Form_Ready") {
        $approved++;
    }
    else if($row['status'] == "Rejected") {
        $rejected++;
    }
}

mysqli_data_seek($leaveQuery, 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Details - Nexus</title>
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

        <?php if($from_page == 'search_students'): ?>
            <!-- SIDEBAR UNTUK LECTURER (dari search_students.php) -->
            <a href="dashboard_lecturer.php" class="nav-link">
                <i class="fa-solid fa-chart-column nav-fa"></i>
                Dashboard
            </a>

            <a href="profile_lecturer.php" class="nav-link">
                <i class="fa-solid fa-user nav-fa"></i>
                Profile
            </a>

            <br>

            <a href="pending_applications.php" class="nav-link">
                <i class="fa-solid fa-envelope nav-fa"></i>
                Pending Applications
            </a>

            <a href="search_students.php" class="nav-link active">
                <i class="fa-solid fa-magnifying-glass nav-fa"></i>
                Search Students
            </a>

        <?php else: ?>
            <!-- SIDEBAR UNTUK ADMIN (dari manage_students.php atau high_absence_students.php) -->
            <a href="dashboard_admin.php" class="nav-link">
                <i class="fa-solid fa-chart-column nav-fa"></i> Dashboard
            </a>

            <a href="profile_admin.php" class="nav-link">
                <i class="fa-solid fa-user nav-fa"></i> Profile
            </a>

            <div class="sidebar-divider"></div>

            <div class="nav-item has-submenu" onclick="toggleSubmenu(this)">
                <div class="nav-link">
                    <i class="fa-solid fa-users nav-fa"></i> User Management
                    <i class="fa-solid fa-chevron-right chevron"></i>
                </div>
                <div class="submenu">
                    <a href="manage_students.php" class="active-sub"><i class="fa-solid fa-user-graduate"></i> Manage Students</a>
                    <a href="assign_lecturer.php"><i class="fa-solid fa-chalkboard-user"></i> Manage Lecturers</a>
                </div>
            </div>

            <div class="nav-item has-submenu submenu-open" onclick="toggleSubmenu(this)">
                <div class="nav-link">
                    <i class="fa-solid fa-file-alt nav-fa"></i> Leave Monitoring
                    <i class="fa-solid fa-chevron-right chevron"></i>
                </div>
                <div class="submenu">
                    <a href="all_applications.php"><i class="fa-solid fa-list"></i> All Applications</a>
                    <?php if($from_page == 'high_absence'): ?>
                        <a href="high_absence_students.php" class="active-sub"><i class="fa-solid fa-chart-line"></i> High Absence Students</a>
                    <?php else: ?>
                        <a href="high_absence_students.php"><i class="fa-solid fa-chart-line"></i> High Absence Students</a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="nav-item has-submenu" onclick="toggleSubmenu(this)">
                <div class="nav-link">
                    <i class="fa-solid fa-chart-simple nav-fa"></i> Reports
                    <i class="fa-solid fa-chevron-right chevron"></i>
                </div>
                <div class="submenu">
                    <a href="absence_summary.php"><i class="fa-solid fa-file-export"></i> Absence Summary</a>
                    <a href="analytics.php"><i class="fa-solid fa-chart-pie"></i> Analytics</a>
                </div>
            </div>

            <div class="nav-item has-submenu" onclick="toggleSubmenu(this)">
                <div class="nav-link">
                    <i class="fa-solid fa-gear nav-fa"></i> System
                    <i class="fa-solid fa-chevron-right chevron"></i>
                </div>
                <div class="submenu">
                    <a href="activity_logs.php"><i class="fa-solid fa-clock"></i> Activity Logs</a>
                </div>
            </div>
        <?php endif; ?>

    </nav>

</div>

<div class="dashboard-main">
    <div class="topbar">
        <div class="topbar-name"><?php echo strtoupper($user['full_name']); ?> (<?php echo strtoupper($user['role']); ?>)</div>
        <div class="topbar-right">
            <span style="color:#ccc; font-size:24px;">|</span>
            <a href="logout.php" class="logout-link"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
        </div>
    </div>

    <div class="dashboard-content">
        <div class="dashboard-card">
            <h1 style="font-size:48px; margin-bottom:20px;">Student Details</h1>

            <div class="profile-layout">
                <div class="profile-icon">
                    <i class="fa-regular fa-circle-user" style="font-size:180px; color:#555;"></i>
                </div>
                <div class="profile-info">
                    <p><strong>Full Name:</strong> <?php echo strtoupper($student['full_name']); ?></p>
                    <p><strong>NexusID:</strong> <?php echo strtoupper($student['nexus_id']); ?></p>
                    <p><strong>Email:</strong> <?php echo $student['email']; ?></p>
                    <p><strong>Username:</strong> <?php echo $student['username']; ?></p>
                    <p><strong>Status:</strong> 
                        <span class="status-badge <?php echo ($student['status'] ?? 'active') == 'active' ? 'status-active' : 'status-inactive'; ?>">
                            <?php echo ($student['status'] ?? 'active') == 'active' ? 'Active' : 'Inactive'; ?>
                        </span>
                    </p>
                </div>
            </div>

            <h2 style="font-size:42px; margin-top:20px;">Leave Summary</h2>
            <div class="profile-badge" style="display:block; padding:20px; line-height:2;">
                Total Leave: <?php echo $totalLeave; ?><br>
                Pending: <?php echo $pending; ?><br>
                Approved: <?php echo $approved; ?><br>
                Rejected: <?php echo $rejected; ?>
            </div>

            <h2 style="font-size:42px; margin-top:20px;">Leave History</h2>
            <div class="history-table-box">
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>Date Applied</th>
                            <th>Leave Period</th>
                            <th>Leave Type</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Rejection Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while($leave = mysqli_fetch_assoc($leaveQuery)) { ?>
                        <tr>
                            <td><?php echo date("d M Y", strtotime($leave['created_at'])); ?></td>
                            <td><?php echo date("j M Y", strtotime($leave['start_date'])) . " - " . date("j M Y", strtotime($leave['end_date'])); ?></td>
                            <td><?php echo $leave['leave_type']; ?></td>
                            <td style="max-width:300px;"><?php echo nl2br($leave['reason']); ?></td>
                            <td>
                                <?php
                                if($leave['status'] == "Approved" || $leave['status'] == "Approved_Form_Ready") {
                                    echo "<span style='color:limegreen; font-weight:bold;'>Approved</span>";
                                }
                                else if($leave['status'] == "Rejected") {
                                    echo "<span style='color:red; font-weight:bold;'>Rejected</span>";
                                }
                                else if($leave['status'] == "Pending_Approvals") {
                                    echo "<span style='color:#ff8c00; font-weight:bold;'>Pending Approvals</span>";
                                }
                                else {
                                    echo "<span style='color:orange; font-weight:bold;'>Pending</span>";
                                }
                                ?>
                            </td>
                            <td style="max-width:250px;">
                                <?php
                                if($leave['status'] == "Rejected" && !empty($leave['rejection_reason'])) {
                                    echo "<span style='color:red;'>" . htmlspecialchars($leave['rejection_reason']) . "</span>";
                                } else {
                                    echo "-";
                                }
                                ?>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
            
            <div style="margin-top:20px; text-align:right;">
                <?php if($from_page == 'high_absence'): ?>
                    <a href="high_absence_students.php" class="profile-btn" style="background:#6c757d; color:white;">Back to High Absence</a>
                <?php elseif($from_page == 'search_students'): ?>
                    <a href="search_students.php" class="profile-btn" style="background:#6c757d; color:white;">Back to Search</a>
                <?php else: ?>
                    <a href="manage_students.php" class="profile-btn" style="background:#6c757d; color:white;">Back to Students</a>
                <?php endif; ?>
                
                <?php if($user['role'] == 'admin'): ?>
                    <a href="edit_student.php?id=<?php echo $student_id; ?>" class="profile-btn" style="background:#ffc107; color:black;">Edit Student</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function toggleSubmenu(element) {
    element.classList.toggle('submenu-open');
}
</script>

</body>
</html>