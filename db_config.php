<?php
session_start();

$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "nexus_db"
);

if(!$conn){
    die("Connection failed: " . mysqli_connect_error());
}


// ===============================
// SMTP CONFIG
// ===============================

$smtpHost = "smtp.gmail.com";

$smtpUsername = "ainaatiqahhuda@gmail.com";

$smtpPassword = "tddokdqodhihdpmg";

$smtpPort = 587;

$smtpFromName = "Nexus EduLeave System";
?>