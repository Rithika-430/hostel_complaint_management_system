<?php
/**
 * Hostel Complaint Management System
 * Enhanced Student Dashboard (student.php)
 * 
 * Features:
 * - Notice Board / Warden Announcements
 * - Complaint Lodging with Urgency / Priority (Low, Medium, High, Urgent)
 * - View Warden Resolution Remarks
 * - Print Complaint Acknowledgement Slip
 */

session_start();

// Protect Page: Ensure student is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: index.php?msg=auth_required");
    exit();
}

require_once 'db.php';

$student_id   = $_SESSION['user_id'];
$student_name = $_SESSION['name'];
$roll_number  = $_SESSION['roll_number'] ?? 'N/A';
$hostel_block = $_SESSION['hostel_block'] ?? 'Block A';
$room_number  = $_SESSION['room_number'] ?? '';

// Flash notifications
$success_msg = $_SESSION['success_msg'] ?? '';
$error_msg   = $_SESSION['error_msg'] ?? '';
unset($_SESSION['success_msg'], $_SESSION['error_msg']);

// 1. Fetch Active Hostel Notices
$notices = [];
$notices_res = mysqli_query($conn, "SELECT title, content, posted_by, created_at FROM notices ORDER BY created_at DESC LIMIT 3");
if ($notices_res) {
    while ($n = mysqli_fetch_assoc($notices_res)) {
        $notices[] = $n;
    }
}

// 2. Fetch Student's Complaint Statistics
$stats = [
    'total'       => 0,
    'pending'     => 0,
    'in_progress' => 0,
    'resolved'    => 0
];

$stat_sql = "SELECT status, COUNT(*) as count FROM complaints WHERE student_id = ? GROUP BY status";
$stat_stmt = mysqli_prepare($conn, $stat_sql);
if ($stat_stmt) {
    mysqli_stmt_bind_param($stat_stmt, "i", $student_id);
    mysqli_stmt_execute($stat_stmt);
    $stat_result = mysqli_stmt_get_result($stat_stmt);
    while ($row = mysqli_fetch_assoc($stat_result)) {
        $status_key = strtolower(str_replace(' ', '_', $row['status']));
        if (isset($stats[$status_key])) {
            $stats[$status_key] = (int)$row['count'];
        }
        $stats['total'] += (int)$row['count'];
    }
    mysqli_stmt_close($stat_stmt);
}

// 3. Fetch All Complaints Submitted by This Student
$complaints = [];
$comp_sql = "SELECT id, category, priority, description, status, warden_remark, created_at, updated_at 
             FROM complaints 
             WHERE student_id = ? 
             ORDER BY created_at DESC";
$comp_stmt = mysqli_prepare($conn, $comp_sql);
if ($comp_stmt) {
    mysqli_stmt_bind_param($comp_stmt, "i", $student_id);
    mysqli_stmt_execute($comp_stmt);
    $comp_result = mysqli_stmt_get_result($comp_stmt);
    while ($row = mysqli_fetch_assoc($comp_result)) {
        $complaints[] = $row;
    }
    mysqli_stmt_close($comp_stmt);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Hostel Complaint Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Top Navigation Bar -->
    <header class="navbar">
        <a href="student.php" class="nav-brand">
            <span class="nav-brand-icon">🏢</span>
            <div class="nav-brand-text">
                <h2>Hostel Care</h2>
                <span>Student Grievance Portal</span>
            </div>
        </a>

        <div class="nav-user-info">
            <div class="user-badge">
                <div class="user-name"><?php echo htmlspecialchars($student_name); ?></div>
                <div class="user-role"><?php echo htmlspecialchars($hostel_block); ?> | Roll: <?php echo htmlspecialchars($roll_number); ?></div>
            </div>
            <a href="logout.php" class="btn-logout" title="Sign out of your session">
                <span>Logout</span> &rarr;
            </a>
        </div>
    </header>

    <div class="main-wrapper">
        <!-- Flash Messages -->
        <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success">
                <span>✅ <?php echo htmlspecialchars($success_msg); ?></span>
                <button type="button" class="alert-close">&times;</button>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger">
                <span>⚠️ <?php echo htmlspecialchars($error_msg); ?></span>
                <button type="button" class="alert-close">&times;</button>
            </div>
        <?php endif; ?>

        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <div>
                <h1>Welcome, <?php echo htmlspecialchars($student_name); ?>! 👋</h1>
                <p>Register, track, and monitor hostel maintenance and room complaints in real-time.</p>
            </div>
            <div class="student-meta-pill">
                <strong><?php echo htmlspecialchars($hostel_block); ?></strong> &bull; Room: <strong><?php echo htmlspecialchars($room_number ?: 'Not Set'); ?></strong> &bull; Roll: <strong><?php echo htmlspecialchars($roll_number); ?></strong>
            </div>
        </div>

        <!-- Hostel Notice Board / Announcements -->
        <?php if (!empty($notices)): ?>
            <div class="notice-board-card">
                <div class="notice-board-header">
                    <span>📢 Official Hostel Announcements</span>
                </div>
                <div class="notice-board-body">
                    <?php foreach ($notices as $notice): ?>
                        <div class="notice-item">
                            <h4><?php echo htmlspecialchars($notice['title']); ?></h4>
                            <p><?php echo nl2br(htmlspecialchars($notice['content'])); ?></p>
                            <span class="notice-meta">Posted by <?php echo htmlspecialchars($notice['posted_by']); ?> &bull; <?php echo date("d M Y", strtotime($notice['created_at'])); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Complaint Status Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon total">📋</div>
                <div class="stat-details">
                    <h3><?php echo $stats['total']; ?></h3>
                    <span>Total Submitted</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon pending">⏳</div>
                <div class="stat-details">
                    <h3><?php echo $stats['pending']; ?></h3>
                    <span>Pending</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon progress">⚙️</div>
                <div class="stat-details">
                    <h3><?php echo $stats['in_progress']; ?></h3>
                    <span>In Progress</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon resolved">✅</div>
                <div class="stat-details">
                    <h3><?php echo $stats['resolved']; ?></h3>
                    <span>Resolved</span>
                </div>
            </div>
        </div>

        <!-- Section 1: Submit a New Hostel Complaint Form -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <span>✍️</span> Register New Hostel Complaint
                </div>
                <span style="font-size: 12px; color: #64748b;">Fast resolution guarantee for high urgency items</span>
            </div>
            <div class="card-body">
                <form action="submit_complaint.php" method="POST" id="complaintForm">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="student_name">Student Name</label>
                            <input 
                                type="text" 
                                id="student_name" 
                                value="<?php echo htmlspecialchars($student_name); ?>" 
                                readonly 
                                disabled
                            >
                        </div>

                        <div class="form-group">
                            <label for="roll_number">Roll Number</label>
                            <input 
                                type="text" 
                                id="roll_number" 
                                value="<?php echo htmlspecialchars($roll_number); ?>" 
                                readonly 
                                disabled
                            >
                        </div>

                        <div class="form-group">
                            <label for="room_number">Room Number <span class="required">*</span></label>
                            <input 
                                type="text" 
                                id="room_number" 
                                name="room_number" 
                                placeholder="e.g. B-204" 
                                value="<?php echo htmlspecialchars($room_number); ?>" 
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="category">Complaint Category <span class="required">*</span></label>
                            <select id="category" name="category" required>
                                <option value="" disabled selected>-- Select Category --</option>
                                <option value="Electrical">⚡ Electrical (Fan, Light, Switchboard, AC)</option>
                                <option value="Plumbing">🚰 Plumbing (Tap leak, Flush, Shower, Pipe)</option>
                                <option value="Cleaning">🧹 Cleaning (Washroom, Corridor, Room)</option>
                                <option value="Furniture">🪑 Furniture (Bed, Study Table, Cupboard, Lock)</option>
                                <option value="Internet">📶 Internet / Wi-Fi (No signal, Speed)</option>
                                <option value="Other">📌 Other Maintenance Issues</option>
                            </select>
                        </div>

                        <div class="form-group full-width">
                            <label for="priority">Urgency / Priority Level <span class="required">*</span></label>
                            <select id="priority" name="priority" required>
                                <option value="Low">🟢 Low — Minor inconvenience (can wait 2-3 days)</option>
                                <option value="Medium" selected>🔵 Medium — Regular maintenance issue</option>
                                <option value="High">🟠 High — Severely affecting daily activity</option>
                                <option value="Urgent">🔴 Urgent / Emergency — Water leakage, short circuit, safety issue</option>
                            </select>
                        </div>

                        <div class="form-group full-width">
                            <label for="description">Complaint Description <span class="required">*</span></label>
                            <textarea 
                                id="description" 
                                name="description" 
                                rows="3" 
                                maxlength="500" 
                                placeholder="Describe the problem clearly (e.g. Ceiling fan in room B-204 is making loud humming sound and running slow)..." 
                                required
                            ></textarea>
                            <div class="char-counter">
                                <span id="charCount">0</span> / 500 characters
                            </div>
                        </div>
                    </div>

                    <div style="margin-top: 10px;">
                        <button type="submit" class="btn btn-primary">
                            <span>Submit Complaint</span> &rarr;
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Section 2: View Logged-in Student's Complaints -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <span>📑</span> My Submitted Complaints History
                </div>
                <div style="font-size: 13px; color: #64748b;">
                    Showing <?php echo count($complaints); ?> record(s)
                </div>
            </div>

            <div class="card-body" style="padding: 0;">
                <?php if (empty($complaints)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📭</div>
                        <h3>No Complaints Found</h3>
                        <p>You have not submitted any complaints yet. Use the form above to report any issue.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Category</th>
                                    <th>Priority</th>
                                    <th>Description</th>
                                    <th>Warden Action / Remarks</th>
                                    <th>Submitted On</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($complaints as $c): ?>
                                    <?php 
                                        $statusClass = 'status-' . strtolower(str_replace(' ', '-', $c['status'])); 
                                        $priorityClass = 'priority-' . strtolower($c['priority'] ?? 'medium');
                                        $formattedDate = date("d M Y, h:i A", strtotime($c['created_at']));
                                    ?>
                                    <tr>
                                        <td>
                                            <span class="complaint-id">#CMP-<?php echo str_pad($c['id'], 3, '0', STR_PAD_LEFT); ?></span>
                                        </td>
                                        <td>
                                            <span class="complaint-category"><?php echo htmlspecialchars($c['category']); ?></span>
                                        </td>
                                        <td>
                                            <span class="priority-badge <?php echo $priorityClass; ?>">
                                                <?php echo htmlspecialchars($c['priority'] ?? 'Medium'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="complaint-desc">
                                                <?php echo nl2br(htmlspecialchars($c['description'])); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if (!empty($c['warden_remark'])): ?>
                                                <div class="warden-remark-box">
                                                    💬 <strong>Warden:</strong> <?php echo htmlspecialchars($c['warden_remark']); ?>
                                                </div>
                                            <?php else: ?>
                                                <span style="font-size: 12px; color: #94a3b8; font-style: italic;">Awaiting warden review</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="date-text"><?php echo $formattedDate; ?></span>
                                        </td>
                                        <td>
                                            <span class="status-badge <?php echo $statusClass; ?>">
                                                <?php echo htmlspecialchars($c['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="print_complaint.php?id=<?php echo $c['id']; ?>" target="_blank" class="btn btn-sm btn-outline" title="Print Complaint Slip">
                                                🖨️ Slip
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <footer class="footer">
        Hostel Complaint Management System &bull; College Web Programming Mini Project
    </footer>

    <script src="script.js"></script>
</body>
</html>
