<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin Dashboard') | NPL Season Hub</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        :root {
            --npl-navy: #202766;
            --npl-navy-dark: #171D50;
            --npl-orange: #F06445;
            --npl-bg: #F6F8FB;
        }

        [x-cloak] {
            display: none !important;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: Inter, ui-sans-serif, system-ui, -apple-system,
                BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .npl-scrollbar::-webkit-scrollbar {
            width: 5px;
        }

        .npl-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .npl-scrollbar::-webkit-scrollbar-thumb {
            background: #475569;
            border-radius: 999px;
        }

        .admin-nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            border: 1px solid transparent;
            border-radius: 10px;
            padding: 11px 12px;
            color: #CBD5E1;
            font-size: 13px;
            font-weight: 600;
            transition: background .18s ease, color .18s ease;
        }

        .admin-nav-link:hover {
            background: rgba(255,255,255,.07);
            color: #fff;
        }

        .admin-nav-link.active {
            background: rgba(240,100,69,.14);
            border-color: rgba(240,100,69,.2);
            color: #FF9D83;
        }

        .admin-nav-icon {
            display: inline-flex;
            width: 21px;
            flex-shrink: 0;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }

        .admin-nav-label {
            padding: 22px 12px 8px;
            color: #64748B;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .15em;
            text-transform: uppercase;
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

<body class="min-h-screen antialiased text-slate-800"
      style="background:var(--npl-bg);">

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
@endphp

<div class="min-h-screen">

    {{-- MOBILE BACKDROP --}}
    <button
        id="sidebarBackdrop"
        type="button"
        aria-label="Close navigation"
        class="fixed inset-0 z-40 hidden cursor-default bg-slate-950/60 backdrop-blur-sm lg:hidden"
        onclick="toggleSidebar(false)">
    </button>

    {{-- SIDEBAR --}}
    <aside
        id="adminSidebar"
        class="fixed inset-y-0 left-0 z-50 flex w-[272px] -translate-x-full flex-col text-white transition-transform duration-200 ease-out lg:translate-x-0"
        style="background:var(--npl-navy-dark);">

        {{-- BRAND --}}
        <div class="flex h-[76px] shrink-0 items-center justify-between border-b border-white/10 px-5">

            <a href="{{ route('admin.fixtures.index') }}"
               class="flex min-w-0 items-center gap-3">

                <span
                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-xs font-black tracking-wide text-white shadow-lg"
                    style="background:var(--npl-orange);">
                    NPL
                </span>

                <span class="min-w-0">
                    <span class="block truncate text-sm font-extrabold">
                        NPL Season Hub
                    </span>

                    <span class="mt-1 block text-[10px] font-medium uppercase tracking-[.16em] text-slate-400">
                        Management portal
                    </span>
                </span>
            </a>

            <button
                type="button"
                onclick="toggleSidebar(false)"
                class="rounded-lg p-2 text-slate-400 hover:bg-white/10 hover:text-white lg:hidden"
                aria-label="Close sidebar">
                &times;
            </button>
        </div>

        {{-- NAVIGATION --}}
        <nav class="npl-scrollbar flex-1 overflow-y-auto px-3 pb-5">

            {{-- OVERVIEW --}}
            <p class="admin-nav-label">Overview</p>

            <a href="{{ route('admin.fixtures.index') }}"
               class="admin-nav-link {{ $isRouteActive(['admin.dashboard']) ? 'active' : '' }}">
                <span class="admin-nav-icon">&#9638;</span>
                <span class="flex-1">Dashboard</span>
            </a>

            {{-- TOURNAMENT --}}
            <p class="admin-nav-label">Tournament</p>

            <a href="{{ route('admin.seasons.index') }}"
               class="admin-nav-link {{ $isRouteActive(['admin.seasons.*']) ? 'active' : '' }}">
                <span class="admin-nav-icon">&#9719;</span>
                <span class="flex-1">Manage Seasons</span>
            </a>

            <a href="{{ route('admin.teams.index') }}"
               class="admin-nav-link {{ $isRouteActive(['admin.teams.*']) ? 'active' : '' }}">
                <span class="admin-nav-icon">&#9823;</span>
                <span class="flex-1">Manage Teams</span>
            </a>

            @if(\Illuminate\Support\Facades\Route::has('admin.players.index'))
                <a href="{{ route('admin.players.index') }}"
                   class="admin-nav-link {{ $isRouteActive(['admin.players.*']) ? 'active' : '' }}">
                    <span class="admin-nav-icon">&#9818;</span>
                    <span class="flex-1">Manage Players</span>
                </a>
            @endif

            <a href="{{ route('admin.fixtures.index') }}"
               class="admin-nav-link {{ $isRouteActive(['admin.fixtures.*']) ? 'active' : '' }}">
                <span class="admin-nav-icon">&#127951;</span>
                <span class="flex-1">Fixtures &amp; Results</span>
            </a>

            {{-- LIVE SCORING --}}
            <p class="admin-nav-label">Live scoring</p>

            @if(\Illuminate\Support\Facades\Route::has('admin.live.index'))
                <a href="{{ route('admin.live.index') }}"
                   class="admin-nav-link {{ $isRouteActive(['admin.live.index', 'admin.live.admin']) ? 'active' : '' }}">
                    <span class="admin-nav-icon text-red-400">&#9679;</span>
                    <span class="flex-1">Live Match Manager</span>

                    <span
                        class="rounded-full px-2 py-1 text-[9px] font-extrabold uppercase"
                        style="background:rgba(240,100,69,.16);color:#FF9D83;">
                        LIVE
                    </span>
                </a>

                <a href="{{ route('admin.live.index') }}"
                   class="admin-nav-link {{ $isRouteActive(['admin.live.index']) ? 'active' : '' }}">
                    <span class="admin-nav-icon">&#9998;</span>
                    <span class="flex-1">Select Fixture to Score</span>
                </a>
            @endif

            {{-- SCORECARDS --}}
            @if(\Illuminate\Support\Facades\Route::has('admin.scorecards.index'))
                <a href="{{ route('admin.scorecards.index') }}"
                   class="admin-nav-link {{ $isRouteActive(['admin.scorecards.*']) ? 'active' : '' }}">
                    <span class="admin-nav-icon">&#128202;</span>
                    <span class="flex-1">Scorecards Management</span>
                </a>
            @endif

            {{-- CONTENT MANAGEMENT --}}
            <p class="admin-nav-label">Content management</p>

            <a href="{{ route('admin.articles.index') }}"
               class="admin-nav-link {{ $isRouteActive(['admin.articles.*']) ? 'active' : '' }}">
                <span class="admin-nav-icon">&#9776;</span>
                <span class="flex-1">Manage News</span>
            </a>

            <a href="{{ route('admin.categories.index') }}"
               class="admin-nav-link {{ $isRouteActive(['admin.categories.*']) ? 'active' : '' }}">
                <span class="admin-nav-icon">&#9636;</span>
                <span class="flex-1">Categories</span>
            </a>

            <a href="{{ route('admin.tags.index') }}"
               class="admin-nav-link {{ $isRouteActive(['admin.tags.*']) ? 'active' : '' }}">
                <span class="admin-nav-icon">#</span>
                <span class="flex-1">Tags</span>
            </a>

            <div class="my-5 border-t border-white/10"></div>

            <a href="{{ route('home') }}"
               target="_blank"
               rel="noopener noreferrer"
               class="admin-nav-link">
                <span class="admin-nav-icon">&#8599;</span>
                <span class="flex-1">View Public Website</span>
            </a>
        </nav>

        {{-- SIDEBAR USER --}}
        <div class="shrink-0 border-t border-white/10 p-4">

            <div class="mb-3 flex items-center gap-3 px-1">
                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-sm font-black"
                    style="background:rgba(255,255,255,.1);">
                    {{ $userInitial }}
                </div>

                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-bold text-white">
                        {{ $userName }}
                    </p>

                    <p class="mt-1 text-[10px] text-slate-400">
                        Administrator
                    </p>
                </div>
            </div>

            @if(\Illuminate\Support\Facades\Route::has('logout'))
                <form action="{{ route('logout') }}" method="POST">
                    @csrf

                    <button
                        type="submit"
                        class="flex w-full items-center gap-3 rounded-xl border border-white/10 px-3 py-2.5 text-left text-xs font-bold text-slate-300 transition hover:border-red-400/30 hover:bg-red-500/10 hover:text-red-300">
                        <span>&#10162;</span>
                        Sign out
                    </button>
                </form>
            @endif
        </div>
    </aside>

    {{-- MAIN AREA --}}
    <div class="min-h-screen lg:pl-[272px]">

        {{-- HEADER --}}
        <header class="sticky top-0 z-30 border-b border-slate-200/80 bg-white/95 backdrop-blur">
            <div class="flex h-[76px] items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">

                <div class="flex min-w-0 items-center gap-3">

                    <button
                        type="button"
                        onclick="toggleSidebar()"
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-lg transition hover:bg-slate-50 lg:hidden"
                        aria-label="Toggle navigation"
                        aria-controls="adminSidebar">
                        &#9776;
                    </button>

                    <div class="min-w-0">
                        <p class="hidden text-[10px] font-bold uppercase tracking-[.17em] text-slate-400 sm:block">
                            NPL Season Hub / Administration
                        </p>

                        <h1
                            class="truncate text-base font-extrabold sm:mt-1 sm:text-lg"
                            style="color:var(--npl-navy);">
                            @yield('page_heading', 'Dashboard')
                        </h1>
                    </div>
                </div>

                <div class="flex shrink-0 items-center gap-2 sm:gap-3">

                    <span class="hidden items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-2 text-[11px] font-bold text-slate-500 sm:flex">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        Admin workspace
                    </span>

                    <a
                        href="{{ route('home') }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-2 rounded-xl px-3 py-2.5 text-xs font-bold text-white transition hover:opacity-90 sm:px-4"
                        style="background:var(--npl-navy);">
                        View site <span aria-hidden="true">&#8599;</span>
                    </a>
                </div>
            </div>
        </header>

        {{-- FLASH MESSAGES --}}
        <div class="px-4 pt-5 sm:px-6 lg:px-8">

            @if(session('success'))
                <div
                    role="status"
                    class="mb-4 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">

                    <span class="font-black">&#10003;</span>

                    <div class="flex-1">
                        {{ session('success') }}
                    </div>

                    <button
                        type="button"
                        onclick="this.parentElement.remove()"
                        class="font-bold"
                        aria-label="Dismiss notification">
                        &times;
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div
                    role="alert"
                    class="mb-4 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">

                    <span class="font-black">!</span>

                    <div class="flex-1">
                        {{ session('error') }}
                    </div>

                    <button
                        type="button"
                        onclick="this.parentElement.remove()"
                        class="font-bold"
                        aria-label="Dismiss notification">
                        &times;
                    </button>
                </div>
            @endif

            @if($errors->any())
                <div
                    role="alert"
                    class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">

                    <p class="mb-2 font-bold">
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
        <footer class="border-t border-slate-200 bg-white px-4 py-5 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-2 text-xs text-slate-400 sm:flex-row sm:items-center sm:justify-between">
                <p>
                    &copy; {{ date('Y') }} NPL Season Hub. All rights reserved.
                </p>

                <p>Administration portal</p>
            </div>
        </footer>
    </div>
</div>

{{-- MOBILE SIDEBAR SCRIPT --}}
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