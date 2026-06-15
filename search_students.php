<?php
include 'db_config.php';

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'lecturer') {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];
$search = "";

if(isset($_GET['search'])) {
    $search = trim($_GET['search']);
}

function highlightText($text, $search) {
    if($search == "") {
        return $text;
    }
    return preg_replace(
        "/(" . preg_quote($search, '/') . ")/i",
        "<span style='background:yellow; padding:2px 4px; border-radius:4px;'>$1</span>",
        $text
    );
}

// Get all students (no filter)
$sql = "SELECT * FROM users WHERE role = 'student' ORDER BY full_name ASC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Students - Nexus</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="dashboard-page">

<div class="sidebar">

    <img src="images/logo.png" class="sidebar-logo">

    <div class="sidebar-title">
        EduLeave System
    </div>

    <div class="sidebar-date">
        <?php echo date("l d M Y"); ?>
    </div>

    <nav class="nav-menu">

        <a href="dashboard_lecturer.php" class="nav-link">
            <i class="fa-solid fa-chart-column nav-fa"></i>
            Dashboard
        </a>

        <a href="profile_lecturer.php" class="nav-link">
            <i class="fa-solid fa-user nav-fa"></i>
            Profile
        </a>

        <br>

        <a href="pending_applications.php" class="nav-link">
            <i class="fa-solid fa-envelope nav-fa"></i>
            Pending Applications
        </a>

        <a href="search_students.php" class="nav-link active">
            <i class="fa-solid fa-magnifying-glass nav-fa"></i>
            Search Students
        </a>

    </nav>

</div>

<div class="dashboard-main">
    <div class="topbar">
        <div class="topbar-name"><?php echo strtoupper($user['full_name']); ?> (LECTURER)</div>
        <div class="topbar-right">
    <span style="color:#ccc; font-size:24px;">|</span>
    <a href="logout.php" class="logout-link"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
</div>
    </div>

    <div class="dashboard-content">
        <div class="dashboard-card">
            <h1 style="font-size:48px; margin-bottom:5px;">Search Students</h1>
            <p style="font-size:18px; color:#666;">Find and view student records.</p>

            <form method="GET">
                <div class="search-box" style="width:100%; margin-top:20px; margin-bottom:30px;">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" name="search" placeholder="Search by Student Name or NexusID" class="history-search" style="font-size:18px; width:550px;" value="<?php echo $search; ?>">
                </div>
            </form>

            <h2 style="font-size:42px; margin-bottom:20px;">STUDENT DIRECTORY</h2>

            <div class="history-table-box">
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>ID</th>
                            <th>Leave Applications</th>
                            <th>Latest Status</th>
                            <th>Rejection Reason</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    while($student = mysqli_fetch_assoc($result)) {
                        $studentId = $student['id'];

                        if($search != "") {
                            $searchText = strtolower($student['full_name'] . $student['nexus_id']);
                            if(strpos($searchText, strtolower($search)) === false) {
                                continue;
                            }
                        }

                        $leaveQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM leave_applications WHERE student_id = '$studentId'");
                        $leaveData = mysqli_fetch_assoc($leaveQuery);
                        $totalLeave = $leaveData['total'];

                        $statusQuery = mysqli_query($conn, "SELECT status FROM leave_applications WHERE student_id = '$studentId' ORDER BY created_at DESC LIMIT 1");
                        $statusData = mysqli_fetch_assoc($statusQuery);
                        $latestStatus = $statusData['status'] ?? 'No Applications Yet';
                    ?>
                        <tr>
                            <td style="padding:15px; border:1px solid #ddd;">
                                <?php echo highlightText(strtoupper($student['full_name']), $search); ?>
                            </td>
                            <td style="padding:15px; border:1px solid #ddd;">
                                <?php echo highlightText(strtoupper($student['nexus_id']), $search); ?>
                            </td>
                            <td style="padding:15px; border:1px solid #ddd;">
                                <?php echo highlightText($totalLeave, $search); ?>
                            </td>
                            <td style="padding:15px; border:1px solid #ddd;">
                                <?php
                                $statusLower = strtolower($latestStatus);
                                if($statusLower == "approved") {
                                    echo "<span style='color:limegreen; font-weight:bold;'>" . highlightText($latestStatus, $search) . "</span>";
                                }
                                elseif($statusLower == "rejected") {
                                    echo "<span style='color:red; font-weight:bold;'>" . highlightText($latestStatus, $search) . "</span>";
                                }
                                elseif($statusLower == "pending_approvals") {
                                    echo "<span style='color:#ff8c00; font-weight:bold;'>Pending Approvals</span>";
                                }
                                elseif($statusLower == "partial_signatures") {
                                    echo "<span style='color:#ff8c00; font-weight:bold;'>Partial Approvals</span>";
                                }
                                elseif($statusLower == "approved_form_ready") {
                                    echo "<span style='color:green; font-weight:bold;'>Approved - Form Ready</span>";
                                }
                                elseif($statusLower == "pending") {
                                    echo "<span style='color:orange; font-weight:bold;'>" . highlightText($latestStatus, $search) . "</span>";
                                }
                                else {
                                    echo highlightText($latestStatus, $search);
                                }
                                ?>
                            </td>
                            <td style="padding:15px; border:1px solid #ddd; max-width:200px;">
                                <?php
                                if($latestStatus == "Rejected") {
                                    $reasonQuery = mysqli_query($conn, "SELECT rejection_reason FROM leave_applications WHERE student_id = '$studentId' AND status='Rejected' ORDER BY created_at DESC LIMIT 1");
                                    $reasonData = mysqli_fetch_assoc($reasonQuery);
                                    $rejectReason = $reasonData['rejection_reason'] ?? '-';
                                    echo "<span style='color:red;'>" . htmlspecialchars(substr($rejectReason, 0, 50)) . "</span>";
                                } else {
                                    echo "-";
                                }
                                ?>
                            </td>
                            <td style="padding:15px; border:1px solid #ddd;">
                                <a href="student_details.php?id=<?php echo $studentId; ?>" style="color:#2563eb; font-weight:bold; text-decoration:none;">View</a>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</body>
</html>