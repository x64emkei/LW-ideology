<?php
declare(strict_types=1);
if (!defined('APP_NAME')) {
    require_once dirname(__DIR__) . '/config/app.php';
}

$currentUser = current_user();
$pageTitle = isset($pageTitle) ? $pageTitle . ' — ' . get_setting('site_name', 'LagunaWISE — Ideology Division') : get_setting('site_name', 'LagunaWISE — Ideology Division');
$pageDesc = isset($pageDescription) ? $pageDescription : get_setting('division_statement', 'An intellectual and educational division of LagunaWISE dedicated to fostering critical thinking, media literacy, and digital citizenship.');
$httpHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
$reqUri = $_SERVER['REQUEST_URI'] ?? '/';
$isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
$canonicalUrl = isset($canonicalUrl) ? $canonicalUrl : ($isHttps ? "https" : "http") . "://{$httpHost}{$reqUri}";
$ogImage = isset($ogImage) ? $ogImage : asset('assets/images/lagunawise-map-logo.png');
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="<?= e($pageDesc) ?>">
    <link rel="canonical" href="<?= e($canonicalUrl) ?>">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= e($canonicalUrl) ?>">
    <meta property="og:title" content="<?= e($pageTitle) ?>">
    <meta property="og:description" content="<?= e($pageDesc) ?>">
    <meta property="og:image" content="<?= e($ogImage) ?>">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($pageTitle) ?>">
    <meta name="twitter:description" content="<?= e($pageDesc) ?>">
    <meta name="twitter:image" content="<?= e($ogImage) ?>">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= asset('assets/images/lagunawise-map-logo.png') ?>">

    <!-- Google Fonts: Plus Jakarta Sans, Inter, Source Serif 4 -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Source+Serif+4:ital,opsz,wght@0,8..60,400;0,8..60,600;1,8..60,400&display=swap" rel="stylesheet">

    <!-- Font Awesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Tailwind CSS (Utility framework for prototype) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        navy: {
                            950: '#030D22',
                            900: '#061638',
                            850: '#081B44',
                            800: '#0B2252',
                            700: '#153372',
                        },
                        lwblue: {
                            DEFAULT: '#1557A6',
                            hover: '#114482',
                            light: '#2799E8',
                            ice: '#DCEEFF',
                        },
                        silver: '#C5CEDB',
                        surface: '#F4F7FB',
                        editorial: {
                            text: '#26344A',
                            muted: '#718096'
                        }
                    },
                    fontFamily: {
                        sans: ['"Inter"', 'sans-serif'],
                        display: ['"Plus Jakarta Sans"', 'sans-serif'],
                        serif: ['"Source Serif 4"', 'serif'],
                    }
                }
            }
        }
    </script>

    <style>
        :root {
            --color-deep-navy: #030D22;
            --color-primary-navy: #061638;
            --color-secondary-navy: #0B2252;
            --color-lw-blue: #1557A6;
            --color-tech-blue: #2799E8;
            --color-ice-blue: #DCEEFF;
            --color-silver: #C5CEDB;
            --color-bg-light: #F4F7FB;
            --color-body-text: #26344A;
            --color-muted-text: #718096;
        }

        html {
            overflow-x: hidden;
            width: 100%;
            max-width: 100vw;
        }

        body {
            overflow-x: hidden;
            width: 100%;
            max-width: 100vw;
            position: relative;
            font-family: 'Inter', sans-serif;
            color: var(--color-body-text);
            background-color: var(--color-bg-light);
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        /* Universal mobile anti-overflow protections */
        img, svg, video, iframe {
            max-width: 100%;
            height: auto;
        }

        p, h1, h2, h3, h4, h5, h6, a, span {
            overflow-wrap: break-word;
            word-break: break-word;
        }

        h1, h2, h3, h4, .font-heading {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .editorial-prose {
            font-family: 'Source Serif 4', Georgia, Cambria, 'Times New Roman', serif;
            font-size: 1.125rem;
            line-height: 1.85;
            color: #1e293b;
        }

        .editorial-prose p {
            margin-bottom: 1.6rem;
            line-height: 1.85;
            color: #1e293b;
        }

        .editorial-prose p:last-child {
            margin-bottom: 0;
        }

        .editorial-prose h1, .editorial-prose h2, .editorial-prose h3, .editorial-prose h4 {
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #061638;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .editorial-prose h1 {
            font-size: 2rem;
            line-height: 1.25;
            margin-top: 2.75rem;
            margin-bottom: 1.25rem;
        }

        .editorial-prose h2 {
            font-size: 1.65rem;
            line-height: 1.35;
            margin-top: 2.75rem;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .editorial-prose h3 {
            font-size: 1.35rem;
            line-height: 1.4;
            margin-top: 2.25rem;
            margin-bottom: 0.75rem;
        }

        .editorial-prose h4 {
            font-size: 1.15rem;
            margin-top: 1.75rem;
            margin-bottom: 0.5rem;
        }

        .editorial-prose blockquote {
            margin: 2rem 0;
            padding: 1.25rem 1.75rem;
            background-color: #f8fafc;
            border-left: 4px solid #1557A6;
            border-radius: 0 0.75rem 0.75rem 0;
            font-style: italic;
            font-size: 1.15rem;
            line-height: 1.75;
            color: #0f172a;
        }

        .editorial-prose blockquote p {
            margin-bottom: 0;
        }

        .editorial-prose ul {
            list-style-type: disc;
            margin: 1.5rem 0;
            padding-left: 2rem;
        }

        .editorial-prose ol {
            list-style-type: decimal;
            margin: 1.5rem 0;
            padding-left: 2rem;
        }

        .editorial-prose li {
            margin-bottom: 0.6rem;
            line-height: 1.75;
        }

        .editorial-prose a {
            color: #1557A6;
            text-decoration: underline;
            text-underline-offset: 3px;
            font-weight: 600;
            transition: color 0.15s ease;
        }

        .editorial-prose a:hover {
            color: #061638;
        }

        .editorial-prose strong, .editorial-prose b {
            font-weight: 700;
            color: #0f172a;
        }

        .editorial-prose em, .editorial-prose i {
            font-style: italic;
        }

        .editorial-prose hr {
            border: 0;
            border-top: 1px solid #cbd5e1;
            margin: 2.5rem 0;
        }

        .editorial-prose code {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            background-color: #f1f5f9;
            color: #be185d;
            padding: 0.2rem 0.4rem;
            border-radius: 0.25rem;
            font-size: 0.9em;
        }

        .editorial-prose pre {
            background-color: #0f172a;
            color: #f8fafc;
            padding: 1.25rem;
            border-radius: 0.75rem;
            overflow-x: auto;
            margin: 1.75rem 0;
            font-size: 0.875rem;
            line-height: 1.6;
        }

        .editorial-prose pre code {
            background: transparent;
            color: inherit;
            padding: 0;
        }

        .editorial-prose figure {
            margin: 2rem 0;
        }

        .editorial-prose figcaption {
            font-family: 'Inter', sans-serif;
            font-size: 0.75rem;
            color: #64748b;
            text-align: center;
            margin-top: 0.5rem;
        }

        /* Mobile Reader Mode & Typography Scaling */
        .editorial-prose.text-size-lg p {
            font-size: 1.25rem !important;
            line-height: 2 !important;
        }
        .editorial-prose.text-size-lg h2 {
            font-size: 1.85rem !important;
        }
        .editorial-prose.text-size-lg h3 {
            font-size: 1.5rem !important;
        }

        /* No-scrollbar utility for mobile horizontal swipe carousels */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        /* Floating Mobile Reader Bar Animations */
        #mobileReaderBar {
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.3s ease;
        }
        #mobileReaderBar.hide-bar {
            transform: translateY(130%);
            opacity: 0;
            pointer-events: none;
        }

        /* Focus accessibility indicator */
        a:focus-visible, button:focus-visible, input:focus-visible, select:focus-visible, textarea:focus-visible {
            outline: 2px solid #2799E8;
            outline-offset: 2px;
        }

        /* Shimmer & Skeleton Loaders */
        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }

        .skeleton {
            background: linear-gradient(90deg, #E2E8F0 25%, #F1F5F9 50%, #E2E8F0 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }

        .skeleton-dark {
            background: linear-gradient(90deg, #0B2252 25%, #153372 50%, #0B2252 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }

        /* Smooth page fade-in animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(4px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-fade-in {
            animation: fadeIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        /* Top Page Loading Bar */
        #top-loading-bar {
            position: fixed;
            top: 0;
            left: 0;
            height: 3px;
            background: linear-gradient(90deg, #2799E8, #1557A6);
            z-index: 99999;
            width: 100%;
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s ease-out, opacity 0.3s ease-out;
        }

        /* Subtle scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #F1F5F9;
        }
        ::-webkit-scrollbar-thumb {
            background: #CBD5E1;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94A3B8;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col antialiased selection:bg-lwblue-ice selection:text-navy-900">
    <div id="top-loading-bar"></div>

    <!-- =========================================================================
         UPPER TIER: LAGUNAWISE EDITORIAL IDENTITY & OFFICIAL WORDMARK (NAVY)
         ========================================================================= -->
    <header class="bg-navy-900 text-white border-b border-navy-800 relative z-30" style="background-color: #061638;">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="flex justify-between items-center py-3 sm:py-3.5">
                
                <!-- Left: Official LagunaWISE Emblem & Wordmark -->
                <a href="<?= url() ?>" class="flex items-center gap-3.5 group focus:outline-hidden focus-visible:ring-2 focus-visible:ring-lwblue-light" aria-label="LagunaWISE Ideology Division Home">
                    <img src="<?= asset('assets/images/lagunawise-map-logo.png') ?>" 
                         alt="LagunaWISE Emblem" 
                         width="44" 
                         height="44" 
                         style="width: 44px; height: 44px; min-width: 44px; min-height: 44px; max-width: 44px; max-height: 44px;" 
                         class="w-11 h-11 shrink-0 object-contain rounded-full shadow-xs group-hover:scale-105 transition-transform duration-200 border border-white/20 bg-white/5 p-0.5">
                    
                    <div class="flex flex-col">
                        <div class="flex items-center gap-2 flex-wrap">
                            <!-- Official LagunaWISE Cropped Wordmark Graphic -->
                            <img src="<?= asset('assets/images/lagunawise-wordmark-text.png') ?>" 
                                 alt="LAGUNAWISE" 
                                 height="24" 
                                 style="height: 24px; width: auto; max-height: 26px;" 
                                 class="h-5 sm:h-6 w-auto object-contain brightness-110">
                            
                            <span class="text-[9px] font-black uppercase tracking-widest text-lwblue-ice bg-lwblue/60 border border-lwblue-light/30 px-2 py-0.5 rounded-xs">
                                IDEOLOGY DIVISION
                            </span>
                        </div>
                        <span class="text-[10px] text-slate-300 font-medium tracking-wide hidden sm:block mt-0.5">
                            Critical Thinking • Media Literacy • Responsible Digital Citizenship
                        </span>
                    </div>
                </a>

                <!-- Right: Editorial Metadata & Connect Channels -->
                <div class="flex items-center gap-4 sm:gap-6">
                    <!-- Date & Regional Secretariat Info -->
                    <div class="hidden lg:flex flex-col items-end text-right text-xs space-y-0.5">
                        <span class="text-white font-semibold flex items-center gap-1.5">
                            <i class="fa-regular fa-calendar-days text-lwblue-light text-[11px]"></i>
                            <?= date('l, F j, Y') ?>
                        </span>
                        <span class="text-slate-300 text-[11px] flex items-center gap-1">
                            <i class="fa-solid fa-location-dot text-lwblue-light text-[10px]"></i>
                            Barangay Barandal, Calamba City, Laguna
                        </span>
                    </div>

                    <!-- Connect Channels (Instagram, X, Facebook, LinkedIn) -->
                    <div class="hidden sm:flex items-center gap-1.5 text-xs border-l border-navy-700/80 pl-4">
                        <a href="https://instagram.com/lagunawise" target="_blank" rel="noopener noreferrer" class="w-7 h-7 rounded-full bg-white/10 hover:bg-lwblue text-white flex items-center justify-center transition" aria-label="Instagram">
                            <i class="fa-brands fa-instagram text-xs"></i>
                        </a>
                        <a href="https://twitter.com/lagunawise" target="_blank" rel="noopener noreferrer" class="w-7 h-7 rounded-full bg-white/10 hover:bg-lwblue text-white flex items-center justify-center transition" aria-label="X (Twitter)">
                            <i class="fa-brands fa-x-twitter text-xs"></i>
                        </a>
                        <a href="https://facebook.com/lagunawise" target="_blank" rel="noopener noreferrer" class="w-7 h-7 rounded-full bg-white/10 hover:bg-lwblue text-white flex items-center justify-center transition" aria-label="Facebook">
                            <i class="fa-brands fa-facebook-f text-xs"></i>
                        </a>
                        <a href="https://linkedin.com/company/lagunawise" target="_blank" rel="noopener noreferrer" class="w-7 h-7 rounded-full bg-white/10 hover:bg-lwblue text-white flex items-center justify-center transition" aria-label="LinkedIn">
                            <i class="fa-brands fa-linkedin-in text-xs"></i>
                        </a>
                    </div>

                    <!-- Mobile Hamburger Button -->
                    <button type="button" 
                            id="mobileMenuToggle" 
                            aria-label="Toggle Navigation Menu"
                            aria-expanded="false" 
                            class="lg:hidden p-2 text-white hover:bg-white/10 rounded-md transition focus:outline-hidden">
                        <i class="fa-solid fa-bars text-lg" id="mobileMenuIcon"></i>
                    </button>
                </div>

            </div>
        </div>
    </header>

    <!-- =========================================================================
         LOWER TIER: PRIMARY EDITORIAL NAVIGATION & AUTHENTICATION BAR (WHITE)
         ========================================================================= -->
    <nav class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200 shadow-xs" aria-label="Primary Editorial Desks Navigation">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="flex justify-between items-center h-12 sm:h-13">
                
                <!-- Left: Core Editorial Navigation Links -->
                <div class="hidden lg:flex items-center space-x-1 sm:space-x-2 text-xs font-bold tracking-wider uppercase text-slate-700">
                    <a href="<?= url() ?>" 
                       class="px-3 py-2 rounded-md transition <?= ($_SERVER['SCRIPT_NAME'] == APP_BASE_URL . '/index.php' || $_SERVER['SCRIPT_NAME'] == APP_BASE_URL . '/') ? 'text-lwblue bg-lwblue-ice/70 font-black' : 'hover:text-navy-900 hover:bg-slate-100' ?>">
                        Home
                    </a>
                    <a href="<?= url('pages/about.php') ?>" 
                       class="px-3 py-2 rounded-md transition <?= str_contains($_SERVER['SCRIPT_NAME'], 'about.php') ? 'text-lwblue bg-lwblue-ice/70 font-black' : 'hover:text-navy-900 hover:bg-slate-100' ?>">
                        About
                    </a>
                    <a href="<?= url('pages/articles.php') ?>" 
                       class="px-3 py-2 rounded-md transition <?= str_contains($_SERVER['SCRIPT_NAME'], 'articles.php') || str_contains($_SERVER['SCRIPT_NAME'], 'article.php') ? 'text-lwblue bg-lwblue-ice/70 font-black' : 'hover:text-navy-900 hover:bg-slate-100' ?>">
                        Articles &amp; Insights
                    </a>
                    <a href="<?= url('pages/topics.php') ?>" 
                       class="px-3 py-2 rounded-md transition <?= str_contains($_SERVER['SCRIPT_NAME'], 'topics.php') || str_contains($_SERVER['SCRIPT_NAME'], 'topic.php') ? 'text-lwblue bg-lwblue-ice/70 font-black' : 'hover:text-navy-900 hover:bg-slate-100' ?>">
                        Featured Topics
                    </a>
                    <a href="<?= url('pages/news.php') ?>" 
                       class="px-3 py-2 rounded-md transition <?= str_contains($_SERVER['SCRIPT_NAME'], 'news.php') ? 'text-lwblue bg-lwblue-ice/70 font-black' : 'hover:text-navy-900 hover:bg-slate-100' ?>">
                        News &amp; Updates
                    </a>
                    <a href="<?= url('pages/contact.php') ?>" 
                       class="px-3 py-2 rounded-md transition <?= str_contains($_SERVER['SCRIPT_NAME'], 'contact.php') ? 'text-lwblue bg-lwblue-ice/70 font-black' : 'hover:text-navy-900 hover:bg-slate-100' ?>">
                        Contact
                    </a>
                </div>

                <!-- Mobile Compact Indicator (When in Mobile View) -->
                <div class="lg:hidden flex items-center text-xs font-bold text-navy-900 uppercase tracking-wider">
                    <span class="text-lwblue mr-1">Desk:</span>
                    <span>
                        <?php 
                        if (str_contains($_SERVER['SCRIPT_NAME'], 'about.php')) echo 'About';
                        elseif (str_contains($_SERVER['SCRIPT_NAME'], 'articles.php') || str_contains($_SERVER['SCRIPT_NAME'], 'article.php')) echo 'Articles';
                        elseif (str_contains($_SERVER['SCRIPT_NAME'], 'topics.php') || str_contains($_SERVER['SCRIPT_NAME'], 'topic.php')) echo 'Topics';
                        elseif (str_contains($_SERVER['SCRIPT_NAME'], 'news.php')) echo 'News';
                        elseif (str_contains($_SERVER['SCRIPT_NAME'], 'contact.php')) echo 'Contact';
                        else echo 'Frontpage';
                        ?>
                    </span>
                </div>

                <!-- Right: Search & Account Actions (Login & Join the Community) -->
                <div class="flex items-center space-x-2 sm:space-x-3">
                    <!-- Search Trigger Button -->
                    <button type="button" 
                            id="searchModalBtn"
                            aria-label="Search articles and insights"
                            class="p-2 text-slate-600 hover:text-navy-900 hover:bg-slate-100 rounded-md transition">
                        <i class="fa-solid fa-magnifying-glass text-sm"></i>
                    </button>

                    <?php if ($currentUser): ?>
                        <!-- Authenticated User Menu -->
                        <div class="relative" id="userMenuWrapper">
                            <button type="button" 
                                    id="userMenuButton"
                                    aria-expanded="false" 
                                    aria-haspopup="true"
                                    class="flex items-center gap-2 pl-2 pr-3 py-1.5 rounded-md border border-slate-200 hover:border-slate-300 bg-white transition focus:outline-hidden focus:ring-2 focus:ring-lwblue">
                                <img src="<?= asset($currentUser['avatar'] ?? 'assets/images/default-avatar.svg') ?>" 
                                     alt="<?= e($currentUser['name']) ?>" 
                                     class="w-6 h-6 rounded-full object-cover border border-slate-200">
                                <span class="text-xs font-semibold text-slate-800 hidden sm:inline max-w-[120px] truncate">
                                    <?= e($currentUser['name']) ?>
                                </span>
                                <i class="fa-solid fa-chevron-down text-[10px] text-slate-400"></i>
                            </button>

                            <!-- Dropdown Menu -->
                            <div id="userMenuDropdown" 
                                 class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl border border-slate-200 py-2 z-50 text-xs transform transition ease-out duration-100">
                                <div class="px-4 py-2 border-b border-slate-100">
                                    <p class="font-semibold text-slate-900"><?= e($currentUser['name']) ?></p>
                                    <p class="text-[11px] text-slate-500 truncate"><?= e($currentUser['email']) ?></p>
                                    <span class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-bold bg-lwblue-ice text-lwblue uppercase">
                                        <?= e(str_replace('_', ' ', $currentUser['primary_role'])) ?>
                                    </span>
                                </div>

                                <a href="<?= url('pages/dashboard.php') ?>" class="flex items-center gap-2.5 px-4 py-2 hover:bg-slate-50 text-slate-700 font-medium">
                                    <i class="fa-solid fa-gauge-high text-slate-400 w-4"></i> My Dashboard
                                </a>

                                <?php if (user_has_any_role(['administrator', 'super_admin', 'admin', 'editor'])): ?>
                                    <a href="<?= url('admin/index.php') ?>" class="flex items-center gap-2.5 px-4 py-2 hover:bg-slate-50 text-lwblue font-semibold">
                                        <i class="fa-solid fa-shield-halved text-lwblue w-4"></i> CMS Administration
                                    </a>
                                <?php endif; ?>

                                <?php if (user_has_any_role(['administrator', 'super_admin', 'admin', 'editor', 'contributor', 'writer'])): ?>
                                    <a href="<?= url('admin/article-create.php') ?>" class="flex items-center gap-2.5 px-4 py-2 hover:bg-slate-50 text-slate-700 font-medium">
                                        <i class="fa-solid fa-pen-nib text-slate-400 w-4"></i> Create Article
                                    </a>
                                <?php endif; ?>

                                <a href="<?= url('admin/profile.php') ?>" class="flex items-center gap-2.5 px-4 py-2 hover:bg-slate-50 text-slate-700 font-medium">
                                    <i class="fa-solid fa-user-gear text-slate-400 w-4"></i> Profile &amp; Settings
                                </a>

                                <div class="border-t border-slate-100 my-1"></div>

                                <a href="<?= url('auth/logout.php') ?>" class="flex items-center gap-2.5 px-4 py-2 hover:bg-red-50 text-red-600 font-medium">
                                    <i class="fa-solid fa-arrow-right-from-bracket text-red-400 w-4"></i> Log Out
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Guest Actions: Log In and Join the Community in the Lower Half -->
                        <div class="flex items-center space-x-2">
                            <a href="<?= url('auth/login.php') ?>" class="text-xs font-bold px-3 py-1.5 text-slate-700 hover:text-lwblue transition uppercase tracking-wider">
                                Log In
                            </a>
                            <a href="<?= url('auth/register.php') ?>" class="text-xs font-bold px-3.5 py-1.5 bg-lwblue hover:bg-lwblue-hover text-white rounded-md transition shadow-xs uppercase tracking-wider">
                                Join the Community
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>

        <!-- Mobile Drawer Navigation -->
        <div id="mobileMenu" class="hidden lg:hidden border-t border-slate-100 bg-white px-4 pt-3 pb-6 space-y-3 shadow-lg">
            <nav class="flex flex-col space-y-1.5 text-sm font-semibold text-slate-700">
                <a href="<?= url() ?>" class="px-3 py-2 rounded-md hover:bg-slate-50 hover:text-lwblue <?= ($_SERVER['SCRIPT_NAME'] == APP_BASE_URL . '/index.php' || $_SERVER['SCRIPT_NAME'] == APP_BASE_URL . '/') ? 'text-lwblue bg-slate-50 font-bold' : '' ?>">Home</a>
                <a href="<?= url('pages/about.php') ?>" class="px-3 py-2 rounded-md hover:bg-slate-50 hover:text-lwblue <?= str_contains($_SERVER['SCRIPT_NAME'], 'about.php') ? 'text-lwblue bg-slate-50 font-bold' : '' ?>">About the Division</a>
                <a href="<?= url('pages/articles.php') ?>" class="px-3 py-2 rounded-md hover:bg-slate-50 hover:text-lwblue <?= str_contains($_SERVER['SCRIPT_NAME'], 'articles.php') ? 'text-lwblue bg-slate-50 font-bold' : '' ?>">Articles &amp; Insights</a>
                <a href="<?= url('pages/topics.php') ?>" class="px-3 py-2 rounded-md hover:bg-slate-50 hover:text-lwblue <?= str_contains($_SERVER['SCRIPT_NAME'], 'topics.php') ? 'text-lwblue bg-slate-50 font-bold' : '' ?>">Featured Topics</a>
                <a href="<?= url('pages/news.php') ?>" class="px-3 py-2 rounded-md hover:bg-slate-50 hover:text-lwblue <?= str_contains($_SERVER['SCRIPT_NAME'], 'news.php') ? 'text-lwblue bg-slate-50 font-bold' : '' ?>">News &amp; Updates</a>
                <a href="<?= url('pages/contact.php') ?>" class="px-3 py-2 rounded-md hover:bg-slate-50 hover:text-lwblue <?= str_contains($_SERVER['SCRIPT_NAME'], 'contact.php') ? 'text-lwblue bg-slate-50 font-bold' : '' ?>">Contact</a>
            </nav>

            <?php if (!$currentUser): ?>
                <div class="pt-3 border-t border-slate-100 flex flex-col gap-2">
                    <a href="<?= url('auth/login.php') ?>" class="w-full text-center py-2.5 text-xs font-bold text-slate-700 bg-slate-100 rounded-md hover:bg-slate-200 uppercase tracking-wider">
                        Log In
                    </a>
                    <a href="<?= url('auth/register.php') ?>" class="w-full text-center py-2.5 text-xs font-bold text-white bg-lwblue rounded-md hover:bg-lwblue-hover uppercase tracking-wider shadow-xs">
                        Join the Community
                    </a>
                </div>
            <?php else: ?>
                <div class="pt-3 border-t border-slate-100 flex flex-col gap-2">
                    <div class="px-3 py-2 bg-surface rounded-md flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-navy-900"><?= e($currentUser['name']) ?></p>
                            <p class="text-[10px] text-slate-400 capitalize"><?= e(str_replace('_', ' ', $currentUser['primary_role'])) ?></p>
                        </div>
                        <a href="<?= url('auth/logout.php') ?>" class="text-[11px] font-bold text-rose-600 hover:underline">Log Out</a>
                    </div>
                    <a href="<?= url('pages/dashboard.php') ?>" class="w-full text-center py-2.5 text-xs font-bold text-slate-700 bg-slate-100 rounded-md hover:bg-slate-200 flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-gauge-high text-xs"></i>
                        <span>My Dashboard</span>
                    </a>
                    <?php if (user_has_any_role(['administrator', 'super_admin', 'admin', 'editor', 'contributor', 'writer'])): ?>
                        <a href="<?= url('admin/article-create.php') ?>" class="w-full text-center py-2.5 text-xs font-bold text-white bg-lwblue rounded-md hover:bg-lwblue-hover flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-pen-nib text-xs"></i>
                            <span>Create Article</span>
                        </a>
                    <?php endif; ?>
                    <?php if (user_has_any_role(['administrator', 'super_admin', 'admin', 'editor'])): ?>
                        <a href="<?= url('admin/index.php') ?>" class="w-full text-center py-2.5 text-xs font-bold text-slate-700 border border-slate-200 rounded-md hover:bg-slate-50 flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-shield-halved text-xs text-lwblue"></i>
                            <span>CMS Administration</span>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Global Search Modal Dialog -->
    <div id="searchModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-navy-900/60 backdrop-blur-sm flex items-start justify-center pt-20 px-4" role="dialog" aria-modal="true">
        <div class="bg-white w-full max-w-2xl rounded-2xl shadow-2xl border border-slate-100 overflow-hidden transform transition-all">
            <div class="p-4 border-b border-slate-100 flex items-center gap-3">
                <i class="fa-solid fa-magnifying-glass text-slate-400 text-lg"></i>
                <form action="<?= url('pages/search.php') ?>" method="GET" class="flex-1">
                    <input type="text" 
                           name="q" 
                           id="globalSearchInput"
                           placeholder="Search articles, concepts, topics, or announcements..." 
                           class="w-full text-sm font-medium text-slate-800 placeholder-slate-400 focus:outline-none bg-transparent"
                           required>
                </form>
                <button type="button" id="closeSearchModal" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <div class="p-5 text-xs text-slate-500 bg-slate-50/70">
                <p class="font-semibold text-slate-700 mb-2">Suggested Searches:</p>
                <div class="flex flex-wrap gap-2">
                    <a href="<?= url('pages/search.php?q=misinformation') ?>" class="px-2.5 py-1 bg-white border border-slate-200 rounded-md hover:border-lwblue hover:text-lwblue transition">Misinformation</a>
                    <a href="<?= url('pages/search.php?q=media+literacy') ?>" class="px-2.5 py-1 bg-white border border-slate-200 rounded-md hover:border-lwblue hover:text-lwblue transition">Media Literacy</a>
                    <a href="<?= url('pages/search.php?q=digital+citizenship') ?>" class="px-2.5 py-1 bg-white border border-slate-200 rounded-md hover:border-lwblue hover:text-lwblue transition">Digital Citizenship</a>
                    <a href="<?= url('pages/search.php?q=artificial+intelligence') ?>" class="px-2.5 py-1 bg-white border border-slate-200 rounded-md hover:border-lwblue hover:text-lwblue transition">Artificial Intelligence</a>
                    <a href="<?= url('pages/search.php?q=critical+thinking') ?>" class="px-2.5 py-1 bg-white border border-slate-200 rounded-md hover:border-lwblue hover:text-lwblue transition">Critical Thinking</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Flash Notifications Container -->
    <?php $flashMessages = get_flash(); ?>
    <?php if (!empty($flashMessages)): ?>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 pt-4 w-full" id="flashContainer">
            <?php foreach ($flashMessages as $msg): ?>
                <?php 
                    $type = $msg['type'];
                    $color = match($type) {
                        'success' => 'bg-emerald-50 border-emerald-200 text-emerald-800',
                        'error'   => 'bg-rose-50 border-rose-200 text-rose-800',
                        'warning' => 'bg-amber-50 border-amber-200 text-amber-800',
                        default   => 'bg-blue-50 border-blue-200 text-blue-800'
                    };
                    $icon = match($type) {
                        'success' => 'fa-circle-check text-emerald-500',
                        'error'   => 'fa-circle-exclamation text-rose-500',
                        'warning' => 'fa-triangle-exclamation text-amber-500',
                        default   => 'fa-circle-info text-blue-500'
                    };
                ?>
                <div class="flex items-center justify-between p-4 mb-3 rounded-xl border text-sm font-medium shadow-sm <?= $color ?>">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid <?= $icon ?> text-base"></i>
                        <span><?= e($msg['message']) ?></span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-current opacity-60 hover:opacity-100 p-1" aria-label="Dismiss message">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <main class="flex-grow">
