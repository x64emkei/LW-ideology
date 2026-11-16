<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/app.php';

// Only staff (super_admin, admin, editor) can access administrative CMS
require_role(['administrator', 'super_admin', 'admin', 'editor']);

$currentUser = current_user();
$adminPageTitle = $adminPageTitle ?? 'Administration CMS';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($adminPageTitle) ?> — LagunaWISE CMS</title>
    
    <link rel="icon" type="image/png" href="<?= asset('assets/images/lagunawise-map-logo.png') ?>">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&family=Source+Serif+4:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        navy: {
                            900: '#061638',
                            800: '#0B2252',
                            700: '#153372',
                        },
                        lwblue: {
                            DEFAULT: '#1557A6',
                            hover: '#114482',
                            light: '#2799E8',
                            ice: '#DCEEFF',
                        },
                        surface: '#F4F7FB'
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
        .editorial-prose {
            font-family: 'Source Serif 4', Georgia, Cambria, 'Times New Roman', serif;
            font-size: 1.05rem;
            line-height: 1.8;
            color: #1e293b;
        }
        .editorial-prose p { margin-bottom: 1.4rem; line-height: 1.8; color: #1e293b; }
        .editorial-prose p:last-child { margin-bottom: 0; }
        .editorial-prose h1, .editorial-prose h2, .editorial-prose h3, .editorial-prose h4 {
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #061638;
            font-weight: 800;
        }
        .editorial-prose h2 { font-size: 1.5rem; margin-top: 2rem; margin-bottom: 0.75rem; padding-bottom: 0.35rem; border-bottom: 1px solid #e2e8f0; }
        .editorial-prose h3 { font-size: 1.25rem; margin-top: 1.75rem; margin-bottom: 0.5rem; }
        .editorial-prose blockquote {
            margin: 1.5rem 0;
            padding: 1rem 1.5rem;
            background-color: #f8fafc;
            border-left: 4px solid #1557A6;
            border-radius: 0 0.5rem 0.5rem 0;
            font-style: italic;
            color: #0f172a;
        }
        .editorial-prose blockquote p { margin-bottom: 0; }
        .editorial-prose ul { list-style-type: disc; margin: 1.25rem 0; padding-left: 1.75rem; }
        .editorial-prose ol { list-style-type: decimal; margin: 1.25rem 0; padding-left: 1.75rem; }
        .editorial-prose li { margin-bottom: 0.4rem; line-height: 1.7; }
        .editorial-prose a { color: #1557A6; text-decoration: underline; font-weight: 600; }
        .editorial-prose strong, .editorial-prose b { font-weight: 700; color: #0f172a; }
        .editorial-prose em, .editorial-prose i { font-style: italic; }
        .editorial-prose hr { border: 0; border-top: 1px solid #cbd5e1; margin: 2rem 0; }
        .editorial-prose code { background-color: #f1f5f9; color: #be185d; padding: 0.15rem 0.35rem; border-radius: 0.25rem; font-size: 0.9em; font-family: monospace; }
        .editorial-prose pre { background-color: #0f172a; color: #f8fafc; padding: 1rem; border-radius: 0.5rem; overflow-x: auto; margin: 1.5rem 0; font-size: 0.85rem; }
    </style>
</head>
<body class="bg-surface text-slate-800 antialiased font-sans min-h-screen flex flex-col">

    <!-- Top Navigation Bar -->
    <header class="bg-navy-900 text-white sticky top-0 z-40 border-b border-navy-800 shadow-md">
        <div class="px-4 sm:px-6 py-3 flex items-center justify-between">
            
            <div class="flex items-center gap-4">
                <button type="button" id="sidebarToggle" class="p-2 text-slate-300 hover:text-white rounded-lg hover:bg-navy-800 lg:hidden">
                    <i class="fa-solid fa-bars"></i>
                </button>

                <a href="<?= url('admin/index.php') ?>" class="flex items-center gap-3">
                    <img src="<?= asset('assets/images/lagunawise-map-logo.png') ?>" alt="Logo" class="w-8 h-8 rounded-full object-contain">
                    <div class="flex flex-col">
                        <span class="text-xs font-bold tracking-wider text-lwblue-light uppercase">LagunaWISE</span>
                        <span class="text-sm font-extrabold text-white tracking-tight">CMS Administration</span>
                    </div>
                </a>
            </div>

            <div class="flex items-center gap-4 text-xs">
                <a href="<?= url() ?>" target="_blank" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-navy-800 hover:bg-navy-700 text-slate-300 hover:text-white transition">
                    <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                    <span>View Public Website</span>
                </a>

                <div class="flex items-center gap-3 border-l border-navy-800 pl-4">
                    <img src="<?= asset($currentUser['avatar'] ?? 'assets/images/default-avatar.svg') ?>" 
                         alt="<?= e($currentUser['name']) ?>" 
                         class="w-7 h-7 rounded-full object-cover border border-slate-600">
                    <div class="hidden md:block text-left">
                        <p class="font-bold text-white text-xs leading-none"><?= e($currentUser['name']) ?></p>
                        <p class="text-[10px] text-lwblue-light capitalize leading-tight mt-0.5"><?= e(str_replace('_', ' ', $currentUser['primary_role'])) ?></p>
                    </div>
                    <a href="<?= url('auth/logout.php') ?>" class="p-2 text-slate-400 hover:text-rose-400" title="Log Out">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    </a>
                </div>
            </div>

        </div>
    </header>

    <div class="flex-1 flex overflow-hidden">
        
        <!-- Collapsible Sidebar Navigation -->
        <aside id="adminSidebar" class="w-64 bg-white border-r border-slate-200 flex-shrink-0 hidden lg:flex flex-col justify-between py-6 px-4 z-30 transition-all duration-200">
            <div class="space-y-6">
                
                <div>
                    <span class="text-[10px] font-bold tracking-widest text-slate-400 uppercase px-3">Editorial Core</span>
                    <nav class="mt-2 space-y-1 text-xs font-semibold">
                        <a href="<?= url('admin/index.php') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= str_contains($_SERVER['SCRIPT_NAME'], 'index.php') || str_contains($_SERVER['SCRIPT_NAME'], 'dashboard.php') ? 'bg-navy-900 text-white' : 'text-slate-700 hover:bg-slate-100' ?>">
                            <i class="fa-solid fa-gauge-high w-4 text-center"></i>
                            <span>Dashboard Overview</span>
                        </a>

                        <a href="<?= url('admin/articles.php') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= str_contains($_SERVER['SCRIPT_NAME'], 'articles.php') ? 'bg-navy-900 text-white' : 'text-slate-700 hover:bg-slate-100' ?>">
                            <i class="fa-solid fa-newspaper w-4 text-center"></i>
                            <span>Articles Management</span>
                        </a>

                        <a href="<?= url('admin/submissions.php') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= str_contains($_SERVER['SCRIPT_NAME'], 'submissions.php') ? 'bg-navy-900 text-white' : 'text-slate-700 hover:bg-slate-100' ?>">
                            <i class="fa-solid fa-inbox w-4 text-center"></i>
                            <span>Editorial Submissions</span>
                        </a>

                        <a href="<?= url('admin/article-create.php') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= str_contains($_SERVER['SCRIPT_NAME'], 'article-create.php') ? 'bg-navy-900 text-white' : 'text-slate-700 hover:bg-slate-100' ?>">
                            <i class="fa-solid fa-pen-nib w-4 text-center"></i>
                            <span>Create New Article</span>
                        </a>

                        <a href="<?= url('admin/categories.php') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= str_contains($_SERVER['SCRIPT_NAME'], 'categories.php') ? 'bg-navy-900 text-white' : 'text-slate-700 hover:bg-slate-100' ?>">
                            <i class="fa-solid fa-folder-tree w-4 text-center"></i>
                            <span>Topics &amp; Categories</span>
                        </a>

                        <a href="<?= url('admin/comments.php') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= str_contains($_SERVER['SCRIPT_NAME'], 'comments.php') ? 'bg-navy-900 text-white' : 'text-slate-700 hover:bg-slate-100' ?>">
                            <i class="fa-regular fa-comments w-4 text-center"></i>
                            <span>Discussion Moderation</span>
                        </a>
                    </nav>
                </div>

                <div>
                    <span class="text-[10px] font-bold tracking-widest text-slate-400 uppercase px-3">Publishing &amp; Assets</span>
                    <nav class="mt-2 space-y-1 text-xs font-semibold">
                        <a href="<?= url('admin/announcements.php') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= str_contains($_SERVER['SCRIPT_NAME'], 'announcements.php') ? 'bg-navy-900 text-white' : 'text-slate-700 hover:bg-slate-100' ?>">
                            <i class="fa-solid fa-bullhorn w-4 text-center"></i>
                            <span>News &amp; Campaigns</span>
                        </a>

                        <a href="<?= url('admin/media.php') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= str_contains($_SERVER['SCRIPT_NAME'], 'media.php') ? 'bg-navy-900 text-white' : 'text-slate-700 hover:bg-slate-100' ?>">
                            <i class="fa-solid fa-photo-film w-4 text-center"></i>
                            <span>Media Library</span>
                        </a>
                    </nav>
                </div>

                <?php if (user_has_any_role(['super_admin', 'admin'])): ?>
                    <div>
                        <span class="text-[10px] font-bold tracking-widest text-slate-400 uppercase px-3">System Governance</span>
                        <nav class="mt-2 space-y-1 text-xs font-semibold">
                            <a href="<?= url('admin/users.php') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= str_contains($_SERVER['SCRIPT_NAME'], 'users.php') ? 'bg-navy-900 text-white' : 'text-slate-700 hover:bg-slate-100' ?>">
                                <i class="fa-solid fa-users-gear w-4 text-center"></i>
                                <span>Users &amp; Roles</span>
                            </a>

                            <a href="<?= url('admin/settings.php') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= str_contains($_SERVER['SCRIPT_NAME'], 'settings.php') ? 'bg-navy-900 text-white' : 'text-slate-700 hover:bg-slate-100' ?>">
                                <i class="fa-solid fa-sliders w-4 text-center"></i>
                                <span>Platform Settings</span>
                            </a>
                        </nav>
                    </div>
                <?php endif; ?>

            </div>

            <!-- Profile & Return link -->
            <div class="pt-4 border-t border-slate-200 space-y-2 text-xs font-semibold">
                <a href="<?= url('admin/profile.php') ?>" class="flex items-center gap-3 px-3 py-2 rounded-xl text-slate-700 hover:bg-slate-100">
                    <i class="fa-solid fa-user-shield w-4 text-center text-slate-500"></i>
                    <span>Profile &amp; Password</span>
                </a>
                <a href="<?= url('pages/dashboard.php') ?>" class="flex items-center gap-3 px-3 py-2 rounded-xl text-lwblue hover:bg-blue-50">
                    <i class="fa-solid fa-user w-4 text-center"></i>
                    <span>Reader View</span>
                </a>
            </div>
        </aside>

        <!-- Main Admin Content Area -->
        <main class="flex-1 overflow-y-auto p-4 sm:p-8 bg-surface">
            
            <!-- Admin Flash Messages -->
            <?php $flash = get_flash(); ?>
            <?php if (!empty($flash)): ?>
                <div class="mb-6 space-y-2 max-w-7xl mx-auto">
                    <?php foreach ($flash as $f): ?>
                        <div class="p-4 rounded-xl text-xs font-semibold flex items-center justify-between border shadow-xs
                            <?= match($f['type']) {
                                'success' => 'bg-emerald-50 border-emerald-200 text-emerald-800',
                                'error'   => 'bg-rose-50 border-rose-200 text-rose-800',
                                'warning' => 'bg-amber-50 border-amber-200 text-amber-800',
                                default   => 'bg-blue-50 border-blue-200 text-blue-800'
                            } ?>">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-circle-info"></i>
                                <span><?= e($f['message']) ?></span>
                            </div>
                            <button type="button" onclick="this.parentElement.remove()" class="text-current opacity-60 hover:opacity-100">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="max-w-7xl mx-auto space-y-8">
