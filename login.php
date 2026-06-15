<?php
include 'db_config.php';

$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nexus_id = trim($_POST['nexus_id']);
    $password = $_POST['password'];

    // FIND USER BY NEXUS_ID
    $stmt = $conn->prepare("SELECT * FROM users WHERE nexus_id = ?");
    $stmt->bind_param("s", $nexus_id);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {

        $user = $result->fetch_assoc();

        // VERIFY PASSWORD
        if (password_verify($password, $user['password'])) {

            // SAVE SESSION
            $_SESSION['user'] = $user;

            // REDIRECT BASED ON ROLE
            if ($user['role'] === 'student') {
                header("Location: dashboard_student.php");
            } elseif ($user['role'] === 'lecturer') {
                header("Location: dashboard_lecturer.php");
            } elseif ($user['role'] === 'admin') {
                header("Location: dashboard_admin.php");
            }
            exit();

        } else {
            $error_msg = "Invalid password.";
        }

    } else {
        $error_msg = "No account found with that Nexus ID.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Nexus</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="login-box">
    <div class="login-top">
        <h1>Sign In</h1>
        <img src="images/logo.png" class="login-logo">
        <div class="create-account">
            <a href="register.php">Create an account</a>
        </div>
    </div>

    <div class="login-bottom">
        <form method="POST" action="login.php">
            <label>Nexus ID</label>
            <input type="text" name="nexus_id" required placeholder="Enter your Nexus ID (STUxxxx, LECxxxx, ADMxxxx)">

            <label>Password</label>
            <input type="password" name="password" required placeholder="Enter your password">

            <div class="login-row">
                <a href="forgot_password.php" class="forgot-link">Forgot password?</a>
                <button type="submit" class="login-btn">Login</button>
            </div>

            <?php if($error_msg): ?>
                <p style="color:#ff8282; text-align:center; margin-top:15px; font-weight:bold;">
                    <?php echo $error_msg; ?>
                </p>
            <?php endif; ?>
        </form>
    </div>
</div>

</body>
</html>