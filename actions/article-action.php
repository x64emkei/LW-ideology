<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';

require_login();
$user = current_user();
$db = Database::getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Security::enforceCsrf();

    $action = $_POST['action'] ?? '';
    $articleId = (int)($_POST['article_id'] ?? 0);
    $returnTo = $_POST['return_to'] ?? url('pages/dashboard.php');

    if ($articleId <= 0) {
        flash('error', 'Invalid article requested.');
        header('Location: ' . $returnTo);
        exit;
    }

    // Fetch article to check ownership
    $stmt = $db->prepare("SELECT * FROM articles WHERE id = ? LIMIT 1");
    $stmt->execute([$articleId]);
    $article = $stmt->fetch();

    if (!$article) {
        flash('error', 'Article not found.');
        header('Location: ' . $returnTo);
        exit;
    }

    $isAuthor = ((int)$article['author_id'] === (int)$user['id']);
    $isStaff = user_has_any_role(['administrator', 'super_admin', 'admin', 'editor']);

    if (!$isAuthor && !$isStaff) {
        flash('error', 'Unauthorized: You may only manage your own articles.');
        header('Location: ' . $returnTo);
        exit;
    }

    if ($action === 'delete_draft') {
        // Only author or admin can delete a draft
        if ($article['status'] === 'draft' || $isStaff) {
            $del = $db->prepare("DELETE FROM articles WHERE id = ?");
            $del->execute([$articleId]);
            log_activity((int)$user['id'], 'article_deleted', 'article', $articleId, "Draft #{$articleId} deleted by user.");
            flash('success', "Draft '{$article['title']}' was successfully deleted.");
        } else {
            flash('error', 'Only draft manuscripts can be deleted.');
        }
    } elseif ($action === 'submit_for_review') {
        // Change status from draft / revision_requested to pending_review
        if (in_array($article['status'], ['draft', 'revision_requested'], true)) {
            $upd = $db->prepare("UPDATE articles SET status = 'pending_review', updated_at = NOW() WHERE id = ?");
            $upd->execute([$articleId]);

            // Add review record
            $db->prepare("
                INSERT INTO editorial_reviews (article_id, reviewer_id, action, feedback, created_at)
                VALUES (?, ?, 'submitted', 'Manuscript submitted for editorial evaluation.', NOW())
            ")->execute([$articleId, $user['id']]);

            log_activity((int)$user['id'], 'article_submitted', 'article', $articleId, "Article #{$articleId} submitted for review.");
            flash('success', "Article '{$article['title']}' has been submitted for editorial peer review.");
        } else {
            flash('info', 'This article is already in the review or published pipeline.');
        }
    }

    header('Location: ' . $returnTo);
    exit;
}

header('Location: ' . url('pages/dashboard.php'));
exit;
