<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

if (!is_logged_in()) {
    echo json_encode(['status' => 'unauthorized', 'message' => 'Please log in to bookmark this article.']);
    exit;
}

if (!Security::validateCsrfToken()) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Invalid security token']);
    exit;
}

$articleId = (int)($_POST['article_id'] ?? 0);
$userId = (int)$_SESSION['user_id'];

if ($articleId <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid article']);
    exit;
}

$db = Database::getConnection();

try {
    // Check if already bookmarked
    $check = $db->prepare("SELECT id FROM bookmarks WHERE user_id = ? AND article_id = ?");
    $check->execute([$userId, $articleId]);
    $existing = $check->fetch();

    if ($existing) {
        $del = $db->prepare("DELETE FROM bookmarks WHERE id = ?");
        $del->execute([$existing['id']]);
        log_activity($userId, 'bookmark_removed', 'article', $articleId, 'Bookmark removed.');
        echo json_encode(['status' => 'success', 'action' => 'removed', 'message' => 'Article removed from bookmarks.']);
        exit;
    } else {
        $ins = $db->prepare("INSERT INTO bookmarks (user_id, article_id, created_at) VALUES (?, ?, NOW())");
        $ins->execute([$userId, $articleId]);
        log_activity($userId, 'bookmark_added', 'article', $articleId, 'Article bookmarked.');
        echo json_encode(['status' => 'success', 'action' => 'bookmarked', 'message' => 'Article saved to your bookmarks!']);
        exit;
    }
} catch (Exception $e) {
    error_log("Bookmark action error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
    exit;
}
