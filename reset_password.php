<?php
require_once 'db_config.php';

if (!isset($_SESSION['otp_verified']) || !isset($_SESSION['reset_email'])) {
    header("Location: forgot_password.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password         = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $email           = $_SESSION['reset_email'];
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $hashed_password, $email);

        if ($stmt->execute()) {
            session_unset();
            session_destroy();
            header("Location: login.php?reset=success");
            exit();
        } else {
            $error = "Error updating password. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Reset Password — Nexus</title>
  <link rel="stylesheet" href="style.css">
  <style>
    body {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .forgot-box {
        height: auto;
        min-height: auto;
        padding: 45px 50px;
    }
    .forgot-box h1 {
        margin-bottom: 10px;
    }
    .custom-icon-wrap {
        width: 80px;
        height: 80px;
        background: #123966;
        border-radius: 50%;
        margin: 15px auto 25px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .custom-icon-wrap svg {
        width: 40px;
        height: 40px;
        stroke: white;
    }
    .forgot-form-desc {
        font-size: 20px;
        font-weight: 500;
        color: #555;
        margin-bottom: 25px;
        line-height: 1.4;
    }
    .forgot-msg-error {
        color: red;
        font-size: 18px;
        font-weight: 800;
        margin-bottom: 15px;
        text-align: left;
    }
  </style>
</head>
<body>
<div class="forgot-box">
  <h1>NEXUS</h1>
  <div class="custom-icon-wrap">
    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
  </div>
  <h2>Set New Password</h2>
  <p class="forgot-form-desc">Choose a strong, secure new password for your access account.</p>

  <?php if (isset($error)): ?>
    <div class="forgot-msg-error">
      <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>

  <form method="POST" class="forgot-form">
    <label for="password">New Password</label>
    <input type="password" name="password" id="password" placeholder="Minimum 6 characters" style="margin-bottom: 20px;" required/>
    
    <label for="confirm_password">Confirm Password</label>
    <input type="password" name="confirm_password" id="confirm_password" placeholder="Repeat your password" style="margin-bottom: 20px;" required/>
    
    <button type="submit" class="full-outline-btn" style="margin-top: 15px;">Reset Password</button>
  </form>
  <div style="text-align: center;">
    <a href="forgot_password.php" class="back-login">Start Over</a>
  </div>
</div>
</body>
</html>