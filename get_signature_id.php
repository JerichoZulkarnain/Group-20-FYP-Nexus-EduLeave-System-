<?php
include 'db_config.php';

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'lecturer') {
    echo json_encode(['error' => 'Not authorized']);
    exit();
}

$leave_id = $_GET['leave_id'] ?? 0;
$lecturer_id = $_SESSION['user']['id'];

// Debug - let's see what's happening
$query = mysqli_query($conn, "SELECT id, status FROM lecturer_signatures WHERE leave_id = '$leave_id' AND lecturer_id = '$lecturer_id'");

if(!$query) {
    echo json_encode(['error' => mysqli_error($conn)]);
    exit();
}

$result = mysqli_fetch_assoc($query);

if($result) {
    echo json_encode(['signature_id' => $result['id'], 'status' => $result['status']]);
} else {
    // Check if there are any records for this leave_id at all
    $checkQuery = mysqli_query($conn, "SELECT * FROM lecturer_signatures WHERE leave_id = '$leave_id'");
    $totalRecords = mysqli_num_rows($checkQuery);
    
    echo json_encode([
        'signature_id' => null, 
        'error' => "No signature found for leave_id=$leave_id, lecturer_id=$lecturer_id. Total records for this leave: $totalRecords"
    ]);
}
?>