<?php
include 'db_config.php';

if(!isset($_SESSION['user']) || ($_SESSION['user']['role'] !== 'lecturer' && $_SESSION['user']['role'] !== 'admin')) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];

if(!isset($_GET['id'])) {
    header("Location: assign_lecturer.php");
    exit();
}

$lecturer_id = $_GET['id'];

$lecturerQuery = mysqli_query($conn, "SELECT * FROM users WHERE id = '$lecturer_id' AND role = 'lecturer'");
$lecturer = mysqli_fetch_assoc($lecturerQuery);

if(!$lecturer) {
    header("Location: assign_lecturer.php");
    exit();
}

// Get assigned students for this lecturer
$studentsQuery = mysqli_query($conn, "
    SELECT u.* FROM users u
    INNER JOIN student_lecturers sl ON u.id = sl.student_id
    WHERE sl.lecturer_id = '$lecturer_id'
    ORDER BY u.full_name ASC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lecturer Details - Nexus</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .status-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }
        .assigned-students-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .assigned-students-table th {
            background: #123966;
            color: white;
            padding: 10px;
            text-align: left;
        }
        .assigned-students-table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .back-btn {
            background: #6c757d;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }
    </style>
</head>
<body class="dashboard-page">

<div class="sidebar">
    <img src="images/logo.png" class="sidebar-logo">
    <div class="sidebar-title">EduLeave System</div>
    <div class="sidebar-date"><?php echo date("l d M Y"); ?></div>
    <nav class="nav-menu">
        <?php if($user['role'] == 'lecturer'): ?>
            <a href="dashboard_lecturer.php" class="nav-link"><i class="fa-solid fa-chart-column nav-fa"></i> Dashboard</a>
            <a href="profile_lecturer.php" class="nav-link"><i class="fa-solid fa-user nav-fa"></i> Profile</a>
            <br>
            <a href="pending_applications.php" class="nav-link"><i class="fa-solid fa-envelope nav-fa"></i> Applications</a>
            <a href="search_students.php" class="nav-link"><i class="fa-solid fa-magnifying-glass nav-fa"></i> Search Students</a>
        <?php else: ?>
            <a href="dashboard_admin.php" class="nav-link"><i class="fa-solid fa-chart-column nav-fa"></i> Dashboard</a>
            <a href="profile_admin.php" class="nav-link"><i class="fa-solid fa-user nav-fa"></i> Profile</a>
            <br>
            <a href="assign_lecturer.php" class="nav-link"><i class="fa-solid fa-chalkboard-user nav-fa"></i> Manage Lecturers & Assign</a>
            <a href="manage_students.php" class="nav-link"><i class="fa-solid fa-user-graduate nav-fa"></i> Manage Students</a>
        <?php endif; ?>
    </nav>
</div>

<div class="dashboard-main">
    <div class="topbar">
        <div class="topbar-name"><?php echo strtoupper($user['full_name']); ?> (<?php echo strtoupper($user['role']); ?>)</div>
        <div class="topbar-right">
            <i class="fa-solid fa-bell"></i>
            <span style="color:#ccc; font-size:24px;">|</span>
            <a href="logout.php" class="logout-link"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
        </div>
    </div>

    <div class="dashboard-content">
        <div class="dashboard-card">
            <h1 style="font-size:48px; margin-bottom:20px;">Lecturer Details</h1>

            <div class="profile-layout">
                <div class="profile-icon">
                    <i class="fa-regular fa-circle-user" style="font-size:180px; color:#555;"></i>
                </div>
                <div class="profile-info">
                    <p><strong>Full Name:</strong> <?php echo strtoupper($lecturer['full_name']); ?></p>
                    <p><strong>NexusID:</strong> <?php echo strtoupper($lecturer['nexus_id']); ?></p>
                    <p><strong>Email:</strong> <?php echo $lecturer['email']; ?></p>
                    <p><strong>Username:</strong> <?php echo $lecturer['username']; ?></p>
                    <p><strong>Status:</strong> 
                        <span class="status-badge <?php echo ($lecturer['status'] ?? 'active') == 'active' ? 'status-active' : 'status-inactive'; ?>">
                            <?php echo ($lecturer['status'] ?? 'active') == 'active' ? 'Active' : 'Inactive'; ?>
                        </span>
                    </p>
                </div>
            </div>

            <h2 style="font-size:32px; margin-top:30px;">Assigned Students</h2>
            
            <?php if(mysqli_num_rows($studentsQuery) > 0): ?>
                <table class="assigned-students-table">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>NexusID</th>
                            <th>Email</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($student = mysqli_fetch_assoc($studentsQuery)): ?>
                            <tr>
                                <td><?php echo $student['full_name']; ?></td>
                                <td><?php echo $student['nexus_id']; ?></td>
                                <td><?php echo $student['email']; ?></td>
                                <td>
                                    <span class="status-badge <?php echo ($student['status'] ?? 'active') == 'active' ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo ($student['status'] ?? 'active') == 'active' ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color:#666; padding:20px; text-align:center;">No students assigned to this lecturer.</p>
            <?php endif; ?>

            <?php if($user['role'] == 'admin'): ?>
                <div style="margin-top:30px;">
                    <a href="assign_lecturer.php" class="back-btn">Back to Manage Lecturers</a>
                    <a href="edit_lecturer.php?id=<?php echo $lecturer_id; ?>" class="back-btn" style="background:#ffc107; color:black; margin-left:10px;">Edit Lecturer</a>
                </div>
            <?php else: ?>
                <div style="margin-top:30px;">
                    <a href="dashboard_lecturer.php" class="back-btn">Back to Dashboard</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>