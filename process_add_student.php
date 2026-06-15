<?php
include 'db_config.php';
include 'log_activity.php';
logActivity($conn, $_SESSION['user']['id'], $_SESSION['user']['full_name'], $_SESSION['user']['role'], 'Account Creation', 'Created new student account: ' . $full_name . ' (' . $nexus_id . ')');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once __DIR__ . '/src/Exception.php';
require_once __DIR__ . '/src/PHPMailer.php';
require_once __DIR__ . '/src/SMTP.php';

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $nexus_id = strtoupper(mysqli_real_escape_string($conn, $_POST['nexus_id']));
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $send_email = isset($_POST['send_email']) ? true : false;
    
    $temp_password = $_POST['temp_password'];
    if(empty($temp_password)) {
        $temp_password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 12);
    }
    
    $hashed_password = password_hash($temp_password, PASSWORD_DEFAULT);
    $role = 'student';
    
    $checkQuery = mysqli_query($conn, "SELECT id FROM users WHERE nexus_id = '$nexus_id'");
    if(mysqli_num_rows($checkQuery) > 0) {
        echo "<script>alert('Nexus ID already exists!'); window.history.back();</script>";
        exit();
    }
    
    $sql = "INSERT INTO users (full_name, nexus_id, email, username, password, role, status, created_at) 
            VALUES ('$full_name', '$nexus_id', '$email', '$username', '$hashed_password', '$role', '$status', NOW())";
    
    if(mysqli_query($conn, $sql)) {
        if($send_email) {
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
                $mail->setFrom($smtpUsername, 'Nexus EduLeave System');
                $mail->addAddress($email, $full_name);
                $mail->isHTML(true);
                $mail->Subject = 'Your Nexus EduLeave System Account';
                $mail->Body    = "
                    <div style='font-family:Arial,sans-serif;max-width:500px;margin:0 auto;background:#fff;border-radius:12px;overflow:hidden;'>
                        <div style='background:#123966;padding:25px;text-align:center;'>
                            <h1 style='color:#fff;margin:0;'>Nexus EduLeave System</h1>
                        </div>
                        <div style='padding:25px;'>
                            <p style='font-size:16px;'>Dear <strong>$full_name</strong>,</p>
                            <p>Your account has been created in the Nexus EduLeave System.</p>
                            <div style='background:#f5f5f5;padding:15px;border-radius:8px;margin:20px 0;'>
                                <p style='margin:5px 0;'><strong>Nexus ID:</strong> $nexus_id</p>
                                <p style='margin:5px 0;'><strong>Username:</strong> $username</p>
                                <p style='margin:5px 0;'><strong>Temporary Password:</strong> $temp_password</p>
                            </div>
                            <p>Please login and change your password after first login.</p>
                            <p><a href='http://localhost/nexus/login.php' style='background:#123966;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;display:inline-block;'>Login Now</a></p>
                        </div>
                    </div>
                ";
                $mail->send();
            } catch (Exception $e) {
                // Email failed but account still created
            }
        }
        
        $_SESSION['new_student'] = [
            'full_name' => $full_name,
            'nexus_id' => $nexus_id,
            'email' => $email,
            'username' => $username,
            'temp_password' => $temp_password
        ];
        
        header("Location: add_student.php");
        exit();
    } else {
        echo "<script>alert('Error: " . mysqli_error($conn) . "'); window.history.back();</script>";
    }
} else {
    header("Location: add_student.php");
}
?>