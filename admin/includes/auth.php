<?php
// Admin authentication functions

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../login.php');
        exit();
    }
}

function requireAdmin() {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header('Location: ../index.php');
        exit();
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function getCurrentUser($pdo) {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return null;
    }
}

function checkPermission($permission) {
    if (!isAdmin()) {
        return false;
    }
    
    // Add more permission checks here if needed
    return true;
}

function redirectToLogin() {
    header('Location: ../login.php');
    exit();
}

function redirectToAdmin() {
    header('Location: dashboard.php');
    exit();
}

function logout() {
    session_destroy();
    redirectToLogin();
}
?>
