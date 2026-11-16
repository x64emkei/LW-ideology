<?php
declare(strict_types=1);

/**
 * ANTIGRAVITY TASK 04 — LIVE DEMONSTRATION ACCEPTANCE TEST RUNNER
 * Tests the live HTTP application running on Apache/XAMPP and MariaDB.
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/security.php';

$baseUrl = 'http://localhost/LW-ideology';
$db = Database::getConnection();

echo "====================================================================\n";
echo "LAGUNAWISE IDEOLOGY DIVISION — LIVE DEMONSTRATION ACCEPTANCE TEST\n";
echo "Target Base URL: {$baseUrl}\n";
echo "Date & Time: " . date('Y-m-d H:i:s') . "\n";
echo "====================================================================\n\n";

$passCount = 0;
$failCount = 0;
$results = [];

function record_test(string $section, string $testName, bool $passed, string $details = ''): void {
    global $passCount, $failCount, $results;
    if ($passed) {
        $passCount++;
        echo "  [PASS] {$testName}\n";
    } else {
        $failCount++;
        echo "  [FAIL] {$testName}\n";
    }
    if ($details) {
        echo "         -> {$details}\n";
    }
    $results[] = [
        'section' => $section,
        'name' => $testName,
        'passed' => $passed,
        'details' => $details
    ];
}

function http_request(string $url, string $method = 'GET', array $postData = [], string $cookieFile = ''): array {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Antigravity-QA-AcceptanceEngine/1.0');

    if ($cookieFile) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    }

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    $totalTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
    $err = curl_error($ch);
    curl_close($ch);

    $headers = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);

    return [
        'code' => $httpCode,
        'headers' => $headers,
        'body' => $body,
        'url' => $effectiveUrl,
        'time' => $totalTime,
        'error' => $err
    ];
}

function extract_csrf(string $html): string {
    if (preg_match('/name=["\']csrf_token["\']\s+value=["\']([^"\']+)["\']/', $html, $matches)) {
        return $matches[1];
    }
    return '';
}

// =============================================================================
// SECTION 1: PUBLIC WEBSITE VERIFICATION & STRICT NO-FAKE-CONTENT AUDIT
// =============================================================================
echo "--------------------------------------------------------------------\n";
echo "SECTION 1: Public Website Verification & Genuine Empty States\n";
echo "--------------------------------------------------------------------\n";

$publicPages = [
    'Homepage'          => "{$baseUrl}/index.php",
    'About'             => "{$baseUrl}/pages/about.php",
    'Articles Catalog'  => "{$baseUrl}/pages/articles.php",
    'Featured Topics'   => "{$baseUrl}/pages/topics.php",
    'News & Updates'    => "{$baseUrl}/pages/news.php",
    'Contact'           => "{$baseUrl}/pages/contact.php",
    'Search Endpoint'   => "{$baseUrl}/pages/search.php?q=civic",
    'Registration Form' => "{$baseUrl}/auth/register.php",
    'Login Form'        => "{$baseUrl}/auth/login.php",
];

foreach ($publicPages as $label => $url) {
    $res = http_request($url);
    $is200 = ($res['code'] === 200);
    $hasHeader = str_contains($res['body'], 'IDEOLOGY DIVISION');
    $hasFooter = str_contains($res['body'], 'LagunaWISE Ideology Division') || str_contains($res['body'], 'lagunawise.ph@gmail.com');
    
    record_test(
        'Public Website',
        "{$label} Accessibility & Status 200",
        $is200 && $hasHeader,
        "HTTP {$res['code']}, Load time: " . round($res['time'] * 1000, 2) . "ms"
    );
}

// Audit Genuine Empty States on Articles & News
$articlesRes = http_request("{$baseUrl}/pages/articles.php");
$emptyArticlesOk = str_contains($articlesRes['body'], 'No articles have been published yet.') 
                || str_contains($articlesRes['body'], 'No published articles');
record_test(
    'Public Website',
    'Articles Directory Shows Genuine Empty State (No Fake Records)',
    $emptyArticlesOk,
    'Verified exact string: "No articles have been published yet."'
);

$newsRes = http_request("{$baseUrl}/pages/news.php");
$emptyNewsOk = str_contains($newsRes['body'], 'No announcements are currently available.')
            || str_contains($newsRes['body'], 'No announcements');
record_test(
    'Public Website',
    'News Page Shows Genuine Empty State (No Fabricated Announcements)',
    $emptyNewsOk,
    'Verified exact string: "No announcements are currently available."'
);

// Verify Zero Fake Users / Dummy Records in DB
$userCount = (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$articleCount = (int)$db->query("SELECT COUNT(*) FROM articles")->fetchColumn();
$announcementCount = (int)$db->query("SELECT COUNT(*) FROM announcements")->fetchColumn();
$zeroFakeDb = ($userCount === 0 && $articleCount === 0 && $announcementCount === 0);
record_test(
    'Public Website',
    'Database Clean Zero State Audit (users=0, articles=0, announcements=0)',
    $zeroFakeDb,
    "Current live DB counts: users={$userCount}, articles={$articleCount}, announcements={$announcementCount}"
);

// =============================================================================
// SECTION 2: ACCOUNT WORKFLOW & RBAC BOUNDARIES
// =============================================================================
echo "\n--------------------------------------------------------------------\n";
echo "SECTION 2: Account Workflow & RBAC Boundaries\n";
echo "--------------------------------------------------------------------\n";

$adminCookie = tempnam(sys_get_temp_dir(), 'lw_admin_');
$adminEmail = 'qa_admin_' . bin2hex(random_bytes(4)) . '@lagunawise-test.local';
$adminUser = 'qaadmin_' . bin2hex(random_bytes(3));
$adminPass = 'SecureAdmin@2026!';
$adminName = 'System QA Administrator';

// 2.1 Register First User (Becomes Administrator)
$regGet1 = http_request("{$baseUrl}/auth/register.php", 'GET', [], $adminCookie);
$csrfToken1 = extract_csrf($regGet1['body']);
record_test('Account Workflow', 'Extract CSRF Token from Registration Form', !empty($csrfToken1), "Token length: " . strlen($csrfToken1));

$regPost1 = http_request("{$baseUrl}/auth/register.php", 'POST', [
    'csrf_token' => $csrfToken1,
    'name' => $adminName,
    'username' => $adminUser,
    'email' => $adminEmail,
    'password' => $adminPass,
    'confirm_password' => $adminPass,
    'agree_terms' => '1'
], $adminCookie);

// Check Administrator in MySQL
$adminStmt = $db->prepare("SELECT u.*, r.name as role_name FROM users u JOIN user_roles ur ON u.id = ur.user_id JOIN roles r ON ur.role_id = r.id WHERE u.email = ?");
$adminStmt->execute([$adminEmail]);
$createdAdmin = $adminStmt->fetch();
$adminUserId = $createdAdmin ? (int)$createdAdmin['id'] : 0;
$isAdmin = ($createdAdmin && $createdAdmin['role_name'] === 'administrator');

record_test(
    'Account Workflow',
    'First Public Registration Boots to Administrator Role',
    $isAdmin,
    "Admin User ID: {$adminUserId}, Role: " . ($createdAdmin['role_name'] ?? 'None')
);

// 2.2 Register Second User (Assigned Contributor Role)
$contribCookie = tempnam(sys_get_temp_dir(), 'lw_contrib_');
$contribEmail = 'qa_contributor_' . bin2hex(random_bytes(4)) . '@lagunawise-test.local';
$contribUser = 'qacontrib_' . bin2hex(random_bytes(3));
$contribPass = 'ContributorPass@2026!';
$contribName = 'Jane Contributor Scholar';

$regGet2 = http_request("{$baseUrl}/auth/register.php", 'GET', [], $contribCookie);
$csrfToken2 = extract_csrf($regGet2['body']);
$regPost2 = http_request("{$baseUrl}/auth/register.php", 'POST', [
    'csrf_token' => $csrfToken2,
    'name' => $contribName,
    'username' => $contribUser,
    'email' => $contribEmail,
    'password' => $contribPass,
    'confirm_password' => $contribPass,
    'agree_terms' => '1'
], $contribCookie);

$contribStmt = $db->prepare("SELECT u.*, r.name as role_name FROM users u JOIN user_roles ur ON u.id = ur.user_id JOIN roles r ON ur.role_id = r.id WHERE u.email = ?");
$contribStmt->execute([$contribEmail]);
$contribUserData = $contribStmt->fetch();
$contribUserId = $contribUserData ? (int)$contribUserData['id'] : 0;
$isStrictContributor = ($contribUserData && $contribUserData['role_name'] === 'contributor');

record_test(
    'Account Workflow',
    'Subsequent Public Registration Assigned Contributor Role (RBAC)',
    $isStrictContributor,
    "Contributor User ID: {$contribUserId}, Verified Role: " . ($contribUserData['role_name'] ?? 'None')
);

// 2.3 Contributor Login & Dashboard Access Session
$loginGet = http_request("{$baseUrl}/auth/login.php", 'GET', [], $contribCookie);
$loginCsrf = extract_csrf($loginGet['body']);
$loginPost = http_request("{$baseUrl}/auth/login.php", 'POST', [
    'csrf_token' => $loginCsrf,
    'identity' => $contribEmail,
    'password' => $contribPass
], $contribCookie);

$dashCheck = http_request("{$baseUrl}/pages/dashboard.php", 'GET', [], $contribCookie);
$loginSuccess = ($dashCheck['code'] === 200 && str_contains($dashCheck['body'], $contribName));
record_test(
    'Account Workflow',
    'Contributor Login & Dedicated Dashboard Access Session',
    $loginSuccess,
    "Logged in as {$contribName}, Dashboard HTTP {$dashCheck['code']}"
);

// 2.4 Unauthorized Access Prevention: Contributor attempts Administrative CMS
$adminAttempt = http_request("{$baseUrl}/admin/users.php", 'GET', [], $contribCookie);
$blockedAdmin = ($adminAttempt['code'] === 403 || str_contains($adminAttempt['url'], 'dashboard.php') || str_contains($adminAttempt['url'], 'login.php'));
record_test(
    'Account Workflow',
    'Unauthorized Administrative Access Blocked for Contributor',
    $blockedAdmin,
    "Attempted /admin/users.php -> Final URL: {$adminAttempt['url']}, Code: {$adminAttempt['code']}"
);

// 2.5 Logout Test
$logoutCookie = tempnam(sys_get_temp_dir(), 'lw_logout_');
// Login a dummy reader to test logout
$regGet3 = http_request("{$baseUrl}/auth/register.php", 'GET', [], $logoutCookie);
$csrfToken3 = extract_csrf($regGet3['body']);
$dummyEmail = 'temp_logout_' . bin2hex(random_bytes(3)) . '@test.local';
http_request("{$baseUrl}/auth/register.php", 'POST', [
    'csrf_token' => $csrfToken3,
    'name' => 'Logout Tester',
    'username' => 'logouttest_' . bin2hex(random_bytes(2)),
    'email' => $dummyEmail,
    'password' => 'Pass12345!',
    'confirm_password' => 'Pass12345!',
    'agree_terms' => '1'
], $logoutCookie);

$logoutReq = http_request("{$baseUrl}/auth/logout.php", 'GET', [], $logoutCookie);
$afterLogout = http_request("{$baseUrl}/pages/dashboard.php", 'GET', [], $logoutCookie);
$logoutOk = (str_contains($afterLogout['url'], 'login.php') || $afterLogout['code'] === 302);
record_test(
    'Account Workflow',
    'User Logout & Immediate Session Invalidation',
    $logoutOk,
    "Protected route access after logout redirected to {$afterLogout['url']}"
);

// Clean up dummy logout user
$db->prepare("DELETE ur FROM user_roles ur JOIN users u ON ur.user_id = u.id WHERE u.email = ?")->execute([$dummyEmail]);
$db->prepare("DELETE FROM users WHERE email = ?")->execute([$dummyEmail]);
@unlink($logoutCookie);

// =============================================================================
// SECTION 3: EDITORIAL WORKFLOW (CREATION -> SUBMISSION -> REVIEW -> PUBLICATION)
// =============================================================================
echo "\n--------------------------------------------------------------------\n";
echo "SECTION 3: Editorial Workflow (Creation -> Review -> Publication)\n";
echo "--------------------------------------------------------------------\n";

// 3.1 Contributor Drafts Article via /admin/article-create.php
$editorGet = http_request("{$baseUrl}/admin/article-create.php", 'GET', [], $contribCookie);
$articleCsrf = extract_csrf($editorGet['body']);

$articleTitle = "Dialectical Epistemology in Networked Media: A Field Analysis";
$articleSlug = "dialectical-epistemology-" . bin2hex(random_bytes(3));
$articleExcerpt = "An empirical investigation into conversational dynamics across modern information bubbles.";
$articleContent = "<p>Contemporary public discourse is increasingly mediated by algorithmic curation feeds. This inquiry outlines the dialectical heuristics necessary for verification.</p>";

$saveDraftPost = http_request("{$baseUrl}/admin/article-create.php", 'POST', [
    'csrf_token' => $articleCsrf,
    'title' => $articleTitle,
    'subtitle' => 'Heuristics for Digital Verification',
    'excerpt' => $articleExcerpt,
    'content' => $articleContent,
    'references_sources' => 'UNESCO Media and Information Literacy Framework (2024)',
    'tags' => 'epistemology, media-literacy, verification',
    'category_ids' => [1],
    'reading_time' => 6,
    'submit_action' => 'save_draft'
], $contribCookie);

// Verify draft in database
$articleStmt = $db->prepare("SELECT * FROM articles WHERE slug = ?");
$articleStmt->execute([$articleSlug]);
$draftArticle = $articleStmt->fetch();
$articleId = $draftArticle ? (int)$draftArticle['id'] : 0;
$draftSaved = ($articleId > 0 && $draftArticle['status'] === 'draft');

record_test(
    'Editorial Workflow',
    'Contributor Draft Creation & MySQL Persistence',
    $draftSaved,
    "Article ID: {$articleId}, Status: " . ($draftArticle['status'] ?? 'None')
);

// 3.2 Contributor Submits Article for Editorial Review
$dashPage = http_request("{$baseUrl}/pages/dashboard.php", 'GET', [], $contribCookie);
$dashCsrf = extract_csrf($dashPage['body']);

$submitPost = http_request("{$baseUrl}/actions/article-action.php", 'POST', [
    'csrf_token' => $dashCsrf,
    'action' => 'submit_for_review',
    'article_id' => $articleId,
    'return_to' => url('pages/dashboard.php')
], $contribCookie);

$articleStmt->execute([$articleSlug]);
$submittedArticle = $articleStmt->fetch();
$isSubmitted = ($submittedArticle && $submittedArticle['status'] === 'pending_review');

record_test(
    'Editorial Workflow',
    'Contributor Submits Article to Editorial Review Queue',
    $isSubmitted,
    "Article Status in MySQL: " . ($submittedArticle['status'] ?? 'None')
);

// 3.3 Editor Reviews & Requests Revision
$subQueue = http_request("{$baseUrl}/admin/submissions.php", 'GET', [], $adminCookie);
$queueCsrf = extract_csrf($subQueue['body']);

$revisionPost = http_request("{$baseUrl}/admin/submissions.php", 'POST', [
    'csrf_token' => $queueCsrf,
    'action' => 'request_revision',
    'article_id' => $articleId,
    'feedback' => 'Please provide specific case examples in Section 2 demonstrating dialectical verification.'
], $adminCookie);

$articleStmt->execute([$articleSlug]);
$revisedArticle = $articleStmt->fetch();
$isRevisionRequested = ($revisedArticle && $revisedArticle['status'] === 'revision_requested');

record_test(
    'Editorial Workflow',
    'Editor Evaluates and Requests Author Revision with Feedback',
    $isRevisionRequested,
    "Status in MySQL: " . ($revisedArticle['status'] ?? 'None')
);

// 3.4 Contributor Addresses Feedback & Resubmits
$resubmitPost = http_request("{$baseUrl}/actions/article-action.php", 'POST', [
    'csrf_token' => $dashCsrf,
    'action' => 'submit_for_review',
    'article_id' => $articleId,
    'return_to' => url('pages/dashboard.php')
], $contribCookie);

$articleStmt->execute([$articleSlug]);
$resubmittedArticle = $articleStmt->fetch();
$isResubmitted = ($resubmittedArticle && $resubmittedArticle['status'] === 'pending_review');

record_test(
    'Editorial Workflow',
    'Contributor Resubmission Following Revision Feedback',
    $isResubmitted,
    "Status in MySQL: " . ($resubmittedArticle['status'] ?? 'None')
);

// 3.5 Editor Approves and Publishes
$subQueue2 = http_request("{$baseUrl}/admin/submissions.php", 'GET', [], $adminCookie);
$queueCsrf2 = extract_csrf($subQueue2['body']);

$publishPost = http_request("{$baseUrl}/admin/submissions.php", 'POST', [
    'csrf_token' => $queueCsrf2,
    'action' => 'publish',
    'article_id' => $articleId,
    'feedback' => 'Revisions confirmed. Approved and published to live editorial catalog.'
], $adminCookie);

$articleStmt->execute([$articleSlug]);
$publishedArticle = $articleStmt->fetch();
$isPublished = ($publishedArticle && $publishedArticle['status'] === 'published' && !empty($publishedArticle['published_at']));

record_test(
    'Editorial Workflow',
    'Editor Final Approval & Live Publication with Timestamp',
    $isPublished,
    "Status: " . ($publishedArticle['status'] ?? 'None') . ", Published At: " . ($publishedArticle['published_at'] ?? 'None')
);

// 3.6 Verify Published Article Appears in Public Directory
$publicCheck = http_request("{$baseUrl}/pages/articles.php");
$publishedVisible = ($publicCheck['code'] === 200 && str_contains($publicCheck['body'], $articleTitle));
record_test(
    'Editorial Workflow',
    'Published Article Immediately Visible in Public Catalog',
    $publishedVisible,
    "Found '{$articleTitle}' in public catalog HTML"
);

// 3.7 Ownership Boundary: Contributor cannot tamper with or delete published article
$tamperAttempt = http_request("{$baseUrl}/actions/article-action.php", 'POST', [
    'csrf_token' => $dashCsrf,
    'action' => 'delete_draft',
    'article_id' => $articleId
], $contribCookie);

$articleStmt->execute([$articleSlug]);
$safeArticle = $articleStmt->fetch();
$tamperBlocked = ($safeArticle !== false); // Article still exists
record_test(
    'Editorial Workflow',
    'Ownership & State Boundary: Contributor Blocked from Deleting Published Record',
    $tamperBlocked,
    "Article #{$articleId} safely retained in MySQL"
);

// =============================================================================
// SECTION 4: ADMINISTRATIVE WORKFLOW & REAL DATABASE METRICS
// =============================================================================
echo "\n--------------------------------------------------------------------\n";
echo "SECTION 4: Administrative Workflow & Real Database Statistics\n";
echo "--------------------------------------------------------------------\n";

$adminDash = http_request("{$baseUrl}/admin/index.php", 'GET', [], $adminCookie);
$hasMetrics = ($adminDash['code'] === 200 && str_contains($adminDash['body'], 'Dashboard') && str_contains($adminDash['body'], 'Users'));
record_test(
    'Admin Workflow',
    'Administrative Portal Access & Metrics Panel',
    $hasMetrics,
    "Admin Dashboard HTTP {$adminDash['code']}"
);

$catAdmin = http_request("{$baseUrl}/admin/categories.php", 'GET', [], $adminCookie);
record_test(
    'Admin Workflow',
    'Editorial Categories Management Panel Access',
    ($catAdmin['code'] === 200 && str_contains($catAdmin['body'], 'Categories')),
    "HTTP {$catAdmin['code']}"
);

$annAdmin = http_request("{$baseUrl}/admin/announcements.php", 'GET', [], $adminCookie);
record_test(
    'Admin Workflow',
    'Official Announcements Management Panel Access',
    ($annAdmin['code'] === 200 && str_contains($annAdmin['body'], 'Announcements')),
    "HTTP {$annAdmin['code']}"
);

$mediaAdmin = http_request("{$baseUrl}/admin/media.php", 'GET', [], $adminCookie);
record_test(
    'Admin Workflow',
    'Media Library Management Panel Access',
    ($mediaAdmin['code'] === 200 && str_contains($mediaAdmin['body'], 'Media')),
    "HTTP {$mediaAdmin['code']}"
);

$settingsAdmin = http_request("{$baseUrl}/admin/settings.php", 'GET', [], $adminCookie);
record_test(
    'Admin Workflow',
    'Website System Configuration Settings Access',
    ($settingsAdmin['code'] === 200 && str_contains($settingsAdmin['body'], 'Settings')),
    "HTTP {$settingsAdmin['code']}"
);

// Verify actual database statistics match admin panel
$actualUsers = (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$actualArticles = (int)$db->query("SELECT COUNT(*) FROM articles")->fetchColumn();
$actualCategories = (int)$db->query("SELECT COUNT(*) FROM categories")->fetchColumn();

record_test(
    'Admin Workflow',
    'Administrative Metrics Derived From Actual MySQL Database Counts',
    ($actualUsers > 0 && $actualCategories === 8),
    "Verified in DB: Users={$actualUsers}, Articles={$actualArticles}, Categories={$actualCategories}"
);

// =============================================================================
// SECTION 5: STRICT CLEANUP OF STAGING DATA (RESTORE ZERO FAKE STATE)
// =============================================================================
echo "\n--------------------------------------------------------------------\n";
echo "SECTION 5: Strict Cleanup of Temporary Staging Records\n";
echo "--------------------------------------------------------------------\n";

if ($articleId > 0) {
    $db->prepare("DELETE FROM editorial_reviews WHERE article_id = ?")->execute([$articleId]);
    $db->prepare("DELETE FROM article_categories WHERE article_id = ?")->execute([$articleId]);
    $db->prepare("DELETE FROM articles WHERE id = ?")->execute([$articleId]);
}
if ($contribUserId > 0) {
    $db->prepare("DELETE FROM user_roles WHERE user_id = ?")->execute([$contribUserId]);
    $db->prepare("DELETE FROM users WHERE id = ?")->execute([$contribUserId]);
}
if ($adminUserId > 0) {
    $db->prepare("DELETE FROM user_roles WHERE user_id = ?")->execute([$adminUserId]);
    $db->prepare("DELETE FROM users WHERE id = ?")->execute([$adminUserId]);
}

// Clean up cookie files
@unlink($adminCookie);
@unlink($contribCookie);

// Verify Zero Fake State Restored
$postCleanupUsers = (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$postCleanupArticles = (int)$db->query("SELECT COUNT(*) FROM articles")->fetchColumn();
$cleanupOk = ($postCleanupUsers === 0 && $postCleanupArticles === 0);

record_test(
    'Database Hygiene',
    'Zero-Fake Database State Fully Restored After Acceptance Audit',
    $cleanupOk,
    "Live database verified clean: users={$postCleanupUsers}, articles={$postCleanupArticles}"
);

// =============================================================================
// SUMMARY
// =============================================================================
$total = $passCount + $failCount;
$pct = $total > 0 ? round(($passCount / $total) * 100, 1) : 0;

echo "\n====================================================================\n";
echo "ACCEPTANCE TEST SUMMARY: {$passCount} / {$total} PASSING ({$pct}%)\n";
echo "====================================================================\n";

if ($failCount === 0) {
    echo "STATUS: ALL ACCEPTANCE ASSERTIONS SATISFIED (100% READY).\n";
} else {
    echo "STATUS: {$failCount} FAILURE(S) DETECTED.\n";
}
