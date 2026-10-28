<?php
/**
 * Hostel Complaint Management System
 * Post Official Hostel Notice (post_notice.php)
 */

session_start();

// Ensure user is authenticated as a warden
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'warden') {
    header("Location: index.php?msg=auth_required");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: warden.php");
    exit();
}

require_once 'db.php';

$title     = trim($_POST['title'] ?? '');
$content   = trim($_POST['content'] ?? '');
$posted_by = $_SESSION['name'] ?? 'Chief Warden';

if (empty($title) || empty($content)) {
    $_SESSION['error_msg'] = "Notice title and content cannot be empty.";
    header("Location: warden.php");
    exit();
}

$stmt = mysqli_prepare($conn, "INSERT INTO notices (title, content, posted_by) VALUES (?, ?, ?)");
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "sss", $title, $content, $posted_by);
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_msg'] = "Announcement posted successfully to the Hostel Notice Board!";
    } else {
        $_SESSION['error_msg'] = "Failed to post notice: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
} else {
    $_SESSION['error_msg'] = "Database error: " . mysqli_error($conn);
}

header("Location: warden.php");
exit();
?>
