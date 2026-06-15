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
    SELECT la.*, u.full_name as student_name, u.nexus_id, u.email
    FROM leave_applications la
    JOIN users u ON la.student_id = u.id
    WHERE DATE(la.created_at) BETWEEN '$start_date' AND '$end_date'
    ORDER BY la.created_at DESC
");

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="absence_summary_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');

// Add UTF-8 BOM
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Title
fputcsv($output, ['NEXUS EDULEAVE SYSTEM']);
fputcsv($output, ['Absence Summary Report']);
fputcsv($output, ['Report Period: ' . date("d M Y", strtotime($start_date)) . ' - ' . date("d M Y", strtotime($end_date))]);
fputcsv($output, ['Generated on: ' . date("d M Y H:i:s")]);
fputcsv($output, []);
fputcsv($output, []);

// Summary
fputcsv($output, ['REPORT SUMMARY']);
fputcsv($output, ['Total Applications', $summary['total'] ?? 0]);
fputcsv($output, ['Approved', $summary['approved'] ?? 0]);
fputcsv($output, ['Rejected', $summary['rejected'] ?? 0]);
fputcsv($output, ['Pending', $summary['pending'] ?? 0]);
fputcsv($output, ['Average Leave Duration', ($summary['avg_duration'] ? round($summary['avg_duration'], 1) : 0) . ' Days']);
fputcsv($output, ['Most Common Leave Type', $most_common_type]);
fputcsv($output, []);
fputcsv($output, []);

// Detailed Applications
fputcsv($output, ['DETAILED LEAVE APPLICATIONS']);
fputcsv($output, ['Student Name', 'NexusID', 'Email', 'Leave Type', 'Duration', 'Start Date', 'End Date', 'Applied Date', 'Status']);

if(mysqli_num_rows($detailsQuery) > 0) {
    while($row = mysqli_fetch_assoc($detailsQuery)) {
        fputcsv($output, [
            $row['student_name'],
            $row['nexus_id'],
            $row['email'],
            $row['leave_type'],
            $row['duration'],
            date("d M Y", strtotime($row['start_date'])),
            date("d M Y", strtotime($row['end_date'])),
            date("d M Y", strtotime($row['created_at'])),
            $row['status']
        ]);
    }
} else {
    fputcsv($output, ['No leave applications found']);
}

fclose($output);
?>