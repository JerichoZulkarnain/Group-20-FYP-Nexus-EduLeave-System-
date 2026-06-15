<?php
include 'db_config.php';
include 'log_activity.php';


if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];

logActivity($conn, $user['id'], $user['full_name'], $user['role'], 'Page View', 'Viewed Manage Lecturers page');

// ==================== MANAGE LECTURERS SECTION ====================

$lecturer_filter = $_GET['lecturer_filter'] ?? 'all';
$lecturer_search = $_GET['lecturer_search'] ?? '';

$lecturer_sql = "SELECT * FROM users WHERE role = 'lecturer'";

if($lecturer_filter == 'active') {
    $lecturer_sql .= " AND (status = 'active' OR status IS NULL)";
} elseif($lecturer_filter == 'inactive') {
    $lecturer_sql .= " AND status = 'inactive'";
}

if(!empty($lecturer_search)) {
    $lecturer_sql .= " AND (full_name LIKE '%$lecturer_search%' OR nexus_id LIKE '%$lecturer_search%' OR email LIKE '%$lecturer_search%')";
}

$lecturer_sql .= " ORDER BY full_name ASC";
$lecturers_list = mysqli_query($conn, $lecturer_sql);

// ==================== ASSIGN LECTURER TO STUDENT SECTION ====================

if(isset($_POST['assign'])) {
    $student_id = $_POST['student_id'];
    $selected_lecturers = $_POST['lecturers'] ?? [];
    
    $existingQuery = mysqli_query($conn, "SELECT lecturer_id FROM student_lecturers WHERE student_id = '$student_id'");
    $existing = [];
    while($row = mysqli_fetch_assoc($existingQuery)) {
        $existing[] = $row['lecturer_id'];
    }
    
    $to_remove = array_diff($existing, $selected_lecturers);
    foreach($to_remove as $lecturer_id) {
        mysqli_query($conn, "DELETE FROM student_lecturers WHERE student_id = '$student_id' AND lecturer_id = '$lecturer_id'");
        $leaveQuery = mysqli_query($conn, "SELECT id FROM leave_applications WHERE student_id = '$student_id'");
        while($leave = mysqli_fetch_assoc($leaveQuery)) {
            $leave_id = $leave['id'];
            mysqli_query($conn, "DELETE FROM lecturer_signatures WHERE leave_id = '$leave_id' AND lecturer_id = '$lecturer_id' AND status = 'Pending'");
        }
    }
    
    // Find lecturers to add (selected but not existing)
$to_add = array_diff($selected_lecturers, $existing);
foreach($to_add as $lecturer_id) {
    mysqli_query($conn, "INSERT IGNORE INTO student_lecturers (student_id, lecturer_id) VALUES ('$student_id', '$lecturer_id')");
    
  // After assigning lecturer to student
$message = "You have been assigned as lecturer for student: " . $student_name;
addNotification($conn, $lecturer_id, "New Student Assigned", $message, "assignment");
}
    
    echo "<script>alert('Lecturer(s) assigned successfully');</script>";
}

if(isset($_GET['delete_lecturer'])) {
    $delete_id = $_GET['delete_lecturer'];
    $lecQuery = mysqli_query($conn, "SELECT full_name FROM users WHERE id = '$delete_id'");
    $lecName = mysqli_fetch_assoc($lecQuery);
    
    mysqli_query($conn, "DELETE FROM student_lecturers WHERE lecturer_id = '$delete_id'");
    mysqli_query($conn, "DELETE FROM users WHERE id = '$delete_id' AND role = 'lecturer'");
    
    logActivity($conn, $user['id'], $user['full_name'], $user['role'], 'Account Deletion', 'Deleted lecturer account: ' . ($lecName['full_name'] ?? $delete_id));
    addNotification($conn, $user['id'], 'Lecturer Deleted', 'Deleted lecturer account: ' . ($lecName['full_name'] ?? $delete_id), 'account');
    
    echo "<script>alert('Lecturer deleted successfully'); window.location.href='assign_lecturer.php';</script>";
}

$students = mysqli_query($conn, "SELECT * FROM users WHERE role = 'student' ORDER BY full_name");
$lecturers = mysqli_query($conn, "SELECT * FROM users WHERE role = 'lecturer' ORDER BY full_name");

$assigned = [];
$assignedQuery = mysqli_query($conn, "SELECT student_id, lecturer_id FROM student_lecturers");
while($row = mysqli_fetch_assoc($assignedQuery)) {
    $assigned[$row['student_id']][] = $row['lecturer_id'];
}

$assignedCount = [];
$countQuery = mysqli_query($conn, "SELECT lecturer_id, COUNT(*) as count FROM student_lecturers GROUP BY lecturer_id");
while($row = mysqli_fetch_assoc($countQuery)) {
    $assignedCount[$row['lecturer_id']] = $row['count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Lecturers - Nexus</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .section-title {
            background: #123966;
            color: white;
            padding: 12px 15px;
            border-radius: 8px;
            margin: 30px 0 20px 0;
            font-size: 20px;
        }
        .add-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
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
        .filter-select, .search-input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .search-input {
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
        .data-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 30px;
        }
        .data-table th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
        }
        .data-table td {
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
        .action-btn {
            padding: 4px 8px;
            border-radius: 3px;
            text-decoration: none;
            font-size: 12px;
            display: inline-block;
            margin: 0 2px;
        }
        .edit-btn { background: #ffc107; color: black; }
        .delete-btn { background: #dc3545; color: white; }
        .assign-count {
            background: #123966;
            color: white;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 12px;
            display: inline-block;
        }
        .lecturer-checkbox-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
            max-height: 150px;
            overflow-y: auto;
            border: 1px solid #ccc;
            padding: 10px;
            border-radius: 5px;
            background: #f9f9f9;
        }
        .lecturer-checkbox-group label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            cursor: pointer;
        }
        .assign-btn {
            background: #123966;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
        }
        .assigned-list {
            font-size: 12px;
            color: green;
            margin-top: 5px;
        }
        .table-cell {
            vertical-align: top;
        }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
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
        <div class="nav-item has-submenu submenu-open" onclick="toggleSubmenu(this)">
            <div class="nav-link">
                <i class="fa-solid fa-users nav-fa"></i> User Management
                <i class="fa-solid fa-chevron-right chevron"></i>
            </div>
            <div class="submenu">
                <a href="manage_students.php"><i class="fa-solid fa-user-graduate"></i> Manage Students</a>
                <a href="assign_lecturer.php" class="active-sub"><i class="fa-solid fa-chalkboard-user"></i> Manage Lecturers</a>
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
            
            <!-- SECTION 1: MANAGE LECTURERS -->
            <div class="section-title">
                <i class="fa-solid fa-chalkboard-user"></i> Manage Lecturers
            </div>
            
            <div class="section-header">
                <div></div>
                <a href="add_lecturer.php" class="add-btn"><i class="fa-solid fa-plus"></i> Add Lecturer</a>
            </div>
            
            <div class="filter-bar">
                <div class="filter-left">
                    <span>Search:</span>
                    <form method="GET" style="display: flex; gap: 10px;">
                        <input type="text" name="lecturer_search" class="search-input" placeholder="Search lecturers..." value="<?php echo htmlspecialchars($lecturer_search); ?>">
                        <button type="submit" class="search-btn"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
                    </form>
                </div>
                <div class="filter-left">
                    <span>Filter:</span>
                    <form method="GET" style="display: flex; gap: 10px;">
                        <select name="lecturer_filter" class="filter-select" onchange="this.form.submit()">
                            <option value="all" <?php echo $lecturer_filter == 'all' ? 'selected' : ''; ?>>All Lecturers</option>
                            <option value="active" <?php echo $lecturer_filter == 'active' ? 'selected' : ''; ?>>Active Lecturers</option>
                            <option value="inactive" <?php echo $lecturer_filter == 'inactive' ? 'selected' : ''; ?>>Inactive Lecturers</option>
                        </select>
                        <?php if(!empty($lecturer_search)): ?>
                            <input type="hidden" name="lecturer_search" value="<?php echo htmlspecialchars($lecturer_search); ?>">
                        <?php endif; ?>
                    </form>
                </div>
            </div>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Lecturer Name</th>
                        <th>NexusID</th>
                        <th>Email</th>
                        <th>Assigned Students</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if(mysqli_num_rows($lecturers_list) > 0): ?>
                    <?php while($lecturer = mysqli_fetch_assoc($lecturers_list)): 
                        $assigned_students = $assignedCount[$lecturer['id']] ?? 0;
                    ?>
                        <tr>
                            <td><?php echo $lecturer['full_name']; ?></td>
                            <td><?php echo $lecturer['nexus_id']; ?></td>
                            <td><?php echo $lecturer['email']; ?></td>
                            <td><span class="assign-count"><?php echo $assigned_students; ?> students</span></td>
                            <td>
                                <span class="status-badge <?php echo ($lecturer['status'] ?? 'active') == 'active' ? 'status-active' : 'status-inactive'; ?>">
                                    <?php echo ($lecturer['status'] ?? 'active') == 'active' ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td>
                                <a href="edit_lecturer.php?id=<?php echo $lecturer['id']; ?>" class="action-btn edit-btn">Edit</a>
                                <a href="assign_lecturer.php?delete_lecturer=<?php echo $lecturer['id']; ?>" class="action-btn delete-btn" onclick="return confirm('Delete this lecturer?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align:center; padding:40px;">No lecturers found.赶
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
            
            <!-- SECTION 2: ASSIGN LECTURER TO STUDENT -->
            <div class="section-title" style="margin-top: 40px;">
                <i class="fa-solid fa-user-tag"></i> Assign Lecturer to Student
            </div>
            <p style="color:#666; margin-bottom:20px;">Select multiple lecturers for each student.</p>
            
            <div class="history-table-box">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>NexusID</th>
                            <th>Current Lecturer(s)</th>
                            <th>Assign New Lecturer(s)</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php 
                    mysqli_data_seek($students, 0);
                    while($student = mysqli_fetch_assoc($students)) { 
                        $student_id = $student['id'];
                        $current_lecturers = [];
                        if(isset($assigned[$student_id])) {
                            $lecIds = $assigned[$student_id];
                            $lecIdsStr = implode(',', $lecIds);
                            $lecNamesQuery = mysqli_query($conn, "SELECT full_name FROM users WHERE id IN ($lecIdsStr)");
                            while($lec = mysqli_fetch_assoc($lecNamesQuery)) {
                                $current_lecturers[] = $lec['full_name'];
                            }
                        }
                        $current_lecturers_str = !empty($current_lecturers) ? implode(', ', $current_lecturers) : 'Not Assigned';
                    ?>
                        <form method="POST">
                            <input type="hidden" name="student_id" value="<?php echo $student_id; ?>">
                            <tr>
                                <td><?php echo $student['full_name']; ?></td>
                                <td><?php echo $student['nexus_id']; ?></td>
                                <td class="table-cell">
                                    <div class="assigned-list"><?php echo $current_lecturers_str; ?></div>
                                </td>
                                <td class="table-cell">
                                    <div class="lecturer-checkbox-group">
                                        <?php 
                                        mysqli_data_seek($lecturers, 0);
                                        while($lec = mysqli_fetch_assoc($lecturers)) { 
                                            $checked = (isset($assigned[$student_id]) && in_array($lec['id'], $assigned[$student_id])) ? 'checked' : '';
                                        ?>
                                            <label>
                                                <input type="checkbox" name="lecturers[]" value="<?php echo $lec['id']; ?>" <?php echo $checked; ?>>
                                                <?php echo $lec['full_name']; ?>
                                            </label>
                                        <?php } ?>
                                    </div>
                                </td>
                                <td><button type="submit" name="assign" class="assign-btn">Assign</button></td>
                            </tr>
                        </form>
                    <?php } ?>
                    </tbody>
                </table>
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
    if(wrapper && !wrapper.contains(event.target)) {
        document.getElementById('notificationDropdown').style.display = 'none';
    }
});


</script>

</body>
</html>