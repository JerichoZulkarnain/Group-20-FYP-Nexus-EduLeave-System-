<?php
include 'db_config.php';
include 'log_activity.php';


if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];

logActivity($conn, $user['id'], $user['full_name'], $user['role'], 'Page View', 'Viewed Manage Students page');

$filter = $_GET['filter'] ?? 'all';
$search = $_GET['search'] ?? '';

$sql = "SELECT * FROM users WHERE role = 'student'";

if($filter == 'active') {
    $sql .= " AND (status = 'active' OR status IS NULL)";
} elseif($filter == 'inactive') {
    $sql .= " AND status = 'inactive'";
} elseif($filter == 'with_leave') {
    $sql .= " AND id IN (SELECT DISTINCT student_id FROM leave_applications)";
} elseif($filter == 'without_leave') {
    $sql .= " AND id NOT IN (SELECT DISTINCT student_id FROM leave_applications)";
} elseif($filter == 'high_absence') {
    $sql .= " AND id IN (SELECT student_id FROM leave_applications GROUP BY student_id HAVING COUNT(*) > 3)";
}

if(!empty($search)) {
    $sql .= " AND (full_name LIKE '%$search%' OR nexus_id LIKE '%$search%' OR email LIKE '%$search%')";
}

$sql .= " ORDER BY full_name ASC";
$students = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Students - Nexus</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .add-student-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .filter-bar {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
        }
        .filter-left {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        .filter-select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            background: white;
        }
        .search-input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 250px;
        }
        .search-btn {
            background: #123966;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        .students-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .students-table th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
        }
        .students-table td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }
        .action-btn {
            padding: 4px 8px;
            border-radius: 3px;
            text-decoration: none;
            font-size: 12px;
        }
        .view-btn { background: #17a2b8; color: white; }
        .edit-btn { background: #ffc107; color: black; }
        .deactivate-btn { background: #dc3545; color: white; }
        .activate-btn { background: #28a745; color: white; }
        
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
        <a href="dashboard_admin.php" class="nav-link">
            <i class="fa-solid fa-chart-column nav-fa"></i> Dashboard
        </a>
        <a href="profile_admin.php" class="nav-link">
            <i class="fa-solid fa-user nav-fa"></i> Profile
        </a>
        <div class="sidebar-divider"></div>
        <div class="nav-item has-submenu submenu-open" onclick="toggleSubmenu(this)">
            <div class="nav-link">
                <i class="fa-solid fa-users nav-fa"></i> User Management
                <i class="fa-solid fa-chevron-right chevron"></i>
            </div>
            <div class="submenu">
                <a href="manage_students.php" class="active-sub"><i class="fa-solid fa-user-graduate"></i> Manage Students</a>
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
           
            <span style="color:#ccc; font-size:24px;">|</span>
            <a href="logout.php" class="logout-link"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
        </div>
    </div>

    <div class="dashboard-content">
        <div class="dashboard-card">
            <div class="page-header">
                <h1 style="font-size:32px; margin:0;">Manage Students</h1>
                <button class="add-student-btn" onclick="window.location.href='add_student.php'">
                    <i class="fa-solid fa-plus"></i> Add Student
                </button>
            </div>
            
            <div class="filter-bar">
                <div class="filter-left">
                    <span>Search:</span>
                    <form method="GET" style="display: flex; gap: 10px;">
                        <input type="text" name="search" class="search-input" placeholder="Search students..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="search-btn"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
                    </form>
                </div>
                <div class="filter-left">
                    <span>Filter:</span>
                    <form method="GET" style="display: flex; gap: 10px;">
                        <select name="filter" class="filter-select" onchange="this.form.submit()">
                            <option value="all" <?php echo $filter == 'all' ? 'selected' : ''; ?>>All Students</option>
                            <option value="active" <?php echo $filter == 'active' ? 'selected' : ''; ?>>Active Students</option>
                            <option value="inactive" <?php echo $filter == 'inactive' ? 'selected' : ''; ?>>Inactive Students</option>
                            <option value="with_leave" <?php echo $filter == 'with_leave' ? 'selected' : ''; ?>>With Leave Records</option>
                            <option value="without_leave" <?php echo $filter == 'without_leave' ? 'selected' : ''; ?>>Without Leave Records</option>
                            <option value="high_absence" <?php echo $filter == 'high_absence' ? 'selected' : ''; ?>>High Absence (>3 leaves)</option>
                        </select>
                        <?php if(!empty($search)): ?>
                            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                        <?php endif; ?>
                    </form>
                </div>
            </div>
            
            <table class="students-table">
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>NexusID</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if(mysqli_num_rows($students) > 0): ?>
                    <?php while($student = mysqli_fetch_assoc($students)): ?>
                        <tr>
                            <td><?php echo $student['full_name']; ?></td>
                            <td><?php echo $student['nexus_id']; ?></td>
                            <td><?php echo $student['email']; ?></td>
                            <td>
                                <span class="status-badge <?php echo ($student['status'] ?? 'active') == 'active' ? 'status-active' : 'status-inactive'; ?>">
                                    <?php echo ($student['status'] ?? 'active') == 'active' ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td class="action-buttons">
                                <a href="student_details.php?id=<?php echo $student['id']; ?>" class="action-btn view-btn">View</a>
                                <a href="edit_student.php?id=<?php echo $student['id']; ?>" class="action-btn edit-btn">Edit</a>
                                <?php if(($student['status'] ?? 'active') == 'active'): ?>
                                    <a href="deactivate_student.php?id=<?php echo $student['id']; ?>&action=deactivate" class="action-btn deactivate-btn" onclick="return confirm('Deactivate this account?')">Deactivate</a>
                                <?php else: ?>
                                    <a href="deactivate_student.php?id=<?php echo $student['id']; ?>&action=activate" class="action-btn activate-btn" onclick="return confirm('Activate this account?')">Activate</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align:center; padding:40px;">No students found.赶
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