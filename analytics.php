<?php
include 'db_config.php';
include 'log_activity.php';


if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];

logActivity($conn, $user['id'], $user['full_name'], $user['role'], 'Page View', 'Viewed Analytics page');

$year = $_GET['year'] ?? date('Y');

$monthlyQuery = mysqli_query($conn, "
    SELECT 
        MONTH(created_at) as month,
        COUNT(*) as count
    FROM leave_applications
    WHERE YEAR(created_at) = '$year'
    GROUP BY MONTH(created_at)
    ORDER BY month ASC
");

$monthlyData = [];
for($i = 1; $i <= 12; $i++) {
    $monthlyData[$i] = 0;
}
while($row = mysqli_fetch_assoc($monthlyQuery)) {
    $monthlyData[$row['month']] = $row['count'];
}

$maxCount = max($monthlyData);
$barMaxWidth = 300;

$typeQuery = mysqli_query($conn, "
    SELECT 
        leave_type,
        COUNT(*) as count
    FROM leave_applications
    GROUP BY leave_type
");

$leaveTypes = [];
$totalLeaves = 0;
while($row = mysqli_fetch_assoc($typeQuery)) {
    $leaveTypes[] = $row;
    $totalLeaves += $row['count'];
}

$allTypes = ['Medical', 'Emergency', 'Long Vacation'];
foreach($allTypes as $at) {
    $found = false;
    foreach($leaveTypes as $lt) {
        if(trim($lt['leave_type']) == $at) {
            $found = true;
            break;
        }
    }
    if(!$found) {
        $leaveTypes[] = ['leave_type' => $at, 'count' => 0, 'percentage' => 0];
    }
}

$totalLeaves = 0;
foreach($leaveTypes as $lt) {
    $totalLeaves += $lt['count'];
}

foreach($leaveTypes as &$type) {
    $type['percentage'] = $totalLeaves > 0 ? round(($type['count'] / $totalLeaves) * 100, 1) : 0;
}

$uniqueTypes = [];
foreach($leaveTypes as $lt) {
    $uniqueTypes[trim($lt['leave_type'])] = $lt;
}
$leaveTypes = array_values($uniqueTypes);

usort($leaveTypes, function($a, $b) {
    $order = ['Medical' => 1, 'Emergency' => 2, 'Long Vacation' => 3];
    return $order[trim($a['leave_type'])] - $order[trim($b['leave_type'])];
});

$totalQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM leave_applications WHERE YEAR(created_at) = '$year'");
$totalYear = mysqli_fetch_assoc($totalQuery);

$yearsQuery = mysqli_query($conn, "SELECT DISTINCT YEAR(created_at) as year FROM leave_applications ORDER BY year DESC");
$years = [];
while($row = mysqli_fetch_assoc($yearsQuery)) {
    $years[] = $row['year'];
}
if(empty($years)) {
    $years = [date('Y')];
}

$monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Analytics - Nexus</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .section-title {
            font-size: 28px;
            font-weight: 700;
            color: #123966;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid #123966;
        }
        .filter-bar {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            align-items: flex-end;
        }
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .filter-group label {
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }
        .filter-group select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            min-width: 120px;
        }
        .filter-btn {
            background: #123966;
            color: white;
            border: none;
            padding: 8px 25px;
            border-radius: 5px;
            cursor: pointer;
            height: 38px;
        }
        .analytics-container {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
            margin-top: 20px;
        }
        .chart-container {
            flex: 2;
            min-width: 300px;
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .pie-container {
            flex: 1;
            min-width: 280px;
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .chart-title {
            font-size: 18px;
            font-weight: 700;
            color: #123966;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e0e0e0;
        }
        .bar-item {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .bar-label {
            width: 40px;
            font-weight: 600;
            color: #555;
        }
        .bar-container {
            flex: 1;
            background: #e9ecef;
            border-radius: 20px;
            height: 30px;
            overflow: hidden;
        }
        .bar {
            background: #28a745;
            height: 30px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 10px;
            color: white;
            font-size: 12px;
            font-weight: bold;
        }
        .bar-value {
            width: 50px;
            text-align: right;
            font-weight: 600;
            color: #555;
        }
        .pie-item {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        .pie-color {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            margin-right: 12px;
        }
        .pie-label {
            flex: 1;
            font-weight: 600;
            color: #333;
        }
        .pie-percent {
            font-weight: 700;
            color: #123966;
            width: 60px;
            text-align: right;
        }
        .pie-count {
            color: #666;
            width: 80px;
            text-align: right;
            font-size: 12px;
        }
        .total-card {
            background: #123966;
            color: white;
            border-radius: 10px;
            padding: 15px 20px;
            text-align: center;
            margin-bottom: 20px;
        }
        .total-card h3 {
            margin: 0;
            font-size: 14px;
            font-weight: normal;
            opacity: 0.9;
        }
        .total-card .number {
            font-size: 32px;
            font-weight: 700;
            margin: 5px 0 0;
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
        <div class="nav-item has-submenu submenu-open" onclick="toggleSubmenu(this)">
            <div class="nav-link">
                <i class="fa-solid fa-chart-simple nav-fa"></i> Reports
                <i class="fa-solid fa-chevron-right chevron"></i>
            </div>
            <div class="submenu">
                <a href="absence_summary.php"><i class="fa-solid fa-file-export"></i> Absence Summary</a>
                <a href="analytics.php" class="active-sub"><i class="fa-solid fa-chart-pie"></i> Analytics</a>
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
            <div class="section-title">
                <i class="fa-solid fa-chart-pie"></i> Leave Analytics
            </div>

            <form method="GET" class="filter-bar">
                <div class="filter-group">
                    <label>Select Year</label>
                    <select name="year">
                        <?php foreach($years as $y): ?>
                            <option value="<?php echo $y; ?>" <?php echo $year == $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <button type="submit" class="filter-btn"><i class="fa-solid fa-chart-simple"></i> Apply</button>
                </div>
            </form>

            <div class="analytics-container">
                <div class="chart-container">
                    <div class="chart-title">
                        <i class="fa-solid fa-chart-line"></i> Applications By Month (<?php echo $year; ?>)
                    </div>
                    <?php for($i = 1; $i <= 12; $i++): 
                        $count = $monthlyData[$i];
                        $percentage = $maxCount > 0 ? ($count / $maxCount) * 100 : 0;
                        $barWidth = $percentage * ($barMaxWidth / 100);
                        $barWidth = min($barWidth, $barMaxWidth);
                    ?>
                        <div class="bar-item">
                            <div class="bar-label"><?php echo $monthNames[$i-1]; ?></div>
                            <div class="bar-container">
                                <?php if($count > 0): ?>
                                    <div class="bar" style="width: <?php echo $barWidth; ?>px;">
                                        <?php echo $count; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="bar" style="width: 0px;"></div>
                                <?php endif; ?>
                            </div>
                            <div class="bar-value"><?php echo $count; ?></div>
                        </div>
                    <?php endfor; ?>
                    
                    <div style="margin-top: 20px; text-align: center; color: #666; font-size: 12px;">
                        Total Applications in <?php echo $year; ?>: <?php echo $totalYear['total']; ?>
                    </div>
                </div>

                <div class="pie-container">
                    <div class="chart-title">
                        <i class="fa-solid fa-chart-pie"></i> Leave Distribution
                    </div>
                    
                    <div class="total-card">
                        <h3>Total Leave Applications</h3>
                        <div class="number"><?php echo $totalLeaves; ?></div>
                    </div>
                    
                    <?php foreach($leaveTypes as $type): 
                        $typeColor = '';
                        if($type['leave_type'] == 'Medical') {
                            $typeColor = '#28a745';
                        } elseif($type['leave_type'] == 'Emergency') {
                            $typeColor = '#ffc107';
                        } elseif($type['leave_type'] == 'Long Vacation') {
                            $typeColor = '#17a2b8';
                        } else {
                            $typeColor = '#28a745';
                        }
                    ?>
                        <div class="pie-item">
                            <div class="pie-color" style="background: <?php echo $typeColor; ?>;"></div>
                            <div class="pie-label"><?php echo $type['leave_type']; ?></div>
                            <div class="pie-percent"><?php echo $type['percentage']; ?>%</div>
                            <div class="pie-count">(<?php echo $type['count']; ?> leaves)</div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div style="margin-top: 20px; text-align: center; position: relative; width: 180px; height: 180px; margin: 20px auto;">
                        <svg width="180" height="180" viewBox="0 0 180 180">
                            <?php
                            $start_angle = 0;
                            $centerX = 90;
                            $centerY = 90;
                            $radius = 80;
                            
                            if($totalLeaves > 0) {
                                $nonZeroTypes = [];
                                foreach($leaveTypes as $type) {
                                    if($type['count'] > 0) {
                                        $nonZeroTypes[] = $type;
                                    }
                                }
                                
                                if(count($nonZeroTypes) == 1) {
                                    $type = $nonZeroTypes[0];
                                    if($type['leave_type'] == 'Medical') {
                                        $color = '#28a745';
                                    } elseif($type['leave_type'] == 'Emergency') {
                                        $color = '#ffc107';
                                    } elseif($type['leave_type'] == 'Long Vacation') {
                                        $color = '#17a2b8';
                                    } else {
                                        $color = '#28a745';
                                    }
                                    ?>
                                    <circle cx="<?php echo $centerX; ?>" cy="<?php echo $centerY; ?>" r="<?php echo $radius; ?>" fill="<?php echo $color; ?>" stroke="white" stroke-width="2"/>
                                    <?php
                                } else {
                                    foreach($nonZeroTypes as $type):
                                        $percentage = $type['percentage'];
                                        $angle = ($percentage / 100) * 360;
                                        $end_angle = $start_angle + $angle;
                                        
                                        $start_rad = deg2rad($start_angle);
                                        $end_rad = deg2rad($end_angle);
                                        
                                        $x1 = $centerX + $radius * cos($start_rad);
                                        $y1 = $centerY + $radius * sin($start_rad);
                                        $x2 = $centerX + $radius * cos($end_rad);
                                        $y2 = $centerY + $radius * sin($end_rad);
                                        
                                        $large_arc = ($angle > 180) ? 1 : 0;
                                        
                                        $path = "M $centerX $centerY L $x1 $y1 A $radius $radius 0 $large_arc 1 $x2 $y2 Z";
                                        
                                        if($type['leave_type'] == 'Medical') {
                                            $typeColor = '#28a745';
                                        } elseif($type['leave_type'] == 'Emergency') {
                                            $typeColor = '#ffc107';
                                        } elseif($type['leave_type'] == 'Long Vacation') {
                                            $typeColor = '#17a2b8';
                                        } else {
                                            $typeColor = '#28a745';
                                        }
                                        ?>
                                        <path d="<?php echo $path; ?>" fill="<?php echo $typeColor; ?>" stroke="white" stroke-width="2"/>
                                        <?php
                                        $start_angle += $angle;
                                    endforeach;
                                }
                            } else {
                                ?>
                                <circle cx="<?php echo $centerX; ?>" cy="<?php echo $centerY; ?>" r="<?php echo $radius; ?>" fill="#e9ecef" stroke="white" stroke-width="2"/>
                                <text x="90" y="95" text-anchor="middle" font-size="12" fill="#999">No data</text>
                                <?php
                            }
                            ?>
                            <circle cx="90" cy="90" r="35" fill="white"/>
                        </svg>
                    </div>
                </div>
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