<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'logs/php_errors.log');

session_start();
define('DB_HOST', 'localhost');
define('DB_NAME', 'crme_db');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur DB: " . $e->getMessage());
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUser($pdo) {
    if (!isLoggedIn()) return null;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND is_active = 1");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        // session refers to missing user: clear it to avoid repeated 403s
        unset($_SESSION['user_id']);
        return null;
    }
    return $user;
}

function requireRole($pdo, $requiredRoles) {
    $user = getCurrentUser($pdo);
    $requiredRoles = (array)$requiredRoles;
    if (!$user) {
        http_response_code(403);
        include '403.php';
        exit;
    }

    // Normalize user roles: support JSON field, or legacy comma-separated string
    $userRoles = json_decode($user['roles'], true);
    if (!is_array($userRoles)) {
        if (is_string($user['roles'])) {
            $s = trim($user['roles']);
            $s = trim($s, "\"' ");
            $userRoles = array_filter(array_map('trim', explode(',', $s)));
        } else {
            $userRoles = (array)$user['roles'];
        }
    }

    foreach ($requiredRoles as $role) {
        if (in_array($role, $userRoles, true)) {
            return $user;
        }
    }

    http_response_code(403);
    include '403.php';
    exit;
}

function requireOwner($pdo, $contactId) {
    $user = getCurrentUser($pdo);
    if (!$user) return false;
    if (empty($contactId) || !is_numeric($contactId)) return false;
    $stmt = $pdo->prepare("SELECT id FROM contacts WHERE id = ? AND owner_id = ?");
    $stmt->execute([$contactId, $user['id']]);
    return $stmt->fetch() !== false;
}
?>
