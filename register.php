<?php
include 'db_config.php';
$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullName = $_POST['fullName'];
    $nexusId = strtoupper($_POST['nexusId']);
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    $role = "";
    if(strpos($nexusId, 'STU') === 0) $role = "student";
    elseif(strpos($nexusId, 'LEC') === 0) $role = "lecturer";
    elseif(strpos($nexusId, 'ADM') === 0) $role = "admin";

    if(!$role) { 
        $msg = "NexusID must start with STU, LEC or ADM."; 
    } else {
        $stmt = $conn->prepare("INSERT INTO users (full_name, nexus_id, email, username, password, role) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param("ssssss", $fullName, $nexusId, $email, $username, $password, $role);
        if($stmt->execute()) { 
            header("Location: login.php"); 
        } else { 
            $msg = "Error: Nexus ID or Email already exists."; 
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Nexus Register</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="register-box">
    <div class="register-left">
        <h1>Sign Up</h1>
        <form method="POST">
            <label>Full Name</label>
            <input type="text" name="fullName" required>
            
            <label>Nexus ID</label>
            <input type="text" name="nexusId" required placeholder="STUxxxx / LECxxxx / ADMxxxx">
            
            <label>Email</label>
            <input type="email" name="email" required>
            
            <label>Username</label>
            <input type="text" name="username" required>
            
            <label>Password</label>
            <input type="password" name="password" required>
            
            <button type="submit">Register</button>
            <p id="msg"><?php echo $msg; ?></p>
        </form>
    </div>
    <div class="register-right">
        <img src="images/logo.png" class="logo">
        <h2>Already have an account?</h2>
        <a href="login.php" class="outline-btn">Login</a>
    </div>
</div>
</body>
</html>