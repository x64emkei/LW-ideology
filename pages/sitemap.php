<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';

header('Content-Type: application/xml; charset=utf-8');

$db = Database::getConnection();

// Fetch all published articles
$articles = $db->query("SELECT slug, updated_at, published_at FROM articles WHERE status = 'published' ORDER BY published_at DESC")->fetchAll();

// Fetch all categories
$categories = $db->query("SELECT slug, created_at FROM categories ORDER BY id ASC")->fetchAll();

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <!-- Static Main Pages -->
    <url>
        <loc><?= e(url()) ?></loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc><?= e(url('pages/about.php')) ?></loc>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc><?= e(url('pages/articles.php')) ?></loc>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>
    <url>
        <loc><?= e(url('pages/topics.php')) ?></loc>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc><?= e(url('pages/news.php')) ?></loc>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
    <url>
        <loc><?= e(url('pages/contact.php')) ?></loc>
        <changefreq>monthly</changefreq>
        <priority>0.6</priority>
    </url>

    <!-- Published Articles -->
    <?php foreach ($articles as $art): ?>
        <url>
            <loc><?= e(url('pages/article.php?slug=' . urlencode($art['slug']))) ?></loc>
            <lastmod><?= date('Y-m-d', strtotime($art['updated_at'] ?? $art['published_at'])) ?></lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.8</priority>
        </url>
    <?php endforeach; ?>

    <!-- Topics -->
    <?php foreach ($categories as $cat): ?>
        <url>
            <loc><?= e(url('pages/topic.php?slug=' . urlencode($cat['slug']))) ?></loc>
            <changefreq>weekly</changefreq>
            <priority>0.7</priority>
        </url>
    <?php endforeach; ?>
</urlset>
