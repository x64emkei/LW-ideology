<?php
declare(strict_types=1);

/**
 * Route Alias: create_article.php -> admin/article-create.php
 */
require_once __DIR__ . '/config/app.php';
header('Location: ' . url('admin/article-create.php'));
exit;
