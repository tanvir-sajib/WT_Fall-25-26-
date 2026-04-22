<?php
// ============================================================
// FILE: FT_Project/audit_log.php  (NEW FILE)
// Include this in all admin pages, then call log_action()
// ============================================================

function log_action($conn, $action, $description = '') {
    $admin_id  = $_SESSION['admin_id']   ?? 'unknown';
    $username  = $_SESSION['username']   ?? 'unknown';
    $ip        = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $action    = mysqli_real_escape_string($conn, $action);
    $desc      = mysqli_real_escape_string($conn, $description);
    $admin_id  = mysqli_real_escape_string($conn, $admin_id);
    $username  = mysqli_real_escape_string($conn, $username);

    mysqli_query($conn,
        "INSERT INTO admin_audit_logs (admin_id, admin_username, action, description, ip_address)
         VALUES ('$admin_id', '$username', '$action', '$desc', '$ip')");
}