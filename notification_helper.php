<?php
// notification_helper.php

function addNotification($conn, $user_id, $title, $message, $type = 'general') {
    $title = mysqli_real_escape_string($conn, $title);
    $message = mysqli_real_escape_string($conn, $message);
    $type = mysqli_real_escape_string($conn, $type);
    
    $query = "INSERT INTO notifications (user_id, title, message, type, is_read, created_at) 
              VALUES ('$user_id', '$title', '$message', '$type', 0, NOW())";
    
    return mysqli_query($conn, $query);
}

function getNotifications($conn, $user_id, $limit = 10) {
    $query = "SELECT * FROM notifications 
              WHERE user_id = '$user_id' 
              ORDER BY created_at DESC 
              LIMIT $limit";
    $result = mysqli_query($conn, $query);
    
    $notifications = [];
    while($row = mysqli_fetch_assoc($result)) {
        $row['time_ago'] = timeAgo($row['created_at']);
        $notifications[] = $row;
    }
    return $notifications;
}

function getUnreadCount($conn, $user_id) {
    $query = "SELECT COUNT(*) as count FROM notifications 
              WHERE user_id = '$user_id' AND is_read = 0";
    $result = mysqli_query($conn, $query);
    $data = mysqli_fetch_assoc($result);
    return $data['count'];
}

function timeAgo($timestamp) {
    $time_ago = strtotime($timestamp);
    $current_time = time();
    $time_difference = $current_time - $time_ago;
    $seconds = $time_difference;
    
    $minutes = round($seconds / 60);
    $hours = round($seconds / 3600);
    $days = round($seconds / 86400);
    
    if($seconds <= 60) {
        return "Just now";
    } else if($minutes <= 60) {
        return ($minutes == 1) ? "1 minute ago" : "$minutes minutes ago";
    } else if($hours <= 24) {
        return ($hours == 1) ? "1 hour ago" : "$hours hours ago";
    } else if($days <= 7) {
        return ($days == 1) ? "Yesterday" : "$days days ago";
    } else {
        return date("d M Y", strtotime($timestamp));
    }
}
?>