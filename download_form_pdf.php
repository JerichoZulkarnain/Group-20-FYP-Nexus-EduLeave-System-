<?php
include 'db_config.php';

if(!isset($_GET['token'])) {
    die("Invalid request.");
}

$token = $_GET['token'];
$formQuery = mysqli_query($conn, "SELECT id FROM leave_applications WHERE form_token = '$token'");
$formData = mysqli_fetch_assoc($formQuery);

if(!$formData) {
    die("Form not found.");
}

// Redirect to signature_form.php with print option
header("Location: signature_form.php?token=$token&print=1");
exit();
?>