<?php
include 'db_config.php';
include 'notification_helper.php';

if(!isset($_SESSION['user']) || ($_SESSION['user']['role'] !== 'admin' && $_SESSION['user']['role'] !== 'lecturer')) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];

if(!isset($_GET['id'])) {
    header("Location: all_applications.php");
    exit();
}

$application_id = $_GET['id'];

// Get application details
$appQuery = mysqli_query($conn, "
    SELECT la.*, u.full_name as student_name, u.nexus_id, u.email as student_email
    FROM leave_applications la
    JOIN users u ON la.student_id = u.id
    WHERE la.id = '$application_id'
");
$application = mysqli_fetch_assoc($appQuery);

if(!$application) {
    header("Location: all_applications.php");
    exit();
}

// Get evidence
$evidenceQuery = mysqli_query($conn, "SELECT file_path FROM evidence WHERE student_id = '{$application['student_id']}' ORDER BY uploaded_at DESC LIMIT 1");
$evidence = mysqli_fetch_assoc($evidenceQuery);

$status_class = '';
$status_text = '';
if($application['status'] == 'Pending' || $application['status'] == 'Pending_Approvals') {
    $status_class = 'status-pending';
    $status_text = 'Pending';
} elseif($application['status'] == 'Partial_Signatures') {
    $status_class = 'status-pending';
    $status_text = 'Partial Approvals';
} elseif($application['status'] == 'Approved_Form_Ready') {
    $status_class = 'status-approved';
    $status_text = 'Form Ready';
} elseif($application['status'] == 'Approved') {
    $status_class = 'status-approved';
    $status_text = 'Approved';
} elseif($application['status'] == 'Rejected') {
    $status_class = 'status-rejected';
    $status_text = 'Rejected';
} else {
    $status_class = 'status-pending';
    $status_text = $application['status'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Application - Nexus</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .details-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .details-header {
            background: #123966;
            color: white;
            padding: 20px 25px;
        }
        .details-header h1 {
            margin: 0;
            font-size: 24px;
        }
        .details-body {
            padding: 25px;
        }
        .detail-row {
            display: flex;
            margin-bottom: 18px;
            padding-bottom: 12px;
            border-bottom: 1px solid #eee;
        }
        .detail-label {
            width: 180px;
            font-weight: 700;
            color: #123966;
        }
        .detail-value {
            flex: 1;
            color: #333;
        }
        .evidence-link {
            background: #f0f0f0;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            color: #123966;
            display: inline-block;
        }
        .evidence-link:hover {
            background: #123966;
            color: white;
        }
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        .back-btn {
            background: #6c757d;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }
        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #123966;
            margin: 20px 0 15px 0;
            padding-bottom: 8px;
            border-bottom: 2px solid #123966;
        }
        
        /* Sidebar Menu Styles */
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
        <div class="topbar-name"><?php echo strtoupper($user['full_name']); ?> (<?php echo strtoupper($user['role']); ?>)</div>
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
        <div class="details-container">
            <div class="details-header">
                <h1><i class="fa-solid fa-file-alt"></i> Leave Application Details</h1>
            </div>
            <div class="details-body">
                <div class="detail-row">
                    <div class="detail-label">Student Name:</div>
                    <div class="detail-value"><?php echo strtoupper($application['student_name']); ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Nexus ID:</div>
                    <div class="detail-value"><?php echo $application['nexus_id']; ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Email:</div>
                    <div class="detail-value"><?php echo $application['student_email']; ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Leave Type:</div>
                    <div class="detail-value"><?php echo $application['leave_type']; ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Duration:</div>
                    <div class="detail-value"><?php echo $application['duration']; ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Leave Period:</div>
                    <div class="detail-value"><?php echo date("d M Y", strtotime($application['start_date'])); ?> - <?php echo date("d M Y", strtotime($application['end_date'])); ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Reason:</div>
                    <div class="detail-value"><?php echo nl2br($application['reason']); ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Applied Date:</div>
                    <div class="detail-value"><?php echo date("d M Y H:i", strtotime($application['created_at'])); ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Status:</div>
                    <div class="detail-value">
                        <span class="status-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                    </div>
                </div>
                
                <!-- Rejection Reason -->
                <?php if($application['status'] == 'Rejected' && !empty($application['rejection_reason'])): ?>
                <div class="section-title"><i class="fa-solid fa-ban"></i> Rejection Details</div>
                <div class="detail-row">
                    <div class="detail-label">Rejection Reason:</div>
                    <div class="detail-value" style="color:red;"><?php echo nl2br($application['rejection_reason']); ?></div>
                </div>
                <?php endif; ?>

                <!-- Supporting Evidence -->
                <div class="section-title"><i class="fa-solid fa-paperclip"></i> Supporting Evidence</div>
                <div class="detail-row">
                    <div class="detail-label">Medical Certificate (MC):</div>
                    <div class="detail-value">
                        <?php if(!empty($application['mc_file'])): ?>
                            <?php if(file_exists($application['mc_file'])): ?>
                                <a href="<?php echo $application['mc_file']; ?>" target="_blank" class="evidence-link"><i class="fa-regular fa-file-pdf"></i> View MC</a>
                            <?php else: ?>
                                <span style="color:red;">File not found: <?php echo basename($application['mc_file']); ?></span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span style="color:#999;">No MC uploaded</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Evidence/Justification:</div>
                    <div class="detail-value">
                        <?php if(!empty($evidence['file_path'])): ?>
                            <?php if(file_exists($evidence['file_path'])): ?>
                                <a href="<?php echo $evidence['file_path']; ?>" target="_blank" class="evidence-link"><i class="fa-regular fa-file-image"></i> View Evidence</a>
                            <?php else: ?>
                                <span style="color:red;">File not found: <?php echo basename($evidence['file_path']); ?></span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span style="color:#999;">No evidence uploaded</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Back Button -->
                <div style="text-align: center; margin-top: 30px;">
                    <?php if($user['role'] == 'admin'): ?>
                        <a href="all_applications.php" class="back-btn"><i class="fa-solid fa-arrow-left"></i> Back to Applications</a>
                    <?php else: ?>
                        <a href="pending_applications.php" class="back-btn"><i class="fa-solid fa-arrow-left"></i> Back</a>
                    <?php endif; ?>
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