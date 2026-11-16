<?php
declare(strict_types=1);

/**
 * LagunaWISE Ideology Division — CLI Administrative User Creation
 * Usage: php cli/create-admin.php [username] [email] [password] [fullName]
 */

require_once dirname(__DIR__) . '/config/app.php';

if (php_sapi_name() !== 'cli') {
    die("This script can only be executed via the command line interface.\n");
}

$username = $argv[1] ?? 'admin';
$email = $argv[2] ?? 'admin@lagunawise.org';
$password = $argv[3] ?? 'AdminPass123!';
$fullName = $argv[4] ?? 'System Administrator';

$db = Database::getConnection();

// Check if user already exists
$stmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
$stmt->execute([$username, $email]);
$existing = $stmt->fetch();

if ($existing) {
    echo "User already exists with ID: {$existing['id']}. Updating to administrator role...\n";
    $userId = (int)$existing['id'];
} else {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $ins = $db->prepare("
        INSERT INTO users (name, username, email, password_hash, avatar, status, must_change_password, created_at)
        VALUES (?, ?, ?, ?, 'assets/images/avatar-admin.svg', 'active', 0, NOW())
    ");
    $ins->execute([$fullName, $username, $email, $hash]);
    $userId = (int)$db->lastInsertId();
    echo "Created user account '{$username}' (ID: {$userId})\n";
}

// Ensure Administrator role assigned
$roleStmt = $db->prepare("SELECT id FROM roles WHERE name = 'administrator' LIMIT 1");
$roleStmt->execute();
$adminRoleId = (int)$roleStmt->fetchColumn();

if ($adminRoleId > 0) {
    $db->prepare("DELETE FROM user_roles WHERE user_id = ?")->execute([$userId]);
    $db->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)")->execute([$userId, $adminRoleId]);
    echo "Successfully assigned 'Administrator' role to user ID {$userId}.\n";
} else {
    echo "Error: Administrator role not found in roles table.\n";
}
