<?php
/**
 * Hostel Complaint Management System
 * Unified Login Portal (index.php)
 * 
 * Supports both dynamic Student logins and Warden logins.
 */

session_start();

// If user is already logged in, redirect directly to appropriate dashboard
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'warden') {
        header("Location: warden.php");
        exit();
    } else if ($_SESSION['role'] === 'student') {
        header("Location: student.php");
        exit();
    }
}

require_once 'db.php';

$error_msg = "";
$info_msg = "";

// Check for query parameter messages
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'logged_out') {
        $info_msg = "You have been logged out successfully.";
    } elseif ($_GET['msg'] === 'auth_required') {
        $error_msg = "Access denied. Please log in to view that page.";
    } elseif ($_GET['msg'] === 'reg_success') {
        $info_msg = "Registration successful! You can now log in with your credentials.";
    }
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error_msg = "Please enter both username and password.";
    } else {
        // Use prepared statements to protect against SQL Injection
        $sql = "SELECT id, name, username, password, role, roll_number, hostel_block, room_number, phone FROM users WHERE username = ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($row = mysqli_fetch_assoc($result)) {
                // Verify password against stored hash
                if (password_verify($password, $row['password'])) {
                    session_regenerate_id(true);

                    // Store user details in session
                    $_SESSION['user_id']      = $row['id'];
                    $_SESSION['name']         = $row['name'];
                    $_SESSION['username']     = $row['username'];
                    $_SESSION['role']         = $row['role'];
                    $_SESSION['roll_number']  = $row['roll_number'];
                    $_SESSION['hostel_block'] = $row['hostel_block'] ?? 'Block A';
                    $_SESSION['room_number']  = $row['room_number'];
                    $_SESSION['phone']        = $row['phone'];

                    // Role-based redirection
                    if ($row['role'] === 'warden') {
                        header("Location: warden.php");
                        exit();
                    } else {
                        header("Location: student.php");
                        exit();
                    }
                } else {
                    $error_msg = "Invalid password. Please check your credentials.";
                }
            } else {
                $error_msg = "No account found with username: " . htmlspecialchars($username);
            }
            mysqli_stmt_close($stmt);
        } else {
            $error_msg = "Database query error: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Hostel Complaint Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-body">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-icon">🏢</div>
            <h1>Hostel Care Portal</h1>
            <p>Unified Complaint & Maintenance Management System</p>
        </div>

        <!-- Success/Info Alert -->
        <?php if (!empty($info_msg)): ?>
            <div class="alert alert-info">
                <span><?php echo htmlspecialchars($info_msg); ?></span>
                <button type="button" class="alert-close">&times;</button>
            </div>
        <?php endif; ?>

        <!-- Error Alert -->
        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger">
                <span><?php echo htmlspecialchars($error_msg); ?></span>
                <button type="button" class="alert-close">&times;</button>
            </div>
        <?php endif; ?>

        <!-- Quick Demo Accounts Helper -->
        <div class="demo-box">
            <div class="demo-box-title">
                <span>⚡ Quick Demo Credentials</span>
                <a href="setup.php" style="color: #0284c7; text-decoration: none; font-size: 11px;">Reset DB</a>
            </div>
            <div class="demo-buttons">
                <button type="button" class="demo-btn" id="fillWarden">👔 Warden (warden1)</button>
                <button type="button" class="demo-btn" id="fillStudent">🎓 Sample Student (student1)</button>
            </div>
        </div>

        <!-- Login Form -->
        <form action="index.php" method="POST" id="loginForm" novalidate>
            <div class="form-group">
                <label for="username">Username <span class="required">*</span></label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    placeholder="Enter your registered username" 
                    value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                    required 
                    autocomplete="username"
                >
            </div>

            <div class="form-group">
                <label for="password">Password <span class="required">*</span></label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="Enter your password" 
                    required 
                    autocomplete="current-password"
                >
            </div>

            <div style="margin-top: 24px;">
                <button type="submit" class="btn btn-primary btn-full" style="padding: 12px;">
                    Sign In to Dashboard &rarr;
                </button>
            </div>
        </form>

        <!-- New Student Registration Call-To-Action -->
        <div style="margin-top: 24px; padding-top: 18px; border-top: 1px solid #e2e8f0; text-align: center;">
            <p style="font-size: 13px; color: #475569; margin-bottom: 10px;">Are you a new hosteller without an account?</p>
            <a href="register.php" class="btn btn-accent btn-full" style="text-decoration: none; padding: 10px;">
                ✨ Register as New Student &rarr;
            </a>
        </div>

        <div style="text-align: center; margin-top: 20px; font-size: 12px; color: #64748b;">
            College Web Programming Mini Project &bull; PHP & MySQL
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
