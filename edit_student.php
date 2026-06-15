<?php
include 'db_config.php';
include 'log_activity.php';

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$student_id = $_GET['id'] ?? 0;
$studentQuery = mysqli_query($conn, "SELECT * FROM users WHERE id = '$student_id' AND role = 'student'");
$student = mysqli_fetch_assoc($studentQuery);

if(!$student) {
    header("Location: manage_students.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    mysqli_query($conn, "UPDATE users SET full_name = '$full_name', email = '$email', username = '$username', status = '$status' WHERE id = '$student_id'");
    
    // Log activity AFTER successful update
    logActivity($conn, $_SESSION['user']['id'], $_SESSION['user']['full_name'], $_SESSION['user']['role'], 'Account Update', 'Updated student account: ' . $full_name);
    
    echo "<script>alert('Student updated successfully!'); window.location.href='manage_students.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Student - Nexus</title>
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
        .readonly-field {
            background: #e9ecef;
            cursor: not-allowed;
        }
        .btn-save {
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
    </style>
</head>
<body>

<div class="form-card">
    <div class="form-header">
        <h1><i class="fa-solid fa-pen"></i> Edit Student</h1>
    </div>
    
    <div class="form-body">
        <form method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" value="<?php echo htmlspecialchars($student['full_name']); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Nexus ID</label>
                <input type="text" value="<?php echo $student['nexus_id']; ?>" class="readonly-field" readonly disabled>
            </div>
            
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($student['username']); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="active" <?php echo ($student['status'] ?? 'active') == 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo ($student['status'] ?? 'active') == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            
            <button type="submit" class="btn-save">Save Changes</button>
            <a href="manage_students.php" class="btn-cancel">Cancel</a>
        </form>
    </div>
</div>

</body>
</html>