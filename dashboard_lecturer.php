<?php
include 'db_config.php';
include 'notification_helper.php';

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'lecturer') {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];
$lecturer_id = $user['id'];

$nameParts = explode(' ', trim($user['full_name']));
$drName = (strtoupper($nameParts[0]) === 'MUHAMMAD') ? "Dr. Ali" : $nameParts[0];

// Get regular pending applications
$pendingQuery = mysqli_query($conn, "
    SELECT COUNT(*) as total
    FROM leave_applications
    INNER JOIN student_lecturers ON leave_applications.student_id = student_lecturers.student_id
    WHERE student_lecturers.lecturer_id = '$lecturer_id' 
    AND (leave_applications.status='Pending' OR leave_applications.status='Pending_Approvals')
");
$pendingCount = mysqli_fetch_assoc($pendingQuery)['total'];

// Get approved applications
$approvedQuery = mysqli_query($conn, "
    SELECT COUNT(*) as total
    FROM leave_applications
    INNER JOIN student_lecturers ON leave_applications.student_id = student_lecturers.student_id
    WHERE student_lecturers.lecturer_id = '$lecturer_id' 
    AND (leave_applications.status='Approved' OR leave_applications.status='Approved_Form_Ready')
");
$approvedCount = mysqli_fetch_assoc($approvedQuery)['total'];

// Get rejected applications
$rejectedQuery = mysqli_query($conn, "
    SELECT COUNT(*) as total
    FROM leave_applications
    INNER JOIN student_lecturers ON leave_applications.student_id = student_lecturers.student_id
    WHERE student_lecturers.lecturer_id = '$lecturer_id' 
    AND leave_applications.status='Rejected'
");
$rejectedCount = mysqli_fetch_assoc($rejectedQuery)['total'];

// Get total applications
$totalQuery = mysqli_query($conn, "
    SELECT COUNT(*) as total
    FROM leave_applications
    INNER JOIN student_lecturers ON leave_applications.student_id = student_lecturers.student_id
    WHERE student_lecturers.lecturer_id = '$lecturer_id'
");
$totalCount = mysqli_fetch_assoc($totalQuery)['total'];

// Get notifications for lecturer
$notifications = getNotifications($conn, $lecturer_id, 10);
$unreadCount = getUnreadCount($conn, $lecturer_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lecturer Dashboard - Nexus</title>
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
        <a href="dashboard_lecturer.php" class="nav-link active">
            <i class="fa-solid fa-chart-column nav-fa"></i> Dashboard
        </a>
        <a href="profile_lecturer.php" class="nav-link">
            <i class="fa-solid fa-user nav-fa"></i> Profile
        </a>
        <br>
        <a href="pending_applications.php" class="nav-link">
            <i class="fa-solid fa-envelope nav-fa"></i> Pending Applications
        </a>
        <a href="search_students.php" class="nav-link">
            <i class="fa-solid fa-magnifying-glass nav-fa"></i> Search Students
        </a>
    </nav>
</div>

<div class="dashboard-main">
    <div class="topbar">
        <div class="topbar-name"><?php echo strtoupper($user['full_name']); ?> (LECTURER)</div>
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
                <h1 style="font-size:44px; margin-bottom:5px;">Hello <?php echo $drName; ?>!</h1>
                <p style="font-size:19px;">Review and manage student leave applications here.</p>
            </div>

            <div class="dashboard-grid" style="margin-top:20px;">
    <div class="left-stats">
        <div style="margin-bottom:25px; display: flex; align-items: center; gap: 15px;">
            <i class="fa-solid fa-clock" style="font-size: 32px; color: #ffc107;"></i>
            <div>
                <p style="margin: 0;">Pending Applications</p>
                <h2 style="font-size:28px; color:orange; margin: 0;"><?php echo $pendingCount; ?></h2>
            </div>
        </div>
        <div style="margin-bottom:25px; display: flex; align-items: center; gap: 15px;">
            <i class="fa-solid fa-check-circle" style="font-size: 32px; color: #28a745;"></i>
            <div>
                <p style="margin: 0;">Approved Applications</p>
                <h2 style="font-size:28px; color:limegreen; margin: 0;"><?php echo $approvedCount; ?></h2>
            </div>
        </div>
        <div style="margin-bottom:25px; display: flex; align-items: center; gap: 15px;">
            <i class="fa-solid fa-times-circle" style="font-size: 32px; color: #dc3545;"></i>
            <div>
                <p style="margin: 0;">Rejected Applications</p>
                <h2 style="font-size:28px; color:red; margin: 0;"><?php echo $rejectedCount; ?></h2>
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: 15px;">
            <i class="fa-solid fa-chart-simple" style="font-size: 32px; color: #123966;"></i>
            <div>
                <p style="margin: 0;">Total Requests</p>
                <h2 style="font-size:28px; color:#123966; margin: 0;"><?php echo $totalCount; ?></h2>
            </div>
        </div>
    </div>

    <div class="right-column-container">
        <div class="right-box">
            <h2 style="font-size:32px;">Leave Application Summary</h2>
            <p><i class="fa-solid fa-clock" style="color:orange;"></i> Pending: <?php echo $pendingCount; ?></p>
            <p><i class="fa-solid fa-check-circle" style="color:green;"></i> Approved: <?php echo $approvedCount; ?></p>
            <p><i class="fa-solid fa-times-circle" style="color:red;"></i> Rejected: <?php echo $rejectedCount; ?></p>
            <hr style="margin: 10px 0;">
            <p><i class="fa-solid fa-chart-simple"></i> Total: <?php echo $totalCount; ?></p>
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
                        item.onclick = () => window.location.href = 'pending_applications.php';
                        
                        let icon = 'fa-bell';
                        if(notif.title.includes('Approved')) icon = 'fa-check-circle';
                        else if(notif.title.includes('Rejected')) icon = 'fa-times-circle';
                        else if(notif.title.includes('Submitted')) icon = 'fa-paper-plane';
                        else if(notif.title.includes('Assigned')) icon = 'fa-user-plus';
                        
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