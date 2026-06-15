<?php
include 'db_config.php';

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

if(!isset($_GET['token'])) {
    die("Invalid request.");
}

$token = $_GET['token'];
$user = $_SESSION['user'];

// Get leave details
$leaveQuery = mysqli_query($conn, "
    SELECT la.*, u.full_name as student_name, u.nexus_id, u.email as student_email
    FROM leave_applications la
    JOIN users u ON la.student_id = u.id
    WHERE la.form_token = '$token' AND la.student_id = '{$user['id']}'
");
$leave = mysqli_fetch_assoc($leaveQuery);

if(!$leave) {
    die("Form not found.");
}

// Get all signatures for this leave
$signaturesQuery = mysqli_query($conn, "
    SELECT ls.*, u.full_name as lecturer_name, ls.subject
    FROM lecturer_signatures ls
    JOIN users u ON ls.lecturer_id = u.id
    WHERE ls.leave_id = '{$leave['id']}'
    ORDER BY ls.id ASC
");
$signatures = [];
while($sig = mysqli_fetch_assoc($signaturesQuery)) {
    $signatures[] = $sig;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Long Vacation Form - Nexus</title>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
        }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: #eef2f7;
        }
        .no-print {
            text-align: center;
            margin-bottom: 20px;
        }
        .no-print button {
            background: #123966;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin: 0 5px;
        }
        .form-container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .form-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #123966;
            padding-bottom: 15px;
        }
        .form-header img {
            height: 80px;
            margin-bottom: 10px;
        }
        .form-header h1 {
            color: #123966;
            margin: 0;
            font-size: 26px;
        }
        .form-header h2 {
            color: #555;
            margin: 5px 0 0;
            font-size: 20px;
        }
        .form-section {
            margin-bottom: 25px;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 8px;
        }
        .form-section h3 {
            background: #123966;
            color: white;
            padding: 8px 15px;
            margin: -15px -15px 15px -15px;
            border-radius: 8px 8px 0 0;
            font-size: 16px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 10px;
            border: 1px solid #ddd;
            vertical-align: top;
        }
        .info-table td:first-child {
            width: 200px;
            font-weight: bold;
            background: #f9f9f9;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .signature-table th, .signature-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .signature-table th {
            background: #123966;
            color: white;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 11px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        .status-lulus {
            color: green;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="no-print">
    <button onclick="window.print()"><i class="fa-solid fa-print"></i> Print / Save as PDF</button>
    <button onclick="window.location.href='track_status.php'">Back to Status</button>
</div>

<div class="form-container">
    <div class="form-header">
        <img src="images/logo.png" alt="Nexus EduLeave System">
        <h1>NEXUS EDULEAVE SYSTEM</h1>
        <h2>BORANG CUTI PANJANG PELAJAR</h2>
        <p style="font-size: 12px; color: #666;">(Hendaklah Diisi Dalam 3 Salinan)</p>
    </div>

    <!-- Student Information -->
    <div class="form-section">
        <h3>MAKLUMAT PELAJAR</h3>
        <table class="info-table">
            <tr>
                <td style="width:200px;">Nama Pelajar:</td>
                <td><strong><?php echo strtoupper($leave['student_name']); ?></strong></td>
            </tr>
            <tr>
                <td>No. ID / NexusID:</td>
                <td><?php echo strtoupper($leave['nexus_id']); ?></td>
            </tr>
            <tr>
                <td>Email:</td>
                <td><?php echo $leave['student_email']; ?></td>
            </tr>
            <tr>
                <td>Section:</td>
                <td><strong><?php echo !empty($leave['student_section']) ? htmlspecialchars($leave['student_section']) : '_________________________'; ?></strong></td>
            </tr>
        </table>
    </div>

    <!-- Leave Information -->
    <div class="form-section">
        <h3>MAKLUMAT PERMOHONAN CUTI</h3>
        <table class="info-table">
            <tr>
                <td style="width:200px;">Jenis Cuti:</td>
                <td><strong>Long Vacation (Cuti Panjang)</strong></td>
            </tr>
            <tr>
                <td>Sebab Bercuti:</td>
                <td><?php echo nl2br($leave['reason']); ?></td>
            </tr>
            <tr>
                <td>Bilangan Hari:</td>
                <td><?php echo $leave['duration']; ?></td>
            </tr>
            <tr>
                <td>Tarikh Cuti:</td>
                <td><strong><?php echo date("d/m/Y", strtotime($leave['start_date'])); ?></strong> hingga <strong><?php echo date("d/m/Y", strtotime($leave['end_date'])); ?></strong></td>
            </tr>
            <tr>
                <td>Tarikh Permohonan:</td>
                <td><?php echo date("d/m/Y", strtotime($leave['created_at'])); ?></td>
            </tr>
        </table>
    </div>

    <!-- Lecturer Signatures with Kelulusan column -->
    <div class="form-section">
        <h3>PENGESAHAN PENSYARAH</h3>
        <table class="signature-table">
            <thead>
                <tr>
                    <th style="width:5%">Bil.</th>
                    <th style="width:35%">Nama Pensyarah</th>
                    <th style="width:30%">Kod Kursus / Subjek</th>
                    <th style="width:30%">Kelulusan</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $bil = 1;
                foreach($signatures as $sig): 
                ?>
                <tr>
                    <td><?php echo $bil++; ?></td>
                    <td><?php echo $sig['lecturer_name']; ?></td>
                    <td><?php echo !empty($sig['subject']) ? $sig['subject'] : '_________________________'; ?></td>
                    <td><span class="status-lulus">✓ Lulus</span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Approval Status -->
    <div class="form-section">
        <h3>STATUS KELULUSAN</h3>
        <table class="info-table">
            <tr>
                <td style="width:200px;">Status:</td>
                <td><span style="color:green; font-weight:bold; font-size:16px;">✓ DILULUSKAN (Approved)</span></td>
            </tr>
            <tr>
                <td>Tarikh Kelulusan:</td>
                <td><?php echo date("d/m/Y H:i", strtotime($leave['form_generated_at'])); ?></td>
            </tr>
            <tr>
                <td>Catatan:</td>
                <td>Permohonan cuti panjang ini telah diluluskan oleh semua pensyarah yang berkaitan.</td>
            </tr>
        </table>
    </div>

    <!-- Approval Section -->
    <div class="form-section">
        <h3>DILULUSKAN / TIDAK DILULUSKAN</h3>
        <table class="info-table">
            <tr>
                <td style="width:200px;">Bil. Hari diluluskan:</td>
                <td><?php echo $leave['duration']; ?></td>
            </tr>
            <tr>
                <td>Tarikh:</td>
                <td>Dari <?php echo date("d/m/Y", strtotime($leave['start_date'])); ?> hingga <?php echo date("d/m/Y", strtotime($leave['end_date'])); ?></td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>Dokumen ini dijana secara automatik oleh Nexus EduLeave System.</p>
        <p>Generated on: <?php echo date("d/m/Y H:i:s"); ?></p>
    </div>
</div>

</body>
</html>