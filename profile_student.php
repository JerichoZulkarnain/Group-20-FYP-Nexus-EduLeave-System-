<?php
include 'db_config.php';

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];
$userId = $user['id'];

// Handle Profile Update
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
    }
}

$edit_mode = isset($_GET['edit']) && $_GET['edit'] == 1;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Profile - Nexus</title>
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
    </style>
</head>

<body class="dashboard-page">

<div class="sidebar">
    <img src="images/logo.png" class="sidebar-logo">
    <div class="sidebar-title">EduLeave System</div>
    <div class="sidebar-date"><?php echo date("l d M Y"); ?></div>

    <nav class="nav-menu">
        <a href="dashboard_student.php" class="nav-link"><i class="fa-solid fa-house nav-fa"></i> Home</a>
        <a href="profile_student.php" class="nav-link active"><i class="fa-solid fa-user nav-fa"></i> Profile</a>
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
                            <a href="profile_student.php" class="cancel-btn"><i class="fa-solid fa-times"></i> Cancel</a>
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
                    <div class="profile-badge">Account Type: Student</div>
                </div>
            <?php endif; ?>

            <div class="profile-actions">
                <?php if($edit_mode): ?>
                    <a href="profile_student.php" class="profile-btn">View Profile</a>
                <?php else: ?>
                    <a href="profile_student.php?edit=1" class="profile-btn">Edit Profile</a>
                    <a href="change_password.php" class="profile-btn">Change Password</a>
                <?php endif; ?>
            </div>

            <div class="account-created">
                Account Created: <?php echo date("d M Y", strtotime($user['created_at'])); ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>