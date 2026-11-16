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

/**
 * Render Markdown content to safe, semantic HTML
 * Supports headings, paragraphs, bold, italics, blockquotes, lists, links, images, code, and dividers
 */
function render_markdown(string $md): string {
    $text = trim($md);
    if ($text === '') {
        return '';
    }

    // Normalize newlines
    $text = str_replace(["\r\n", "\r"], "\n", $text);

    // Stash code blocks
    $codeBlocks = [];
    $text = preg_replace_callback('/```([a-zA-Z0-9_-]*)\n([\s\S]*?)```/', function ($matches) use (&$codeBlocks) {
        $key = '%%CODEBLOCK_' . count($codeBlocks) . '%%';
        $lang = htmlspecialchars(trim($matches[1]), ENT_QUOTES, 'UTF-8');
        $code = htmlspecialchars($matches[2], ENT_QUOTES, 'UTF-8');
        $codeBlocks[$key] = '<pre class="my-6 p-4 rounded-xl bg-slate-900 text-slate-100 overflow-x-auto text-xs font-mono leading-relaxed"><code class="language-' . ($lang ?: 'text') . '">' . $code . '</code></pre>';
        return "\n\n" . $key . "\n\n";
    }, $text);

    // Stash inline code
    $inlineCodes = [];
    $text = preg_replace_callback('/`([^`\n]+)`/', function ($matches) use (&$inlineCodes) {
        $key = '%%INLINECODE_' . count($inlineCodes) . '%%';
        $inlineCodes[$key] = '<code class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-800 text-xs font-mono font-semibold">' . htmlspecialchars($matches[1], ENT_QUOTES, 'UTF-8') . '</code>';
        return $key;
    }, $text);

    // Split into blocks by double newlines
    $rawBlocks = preg_split('/\n{2,}/', $text);
    $parsedBlocks = [];

    foreach ($rawBlocks as $block) {
        $block = trim($block);
        if ($block === '') {
            continue;
        }

        // Check for code block placeholder
        if (isset($codeBlocks[$block])) {
            $parsedBlocks[] = $codeBlocks[$block];
            continue;
        }

        // Horizontal Rule (--- or ***)
        if (preg_match('/^(\*{3,}|-{3,}|_{3,})$/', $block)) {
            $parsedBlocks[] = '<hr class="my-8 border-slate-200">';
            continue;
        }

        // Headings (# Heading)
        if (preg_match('/^(#{1,6})\s+(.+)$/m', $block, $m) && !str_contains($block, "\n")) {
            $level = strlen($m[1]);
            $headingText = format_inline_markdown($m[2]);
            $parsedBlocks[] = "<h{$level}>{$headingText}</h{$level}>";
            continue;
        }

        // Blockquotes (> Quote)
        if (str_starts_with($block, '>')) {
            $lines = explode("\n", $block);
            $quoteLines = [];
            foreach ($lines as $line) {
                $quoteLines[] = preg_replace('/^>\s?/', '', $line);
            }
            $quoteContent = format_inline_markdown(implode("\n", $quoteLines));
            $parsedBlocks[] = '<blockquote><p>' . nl2br($quoteContent) . '</p></blockquote>';
            continue;
        }

        // Unordered Lists (- Item or * Item)
        if (preg_match('/^[\*\-]\s+/m', $block)) {
            $lines = explode("\n", $block);
            $items = [];
            foreach ($lines as $line) {
                $line = trim($line);
                if (preg_match('/^[\*\-]\s+(.+)$/', $line, $lm)) {
                    $items[] = '<li>' . format_inline_markdown($lm[1]) . '</li>';
                }
            }
            if (!empty($items)) {
                $parsedBlocks[] = '<ul>' . implode('', $items) . '</ul>';
                continue;
            }
        }

        // Ordered Lists (1. Item)
        if (preg_match('/^\d+\.\s+/m', $block)) {
            $lines = explode("\n", $block);
            $items = [];
            foreach ($lines as $line) {
                $line = trim($line);
                if (preg_match('/^\d+\.\s+(.+)$/', $line, $lm)) {
                    $items[] = '<li>' . format_inline_markdown($lm[1]) . '</li>';
                }
            }
            if (!empty($items)) {
                $parsedBlocks[] = '<ol>' . implode('', $items) . '</ol>';
                continue;
            }
        }

        // If block is already an HTML block tag, preserve and format inline
        if (preg_match('/^<(p|div|figure|blockquote|h[1-6]|ul|ol|table)\b/i', $block)) {
            $parsedBlocks[] = format_inline_markdown($block);
            continue;
        }

        // Standard Paragraph with inline formatting
        $inlineFormatted = format_inline_markdown($block);
        $inlineFormatted = nl2br($inlineFormatted);
        $parsedBlocks[] = '<p>' . $inlineFormatted . '</p>';
    }

    $html = implode("\n\n", $parsedBlocks);

    // Restore inline code
    foreach ($inlineCodes as $key => $codeHtml) {
        $html = str_replace($key, $codeHtml, $html);
    }

    // Restore any leftover code blocks
    foreach ($codeBlocks as $key => $blockHtml) {
        $html = str_replace($key, $blockHtml, $html);
    }

    return $html;
}

/**
 * Format inline markdown syntax (bold, italics, links, images)
 */
function format_inline_markdown(string $text): string {
    // Images: ![alt](url)
    $text = preg_replace_callback('/!\[([^\]]*)\]\(([^)]+)\)/', function ($m) {
        $alt = htmlspecialchars($m[1], ENT_QUOTES, 'UTF-8');
        $src = htmlspecialchars(trim($m[2]), ENT_QUOTES, 'UTF-8');
        $caption = !empty($alt) ? '<figcaption class="text-xs text-slate-500 mt-2 text-center">' . $alt . '</figcaption>' : '';
        return '<figure class="my-8"><img src="' . $src . '" alt="' . $alt . '" class="rounded-xl shadow-md w-full max-h-[500px] object-cover">' . $caption . '</figure>';
    }, $text);

    // Links: [text](url)
    $text = preg_replace_callback('/\[([^\]]+)\]\(([^)]+)\)/', function ($m) {
        $label = $m[1];
        $url = htmlspecialchars(trim($m[2]), ENT_QUOTES, 'UTF-8');
        if (preg_match('/^javascript:/i', $url)) {
            $url = '#';
        }
        return '<a href="' . $url . '" target="_blank" rel="noopener noreferrer">' . $label . '</a>';
    }, $text);

    // Bold + Italic: ***text***
    $text = preg_replace('/\*\*\*([^\*]+)\*\*\*/', '<strong><em>$1</em></strong>', $text);

    // Bold: **text** or __text__
    $text = preg_replace('/\*\*([^\*]+)\*\*/', '<strong>$1</strong>', $text);
    $text = preg_replace('/__([^_\n]+)__/', '<strong>$1</strong>', $text);

    // Italic: *text* or _text_
    $text = preg_replace('/\*([^\*\n]+)\*/', '<em>$1</em>', $text);
    $text = preg_replace('/(?<=\s|^)_([^_\n]+)_(?=\s|$|[.,;:!?])/', '<em>$1</em>', $text);

    // Citations / Em dashes: -- or —
    $text = preg_replace('/(?<=\s)--(?=\s)/', '&mdash;', $text);

    return $text;
}

/**
 * Render article content safely with Markdown parsing and HTML sanitization
 */
function render_article_content(string $content): string {
    $parsedHtml = render_markdown($content);
    return Security::sanitizeArticleHtml($parsedHtml);
}
