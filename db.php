<?php
/**
 * Hostel Complaint Management System
 * Database Connection File (db.php)
 * 
 * Uses MySQLi with MySQL running on localhost:3307
 */

// Database configuration settings
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "hostel_complaint_db";
$db_port = 3307; // Configured for port 3307 as required

// Create connection to MySQL database
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name, $db_port);

// Check if connection succeeded
if (!$conn) {
    // If database does not exist yet or connection fails, give clear guidance
    $error_msg = mysqli_connect_error();
    die("
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Database Connection Error</title>
        <link rel='stylesheet' href='style.css'>
    </head>
    <body class='auth-body'>
        <div class='auth-card' style='max-width: 520px; text-align: center;'>
            <div style='font-size: 48px; color: #ef4444; margin-bottom: 12px;'>⚠️</div>
            <h2 style='color: #1e3a8a; margin-bottom: 8px;'>Database Connection Error</h2>
            <p style='color: #475569; font-size: 14px; margin-bottom: 16px;'>
                Could not connect to MySQL server on <strong>localhost:3307</strong>.
            </p>
            <div style='background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px; border-radius: 8px; font-size: 13px; text-align: left; margin-bottom: 20px;'>
                <strong>Error Details:</strong> " . htmlspecialchars($error_msg) . "
            </div>
            <div style='text-align: left; background: #f8fafc; border: 1px solid #e2e8f0; padding: 14px; border-radius: 8px; font-size: 13px; margin-bottom: 20px;'>
                <strong>Troubleshooting Steps:</strong>
                <ol style='margin-left: 20px; margin-top: 8px; color: #334155;'>
                    <li>Open <strong>XAMPP Control Panel</strong>.</li>
                    <li>Ensure <strong>Apache</strong> and <strong>MySQL</strong> are started (Running).</li>
                    <li>Verify MySQL is configured on port <strong>3307</strong>.</li>
                    <li>If you haven't initialized the database yet, run the setup script:</li>
                </ol>
            </div>
            <a href='setup.php' class='btn btn-primary' style='display: inline-block; width: 100%; text-decoration: none;'>Run One-Click Database Setup (setup.php)</a>
        </div>
    </body>
    </html>
    ");
}

// Set character set to utf8mb4 for unicode support
mysqli_set_charset($conn, "utf8mb4");
?>
