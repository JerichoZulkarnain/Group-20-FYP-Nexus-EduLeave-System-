<?php
require_once 'db_config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once __DIR__ . '/src/Exception.php';
require_once __DIR__ . '/src/PHPMailer.php';
require_once __DIR__ . '/src/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $otp = rand(100000, 999999);
        $_SESSION['reset_otp']   = $otp;
        $_SESSION['reset_email'] = $email;
        $_SESSION['otp_time']    = time();

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = $smtpHost;
            $mail->SMTPAuth   = true;
            $mail->Username   = $smtpUsername;
            $mail->Password   = $smtpPassword;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $smtpPort;

            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer'       => false,
                    'verify_peer_name'  => false,
                    'allow_self_signed' => true,
                ]
            ];

            $mail->setFrom($smtpUsername, $smtpFromName);
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset OTP - Nexus';
            $mail->Body    = "
                <div style='font-family:Arial,sans-serif;max-width:480px;margin:0 auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.08);'>
                    <div style='background:#123966;padding:32px;text-align:center;'>
                        <h1 style='color:#fff;margin:0;font-size:24px;'>Nexus</h1>
                        <p style='color:rgba(255,255,255,0.85);margin:6px 0 0;font-size:14px;'>Password Reset Request</p>
                    </div>
                    <div style='padding:32px;'>
                        <p style='color:#333;font-size:15px;margin-bottom:24px;'>Hi there! Use the OTP below to reset your password. It expires in <strong>10 minutes</strong>.</p>
                        <div style='background:#eef1fc;border-radius:10px;padding:24px;text-align:center;margin-bottom:24px;'>
                            <p style='margin:0;color:#123966;font-size:13px;letter-spacing:.05em;text-transform:uppercase;font-weight:600;'>Your OTP Code</p>
                            <p style='margin:10px 0 0;font-size:42px;font-weight:700;color:#2c2f3a;letter-spacing:10px;'>$otp</p>
                        </div>
                        <p style='color:#888;font-size:13px;'>If you didn't request this, you can safely ignore this email.</p>
                    </div>
                    <div style='background:#f5f6fa;padding:16px;text-align:center;'>
                        <p style='margin:0;color:#aaa;font-size:12px;'>© Nexus Support</p>
                    </div>
                </div>
            ";

            $mail->send();
            $_SESSION['status'] = "OTP sent to <strong>$email</strong>. Check your inbox.";
            header("Location: verify_otp.php");
            exit();
        } catch (Exception $e) {
            $error = "Could not send email. Error: {$mail->ErrorInfo}";
        }
    } else {
        $error = "No account found with that email address.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Forgot Password — Nexus</title>
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
        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
    </div>
    <h2>Forgot Password?</h2>
    <p class="forgot-form-desc">Enter your registered email and we'll send you a 6-digit OTP to reset your password.</p>

    <?php if (isset($error)): ?>
        <div class="forgot-msg-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" class="forgot-form">
        <label for="email">Email Address</label>
        <input type="email" name="email" id="email" placeholder="you@example.com" style="margin-bottom: 20px;" required/>
        <button type="submit" class="full-outline-btn" style="margin-top: 15px;">Send OTP</button>
    </form>
    <div style="text-align: center;">
        <a href="login.php" class="back-login">Back to Login</a>
    </div>
</div>
</body>
</html>