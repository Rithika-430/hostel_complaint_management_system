<?php
/**
 * Hostel Complaint Management System
 * Enhanced Database Setup Script (setup.php)
 * 
 * Automatically initializes:
 * 1. Database: hostel_complaint_db (on MySQL localhost:3307)
 * 2. Tables: users, complaints, notices
 * 3. Default demo warden account
 * 4. Sample student and sample complaints with priority and warden remarks
 * 5. Sample hostel notice board announcements
 */

$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_port = 3307;
$db_name = "hostel_complaint_db";

$messages = [];
$error = null;

// Step 1: Connect to MySQL Server (port 3307)
$conn = @mysqli_connect($db_host, $db_user, $db_pass, null, $db_port);

if (!$conn) {
    $error = "Could not connect to MySQL server at {$db_host}:{$db_port}. Reason: " . mysqli_connect_error() . 
             "<br><br><strong>Checklist:</strong><br>1. Is XAMPP running?<br>2. Is MySQL service started?<br>3. Is MySQL configured on port 3307 in XAMPP?";
} else {
    // Step 2: Create Database
    $sql_create_db = "CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    if (mysqli_query($conn, $sql_create_db)) {
        $messages[] = [
            'type' => 'success',
            'title' => 'Database Created / Verified',
            'detail' => "Database <code>{$db_name}</code> is ready on port {$db_port}."
        ];
    } else {
        $error = "Error creating database: " . mysqli_error($conn);
    }

    if (!$error) {
        mysqli_select_db($conn, $db_name);

        // Step 3: Create 'users' table
        $sql_users_table = "CREATE TABLE IF NOT EXISTS `users` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL,
            `username` VARCHAR(50) NOT NULL UNIQUE,
            `password` VARCHAR(255) NOT NULL,
            `role` ENUM('student', 'warden') NOT NULL,
            `roll_number` VARCHAR(30) DEFAULT NULL,
            `hostel_block` VARCHAR(20) DEFAULT 'Block A',
            `room_number` VARCHAR(20) DEFAULT NULL,
            `phone` VARCHAR(20) DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

        if (mysqli_query($conn, $sql_users_table)) {
            $messages[] = [
                'type' => 'success',
                'title' => 'Table `users` Ready',
                'detail' => "Stores user credentials, student registration details (Roll No, Block, Room, Phone)."
            ];
        } else {
            $error = "Error creating `users` table: " . mysqli_error($conn);
        }

        // Add columns if table already existed from earlier run
        @mysqli_query($conn, "ALTER TABLE `users` ADD COLUMN `hostel_block` VARCHAR(20) DEFAULT 'Block A' AFTER `roll_number`");
        @mysqli_query($conn, "ALTER TABLE `users` ADD COLUMN `phone` VARCHAR(20) DEFAULT NULL AFTER `room_number`");

        // Step 4: Create 'complaints' table
        $sql_complaints_table = "CREATE TABLE IF NOT EXISTS `complaints` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `student_id` INT NOT NULL,
            `category` VARCHAR(50) NOT NULL,
            `priority` ENUM('Low', 'Medium', 'High', 'Urgent') DEFAULT 'Medium',
            `description` TEXT NOT NULL,
            `status` ENUM('Pending', 'In Progress', 'Resolved') DEFAULT 'Pending',
            `warden_remark` TEXT DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

        if (mysqli_query($conn, $sql_complaints_table)) {
            $messages[] = [
                'type' => 'success',
                'title' => 'Table `complaints` Ready',
                'detail' => "Stores complaint category, urgency priority, description, status & warden resolution remarks."
            ];
        } else {
            $error = "Error creating `complaints` table: " . mysqli_error($conn);
        }

        // Add priority and warden_remark columns if table already existed
        @mysqli_query($conn, "ALTER TABLE `complaints` ADD COLUMN `priority` ENUM('Low', 'Medium', 'High', 'Urgent') DEFAULT 'Medium' AFTER `category`");
        @mysqli_query($conn, "ALTER TABLE `complaints` ADD COLUMN `warden_remark` TEXT DEFAULT NULL AFTER `status`");
        @mysqli_query($conn, "ALTER TABLE `complaints` ADD COLUMN `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`");

        // Step 5: Create 'notices' table for Hostel Announcement Board
        $sql_notices_table = "CREATE TABLE IF NOT EXISTS `notices` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `title` VARCHAR(150) NOT NULL,
            `content` TEXT NOT NULL,
            `posted_by` VARCHAR(100) DEFAULT 'Chief Warden',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

        if (mysqli_query($conn, $sql_notices_table)) {
            $messages[] = [
                'type' => 'success',
                'title' => 'Table `notices` Ready',
                'detail' => "Stores official hostel notices and announcements issued by the warden."
            ];
        }

        // Step 6: Insert Default Warden and Sample Student
        if (!$error) {
            // Check Warden
            $check_warden = mysqli_query($conn, "SELECT id FROM `users` WHERE `username` = 'warden1'");
            if (mysqli_num_rows($check_warden) == 0) {
                $warden_pwd = password_hash('warden123', PASSWORD_DEFAULT);
                $stmt = mysqli_prepare($conn, "INSERT INTO `users` (`name`, `username`, `password`, `role`, `roll_number`, `hostel_block`, `room_number`, `phone`) VALUES ('Mr. Rajesh Kumar', 'warden1', ?, 'warden', NULL, 'Admin Block', 'Warden Office', '9876543210')");
                mysqli_stmt_bind_param($stmt, "s", $warden_pwd);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }

            // Check Sample Student
            $check_student = mysqli_query($conn, "SELECT id FROM `users` WHERE `username` = 'student1'");
            if (mysqli_num_rows($check_student) == 0) {
                $student_pwd = password_hash('student123', PASSWORD_DEFAULT);
                $stmt = mysqli_prepare($conn, "INSERT INTO `users` (`name`, `username`, `password`, `role`, `roll_number`, `hostel_block`, `room_number`, `phone`) VALUES ('Rahul Sharma', 'student1', ?, 'student', '21CS101', 'Block B', 'B-204', '9845123456')");
                mysqli_stmt_bind_param($stmt, "s", $student_pwd);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }

            // Step 7: Seed Notices if empty
            $notices_cnt = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM `notices`"))['cnt'];
            if ($notices_cnt == 0) {
                $sample_notices = [
                    [
                        'title' => '🚰 Water Tank Cleaning Schedule',
                        'content' => 'Please note that water tanks in Block A & B will be cleaned this Saturday from 9:00 AM to 1:00 PM. Water supply will be paused during this period.',
                        'posted_by' => 'Chief Warden'
                    ],
                    [
                        'title' => '📶 High-Speed Wi-Fi Router Upgrade',
                        'content' => 'New 5GHz Wi-Fi access points have been installed in all corridors. If you experience signal drops, submit a complaint under category Internet.',
                        'posted_by' => 'Hostel Office'
                    ]
                ];
                $stmt = mysqli_prepare($conn, "INSERT INTO `notices` (`title`, `content`, `posted_by`) VALUES (?, ?, ?)");
                foreach ($sample_notices as $n) {
                    mysqli_stmt_bind_param($stmt, "sss", $n['title'], $n['content'], $n['posted_by']);
                    mysqli_stmt_execute($stmt);
                }
                mysqli_stmt_close($stmt);
            }

            // Step 8: Seed Demo Complaints if empty
            $comp_cnt = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM `complaints`"))['cnt'];
            if ($comp_cnt == 0) {
                $s_res = mysqli_query($conn, "SELECT id FROM `users` WHERE `username` = 'student1' LIMIT 1");
                $s_id = mysqli_fetch_assoc($s_res)['id'] ?? 2;

                $sample_complaints = [
                    [
                        'student_id' => $s_id,
                        'category' => 'Electrical',
                        'priority' => 'High',
                        'description' => 'Ceiling fan in room B-204 is making an unusually loud noise and rotating very slowly.',
                        'status' => 'Pending',
                        'warden_remark' => null
                    ],
                    [
                        'student_id' => $s_id,
                        'category' => 'Plumbing',
                        'priority' => 'Urgent',
                        'description' => 'Water tap in bathroom attached to room B-204 is leaking continuously. Flooding washroom floor.',
                        'status' => 'In Progress',
                        'warden_remark' => 'Plumber Ramesh has been assigned. Maintenance work scheduled today at 4:30 PM.'
                    ],
                    [
                        'student_id' => $s_id,
                        'category' => 'Furniture',
                        'priority' => 'Medium',
                        'description' => 'Study chair backrest screw is loose and unstable.',
                        'status' => 'Resolved',
                        'warden_remark' => 'Carpenter inspected and tightened fixture on Oct 11.'
                    ]
                ];

                $stmt = mysqli_prepare($conn, "INSERT INTO `complaints` (`student_id`, `category`, `priority`, `description`, `status`, `warden_remark`) VALUES (?, ?, ?, ?, ?, ?)");
                foreach ($sample_complaints as $c) {
                    mysqli_stmt_bind_param($stmt, "isssss", $c['student_id'], $c['category'], $c['priority'], $c['description'], $c['status'], $c['warden_remark']);
                    mysqli_stmt_execute($stmt);
                }
                mysqli_stmt_close($stmt);
            }

            $messages[] = [
                'type' => 'success',
                'title' => 'Sample Data & Notice Board Ready',
                'detail' => 'Database fully seeded with Warden admin, sample student, notices, and priority complaints.'
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - Hostel Complaint Management System</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .setup-container {
            max-width: 760px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        .setup-header {
            background: linear-gradient(135deg, #1e3a8a, #0284c7);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .setup-header h1 {
            font-size: 24px;
            margin-bottom: 8px;
            font-weight: 700;
        }
        .setup-header p {
            font-size: 14px;
            opacity: 0.9;
        }
        .setup-body {
            padding: 30px;
        }
        .step-item {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            padding: 14px 18px;
            border-radius: 8px;
            margin-bottom: 12px;
            border: 1px solid #86efac;
            background: #f0fdf4;
        }
        .step-icon {
            font-size: 20px;
            flex-shrink: 0;
            margin-top: 2px;
        }
        .step-content h4 {
            margin: 0 0 4px 0;
            font-size: 15px;
            color: #0f172a;
        }
        .step-content p {
            margin: 0;
            font-size: 13px;
            color: #475569;
        }
        .cred-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 13px;
        }
        .cred-table th, .cred-table td {
            border: 1px solid #cbd5e1;
            padding: 10px 14px;
            text-align: left;
        }
        .cred-table th {
            background: #f1f5f9;
            color: #1e293b;
            font-weight: 600;
        }
        .cred-table tr:hover {
            background: #f8fafc;
        }
        .setup-actions {
            margin-top: 25px;
            display: flex;
            gap: 12px;
            justify-content: center;
        }
    </style>
</head>
<body class="auth-body">
    <div class="setup-container">
        <div class="setup-header">
            <h1>Hostel Complaint Management System</h1>
            <p>Database & Schema Setup Wizard (Port 3307)</p>
        </div>

        <div class="setup-body">
            <?php if ($error): ?>
                <div class="alert alert-danger" style="margin-bottom: 20px;">
                    <h3 style="margin-top: 0; font-size: 16px;">⚠️ Setup Issue</h3>
                    <div><?php echo $error; ?></div>
                </div>
                <div style="text-align: center; margin-top: 20px;">
                    <a href="setup.php" class="btn btn-primary">Retry Setup</a>
                </div>
            <?php else: ?>
                <h3 style="color: #1e3a8a; margin-top: 0; margin-bottom: 16px;">Installation Status</h3>
                
                <?php foreach ($messages as $msg): ?>
                    <div class="step-item">
                        <div class="step-icon">✅</div>
                        <div class="step-content">
                            <h4><?php echo $msg['title']; ?></h4>
                            <p><?php echo $msg['detail']; ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 16px; margin: 20px 0;">
                    <h4 style="color: #1e3a8a; margin-top: 0; margin-bottom: 6px;">✨ New Features Included:</h4>
                    <ul style="margin-left: 20px; font-size: 13px; color: #334155;">
                        <li><strong>Student Self-Registration:</strong> Any new student can register with their own name, roll number, hostel block & room number!</li>
                        <li><strong>Complaint Urgency / Priority:</strong> Low, Medium, High, and Urgent (Emergency) tags.</li>
                        <li><strong>Warden Action Remarks:</strong> Wardens can add resolution comments visible to the student.</li>
                        <li><strong>Hostel Notice Board:</strong> Announcements from the warden displayed to students.</li>
                        <li><strong>Print Complaint Slip:</strong> Printable acknowledgement receipt for any complaint.</li>
                    </ul>
                </div>

                <h3 style="color: #1e3a8a; margin-top: 20px; margin-bottom: 10px;">Default Warden Administrator Account</h3>
                <table class="cred-table">
                    <thead>
                        <tr>
                            <th>Role</th>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Password</th>
                            <th>Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Warden</strong></td>
                            <td>Mr. Rajesh Kumar</td>
                            <td><code>warden1</code></td>
                            <td><code>warden123</code></td>
                            <td>Permanent Admin Account</td>
                        </tr>
                    </tbody>
                </table>
                <p style="font-size: 13px; color: #64748b;">
                    *Students do not have fixed credentials. You or your professor can register any new student directly via the <strong>"Register as New Student"</strong> button!
                </p>

                <div class="setup-actions">
                    <a href="register.php" class="btn btn-accent" style="font-size: 14px; padding: 12px 20px;">
                        Register New Student &rarr;
                    </a>
                    <a href="index.php" class="btn btn-primary" style="font-size: 14px; padding: 12px 24px;">
                        Go to Login Page &rarr;
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
