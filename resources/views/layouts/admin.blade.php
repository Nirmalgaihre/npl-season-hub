<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin Dashboard') | NPL Season Hub</title>

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
            border-color: transparent;
            color: #fff;
            font-weight: 600;
        }

        .admin-nav-link.active .admin-nav-icon {
            color: #fff;
        }

        .admin-nav-link.group-active {
            background: rgba(255, 255, 255, .045);
            color: #fff;
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

    @stack('styles')
</head>

<body class="min-h-screen antialiased">

@php
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
    ]);

    $liveActive = $isRouteActive([
        'admin.live.*',
        'admin.scorecards.*',
    ]);

    $contentActive = $isRouteActive([
        'admin.articles.*',
        'admin.categories.*',
        'admin.tags.*',
    ]);
@endphp

<div class="min-h-screen">

    {{-- MOBILE BACKDROP --}}
    <button
        id="sidebarBackdrop"
        type="button"
        aria-label="Close navigation"
        class="fixed inset-0 z-40 hidden cursor-default bg-gray-950/60 backdrop-blur-sm lg:hidden"
        onclick="toggleSidebar(false)">
    </button>

    {{-- PROFESSIONAL SIDEBAR --}}
    <aside
        id="adminSidebar"
        class="fixed inset-y-0 left-0 z-50 flex w-[260px] -translate-x-full flex-col text-white transition-transform duration-200 ease-out lg:translate-x-0"
        style="background:var(--npl-sidebar);">

        {{-- BRAND --}}
        <div class="flex h-[76px] shrink-0 items-center justify-between border-b px-5"
             style="border-color:var(--npl-sidebar-border);">

            <a href="{{ route('admin.fixtures.index') }}"
               class="flex min-w-0 items-center gap-3 no-underline">

                <span class="admin-brand-mark">
                    NPL
                </span>

                <span class="min-w-0">
                    <span class="block truncate text-sm font-bold tracking-tight text-white">
                        NPL Season Hub
                    </span>

                    <span class="mt-1 block text-[10px] font-medium uppercase tracking-[.13em] text-gray-400">
                        Administration
                    </span>
                </span>
            </a>

            <button
                type="button"
                onclick="toggleSidebar(false)"
                class="rounded-lg p-2 text-gray-400 transition hover:bg-white/10 hover:text-white lg:hidden"
                aria-label="Close sidebar">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        {{-- NAVIGATION --}}
        <nav class="npl-scrollbar flex-1 overflow-y-auto px-3 pb-5">

            {{-- WORKSPACE --}}
            <p class="admin-nav-label">Workspace</p>

            <a href="{{ route('admin.dashboard') }}"
    class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition
    {{ request()->routeIs('admin.dashboard')
        ? 'bg-gray-700 text-white'
        : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
    <i class="fa-solid fa-gauge-high w-5 text-center"></i>
    <span>Dashboard</span>
</a>

            {{-- TOURNAMENT --}}
            <button
                type="button"
                class="admin-nav-link admin-dropdown-button mt-1 {{ $tournamentActive ? 'group-active' : '' }}"
                data-dropdown-toggle="tournamentDropdown"
                aria-controls="tournamentDropdown"
                aria-expanded="{{ $tournamentActive ? 'true' : 'false' }}">

                <span class="admin-nav-icon">
                    <i class="fa-regular fa-calendar-days"></i>
                </span>

                <span class="flex-1">Tournament</span>

                <span class="admin-dropdown-arrow {{ $tournamentActive ? 'is-open' : '' }}"
                      data-dropdown-arrow>
                    <i class="fa-solid fa-chevron-down"></i>
                </span>
            </button>

            <div
                id="tournamentDropdown"
                class="admin-submenu"
                @if(!$tournamentActive) hidden @endif>

                <a href="{{ route('admin.seasons.index') }}"
                   class="admin-nav-link {{ $isRouteActive(['admin.seasons.*']) ? 'active' : '' }}">
                    <span class="admin-nav-icon"><i class="fa-regular fa-calendar"></i></span>
                    <span class="flex-1">Manage Seasons</span>
                </a>

                <a href="{{ route('admin.teams.index') }}"
                   class="admin-nav-link {{ $isRouteActive(['admin.teams.*']) ? 'active' : '' }}">
                    <span class="admin-nav-icon"><i class="fa-solid fa-people-group"></i></span>
                    <span class="flex-1">Manage Teams</span>
                </a>

                @if(\Illuminate\Support\Facades\Route::has('admin.players.index'))
                    <a href="{{ route('admin.players.index') }}"
                       class="admin-nav-link {{ $isRouteActive(['admin.players.*']) ? 'active' : '' }}">
                        <span class="admin-nav-icon"><i class="fa-solid fa-user-group"></i></span>
                        <span class="flex-1">Manage Players</span>
                    </a>
                @endif

                <a href="{{ route('admin.fixtures.index') }}"
                   class="admin-nav-link {{ $isRouteActive(['admin.fixtures.*']) ? 'active' : '' }}">
                    <span class="admin-nav-icon"><i class="fa-solid fa-trophy"></i></span>
                    <span class="flex-1">Fixtures &amp; Results</span>
                </a>
            </div>

            {{-- LIVE SCORING --}}
            <button
                type="button"
                class="admin-nav-link admin-dropdown-button mt-1 {{ $liveActive ? 'group-active' : '' }}"
                data-dropdown-toggle="liveScoringDropdown"
                aria-controls="liveScoringDropdown"
                aria-expanded="{{ $liveActive ? 'true' : 'false' }}">

                <span class="admin-nav-icon">
                    <i class="fa-solid fa-tower-broadcast"></i>
                </span>

                <span class="flex-1">Live Scoring</span>

                <span class="admin-dropdown-arrow {{ $liveActive ? 'is-open' : '' }}"
                      data-dropdown-arrow>
                    <i class="fa-solid fa-chevron-down"></i>
                </span>
            </button>

            <div
                id="liveScoringDropdown"
                class="admin-submenu"
                @if(!$liveActive) hidden @endif>

                @if(\Illuminate\Support\Facades\Route::has('admin.live.index'))
                    <a href="{{ route('admin.live.index') }}"
                       class="admin-nav-link {{ $isRouteActive(['admin.live.index', 'admin.live.admin']) ? 'active' : '' }}">
                        <span class="admin-nav-icon" style="color:#f87171;">
                            <i class="fa-solid fa-circle-dot"></i>
                        </span>

                        <span class="flex-1">Live Match Manager</span>

                        <span class="rounded px-1.5 py-1 text-[9px] font-bold uppercase tracking-wide"
                              style="background:#3b2028;color:#fca5a5;">
                            Live
                        </span>
                    </a>

                    <a href="{{ route('admin.live.index') }}"
                       class="admin-nav-link {{ $isRouteActive(['admin.live.index']) ? 'active' : '' }}">
                        <span class="admin-nav-icon"><i class="fa-solid fa-pen-to-square"></i></span>
                        <span class="flex-1">Select Fixture to Score</span>
                    </a>
                @endif

                @if(\Illuminate\Support\Facades\Route::has('admin.scorecards.index'))
                    <a href="{{ route('admin.scorecards.index') }}"
                       class="admin-nav-link {{ $isRouteActive(['admin.scorecards.*']) ? 'active' : '' }}">
                        <span class="admin-nav-icon"><i class="fa-solid fa-chart-column"></i></span>
                        <span class="flex-1">Scorecards Management</span>
                    </a>
                @endif
            </div>

            {{-- CONTENT MANAGEMENT --}}
            <button
                type="button"
                class="admin-nav-link admin-dropdown-button mt-1 {{ $contentActive ? 'group-active' : '' }}"
                data-dropdown-toggle="contentManagementDropdown"
                aria-controls="contentManagementDropdown"
                aria-expanded="{{ $contentActive ? 'true' : 'false' }}">

                <span class="admin-nav-icon">
                    <i class="fa-regular fa-file-lines"></i>
                </span>

                <span class="flex-1">Content Management</span>

                <span class="admin-dropdown-arrow {{ $contentActive ? 'is-open' : '' }}"
                      data-dropdown-arrow>
                    <i class="fa-solid fa-chevron-down"></i>
                </span>
            </button>

            <div
                id="contentManagementDropdown"
                class="admin-submenu"
                @if(!$contentActive) hidden @endif>

                <a href="{{ route('admin.articles.index') }}"
                   class="admin-nav-link {{ $isRouteActive(['admin.articles.*']) ? 'active' : '' }}">
                    <span class="admin-nav-icon"><i class="fa-regular fa-newspaper"></i></span>
                    <span class="flex-1">Manage News</span>
                </a>

                <a href="{{ route('admin.categories.index') }}"
                   class="admin-nav-link {{ $isRouteActive(['admin.categories.*']) ? 'active' : '' }}">
                    <span class="admin-nav-icon"><i class="fa-solid fa-layer-group"></i></span>
                    <span class="flex-1">Categories</span>
                </a>

                <a href="{{ route('admin.tags.index') }}"
                   class="admin-nav-link {{ $isRouteActive(['admin.tags.*']) ? 'active' : '' }}">
                    <span class="admin-nav-icon"><i class="fa-solid fa-tags"></i></span>
                    <span class="flex-1">Tags</span>
                </a>
            </div>

            {{-- SYSTEM --}}
            <div class="admin-sidebar-divider"></div>

            <p class="admin-nav-label" style="padding-top:12px;">System</p>

            <a href="{{ route('home') }}"
               target="_blank"
               rel="noopener noreferrer"
               class="admin-nav-link">
                <span class="admin-nav-icon"><i class="fa-solid fa-arrow-up-right-from-square"></i></span>
                <span class="flex-1">View Public Website</span>
            </a>
        </nav>

        {{-- ADMIN PROFILE --}}
        <div class="shrink-0 border-t p-4"
             style="border-color:var(--npl-sidebar-border);">

            <div class="flex items-center gap-3 px-1 py-1">
                <div class="admin-profile-avatar">
                    {{ $userInitial }}
                </div>

                <div class="min-w-0 flex-1">
                    <p class="truncate text-[13px] font-semibold text-white">
                        {{ $userName }}
                    </p>

                    <p class="mt-1 text-[11px] text-gray-400">
                        Administrator
                    </p>
                </div>
            </div>

            @if(\Illuminate\Support\Facades\Route::has('logout'))
                <form action="{{ route('logout') }}" method="POST" class="mt-3">
                    @csrf

                    <button
                        type="submit"
                        class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left text-xs font-medium text-gray-300 transition hover:bg-red-500/10 hover:text-red-300">
                        <span class="w-5 text-center">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i>
                        </span>
                        Sign out
                    </button>
                </form>
            @endif
        </div>
    </aside>

    {{-- MAIN AREA --}}
    <div class="min-h-screen lg:pl-[260px]">

        {{-- TOP HEADER --}}
        <header class="sticky top-0 z-30 border-b border-gray-200 bg-white/95 backdrop-blur">
            <div class="flex h-[76px] items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">

                <div class="flex min-w-0 items-center gap-3">

                    <button
                        type="button"
                        onclick="toggleSidebar()"
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-600 transition hover:bg-gray-50 lg:hidden"
                        aria-label="Toggle navigation"
                        aria-controls="adminSidebar">
                        <i class="fa-solid fa-bars"></i>
                    </button>

                    <div class="min-w-0">
                        <p class="hidden text-[10px] font-semibold uppercase tracking-[.13em] text-gray-400 sm:block">
                            NPL Season Hub / Administration
                        </p>

                        <h1 class="truncate text-base font-bold tracking-tight text-gray-900 sm:mt-1 sm:text-lg">
                            @yield('page_heading', 'Dashboard')
                        </h1>
                    </div>
                </div>

                <div class="flex shrink-0 items-center gap-3">

                    <span class="hidden items-center gap-2 text-xs font-medium text-gray-500 sm:flex">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        Admin workspace
                    </span>

                    <a
                        href="{{ route('home') }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="admin-top-button">
                        <span>View site</span>
                        <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                    </a>
                </div>
            </div>
        </header>

        {{-- FLASH MESSAGES --}}
        <div class="px-4 pt-5 sm:px-6 lg:px-8">

            @if(session('success'))
                <div
                    role="status"
                    class="mb-4 flex items-start gap-3 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">

                    <span class="mt-0.5 font-bold">
                        <i class="fa-solid fa-circle-check"></i>
                    </span>

                    <div class="flex-1">
                        {{ session('success') }}
                    </div>

                    <button
                        type="button"
                        onclick="this.parentElement.remove()"
                        class="px-1 font-bold"
                        aria-label="Dismiss notification">
                        &times;
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div
                    role="alert"
                    class="mb-4 flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">

                    <span class="mt-0.5 font-bold">
                        <i class="fa-solid fa-circle-exclamation"></i>
                    </span>

                    <div class="flex-1">
                        {{ session('error') }}
                    </div>

                    <button
                        type="button"
                        onclick="this.parentElement.remove()"
                        class="px-1 font-bold"
                        aria-label="Dismiss notification">
                        &times;
                    </button>
                </div>
            @endif

            @if($errors->any())
                <div
                    role="alert"
                    class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">

                    <p class="mb-2 font-semibold">
                        Please check the following:
                    </p>

                    <ul class="list-inside list-disc space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        {{-- PAGE CONTENT --}}
        <main class="min-h-[calc(100vh-160px)] px-4 pb-10 pt-2 sm:px-6 lg:px-8">
            @yield('content')
        </main>

        {{-- FOOTER --}}
        <footer class="border-t border-gray-200 bg-white px-4 py-5 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-2 text-xs text-gray-400 sm:flex-row sm:items-center sm:justify-between">
                <p>
                    &copy; {{ date('Y') }} NPL Season Hub. All rights reserved.
                </p>

                <p>Administration portal</p>
            </div>
        </footer>
    </div>
</div>

{{-- SIDEBAR AND DROPDOWN SCRIPT --}}
<script>
    function toggleSidebar(forceOpen) {
        const sidebar = document.getElementById('adminSidebar');
        const backdrop = document.getElementById('sidebarBackdrop');

        if (!sidebar || !backdrop) {
            return;
        }

        const isClosed = sidebar.classList.contains('-translate-x-full');

        const shouldOpen = typeof forceOpen === 'boolean'
            ? forceOpen
            : isClosed;

        sidebar.classList.toggle('-translate-x-full', !shouldOpen);
        backdrop.classList.toggle('hidden', !shouldOpen);
        document.body.classList.toggle('overflow-hidden', shouldOpen);
    }

    document.addEventListener('DOMContentLoaded', function () {
        const dropdownButtons = Array.from(
            document.querySelectorAll('[data-dropdown-toggle]')
        );

        dropdownButtons.forEach(function (button) {
            const targetId = button.getAttribute('data-dropdown-toggle');
            const submenu = document.getElementById(targetId);
            const arrow = button.querySelector('[data-dropdown-arrow]');

            if (!submenu) {
                return;
            }

            button.addEventListener('click', function () {
                const isExpanded =
                    button.getAttribute('aria-expanded') === 'true';

                /*
                 * Keep one dropdown open at a time.
                 */
                if (!isExpanded) {
                    dropdownButtons.forEach(function (otherButton) {
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

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            toggleSidebar(false);
        }
    });

    window.addEventListener('resize', function () {
        if (window.innerWidth >= 1024) {
            toggleSidebar(false);
        }
    });
</script>

@stack('scripts')

</body>
</html>