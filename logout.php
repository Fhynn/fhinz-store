<?php
require_once 'config/database.php';

// Destroy session
session_unset();
session_destroy();

// Redirect to login page with success message
header('Location: login.php?message=logout_success');
exit();
?>