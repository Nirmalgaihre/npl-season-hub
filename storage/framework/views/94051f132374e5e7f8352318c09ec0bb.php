<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo $__env->yieldContent('title', 'Admin Dashboard'); ?> | NPL Season Hub</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <style>
    :root {
        --npl-sidebar: #111827;
        --npl-sidebar-active: #272d3a;
        --npl-sidebar-border: #273142;
        --npl-sidebar-text: #d1d5db;
        --npl-muted: #9ca3af;
        --npl-red: #dc2626;
        --npl-bg: #f6f8fb;
        --npl-text: #1f2937;
    }

    [x-cloak],
    [hidden] {
        display: none !important;
    }

    html {
        scroll-behavior: smooth;
    }

    body {
        margin: 0;
        font-family: Inter, ui-sans-serif, system-ui, -apple-system,
            BlinkMacSystemFont, "Segoe UI", sans-serif;
        color: var(--npl-text);
        background: var(--npl-bg);
    }

    button,
    a {
        -webkit-tap-highlight-color: transparent;
    }

    .npl-scrollbar {
        scrollbar-width: thin;
        scrollbar-color: #374151 transparent;
    }

    .npl-scrollbar::-webkit-scrollbar {
        width: 5px;
    }

    .npl-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }

    .npl-scrollbar::-webkit-scrollbar-thumb {
        background: #374151;
        border-radius: 999px;
    }

    /* Sidebar navigation */

    .admin-nav-label {
        padding: 23px 12px 9px;
        color: #9ca3af;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .admin-nav-link {
        display: flex;
        align-items: center;
        gap: 12px;
        width: 100%;
        min-height: 42px;
        padding: 10px 12px;
        border: 1px solid transparent;
        border-radius: 8px;
        background: transparent;
        color: var(--npl-sidebar-text);
        font-size: 13px;
        font-weight: 500;
        line-height: 1.4;
        text-align: left;
        text-decoration: none;
        transition: background-color .16s ease, color .16s ease;
    }

    .admin-nav-link:hover {
        background: rgba(255, 255, 255, .055);
        color: #fff;
    }

    .admin-nav-link.active {
        background: var(--npl-sidebar-active);
        color: #fff;
        font-weight: 600;
    }

    .admin-nav-icon {
        display: inline-flex;
        width: 20px;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        color: #9ca3af;
        font-size: 15px;
    }

    .admin-nav-link:hover .admin-nav-icon,
    .admin-nav-link.active .admin-nav-icon,
    .admin-nav-link.group-active .admin-nav-icon {
        color: inherit;
    }

    .admin-nav-link.group-active {
        background: rgba(255, 255, 255, .045);
        color: #fff;
    }

    .admin-dropdown-button {
        cursor: pointer;
    }

    .admin-dropdown-arrow {
        display: inline-flex;
        width: 16px;
        height: 16px;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        color: #9ca3af;
        font-size: 11px;
        transition: transform .18s ease;
    }

    .admin-dropdown-arrow.is-open {
        transform: rotate(180deg);
    }

    .admin-submenu {
        margin: 4px 0 7px 21px;
        padding: 2px 0 2px 11px;
        border-left: 1px solid #374151;
    }

    .admin-submenu .admin-nav-link {
        min-height: 38px;
        padding: 9px 10px;
        gap: 10px;
        border-radius: 7px;
        font-size: 12px;
        font-weight: 400;
    }

    .admin-submenu .admin-nav-icon {
        width: 17px;
        font-size: 13px;
    }

    .admin-submenu .admin-nav-link.active {
        background: #272d3a;
        color: #fff;
        font-weight: 600;
    }

    .admin-nav-link:focus-visible,
    .admin-dropdown-button:focus-visible {
        outline: 2px solid #f87171;
        outline-offset: 2px;
    }

    .admin-sidebar-divider {
        height: 1px;
        margin: 18px 8px 8px;
        background: var(--npl-sidebar-border);
    }

    .admin-brand-mark {
        display: flex;
        width: 42px;
        height: 42px;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: var(--npl-red);
        color: #fff;
        font-size: 13px;
        font-weight: 800;
        letter-spacing: .02em;
    }

    .admin-profile-avatar {
        display: flex;
        width: 40px;
        height: 40px;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: #374151;
        color: #fff;
        font-size: 14px;
        font-weight: 700;
    }

    .admin-top-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        border-radius: 8px;
        padding: 10px 14px;
        background: #111827;
        color: #fff;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        transition: background-color .16s ease;
    }

    .admin-top-button:hover {
        background: #272d3a;
    }

    @media (max-width: 1023px) {
        #adminSidebar {
            box-shadow: 12px 0 35px rgba(0, 0, 0, .15);
        }
    }

    @media (prefers-reduced-motion: reduce) {

        *,
        *::before,
        *::after {
            scroll-behavior: auto !important;
            transition-duration: .01ms !important;
            animation-duration: .01ms !important;
        }
    }
    </style>

    <?php echo $__env->yieldPushContent('styles'); ?>
</head>

<body class="min-h-screen antialiased">

    <?php
    $currentRoute = request()->route()?->getName() ?? '';

    $isRouteActive = function (array $patterns) use ($currentRoute) {
    foreach ($patterns as $pattern) {
    if (\Illuminate\Support\Str::is($pattern, $currentRoute)) {
    return true;
    }
    }

    return false;
    };

    $user = auth()->user();
    $userName = $user?->name ?? $user?->username ?? 'Administrator';
    $userInitial = strtoupper(mb_substr(trim($userName) ?: 'A', 0, 1));

    $tournamentActive = $isRouteActive([
    'admin.seasons.*',
    'admin.teams.*',
    'admin.players.*',
    'admin.fixtures.*',
    'admin.pointtable.*',
    ]);

    $scorecardsActive = $isRouteActive([
    'admin.scorecards.*',
    ]);

    $contentActive = $isRouteActive([
    'admin.articles.*',
    'admin.categories.*',
    'admin.tags.*',
    ]);
    ?>

    <div class="min-h-screen">

        
        <button id="sidebarBackdrop" type="button" aria-label="Close navigation"
            class="fixed inset-0 z-40 hidden cursor-default bg-gray-950/60 backdrop-blur-sm lg:hidden"
            onclick="toggleSidebar(false)">
        </button>

        
        <aside id="adminSidebar"
            class="fixed inset-y-0 left-0 z-50 flex w-[260px] -translate-x-full flex-col text-white transition-transform duration-200 ease-out lg:translate-x-0"
            style="background:var(--npl-sidebar);">

            
            <div class="flex h-[76px] shrink-0 items-center justify-between border-b px-5"
                style="border-color:var(--npl-sidebar-border);">

                <a href="<?php echo e(route('admin.dashboard')); ?>" class="flex min-w-0 items-center gap-3 no-underline">

                    <span class="admin-brand-mark">NPL</span>

                    <span class="min-w-0">
                        <span class="block truncate text-sm font-bold tracking-tight text-white">
                            NPL Season Hub
                        </span>

                        <span class="mt-1 block text-[10px] font-medium uppercase tracking-[.13em] text-gray-400">
                            Administration
                        </span>
                    </span>
                </a>

                <button type="button" onclick="toggleSidebar(false)"
                    class="rounded-lg p-2 text-gray-400 transition hover:bg-white/10 hover:text-white lg:hidden"
                    aria-label="Close sidebar">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            
            <nav class="npl-scrollbar flex-1 overflow-y-auto px-3 pb-5">

                
                <p class="admin-nav-label">Workspace</p>

                <a href="<?php echo e(route('admin.dashboard')); ?>"
                    class="admin-nav-link <?php echo e($isRouteActive(['admin.dashboard']) ? 'active' : ''); ?>">
                    <span class="admin-nav-icon">
                        <i class="fa-solid fa-gauge-high"></i>
                    </span>
                    <span class="flex-1">Dashboard</span>
                </a>

                
                <button type="button"
                    class="admin-nav-link admin-dropdown-button mt-1 <?php echo e($tournamentActive ? 'group-active' : ''); ?>"
                    data-dropdown-toggle="tournamentDropdown" aria-controls="tournamentDropdown"
                    aria-expanded="<?php echo e($tournamentActive ? 'true' : 'false'); ?>">

                    <span class="admin-nav-icon">
                        <i class="fa-regular fa-calendar-days"></i>
                    </span>

                    <span class="flex-1">Tournament</span>

                    <span class="admin-dropdown-arrow <?php echo e($tournamentActive ? 'is-open' : ''); ?>" data-dropdown-arrow>
                        <i class="fa-solid fa-chevron-down"></i>
                    </span>
                </button>

                <div id="tournamentDropdown" class="admin-submenu" <?php if(!$tournamentActive): ?> hidden <?php endif; ?>>

                    <a href="<?php echo e(route('admin.seasons.index')); ?>"
                        class="admin-nav-link <?php echo e($isRouteActive(['admin.seasons.*']) ? 'active' : ''); ?>">
                        <span class="admin-nav-icon">
                            <i class="fa-regular fa-calendar"></i>
                        </span>
                        <span class="flex-1">Manage Seasons</span>
                    </a>

                    <a href="<?php echo e(route('admin.teams.index')); ?>"
                        class="admin-nav-link <?php echo e($isRouteActive(['admin.teams.*']) ? 'active' : ''); ?>">
                        <span class="admin-nav-icon">
                            <i class="fa-solid fa-people-group"></i>
                        </span>
                        <span class="flex-1">Manage Teams</span>
                    </a>

                    <?php if(\Illuminate\Support\Facades\Route::has('admin.players.index')): ?>
                    <a href="<?php echo e(route('admin.players.index')); ?>"
                        class="admin-nav-link <?php echo e($isRouteActive(['admin.players.*']) ? 'active' : ''); ?>">
                        <span class="admin-nav-icon">
                            <i class="fa-solid fa-user-group"></i>
                        </span>
                        <span class="flex-1">Manage Players</span>
                    </a>
                    <?php endif; ?>

                    <a href="<?php echo e(route('admin.fixtures.index')); ?>"
                        class="admin-nav-link <?php echo e($isRouteActive(['admin.fixtures.*']) ? 'active' : ''); ?>">
                        <span class="admin-nav-icon">
                            <i class="fa-solid fa-trophy"></i>
                        </span>
                        <span class="flex-1">Fixtures &amp; Results</span>
                    </a>

                    <a href="<?php echo e(route('admin.pointtable.index')); ?>"
                        class="admin-nav-link <?php echo e($isRouteActive(['admin.pointtable.*']) ? 'active' : ''); ?>">
                        <span class="admin-nav-icon">
                            <i class="fa-solid fa-ranking-star"></i>
                        </span>
                        <span class="flex-1">Points Table</span>
                    </a>
                </div>

                
                <?php if(\Illuminate\Support\Facades\Route::has('admin.scorecards.index')): ?>
                <a href="<?php echo e(route('admin.scorecards.index')); ?>"
                    class="admin-nav-link mt-1 <?php echo e($scorecardsActive ? 'active' : ''); ?>">
                    <span class="admin-nav-icon">
                        <i class="fa-solid fa-chart-column"></i>
                    </span>
                    <span class="flex-1">Scorecards Management</span>
                </a>
                <?php endif; ?>

                
                <button type="button"
                    class="admin-nav-link admin-dropdown-button mt-1 <?php echo e($contentActive ? 'group-active' : ''); ?>"
                    data-dropdown-toggle="contentManagementDropdown" aria-controls="contentManagementDropdown"
                    aria-expanded="<?php echo e($contentActive ? 'true' : 'false'); ?>">

                    <span class="admin-nav-icon">
                        <i class="fa-regular fa-file-lines"></i>
                    </span>

                    <span class="flex-1">Content Management</span>

                    <span class="admin-dropdown-arrow <?php echo e($contentActive ? 'is-open' : ''); ?>" data-dropdown-arrow>
                        <i class="fa-solid fa-chevron-down"></i>
                    </span>
                </button>

                <div id="contentManagementDropdown" class="admin-submenu" <?php if(!$contentActive): ?> hidden <?php endif; ?>>

                    <a href="<?php echo e(route('admin.articles.index')); ?>"
                        class="admin-nav-link <?php echo e($isRouteActive(['admin.articles.*']) ? 'active' : ''); ?>">
                        <span class="admin-nav-icon">
                            <i class="fa-regular fa-newspaper"></i>
                        </span>
                        <span class="flex-1">Manage News</span>
                    </a>

                    <a href="<?php echo e(route('admin.categories.index')); ?>"
                        class="admin-nav-link <?php echo e($isRouteActive(['admin.categories.*']) ? 'active' : ''); ?>">
                        <span class="admin-nav-icon">
                            <i class="fa-solid fa-layer-group"></i>
                        </span>
                        <span class="flex-1">Categories</span>
                    </a>

                    <a href="<?php echo e(route('admin.tags.index')); ?>"
                        class="admin-nav-link <?php echo e($isRouteActive(['admin.tags.*']) ? 'active' : ''); ?>">
                        <span class="admin-nav-icon">
                            <i class="fa-solid fa-tags"></i>
                        </span>
                        <span class="flex-1">Tags</span>
                    </a>
                </div>

                
                <div class="admin-sidebar-divider"></div>

                <p class="admin-nav-label" style="padding-top:12px;">System</p>

                <a href="<?php echo e(route('home')); ?>" target="_blank" rel="noopener noreferrer" class="admin-nav-link">
                    <span class="admin-nav-icon">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </span>
                    <span class="flex-1">View Public Website</span>
                </a>
            </nav>

            
            <div class="shrink-0 border-t p-4" style="border-color:var(--npl-sidebar-border);">

                <div class="flex items-center gap-3 px-1 py-1">
                    <div class="admin-profile-avatar">
                        <?php echo e($userInitial); ?>

                    </div>

                    <div class="min-w-0 flex-1">
                        <p class="truncate text-[13px] font-semibold text-white">
                            <?php echo e($userName); ?>

                        </p>

                        <p class="mt-1 text-[11px] text-gray-400">
                            Administrator
                        </p>
                    </div>
                </div>

                <?php if(\Illuminate\Support\Facades\Route::has('logout')): ?>
                <form action="<?php echo e(route('logout')); ?>" method="POST" class="mt-3">
                    <?php echo csrf_field(); ?>

                    <button type="submit"
                        class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left text-xs font-medium text-gray-300 transition hover:bg-red-500/10 hover:text-red-300">
                        <span class="w-5 text-center">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i>
                        </span>
                        Sign out
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </aside>

        
        <div class="min-h-screen lg:pl-[260px]">

            
            <header class="sticky top-0 z-30 border-b border-gray-200 bg-white/95 backdrop-blur">
                <div class="flex h-[76px] items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">

                    <div class="flex min-w-0 items-center gap-3">

                        <button type="button" onclick="toggleSidebar()"
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-600 transition hover:bg-gray-50 lg:hidden"
                            aria-label="Toggle navigation" aria-controls="adminSidebar">
                            <i class="fa-solid fa-bars"></i>
                        </button>

                        <div class="min-w-0">
                            <p
                                class="hidden text-[10px] font-semibold uppercase tracking-[.13em] text-gray-400 sm:block">
                                NPL Season Hub / Administration
                            </p>

                            <h1 class="truncate text-base font-bold tracking-tight text-gray-900 sm:mt-1 sm:text-lg">
                                <?php echo $__env->yieldContent('page_heading', 'Dashboard'); ?>
                            </h1>
                        </div>
                    </div>

                    <div class="flex shrink-0 items-center gap-3">
                        <span class="hidden items-center gap-2 text-xs font-medium text-gray-500 sm:flex">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            Admin workspace
                        </span>

                        <a href="<?php echo e(route('home')); ?>" target="_blank" rel="noopener noreferrer"
                            class="admin-top-button">
                            <span>View site</span>
                            <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                        </a>
                    </div>
                </div>
            </header>

            
            <div class="px-4 pt-5 sm:px-6 lg:px-8">

                <?php if(session('success')): ?>
                <div role="status"
                    class="mb-4 flex items-start gap-3 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">

                    <span class="mt-0.5 font-bold">
                        <i class="fa-solid fa-circle-check"></i>
                    </span>

                    <div class="flex-1">
                        <?php echo e(session('success')); ?>

                    </div>

                    <button type="button" onclick="this.parentElement.remove()" class="px-1 font-bold"
                        aria-label="Dismiss notification">
                        &times;
                    </button>
                </div>
                <?php endif; ?>

                <?php if(session('error')): ?>
                <div role="alert"
                    class="mb-4 flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">

                    <span class="mt-0.5 font-bold">
                        <i class="fa-solid fa-circle-exclamation"></i>
                    </span>

                    <div class="flex-1">
                        <?php echo e(session('error')); ?>

                    </div>

                    <button type="button" onclick="this.parentElement.remove()" class="px-1 font-bold"
                        aria-label="Dismiss notification">
                        &times;
                    </button>
                </div>
                <?php endif; ?>

                <?php if($errors->any()): ?>
                <div role="alert" class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">

                    <p class="mb-2 font-semibold">
                        Please check the following:
                    </p>

                    <ul class="list-inside list-disc space-y-1">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
                <?php endif; ?>
            </div>

            
            <main class="min-h-[calc(100vh-160px)] px-4 pb-10 pt-2 sm:px-6 lg:px-8">
                <?php echo $__env->yieldContent('content'); ?>
            </main>

            
            <footer class="border-t border-gray-200 bg-white px-4 py-5 sm:px-6 lg:px-8">
                <div class="flex flex-col gap-2 text-xs text-gray-400 sm:flex-row sm:items-center sm:justify-between">
                    <p>
                        &copy; <?php echo e(date('Y')); ?> NPL Season Hub. All rights reserved.
                    </p>

                    <p>Administration portal</p>
                </div>
            </footer>
        </div>
    </div>

    
    <script>
    function toggleSidebar(forceOpen) {
        const sidebar = document.getElementById('adminSidebar');
        const backdrop = document.getElementById('sidebarBackdrop');

        if (!sidebar || !backdrop) {
            return;
        }

        const isClosed = sidebar.classList.contains('-translate-x-full');

        const shouldOpen = typeof forceOpen === 'boolean' ?
            forceOpen :
            isClosed;

        sidebar.classList.toggle('-translate-x-full', !shouldOpen);
        backdrop.classList.toggle('hidden', !shouldOpen);
        document.body.classList.toggle('overflow-hidden', shouldOpen);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const dropdownButtons = Array.from(
            document.querySelectorAll('[data-dropdown-toggle]')
        );

        dropdownButtons.forEach(function(button) {
            const targetId = button.getAttribute('data-dropdown-toggle');
            const submenu = document.getElementById(targetId);
            const arrow = button.querySelector('[data-dropdown-arrow]');

            if (!submenu) {
                return;
            }

            button.addEventListener('click', function() {
                const isExpanded =
                    button.getAttribute('aria-expanded') === 'true';

                if (!isExpanded) {
                    dropdownButtons.forEach(function(otherButton) {
                        if (otherButton === button) {
                            return;
                        }

                        const otherId = otherButton.getAttribute(
                            'data-dropdown-toggle'
                        );

                        const otherSubmenu = document.getElementById(otherId);
                        const otherArrow = otherButton.querySelector(
                            '[data-dropdown-arrow]'
                        );

                        if (otherSubmenu) {
                            otherSubmenu.hidden = true;
                        }

                        otherButton.setAttribute('aria-expanded', 'false');
                        otherButton.classList.remove('group-active');

                        if (otherArrow) {
                            otherArrow.classList.remove('is-open');
                        }
                    });
                }

                const shouldOpen = !isExpanded;

                submenu.hidden = !shouldOpen;

                button.setAttribute(
                    'aria-expanded',
                    shouldOpen ? 'true' : 'false'
                );

                button.classList.toggle('group-active', shouldOpen);

                if (arrow) {
                    arrow.classList.toggle('is-open', shouldOpen);
                }
            });
        });
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            toggleSidebar(false);
        }
    });

    window.addEventListener('resize', function() {
        if (window.innerWidth >= 1024) {
            toggleSidebar(false);
        }
    });
    </script>

    <?php echo $__env->yieldPushContent('scripts'); ?>

</body>

</html><?php /**PATH /opt/lampp/htdocs/npl-season-hub/resources/views/layouts/admin.blade.php ENDPATH**/ ?>