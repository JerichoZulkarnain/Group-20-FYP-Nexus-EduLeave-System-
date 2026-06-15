<?php
include 'db_config.php';
include 'log_activity.php';


if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];

$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-1 month'));
$end_date = $_GET['end_date'] ?? date('Y-m-d');
$generated = isset($_GET['generate']);

$total_applications = 0;
$approved = 0;
$rejected = 0;
$pending = 0;
$avg_duration = 0;
$most_common_type = '-';
$most_common_type_count = 0;

if($generated) {
    logActivity($conn, $user['id'], $user['full_name'], $user['role'], 'Report Generation', 'Generated Absence Summary Report for period: ' . $start_date . ' to ' . $end_date);
    addNotification($conn, $user['id'], 'Report Generated', 'Absence Summary Report generated for ' . date("d M Y", strtotime($start_date)) . ' to ' . date("d M Y", strtotime($end_date)), 'report');
    
    $summaryQuery = mysqli_query($conn, "
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'Approved' OR status = 'Approved_Form_Ready' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) as rejected,
            SUM(CASE WHEN status = 'Pending' OR status = 'Pending_Approvals' OR status = 'Partial_Signatures' THEN 1 ELSE 0 END) as pending,
            AVG(CAST(SUBSTRING_INDEX(duration, ' ', 1) AS UNSIGNED)) as avg_duration
        FROM leave_applications
        WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'
    ");
    $summary = mysqli_fetch_assoc($summaryQuery);
    
    $total_applications = $summary['total'] ?? 0;
    $approved = $summary['approved'] ?? 0;
    $rejected = $summary['rejected'] ?? 0;
    $pending = $summary['pending'] ?? 0;
    $avg_duration = $summary['avg_duration'] ? round($summary['avg_duration'], 1) : 0;
    
    $typeQuery = mysqli_query($conn, "
        SELECT leave_type, COUNT(*) as count
        FROM leave_applications
        WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'
        GROUP BY leave_type
        ORDER BY count DESC
        LIMIT 1
    ");
    $typeResult = mysqli_fetch_assoc($typeQuery);
    if($typeResult) {
        $most_common_type = $typeResult['leave_type'];
        $most_common_type_count = $typeResult['count'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Absence Summary - Nexus</title>
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
        .filter-group input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            min-width: 150px;
        }
        .generate-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 8px 25px;
            border-radius: 5px;
            cursor: pointer;
            height: 38px;
            font-weight: 600;
        }
        .export-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            justify-content: flex-end;
        }
        .export-pdf {
            background: #dc3545;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }
        .export-excel {
            background: #28a745;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }
        .summary-container {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-top: 20px;
        }
        .summary-title {
            font-size: 20px;
            font-weight: 700;
            color: #123966;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #123966;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
        }
        .stat-card h4 {
            color: #555;
            font-size: 14px;
            margin-bottom: 10px;
            font-weight: 600;
        }
        .stat-number {
            font-size: 36px;
            font-weight: 700;
            color: #123966;
        }
        .stat-unit {
            font-size: 14px;
            color: #666;
        }
        .most-common {
            background: #e8f0fe;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            margin-top: 10px;
        }
        .most-common h4 {
            color: #123966;
            font-size: 16px;
            margin-bottom: 10px;
        }
        .most-common-value {
            font-size: 28px;
            font-weight: 700;
            color: #123966;
        }
        .most-common-count {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }
        .no-data {
            text-align: center;
            padding: 40px;
            color: #999;
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
        <div class="nav-item has-submenu" onclick="toggleSubmenu(this)">
            <div class="nav-link">
                <i class="fa-solid fa-file-alt nav-fa"></i> Leave Monitoring
                <i class="fa-solid fa-chevron-right chevron"></i>
            </div>
            <div class="submenu">
                <a href="all_applications.php"><i class="fa-solid fa-list"></i> All Applications</a>
                <a href="high_absence_students.php"><i class="fa-solid fa-chart-line"></i> High Absence Students</a>
            </div>
        </div>
        <div class="nav-item has-submenu submenu-open" onclick="toggleSubmenu(this)">
            <div class="nav-link">
                <i class="fa-solid fa-chart-simple nav-fa"></i> Reports
                <i class="fa-solid fa-chevron-right chevron"></i>
            </div>
            <div class="submenu">
                <a href="absence_summary.php" class="active-sub"><i class="fa-solid fa-file-export"></i> Absence Summary</a>
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
                <i class="fa-solid fa-chart-simple"></i> Absence Summary Report
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
                    <button type="submit" name="generate" value="1" class="generate-btn"><i class="fa-solid fa-chart-simple"></i> Generate Report</button>
                </div>
            </form>

            <?php if($generated): ?>
                <div class="summary-container">
                    <div class="summary-title">
                        <i class="fa-solid fa-chart-line"></i> Report Summary
                        <span style="font-size: 14px; font-weight: normal; margin-left: 10px;">
                            (<?php echo date("d M Y", strtotime($start_date)); ?> - <?php echo date("d M Y", strtotime($end_date)); ?>)
                        </span>
                    </div>
                    
                    <?php if($total_applications > 0): ?>
                        <div class="stats-grid">
                            <div class="stat-card">
                                <h4><i class="fa-solid fa-file-alt"></i> Total Applications</h4>
                                <div class="stat-number"><?php echo $total_applications; ?></div>
                            </div>
                            <div class="stat-card">
                                <h4><i class="fa-solid fa-check-circle"></i> Approved</h4>
                                <div class="stat-number" style="color: #28a745;"><?php echo $approved; ?></div>
                            </div>
                            <div class="stat-card">
                                <h4><i class="fa-solid fa-times-circle"></i> Rejected</h4>
                                <div class="stat-number" style="color: #dc3545;"><?php echo $rejected; ?></div>
                            </div>
                            <div class="stat-card">
                                <h4><i class="fa-solid fa-clock"></i> Pending</h4>
                                <div class="stat-number" style="color: #ffc107;"><?php echo $pending; ?></div>
                            </div>
                            <div class="stat-card">
                                <h4><i class="fa-solid fa-calendar-week"></i> Average Leave Duration</h4>
                                <div class="stat-number"><?php echo $avg_duration; ?> <span class="stat-unit">Days</span></div>
                            </div>
                        </div>
                        
                        <div class="most-common">
                            <h4><i class="fa-solid fa-chart-pie"></i> Most Common Leave Type</h4>
                            <div class="most-common-value"><?php echo $most_common_type; ?></div>
                            <div class="most-common-count"><?php echo $most_common_type_count; ?> applications</div>
                        </div>
                    <?php else: ?>
                        <div class="no-data">
                            <i class="fa-solid fa-inbox" style="font-size: 48px; color: #ccc;"></i>
                            <p>No leave applications found for the selected date range.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if($total_applications > 0): ?>
                    <div class="export-buttons">
                        <a href="export_absence_pdf.php?start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" class="export-pdf" target="_blank">
                            <i class="fa-solid fa-file-pdf"></i> Export PDF
                        </a>
                        <a href="export_absence_excel.php?start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" class="export-excel">
                            <i class="fa-solid fa-file-excel"></i> Export Excel
                        </a>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="summary-container">
                    <div class="no-data">
                        <i class="fa-solid fa-chart-simple" style="font-size: 48px; color: #ccc;"></i>
                        <p>Select a date range and click "Generate Report" to view the absence summary.</p>
                    </div>
                </div>
            <?php endif; ?>
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