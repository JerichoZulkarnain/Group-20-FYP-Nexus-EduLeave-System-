<?php
include 'db_config.php';
include 'notification_helper.php';

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];

// Get ONLY lecturers assigned to this student
$student_id = $user['id'];
$lecturersQuery = mysqli_query($conn, "
    SELECT u.id, u.full_name 
    FROM users u
    INNER JOIN student_lecturers sl ON u.id = sl.lecturer_id
    WHERE sl.student_id = '$student_id' AND u.role = 'lecturer'
    ORDER BY u.full_name
");
$lecturers = [];
while($lec = mysqli_fetch_assoc($lecturersQuery)) {
    $lecturers[] = $lec;
}

// ================= SUBMIT LONG VACATION LEAVE =================
if(isset($_POST['submit_long_vacation'])) {
    $student_id = $user['id'];
    $leave_type = $_POST['leave_type'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $reason = $_POST['reason'];
    $student_section = $_POST['student_section'] ?? '';
    
    // Store each row as separate entry with unique subject
    $lecturer_subjects = [];
    for($i = 1; $i <= 9; $i++) {
        $lecturer_id = $_POST["lecturer_$i"] ?? '';
        $subject = $_POST["subject_$i"] ?? '';
        if(!empty($lecturer_id) && !empty($subject)) {
            $lecturer_subjects[] = ['lecturer_id' => $lecturer_id, 'subject' => $subject];
        }
    }

    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $days = $start->diff($end)->days + 1;
    $duration = $days . " Days";

    $form_token = bin2hex(random_bytes(16));

    $sql = "INSERT INTO leave_applications (student_id, leave_type, start_date, end_date, duration, reason, student_section, status, form_token) 
            VALUES ('$student_id', '$leave_type', '$start_date', '$end_date', '$duration', '$reason', '$student_section', 'Pending_Approvals', '$form_token')";
    
    if(mysqli_query($conn, $sql)) {
        $leave_id = mysqli_insert_id($conn);
        
        foreach($lecturer_subjects as $item) {
            $lecturer_id = $item['lecturer_id'];
            $subject = mysqli_real_escape_string($conn, $item['subject']);
            mysqli_query($conn, "INSERT INTO lecturer_signatures (leave_id, lecturer_id, status, subject) VALUES ('$leave_id', '$lecturer_id', 'Pending', '$subject')");
         // ADD NOTIFICATION FOR LECTURER
    $message = $user['full_name'] . " has submitted a " . $leave_type . " leave application for subject: " . $subject;
    addNotification($conn, $lecturer_id, "New Leave Application", $message, "leave");
            }
        
        echo "<script>alert('Long Vacation Leave Application Submitted Successfully! Waiting for lecturer approvals.');</script>";
        echo "<script>window.location.href='track_status.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

// ================= REGULAR LEAVE (Medical/Emergency) =================
if(isset($_POST['submit_leave'])) {
    $student_id = $user['id'];
    $leave_type = $_POST['leave_type'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $reason = $_POST['reason'];
    $student_section = $_POST['student_section'] ?? '';
    
    // Store each row as separate entry with unique subject
    $lecturer_subjects = [];
    for($i = 1; $i <= 9; $i++) {
        $lecturer_id = $_POST["reg_lecturer_$i"] ?? '';
        $subject = $_POST["reg_subject_$i"] ?? '';
        if(!empty($lecturer_id) && !empty($subject)) {
            $lecturer_subjects[] = ['lecturer_id' => $lecturer_id, 'subject' => $subject];
        }
    }
    
    // VALIDATION: Must select at least one lecturer
    if(empty($lecturer_subjects)) {
        echo "<script>alert('Error: Please select at least one lecturer and subject for approval!'); window.history.back();</script>";
        exit();
    }

    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $days = $start->diff($end)->days + 1;
    $duration = $days . " Days";

    $sql = "INSERT INTO leave_applications (student_id, leave_type, start_date, end_date, duration, reason, student_section, status) 
            VALUES ('$student_id', '$leave_type', '$start_date', '$end_date', '$duration', '$reason', '$student_section', 'Pending_Approvals')";

    if(mysqli_query($conn, $sql)) {
        $leave_id = mysqli_insert_id($conn);
        
        foreach($lecturer_subjects as $item) {
            $lecturer_id = $item['lecturer_id'];
            $subject = mysqli_real_escape_string($conn, $item['subject']);
            mysqli_query($conn, "INSERT INTO lecturer_signatures (leave_id, lecturer_id, status, subject) VALUES ('$leave_id', '$lecturer_id', 'Pending', '$subject')");
         // ADD NOTIFICATION FOR LECTURER
    $message = $user['full_name'] . " has submitted a " . $leave_type . " leave application for subject: " . $subject;
    addNotification($conn, $lecturer_id, "New Leave Application", $message, "leave");
            }
        
        echo "<script>alert('Leave Application Submitted Successfully');</script>";
        echo "<script>window.location.href='track_status.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

// ================= UPLOAD MC =================
if(isset($_POST['upload_mc'])) {
    $targetDir = "uploads/mc/";
    if(!is_dir($targetDir)) { mkdir($targetDir, 0777, true); }

    $fileName = basename($_FILES["mc_file"]["name"]);
    $targetFile = $targetDir . time() . "_" . $fileName;
    $fileSize = $_FILES["mc_file"]["size"];
    $fileTmp = $_FILES["mc_file"]["tmp_name"];
    $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    $allowedTypes = ['pdf', 'jpg', 'jpeg', 'png'];
    $maxSize = 100 * 1024 * 1024;

    if(!in_array($fileType, $allowedTypes)) {
        echo "<script>alert('Only PDF, JPG, JPEG, PNG allowed');</script>";
    } else if($fileSize > $maxSize) {
        echo "<script>alert('File exceeds 100MB limit');</script>";
    } else {
        if(move_uploaded_file($fileTmp, $targetFile)) {
            $student_id = $user['id'];
            mysqli_query($conn, "UPDATE leave_applications SET mc_file='$targetFile' WHERE student_id='$student_id' ORDER BY id DESC LIMIT 1");
            echo "<script>alert('MC uploaded successfully');</script>";
        } else {
            echo "<script>alert('Upload failed');</script>";
        }
    }
}

// ================= UPLOAD EVIDENCE =================
if(isset($_POST['upload_evidence'])) {
    $targetDir = "uploads/evidence/";
    if(!is_dir($targetDir)) { mkdir($targetDir, 0777, true); }

    $fileName = basename($_FILES["evidence_file"]["name"]);
    $targetFile = $targetDir . time() . "_" . $fileName;
    $fileSize = $_FILES["evidence_file"]["size"];
    $fileTmp = $_FILES["evidence_file"]["tmp_name"];
    $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    $allowedTypes = ['pdf', 'jpg', 'jpeg', 'png', 'mp4', 'mov', 'avi', 'zip'];
    $maxSize = 100 * 1024 * 1024;

    if(!in_array($fileType, $allowedTypes)) {
        echo "<script>alert('Allowed: PDF, JPG, JPEG, PNG, MP4, MOV, AVI, ZIP');</script>";
    } else if($fileSize > $maxSize) {
        echo "<script>alert('File exceeds 100MB limit');</script>";
    } else {
        if(move_uploaded_file($fileTmp, $targetFile)) {
            $student_id = $user['id'];
            $description = $_POST['evidence_description'] ?? '';
            $stmt = $conn->prepare("INSERT INTO evidence (student_id, file_path, description, uploaded_at) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("iss", $student_id, $targetFile, $description);
            $stmt->execute();
            echo "<script>alert('Evidence uploaded successfully');</script>";
        } else {
            echo "<script>alert('Upload failed');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Apply Leave - Nexus</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .tab-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
            border-bottom: 2px solid #ddd;
            padding-bottom: 10px;
        }
        .tab-btn {
            background: none;
            border: none;
            padding: 10px 25px;
            font-size: 18px;
            font-weight: 700;
            font-family: 'Baloo 2', cursive;
            cursor: pointer;
            border-radius: 30px;
            transition: 0.3s;
        }
        .tab-btn.active {
            background: #123966;
            color: white;
        }
        .tab-btn:not(.active) {
            background: #f0f0f0;
            color: #555;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .upload-box {
            border: 1px solid #cfcfcf;
            border-radius: 8px;
            background: #f2f2f2;
            height: 200px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: 0.2s;
        }
        .drag-active {
            background: #e5eefc;
            border: 2px dashed #7ea6dc;
        }
        .upload-icon {
            font-size: 60px;
            color: black;
            margin-bottom: 10px;
        }
        .upload-text {
            font-size: 20px;
            margin: 0;
        }
        .browse-text {
            margin-top: 8px;
            color: #123966;
            font-size: 18px;
            cursor: pointer;
        }
        .uploaded-file {
            border: 1px solid #d0d0d0;
            border-radius: 8px;
            background: white;
            padding: 10px 15px;
            margin-top: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .file-icons {
            display: flex;
            gap: 15px;
            font-size: 20px;
            cursor: pointer;
        }
        .lecturer-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            align-items: center;
            flex-wrap: wrap;
        }
        .lecturer-select {
            flex: 1;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
            font-family: 'Baloo 2', cursive;
        }
        .subject-input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
            font-family: 'Baloo 2', cursive;
        }
        .lecturer-label {
            width: 30px;
            font-weight: bold;
            color: #123966;
        }
        .lecturer-container {
            margin-top: 20px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 8px;
            background: #f9f9f9;
        }
        .lecturer-container h3 {
            margin-top: 0;
            margin-bottom: 15px;
            color: #123966;
            font-size: 18px;
        }
        .student-section-input {
            margin: 15px 0;
            padding: 10px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .student-section-input label {
            font-weight: bold;
            margin-right: 15px;
        }
        .student-section-input input {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            width: 100px;
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
        <a href="profile_student.php" class="nav-link"><i class="fa-solid fa-user nav-fa"></i> Profile</a>
        <br>
        <a href="apply_leave.php" class="nav-link active"><i class="fa-solid fa-file-pen nav-fa"></i> Apply Leave</a>
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
        <div class="dashboard-card">
            <h1 style="font-size:42px; margin-bottom:5px;">Leave Application Form</h1>
            <p style="font-size:18px; color:#666;">Complete the form below to submit your leave request.</p>

            <div class="student-info-box">
                <h3><?php echo strtoupper($user['full_name']); ?></h3>
                <p><?php echo strtoupper($user['nexus_id']); ?></p>
            </div>

            <div class="tab-buttons">
                <button class="tab-btn active" onclick="switchTab('apply')">Regular Leave</button>
                <button class="tab-btn" onclick="switchTab('longvacation')">Long Vacation Leave</button>
                <button class="tab-btn" onclick="switchTab('mc')">Upload MC</button>
                <button class="tab-btn" onclick="switchTab('evidence')">Upload Evidence</button>
            </div>

            <!-- TAB 1: REGULAR LEAVE -->
            <div id="applyTab" class="tab-content active">
                <div class="leave-form-box">
                    <h2>Regular Leave Information</h2>
                    <form method="POST">
                        <label>Leave Type</label><br>
                        <select class="leave-input" name="leave_type" required>
                            <option value="">Select Leave Type</option>
                            <option value="Medical">Medical Leave</option>
                            <option value="Emergency">Emergency Leave</option>
                        </select>
                        <br><br>
                        <div class="date-row">
                            <div>
                                <label>Start Date</label><br>
                                <input type="date" id="startDate" name="start_date" class="leave-input" onchange="calculateDays()" required>
                            </div>
                            <div>
                                <label>End Date</label><br>
                                <input type="date" id="endDate" name="end_date" class="leave-input" onchange="calculateDays()" required>
                            </div>
                            <div class="duration-box">Total Duration : <span id="totalDays">0</span> Days</div>
                        </div>
                        <br>
                        <label>Reason for Leave</label><br>
                        <textarea class="reason-box" name="reason" required></textarea>
                        <br>

                        <!-- Student Section Input -->
                        <div class="student-section-input">
                            <label>Your Section:</label>
                            <input type="text" name="student_section" placeholder="e.g., 40, 50, 60">
                        </div>

                        <!-- Lecturer Selection Section for Regular Leave - 9 Rows -->
                        <div class="lecturer-container">
                            <h3>List of Lecturers & Course Code</h3>
                            
                            <?php for($i = 1; $i <= 9; $i++): ?>
                            <div class="lecturer-row">
                                <div class="lecturer-label"><?php echo $i; ?>.</div>
                                <select name="reg_lecturer_<?php echo $i; ?>" class="lecturer-select">
                                    <option value="">-- Select Lecturer --</option>
                                    <?php foreach($lecturers as $lecturer): ?>
                                        <option value="<?php echo $lecturer['id']; ?>"><?php echo $lecturer['full_name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="text" name="reg_subject_<?php echo $i; ?>" class="subject-input" placeholder="Course Code">
                            </div>
                            <?php endfor; ?>
                        </div>

                        <div style="display: flex; gap: 15px;">
                            <button type="reset" class="reset-btn">Reset</button>
                            <button type="submit" name="submit_leave" class="submit-btn">Submit Application</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- TAB 2: LONG VACATION LEAVE -->
            <div id="longVacationTab" class="tab-content">
                <div class="leave-form-box">
                    <!-- IMPORTANT NOTICE - LONG VACATION -->
<div style="background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%); border-left: 6px solid #dc3545; padding: 20px; margin-bottom: 25px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); display: flex; align-items: center; gap: 18px;">
    <div style="background: #dc3545; width: 55px; height: 55px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
        <i class="fa-solid fa-exclamation" style="color: white; font-size: 28px;"></i>
    </div>
    <div style="flex: 1;">
        <div style="font-weight: 800; color: #dc3545; font-size: 18px; margin-bottom: 5px;">
            IMPORTANT NOTICE
        </div>
        <p style="color: #856404; margin: 0; font-size: 14px; line-height: 1.5;">
            Long Vacation leave applications are NOT valid after the second term (mid-semester break).<br>
            Please ensure your application is submitted before the mid-semester deadline. Applications submitted after the second term will be automatically rejected.
        </p>
    </div>
</div>
                    <h2>Long Vacation Leave Information</h2>
                    <p style="color:#666; margin-bottom:15px;">You need approval from selected lecturers before you can download the form.</p>
                    <form method="POST">
                        <input type="hidden" name="leave_type" value="Long Vacation">
                        <div class="date-row">
                            <div>
                                <label>Start Date</label><br>
                                <input type="date" id="startDate2" name="start_date" class="leave-input" onchange="calculateDays2()" required>
                            </div>
                            <div>
                                <label>End Date</label><br>
                                <input type="date" id="endDate2" name="end_date" class="leave-input" onchange="calculateDays2()" required>
                            </div>
                            <div class="duration-box">Total Duration : <span id="totalDays2">0</span> Days</div>
                        </div>
                        <br>
                        <label>Reason for Leave</label><br>
                        <textarea class="reason-box" name="reason" required></textarea>
                        <br>

                        <!-- Student Section Input -->
                        <div class="student-section-input">
                            <label>Your Section:</label>
                            <input type="text" name="student_section" placeholder="e.g., 40, 50, 60">
                        </div>

                        <!-- Lecturer Selection Section for Long Vacation - 9 Rows -->
                        <div class="lecturer-container">
                            <h3>List of Lecturers & Course Code</h3>
                            
                            <?php for($i = 1; $i <= 9; $i++): ?>
                            <div class="lecturer-row">
                                <div class="lecturer-label"><?php echo $i; ?>.</div>
                                <select name="lecturer_<?php echo $i; ?>" class="lecturer-select">
                                    <option value="">-- Select Lecturer --</option>
                                    <?php foreach($lecturers as $lecturer): ?>
                                        <option value="<?php echo $lecturer['id']; ?>"><?php echo $lecturer['full_name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="text" name="subject_<?php echo $i; ?>" class="subject-input" placeholder="Course Code">
                            </div>
                            <?php endfor; ?>
                        </div>

                        <div style="display: flex; gap: 15px;">
                            <button type="reset" class="reset-btn">Reset</button>
                            <button type="submit" name="submit_long_vacation" class="submit-btn">Submit Long Vacation</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- TAB 3: UPLOAD MC -->
            <div id="mcTab" class="tab-content">
                <div class="leave-form-box">
                    <h2>Upload Medical Certificate (MC)</h2>
                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="upload-box" id="mcDropArea">
                            <i class="fa-regular fa-file-lines upload-icon"></i>
                            <p class="upload-text">Drag & Drop your MC here</p>
                            <label for="mcFileInput" class="browse-text">Click to browse</label>
                            <input type="file" id="mcFileInput" name="mc_file" hidden accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                        <div class="upload-info">
                            <span>Supported: PDF, JPG, JPEG, PNG</span>
                            <span>Max Size: 100MB</span>
                        </div>
                        <div id="mcFileList"></div>
                        <div class="upload-buttons" style="margin-top:20px;">
                            <button type="reset" class="cancel-upload-btn">Cancel</button>
                            <button type="submit" name="upload_mc" class="real-upload-btn"><i class="fa-solid fa-upload"></i> Upload MC</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- TAB 4: UPLOAD EVIDENCE -->
            <div id="evidenceTab" class="tab-content">
                <div class="leave-form-box">
                    <h2>Upload Evidence / Justification</h2>
                    <p style="color:#666; margin-bottom:15px;">For accident photos, police report, medical report, etc.</p>
                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="upload-box" id="evidenceDropArea">
                            <i class="fa-solid fa-camera upload-icon"></i>
                            <p class="upload-text">Drag & Drop your evidence here</p>
                            <label for="evidenceFileInput" class="browse-text">Click to browse</label>
                            <input type="file" id="evidenceFileInput" name="evidence_file" hidden accept=".pdf,.jpg,.jpeg,.png,.mp4,.mov,.avi,.zip">
                        </div>
                        <div class="upload-info">
                            <span>Supported: PDF, JPG, PNG, MP4, MOV, AVI, ZIP</span>
                            <span>Max Size: 100MB</span>
                        </div>
                        <label style="display:block; margin-top:15px; font-size:16px; font-weight:600;">Description (optional)</label>
                        <textarea name="evidence_description" rows="2" style="width:100%; border:1px solid #ccc; border-radius:8px; padding:8px; font-size:14px;"></textarea>
                        <div id="evidenceFileList"></div>
                        <div class="upload-buttons" style="margin-top:20px;">
                            <button type="reset" class="cancel-upload-btn">Cancel</button>
                            <button type="submit" name="upload_evidence" class="real-upload-btn"><i class="fa-solid fa-upload"></i> Upload Evidence</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
function switchTab(tab) {
    document.getElementById('applyTab').classList.remove('active');
    document.getElementById('longVacationTab').classList.remove('active');
    document.getElementById('mcTab').classList.remove('active');
    document.getElementById('evidenceTab').classList.remove('active');
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    
    if(tab === 'apply') {
        document.getElementById('applyTab').classList.add('active');
        document.querySelectorAll('.tab-btn')[0].classList.add('active');
    } else if(tab === 'longvacation') {
        document.getElementById('longVacationTab').classList.add('active');
        document.querySelectorAll('.tab-btn')[1].classList.add('active');
    } else if(tab === 'mc') {
        document.getElementById('mcTab').classList.add('active');
        document.querySelectorAll('.tab-btn')[2].classList.add('active');
    } else if(tab === 'evidence') {
        document.getElementById('evidenceTab').classList.add('active');
        document.querySelectorAll('.tab-btn')[3].classList.add('active');
    }
}

function calculateDays() {
    let start = document.getElementById("startDate").value;
    let end = document.getElementById("endDate").value;
    if(start && end) {
        let startDate = new Date(start);
        let endDate = new Date(end);
        let totalDays = (endDate - startDate) / (1000 * 60 * 60 * 24) + 1;
        if(totalDays > 0) document.getElementById("totalDays").innerHTML = totalDays;
    }
}

function calculateDays2() {
    let start = document.getElementById("startDate2").value;
    let end = document.getElementById("endDate2").value;
    if(start && end) {
        let startDate = new Date(start);
        let endDate = new Date(end);
        let totalDays = (endDate - startDate) / (1000 * 60 * 60 * 24) + 1;
        if(totalDays > 0) document.getElementById("totalDays2").innerHTML = totalDays;
    }
}

// MC Upload drag & drop
const mcDropArea = document.getElementById("mcDropArea");
const mcFileInput = document.getElementById("mcFileInput");
const mcFileList = document.getElementById("mcFileList");

if(mcDropArea) {
    mcDropArea.addEventListener("click", () => mcFileInput.click());
    mcFileInput.addEventListener("change", function() { displayFile(this.files[0], 'mc'); });
    mcDropArea.addEventListener("dragover", (e) => { e.preventDefault(); mcDropArea.classList.add("drag-active"); });
    mcDropArea.addEventListener("dragleave", () => { mcDropArea.classList.remove("drag-active"); });
    mcDropArea.addEventListener("drop", (e) => {
        e.preventDefault();
        mcDropArea.classList.remove("drag-active");
        const file = e.dataTransfer.files[0];
        mcFileInput.files = e.dataTransfer.files;
        displayFile(file, 'mc');
    });
}

// Evidence Upload drag & drop
const evidenceDropArea = document.getElementById("evidenceDropArea");
const evidenceFileInput = document.getElementById("evidenceFileInput");
const evidenceFileList = document.getElementById("evidenceFileList");

if(evidenceDropArea) {
    evidenceDropArea.addEventListener("click", () => evidenceFileInput.click());
    evidenceFileInput.addEventListener("change", function() { displayFile(this.files[0], 'evidence'); });
    evidenceDropArea.addEventListener("dragover", (e) => { e.preventDefault(); evidenceDropArea.classList.add("drag-active"); });
    evidenceDropArea.addEventListener("dragleave", () => { evidenceDropArea.classList.remove("drag-active"); });
    evidenceDropArea.addEventListener("drop", (e) => {
        e.preventDefault();
        evidenceDropArea.classList.remove("drag-active");
        const file = e.dataTransfer.files[0];
        evidenceFileInput.files = e.dataTransfer.files;
        displayFile(file, 'evidence');
    });
}

function displayFile(file, type) {
    const fileURL = URL.createObjectURL(file);
    const fileListDiv = (type === 'mc') ? mcFileList : evidenceFileList;
    fileListDiv.innerHTML = `
        <div class="uploaded-file">
            <div><strong>${file.name}</strong><br><span>${file.name.split('.').pop().toUpperCase()}</span></div>
            <div class="file-icons">
                <a href="${fileURL}" target="_blank"><i class="fa-regular fa-eye"></i></a>
                <i class="fa-solid fa-xmark" onclick="removeFile('${type}')"></i>
            </div>
        </div>
    `;
}

function removeFile(type) {
    if(type === 'mc') {
        mcFileInput.value = "";
        mcFileList.innerHTML = "";
    } else {
        evidenceFileInput.value = "";
        evidenceFileList.innerHTML = "";
    }
}
</script>

</body>
</html>