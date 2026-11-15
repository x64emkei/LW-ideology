<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url('pages/articles.php'));
    exit;
}

Security::enforceCsrf();

if (!Security::checkRateLimit('comment_submission', 5, 300)) {
    flash('error', 'Rate limit exceeded: Please wait before submitting additional comments.');
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? url('pages/articles.php')));
    exit;
}

$articleId = (int)($_POST['article_id'] ?? 0);
$slug = trim($_POST['slug'] ?? '');
$content = Security::sanitize($_POST['content'] ?? '');

$currentUser = current_user();

if ($currentUser) {
    $authorName = $currentUser['name'];
    $authorEmail = $currentUser['email'];
    $userId = (int)$currentUser['id'];
} else {
    $authorName = Security::sanitize($_POST['author_name'] ?? '');
    $authorEmail = Security::validateEmail($_POST['author_email'] ?? '');
    $userId = null;
}

if ($articleId <= 0 || empty($content) || empty($authorName) || empty($authorEmail)) {
    flash('error', 'Please fill in all required fields to submit your comment.');
    header('Location: ' . url('pages/article.php?slug=' . urlencode($slug) . '#comments'));
    exit;
}

$db = Database::getConnection();

try {
    // Verify article exists and is published
    $check = $db->prepare("SELECT id FROM articles WHERE id = ? AND status = 'published' LIMIT 1");
    $check->execute([$articleId]);
    if (!$check->fetch()) {
        flash('error', 'Unable to comment on this article.');
        header('Location: ' . url('pages/articles.php'));
        exit;
    }

    $stmt = $db->prepare("
        INSERT INTO comments (article_id, user_id, author_name, author_email, content, status, created_at)
        VALUES (?, ?, ?, ?, ?, 'approved', NOW())
    ");
    $stmt->execute([$articleId, $userId, $authorName, $authorEmail, $content]);

    log_activity($userId, 'comment_submitted', 'comment', (int)$db->lastInsertId(), "Comment posted on article ID {$articleId}");

    flash('success', 'Your comment has been submitted and posted to the discussion.');
    header('Location: ' . url('pages/article.php?slug=' . urlencode($slug) . '#comments'));
    exit;

} catch (Exception $e) {
    error_log("Comment error: " . $e->getMessage());
    flash('error', 'An error occurred while posting your comment.');
    header('Location: ' . url('pages/article.php?slug=' . urlencode($slug) . '#comments'));
    exit;
}
