<?php
include 'db_config.php';
include 'notification_helper.php';

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];
$student_id = $user['id'];

$firstName = explode(' ', trim($user['full_name']))[0];

// ======================================
// TOTAL APPLICATIONS
// ======================================

$totalQuery = mysqli_query($conn,
"SELECT COUNT(*) as total
FROM leave_applications
WHERE student_id = '$student_id'");

$totalData = mysqli_fetch_assoc($totalQuery);
$totalApplications = $totalData['total'];

// ======================================
// PENDING
// ======================================

$pendingQuery = mysqli_query($conn,
"SELECT COUNT(*) as total
FROM leave_applications
WHERE student_id = '$student_id'
AND (status='Pending' OR status='Pending_Approvals' OR status='Partial_Signatures')");

$pendingData = mysqli_fetch_assoc($pendingQuery);
$pendingCount = $pendingData['total'];

// ======================================
// APPROVED
// ======================================

$approvedQuery = mysqli_query($conn,
"SELECT COUNT(*) as total
FROM leave_applications
WHERE student_id = '$student_id'
AND (status='Approved' OR status='Approved_Form_Ready')");

$approvedData = mysqli_fetch_assoc($approvedQuery);
$approvedCount = $approvedData['total'];

// ======================================
// REJECTED
// ======================================

$rejectedQuery = mysqli_query($conn,
"SELECT COUNT(*) as total
FROM leave_applications
WHERE student_id = '$student_id'
AND status='Rejected'");

$rejectedData = mysqli_fetch_assoc($rejectedQuery);
$rejectedCount = $rejectedData['total'];

// ======================================
// GET NOTIFICATIONS FROM DATABASE
// ======================================

$notifications = getNotifications($conn, $student_id, 10);
$unreadCount = getUnreadCount($conn, $student_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard - Nexus</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
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
        <a href="dashboard_student.php" class="nav-link active"><i class="fa-solid fa-house nav-fa"></i> Home</a>
        <a href="profile_student.php" class="nav-link"><i class="fa-solid fa-user nav-fa"></i> Profile</a>
        <br>
        <a href="apply_leave.php" class="nav-link"><i class="fa-solid fa-file-pen nav-fa"></i> Apply Leave</a>
        <a href="leave_history.php" class="nav-link"><i class="fa-solid fa-clock-rotate-left nav-fa"></i> Leave History</a>
        <a href="track_status.php" class="nav-link"><i class="fa-solid fa-calendar-check nav-fa"></i> Track Status</a>
    </nav>
</div>

<div class="dashboard-main">
    <div class="topbar">
        <div class="topbar-name"><?php echo strtoupper($user['full_name']); ?> (STUDENT)</div>
        <div class="topbar-right">
            <div class="notification-wrapper">
                <i class="fa-solid fa-bell notification-bell" onclick="toggleNotifications()"></i>
                <?php if($unreadCount > 0): ?>
                    <span class="notification-badge" id="notificationBadge"><?php echo $unreadCount; ?></span>
                <?php endif; ?>
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
            <div class="welcome-box" style="background:linear-gradient(to right,#ece9e6,#ffffff); border-radius:60px;">
                <h1 style="font-size:44px; margin-bottom:5px;">Hello <?php echo $firstName; ?>!</h1>
                <p style="font-size:19px;">Manage your leave applications and track approval status here.</p>
            </div>

            <div class="dashboard-grid">
    <div class="left-stats">
        <div style="margin-bottom:25px; display: flex; align-items: center; gap: 15px;">
            <i class="fa-solid fa-file-lines" style="font-size: 32px; color: #123966;"></i>
            <div>
                <p style="margin: 0;">Total Applications</p>
                <h2 style="font-size:28px; color:#123966; margin: 0;"><?php echo $totalApplications; ?></h2>
            </div>
        </div>
        <div style="margin-bottom:25px; display: flex; align-items: center; gap: 15px;">
            <i class="fa-solid fa-clock" style="font-size: 32px; color: #ffc107;"></i>
            <div>
                <p style="margin: 0;">Pending Requests</p>
                <h2 style="font-size:28px; color:orange; margin: 0;"><?php echo $pendingCount; ?></h2>
            </div>
        </div>
        <div style="margin-bottom:25px; display: flex; align-items: center; gap: 15px;">
            <i class="fa-solid fa-check-circle" style="font-size: 32px; color: #28a745;"></i>
            <div>
                <p style="margin: 0;">Approved Requests</p>
                <h2 style="font-size:28px; color:limegreen; margin: 0;"><?php echo $approvedCount; ?></h2>
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: 15px;">
            <i class="fa-solid fa-times-circle" style="font-size: 32px; color: #dc3545;"></i>
            <div>
                <p style="margin: 0;">Rejected Requests</p>
                <h2 style="font-size:28px; color:red; margin: 0;"><?php echo $rejectedCount; ?></h2>
            </div>
        </div>
    </div>

    <div class="right-column-container">
        <div class="right-box">
            <h2 style="font-size:32px;">Recent Activity</h2>
            <?php
            $recentQuery = mysqli_query($conn, "SELECT leave_type, status, created_at FROM leave_applications WHERE student_id = '$student_id' ORDER BY created_at DESC LIMIT 5");
            while($activity = mysqli_fetch_assoc($recentQuery)) {
            ?>
                <p><i class="fa-solid fa-bullhorn"></i> <?php echo $activity['leave_type']; ?> - <?php echo $activity['status']; ?></p>
            <?php } ?>
        </div>

        <div style="margin-top:15px;">
            <a href="apply_leave.php" class="apply-btn" style="border-radius:30px; padding:15px 40px; font-size:24px;">
                <i class="fa-regular fa-file-lines" style="margin-right:10px;"></i> Apply Leave Now
            </a>
        </div>
    </div>
</div>
        </div>
    </div>
</div>

<script>
function loadNotifications() {
    fetch('get_notifications.php')
        .then(response => response.json())
        .then(data => {
            if(data.unread_count !== undefined) {
                const badge = document.getElementById('notificationBadge');
                if(data.unread_count > 0) {
                    if(badge) {
                        badge.textContent = data.unread_count;
                        badge.style.display = 'inline-block';
                    } else {
                        const bell = document.querySelector('.notification-bell');
                        const newBadge = document.createElement('span');
                        newBadge.className = 'notification-badge';
                        newBadge.id = 'notificationBadge';
                        newBadge.textContent = data.unread_count;
                        bell.parentNode.appendChild(newBadge);
                    }
                } else {
                    if(badge) badge.style.display = 'none';
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
                        item.onclick = () => window.location.href = 'track_status.php';
                        
                        let icon = 'fa-bell';
                        if(notif.title.includes('Approved')) icon = 'fa-check-circle';
                        else if(notif.title.includes('Rejected')) icon = 'fa-times-circle';
                        else if(notif.title.includes('Submitted')) icon = 'fa-paper-plane';
                        
                        item.innerHTML = `
                            <div class="notification-icon">
                                <i class="fa-solid ${icon}" style="color: #123966;"></i>
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
            const badge = document.getElementById('notificationBadge');
            if(badge) badge.style.display = 'none';
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