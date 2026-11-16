<?php
declare(strict_types=1);

/**
 * Authentication & Role-Based Access Control (RBAC) System
 * LagunaWISE — Ideology Division
 */

function is_logged_in(): bool {
    return !empty($_SESSION['user_id']);
}

function current_user(): ?array {
    if (!is_logged_in()) {
        return null;
    }

    static $currentUser = null;

    if ($currentUser === null) {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("
                SELECT u.id, u.name, u.username, u.email, u.bio, u.avatar, u.status, u.must_change_password, u.created_at
                FROM users u
                WHERE u.id = ? AND u.status = 'active'
                LIMIT 1
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();

            if ($user) {
                // Fetch roles
                $roleStmt = $db->prepare("
                    SELECT r.name, r.display_name
                    FROM roles r
                    JOIN user_roles ur ON ur.role_id = r.id
                    WHERE ur.user_id = ?
                ");
                $roleStmt->execute([$user['id']]);
                $roles = $roleStmt->fetchAll();

                $user['roles'] = array_column($roles, 'name');
                $user['roles_data'] = $roles;
                $user['primary_role'] = $user['roles'][0] ?? 'reader';
                $currentUser = $user;
            } else {
                // User inactive or removed; logout
                logout_user();
                return null;
            }
        } catch (Exception $e) {
            error_log("current_user error: " . $e->getMessage());
            return null;
        }
    }

    return $currentUser;
}

function user_has_role(string $role): bool {
    $user = current_user();
    if (!$user) return false;

    // Administrator inherently satisfies administrative/editorial role checks
    if (in_array('administrator', $user['roles'], true) || in_array('super_admin', $user['roles'], true) || in_array('admin', $user['roles'], true)) {
        return true;
    }

    if ($role === 'contributor' && in_array('writer', $user['roles'], true)) return true;
    if ($role === 'writer' && in_array('contributor', $user['roles'], true)) return true;

    return in_array($role, $user['roles'], true);
}

function user_has_any_role(array $roles): bool {
    $user = current_user();
    if (!$user) return false;

    if (in_array('administrator', $user['roles'], true) || in_array('super_admin', $user['roles'], true) || in_array('admin', $user['roles'], true)) {
        return true;
    }

    foreach ($roles as $role) {
        if (in_array($role, $user['roles'], true)) {
            return true;
        }
        if ($role === 'contributor' && in_array('writer', $user['roles'], true)) {
            return true;
        }
        if ($role === 'writer' && in_array('contributor', $user['roles'], true)) {
            return true;
        }
        if ($role === 'admin' && in_array('administrator', $user['roles'], true)) {
            return true;
        }
    }

    return false;
}

function require_login(string $redirect = ''): void {
    if (!is_logged_in()) {
        if (empty($redirect)) {
            $redirect = $_SERVER['REQUEST_URI'] ?? '';
        }
        flash('error', 'Please log in to access this page.');
        header('Location: ' . url('auth/login.php?redirect=' . urlencode($redirect)));
        exit;
    }

    $user = current_user();
    // Enforce mandatory password change if flagged (e.g., initial local admin setup)
    if (!empty($user['must_change_password'])) {
        $currentScript = $_SERVER['SCRIPT_NAME'] ?? '';
        if (!str_contains($currentScript, 'profile.php') && !str_contains($currentScript, 'logout.php')) {
            flash('warning', 'Notice: You must change your default administrator password before continuing.');
            header('Location: ' . url('admin/profile.php'));
            exit;
        }
    }
}

function require_role($roles, string $redirect = 'index.php'): void {
    require_login();

    $rolesArray = is_array($roles) ? $roles : [$roles];
    if (!user_has_any_role($rolesArray)) {
        http_response_code(403);
        flash('error', 'Unauthorized: You do not possess the required permissions to access that resource.');
        header('Location: ' . url($redirect));
        exit;
    }
}

function login_user(array $user, array $roles = []): void {
    // Regenerate session id to prevent session fixation attacks
    session_regenerate_id(true);

    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['avatar'] = $user['avatar'] ?? 'assets/images/default-avatar.svg';
    $_SESSION['roles'] = $roles;

    // Update last_login_at in database
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);

        log_activity((int)$user['id'], 'user_login', 'user', (int)$user['id'], 'User logged in successfully.');
    } catch (Exception $e) {
        // non-fatal
    }
}

function logout_user(): void {
    if (is_logged_in()) {
        log_activity((int)$_SESSION['user_id'], 'user_logout', 'user', (int)$_SESSION['user_id'], 'User logged out.');
    }

    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    session_destroy();
}
