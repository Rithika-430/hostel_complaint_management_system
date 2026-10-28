<?php
/**
 * Hostel Complaint Management System
 * Save New Complaint with Priority (submit_complaint.php)
 */

session_start();

// Ensure user is authenticated as a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: index.php?msg=auth_required");
    exit();
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: student.php");
    exit();
}

require_once 'db.php';

$student_id  = (int)$_SESSION['user_id'];
$category    = trim($_POST['category'] ?? '');
$priority    = trim($_POST['priority'] ?? 'Medium');
$description = trim($_POST['description'] ?? '');
$room_number = trim($_POST['room_number'] ?? '');

$allowed_categories = ['Electrical', 'Plumbing', 'Cleaning', 'Furniture', 'Internet', 'Other'];
$allowed_priorities = ['Low', 'Medium', 'High', 'Urgent'];

// 1. Validation
if (empty($category) || !in_array($category, $allowed_categories)) {
    $_SESSION['error_msg'] = "Please select a valid complaint category.";
    header("Location: student.php");
    exit();
}

if (!in_array($priority, $allowed_priorities)) {
    $priority = 'Medium';
}

if (empty($room_number)) {
    $_SESSION['error_msg'] = "Please enter your room number.";
    header("Location: student.php");
    exit();
}

if (strlen($description) < 10) {
    $_SESSION['error_msg'] = "Complaint description is too short. Please provide at least 10 characters.";
    header("Location: student.php");
    exit();
}

if (strlen($description) > 500) {
    $_SESSION['error_msg'] = "Complaint description must not exceed 500 characters.";
    header("Location: student.php");
    exit();
}

// 2. Update user's room number in users table if updated
if (!empty($room_number) && $room_number !== ($_SESSION['room_number'] ?? '')) {
    $update_room_stmt = mysqli_prepare($conn, "UPDATE users SET room_number = ? WHERE id = ?");
    if ($update_room_stmt) {
        mysqli_stmt_bind_param($update_room_stmt, "si", $room_number, $student_id);
        mysqli_stmt_execute($update_room_stmt);
        mysqli_stmt_close($update_room_stmt);
        $_SESSION['room_number'] = $room_number;
    }
}

// 3. Insert complaint with Urgency Priority using Prepared Statement
$insert_sql = "INSERT INTO complaints (student_id, category, priority, description, status) VALUES (?, ?, ?, ?, 'Pending')";
$stmt = mysqli_prepare($conn, $insert_sql);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "isss", $student_id, $category, $priority, $description);

    if (mysqli_stmt_execute($stmt)) {
        $complaint_id = mysqli_insert_id($conn);
        $formatted_id = "#CMP-" . str_pad($complaint_id, 3, '0', STR_PAD_LEFT);
        $_SESSION['success_msg'] = "Your complaint {$formatted_id} [Priority: {$priority}] has been submitted successfully and marked as Pending.";
    } else {
        $_SESSION['error_msg'] = "Failed to submit complaint: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
} else {
    $_SESSION['error_msg'] = "Database error: " . mysqli_error($conn);
}

// Redirect back to student dashboard
header("Location: student.php");
exit();
?>
