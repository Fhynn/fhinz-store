<?php
// Admin configuration file

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include necessary files
require_once '../config/database.php';
require_once 'functions.php';
require_once 'auth.php';

// Set timezone
date_default_timezone_set('Asia/Jakarta');

// Define constants
define('ADMIN_URL', 'http://localhost/fhinz-store/admin/');
define('SITE_URL', 'http://localhost/fhinz-store/');
define('UPLOAD_PATH', '../uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in and has admin access
requireLogin();
requireAdmin();
?>
