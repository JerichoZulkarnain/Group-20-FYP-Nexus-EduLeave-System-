<?php
include 'db_config.php';
include 'log_activity.php';
include 'notification_helper.php';

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];

logActivity($conn, $user['id'], $user['full_name'], $user['role'], 'Page View', 'Viewed Admin Dashboard');

// ==================== GET STATISTICS FROM DATABASE ====================

// Total Students
$studentsQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'student'");
$studentsCount = mysqli_fetch_assoc($studentsQuery);
$totalStudents = $studentsCount['total'];

// Total Lecturers
$lecturersQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'lecturer'");
$lecturersCount = mysqli_fetch_assoc($lecturersQuery);
$totalLecturers = $lecturersCount['total'];

// Total Active Accounts
$activeQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'student' OR role = 'lecturer'");
$activeCount = mysqli_fetch_assoc($activeQuery);
$totalActive = $activeCount['total'];

// Total Applications
$applicationsQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM leave_applications");
$applicationsCount = mysqli_fetch_assoc($applicationsQuery);
$totalApplications = $applicationsCount['total'];

// Pending Requests
$pendingQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM leave_applications WHERE status = 'Pending' OR status = 'Pending_Approvals' OR status = 'Partial_Signatures'");
$pendingCount = mysqli_fetch_assoc($pendingQuery);
$totalPending = $pendingCount['total'];

// Approved Requests
$approvedQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM leave_applications WHERE status = 'Approved' OR status = 'Approved_Form_Ready'");
$approvedCount = mysqli_fetch_assoc($approvedQuery);
$totalApproved = $approvedCount['total'];

// Rejected Requests
$rejectedQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM leave_applications WHERE status = 'Rejected'");
$rejectedCount = mysqli_fetch_assoc($rejectedQuery);
$totalRejected = $rejectedCount['total'];

// Recent Activities
$recentActivitiesQuery = mysqli_query($conn, "SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Nexus</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .dashboard-grid {
            display: grid;
            grid-template-columns: 32% 66%;
            gap: 16px;
            height: calc(100% - 120px);
        }
        .left-stats {
            border: 1px solid #bfbfbf;
            border-radius: 8px;
            background: white;
            padding: 14px 22px;
            overflow-y: auto;
        }
        .left-stats p {
            font-size: 20px;
            font-weight: 800;
            color: #123966;
            margin: 0 0 24px;
            line-height: 1.2;
        }
        .right-box {
            border: 1px solid #bfbfbf;
            border-radius: 8px;
            background: white;
            padding: 12px 18px;
            margin-bottom: 12px;
        }
        .right-box h2 {
            margin: 0 0 6px;
            color: #123966;
            font-size: 24px;
            font-weight: 800;
        }
        .right-box p {
            color: #123966;
            font-size: 17px;
            font-weight: 700;
            margin: 8px 0;
            line-height: 1.3;
        }
        .welcome-box {
            background: rgba(248,248,248,0.95);
            border-radius: 55px;
            padding: 16px 34px;
            margin-bottom: 16px;
        }
        .welcome-box h1 {
            margin: 0;
            color: #123966;
            font-size: 34px;
            line-height: 1;
        }
        .welcome-box p {
            margin: 6px 0 0;
            color: #123966;
            font-size: 18px;
            font-weight: 700;
        }
        .dashboard-card {
            background: rgba(255,255,255,0.92);
            border-radius: 12px;
            padding: 18px;
            height: auto;
            min-height: calc(100vh - 130px);
            overflow: visible;
        }
        .dashboard-content {
            padding: 18px 28px;
            overflow-y: auto;
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
        .dashboard-page {
            margin: 0;
            display: flex;
            height: 100vh;
        }
        .dashboard-main {
            flex: 1;
            display: flex;
            flex-direction: column;
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
        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: #123966;
        }
        
        /* Notification Styles */
        .notification-wrapper {
            position: relative;
        }
        .notification-bell {
            cursor: pointer;
            font-size: 24px;
            position: relative;
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
        .notification-dropdown h3 {
            padding: 12px 15px;
            margin: 0;
            border-bottom: 1px solid #eee;
            color: #123966;
        }
        .notification-item {
            padding: 12px 15px;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            transition: background 0.2s;
        }
        .notification-item:hover {
            background: #f5f5f5;
        }
        .notification-item.unread {
            background: #e8f0fe;
        }
        .notification-icon {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: #eef2f7;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
        }
        .notification-content {
            display: inline-block;
            vertical-align: top;
            width: calc(100% - 50px);
        }
        .notification-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 4px;
        }
        .notification-message {
            font-size: 12px;
            color: #666;
            margin-bottom: 4px;
        }
        .notification-time {
            font-size: 11px;
            color: #999;
        }
        .mark-all-read {
            padding: 10px;
            text-align: center;
            background: #f8f9fa;
            cursor: pointer;
            color: #123966;
            font-size: 12px;
        }
        .mark-all-read:hover {
            background: #e9ecef;
        }
    </style>
</head>

<body class="dashboard-page">

<div class="sidebar">
    <img src="images/logo.png" class="sidebar-logo">
    <div class="sidebar-title">EduLeave System</div>
    <div class="sidebar-date"><?php echo date("l d M Y"); ?></div>

    <nav class="nav-menu">
        <a href="dashboard_admin.php" class="nav-link active">
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
            <div class="notification-wrapper">
                <i class="fa-solid fa-bell notification-bell" onclick="toggleNotifications()"></i>
                <span class="notification-badge" id="notificationBadge">0</span>
                <div class="notification-dropdown" id="notificationDropdown">
                    <h3>Notifications</h3>
                    <div id="notificationList"></div>
                    <div class="mark-all-read" onclick="markAllRead()">Mark all as read</div>
                </div>
            </div>
            <span style="color:#ccc; font-size:24px;">|</span>
            <a href="logout.php" class="logout-link"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
        </div>
    </div>

    <div class="dashboard-content">
        <div class="dashboard-card">
            <div class="welcome-box" style="text-align:center; background:linear-gradient(to right, #ece9e6, #ffffff); border-radius:60px;">
                <h1 style="font-size:48px; margin-bottom:0;">Hello Admin!</h1>
                <p style="font-size:20px;">Monitor system activity and manage records here.</p>
            </div>

            <div class="dashboard-grid" style="margin-top:20px;">
    <div class="left-stats">
        <div style="margin-bottom:25px; display: flex; align-items: center; gap: 15px;">
            <i class="fa-solid fa-users" style="font-size: 32px; color: #123966;"></i>
            <div>
                <p style="margin: 0;">Total Students</p>
                <h2 style="font-size:28px; color:#123966; margin: 0;"><?php echo $totalStudents; ?></h2>
            </div>
        </div>
        <div style="margin-bottom:25px; display: flex; align-items: center; gap: 15px;">
            <i class="fa-solid fa-chalkboard-user" style="font-size: 32px; color: #17a2b8;"></i>
            <div>
                <p style="margin: 0;">Total Lecturers</p>
                <h2 style="font-size:28px; color:#17a2b8; margin: 0;"><?php echo $totalLecturers; ?></h2>
            </div>
        </div>
        <div style="margin-bottom:25px; display: flex; align-items: center; gap: 15px;">
            <i class="fa-solid fa-file-lines" style="font-size: 32px; color: #28a745;"></i>
            <div>
                <p style="margin: 0;">Total Applications</p>
                <h2 style="font-size:28px; color:#28a745; margin: 0;"><?php echo $totalApplications; ?></h2>
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: 15px;">
            <i class="fa-solid fa-user-check" style="font-size: 32px; color: #6c757d;"></i>
            <div>
                <p style="margin: 0;">Active Accounts</p>
                <h2 style="font-size:28px; color:#6c757d; margin: 0;"><?php echo $totalActive; ?></h2>
            </div>
        </div>
    </div>

    <div class="right-column-container">
        <div class="right-box">
            <h2 style="border-bottom:1px solid #eee; padding-bottom:5px;">System Summary</h2>
            <p><i class="fa-solid fa-clock" style="color:orange;"></i> Pending Requests: <?php echo $totalPending; ?></p>
            <p><i class="fa-solid fa-check-circle" style="color:green;"></i> Approved Requests: <?php echo $totalApproved; ?></p>
            <p><i class="fa-solid fa-times-circle" style="color:red;"></i> Rejected Requests: <?php echo $totalRejected; ?></p>
        </div>

        <div class="right-box">
            <h2 style="border-bottom:1px solid #eee; padding-bottom:5px;">Recent System Activities</h2>
            <?php if(mysqli_num_rows($recentActivitiesQuery) > 0): ?>
                <?php while($activity = mysqli_fetch_assoc($recentActivitiesQuery)): ?>
                    <p><i class="fa-solid fa-circle" style="font-size: 8px; color: #123966; margin-right: 8px;"></i> <?php echo $activity['activity_description']; ?> <span style="font-size: 11px; color: #999;">(<?php echo date("d M H:i", strtotime($activity['created_at'])); ?>)</span></p>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No activities available yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
        </div>
    </div>
</div>

<script>
function toggleSubmenu(element) {
    element.classList.toggle('submenu-open');
}

function loadNotifications() {
    fetch('get_notifications.php')
        .then(response => response.json())
        .then(data => {
            if(data.unread_count !== undefined) {
                document.getElementById('notificationBadge').textContent = data.unread_count;
                if(data.unread_count > 0) {
                    document.getElementById('notificationBadge').style.display = 'inline-block';
                } else {
                    document.getElementById('notificationBadge').style.display = 'none';
                }
            }
            if(data.notifications) {
                const list = document.getElementById('notificationList');
                list.innerHTML = '';
                if(data.notifications.length === 0) {
                    list.innerHTML = '<div style="padding: 20px; text-align: center; color: #999;">No notifications</div>';
                } else {
                    data.notifications.forEach(notif => {
                        const item = document.createElement('div');
                        item.className = 'notification-item' + (notif.is_read ? '' : ' unread');
                        item.onclick = () => window.location.href = 'activity_logs.php';
                        item.innerHTML = `
                            <div class="notification-icon">
                                <i class="fa-solid ${notif.icon}"></i>
                            </div>
                            <div class="notification-content">
                                <div class="notification-title">${notif.title}</div>
                                <div class="notification-message">${notif.message}</div>
                                <div class="notification-time">${notif.time_ago}</div>
                            </div>
                        `;
                        list.appendChild(item);
                    });
                }
            }
        });
}

function toggleNotifications() {
    const dropdown = document.getElementById('notificationDropdown');
    if(dropdown.style.display === 'block') {
        dropdown.style.display = 'none';
    } else {
        loadNotifications();
        dropdown.style.display = 'block';
    }
}

function markAllRead() {
    fetch('get_notifications.php?action=mark_read')
        .then(response => response.json())
        .then(() => {
            loadNotifications();
            document.getElementById('notificationBadge').textContent = '0';
            document.getElementById('notificationBadge').style.display = 'none';
        });
}

document.addEventListener('click', function(event) {
    const wrapper = document.querySelector('.notification-wrapper');
    if(wrapper && !wrapper.contains(event.target)) {
        document.getElementById('notificationDropdown').style.display = 'none';
    }
});

setInterval(loadNotifications, 30000);
</script>

</body>
</html>