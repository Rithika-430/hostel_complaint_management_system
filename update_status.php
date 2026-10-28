<?php
/**
 * Hostel Complaint Management System
 * Update Complaint Status & Warden Remark (update_status.php)
 */

session_start();

// Ensure user is authenticated as a warden
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'warden') {
    header("Location: index.php?msg=auth_required");
    exit();
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: warden.php");
    exit();
}

require_once 'db.php';

$complaint_id  = isset($_POST['complaint_id']) ? (int)$_POST['complaint_id'] : 0;
$new_status    = trim($_POST['status'] ?? '');
$warden_remark = trim($_POST['warden_remark'] ?? '');
$return_filter = trim($_POST['return_filter'] ?? 'all');

$allowed_statuses = ['Pending', 'In Progress', 'Resolved'];

// 1. Validation
if ($complaint_id <= 0) {
    $_SESSION['error_msg'] = "Invalid complaint ID specified.";
    header("Location: warden.php?filter=" . urlencode($return_filter));
    exit();
}

if (!in_array($new_status, $allowed_statuses)) {
    $_SESSION['error_msg'] = "Invalid status value provided.";
    header("Location: warden.php?filter=" . urlencode($return_filter));
    exit();
}

// 2. Update status and remark using Prepared Statement
$update_sql = "UPDATE complaints SET status = ?, warden_remark = ? WHERE id = ?";
$stmt = mysqli_prepare($conn, $update_sql);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ssi", $new_status, $warden_remark, $complaint_id);

    if (mysqli_stmt_execute($stmt)) {
        $formatted_id = "#CMP-" . str_pad($complaint_id, 3, '0', STR_PAD_LEFT);
        $_SESSION['success_msg'] = "Complaint {$formatted_id} updated to '{$new_status}' with your resolution remark saved!";
    } else {
        $_SESSION['error_msg'] = "Database error while updating complaint: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
} else {
    $_SESSION['error_msg'] = "Database preparation error: " . mysqli_error($conn);
}

// Redirect back to warden dashboard
header("Location: warden.php?filter=" . urlencode($return_filter));
exit();
?>
