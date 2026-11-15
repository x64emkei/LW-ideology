<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url());
    exit;
}

Security::enforceCsrf();

$email = Security::validateEmail($_POST['email'] ?? '');
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if (!$email) {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Please provide a valid email address.']);
        exit;
    }
    flash('error', 'Please provide a valid email address to subscribe.');
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? url()));
    exit;
}

$db = Database::getConnection();

try {
    // Check existing
    $stmt = $db->prepare("SELECT id, status FROM newsletter_subscribers WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $existing = $stmt->fetch();

    if ($existing) {
        if ($existing['status'] === 'subscribed') {
            $msg = 'You are already subscribed to the LagunaWISE Ideology Division dispatch!';
        } else {
            $update = $db->prepare("UPDATE newsletter_subscribers SET status = 'subscribed', subscribed_at = NOW() WHERE id = ?");
            $update->execute([$existing['id']]);
            $msg = 'Welcome back! Your subscription to the dispatch has been reactivated.';
        }
    } else {
        $token = bin2hex(random_bytes(16));
        $insert = $db->prepare("INSERT INTO newsletter_subscribers (email, status, token, subscribed_at) VALUES (?, 'subscribed', ?, NOW())");
        $insert->execute([$email, $token]);
        $msg = 'Thank you for subscribing to the LagunaWISE Ideology Division Dispatch!';
    }

    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'message' => $msg]);
        exit;
    }

    flash('success', $msg);
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? url()));
    exit;

} catch (Exception $e) {
    error_log("Newsletter subscription error: " . $e->getMessage());
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'An error occurred while saving your subscription.']);
        exit;
    }
    flash('error', 'An error occurred while subscribing. Please try again.');
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? url()));
    exit;
}
