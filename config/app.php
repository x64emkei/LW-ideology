<?php
declare(strict_types=1);

/**
 * Application Core Configuration
 * LagunaWISE — Ideology Division
 */

// Error reporting settings (Development friendly for XAMPP prototype)
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Enforce standard UTF-8 charset
ini_set('default_charset', 'UTF-8');
if (function_exists('mb_internal_encoding')) {
    mb_internal_encoding('UTF-8');
}
if (!headers_sent() && php_sapi_name() !== 'cli') {
    header('Content-Type: text/html; charset=UTF-8');
}

// Timezone
date_default_timezone_set('Asia/Manila');

// Session configuration
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_samesite', 'Lax');
    // If running over HTTPS, set secure flag
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        ini_set('session.cookie_secure', '1');
    }
    session_start();
}

// Local testing & automated screenshot capture helper (restricted strictly to local loopback)
if (in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) {
    if (!empty($_GET['preview_user_id']) && is_numeric($_GET['preview_user_id'])) {
        $_SESSION['user_id'] = (int)$_GET['preview_user_id'];
    }
}

// Compute base directory path relative to web root
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$scriptDir = dirname($scriptName);

// Clean up scriptDir to find base project root
if (str_contains($scriptName, '/admin/')) {
    $baseDir = dirname($scriptDir);
} elseif (str_contains($scriptName, '/pages/') || str_contains($scriptName, '/auth/') || str_contains($scriptName, '/actions/')) {
    $baseDir = dirname($scriptDir);
} else {
    $baseDir = $scriptDir;
}

$baseDir = rtrim($baseDir, '/');
if ($baseDir === '\\' || $baseDir === '.') {
    $baseDir = '';
}

define('APP_NAME', 'LagunaWISE — Ideology Division');
define('APP_VERSION', '1.0.0');
define('APP_BASE_URL', $baseDir);
define('ROOT_PATH', dirname(__DIR__));
define('UPLOAD_DIR', ROOT_PATH . '/assets/uploads');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB

// Require helper files
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/config/security.php';
require_once ROOT_PATH . '/includes/functions.php';
require_once ROOT_PATH . '/includes/auth.php';
