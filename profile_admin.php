<?php
include 'db_config.php';
include 'log_activity.php';


if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];
$userId = $user['id'];

logActivity($conn, $user['id'], $user['full_name'], $user['role'], 'Page View', 'Viewed Admin Profile');

$update_success = false;
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $newName = $_POST['full_name'];
    $newEmail = $_POST['email'];
    $newUsername = $_POST['username'];

    $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, username = ? WHERE id = ?");
    $stmt->bind_param("sssi", $newName, $newEmail, $newUsername, $userId);
    
    if ($stmt->execute()) {
        $_SESSION['user']['full_name'] = $newName;
        $_SESSION['user']['email'] = $newEmail;
        $_SESSION['user']['username'] = $newUsername;
        $user = $_SESSION['user'];
        $update_success = true;
        logActivity($conn, $user['id'], $user['full_name'], $user['role'], 'Account Update', 'Updated profile information');
        addNotification($conn, $user['id'], 'Profile Updated', 'Your profile information has been updated successfully.', 'account');
    }
}

$edit_mode = isset($_GET['edit']) && $_GET['edit'] == 1;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Profile - Nexus</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .edit-mode-input {
            width: 100%;
            padding: 8px 12px;
            font-size: 18px;
            font-family: 'Baloo 2', cursive;
            border: 1px solid #ccc;
            border-radius: 6px;
            margin-top: 5px;
        }
        .profile-info-edit-mode p {
            margin-bottom: 15px;
        }
        .profile-info-edit-mode label {
            font-weight: 700;
            color: #123966;
            display: inline-block;
            width: 120px;
        }
        .save-btn {
            background: #28a745;
            color: white;
            border: none;
            border-radius: 35px;
            padding: 10px 25px;
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            margin-right: 10px;
        }
        .cancel-btn {
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 35px;
            padding: 10px 25px;
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
        }
        .success-msg {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        .profile-card {
            background: rgba(255,255,255,0.92);
            border-radius: 12px;
            padding: 40px;
            min-height: calc(100vh - 140px);
            position: relative;
        }
        .profile-layout {
            display: grid;
            grid-template-columns: 220px 1fr;
            gap: 35px;
        }
        .profile-icon {
            font-size: 190px;
            color: #555;
            line-height: 1;
            text-align: center;
        }
        .profile-info p {
            color: #123966;
            font-size: 25px;
            font-weight: 800;
            margin: 0 0 22px;
        }
        .profile-badge {
            display: inline-block;
            border: 1px solid #cfcfcf;
            background: white;
            border-radius: 8px;
            padding: 12px 28px;
            color: #123966;
            font-size: 25px;
            font-weight: 800;
            margin: 10px 0;
        }
        .profile-actions {
            position: absolute;
            left: 35px;
            bottom: 25px;
            display: flex;
            gap: 25px;
        }
        .profile-btn {
            font-family: 'Baloo 2', cursive;
            font-size: 22px;
            font-weight: 700;
            text-decoration: none;
            padding: 12px 28px;
            border: 2px solid #bdbdbd;
            border-radius: 35px;
            background: white;
            color: #333;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 55px;
            min-width: 180px;
        }
        .account-created {
            position: absolute;
            right: 35px;
            bottom: 32px;
            color: #777;
            font-size: 23px;
            font-weight: 800;
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

        <a href="profile_admin.php" class="nav-link active">
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
           
            <span style="color:#ccc; font-size:24px;">|</span>
            <a href="logout.php" class="logout-link"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
        </div>
    </div>

    <div class="dashboard-content">
        <div class="profile-card">
            <?php if($update_success): ?>
                <div class="success-msg">Profile updated successfully!</div>
            <?php endif; ?>

            <div class="profile-layout">
                <div class="profile-icon">
                    <i class="fa-regular fa-circle-user"></i>
                </div>

                <?php if($edit_mode): ?>
                    <form method="POST" class="profile-info-edit-mode">
                        <p>
                            <label><strong>Full Name:</strong></label><br>
                            <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" class="edit-mode-input" required>
                        </p>
                        <p>
                            <label><strong>NexusID:</strong></label><br>
                            <input type="text" value="<?php echo htmlspecialchars($user['nexus_id']); ?>" class="edit-mode-input" disabled>
                        </p>
                        <p>
                            <label><strong>Email:</strong></label><br>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="edit-mode-input" required>
                        </p>
                        <p>
                            <label><strong>Username:</strong></label><br>
                            <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" class="edit-mode-input" required>
                        </p>
                        <div style="margin-top: 25px;">
                            <button type="submit" name="update_profile" class="save-btn"><i class="fa-solid fa-save"></i> Save Changes</button>
                            <a href="profile_admin.php" class="cancel-btn"><i class="fa-solid fa-times"></i> Cancel</a>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="profile-info">
                        <p><strong>Full Name:</strong> <?php echo strtoupper($user['full_name']); ?></p>
                        <p><strong>NexusID:</strong> <?php echo strtoupper($user['nexus_id']); ?></p>
                        <p><strong>Email:</strong> <?php echo $user['email']; ?></p>
                        <p><strong>Username:</strong> <?php echo $user['username']; ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Status dan Account Type - HANYA NAMPAK BUKAN DALAM EDIT MODE -->
            <?php if(!$edit_mode): ?>
                <div style="margin-top:15px;">
                    <div class="profile-badge">Status: Active</div>
                    <br>
                    <div class="profile-badge">Account Type: Admin</div>
                </div>
            <?php endif; ?>

            <div class="profile-actions">
                <?php if($edit_mode): ?>
                    <a href="profile_admin.php" class="profile-btn">View Profile</a>
                <?php else: ?>
                    <a href="profile_admin.php?edit=1" class="profile-btn">Edit Profile</a>
                    <a href="change_password.php" class="profile-btn">Change Password</a>
                <?php endif; ?>
            </div>

            <div class="account-created">
                Account Created: <?php echo date("d M Y", strtotime($user['created_at'])); ?>
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