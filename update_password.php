<?php
include 'db_config.php';

if(!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];
$userId = $user['id'];

if($_SERVER["REQUEST_METHOD"] == "POST") {

    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // GET CURRENT PASSWORD FROM DATABASE
    $sql = "SELECT password FROM users WHERE id = '$userId'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);

    $currentPassword = $row['password'];

    // CHECK OLD PASSWORD
    if(!password_verify($old_password, $currentPassword)) {

        echo "
        <script>
            alert('Old password is incorrect!');
            window.history.back();
        </script>
        ";
        exit();

    }

    // CHECK CONFIRM PASSWORD
    if($new_password != $confirm_password) {

        echo "
        <script>
            alert('New password and confirm password do not match!');
            window.history.back();
        </script>
        ";
        exit();

    }

    // HASH NEW PASSWORD
    $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);

    // UPDATE PASSWORD
    $update = "UPDATE users
               SET password = '$hashedPassword'
               WHERE id = '$userId'";

    if(mysqli_query($conn, $update)) {

        echo "
        <script>
            alert('Password changed successfully!');
            window.location.href='profile.php';
        </script>
        ";

    } else {

        echo "
        <script>
            alert('Failed to change password!');
            window.history.back();
        </script>
        ";

    }

}
?>