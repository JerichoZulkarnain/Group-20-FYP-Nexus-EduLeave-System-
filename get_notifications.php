<?php
// Remove any whitespace before <?php
include 'db_config.php';

// Check if session is already started
if(session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if(!isset($_SESSION['user'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user']['id'];
$action = $_GET['action'] ?? '';

if($action == 'mark_read') {
    mysqli_query($conn, "UPDATE notifications SET is_read = 1 WHERE user_id = '$user_id'");
    echo json_encode(['success' => true]);
    exit();
}

// Get notifications
$result = mysqli_query($conn, "SELECT * FROM notifications WHERE user_id = '$user_id' ORDER BY created_at DESC LIMIT 20");

$notifications = [];
while($row = mysqli_fetch_assoc($result)) {
    $row['time_ago'] = timeAgo($row['created_at']);
    $notifications[] = $row;
}

$unreadResult = mysqli_query($conn, "SELECT COUNT(*) as count FROM notifications WHERE user_id = '$user_id' AND is_read = 0");
$unreadCount = mysqli_fetch_assoc($unreadResult)['count'];

echo json_encode([
    'notifications' => $notifications,
    'unread_count' => (int)$unreadCount
]);

function timeAgo($timestamp) {
    $diff = time() - strtotime($timestamp);
    if($diff < 60) return "Just now";
    if($diff < 3600) return round($diff/60) . " minutes ago";
    if($diff < 86400) return round($diff/3600) . " hours ago";
    if($diff < 604800) return round($diff/86400) . " days ago";
    return date("d M Y", strtotime($timestamp));
}
?>