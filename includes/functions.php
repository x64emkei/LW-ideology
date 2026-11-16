<?php
declare(strict_types=1);

/**
 * Global Helper Functions & Utilities
 * LagunaWISE — Ideology Division
 */

/**
 * Context-appropriate HTML escaping
 */
function e(?string $string): string {
    return htmlspecialchars((string)($string ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Render hidden HTML input for CSRF protection
 */
function csrf_field(): string {
    return Security::csrfField();
}

/**
 * Get current CSRF token string
 */
function csrf_token(): string {
    return Security::getCsrfToken();
}

/**
 * Generate full URL based on application base path
 */
function url(string $path = ''): string {
    $base = APP_BASE_URL;
    $path = '/' . ltrim($path, '/');
    return ($base === '' ? '' : $base) . $path;
}

/**
 * Generate asset URL
 */
function asset(string $path): string {
    return url(ltrim($path, '/'));
}

/**
 * Create URL-friendly slug
 */
function slugify(string $text): string {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text) ?: $text;
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'n-a' : $text;
}

/**
 * Calculate estimated reading time in minutes
 */
function calculate_reading_time(string $content, int $wpm = 200): int {
    $wordCount = str_word_count(strip_tags($content));
    $minutes = (int)ceil($wordCount / $wpm);
    return max(1, $minutes);
}

/**
 * Truncate text cleanly to a given word limit
 */
function truncate_words(string $text, int $limit = 30, string $end = '...'): string {
    $plain = strip_tags($text);
    $words = explode(' ', $plain);
    if (count($words) <= $limit) {
        return $plain;
    }
    return implode(' ', array_slice($words, 0, $limit)) . $end;
}

/**
 * Format timestamp into readable date
 */
function format_date(?string $datetime, string $format = 'M j, Y'): string {
    if (empty($datetime)) return 'N/A';
    try {
        $dt = new DateTime($datetime);
        return $dt->format($format);
    } catch (Exception $e) {
        return $datetime;
    }
}

/**
 * Set flash message
 */
function flash(string $type, string $message): void {
    $_SESSION['flash_messages'][] = [
        'type' => $type, // 'success', 'error', 'warning', 'info'
        'message' => $message
    ];
}

/**
 * Retrieve and clear flash messages
 */
function get_flash(): array {
    $messages = $_SESSION['flash_messages'] ?? [];
    unset($_SESSION['flash_messages']);
    return $messages;
}

/**
 * Retrieve a website setting by key from database
 */
function get_setting(string $key, ?string $default = null): string {
    static $cache = null;

    if ($cache === null) {
        $cache = [];
        try {
            $db = Database::getConnection();
            $stmt = $db->query("SELECT setting_key, setting_value FROM website_settings");
            while ($row = $stmt->fetch()) {
                $cache[$row['setting_key']] = $row['setting_value'];
            }
        } catch (Exception $e) {
            // fallback
        }
    }

    return $cache[$key] ?? $default ?? '';
}

/**
 * Log activity for administrative auditing
 */
function log_activity(?int $userId, string $action, ?string $entityType = null, ?int $entityId = null, ?string $details = null): void {
    try {
        $db = Database::getConnection();
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 500);

        $stmt = $db->prepare("
            INSERT INTO activity_logs (user_id, action, entity_type, entity_id, ip_address, user_agent, details, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$userId, $action, $entityType, $entityId, $ip, $ua, $details]);
    } catch (Exception $e) {
        error_log("Failed to log activity: " . $e->getMessage());
    }
}
