<?php
/**
 * Hostel Complaint Management System
 * Student Self-Registration (register.php)
 * 
 * Allows any new student to create their own account dynamically.
 */

session_start();

// If already logged in, redirect
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'warden') {
        header("Location: warden.php");
        exit();
    } else {
        header("Location: student.php");
        exit();
    }
}

require_once 'db.php';

$error_msg = "";
$success_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name         = trim($_POST['name'] ?? '');
    $roll_number  = trim($_POST['roll_number'] ?? '');
    $hostel_block = trim($_POST['hostel_block'] ?? 'Block A');
    $room_number  = trim($_POST['room_number'] ?? '');
    $phone        = trim($_POST['phone'] ?? '');
    $username     = trim($_POST['username'] ?? '');
    $password     = trim($_POST['password'] ?? '');
    $confirm_pwd  = trim($_POST['confirm_password'] ?? '');

    // Validation
    if (empty($name) || empty($roll_number) || empty($room_number) || empty($username) || empty($password)) {
        $error_msg = "Please fill in all required fields.";
    } elseif (strlen($username) < 3) {
        $error_msg = "Username must be at least 3 characters long.";
    } elseif (strlen($password) < 6) {
        $error_msg = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirm_pwd) {
        $error_msg = "Passwords do not match. Please verify both fields.";
    } else {
        // Check if username already exists
        $check_sql = "SELECT id FROM users WHERE username = ? LIMIT 1";
        $check_stmt = mysqli_prepare($conn, $check_sql);
        mysqli_stmt_bind_param($check_stmt, "s", $username);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);

        if (mysqli_stmt_num_rows($check_stmt) > 0) {
            $error_msg = "Username '{$username}' is already taken. Please choose a different username.";
            mysqli_stmt_close($check_stmt);
        } else {
            mysqli_stmt_close($check_stmt);

            // Hash password securely
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'student';

            // Insert new student into users table
            $insert_sql = "INSERT INTO users (name, username, password, role, roll_number, hostel_block, room_number, phone) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $insert_stmt = mysqli_prepare($conn, $insert_sql);

            if ($insert_stmt) {
                mysqli_stmt_bind_param(
                    $insert_stmt, 
                    "ssssssss", 
                    $name, 
                    $username, 
                    $hashed_password, 
                    $role, 
                    $roll_number, 
                    $hostel_block, 
                    $room_number, 
                    $phone
                );

                if (mysqli_stmt_execute($insert_stmt)) {
                    $new_user_id = mysqli_insert_id($conn);
                    mysqli_stmt_close($insert_stmt);

                    // Automatically start session for immediate login!
                    session_regenerate_id(true);
                    $_SESSION['user_id']      = $new_user_id;
                    $_SESSION['name']         = $name;
                    $_SESSION['username']     = $username;
                    $_SESSION['role']         = 'student';
                    $_SESSION['roll_number']  = $roll_number;
                    $_SESSION['hostel_block'] = $hostel_block;
                    $_SESSION['room_number']  = $room_number;
                    $_SESSION['success_msg']  = "Registration successful! Welcome to the Hostel Complaint Portal, {$name}.";

                    header("Location: student.php");
                    exit();
                } else {
                    $error_msg = "Registration failed: " . mysqli_error($conn);
                }
            } else {
                $error_msg = "Database error: " . mysqli_error($conn);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration - Hostel Complaint Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-body">
    <div class="auth-card" style="max-width: 540px;">
        <div class="auth-header">
            <div class="auth-icon">🎓</div>
            <h1>New Student Registration</h1>
            <p>Create your personal account to lodge and track hostel complaints</p>
        </div>

        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger">
                <span>⚠️ <?php echo htmlspecialchars($error_msg); ?></span>
                <button type="button" class="alert-close">&times;</button>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST" id="registerForm">
            <div class="form-grid">
                <div class="form-group full-width">
                    <label for="name">Full Name <span class="required">*</span></label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        placeholder="e.g. Ananya Krishnan" 
                        value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" 
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="roll_number">College Roll / Reg No <span class="required">*</span></label>
                    <input 
                        type="text" 
                        id="roll_number" 
                        name="roll_number" 
                        placeholder="e.g. 22CS155" 
                        value="<?php echo isset($_POST['roll_number']) ? htmlspecialchars($_POST['roll_number']) : ''; ?>" 
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="hostel_block">Hostel Block <span class="required">*</span></label>
                    <select id="hostel_block" name="hostel_block" required>
                        <option value="Block A (Boys)" <?php echo (isset($_POST['hostel_block']) && $_POST['hostel_block'] == 'Block A (Boys)') ? 'selected' : ''; ?>>Block A (Boys)</option>
                        <option value="Block B (Boys)" <?php echo (isset($_POST['hostel_block']) && $_POST['hostel_block'] == 'Block B (Boys)') ? 'selected' : ''; ?>>Block B (Boys)</option>
                        <option value="Block C (Girls)" <?php echo (isset($_POST['hostel_block']) && $_POST['hostel_block'] == 'Block C (Girls)') ? 'selected' : ''; ?>>Block C (Girls)</option>
                        <option value="Block D (Girls)" <?php echo (isset($_POST['hostel_block']) && $_POST['hostel_block'] == 'Block D (Girls)') ? 'selected' : ''; ?>>Block D (Girls)</option>
                        <option value="PG Block" <?php echo (isset($_POST['hostel_block']) && $_POST['hostel_block'] == 'PG Block') ? 'selected' : ''; ?>>PG Hostel Block</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="room_number">Room Number <span class="required">*</span></label>
                    <input 
                        type="text" 
                        id="room_number" 
                        name="room_number" 
                        placeholder="e.g. B-204" 
                        value="<?php echo isset($_POST['room_number']) ? htmlspecialchars($_POST['room_number']) : ''; ?>" 
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="phone">Phone / Mobile No</label>
                    <input 
                        type="text" 
                        id="phone" 
                        name="phone" 
                        placeholder="e.g. 9876543210" 
                        value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
                    >
                </div>

                <div class="form-group full-width">
                    <label for="username">Choose a Username <span class="required">*</span></label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        placeholder="e.g. ananya22" 
                        value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                        required
                    >
                    <small style="font-size: 11px; color: #64748b;">This will be your login ID.</small>
                </div>

                <div class="form-group">
                    <label for="password">Password <span class="required">*</span></label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="At least 6 characters" 
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password <span class="required">*</span></label>
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        placeholder="Re-enter password" 
                        required
                    >
                </div>
            </div>

            <div style="margin-top: 20px;">
                <button type="submit" class="btn btn-primary btn-full" style="padding: 12px;">
                    Complete Registration & Enter Dashboard &rarr;
                </button>
            </div>
        </form>

        <div style="text-align: center; margin-top: 20px; font-size: 13px; color: #475569;">
            Already have an account? <a href="index.php" style="color: #0284c7; font-weight: 600; text-decoration: none;">Sign In here</a>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
