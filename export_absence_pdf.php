<?php
include 'db_config.php';

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Get date range from URL
$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-1 month'));
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Get summary statistics
$summaryQuery = mysqli_query($conn, "
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'Approved' OR status = 'Approved_Form_Ready' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) as rejected,
        SUM(CASE WHEN status = 'Pending' OR status = 'Pending_Approvals' OR status = 'Partial_Signatures' THEN 1 ELSE 0 END) as pending,
        AVG(CAST(SUBSTRING_INDEX(duration, ' ', 1) AS UNSIGNED)) as avg_duration
    FROM leave_applications
    WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'
");
$summary = mysqli_fetch_assoc($summaryQuery);

// Get most common leave type
$typeQuery = mysqli_query($conn, "
    SELECT leave_type, COUNT(*) as count
    FROM leave_applications
    WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'
    GROUP BY leave_type
    ORDER BY count DESC
    LIMIT 1
");
$typeResult = mysqli_fetch_assoc($typeQuery);
$most_common_type = $typeResult['leave_type'] ?? '-';
$most_common_type_count = $typeResult['count'] ?? 0;

// Get detailed leave applications
$detailsQuery = mysqli_query($conn, "
    SELECT la.*, u.full_name as student_name, u.nexus_id
    FROM leave_applications la
    JOIN users u ON la.student_id = u.id
    WHERE DATE(la.created_at) BETWEEN '$start_date' AND '$end_date'
    ORDER BY la.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Absence Summary Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #123966;
            padding-bottom: 10px;
        }
        .header h1 {
            color: #123966;
            margin: 0;
            font-size: 24px;
        }
        .header p {
            color: #666;
            margin: 5px 0 0;
        }
        .date-range {
            text-align: center;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: bold;
        }
        .summary-title {
            background: #123966;
            color: white;
            padding: 8px;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .stats-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .stats-table td {
            padding: 10px;
            border: 1px solid #ddd;
        }
        .stats-table td:first-child {
            font-weight: bold;
            width: 250px;
            background: #f5f5f5;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 10px;
        }
        .details-table th {
            background: #123966;
            color: white;
            padding: 8px;
            text-align: left;
            border: 1px solid #123966;
        }
        .details-table td {
            padding: 6px;
            border: 1px solid #ddd;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .value-number {
            font-weight: bold;
            color: #123966;
            font-size: 18px;
        }
        .approved { color: #28a745; font-weight: bold; }
        .rejected { color: #dc3545; font-weight: bold; }
        .pending { color: #ffc107; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>NEXUS EDULEAVE SYSTEM</h1>
        <p>Absence Summary Report</p>
    </div>
    
    <div class="date-range">
        Report Period: <?php echo date("d F Y", strtotime($start_date)); ?> - <?php echo date("d F Y", strtotime($end_date)); ?>
    </div>
    
    <div class="summary-section">
        <div class="summary-title">REPORT SUMMARY</div>
        <table class="stats-table">
            <tr><td>Total Applications</td><td class="value-number"><?php echo $summary['total'] ?? 0; ?></td></tr>
            <tr><td>Approved</td><td class="value-number approved"><?php echo $summary['approved'] ?? 0; ?></td></tr>
            <tr><td>Rejected</td><td class="value-number rejected"><?php echo $summary['rejected'] ?? 0; ?></td></tr>
            <tr><td>Pending</td><td class="value-number pending"><?php echo $summary['pending'] ?? 0; ?></td></tr>
            <tr><td>Average Leave Duration</td><td class="value-number"><?php echo $summary['avg_duration'] ? round($summary['avg_duration'], 1) : 0; ?> Days</td></tr>
            <tr><td>Most Common Leave Type</td><td class="value-number"><?php echo $most_common_type; ?> (<?php echo $most_common_type_count; ?> applications)</td></tr>
        </table>
    </div>
    
    <div class="summary-section">
        <div class="summary-title">DETAILED LEAVE APPLICATIONS</div>
        <table class="details-table">
            <thead><tr><th>Student Name</th><th>NexusID</th><th>Leave Type</th><th>Duration</th><th>Applied Date</th><th>Status</th></tr></thead>
            <tbody>
                <?php if(mysqli_num_rows($detailsQuery) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($detailsQuery)): ?>
                        <tr>
                            <td><?php echo $row['student_name']; ?></td>
                            <td><?php echo $row['nexus_id']; ?></td>
                            <td><?php echo $row['leave_type']; ?></td>
                            <td><?php echo $row['duration']; ?></td>
                            <td><?php echo date("d M Y", strtotime($row['created_at'])); ?></td>
                            <td><?php echo $row['status']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align:center;">No leave applications found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <div class="footer">
        Generated on: <?php echo date("d F Y H:i:s"); ?> | Nexus EduLeave System
    </div>
    
    <script>
        window.print();
    </script>
</body>
</html>