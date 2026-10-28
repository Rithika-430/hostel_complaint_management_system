<?php
/**
 * Hostel Complaint Management System
 * Printable Complaint Acknowledgement Slip (print_complaint.php)
 */

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?msg=auth_required");
    exit();
}

require_once 'db.php';

$complaint_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id      = (int)$_SESSION['user_id'];
$user_role    = $_SESSION['role'];

// Fetch complaint with student details
if ($user_role === 'student') {
    $stmt = mysqli_prepare($conn, "SELECT c.*, u.name as student_name, u.roll_number, u.hostel_block, u.room_number, u.phone 
                                   FROM complaints c 
                                   JOIN users u ON c.student_id = u.id 
                                   WHERE c.id = ? AND c.student_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $complaint_id, $user_id);
} else {
    $stmt = mysqli_prepare($conn, "SELECT c.*, u.name as student_name, u.roll_number, u.hostel_block, u.room_number, u.phone 
                                   FROM complaints c 
                                   JOIN users u ON c.student_id = u.id 
                                   WHERE c.id = ?");
    mysqli_stmt_bind_param($stmt, "i", $complaint_id);
}

mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$c = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$c) {
    die("Complaint record not found or access denied.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complaint Slip #CMP-<?php echo str_pad($c['id'], 3, '0', STR_PAD_LEFT); ?> - Hostel Care</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: #f1f5f9;
            padding: 30px 15px;
        }
        .slip-container {
            max-width: 680px;
            margin: 0 auto;
            background: #ffffff;
            border: 2px dashed #94a3b8;
            padding: 32px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .slip-header {
            text-align: center;
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 16px;
            margin-bottom: 24px;
        }
        .slip-header h2 {
            color: #1e3a8a;
            font-size: 20px;
            margin-bottom: 4px;
            text-transform: uppercase;
        }
        .slip-header p {
            color: #64748b;
            font-size: 13px;
        }
        .slip-meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 20px;
            background: #f8fafc;
            padding: 16px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            font-size: 14px;
        }
        .slip-row {
            margin-bottom: 6px;
        }
        .slip-label {
            color: #64748b;
            font-size: 12px;
            text-transform: uppercase;
            font-weight: 600;
        }
        .slip-value {
            color: #0f172a;
            font-weight: 600;
        }
        .slip-desc-box {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 20px;
        }
        .slip-signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }
        .sign-box {
            text-align: center;
            width: 180px;
            border-top: 1px solid #0f172a;
            padding-top: 6px;
            font-size: 12px;
            color: #475569;
        }
        .print-toolbar {
            max-width: 680px;
            margin: 0 auto 20px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        @media print {
            .print-toolbar, .footer {
                display: none !important;
            }
            body {
                background: #ffffff;
                padding: 0;
            }
            .slip-container {
                box-shadow: none;
                border: 1px solid #000;
            }
        }
    </style>
</head>
<body>
    <div class="print-toolbar">
        <a href="<?php echo ($user_role === 'warden') ? 'warden.php' : 'student.php'; ?>" class="btn btn-sm btn-outline">
            &larr; Back to Dashboard
        </a>
        <button onclick="window.print()" class="btn btn-sm btn-primary">
            🖨️ Print Slip / Save PDF
        </button>
    </div>

    <div class="slip-container">
        <div class="slip-header">
            <h2>🏢 Hostel Maintenance & Grievance Cell</h2>
            <p>Official Complaint Acknowledgement Slip</p>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <div>
                <span class="complaint-id" style="font-size: 16px; padding: 4px 10px;">
                    Complaint ID: #CMP-<?php echo str_pad($c['id'], 3, '0', STR_PAD_LEFT); ?>
                </span>
            </div>
            <div>
                <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $c['status'])); ?>">
                    Status: <?php echo htmlspecialchars($c['status']); ?>
                </span>
            </div>
        </div>

        <div class="slip-meta-grid">
            <div class="slip-row">
                <div class="slip-label">Student Name</div>
                <div class="slip-value"><?php echo htmlspecialchars($c['student_name']); ?></div>
            </div>
            <div class="slip-row">
                <div class="slip-label">Roll Number</div>
                <div class="slip-value"><?php echo htmlspecialchars($c['roll_number']); ?></div>
            </div>
            <div class="slip-row">
                <div class="slip-label">Hostel Block & Room</div>
                <div class="slip-value"><?php echo htmlspecialchars($c['hostel_block']); ?> - Room <?php echo htmlspecialchars($c['room_number']); ?></div>
            </div>
            <div class="slip-row">
                <div class="slip-label">Phone Contact</div>
                <div class="slip-value"><?php echo htmlspecialchars($c['phone'] ?: 'N/A'); ?></div>
            </div>
            <div class="slip-row">
                <div class="slip-label">Category</div>
                <div class="slip-value"><?php echo htmlspecialchars($c['category']); ?></div>
            </div>
            <div class="slip-row">
                <div class="slip-label">Urgency Priority</div>
                <div class="slip-value"><?php echo htmlspecialchars($c['priority']); ?></div>
            </div>
            <div class="slip-row" style="grid-column: 1 / -1;">
                <div class="slip-label">Submission Date & Time</div>
                <div class="slip-value"><?php echo date("d F Y, h:i A", strtotime($c['created_at'])); ?></div>
            </div>
        </div>

        <div class="slip-desc-box">
            <div class="slip-label" style="margin-bottom: 6px;">Complaint Description</div>
            <p style="margin: 0; font-size: 14px; line-height: 1.5; color: #1e293b;">
                <?php echo nl2br(htmlspecialchars($c['description'])); ?>
            </p>
        </div>

        <?php if (!empty($c['warden_remark'])): ?>
            <div class="warden-remark-box" style="margin-bottom: 20px;">
                <strong>Warden Resolution Remark / Action Taken:</strong><br>
                <?php echo nl2br(htmlspecialchars($c['warden_remark'])); ?>
            </div>
        <?php endif; ?>

        <div class="slip-signatures">
            <div class="sign-box">
                Student Signature
            </div>
            <div class="sign-box">
                Hostel Warden / Cell In-Charge
            </div>
        </div>
    </div>
</body>
</html>
