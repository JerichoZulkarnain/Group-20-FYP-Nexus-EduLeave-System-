<?php
include 'db_config.php';
include 'log_activity.php';


if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];

logActivity($conn, $user['id'], $user['full_name'], $user['role'], 'Page View', 'Viewed High Absence Students page');

$threshold = $_GET['threshold'] ?? 5;

$studentsQuery = mysqli_query($conn, "
    SELECT u.id, u.full_name, u.nexus_id, u.email,
           COUNT(la.id) as total_leaves,
           SUM(CASE WHEN la.status = 'Approved' OR la.status = 'Approved_Form_Ready' THEN 1 ELSE 0 END) as approved_leaves,
           SUM(CASE WHEN la.status = 'Rejected' THEN 1 ELSE 0 END) as rejected_leaves,
           SUM(CASE WHEN la.status = 'Pending' OR la.status = 'Pending_Approvals' THEN 1 ELSE 0 END) as pending_leaves
    FROM users u
    LEFT JOIN leave_applications la ON u.id = la.student_id
    WHERE u.role = 'student'
    GROUP BY u.id
    HAVING total_leaves >= $threshold
    ORDER BY total_leaves DESC
");

$allStudentsQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'student'");
$allStudents = mysqli_fetch_assoc($allStudentsQuery);

$leaveStatsQuery = mysqli_query($conn, "
    SELECT 
        leave_type,
        COUNT(*) as count
    FROM leave_applications
    GROUP BY leave_type
");

$leaveTypes = [];
$totalLeaves = 0;
while($row = mysqli_fetch_assoc($leaveStatsQuery)) {
    $leaveTypes[] = $row;
    $totalLeaves += $row['count'];
}

$allTypes = ['Medical', 'Emergency', 'Long Vacation'];
foreach($allTypes as $at) {
    $found = false;
    foreach($leaveTypes as $lt) {
        if(trim($lt['leave_type']) == $at) {
            $found = true;
            break;
        }
    }
    if(!$found) {
        $leaveTypes[] = ['leave_type' => $at, 'count' => 0, 'percentage' => 0];
    }
}

$totalLeaves = 0;
foreach($leaveTypes as $lt) {
    $totalLeaves += $lt['count'];
}

foreach($leaveTypes as &$type) {
    $type['percentage'] = $totalLeaves > 0 ? round(($type['count'] / $totalLeaves) * 100, 1) : 0;
}

$uniqueTypes = [];
foreach($leaveTypes as $lt) {
    $uniqueTypes[trim($lt['leave_type'])] = $lt;
}
$leaveTypes = array_values($uniqueTypes);

usort($leaveTypes, function($a, $b) {
    $order = ['Medical' => 1, 'Emergency' => 2, 'Long Vacation' => 3];
    return $order[trim($a['leave_type'])] - $order[trim($b['leave_type'])];
});

$totalQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM leave_applications");
$totalAll = mysqli_fetch_assoc($totalQuery);

$approvedQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM leave_applications WHERE status = 'Approved' OR status = 'Approved_Form_Ready'");
$approvedTotal = mysqli_fetch_assoc($approvedQuery);

$rejectedQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM leave_applications WHERE status = 'Rejected'");
$rejectedTotal = mysqli_fetch_assoc($rejectedQuery);

$pendingQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM leave_applications WHERE status = 'Pending' OR status = 'Pending_Approvals'");
$pendingTotal = mysqli_fetch_assoc($pendingQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>High Absence Students - Nexus</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .section-title {
            font-size: 28px;
            font-weight: 700;
            color: #123966;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid #123966;
        }
        .filter-bar {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            align-items: flex-end;
        }
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .filter-group label {
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }
        .filter-group select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            min-width: 150px;
        }
        .filter-btn {
            background: #123966;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 5px;
            cursor: pointer;
            height: 38px;
        }
        .students-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .students-table th {
            background: #123966;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }
        .students-table td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
        }
        .risk-high {
            background: #dc3545;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }
        .risk-medium {
            background: #ffc107;
            color: #333;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }
        .risk-low {
            background: #28a745;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }
        .stats-container {
            display: flex;
            gap: 20px;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            flex: 1;
            min-width: 200px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stats-card h3 {
            color: #123966;
            margin-bottom: 15px;
            font-size: 18px;
        }
        .stats-number {
            font-size: 36px;
            font-weight: 700;
            color: #123966;
        }
        .leave-type-stats {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .leave-type-stats h3 {
            color: #123966;
            margin-bottom: 15px;
            font-size: 18px;
        }
        .progress-bar-container {
            background: #e9ecef;
            border-radius: 10px;
            height: 30px;
            margin-bottom: 20px;
            overflow: hidden;
        }
        .progress-bar {
            height: 30px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 10px;
            color: white;
            font-size: 13px;
            font-weight: bold;
        }
        .type-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-weight: 500;
        }
        .type-name {
            font-weight: 600;
            color: #123966;
        }
        .view-btn {
            background: #17a2b8;
            color: white;
            border: none;
            padding: 5px 12px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 12px;
        }
        .nav-menu {
            display: flex;
            flex-direction: column;
        }
        .nav-link {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 14px 0;
            text-decoration: none;
            color: black;
            font-size: 22px;
            font-weight: 700;
            cursor: pointer;
        }
        .nav-link:hover {
            color: #7ea6dc;
        }
        .nav-link.active {
            color: #7da2d6;
        }
        .nav-fa {
            width: 32px;
            text-align: center;
            font-size: 24px;
        }
        .submenu {
            margin-left: 45px;
            display: flex;
            flex-direction: column;
            display: none;
        }
        .submenu a {
            padding: 10px 0;
            font-size: 18px;
            font-weight: 600;
            color: #555;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .submenu a:hover {
            color: #7ea6dc;
        }
        .submenu i {
            width: 24px;
            font-size: 16px;
        }
        .active-sub {
            color: #7da2d6 !important;
            font-weight: bold;
        }
        .has-submenu {
            cursor: pointer;
        }
        .chevron {
            margin-left: auto;
            font-size: 14px;
            transition: transform 0.3s;
        }
        .submenu-open .submenu {
            display: flex;
        }
        .submenu-open .chevron {
            transform: rotate(90deg);
        }
        .sidebar-divider {
            height: 1px;
            background: #ddd;
            margin: 15px 0;
        }
        .sidebar-title {
            font-size: 22px;
        }
        .sidebar-date {
            font-size: 16px;
        }
        .dashboard-page {
            margin: 0;
            display: flex;
            height: 100vh;
        }
        .sidebar {
            width: 260px;
            background: white;
            padding: 18px 22px;
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow-y: auto;
            flex-shrink: 0;
        }
        .sidebar-logo {
            width: 140px;
            margin-bottom: 8px;
        }
        .dashboard-main {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .topbar {
            height: 88px;
            background: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            flex-shrink: 0;
        }
        .topbar-name {
            color: #123966;
            font-size: 18px;
            font-weight: 800;
            line-height: 1.2;
            padding-top: 8px;
        }
        .topbar-right {
            display: flex;
            align-items: center;
            gap: 22px;
        }
        .logout-link {
            color: #123966;
            text-decoration: none;
            font-size: 22px;
            font-weight: 800;
        }
        .dashboard-content {
            padding: 18px 28px;
            overflow-y: auto;
        }
        .dashboard-card {
            background: rgba(255,255,255,0.92);
            border-radius: 12px;
            padding: 18px;
            height: auto;
            min-height: calc(100vh - 130px);
            overflow: visible;
        }
        .notification-wrapper {
            position: relative;
        }
        .notification-bell {
            cursor: pointer;
            font-size: 24px;
        }
        .notification-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: red;
            color: white;
            font-size: 10px;
            font-weight: bold;
            padding: 2px 6px;
            border-radius: 50%;
            min-width: 18px;
            text-align: center;
        }
        .notification-dropdown {
            display: none;
            position: absolute;
            top: 40px;
            right: 0;
            width: 350px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            z-index: 1000;
            max-height: 400px;
            overflow-y: auto;
        }
    </style>
</head>
<body class="dashboard-page">

<div class="sidebar">
    <img src="images/logo.png" class="sidebar-logo">
    <div class="sidebar-title">EduLeave System</div>
    <div class="sidebar-date"><?php echo date("l d M Y"); ?></div>
    <nav class="nav-menu">
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
                <a href="manage_students.php"><i class="fa-solid fa-user-graduate"></i> Manage Students</a>
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
                <a href="high_absence_students.php" class="active-sub"><i class="fa-solid fa-chart-line"></i> High Absence Students</a>
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
    </nav>
</div>

<div class="dashboard-main">
    <div class="topbar">
        <div class="topbar-name"><?php echo strtoupper($user['full_name']); ?> (ADMIN)</div>
        <div class="topbar-right">
           
            <span style="color:#ccc; font-size:24px;">|</span>
            <a href="logout.php" class="logout-link"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
        </div>
    </div>

    <div class="dashboard-content">
        <div class="dashboard-card">
            <div class="section-title">
                <i class="fa-solid fa-chart-line"></i> High Absence Students
            </div>

            <form method="GET" class="filter-bar">
                <div class="filter-group">
                    <label>Threshold (Minimum Leaves)</label>
                    <select name="threshold" onchange="this.form.submit()">
                        <option value="3" <?php echo $threshold == 3 ? 'selected' : ''; ?>>3 Leaves</option>
                        <option value="5" <?php echo $threshold == 5 ? 'selected' : ''; ?>>5 Leaves</option>
                        <option value="7" <?php echo $threshold == 7 ? 'selected' : ''; ?>>7 Leaves</option>
                        <option value="10" <?php echo $threshold == 10 ? 'selected' : ''; ?>>10 Leaves</option>
                        <option value="15" <?php echo $threshold == 15 ? 'selected' : ''; ?>>15 Leaves</option>
                    </select>
                </div>
                <div class="filter-group">
                    <button type="submit" class="filter-btn"><i class="fa-solid fa-filter"></i> Apply</button>
                </div>
            </form>

            <table class="students-table">
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>NexusID</th>
                        <th>Total Leave</th>
                        <th>Approved</th>
                        <th>Pending</th>
                        <th>Rejected</th>
                        <th>Risk Level</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php if(mysqli_num_rows($studentsQuery) > 0): ?>
                    <?php while($student = mysqli_fetch_assoc($studentsQuery)): 
                        $total = $student['total_leaves'];
                        $risk = '';
                        $risk_class = '';
                        if($total >= 10) {
                            $risk = 'HIGH';
                            $risk_class = 'risk-high';
                        } elseif($total >= 5) {
                            $risk = 'MEDIUM';
                            $risk_class = 'risk-medium';
                        } else {
                            $risk = 'LOW';
                            $risk_class = 'risk-low';
                        }
                    ?>
                        <tr>
                            <td><?php echo $student['full_name']; ?></td>
                            <td><?php echo $student['nexus_id']; ?></td>
                            <td><?php echo $student['total_leaves']; ?> days</span></td>
                            <td><?php echo $student['approved_leaves']; ?></span></td>
                            <td><?php echo $student['pending_leaves']; ?></span></td>
                            <td><?php echo $student['rejected_leaves']; ?></span></td>
                            <td><span class="<?php echo $risk_class; ?>"><?php echo $risk; ?></span></span></td>
                            <td>
                                <a href="student_details.php?id=<?php echo $student['id']; ?>" class="view-btn"><i class="fa-solid fa-eye"></i> View</a>
                            </span>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align:center; padding:40px;">No students found with <?php echo $threshold; ?> or more leave applications.赶
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>

            <div class="stats-container">
                <div class="stats-card">
                    <h3><i class="fa-solid fa-users"></i> Total Students</h3>
                    <div class="stats-number"><?php echo $allStudents['total']; ?></div>
                </div>
                <div class="stats-card">
                    <h3><i class="fa-solid fa-check-circle"></i> Total Approved Leaves</h3>
                    <div class="stats-number"><?php echo $approvedTotal['total']; ?></div>
                </div>
                <div class="stats-card">
                    <h3><i class="fa-solid fa-clock"></i> Total Pending Leaves</h3>
                    <div class="stats-number"><?php echo $pendingTotal['total']; ?></div>
                </div>
                <div class="stats-card">
                    <h3><i class="fa-solid fa-times-circle"></i> Total Rejected Leaves</h3>
                    <div class="stats-number"><?php echo $rejectedTotal['total']; ?></div>
                </div>
            </div>

            <div class="leave-type-stats">
                <h3><i class="fa-solid fa-chart-pie"></i> Leave Type Distribution</h3>
                
                <?php 
                $colorIndex = 0;
                foreach($leaveTypes as $type): 
                    $typeColor = '';
                    if($type['leave_type'] == 'Medical') {
                        $typeColor = '#28a745';
                    } elseif($type['leave_type'] == 'Emergency') {
                        $typeColor = '#ffc107';
                    } elseif($type['leave_type'] == 'Long Vacation') {
                        $typeColor = '#17a2b8';
                    } else {
                        $typeColor = '#28a745';
                    }
                ?>
                    <div class="type-row">
                        <span class="type-name"><?php echo $type['leave_type']; ?></span>
                        <span><?php echo $type['percentage']; ?>% (<?php echo $type['count']; ?> leaves)</span>
                    </div>
                    <div class="progress-bar-container">
                        <div class="progress-bar" style="width: <?php echo $type['percentage']; ?>%; background: <?php echo $typeColor; ?>;">
                            <?php echo $type['percentage']; ?>%
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <?php if($totalLeaves == 0): ?>
                    <p style="color:#999; text-align:center;">No leave applications found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function toggleSubmenu(element) {
    element.classList.toggle('submenu-open');
}

document.addEventListener('click', function(event) {
    const wrapper = document.querySelector('.notification-wrapper');
    if(wrapper && wrapper.contains(event.target)) {
        return;
    }
    const dropdown = document.getElementById('notificationDropdown');
    if(dropdown) {
        dropdown.style.display = 'none';
    }
});
</script>

</body>
</html>