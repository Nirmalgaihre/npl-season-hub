<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Nepal Premier League') | Fan Site</title>

    <meta name="description"
        content="@yield('meta_description', 'The fan home of the Nepal Premier League — live scores, fixtures, teams, standings, tickets and news.')">
    <meta name="theme-color" content="#0B1526">
    <meta name="color-scheme" content="light">

    <meta property="og:site_name" content="Nepal Premier League">
    <meta property="og:type" content="website">
    <meta property="og:title" content="@yield('title', 'Nepal Premier League')">
    <meta property="og:description" content="@yield('meta_description', 'The fan home of the Nepal Premier League.')">
    <meta property="og:url" content="@yield('canonical_url', url()->current())">
    <meta property="og:image" content="@yield('og_image', asset('images/npl-og.jpg'))">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', 'Nepal Premier League')">
    <meta name="twitter:description" content="@yield('meta_description', 'The fan home of the Nepal Premier League.')">

    <link rel="canonical" href="@yield('canonical_url', url()->current())">
    <link rel="icon" type="image/png" href="{{ asset('images/npl.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700;800;900&family=Inter:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>

    <script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    npl: {
                        navy: '#0B1526',
                        navyLight: '#122036',
                        navyMid: '#1A2C47',
                        red: '#D81F39',
                        redDark: '#B0172D',
                        gold: '#F0C75E',
                        goldDark: '#D4A933',
                        background: '#F6F7FA',
                        surface: '#FFFFFF',
                        border: '#E6E8EE',
                        muted: '#64748B',
                        text: '#16202F',
                        green: '#15803D',
                    }
                },
                fontFamily: {
                    sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    display: ['"Barlow Condensed"', 'Inter', 'sans-serif'],
                },
                boxShadow: {
                    soft: '0 4px 24px rgba(11,21,38,.06)',
                    card: '0 2px 8px rgba(15,23,42,.035)',
                    elevated: '0 12px 35px rgba(11,21,38,.14)',
                },
                maxWidth: {
                    site: '1280px'
                },
                keyframes: {
                    fadeIn: {
                        '0%': {
                            opacity: '0',
                            transform: 'translateY(-5px)'
                        },
                        '100%': {
                            opacity: '1',
                            transform: 'translateY(0)'
                        }
                    },
                    pulseDot: {
                        '0%,100%': {
                            opacity: '1'
                        },
                        '50%': {
                            opacity: '.35'
                        }
                    }
                },
                animation: {
                    'fade-in': 'fadeIn .2s ease-out',
                    'pulse-dot': 'pulseDot 1.6s infinite',
                }
            }
        }
    };
    </script>

    <style>
    :root {
        --npl-navy: #0B1526;
        --npl-navy-light: #122036;
        --npl-red: #D81F39;
        --npl-gold: #F0C75E;
        --npl-background: #F6F7FA;
        --npl-border: #E6E8EE;
        --npl-text: #16202F;
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

    .npl-display {
        font-family: "Barlow Condensed", Inter, sans-serif;
    }

    a,
    button,
    summary {
        -webkit-tap-highlight-color: transparent;
    }

    a {
        text-decoration: none;
        transition: color .18s ease, background-color .18s ease, border-color .18s ease;
    }

    button {
        font: inherit;
    }

    a:focus-visible,
    button:focus-visible,
    summary:focus-visible {
        outline: 3px solid var(--npl-gold);
        outline-offset: 4px;
    }

    img {
        max-width: 100%;
    }

    ::selection {
        color: #fff;
        background: var(--npl-red);
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

    /* ===== TOP BAR ===== */
    .npl-topbar {
        background: var(--npl-navy);
        border-bottom: 1px solid rgba(255, 255, 255, .08);
        color: rgba(255, 255, 255, .72);
        font-size: 11px;
        letter-spacing: .3px;
    }

    .npl-topbar a {
        color: rgba(255, 255, 255, .72);
    }

    .npl-topbar a:hover {
        color: var(--npl-gold);
    }

    .npl-topbar-inner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        min-height: 36px;
    }

    .npl-topbar-social {
        display: inline-flex;
        align-items: center;
        gap: 14px;
    }

    .npl-topbar-social a {
        display: inline-flex;
        align-items: center;
    }

    .npl-topbar-social svg {
        width: 14px;
        height: 14px;
    }

    /* ===== BRAND ===== */
    .npl-brand {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
        flex-shrink: 0;
    }

    .npl-brand-logo {
        width: 48px;
        height: 48px;
        flex-shrink: 0;
        object-fit: contain;
    }

    .npl-brand-name {
        display: block;
        color: #fff;
        font-family: "Barlow Condensed", Inter, sans-serif;
        font-size: 22px;
        font-weight: 900;
        line-height: 1;
        letter-spacing: .5px;
        text-transform: uppercase;
    }

    .npl-brand-name span {
        color: var(--npl-gold);
    }

    .npl-brand-subtitle {
        display: block;
        margin-top: 4px;
        color: rgba(255, 255, 255, .55);
        font-size: 9px;
        font-weight: 800;
        letter-spacing: 2.2px;
        text-transform: uppercase;
    }

    /* ===== NAV ===== */
    .npl-nav-link {
        position: relative;
        display: inline-flex;
        min-height: 76px;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        color: rgba(255, 255, 255, .78);
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    .npl-nav-link:hover {
        color: #fff;
    }

    .npl-nav-link-active {
        color: #fff;
    }

    .npl-nav-link-active::after {
        content: "";
        position: absolute;
        right: 0;
        bottom: 0;
        left: 0;
        height: 3px;
        border-radius: 3px 3px 0 0;
        background: var(--npl-gold);
    }

    .npl-cta {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 40px;
        padding: 0 18px;
        border-radius: 4px;
        background: var(--npl-red);
        color: #fff;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 1px;
        text-transform: uppercase;
        transition: background .2s ease, transform .2s ease;
    }

    .npl-cta:hover {
        background: var(--npl-red-dark);
        color: #fff;
        transform: translateY(-1px);
    }

    /* ===== MOBILE MENU ===== */
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
        text-transform: uppercase;
        letter-spacing: .5px;
    }

    .npl-mobile-link:hover {
        background: var(--npl-background);
    }

    .npl-mobile-link-active {
        color: var(--npl-red);
        background: #FFF1F3;
    }

    .npl-menu-panel {
        animation: fadeIn .18s ease-out;
    }

    /* ===== FOOTER ===== */
    .npl-footer {
        background: var(--npl-navy);
        color: #fff;
    }

    .npl-footer-heading {
        color: #fff;
        font-family: "Barlow Condensed", Inter, sans-serif;
        font-size: 15px;
        font-weight: 800;
        letter-spacing: 1.6px;
        text-transform: uppercase;
    }

    .npl-footer-link {
        display: inline-flex;
        width: fit-content;
        color: rgba(255, 255, 255, .6);
        font-size: 13px;
        font-weight: 500;
    }

    .npl-footer-link:hover {
        color: var(--npl-gold);
    }

    .npl-footer-logo {
        width: 46px;
        height: 46px;
        object-fit: contain;
    }

    .npl-footer-social {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        border-radius: 50%;
        border: 1px solid rgba(255, 255, 255, .18);
        color: rgba(255, 255, 255, .75);
        transition: all .2s ease;
    }

    .npl-footer-social:hover {
        border-color: var(--npl-gold);
        color: var(--npl-gold);
        transform: translateY(-2px);
    }

    .npl-footer-social svg {
        width: 16px;
        height: 16px;
    }

    .npl-skip-link {
        position: absolute;
        z-index: 100;
        top: 8px;
        left: 8px;
        transform: translateY(-160%);
        border-radius: 8px;
        background: #fff;
        padding: 12px 16px;
        color: var(--npl-navy);
        font-weight: 700;
    }

    .npl-skip-link:focus {
        transform: translateY(0);
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
            width: 40px;
            height: 40px;
        }

        .npl-brand-name {
            font-size: 18px;
        }

        .npl-brand-subtitle {
            font-size: 8px;
            letter-spacing: 1.6px;
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

    <a href="#main-content" class="npl-skip-link">Skip to main content</a>

    {{-- ===================== TOP BAR ===================== --}}
    <div class="npl-topbar">
        <div class="npl-container npl-topbar-inner">
            <div class="hidden sm:flex items-center gap-4">
                <span class="inline-flex items-center gap-2">
                    <span class="h-1.5 w-1.5 rounded-full bg-npl-red animate-pulse-dot"></span>
                    <span class="font-bold uppercase tracking-widest">Fan Site</span>
                </span>
                <span class="hidden md:inline text-white/30">•</span>
                <span class="hidden md:inline">Nepal Premier League — Season 2026</span>
            </div>

            <div class="flex items-center gap-4 ml-auto">
                <a href="https://esewa.com.np/" target="_blank"
                    class="hidden sm:inline font-bold uppercase tracking-widest text-npl-gold hover:text-white">
                    Buy Tickets
                </a>
                <span class="hidden sm:inline text-white/30">|</span>
                <div class="npl-topbar-social" aria-label="Social media">
                    <a href="#" aria-label="Facebook">
                        <svg fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M22 12a10 10 0 1 0-11.6 9.9v-7H7.9V12h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.5h-1.3c-1.2 0-1.6.8-1.6 1.6V12h2.8l-.4 2.9h-2.4v7A10 10 0 0 0 22 12Z" />
                        </svg>
                    </a>
                    <a href="#" aria-label="X">
                        <svg fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M18.9 2H22l-7.4 8.5L23 22h-6.8l-5.3-7-6.1 7H1.7l7.9-9L1 2h6.9l4.8 6.4L18.9 2Zm-1.2 18h1.9L6.4 3.9H4.4L17.7 20Z" />
                        </svg>
                    </a>
                    <a href="#" aria-label="Instagram">
                        <svg fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M12 2.2c3.2 0 3.6 0 4.9.1 1.2.1 1.8.3 2.2.4.6.2 1 .5 1.5 1 .5.5.8.9 1 1.5.2.4.4 1 .4 2.2.1 1.3.1 1.7.1 4.9s0 3.6-.1 4.9c-.1 1.2-.3 1.8-.4 2.2-.2.6-.5 1-1 1.5-.5.5-.9.8-1.5 1-.4.2-1 .4-2.2.4-1.3.1-1.7.1-4.9.1s-3.6 0-4.9-.1c-1.2-.1-1.8-.3-2.2-.4-.6-.2-1-.5-1.5-1-.5-.5-.8-.9-1-1.5-.2-.4-.4-1-.4-2.2-.1-1.3-.1-1.7-.1-4.9s0-3.6.1-4.9c.1-1.2.3-1.8.4-2.2.2-.6.5-1 1-1.5.5-.5.9-.8 1.5-1 .4-.2 1-.4 2.2-.4 1.3-.1 1.7-.1 4.9-.1Zm0 3.6a6.2 6.2 0 1 0 0 12.4 6.2 6.2 0 0 0 0-12.4Zm0 10.2a4 4 0 1 1 0-8 4 4 0 0 1 0 8Zm6.4-10.5a1.4 1.4 0 1 1-2.8 0 1.4 1.4 0 0 1 2.8 0Z" />
                        </svg>
                    </a>
                    <a href="#" aria-label="YouTube">
                        <svg fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M23.5 6.5c-.3-1-1-1.8-2-2.1C19.8 4 12 4 12 4s-7.8 0-9.5.4c-1 .3-1.7 1-2 2.1C0 8.2 0 12 0 12s0 3.8.4 5.5c.3 1 1 1.8 2 2.1 1.7.4 9.5.4 9.5.4s7.8 0 9.5-.4c1-.3 1.7-1 2-2.1.4-1.7.4-5.5.4-5.5s0-3.8-.4-5.5ZM9.6 15.6V8.4l6.2 3.6-6.2 3.6Z" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================== HEADER ===================== --}}
    <header class="sticky top-0 z-40 border-b border-white/10 bg-npl-navy text-white shadow-sm">
        <div class="npl-container">
            <div class="flex min-h-[70px] items-center justify-between gap-4 lg:min-h-[76px]">

                <a href="{{ route('home') }}" class="npl-brand" aria-label="Nepal Premier League home">
                    <img src="{{ asset('images/npl.png') }}" alt="Nepal Premier League" class="npl-brand-logo"
                        width="48" height="48" fetchpriority="high">
                    <span class="min-w-0">
                        <span class="npl-brand-name">Nepal Premier <span>League</span></span>
                        <span class="npl-brand-subtitle">Fan Website</span>
                    </span>
                </a>

                {{-- DESKTOP NAV --}}
                <nav class="hidden h-full items-center gap-5 lg:flex xl:gap-7" aria-label="Main navigation">
                    <a href="{{ route('home') }}" @class(['npl-nav-link', 'npl-nav-link-active'=>
                        request()->routeIs('home')])
                        @if(request()->routeIs('home')) aria-current="page" @endif>Home</a>

                    <a href="{{ route('fixtures.index') }}" @class(['npl-nav-link', 'npl-nav-link-active'=>
                        request()->routeIs('fixtures.*')])
                        @if(request()->routeIs('fixtures.*')) aria-current="page" @endif>Matches</a>

                    <a href="{{ route('teams.index') }}" @class(['npl-nav-link', 'npl-nav-link-active'=>
                        request()->routeIs('teams.*')])
                        @if(request()->routeIs('teams.*')) aria-current="page" @endif>Teams</a>

                    <a href="{{ route('pointtable.index') }}" @class(['npl-nav-link', 'npl-nav-link-active'=>
                        request()->routeIs('pointtable.*')])
                        @if(request()->routeIs('pointtable.*')) aria-current="page" @endif>Standings</a>

                    <a href="{{ route('news.index') }}" @class(['npl-nav-link', 'npl-nav-link-active'=>
                        request()->routeIs('news.*')])
                        @if(request()->routeIs('news.*')) aria-current="page" @endif>News</a>

                    <a href="{{ route('about') }}" @class(['npl-nav-link', 'npl-nav-link-active'=>
                        request()->routeIs('about')])
                        @if(request()->routeIs('about')) aria-current="page" @endif>About</a>
                </nav>

                <div class="hidden items-center gap-3 lg:flex">
                    <a href="#" class="npl-cta">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 0 0-2 2v3a2 2 0 1 1 0 4v3a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-3a2 2 0 1 1 0-4V7a2 2 0 0 0-2-2H5Z" />
                        </svg>
                        Tickets
                    </a>
                </div>

                {{-- MOBILE MENU --}}
                <details class="relative shrink-0 lg:hidden">
                    <summary
                        class="flex h-10 w-10 cursor-pointer items-center justify-center rounded-xl border border-white/20 transition hover:bg-white/10"
                        aria-label="Open navigation menu">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16" />
                        </svg>
                    </summary>

                    <nav class="npl-menu-panel absolute right-0 top-[50px] z-50 w-[min(290px,calc(100vw-32px))] rounded-2xl border border-npl-border bg-white p-3 text-npl-navy shadow-elevated"
                        aria-label="Mobile navigation">
                        <div class="border-b border-slate-100 px-3 pb-3 pt-1">
                            <p class="text-[10px] font-extrabold uppercase tracking-[1.5px] text-slate-400">Nepal
                                Premier League</p>
                            <p class="mt-1 text-sm font-bold text-npl-navy">Fan menu</p>
                        </div>

                        <div class="grid gap-1 py-2">
                            <a href="{{ route('home') }}"
                                @class(['npl-mobile-link','npl-mobile-link-active'=>request()->routeIs('home')])>Home</a>
                            <a href="{{ route('fixtures.index') }}"
                                @class(['npl-mobile-link','npl-mobile-link-active'=>request()->routeIs('fixtures.*')])>Matches</a>
                            <a href="{{ route('teams.index') }}"
                                @class(['npl-mobile-link','npl-mobile-link-active'=>request()->routeIs('teams.*')])>Teams</a>
                            <a href="{{ route('pointtable.index') }}"
                                @class(['npl-mobile-link','npl-mobile-link-active'=>request()->routeIs('pointtable.*')])>Standings</a>
                            <a href="{{ route('news.index') }}"
                                @class(['npl-mobile-link','npl-mobile-link-active'=>request()->routeIs('news.*')])>News</a>
                            <a href="{{ route('about') }}"
                                @class(['npl-mobile-link','npl-mobile-link-active'=>request()->routeIs('about')])>About</a>
                        </div>

                        <div class="border-t border-slate-100 pt-3">
                            <a href="https://esewa.com.np/" target="_blank" class="npl-cta w-full">
                                Buy Tickets
                            </a>
                        </div>
                    </nav>
                </details>
            </div>
        </div>
    </header>

    {{-- ===================== PAGE CONTENT ===================== --}}
    <main id="main-content" class="min-h-[65vh] flex-1" tabindex="-1">
        @yield('content')
    </main>

    {{-- ===================== FOOTER ===================== --}}
    <footer class="npl-footer mt-12 sm:mt-16">
        <div class="npl-container">
            <div class="grid gap-10 py-12 sm:py-14 md:grid-cols-2 lg:grid-cols-12 lg:gap-12">

                {{-- BRAND --}}
                <div class="lg:col-span-5">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-3"
                        aria-label="Nepal Premier League home">
                        <img src="{{ asset('images/npl.png') }}" alt="Nepal Premier League" class="npl-footer-logo"
                            width="46" height="46" loading="lazy">
                        <span>
                            <span
                                class="block npl-display text-2xl font-black uppercase leading-none tracking-wide text-white">
                                Nepal Premier <span class="text-npl-gold">League</span>
                            </span>
                            <span class="mt-1.5 block text-[10px] font-bold uppercase tracking-[2px] text-white/45">
                                Fan Website
                            </span>
                        </span>
                    </a>

                    <p class="mt-5 max-w-md text-sm leading-7 text-white/60">
                        The Fan home of the Nepal Premier League. Follow live scores,
                        fixtures, teams, standings, tickets and news from Nepal's premier
                        T20 cricket competition.
                    </p>

                    <div class="mt-6 flex items-center gap-3">
                        <a href="#" class="npl-footer-social" aria-label="Facebook">
                            <svg fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M22 12a10 10 0 1 0-11.6 9.9v-7H7.9V12h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.5h-1.3c-1.2 0-1.6.8-1.6 1.6V12h2.8l-.4 2.9h-2.4v7A10 10 0 0 0 22 12Z" />
                            </svg>
                        </a>
                        <a href="#" class="npl-footer-social" aria-label="X">
                            <svg fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M18.9 2H22l-7.4 8.5L23 22h-6.8l-5.3-7-6.1 7H1.7l7.9-9L1 2h6.9l4.8 6.4L18.9 2Zm-1.2 18h1.9L6.4 3.9H4.4L17.7 20Z" />
                            </svg>
                        </a>
                        <a href="#" class="npl-footer-social" aria-label="Instagram">
                            <svg fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 2.2c3.2 0 3.6 0 4.9.1 1.2.1 1.8.3 2.2.4.6.2 1 .5 1.5 1 .5.5.8.9 1 1.5.2.4.4 1 .4 2.2.1 1.3.1 1.7.1 4.9s0 3.6-.1 4.9c-.1 1.2-.3 1.8-.4 2.2-.2.6-.5 1-1 1.5-.5.5-.9.8-1.5 1-.4.2-1 .4-2.2.4-1.3.1-1.7.1-4.9.1s-3.6 0-4.9-.1c-1.2-.1-1.8-.3-2.2-.4-.6-.2-1-.5-1.5-1-.5-.5-.8-.9-1-1.5-.2-.4-.4-1-.4-2.2-.1-1.3-.1-1.7-.1-4.9s0-3.6.1-4.9c.1-1.2.3-1.8.4-2.2.2-.6.5-1 1-1.5.5-.5.9-.8 1.5-1 .4-.2 1-.4 2.2-.4 1.3-.1 1.7-.1 4.9-.1Zm0 3.6a6.2 6.2 0 1 0 0 12.4 6.2 6.2 0 0 0 0-12.4Zm0 10.2a4 4 0 1 1 0-8 4 4 0 0 1 0 8Zm6.4-10.5a1.4 1.4 0 1 1-2.8 0 1.4 1.4 0 0 1 2.8 0Z" />
                            </svg>
                        </a>
                        <a href="#" class="npl-footer-social" aria-label="YouTube">
                            <svg fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M23.5 6.5c-.3-1-1-1.8-2-2.1C19.8 4 12 4 12 4s-7.8 0-9.5.4c-1 .3-1.7 1-2 2.1C0 8.2 0 12 0 12s0 3.8.4 5.5c.3 1 1 1.8 2 2.1 1.7.4 9.5.4 9.5.4s7.8 0 9.5-.4c1-.3 1.7-1 2-2.1.4-1.7.4-5.5.4-5.5s0-3.8-.4-5.5ZM9.6 15.6V8.4l6.2 3.6-6.2 3.6Z" />
                            </svg>
                        </a>
                    </div>
                </div>

                {{-- LEAGUE --}}
                <div class="lg:col-span-3">
                    <h2 class="npl-footer-heading">League</h2>
                    <nav class="mt-5 grid justify-items-start gap-3.5" aria-label="League links">
                        <a href="{{ route('fixtures.index') }}" class="npl-footer-link">Matches &amp; Fixtures</a>
                        <a href="{{ route('teams.index') }}" class="npl-footer-link">Teams</a>
                        <a href="{{ route('pointtable.index') }}" class="npl-footer-link">Standings</a>
                        <a href="{{ route('seasons.index') }}" class="npl-footer-link">Season Archive</a>
                        <a href="{{ route('news.index') }}" class="npl-footer-link">News</a>
                        <a href="#" class="npl-footer-link">Tickets</a>
                    </nav>
                </div>

                {{-- SUPPORT --}}
                <div class="lg:col-span-4">
                    <h2 class="npl-footer-heading">Support &amp; Info</h2>
                    <nav class="mt-5 grid justify-items-start gap-3.5" aria-label="Support links">
                        <a href="{{ route('about') }}" class="npl-footer-link">About the League</a>
                        <a href="{{ route('contact') }}" class="npl-footer-link">Contact Us</a>
                        <a href="{{ route('privacy-policy') }}" class="npl-footer-link">Privacy Policy</a>
                        <a href="#" class="npl-footer-link">Terms of Use</a>
                        <a href="#" class="npl-footer-link">FAQs</a>
                    </nav>

                    <div class="mt-6 border-l-2 border-npl-gold pl-4">
                        <p class="text-sm font-semibold leading-6 text-white/80">
                            Fan-generated scores, results and standings are verified by league administrators.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- COPYRIGHT --}}
        <div class="border-t border-white/10">
            <div class="npl-container">
                <div
                    class="flex flex-col gap-3 py-5 text-xs text-white/45 sm:flex-row sm:items-center sm:justify-between">
                    <p>&copy; {{ date('Y') }} Nepal Premier League. All rights reserved.</p>
                    <p class="flex items-center gap-2">
                        <span class="h-1.5 w-1.5 rounded-full bg-npl-red"></span>
                        The fan home of NPL cricket.
                    </p>
                </div>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>

</html>