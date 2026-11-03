
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'NPL Cricket Hub') | Nepal Cricket</title>

    <meta name="description" content="@yield('meta_description', 'Follow Nepal Premier League fixtures, teams, points table, player statistics, match results and cricket news.')">
    <meta name="theme-color" content="#202766">
    <meta name="color-scheme" content="light">

    <meta property="og:site_name" content="NPL Cricket Hub">
    <meta property="og:type" content="website">
    <meta property="og:title" content="@yield('title', 'NPL Cricket Hub')">
    <meta property="og:description" content="@yield('meta_description', 'Your home for Nepal Premier League fixtures, teams, standings and cricket news.')">
    <meta property="og:url" content="@yield('canonical_url', url()->current())">

    <link rel="canonical" href="@yield('canonical_url', url()->current())">
    <link rel="icon" type="image/png" href="{{ asset('images/npl.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        npl: {
                            navy: '#202766',
                            dark: '#171D50',
                            darker: '#111638',
                            orange: '#F06445',
                            orangeDark: '#D94D31',
                            background: '#F6F8FB',
                            surface: '#FFFFFF',
                            muted: '#64748B',
                            border: '#E5E9F2',
                            green: '#16845B',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif']
                    },
                    boxShadow: {
                        soft: '0 4px 24px rgba(32,39,102,.06)',
                        card: '0 2px 8px rgba(15,23,42,.035)',
                        elevated: '0 12px 35px rgba(23,29,80,.12)'
                    },
                    maxWidth: {
                        site: '1280px'
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0', transform: 'translateY(-5px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' }
                        }
                    },
                    animation: {
                        'fade-in': 'fadeIn .2s ease-out'
                    }
                }
            }
        };
    </script>

    <style>
        :root {
            --npl-navy: #202766;
            --npl-dark: #171D50;
            --npl-orange: #F06445;
            --npl-background: #F6F8FB;
            --npl-border: #E5E9F2;
            --npl-text: #1E293B;
            --npl-muted: #64748B;
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
            scroll-padding-top: 100px;
            -webkit-text-size-adjust: 100%;
        }

        body {
            min-height: 100vh;
            font-family: Inter, ui-sans-serif, system-ui, sans-serif;
            color: var(--npl-text);
            background: var(--npl-background);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        a, button, summary {
            -webkit-tap-highlight-color: transparent;
        }

        a {
            text-decoration: none;
            transition: color .18s ease, background-color .18s ease,
                border-color .18s ease;
        }

        button {
            font: inherit;
        }

        a:focus-visible,
        button:focus-visible,
        summary:focus-visible {
            outline: 3px solid var(--npl-orange);
            outline-offset: 4px;
        }

        img {
            max-width: 100%;
        }

        ::selection {
            color: white;
            background: var(--npl-navy);
        }

        summary {
            list-style: none;
        }

        summary::-webkit-details-marker {
            display: none;
        }

        .npl-container {
            width: 100%;
            max-width: 1280px;
            margin-inline: auto;
            padding-inline: 20px;
        }

        .npl-brand {
            display: inline-flex;
            align-items: center;
            gap: 11px;
            min-width: 0;
            flex-shrink: 0;
        }

        .npl-brand-logo {
            width: 46px;
            height: 46px;
            flex-shrink: 0;
            object-fit: contain;
        }

        .npl-brand-name {
            display: block;
            color: white;
            font-size: 17px;
            font-weight: 800;
            line-height: 1.25;
            letter-spacing: -.55px;
        }

        .npl-brand-subtitle {
            display: block;
            margin-top: 4px;
            color: rgb(255 255 255 / 58%);
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 1.8px;
            text-transform: uppercase;
        }

        .npl-nav-link {
            position: relative;
            display: inline-flex;
            min-height: 76px;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
            color: rgb(255 255 255 / 70%);
            font-size: 12px;
            font-weight: 700;
        }

        .npl-nav-link:hover {
            color: white;
        }

        .npl-nav-link-active {
            color: white;
        }

        .npl-nav-link-active::after {
            position: absolute;
            right: 0;
            bottom: 0;
            left: 0;
            height: 3px;
            border-radius: 3px 3px 0 0;
            background: var(--npl-orange);
            content: '';
        }

        .npl-mobile-link {
            display: flex;
            min-height: 46px;
            align-items: center;
            gap: 12px;
            border-radius: 10px;
            padding: 11px 13px;
            color: var(--npl-navy);
            font-size: 13px;
            font-weight: 650;
        }

        .npl-mobile-link:hover {
            background: #F6F8FB;
        }

        .npl-mobile-link-active {
            color: #D94D31;
            background: #FFF0EC;
        }

        .npl-menu-panel {
            animation: fadeIn .18s ease-out;
        }

        .npl-footer-link {
            display: inline-flex;
            width: fit-content;
            color: rgb(255 255 255 / 65%);
            font-size: 13px;
        }

        .npl-footer-link:hover {
            color: white;
        }

        .npl-footer-heading {
            color: white;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 1.4px;
            text-transform: uppercase;
        }

        .npl-skip-link {
            position: absolute;
            z-index: 100;
            top: 8px;
            left: 8px;
            transform: translateY(-160%);
            border-radius: 8px;
            background: white;
            padding: 12px 16px;
            color: var(--npl-navy);
            font-weight: 700;
        }

        .npl-skip-link:focus {
            transform: translateY(0);
        }

        .npl-footer-logo {
            width: 42px;
            height: 42px;
            object-fit: contain;
        }

        @media (min-width: 640px) {
            .npl-container {
                padding-inline: 28px;
            }
        }

        @media (min-width: 1024px) {
            .npl-container {
                padding-inline: 32px;
            }
        }

        @media (max-width: 420px) {
            .npl-brand {
                gap: 8px;
            }

            .npl-brand-logo {
                width: 39px;
                height: 39px;
            }

            .npl-brand-name {
                font-size: 14px;
            }

            .npl-brand-subtitle {
                font-size: 8px;
                letter-spacing: 1.3px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            html {
                scroll-behavior: auto;
            }

            *,
            *::before,
            *::after {
                animation-duration: .01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: .01ms !important;
            }
        }
    </style>

    @stack('styles')
</head>

<body class="flex min-h-screen flex-col bg-npl-background font-sans text-slate-800 antialiased">

    <a href="#main-content" class="npl-skip-link">
        Skip to main content
    </a>

    {{-- HEADER --}}
    <header class="sticky top-0 z-40 border-b border-white/10 bg-npl-navy text-white shadow-sm">
        <div class="npl-container">
            <div class="flex min-h-[70px] items-center justify-between gap-4 lg:min-h-[76px]">

                <a href="{{ route('home') }}"
                   class="npl-brand"
                   aria-label="NPL Cricket Hub home">

                    <img
                        src="{{ asset('images/npl.png') }}"
                        alt="NPL Cricket Hub"
                        class="npl-brand-logo"
                        width="46"
                        height="46"
                        fetchpriority="high"
                    >

                    <span class="min-w-0">
                        <span class="npl-brand-name">NPL Cricket Hub</span>
                        <span class="npl-brand-subtitle">Nepal Cricket</span>
                    </span>
                </a>

                {{-- DESKTOP NAVIGATION --}}
                <nav class="hidden h-full items-center gap-4 lg:flex xl:gap-6"
                     aria-label="Main navigation">

                    <a href="{{ route('home') }}"
                       @class(['npl-nav-link', 'npl-nav-link-active' => request()->routeIs('home')])
                       @if(request()->routeIs('home')) aria-current="page" @endif>
                        Home
                    </a>

                    <a href="{{ route('fixtures.index') }}"
                       @class(['npl-nav-link', 'npl-nav-link-active' => request()->routeIs('fixtures.*')])
                       @if(request()->routeIs('fixtures.*')) aria-current="page" @endif>
                        Fixtures
                    </a>

                    <a href="{{ route('teams.index') }}"
                       @class(['npl-nav-link', 'npl-nav-link-active' => request()->routeIs('teams.*')])
                       @if(request()->routeIs('teams.*')) aria-current="page" @endif>
                        Teams
                    </a>

                    <a href="{{ route('pointtable.index') }}"
                       @class(['npl-nav-link', 'npl-nav-link-active' => request()->routeIs('pointtable.*')])
                       @if(request()->routeIs('pointtable.*')) aria-current="page" @endif>
                        Points Table
                    </a>

                    <a href="{{ route('seasons.index') }}"
                       @class(['npl-nav-link', 'npl-nav-link-active' => request()->routeIs('seasons.*')])
                       @if(request()->routeIs('seasons.*')) aria-current="page" @endif>
                        Seasons
                    </a>

                    <a href="{{ route('news.index') }}"
                       @class(['npl-nav-link', 'npl-nav-link-active' => request()->routeIs('news.*')])
                       @if(request()->routeIs('news.*')) aria-current="page" @endif>
                        News
                    </a>
                </nav>

                {{-- MOBILE MENU --}}
                <details class="relative shrink-0 lg:hidden">
                    <summary
                        class="flex h-10 w-10 cursor-pointer items-center justify-center rounded-xl border border-white/20 transition hover:bg-white/10"
                        aria-label="Open navigation menu">

                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="h-5 w-5"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor"
                             stroke-width="1.8"
                             aria-hidden="true">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  d="M4 7h16M4 12h16M4 17h16"/>
                        </svg>
                    </summary>

                    <nav class="npl-menu-panel absolute right-0 top-[50px] z-50 w-[min(290px,calc(100vw-32px))] rounded-2xl border border-npl-border bg-white p-3 text-npl-navy shadow-elevated"
                         aria-label="Mobile navigation">

                        <div class="border-b border-slate-100 px-3 pb-3 pt-1">
                            <p class="text-[10px] font-extrabold uppercase tracking-[1.5px] text-slate-400">
                                Explore NPL
                            </p>
                            <p class="mt-1 text-sm font-bold text-npl-navy">
                                Nepal cricket, all in one place
                            </p>
                        </div>

                        <div class="grid gap-1 py-2">

                            <a href="{{ route('home') }}"
                               @class(['npl-mobile-link', 'npl-mobile-link-active' => request()->routeIs('home')])
                               @if(request()->routeIs('home')) aria-current="page" @endif>
                                Home
                            </a>

                            <a href="{{ route('fixtures.index') }}"
                               @class(['npl-mobile-link', 'npl-mobile-link-active' => request()->routeIs('fixtures.*')])
                               @if(request()->routeIs('fixtures.*')) aria-current="page" @endif>
                                Fixtures &amp; Results
                            </a>

                            <a href="{{ route('teams.index') }}"
                               @class(['npl-mobile-link', 'npl-mobile-link-active' => request()->routeIs('teams.*')])
                               @if(request()->routeIs('teams.*')) aria-current="page" @endif>
                                Teams
                            </a>

                            <a href="{{ route('pointtable.index') }}"
                               @class(['npl-mobile-link', 'npl-mobile-link-active' => request()->routeIs('pointtable.*')])
                               @if(request()->routeIs('pointtable.*')) aria-current="page" @endif>
                                Points Table
                            </a>

                            <a href="{{ route('seasons.index') }}"
                               @class(['npl-mobile-link', 'npl-mobile-link-active' => request()->routeIs('seasons.*')])
                               @if(request()->routeIs('seasons.*')) aria-current="page" @endif>
                                Seasons
                            </a>

                            <a href="{{ route('news.index') }}"
                               @class(['npl-mobile-link', 'npl-mobile-link-active' => request()->routeIs('news.*')])
                               @if(request()->routeIs('news.*')) aria-current="page" @endif>
                                Latest News
                            </a>
                        </div>

                        <div class="border-t border-slate-100 px-3 pt-3">
                            <p class="text-xs leading-5 text-slate-500">
                                Your home for Nepal cricket.
                            </p>
                        </div>
                    </nav>
                </details>
            </div>
        </div>
    </header>

    {{-- PAGE CONTENT --}}
    <main id="main-content" class="min-h-[65vh] flex-1" tabindex="-1">
        @yield('content')
    </main>

    {{-- FOOTER --}}
    <footer class="mt-12 bg-npl-darker text-white sm:mt-16">
        <div class="npl-container">

            <div class="grid gap-10 py-10 sm:py-12 md:grid-cols-2 lg:grid-cols-12 lg:gap-12">

                {{-- BRAND --}}
                <div class="lg:col-span-5">
                    <a href="{{ route('home') }}"
                       class="inline-flex items-center gap-3"
                       aria-label="NPL Cricket Hub home">

                        <img
                            src="{{ asset('images/npl.png') }}"
                            alt="NPL Cricket Hub logo"
                            class="npl-footer-logo"
                            width="42"
                            height="42"
                            loading="lazy"
                        >

                        <span>
                            <span class="block text-base font-extrabold tracking-tight">
                                NPL Cricket Hub
                            </span>
                            <span class="mt-1 block text-[10px] font-bold uppercase tracking-[1.5px] text-white/45">
                                Nepal Cricket
                            </span>
                        </span>
                    </a>

                    <p class="mt-5 max-w-md text-sm leading-7 text-white/60">
                        Follow Nepal Premier League fixtures, team squads,
                        standings, match results, player performances and
                        the latest cricket news.
                    </p>

                    <div class="mt-5 inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-2">
                        <span class="h-2 w-2 rounded-full bg-npl-orange"></span>
                        <span class="text-xs font-semibold text-white/70">
                            Made for Nepal cricket fans
                        </span>
                    </div>
                </div>

                {{-- EXPLORE --}}
                <div class="lg:col-span-3">
                    <h2 class="npl-footer-heading">Explore</h2>

                    <nav class="mt-5 grid justify-items-start gap-4"
                         aria-label="Footer navigation">

                        <a href="{{ route('home') }}" class="npl-footer-link">Home</a>
                        <a href="{{ route('fixtures.index') }}" class="npl-footer-link">Fixtures &amp; Results</a>
                        <a href="{{ route('teams.index') }}" class="npl-footer-link">Teams</a>
                        <a href="{{ route('pointtable.index') }}" class="npl-footer-link">Points Table</a>
                        <a href="{{ route('seasons.index') }}" class="npl-footer-link">Season Archive</a>
                        <a href="{{ route('news.index') }}" class="npl-footer-link">Latest News</a>
                    </nav>
                </div>

                {{-- INFORMATION --}}
                <div class="lg:col-span-4">
                    <h2 class="npl-footer-heading">About the scores</h2>

                    <p class="mt-5 text-sm leading-7 text-white/60">
                        Match scores, results and standings are shown using
                        information entered and verified by the site's
                        administrators.
                    </p>

                    <div class="mt-5 border-l-2 border-npl-orange pl-4">
                        <p class="text-sm font-semibold leading-6 text-white/80">
                            One place for fixtures, teams, statistics and
                            Nepal Premier League updates.
                        </p>
                    </div>

                    <nav class="mt-5 flex flex-wrap gap-x-5 gap-y-3"
                         aria-label="Information links">
                        <a href="{{ route('about') }}" class="npl-footer-link">About Us</a>
                        <a href="{{ route('contact') }}" class="npl-footer-link">Contact</a>
                        <a href="{{ route('privacy-policy') }}" class="npl-footer-link">Privacy Policy</a>
                    </nav>
                </div>
            </div>
        </div>

        {{-- COPYRIGHT --}}
        <div class="border-t border-white/10">
            <div class="npl-container">
                <div class="flex flex-col gap-3 py-5 text-xs text-white/45 sm:flex-row sm:items-center sm:justify-between">
                    <p>
                        &copy; {{ date('Y') }} NPL Cricket Hub. All rights reserved.
                    </p>
                    <p>Built for Nepal cricket fans.</p>
                </div>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
