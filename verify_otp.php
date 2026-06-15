<?php
require_once 'db_config.php';

if (!isset($_SESSION['reset_otp']) || !isset($_SESSION['reset_email'])) {
    header("Location: forgot_password.php");
    exit();
}

$status = null;
if (isset($_SESSION['status'])) {
    $status = $_SESSION['status'];
    unset($_SESSION['status']);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $entered_otp = trim($_POST['otp']);
    $stored_otp  = $_SESSION['reset_otp'];
    $otp_time    = $_SESSION['otp_time'];

    if (time() - $otp_time > 600) {
        $error = "OTP has expired. Please request a new one.";
        unset($_SESSION['reset_otp'], $_SESSION['otp_time']);
    } elseif ($entered_otp == $stored_otp) {
        $_SESSION['otp_verified'] = true;
        header("Location: reset_password.php");
        exit();
    } else {
        $error = "Invalid OTP. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Verify OTP — Nexus</title>
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
        margin-bottom: 15px;
        line-height: 1.4;
    }
    .email-badge {
        display: inline-block;
        background: #e7e7e7;
        border: 1px solid #bfbfbf;
        color: #123966;
        font-size: 19px;
        font-weight: 800;
        padding: 4px 18px;
        border-radius: 20px;
        margin-bottom: 25px;
    }
    .forgot-msg-error {
        color: red;
        font-size: 18px;
        font-weight: 800;
        margin-bottom: 15px;
        text-align: left;
    }
    .status-msg-success {
        color: green;
        font-size: 18px;
        font-weight: 800;
        margin-bottom: 15px;
        text-align: left;
    }
    .otp-digits-container {
        display: flex;
        justify-content: center; /* Centered layout properties */
        gap: 12px;
        margin: 15px auto 35px;
        max-width: 100%;
    }
    .otp-digits-container input {
        width: 55px;
        height: 60px;
        border: 1px solid #bfbfbf;
        border-radius: 8px;
        background: #e7e7e7;
        text-align: center;
        font-size: 28px;
        font-family: 'Baloo 2', cursive;
        font-weight: 800;
        color: #123966;
        outline: none;
    }
    .otp-digits-container input:focus {
        border: 2px solid #123966;
        background: #ffffff;
    }
  </style>
</head>
<body>
<div class="forgot-box" style="width: 500px; max-width: 100%;">
  <h1>NEXUS</h1>
  <div class="custom-icon-wrap">
    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.35 2 2 0 0 1 3.6 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.63a16 16 0 0 0 6 6l.95-.95a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
  </div>
  <h2>Enter OTP Code</h2>
  <p class="forgot-form-desc" style="margin-bottom: 5px;">We sent a 6-digit verification code to</p>
  <div style="text-align:center;">
    <span class="email-badge"><?= htmlspecialchars($_SESSION['reset_email']) ?></span>
  </div>

  <?php if (isset($error)): ?>
    <div class="forgot-msg-error">
      <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>

  <?php if ($status): ?>
    <div class="status-msg-success">
      <?= $status ?>
    </div>
  <?php endif; ?>

  <form method="POST" onsubmit="assembleOtp()" class="forgot-form">
    <label style="text-align:center; display:block; font-size: 22px;">6-Digit OTP Code</label>
    <div class="otp-digits-container">
      <input type="text" maxlength="1" class="otp-digit" inputmode="numeric" pattern="[0-9]" required/>
      <input type="text" maxlength="1" class="otp-digit" inputmode="numeric" pattern="[0-9]" required/>
      <input type="text" maxlength="1" class="otp-digit" inputmode="numeric" pattern="[0-9]" required/>
      <input type="text" maxlength="1" class="otp-digit" inputmode="numeric" pattern="[0-9]" required/>
      <input type="text" maxlength="1" class="otp-digit" inputmode="numeric" pattern="[0-9]" required/>
      <input type="text" maxlength="1" class="otp-digit" inputmode="numeric" pattern="[0-9]" required/>
    </div>
    <input type="hidden" name="otp" id="otp"/>
    <button type="submit" class="full-outline-btn">Verify OTP</button>
  </form>

  <div class="resend-text" style="margin-top: 25px; text-align: center; font-size: 20px;">
    Code expires in: <span id="countdown" style="font-weight: 800; color: #123966;">10:00</span>
  </div>
  <div style="text-align: center; margin-top: 15px;">
    <a href="forgot_password.php" class="back-login" style="margin-top: 0;">Request New OTP</a>
  </div>
</div>

<script>
  const digits = document.querySelectorAll('.otp-digit');
  digits.forEach((inp, i) => {
    inp.addEventListener('input', () => {
      inp.value = inp.value.replace(/[^0-9]/g, '');
      if (inp.value && i < digits.length - 1) digits[i + 1].focus();
    });
    inp.addEventListener('keydown', e => {
      if (e.key === 'Backspace' && !inp.value && i > 0) digits[i - 1].focus();
    });
    inp.addEventListener('paste', e => {
      e.preventDefault();
      const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
      [...pasted].forEach((ch, j) => { if (digits[i + j]) digits[i + j].value = ch; });
      const next = Math.min(i + pasted.length, digits.length - 1);
      digits[next].focus();
    });
  });

  function assembleOtp() {
    document.getElementById('otp').value = [...digits].map(d => d.value).join('');
  }

  <?php $remaining = max(0, 600 - (time() - $_SESSION['otp_time'])); ?>
  let secs = <?= $remaining ?>;
  const cd = document.getElementById('countdown');
  const timer = setInterval(() => {
    if (secs <= 0) { 
        clearInterval(timer); 
        cd.textContent = 'Expired'; 
        cd.style.color = 'red'; 
        return; 
    }
    secs--;
    const m = String(Math.floor(secs / 60)).padStart(2, '0');
    const s = String(secs % 60).padStart(2, '0');
    cd.textContent = m + ':' + s;
  }, 1000);
</script>
</body>
</html>