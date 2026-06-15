<?php
include 'db_config.php';

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];
$success_data = null;

if(isset($_SESSION['new_lecturer'])) {
    $success_data = $_SESSION['new_lecturer'];
    unset($_SESSION['new_lecturer']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Lecturer - Nexus</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            background: #eef2f7;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .form-card {
            background: white;
            width: 500px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .form-header {
            background: #123966;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .form-header h1 {
            margin: 0;
            font-size: 24px;
        }
        .form-body {
            padding: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            font-family: inherit;
        }
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .checkbox-group input {
            width: auto;
        }
        .checkbox-group label {
            margin: 0;
        }
        .btn-create {
            background: #28a745;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
        }
        .btn-cancel {
            background: #6c757d;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
            text-align: center;
            display: block;
            text-decoration: none;
        }
        .password-hint {
            font-size: 11px;
            color: #888;
            margin-top: 5px;
        }
        
        /* Success Popup */
        .popup-overlay {
            display: <?php echo $success_data ? 'flex' : 'none'; ?>;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .popup-card {
            background: white;
            width: 450px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .popup-header {
            background: #28a745;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .popup-header i {
            font-size: 48px;
            margin-bottom: 10px;
        }
        .popup-header h2 {
            margin: 0;
            font-size: 22px;
        }
        .popup-body {
            padding: 25px;
        }
        .info-row {
            display: flex;
            margin-bottom: 12px;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .info-label {
            width: 140px;
            font-weight: 600;
            color: #555;
        }
        .info-value {
            flex: 1;
            color: #333;
            font-weight: 500;
        }
        .popup-footer {
            padding: 20px;
            text-align: center;
            border-top: 1px solid #eee;
        }
        .back-btn {
            background: #123966;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
        }
    </style>
</head>
<body>

<div class="form-card">
    <div class="form-header">
        <h1><i class="fa-solid fa-user-plus"></i> Create Lecturer Account</h1>
    </div>
    
    <div class="form-body">
        <form action="process_add_lecturer.php" method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" required placeholder="Enter full name">
            </div>
            
            <div class="form-group">
                <label>Nexus ID</label>
                <input type="text" name="nexus_id" required placeholder="LECxxxx">
                <div class="password-hint">Must start with LEC followed by numbers</div>
            </div>
            
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required placeholder="lecturer@example.com">
            </div>
            
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required placeholder="Enter username">
            </div>
            
            <div class="form-group">
                <label>Temporary Password</label>
                <input type="text" name="temp_password" placeholder="Leave blank to auto-generate">
                <div class="password-hint">Minimum 6 characters. Leave blank for auto-generated password.</div>
            </div>
            
            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            
            <div class="form-group checkbox-group">
                <input type="checkbox" name="send_email" value="1" checked id="sendEmail">
                <label for="sendEmail">Send account details to email</label>
            </div>
            
            <button type="submit" class="btn-create">Create Account</button>
            <a href="assign_lecturer.php" class="btn-cancel">Cancel</a>
        </form>
    </div>
</div>

<!-- Success Popup -->
<?php if($success_data): ?>
<div id="successPopup" class="popup-overlay">
    <div class="popup-card">
        <div class="popup-header">
            <i class="fa-solid fa-check-circle"></i>
            <h2>Lecturer Account Created Successfully</h2>
        </div>
        <div class="popup-body">
            <div class="info-row">
                <div class="info-label">Lecturer:</div>
                <div class="info-value"><?php echo $success_data['full_name']; ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Nexus ID:</div>
                <div class="info-value"><?php echo $success_data['nexus_id']; ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Email:</div>
                <div class="info-value"><?php echo $success_data['email']; ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Username:</div>
                <div class="info-value"><?php echo $success_data['username']; ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Temporary Password:</div>
                <div class="info-value" style="font-family: monospace; font-size: 14px;"><?php echo $success_data['temp_password']; ?></div>
            </div>
        </div>
        <div class="popup-footer">
           <a href="assign_lecturer.php" class="back-btn">Back to Lecturers</a>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.getElementById('successPopup')?.addEventListener('click', function(e) {
    if(e.target === this) {
        window.location.href = 'manage_lecturers.php';
    }
});
</script>

</body>
</html>