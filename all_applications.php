<?php
include 'db_config.php';
include 'log_activity.php';


if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];

logActivity($conn, $user['id'], $user['full_name'], $user['role'], 'Page View', 'Viewed All Applications page');

$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$status_filter = $_GET['status'] ?? 'all';
$leave_type_filter = $_GET['leave_type'] ?? 'all';

$sql = "SELECT la.*, u.full_name as student_name, u.nexus_id 
        FROM leave_applications la
        JOIN users u ON la.student_id = u.id
        WHERE 1=1";

if(!empty($start_date)) {
    $sql .= " AND DATE(la.created_at) >= '$start_date'";
}
if(!empty($end_date)) {
    $sql .= " AND DATE(la.created_at) <= '$end_date'";
}
if($status_filter != 'all') {
    $sql .= " AND la.status = '$status_filter'";
}
if($leave_type_filter != 'all') {
    $sql .= " AND la.leave_type = '$leave_type_filter'";
}

$sql .= " ORDER BY la.created_at DESC";
$applications = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Applications - Nexus</title>
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
        .filter-group input, .filter-group select {
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
        .reset-btn {
            background: #6c757d;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 5px;
            cursor: pointer;
            height: 38px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        .applications-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .applications-table th {
            background: #123966;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }
        .applications-table td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
        }
        .status-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }
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
                <a href="all_applications.php" class="active-sub"><i class="fa-solid fa-list"></i> All Applications</a>
                <a href="high_absence_students.php"><i class="fa-solid fa-chart-line"></i> High Absence Students</a>
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
                <i class="fa-solid fa-list"></i> All Leave Applications
            </div>

            <form method="GET" class="filter-bar">
                <div class="filter-group">
                    <label>Start Date</label>
                    <input type="date" name="start_date" value="<?php echo $start_date; ?>">
                </div>
                <div class="filter-group">
                    <label>End Date</label>
                    <input type="date" name="end_date" value="<?php echo $end_date; ?>">
                </div>
                <div class="filter-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>All</option>
                        <option value="Pending" <?php echo $status_filter == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="Pending_Approvals" <?php echo $status_filter == 'Pending_Approvals' ? 'selected' : ''; ?>>Pending Approvals</option>
                        <option value="Partial_Signatures" <?php echo $status_filter == 'Partial_Signatures' ? 'selected' : ''; ?>>Partial Approvals</option>
                        <option value="Approved" <?php echo $status_filter == 'Approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="Approved_Form_Ready" <?php echo $status_filter == 'Approved_Form_Ready' ? 'selected' : ''; ?>>Approved</option>
                        <option value="Rejected" <?php echo $status_filter == 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Leave Type</label>
                    <select name="leave_type">
                        <option value="all" <?php echo $leave_type_filter == 'all' ? 'selected' : ''; ?>>All</option>
                        <option value="Medical" <?php echo $leave_type_filter == 'Medical' ? 'selected' : ''; ?>>Medical</option>
                        <option value="Emergency" <?php echo $leave_type_filter == 'Emergency' ? 'selected' : ''; ?>>Emergency</option>
                        <option value="Long Vacation" <?php echo $leave_type_filter == 'Long Vacation' ? 'selected' : ''; ?>>Long Vacation</option>
                    </select>
                </div>
                <div class="filter-group">
                    <button type="submit" class="filter-btn"><i class="fa-solid fa-filter"></i> Apply Filter</button>
                </div>
                <div class="filter-group">
                    <a href="all_applications.php" class="reset-btn"><i class="fa-solid fa-rotate-left"></i> Reset</a>
                </div>
            </form>

            <table class="applications-table">
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>NexusID</th>
                        <th>Leave Type</th>
                        <th>Duration</th>
                        <th>Applied Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php if(mysqli_num_rows($applications) > 0): ?>
                    <?php while($app = mysqli_fetch_assoc($applications)): 
                        $status_class = '';
                        $status_text = '';
                        
                        if($app['status'] == 'Pending' || $app['status'] == 'Pending_Approvals') {
                            $status_class = 'status-pending';
                            $status_text = 'Pending';
                        } elseif($app['status'] == 'Partial_Signatures') {
                            $status_class = 'status-pending';
                            $status_text = 'Partial Approvals';
                        } elseif($app['status'] == 'Approved_Form_Ready' || $app['status'] == 'Approved') {
                            $status_class = 'status-approved';
                            $status_text = 'Approved';
                        } elseif($app['status'] == 'Rejected') {
                            $status_class = 'status-rejected';
                            $status_text = 'Rejected';
                        } else {
                            $status_class = 'status-pending';
                            $status_text = $app['status'];
                        }
                    ?>
                        <tr>
                            <td><?php echo $app['student_name']; ?></td>
                            <td><?php echo $app['nexus_id']; ?></td>
                            <td><?php echo $app['leave_type']; ?></td>
                            <td><?php echo $app['duration']; ?></td>
                            <td><?php echo date("d M Y", strtotime($app['created_at'])); ?></td>
                            <td>
                                <span class="status-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                            </td>
                            <td>
                                <a href="view_application.php?id=<?php echo $app['id']; ?>" class="view-btn"><i class="fa-solid fa-eye"></i> View</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align:center; padding:40px;">No leave applications found.赶
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function toggleSubmenu(element) {
    element.classList.toggle('submenu-open');
}



document.addEventListener('click', function(event) {
    const wrapper = document.querySelector('.notification-wrapper');
    if(wrapper && !wrapper.contains(event.target)) {
        document.getElementById('notificationDropdown').style.display = 'none';
    }
});

</script>

</body>
</html>