<?php
declare(strict_types=1);

/**
 * Web Application Security & CSRF Protection
 * LagunaWISE — Ideology Division
 */

class Security {
    /**
     * Send standard defensive HTTP security headers
     */
    public static function setSecurityHeaders(): void {
        if (!headers_sent()) {
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: SAMEORIGIN');
            header('X-XSS-Protection: 1; mode=block');
            header('Referrer-Policy: strict-origin-when-cross-origin');
            header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
        }
    }

    /**
     * Generate or fetch current CSRF token
     */
    public static function getCsrfToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Render hidden HTML input for CSRF protection
     */
    public static function csrfField(): string {
        $token = self::getCsrfToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Validate incoming CSRF token on state-changing requests
     */
    public static function validateCsrfToken(?string $token = null): bool {
        if ($token === null) {
            $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        }

        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Enforce CSRF check or terminate with 403 Forbidden
     */
    public static function enforceCsrf(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!self::validateCsrfToken()) {
                http_response_code(403);
                die('Security Error: Invalid or expired CSRF authentication token. Please refresh the page and try again.');
            }
        }
    }

    /**
     * Rate limiter for sensitive operations (login, reset, contact)
     */
    public static function checkRateLimit(string $action, int $maxAttempts = 5, int $decaySeconds = 300): bool {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $key = 'rate_limit_' . md5($action . '_' . $ip);

        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [
                'attempts' => 1,
                'first_attempt' => time()
            ];
            return true;
        }

        $elapsed = time() - $_SESSION[$key]['first_attempt'];
        if ($elapsed > $decaySeconds) {
            $_SESSION[$key] = [
                'attempts' => 1,
                'first_attempt' => time()
            ];
            return true;
        }

        if ($_SESSION[$key]['attempts'] >= $maxAttempts) {
            return false;
        }

        $_SESSION[$key]['attempts']++;
        return true;
    }

    /**
     * Reset rate limit on success
     */
    public static function clearRateLimit(string $action): void {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $key = 'rate_limit_' . md5($action . '_' . $ip);
        unset($_SESSION[$key]);
    }

    /**
     * Sanitize general plain text input
     */
    public static function sanitize(string $input): string {
        return trim(strip_tags($input));
    }

    /**
     * Validate and sanitize an email address
     */
    public static function validateEmail(string $email): ?string {
        $cleaned = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
        if (filter_var($cleaned, FILTER_VALIDATE_EMAIL)) {
            return strtolower($cleaned);
        }
        return null;
    }

    /**
     * Safe HTML content sanitizer for article bodies
     * Allows semantic tags while stripping script, iframe, inline event handlers, and dangerous protocols
     */
    public static function sanitizeArticleHtml(string $html): string {
        // Strip <script> and <style> tags and their inner content
        $html = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $html);
        $html = preg_replace('#<style(.*?)>(.*?)</style>#is', '', $html);

        // Allowed semantic tags
        $allowed = '<p><br><h1><h2><h3><h4><h5><h6><blockquote><ul><ol><li><strong><em><b><i><a><img><figure><figcaption><cite><code><pre><hr>';
        $cleaned = strip_tags($html, $allowed);

        // Remove any on* event handler attributes like onclick=, onload=, onerror=
        $cleaned = preg_replace('/\s+on[a-zA-Z0-9_-]+\s*=\s*([\"\'`]).*?\1/is', '', $cleaned);
        $cleaned = preg_replace('/\s+on[a-zA-Z0-9_-]+\s*=[^\s>]+/is', '', $cleaned);

        // Neutralize javascript: and vbscript: links
        $cleaned = preg_replace('/href\s*=\s*(["\'])\s*(javascript|vbscript|data):[^"\']*\1/is', 'href="#"', $cleaned);
        $cleaned = preg_replace('/src\s*=\s*(["\'])\s*(javascript|vbscript):[^"\']*\1/is', 'src=""', $cleaned);

        return $cleaned;
    }
}

Security::setSecurityHeaders();
