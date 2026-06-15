<?php
include 'db_config.php';
if(!isset($_SESSION['reset_email'])) header("Location: forgot_password.php");
$msg = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $enteredCode = implode('', $_POST['code']);
    if ($enteredCode === $_SESSION['temp_code']) {
        header("Location: reset_password.php");
    } else {
        $msg = "Wrong verification code.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><link rel="stylesheet" href="style.css">
</head>
<body>
<div class="verify-box">
    <h1>Verification Code</h1>
    <form method="POST">
        <div class="code-row">
            <input type="text" name="code[]" maxlength="1" class="code-input" required>
            <input type="text" name="code[]" maxlength="1" class="code-input" required>
            <input type="text" name="code[]" maxlength="1" class="code-input" required>
            <input type="text" name="code[]" maxlength="1" class="code-input" required>
            <input type="text" name="code[]" maxlength="1" class="code-input" required>
        </div>
        <button type="submit" class="full-outline-btn">Continue</button>
        <p style="color:red; text-align:center;"><?php echo $msg; ?></p>
    </form>
</div>
</body>
</html>