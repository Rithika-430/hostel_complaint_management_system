<?php
/**
 * Hostel Complaint Management System
 * Enhanced Warden Dashboard (warden.php)
 * 
 * Features:
 * - Priority-wise and Status-wise Complaint Tracking
 * - Add Warden Resolution Remarks
 * - Post Official Announcements to Student Notice Board
 * - Real-time filtering and search
 */

session_start();

// Protect Page: Ensure warden is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'warden') {
    header("Location: index.php?msg=auth_required");
    exit();
}

require_once 'db.php';

$warden_name = $_SESSION['name'];

// Flash notifications
$success_msg = $_SESSION['success_msg'] ?? '';
$error_msg   = $_SESSION['error_msg'] ?? '';
unset($_SESSION['success_msg'], $_SESSION['error_msg']);

// 1. Fetch Overall Complaint Statistics
$stats = [
    'total'       => 0,
    'pending'     => 0,
    'in_progress' => 0,
    'resolved'    => 0,
    'urgent'      => 0
];

$stat_sql = "SELECT status, COUNT(*) as count FROM complaints GROUP BY status";
$stat_result = mysqli_query($conn, $stat_sql);
if ($stat_result) {
    while ($row = mysqli_fetch_assoc($stat_result)) {
        $status_key = strtolower(str_replace(' ', '_', $row['status']));
        if (isset($stats[$status_key])) {
            $stats[$status_key] = (int)$row['count'];
        }
        $stats['total'] += (int)$row['count'];
    }
}

// Count urgent complaints
$urgent_res = mysqli_query($conn, "SELECT COUNT(*) as count FROM complaints WHERE priority = 'Urgent' AND status != 'Resolved'");
if ($urgent_res) {
    $stats['urgent'] = (int)mysqli_fetch_assoc($urgent_res)['count'];
}

// 2. Status Filter Handling
$allowed_filters = ['all', 'Pending', 'In Progress', 'Resolved', 'Urgent'];
$current_filter  = $_GET['filter'] ?? 'all';
if (!in_array($current_filter, $allowed_filters)) {
    $current_filter = 'all';
}

// 3. Fetch All Complaints with Full Student Details
$complaints = [];
if ($current_filter === 'all') {
    $query = "SELECT c.id, c.category, c.priority, c.description, c.status, c.warden_remark, c.created_at, 
                     u.name AS student_name, u.roll_number, u.hostel_block, u.room_number, u.phone 
              FROM complaints c 
              JOIN users u ON c.student_id = u.id 
              ORDER BY 
                CASE c.priority 
                    WHEN 'Urgent' THEN 1 
                    WHEN 'High' THEN 2 
                    WHEN 'Medium' THEN 3 
                    ELSE 4 
                END, 
                c.created_at DESC";
    $result = mysqli_query($conn, $query);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $complaints[] = $row;
        }
    }
} elseif ($current_filter === 'Urgent') {
    $query = "SELECT c.id, c.category, c.priority, c.description, c.status, c.warden_remark, c.created_at, 
                     u.name AS student_name, u.roll_number, u.hostel_block, u.room_number, u.phone 
              FROM complaints c 
              JOIN users u ON c.student_id = u.id 
              WHERE c.priority = 'Urgent'
              ORDER BY c.created_at DESC";
    $result = mysqli_query($conn, $query);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $complaints[] = $row;
        }
    }
} else {
    $query = "SELECT c.id, c.category, c.priority, c.description, c.status, c.warden_remark, c.created_at, 
                     u.name AS student_name, u.roll_number, u.hostel_block, u.room_number, u.phone 
              FROM complaints c 
              JOIN users u ON c.student_id = u.id 
              WHERE c.status = ? 
              ORDER BY 
                CASE c.priority 
                    WHEN 'Urgent' THEN 1 
                    WHEN 'High' THEN 2 
                    WHEN 'Medium' THEN 3 
                    ELSE 4 
                END, 
                c.created_at DESC";
    $stmt = mysqli_prepare($conn, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $current_filter);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $complaints[] = $row;
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warden Dashboard - Hostel Complaint Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Top Navigation Bar -->
    <header class="navbar">
        <a href="warden.php" class="nav-brand">
            <span class="nav-brand-icon">🏢</span>
            <div class="nav-brand-text">
                <h2>Hostel Care</h2>
                <span>Chief Warden Administration</span>
            </div>
        </a>

        <div class="nav-user-info">
            <div class="user-badge">
                <div class="user-name"><?php echo htmlspecialchars($warden_name); ?></div>
                <div class="user-role">Hostel Administrator</div>
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
                <h1>Warden Administration Hub 👔</h1>
                <p>Track all student grievances, assign maintenance staff, update progress, and post notices.</p>
            </div>
            <div class="student-meta-pill">
                Administrator: <strong><?php echo htmlspecialchars($warden_name); ?></strong>
            </div>
        </div>

        <!-- System Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon total">📊</div>
                <div class="stat-details">
                    <h3><?php echo $stats['total']; ?></h3>
                    <span>Total Complaints</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon pending">⏳</div>
                <div class="stat-details">
                    <h3><?php echo $stats['pending']; ?></h3>
                    <span>Pending Action</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon progress">🔧</div>
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

        <!-- Feature: Post Announcement to Hostel Notice Board -->
        <div class="card" style="margin-bottom: 24px;">
            <div class="card-header">
                <div class="card-title">
                    <span>📢</span> Post Announcement to Students Notice Board
                </div>
            </div>
            <div class="card-body">
                <form action="post_notice.php" method="POST" style="display: flex; flex-direction: column; gap: 12px;">
                    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 14px;">
                        <input type="text" name="title" placeholder="Notice Title (e.g. 🚰 Water Tank Cleaning Notice)" required>
                        <input type="text" name="content" placeholder="Notice Description / Instructions for hostellers..." required>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-sm btn-primary">
                            📢 Publish Notice Now
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Main Complaints Management Panel -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <span>📋</span> All Student Complaints & Maintenance Requests
                </div>
                <div style="font-size: 13px; color: #64748b;">
                    Showing <?php echo count($complaints); ?> complaints (Filter: <strong><?php echo htmlspecialchars(ucfirst($current_filter)); ?></strong>)
                </div>
            </div>

            <div class="card-body">
                <!-- Toolbar: Filter Pills and Live Search -->
                <div class="toolbar-container">
                    <div class="filter-pills">
                        <a href="warden.php?filter=all" class="filter-pill <?php echo ($current_filter === 'all') ? 'active' : ''; ?>">
                            All (<?php echo $stats['total']; ?>)
                        </a>
                        <a href="warden.php?filter=Urgent" class="filter-pill <?php echo ($current_filter === 'Urgent') ? 'active' : ''; ?>" style="border-color: #fca5a5; color: #b91c1c;">
                            🚨 Urgent (<?php echo $stats['urgent']; ?>)
                        </a>
                        <a href="warden.php?filter=Pending" class="filter-pill <?php echo ($current_filter === 'Pending') ? 'active' : ''; ?>">
                            ⏳ Pending (<?php echo $stats['pending']; ?>)
                        </a>
                        <a href="warden.php?filter=In Progress" class="filter-pill <?php echo ($current_filter === 'In Progress') ? 'active' : ''; ?>">
                            ⚙️ In Progress (<?php echo $stats['in_progress']; ?>)
                        </a>
                        <a href="warden.php?filter=Resolved" class="filter-pill <?php echo ($current_filter === 'Resolved') ? 'active' : ''; ?>">
                            ✅ Resolved (<?php echo $stats['resolved']; ?>)
                        </a>
                    </div>

                    <div class="search-box">
                        <span class="search-icon">🔍</span>
                        <input 
                            type="text" 
                            id="searchInput" 
                            class="search-input" 
                            placeholder="Live filter by name, roll, room..."
                        >
                    </div>
                </div>

                <!-- Complaints Table -->
                <div class="table-responsive">
                    <table class="custom-table" id="complaintsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Student Details</th>
                                <th>Block & Room</th>
                                <th>Category & Urgency</th>
                                <th>Complaint Description</th>
                                <th>Submitted On</th>
                                <th>Status</th>
                                <th>Warden Action & Resolution Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($complaints)): ?>
                                <tr>
                                    <td colspan="8" style="text-align: center; padding: 36px; color: #64748b;">
                                        No complaints found under the "<?php echo htmlspecialchars($current_filter); ?>" filter.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($complaints as $c): ?>
                                    <?php 
                                        $statusClass = 'status-' . strtolower(str_replace(' ', '-', $c['status'])); 
                                        $priorityClass = 'priority-' . strtolower($c['priority'] ?? 'medium');
                                        $formattedDate = date("d M Y, h:i A", strtotime($c['created_at']));
                                    ?>
                                    <tr class="complaint-row">
                                        <td>
                                            <span class="complaint-id">#CMP-<?php echo str_pad($c['id'], 3, '0', STR_PAD_LEFT); ?></span>
                                            <div style="margin-top: 6px;">
                                                <a href="print_complaint.php?id=<?php echo $c['id']; ?>" target="_blank" class="btn btn-sm btn-outline" style="font-size: 11px; padding: 2px 6px;" title="Print Work Order Slip">
                                                    🖨️ Slip
                                                </a>
                                            </div>
                                        </td>
                                        <td>
                                            <strong style="color: #0f172a;"><?php echo htmlspecialchars($c['student_name']); ?></strong><br>
                                            <span style="font-size: 12px; color: #64748b;">Roll: <?php echo htmlspecialchars($c['roll_number'] ?? 'N/A'); ?></span>
                                            <?php if (!empty($c['phone'])): ?>
                                                <br><span style="font-size: 11px; color: #0284c7;">📞 <?php echo htmlspecialchars($c['phone']); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong style="color: #1e3a8a;"><?php echo htmlspecialchars($c['room_number'] ?? 'N/A'); ?></strong><br>
                                            <span style="font-size: 11px; color: #64748b;"><?php echo htmlspecialchars($c['hostel_block'] ?? 'Block A'); ?></span>
                                        </td>
                                        <td>
                                            <span class="complaint-category"><?php echo htmlspecialchars($c['category']); ?></span><br>
                                            <span class="priority-badge <?php echo $priorityClass; ?>" style="margin-top: 4px;">
                                                <?php echo htmlspecialchars($c['priority'] ?? 'Medium'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="complaint-desc">
                                                <?php echo nl2br(htmlspecialchars($c['description'])); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="date-text"><?php echo $formattedDate; ?></span>
                                        </td>
                                        <td>
                                            <span class="status-badge <?php echo $statusClass; ?>">
                                                <?php echo htmlspecialchars($c['status']); ?>
                                            </span>
                                        </td>
                                        <td style="min-width: 250px;">
                                            <form action="update_status.php" method="POST" class="status-update-form" style="flex-direction: column; align-items: stretch; gap: 6px;">
                                                <input type="hidden" name="complaint_id" value="<?php echo $c['id']; ?>">
                                                <input type="hidden" name="return_filter" value="<?php echo htmlspecialchars($current_filter); ?>">
                                                
                                                <div style="display: flex; gap: 6px;">
                                                    <select name="status" class="status-update-select" required style="flex: 1;">
                                                        <option value="Pending" <?php echo ($c['status'] === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                                        <option value="In Progress" <?php echo ($c['status'] === 'In Progress') ? 'selected' : ''; ?>>In Progress</option>
                                                        <option value="Resolved" <?php echo ($c['status'] === 'Resolved') ? 'selected' : ''; ?>>Resolved</option>
                                                    </select>
                                                    <button type="submit" class="btn btn-sm btn-accent">
                                                        Save
                                                    </button>
                                                </div>

                                                <input 
                                                    type="text" 
                                                    name="warden_remark" 
                                                    value="<?php echo htmlspecialchars($c['warden_remark'] ?? ''); ?>" 
                                                    placeholder="Add remark (e.g. Plumber assigned for 4 PM)" 
                                                    style="font-size: 12px; padding: 5px 8px; border-radius: 4px;"
                                                >
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <!-- Hidden placeholder for empty live search matches -->
                            <tr id="noSearchResults" style="display: none;">
                                <td colspan="8" style="text-align: center; padding: 36px; color: #64748b;">
                                    🔍 No complaints match your live search keyword.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        Hostel Complaint Management System &bull; College Web Programming Mini Project
    </footer>

    <script src="script.js"></script>
</body>
</html>
