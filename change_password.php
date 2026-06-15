<?php
include 'db_config.php';

if(!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];
$userId = $user['id'];
$role = $user['role'];

if($_SERVER["REQUEST_METHOD"] == "POST") {

    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // GET CURRENT PASSWORD FROM DATABASE
    $sql = "SELECT password FROM users WHERE id = '$userId'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);

    $currentPassword = $row['password'];

    // CHECK OLD PASSWORD
    if(!password_verify($old_password, $currentPassword)) {
        echo "
        <script>
            alert('Old password is incorrect!');
            window.history.back();
        </script>
        ";
        exit();
    }

    // CHECK CONFIRM PASSWORD
    if($new_password != $confirm_password) {
        echo "
        <script>
            alert('New password and confirm password do not match!');
            window.history.back();
        </script>
        ";
        exit();
    }

    // CHECK PASSWORD LENGTH
    if(strlen($new_password) < 6) {
        echo "
        <script>
            alert('Password must be at least 6 characters!');
            window.history.back();
        </script>
        ";
        exit();
    }

    // HASH NEW PASSWORD
    $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);

    // UPDATE PASSWORD
    $update = "UPDATE users SET password = '$hashedPassword' WHERE id = '$userId'";

    if(mysqli_query($conn, $update)) {
        if($role == 'student') {
            $redirect = 'profile_student.php';
        } elseif($role == 'lecturer') {
            $redirect = 'profile_lecturer.php';
        } else {
            $redirect = 'profile_admin.php';
        }
        echo "
        <script>
            alert('Password changed successfully!');
            window.location.href='$redirect';
        </script>
        ";
    } else {
        echo "
        <script>
            alert('Failed to change password!');
            window.history.back();
        </script>
        ";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Change Password - Nexus</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .change-password-box h1{
            font-size: 46px;
            margin-top: 0;
            margin-bottom: 18px;
        }
        .change-password-box label{
            display: block;
            font-size: 20px;
            margin-top: 10px;
            margin-bottom: 6px;
        }
        .change-pass-field{
            width: 100%;
            max-width: 500px;
            height: 50px;
            border: 2px solid #bdbdbd;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 15px;
            margin-bottom: 10px;
        }
        .change-pass-field input{
            border: none;
            outline: none;
            background: transparent;
            width: 100%;
            font-size: 18px;
            font-family: 'Baloo 2', cursive;
        }
        .change-pass-field i{
            font-size: 22px;
            cursor: pointer;
        }
        .change-password-btn{
            margin-top: 20px;
            width: 250px;
            height: 50px;
            border-radius: 40px;
            border: 2px solid #123966;
            background: white;
            color: #123966;
            font-size: 20px;
            font-weight: 800;
            font-family: 'Baloo 2', cursive;
            cursor: pointer;
        }
        .back-btn {
            display: inline-block;
            margin-top: 15px;
            color: #555;
            text-decoration: none;
            font-size: 16px;
        }
        .change-password-box {
            max-width: 100%;
        }
        .change-pass-field {
            width: 100%;
            max-width: 520px;
        }
        .change-password-btn {
            margin-top: 25px;
        }
        .back-btn {
            display: inline-block;
            margin-top: 25px;
        }
        .profile-card {
            background: rgba(255,255,255,0.92);
            border-radius: 12px;
            padding: 40px;
            min-height: calc(100vh - 140px);
            position: relative;
        }
        
    </style>
</head>

<body class="dashboard-page">

<!-- SIDEBAR - SAMA PERSIS DENGAN PROFILE_LECTURER.PHP -->
<div class="sidebar">

    <img src="images/logo.png" class="sidebar-logo">

    <div class="sidebar-title">
        EduLeave System
    </div>

    <div class="sidebar-date">
        <?php echo date("l d M Y"); ?>
    </div>

    <nav class="nav-menu">
        <?php if($role == 'student'): ?>
            <a href="dashboard_student.php" class="nav-link"><i class="fa-solid fa-house nav-fa"></i> Home</a>
            <a href="profile_student.php" class="nav-link active"><i class="fa-solid fa-user nav-fa"></i> Profile</a>
            <br>
            <a href="apply_leave.php" class="nav-link"><i class="fa-solid fa-file-pen nav-fa"></i> Apply Leave</a>
            <a href="leave_history.php" class="nav-link"><i class="fa-solid fa-clock-rotate-left nav-fa"></i> Leave History</a>
            <a href="track_status.php" class="nav-link"><i class="fa-solid fa-calendar-check nav-fa"></i> Track Status</a>
        <?php elseif($role == 'lecturer'): ?>
            <a href="dashboard_lecturer.php" class="nav-link"><i class="fa-solid fa-chart-column nav-fa"></i> Dashboard</a>
            <a href="profile_lecturer.php" class="nav-link active"><i class="fa-solid fa-user nav-fa"></i> Profile</a>
            <br>
            <a href="pending_applications.php" class="nav-link"><i class="fa-solid fa-envelope nav-fa"></i> Pending Applications</a>
            <a href="search_students.php" class="nav-link"><i class="fa-solid fa-magnifying-glass nav-fa"></i> Search Students</a>
        <?php else: ?>
            <a href="dashboard_admin.php" class="nav-link"><i class="fa-solid fa-chart-column nav-fa"></i> Dashboard</a>
            <a href="profile_admin.php" class="nav-link active"><i class="fa-solid fa-user nav-fa"></i> Profile</a>
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
        <?php endif; ?>
    </nav>

</div>

<div class="dashboard-main">
    <div class="topbar">
        <div class="topbar-name"><?php echo strtoupper($user['full_name']); ?> (<?php echo strtoupper($role); ?>)</div>
        <div class="topbar-right">
            <span style="color:#ccc; font-size:24px;">|</span>
            <a href="logout.php" class="logout-link"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
        </div>
    </div>

    <div class="dashboard-content">
        <div class="profile-card change-password-box">
            <h1>Change Password</h1>
            
            <form method="POST" action="">
                <label>Old Password</label>
                <div class="change-pass-field">
                    <input type="password" name="old_password" id="oldPassword" required>
                    <i class="fa-regular fa-eye-slash" onclick="togglePassword('oldPassword', this)"></i>
                </div>

                <label>New Password</label>
                <div class="change-pass-field">
                    <input type="password" name="new_password" id="newPassword" required>
                    <i class="fa-regular fa-eye-slash" onclick="togglePassword('newPassword', this)"></i>
                </div>

                <label>Confirm New Password</label>
                <div class="change-pass-field">
                    <input type="password" name="confirm_password" id="confirmPassword" required>
                    <i class="fa-regular fa-eye-slash" onclick="togglePassword('confirmPassword', this)"></i>
                </div>

                <div style="text-align: right; margin-top: 20px;">
                    <button type="submit" class="change-password-btn">Change Password</button>
                </div>
                
                <div style="text-align: center; margin-top: 15px;">
                    <?php if($role == 'student'): ?>
                        <a href="profile_student.php" class="back-btn"><i class="fa-solid fa-arrow-left"></i> Back to Profile</a>
                    <?php elseif($role == 'lecturer'): ?>
                        <a href="profile_lecturer.php" class="back-btn"><i class="fa-solid fa-arrow-left"></i> Back to Profile</a>
                    <?php else: ?>
                        <a href="profile_admin.php" class="back-btn"><i class="fa-solid fa-arrow-left"></i> Back to Profile</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function togglePassword(inputId, icon) {
    let input = document.getElementById(inputId);
    if(input.type === "password") {
        input.type = "text";
        icon.className = "fa-regular fa-eye";
    } else {
        input.type = "password";
        icon.className = "fa-regular fa-eye-slash";
    }
}

function toggleSubmenu(element) {
    element.classList.toggle('submenu-open');
}
</script>

</body>
</html>