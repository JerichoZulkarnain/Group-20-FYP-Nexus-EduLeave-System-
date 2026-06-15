<?php
function logActivity($conn, $user_id, $user_name, $user_role, $activity_type, $activity_description) {
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $created_at = date('Y-m-d H:i:s');
    
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, user_name, user_role, activity_type, activity_description, ip_address, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssss", $user_id, $user_name, $user_role, $activity_type, $activity_description, $ip_address, $created_at);
    $stmt->execute();
    $stmt->close();
}
?>